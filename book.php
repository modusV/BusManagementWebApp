<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 24/06/2018
 * Time: 20:23
 */

require_once 'Core/init.php';

$user = new User(); // current user

$s = Session::getInstance();
if(!$s->getStatus()){
    Redirect::to('logout.php');
}

if($user->isLoggedIn()) {
    if (Input::exists()) {
        if (Token::check(Input::get('token'))) {
            $i = new Itinerary();

            if(Input::get('delete') != ''){
                $i->delete($user->data()['email']);
                notify("success", Text::get("BOOKING_DELETE_SUCCESSFUL"));
            }

            $validate = new Validation();
            $validation = $validate->check($_POST, array(
                "start" => [
                    "required" => true,
                    "greater" => "end",
                    "max_characters" => 30,
                ],
                "end" => [
                    "required" => true,
                    "max_characters" => 30,
                ],
                "people" => [
                    "required" => true,
                    "numeric" => true,
                ],
            ));

            $toprint = null;
            foreach ($validation->errors() as $error) {
                foreach ($error as $e) {
                    $toprint .= $e . "<br>";
                }
            }
            if(isset($toprint)){
                notify("error", $toprint);
            }

            if ($validation->passed()) {
                try {
                    $i->insert($user->data()['email'], Input::get('start'), Input::get('end'), Input::get('people'));
                    notify("success", Text::get("BOOKING_SUCCESSFUL"));
                }catch (Exception $e){
                    notify("error", Text::get($e->getMessage()));
                }
            }
        } else {
            notify("error", Text::get("INPUT_INCORRECT_CSRF_TOKEN"));
        }
    }
}

?>

<?php
//echo Session::get(Config::get('session/session_name'));

//echo $user->data()['email'];
//$user = new User(6); // another user

if($user->isLoggedIn()){
    $i = new Itinerary();
    ?>
    <head>
        <link rel="stylesheet" type="text/css" href="Layouts/Book.css">
        <link rel="stylesheet" type="text/css" href="Layouts/Notifications.css">
        <script src="Javascript/notifications.js"></script>
        <script src="Javascript/checks.js"></script>

        <script>
            checkCookie();
        </script>
    </head>
    <header>
        <h1>Shuttle bus service</h1>
    </header>
    <body>
        <section>
            <div id="navigation">
                <nav>
                    <ul>
                        <p id="welcome">Welcome <?php echo escape($user->data()['name']); ?>!</p>
                        <li><a href="index.php" class="button">Home</a></li>
                        <p><br></p>
                        <li><a href="book.php" class="button">Book</a></li>
                        <p><br></p>
                        <li><a href="update.php" class="button">Update details</a></li>
                        <p><br></p>
                        <li><a href="logout.php" class="button">Log out</a></li>
                        <br>
                        <br>
                        <noscript>Looks like you have javascript disabled,
                        some functions may not be available</noscript>
                    </ul>
                </nav>
            </div>

            <article>
                <h1>Bus itinerary</h1>
                <p>Current bus track:</p>

                <form action="<?php $_PHP_SELF ?>" method="POST">
                    <div id="fields">
                        <label>Choose a starting point or insert a new one:
                            <input name="start" id="start" list="start_stops"/>
                        </label>

                        <datalist id="start_stops">
                                <?php
                                //$i->printItinerary(true);
                                $i->printStops();
                                ?>
                        </datalist>
                    </div>

                    <div id="fields">
                        <label>Choose an ending point or insert a new one:
                            <input name="end" id="end" list="start_stops"/>
                        </label>
                    </div>

                    <div id="fields">
                        <label>Choose how many passengers:
                            <input name="people" id="people" list="passengers"/>
                        </label>
                        <datalist id="passengers">
                            <?php
                             printNumberOptions(10);
                            ?>
                        </datalist>
                    </div>

                    <br><br>
                    <div>
                        <input type="hidden" name="token" value="<?php $s = Token::generate(); echo $s; ?>">
                        <input type="submit" class="button">
                    </div>
                </form>


                <div>
                    <?php
                    if(($booking = $i->hasBooked($user->data()['email']))){
                        echo "<br><br><p> <h1>Your booking:</h1>";
                        echo "Start: " . escape($booking['start']) . "   ";
                        echo "Stop:  " . escape($booking['end']) . "</p>";
                        printDeleteButton($s);
                    }
                    ?>
                </div>

            </article>
        </section>
    </body>
    <footer>
        <p>Lorenzo Santolini, All rights reserved.</p>
    </footer>
    <?php

}else{
    Redirect::to('welcome.php');
}

?>