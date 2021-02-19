<?php
    include_once("../../include-files/autoload-server-files.php");

    $SelectedPage = 'Sales Executives';
    SetReturnURL();

    /*----------------------------------------------------
        FILTERS
    ------------------------------------------------------*/

    if(is_numeric($_GET['s']))
    {
        $filter[] = "s.Status = '".$_GET['s']."'";
        $ActiveFilter['s'] = 'filtered';
    }
    if($_GET['q'] != '')
    {
        $_GET['q'] = CleanText($_GET['q']);
        $ActiveFilter['q'] = 'filtered';

        if(is_numeric($_GET['q']))
        {
            $filter[] = "s.SalesID = '".$_GET['q']."'";
        }
        else
        {
            $filter[] = "(s.FirstName LIKE '%".$_GET['q']."%' OR s.LastName LIKE '%".$_GET['q']."%')";
        }
    }

    // FETCH THE SALES AGENTS FROM THE DATABASE
    $GetSalesAgents = "SELECT s.SalesID FROM sales s".(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '');
    $GetSalesAgents = MysqlQuery($GetSalesAgents);
    $TotalRecords = mysqli_num_rows($GetSalesAgents);


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
                $('input[name*=SalesID]').prop('checked', this.checked);
            });

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
                                <div class="content-header clearfix mg-b-5">
                                    <a href="/admin/sales/add/" class="btn btn-primary">
                                        <i class="fa fa-plus-square"></i>&nbsp;
                                        Create Sales Account
                                    </a>
                                </div>
                            </div>
                        </div>
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
                                                    <option<?=$_GET['s'] == 1 ? ' selected' : ''?> value="1">Active</option>
                                                    <option<?=$_GET['s'] == '0' ? ' selected' : ''?> value="0">Inactive</option>
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
                                    <form action="/admin/ajax/delete-selected/" id="SalesForm">
                                        <input type="hidden" name="option" value="Sales" />
                                        <input type="hidden" name="FieldName" value="SalesID" />
                                        <table class="table" data-expand-first="false" data-toggle-column="last">
                                            <thead>
                                                <tr>
                                                    <th style="width: 40px">
                                                        <div class="custom-checkbox">
                                                            <label>
                                                                <input type="checkbox" name="CheckAll" value="All" /><span></span>
                                                            </label>
                                                        </div>
                                                    </th>
                                                    <th data-breakpoints="md sm xs">Sales ID</th>
                                                    <th data-breakpoints="md sm xs">Name</th>
                                                    <th data-breakpoints="md sm xs">Email</th>
                                                    <th data-breakpoints="md sm xs">Mobile</th>
                                                    <th data-breakpoints="md sm xs">Location</th>
                                                    <th data-breakpoints="md sm xs">Registered On</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
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
                                    				$navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total - ', $ShowTotalPages);

                                            	    $GetSalesAgents = "SELECT s.*, st.Name, ct.CityName FROM sales s LEFT JOIN states st ON st.StateID = s.State LEFT JOIN cities ct ON ct.CityID = s.City ";
                                                    $GetSalesAgents = $GetSalesAgents.(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '')." ORDER BY s.SalesID DESC LIMIT ".$page.", ".$PerPage;
                                        			$res = MysqlQuery($GetSalesAgents);

                                                    for(; $row = mysqli_fetch_assoc($res); )
                                                    {
                                                        echo '<tr id="row'.$row['SalesID'].'">
                                                                <td>
                                                                    <div class="custom-checkbox">
                                                                        <label>
                                                                            <input type="checkbox" name="SalesID[]" value="'.$row['SalesID'].'" /><span></span>
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                                <td>'.$row['SalesID'].'</td>
                                                                <td>'.$row['FirstName'].' '.$row['LastName'].'</td>
                                                                <td>'.$row['EmailID'].'</td>
                                                                <td>'.$row['Mobile'].'</td>
                                                                <td>'.$row['CityName'].'</td>
                                                                <td>'.FormatDateTime('dt', $row['RegistrationTimestamp']).'</td>
                                                                <td>
                                                                    <LABEL class="switch big">
                                                                        <INPUT type="checkbox" class="switch-input"'.($row['Status'] == '1' ? ' checked' : '').'
                                                                                data-id="'.$row['SalesID'].'" data-value="Sales Agent" onclick="ChangeStatus(this)">
                                                                        <SPAN class="switch-label" data-on="Active" data-off="Inactive"></SPAN><SPAN class="switch-handle"></SPAN>
                                                                   </LABEL>
                                                                </td>
                                                                <td>
                                                                    <a class="btn btn-warning" href="/admin/sales/clients/?id='.$row['SalesID'].'">View Clients</a>
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

	</body>
</html>