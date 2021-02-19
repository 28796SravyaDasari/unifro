<?php
    $response['status']         = 'error';

	if($LoggedIn)
	{
	    if(!is_numeric($_POST['id']))
        {
            $response['response_message']    = 'Please refresh the page and try again.';
        }
        else
        {
            $activity   = 'Review Deleted';
            $table      = 'product_reviews';
            $Desc       = 'Review deleted for Product: '.GetProductDetails($_POST['id'], 'ProductName');

            MysqlQuery("DELETE FROM product_reviews WHERE ProductID = '".$_POST['id']."' AND ReviewBy = '".$MemberID."' LIMIT 1");
            if(MysqlAffectedRows() == 1)
            {
                MysqlQuery("DELETE FROM product_ratings WHERE MemberID = '".$MemberID."' AND ProductID = '".$_POST['id']."' LIMIT 1");

                RecordMemberActivity($activity, $table, $_POST['ProductID'], $Desc);

                $response['status']             = 'success';
            }
            else
            {
                $response['response_message']    = 'Something went wrong! Please try again.';
            }
        }
	}
    else
	{
		$response['status']         = 'login';
	}
    echo json_encode($response);
?>