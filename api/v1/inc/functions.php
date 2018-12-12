<?php

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields)
{
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email)
{
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoResponse(400, $response);
        $app->stop();
    }
}


/**
 * @param $status_code
 * @param $response
 */
function echoResponse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');
	
	header('Access-Control-Allow-Origin:*'); 

    echo json_encode($response);
}

/**
 * @param $token
 * @param $query
 * @return mixed
 */
function saveQuery($token, $query)
{
    $queries_path = "./conf/queries.json";
    if (file_exists($queries_path)) {
        $queries_json = file_get_contents($queries_path);
        $queries = json_decode($queries_json, true);
        $queries[$token] = $query;
        file_put_contents($queries_path, json_encode($queries));
        return array("error" => false, "message" => "Query saved successfully !", "query" => array($token => $query));
    } else
        return array("error" => true, "message" => "File not exists.");
}

/**
 * @param $token
 * @return mixed
 */
function getQuery($token)
{
    $queries_path = "./conf/queries.json";
    if (file_exists($queries_path)) {
        $queries_json = file_get_contents($queries_path);
        $queries      = json_decode($queries_json, true);
        if (isset($queries[$token])) {
            $query = $queries[$token];
            return array("error" => false, "query" => $query);
        } else {
            return array("error" => true, "message" => "Query not found.");
        }
    } else
        return array("error" => true, "message" => "File not exists.");
}

/**
 * @param $token
 * @return mixed
 */
function delQuery($token)
{
    $queries_path = "./conf/queries.json";
    if (file_exists($queries_path)) {
        $queries_json = file_get_contents($queries_path);
        $queries      = json_decode($queries_json, true);
        if (isset($queries[$token])) {
            unset($queries[$token]);
            file_put_contents($queries_path, json_encode($queries));
            return array("error" => false, "message" => "Query deleted successfully !");
        } else {
            return array("error" => true, "message" => "Query not found.");
        }
    } else
        return array("error" => true, "message" => "File not exists.");
}
/**
 * @return mixed
 */
function getQueries()
{
    $queries_path = "./conf/queries.json";
    if (file_exists($queries_path)) {
        $queries_json = file_get_contents($queries_path);
        $queries      = json_decode($queries_json, true);
        return array("error" => false, "queries" => $queries);
    } else
        return array("error" => true, "message" => "File not exists.");
}