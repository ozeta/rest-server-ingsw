<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 24/05/2017
 * Time: 22:08
 */

namespace ingsw10;

class Credential implements \JsonSerializable
{
    private $username;

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }
    public function getPassword()
    {
        return $this->password;
    }
    private $password;

    public function __construct($owner)
    {
        $this->username = $owner->username;
        $this->password = $owner->password;
    }
    function jsonSerialize()
    {
        return ['username' => $this->username, 'password' => null];

    }
}

class Employee implements \JsonSerializable
{
    private $ID;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->ID;
    }
    public function getUsername()
    {
        return $this->cred->getUsername();
    }
    public function getPassword()
    {
        return $this->cred->getPassword();
    }

    private $role;

    private $cred;
    private $addr;
    private $birthDate;
    private $hireDate;
    private $firstName;
    private $lastName;
    private $email;
    private $phone;
    private $cf;

    /**
     * Legal constructor.
     * @param $addr
     */
    public function __construct($owner)
    {
        $this->ID = $owner->id;
        $this->role = $owner->role;
        $this->cred = new Credential($owner);
        $this->addr = new Address($owner);
        $this->firstName = $owner->firstname;
        $this->lastName = $owner->lastname;
        $this->birthDate = $owner->birthdate;
        $this->hireDate = $owner->hiredate;
        $this->cf = new CF($owner);
        $this->email = $owner->email;
        $this->phone = $owner->phone;
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