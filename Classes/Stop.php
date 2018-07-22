<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 19/06/2018
 * Time: 15:04
 */

class Stop{
    private $_name = null;
    private $_passengers = 0;
    private $_email = []; // associative array email as key, n passengers as value
    private $_off = []; // tells how many groups are getting off at this stop


    /**
     * Stop constructor.
     * @param $name
     */
    public function __construct($name) {
        $this->_name = $name;
    }

    /**
     * Bookings getter
     * @return array
     */
    public function getEmails(){
        return $this->_email;
    }
    /**
     * Adds a group to the off variable
     */
    public function addOff($email, $people){
        $this->_off[$email] = $people;
    }

    /**
     * Off setter
     * @param $off
     */
    public function removeOff($off){
        $this->_off = $off;
    }

    /**
     * Off getter
     * @return array
     */
    public function getOff(){
        return $this->_off;
    }
    /**
     * name getter
     * @return null
     */
    public function getName(){
        return $this->_name;
    }

    /**
     * Add passengers
     * @param $passengers
     */
    public function addPassengers($passengers){
        $this->_passengers += $passengers;
    }

    /**
     * Sets passengers
     */
    public function setPassengers($passengers){
        $this->_passengers = $passengers;
    }

    /**
     * @return int
     */
    public function getPassengers() {
        return $this->_passengers;
    }

    /**
     * Check if a specific user booked this stop
     * @return bool | int
     */
    public function emailPresent($email){
        if(array_key_exists($email, $this->_email)){
            return $this->_email[$email];
        }
        return 0;
    }

    /**
     * checks if a specific user is getting off at this stop
     * @param $email
     * @return int|mixed
     */
    public function emailPresentOff($email){
        if(array_key_exists($email, $this->_off)){
            return $this->_off[$email];
        }
        return 0;
    }

    /**
     * Deletes a user
     * @param $email
     * @return bool
     */
    public function delete($email){
        if(isset($this->_email[$email])) {
            $this->_passengers -= $this->_email[$email];
            unset($this->_email[$email]);
            return true;
        }
        return false;
    }

    /**
     * Deletes a passengers getting off
     * @param $email
     * @return bool
     */

    public function deleteOff($email){
        if(isset($this->_off[$email])) {
            unset($this->_off[$email]);
            return true;
        }
        return false;
    }

    /**
     * Adds a booking
     * @param string $email
     */
    public function addBooking($email, $passengers) {
        $this->_email[$email] = $passengers;
        $this->addPassengers($passengers);
    }

    /**
     * Prints info of a stop
     */
    public function printStop(){
        echo "{$this->_name}";
    }
}