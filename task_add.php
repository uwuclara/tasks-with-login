<?php
//session start
session_start();

//check if logged
if(!isset($_SESSION['user_id'])){
    header("Location: /login.php");
}

//lib
require_once('db.php');

try{
	//call a class
	$task = new Task();
}catch(Exception $e){
	echo '<i class="">Database Error: '.$e.'</i><br>';
	exit;
}

//if edit button been pressed
if(isset($_POST["task_add_summit"])){
    try{
		//if user is logged in
        if(isset($_SESSION['user_id'])){
			//replace empty with null
            if($_POST["name"] == ""){
                $postname = NULL;
            }else{
                $postname = $_POST["name"];
            }
            if($_POST["description"] == ""){
                $decs = NULL;
            }else{
                $decs = $_POST["description"];
            }
            if($_POST["deadline"] == ""){
                $deadline = NULL;
            }else{
                $deadline = strtotime($_POST["deadline"]);
            }
            if(isset($_FILES['upload']["name"]) AND $_FILES['upload']["name"] !== ""){
                $upload = $_FILES['upload'];
            }else{
                $upload = NULL;
            }
			//call a fun
            $task->AddTask($postname, $decs, $deadline, $upload);
            ?>
                <script>    
				//change an url and reload
                    if(typeof window.history.pushState == 'function'){
                        window.history.pushState({}, "Hide", "/member.php");
                        location.reload();
                    }
                </script> 
            <?php
        }else{
            header("Location: /login.php");
        }
    }catch(Exception $e){
        $message = $e->getMessage();
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
						document.getElementById("task_add").disabled = true;
					}else{
						document.getElementById("task_add").disabled = false;
					}
				} 
			} 
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
                    if(isset($message)){
                        echo '<i class="">'.$message.'</i><br>';
                    }
                ?>
            </div>
            <div>
                <form action="" method="post" enctype="multipart/form-data">
                    <label for="name">Name:</label>
                    <input type="text" name="name" id="name" required style="width: 10em"><br>
                    <label for="description">Description:</label>
                    <input type="text" name="description" id="description" style="width: 10em"><br>
                    <label for="deadline"> Deadline:</label>
                    <input type="date" name="deadline" id="deadline" min="<?php echo date("Y-m-d"); ?>" max="2038-01-19"><br>
                    <label for="file"> Upload Max(2 MB):</label>
                    <input type="file" id="file" name="upload" accept="image/*, .txt, .xls,.xlsx, .pdf, .doc, .docx" onchange="FileSize()"><br>
                    <input type="submit" id="task_add" name="task_add_summit" value="Add!">
                </form>
            </div>
            <div>
                <p><a href="/index.php">Index</a></p>
                <p><a href="/member.php">Member</a></p>
            </div>
        </div>
    </body>
</html>