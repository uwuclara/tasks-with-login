<?php
//session start
session_start();

//check if user is logged
if(!isset($_SESSION['user_id']))
{

    header("Location: /login.php");

}

//lib
require_once __DIR__ ."/db.php";

try
{

	//calling a class
	$user = new User();

}
catch(Exception $e)
{

	echo '<i class="">Database Error: '.$e->getMessage().'</i><br>';
	exit;

}

//calling logout fun
try
{

    $user->Logout();

}
catch(Exception $e)
{

    $message = $e->getMessage();

}

?>

<!DOCTYPE html>  
<html lang="">
    <head>
        <title>Tasks with login</title>
    </head>
    <body>
        <div>
            <?php

                //if there was an exception print it
                if(isset($message))
                {

                    echo '<i class="">'.$message.'</i><br>';

                }

            ?>
        </div>
    </body>
</html>