<?php

    $SelectedPage = GetSalesAgentName($_GET['id']);

    /*----------------------------------------------------
        FILTERS
    ------------------------------------------------------*/

    if($_GET['s'] != '')
    {
        $filter[] = "Status = '".$_GET['s']."'";
        $ActiveFilter['s'] = 'filtered';
    }
    if($_GET['q'] != '')
    {
        $_GET['q'] = CleanText($_GET['q']);
        $ActiveFilter['q'] = 'filtered';

        if(is_numeric($_GET['q']))
        {
            $filter[] = "ClientID = '".$_GET['q']."'";
        }
        else
        {
            $filter[] = "(ClientName LIKE '%".$_GET['q']."%')";
        }
    }

    // FETCH THE CLIENT DETAILS OF THE SALES AGENTS
    $GetClients = "SELECT ClientID FROM clients WHERE SalesID = '".$_GET['id']."'".(count($filter) > 0 ? ' AND '.implode(' AND ', $filter) : '');
    $GetClients = MysqlQuery($GetClients);
    $TotalRecords = mysqli_num_rows($GetClients);

    ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title><?=$SelectedPage?></title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <link href="/css/lightbox.css" rel="stylesheet" />

        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>

        <script>
        $(document).ready(function(){
            $('input[name=CheckAll]').on('change', function(){
                $('input[name*=FabricID]').prop('checked', this.checked);
            });

            lightbox.option({ 'showImageNumberLabel': false, 'wrapAround': true, alwaysShowNavOnTouchDevices: true })
        })
        </script>
    </head>
    <body>

        <?php include_once(_ROOT._AdminIncludesDir."admin-common-scripts.php"); ?>

        <div id="wrapper">
            <?php include_once(_ROOT._AdminIncludesDir."admin-header.php"); ?>
            <?php include_once(_ROOT._AdminIncludesDir."admin-sidebar.php"); ?>

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
                                            <li>
                                                <select class="selectric xs <?=$ActiveFilter['s']?>" name="s">
                                                    <option value="">Status</option>
                                                    <option<?=$_GET['s'] == 'y' ? ' selected' : ''?> value="y">Active</option>
                                                    <option<?=$_GET['s'] == 'n' ? ' selected' : ''?> value="n">Inactive</option>
                                                </select>
                                            </li>
                                            <li>
                                                <input type="text" class="form-control xs <?=$ActiveFilter['q']?>" name="q" value="<?=$_GET['q']?>">
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
                                    
                                    <form action="/admin/ajax/delete-selected/" id="FabricForm">
                                        <input type="hidden" name="option" value="Fabric" />
                                        <input type="hidden" name="FieldName" value="FabricID" />
                                        <table class="table" data-expand-first="false" data-toggle-column="last">
                                            <thead>
                                                <tr>
                                                    <th data-breakpoints="md sm xs">Client ID</th>
                                                    <th data-breakpoints="md sm xs">Client Name</th>
                                                    <th data-breakpoints="md sm xs">Email</th>
                                                    <th data-breakpoints="md sm xs">Mobile</th>
                                                    <th data-breakpoints="md sm xs">Registered On</th>
                                                    <th data-breakpoints="md sm xs">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if($TotalRecords > 0)
                                            	{
                                            	    $PerPage = 10;
                                    				$page = !is_numeric($_GET['page'])?0:($_GET['page'] - 1);
                                    				$page = $page * $PerPage;

                                    				$ShowTotalPages = $TotalRecords > $PerPage ? true : false;
                                    				$navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total Clients - ', $ShowTotalPages);

                                            	    $GetClients = "SELECT * FROM clients WHERE SalesID = '".$_GET['id']."'";
                                                    $GetClients = $GetClients.(count($filter) > 0 ? ' AND '.implode(' AND ', $filter) : '')." ORDER BY ClientID DESC LIMIT ".$page.", ".$PerPage;
                                        			$res = MysqlQuery($GetClients);

                                                    for(; $row = mysqli_fetch_assoc($res); )
                                                    {
                                                        echo '<tr id="row'.$row['ClientID'].'">
                                                                <td>'.$row['ClientID'].'</td>
                                                                <td>'.$row['ClientName'].'</td>
                                                                <td>'.$row['EmailID'].'</td>
                                                                <td>'.$row['Mobile'].'</td>
                                                                <td>'.FormatDateTime('dt', $row['RegisteredOn']).'</td>
                                                                <td>'.($row['Status'] == 'y' ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>').'</td>
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