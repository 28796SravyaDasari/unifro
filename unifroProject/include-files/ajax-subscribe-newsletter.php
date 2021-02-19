<?php

    $response['status'] = 'error';

    if(isset($_POST['Email']))
    {
        if(!ValidateEmail($_POST['Email']))
        {
            $response['response_message'] = 'Please enter a valid email address';
        }
        else
        {
            // Let's check if email is already added in subscription list
            if(mysqli_num_rows(MysqlQuery("SELECT Email FROM newsletter_subscription WHERE Email = '".$_POST['Email']."' LIMIT 1")) == 1)
            {
                $response['response_message'] = 'Your email is already available in our subscription list';
            }
            else
            {
                // Let's check if email is registered in our members table
                if(mysqli_num_rows(MysqlQuery("SELECT MemberID FROM customers WHERE EmailID = '".$_POST['Email']."' LIMIT 1")) == 1)
                {
                    $response['response_message'] = 'You are our registered member. To receive email updates of our products, just sign in, go to Settings page and put a tick against the Newsletter Subscription';
                }
                else
                {
                    // Let's add the email in our subscription list
                    MysqlQuery("INSERT INTO newsletter_subscription (Email, AddedOn) VALUES ('".$_POST['Email']."', '".time()."')");

                    if(MysqlAffectedRows() == 1)
                    {
                        $response['status'] = 'success';
                        $response['response_message'] = 'Thank you for subscribing!<br> You would receive email updates of our products soon.';
                    }
                    else
                    {
                        $response['response_message'] = 'Something went wrong! Please try again. [LN 40]';
                    }
                }
            }
        }
    }
    else
    {
        $response['response_message'] = 'Invalid Access!';
    }

    echo json_encode($response);
?>