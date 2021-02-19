<?php

    if(VerifyFormToken('LoginForm'))
    {
        $_POST['Password'] = stripslashes($_POST['Password']);

		// REMOVE SALT FROM PASSWORD ADDED USING JAVASCRIPT
		for($c = 0, $password = ''; $c < strlen($_POST['Password']); $c = $c + $_SESSION['HashLength'] + 1)
		{
			$password .= substr($_POST['Password'], $c, 1);
		}
		$_POST['Password'] = $password;

        // CHECK IF COOKIES ARE ENABLED
		if(count($_COOKIE) == 0)
		{
            echo json_encode(array('status' => 'error', 'message' => 'Cookies are disabled! Please Enable Cookies.'));
		}
        elseif(!preg_match( "/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $_POST['EmailID']))
        {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid Email ID!'));
        }
        else
        {
            //VALIDATE EMAIL AND FETCH THE MEMBER DETAILS
            $MemberDetails = MysqlQuery("SELECT EmailID, Password, Status FROM sales WHERE EmailID = '".$_POST['EmailID']."' LIMIT 1");

            if(mysqli_num_rows($MemberDetails) == 1)
			{
				$MemberDetails = mysqli_fetch_assoc($MemberDetails);

				if($_POST['EmailID'] == $MemberDetails['EmailID'] && $MemberDetails['Status'] == '0')
				{
				    echo json_encode(array('status' => 'error', 'message' => 'Your account is not Active! Contact Admin'));
				}
				elseif($_POST['EmailID'] == $MemberDetails['EmailID'] && !VerifyPwd($_POST['Password'], $MemberDetails['Password'] ))
				{
					echo json_encode(array('status' => 'error', 'message' => 'Incorrect Password!'));
				}
				else
				{
					$SID = GeneratePwd(AlphaNumericCode(30).microtime(get_as_float));

                    $q = MysqlQuery("UPDATE sales SET SID = '".$SID."', LastLoginTimestamp = CurrentLoginTimestamp, LastLoginIP = CurrentLoginIP, CurrentLoginTimestamp = ".time().", CurrentLoginIP = '".GetIP()."' WHERE EmailID = '".$_POST['EmailID']."' LIMIT 1");

					if(MysqlAffectedRows() == 1)
					{
					    $q = MysqlQuery("SELECT SalesID FROM sales WHERE SID = '".$SID."' LIMIT 1");
						$MemberDetails = mysqli_fetch_assoc($q);
						$MemberID = $MemberDetails['MemberID'];

						setcookie('ssid', $SID, 0, '/');
                        RecordSalesActivity('Logged In', 'sales', $MemberID);

						if(isset($_SESSION['ReturnURL']))
						{
							if($_SESSION['ReturnURL'] == '/' || $_SESSION['ReturnURL'] == '/index.php')
							{
								$targetURL = $MasterMemberTypes[1]['MyAccountURL'];
							}
							else
								$targetURL = $_SESSION['ReturnURL'];

							unset($_SESSION['ReturnURL']);
						}
						else
						{
							$targetURL = $MasterMemberTypes[1]['MyAccountURL'];
						}
                        echo json_encode(array('status' => 'success', 'redirect' => $targetURL));
					}
					else
					{
                        echo json_encode(array('status' => 'error', 'message' => 'Something went wrong! Try after some time. [LN 116]'));
					}
				}
			}
			else
			{
			    echo json_encode(array('status' => 'error', 'message' => 'No records found for this Email!'));
			}
        }
    }
    else
    {
        GenerateHackLog('Login Page');
    }
?>