<?php

    $response['status'] = 'error';

    if(isset($_POST['Role']))
	{
	    $_POST['Role'] = ucwords( CleanText($_POST['Role']) );

	    if($_POST['Role'] == '')
        {
            $response['message'] = 'Enter a valid Role Name';
        }
        elseif(!is_numeric($_POST['RoleID']) && mysqli_num_rows(MysqlQuery("SELECT RoleID FROM roles WHERE Role = '".$_POST['Role']."' LIMIT 1")) == 1)
        {
            $response['message'] = 'Role Name already exists!';
        }
        else
        {
            if(is_numeric($_POST['RoleID']))
            {
                $res    = MysqlQuery("UPDATE roles SET Role = '".$_POST['Role']."', UpdatedOn = '".time()."', UpdatedBy = '".$AID."' WHERE RoleID = '".$_POST['RoleID']."' LIMIT 1");
                $desc   = 'Role name updated as '.$_POST['Role'];
                $ID     = $_POST['RoleID'];
            }
            else
            {
                $res    = MysqlQuery("INSERT INTO roles (Role, Status, AddedOn, AddedBy) VALUES ('".$_POST['Role']."', '1', '".time()."', '".$AID."')");
                $desc   = 'New Role added: '.$_POST['Role'];
                $ID     = MysqlInsertID();
            }

            if(MysqlAffectedRows() >= 0)
    		{
                RecordAdminActivity('Role', 'roles', $ID, $desc);

                $response['status'] = 'success';
                $response['message'] = 'Role Successfully Saved!';
                $response['redirect'] = '/admin/roles/';
    		}
    		else
    		{
    		    $response['message'] = 'Something went wrong! Please try again. [LN 100]'.$res;
    		}
        }
	}
	else
	{
	    $response['message'] = 'Invalid Access!';
	}

    echo json_encode($response);
?>