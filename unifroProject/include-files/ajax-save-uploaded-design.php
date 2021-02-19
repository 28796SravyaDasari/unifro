<?php

    $response['status'] = 'error';

    if(VerifyFormToken('UploadYourDesignForm'))
    {
        if($_POST['FirstName'] == '')
        {
            $error['FirstName'] = 'Enter First Name';
        }
        if($_POST['LastName'] == '')
        {
            $error['LastName'] = 'Enter Last Name';
        }
        if(!preg_match( "/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $_POST['EmailID']))
        {
            $error['EmailID'] = 'Enter a valid Email Address';
        }
        if($_FILES['SampleDesign']['name'] == '')
        {
            $error['SampleDesign'] = 'Please upload your design';
        }
        if($_FILES['SampleDesign']['name'] != '')
        {
            $SampleDesign = $_FILES['SampleDesign']['tmp_name'];
            $SampleDesignSize = $_FILES['SampleDesign']['size'];
            $SampleDesignName = $_FILES['SampleDesign']['name'];
            $SampleDesignExt = strtolower(substr(strrchr($SampleDesignName,'.'),1));

            if(array_search(strtolower($SampleDesignExt), $AllowedImageTypes) === false)
            {
                $error['SampleDesign'] = 'Invalid File type. Only '.implode(', ', $AllowedImageTypes).' are allowed';
            }
        }
        if(strlen($_POST['Mobile']) < 10)
		{
			$error['Mobile'] = 'Enter a valid Mobile Number';
		}

        if(!isset($error))
        {
            $MemberID = $LoggedIn ? $MemberID : 0;

            $FileName = time().'.'.$SampleDesignExt;
            $ImageDir = _ROOT._SampleDesignDir;

            $EmailMessage = '<TR>
                                <TD>
                                    <h1>Upload Your Design</h1>
                                </TD>
                            </TR>
                            <TR>
                                <TD>
                                <TABLE bgcolor="#CCCCCC" cellpadding="10" cellspacing="1" width="350px" style="font-family:arial,tahoma;font-size:13px">
                                <TR bgcolor="#FFFFFF">
                                    <TD>Name</TD>
                                    <TD>'.$_POST['FirstName'].' '.$_POST['LastName'].'</TD>
                                </TR>
                                <TR bgcolor="#FFFFFF">
                                    <TD>Mobile</TD>
                                    <TD>'.$_POST['Mobile'].'</TD>
                                </TR>
                                <TR bgcolor="#FFFFFF">
                                    <TD>Email</TD>
                                    <TD>'.$_POST['EmailID'].'</TD>
                                </TR>
                                <TR bgcolor="#FFFFFF">
                                    <TD>Address</TD>
                                    <TD>'.$_POST['Address'].'</TD>
                                </TR>
                                </TABLE>
                                </TD>
                            </TR>';

            $EmailMessage = FormatEmail($EmailMessage);

            if(move_uploaded_file($SampleDesign, $ImageDir.$FileName))
            {
                if(SendMailHTML('admin@myzow.com', 'Enquiry via Upload Your Design Form', $EmailMessage, $ImageDir.$FileName))
                {
                    $response['status'] = 'success';
                    $response['response_message'] = 'Thank you! We have received your design. <br>One of our representative will get back to you.';

                }
                else
                {
                    $response['response_message'] = 'Error occurred! Please try again. [LN 10]';
                }
            }
            else
            {
                $response['response_message'] = 'Error occurred! Please try again. [LN 20]';
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
        echo json_encode(array('status' => 'error', 'message' => 'Something went wrong! Try after some time. [LN 100]'));
    }

    echo json_encode($response);
?>