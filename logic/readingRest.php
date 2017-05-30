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

        $id = $tokens[1];
        if ($tokens[0] == $this->name) {

                if ($tokens[1] == "create") {
                    $res = $this->readingDao->create($resourceArray);
                } else if ($tokens[1] == "constraint") {
                    return $this->autoConstraint();
                } elseif ($tokens[1] == "drop") {
                    $res = $this->readingDao->dropTable(DBUSER);
                } elseif ($tokens[1] == "table") {
                    $res = $this->readingDao->getMeta();
                }elseif ($tokens[1] == "operator" && is_numeric($tokens[2])) {
                    $res = $this->readingDao->getAllByOperator($tokens[2], $this->employeeDao, $this->customerDao, $this->watermeterDao);
                }
             elseif (is_numeric($id)) {
                if ($request->getRequestMethod() == 'GET') {
                    $res = $this->readingDao->get($id, $this->employeeDao, $this->customerDao, $this->watermeterDao);
                } elseif ($request->getRequestMethod() == 'PUT') {
                    $res = $this->readingDao->update($resourceArray, $id);

                }
            } elseif ($request->getRequestMethod() == 'POST') {
                $res = $this->readingDao->create($resourceArray);
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
