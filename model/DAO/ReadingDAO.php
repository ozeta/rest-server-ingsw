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
include_once "DAO.php";
require_once "EPDOStatement.php";

class ReadingDAO
{
    protected $insertStmt;
    protected $updateStmt;
    protected $deleteStmt;
    protected $selectMaxId;
    protected $selectLegalStmt;
    protected $selectPhysicalStmt;

    protected $querySql;
    protected $tableName;

    protected $configStmt;
    protected $dropTableStmt;

    protected $dbUsername;
    protected $addConstraintStmt;
    private $getAllByIdStmt;
    private $getMetaStmt = "SHOW COLUMNS FROM :table";

    protected $PDO;
    protected $selectStmt;


    public function __construct($dbHost, $username, $password, $schema, $tableName, $employee, $legal, $physical)
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
        $this->selectStmt = /** @lang mysql */
            "SELECT *
            FROM $tableName
             WHERE id = :id;
            ";
        $this->dropTableStmt = /** @lang mysql */
            "
            ALTER TABLE $tableName DROP FOREIGN KEY FKWaterMeter828994;
            ALTER TABLE $tableName DROP FOREIGN KEY FKWaterMeter372565;
            ALTER TABLE $tableName DROP FOREIGN KEY FKWaterMeter69297;
            DROP TABLE IF EXISTS $tableName";

        $this->configStmt = /** @lang mysql */
            "CREATE SCHEMA IF NOT EXISTS $schema ;
            create table IF NOT EXISTS $tableName (
            id            int(11) NOT NULL AUTO_INCREMENT, 
            value         double NOT NULL, 
            assignment    date , 
            reading       date , 
            id_operator   int(11) NOT NULL, 
            watermeter_id int(11) NOT NULL, 
            id_legal      int(11), 
            id_physical   int(11), 
            PRIMARY KEY (id));
            ";
        $this->addConstraintStmt = /** @lang mysql */
            "ALTER TABLE $tableName  ADD INDEX FKWaterMeter828994 (id_operator), ADD CONSTRAINT FKWaterMeter828994 FOREIGN KEY (id_operator) REFERENCES $employee (id);
            ALTER TABLE $tableName  ADD INDEX FKWaterMeter372565 (id_legal), ADD CONSTRAINT FKWaterMeter372565 FOREIGN KEY (id_legal) REFERENCES $legal (id);
            ALTER TABLE $tableName  ADD INDEX FKWaterMeter69297 (id_physical), ADD CONSTRAINT FKWaterMeter69297 FOREIGN KEY (id_physical) REFERENCES $physical (id);";
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
            (
            :value, 
            :assignment, 
            :reading, 
            :id_operator, 
            :watermeter_id, 
            :id_legal, 
            :id_physical);
            ";
        $this->updateStmt = /** @lang mysql */
            "UPDATE $tableName SET 
            value = :value, 
            assignment = :assignment, 
            reading = :reading, 
            id_operator = :id_operator, 
            watermeter_id = :watermeter_id, 
            id_legal = :id_legal, 
            id_physical = :id_physical
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


        $this->querySql = /** @lang mysql */
            "SELECT * FROM $tableName ";

        $this->getByOperator = /** @lang mysql */
            "SELECT id FROM $tableName WHERE id_operator = :id";
    }


    public function getMeta($ID)
    {
        $this->getMetaStmt = str_replace(":table", $this->tableName, $this->getMetaStmt);
        $res = $this->PDO->prepare($this->getMetaStmt);

        if (QueryRunner::execute($res)) {
            $result = $res->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        return $result;
    }

    private function bindParameters($resourceArray, $res)
    {
        $placeholder = -1;
        if ($resourceArray["legalID"] != 0) {
            //legal
            $res->bindParam(':id_legal', $resourceArray["legalID"]);
            $res->bindParam(':id_physical', $placeholder);
        } else {
            //physical
            $res->bindParam(':id_physical', $resourceArray["physicalID"]);
            $res->bindParam(':id_legal', $placeholder);
        }
        $res->bindParam(':value', $resourceArray["value"]);
        $res->bindParam(':assignment', $resourceArray["assignment"]);
        $res->bindParam(':reading', $resourceArray["reading"]);
        $res->bindParam(':watermeter_id', $resourceArray["watermeterID"]);
        $res->bindParam(':id_operator', $resourceArray["operator"]["ID"]);
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
            return $this->PDO->lastInsertId('id');
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


    public function getAllLegal($ID)
    {
        $res = $this->PDO->prepare($this->selectLegalStmt);

        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $result = $res->fetchAll(PDO::FETCH_OBJ);
        }
        return $result;
    }

    public function getAllPhysical($ID)
    {
        $res = $this->PDO->prepare($this->selectPhysicalStmt);

        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $result = $res->fetchAll(PDO::FETCH_OBJ);
        }
        return $result;
    }

    public function getMaxID()
    {
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
        if (!$this->get($ID)) return null;
        $res = $this->PDO->prepare($this->deleteStmt);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            if ($res->rowCount() == 1) return true;
        }
        return null;
    }

    public function get($ID, $employeeDao, $customerDao, $watermeterDao)
    {
        $res = $this->PDO->prepare($this->selectStmt);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $res = $res->fetch(PDO::FETCH_OBJ);
        }
        if ($res->id_legal != 0) {
            $customer = $customerDao->getLegal($res->id_legal);
        } else {
            $customer = $customerDao->getPhysical($res->id_physical);
        }
        if (!$customer) return null;
        $operator = $employeeDao->get($res->id_operator);
        if (!$operator) return null;
        $watermeter = $watermeterDao->get($customerDao, $res->watermeter_id);
        if (!$operator) return null;

        $result = new Reading($res, $customer, $operator, $watermeter);

        return $result;
    }

    public function getAllByOperator($ID, $employeeDao, $customerDao, $watermeterDao)
    {
        $res = $this->PDO->prepare($this->getByOperator);
        $res->bindParam(':id', $ID, PDO::PARAM_INT);
        if (QueryRunner::execute($res)) {
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
        }
        //var_dump($res);
        $i = 0;
        foreach ($res as $key => $value) {
            $row[$i++] = $this->get($value["id"], $employeeDao, $customerDao, $watermeterDao);
        }
        return $row;
    }
}