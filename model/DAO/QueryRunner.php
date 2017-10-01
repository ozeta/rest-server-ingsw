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

    private static function parseError($error){
        if (preg_match('#\'cf\'#', $error, $match) > 0) {
            return QCFCODE;
        }
        if (preg_match('#\'username\'#', $error, $match) > 0) {
            return QUSERCODE;
        }
        preg_match('#\'phone\'#', $error, $match);
        if (preg_match('#\'phone\'#', $error, $match) > 0) {
            return QPHONECODE;
        }
        if (preg_match('#\'email\'#', $error, $match) > 0) {
            return QEMAILCODE;
        }
    }
    public static function execute(PDOStatement $stmt)
    {
        try {
            return $stmt->execute();

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {

//                 var_dump($stmt);
//                echo $e->getMessage();
                return QueryRunner::parseError($e->getMessage());
            } else {
            }
//            echo var_dump($e);
            if (isset($e->errorInfo) && isset($e->errorInfo[2])) {
                return QueryRunner::parseError($e->getMessage());
//                if (preg_match('#\'cf\'#', $e->errorInfo[2], $match) > 0) {
//                    return QCFCODE;
//                }
//                if (preg_match('#\'username\'#', $e->errorInfo[2], $match) > 0) {
//                    return QUSERCODE;
//                }
//                preg_match('#\'phone\'#', $e->errorInfo[2], $match);
//                if (preg_match('#\'phone\'#', $e->errorInfo[2], $match) > 0) {
//                    return QPHONECODE;
//                }
//                if (preg_match('#\'email\'#', $e->errorInfo[2], $match) > 0) {
//                    return QEMAILCODE;
//                }


            }
            return false;
        }
    }
}