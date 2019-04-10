<?php
/**
 * RUIAN QUERY INTERFACE
 *
 * /obec/{id_obce}
 * /hledej_obec?q={obec}
 * /casti_obce/{id_obce}
 * /adresy/{id_obce}
 * /obec/{id_obce}/adresy
 * /adresa/{id_adresy}
 * /adresy_obce/{id_obce}[?limit=&offset=]
 * /adresy_casti_obce/{id_cast_obce}[?limit=&offset=]
 * /ruians?q=comma_separated_string
 * /search?q=street number,city
 * /detail/{ruian}
 *
 */


require 'vendor/autoload.php';
require 'config.php';

use Slim\Views\PhpRenderer;

$connection = new PDO("mysql:dbname=" . DB_DATABASE . ";host=" . DB_HOST . ";charset=utf8", DB_USER, DB_PASSWORD);
$db = new NotORM($connection);

$container = new \Slim\Container();
$app = new \Slim\App($container);
$container['db'] = $db;

$app->add(new \CorsSlim\CorsSlim());
$container = $app->getContainer();
$container['renderer'] = new PhpRenderer("./templates");

function apply_limit($obj, $request)
{
  $limit = $request->getParam('limit');
  $offset = $request->getParam('offset') ?: 0;

  if ($limit) {
    $obj = $obj->limit($limit, $offset);
  }
  return $obj;
}

function apply_search($obj, $request, $field)
{
  if ($q = $request->getParam('q')) {
    $obj = $obj->where("$field LIKE ?", "$q%");
  }
  return $obj;
}

function apply_field($obj, $request, $field)
{
  if ($q = $request->getParam($field)) {
    $obj = $obj->where($field, $q);
  }
  return $obj;
}

function casti_obce($db, $obec_id)
{
  $casti = $db->ruian_casti_obce()->where('obec_id', $obec_id);
  foreach ($casti as $cast) {
    $res[] = ['id' => $cast['id'], 'nazev' => $cast['nazev'], 'psc' => $cast['psc'], 'mo' => $cast['nazev_momc'], 'mop' => $cast['nazev_mop']];
  }
  return $res;
}

function cast_to_arr($cast)
{
  return ['id' => $cast['id'], 'obec_id' => $cast['obec_id'], 'nazev' => $cast['nazev'], 'psc' => $cast['psc'], 'mo' => $cast['nazev_momc'], 'mop' => $cast['nazev_mop']];
}

function ulice_to_arr($ulice)
{
  return ['id' => $ulice['id'], 'obec_id' => $ulice['obec_id'], 'nazev_ulice' => $ulice['nazev_ulice']];
}

function obec_to_arr($obec, $casti = null)
{
  return array('id' => $obec['id'], 'nazev' => $obec['nazev'], 'casti' => $casti);
}

/**
 * @param $x
 * @param $y
 * @param int $H Vyska nad morem, std. 200
 * @return array GPS['lat','lng']
 */
function toGPS($x, $y, $H = 200)
{
  /*Vypocet zemepisnych souradnic z rovinnych souradnic*/
  $a = 6377397.15508;
  $e = 0.081696831215303;
  $n = 0.97992470462083;
  $konst_u_ro = 12310230.12797036;
  $sinUQ = 0.863499969506341;
  $cosUQ = 0.504348889819882;
  $sinVQ = 0.420215144586493;
  $cosVQ = 0.907424504992097;
  $alfa = 1.000597498371542;
  $k = 1.003419163966575;
  $ro = max(sqrt($x * $x + $y * $y), 0.01);
  $epsilon = 2 * atan($y / ($ro + $x));
  $D = $epsilon / $n;
  $S = 2 * atan(exp(1 / $n * log($konst_u_ro / $ro))) - M_PI_2;
  $sinS = sin($S);
  $cosS = cos($S);
  $sinU = $sinUQ * $sinS - $cosUQ * $cosS * cos($D);
  $cosU = sqrt(1 - $sinU * $sinU);
  $sinDV = sin($D) * $cosS / $cosU;
  $cosDV = sqrt(1 - $sinDV * $sinDV);
  $sinV = $sinVQ * $cosDV - $cosVQ * $sinDV;
  $cosV = $cosVQ * $cosDV + $sinVQ * $sinDV;
  $Ljtsk = 2 * atan($sinV / (1 + $cosV)) / $alfa;
  $t = exp(2 / $alfa * log((1 + $sinU) / $cosU / $k));
  $pom = ($t - 1) / ($t + 1);
  do {
    $sinB = $pom;
    $pom = $t * exp($e * log((1 + $e * $sinB) / (1 - $e * $sinB)));
    $pom = ($pom - 1) / ($pom + 1);
  } while (abs($pom - $sinB) > 0.000000000000001);
  $Bjtsk = atan($pom / sqrt(1 - $pom * $pom));
  /* Pravoúhlé souřadnice ve S-JTSK */
  $a = 6377397.15508;
  $f_1 = 299.152812853;
  $e2 = 1 - (1 - 1 / $f_1) * (1 - 1 / $f_1);
  $ro = $a / sqrt(1 - $e2 * sin($Bjtsk) * sin($Bjtsk));
  $x = ($ro + $H) * cos($Bjtsk) * cos($Ljtsk);
  $y = ($ro + $H) * cos($Bjtsk) * sin($Ljtsk);
  $z = ((1 - $e2) * $ro + $H) * sin($Bjtsk);

  /* Pravoúhlé souřadnice v WGS-84*/
  $dx = 570.69;
  $dy = 85.69;
  $dz = 462.84;
  $wz = -5.2611 / 3600 * M_PI / 180;
  $wy = -1.58676 / 3600 * M_PI / 180;
  $wx = -4.99821 / 3600 * M_PI / 180;
  $m = 3.543 * pow(10, -6);
  $xn = $dx + (1 + $m) * ($x + $wz * $y - $wy * $z);
  $yn = $dy + (1 + $m) * (-$wz * $x + $y + $wx * $z);
  $zn = $dz + (1 + $m) * ($wy * $x - $wx * $y + $z);
  /* Geodetické souřadnice v systému WGS-84*/
  $a = 6378137.0;
  $f_1 = 298.257223563;
  $a_b = $f_1 / ($f_1 - 1);
  $p = sqrt($xn * $xn + $yn * $yn);
  $e2 = 1 - (1 - 1 / $f_1) * (1 - 1 / $f_1);
  $theta = atan($zn * $a_b / $p);
  $st = sin($theta);
  $ct = cos($theta);
  $t = ($zn + $e2 * $a_b * $a * $st * $st * $st) / ($p - $e2 * $a * $ct * $ct * $ct);
  $B = atan($t);
  $L = 2 * atan($yn / ($p + $xn));
  $B = $B / M_PI * 180;
  if ($B < 0) {
    $B = -$B;
  }
  $gps = array();
  $gps['lat'] = $B;

  $L = $L / M_PI * 180;
  //$delka="E";
  if ($L < 0) {
    $L = -$L;
    //$delka="W";
  }
  $gps['lng'] = $L;

  return $gps;
}

function adr_to_arr($adr, $full = false)
{
  global $db;
  $res = array(
    'id' => $adr['id'],
    'obec_id' => $adr['obec_id'],
    'casti_obce_id' => $adr['casti_obce_id'],
    'ulice' => $adr['nazev_ulice'],
    'ulice_id' => $adr['ulice_id'],
    'typ_so' => $adr['typ_so'],
    'cp' => $adr['cislo_domovni'],
    'co' => $adr['cislo_orientacni'],
    'znak_co' => $adr['znak_cisla_orientacniho'],
    'x' => $adr['souradnice_x'],
    'y' => $adr['souradnice_y'],
    'gps' => toGPS($adr['souradnice_x'], $adr['souradnice_y']),
  );

  if ($adr['obec']) {
    $res['obec'] = $adr['obec'];
  }

  if ($full) {
    $obec = $db->ruian_obce[$res['obec_id']];
    $res['obec'] = ['id' => $obec['id'], 'nazev' => $obec['nazev']];
    $cast = $db->ruian_casti_obce[$res['casti_obce_id']];
    $res['cast_obce'] = ['id' => $cast['id'], 'nazev' => $cast['nazev'], 'psc' => $cast['psc'], 'mop' => $cast['nazev_mop'], 'mo' => $cast['nazev_momc']];
  }

  return $res;
}


$app->get('/obce', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {
  /**
   * @var \Slim\Http\Response $response
   */
  $db = $this->db;
  $res = array();
  $obce = $db->ruian_obce();
  $obce = apply_limit($obce, $request);
  $obce = apply_search($obce, $request, 'nazev');

  foreach ($obce as $obec) {
    $casti = casti_obce($db, $obec['id']);
    $res[] = obec_to_arr($obec, $casti);

  }
  return $response->withJson($res);

});

$app->get('/obec/{id}', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

  $db = $this->db;
  $res = array();
  $obec = $db->ruian_obce[$args['id']];
  if (!$obec) {
    return $response->withStatus('404');
  }

  return $response->withJson($obec);
});
$app->get('/cast_obce/{id}', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

  $db = $this->db;
  $res = array();
  $obec = $db->ruian_casti_obce[$args['id']];
  if (!$obec) {
    return $response->withStatus('404');
  }

  return $response->withJson($obec);
});

$app->get('/adresa/{id}', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

  $db = $this->db;
  $res = array();
  $adresa = $db->ruian_adresy[$args['id']];
  if (!$adresa) {
    return $response->withStatus('404');
  }

  return $response->withJson(adr_to_arr($adresa, true));
});

$app->get('/detail/{id}', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

  $db = $this->db;
  $res = array();
  $adresa = $db->ruian_adresy[$args['id']];
  if (!$adresa) {
    return $response->withStatus('404');
  }
  $args['adresa'] = adr_to_arr($adresa, true);
  return $this->renderer->render($response, "/detail.php", $args);

});

$app->get('/adresy/{id}', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

  $db = $this->db;
  $res = array();
  $obec = $db->ruian_obce[$args['id']];

  $adresy = $db->ruian_adresy()->where('obec_id', $args['id']);
  $adresy = apply_limit($adresy, $request);
  $adresy = apply_search($adresy, $request, 'nazev_ulice');
  $adresy = apply_field($adresy, $request, 'casti_obce_id');
  $adresy = apply_field($adresy, $request, 'ulice_id');

  foreach ($adresy as $adr) {
    $adr['obec'] = $obec['nazev'];
    $res[] = adr_to_arr($adr);
  }
  return $response->withJson($res);
});

$app->get('/casti_obce/{id}', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

  $db = $this->db;
  $res = array();

  $casti = $db->ruian_casti_obce()->where('obec_id', $args['id']);
  $casti = apply_limit($casti, $request);
  $casti = apply_search($casti, $request, 'nazev');

  foreach ($casti as $cast) {
    $res[] = cast_to_arr($cast);
  }

  return $response->withJson($res);

});

$app->get('/ulice/{id}', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

  $db = $this->db;
  $res = array();

  $ulice = $db->ruian_ulice()->where('obec_id', $args['id']);
  $ulice = apply_field($ulice, $request, 'casti_obce_id');
  $ulice = apply_limit($ulice, $request);
  $ulice = apply_search($ulice, $request, 'nazev');

  foreach ($ulice as $ulice) {
    $res[] = ulice_to_arr($ulice);
  }

  return $response->withJson($res);

});

/**
 * /najit?{o=OBEC|oid=obec_id}&u=ULICE&c=CISLO_POPISNE&co=CISLO_ORIENTACNI
 */
$app->get('/najit', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

  $db = $this->db;
  $res = array();
  $mesto = $request->getParam('o');
  $obec_id = $request->getParam('oid');
  $ulice = $request->getParam('u');
  $cp = $request->getParam('c');
  $co = $request->getParam('co');
  $znak = '';
  //Cislo orientacni muze mit i znak
  if (preg_match('/(\d+)(.*)/', $co, $results)) {
    $co = $results[1];
    $znak = $results[2];
  }

  $id_obci = array();
  if (!$obec_id) {
    $mesta = $db->ruian_obce()->where('nazev LIKE ?', $mesto);
    foreach ($mesta as $row) {
      $id_obci[] = $row['id'];
    }
  } else {
    $id_obci = [$obec_id];
  }

  $adresy = $db->ruian_adresy()->where('obec_id', $id_obci);

  if ($ulice) {
    $adresy = $adresy->where('nazev_ulice LIKE ?', $ulice);
  }
  if ($cp) {
    $adresy = $adresy->where('cislo_domovni', $cp);
  }
  if ($co) {
    $adresy = $adresy->where('cislo_orientacni', $co);

  }
  if ($znak) {
    $adresy = $adresy->where('znak_cisla_orientacniho=?', $znak);
  }

  foreach ($adresy as $adr) {
    $res[] = adr_to_arr($adr, true);
  }

  return $response->withJson($res);
});
/**
 * /search?q=street number,city
 */
$app->get('/search', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

  $db = $this->db;
  $res = array();
  list($street, $mesto) = array_map('trim', explode(',', $request->getParam('q')));
  $znak = '';
  $ulice = '';
  $cp = '';
  $co = '';

  //Cislo orientacni muze mit i znak
  if (preg_match('/^((?:\d+[ .])?[^\d]*)(?: *(\d+)(?:\/?(\d+)?(.*)?))?$/', $street, $results)) {
    $ulice = trim($results[1]);
    $cp = trim($results[2]);
    $co = trim($results[3]);
    $znak = trim($results[4]);
  }
  $id_obci = array();
  $adresy = $db->ruian_adresy()->limit(50);
  if ($mesto) {
    $mesta = $db->ruian_obce()->where('nazev LIKE ?', "$mesto%");
    foreach ($mesta as $row) {
      $id_obci[] = $row['id'];
    }
    $adresy = $adresy->where('obec_id', $id_obci);
  }

  if ($ulice) {
    $adresy = $adresy->where('nazev_ulice LIKE ?', "$ulice%");
  }
  if ($cp) {
    $adresy = $adresy->where('cislo_domovni LIKE ?', "$cp%");
  }
  if ($co) {
    $adresy = $adresy->where('cislo_orientacni', $co);

  }
  if ($znak) {
    $adresy = $adresy->where('znak_cisla_orientacniho = ?', $znak);
  }
  foreach ($adresy as $adr) {
    $res[] = adr_to_arr($adr, true);
  }

  return $response->withJson($res);
});

/**
 * ruians - M: Mesto, MC: mestska cast, bez adresa
 */
$app->get('/ruians', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {
  $db = $this->db;
  $res = [];
  $ruians = array_map('trim', explode(',', $request->getParam('q')));


  $mesta = array_map(function ($item) {
    return str_replace('M:', '', $item);
  }, array_filter($ruians, function ($item) {
    return preg_match('/^M:\d+/', $item);
  }));
  foreach ($db->ruian_obce()->where('id', $mesta) as $obec) {
    $res['M:' . $obec['id']] = ['typ' => 'obec'] + obec_to_arr($obec);
  }
  $casti_obce = array_map(function ($item) {
    return str_replace('MC:', '', $item);
  }, array_filter($ruians, function ($item) {
    return preg_match('/^MC:/', $item);
  }));

  foreach ($db->ruian_casti_obce()->where('id', $casti_obce) as $cast_obce) {
    $res['MC:' . $cast_obce['id']] = ['typ' => 'cast_obce'] + cast_to_arr($cast_obce);
  }
  $adresy = array_filter($ruians, function ($item) {
    return !preg_match('/^MC?:/', $item);
  });
  foreach ($db->ruian_adresy()->where('id', $adresy) as $adresa) {
    $res[$adresa['id']] = ['typ' => 'adresa'] + adr_to_arr($adresa, true);
  }

  return $response->withJson($res);
});

$app->get('/', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {
  return $this->renderer->render($response, "/index.php", $args);
});

$app->run();
