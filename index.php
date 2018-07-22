<?php

/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 17:56
 */

require_once 'Core/init.php';

//requireHTTPS();
/*
if(Session::exists("REGISTER_USER_CREATED")) {
    echo Session::flash("REGISTER_USER_CREATED");
}
*/

?>

<?php
//echo Session::get(Config::get('session/session_name'));

$user = new User();

$s = Session::getInstance();
if(!$s->getStatus()){
    Redirect::to('logout.php');
}


if($user->isLoggedIn()){
        $i = new Itinerary();

        //$i->insert('pinso', 'fermi to', 'toronto', '2');
        ?>
        <head>
            <link rel="stylesheet" type="text/css" href="Layouts/Index.css">
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

                <table>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                        <th>Passengers</th>
                        <th>Passengers Id</th>
                        <?php
                        $i->printItinerary(true, $user->data()['email']);
                        ?>
                </table>
            </article>
        </section>
    </body>


        <footer>
            <p>Lorenzo Santolini, All rights reserved.</p>
        </footer>
<?php

}else {
    Redirect::to('welcome.php');
}

?>





