<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 17/05/2017
 * Time: 23:52
 */

namespace ingsw10;

class CustomerRest implements RestInterface
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


    public function parseRequest($request)
    {
        $response = null;
        $res = null;
        $tokens = $request->getUriTokens();
        $id = $tokens[2];
        if ($request->getAttachedJson() != null) {
            $resourceArray = json_decode($request->getAttachedJson(), true);
        }

        if ($tokens[0] == $this->name) {
            if ($tokens[1] == "legal") {
                if ($request->getRequestMethod() == 'GET' && $tokens[2] == "table") {
                    $res = $this->dao->getMetaLegal();
                } else if ($request->getRequestMethod() == 'GET' && !is_numeric($id)) {
                    $res = $this->dao->searchLegal($tokens[2]);
                } else if ($request->getRequestMethod() == 'GET') {
                    $res = $this->dao->getLegal($id);
                } else if ($request->getRequestMethod() == 'PUT' && is_numeric($id)) {
                    if ($resourceArray == null) {
                        return new Response(404, "expecting json file");
                    }
                    $res = $this->dao->updateLegal($resourceArray, $id);
                    if ($res != null) {

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

                } else if ($request->getRequestMethod() == 'DELETE') {
                    $res = $this->dao->deleteLegal($id);
                } elseif ($request->getRequestMethod() == 'POST' && !isset($tokens[2])) {
                    if ($resourceArray == null) {
                        return new Response(404, "expecting json file");
                    }
                    $res = $this->dao->createLegal($resourceArray);
                    if ($res != null) {

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
            } elseif ($tokens[1] == "physical") {
                if ($request->getRequestMethod() == 'GET' && $tokens[2] == "table") {
                    $res = $this->dao->getMetaPhysical();
                } else if ($request->getRequestMethod() == 'GET' && !is_numeric($id)) {
                    $res = $this->dao->searchPhysical($tokens[2]);
                } else if ($request->getRequestMethod() == 'GET' && is_numeric($id)) {
                    $res = $this->dao->getPhysical($id);
                } else if ($request->getRequestMethod() == 'PUT' && is_numeric($id)) {
                    if ($resourceArray == null) {
                        return new Response(404, "expecting json file");
                    }
                    $res = $this->dao->updatePhysical($resourceArray, $id);
                    if ($res != null) {
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

                } else if ($request->getRequestMethod() == 'DELETE' && is_numeric($id)) {
                    $res = $this->dao->deletePhysical($id);
                } elseif ($request->getRequestMethod() == 'POST' && !isset($tokens[2])) {
                    if ($resourceArray == null) {
                        return new Response(404, "expecting json file");
                    }
                    //var_dump($resourceArray);
                    $res = $this->dao->createPhysical($resourceArray);
                    if ($res != null) {
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
