<?php
    include_once("../../include-files/autoload-server-files.php");

    $SelectedPage = 'Banner Management';
    SetReturnURL();

    /*----------------------------------------------------
        FILTERS
    ------------------------------------------------------*/

    if(is_numeric($_GET['s']))
    {
        $filter[] = "Status = '".$_GET['s']."'";
        $ActiveFilter['s'] = 'filtered';
    }

    // FETCH BANNERS
    $GetBanners = "SELECT * FROM banners".(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '');
    $GetBanners = MysqlQuery($GetBanners);
    $TotalRecords = mysqli_num_rows($GetBanners);


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
            $('input[name=CheckAll]').on('change', function(){
                $('input[name*=BannerID]').prop('checked', this.checked);
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
                                <div class="filter-box">
                                    <form>
                                        <ul class="filters list-inline">
                                            <li>
                                                <a href="/admin/banners/add/" class="btn btn-primary">
                                                    <i class="fa fa-plus-square"></i>&nbsp;
                                                    Add New Banner
                                                </a>
                                            </li>
                                            <li>
                                                <button type="button" class="btn btn-danger" data-id="BannerForm" onclick="DeleteSelected(this)">
                                                    <i class="fa fa-trash-o"></i>&nbsp;
                                                    Delete (selected)
                                                </button>
                                            </li>
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
                                    <form action="/admin/ajax/delete-selected/" id="BannerForm">
                                        <input type="hidden" name="option" value="Banners" />
                                        <input type="hidden" name="FieldName" value="BannerID" />
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
                                                    <th data-breakpoints="md sm xs" style="width: 300px">Picture</th>
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

                                            	    $GetBanners = "SELECT * FROM banners";
                                                    $GetBanners = $GetBanners.(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '')." LIMIT ".$page.", ".$PerPage;
                                        			$res = MysqlQuery($GetBanners);

                                                    for(; $row = mysqli_fetch_assoc($res); )
                                                    {
                                                        echo '<tr id="row'.$row['BannerID'].'">
                                                                <td>
                                                                    <div class="custom-checkbox">
                                                                        <label>
                                                                            <input type="checkbox" name="BannerID[]" value="'.$row['BannerID'].'" /><span></span>
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <a class="thumbnail" href="'._BannerDir.$row['ImageName'].'"
                                                                        style="background: url('._BannerDir.$row['ImageName'].')" target="_blank">
                                                                    </a>
                                                                </td>
                                                                <td>
                                                                    <LABEL class="switch big">
                                                                        <INPUT type="checkbox" class="switch-input"'.($row['Status'] == '1' ? ' checked' : '').'
                                                                                data-id="'.$row['BannerID'].'" data-value="Banner" onclick="ChangeStatus(this)">
                                                                        <SPAN class="switch-label" data-on="Active" data-off="Inactive"></SPAN><SPAN class="switch-handle"></SPAN>
                                                                   </LABEL>
                                                                </td>
                                                                <td>
                                                                    <a class="btn btn-info" href="/admin/banners/edit/?id='.$row['BannerID'].'"><i class="fa fa-pencil"></i>&nbsp; Edit</a>
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