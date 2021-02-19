<?php
	if(isset($_COOKIE['asid']))
	{
		setcookie("asid", $_COOKIE['asid'], time()+1800, '/');
		$GetUserDetails = MysqlQuery("SELECT * FROM admins WHERE SID = '".$_COOKIE['asid']."' LIMIT 1");
        if(mysqli_num_rows($GetUserDetails) == 1)
        {
            $AdminDetails = mysqli_fetch_assoc($GetUserDetails);

            $AID            = $AdminDetails['AdminID'];
			$AdminName      = $AdminDetails['Name'];
			$AdminUsername  = $AdminDetails['Username'];
			$LoggedIn       = true;

            //GET EMPLOYEE ROLES & THEIR PERMISSIONS
            $AdminRoles = MysqlQuery("SELECT r.Role, r.RoleID, p.PageID FROM roles r LEFT JOIN role_permissions p ON p.RoleID = r.RoleID WHERE r.RoleID = '".$AdminDetails['RoleID']."'");
            for(;$row = mysqli_fetch_assoc($AdminRoles);)
            {
                $AdminDetails['Role'][$row['RoleID']] = $row['Role'];
                if($row['PageID'] == '')
                {
                    $PermissionIDs[] = '';
                }
                else
                {
                    $PermissionIDs[$row['PageID']] = $row['PageID'];
                }
                $PermissionIDs = array_filter($PermissionIDs);
            }

            // GET ADMIN PAGE
            $GetAdminPages = MysqlQuery("SELECT * FROM admin_pages WHERE Status = 'y' ORDER By SortOrder");
            for(;$row = mysqli_fetch_assoc($GetAdminPages);)
            {
                $AdminPages['List'][$row['ParentID']][] = $row['PageID'];
                $AdminPages['Data'][$row['PageID']] = $row;

                if($baseUrl == $row['PageURL'])
                {
                    if(count($PermissionIDs) > 0)
                    {
                        if(!isset($PermissionIDs[$row['PageID']]))
                        {
                            if(strpos($row['PageURL'], '/admin/ajax/') !== false)
                            {
                                echo json_encode(array('status' => 'error', 'message' => 'You do not have permissions to '.$row['PageTitle']));
                                exit();
                            }
                            else
                            {
                                $_SESSION['AlertMessage'] = 'You do not have permissions to '.$row['PageTitle'];
                                header('Location: /admin/dashboard/');
                                exit;
                            }
                        }
                    }
                }
            }
        }
	}

    if($LoggedIn !== true && $FolderLevels[0] == 'admin' && $FolderLevels[1] == 'ajax')
    {
        echo json_encode(array('status' => 'login', 'redirect' => '/admin/'));
        exit();
    }

	if(isset($_GET['logout']))
	{
		setcookie('asid', 0, time()-3600, '/');
        RecordAdminActivity('Logged Out', 'admins', $AID);
		header('Location: /admin/');
		exit();
	}
	elseif($LoggedIn == true && $_SERVER['PHP_SELF'] == "/admin/index.php")
	{
		header('Location: /admin/dashboard/');
		exit();
	}
	elseif($LoggedIn !== true && $_SERVER['PHP_SELF'] != "/admin/index.php")
	{
		$_SESSION['AdminReturnURL'] = $_SERVER['REQUEST_URI'];
		header("Location: /admin/");
		exit();
	}

?>