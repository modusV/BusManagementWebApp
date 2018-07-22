<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 24/06/2018
 * Time: 23:18
 */

function printNumberOptions($value){
    for ($i = 1; $i < $value + 1; $i++){
        echo "<option value=\"" . $i . "\">";
    }
}

function printDeleteButton($s){

    echo "<p> If you want, delete your booking </p><br>";
    echo "<form action = \"\" method = \"post\">
               <input type=\"hidden\" name=\"token\" value=\"" . $s ."\">
                   <button name=\"delete\" value=\"delete\" class=\"button\" id=\"delete\">Delete</button>
                </form>";

}

function notify($type, $error) {

    echo '<div id="snackbar" ';
    switch ($type){
        case "error":
            echo 'style="background-color:red"';
            break;
        case "success":
            echo 'style="background-color:#12D601"';
            break;
        default:
            echo 'style="background-color:grey"';
            break;
    }
    echo '>' . $error . ' </div>';
    echo '<script type="text/javascript">
         myFunction();
        </script>';
}


