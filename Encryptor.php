<?php
namespace ingsw10;
require './lib/password.php';
use Exception;

final class Encryptor
{

    const CPU_COST_LOW = 4;
    const CPU_COST_MEDIUM = 16;
    const CPU_COST_HIGH = 31;

    static function hash($password, $cpuCost)
    {
        if ($cpuCost != (Encryptor::CPU_COST_LOW || Encryptor::CPU_COST_MEDIUM || Encryptor::CPU_COST_HIGH)) throw new Exception("
            accepted values: Encryptor::CPU_COST_LOW, Encryptor::CPU_COST_MEDIUM, Encryptor::CPU_COST_HIGH");
        return password_hash($password, PASSWORD_DEFAULT, array("cost" => $cpuCost));
    }

    static function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }
}

?>