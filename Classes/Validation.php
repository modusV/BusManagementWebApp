<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 18:04
 */

class Validation{

    private $_passed = false,
            $_errors = array(),
            $_db = null;

    public function __construct(){
        $this->_db = Database::getInstance();
    }

    /**
     * Checks if the input is present
     * @param $source
     * @param array $inputs
     * @return $this
     */

    public function check($source, array $inputs) {
        $this->_errors = [];
        $this->_passed = false;

        foreach ($inputs as $input => $rules) {

            if (isset($source[$input])) {
                $value = trim($source[$input]);
                $this->validate($input, $value, $rules);
            } else {
                $this->addError($input, Text::get("VALIDATE_MISSING_INPUT", ["%ITEM%" => $input]));
            }
        }
        if (empty($this->_errors)) {
            $this->_passed = true;
        }
        return $this;
    }


    /**
     * Returns all the errors occurred
     * @return array
     */
    public function errors(){
        return $this->_errors;
    }

    /**
     * Adds error to error string
     * @param $input
     * @param $error
     */
    private function addError($input, $error) {
        $this->_errors[$input][] = str_replace(['-', '_'], ' ', ucfirst(strtolower($error)));
    }

    /**
     * true if the test is passed
     * @return bool
     */
    public function passed(){
        return $this->_passed;
    }

    /**
     * Validates input calling methods
     * @param $input
     * @param $value
     * @param array $rules
     */
    private function validate($input, $value, array $rules) {
        foreach ($rules as $rule => $ruleValue) {
            if (($rule === "required" and $ruleValue === true) and empty($value)) {
                $this->addError($input, Text::get("VALIDATE_REQUIRED_RULE", ["%ITEM%" => $input]));
            } elseif (!empty($value)) {
                $methodName = lcfirst(ucwords(strtolower(str_replace(["-", "_"], "", $rule)))) . "Rule";
                if (method_exists($this, $methodName)) {
                    $this->{$methodName}($input, $value, $ruleValue);
                } else {
                    $this->addError($input, Text::get("VALIDATE_MISSING_METHOD", ["%ITEM%" => $input]));
                }
            }
        }
    }


    /**
     * Filters and checks if a field is valid
     * @param $input
     * @param $value
     * @param $ruleValue
     */

    protected function filterRule($input, $value, $ruleValue) {
        switch ($ruleValue) {
            // Email
            case "email":
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $data = [
                        "%ITEM%" => $input,
                        "%RULE_VALUE%" => $ruleValue
                    ];
                    $this->addError($input, Text::get("VALIDATE_FILTER_RULE", $data));
                }
                break;
        }
    }

    /**
     * Checks if a parameter is equal to another
     * @param $input
     * @param $value
     * @param $ruleValue
     */

    protected function matchesRule($input, $value, $ruleValue) {
        if ($value != Input::get($ruleValue)) {
            $data = [
                "%ITEM%" => $input,
                "%RULE_VALUE%" => $ruleValue
            ];
            $this->addError($input, Text::get("VALIDATE_MATCHES_RULE", $data));
        }
    }

    /**
     * Checks if there is at least a lower case char and an uppercase/digit
     * @param $input "password"
     * @param $value   "mypassword12"
     * @param $ruleValue "true/false"
     */
    protected function secureRule($input, $value, $ruleValue){
        if($ruleValue === true) {
            $uppercase = preg_match('@[A-Z]@', $value);
            $lowercase = preg_match('@[a-z]@', $value);
            $number = preg_match('@[0-9]@', $value);

            if (!$lowercase || (!$number && !$uppercase)) {
                $data = [
                    "%ITEM%" => $input,
                    "%RULE_VALUE%" => $ruleValue
                ];
                $this->addError($input, Text::get("VALIDATE_SECURE_RULE", $data));
            }
        }
    }

    /**
     * Checks max characters
     * @param $input
     * @param $value
     * @param $ruleValue
     */

    protected function maxCharactersRule($input, $value, $ruleValue) {
        if (strlen($value) > $ruleValue) {
            $data = [
                "%ITEM%" => $input,
                "%RULE_VALUE%" => $ruleValue
            ];
            $this->addError($input, Text::get("VALIDATE_MAX_CHARACTERS_RULE", $data));
        }
    }

    /**
     * Checks min characters
     * @param $input
     * @param $value
     * @param $ruleValue
     */

    protected function minCharactersRule($input, $value, $ruleValue) {
        if (strlen($value) < $ruleValue) {
            $data = [
                "%ITEM%" => $input,
                "%RULE_VALUE%" => $ruleValue
            ];
            $this->addError($input, Text::get("VALIDATE_MIN_CHARACTERS_RULE", $data));
        }
    }


    /**
     * Checks if its present a field
     * @param $input
     * @param $value
     * @param $ruleValue
     */
    protected function requiredRule($input, $value, $ruleValue) {
        if ($ruleValue === true and empty($value)) {
            $this->addError($input, Text::get("VALIDATE_REQUIRED_RULE", ["%ITEM%" => $input]));
        }
    }


    /**
     * checks if in the db is unique
     * @param $input
     * @param $value
     * @param $ruleValue
     */
    protected function uniqueRule($input, $value, $ruleValue) {
        //remember to lock db here.

        $check = $this->_db->select($ruleValue, [$input, "=", $value], true);
        if(!is_bool($check)) {
            if (!$check->error()) {
                $count = $check->getRowsCount();
                if ($count) {
                    /*f ($this->_recordID and $check->first()->id === $this->_recordID) {
                        return;
                    }
                    */
                    $this->addError($input, Text::get("VALIDATE_UNIQUE_RULE", ["%ITEM%" => $input]));
                }
            } else {
                $this->addError($input, Text::get("DATABASE_ERROR", ["%ITEM%" => $input]));
            }
        }
        else{
            $this->addError($input, Text::get("DATABASE_EXCEPTION", ["%ITEM%" => $input]));
        }
    }


    /**
     * Checks if a parameter greater than another
     * @param $input
     * @param $value
     * @param $ruleValue
     */

    protected function greaterRule($input, $value, $ruleValue) {
        if (strcmp($value, Input::get($ruleValue)) >= 0) {
            $data = [
                "%ITEM%" => $input,
                "%RULE_VALUE%" => $ruleValue,
            ];
            $this->addError($input, Text::get("VALIDATE_LESSER_RULE", $data));
        }
    }

    /**
     * @param $input "password"
     * @param $value   "mypassword12"
     * @param $ruleValue "true/false"
     */
    protected function numericRule($input, $value, $ruleValue){
        if($ruleValue === true){
            if (!ctype_digit($value)){
                $data = [
                    "%ITEM%" => $input,
                ];
                $this->addError($input, Text::get("VALIDATE_NUMBER_RULE", $data));
            }
        }
    }
    
    /**
     * @param $input "password"
     * @param $value   "mypassword12"
     * @param $ruleValue "true/false"
     */
    protected function positiveRule($input, $value, $ruleValue){
        if($ruleValue === true){
            if ($value < 0){
                $data = [
                    "%ITEM%" => $input,
                ];
                $this->addError($input, Text::get("VALIDATE_POSITIVE_RULE", $data));
            }
        }
    }

}