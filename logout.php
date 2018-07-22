<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 17:57
 */

require_once  'Core/init.php';

$user = new User();
$user->logout();
Redirect::to('welcome.php');