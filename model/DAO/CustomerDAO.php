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

include_once "QueryRunner.php";

class CustomerDAO
{

    private $getAllStmt;
    private $setAllStmt;

    private $insertLegalStmt;
    private $insertPhysicalStmt;

    private $updateLegalStmt;
    private $updatePhysicalStmt;

    private $deleteLegalStmt;
    private $deletePhysicalStmt;

    private $selectLegalStmt;
    private $selectPhysicalStmt;

    private $selectLegalMaxId;


    private $queryLegalSql;
    private $queryPhysicalSql;

    private $legalTableN;
    private $physicalTableN;

    private $configLegalStmt;
    private $configPhysicalStmt;

    private $dropLegalTableStmt;
    private $dropPhysicalTableStmt;

    private $dbUsername;
    private $PDO;


    public function __construct($dbHost, $username, $password, $schema, $legalTableName, $physicalTableName)
    {
        $this->dbUsername = $username;
        $legalTableName = "$schema.$legalTableName";
        $physicalTableName = "$schema.$physicalTableName";
        $this->legalTableN = $legalTableName;
        $this->physicalTableN = $physicalTableName;

        try {
            #stringa caratteristica mysql
            $this->PDO = new PDO("mysql:host=$dbHost", $username, $password);
            #imposta quanti errori mostrare in caso di eccezione
            $this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log($e->getMessage() . "\n\n", 3, "./server-errors.log");
        }

        $this->dropLegalTableStmt = /** @lang mysql */
            "DROP TABLE IF EXISTS $legalTableName";
        $this->dropPhysicalTableStmt = /** @lang mysql */
            "DROP TABLE IF EXISTS $physicalTableName";
        $this->configLegalStmt = /** @lang mysql */

            "CREATE SCHEMA IF NOT EXISTS $schema ;
            create table IF NOT EXISTS $legalTableName (
            id            int(11) NOT NULL AUTO_INCREMENT, 
            legal_name    varchar(255) NOT NULL, 
            email         varchar(255) NOT NULL, 
            cf            varchar(255) NOT NULL UNIQUE, 
            city          varchar(255) NOT NULL, 
            prov          varchar(255) NOT NULL, 
            street        varchar(255) NOT NULL, 
            street_number varchar(255) NOT NULL, 
            phone         varchar(255) NOT NULL, 
            piva          int(11) NOT NULL UNIQUE,  
            cap           varchar(255) NOT NULL, 
            PRIMARY KEY (id));
            ";
        $this->configPhysicalStmt = /** @lang mysql */

            "CREATE SCHEMA IF NOT EXISTS $schema ;
            create table IF NOT EXISTS $physicalTableName (
            id            int(11) NOT NULL AUTO_INCREMENT, 
            first_name    varchar(255) NOT NULL, 
            last_name     varchar(255) NOT NULL, 
            birthday      date NOT NULL, 
            email         varchar(255) NOT NULL, 
            cf            varchar(255) NOT NULL UNIQUE, 
            city          varchar(255) NOT NULL, 
            prov          varchar(255) NOT NULL, 
            street        varchar(255) NOT NULL, 
            street_number varchar(255) NOT NULL, 
            cap           varchar(255) NOT NULL, 
            phone         varchar(255) NOT NULL, 
            PRIMARY KEY (id));
            
            ";
        $this->insertLegalStmt = /** @lang mysql */
            "INSERT INTO $legalTableName
                     (legal_name, email, cf, city, prov, street, street_number, phone, piva, cap) 
            VALUES (:legal_name, :email, :cf, :city, :prov, :street, :street_number, :phone, :piva, :cap);
            ";

        $this->insertPhysicalStmt = /** @lang mysql */
            "INSERT INTO $physicalTableName
                    (first_name,  last_name,  email,  phone,  birthday,  cf,  city,  prov,  street,  street_number,  cap) 
            VALUES (:first_name, :last_name, :email, :phone, :birthday, :cf, :city, :prov, :street, :street_number, :cap);
            ";
        $this->updateLegalStmt = /** @lang mysql */
            "UPDATE $legalTableName SET 
            legal_name = :legal_name, 
            email = :email, 
            cf = :cf, 
            city = :city, 
            prov = :prov, 
            street = :street, 
            street_number = :street_number, 
            phone = :phone, 
            cap = :cap,
            piva = :piva
            WHERE
            $legalTableName.id = :id;
            ";

        $this->updatePhysicalStmt = /** @lang mysql */
            "UPDATE $physicalTableName SET 
            first_name = :first_name, 
            last_name = :last_name, 
            email = :email, 
            birthday = :birthday, 
            cf = :cf, 
            city = :city, 
            prov = :prov, 
            street = :street, 
            street_number = :street_number, 
            phone = :phone, 
            cap = :cap
            WHERE
            $physicalTableName.id = :id;
            ";


        $this->deleteLegalStmt = /** @lang mysql */
            "DELETE FROM $legalTableName 
            WHERE id = :id;
            ";
        $this->deletePhysicalStmt = /** @lang mysql */
            "DELETE FROM $physicalTableName 
            WHERE id = :id;
            ";

        $this->selectLegalMaxId = /** @lang mysql */
            //"SELECT id, email, cf, city, prov, street, street_number, phone, piva, cap
            "SELECT max(id)
            FROM $legalTableName
            ;
            ";
        $this->selectLegalStmt = /** @lang mysql */
            //"SELECT id, email, cf, city, prov, street, street_number, phone, piva, cap
            "SELECT *
            FROM $legalTableName
             WHERE id = :id;
            ";
        $this->selectPhysicalStmt = /** @lang mysql */
            //"SELECT id, email, cf, city, prov, street, street_number, phone, piva, cap
            "SELECT *
            FROM $physicalTableName 
            WHERE id = :id;
            ";

        $this->queryLegalSql = "SELECT * FROM $legalTableName ";
        $this->queryPhysicalSql = "SELECT * FROM $physicalTableName";

    }

    /**
     * @param $resourceArray legal customer parsed in Associative array
     * @return int succeeded, returns last customer id. if fails, returns null
     */
    private function createLegal($resourceArray)
    {
        $res = $this->PDO->prepare($this->insertLegalStmt);
        $res->bindParam(':legal_name', $resourceArray["legalName"]);
        $res->bindParam(':email', $resourceArray["email"]);
        $res->bindParam(':cf', $resourceArray["cf"]["value"]);
        $res->bindParam(':city', $resourceArray["addr"]["city"]);
        $res->bindParam(':prov', $resourceArray["addr"]["prov"]);
        $res->bindParam(':street', $resourceArray["addr"]["street"]);
        $res->bindParam(':street_number', $resourceArray["addr"]["streetNumber"]);
        $res->bindParam(':cap', $resourceArray["addr"]["cap"]);
        $res->bindParam(':phone', $resourceArray["phone"]);
        $res->bindParam(':piva', $resourceArray["PIVA"]);
        if (QueryRunner::execute($res)) {
            return $this->PDO->lastInsertId('ID');
        }
        return null;
    }

    private function createPhysical($resourceArray)
    {
        $res = $this->PDO->prepare($this->insertPhysicalStmt);
        $res->bindParam(':first_name', $resourceArray["firstName"]);
        $res->bindParam(':last_name', $resourceArray["lastName"]);
        $res->bindParam(':birthday', $resourceArray["birthDate"]);
        $res->bindParam(':email', $resourceArray["email"]);
        $res->bindParam(':cf', $resourceArray["cf"]["value"]);
        $res->bindParam(':city', $resourceArray["addr"]["city"]);
        $res->bindParam(':prov', $resourceArray["addr"]["prov"]);
        $res->bindParam(':street', $resourceArray["addr"]["street"]);
        $res->bindParam(':street_number', $resourceArray["addr"]["streetNumber"]);
        $res->bindParam(':cap', $resourceArray["addr"]["cap"]);
        $res->bindParam(':phone', $resourceArray["phone"]);
        if (QueryRunner::execute($res)) {
            return $this->PDO->lastInsertId('ID');
        }
        return null;
    }

    private function updateLegal($resourceArray, $id)
    {
        $res = $this->PDO->prepare($this->updateLegalStmt);
        $res->bindParam(':id', $id);
        $res->bindParam(':legal_name', $resourceArray["legalName"]);
        $res->bindParam(':email', $resourceArray["email"]);
        $res->bindParam(':cf', $resourceArray["cf"]["value"]);
        $res->bindParam(':city', $resourceArray["addr"]["city"]);
        $res->bindParam(':prov', $resourceArray["addr"]["prov"]);
        $res->bindParam(':street', $resourceArray["addr"]["street"]);
        $res->bindParam(':street_number', $resourceArray["addr"]["streetNumber"]);
        $res->bindParam(':cap', $resourceArray["addr"]["cap"]);
        $res->bindParam(':phone', $resourceArray["phone"]);
        $res->bindParam(':piva', $resourceArray["PIVA"]);

        $result = QueryRunner::execute($res);

        return $result;
    }

    private function updatePhysical($resourceArray, $id)
    {
        $res = $this->PDO->prepare($this->updatePhysicalStmt);
        $res->bindParam(':id', $id);
        $res->bindParam(':firstName', $resourceArray["firstName"]);
        $res->bindParam(':lastName', $resourceArray["lastName"]);
        $res->bindParam(':birthDay', $resourceArray["lastName"]);
        $res->bindParam(':email', $resourceArray["email"]);
        $res->bindParam(':cf', $resourceArray["cf"]["value"]);
        $res->bindParam(':city', $resourceArray["addr"]["city"]);
        $res->bindParam(':prov', $resourceArray["addr"]["prov"]);
        $res->bindParam(':street', $resourceArray["addr"]["street"]);
        $res->bindParam(':street_number', $resourceArray["addr"]["streetNumber"]);
        $res->bindParam(':cap', $resourceArray["addr"]["cap"]);
        $res->bindParam(':phone', $resourceArray["phone"]);

        return QueryRunner::execute($res);
    }

    /***
     * @param $resourceArray generic customer parsed from a json string and converted into an associative array
     * @return null
     */
    public function create($resourceArray)
    {
        if (isset($resourceArray["PIVA"]) && !isset($resourceArray["birthDate"])) return $this->createLegal($resourceArray);
        if (isset($resourceArray["firstName"]) && isset($resourceArray["birthDate"])) return $this->createPhysical($resourceArray);
        return null;
    }

    public function update($resourceArray, $id)
    {
        if (isset($resourceArray["PIVA"]) && !isset($resourceArray["birthDate"])) return $this->updateLegal($resourceArray, $id);
        if (isset($resourceArray["firstName"]) && isset($resourceArray["birthDate"])) return $this->updatePhysical($resourceArray, $id);
        return null;
    }

    /**
     * @param $ID integer ID
     * @return customer or false if not exists.
     */

    public function getLegal($ID)
    {
        $res = $this->PDO->prepare($this->selectLegalStmt);
        //echo var_dump($res);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $result = $res->fetch(PDO::FETCH_OBJ);
        }
        //echo var_dump($result);
        return $result;
    }

    public function getPhysical($ID)
    {
        $res = $this->PDO->prepare($this->selectPhysicalStmt);
        //echo var_dump($res);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $result = $res->fetch(PDO::FETCH_OBJ);
        }
        //echo var_dump($result);
        return $result;
    }

    public function getMaxID()
    {
        $res = $this->PDO->prepare($this->selectLegalMaxId);
        if (QueryRunner::execute($res)) {
            $result = $res->fetch(PDO::FETCH_OBJ);
        }
        //echo var_dump($result);
        return $result;
    }

    /**
     * @return boolean. true if success
     */
    function autoConfigureLegalCustomer()
    {
        $res = $this->PDO->prepare($this->configLegalStmt);
        return QueryRunner::execute($res);
    }

    function autoConfigurePhysicalCustomer()
    {
        $res = $this->PDO->prepare($this->configPhysicalStmt);
        return QueryRunner::execute($res);
    }

    /**
     * @param $DBuser dbUsername: used to doublecheck the intention of drop the table
     * @return boolean. true if success
     */
    function dropTableLegalCustomer($DBuser)
    {
        if ($DBuser != $this->dbUsername) {
            return false;
        }
        $res = $this->PDO->prepare($this->dropLegalTableStmt);
        return QueryRunner::execute($res);
    }


    public function searchLegal($queryUrl)
    {
        $queryArr = null;
        $whereStmt = "";
        $row = null;
        parse_str($queryUrl, $queryArr);
        foreach ($queryArr as $key => $value) {
            $whereStmt .= "$key = :$key ";
            if ($value != end($queryArr)) $whereStmt .= "and ";

        }
        $newQuery = "$this->queryLegalSql WHERE $whereStmt ORDER BY id";

        $res = $this->PDO->prepare($newQuery);
        foreach ($queryArr as $key => $value) {
            $res->bindValue($key, $value);
        }

        if (QueryRunner::execute($res)) {
            $row = $res->fetchAll(PDO::FETCH_OBJ);
        }
        return $row;
    }

    public function searchPhysical($queryUrl)
    {
        $queryArr = null;
        $whereStmt = "";
        $row = null;
        parse_str($queryUrl, $queryArr);
        foreach ($queryArr as $key => $value) {
            $whereStmt .= "$key = :$key ";
            if ($value != end($queryArr)) $whereStmt .= "and ";

        }
        $newQuery = "$this->queryPhysicalSql WHERE $whereStmt ORDER BY id";

        $res = $this->PDO->prepare($newQuery);
        foreach ($queryArr as $key => $value) {
            $res->bindValue($key, $value);
        }

        if (QueryRunner::execute($res)) {
            $row = $res->fetchAll(PDO::FETCH_OBJ);
        }
        return $row;
    }

    public function deleteLegal($ID)
    {
        if (!$this->get($ID)) return false;
        $res = $this->PDO->prepare($this->deleteLegalStmt);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            if ($res->rowCount() == 1) return true;
        }
        return false;
    }

    public function deletePhysical($ID)
    {
        if (!$this->get($ID)) return false;
        $res = $this->PDO->prepare($this->deletePhysicalStmt);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            if ($res->rowCount() == 1) return true;
        }
        return false;
    }
}