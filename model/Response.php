<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 15/05/2017
 * Time: 23:13
 */

namespace ingsw10;


class Response
{

    /**
     * Builds and echoes a response
     * @param $http_response_code standard http response code values
     * @param $body
     * @param $content_type possible values: "json", "html"
     */
    private $http_response_code;
    private $body ="";
    private $content_type;

    public function __construct($http_response_code, $body = null, $content_type = "html")
    {
        $this->http_response_code = $http_response_code;
        $this->body = $body;
        $this->content_type = $content_type;
    }

    public function reply()
    {
        $json_type = "Content-Type: application/json";
        $html_type = "Content-Type:text/html";

        if (!is_numeric($this->http_response_code)) throw new \RuntimeException("(!) Warning! Response code must be a number");
        if ($this->http_response_code >= 400 && isset($body)) throw new \RuntimeException("(!) Warning! body must be empty for response code " . $this->http_response_code . ".");
        if ($this->content_type == "json") {
            header($json_type);
        } else if ($this->content_type == "html") {
            header($html_type);
        } else {
            throw new \RuntimeException("(!) Warning! content_type : " . $json_type . " | " . $html_type);
        }
        http_response_code($this->http_response_code);
        echo $this->body;
    }


}