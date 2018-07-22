<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 18:00
 */

class Session{

    const SESSION_STARTED = TRUE;
    const SESSION_NOT_STARTED = FALSE;
    const SESSION_EXPIRED = FALSE;
    const SESSION_VALID = TRUE;


    private static $instance;
    private $_sessionState = self::SESSION_NOT_STARTED;
    private $_sessionStatus = self::SESSION_EXPIRED;

    public function getStatus(){
        return $this->_sessionStatus;
    }


    public function setStatus($status){
        $this->_sessionStatus = $status;
    }

    /**
     * Instantiates session if it doesn't exist
     * @return Session
     */

    public static function getInstance() {
        if ( !isset(self::$instance)) {
            self::$instance = new self;
        }
        self::$instance->startSession();
        return self::$instance;
    }

    /**
     * Starts the session if it is not started yet
     * @return bool
     */
    public function startSession() {
        if ( $this->_sessionState == self::SESSION_NOT_STARTED ) {
            $this->_sessionState = session_start();
            $this->_sessionStatus = self::SESSION_VALID;
        }
        if(isset($_SESSION['LAST_ACTIVITY']) && $this->elapsed()){
            $this->_sessionStatus = self::SESSION_EXPIRED;
            return $this->_sessionStatus;
        }
        $this->put('LAST_ACTIVITY', time());
        return $this->_sessionState;
    }

    /**
     * Destroys the session
     * @return bool
     */
    public function destroy() {
        if ( $this->_sessionState == self::SESSION_STARTED ) {
            $this->_sessionState = !session_destroy();
            unset( $_SESSION );
            return !$this->_sessionState;
        }
        return FALSE;
    }


    /**
     * Checks if the session is still valid, returns true if elapsed
     * @return bool
     */

    public function elapsed() {
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > 120) {
            //unset($_SESSION['LAST_ACTIVITY']);
            $this->destroy();
            return true;
        }
        return false;
    }

    /**
     * Put value
     * @param $name
     * @param $value
     * @return mixed
     */

    public function put($name, $value){
        return $_SESSION[$name] = $value;
    }

    /**
     * Checks if session exists
     * @param $name
     * @return bool
     */
    public function exists($name){
        return (isset($_SESSION[$name])) ? true : false;
    }

    /**
     * Get session
     * @param $name
     * @return mixed
     */
    public function get($name){
        return $_SESSION[$name];
    }

    /**
     * Delete session
     * @param $name
     */
    public function delete($name) {
        if(self::exists($name)){
            unset($_SESSION[$name]);
        }
    }

    /**
     * Method to display message just once
     * @param $name
     * @param string $string
     * @return mixed|string
     */

    public function flash($name, $string = ''){
        if(self::exists($name)) {
            $session = self::get($name);
            self::delete($name);
            return $session;
        }else{
            self::put($name, $string);
        }
        return '';
    }
}