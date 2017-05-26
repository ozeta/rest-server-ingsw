<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 24/05/2017
 * Time: 22:08
 */

namespace ingsw10;

include_once "Customer.php";
class Legal extends Customer implements \JsonSerializable
{
    protected $legalName;
    protected $PIVA;

    public function __construct($owner)
    {
        parent::__construct($owner);
        $this->legalName = $owner->legal_name;
        $this->PIVA = $owner->piva;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return get_object_vars($this);
    }
}