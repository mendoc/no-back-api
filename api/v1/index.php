<?php

require_once "inc/functions.php";
require_once "inc/requetor.php";
require_once 'inc/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->post('/execute', function () use ($app) {

    verifyRequiredParams(array('token'));

    $token   = $app->request->post('token');

    $requete = getQuery($token);

    if (!$requete['error']) {
        $rt                  = x($requete['query']);
        $response['error']   = ($rt) ? false : true;
        $response['result']  = $rt;
        $response['message'] = l('return');
    } else {
        $response['error']   = true;
        $response['message'] = $requete['message'];
    }

    echoResponse(200, array("response" => $response));
});

$app->post('/del', function () use ($app) {

    verifyRequiredParams(array('token'));

    $token = $app->request->post('token');

    $rt = delQuery($token);

    echoResponse(200, $rt);
});

$app->post('/save', function () use ($app) {

    verifyRequiredParams(array('sql'));

    $query = $app->request->post('sql');
    $token = md5(time());

    $rt = saveQuery($token, $query);

    $response = array("response" => $rt);

    echoResponse(200, $response);
});

$app->post('/queries', function () use ($app) {

    $rt = getQueries();

    $response = array("response" => $rt);

    echoResponse(200, $response);
});

$app->run();