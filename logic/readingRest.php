<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 23/05/2017
 * Time: 18:56
 */

namespace ingsw10;


class readingRest
{

    private $name = "reading";
    private $readingDao;
    private $employeeDao;
    private $customerDao;
    private $watermeterDao;


    /**
     * readingRest constructor.
     * @param $readingDao
     * @param $employeeDao
     * @param $customerDao
     * @param $watermeterDao
     */
    public function __construct($readingDao, $employeeDao, $customerDao, $watermeterDao)
    {
        $this->readingDao = $readingDao;
        $this->employeeDao = $employeeDao;
        $this->customerDao = $customerDao;
        $this->watermeterDao = $watermeterDao;
    }

    /**
     * StubRestAPI constructor.
     * @param string $name
     */

    private function autoConfigure()
    {
        $res = $this->readingDao->autoConfigure();
        if ($res == null) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_PRETTY_PRINT));
        }
        return $response;
    }

    private function autoConstraint()
    {
        $res = $this->readingDao->addConstraint();
        if ($res == null) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_PRETTY_PRINT));
        }
        return $response;
    }


    private function getAll($id)
    {
        $response = null;
        $res = $this->readingDao->getAllLegal($id);
        if ($res == null) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_PRETTY_PRINT));
        }
        return $response;
    }


    public function parseRequest($request)
    {
        $res = null;
        $response = null;
        $tokens = $request->getUriTokens();
        if ($request->getAttachedJson() != null) {
            $resourceArray = json_decode($request->getAttachedJson(), true);
        }

        $readID = $tokens[1];
        if ($tokens[0] == $this->name) {

            if ($tokens[1] == "create") {
                $res = $this->readingDao->create($resourceArray);
            } else if ($tokens[1] == "constraint") {
                $res = $this->autoConstraint();
            } else if ($tokens[1] == "flush") {
                $res = $this->readingDao->flush();
            } elseif ($tokens[1] == "drop") {
                $res = $this->readingDao->dropTable(DBUSER);
            } elseif ($tokens[1] == "table") {
                $res = $this->readingDao->getMeta();
            } elseif ($tokens[1] == "operator" && is_numeric($tokens[2])) {
                $res = $this->readingDao->getAllByOperator($tokens[2], $this->employeeDao, $this->customerDao, $this->watermeterDao);
            } elseif (is_numeric($readID)) {
                if ($request->getRequestMethod() == 'GET') {
                    $res = $this->readingDao->get($readID, $this->employeeDao, $this->customerDao, $this->watermeterDao);
                } elseif ($request->getRequestMethod() == 'PUT') {
                    if (is_numeric($tokens[2]) && is_numeric($tokens[3])) {
                        $opID = $tokens[2];
                        $value = $tokens[3];
                        $res = $this->readingDao->updateValue($readID, $opID, $value);
                    }
                }
            } elseif ($request->getRequestMethod() == 'POST') {
                $res = $this->readingDao->create($resourceArray);
            }
        }
        if ($res == null || (isset($res) && $res == false)) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_PRETTY_PRINT));
        }
        return $response;
    }


}
