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

    private function autoConstraint()
    {
        $res = $this->dao->addConstraint();
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

    private function getAllLegal($id)
    {
        $response = null;
        $res = $this->dao->getAllLegal($id);
        if ($res == false) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_NUMERIC_CHECK));
        }
        return $response;
    }

    private
    function getAllPhysical($id)
    {
        $response = null;
        $res = $this->dao->getAllPhysical($id);
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
            if ($tokens[1] == "operator" && is_numeric($tokens[2]) && $request->getRequestMethod() == 'GET') {
                return $this->getAllLegal($tokens[2]);
            } elseif ($tokens[1] == "physical" && is_numeric($tokens[2]) && $request->getRequestMethod() == 'GET') {
                return $this->getAllPhysical($tokens[2]);
            } else if ($tokens[1] == "create") {
                return $this->autoConfigure();
            } else if ($tokens[1] == "constraint") {
                return $this->autoConstraint();
            } elseif ($tokens[1] == "drop") {
                return $this->drop();
            } elseif ($tokens[1] == "max") {
                return $this->maxID();
            } elseif ($tokens[1] == "table") {
                return $this->getMeta();
            } elseif (is_numeric($id)) {
                if ($request->getRequestMethod() == 'GET') {
                    return $this->get($id);
                } elseif ($request->getRequestMethod() == 'DELETE') {
                    return $this->delete($id);
                } elseif ($request->getRequestMethod() == 'PUT') {
                    return $this->update($request, $id);
                }
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
