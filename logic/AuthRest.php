<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 17/05/2017
 * Time: 23:52
 */

namespace ingsw10;

class AuthRest implements RestInterface
{

    private $apikeyArray;
    private $name = "auth";
    private $dao;


    /**
     * StubRestAPI constructor.
     * @param string $name
     */
    public function __construct($dao, $apikeyArray)
    {
        $this->dao = $dao;
        $this->apikeyArray = $apikeyArray;
    }

    private function verify_api_key($apikey)
    {
        return $this->apikeyArray[$apikey] == 1;
    }
	
    private function decode($string)
    {
        return base64_decode($string);
    }
	
    public function parseRequest($request)
    {
        $res = null;
        $response = null;
        $tokens = $request->getUriTokens();
        $headers = $request->getHeaders();
        $auth = $headers['Authorization'];
        $apikey = $headers['Api-Key'];
        if (!$this->verify_api_key($apikey)) {
            $response = new Response("404", "no suitable api key");
            $response->reply();
            exit(0);
        }

        list ($dump, $auth) = explode(" ", $auth);
        list ($user64, $pass64) = explode(":", $auth);
        $user = $this->decode($user64);
        $pass = $this->decode($pass64);

        $res = $this->dao->login($user, $pass);

        if ($res == null) {
            $response = new Response(404, $res);
        } else {
            $response = new Response(200, json_encode($res, JSON_PRETTY_PRINT));
        }

        return $response;
    }

}
