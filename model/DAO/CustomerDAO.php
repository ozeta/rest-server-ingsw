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

    private $getLegalByCF;
    private $getLegalByPIVA;
    private $getPhysicalByCF;

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
    private static $instance = null;

    public static function getInstance($dbHost, $username, $password, $schema, $legalTableName, $physicalTableName)
    {
        if (static::$instance === null) {
            static::$instance = new CustomerDAO($dbHost, $username, $password, $schema, $legalTableName, $physicalTableName);
        }
        return static::$instance;
    }

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
            //$this->PDO->setAttribute(PDO::ATTR_STATEMENT_CLASS, array("EPDOStatement\EPDOStatement", array($this->PDO)));
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
            VALUES (:firstname, :lastname, :email, :phone, :birthday, :cf, :city, :prov, :street, :street_number, :cap);
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
            first_name = :firstname, 
            last_name = :lastname, 
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
        $this->getLegalByCF = /** @lang mysql */
            "SELECT *
            FROM $legalTableName
             WHERE cf = :cf;
            ";
        $this->getLegalByPIVA = /** @lang mysql */
            "SELECT *
            FROM $legalTableName
             WHERE piva = :piva;
            ";
        $this->getPhysicalByCF = /** @lang mysql */
            "SELECT *
            FROM $physicalTableName
            WHERE cf = :cf;
            ";
        $this->queryLegalSql = "SELECT * FROM $legalTableName ";
        $this->queryPhysicalSql = "SELECT * FROM $physicalTableName";

    }

    /**
     * @param $resourceArray
     * @return int|null|string
     */
    public function createLegal($resourceArray)
    {

        if ($this->getLegalByCF($resourceArray["cf"]["value"]) != null) {
            return -1;
        }
        if ($this->getLegalByPIVA($resourceArray["PIVA"]) != null) {
            return -2;
        }

        if (strlen($resourceArray["PIVA"]) > 20) {
            return -3;
        }
        $res = $this->PDO->prepare($this->insertLegalStmt);
        $res = $this->bindLegal($res, $resourceArray);

        //echo $res->interpolateQuery();
        if (QueryRunner::execute($res)) {
            return $this->PDO->lastInsertId('ID');

        }
        return null;
    }

    /**
     * @param $res
     * @param $resourceArray
     * @return mixed
     */
    private function bindLegal($res, $resourceArray)
    {
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
        return $res;
    }

    /**
     * @param $resourceArray
     * @return int|null|string
     */
    public function createPhysical($resourceArray)
    {

        if ($this->getPhysicalByCF($resourceArray["cf"]["value"]) != null) {
            return -1;
        }


        $res = $this->PDO->prepare($this->insertPhysicalStmt);
        $res = $this->bindPhysical($res, $resourceArray);
        //echo $res->interpolateQuery();
        if (QueryRunner::execute($res)) {
            return $this->PDO->lastInsertId('ID');
        }
        return null;
    }

    /**
     * @param $resourceArray
     * @param $id
     * @return int|mixed
     */
    public function updateLegal($resourceArray, $id)
    {
        if (!$this->getLegal($id)) return null;
        $test = $this->getLegalByCF($resourceArray["cf"]["value"]);
        if ($test && $test->getId() != $resourceArray["id"] ) {
            echo $test->getId()."!=".$resourceArray["id"];
            return -1;
        }
        $test = $this->getLegalByPIVA($resourceArray["PIVA"]);
        if ($test && $test->getId() != $resourceArray["id"] ) {
            echo $test->getId()."!=".$resourceArray["id"];
            return -2;
        }

        //echo "$test->getId(), ".$resourceArray['cf']['value'];
        $res = $this->PDO->prepare($this->updateLegalStmt);
        $res->bindParam(':id', $id);
        $res = $this->bindLegal($res, $resourceArray);

        $result = QueryRunner::execute($res);

        return $result;
    }

    /**
     * @param $res
     * @param $resourceArray
     * @return mixed
     */
    private function bindPhysical($res, $resourceArray)
    {
        $res->bindParam(':firstname', $resourceArray["firstName"]);
        $res->bindParam(':lastname', $resourceArray["lastName"]);
        $res->bindParam(':birthday', $resourceArray["birthDate"]);
        $res->bindParam(':email', $resourceArray["email"]);
        $res->bindParam(':cf', $resourceArray["cf"]["value"]);
        $res->bindParam(':city', $resourceArray["addr"]["city"]);
        $res->bindParam(':prov', $resourceArray["addr"]["prov"]);
        $res->bindParam(':street', $resourceArray["addr"]["street"]);
        $res->bindParam(':street_number', $resourceArray["addr"]["streetNumber"]);
        $res->bindParam(':cap', $resourceArray["addr"]["cap"]);
        $res->bindParam(':phone', $resourceArray["phone"]);
        return $res;
    }

    /**
     * @param $resourceArray
     * @param $id
     * @return mixed
     */
    public function updatePhysical($resourceArray, $id)
    {
        if (!$this->getPhysical($id)) return null;
        $test = $this->getPhysicalByCF($resourceArray["cf"]["value"]);
        if ($test && $test->getId() != $resourceArray["id"] ) {
            return -1;
        }
        $res = $this->PDO->prepare($this->updatePhysicalStmt);
        $res->bindParam(':id', $id);
        $res = $this->bindPhysical($res, $resourceArray);
        //echo $res->interpolateQuery();
        return QueryRunner::execute($res);
    }

    /**
     * @param $ID
     * @return Legal|null
     */
    public function getLegal($ID)
    {
        $res = $this->PDO->prepare($this->selectLegalStmt);
        //echo var_dump($res);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $result = $res->fetch(PDO::FETCH_OBJ);
        }
        if ($result) {
            return new Legal($result);
        }
        return null;
    }

    /**
     * @param $CF
     * @return Legal|null
     *
     */
    public function getLegalByCF($CF)
    {
        $res = $this->PDO->prepare($this->getLegalByCF);
        //echo var_dump($res);
        $res->bindParam(':cf', $CF, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $result = $res->fetch(PDO::FETCH_OBJ);
        }
        if ($result) {
            return new Legal($result);
        }
        return null;
        /*        try {
                    $res->execute();
                    $result = $res->fetch(PDO::FETCH_OBJ);
                    if ($result) {
                        return new Legal($result);
                    }
                } catch (PDOException $e) {
                    return null;
                }*/
    }

    public function getLegalByPIVA($PIVA)
    {
        $res = $this->PDO->prepare($this->getLegalByPIVA);
        //echo var_dump($res);
        $res->bindParam(':piva', $PIVA, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $result = $res->fetch(PDO::FETCH_OBJ);
        }
        if ($result) {
            return new Legal($result);
        }
        return null;
/*        try {
            $res->execute();
            $result = $res->fetch(PDO::FETCH_OBJ);
            if ($result) {
                return new Legal($result);
            }
        } catch (PDOException $e) {
            return null;
        }*/
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
        if ($result) return new Physical($result);
        else return null;
    }

    public function getPhysicalByCF($CF)
    {
        $res = $this->PDO->prepare($this->getPhysicalByCF);
        //echo var_dump($res);
        $res->bindParam(':cf', $CF, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $result = $res->fetch(PDO::FETCH_OBJ);
        }
        if ($result) {
            return new Legal($result);
        } else return null;
    }

    public function getMaxID()
    {
        $res = $this->PDO->prepare($this->selectLegalMaxId);
        if (QueryRunner::execute($res)) {
            $result = $res->fetch(PDO::FETCH_OBJ);
        }
        if ($result) return new Legal($result);
        else return null;
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
            return null;
        }
        $res = $this->PDO->prepare($this->dropLegalTableStmt);
        return QueryRunner::execute($res);
    }

    /**
     * @param $queryUrl
     * @param $querySql
     * @return array of entities
     */
    private function search($queryUrl, $querySql)
    {
        $queryArr = null;
        $whereStmt = "";
        $row = null;
        parse_str($queryUrl, $queryArr);
        foreach ($queryArr as $key => $value) {
            $whereStmt .= "$key = :$key ";
            if ($value != end($queryArr)) $whereStmt .= "and ";

        }
        $newQuery = "$querySql WHERE $whereStmt ORDER BY id";

        $res = $this->PDO->prepare($newQuery);
        foreach ($queryArr as $key => $value) {
            $res->bindValue($key, $value);
        }
        return $res;
    }

    /**
     * @param $queryUrl
     * @return null
     */
    public function searchLegal($queryUrl)
    {
        $res = $this->search($queryUrl, $this->queryLegalSql);

        if (QueryRunner::execute($res)) {
            $result = null;
            $i = 0;
            $row = $res->fetchAll(PDO::FETCH_OBJ);
            foreach ($row as $key => $value) {
                $result[$i++] = new Legal($value);
            }
        }
        return $result;
    }

    /**
     * @param $queryUrl
     * @return null
     */
    public function searchPhysical($queryUrl)
    {
        $res = $this->search($queryUrl, $this->queryPhysicalSql);

        if (QueryRunner::execute($res)) {
            $result = null;
            $i = 0;
            $row = $res->fetchAll(PDO::FETCH_OBJ);
            foreach ($row as $key => $value) {
                $result[$i++] = new Physical($value);
            }
        }
        return $result;
    }

    /**
     * @param $ID
     * @return bool|null
     */
    public function deleteLegal($ID)
    {
        if (!$this->getLegal($ID)) return null;
        $res = $this->PDO->prepare($this->deleteLegalStmt);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            if ($res->rowCount() == 1) return true;
        }
        return null;
    }

    /**
     * @param $ID
     * @return bool|null
     */
    public function deletePhysical($ID)
    {
        if (!$this->getPhysical($ID)) return null;
        $res = $this->PDO->prepare($this->deletePhysicalStmt);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            if ($res->rowCount() == 1) return true;
        }
        return null;
    }

    /**
     * @param $ID
     * @return array|null
     */
    public function getMetaLegal($ID)
    {
        $result = null;
        $getMetaStmt = "SHOW COLUMNS FROM $this->legalTableN";
        $res = $this->PDO->prepare($getMetaStmt);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $result = $res->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        return $result;
    }

    /**
     * @param $ID
     * @return array|null
     */
    public function getMetaPhysical($ID)
    {
        $result = null;
        $getMetaStmt = "SHOW COLUMNS FROM $this->physicalTableN";
        $res = $this->PDO->prepare($getMetaStmt);
        if (QueryRunner::execute($res)) {
            $result = $res->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        return $result;
    }

    /**
     * @param $id
     */
    public function jsonTest($id)
    {
        $ownerID = -1;
        $owner = null;

        $owner = $this->getPhysical($id);
        if ($owner) {
            $legal = new Physical($owner);
            // $watermeter = new Watermeter($address,$legal, 0);
            // var_dump($watermeter);
            echo json_encode($legal, JSON_PRETTY_PRINT);

        } else echo "no owner!!";
    }
}