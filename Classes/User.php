<?php

/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 18:04
 */

class User{
    private $_db;
    private $_data = [];
    private $_error = false;
    private $_sessionName;
    private $_isLoggedIn = false;
    private $_cookieName;


    /**
     * Checks if the user is logged in
     * @return bool
     */

    public function isLoggedIn(){
        /*if(Session::getInstance()->elapsed()) {
            $this->logout();
        }*/
        return $this->_isLoggedIn;
    }

    /**
     * Method to check if any error occurred
     * @return bool
     */
    public function error(){
        return $this->_error;
    }


    /**
     * User constructor.
     * Will return the current user if it is logged in, otherwise it will
     * get the user passed by argument (id)
     * @param null $user
     */

    public function __construct($user = null){
        $this->_db = Database::getInstance();
        $this->_sessionName = Config::get('session/session_name');
        $this->_cookieName = Config::get('remember/cookie_name');

        if(!$user) {
            if(Session::getInstance()->exists($this->_sessionName)){
                $user = Session::getInstance()->get($this->_sessionName);
                if($this->find($user)){
                    $this->_isLoggedIn = true;
                } else{
                    // process logout
                }
            }
        } else{
            $this->find($user);
        }
    }

    /**
     * This creates a new user
     * @param array $fields
     * @throws Exception
     */

    public function create($fields = array()){

        /** TODO sanitize strings */

        $value = $this->_db->insert('users', $fields, true, 'email');
        try{
            if($this->_db->error()) {
                throw new Exception(Text::get("DATABASE_ERROR"));
            }
            if(!$value){
                throw new Exception("REGISTER_USER_EXISTS");
            }
        }catch(Exception $e){
            throw $e;
        }
    }

    /**
     * Checks on database if a user is present (by email)
     * stores in _data his info
     * @param null $user
     * @return bool
     */
    public function find($user = null){
        if($user){
            $field = (is_numeric($user)) ? 'id' : 'email';
            $data = $this->_db->action("SELECT *", 'users', array($field, "=", $user), false);
            if(!is_bool($data)) {
                if(!$data->error()) {
                    $count = $data->getRowsCount();
                    if ($count) {
                        $this->_data = $data->getFirst();
                        //printf ("%s (%s)\n", $this->_data['email'], $this->_data['id']);
                        return true;
                    }
                    return false;
                }

            }
        }
    }

    /**
     * Return data informations
     * @return array
     */
    public function data(){
        return $this->_data;
    }

    /**
     * This log in a use if present and if the password is correct
     * @param null $username
     * @param null $password
     * @return bool
     */
    public function login($username = null, $password = null, $remember = false){

        /** TODO sanitize strings $user */
        $this->_error = false;

        if(!$username && !$password && $this->exists()){
            Session::getInstance()->put($this->_sessionName, $this->data()['id']);
        }else {
            $user = $this->find($username);

            if (!$this->error()) {
                if ($user) {
                    //echo $this->_data[2]; //password field
                    //echo "user password:  " . $this->_data['password'] . "  pass on db: " . Hash::make($password, $this->_data['salt']);
                    if ($this->_data['password'] === Hash::make($password, $this->_data['salt'])) {
                        Session::getInstance()->put($this->_sessionName, $this->_data['id']);
                        Session::getInstance()->put('timestamp', time());

                        if ($remember) {
                            $hash = Hash::unique();
                            $hashCheck = $this->_db->select('users_session', array('user_id', '=', $this->data()['id']), false);
                            if (!is_bool($hashCheck)) {
                                if (!$hashCheck->getRowsCount()) {
                                    $esit = $this->_db->insert('users_session', array(
                                        'user_id' => $this->data()['id'],
                                        'hash' => $hash,
                                    ), true, 'user_id');

                                    if(!$esit){
                                        $this->logout();
                                    }
                                } else {
                                    $hash = $hashCheck->getFirst()['hash'];
                                }
                            }
                            Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
                        }
                        return true;
                    }
                    return false;
                }
                return false;
            }
            return false;
        }
        return false;
    }

    /**
     * Function to update user data
     * @param array $fields
     * @param null $id
     * @return bool
     */

    public function update($fields = array(), $id = null){

        if(!$id && $this->isLoggedIn()){
            $id = $this->data()['id'];
        }

        $where = [];
        $where[0] = 'id';
        $where[1] = '=';
        $where[2] = $id;
        $w = [];

        foreach ($fields as $key => $value){
            $s = $where;
            $w[$key] = $s;
        }

        try {

            if (count($fields) == 1) {
                $values= array_values($fields);
                $keys = array_keys($fields);

                $set[0] = array_shift($keys);
                $set[1] = array_shift($values);
                if (!$this->_db->update('users', $set, $where, true)) {
                    throw new Exception('Database error single in update');
                }
            } else {
                if (!$this->_db->multipleUpdate('users', $fields, $w)) {
                    throw new Exception('Database error multiple update');
                }
            }
        }catch (Exception $e){
            echo "Exception occurred in user: " . $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * Checks if any data is present
     * @return bool
     */

    public function exists(){
        return (!empty($this->data())) ? true : false;
    }

    /**
     * Logout function
     */
    public function logout(){
        $this->_db->delete('users_session', array('user_id', '=', $this->data()['id']), true);
        Session::getInstance()->delete($this->_sessionName);
        $this->_isLoggedIn = false;
        Cookie::delete($this->_cookieName);
    }
}
