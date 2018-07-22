<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 28/06/2018
 * Time: 19:20
 */

require_once 'Classes/Cookie.php';
require_once 'Classes/Redirect.php';


Cookie::put("test", true, time() + 2000);
if(Cookie::count() >0){
    Redirect::to('index.php');
}

?>

<html>
<head>
    <link rel="stylesheet" type="text/css" href="Layouts/Index.css">
    <link rel="stylesheet" type="text/css" href="Layouts/Notifications.css">
    <script src="Javascript/notifications.js"></script>
    <script src="Javascript/checks.js"></script>

</head>
<header>
    <h1>Shuttle bus service</h1>
</header>

<section>
    <div id="navigation">
        <nav>
                <p id="welcome">Ops, seems like there is some error!</p>
        </nav>
    </div>

    <article>
        <h1>Please enable cookies and Javascript for a correct navigation</h1>
        <br>
        <p>Back to home:</p>
        <br>
        <a href="welcome.php" class='button'>Home</a>
    </article>
</section>

<footer>
    <p>Lorenzo Santolini, All rights reserved.</p>
</footer>
</html>