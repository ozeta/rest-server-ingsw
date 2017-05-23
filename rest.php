<?php

namespace ingsw10;

include_once "settings.php";

include_once "./logic/WaterMeterRest.php";
include_once "./logic/ParameterRest.php";
include_once "./logic/EmployeeRest.php";
include_once "./logic/CustomerRest.php";
include_once "./model/Request.php";
include_once "./model/Response.php";
include_once "./model/DAO/ParametersTableDAO.php";
include_once "./model/DAO/CustomerDAO.php";
include_once "./model/DAO/EmployeeDAO.php";
include_once "./model/DAO/WaterMeterDAO.php";

/**
 * empty response
 */
$response = ['code' => 400, 'body' => null];

/**
 * associates the service to the relative Array
 */
$service = [
    "watermeter" => new WaterMeterRest(new WaterMeterDAO(DBHOST, DBUSER, DBPASSWORD, USERSCHEMA, "watermeter")),
    "employee" => new EmployeeRest(new EmployeeDAO(DBHOST, DBUSER, DBPASSWORD, USERSCHEMA, "employee")),
    "customer" => new CustomerRest(new CustomerDAO(DBHOST, DBUSER, DBPASSWORD, USERSCHEMA, "legal", "physical")),
    "parameters" => new ParameterRest(new ParametersTableDAO(DBHOST, DBUSER, DBPASSWORD, USERSCHEMA, "parameters"))
];

/*
 * splits the url after server's name
 * */
$uri = "$_SERVER[REQUEST_URI]";
$uri = rtrim($uri, '/');
list ($localFolder, $uri) = explode(basename(__FILE__, '.php') . '.php', $uri);

/**
 * separates url tokens by '/'
 */
$uriTokens = explode('/', $uri);
$uriTokens = array_slice($uriTokens, 1);


/**
 * if url is not good formed, throw exception
 */
if (!isset($uriTokens[0])) {
    $response = new Response("404");
    $response->reply();
    //echo("GCI webservice.</br>(!) Warning! Url not valid. Expecting entity");
    exit(0);
}

$uriEntity = $uriTokens[0];

/**
 * puts a client's input into a Request Object
 */
$request = new Request($_SERVER['REQUEST_METHOD'], file_get_contents('php://input'), $uriTokens);

/**
 * launchs the service, if exists into the Array
 *  */
if (isset($service[$uriEntity])) {
    $response = $service[$uriEntity]->parseRequest($request);
    if ($response != null) {
        $response->reply();
    }
} else {
    $response = new Response("404");
    if ($response != null) {
        $response->reply();
    }
}


?>