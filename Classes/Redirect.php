<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 18:00
 */

class Redirect{

    public static function to($location = null){
        if(is_numeric($location)){
            switch ($location){
                case 404:
                    header('HTTP/1.0 Not Found');
                    include ('Includes/Errors/404.php');
                    exit();
                    break;
            }
        }
        if(isset($location)){
            header('Location: ' . $location);
            exit();
        }
    }
}