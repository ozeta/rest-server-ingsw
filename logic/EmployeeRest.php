<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 17/05/2017
 * Time: 23:52
 */

namespace ingsw10;

class EmployeeRest
{

    private $name = "employee";
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

    private function autoConfigure()
    {
        $res = $this->dao->autoConfigure();
        if ($res == false) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_NUMERIC_CHECK));
        }
        return $response;
    }

    private function dropTable($DBuser)
    {
        $response = null;
        $res = $this->dao->dropTableLegalCustomer($DBuser);
        if ($res == false) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_NUMERIC_CHECK));
        }
        return $response;

    }

    private function search($queryUrl)
    {
        $response = null;
        $res = $this->dao->search($queryUrl);
        if ($res == false) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_NUMERIC_CHECK));
        }
        return $response;

    }

    private function create($request)
    {
        $response = null;
        $customerArray = json_decode($request->getAttachedJson(), true);
        $res = $this->dao->create($customerArray);
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
        $res = $this->dao->update($customerArray, $id);
        if ($res != null) {
            $response = new Response(201);
        } else {
            $response = new Response(400);
        }
        return $response;

    }

    private function delete($id)
    {
        $response = null;

        $res = $this->dao->delete($id);
        if ($res != null) {
            $response = new Response(201, $res);
        } else {
            $response = new Response(400);
        }
        return $response;

    }

    private function get($id)
    {
        $response = null;
        $res = $this->dao->get($id);
        if ($res == false) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_NUMERIC_CHECK));
        }
        return $response;


    }

    private function maxID()
    {
        $response = null;
        $res = $this->dao->getMaxID();
        if ($res == false) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_NUMERIC_CHECK));
        }
        return $response;

    }

    private function drop()
    {
        $response = null;

        $res = $this->dao->dropTable(DBUSER);
        if ($res == false) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_NUMERIC_CHECK));
        }
        return $response;

    }

    public function parseRequest($request)
    {
        $response = null;
        $tokens = $request->getUriTokens();
        $id = $tokens[1];
        if ($tokens[0] == $this->name) {

            if ($tokens[1] == "configure") {
                return $this->autoConfigure();
            } else if ($tokens[1] == "table") {
                return $this->getMeta();
            } elseif ($tokens[1] == "drop") {
                return $this->drop();
            } elseif ($tokens[1] == "max") {
                return $this->maxID();
            } elseif (is_numeric($id)) {
                if ($request->getRequestMethod() == 'GET') {
                    return $this->get($id);
                } elseif ($request->getRequestMethod() == 'DELETE') {
                    return $this->delete($id);
                } elseif ($request->getRequestMethod() == 'PUT') {
                    return $this->update($request, $id);
                }
            } elseif ($request->getRequestMethod() == 'GET' and isset($tokens[1])) {
                //SEARCH
                return $this->search($tokens[1]);
            } elseif ($request->getRequestMethod() == 'POST') {
                return $this->create($request);

            }
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
