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
	//calling a class
	$task = new Task();
	//getting user's tasks
	$user_tasks = $task->FetchTasks();
}catch(Exception $e){
	echo '<i class="">Database Error: '.$e.'</i><br>';
	exit;
}

$num_task = 0;

?>
<!DOCTYPE html>  
<html lang="">
	<script>
        Disable_Download = (id) => {
			document.getElementById(id).disabled = true;
		} 
	</script>
    <head>
        <title>Tasks with login</title>
        <style>
            thead {
                color: #ff0000;
            }
            table, th, td {
                border: 1px solid #000000;
            }
            .inline {
                display: inline;
            }
        </style>
    </head>
    <body>
        <div>
            <h1>Account info:</h1>
            <ul>
                <li>Name: <?php echo $task->getName(); ?></li>
                <li>Email: <?php echo $task->getEmail(); ?></li>
            </ul>
            <br>
        </div>
        <div>
            <div>
                <h2 class="inline">Add task:</h2>
                <a class="inline" id="button" href="/task_add.php" title="add task">
                    <button>ADD</button>
                </a>
            </div>
            <div>
                <h2>Task list:</h2>
                <table class="">
                    <thead>
                        <tr>
                            <th>N.</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Deadline</th>
                            <th>File</th>
                        </tr>
                    </thead>
                    <?php
                        foreach($user_tasks as $user_task){
                            $num_task++;
                            ?><input type="hidden" id="task_id" name="task_id" value="<?php echo $user_task['id'];?>">
                                <tbody>
                                    <tr>
                                        <td><?php echo $num_task; ?></td>
                                        <td><?php echo $user_task['name']; ?></td>
                                        <td><?php if(!empty($user_task['description'])) echo $user_task['description']; ?></td>
                                        <td><?php if(!empty($user_task['deadline'])) echo date("d.m.y", (int)$user_task['deadline']); ?></td>
                                        <td><?php if(!empty($user_task['file_id'])) echo ltrim(strstr($user_task['file_id'], '_'), '_'); ?></td>
										<td><form action="/download.php" method="post"><input type="hidden" id="file_url" name="file_url" value="<?php if(!empty($user_task['file_id'])) echo $user_task['file_id'];?>"><input type="submit" name="download" id="<?php echo $user_task['id'];?>" value="Download"></form><?php if(empty($user_task['file_id']))echo '<script type="text/javascript"> Disable_Download('.$user_task['id'].'); </script>';?>
                                        <td><form action="/task_edit.php" method="post"><input type="hidden" id="task_id" name="task_id" value="<?php echo $user_task['id']; ?>"><input type="hidden" id="user_id" name="user_id" value="<?php echo $task->getID();?>"><input type="hidden" id="name" name="name" value="<?php echo $user_task['name'];?>"><input type="hidden" id="description" name="description" value="<?php echo $user_task['description'];?>"><input type="hidden" id="deadline" name="deadline" value="<?php echo $user_task['deadline'];?>"><input type="submit" name="edit_button" value="EDIT"></form></td>
                                        <td><form action="/task_remove.php" method="post"><input type="hidden" id="task_id" name="task_id" value="<?php echo $user_task['id'];?>"><input type="hidden" id="user_id" name="user_id" value="<?php echo $task->getID();?>"><input type="submit" name="remove_button" value="REMOVE"></form></td>
                                    </tr>
                                </tbody>
                            <?php
                        }
                    ?>
                </table>
                <br>
            </div>
        </div>
        <div>
            <p><a href="/index.php">Index</a></p>
            <p><a href="/logout.php">Logout</a></p>
        </div>
    </body>
</html>