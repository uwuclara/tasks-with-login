<?php
//session start
session_start();

//check if logged in
if(isset($_SESSION['user_name'])){
    header("Location: /member.php");
}

//lib
require_once('db.php');

try{
	//calling a class
	$user = new User();
}catch(Exception $e){
	echo '<i class="">Database Error: '.$e.'</i><br>';
	exit;
}

//if register button been pressed
if(isset($_POST["register_summit"])){    
    //required
    $data = array(  
        'first name'  =>   $_POST["first_name"],
        'last name'  =>   $_POST["last_name"],
        'email'  =>   $_POST["email"],
        'password'  =>   $_POST["password"],
        'password_repeat'  =>   $_POST["password_repeat"] 
    ); 
    //check if required is not empty
    if($user->Required($data) == true){
        //calling a register fun
        try{
            $user->Register($_POST["first_name"], $_POST["last_name"], $_POST["email"], $_POST["password"], $_POST["password_repeat"]);
        }catch(Exception $e){
            $message = $e->getMessage();
        }  
    }else{  
        //if error
        $message = $user->getError();
    }  
}
?>
<!DOCTYPE html>  
<html lang="">
    <head>
        <title>Tasks with login</title>
        <script>    
            //just to clear
            if(typeof window.history.pushState == 'function'){
                window.history.pushState({}, "Hide", "/register.php");
            }
        </script> 
    </head>
    <body>
        <div>
            <h2>Register:</h2>
            <div>
                <?php 
                    //if error print it
                    if(isset($message)){
                        echo '<i class="">'.$message.'</i></br>';
                    }
                ?>
            </div>
            <div>
                <form action="" method="post">
                    <label for="first_name">First Name:</label>
                    <input type="text" name="first_name" id="first_name" minlength="2" maxlength="30" style="width: 10em"><br>
                    <label for="last_name">Last Name:</label>
                    <input type="text" name="last_name" id="last_name" minlength="2" maxlength="30" style="width: 10em"><br>
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" style="width: 10em"><br>
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required minlength="6" style="width: 10em"><br>
                    <label for="password_repeat">Password repeat:</label>
                    <input type="password" name="password_repeat" id="password_repeat" required minlength="6" style="width: 10em">
                    <input type="submit" name="register_summit" value="Register!">
                </form>
            </div>
            <div>
                <p><a href="/login.php">Login</a></p>
                <p><a href="/index.php">Index</a></p>
            </div>
        </div>
    </body>
</html>