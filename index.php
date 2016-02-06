<?php
/**
 * RUIAN QUERY INTERFACE
 *
 * /obec/{id_obce}
 * /hledej_obec?q={obec}
 * /casti_obce/{id_obce}
 * /ulice/{id_obce}[?q={ulice}]
 * /adresa/{id_adresy}
 * /adresy_obce/{id_obce}[?limit=&offset=]
 * /adresy_casti_obce/{id_cast_obce}[?limit=&offset=]
 */


require 'vendor/autoload.php';

$connection = new PDO("mysql:dbname=ruian;charset=utf8", 'ruian', 'ruian');
$db = new NotORM($connection);

$container = new \Slim\Container();
$app = new \Slim\App($container);
$container['db'] = $db;

$app->add(new \CorsSlim\CorsSlim());

function apply_limit($obj, $request)
{
    $limit = $request->getParam('limit');
    $offset = $request->getParam('offset') ?: 0;

    if ($limit) {
        $obj = $obj->limit($limit, $offset);
    }
    return $obj;
}

function apply_search($obj, $request, $field){
    if ($q = $request->getParam('q')) {
        $obj = $obj->where("$field LIKE ?", "$q%");
    }
    return $obj;
}

function adr_to_arr($adr, $full = false){
    global  $db;
    $res = array(
        'id' => $adr['id'],
        'obec_id' => $adr['obec_id'],
        'casti_obce_id' => $adr['casti_obce_id'],
        'ulice' => $adr['nazev_ulice'],
        'typ_so' => $adr['typ_so'],
        'cp' => $adr['cislo_domovni'],
        'co' => $adr['cislo_orientacni'],
        'znak_co' => $adr['znak_cisla_orientacniho'],
        'x' => $adr['souradnice_x'],
        'y' => $adr['souradnice_y'],
    );

    if($full){
        $obec = $db->ruian_obce[$res['obec_id']];
        $res['obec'] = ['id'=>$obec['id'], 'nazev' => $obec['nazev']];
        $cast = $db->ruian_casti_obce[$res['casti_obce_id']];
        $res['cast_obce'] = ['id'=>$cast['id'], 'nazev' => $cast['nazev'],'psc'=>$cast['psc'],'nazev_mop'=>$cast['nazev_mop'], 'nazev_momc'=>$cast['nazev_momc']];
    }

    return $res;
}


$app->get('/obce', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {
    /**
     * @var \Slim\Http\Response $response
     */
    $db = $this->get('db');
    $res = array();
    $obce = $db->ruian_obce();
    $obce = apply_limit($obce,$request);
    $obce = apply_search($obce,$request,'nazev');

    foreach ($obce as $obec) {
        $res[] = array('id' => $obec['obec_id'], 'nazev' => $obec['nazev']);
    }
    return $response->withJson($res);

});

$app->get('/obec/{id}', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

    $db = $this->get('db');
    $res = array();
    foreach ($db->ruian_obce()->where('obec_id', $args['id']) as $obec) {
        $res[] = array('id' => $obec['obec_id'], 'nazev' => $obec['nazev']);
    }
    return $response->withJson($res);

});
$app->get('/adresy/{id}', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

    $db = $this->get('db');
    $res = array();

    $adresy = $db->ruian_adresy()->where('obec_id', $args['id']);
    $adresy = apply_limit($adresy,$request);
    $adresy = apply_search($adresy,$request,'nazev_ulice');

    foreach ($adresy as $adr) {
        $res[] = adr_to_arr($adr);
    }

    return $response->withJson($res);

});
$app->get('/casti_obce/{id}', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

    $db = $this->get('db');
    $res = array();

    $casti = $db->ruian_casti_obci()->where('obec_id', $args['id']);
    $casti = apply_limit($casti,$request);
    $casti = apply_search($casti,$request,'nazev');

    foreach ($casti as $cast) {
        $res[] = ['id'=>$cast['id'],'nazev'=>$cast['nazev']];
    }

    return $response->withJson($res);

});

/**
 * /najit?{o=OBEC|oid=obec_id}&u=ULICE&c=CISLO_POPISNE&co=CISLO_ORIENTACNI
 */
$app->get('/najit', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

    $db = $this->get('db');
    $res = array();
    $mesto = $request->getParam('o');
    $obec_id = $request->getParam('oid');
    $ulice = $request->getParam('u');
    $cp = $request->getParam('c');
    $co = $request->getParam('co');
    $znak = '';
    //Cislo orientacni muze mit i znak
    if(preg_match('/(\d+)(.*)/',$co,$results)){
        $co = $results[1];
        $znak = $results[2];
    }

    $id_obci = array();
    if(!$obec_id) {
        $mesta = $db->ruian_obce()->where('nazev LIKE ?', $mesto);
        foreach ($mesta as $row) {
            $id_obci[] = $row['id'];
        }

    } else {
        $id_obci = [$obec_id];
    }

    $adresy = $db->ruian_adresy()->where('obec_id', $id_obci);

    if($ulice){
        $adresy = $adresy->where('nazev_ulice LIKE ?',$ulice);
    }
    if($cp){
        $adresy = $adresy->where('cislo_domovni',$cp);
    }
    if($co){
        $adresy = $adresy->where('cislo_orientacni',$co);

    }
    if($znak){
        $adresy = $adresy->where('znak_cisla_orientacniho=?',$znak);
    }


    foreach ($adresy as $adr) {
        $res[] = adr_to_arr($adr, true);
    }

    return $response->withJson($res);

});


$app->run();