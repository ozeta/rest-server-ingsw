<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 17/05/2017
 * Time: 23:52
 */

namespace ingsw10;

class CustomerRest
{

    private $name = "customer";
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

    private function autoConfigureLegal()
    {
        return $this->dao->autoConfigureLegalCustomer();
    }

    private function autoConfigurePhysical()
    {
        return $this->dao->autoConfigurePhysicalCustomer();
    }

    private function dropTableLegalCustomer($DBuser)
    {
        return $this->dao->dropTableLegalCustomer($DBuser);
    }

    private function dropTablePhysicalCustomer($DBuser)
    {
        return $this->dao->dropTablePhysicalCustomer($DBuser);
    }

    private function getLegal($id)
    {
        $response = null;
        $res = $this->dao->getLegal($id);
        if ($res == false) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_NUMERIC_CHECK));
        }
        return $response;
    }

    private function getPhysical($id)
    {
        $response = null;

        $res = $this->dao->getPhysical($id);
        if ($res == false) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_NUMERIC_CHECK));
        }
        return $response;
    }

    private function getMaxId($id)
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

    private function deleteLegal($id)
    {
        $response = null;

        $res = $this->dao->deleteLegal($id);
        if ($res != null) {
            $response = new Response(201, $res);
        } else {
            $response = new Response(400);
        }
        return $response;
    }

    private function updateLegal($request, $id)
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

    private function searchLegal($id)
    {
        $response = null;
        $res = $this->dao->searchLegal($id);
        if ($res == false) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_NUMERIC_CHECK));
        }
        return $response;

    }

    private function createCustomer($request)
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

    public function parseRequest($request)
    {
        $response = null;
        $tokens = $request->getUriTokens();
        $id = $tokens[2];


        if ($tokens[0] == $this->name) {
            if ($tokens[1] == "legal") {
                if ($request->getRequestMethod() == 'GET' && $tokens[2] == "table") {
                    return $this->getMetaLegal();
                } else if ($request->getRequestMethod() == 'GET' && !is_numeric($id)) {
                    return $this->searchLegal($tokens[2]);
                } else if ($request->getRequestMethod() == 'GET') {
                    return $this->getLegal($id);
                } else if ($request->getRequestMethod() == 'PUT') {
                    return $this->updateLegal($request, $id);
                } else if ($request->getRequestMethod() == 'DELETE') {
                    return $this->deleteLegal($id);
                } elseif ($request->getRequestMethod() == 'POST') {
                    return $this->createCustomer($request);
                }
            } elseif ($tokens[1] == "physical") {
                if ($request->getRequestMethod() == 'GET' && $tokens[2] == "table") {
                    return $this->getMetaPhysical();
                } else if ($request->getRequestMethod() == 'GET' && !is_numeric($id)) {
                    return $this->searchPhysical($tokens[2]);
                } else if ($request->getRequestMethod() == 'GET') {
                    return $this->getPhysical($id);
                } else if ($request->getRequestMethod() == 'PUT') {
                    return $this->updatePhysical($request, $id);

                } else if ($request->getRequestMethod() == 'DELETE') {
                    return $this->deletePhysical($id);
                } elseif ($request->getRequestMethod() == 'POST') {
                    return $this->createCustomer($request);
                }
            } elseif ($tokens[1] == "max") {
                return $this->getMaxId($id);
            }
        }
        return $response;
    }

    private function getMetaLegal()
    {
        $response = null;
        $res = $this->dao->getMetaLegal();
        if ($res == false) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_NUMERIC_CHECK));
        }
        return $response;
    }

    private function getMetaPhysical()
    {
        $response = null;
        $res = $this->dao->getMetaPhysical();
        if ($res == false) {
            $response = new Response(404);
        } else {
            $response = new Response(200, json_encode($res, JSON_NUMERIC_CHECK));
        }
        return $response;
    }

}
