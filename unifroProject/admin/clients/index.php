<?php
    include_once("../../include-files/autoload-server-files.php");

    $SelectedPage = 'Clients';
    SetReturnURL();

    /*----------------------------------------------------
        FILTERS
    ------------------------------------------------------*/

    if($_GET['s'] != '')
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
            $filter[] = "f.ClientID = '".$_GET['q']."'";
        }
        else
        {
            $filter[] = "(c.ClientName LIKE '%".$_GET['q']."%' OR ContactFirstName LIKE '%".$_GET['q']."%' OR ContactLastName LIKE '%".$_GET['q']."%')";
        }
    }

    // FETCH CLIENT DETAILS FROM THE DATABASE
    $GetClients = "SELECT c.ClientID FROM clients c LEFT JOIN sales s ON s.SalesID = c.SalesID".(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '');
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
                $('input[name*=ClientID]').prop('checked', this.checked);
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
                                                <input type="text" class="form-control xs <?=$ActiveFilter['q']?>" name="q" placeholder="Search By Client Name" value="<?=$_GET['q']?>">
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
                                    <form action="/admin/ajax/delete-selected/" id="ClientForm">
                                        <input type="hidden" name="option" value="Client" />
                                        <input type="hidden" name="FieldName" value="ClientID" />
                                        <table class="table" data-expand-first="false" data-toggle-column="last">
                                            <thead>
                                                <tr>
                                                    <th data-breakpoints="md sm xs">Client ID</th>
                                                    <th data-breakpoints="md sm xs">Client Name</th>
                                                    <th data-breakpoints="md sm xs">Email</th>
                                                    <th data-breakpoints="md sm xs">Mobile</th>
                                                    <th data-breakpoints="md sm xs">City</th>
                                                    <th data-breakpoints="md sm xs">Sales Executive</th>
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
                                    				$navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total Clients - ', $ShowTotalPages);

                                            	    $GetClients = "SELECT c.*, ct.CityName, s.FirstName, s.LastName FROM clients c LEFT JOIN sales s ON s.SalesID = c.SalesID LEFT JOIN cities ct ON ct.CityID = c.City";
                                                    $GetClients = $GetClients.(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '')." ORDER BY c.ClientID DESC LIMIT ".$page.", ".$PerPage;
                                        			$res = MysqlQuery($GetClients);

                                                    for(; $row = mysqli_fetch_assoc($res); )
                                                    {
                                                        echo '<tr id="row'.$row['ClientID'].'">
                                                                <td>'.$row['ClientID'].'</td>
                                                                <td>'.$row['ClientName'].'</td>
                                                                <td>'.$row['EmailID'].'</td>
                                                                <td>'.$row['Mobile'].'</td>
                                                                <td>'.$row['CityName'].'</td>
                                                                <td>'.$row['FirstName'].' '.$row['LastName'].'</td>
                                                                <td style="width: 15%">
                                                                    <LABEL class="switch big">
                                                                        <INPUT type="checkbox" class="switch-input"'.($row['Status'] == 'y' ? ' checked' : '').'
                                                                                data-id="'.$row['ClientID'].'" data-value="Client" onclick="ChangeStatus(this)">
                                                                        <SPAN class="switch-label" data-on="Active" data-off="Inactive"></SPAN><SPAN class="switch-handle"></SPAN>
                                                                   </LABEL>
                                                                </td>
                                                                <td>
                                                                    <a class="btn btn-warning" href="/admin/clients/products/'.$row['ClientID'].'/">View Products</a>
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