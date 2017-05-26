<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 24/05/2017
 * Time: 12:56
 */

namespace ingsw10;


use PDO;
use PDOException;

include_once "QueryRunner.php";
require_once "EPDOStatement.php";

class DAO
{
    private $getMetaStmt = "SHOW COLUMNS FROM :table";
    protected $PDO;
    protected $selectMaxId;
    protected $selectLegalStmt;
    protected $selectPhysicalStmt;
    protected $selectStmt;

    protected $tableName;

    protected $configStmt;
    protected $dropTableStmt;

    protected $dbUsername;
    protected $addConstraintStmt;

    /**
     * DAO constructor.
     * @param string $getMeta
     */
    public function __construct($dbHost, $username, $password, $schema, $tableName)
    {
        $tableName = "$schema.$tableName";
        $this->dbUsername = $username;
        $this->tableName = $tableName;
        $this->getMetaStmt = str_replace(":table", $tableName, $this->getMetaStmt);
        try {
            #stringa caratteristica mysql

            $this->PDO = new PDO ("mysql:host=$dbHost", $username, $password);
            //$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->PDO->setAttribute(PDO::ATTR_STATEMENT_CLASS, array("EPDOStatement\EPDOStatement", array($this->PDO)));
        } catch (PDOException $e) {
            error_log($e->getMessage() . "\n\n", 3, "./server-errors.log");
        }
        $this->selectStmt = /** @lang mysql */
            "SELECT *
            FROM $tableName
             WHERE id = :id;
            ";
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
        $watermeter = $watermeterDao->get($customerDao, $res->id_operator);
        if (!$operator) return null;

        $result = new Reading($res, $customer, $operator, $watermeter);

        return $result;
    }

    public function getMeta($ID)
    {
        $getMetaStmt = "SHOW COLUMNS FROM $this->tableName";
        $res = $this->PDO->prepare($getMetaStmt);
        if (QueryRunner::execute($res)) {
            $result = $res->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        return $result;
    }

}