<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 17:59
 */

class Input {

    /**
     * checks if input exists
     * @param string $type
     * @return bool
     */

    public static function exists($type = 'POST'){
        switch($type){
            case 'POST':
                return(!empty($_POST)) ? true : false;
                break;
            case 'GET':
                return(!empty($_POST)) ? true : false;
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Checks
     * @param $item
     * @return string
     */

    public static function get($item){
        if(isset($_POST[$item])){
            /*echo "In input we see stuff like this: - name: ";
            echo $item . "  token value: -";
            echo $_POST[$item] . "  ---------- <br>";*/
            return $_POST[$item];
        } else if(isset($_GET[$item])){
            return $_GET[$item];
        }
        return '';
    }
}