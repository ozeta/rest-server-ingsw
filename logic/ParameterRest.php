<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 18/05/2017
 * Time: 19:07
 */

namespace ingsw10;


class ParameterRest
{

    private $name = "parameters";
    private $dao;
    private $functionArray;

    /**
     * StubRestAPI constructor.
     * @param string $name
     */
    public function __construct($dao)
    {
        $this->dao = $dao;
    }


    public function parseRequest($request)
    {
        $response = null;
        $tokens = $request->getUriTokens();
        if ($tokens[0] == $this->name && !isset($tokens[1])) {
            if ($request->getRequestMethod() == 'GET') {
                // var_dump( $this->dao->insertDummy());
                $res = $this->dao->getAll();
                if ($res == false) {
                    $response = new Response(404);
                } else {
                    $response = new Response(200, json_encode($this->dao->getAll(), JSON_NUMERIC_CHECK));
                }

                //echo(json_encode($this->dao->getAll(), JSON_NUMERIC_CHECK));
                //
                // var_dump($this->dao->dropTable(DBUSER));
            } elseif ($request->getRequestMethod() == 'PUT') {
                $arr = json_decode($request->getAttachedJson(), true);
                $daily_interest = $arr['dailyInterest'];
                $shipping_cost = $arr['shippingCost'];
                $water_cost = $arr['waterCost'];
                $tax_rate = $arr['taxRate'];
                $this->dao->setAll($daily_interest, $shipping_cost, $water_cost, $tax_rate);
                $response = new Response(200);
            }
        } elseif ($tokens[1] == "autoconfigure") {
            if ($this->dao->autoConfigure() == true) {
                $this->dao->insertDummy();
                return new Response(200);
            }
            $response = new Response(400);
        } elseif ($tokens[1] == "drop") {
            if ($this->dao->dropTable(DBUSER) == true) {
                return new Response(200);
            }
            $response = new Response(400);
        } elseif ($tokens[1] == "table"){
            return $this->getMeta();
        }
        return $response;
    }
    private function getMeta()
    {
        $response = null;
        $res = $this->dao->getMeta();
        if ($res == false) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_NUMERIC_CHECK));
        }
        return $response;
    }
}