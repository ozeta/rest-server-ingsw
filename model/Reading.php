<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 25/05/2017
 * Time: 22:33
 */

namespace ingsw10;


class Reading implements \JsonSerializable
{
    protected $id;
    protected $value;
    protected $assignment;
    protected $reading;
    protected $operator;
    protected $watermeter;
    protected $customer;

    /**
     * Customer constructor.
     * @param $addr
     */
    public function __construct($res, $customer, $operator, $watermeter)
    {
        $this->id = $res->id;
        $this->value = $res->value;
        $this->assignment = $res->assignment;
        $this->reading = $res->reading;
        $this->customer = $customer;
        $this->operator = $operator;
        $this->watermeter = $watermeter;
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