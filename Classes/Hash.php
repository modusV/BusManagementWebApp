<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 17:59
 */


class Hash{
    public static function make($string, $salt = ''){
        return(hash("md5", $string . $salt));
    }

    /**
     * Generate salt 32 chars
     * @return string
     */
    public static function salt(){
        return $salt = uniqid(mt_rand(), true);

    }

    public static function unique(){
        return self::make(uniqid());
    }
}