<?php

namespace ingsw10;

include_once "settings.php";

include_once "./logic/timeRest.php";
include_once "./logic/RestInterface.php";
include_once "./logic/readingRest.php";
include_once "./logic/WaterMeterRest.php";
include_once "./logic/ParameterRest.php";
include_once "./logic/EmployeeRest.php";
include_once "./logic/AuthRest.php";
include_once "./logic/CustomerRest.php";
include_once "./model/Request.php";
include_once "./model/Response.php";
include_once "./model/DAO/ParametersTableDAO.php";
include_once "./model/DAO/CustomerDAO.php";
include_once "./model/DAO/EmployeeDAO.php";
include_once "./model/DAO/WaterMeterDAO.php";
include_once "./model/DAO/ReadingDAO.php";
include_once "./model/Address.php";
include_once "./model/Watermeter.php";
include_once "./model/Legal.php";
include_once "./model/Physical.php";
include_once "./model/CF.php";
include_once "./model/Employee.php";
include_once "./model/Customer.php";
include_once "./model/Reading.php";

class Rest
{

    private $serviceArray;
    private $request;
    private $uriEntity;

    public function __construct($serviceArray)
    {
        $this->serviceArray = $serviceArray;
        $this->check_ssl();
        $this->parse_request();
    }

    public function check_ssl()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {

        } else {
            $response = new Response("404", "SSL TUNNELLING REQUIRED");
            $response->reply();
            exit(0);
        }
    }

    private function parse_request(){
        /**
         * get headers from request
         */
        $headers = getallheaders();

        /*
         * split the url after server's name
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
            exit(0);
        }

        $this->uriEntity = $uriTokens[0];

        /**
         * puts a client's input into a Request Object
         */
        $this->request = new Request($_SERVER['REQUEST_METHOD'], file_get_contents('php://input'), $uriTokens, $headers);
    }

    /**
     * launchs the service, if exists into the serviceArray
     *  */
    public function execute_request(){
        if (isset($this->serviceArray[$this->uriEntity])) {
            $response = $this->serviceArray[$this->uriEntity]->parseRequest($this->request);

            if ($response != null) {
                $response->reply();
            }
        } else {
            $response = new Response("404");
            if ($response != null) {
                $response->reply();
            }
        }
    }
}

/**
 * associates the resource to the relative DAO
 */
$watermeterDAO  = new WaterMeterDAO         (DBHOST, DBUSER, DBPASSWORD, USERSCHEMA, "watermeter", "reading", "legal", "physical");
$readingDAO     = new ReadingDAO            (DBHOST, DBUSER, DBPASSWORD, USERSCHEMA, "reading", "employee", "legal", "physical");
$employeeDAO    = new EmployeeDAO           (DBHOST, DBUSER, DBPASSWORD, USERSCHEMA, "employee");
$customerDAO    = new CustomerDAO           (DBHOST, DBUSER, DBPASSWORD, USERSCHEMA, "legal", "physical");
$parameterDAO   = new ParametersTableDAO    (DBHOST, DBUSER, DBPASSWORD, USERSCHEMA, "parameters");

$apikeys = [
    "ec457d0a974c48d5685a7efa03d137dc8bbde7e3" => 1
];

$serviceArray = [
    "watermeter" => new WaterMeterRest($watermeterDAO, $customerDAO),
    "employee" => new EmployeeRest($employeeDAO),
    "customer" => new CustomerRest($customerDAO),
    "parameters" => new ParameterRest($parameterDAO),
    "reading" => new readingRest($readingDAO, $employeeDAO, $customerDAO, $watermeterDAO),
    "time" => new TimeRest(),
    "auth" => new AuthRest($employeeDAO,$apikeys)
];


$rest = new Rest($serviceArray);
$rest->execute_request();
?>