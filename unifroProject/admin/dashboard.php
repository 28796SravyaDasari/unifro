<?php

    $SelectedPage = 'Dashboard';

    if($_GET['date'] != '')
    {
        $DateRange  = explode(' to ',$_GET['date']);
        $From       = strtotime($DateRange[0]);
        $To         = strtotime($DateRange[1]) != '' ? strtotime($DateRange[1]) : time();
    }
    else
    {
        $From           = strtotime("today");
        $To             = time();
        $_GET['date']   = date('d-M-Y');
    }

    if($_GET['sa'] > 0)
    {
        $filter[] = "s.SalesID = '".$_GET['sa']."'";
    }
    if($_GET['cl'] > 0)
    {
        $filter[] = "o.ClientID = '".$_GET['cl']."'";
    }
    if($_GET['l'] != '')
    {
        $filter[] = "o.ShippingCity = '".$_GET['l']."'";
    }
    if($_GET['cu'] > 0)
    {
        $filter[] = "o.MemberID = '".$_GET['cu']."'";
    }

    $PerPage = 15;
    $page = !is_numeric($_GET['page'])?0:($_GET['page'] - 1);
    $page = $page * $PerPage;

    /*----------------------------------------------------
            FOR CLIENTS
    ------------------------------------------------------*/
    $TotalAmount = 0;

    $ListClientOrders = "SELECT o.FinalTotal FROM client_orders o
                        LEFT JOIN client_order_status os ON os.OrderID = o.OrderID
                        LEFT JOIN clients c ON c.ClientID = o.ClientID
                        LEFT JOIN sales s ON s.SalesID = c.SalesID
                        WHERE os.Status = 'Completed' AND (o.OrderDate BETWEEN '".$From."' AND '".$To."')".(count($filter) > 0 ? ' AND '.implode(' AND ', $filter) : '');
    $ListClientOrders   = MysqlQuery($ListClientOrders);
    $TotalRecords       = mysqli_num_rows($ListClientOrders);
    if($TotalRecords > 0)
    {
        for(;$amount = mysqli_fetch_assoc($ListClientOrders);)
        {
            $TotalAmount = $TotalAmount + $amount['FinalTotal'];
        }
    }

    $TotalRecords   = mysqli_num_rows(MysqlQuery($ListClientOrders));

    $navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total Records - ', true);

    $ListClientOrders = "SELECT o.OrderID, o.FinalTotal, o.OrderDate, o.ShippingCity, os.Status, os.UpdatedOn, c.ClientName, c.ClientID, s.SalesID, s.FirstName, s.LastName FROM client_orders o
                        LEFT JOIN clients c ON c.ClientID = o.ClientID
                        LEFT JOIN client_order_status os ON os.OrderID = o.OrderID
                        LEFT JOIN sales s ON s.SalesID = c.SalesID
                        WHERE os.Status = 'Completed' AND (o.OrderDate BETWEEN '".$From."' AND '".$To."')".(count($filter) > 0 ? ' AND '.implode(' AND ', $filter) : '')." ORDER BY o.OrderID DESC LIMIT ".$page.", ".$PerPage;

    $ListClientOrders = MysqlFetchAll(MysqlQuery($ListClientOrders));


    /*----------------------------------------------------
            FOR CUSTOMERS
    ------------------------------------------------------*/
    if(isset($_GET['customer']))
    {
        $TotalAmount = 0;

        $ListCustomerOrders = "SELECT o.FinalTotal FROM customer_orders o
                        LEFT JOIN customer_order_status os ON os.OrderID = o.OrderID
                        LEFT JOIN customers c ON c.MemberID = o.MemberID
                        WHERE os.Status = 'Completed' AND (o.OrderDate BETWEEN '".$From."' AND '".$To."')";
        $ListCustomerOrders     = MysqlQuery($ListCustomerOrders);
        $TotalCustomerOrders    = mysqli_num_rows($ListCustomerOrders);
        if($TotalCustomerOrders > 0)
        {
            for(;$amount = mysqli_fetch_assoc($ListCustomerOrders);)
            {
                $TotalAmount = $TotalAmount + $amount['FinalTotal'];
            }
        }

        $customernavs = CreateNavs($TotalCustomerOrders, $PerPage, 'page', 9, 'Total Records - ', true);

        $ListCustomerOrders = "SELECT o.OrderID, o.FinalTotal, o.OrderDate, o.ShippingCity, os.Status, os.UpdatedOn, c.FirstName, c.LastName, c.MemberID FROM customer_orders o
                            LEFT JOIN customer_order_status os ON os.OrderID = o.OrderID
                            LEFT JOIN customers c ON c.MemberID = o.MemberID
                            WHERE os.Status = 'Completed' AND (o.OrderDate BETWEEN '".$From."' AND '".$To."')".(count($filter) > 0 ? ' AND '.implode(' AND ', $filter) : '')." ORDER BY o.OrderID DESC LIMIT ".$page.", ".$PerPage;

        $ListCustomerOrders = MysqlFetchAll(MysqlQuery($ListCustomerOrders));
    }

    $Uri = $_GET['date'].(isset($_GET['customer'])?'&customer=y':'');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description" content="">

        <title>Admin Panel</title>

        <?php include_once("include-files/common-css.php"); ?>
        <link rel="stylesheet" type="text/css" href="/css/slick.css"/>
        <link rel="stylesheet" type="text/css" href="/css/slick-theme.css"/>
        <link rel="stylesheet" type="text/css" href="/css/flatpickr.min.css"/>
        <link rel="stylesheet" type="text/css" href="/css/autocomplete-devbridge.css"/>

        <?php include_once("include-files/common-js.php"); ?>
        <script src="/js/jquery.bootstrap-responsive-tabs.min.js"></script>
        <script src="/js/moment.min.js"></script>
        <script src="/js/flatpickr.min.js"></script>
        <script src="/js/autocomplete-devbridge-min.js"></script>

        <script>
            $(document).ready(function()
            {
                $('.datepicker').flatpickr({
                    dateFormat: 'd-M-Y',
                    mode: "range",
                    maxDate: "today",
                    onChange : function() {
                        $('.datepicker').closest('form').submit();
                    }
                });

                $('#SalesAgents').autocomplete({
                            serviceUrl: '/admin/include-files/ajax-search-sales.php',
                            paramName: 'term',
                            groupBy: 'category',
                            minChars: 2,
                            onSearchStart: function(){ $("#SalesAgents").addClass('autocompleteProcessing'); },
                            onSearchComplete: function(){ $("#SalesAgents").removeClass('autocompleteProcessing'); },
                            onSelect: function (suggestion) {
                                if(suggestion.data.dataValue != 'null')
                                {
                                    window.location = "?date=<?=$_GET['date']?>&sa=" + suggestion.data.dataValue;
                                }
                            }
                });

                $('#Client').autocomplete({
                            serviceUrl: '/admin/include-files/ajax-search-client.php',
                            paramName: 'term',
                            groupBy: 'category',
                            minChars: 2,
                            onSelect: function (suggestion) {
                                if(suggestion.data.dataValue != 'null')
                                {
                                    window.location = "?date=<?=$_GET['date']?>&cl=" + suggestion.data.dataValue;
                                }
                            }
                });

                $('.SearchCity').autocomplete({
                            serviceUrl: '/admin/include-files/ajax-search-city.php',
                            paramName: 'term',
                            groupBy: 'category',
                            minChars: 2,
                            onSelect: function (suggestion) {
                                if(suggestion.data.dataValue != 'null')
                                {
                                    window.location = "?date=<?=$Uri?>&l=" + suggestion.data.dataValue;
                                }
                            }
                });

                $('#Customers').autocomplete({
                            serviceUrl: '/admin/include-files/ajax-search-customer.php',
                            paramName: 'term',
                            groupBy: 'category',
                            minChars: 2,
                            onSelect: function (suggestion) {
                                if(suggestion.data.dataValue != 'null')
                                {
                                    window.location = "?date=<?=$Uri?>&customer=y&cu=" + suggestion.data.dataValue;
                                }
                            }
                });
            })

        </script>

    </head>


    <body>

        <?php include(_ROOT._AdminIncludesDir."admin-common-scripts.php"); ?>

        <!-- Begin page -->
        <div id="wrapper">

            <?php include(_ROOT._AdminIncludesDir."admin-header.php"); ?>
            <?php include(_ROOT._AdminIncludesDir."admin-sidebar.php"); ?>

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <!-- Start content -->
                <div class="content">
                    <div class="container">

                        <div class="row">
                            <div class="col-md-6 col-lg-3">
                                <div class="dashboard-widget card-box fadeInDown animated">
                                    <h4 class="title"><i class="ti-user"></i> &nbsp;Total Registrations</h4>
                                    <div class="pull-left">
                                        <a href="/admin/clients/">
                                            <h3 class="text-dark"><b class="counter"><?=TotalRegistrations('client')?></b></h3>
                                            <p class="text-primary">Clients</p>
                                        </a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="/admin/customers/">
                                            <h3 class="text-dark"><b class="counter"><?=TotalRegistrations('customer')?></b></h3>
                                            <p class="text-success">Customers</p>
                                        </a>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <div class="dashboard-widget card-box fadeInDown animated">
                                    <h4 class="title"><i class="ti-shopping-cart"></i> &nbsp;Total Orders</h4>
                                    <div class="pull-left">
                                        <a href="/admin/clients/orders/">
                                            <h3 class="text-dark"><b class="counter"><?=TotalOrders('client')?></b></h3>
                                            <p class="text-primary">Client</p>
                                        </a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="/admin/customers/orders/">
                                            <h3 class="text-dark"><b class="counter"><?=TotalOrders('customer')?></b></h3>
                                            <p class="text-success">Customer</p>
                                        </a>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <div class="dashboard-widget card-box fadeInDown animated">
                                    <h4 class="title"><i class="ti-shopping-cart-full"></i> &nbsp;Abandoned Cart</h4>
                                    <div class="pull-left">
                                        <a href="/admin/abandoned-cart/clients/">
                                            <h3 class="text-dark"><b class="counter"><?=TotalAbandonedCart('client')?></b></h3>
                                            <p class="text-primary">Client</p>
                                        </a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="/admin/abandoned-cart/customers/">
                                            <h3 class="text-dark"><b class="counter"><?=TotalAbandonedCart('customer')?></b></h3>
                                            <p class="text-success">Customer</p>
                                        </a>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <div class="dashboard-widget card-box fadeInDown animated">
                                    <h4 class="title"><i class="fa fa-rupee"></i> &nbsp;Total Sales</h4>
                                    <div class="pull-left">
                                        <a href="/admin/clients/orders/?s=Completed">
                                            <h3 class="text-dark"><b class="counter"><?=TotalSales('client')?></b></h3>
                                            <p class="text-primary">Client</p>
                                        </a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="/admin/customers/orders/?s=Completed">
                                            <h3 class="text-dark"><b class="counter"><?=TotalSales('customer')?></b></h3>
                                            <p class="text-success">Customer</p>
                                        </a>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-box nav-tabs-filled">
                                    <div class="font21 bold mg-b-10">Sales Report</div>
                                    <div>
                                        <form method="get">
                                            <div class="input-group pull-right" style="max-width: 300px">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                <input type="text" class="form-control datepicker" name="date" value="<?=$_GET['date']?>">
                                            </div>
                                        </form>
                                    </div>

                                    <ul class="nav nav-tabs tabs responsive-tabs mg-t-20" style="width: 100%;">
                                        <li class="tab<?=!isset($_GET['customer'])?' active' : ''?>">
                                            <a href="?date=<?=$_GET['date']?>" onclick="ShowProcessing()">
                                                Client Sales
                                            </a>
                                        </li>
                                        <li class="tab<?=isset($_GET['customer'])?' active' : ''?>">
                                            <a href="?customer=y&date=<?=$_GET['date']?>" onclick="ShowProcessing()">
                                                Customer Sales
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane<?=!isset($_GET['customer'])?' active' : ''?>" id="home-2">
                                            <table class="table" data-expand-first="false" data-toggle-column="last">
                                                <thead>
                                                    <tr>
                                                        <th data-breakpoints="md sm xs">Order ID</th>
                                                        <th>Order Date</th>
                                                        <th data-breakpoints="xs">Client ID</th>
                                                        <th data-breakpoints="md sm xs" class="dropdown">
                                                            <span class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true" style="cursor:default">
                                                                Client Name &nbsp;<span class="fa fa-caret-down"></span>
                                                            </span>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="#" onclick="DropParam('cl')">Show All</a></li>
                                                                <li>
                                                                    <input type="text" class="form-control" id="Client" placeholder="Type Client Name">
                                                                </li>
                                                            </ul>
                                                        </th>
                                                        <th class="dropdown" data-breakpoints="md sm xs">
                                                            <span class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true" style="cursor:default">
                                                                Location &nbsp;<span class="fa fa-caret-down"></span>
                                                            </span>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="#" onclick="DropParam('l')">Show All</a></li>
                                                                <li>
                                                                    <input type="text" class="form-control SearchCity" placeholder="Type Location Name">
                                                                </li>
                                                            </ul>
                                                        </th>
                                                        <th class="dropdown" data-breakpoints="md sm xs">
                                                            <span class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true" style="cursor:default">
                                                                Sales Executive &nbsp;<span class="fa fa-caret-down"></span>
                                                            </span>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="#" onclick="DropParam('sa')">Show All</a></li>
                                                                <li>
                                                                    <input type="text" class="form-control" id="SalesAgents" placeholder="Type Executive Name">
                                                                </li>
                                                            </ul>
                                                        </th>
                                                        <th data-breakpoints="md sm xs">Sale Date</th>
                                                        <th class="text-right" data-breakpoints="md sm xs">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php

                                                    foreach($ListClientOrders as $row)
                                                    {
                                                        echo '<tr>
                                                                <td>'.$row['OrderID'].'</td>
                                                                <td>'.FormatDateTime('d',$row['OrderDate']).'</td>
                                                                <td>'.$row['ClientID'].'</td>
                                                                <td>'.$row['ClientName'].'</td>
                                                                <td>'.$row['ShippingCity'].'</td>
                                                                <td>'.$row['FirstName'].' '.$row['LastName'].'</td>
                                                                <td>'.FormatDateTime('d',$row['UpdatedOn']).'</td>
                                                                <td class="text-right">'.FormatAmount($row['FinalTotal']).'</td>
                                                            </tr>';
                                                    }
                                                    ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="7">
                                                            <?=$navs != '' ? $navs : ''?>
                                                        </td>
                                                        <td class="font21 bold text-right">Sale Amount - <?=FormatAmount($TotalAmount)?></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <div class="tab-pane<?=isset($_GET['customer'])?' active' : ''?>" id="profile-2">
                                            <table class="table" data-expand-first="false" data-toggle-column="last">
                                                <thead>
                                                    <tr>
                                                        <th>Order ID</th>
                                                        <th>Order Date</th>
                                                        <th data-breakpoints="xs">Customer ID</th>
                                                        <th class="dropdown">
                                                            <span class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true" style="cursor:default">
                                                                Customer Name &nbsp;<span class="fa fa-caret-down"></span>
                                                            </span>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="#" onclick="DropParam('cu')">Show All</a></li>
                                                                <li>
                                                                    <input type="text" class="form-control" id="Customers" placeholder="Type Customer Name">
                                                                </li>
                                                            </ul>
                                                        </th>
                                                        <th class="dropdown">
                                                            <span class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true" style="cursor:default">
                                                                Location &nbsp;<span class="fa fa-caret-down"></span>
                                                            </span>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="#" onclick="DropParam('l')">Show All</a></li>
                                                                <li>
                                                                    <input type="text" class="form-control SearchCity" placeholder="Type Location Name">
                                                                </li>
                                                            </ul>
                                                        </th>
                                                        <th data-breakpoints="md sm xs">Sale Date</th>
                                                        <th class="text-right" data-breakpoints="md sm xs">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    foreach($ListCustomerOrders as $row)
                                                    {
                                                        echo '<tr>
                                                                <td>'.$row['OrderID'].'</td>
                                                                <td>'.FormatDateTime('d',$row['OrderDate']).'</td>
                                                                <td>'.$row['MemberID'].'</td>
                                                                <td>'.$row['FirstName'].' '.$row['LastName'].'</td>
                                                                <td>'.$row['ShippingCity'].'</td>
                                                                <td>'.FormatDateTime('d',$row['UpdatedOn']).'</td>
                                                                <td class="text-right">'.FormatAmount($row['FinalTotal']).'</td>
                                                            </tr>';
                                                    }
                                                    ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="6">
                                                            <?=$customernavs != '' ? $customernavs : ''?>
                                                        </td>
                                                        <td class="font21 bold text-right">Sale Amount - <?=FormatAmount($TotalAmount)?></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- End Right content here -->
            <!-- ============================================================== -->

        </div>
        <!-- END wrapper -->



    </body>
</html>