<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 17:58
 */

class Cookie{
    
    public static function count(){
    return count($_COOKIE);
    }
    /**
     * Check if a cookie exists
     * @param $name
     * @return bool
     */
    public static function exists($name){
        return (isset($_COOKIE[$name])) ? true : false;
    }

    /**
     * Retrieves a cookie
     * @param $name
     * @return mixed
     */
    public static function get($name){
        return $_COOKIE[$name];
    }

    /**
     * Deletes a cookie
     * @param $name
     */
    public static function delete($name){
        self::put($name, '', time()-1);
    }

    /**
     * Puts a new cookie
     * @param $name
     * @param $value
     * @param $expiry
     * @return bool
     */
    public static function put($name, $value, $expiry){
        if(setcookie($name, $value, time() + $expiry, '/')){
            return true;
        }else{
            Redirect::to('error.php');
        }
        return false;
    }
}