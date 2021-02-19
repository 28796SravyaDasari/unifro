<?php

    if(isset($_POST['EmailID']))
    {
        if(!preg_match( "/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $_POST['EmailID']))
        {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid Email ID!'));
        }
        else
        {
            // VALIDATE EMAIL AND FETCH THE SALES DETAILS
            $SalesDetails = MysqlQuery("SELECT SalesID FROM sales WHERE EmailID = '".$_POST['EmailID']."' LIMIT 1");

            if(mysqli_num_rows($SalesDetails) == 1)
            {
                $SalesDetails = mysqli_fetch_assoc($SalesDetails);

                $ForgotPasswordLink = md5($SalesDetails['SalesID'].$_POST['EmailID'].time());

                $res = MysqlQuery("UPDATE sales SET ForgotPasswordLink = '".$ForgotPasswordLink."' WHERE SalesID = '".$SalesDetails['SalesID']."' LIMIT 1");
                if(MysqlAffectedRows() < 1)
    			{
    			 	echo json_encode(array('status' => 'error', 'message' => 'Something went wrong! Please try again.'));
    			}
                else
                {
                    $EmailBody = '<tr>
                                    <td>
                                        <table style="font-size: 14px;">
                                            <tr>
                                                <td style="font-weight: 300; font-size: 21px">Password Reset Request</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 15px">You have requested to reset your password. Please click on the below link to reset your password.</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 15px"><b><a href="'._HOST.'/sales/reset-password/'.$ForgotPasswordLink.'/">Reset Password</a></b></td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 15px">If you have not initiated this request, then kindly ignore this email.</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>';
                    $EmailMessage = FormatEmail($EmailBody);

                    if(!SendMailHTML($_POST['EmailID'], 'Password Reset Request - Unifro', $EmailMessage))
                    {
                        echo json_encode(array('status' => 'error', 'message' => 'Something went wrong! Please try again. [LN 50]'));
                    }

                    echo json_encode(array('status' => 'success', 'message' => '<span class="text-success">We have sent a link to reset your password to your registered email address. Please follow the link to reset your password</span>'));
                }
            }
        }
    }
?>