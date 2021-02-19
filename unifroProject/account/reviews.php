<?php
    include_once("../include-files/autoload-server-files.php");
    CheckLogin();

    $ActivePage = 'Reviews';

    $GetProductReviews = "SELECT ID FROM product_reviews WHERE ReviewBy = '".$MemberID."'";
    $TotalRecords = mysqli_num_rows( MysqlQuery($GetProductReviews) );

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <META name="robots" content="noindex,nofollow" />

        <title>My Reviews - Unifro</title>

        <?php include_once(_ROOT."/include-files/common-css.php"); ?>
        <?php include_once(_ROOT."/include-files/common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            $("#ProfileForm").submit(function(e)
            {
                e.preventDefault();
                ShowProcessing();

                AjaxResponse = AjaxFormSubmit(this);

                $.when(AjaxResponse).done(function(response)
                {
                    response = $.parseJSON(response);

                    if(response.status == 'success')
                    {
                        AlertBox(response.response_message, 'success', function(){ location.reload() });
                    }
                    else if(response.status == 'login')
                    {
                        AlertBox('Oops! Your session has expired! Please login again.', 'error', function(){ location.href = '/login/' } );
                    }
                    else if(response.status == 'validation')
                	{
                	    ThrowError(response.error, true);
                	}
                    else
                    {
                        AlertBox(response.response_message);
                    }
                });

            });
        });

        </script>
    </head>


    <body>

        <?php include_once(_ROOT."/include-files/header.php"); ?>

        <div class="container">
            <div class="row">
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?=_HOST?>"><i class="fa fa-home"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="/account/">My Account</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Reviews</li>
                    </ol>
                </nav>
            </div>
            <div class="row mg-t-30">

                <div class="col-sm-12">

                    <div class="font21 mg-b-20">My Reviews</div>

                        <div class="card-box">
                            <table class="table" data-expand-first="false" data-toggle-column="last">
                                <thead>
                                    <tr>
                                        <th data-breakpoints="md sm xs">Picture</th>
                                        <th data-breakpoints="md sm xs">Product Name</th>
                                        <th data-breakpoints="md sm xs">Review</th>
                                        <th data-breakpoints="md sm xs">Ratings</th>
                                        <th data-breakpoints="md sm xs">Review Date</th>
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
                        				$navs = CreateNavs($TotalRecords, $PerPage, 'page', 9, 'Total Reviews - ', $ShowTotalPages);

                                	    $GetProductReviews = "SELECT p.ProductName, p.ProductURL, pr.*, r.Rating FROM product_reviews pr
                                                                LEFT JOIN product_ratings r ON r.ProductID = pr.ProductID
                                                                LEFT JOIN products p ON p.ProductID = pr.ProductID
                                                                WHERE pr.ReviewBy = '".$MemberID."'";
                                        $GetProductReviews = $GetProductReviews.(count($filter) > 0 ? ' AND '.implode(' AND ', $filter) : '')." ORDER BY pr.ID DESC LIMIT ".$page.", ".$PerPage;

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
                                                $Ratings = 'Yet to Rate';
                                            }

                                            echo '<tr>
                                                    <td>
                                                        <a class="thumbnail" href="'.$row['ProductURL'].'" style="background: url('.$DefaultImage.')"></a>
                                                    </td>
                                                    <td>
                                                        <a href="'.$row['ProductURL'].'">'.$row['ProductName'].'</a>
                                                    </td>
                                                    <td class="font13">'.$row['Review'].'</td>
                                                    <td>'.$Ratings.'</td>
                                                    <td>'.FormatDateTime('d',$row['ReviewDate']).'</td>
                                                    <td>
                                                        '.($row['Status'] == '1' ? '<span class="label label-success">Live<span>' : '<span class="label label-danger">Under Moderation<span>').'
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-white" data-id="'.$row['ProductID'].'" onclick="DeleteReview(this)"><i class="fa fa-trash"></i></button>
                                                    </td>
                                                </tr>';
                                        }
                                    }
                                    else
                                    {
                                        echo '<tr><td class="no-data" colspan="7">No Records Found</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>

                        </div>

                        <?=$navs != '' ? '<div class="card-box">'.$navs.'</div>' : ''?>

                </div>      <!-- END OF COL -->



            </div>  <!-- END OF ROW -->

        </div>  <!-- END OF CONTAINER -->

    </body>
</html>