<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 28/06/2018
 * Time: 18:31
 */

require_once 'Core/init.php';


$i = new Itinerary();
?>

<html>
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
                    <ul id="ulls">
                        <p id="welcome">Welcome! You need to:</p>
                        <li><a href="login.php" class='button'>login</a></li>
                        <p><br></p>
                        <li><a href="register.php" class='button'>register</a></li>
                        <p><br></p>
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
                        <?php
                        $i->printItinerary(false);
                        ?>
                </table>
            </article>
        </section>
    </body>

    <footer>
        <p>Lorenzo Santolini, All rights reserved.</p>
    </footer>
</html>
