<?php
    require_once 'Core/init.php';



    if(Input::exists()){
        if(Token::check(Input::get('token'))) {

            $validate = new Validation();
            $validation = $validate->check($_POST, array(
                "name" => [
                    "required" => true,
                    "max_characters" => 30,
                ],

                "email" => [
                    "filter" => "email",
                    "required" => true,
                    "unique" => "users",
                    "max_characters" => 30,
                ],
                "password" => [
                    "min_characters" => 1,
                    "required" => true,
                    "secure" => true
                ],
                "password_repeat" => [
                    "matches" => "password",
                    "required" => true
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
                /*
                Session::flash("REGISTER_USER_CREATED", Text::get("REGISTER_USER_CREATED"));
                header('Location: index.php');
                */
                $user = new User();

                $salt = Hash::salt();

                try{
                    $user->create(array(
                            'name' => Input::get('name'),
                            'email' => Input::get('email'),
                            'password' => Hash::make(Input::get('password'), $salt),
                            'salt' => $salt,
                            'joined' => date('Y-m-d H:i:s'),
                            'group' => 1,
                    ));


                    notify("success", Text::get("REGISTER_USER_CREATED"));

                }catch (Exception $e){
                    // redirect user to page "error in registration"
                    notify("error", Text::get($e->getMessage()));
                }
            }
        }
        else{
            notify("error", Text::get("INPUT_INCORRECT_CSRF_TOKEN"));
        }
    }
?>

    <html>
    <head>
        <link rel="stylesheet" type="text/css" href="Layouts/Register.css">
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
                    <p id="welcome">Already registered? Just:</p>
                    <div>
                        <ul>
                            <li><a href="login.php" class="button">login</a></li>
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
                    <label for="name" class="tooltip">Name
                        <span class="tooltiptext">Insert your name</span>
                    </label>
                    <input type="text" name="name" id="name" value="<?php echo escape(Input::get('name')); ?>" autocomplete="off">
                </div>
                <br>
                <div class="field">
                    Enter your
                    <label for="email" class="tooltip">Email
                        <span class="tooltiptext">Enter your email address</span>
                    </label>
                    <input type="email" name="email" id="email" value="<?php echo escape(Input::get('email')); ?>">
                </div>
                <br>
                <div class="field">
                    Choose a
                    <label for="password" class="tooltip">password
                        <span class="tooltiptext">At least a lower case, an upper-case or a number</span>
                    </label>
                    <input type="password" pattern="^(?=.*[a-z])(?=.*[A-Z\d]).+$" name="password" id="password">
                </div>
                <br>
                <div class="field">
                    <label for="password">Enter your password again</label>
                    <input type="password" name="password_repeat" value="" id="password_repeat">
                </div>
                <br>

                <input type="hidden" name="token" value="<?php echo Token::generate() ?>">
                <div>
                    <input type="submit" value="Register" class="button">
                </div>


            </form>
        </article>
    </section>
    </body>

    <footer>
        <p>Lorenzo Santolini, All rights reserved.</p>
    </footer>
</html>





