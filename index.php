<?php
//session start
session_start();
?>
<!DOCTYPE html>  
<html lang="">
    <head>
        <title>Tasks with login</title>
    </head>
    <body>
        <h1>Tasks with login:</h1>
        <?php
            //check if logged
            if(isset($_SESSION['user_id'])){
                ?> 
                    <body>
                        <h2>Welcome (<?php echo $_SESSION['user_name']; ?>)</h2>
                            <div>
                                <ul>
                                    <li id = "" class = "">
                                        <a href="/member.php">Member</a>
                                    </li>
                                    <li id = "" class = "">
                                        <a href="/logout.php">Log out</a>
                                    </li>
                                </ul>
                            </div>
                    </body>
                <?php
            }else{
                ?>
                    <h2>(Go on, register. Perhaps, the cool one with acc already?)</h2>
                    <div>
                        <ul>
                            <li id = "" class = "">
                                <a href="/login.php">Login</a>
                            </li>
                            <li id = "" class = "">
                                <a href="/register.php">Register</a>
                            </li>
                        </ul>
                    </div>
                <?php
            }
        ?>
    </body>
</html>




    