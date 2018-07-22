<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 17:58
 */


class Config{


    /**
     * Function to return strings from the init.php
     * @param null $path
     * @return bool|mixed
     */

    public static function get($path = null){
        if($path){
            $config = $GLOBALS['config'];
            $path = explode('/', $path);

            foreach ($path as $bit){
                if(isset($config[$bit])){
                    $config = $config[$bit];
                }
            }

            return $config;
        }
        return false;
    }
}