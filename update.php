<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 17:57
 */

require_once 'Core/init.php';

$user = new User();


$s = Session::getInstance();
if(!$s->getStatus()){
    Redirect::to('logout.php');
}

if(!$user->isLoggedIn()){
    Redirect::to('welcome.php');
}

if(Input::exists()){
    if(Token::check(Input::get('token'))){
        $validation = new Validation();
        $validate = $validation->check($_POST, array(
           'name' => array(
               'required' => true,
               'min_characters' => 3,
               'max_characters' => 30,
           ),
        ));


        if($validation->passed()){

            try{
                $user->update(array('name' => Input::get('name')));
                notify("success", Text::get("USER_UPDATE_SUCCESS"));

            }catch (Exception $exception){
                die($exception->getMessage());
            }

        }else{
            $toprint = null;
            foreach ($validation->errors() as $error) {
                foreach ($error as $e) {
                    $toprint .= $e . "<br>";
                }
            }
            if(isset($toprint)){
                notify("error", $toprint);
            }
        }
    }
}

?>


<?php
//echo Session::get(Config::get('session/session_name'));

$user = new User(); // current user
//echo $user->data()['email'];
//$user = new User(6); // another user

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
            <h1>User Info</h1>

            <form action="" method="post">
                <div class="fields">
                    <label for="name">Update your username:
                        <input type="text" name="name" id="name" value="<?php echo escape($user->data()['name']) ?>">
                    </label>
                </div>
                <br>
                <br>
                <div>
                    <input type="submit" value="Update" class="button">
                    <input type="hidden" name="token" value="<?php echo Token::generate()?>">
                </div>
            </form>

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
/*
if(!Token::check(Input::get('token'))) {
    Redirect::to('Includes/Errors/404.php');
    die();
}
*/

?>


