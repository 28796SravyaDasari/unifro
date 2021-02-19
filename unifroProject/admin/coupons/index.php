<?php
    include_once("../../include-files/autoload-server-files.php");

    $SelectedPage = 'Coupons';

    SetReturnURL();

    /*----------------------------------------------------
        FILTERS
    ------------------------------------------------------*/

    if(is_numeric($_GET['s']))
    {
        $filter[] = "c.Status = '".$_GET['s']."'";
        $ActiveFilter['s'] = 'filtered';
    }
    if($_GET['x'] == 'a')
    {
        $filter[] = "c.Expiry > '".time()."'";
        $ActiveFilter['x'] = 'filtered';
    }
    if($_GET['x'] == 'x')
    {
        $filter[] = "c.Expiry < '".time()."'";
        $ActiveFilter['x'] = 'filtered';
    }
    if($_GET['q'] != '')
    {
        $_GET['q'] = CleanText($_GET['q']);
        $ActiveFilter['q'] = 'filtered';

        $filter[] = "c.CouponCode LIKE '%".$_GET['q']."%'";
    }

    // FETCH COUPON DETAILS FROM THE DATABASE
    $GetCoupons = "SELECT c.CouponID FROM coupons c".(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '');
    $GetCoupons = MysqlQuery($GetCoupons);
    $TotalRecords = mysqli_num_rows($GetCoupons);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title><?=$SelectedPage?></title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>

        <script>
        $(document).ready(function(){
            $('input[name=CheckAll]').on('change', function(){
                $('input[name*=CouponID]').prop('checked', this.checked);
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
                                    <div>
                                        <a href="/admin/coupons/add/" class="btn btn-primary">
                                            <i class="fa fa-plus-square"></i>&nbsp;
                                            Add Coupon
                                        </a>
                                        <button type="button" class="btn btn-danger" data-id="CouponForm" onclick="DeleteSelected(this)">
                                            <i class="fa fa-trash-o"></i>&nbsp;
                                            Delete Selected
                                        </button>
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
                                            <li>
                                                <select class="selectric xs <?=$ActiveFilter['s']?>" name="s">
                                                    <option value="">Status</option>
                                                    <option<?=$_GET['s'] == '1' ? ' selected' : ''?> value="1">Active</option>
                                                    <option<?=$_GET['s'] == '0' ? ' selected' : ''?> value="0">Inactive</option>
                                                </select>
                                            </li>
                                            <li>
                                                <select class="selectric xs <?=$ActiveFilter['x']?>" name="x">
                                                    <option value="">All Coupons</option>
                                                    <option<?=$_GET['x'] == 'a' ? ' selected' : ''?> value="a">Active Coupons</option>
                                                    <option<?=$_GET['x'] == 'x' ? ' selected' : ''?> value="x">Expired Coupons</option>
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
                                    <form action="/admin/ajax/delete-selected/" id="CouponForm">
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
                                                    <th>Coupon Code</th>
                                                    <th data-breakpoints="md sm xs">Discount</th>
                                                    <th data-breakpoints="md sm xs">Max Discount</th>
                                                    <th data-breakpoints="md sm xs">Min Order Amt</th>
                                                    <th>Expiry</th>
                                                    <th data-breakpoints="md sm xs">Status</th>
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
                                        				$navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total Records - ', $ShowTotalPages);

                                                	    $GetCoupons = "SELECT c.* FROM coupons c";
                                                	    $GetCoupons = $GetCoupons.(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '')." ORDER BY c.CouponID DESC LIMIT ".$page.", ".$PerPage;
                                            			$res = MysqlQuery($GetCoupons);

                                                        for(; $row = mysqli_fetch_assoc($res); )
                                                        {
                                                            if($row['DiscountType'] == '%')
                                                            {
                                                                $Discount = $row['Discount'].'%';
                                                            }
                                                            else
                                                            {
                                                                $Discount = 'Rs. '.$row['Discount'];
                                                            }

                                                            echo '<tr id="row'.$row['CouponID'].'">
                                                                    <td>
                                                                        <div class="custom-checkbox">
                                                                            <label>
                                                                                <input type="checkbox" name="CouponID[]" value="'.$row['CouponID'].'" /><span></span>
                                                                            </label>
                                                                        </div>
                                                                    </td>
                                                                    <td>'.$row['CouponCode'].'</td>
                                                                    <td>'.$Discount.'</td>
                                                                    <td>'.($row['MaxDiscount'] > 0 ? FormatAmount($row['MaxDiscount']) : 'NA').'</td>
                                                                    <td>'.FormatAmount($row['MinOrderAmount']).'</td>
                                                                    <td>'.FormatDateTime('dt',$row['Expiry']).'</td>
                                                                    <td style="width: 15%">
                                                                        <LABEL class="switch big">
                                                                            <INPUT type="checkbox" class="switch-input"'.($row['Status'] == '1' ? ' checked' : '').'
                                                                                    data-id="'.$row['CouponID'].'" data-value="Coupon" onclick="ChangeStatus(this)">
                                                                            <SPAN class="switch-label" data-on="Active" data-off="Inactive"></SPAN><SPAN class="switch-handle"></SPAN>
                                                                       </LABEL>
                                                                    </td>
                                                                    <td>
                                                                        <a class="btn btn-default" href="/admin/coupons/edit/?id='.$row['CouponID'].'">
                                                                            <i class="fa fa-pencil"></i>&nbsp; Edit
                                                                        </a>
                                                                    </td>
                                                                </tr>';
                                                        }
                                                    }
                                                    ?>

                                            </tbody>
                                        </table>
                                    </form>
                                    <?=$navs != '' ? '<div>'.$navs.'</div>' : ''?>
                                </div>
                            </div>
                        </div>
                    </div>   <!-- container -->

                </div>   <!-- content -->
            </div>


                <?php include(_ROOT._AdminIncludesDir."footer.php"); ?>

            </div>
            <!-- ==============================================================
                End of Right content here
            ============================================================== -->
        </div>

	</body>
</html>