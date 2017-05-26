<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 23/05/2017
 * Time: 18:56
 */

namespace ingsw10;


class WaterMeterRest
{

    private $name = "watermeter";
    private $watermeterDao;
    private $customerDao;
    private $functionArray;

    /**
     * StubRestAPI constructor.
     * @param string $name
     */
    public function __construct($dao, $customerDao)
    {
        $this->watermeterDao = $dao;
        $this->customerDao = $customerDao;

    }

    private function autoConfigure()
    {
        $res = $this->watermeterDao->autoConfigure();
        if ($res == null) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_PRETTY_PRINT));
        }
        return $response;
    }

    private function autoConstraint()
    {
        $res = $this->watermeterDao->addConstraint();
        if ($res == null) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_PRETTY_PRINT));
        }
        return $response;
    }

    private function create($request)
    {
        $response = null;
        $customerArray = json_decode($request->getAttachedJson(), true);
        $res = $this->watermeterDao->create($customerArray);
        if ($res != null) {
            $response = new Response(201, $res);
        } else {
            $response = new Response(400);
        }
        return $response;

    }

    private function update($request, $id)
    {
        $response = null;
        $customerArray = json_decode($request->getAttachedJson(), true);
        $res = $this->watermeterDao->update($customerArray, $id);
        if ($res != null) {
            $response = new Response(201);
        } else {
            $response = new Response(400);
        }
        return $response;

    }


    public function parseRequest($request, $res)
    {
        $response = null;
        $tokens = $request->getUriTokens();
        $id = $tokens[1];
        if ($tokens[0] == $this->name) {
            if ($tokens[1] == "legal"       && is_numeric($tokens[2]) && $request->getRequestMethod() == 'GET') {
                $res = $this->watermeterDao->getAllLegal($this->customerDao,$tokens[2]);
            } elseif ($tokens[1] == "physical" && is_numeric($tokens[2]) && $request->getRequestMethod() == 'GET') {
                $res = $this->watermeterDao->getAllPhysical($this->customerDao, $tokens[2]);
            } else if ($tokens[1] == "create") {
                return $this->autoConfigure();
            } else if ($tokens[1] == "constraint") {
                return $this->autoConstraint();
            } elseif ($tokens[1] == "drop") {
                $res = $this->watermeterDao->dropTable(DBUSER);
            } elseif ($tokens[1] == "json") {
                return $this->watermeterDao->jsonTest($this->customerDao);
            } elseif ($tokens[1] == "table") {
                $res = $this->watermeterDao->getMeta();
            } elseif (is_numeric($id)) {
                if ($request->getRequestMethod() == 'GET') {
                    $res = $this->watermeterDao->get($this->customerDao,$id);

                } elseif ($request->getRequestMethod() == 'DELETE') {
                    $res = $this->watermeterDao->delete($id);
                } elseif ($request->getRequestMethod() == 'PUT') {
                    return $this->update($request, $id);
                }
            } elseif ($request->getRequestMethod() == 'POST') {
                return $this->create($request);
            }
        }
        if ($res == null) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_PRETTY_PRINT));
        }
        return $response;
    }

}
