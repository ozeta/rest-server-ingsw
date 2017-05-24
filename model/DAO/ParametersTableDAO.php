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

class ParametersTableDAO
{

    private $getAllStmt;
    private $setAllStmt;

    private $configStmt;
    private $insertStmt;
    private $dropTableStmt;
    private $tableName;
    private $dbUsername;
    private $PDO;


    public function __construct($dbHost, $username, $password, $schema, $tableName)
    {
        $this->dbUsername = $username;
        $tableName = $schema . "." . $tableName;
        $this->tableName = $tableName;
        try {
            #stringa caratteristica mysql
            $this->PDO = new PDO("mysql:host=$dbHost", $username, $password);
            #imposta quanti errori mostrare in caso di eccezione
            $this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log($e->getMessage() . "\n\n", 3, "./server-errors.log");
        }

        $this->dropTableStmt = /** @lang mysql */
            "DROP TABLE IF EXISTS $tableName";

        $this->configStmt = /** @lang mysql */
            "CREATE SCHEMA IF NOT EXISTS $schema ; create table IF NOT EXISTS $tableName (
            id             int(11) NOT NULL, 
            daily_interest float NOT NULL,
            shipping_cost  float NOT NULL,
            water_cost     float NOT NULL,
            tax_rate       float NOT NULL,
            PRIMARY KEY (id));";
        $this->insertStmt = /** @lang mysql */
            "INSERT INTO $tableName(id, daily_interest, shipping_cost,water_cost, tax_rate) 
            VALUES (1, -1,-1,-1,-1);";

        $this->getAllStmt = /** @lang mysql */
            "SELECT daily_interest, shipping_cost, water_cost, tax_rate 
            FROM $tableName WHERE id = 1;";

        $this->setAllStmt = /** @lang mysql */
            "UPDATE $tableName SET 
            daily_interest = :daily_interest, 
            shipping_cost = :shipping_cost, 
            water_cost = :water_cost, 
            tax_rate = :tax_rate 
            WHERE        
            id = 1;";
    }

    /**
     * @return boolean. true if success
     */
    function autoConfigure()
    {
        $res = $this->PDO->prepare($this->configStmt);
        return QueryRunner::execute($res);
    }
    function insertDummy()
    {
        $res = $this->PDO->prepare($this->insertStmt);
        return QueryRunner::execute($res);
    }
    /**
     * @param $DBuser dbUsername: used to doublecheck the intention of drop the table
     * @return boolean. true if success
     */
    function dropTable($DBuser)
    {
        if ($DBuser != $this->dbUsername) {
            return false;
        }
        $res = $this->PDO->prepare($this->dropTableStmt);
        return QueryRunner::execute($res);
    }

    /**
     * @return mixed or false
     */
    public function getAll()
    {
        $result = null;
        $res = $this->PDO->prepare($this->getAllStmt);
        if (QueryRunner::execute($res)) {
            $result = $res->fetch(PDO::FETCH_OBJ);
            //if ($result) return User::recast($result);
        }
        return $result;
    }

    /**
     * @param $daily_interest
     * @param $shipping_cost
     * @param $water_cost
     * @param $tax_rate
     * @return boolean. true if success
     */
    public function setAll($daily_interest, $shipping_cost, $water_cost, $tax_rate)
    {
        $res = $this->PDO->prepare($this->setAllStmt);
        $res->bindParam(':daily_interest', $daily_interest);
        $res->bindParam(':shipping_cost', $shipping_cost);
        $res->bindParam(':water_cost', $water_cost);
        $res->bindParam(':tax_rate', $tax_rate);
        return QueryRunner::execute($res);
    }
    public function getMeta()
    {
        $result = null;
        $getMetaStmt = "SHOW COLUMNS FROM $this->tableName";
        $res = $this->PDO->prepare($getMetaStmt);
        if (QueryRunner::execute($res)) {
            $result = $res->fetchAll(PDO::FETCH_COLUMN,0);
        }
        return $result;
    }
}