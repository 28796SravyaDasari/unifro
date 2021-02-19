<?php
	if(!isset($_COOKIE['asid']))
	{
		echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
		exit();
	}

    $response['status'] = 'error';

    if(isset($_POST['AdminID']))
	{
	    $allowed = array('_');

	    if(!is_numeric($_POST['Role']))
        {
            $error['Role'] = 'Select Role';
        }
	    if($_POST['Name'] == '')
        {
            $error['Name'] = 'Please enter Name';
        }
        if(!preg_match( "/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $_POST['Email']))
        {
            $error['Email'] = 'Enter a valid Email ID';
        }
        if(!is_numeric($_POST['AdminID']) && mysqli_num_rows(MysqlQuery("SELECT AdminID FROM admins where Email = '".$_POST['Email']."' LIMIT 1")) == 1)
        {
            $error['Email'] = 'Email ID already registered! Please use another Email';
        }
        if(!NoSpecialCharacters($_POST['Username'], $allowed))
        {
            $error['Username'] = 'Invalid User Name! <SPAN class="font11">(special characters are not allowed)</SPAN>';
        }
        if(!is_numeric($_POST['AdminID']) && mysqli_num_rows(MysqlQuery("SELECT AdminID FROM admins WHERE Username = '".$_POST['Username']."' LIMIT 1")) == 1)
        {
            $error['Username'] = 'User Name is already taken!';
        }
        if(strlen($_POST['Username']) < 3)
        {
            $error['Username'] = 'Username must be atleast 3 characters long!';
        }
        if(!is_numeric($_POST['AdminID']) && strpos($_POST['Password'], ' ', 0) !== false)
        {
            $error['Password'] = 'Spaces are not allowed in password!';
        }
        if(!is_numeric($_POST['AdminID']) && strlen($_POST['Password']) < 4)
        {
            $error['Password'] = 'At least 4 characters for password!';
        }

        if(!isset($error))
        {
            $Password = GeneratePwd(stripslashes($_POST['Password']));

            if(is_numeric($_POST['AdminID']))
            {
                $res = MysqlQuery("UPDATE admins SET RoleID = '".$_POST['Role']."', Name = '".$_POST['Name']."', Email = '".$_POST['Email']."', Password = '".$Password."',
                                    UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE AdminID = '".$_POST['AdminID']."' LIMIT 1");

                $activity = 'Account Details Updated';
                $EditMode = true;
                $ID = $_POST['AdminID'];
            }
            else
            {
                MysqlQuery("INSERT INTO admins (RoleID, Name, Email, Username, Password, CreatedOn)
                            VALUES ('".$_POST['Role']."', '".$_POST['Name']."', '".$_POST['Email']."', '".$_POST['Username']."', '".$Password."', '".time()."')");

                $ID = MysqlInsertID();
                $activity = 'Admin Account Created. ('.$_POST['Name'].')';
            }

    		if(MysqlAffectedRows() >= 0)
    		{
    		    RecordAdminActivity($activity, 'admins', $ID);

                $response['status'] = 'success';
                $response['message'] = 'Admin Account Details Saved!';
                $response['redirect'] = '/admin/accounts/';
    		}
    		else
    		{
    		    $response['message'] = 'Something went wrong! Please try again. [LN 100]'.$res;
    		}
        }
        else
        {
            $response['error'] = $error;
            $response['status'] = 'validation';
        }
	}
	else
	{
	    $response['message'] = 'Invalid Access!';
	}

    echo json_encode($response);
?>