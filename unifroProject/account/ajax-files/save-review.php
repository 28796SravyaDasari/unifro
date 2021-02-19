<?php
    $response['status']         = 'error';

	if($LoggedIn)
	{
	    if(!is_numeric($_POST['ProductRating']))
        {
            $response['response_message']    = 'Please select your rating for the Product!';
        }
        else
        {
            //	FIRST TRY TO UPDATE THE RATINGS ASSUMING THE PRODUCT WAS ALREADY RATED BY THE SAME USER AS WE CAN HAVE A SINGLE ROW FOR A USER FOR A PRODUCT
            MysqlQuery("UPDATE product_ratings SET Rating = '".$_POST['ProductRating']."', UpdatedOn = '".time()."' WHERE ProductID = '".$_POST['ProductID']."' AND MemberID = '".$MemberID."' LIMIT 1");
            if(MysqlAffectedRows() == 0)
            {
            	MysqlQuery("INSERT INTO product_ratings (MemberID, ProductID, Rating, AddedOn, UpdatedOn)
            	VALUES ('".$MemberID."', '".$_POST['ProductID']."', '".$_POST['ProductRating']."', '".time()."', '0')");
            }

            if(MysqlAffectedRows() == 1)
            {
                $activity   = 'Product Rated';
                $table      = 'product_ratings';
                $Desc       = GetProductDetails($_POST['ProductID'], 'ProductName').' has been rated as '.$_POST['ProductRating'];

                RecordMemberActivity($activity, $table, $_POST['ProductID'], $Desc);

            	//	NOW LET'S UPDATE THE UPDATED 'OVERALL' RATING IN THE PRODUCTS TABLE
            	MysqlQuery("UPDATE products SET Rating = (SELECT ROUND(SUM(Rating)/COUNT(RatingID), 1) FROM product_ratings WHERE ProductID = '".$_POST['ProductID']."')
                            WHERE ProductID = '".$_POST['ProductID']."' LIMIT 1");

                if($_POST['review'] != '')
                {
                    $res = MysqlQuery("INSERT INTO product_reviews (ProductID, Review, ReviewBy, ReviewDate)
                    VALUES ('".$_POST['ProductID']."', '".$_POST['review']."', '".$MemberID."', '".time()."')");
                    if(MysqlAffectedRows() == 1)
                    {
                        $response['status']             = 'success';
                        $response['response_message']   = 'Thank you! You review will be made live soon.';

                        $activity   = 'Review Posted';
                        $table      = 'product_reviews';
                        $Desc       = 'Post a review for '.GetProductDetails($_POST['ProductID'], 'ProductName');

                        RecordMemberActivity($activity, $table, $_POST['ProductID'], $Desc);
                    }
                    else
                    {
                        $response['response_message']    = 'Error Occurred! [LN 40]'.$res;
                    }
                }
                else
                {
                    $response['status']             = 'success';
                    $response['response_message']    = 'Rating submitted successfully! Thank you!';
                }
            }
            else
            {
                $response['response_message']    = 'Error Occurred! [LN 60]';
            }
        }
	}
    else
	{
		$response['status']         = 'login';
	}
    echo json_encode($response);
?>