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


$app->get('/obce', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {
    /**
     * @var \Slim\Http\Response $response
     */
    $db = $this->get('db');
    $res = array();
    $obce = $db->ruian_obce();
    $obce = apply_limit($obce,$request);
    $obce = apply_search($obce,$request,'nazev_obce');




    foreach ($obce as $obec) {
//        echo $obec.'<br />';
//        var_dump($obec);
        $res[] = array('kod' => $obec['kod_obce'], 'nazev' => $obec['nazev_obce']);
    }
//    echo json_encode($res);
    return $response->withJson($res);

});

$app->get('/obec/{id}', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

    $db = $this->get('db');
    $res = array();
    foreach ($db->ruian_obce()->where('kod_obce', $args['id']) as $obec) {
        $res[] = array('kod' => $obec['kod_obce'], 'nazev' => $obec['nazev_obce']);
    }
//    echo json_encode($res);
    return $response->withJson($res);

});
$app->get('/adresy/{id}', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {

    $db = $this->get('db');
    $res = array();

    $adresy = $db->ruian_adresy()->where('kod_obce', $args['id']);
    $adresy = apply_limit($adresy,$request);
    $adresy = apply_search($adresy,$request,'nazev_ulice');

    foreach ($adresy as $adr) {
        $res[] = array(
            'kod' => $adr['kod_adm'],
            'kod_obce' => $adr['kod_obce'],
            'kod_casti' => $adr['kod_casti_obce'],
            'ulice' => $adr['nazev_ulice'],
            'typ_so' => $adr['typ_so'],
            'cp' => $adr['cislo_domovni'],
            'co' => $adr['cislo_orientacni'],
            'znak_co' => $adr['znak_cisla_orientacniho'],
            'x' => $adr['souradnice_x'],
            'y' => $adr['souradnice_y'],
        );
    }

    return $response->withJson($res);

});


$app->run();