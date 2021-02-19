<?php
    include_once("../../include-files/autoload-server-files.php");

    $SelectedPage = 'Customers';
    SetReturnURL();

    /*----------------------------------------------------
        FILTERS
    ------------------------------------------------------*/

    if(is_numeric($_GET['s']))
    {
        $filter[] = "c.Status = '".$_GET['s']."'";
        $ActiveFilter['s'] = 'filtered';
    }
    if($_GET['q'] != '')
    {
        $_GET['q'] = CleanText($_GET['q']);
        $ActiveFilter['q'] = 'filtered';

        if(is_numeric($_GET['q']))
        {
            $filter[] = "c.MemberID = '".$_GET['q']."'";
        }
        else
        {
            $filter[] = "(c.FirstName LIKE '%".$_GET['q']."%' OR c.LastName LIKE '%".$_GET['q']."%')";
        }
    }

    // FETCH CUSTOMERS DETAILS FROM THE DATABASE
    $GetCustomers = "SELECT c.MemberID FROM customers c".(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '');
    $GetCustomers = MysqlQuery($GetCustomers);
    $TotalRecords = mysqli_num_rows($GetCustomers);

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
                $('input[name*=MemberID]').prop('checked', this.checked);
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
                                                    <option<?=$_GET['s'] == '1' ? ' selected' : ''?> value="1">Active</option>
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
                                    <form action="/admin/ajax/delete-selected/" id="CustomerForm">
                                        <input type="hidden" name="option" value="customers" />
                                        <input type="hidden" name="FieldName" value="MemberID" />
                                        <table class="table" data-expand-first="false" data-toggle-column="last">
                                            <thead>
                                                <tr>
                                                    <th data-breakpoints="md sm xs">Customer ID</th>
                                                    <th data-breakpoints="md sm xs">Name</th>
                                                    <th data-breakpoints="md sm xs">Email</th>
                                                    <th data-breakpoints="md sm xs">City</th>
                                                    <th data-breakpoints="md sm xs">Registered On</th>
                                                    <th data-breakpoints="md sm xs">Status</th>
                                                    <th>Action</th>
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
                                    				$navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total Customers - ', $ShowTotalPages);

                                            	    $GetCustomers = "SELECT c.*, ct.CityName FROM customers c LEFT JOIN cities ct ON ct.CityID = c.City";
                                                    $GetCustomers = $GetCustomers.(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '')." ORDER BY c.MemberID DESC LIMIT ".$page.", ".$PerPage;
                                        			$res = MysqlQuery($GetCustomers);

                                                    for(; $row = mysqli_fetch_assoc($res); )
                                                    {
                                                        echo '<tr id="row'.$row['MemberID'].'">
                                                                <td>'.$row['MemberID'].'</td>
                                                                <td>'.$row['FirstName'].' '.$row['LastName'].'</td>
                                                                <td>'.$row['EmailID'].'</td>
                                                                <td>'.$row['CityName'].'</td>
                                                                <td>'.FormatDateTime('dt', $row['RegisteredOn']).'</td>
                                                                <td>
                                                                    <LABEL class="switch big">
                                                                        <INPUT type="checkbox" class="switch-input"'.($row['Status'] == '1' ? ' checked' : '').'
                                                                                data-id="'.$row['MemberID'].'" data-value="Customer" onclick="ChangeStatus(this)">
                                                                        <SPAN class="switch-label" data-on="Active" data-off="Inactive"></SPAN><SPAN class="switch-handle"></SPAN>
                                                                   </LABEL>
                                                                </td>

                                                                <td>
                                                                    <a class="btn btn-white" data-id="'.$row['MemberID'].'" onclick="DeleteAccount(this)"><i class="fa fa-trash"></i></a>
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