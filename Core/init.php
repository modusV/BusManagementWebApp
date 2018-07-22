<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 18:07
 */


$GLOBALS['config'] = array(

    'mysql' => array(
        'host' => '127.0.0.1',
        'username' => 'YourUsername',
        'password' => 'YourPassword',
        'db' => 'YourDb'
    ),
    'remember' => array(
        'cookie_name' => 'hash',
        'cookie_expiry' => 3600 //seconds
    ),

    'session' => array(
        'session_name' => 'user',
        'token_name' => 'token',
        'session_expiry' => 120,
    ),

    'constants' => array(
        'shuttle_capacity' => '4'
    ),

    "texts" => [
        //
        // Login Model Texts
        // =====================================================================
        "LOGIN_INVALID_PASSWORD" => "The email / password combination you have entered is incorrect",
        "LOGIN_USER_NOT_FOUND" => "The email / password you have entered has not been found!",
        "LOGIN_GENERIC_ERROR" => "There was an error in the login process",
        "" => "",
        //
        // Register Model Texts
        // =====================================================================
        "REGISTER_USER_CREATED" => "Your account has been successfully created!",
        "REGISTER_USER_EXISTS" => "This username is taken!",
        "" => "",
        //
        // User Model Texts
        // =====================================================================
        "USER_CREATE_EXCEPTION" => "There was a problem creating this account!",
        "USER_UPDATE_EXCEPTION" => "There was a problem updating this account!",
        "USER_UPDATE_SUCCESS" => "Your details have been updated successfully!",
        "USER_PWD_UPDATED" => "Your password has been successfully updated!",

        "" => "",
        //
        // Input Utility Texts
        // =====================================================================
        "INPUT_INCORRECT_CSRF_TOKEN" => "Cross-site request forgery verification failed!",
        "" => "",
        //
        // Validate Utility Texts
        // =====================================================================
        "VALIDATE_FILTER_RULE" => "%ITEM% is not a valid %RULE_VALUE%!",
        "VALIDATE_MISSING_INPUT" => "Unable to validate %ITEM%!",
        "VALIDATE_MISSING_METHOD" => "Unable to validate %ITEM%!",
        "VALIDATE_MATCHES_RULE" => "%RULE_VALUE% must match %ITEM%.",
        "VALIDATE_MAX_CHARACTERS_RULE" => "%ITEM% can only be a maximum of %RULE_VALUE% characters.",
        "VALIDATE_MIN_CHARACTERS_RULE" => "%ITEM% must be a minimum of %RULE_VALUE% characters.",
        "VALIDATE_REQUIRED_RULE" => "%ITEM% is required!",
        "VALIDATE_UNIQUE_RULE" => "%ITEM% already exists.",
        "VALIDATE_SECURE_RULE" => "%ITEM% requires at least a lower case character and an upper case one or a digit",
        "VALIDATE_LESSER_RULE" => "%RULE_VALUE% must be greater than %ITEM%.",
        "VALIDATE_NUMBER_RULE" => "%ITEM% must be numeric",
        "" => "",
        //
        // Database
        // =====================================================================
        "DATABASE_ERROR" => "Database error occurred",
        "DATABASE_EXCEPTION" => "Database exception occurred",
        "DATABASE_EMPTY_QUERY" => "No results returned from query",
        "DATABASE_EMPTY_RECORD" => "Record does not contain requested field",

        "" => "",

        // Itinerary & stops
        // =====================================================================
        "BOOKING_NOT_FOUND" => "Booking with the corresponding email not found",
        "BOOKING_SUCCESSFUL" => "You booked with success!",
        "BOOKING_DELETE_SUCCESSFUL" => "You deleted your booking with success!",
        "BOOKING_ALREADY_DONE" => "You have already booked",
        "BOOKING_UNDEFINED_ERROR" => "An error occurred while booking",
        "BOOKING_SITS_UNAVAILABLE" => "Not enough available sits",
        "BOOKING_UNAVAILABLE_ROUTE" => "The shuttle is going the other way!",
        "BOOKING_NO_PASSENGERS" => "No passengers in this segment",
        "" => "",

        //
        // General Errors
        // =====================================================================
        "404" => "Ops, that page cannot be found (404)",
        "" => "",
    ],
);

// load classes

spl_autoload_register(function($class){
    require_once 'Classes/' . $class .'.php';
});


require_once 'Functions/sanitize.php';
require_once 'Functions/utilities.php';
require_once 'Functions/securenav.php';

requireHTTPS();
Session::getInstance();

$s = Session::getInstance();
if(!$s->getStatus()){
    Redirect::to('logout.php');
}
Cookie::put("test", true, time() + 2000);
if(!Cookie::count()){
    Redirect::to('error.php');
}


if(Cookie::exists(Config::get('remember/cookie_name')) && !Session::getInstance()->exists(Config::get('session/session_name'))){
    $hash = Cookie::get(Config::get('remember/cookie_name'));
    $hashCheck = Database::getInstance()->select('users_session', array('hash', '=', $hash), false);
    if(!is_bool($hashCheck)){
        if($hashCheck->getRowsCount()) {
            $user = new User($hashCheck->getFirst()['user_id']);
            $user->login();
        }
    }
}