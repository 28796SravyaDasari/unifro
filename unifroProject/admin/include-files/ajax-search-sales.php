<?php

    include_once("../../include-files/autoload-server-files.php");

    $MaxResults = 10;

    if(isset($_GET['term']))
    {
        $_GET['term'] = array_map('trim', explode(' ', strtolower(trim($_GET['term']))));

        /***************************LETS CHECK IF WE CAN FIND ANY REUSLT FROM DIRECTORS TABLE*******************************/
        $query = "SELECT SalesID, FirstName, LastName FROM sales WHERE FirstName LIKE '%".$_GET['term'][0]."%' OR LastName LIKE '%".$_GET['term'][0]."%'";
        
        $res = MysqlQuery($query);
        if(mysqli_num_rows($res) > 0)
        {
            for( ; $row = mysqli_fetch_assoc($res); )
            {
                $Name = $row['FirstName'].' '.$row['LastName'];

                $MatchedText = $Name;
                $ResultArray = array_filter(explode(' ', strtolower($MatchedText)));
                $MatchesFound = count(array_intersect($_GET['term'], $ResultArray));

                //	LETS CHECK IF THERE'S ANY WHOLE MATCH
                if(count(array_filter(array_diff($ResultArray, $_GET['term']))) == 0 || stripos($MatchedText, implode(' ', $_GET['term']), 0) !== false)
                {
                    $MatchesFound = 1000;
                    $results[$Name.'~'.$row['SalesID']] = $MatchesFound - strlen($MatchedText);
                }
                else
                {
                    $results[$Name.'~'.$row['SalesID']] = $MatchesFound;
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
                $output['suggestions'][] = array('value' => $values[0], 'data' => array('category' => 'Search Results', 'dataValue' => $values[1] ));
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