<?php

    $response['status'] = 'success';

    if($_POST['CategoryID'] > 0)
    {
        /*------------
            Sorting
        -------------*/
        if($_POST['SortBy']['Price'] == 'l')
        {
            $SortBy = "ORDER BY p.Rate";
        }
        elseif($_POST['SortBy']['Price'] == 'h')
        {
            $SortBy = "ORDER BY p.Rate DESC";
        }
        else
        {
            $SortBy = "ORDER BY p.ProductID DESC";
        }

        /*------------
            Filters
        -------------*/
        if($_POST['Filter']['Price'] != '')
        {
            $price = explode(',', $_POST['Filter']['Price']);

            $filter[] = "(p.Rate BETWEEN '".$price[0]."' AND '".$price[1]."')";
        }
        if(isset($_POST['Filter']['Color']))
        {
            $filter[] = "p.Color IN('".implode("','", $_POST['Filter']['Color'])."')";
        }
        if(isset($_POST['Filter']['Discount']))
        {
            $filter[] = "p.Discount IN('".implode("','", $_POST['Filter']['Discount'])."')";
        }
        if(isset($_POST['Filter']['Size']))
        {
            $filter[] = "ps.Size IN(".implode(",", $_POST['Filter']['Size']).") AND ps.Quantity > 0";
        }

        $GetProducts = "SELECT pc.ProductID FROM product_categories pc
                        LEFT JOIN products p ON p.ProductID = pc.ProductID
                        LEFT JOIN product_stock ps ON ps.ProductID = pc.ProductID
                        LEFT JOIN product_images pi ON pi.ProductID = p.ProductID
                        WHERE pc.CategoryID = '".$_POST['CategoryID']."' AND pi.DefaultImage = '1' AND p.Status = '1'".
                        (count($filter) > 0 ? ' AND '.implode(' AND ', $filter) : '')." GROUP BY p.ProductID";

        $GetProducts    = MysqlQuery($GetProducts);
        $TotalProducts  = mysqli_num_rows($GetProducts);
    }



    if($TotalProducts > 0)
    {
        $PerPage = 12;
        $page = $_POST['page'] > 0 ? ($_POST['page'] - 1) : 0;
        $page = $page * $PerPage;

        $GetProducts = "SELECT pc.ProductID, pi.FileName, p.ProductName, p.ProductURL, p.Rate, p.Discount, p.DiscountType FROM product_categories pc
                        LEFT JOIN products p ON p.ProductID = pc.ProductID
                        LEFT JOIN product_stock ps ON ps.ProductID = pc.ProductID
                        LEFT JOIN product_images pi ON pi.ProductID = p.ProductID
                        WHERE pc.CategoryID = '".$_POST['CategoryID']."' AND pi.DefaultImage = '1' AND p.Status = '1'".
                        (count($filter) > 0 ? ' AND '.implode(' AND ', $filter) : '')." GROUP BY p.ProductID ".$SortBy." LIMIT ".$page.", ".$PerPage;

        $GetProducts = MysqlQuery($GetProducts);
        $ProductDetails = MysqlFetchAll($GetProducts);

        foreach($ProductDetails as $product)
        {
            $response['response_message'] .= ProductWidget($product);
        }

        //$response['response_message'] .= '<div class="page-selection"></div>';

        $response['count'] = $TotalProducts;
        $response['total'] = ceil($TotalProducts/$PerPage);
        $response['page'] = !is_numeric($_POST['page']) ? 1 : $_POST['page'];
    }
    else
    {
        $response['response_message'] = '<div class="no-data">No Products Found</div>';
    }

    $response['clear'] = count($filter) > 0 ? true : false;

    echo json_encode($response);
?>