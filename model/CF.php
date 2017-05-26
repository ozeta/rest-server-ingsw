<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 24/05/2017
 * Time: 20:15
 */

namespace ingsw10;


class CF implements \JsonSerializable
{
    private $value;


    /**
     * Address constructor.
     * @param $street
     * @param $streetNumber
     * @param $cap
     * @param $city
     * @param $prov
     */
    public function __construct($data)
    {
        $this->value = $data->cf;
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