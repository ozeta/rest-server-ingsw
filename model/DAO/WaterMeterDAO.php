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
            //$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->PDO->setAttribute(PDO::ATTR_STATEMENT_CLASS, array("EPDOStatement\EPDOStatement", array($this->PDO)));
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
            PRIMARY KEY (id));
            ";

        $this->addConstraintStmt = /** @lang mysql */
            "ALTER TABLE $tableName ADD INDEX FKWatermeter681382 (id), ADD CONSTRAINT FKWatermeter681382 FOREIGN KEY (id) REFERENCES $reading (id);
            ALTER TABLE $tableName ADD INDEX FKWatermeter123385 (id_legal), ADD CONSTRAINT FKWatermeter123385 FOREIGN KEY (id_legal) REFERENCES $legal (id);
            ALTER TABLE $tableName ADD INDEX FKWatermeter426653 (id_physical), ADD CONSTRAINT FKWatermeter426653 FOREIGN KEY (id_physical) REFERENCES $physical (id);";
        $this->insertStmt = /** @lang mysql */
            "INSERT INTO $tableName
            (
            value, 
            assignment, 
            reading, 
            id_operator, 
            watermeter_id, 
            id_legal, 
            id_physical) 
            VALUES 
            (:value, 
            :assignment, 
            :reading, 
            :id_operator, 
            :watermeter_id, 
            :id_legal, 
            :id_physical);";
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

    private function bindParameters($resourceArray, $res)
    {
        $placeholder = -1;
        if (isset($resourceArray["owner"]["PIVA"])) {
            //legal
            $res->bindParam(':id_legal', $resourceArray["owner"]["id"]);
            $res->bindParam(':id_physical', $placeholder);
        } else {
            //physical
            $res->bindParam(':id_physical', $resourceArray["legal"]["id"]);
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
     * @param $custArray legal customer parsed in Associative array
     * @return int succeeded, returns last customer id. if fails, returns null
     */
    public function create($resourceArray)
    {
        $res = $this->PDO->prepare($this->insertStmt);
        $res = $this->bindParameters($resourceArray, $res);

        if (QueryRunner::execute($res)) {
            return $this->PDO->lastInsertId('ID');
        }
        return null;
    }

    public function update($resourceArray, $id)
    {
        $res = $this->PDO->prepare($this->updateStmt);
        $res = $this->bindParameters($resourceArray, $res);
        $res->bindValue(':id', $id);
        return QueryRunner::execute($res);
    }



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

    public function getAllLegal($dao, $ID)
    {
        $result = null;
        $customer = $dao->getLegal($ID);
        if (!$customer) return null;

        $res = $this->PDO->prepare($this->selectLegalStmt);

        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $row = $res->fetchAll(PDO::FETCH_OBJ);
            $i = 0;
            foreach ($row as $key => $value) {
                $result[$i++] = new Watermeter($customer, $value);
            }
        }
        return $result;
    }

    public function getAllPhysical($dao, $ID)
    {
        $result = null;
        $customer = $dao->getPhysical($ID);
        if (!$customer) return null;
        echo "LOL";

        $res = $this->PDO->prepare($this->selectPhysicalStmt);

        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $row = $res->fetchAll(PDO::FETCH_OBJ);
            $i = 0;
            foreach ($row as $key => $value) {
                $result[$i++] = new Watermeter($customer, $value);
            }
        }
        return $result;
    }


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
        if (!$this->get(null, $ID)) return null;
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
}