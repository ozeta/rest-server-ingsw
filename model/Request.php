<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 16/05/2017
 * Time: 22:16
 */

namespace ingsw10;


class Request
{

    private $requestMethod;
    private $attachedJson;
    private $uriTokens;
    /**
     * Request constructor.
     * @param mixed $REQUEST_METHOD
     * @param bool|string $file_get_contents
     * @param array $uriTokens
     */
    public function __construct($requestMethod, $attachedJson, $uriTokens)
    {
        $this->uriTokens = $uriTokens;
        $this->attachedJson = $attachedJson;
        $this->requestMethod = $requestMethod;
    }

    /**
     * @return mixed
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * @param mixed $requestMethod
     */
    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }

    /**
     * @return mixed
     */
    public function getAttachedJson()
    {
        return $this->attachedJson;
    }

    /**
     * @param mixed $attachedJson
     */
    public function setAttachedJson($attachedJson)
    {
        $this->attachedJson = $attachedJson;
    }

    /**
     * @return array
     */
    public function getUriTokens()
    {
        return $this->uriTokens;
    }

    /**
     * @param array $uriTokens
     */
    public function setUriTokens(array $uriTokens)
    {
        $this->uriTokens = $uriTokens;
    }
}