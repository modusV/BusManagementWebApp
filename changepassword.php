<?php
/**
 * Created by PhpStorm.
 * User: lorenzosantolini
 * Date: 15/06/2018
 * Time: 17:57
 */

require_once 'Core/init.php';

$user = new User();


if(!$user->isLoggedIn()){
    Redirect::to('index.php');
}

if(Input::exists()) {
    if (Token::check(Input::get('token'))) {
        $validation = new Validation();
        $validate = $validation->check($_POST, array(
            'password' => array(
                "required" => true,
            ),
            'password_new' => array(
                "min_characters" => 2,
                "required" => true,
                "secure" => false

            ),
            'password_new_again' => array(
                'matches' => 'password_new',
                'required' => false
            )
        ));

        if ($validation->passed()) {
            if(Hash::make(Input::get('password'), $user->data()['salt']) !== $user->data()['password']){
                echo Text::get("LOGIN_INVALID_PASSWORD");
            } else {
                echo "boh";
                /*
                $salt = Hash::salt();
                $user->update(array(
                    'password' => Hash::make(Input::get('password_new'), $salt),
                    'salt' => $salt,
                ));
                Session::flash('home', "USER_PWD_UPDATED");
                Redirect::to('index.php');
                */

                /*
                if ($user->update(array(
                    'group' => 2,
                    'name' => 'gino',
                ))) {
                    Session::flash("USER_PWD_UPDATED");
                    Redirect::to('index.php');
                }*/

            }
        } else {
            foreach ($validation->errors() as $error) {
                foreach ($error as $r)
                    echo $r, '<br>';
            }
        }
    }

}


?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="Layouts/Index.css">
    </head>
    <header>
        <h1>Shuttle bus service</h1>
    </header>
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
                    <li><a href="changepassword.php" class="button">Change password</a></li>
                    <p><br></p>
                    <li><a href="logout.php" class="button">Log out</a></li>
                </ul>
            </nav>
        </div>

        <article>
            <h1>User Info</h1>

            <form action="" method="post">
                <div class="fields">
                    <label for="password">Current password</label>
                    <input type="password" name="password" id="password">
                </div>
                <br>
                <div class="fields">
                    <label for="password_new">New password</label>
                    <input type="password" pattern="^(?=.*[a-z])(?=.*[A-Z\d]).+$" name="password_new" id="password_new">
                </div>
                <br>
                <div class="fields">
                    <label for="password_new_again">New password again</label>
                    <input type="password" name="password_new_again" id="password_new_again">
                </div>
                <br>
                <div class="fields">
                    <input type="submit" value="Change" class="button">
                    <input type="hidden" name="token" value="<?php echo Token::generate()?>">
                </div>
                <br>
            </form>
        </article>
    </section>

    <footer>
        <p>Lorenzo Santolini, All rights reserved.</p>
    </footer>
</html>

