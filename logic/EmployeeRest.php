<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 17/05/2017
 * Time: 23:52
 */

namespace ingsw10;

class EmployeeRest implements RestInterface
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

    public function parseRequest($request)
    {
        $res = null;
        $response = null;
        $tokens = $request->getUriTokens();
        $id = $tokens[1];
        $resourceArray = null;
        if ($request->getAttachedJson() != null) {
            $resourceArray = json_decode($request->getAttachedJson(), true);
        }
        if ($tokens[0] == $this->name) {

            if ($tokens[1] == "configure") {
                $res = $this->dao->autoConfigure();
            } else if ($tokens[1] == "table") {
                $res = $this->dao->getMeta();
            } elseif ($tokens[1] == "drop") {
                $res = $this->dao->dropTable(DBUSER);
            } elseif ($tokens[1] == "username" && isset($tokens[2])) {
                $res = $this->dao->getByUsername($tokens[2]);
            } elseif ($tokens[1] == "login" && isset($tokens[2])) {
                $res = $this->dao->getLogin($tokens[2]);
            } elseif (is_numeric($id)) {
                if ($request->getRequestMethod() == 'GET') {
                    $res = $this->dao->get($id);
                } elseif ($request->getRequestMethod() == 'DELETE') {
                    $res = $this->dao->delete($id);
                } elseif ($request->getRequestMethod() == 'PUT') {
                    if ($resourceArray == null) {
                        return new Response(404, "expecting json file");
                    }
                    $res = $this->dao->update($resourceArray, $id);
                    if ($res === QCFCODE) {
                        return new Response(CFCODE, "CF in conflict");
                    } else if ($res === QUSERCODE) {
                        return new Response(USERCODE, "Username in conflict");
                    } else if ($res === QPHONECODE) {
                        return new Response(PHONECODE, "Phone in conflict");
                    } else if ($res === QEMAILCODE) {
                        return new Response(EMAILCODE, "Email in conflict");
                    }
                }
            } elseif ($request->getRequestMethod() == 'GET' && isset($tokens[1])) {
                //SEARCH
                $res = $this->dao->search($tokens[1]);
            } elseif ($request->getRequestMethod() == 'POST') {
                if ($resourceArray == null) {
                    return new Response(404, "I'm expecting a Json array");
                }
                $res = $this->dao->create($resourceArray);
                if ($res === QCFCODE) {
                    return new Response(CFCODE, "CF in conflict");
                } else if ($res === QUSERCODE) {
                    return new Response(USERCODE, "Username in conflict");
                } else if ($res === QPHONECODE) {
                    return new Response(PHONECODE, "Phone in conflict");
                } else if ($res === QEMAILCODE) {
                    return new Response(EMAILCODE, "Email in conflict");
                }
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
