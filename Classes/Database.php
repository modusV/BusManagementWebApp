<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 17:59
 */

Class Database {

    private static $_dbInstance = null;
    private $_connection = null;
    private $_result = [];
    private $_rows;
    private $_error = false;


    /**
     * Get database instance
     * @return Database|null
     */

    public static function getInstance(){
        if(!isset(self::$_dbInstance)){
            try {
                self::$_dbInstance = new Database();
            }catch (Exception $e){
                echo "Exception occurred in db building";
                exit();
            }
        }
        return self::$_dbInstance;
    }

    /**
     * CHeck if error occurred
     * @return bool
     */
    public function error() {
        return $this->_error;
    }
    /**
     * DB constructor.
     * Connects meanwhile.
     */

    public function __construct(){

        $this->_connection = mysqli_connect(Config::get('mysql/host'),
            Config::get('mysql/username'), Config::get('mysql/password'),
            Config::get('mysql/db'));
        if (mysqli_connect_errno()) {
            echo "Connection failed: ".
            mysqli_connect_error();
            exit();
        }
    }

    /**
     * Simple queries the db
     * @param $query
     * @return Database | bool
     */
    public function simpleQueryForResult($query){
        $this->_error = false;
        $this->_result = [];

        if ($res = mysqli_query($this->_connection, $query)){
            //echo "result : " . $query;
            if(!is_bool($res)) {
                //$this->_result = mysqli_fetch_assoc($res);
                $i = 0;
                $this->addResult($res);
                //echo "ciao" . $query;
                return $this;
            }
            //echo "Error in simple query" . $query;
            $this->_error = true;
            return false;
        }
        //echo "changed11";
        $this->_error = true;
        return false;
    }

    /**
     * Atomic Check Duplicate Query
     * Locks all table before query to guarantee ACID, send FIRST check, then query,
     * the last parameter if set makes query fail if found duplicate
     * @param $query
     * @param $table
     * @return bool | Database
     */

    public function atomicQuery($query, $table, $rbOnDuplicate){

       $res = [];
       $this->_result = [];
       $this->_error = false;
       $checked = false;

       try{

           $this->disableAutocommit();
           $this->lockTableForUpdate($table);
            //Sleep(20);
           foreach ($query as $key => $value ) {
               if (!($res[$key] = mysqli_query($this->_connection, $value))) {
                   //echo $value;
                   throw new Exception("Query {$key} failed");
               }else if(!$checked && $rbOnDuplicate){
                   if(mysqli_num_rows($res[$key]) > 0){
                       throw new Exception("Duplicate found");
                   }
                   $checked = true;
               }
           }

           //$this->unlockTable();
           $this->commit();

       }catch(Exception $e){
           $this->rollback();
           if(!($e->getMessage() === "Duplicate found")) {
               $this->_error = true;
           }
           $this->enableAutocommit();
           return false;
       }

       $this->enableAutocommit();
       $i = 0;

       // use it again to skip the verification query and add just the real on
       $checked = false;

       foreach ($res as $k => $v) {
           if (!is_bool($res[$k]) && $checked) {
               $this->addResult($v);
           }
           $checked = true;
       }
        return $this;
    }


    /**
     * Locks the whole db until res is freed
     * @param $query
     * @return bool | Database
     *
     * @NOT USED
     */
    public function dbProtectedQuery($query){
        $this->_result = [];
        $this->_error = false;

        if ($res = mysqli_query($this->_connection, $query, MYSQLI_USE_RESULT)){
            if(!is_bool($res)) {
                $i=0;
                while ($row = mysqli_fetch_assoc($res)) {
                    $this->_result[$i++] = $row;
                }
                $this->_rows = mysqli_num_rows($res);
                mysqli_free_result($res);
                return $this;
            }
            $this->_error = true;
            return false;
        }
        $this->_error = true;
        return false;
    }

    /**
     * Get query rows result
     * @return int | null
     */

    public function getRowsCount(){
        if(isset($this->_rows)) {
            $returned = $this->_rows;
            $this->_rows = null;
            return $returned;
        }
        return null;
    }

    /**
     * Returns query result
     * @return array | null
     */

    public function  getQueryResult(){
        if(isset($this->_result)) {
            $returned = $this->_result;
            $this->_result = null;
            return $returned;
        }
        return null;
    }

    /**
     * Allows to perform multiple queries atomically and db protected
     * @param $queries
     * @param $table
     * @return bool
     */

    public function multipleAtomicQuery($queries, $table){
        $this->_error = false;
        $this->_rows = 0;
        $this->_result = [];

        //echo $lockQuery;
        try {

            $this->disableAutocommit();
            $this->lockTable($table);

            foreach ($queries as $item) {

                $this->simpleQueryForResult($item);
                if ($this->error()) {
                    //echo "error description: " . mysqli_error($this->_connection);
                    throw new Exception("Query failed");
                }
            }
            $this->unlockTable();
            $this->commit();

        }catch (Exception $e){
            $this->rollback();
            $this->_error = true;
            echo "One of the query failed " . $e->getMessage();
            $this->enableAutocommit();
            return false;
        }
        $this->enableAutocommit();
        return true;
    }
    /**
     * Pass an action + where to do it, and a flag if we need to be protected:
     * Ex: SELECT Student (action) FROM $table, WHERE mark < 3 (3 where values)
     * @param $action
     * @param $table
     * @param array $where
     * @param $protected
     * @return $this|bool
     */

    public function action($action, $table, array $where = [], $protected) {
        if (count($where) === 3) {
            $operator = $where[1];
            $operators = ["=", ">", "<", ">=", "<="];
            if (in_array($operator, $operators)) {
                $field = $where[0];
                $value = $where[2];

                $this->secure($field);
                $this->secure($value);

                $sql = "{$action} FROM `{$table}` WHERE `{$field}` {$operator} '$value'";
                if($protected) {
                    if($this->atomicQuery(array($sql), $table, false)){
                        return $this;
                    }
                }
                else{
                    if ($this->simpleQueryForResult($sql)){
                        return $this;
                    }
                }
            }
        }
        return false;
    }

    /**
     * SELECT
     * @param $table
     * @param array $where
     * @return bool|Database
     */
    public function select($table, array $where = [], $protected) {
        return($this->action('SELECT * ', $table, $where, $protected));
    }

    /**
     * Selects all rows
     * @param $table
     * @return bool|Database
     */
    public function selectAll($table){
        return($this->simpleQueryForResult("SELECT * FROM {$table}"));
    }
    /**
     * DELETE
     * @param $table
     * @param array $where
     * @return bool|Database
     */

    public function delete($table, array $where = [], $protected) {
        return($this->action('DELETE ', $table, $where, $protected));
    }


    /**
     * This receives two array of arrays, where there are all the parameters in associative arrays.
     * the array must be: 1st dimension the
     * @param $table
     * @param array $set
     * @param array $where
     * @return bool | Database
     */

    public function multipleUpdate($table, $set = [], $where = []){
        $queries = array();
        $operators = ["=", ">", "<", ">=", "<="];
        if(count($set) == count($where)){
            $i = 0;
            foreach ($set as $key => $value){

                $this->secure($value);
                $this->secure($key);

                $operator = $where[$key][1];
                if(in_array($operator, $operators)){
                    $field = $where[$key][0];
                    $field1 = $key;
                    $value = $where[$key][2];
                    $value1 = $value;

                    $sql = "UPDATE `{$table}` SET `{$field1}` = '$value1'
                                        WHERE `{$field}` {$operator} '$value'";
                    array_push($queries, $sql);
                }
            }
            if($this->multipleAtomicQuery($queries, $table)){
                return $this;
            }
            else{
                return false;
            }
        }
    }

    /**
     * UPDATE
     * @param $table
     * @param array $set
     * @param array $where
     * @param $protected
     * @return bool | Database
     */

    public function update($table, $set = [], array $where = [], $protected) {
        if (count($where) === 3 and count($set) === 2) {
            $operator = $where[1];
            $operators = ["=", ">", "<", ">=", "<="];
            if (in_array($operator, $operators)) {

                $this->secure($where[0]);
                $this->secure($where[2]);
                $this->secure($set[0]);
                $this->secure($set[1]);

                $field = $where[0];
                $field1 = $set[0];

                $value = $where[2];
                $value1 = $set[1];

                $sql = "UPDATE `{$table}` SET `{$field1}` = '$value1'
                                        WHERE `{$field}` {$operator} '$value'";

                if($protected) {
                    if($this->atomicQuery(array($sql), $table, false)){
                        return $this;
                    }
                }
                else{
                    if($this->simpleQueryForResult($sql)) {
                        return $this;
                    }
                }
            }
            return false;
        }
        echo "wrong arguments";
        return false;
    }


    /**
     * INSERT
     * @param $table
     * @param array $fields
     * @param $protected
     * @return bool | Database
     */

    public function insert($table, array $fields, $protected, $unique) {

        if (count($fields)) {
            $keys = array_keys($fields);
            $uniqueValue ='';


            foreach ($keys as $k){
                $this->secure($fields[$k]);
                $this->secure($k);
                if(!ctype_digit($fields[$k])){
                    $fields[$k] = "'" . $fields[$k] . "'";
                }
                //echo $fields[$k];
            }

            if(isset($fields['email'])){
                $uniqueValue = $fields['email'];
            }
            else if(isset($fields['user_id'])){
                $uniqueValue = $fields['user_id'];
            }

            $sql = "INSERT INTO $table (`" . implode("`, `", $keys) . "`) VALUES (" . implode(", ", $fields). ")";
            if($protected) {

                $verify = "SELECT * FROM $table WHERE `{$unique}` = " . $uniqueValue;
                if($this->atomicQuery(array($verify, $sql), $table, true)){
                    return $this;
                }
            }else{
                if($this->simpleQueryForResult($sql)){
                    return $this;
                }
            }
        }
        return false;
    }

    /**
     * Stores query result
     * @param $res
     */

    public function addResult($res){
        while ($row = mysqli_fetch_assoc($res)) {
            array_push($this->_result, $row);
        }
        $this->_rows = $this->_rows + mysqli_num_rows($res);
        mysqli_free_result($res);
    }


    /**
     * Rollback transaction
     * @return bool
     */
    public function rollback(){
        if(mysqli_rollback($this->_connection)){
            return true;
        }
        return false;
    }

    /**
     * Commit transaction
     * @return bool
     */

    public function commit(){
        if(mysqli_commit($this->_connection)){
            return true;
        }
        return false;
    }
    /**
     * Disable autocommit query
     */
    public function enableAutocommit(){
        if(mysqli_autocommit($this->_connection, true)){
            return true;
        }
        return false;
    }

    /**
     * Enable autocommit Query
     */
    public function disableAutocommit(){
        if(mysqli_autocommit($this->_connection, false)){
            return true;
        }
        return false;
    }

    /**
     * Lock for update
     * @param $table
     * @return $this
     * @throws Exception
     */

    public function lockTableForUpdate($table){
        $lockQuery = "SELECT * FROM {$table} FOR UPDATE ";
        $this->simpleQuery($lockQuery);
        if(!$this->error()){
            return $this;
        }
        throw new Exception("Error in lock");
    }

    /**
     * Locks table
     * @param $table
     * @return $this|bool
     * @throws Exception
     */
    public function lockTable($table){
        $lockQuery = "LOCK TABLES {$table} WRITE";
        $this->simpleQuery($lockQuery);
        if(!$this->error()){
            return $this;
        }
        throw new Exception("Error in lock");
    }

    /**
     * Unlocks table
     * @param null $table
     * @return $this|bool
     * @throws Exception
     */

    public function unlockTable(){
        $unlockQuery = "UNLOCK TABLES";
        $this->simpleQuery($unlockQuery);
        if(!$this->error()){
            return $this;
        }
        throw new Exception("Error in unlock");
    }

    /**
     * Simple query where we do not need any result
     * @param $query
     * @return $this|bool
     * @throws Exception
     */
    public function simpleQuery($query) {
        $this->_error = false;
        $this->secure($query);
        if (mysqli_query($this->_connection, $query)) {
            return $this;
        }else{
            $this->_error = true;
            throw new Exception("Query exception");
        }
    }

    /**
     * Function to make strings secure
     * @param $query
     */
    public function secure(&$query){
        //$query = htmlentities($query);
        mysqli_real_escape_string($this->_connection, $query);
        $query = addslashes($query);
    }



    /**
     * Returns first result
     * @return null
     */

    public function getFirst(){
        if(isset($this->_result)) {
            $returned = array_shift($this->_result);
            $this->_result = null;
            return $returned;
        }
        return null;
    }

}

