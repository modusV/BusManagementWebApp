<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 18:05
 */

function escape($string){
    //$s = addslashes($string);
    //return strip_tags($s);
    return htmlentities($string, ENT_QUOTES, 'UTF-8');
}
