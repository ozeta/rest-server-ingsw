<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 12/04/2017
 * Time: 20:11
 */

namespace ingsw10;

use PDOException;
use PDOStatement;


class   QueryRunner
{
    /** Executes the query and returns false in case of exception
     * @param PDOStatement $stmt
     * @return mixed The return value of this function on success depends on the fetch type. If an exception is thrown.
     * @internal param $verbose
     */
    public static function execute(PDOStatement $stmt)
    {
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage() . "\n\n", 3, "./server-errors.log");
            echo "(!) " . $e->getMessage();
            echo "\nline: $e->getLine()\n";
            echo "\n$e->errorInfo\n";
            echo "\n$e->getTraceAsString()\n";
            return false;
        }
    }
}