<?php
//session start
session_start();

//check if logged
if(!isset($_SESSION['user_id']))
{

    header("Location: /login.php");

}

//check if task been selected
if(!isset($_POST['task_id']))
{

    header("Location: /member.php");

}

//lib
require_once __DIR__ ."/db.php";

try
{

	//call a class
	$task = new Task();

	//fetching the file url
	$file_url = $task->GetFileUrl($_POST['task_id']);

}
catch(Exception $e)
{

	echo '<i class="">Database Error: '.$e->getMessage().'</i><br>';

	exit;

}

//if edit button was pressed
if(isset($_POST["task_edit_summit"]))
{

    //if user is logged in
    if(isset($_SESSION['user_id']))
    {

        //replace empty with null
        if($_POST["name"] == "")
        {

            $postname = NULL;

        }
        else
        {

            $postname = $_POST["name"];

        }

        if($_POST["description"] == "")
        {

            $decs = NULL;

        }
        else
        {

            $decs = $_POST["description"];

        }

        if($_POST["deadline"] == "")
        {

            $deadline = NULL;

        }
        else
        {

            $deadline = strtotime($_POST["deadline"]);

        }

        if(isset($_FILES['upload']["name"]) AND $_FILES['upload']["name"] !== "")
        {

            $upload = $_FILES['upload'];

        }
        else
        {

            $upload = NULL;

        }

        try
        {

            //call a fun
            $task->EditTask($_POST['task_id'], $postname, $decs, $deadline, $upload);

            ?>
            <script>
                //change an url and reload
                if(typeof window.history.pushState == 'function')
                {
                    window.history.pushState({}, "Hide", "/member.php");
                    location.reload();
                }
            </script>
            <?php

        }
        catch(Exception $e)
        {

            $message = $e->getMessage();

        }

    }
    else
    {

        //login page
        header("Location: /login.php");

    }

}

?>
<!DOCTYPE html>  
<html lang="">
	<script> 
		FileSize = () => { 
			const fi = document.getElementById('file'); 
			if (fi.files.length > 0) { 
				for (let i = 0; i <= fi.files.length - 1; i++) {
					const fs = fi.files.item(i).size;
					const file = Math.round((fs / 1024));
					//more than 2mb disables the button / client validation for file size
					if(file > 2048) { 
						alert("The file size is too big. Max 2 MB!");
						document.getElementById("task_edit").disabled = true;
					}else{
						document.getElementById("task_edit").disabled = false;
					}
				} 
			} 
		} 
	</script>
	<script> 
		Disable_Download = (id) => {
			document.getElementById(id).disabled = true;
		} 
	</script>
    <head>
        <title>Tasks with login</title>
    </head>
    <body>
        <div>
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
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="task_id" name="task_id" value="<?php echo $_POST['task_id']; ?>">
                    <label for="name">Name:</label>
                    <input type="text" name="name" id="name" value="<?php echo $_POST["name"]; ?>" required style="width: 10em"><br>
                    <label for="description">Description:</label>
                    <input type="text" name="description" id="description" value="<?php if(!empty($_POST['description'])) echo $_POST["description"]; ?>" style="width: 10em"><br>
                    <label for="deadline">Deadline:</label>
                    <input type="date" name="deadline" id="deadline" value="<?php if(!empty($_POST['deadline'])) echo date("Y-m-d", $_POST['deadline']); ?>" min="<?php echo date("Y-m-d"); ?>" max="2038-01-19"><br>
                    <label for="file">Upload Max(2 MB):</label>
                    <input type="file" id="file" name="upload" onchange="FileSize()"><br>
                    <input type="submit" id="task_edit" name="task_edit_summit" value="Add!">
                </form>
                File (Uploading file will overwrite this):   					
					<form action="/download.php" method="post"><input type="hidden" id="file_url" name="file_url" value="<?php if(isset($file_url) AND $file_url !== false) echo $file_url;?>"><input type="submit" name="download" id="<?php echo $_POST['task_id'];?>" value="<?php if(isset($file_url) AND $file_url !== false){ echo ltrim(strstr($file_url, '_'), '_');}else{ echo 'none';}?>"></form><?php if(!isset($file_url) OR $file_url == false) echo '<script type="text/javascript"> Disable_Download('.$_POST['task_id'].'); </script>';?>
					<?php if(isset($file_url) AND $file_url !== false){?>
                    <form action="/file_remove.php" method="post"><input type="hidden" id="task_id" name="task_id" value="<?php echo $_POST['task_id']; ?>"><input type="hidden" id="file_url" name="file_url" value="<?php echo $file_url;?>"><input type="submit" name="file_remove" value="remove"></form><?php }?><br>
            </div>
            <div>
                <p><a href="/index.php">Index</a></p>
                <p><a href="/member.php">Member</a></p>
            </div>
        </div>
    </body>
</html>