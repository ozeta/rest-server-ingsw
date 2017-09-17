<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 23/05/2017
 * Time: 18:56
 */

namespace ingsw10;


class WaterMeterRest implements RestInterface
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


    public function parseRequest($request)
    {
        $response = null;
        $tokens = $request->getUriTokens();
        $id = $tokens[1];
        if ($tokens[0] == $this->name) {
            if ($tokens[1] == "legal" && is_numeric($tokens[2]) && $request->getRequestMethod() == 'GET') {
                $res = $this->watermeterDao->getAllLegal($this->customerDao, $tokens[2]);
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
                    $res = $this->watermeterDao->get($this->customerDao, $id);
                } elseif ($request->getRequestMethod() == 'DELETE') {
                    $res = $this->watermeterDao->delete($id);
                } elseif ($request->getRequestMethod() == 'PUT') {
                    $resArray = json_decode($request->getAttachedJson(), true);
                    $res = $this->watermeterDao->update($resArray, $id);
                }
            } elseif ($request->getRequestMethod() == 'POST') {
                if ($request->getAttachedJson() != null) {
                    $resArray = json_decode($request->getAttachedJson(), true);
                    $res = $this->watermeterDao->create($resArray);
                }
            } elseif ($request->getRequestMethod() == 'GET' && isset($tokens[1])) {
                //SEARCH
                $res = $this->watermeterDao->search($this->customerDao, $tokens[1]);
            }
        }
        if ($res == null) {
            $response = new Response(404);
        } else {
            if (is_numeric($res)) {
                $response = new Response(200, $res);
            } else {
                $response = new Response(200, json_encode($res, JSON_PRETTY_PRINT));

            }
        }
        return $response;
    }

}
