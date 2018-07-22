<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 18:00
 */

class Token{

    /**
     * Generates a new session token and stores it in the session global array
     * @return mixed
     */
    public static function generate(){
        $t = Session::getInstance()->put(Config::get('session/token_name'), md5(uniqid()));
        return $t;
    }

    /**
     * Checks if the provided session token is valid, safe to css
     * @param $token
     * @return bool
     */

    public static function check($token){
        $tokenName = Config::get('session/token_name');

        if(Session::getInstance()->exists($tokenName) && $token === Session::getInstance()->get($tokenName)){
            Session::getInstance()->delete($tokenName);
            return true;
        }
        //echo "<br>" . Session::get(Config::get('session/token_name'));
        //echo "<br>" . " " . $token;
        /*
        echo "<br>" . " " . Session::get($tokenName);
        echo "<br>" . " " . Config::get('session/token_name');
       */
        return false;
    }
}