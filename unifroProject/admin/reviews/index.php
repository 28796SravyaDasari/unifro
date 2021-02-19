<?php
    include_once("../../include-files/autoload-server-files.php");

    $SelectedPage = 'Product Reviews';

    /*----------------------------------------------------
        FILTERS
    ------------------------------------------------------*/

    if(is_numeric($_GET['s']))
    {
        $filter[] = "pr.Status = '".$_GET['s']."'";
        $ActiveFilter['s'] = 'filtered';
    }

    $GetProductReviews = "SELECT pr.ID FROM product_reviews pr";
    $GetProductReviews = $GetProductReviews.(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '');
    $GetProductReviews = MysqlQuery($GetProductReviews);
    $TotalRecords = mysqli_num_rows($GetProductReviews);

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
                                    <form action="/admin/ajax/delete-selected/" id="ReviewForm">
                                        <input type="hidden" name="option" value="product_reviews" />
                                        <input type="hidden" name="FieldName" value="ID" />
                                        <table class="table" data-expand-first="false" data-toggle-column="last">
                                            <thead>
                                                <tr>
                                                    <th data-breakpoints="md sm xs">Picture</th>
                                                    <th data-breakpoints="md sm xs">Product Name</th>
                                                    <th data-breakpoints="md sm xs">Review</th>
                                                    <th data-breakpoints="md sm xs">Ratings</th>
                                                    <th data-breakpoints="md sm xs">Posted By</th>
                                                    <th data-breakpoints="md sm xs">Review Date</th>
                                                    <th>Status</th>
                                                    <th data-breakpoints="md sm xs">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if($TotalRecords > 0)
                                            	{
                                            	    $PerPage = 5;
                                    				$page = !is_numeric($_GET['page'])?0:($_GET['page'] - 1);
                                    				$page = $page * $PerPage;

                                    				$ShowTotalPages = $TotalRecords > $PerPage ? true : false;
                                    				$navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total Reviews - ', $ShowTotalPages);

                                            	    $GetProductReviews = "SELECT p.ProductName, pr.*, r.Rating, c.FirstName, c.LastName FROM product_reviews pr
                                                                            LEFT JOIN product_ratings r ON r.ProductID = pr.ProductID
                                                                            LEFT JOIN products p ON p.ProductID = pr.ProductID
                                                                            LEFT JOIN customers c ON c.MemberID = pr.ReviewBy";
                                                    $GetProductReviews = $GetProductReviews.(count($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '')." ORDER BY pr.ID DESC LIMIT ".$page.", ".$PerPage;
                                        			$res = MysqlQuery($GetProductReviews);

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

                                                        if($row['Rating'] > 0)
                                                        {
                                                            $Ratings = '<span class="label label-success">'.$row['Rating'].' <i class="fa fa-star"></i></span>';
                                                        }
                                                        else
                                                        {
                                                            $Ratings = 'NA';
                                                        }

                                                        echo '<tr>
                                                                <td>
                                                                    <a class="thumbnail" href="'.$DefaultImage.'"
                                                                        data-lightbox="'.$row['ProductID'].'swatch" style="background: url('.$DefaultImage.')">
                                                                    </a>
                                                                </td>
                                                                <td>'.$row['ProductName'].'</td>
                                                                <td class="font13">'.$row['Review'].'</td>
                                                                <td>'.$Ratings.'</td>
                                                                <td>'.($row['FirstName'].' '.$row['LastName']).'</td>
                                                                <td>'.FormatDateTime('d',$row['ReviewDate']).'</td>
                                                                <td>
                                                                    <LABEL class="switch big">
                                                                        <INPUT type="checkbox" class="switch-input"'.($row['Status'] == '1' ? ' checked' : '').'
                                                                                data-id="'.$row['ID'].'" data-value="Review" onclick="ChangeStatus(this)">
                                                                        <SPAN class="switch-label" data-on="Active" data-off="Inactive"></SPAN><SPAN class="switch-handle"></SPAN>
                                                                   </LABEL>
                                                                </td>
                                                                <td>
                                                                    <a class="btn btn-white" data-id="'.$row['ID'].'" onclick="DeleteReview(this)"><i class="fa fa-trash"></i></a>
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