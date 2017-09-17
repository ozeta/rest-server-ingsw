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
require_once "EPDOStatement.php";

/**
 * Class WaterMeterDAO
 * @package ingsw10
 */
class WaterMeterDAO
{

    private $getAllByIdStmt;

    private $insertStmt;
    private $updateStmt;
    private $deleteStmt;
    private $selectMaxId;
    private $selectLegalStmt;
    private $selectPhysicalStmt;

    private $querySql;
    private $tableName;

    private $configStmt;
    private $dropTableStmt;

    private $dbUsername;
    private $PDO;
    private $addConstraintStmt;


    public function __construct($dbHost, $username, $password, $schema, $tableName, $reading, $legal, $physical)
    {
        $this->dbUsername = $username;
        $tableName = "$schema.$tableName";
        $this->tableName = $tableName;
        try {
            #stringa caratteristica mysql

            $this->PDO = new PDO ("mysql:host=$dbHost", $username, $password);
            $this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //$this->PDO->setAttribute(PDO::ATTR_STATEMENT_CLASS, array("EPDOStatement\EPDOStatement", array($this->PDO)));
        } catch (PDOException $e) {
            error_log($e->getMessage() . "\n\n", 3, "./server-errors.log");
        }

        $this->dropTableStmt = /** @lang mysql */
            "
            ALTER TABLE $tableName DROP FOREIGN KEY FKWatermeter681382;
            ALTER TABLE $tableName DROP FOREIGN KEY FKWatermeter123385;
            ALTER TABLE $tableName DROP FOREIGN KEY FKWatermeter426653;
            DROP TABLE IF EXISTS $tableName";

        $this->configStmt = /** @lang mysql */

            "CREATE SCHEMA IF NOT EXISTS $schema ;
            create table IF NOT EXISTS $tableName (
            id            int(11) NOT NULL AUTO_INCREMENT, 
            city          varchar(255) NOT NULL, 
            prov          varchar(255) NOT NULL, 
            street        varchar(255) NOT NULL, 
            street_number varchar(255) NOT NULL, 
            cap           varchar(255) NOT NULL, 
            id_physical   int(11), 
            id_legal      int(11), 
            contract_date int(11), 
            PRIMARY KEY (id));
            ";

        $this->addConstraintStmt = /** @lang mysql */
            "ALTER TABLE $tableName ADD INDEX FKreading (id), ADD CONSTRAINT FKreading FOREIGN KEY (id) REFERENCES $reading (id);
            ALTER TABLE $tableName ADD INDEX FKlegal(id_legal), ADD CONSTRAINT FKlegal FOREIGN KEY (id_legal) REFERENCES $legal (id);
            ALTER TABLE $tableName ADD INDEX FKphysical (id_physical), ADD CONSTRAINT FKphysical FOREIGN KEY (id_physical) REFERENCES $physical (id);";
        $this->insertStmt = /** @lang mysql */
            "INSERT INTO $tableName
            (
            city, prov, street, street_number, cap, id_legal, id_physical, contract_date) 
            VALUES 
            (
            :city, 
            :prov, 
            :street, 
            :street_number, 
            :cap, 
            :id_legal, 
            :id_physical,
            now()
            );";
        $this->selectLegalStmt = /** @lang mysql */
            "SELECT *
            FROM $tableName
             WHERE id_legal = :id;
            ";
        $this->selectPhysicalStmt = /** @lang mysql */
            "SELECT *
            FROM $tableName
             WHERE id_physical = :id;
            ";

        $this->updateStmt = /** @lang mysql */
            "UPDATE $tableName SET 
            city = :city, 
            prov = :prov, 
            street = :street, 
            street_number = :street_number, 
            cap = :cap, 
            id_physical = :id_physical, 
            id_legal = :id_legal 
            WHERE id = :id;
            ";

        $this->deleteStmt = /** @lang mysql */
            "DELETE FROM $tableName 
            WHERE id = :id;
            ";

        $this->selectMaxId = /** @lang mysql */
            "SELECT max(id)
            FROM $tableName
            ;
            ";

        $this->selectStmt = /** @lang mysql */
            "SELECT *
            FROM $tableName
             WHERE id = :id;
            ";
        $this->querySql = /** @lang mysql */
            "SELECT * FROM $tableName ";

    }

    /**
     * @param $custArray legal customer parsed in Associative array
     * @return int succeeded, returns last customer id. if fails, returns null
     */
    public function create($resourceArray)
    {
        $res = $this->PDO->prepare($this->insertStmt);
        $res = $this->bindParameters($resourceArray, $res);
        //echo $res->interpolateQuery();

        if (QueryRunner::execute($res)) {
            return $this->PDO->lastInsertId('ID');
        }
        return null;
    }

    /**
     * @param $resourceArray
     * @param $res
     * @return mixed
     */
    private function bindParameters($resourceArray, $res)
    {
        $placeholder = -1;
        if (isset($resourceArray["owner"]["PIVA"])) {
            //legal
            $res->bindParam(':id_legal', $resourceArray["owner"]["id"]);
            $res->bindParam(':id_physical', $placeholder);
        } else {
            //physical
            $res->bindParam(':id_physical', $resourceArray["owner"]["id"]);
            $res->bindParam(':id_legal', $placeholder);
        }
        $res->bindParam(':city', $resourceArray["location"]["city"]);
        $res->bindParam(':prov', $resourceArray["location"]["prov"]);
        $res->bindParam(':street', $resourceArray["location"]["street"]);
        $res->bindParam(':street_number', $resourceArray["location"]["streetNumber"]);
        $res->bindParam(':cap', $resourceArray["location"]["cap"]);
        return $res;
    }

    /**
     * @param $resourceArray
     * @param $id
     * @return null
     */
    public function update($resourceArray, $id)
    {
        $res = $this->PDO->prepare($this->updateStmt);
        $res = $this->bindParameters($resourceArray, $res);
        $res->bindValue(':id', $id);
        //echo $res->interpolateQuery();
        if (QueryRunner::execute($res)) {
            return $id;
        }
        return null;
    }

    /**
     * @param $dao
     * @param $ID
     * @return null
     */
    public function getAllLegal($dao, $ID)
    {
        $result = null;
        $customer = $dao->getLegal($ID);
        if (!$customer) return null;

        $res = $this->PDO->prepare($this->selectLegalStmt);

        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        //echo $res->interpolateQuery();
        if (QueryRunner::execute($res)) {
            $row = $res->fetchAll(PDO::FETCH_OBJ);
            $i = 0;
            foreach ($row as $key => $value) {
                $result[$i++] = new Watermeter($customer, $value);
            }
        }
        return $result;
    }

    /**
     * @param $dao
     * @param $ID
     * @return null
     */
    public function getAllPhysical($dao, $ID)
    {
        $result = null;
        $customer = $dao->getPhysical($ID);
        if (!$customer) return null;

        $res = $this->PDO->prepare($this->selectPhysicalStmt);

        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        //echo $res->interpolateQuery();

        if (QueryRunner::execute($res)) {
            $row = $res->fetchAll(PDO::FETCH_OBJ);
            $i = 0;
            foreach ($row as $key => $value) {
                $result[$i++] = new Watermeter($customer, $value);
            }
        }
        return $result;
    }

    /**
     * @return mixed|null
     */
    public function getMaxID()
    {
        $result = null;
        $res = $this->PDO->prepare($this->selectMaxId);
        if (QueryRunner::execute($res)) {
            $result = $res->fetch(PDO::FETCH_OBJ);
        }
        return $result;
    }

    /**
     * @return boolean. true if success
     */
    public function autoConfigure()
    {
        $res = $this->PDO->prepare($this->configStmt);
        return QueryRunner::execute($res);

    }

    /**
     * @return mixed
     */
    public function addConstraint()
    {
        $res1 = $this->PDO->prepare($this->addConstraintStmt);
        return QueryRunner::execute($res1);
    }

    public function dropTable($DBuser)
    {
        if ($DBuser != $this->dbUsername) {
            return null;
        }
        $res = $this->PDO->prepare($this->dropTableStmt);
        return QueryRunner::execute($res);
    }

    public function delete($ID)
    {
        $res = $this->PDO->prepare($this->deleteStmt);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        //echo $res->interpolateQuery();
        if (QueryRunner::execute($res)) {
            if ($res->rowCount() == 1) return true;
        }
        return null;
    }

    /**
     * @return array|null
     */
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

    public function jsonTest($dao)
    {
        $watermeterStdObj = $this->get($dao, 10);
        $ownerID = -1;
        $owner = null;
        $customer = $dao->getLegal(12);

        //$address = Address::create($res);
        $watermeter = new Watermeter($customer, $watermeterStdObj);
        echo json_encode($watermeter, JSON_PRETTY_PRINT);
    }

    /**
     * @param $customerDao
     * @param $ID
     * @return Watermeter|mixed|null
     */
    public function get($customerDao, $ID)
    {
        $result = null;

        $res = $this->PDO->prepare($this->selectStmt);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $result = $res->fetch(PDO::FETCH_OBJ);
        }
        if ($result->id_legal != -1) {
            $customer = $customerDao->getLegal($result->id_legal);
        } else {
            $customer = $customerDao->getPhysical($result->id_physical);
        }
        if (!$customer) return null;
        $result = new Watermeter($customer, $result);
        return $result;
    }

    /**
     * @param $queryUrl
     * @param $querySql
     * @return mixed|null
     */
    public function search($customerDao, $queryUrl)
    {
        //finds [gt|eq|lt][date] in the query
        $pattern = '((gt|eq|lt)\[([\d-]+)\])';

        $map = ["gt" => ">=", "eq" => "=", "lt" => "<="];
        $queryArr = null;
        $whereStmt = "";
        $row = null;
        parse_str($queryUrl, $queryArr);
        foreach ($queryArr as $key => $value) {
            if (preg_match($pattern, $value, $match) > 0) {
                if (in_array($match[1], array_keys($map))) {
                    $whereStmt .= "$key " . $map[$match[1]] . " :$key ";
                }
            } else {
                $whereStmt .= "$key = :$key ";
            }
            if ($value != end($queryArr)) {
                $whereStmt .= "and ";
            }
        }
        $newQuery = "$this->querySql WHERE $whereStmt ORDER BY id";
        $res = $this->PDO->prepare($newQuery);
        foreach ($queryArr as $key => $value) {
            preg_match($pattern, $value, $match);
            if (isset($match[2])) {
                $value = $match[2];
            }
            $res->bindValue($key, $value);
        }
        $result = null;
        if (QueryRunner::execute($res) > 0) {
            $i = 0;
            $row = $res->fetchAll(PDO::FETCH_OBJ);
            foreach ($row as $key => $value) {
                $cust = $this->PDO->prepare($this->selectStmt);
                $cust->bindParam(':id', $value->id, PDO::PARAM_INT);
                if (QueryRunner::execute($cust)) {
                    $cust_res = $cust->fetch(PDO::FETCH_OBJ);
                }
                if ($cust_res->id_legal != -1) {
                    $customer = $customerDao->getLegal($cust_res->id_legal);
                } else {
                    $customer = $customerDao->getPhysical($cust_res->id_physical);
                }
//                echo var_dump($value) . "\n\n";
//                echo var_dump($customer);
                $result[$i++] = new Watermeter($customer, $value);
            }
        }

        return $result;
    }


}