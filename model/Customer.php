<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 25/05/2017
 * Time: 09:55
 */

namespace ingsw10;


class Customer implements \JsonSerializable
{
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Customer
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return CF
     */
    public function getCf()
    {
        return $this->cf;
    }

    /**
     * @param CF $cf
     * @return Customer
     */
    public function setCf($cf)
    {
        $this->cf = $cf;
        return $this;
    }
    protected $addr;
    protected $email;
    protected $phone;
    protected $watermeterOwnedList;
    protected $cf;

    /**
     * Customer constructor.
     * @param $addr
     */
    public function __construct($owner)
    {
        $this->id = $owner->id;
        $this->addr = new Address($owner);
        $this->email = $owner->email;
        $this->phone = $owner->phone;
        $this->watermeterOwnedList = null;
        $this->cf = new CF($owner);
        // echo "OWNER: ".var_dump($owner);
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