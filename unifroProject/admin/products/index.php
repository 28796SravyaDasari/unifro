<?php
    include_once("../../include-files/autoload-server-files.php");

    $SelectedPage = 'Products';

    /*----------------------------------------------------
        FILTERS
    ------------------------------------------------------*/

    if(is_numeric($_GET['s']))
    {
        $filter[] = "p.Status = '".$_GET['s']."'";
        $ActiveFilter['s'] = 'filtered';
    }
    if(is_numeric($_GET['f']))
    {
        $filter[] = "p.Featured = '".$_GET['f']."'";
        $ActiveFilter['f'] = 'filtered';
    }
    if($_GET['com'] == 'y' || $_GET['com'] == 'n')
    {
        $filter[] = "p.Combo = '".$_GET['com']."'";
        $ActiveFilter['com'] = 'filtered';
    }
    if(is_numeric($_GET['c']))
    {
        $filter[] = "pc.CategoryID = '".$_GET['c']."'";
        $ActiveFilter['c'] = 'filtered';
    }
    if($_GET['q'] != '')
    {
        $_GET['q'] = CleanText($_GET['q']);
        $ActiveFilter['q'] = 'filtered';

        if(is_numeric($_GET['q']))
        {
            $filter[] = "p.ProductID = '".$_GET['q']."'";
        }
        else
        {
            $filter[] = "p.ProductName LIKE '%".$_GET['q']."%'";
        }
    }

    $GetProducts = "SELECT p.ProductID FROM products p LEFT JOIN product_categories pc ON pc.ProductID = p.ProductID";
    $GetProducts = $GetProducts.(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter).' GROUP BY p.ProductID' : ' GROUP BY p.ProductID');
    $GetProducts = MysqlQuery($GetProducts);
    $TotalRecords = mysqli_num_rows($GetProducts);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title>Products</title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            $('input[name=CheckAll]').on('change', function(){
                $('input[name*=ProductID]').prop('checked', this.checked);
            });

            lightbox.option({ 'showImageNumberLabel': false, 'wrapAround': true, alwaysShowNavOnTouchDevices: true })
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
                                <div class="content-header clearfix mg-b-5">
                                    <a href="/admin/products/add/" class="btn btn-primary">
                                        <i class="fa fa-plus-square"></i>&nbsp;
                                        Add Product
                                    </a>
                                    <a href="/admin/products/combo/add/" class="btn btn-success">
                                        <i class="fa fa-plus-square"></i>&nbsp;
                                        Add Combo Product
                                    </a>
                                    <button type="button" class="btn btn-danger" data-id="ProductForm" onclick="DeleteSelected(this)">
                                        <i class="fa fa-trash-o"></i>&nbsp;
                                        Delete (selected)
                                    </button>
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
                                            <li style="width: 200px">
                                                <select class="selectric xs <?=$ActiveFilter['c']?>" name="c">
                                                    <option value="">Category</option>
                                                    <?php
                                                    foreach(OtherCategories(2) as $CatID => $CatTitle)
                                                    {
                                                        echo '<option'.($CatID == $_GET['c'] ? ' selected' : '').' value="'.$CatID.'">'.$CatTitle.'</option>';
                                                    }
                                                    ?>
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
                                                <select class="selectric xs <?=$ActiveFilter['f']?>" name="f">
                                                    <option value="">Featured</option>
                                                    <option<?=$_GET['f'] == '1' ? ' selected' : ''?> value="1">Yes</option>
                                                    <option<?=$_GET['f'] == '0' ? ' selected' : ''?> value="0">No</option>
                                                </select>
                                            </li>
                                            <li>
                                                <select class="selectric xs <?=$ActiveFilter['com']?>" name="com">
                                                    <option value="">Combo</option>
                                                    <option<?=$_GET['com'] == 'y' ? ' selected' : ''?> value="y">Yes</option>
                                                    <option<?=$_GET['com'] == 'n' ? ' selected' : ''?> value="n">No</option>
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
                                    <h4>Total Products - <?=$TotalRecords?></h4>
                                    <form action="/admin/ajax/delete-selected/" id="ProductForm">
                                        <input type="hidden" name="option" value="product" />
                                        <input type="hidden" name="FieldName" value="ProductID" />
                                        <table class="table" data-expand-all="true" data-show-toggle="false">
                                            <thead>
                                                <tr>
                                                    <th style="width: 40px">
                                                        <div class="custom-checkbox">
                                                            <label>
                                                                <input type="checkbox" name="CheckAll" value="All" /><span></span>
                                                            </label>
                                                        </div>
                                                    </th>
                                                    <th data-breakpoints="md sm xs">Picture</th>
                                                    <th data-breakpoints="md sm xs">Product Name</th>
                                                    <th data-breakpoints="md sm xs">Price</th>
                                                    <th data-breakpoints="md sm xs">Discount</th>
                                                    <th data-breakpoints="md sm xs">Combo</th>
                                                    <th data-breakpoints="md sm xs">Featured</th>
                                                    <th>Status</th>
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
                                    				$navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total Products - ', $ShowTotalPages);

                                            	    $GetProducts = "SELECT p.* FROM products p LEFT JOIN product_categories pc ON pc.ProductID = p.ProductID";
                                                    $GetProducts = $GetProducts.(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter).' GROUP BY p.ProductID' : ' GROUP BY p.ProductID')." ORDER BY p.ProductID DESC LIMIT ".$page.", ".$PerPage;
                                        			$res = MysqlQuery($GetProducts);

                                                    for(; $row = mysqli_fetch_assoc($res); )
                                                    {
                                                        /*-------------------------------------------
                                                            LETS GET THE DEFAULT IMAGE OF THE PRODUCT
                                                        --------------------------------------------*/
                                                        $DefaultImage = mysqli_fetch_assoc(MysqlQuery("SELECT FileName FROM product_images WHERE ProductID = '".$row['ProductID']."' AND DefaultImage = '1' LIMIT 1"))['FileName'];
                                                        if($DefaultImage == '')
                                                        {
                                                            $DefaultImage = _ProductNoImage;
                                                        }
                                                        else
                                                        {
                                                            $DefaultImage = _ProductImageDir.$DefaultImage;
                                                        }

                                                        $EditURL = $row['Combo'] == 'y' ? '/admin/products/combo/edit/'.$row['ProductID'] : '/admin/products/edit/'.$row['ProductID'];

                                                        echo '<tr id="row'.$row['ProductID'].'">
                                                                <td>
                                                                    <div class="custom-checkbox">
                                                                        <label>
                                                                            <input type="checkbox" name="ProductID[]" value="'.$row['ProductID'].'" /><span></span>
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <a class="thumbnail" href="'.$DefaultImage.'"
                                                                        data-lightbox="'.$row['ProductID'].'swatch" style="background: url('.$DefaultImage.')">
                                                                    </a>
                                                                </td>
                                                                <td>'.$row['ProductName'].'</td>
                                                                <td>'.$row['Rate'].'</td>
                                                                <td>'.floatval($row['Discount']).$row['DiscountType'].'</td>
                                                                <td>'.($row['Combo'] == 'y' ? 'Yes' : 'No').'</td>
                                                                <td>
                                                                    <LABEL class="switch">
                                                                        <INPUT type="checkbox" class="switch-input"'.($row['Featured'] == '1' ? ' checked' : '').'
                                                                                data-id="'.$row['ProductID'].'" data-value="Featured Product" onclick="ChangeStatus(this)">
                                                                        <SPAN class="switch-label" data-on="Yes" data-off="No"></SPAN><SPAN class="switch-handle"></SPAN>
                                                                   </LABEL>
                                                                </td>
                                                                <td>
                                                                    <LABEL class="switch big">
                                                                        <INPUT type="checkbox" class="switch-input"'.($row['Status'] == '1' ? ' checked' : '').'
                                                                                data-id="'.$row['ProductID'].'" data-value="Product" onclick="ChangeStatus(this)">
                                                                        <SPAN class="switch-label" data-on="Active" data-off="Inactive"></SPAN><SPAN class="switch-handle"></SPAN>
                                                                   </LABEL>
                                                                </td>
                                                                <td>
                                                                    <a class="btn btn-default" href="'.$EditURL.'"><i class="fa fa-pencil"></i>&nbsp; Edit</a>
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