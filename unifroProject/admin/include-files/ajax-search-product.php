<?php

	$MaxResults = 10;

	if(isset($_GET['term']))
	{
		$_GET['term'] = array_map('trim', explode(' ', strtolower(trim($_GET['term']))));

		/***************************LETS CHECK IF WE CAN FIND ANY REUSLT*******************************/
        if($_GET['combo'] == 'n')
		    $query = "SELECT ProductID, ProductName, Rate, Weight, WeightUnit, TaxRate FROM products WHERE Combo != 'y' AND ProductName LIKE '%".$_GET['term'][0]."%'";
        else
		    $query = "SELECT ProductID, ProductName, Rate, Weight, WeightUnit, TaxRate FROM products WHERE ProductName LIKE '%".$_GET['term'][0]."%'";
            
		for($c = 1; $c < count($_GET['term']); $c++)
		{
			$query .= " OR ProductName LIKE '%".$_GET['term'][$c]."%'";
		}
		$res = MysqlQuery($query);
		if(mysqli_num_rows($res) > 0)
		{
			for( ; $row = mysqli_fetch_assoc($res); )
			{
				$MatchedText = $row['ProductName'];
				$ResultArray = array_filter(explode(' ', strtolower($MatchedText)));
				$MatchesFound = count(array_intersect($_GET['term'], $ResultArray));

				//	LETS CHECK IF THERE'S ANY WHOLE MATCH
				if(count(array_filter(array_diff($ResultArray, $_GET['term']))) == 0 || stripos($MatchedText, implode(' ', $_GET['term']), 0) !== false)
				{
				    $data = $row['ProductID'].'~'.$row['Rate'].'~'.$row['Weight'].'~'.$row['WeightUnit'].'~'.$row['TaxRate'];

					$MatchesFound = 1000;
					$results[$MatchedText.'~'.$data] = $MatchesFound - strlen($MatchedText);
				}
				else
				{
					$results[$MatchedText.'~'.$data] = $MatchesFound;
				}
			}
		}

		if(count($results) > 0)
		{
			arsort($results);
			$results = array_keys($results);
			for($c = 0; $c < count($results) && $c <= $MaxResults; $c++)
			{
				$values = explode('~', $results[$c]);
				$output['suggestions'][] = array('value' => $values[0], 'data' => array(
                                                                                            'category'  => 'Search Results',
                                                                                            'ProductID' => $values[1],
                                                                                            'Rate'      => $values[2],
                                                                                            'Weight'    => $values[3],
                                                                                            'WeightUnit'=> $values[4],
                                                                                            'TaxRate'   => $values[5]
                                                                                        ));
			}
			echo json_encode($output);
		}
		else
		{
			$output['suggestions'][] = array('value' => 'No Records Found!', 'data' => 'null');
			echo json_encode($output);
		}
	}
	else
	{
		echo 'invalid access!';
	}
?>