<?php
//session start
session_start();

//check if logged
if(!isset($_SESSION['user_id']))
{

    header("Location: /login.php");

}

//check if task been selected
if(!isset($_POST['file_url']) OR $_POST['file_url'] == "")
{

    header("Location: /member.php");

}

//lib
require_once __DIR__ ."/db.php";

try
{

	//calling a class
	$task = new Task();

}
catch(Exception $e)
{

	echo '<i class="">Database Error: '.$e->getMessage().'</i><br>';

	exit;

}

//check if button been pressed
if(isset($_POST['download']) AND isset($_POST['file_url']))
{

    try
    {

        //downloads the file
		$task->DownloadFile($_POST['file_url']);

    }
    catch(Exception $e)
    {

        $message = $e->getMessage();

    }

}
else
{

    header("Location: /member.php");

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
                //print an exception if error
                if(isset($message))
                {

                    echo '<i class="">'.$message.'</i><br>';

					header("refresh:3;url=member.php");

                }
            ?>
        </div>
    </body>
</html>