<?php
    include_once("../include-files/autoload-server-files.php");
    CheckLogin('', '/sales-login/');

    $ListOrders = "SELECT o.OrderID FROM client_orders o LEFT JOIN clients c ON c.ClientID = o.ClientID WHERE c.ClientID = '".$ClientID."'";
    $ListOrders = $ListOrders.(count($filter) > 0 ? implode(' AND ', $filter) : '');
    $ListOrders = MysqlQuery($ListOrders);
    $TotalRecords = mysqli_num_rows($ListOrders);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description" content="">

        <title>Client Products - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            $('.responsive-tabs').responsiveTabs({
                accordionOn: ['xs', 'sm']
            });

            LoadCartProducts();

        }); // END OF DOCUMENT READY
        </script>

    </head>


    <body class="bg-lightgray">

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card-box">
                        <a class="btn btn-white pull-right" href="/sales/"><i class="fa fa-long-arrow-left"></i> Back to All Clients</a>
                        <h3 class="mg-0"><?=GetClientDetails($ClientID, 'ClientName')?></h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <ul class="nav nav-tabs responsive-tabs" style="width: 100%;">
                        <li class="tab"><a href="/sales/clients/<?=$ClientID?>/">Saved Products</a></li>
                        <li class="active tab"><a href="/sales/clients/orders/<?=$ClientID?>/">Orders</a></li>
                    </ul>
                    <div class="tab-content">
                        <!-----------------------------
                            START OF CLIENT ORDERS
                        ------------------------------>
                        <div class="tab-pane active">
                            <div class="clearfix">
                                <div class="col-lg-12">
                                    <table class="table footable" data-expand-first="false" data-toggle-column="last" id="CartTable">
                                        <thead>
                                            <tr>
                                                <th data-breakpoints="md sm xs">Date</th>
                                                <th data-breakpoints="md sm xs">Order ID</th>
                                                <th data-breakpoints="md sm xs">Products</th>
                                                <th data-breakpoints="md sm xs">Client</th>
                                                <th data-breakpoints="md sm xs">Status</th>
                                                <th data-breakpoints="md sm xs">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if($TotalRecords > 0)
                                        	{
                                        	    $PerPage = 5;
                                				$page = !is_numeric($_GET['page'])?0:($_GET['page'] - 1);
                                				$page = $page * $PerPage;

                                				$ShowTotalPages = $TotalRecords > $PerPage ? true : false;
                                				$navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total Orders - ', $ShowTotalPages);

                                        	    $ListOrders = "SELECT o.OrderID, o.FinalTotal, o.OrderDate, os.Status, c.ClientName FROM client_orders o
                                                                LEFT JOIN clients c ON c.ClientID = o.ClientID
                                                                LEFT JOIN client_order_status os ON os.OrderID = o.OrderID WHERE c.ClientID = '".$ClientID."'";
                                                $ListOrders = $ListOrders.(count($filter) > 0 ? implode(' AND ', $filter) : '')." ORDER BY o.OrderID DESC LIMIT ".$page.", ".$PerPage;
                                    			$res = MysqlQuery($ListOrders);

                                                for(; $row = mysqli_fetch_assoc($res); )
                                                {
                                                    // GET THE NO OF PRODUCTS ADDED TO THE ORDER
                                                    $NoOfProducts = mysqli_num_rows(MysqlQuery("SELECT OrderID FROM client_order_details WHERE OrderID = '".$row['OrderID']."'"));

                                                    echo '<tr id="row'.$row['OrderID'].'">
                                                            <td>'.FormatDateTime('d', $row['OrderDate']).'</td>
                                                            <td>'.$row['OrderID'].'</td>
                                                            <td>'.$NoOfProducts.'</td>
                                                            <td>'.$row['ClientName'].'</td>
                                                            <td>'.$row['Status'].'</td>
                                                            <td>'.FormatAmount($row['FinalTotal']).'</td>
                                                        </tr>';
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>

                                </div>

                            </div>
                        </div>
                        <!-----------------------------
                            END OF CLIENT ORDERS
                        ------------------------------>

                    </div>  <!-- END OF TAB CONTENT -->

                </div>  <!-- END OF COL -->

            </div>  <!-- END OF ROW -->

        </div>  <!-- END OF CONTAINER -->

    </body>
</html>