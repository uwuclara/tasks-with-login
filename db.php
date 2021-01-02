<?php
/*
todo, but prob not:
better error handling and field validation
add some timeout
Actual todo:
done
PHP VERSION >=7.2
*/

//lib
require_once __DIR__ ."/lib/Medoo.php";

//config
require_once __DIR__ ."/config/database.php";

/**
 * @var array $config
 */

//database namespace/class
use Medoo\Medoo;

/**
 * UserException Class for custom error handle
 */
//error handling
class UserException extends Exception {}

/**
 * User Class
 */
class User
{

	private $name;
    private $email;
    private $id;
    protected $database;
    protected $error = NULL;

    /**
     * User constructor.
     */
    public function __construct()
    {

        global $config;

        $this->database = new Medoo([
            'database_type' => $config['database_type'],
            'database_name' => $config['database_name'],
            'server' => $config['server'],
            'username' => $config['username'],
            'password' => $config['password']
        ]);

		if(isset($_SESSION['user_id']) AND isset($_SESSION['user_name']) AND isset($_SESSION['user_email']))
		{

			$this->name = $_SESSION['user_name'];
			$this->email = $_SESSION['user_email'];
			$this->id = $_SESSION['user_id'];

		}

    }

    /**
     * Registers the user
     * @param string $first_name
     * @param string $last_name
     * @param string $user_email
     * @param string $user_password
     * @param string $user_password_repeat
     * @return bool
     * @throws UserException
     */
    public function Register(string $first_name, string $last_name, string $user_email, string $user_password, string $user_password_repeat)
    {

        //check if pass matches
        if($user_password == $user_password_repeat)
        {

            //check if they are not numbers
            if(ctype_alpha($first_name) == true AND ctype_alpha($last_name) == true)
            {

                //check length
                if(strlen($first_name) >= 2 AND strlen($last_name) >= 2 AND strlen($first_name) <= 32 AND strlen($last_name) <= 32)
                {

                    //check if email
                    if(filter_var($user_email, FILTER_VALIDATE_EMAIL) AND strlen($user_email) <= 32)
                    {

						//check if email exists
                        try
                        {

                            $email_check = $this->ExistEmail($user_email);

                        }
                        catch (UserException $e)
                        {

                            throw new UserException($this->error = $e->getMessage());

                        }

                        if($email_check == false)
                        {

							$data = $this->database->insert("users", [
								"first_name" => $first_name,
								"last_name" => $last_name,
								"email" => $user_email,
								"password" => password_hash($user_password, PASSWORD_BCRYPT)
							]);

							$error_array = $this->database->error();

							if($error_array[2] == NULL)
							{
                                if($data->rowCount() !== 0)
                                {
                                    $data = $this->database->select("users", [
                                        "ID"
                                    ], [
                                        "email" => $user_email,
                                        "first_name" => $first_name,
                                        "last_name" => $last_name
                                    ]);

                                    $error_array = $this->database->error();

                                    if($error_array[2] == NULL)
                                    {

                                        if(!isset($data[0]['ID']))
                                        {

                                            throw new UserException($this->error = "Error selecting your ID!");

                                        }
                                        else
                                        {

                                            $this->Logged($first_name, $last_name, $user_email, $data[0]['ID']);

                                            return (bool) true;

                                        }

                                    }
                                    else
                                    {

                                        throw new UserException($this->error = $error_array[2]);

                                    }

                                }
                                else
                                {

                                    throw new UserException($this->error = "Couldn't add user to the database!");

                                }

							}
							else
							{

								throw new UserException($this->error = $error_array[2]);

							}

						}
                        else
                        {

							throw new UserException($this->error = "Email is already in use!");

						}

                    }
                    else
                    {

                        throw new UserException($this->error = "Enter a valid email and must not exceed 32 chars!");

                    }

                }
                else
                {

                    throw new UserException($this->error = "First name and Last name should be at least 2 characters and must not exceed 32 chars!");

                }

            }
            else
            {

                throw new UserException($this->error = "The name must be only letters with no spaces!");

            }

        }
        else
        {

            throw new UserException($this->error = "Passwords doesn't match!");

        }

    }

    /**
     * Logs in the user
     * @param string $user_email
     * @param string $user_password
     * @return bool
     * @throws UserException
     */
    public function Login(string $user_email, string $user_password)
    {

        if(filter_var($user_email, FILTER_VALIDATE_EMAIL) AND strlen($user_email) <= 32)
        {

            $data = $this->database->select("users", [
                "ID",
                "password",
                "first_name",
                "last_name"
            ], [
                "email" => $user_email
            ]);

            $error_array = $this->database->error();

            if($error_array[2] == NULL)
            {

                if(isset($data[0]['first_name']) AND isset($data[0]['last_name']) AND isset($data[0]['ID']) AND isset($data[0]['password']))
                {

                    if(password_verify($user_password, $data[0]['password']))
                    {

                        $this->Logged($data[0]['first_name'], $data[0]['last_name'], $user_email, $data[0]['ID']);

                        return (bool) true;

                    }
                    else
                    {

                        //never say what's actually wrong for security reasons
                        throw new UserException($this->error = "Wrong password or email address doesn't exist. If u forgot password please reset your password!");

                    }

                }
                else
                {

                    //never say what's actually wrong for security reasons
                    throw new UserException($this->error = "Wrong password or email address doesn't exist. If u forgot password please reset your password!");

                }

            }
            else
            {

                throw new UserException($this->error = $error_array[2]);

            }

        }
        else
        {

            throw new UserException($this->error = "Please type an Email!");

        }

    }

    /**
     * Sets the sessions
     * @param string $user_first_name
     * @param string $user_last_name
     * @param string $user_email
     * @param int $user_id
     */
    public function Logged(string $user_first_name, string $user_last_name, string $user_email, int $user_id)
    {

		//set sessions
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $user_first_name . " " . $user_last_name;
        $_SESSION['user_email'] = $user_email;

        $this->id = $user_id;
        $this->email = $user_email; 
        $this->name = $user_first_name . " " . $user_last_name;

        //head to member page
        header("Location: /member.php");

    }

    /**
     * Logs out the user
     */
    public function Logout()
    {

		//destroys the session
		$this->id = NULL;
		$this->email = NULL;
		$this->name = NULL;

        session_unset();
        session_destroy();

		//head to index page
        header("Location: /index.php");

    }

    /**
     * Checks is email is not in database
     * @param string $email
     * @return bool
     * @throws UserException
     */
    public function ExistEmail(string $email)
    {

        $data = $this->database->select("users", [
            "ID"
        ], [
            "email" => $email
        ]);

        $error_array = $this->database->error();

        if($error_array[2] == NULL)
        {

            if(!isset($data[0]))
            {

                return (bool) false;

            }
            else
            {

                return (bool) true;

            }

        }
        else
        {

            throw new UserException($this->error = $error_array[2]);

        }

    }

    /**
     * Outputs unused keys
     * @param array $input
     * @return bool
     */
    public function Required(array $input)
    {

        //everything is required
        $i = 0;

        foreach($input as $key => $value)
        {

            if(empty($value))
            {

                $i++;

                //this will only save one empty key cause it's being overwritten!
                $this->error = "<i>".$key." is required</i>";

            }

        }  
        if($i == 0)
        {

            return (bool) true;

        }

        return (bool) false;

    }

    /**
     * @return int
     */
    public function getID()
    {

		return (int) $this->id;

	}

    /**
     * @return string
     */
    public function getName()
    {

		return (string) $this->name;

	}

    /**
     * @return string
     */
    public function getEmail()
    {

		return (string) $this->email;

	}

    /**
     * @return string
     */
    public function getError()
    {

        return (string) $this->error;

    }

}

/**
 * Task Class
 */
class Task extends User
{

    /**
     * Task constructor.
     */
    public function __construct()
    {

        parent::__construct();

    }

    /**
     * Adds user task
     * @param string $name
     * @param string|null $desc
     * @param int|null $deadline
     * @param array|null $upFile
     * @return bool
     * @throws UserException
     */
    public function AddTask(string $name, string $desc = NULL, int $deadline = NULL, array $upFile = NULL)
    {

        if(!is_null($this->getID()))
        {

            if(strlen($name) <= 32)
            {

                if(isset($desc) AND !isset($deadline) AND !isset($upFile))
                {

                    if(strlen($desc) <= 100)
                    {

                        $data = $this->database->insert("tasks", [
                            "owner_id" => $this->getID(),
                            "name" => $name,
                            "description" => $desc
                        ]);

                        $error_array = $this->database->error();

                        if($error_array[2] !== NULL)
                        {

                            throw new UserException($this->error = $error_array[2]);

                        }
                        else
                        {

                            if($data->rowCount() !== 0)
                            {

                                return (bool)true;

                            }
                            else
                            {

                                throw new UserException($this->error = "Couldn't add user's task!");

                            }

                        }

                    }
                    else
                    {

                        throw new UserException($this->error = "Max 100 characters!");

                    }

                }
                elseif(isset($deadline) AND !isset($desc) AND !isset($upFile))
                {

                    if(is_numeric($deadline) == 1)
                    {

                        $data = $this->database->insert("tasks", [
                            "owner_id" => $this->getID(),
                            "name" => $name,
                            "deadline" => $deadline
                        ]);

                        $error_array = $this->database->error();

                        if($error_array[2] !== NULL)
                        {

                            throw new UserException($this->error = $error_array[2]);

                        }
                        else
                        {

                            if($data->rowCount() !== 0)
                            {

                                return (bool)true;

                            }
                            else
                            {

                                throw new UserException($this->error = "Couldn't add user's task!");

                            }

                        }

                    }
                    else
                    {

                        throw new UserException($this->error = "The date must be an unix number!");

                    }

                }
                elseif(isset($upFile) AND !isset($desc) AND !isset($deadline))
                {
                    try
                    {

                        $FileLocation = $this->SaveFile($upFile);

                    }
                    catch(UserException $e)
                    {

                        throw new UserException($this->error = $e->getMessage());

                    }

                    $data = $this->database->insert("tasks", [
                        "owner_id" => $this->getID(),
                        "name" => $name,
                        "description" => NULL,
                        "deadline" => NULL,
                        "file_id" => $FileLocation
                    ]);

                    $error_array = $this->database->error();

                    if($error_array[2] !== NULL)
                    {

                        throw new UserException($this->error = $error_array[2]);

                    }
                    else
                    {

                        if($data->rowCount() !== 0)
                        {

                            return (bool)true;

                        }
                        else
                        {

                            throw new UserException($this->error = "Couldn't add user's task!");

                        }

                    }

                }
                elseif(isset($desc) AND isset($deadline) AND !isset($upFile))
                {

                    if(strlen($desc) <= 100)
                    {

                        if(is_numeric($deadline) == 1)
                        {

                            $data = $this->database->insert("tasks", [
                                "owner_id" => $this->getID(),
                                "name" => $name,
                                "description" => $desc,
                                "deadline" => $deadline
                            ]);

                            $error_array = $this->database->error();

                            if($error_array[2] !== NULL)
                            {

                                throw new UserException($this->error = $error_array[2]);

                            }
                            else
                            {

                                if($data->rowCount() !== 0)
                                {

                                    return (bool)true;

                                }
                                else
                                {

                                    throw new UserException($this->error = "Couldn't add user's task!");

                                }

                            }

                        }
                        else
                        {

                            throw new UserException($this->error = "The date must be an unix number!");

                        }

                    }
                    else
                    {

                        throw new UserException($this->error = "Max 100 characters!");

                    }

                }
                elseif(isset($desc) AND isset($upFile) AND !isset($deadline))
                {

                    if(strlen($desc) <= 100)
                    {

                        try
                        {

                            $FileLocation = $this->SaveFile($upFile);

                        }
                        catch(UserException $e)
                        {

                            throw new UserException($this->error = $e->getMessage());

                        }

                        $data = $this->database->insert("tasks", [
                            "owner_id" => $this->getID(),
                            "name" => $name,
                            "description" => $desc,
                            "deadline" => NULL,
                            "file_id" => $FileLocation
                        ]);

                        $error_array = $this->database->error();

                        if($error_array[2] !== NULL)
                        {

                            throw new UserException($this->error = $error_array[2]);

                        }
                        else
                        {

                            if($data->rowCount() !== 0)
                            {

                                return (bool)true;

                            }
                            else
                            {

                                throw new UserException($this->error = "Couldn't add user's task!");

                            }

                        }

                    }
                    else
                    {

                        throw new UserException($this->error = "Max 100 characters!");

                    }

                }
                elseif(isset($desc) AND isset($deadline) AND isset($upFile))
                {

                    if(strlen($desc) <= 100)
                    {

                        if(is_numeric($deadline) == 1)
                        {

                            try
                            {

                                $FileLocation = $this->SaveFile($upFile);

                            }
                            catch(UserException $e)
                            {

                                throw new UserException($this->error = $e->getMessage());

                            }

                            $data = $this->database->insert("tasks", [
                                "owner_id" => $this->getID(),
                                "name" => $name,
                                "description" => $desc,
                                "deadline" => $deadline,
                                "file_id" => $FileLocation
                            ]);

                            $error_array = $this->database->error();

                            if($error_array[2] !== NULL)
                            {

                                throw new UserException($this->error = $error_array[2]);

                            }
                            else
                            {

                                if($data->rowCount() !== 0)
                                {

                                    return (bool)true;

                                }
                                else
                                {

                                    throw new UserException($this->error = "Couldn't add user's task!");

                                }

                            }

                        }
                        else
                        {

                            throw new UserException($this->error = "The date must be an unix number!");

                        }

                    }
                    else
                    {

                        throw new UserException($this->error = "Max 100 characters!");

                    }

                }
                elseif(isset($deadline) AND isset($upFile) AND !isset($desc))
                {

                    if(is_numeric($deadline) == 1)
                    {

                        try
                        {

                            $FileLocation = $this->SaveFile($upFile);

                        }
                        catch(UserException $e)
                        {

                            throw new UserException($this->error = $e->getMessage());

                        }

                        $data = $this->database->insert("tasks", [
                            "owner_id" => $this->getID(),
                            "name" => $name,
                            "description" => NULL,
                            "deadline" => $deadline,
                            "file_id" => $FileLocation
                        ]);

                        $error_array = $this->database->error();

                        if($error_array[2] !== NULL)
                        {

                            throw new UserException($this->error = $error_array[2]);

                        }
                        else
                        {

                            if($data->rowCount() !== 0)
                            {

                                return (bool)true;

                            }
                            else
                            {

                                throw new UserException($this->error = "Couldn't add user's task!");

                            }

                        }

                    }
                    else
                    {

                        throw new UserException($this->error = "The date must be an unix number!");

                    }

                }
                else
                {

                    $data = $this->database->insert("tasks", [
                        "owner_id" => $this->getID(),
                        "name" => $name
                    ]);

                    $error_array = $this->database->error();

                    if($error_array[2] !== NULL)
                    {

                        throw new UserException($this->error = $error_array[2]);

                    }
                    else
                    {

                        if($data->rowCount() !== 0)
                        {

                            return (bool)true;

                        }
                        else
                        {

                            throw new UserException($this->error = "Couldn't add user's task!");

                        }

                    }

                }

            }
            else
            {

                throw new UserException($this->error = "Max 32 characters!");

            }

        }
        else
        {

            throw new UserException($this->error = "Not logged!");

        }

    }

    /**
     * Removes user's task
     * @param int $task_id
     * @return bool
     * @throws UserException
     */
    public function RemoveTask(int $task_id)
    {

        if(!is_null($this->getID()))
        {

            try
            {

                $url = $this->GetFileUrl($task_id);

                if($url !== false)
                {
                    //this one will always return true, if there's an error will throw an UserException
                    $this->FileDelete($url);

                }
            }
            catch (UserException $e)
            {

                throw new UserException($this->error = $e->getMessage());

            }

            $data = $this->database->delete("tasks", [
                "owner_id" => $this->getID(),
                "id" => $task_id
            ]);

            $error_array = $this->database->error();

            if($error_array[2] !== NULL)
            {

                throw new UserException($this->error = $error_array[2]);

            }
            else
            {

                if($data->rowCount() !== 0)
                {

                    return (bool)true;

                }
                else
                {

                    throw new UserException($this->error = "Couldn't remove user's task");

                }

            }

        }
        else
        {

            throw new UserException($this->error = "Not logged!");

        }

    }

    /**
     * Moves the file from the temp folder to a permanent folder
     * @param array $file_temp
     * @return string
     * @throws UserException
     */
    public function SaveFile(array $file_temp)
    {

        if(isset($file_temp['size']) AND $file_temp['name'] AND $file_temp['tmp_name'])
        {

            if(!is_null($this->getID()))
            {

                if($file_temp['size'] > 2000000)//2097152
                {

                    throw new UserException($this->error = "The file size max is 2 MB");

                }
                else
                {

                    $file_types = array
                    (
                        'application/pdf',
                        'image/jpeg',
                        'image/jpg',
                        'image/gif',
                        'image/png'
                    );


                    if(!in_array($file_temp['type'], $file_types) AND !empty($file_temp['type']))
                    {

                        throw new UserException($this->error = "Invalid file type. Only PDF, JPG, GIF and PNG types are accepted.");

                    }

                    $random = mt_rand(100, 100000000);

                    if(move_uploaded_file($file_temp['tmp_name'],"uploads/".$random."_".$file_temp['name']) == 1)
                    {

                        return (string) $url = "uploads/".$random."_".$file_temp['name'];

                    }
                    else
                    {

                        throw new UserException($this->error = "Uploading failed, could be either of upload folder not being writeable, doesn't exist or the uploaded file is more than 2 MB!");

                    }

                }

            }
            else
            {

                throw new UserException($this->error = "Not logged!");

            }

        }
        else
        {

            throw new UserException($this->error = "Upload badly done, did you exceed the file size?");

        }

    }

    /**
     * Checks if the file is still on the server and returns url
     * @param int $task_id
     * @return false|string
     * @throws UserException
     */
    public function GetFileUrl(int $task_id)
    {

        if(!is_null($this->getID()))
        {

            $data = $this->database->select("tasks", [
                "file_id"
            ], [
                "owner_id" => $this->getID(),
                "ID" => $task_id
            ]);

            $error_array = $this->database->error();

            if($error_array[2] !== NULL)
            {

                throw new UserException($this->error = $error_array[2]);

            }
            else
            {

                if(isset($data[0]['file_id']) AND $data[0]['file_id']!== NULL)
                {

                    if(file_exists($data[0]['file_id']))
                    {

                        return (string) $data[0]['file_id'];

                    }
                    else
                    {

                        //File no longer is on the server
                        $data = $this->database->update("tasks", [
                            "file_id" => NULL
                        ], [
                            "owner_id" => $this->getID(),
                            "ID" => $task_id
                        ]);

                        $error_array = $this->database->error();

                        if($error_array[2] !== NULL)
                        {

                            throw new UserException($this->error = $error_array[2]);

                        }
                        else
                        {

                            if($data->rowCount() !== 0)
                            {

                                return (bool) false;

                            }
                            else
                            {

                                throw new UserException($this->error = "Error removing user's old file from database or user doesn't own the task!");

                            }

                        }

                    }

                }
                else
                {

                    return (bool) false;

                }

            }

        }
        else
        {

            throw new UserException($this->error = "Not logged!");

        }

    }

    /**
     * Deletes user's file
     * @param string $url
     * @return bool
     * @throws UserException
     */
    public function FileDelete(string $url)
    {

        if(!is_null($this->getID()))
        {

            if(unlink($url))
            {

                $data = $this->database->update("tasks", [
                    "file_id" => NULL
                ], [
                    "owner_id" => $this->getID(),
                    "file_id" => $url
                ]);

                $error_array = $this->database->error();

                if($error_array[2] !== NULL)
                {

                    throw new UserException($this->error = $error_array[2]);

                }
                else
                {

                    if($data->rowCount() !== 0)
                    {

                        return (bool)true;

                    }
                    else
                    {

                        throw new UserException($this->error = "Error file is not in database or user doesn't own this file!");

                    }

                }

            }
            else
            {

                throw new UserException($this->error = "Error deleting a file!");

            }

        }
        else
        {

            throw new UserException($this->error = "Not logged!");

        }

    }

    /**
     * Downloads user's file
     * @param $url
     * @return bool
     * @throws UserException
     */
    public function DownloadFile($url)
    {

        if(!is_null($this->getID()))
        {

            if(file_exists($url))
            {

                $query = $this->database->select("tasks", [
                    "file_id"
                ], [
                    "owner_id" => $this->getID(),
                    "file_id" => $url
                ]);

                $error_array = $this->database->error();

                if($error_array[2] !== NULL)
                {

                    throw new UserException($this->error = $error_array[2]);

                }
                else
                {

                    if(!empty($query))
                    {

                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="'.basename($url).'"');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($url));

                        readfile($url);

                        return (bool)true;

                    }
                    else
                    {

                        throw new UserException($this->error = "You don't have permission to this file!");

                    }

                }
            }
            else
            {

				$data = $this->database->update("tasks", [
					"file_id" => NULL
				], [
					"owner_id" => $this->getID(),
					"file_id" => $url
				]);

				$error_array = $this->database->error();

				if($error_array[2] !== NULL)
				{

					throw new UserException($this->error = $error_array[2]);

				}
				else
				{

                    if($data->rowCount() !== 0)
                    {

                        throw new UserException($this->error = "File doesn't exist on the server!");

                    }
                    else
                    {

                        throw new UserException($this->error = "Couldn't remove non existing file from user's task!");

                    }

				}

            }

        }
        else
        {

            throw new UserException($this->error = "Not logged!");

        }

    }

    /**
     * Fetches user's tasks
     * @return array
     * @throws UserException
     */
    public function FetchTasks()
    {

        if(!is_null($this->getID()))
        {

            $data = $this->database->select("tasks", [
                "name",
                "description",
                "deadline",
                "file_id",
                "id"
            ], [
                "owner_id" => $this->getID()
            ]);

            $error_array = $this->database->error();

            if($error_array[2] !== NULL)
            {

                throw new UserException($this->error = $error_array[2]);

            }
            else
            {

                return (array)$data;

            }

        }
        else
        {

            throw new UserException($this->error = "Not logged!");

        }

    }

    /**
     * Fetches user's task by $task_id
     * @param int $task_id
     * @return array
     * @throws UserException
     */
    public function FetchTask(int $task_id)
    {

        if(!is_null($this->getID()))
        {

            $data = $this->database->select("tasks", [
                "name",
                "description",
                "deadline",
                "file_id",
                "id"
            ], [
                "owner_id" => $this->getID(),
                "id" => $task_id
            ]);

            $error_array = $this->database->error();

            if($error_array[2] !== NULL)
            {

                throw new UserException($this->error = $error_array[2]);

            }
            else
            {

                return (array)$data;

            }

        }
        else
        {

            throw new UserException($this->error = "Not logged!");

        }

    }


    /**
     * Edits user's task
     * @param int $task_id
     * @param string $name
     * @param string|null $desc
     * @param int|null $deadline
     * @param array|null $upFile
     * @return bool
     * @throws UserException
     */
    public function EditTask(int $task_id, string $name, string $desc = NULL, int $deadline = NULL, array $upFile = NULL)
    {

        if(!is_null($this->getID()))
        {

            if(strlen($name) <= 32)
            {

                if(isset($upFile))
                {

                    try
                    {

                        $file_url = $this->GetFileUrl($task_id);

                        if($file_url !== false)
                        {

                            try
                            {

                                $this->FileDelete($file_url);

                            }
                            catch (UserException $e)
                            {

                                throw new UserException($this->error = $e->getMessage());

                            }

                        }

                    }
                    catch (UserException $e)
                    {

                        throw new UserException($this->error = $e->getMessage());

                    }

                }

                if(isset($desc) AND !isset($deadline) AND !isset($upFile))
                {

                    if(strlen($desc) <= 100)
                    {

                        $data = $this->database->update("tasks", [
                            "name" => $name,
                            "deadline" => NULL,
                            "description" => $desc
                        ], [
                            "owner_id" => $this->getID(),
                            "id" => $task_id
                        ]);

                        $error_array = $this->database->error();

                        if($error_array[2] !== NULL)
                        {

                            throw new UserException($this->error = $error_array[2]);

                        }
                        else
                        {

                            if($data->rowCount() !== 0)
                            {

                                return (bool)true;

                            }
                            else
                            {

                                throw new UserException($this->error = "Couldn't edit user's task!");

                            }

                        }

                    }
                    else
                    {

                        throw new UserException($this->error = "Max 100 characters!");

                    }

                }
                elseif(isset($deadline) AND !isset($desc) AND !isset($upFile))
                {

                    if(is_numeric($deadline) == 1)
                    {

                        $data = $this->database->update("tasks", [
                            "name" => $name,
                            "description" => NULL,
                            "deadline" => $deadline
                        ], [
                            "owner_id" => $this->getID(),
                            "id" => $task_id
                        ]);

                        $error_array = $this->database->error();

                        if($error_array[2] !== NULL)
                        {

                            throw new UserException($this->error = $error_array[2]);

                        }
                        else
                        {

                            if($data->rowCount() !== 0)
                            {

                                return (bool)true;

                            }
                            else
                            {

                                throw new UserException($this->error = "Couldn't edit user's task!");

                            }

                        }

                    }
                    else
                    {

                        throw new UserException($this->error = "The date must be an unix number!");

                    }

                }
                elseif(isset($upFile) AND !isset($desc) AND !isset($deadline))
                {

                    try
                    {

                        $FileLocation = $this->SaveFile($upFile);

                    }
                    catch(UserException $e)
                    {

                        throw new UserException($this->error = $e->getMessage());

                    }

                    $data = $this->database->update("tasks", [
                        "name" => $name,
                        "description" => NULL,
                        "deadline" => NULL,
                        "file_id" => $FileLocation
                    ], [
                        "owner_id" => $this->getID(),
                        "id" => $task_id
                    ]);

                    $error_array = $this->database->error();

                    if($error_array[2] !== NULL)
                    {

                        throw new UserException($this->error = $error_array[2]);

                    }
                    else
                    {

                        if($data->rowCount() !== 0)
                        {

                            return (bool)true;

                        }
                        else
                        {

                            throw new UserException($this->error = "Couldn't edit user's task!");

                        }

                    }

                }
                elseif(isset($desc) AND isset($deadline) AND !isset($upFile))
                {

                    if(strlen($desc) <= 100)
                    {

                        if(is_numeric($deadline) == 1)
                        {

                            $data = $this->database->update("tasks", [
                                "name" => $name,
                                "description" => $desc,
                                "deadline" => $deadline
                            ], [
                                "owner_id" => $this->getID(),
                                "id" => $task_id
                            ]);

                            $error_array = $this->database->error();

                            if($error_array[2] !== NULL)
                            {

                                throw new UserException($this->error = $error_array[2]);

                            }
                            else
                            {

                                if($data->rowCount() !== 0)
                                {

                                    return (bool)true;

                                }
                                else
                                {

                                    throw new UserException($this->error = "Couldn't edit user's task!");

                                }

                            }

                        }
                        else
                        {

                            throw new UserException($this->error = "The date must be an unix number!");

                        }

                    }
                    else
                    {

                        throw new UserException($this->error = "Max 100 characters!");

                    }

                }
                elseif(isset($desc) AND isset($upFile) AND !isset($deadline))
                {

                    if(strlen($desc) <= 100)
                    {

                        try
                        {

                            $FileLocation = $this->SaveFile($upFile);

                        }
                        catch(UserException $e)
                        {

                            throw new UserException($this->error = $e->getMessage());

                        }

                        $data = $this->database->update("tasks", [
                            "name" => $name,
                            "description" => $desc,
                            "deadline" => NULL,
                            "file_id" => $FileLocation
                        ], [
                            "owner_id" => $this->getID(),
                            "id" => $task_id
                        ]);

                        $error_array = $this->database->error();

                        if($error_array[2] !== NULL)
                        {

                            throw new UserException($this->error = $error_array[2]);

                        }
                        else
                        {

                            if($data->rowCount() !== 0)
                            {

                                return (bool)true;

                            }
                            else
                            {

                                throw new UserException($this->error = "Couldn't edit user's task!");

                            }

                        }

                    }
                    else
                    {

                        throw new UserException($this->error = "Max 100 characters!");

                    }

                }
                elseif(isset($desc) AND isset($deadline) AND isset($upFile))
                {

                    if(strlen($desc) <= 100)
                    {

                        if(is_numeric($deadline) == 1)
                        {

                            try
                            {

                                $FileLocation = $this->SaveFile($upFile);

                            }
                            catch(UserException $e)
                            {

                                throw new UserException($this->error = $e->getMessage());

                            }

                            $data = $this->database->update("tasks", [
                                "name" => $name,
                                "description" => $desc,
                                "deadline" => $deadline,
                                "file_id" => $FileLocation
                            ], [
                                "owner_id" => $this->getID(),
                                "id" => $task_id
                            ]);

                            $error_array = $this->database->error();

                            if($error_array[2] !== NULL)
                            {

                                throw new UserException($this->error = $error_array[2]);

                            }
                            else
                            {

                                if($data->rowCount() !== 0)
                                {

                                    return (bool)true;

                                }
                                else
                                {

                                    throw new UserException($this->error = "Couldn't edit user's task!");

                                }

                            }

                        }
                        else
                        {

                            throw new UserException($this->error = "The date must be an unix number!");

                        }

                    }
                    else
                    {

                        throw new UserException($this->error = "Max 100 characters!");

                    }

                }
                elseif(isset($deadline) AND isset($upFile) AND !isset($desc))
                {

                    if(is_numeric($deadline) == 1)
                    {

                        try
                        {

                            $FileLocation = $this->SaveFile($upFile);

                        }
                        catch(UserException $e)
                        {

                            throw new UserException($this->error = $e->getMessage());

                        }

                        $data = $this->database->update("tasks", [
                            "name" => $name,
                            "description" => NULL,
                            "deadline" => $deadline,
                            "file_id" => $FileLocation
                        ], [
                            "owner_id" => $this->getID(),
                            "id" => $task_id
                        ]);

                        $error_array = $this->database->error();

                        if($error_array[2] !== NULL)
                        {

                            throw new UserException($this->error = $error_array[2]);

                        }
                        else
                        {

                            if($data->rowCount() !== 0)
                            {

                                return (bool)true;

                            }
                            else
                            {

                                throw new UserException($this->error = "Couldn't edit user's task!");

                            }

                        }

                    }
                    else
                    {

                        throw new UserException($this->error = "The date must be an unix number!");

                    }

                }
                else
                {

                    $data = $this->database->update("tasks", [
                        "name" => $name,
                        "description" => NULL,
                        "deadline" => NULL
                    ], [
                        "owner_id" => $this->getID(),
                        "id" => $task_id
                    ]);

                    $error_array = $this->database->error();

                    if($error_array[2] !== NULL)
                    {

                        throw new UserException($this->error = $error_array[2]);

                    }
                    else
                    {

                        if($data->rowCount() !== 0)
                        {

                            return (bool)true;

                        }
                        else
                        {

                            throw new UserException($this->error = "Couldn't edit user's task!");

                        }

                    }

                }

            }
            else
            {

                throw new UserException($this->error = "Max 32 characters!");

            }

        }
        else
        {

            throw new UserException($this->error = "Not logged!");

        }

	}

}

?>
