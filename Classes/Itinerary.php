<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 19/06/2018
 * Time: 14:48
 */


class Itinerary{

    private $_stops =[];
    private $_db = null;
    private $_errors = [];
    private $_bookings = 0;
    private $_shuttleCapacity = 0;

    /**
     * Itinerary constructor. Pulls from db all data
     */

    public function __construct() {
        $this->_db = Database::getInstance();
        $this->_shuttleCapacity = Config::get('constants/shuttle_capacity');
        $this->refresh();
    }

    /**
     * Refresh itinerary
     * @return bool
     */

    public function refresh(){
        $this->_stops = [];
        $this->_errors = [];
        $this->_bookings = 0;
        $this->_db->selectAll('bookings');
        if(!$this->_db->error()){
            if(($res = $this->_db->getQueryResult()) != null){
                $this->populate($res);
                return true;
            }else{
                $this->addError(Text::get("DATABASE_EMPTY_QUERY"));
            }
        }else{
            $this->addError(Text::get("DATABASE_ERROR"));
        }
        return false;
    }

    /**
     * Inserts a booking in db
     * @param $email
     * @param $start
     * @param $end
     * @param $people
     * @throws Exception
     * @return $this|bool
     */

    /** TODO manage error output */
    public function insert($email, $start, $end, $people){
        $record = array(
            'email' => $email,
            'start' => $start,
            'end' => $end,
            'people' => $people
        );
        $completed = 0;
        try {
            if($this->_db->disableAutocommit()){
                $completed++;
                if($this->_db->lockTableForUpdate('bookings')) {
                    $completed++;
                    $this->refresh();
                    if ($this->book($record)) {
                        $completed++;
                        $duplicates = $this->_db->insert('bookings', $record, true, 'email');
                    }
                    if($duplicates == false && !$this->_db->error()){
                        throw new Exception("BOOKING_ALREADY_DONE");
                    }
                    if(!$this->_db->error()) {
                        $completed++;
                        if($this->_db->unlockTable()) {
                            $completed++;
                            if($this->_db->commit()){
                                $completed++;
                            }
                        }
                    }else{
                        throw new Exception("DATABASE_ERROR");
                    }
                }
            }
            if($completed != 6){
                throw new Exception("BOOKING_UNDEFINED_ERROR");
            }
        }catch (Exception $e){
            $this->_db->rollback();
            $this->addError(Text::get($e->getMessage()));
            $this->_db->enableAutocommit();
            $this->refresh();
            throw $e;
        }
        $this->_db->enableAutocommit();

        if(!$this->_db->error()){;
            return true;
        }
        else{
            $this->addError(Text::get("DATABASE_ERROR"));
            return false;
        }
    }

    /**
     * Checks if the books is feasible in term of people
     * @param $record
     * @return bool
     * @throws Exception
     */

    public function book(&$record){
        $this->addStops($record);
        $this->sortStops();
        $start = $record['start'];
        $end = $record['end'];
        $people = $record['people'];

        $record['start'] = strtoupper($start);
        $record['end'] =strtoupper($end);

        $checkstart = strtoupper($start);
        $checkend = strtoupper($end);

        if(strcmp($checkstart, $checkend) > 0){
            $this->addError(Text::get("BOOKING_UNAVAILABLE_ROUTE"));
            throw new Exception("BOOKING_UNAVAILABLE_ROUTE");
        }
        if($this->maxPeopleBetween($start, $end) + $people > $this->_shuttleCapacity){
            throw new Exception("BOOKING_SITS_UNAVAILABLE");
        }else{
            return true;
        }
    }

    /**
     * Find max people between two stops
     * @param $start
     * @param $end
     * @return int
     */

    public function maxPeopleBetween($start, $end){
        $max = 0;
        $get = 0;
        foreach ($this->_stops as $key => $value){
            if($key === $start){
                $get = 1;
            }
            if($key === $end){
                return $max;
            }
            if($get){
                $passengers = $value->getPassengers();
                if($passengers > $max){
                    $max = $passengers;
                }
            }
        }
    }
    /**
     * Sort stops array
     */

    public function sortStops(){
        usort($this->_stops, array($this, "cmp"));
    }

    /**
     * Populates the _stops array
     * @param $res
     */

    public function populate($res){
        foreach ($res as $record){
            $this->addStops($record);
        }
        $this->sortStops();
        foreach ($res as $record){
            $this->populateStops($record);
        }
    }

    /**
     * Adds a booking (start and end stop)
     * @param $record
     * @return bool
     */

    public function addStops($record){
        if(isset($record['start']) && isset($record['end'])){
            $this->_bookings ++;
            // from
            if(!($s = $this->isPresent($record['start']))) {
                $stop = new Stop(strtoupper($record['start']));
                $this->_stops[$record['start']] = $stop;
            }
            // to
            if(!($s = $this->isPresent(strtoupper($record['end'])))) {
                $stop = new Stop($record['end']);
                $this->_stops[$record['end']] = $stop;
            }
            return true;
        }
        else{
            $this->addError(Text::get("DATABASE_EMPTY_RECORD"));
            return false;
        }
    }

    /**
     * Populates the stops info
     * @param $record
     */

    public function populateStops($record){
        $populate = false;
        foreach ($this->_stops as $key => $value){
            if($value->getName() === $record['start']){
                $populate = true;
            }
            if($value->getName() === $record['end']){
                $value->addOff($record['email'], 0);
                break;
            }
            if($populate){
                $value->addBooking($record['email'], $record['people']);
            }
        }
    }

    /**
     * Deletes booking from db
     * @param $email
     * @return bool
     */

    public function delete($email){
        if(1){
            $this->_db->delete('bookings', array("email", "=", $email), false);
            if($this->_db->error()){
                $this->addError(Text::get("DATABASE_ERROR"));
                $this->refresh();
            } else{
                return true;
            }
        }else{
            $this->addError(Text::get("BOOKING_NOT_FOUND"));
        }
        return false;
    }

    /**
     * Adds error
     * @param $string
     */
    public function addError($string){
        $this->_errors[$string] = $string;
    }

    /**
     * Checks if user has already booked
     * @param $email
     * @return bool
     */
    public function hasBooked($email){
        $this->_db->select('bookings', array('email', "=", $email), false);
        if(!$this->_db->error()){
            if(($booking = $this->_db->getFirst()) != null){
                return $booking;
            }
            else{
                return false;
            }
        }
        $this->addError(Text::get("DATABASE_ERROR"));
        return false;
    }

    /**
     * Checks if a stop is present in the array
     * @param $stopName
     * @return bool|mixed
     */

    public function isPresent($stopName){
        foreach ($this->_stops as $s){
            if($s->getName() == $stopName) {
                return $s;
            }
        }
        return false;
    }

    /**
     * @param Stop $a
     * @param Stop $b
     * @return int
     * to call with usort($your_data, array('YOUR_CLASS_NAME','FUNCTION_NAME'));
     */
    public static function cmp($a, $b) {
        return strcmp($a->getName(), $b->getName());
    }

    /**
     * Getter per le stops
     * @return array
     */
    public function getItinerary(){
        return $this->_stops;
    }

    /**
     * Prints itinerary, if $users is true, prints also the list of users in a stop.
     * @param $users
     */
    public function printItinerary($users, $email = null){
        $previous = null;

        if(isset($email)){
            $hasBooked = $this->hasBooked($email);
        }
        foreach ($this->getItinerary() as $key => $value){
            if(isset($previous)){
                echo "<tr>";

                if(isset($email) && $hasBooked){

                    if(($previous->getName() == $hasBooked['start'])) {
                        echo "<td style='border: solid red 3px'>" . escape($previous->getName()) . "</td>";
                    }
                    else{
                        echo "<td>" . escape($previous->getName()) . "</td>";
                    }

                    if(($value->getName() == $hasBooked['end'])) {
                        echo "<td style='border: solid red 3px'>" . escape($value->getName()) . "</td>";
                    }
                    else{
                        echo "<td>" . escape($value->getName()) . "</td>";

                    }
                }else {
                    echo "<td>" . escape($previous->getName()) . "</td><td>" .
                        escape($value->getName()) . "</td>";
                }

                $e = $previous->getPassengers();
                echo "<td>" . $previous->getPassengers() . "</td>";

                if(isset($email)) {
                    echo "<td> <p id=\"par\">";
                    if($e == 0){
                        echo Text::get('BOOKING_NO_PASSENGERS');
                    }
                    foreach ($previous->getEmails() as $k => $v) {
                        if (!array_key_exists($k, $previous->getOff())) {
                            if ($users) {
                                echo escape($k) . "[" . escape($v) . "] ";
                            }
                        }
                    }
                    echo "</p></td>";
                }
                //echo "<br>";
            }
            $previous = $value;
            echo "</tr>";
        }
    }

    /**
     * Prints stops for the dropdown menu
     */

    public function printStops(){
        foreach ($this->_stops as $stop) {
            $name = escape($stop->getName());
                echo "<option value=\"" . $name . "\">";
        }
    }

}