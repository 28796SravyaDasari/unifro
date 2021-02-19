<?php
    include_once("../../include-files/autoload-server-files.php");

    $SelectedPage = 'Fabrics';
    SetReturnURL();

    /*----------------------------------------------------
        FILTERS
    ------------------------------------------------------*/

    if(is_numeric($_GET['s']))
    {
        $filter[] = "f.Status = '".$_GET['s']."'";
        $ActiveFilter['s'] = 'filtered';
    }
    if(is_numeric($_GET['c']))
    {
        $filter[] = "f.CategoryID = '".$_GET['c']."'";
        $ActiveFilter['c'] = 'filtered';
    }
    if($_GET['q'] != '')
    {
        $_GET['q'] = CleanText($_GET['q']);
        $ActiveFilter['q'] = 'filtered';

        if(is_numeric($_GET['q']))
        {
            $filter[] = "f.FabricID = '".$_GET['q']."'";
        }
        else
        {
            $filter[] = "f.FabricName LIKE '%".$_GET['q']."%'";
        }
    }

    // FETCH THE FABRICS FROM THE DATABASE
    $GetFabrics = "SELECT f.FabricID FROM fabrics f LEFT JOIN master_categories c ON c.CategoryID = f.CategoryID".(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '');
    $GetFabrics = MysqlQuery($GetFabrics);
    $TotalRecords = mysqli_num_rows($GetFabrics);

    $NavigationMenu = MysqlQuery("SELECT * FROM master_categories WHERE CategoryID != '1' ORDER By SortOrder");
    for(;$row = mysqli_fetch_assoc($NavigationMenu);)
    {
        $MasterCategory['List'][$row['ParentID']][] = $row['CategoryID'];
        $MasterCategory['Data'][$row['CategoryID']] = $row;
    }

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
                                <div class="content-header clearfix mg-b-5">
                                    <div>
                                        <a href="/admin/fabrics/add/" class="btn btn-primary">
                                            <i class="fa fa-plus-square"></i>&nbsp;
                                            Add Fabric
                                        </a>
                                        <button type="button" class="btn btn-danger" data-id="FabricForm" onclick="DeleteSelected(this)">
                                            <i class="fa fa-trash-o"></i>&nbsp;
                                            Delete Selected
                                        </button>
                                        <a class="btn btn-success" href="/admin/fabrics/bulk-upload/">
                                            <i class="fa fa-upload"></i>&nbsp;
                                            Bulk Upload
                                        </a>
                                        <a class="btn btn-white" href="/admin/ajax/download/fabrics/">
                                            <i class="fa fa-download"></i>&nbsp;CSV
                                        </a>
                                    </div>
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
                                            <li style="width: 180px">
                                                <select class="selectric xs <?=$ActiveFilter['c']?>" name="c">
                                                    <option value="">Category</option>
                                                    <?=CustomCategories('1', '')?>
                                                </select>
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
                                    <form action="/admin/ajax/delete-selected/" id="FabricForm">
                                        <input type="hidden" name="option" value="Fabric" />
                                        <input type="hidden" name="FieldName" value="FabricID" />
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
                                                    <th data-breakpoints="md sm xs">ID</th>
                                                    <th data-breakpoints="md sm xs">Swatch</th>
                                                    <th data-breakpoints="md sm xs">Category</th>
                                                    <th data-breakpoints="md sm xs">Fabric Name</th>
                                                    <th data-breakpoints="md sm xs">Code</th>
                                                    <th data-breakpoints="md sm xs">Composition</th>
                                                    <th data-breakpoints="md sm xs">Price (Rs.)</th>
                                                    <th data-breakpoints="md sm xs">GSM</th>
                                                    <th>Status</th>
                                                    <th data-breakpoints="md sm xs">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if($TotalRecords > 0)
                                            	{
                                            	    $PerPage = 15;
                                    				$page = !is_numeric($_GET['page'])?0:($_GET['page'] - 1);
                                    				$page = $page * $PerPage;

                                    				$ShowTotalPages = $TotalRecords > $PerPage ? true : false;
                                    				$navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total Fabrics - ', $ShowTotalPages);

                                            	    $GetFabrics = "SELECT f.*, c.CategoryTitle FROM fabrics f LEFT JOIN master_categories c ON c.CategoryID = f.CategoryID";
                                                    $GetFabrics = $GetFabrics.(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '')." ORDER BY f.FabricID DESC LIMIT ".$page.", ".$PerPage;
                                        			$res = MysqlQuery($GetFabrics);

                                                    for(; $row = mysqli_fetch_assoc($res); )
                                                    {
                                                        $ParentCat = implode(' &raquo; ', GetParentCats($row['CategoryID'], $MasterCategory));

                                                        if($row['DefaultFabric'] == 1)
                                                        {
                                                            $DefaultFabric = '<img class="pull-left" src="/images/mark-default.png" title="Default Fabric">';
                                                        }
                                                        else
                                                        {
                                                            $DefaultFabric = '';
                                                        }

                                                        echo '<tr id="row'.$row['FabricID'].'">
                                                                <td>
                                                                    <div class="custom-checkbox">
                                                                        <label>
                                                                            <input type="checkbox" name="FabricID[]" value="'.$row['FabricID'].'" /><span></span>
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                                <td>'.$row['FabricID'].'</td>
                                                                <td>
                                                                    <a class="thumbnail" href="'._FabricImageDir.$row['FabricImage'].'"
                                                                        data-lightbox="'.$row['FabricID'].'swatch" style="background: url('._FabricImageDir.$row['FabricImage'].')">
                                                                        '.$DefaultFabric.'
                                                                    </a>
                                                                </td>
                                                                <td>'.($ParentCat != '' ? $ParentCat.' &raquo; ' : '').$row['CategoryTitle'].'</td>
                                                                <td>'.$row['FabricName'].'</td>
                                                                <td>'.$row['FabricCode'].'</td>
                                                                <td>'.$row['FabricComposition'].'</td>
                                                                <td>'.$row['FabricPrice'].'</td>
                                                                <td>'.$row['FabricGSM'].'</td>
                                                                <td>
                                                                    <LABEL class="switch big">
                                                                        <INPUT type="checkbox" class="switch-input"'.($row['Status'] == '1' ? ' checked' : '').'
                                                                                data-id="'.$row['FabricID'].'" data-value="Fabric" onclick="ChangeStatus(this)">
                                                                        <SPAN class="switch-label" data-on="Active" data-off="Inactive"></SPAN><SPAN class="switch-handle"></SPAN>
                                                                   </LABEL>
                                                                </td>
                                                                <td>
                                                                    <a class="btn btn-default" href="/admin/fabrics/edit/?id='.$row['FabricID'].'"><i class="fa fa-pencil"></i>&nbsp; Edit</a>
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