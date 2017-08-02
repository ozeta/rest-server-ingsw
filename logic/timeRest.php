<?php
/**
 * Created by PhpStorm.
 * User: ozeta
 * Date: 17/05/2017
 * Time: 23:52
 */

namespace ingsw10;

class TimeRest
{

    private $name = "time";
    public function parseRequest($request){
        $currentDateTime = date('Y-m-d H:i:s');
        echo $currentDateTime;
    }
}

?>