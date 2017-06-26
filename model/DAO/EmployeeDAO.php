<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 16/05/2017
 * Time: 13:22
 */

namespace ingsw10;


use PDO;
use PDOException;

require_once "EPDOStatement.php";
include_once "QueryRunner.php";

class EmployeeDAO
{

    private $insertStmt;
    private $updateStmt;
    private $deleteStmt;
    private $selectStmt;
    private $selectMaxId;


    private $querySql;
    private $tableName;

    private $configStmt;
    private $dropTableStmt;

    private $dbUsername;
    private $PDO;


    public function __construct($dbHost, $username, $password, $schema, $tableName)
    {
        $this->dbUsername = $username;
        $tableName = "$schema.$tableName";
        $this->tableName = $tableName;

        try {
            #stringa caratteristica mysql
            $this->PDO = new PDO("mysql:host=$dbHost", $username, $password);
            #imposta quanti errori mostrare in caso di eccezione
             $this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //$this->PDO->setAttribute(PDO::ATTR_STATEMENT_CLASS, array("EPDOStatement\EPDOStatement", array($this->PDO)));

        } catch (PDOException $e) {
            error_log($e->getMessage() . "\n\n", 3, "./server-errors.log");
        }

        $this->dropTableStmt = /** @lang mysql */
            "DROP TABLE IF EXISTS $tableName";
        $this->configStmt = /** @lang mysql */

            "CREATE SCHEMA IF NOT EXISTS $schema ;
            create table IF NOT EXISTS $tableName (
            id            int(11) NOT NULL AUTO_INCREMENT, 
            role          char(1) NOT NULL, 
            username      varchar(255) NOT NULL UNIQUE, 
            password      varchar(255) NOT NULL, 
            firstname    varchar(255) NOT NULL, 
            lastname     varchar(255) NOT NULL, 
            birthdate     date NOT NULL, 
            hiredate      date NOT NULL, 
            email         varchar(255) NOT NULL, 
            cf            varchar(255) NOT NULL UNIQUE, 
            city          varchar(255) NOT NULL, 
            prov          varchar(255) NOT NULL, 
            street        varchar(255) NOT NULL, 
            street_number  varchar(255) NOT NULL, 
            phone         varchar(255) NOT NULL, 
            cap           varchar(255) NOT NULL, 
            PRIMARY KEY (id));
            ";

        $this->insertStmt = /** @lang mysql */
            "INSERT INTO $tableName
                    ( role,  username,  password,  firstname,  lastname,  birthdate,  hiredate,  email,  cf,  city,  prov,  street,  street_number,  phone,  cap) 
            VALUES ( :role, :username, :password, :firstname, :lastname, :birthdate, :hiredate, :email, :cf, :city, :prov, :street, :street_number, :phone, :cap)
            ;";
        $this->updateStmt = /** @lang mysql */
            "UPDATE $tableName SET
            firstname = :firstname, 
            lastname = :lastname, 
            username = :username,
            password = :password,
            role = :role, 
            birthdate = :birthdate, 
            hiredate = :hiredate, 
            email = :email, 
            cf = :cf, 
            city = :city, 
            prov = :prov, 
            street = :street, 
            street_number = :street_number, 
            phone = :phone, 
            cap = :cap 
            WHERE
            $tableName.id = :id;
            ";

        $this->deleteStmt = /** @lang mysql */
            "DELETE FROM $tableName 
            WHERE id = :id;
            ";

        $this->selectMaxId = /** @lang mysql */
            //"SELECT id, email, cf, city, prov, street, street_number, phone, piva, cap
            "SELECT max(id)
            FROM $tableName
            ;
            ";

        $this->selectStmt = /** @lang mysql */
            //"SELECT id, email, cf, city, prov, street, street_number, phone, piva, cap
            "SELECT *
            FROM $tableName 
             WHERE id = :id;
            ";

        $this->selectUsernameStmt = /** @lang mysql */
            //"SELECT id, email, cf, city, prov, street, street_number, phone, piva, cap
            "SELECT id, username, password
            FROM $tableName 
            WHERE username = :username;
            ";
        $this->querySql = "SELECT * FROM $tableName ";

    }

    public function create($resourceArray)
    {
        $res = $this->PDO->prepare($this->insertStmt);
        $res = $this->bindParameters($resourceArray, $res);

        if (QueryRunner::execute($res)) {
            return $this->PDO->lastInsertId('ID');
        }
        return null;
    }


    private function bindParameters($resourceArray, $res)
    {
        $res->bindParam(':firstname', $resourceArray["firstName"]);
        $res->bindParam(':lastname', $resourceArray["lastName"]);
        $res->bindParam(':role', $resourceArray["role"]);
        $res->bindParam(':cf', $resourceArray["cf"]["value"]);
        $res->bindParam(':username', $resourceArray["cred"]["username"]);
        $res->bindParam(':password', $resourceArray["cred"]["password"]);
        $res->bindParam(':city', $resourceArray["addr"]["city"]);
        $res->bindParam(':prov', $resourceArray["addr"]["prov"]);
        $res->bindParam(':street', $resourceArray["addr"]["street"]);
        $res->bindParam(':street_number', $resourceArray["addr"]["streetNumber"]);
        $res->bindParam(':cap', $resourceArray["addr"]["cap"]);
        $res->bindParam(':email', $resourceArray["email"]);
        $res->bindParam(':phone', $resourceArray["phone"]);
        $res->bindParam(':birthdate', $resourceArray["birthDate"]);
        $res->bindParam(':hiredate', $resourceArray["hireDate"]);
        return $res;
    }

    public function update($resourceArray, $id)
    {
        if (!$this->get($id)) return null;
        //var_dump($resourceArray["ID"]);
        $res = $this->PDO->prepare($this->updateStmt);
        $res = $this->bindParameters($resourceArray, $res);

        $res->bindValue(':id', $id);

        $result = QueryRunner::execute($res);

        return $result;
    }


    /**
     * @param $ID integer ID
     * @return customer or false if not exists.
     */

    public function get($ID)
    {
        $res = $this->PDO->prepare($this->selectStmt);
        //echo var_dump($res);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $result = $res->fetch(PDO::FETCH_OBJ);
        }
        if ($result) return new Employee($result);
        else return null;
    }

    public function getByUsername($username)
    {
        $res = $this->PDO->prepare($this->selectUsernameStmt);
        $res->bindParam(':username', $username, PDO::PARAM_INT);
       // $res->interpolateQuery();
        if (QueryRunner::execute($res)) {
            $result = $res->fetch(PDO::FETCH_OBJ);
        }
        return $result;
    }

    public function getMaxID()
    {
        $res = $this->PDO->prepare($this->selectMaxId);
        if (QueryRunner::execute($res)) {
            $result = $res->fetch(PDO::FETCH_OBJ);
        }
        //echo var_dump($result);
        if ($result) return new Employee($result);
        else return null;
    }

    /**
     * @return boolean. true if success
     */
    function autoConfigure()
    {
        $res = $this->PDO->prepare($this->configStmt);
        return QueryRunner::execute($res);
    }


    /**
     * @param $DBuser dbUsername: used to doublecheck the intention of drop the table
     * @return boolean. true if success
     */
    function dropTable($DBuser)
    {
        if ($DBuser != $this->dbUsername) {
            return null;
        }
        $res = $this->PDO->prepare($this->dropTableStmt);
        return QueryRunner::execute($res);
    }


    public function search($queryUrl)
    {
        $queryArr = null;
        $whereStmt = "";
        $row = null;
        parse_str($queryUrl, $queryArr);
        foreach ($queryArr as $key => $value) {
            $whereStmt .= "$key = :$key ";
            if ($value != end($queryArr)) $whereStmt .= "and ";

        }
        $newQuery = "$this->querySql WHERE $whereStmt ORDER BY id";

        $res = $this->PDO->prepare($newQuery);
        foreach ($queryArr as $key => $value) {
            $res->bindValue($key, $value);
        }
        if (QueryRunner::execute($res)) {
            $result = null;
            $i = 0;
            $row = $res->fetchAll(PDO::FETCH_OBJ);
            foreach ($row as $key => $value) {
                $result[$i++] = new Employee($value);
            }
        }
        return $result;
    }

    public function delete($ID)
    {
        if (!$this->get($ID)) return null;
        $res = $this->PDO->prepare($this->deleteStmt);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            if ($res->rowCount() == 1) return true;
        }
        return null;
    }

    public function getMeta()
    {
        $result = null;
        $getMetaStmt = "SHOW COLUMNS FROM $this->tableName";
        $res = $this->PDO->prepare($getMetaStmt);
        if (QueryRunner::execute($res)) {
            $result = $res->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        return $result;
    }
}