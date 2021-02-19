<?php
    include_once("../../include-files/autoload-server-files.php");
    CheckLogin();
    
    $ActivePage = 'Orders';

    $ListOrders = "SELECT o.OrderID FROM customer_orders o LEFT JOIN customer_order_status os ON os.OrderID = o.OrderID WHERE o.MemberID = '".$MemberID."'";
    $ListOrders = $ListOrders.(count($filter) > 0 ? ' AND '.implode(' AND ', $filter) : '');
    $ListOrders = MysqlQuery($ListOrders);
    $TotalRecords = mysqli_num_rows($ListOrders);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <META name="robots" content="noindex,nofollow" />

        <title>My Account - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

    </head>


    <body>

        <?php include_once(_ROOT."/include-files/common-scripts.php"); ?>
        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container">
            <div class="row">
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?=_HOST?>"><i class="fa fa-home"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="/account/">My Account</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Orders</li>
                    </ol>
                </nav>
            </div>

            <div class="row mg-t-30">
                <div class="col-lg-12">
                    <div class="card-box">
                        <form action="/admin/ajax/delete-selected/" id="OrderForm">
                            <input type="hidden" name="option" value="order" />
                            <input type="hidden" name="FieldName" value="OrderID" />
                            <table class="table" data-expand-first="false" data-toggle-column="last">
                                <thead>
                                    <tr>
                                        <th data-breakpoints="md sm xs">Order Date</th>
                                        <th data-breakpoints="md sm xs">Order ID</th>
                                        <th data-breakpoints="md sm xs">Products</th>
                                        <th data-breakpoints="md sm xs">Status</th>
                                        <th data-breakpoints="md sm xs">Total</th>
                                        <th data-breakpoints="md sm xs">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if($TotalRecords > 0)
                                	{
                                	    $PerPage = 20;
                        				$page = !is_numeric($_GET['page'])?0:($_GET['page'] - 1);
                        				$page = $page * $PerPage;

                        				$ShowTotalPages = $TotalRecords > $PerPage ? true : false;
                        				$navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total Orders - ', $ShowTotalPages);

                                	    $ListOrders = "SELECT o.OrderID, o.FinalTotal, o.OrderDate, os.Status FROM customer_orders o
                                                        LEFT JOIN customer_order_status os ON os.OrderID = o.OrderID WHERE o.MemberID = '".$MemberID."'";
                                        $ListOrders = $ListOrders.(count($filter) > 0 ? ' AND '.implode(' AND ', $filter) : '')." ORDER BY o.OrderID DESC LIMIT ".$page.", ".$PerPage;
                            			$res = MysqlQuery($ListOrders);

                                        for(; $row = mysqli_fetch_assoc($res); )
                                        {
                                            // GET THE NO OF PRODUCTS ADDED TO THE ORDER
                                            $NoOfProducts = mysqli_num_rows(MysqlQuery("SELECT OrderID FROM customer_order_details WHERE OrderID = '".$row['OrderID']."'"));

                                            echo '<tr id="row'.$row['OrderID'].'">
                                                    <td>'.FormatDateTime('d', $row['OrderDate']).'</td>
                                                    <td>'.$row['OrderID'].'</td>
                                                    <td>'.$NoOfProducts.'</td>
                                                    <td>'.$row['Status'].'</td>
                                                    <td>'.FormatAmount($row['FinalTotal']).'</td>
                                                    <td>
                                                        <a class="btn btn-primary" href="/account/orders/'.$row['OrderID'].'/">View Order</a>
                                                    </td>
                                                </tr>';
                                        }
                                    }
                                    else
                                    {
                                        echo '<tr><td class="no-data" colspan="6">No Records Found</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </form>
                    </div>

                    <?=$navs != '' ? '<div class="card-box">'.$navs.'</div>' : ''?>

                </div>
            </div>
        </div>
    </body>
</html>