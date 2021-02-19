<?php
    include_once("../../../include-files/autoload-server-files.php");

    $SelectedPage = 'Client Orders';

    /*----------------------------------------------------
        FILTERS
    ------------------------------------------------------*/

    if($_GET['s'] != '')
    {
        $filter[] = "os.Status = '".$_GET['s']."'";
        $ActiveFilter['s'] = 'filtered';
    }
    if($_GET['ps'] != '')
    {
        $filter[] = "o.PaymentStatus = '".$_GET['ps']."'";
        $ActiveFilter['ps'] = 'filtered';
    }
    if(is_numeric($_GET['cat']))
    {
        $filter[] = "cat.CategoryID = '".$_GET['cat']."'";
        $ActiveFilter['cat'] = 'filtered';
    }
    if($_GET['q'] != '')
    {
        $_GET['q'] = CleanText($_GET['q']);
        $ActiveFilter['q'] = 'filtered';

        if(is_numeric($_GET['q']))
        {
            $filter[] = "o.OrderID = '".$_GET['q']."'";
        }
        else
        {
            $filter[] = "c.ClientName LIKE '%".$_GET['q']."%'";
        }
    }

    $ListOrders = "SELECT o.OrderID FROM client_orders o LEFT JOIN clients c ON c.ClientID = o.ClientID LEFT JOIN client_order_status os ON os.OrderID = o.OrderID";
    $ListOrders = $ListOrders.(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '');
    $ListOrders = MysqlQuery($ListOrders);
    $TotalRecords = mysqli_num_rows($ListOrders);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title><?=$SelectedPage?></title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>

        <script>
        $(document).ready(function()
        {

        })

        </script>

    </head>
    <body>

        <?php include(_ROOT._AdminIncludesDir."admin-common-scripts.php"); ?>

        <div id="wrapper">
            <?php include(_ROOT._AdminIncludesDir."admin-header.php"); ?>
            <?php include(_ROOT._AdminIncludesDir."admin-sidebar.php"); ?>

            <!-- ==============================================================
                Start of Right content here
            ============================================================== -->
            <div class="content-page">

                <div class="content">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="filter-box">
                                    <form>
                                        <ul class="filters list-inline">
                                            <li>
                                                <i class="fa fa-filter"></i> Filter
                                            </li>

                                            <li style="width: 180px">
                                                <select class="selectric xs <?=$ActiveFilter['s']?>" name="s">
                                                    <option value="">Status</option>
                                                    <?php
                                                    foreach($MasterOrderStatus as $value)
                                                    {
                                                        echo '<option'.($_GET['s'] == $value ? ' selected' : '').' value="'.$value.'">'.$value.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </li>
                                            <li style="width: 180px">
                                                <select class="selectric xs <?=$ActiveFilter['ps']?>" name="ps">
                                                    <option value="">Payment Status</option>
                                                    <option<?=$_GET['ps'] == 'Pending' ? ' selected' : ''?> value="Pending">Pending</option>
                                                    <option<?=$_GET['ps'] == 'Successful' ? ' selected' : ''?> value="Successful">Successful</option>
                                                    <option<?=$_GET['ps'] == 'Failed' ? ' selected' : ''?> value="Failed">Failed</option>
                                                </select>
                                            </li>
                                            <li>
                                                <input type="text" class="form-control xs <?=$ActiveFilter['q']?>" name="q" placeholder="Order ID / Client Name" value="<?=$_GET['q']?>">
                                            </li>
                                            <li>
                                                <button class="btn btn-info btn-xs"><i class="fa fa-search"></i> &nbsp;Search</button>
                                                <?=ClearFilter()?>
                                            </li>
                                        </ul>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-box">
                                    <form action="/admin/ajax/delete-selected/" id="OrderForm">
                                        <input type="hidden" name="option" value="order" />
                                        <input type="hidden" name="FieldName" value="OrderID" />
                                        <table class="table" data-expand-first="false" data-toggle-column="last">
                                            <thead>
                                                <tr>
                                                    <th data-breakpoints="md sm xs">Date</th>
                                                    <th data-breakpoints="md sm xs">Order ID</th>
                                                    <th data-breakpoints="md sm xs">Products</th>
                                                    <th data-breakpoints="md sm xs">Client</th>
                                                    <th data-breakpoints="md sm xs">Payment Status</th>
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

                                            	    $ListOrders = "SELECT o.OrderID, o.FinalTotal, o.OrderDate, o.PaymentStatus, os.Status, c.ClientName FROM client_orders o
                                                                    LEFT JOIN clients c ON c.ClientID = o.ClientID
                                                                    LEFT JOIN client_order_status os ON os.OrderID = o.OrderID";
                                                    $ListOrders = $ListOrders.(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '')." ORDER BY o.OrderID DESC LIMIT ".$page.", ".$PerPage;
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
                                                                <td>'.$row['PaymentStatus'].'</td>
                                                                <td>'.$row['Status'].'</td>
                                                                <td>'.FormatAmount($row['FinalTotal']).'</td>
                                                                <td>
                                                                    <div class="action-list">
                                                                        <a href="#"><i class="ti-angle-double-down"></i></a>
                                                                        <ul class="sub-menu">
                                                                            <li><a href="details/?id='.$row['OrderID'].'">View Order</a></li>
                                                                            <li><a href="#" data-id="'.$row['OrderID'].'" onclick="DeleteOrder(this)">Delete Order</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </td>
                                                            </tr>';
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </form>
                                </div>

                                <?=$navs != '' ? '<div class="card-box">'.$navs.'</div>' : ''?>

                            </div>
                        </div>

                    </div>   <!-- container -->

                </div>   <!-- content -->
            </div>

            <?php include(_ROOT._AdminIncludesDir."footer.php"); ?>

            <!-- ==============================================================
                End of Right content here
            ============================================================== -->
        </div>

        <script src="/js/lightbox.min.js"></script>

	</body>
</html>