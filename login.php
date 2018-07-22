<?php

require_once 'Core/init.php';

$user = new User(); // current user
//echo $user->data()['email'];
//$user = new User(6); // another user


if($user->isLoggedIn()){
    Redirect::to("index.php");
}

if(Input::exists()){
    if(Token::check(Input::get('token'))){
        $validation = new Validation();
        /** TODO check lengths $validate */
        $validate = $validation->check($_POST, array(
            'email' => array(
                'required' => true,
            ),
            'password' => array(
                'required' => true,
            ),
        ));

        if($validation->passed()){
            $user = new User();
            $remember = (Input::get('remember') === 'on') ? true : false;
            $login = $user->login(Input::get('email'), Input::get('password'), $remember);


            if($login){
                Redirect::to('index.php');
            } else{
                notify("error", Text::get("LOGIN_INVALID_PASSWORD"));
            }
        }else{
            notify("error", Text::get("LOGIN_USER_NOT_FOUND"));
        }
    }else {
        notify( "error", Text::get("INPUT_INCORRECT_CSRF_TOKEN"));
    }
}

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
                <div>
                    <p id="welcome">To create an account:</p>
                    <div>
                        <ul>
                            <li><a href="register.php" class="button">Register</a></li>
                            <p><br></p>
                        </ul>
                    </div>
                </div>

                <div>
                    <div>
                        <a href="index.php" class="button">Home</a>
                    </div>
                </div>
                 <br>
                <br>
                <noscript>Looks like you have javascript disabled,
                        some functions may not be available</noscript>
            </nav>
        </div>

        <article>
            <h1>Login with your credentials:</h1>
            <br>

            <form action="<?php $_PHP_SELF ?>" method="POST">

                <div class="field">
                    <label for="email">Enter your Email</label>
                    <input type="email" name="email" id="email" value="<?php echo escape(Input::get('email')); ?>">
                </div>
                <br>
                <div class="field">
                    <label for="password">Enter your password</label>
                    <input type="password" name="password" id="password" autocomplete="off">
                </div>
                <br>
                <div class="field">
                    <label for="remember">
                        <input type="checkbox" name="remember" id="remember"> Remember me
                    </label>
                </div>
                <br>
                <input type="hidden" name="token" value="<?php echo Token::generate() ?>">
                <div>
                    <input type="submit" value="Login" class="button">
                </div>


            </form>
        </article>
    </section>
    </body>

    <footer>
        <p>Lorenzo Santolini, All rights reserved.</p>
    </footer>
</html>
