<?php
//session start
session_start();

//check if logged
if(isset($_SESSION['user_id']))
{

    //redirect to main member page
    header("Location: /member.php");

}

//lib
require_once __DIR__ ."/db.php";

//calling a class
try
{

	$user = new User();

}
catch(Exception $e)
{

	echo "<i>Database Error ".$e->getMessage()."</i><br>";

	exit;

}

//if login button been pressed
if(isset($_POST["login_summit"]))
{

    //fields that are required
    $data = array
    (
        'email'  =>   $_POST["email"],  
        'password'  =>   $_POST["password"]  
    );

    //check if all required fields are not empty
    if($user->Required($data) == true)
    {

        //calling a login fun
        try
        {

            $user->Login($_POST["email"], $_POST["password"]);

        }
        catch(Exception $e)
        {

            $message = $e->getMessage();

        }

    }
    else
    {

        //sets an error msg
        $message = $user->getError();

    }

}

?>

<!DOCTYPE html>  
<html lang="">
    <head>
        <title>Tasks with login</title> 
    </head>
    <body>
        <div>
            <h2>Login:</h2>
            <div>
                <?php

                    //if error print it
                    if(isset($message))
                    {

                        echo '<i class="">'.$message.'</i><br>';

                    }

                ?>
            </div>
            <div>
                <form action="" method="post">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required style="width: 10em"><br>
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required minlength="6" style="width: 10em">
                    <input type="submit" name="login_summit" value="Login!">
                </form>
            </div>
            <div>
                <p><a href="/register.php">Register</a></p>
                <p><a href="/index.php">Index</a></p>
            </div>
        </div>
    </body>
</html>