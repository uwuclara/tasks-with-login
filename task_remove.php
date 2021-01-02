<?php
//session start
session_start();

//check if logged
if(!isset($_SESSION['user_id']))
{

    header("Location: /login.php");

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

//if post is set from button
if(isset($_POST["remove_button"]))
{

    //calling rem fun
    if(isset($_POST["task_id"]))
    {

        try
        {

            $task->RemoveTask($_POST["task_id"]);

            //redirect to member page
            header("Location: /member.php");

        }
        catch(Exception $e)
        {

            $message = $e->getMessage();

        }

    }
    else
    {

        $message = "Task not selected";

    }

}
else
{

    //redirect to main log in page
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

                }

            ?>
        </div>
    </body>
</html>