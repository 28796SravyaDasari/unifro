<?php
    $SelectedPage = 'Products';

    if(is_numeric($ID))
    {
        // GET THE PRODUCT DETAILS
        $GetProducts = MysqlQuery("SELECT pc.CategoryID, p.* FROM products p LEFT JOIN product_categories pc ON pc.ProductID = p.ProductID WHERE p.ProductID = '".$ID."'");
        if(mysqli_num_rows($GetProducts) > 0)
        {
            $ProductDetails = MysqlFetchAll($GetProducts);
            foreach($ProductDetails as $data)
            {
                $ProductID = $data['ProductID'];
                $Product[$ProductID] = $data;
                $CategoryID[] = $data['CategoryID'];
                $FileName[$data['ImageID']] = $data['FileName'];
            }
            $_POST = $Product[$ProductID];
            $_POST['ParentID'] = array_values(array_unique($CategoryID));

            // GET THE PRODUCT IMAGES
            $ProductPictures = MysqlQuery("SELECT * FROM product_images WHERE ProductID = '".$ID."'");
            $ProductPictures = MysqlFetchAll($ProductPictures);

            // GET THE PRODUCT SIZE LABELS FROM THE CATEGORY TABLE
            $SizeLabels = MysqlQuery("SELECT Size FROM master_categories WHERE CategoryID = '".$_POST['ParentID'][0]."' LIMIT 1");
            $SizeLabels = mysqli_fetch_assoc($SizeLabels)['Size'];
            $SizeLabels = json_decode($SizeLabels, true);

            // GET THE PRODUCT STOCK DETAILS
            $ProductStock = MysqlQuery("SELECT * FROM product_stock WHERE ProductID = '".$ID."'");
            if(mysqli_num_rows($ProductStock) > 0)
            {
                for(;$row = mysqli_fetch_assoc($ProductStock);)
                {
                    //$_POST['Stock'][$row['Size']]['SKU'] = $row['SKU'];
                    //$_POST['Stock'][$row['Size']]['Quantity'] = $row['Quantity'];
                }
            }
        }
        else
        {
            $_SESSION['AlertMessage'] = 'Product does not exists!';
            header('Location: /admin/products/');
            exit;
        }

    }

    // GET THE CATEGORY URL
    $GetCategoryDetails = MysqlQuery("SELECT Size FROM master_categories WHERE CategoryID = '".$ID."' LIMIT 1");
    $GetCategoryDetails = mysqli_fetch_assoc($GetCategoryDetails);

    $ProductSizeArr = json_decode($GetCategoryDetails['Size'], true);

    $GetProductColors = MysqlQuery("SELECT * FROM product_colors WHERE Status = '1' ORDER By ColorName");
    for(;$row = mysqli_fetch_assoc($GetProductColors);)
    {
        $ProductColors .= '<option'.($_POST['Color'] == $row['ColorID'] ? ' selected' : '').' value="'.$row['ColorID'].'">'.$row['ColorName'].'</option>';
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">

        <title><?=$PageTitle?></title>

        <?php include_once(_ROOT._AdminIncludesDir."common-css.php"); ?>
        <?php include_once(_ROOT._AdminIncludesDir."common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            $('.responsive-tabs').responsiveTabs({
                accordionOn: ['xs', 'sm']
            });

            lightbox.option({ 'showImageNumberLabel': false, 'wrapAround': true, alwaysShowNavOnTouchDevices: true })

            options = {
                        showThumbs: false,
                        limit: 5,
                        maxSize: null,
                        changeInput: true,
                        afterShow: function(){
                            $('#tab-pictures').find('form').submit();
                        },
                    };

            $('input[name=ProductImage]').filer(options);

            $(".ProductForm").submit(function(e)
            {
                e.preventDefault();
                ShowProcessing();
                AjaxResponse = AjaxFormSubmit(this);

                $.when(AjaxResponse).done(function(response)
                {
                    response = $.parseJSON(response);

                    if(response.status == 'success')
                    {
                        if(typeof response.message !== 'undefined')
                        {
                            if(typeof response.redirect !== 'undefined')
                            {
                                AlertBox(response.message, 'success', function(){ location.href = response.redirect });
                            }
                            else
                            {
                                AlertBox(response.message, 'success');
                            }
                        }
                        else if(typeof response.html !== 'undefined')
                        {
                            $('.products-wrapper').append(response.html);
                        }
                    }
                    else if(response.status == 'login')
                    {
                        AlertBox('Oops! Your session has expired! Please login again.', 'error', function(){ location.href = response.redirect } );
                    }
                    else if(response.status == 'validation')
                	{
                	    ThrowError(response.error, true);
                	}
                    else
                    {
                        AlertBox(response.message);
                    }
                });
            });

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
                                <div class="content-header clearfix">
                                    <h1 class="pull-left page-title"><?=$PageTitle?></h1>
                                    <div class="pull-right">
                                        <?php if($EditMode) { ?>

                                        <button type="button" class="btn btn-danger">
                                            <i class="fa fa-trash-o"></i>&nbsp;
                                            Delete
                                        </button>

                                        <?php } ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div>
                                    <ul class="nav nav-tabs responsive-tabs" style="width: 100%;">
                                        <li class="active tab"><a data-tab-name="tab-info" data-toggle="tab" href="#tab-info">Product Info</a></li>
                                        <li class="tab"><a data-toggle="tab" href="#tab-pictures">Product Images</a></li>
                                        <!-- <li class="tab"><a data-toggle="tab" href="#tab-stock">Stock</a></li>
                                        <li class="tab"><a data-toggle="tab" href="#tab-seo">SEO</a></li> -->
                                    </ul>
                                    <div class="tab-content">

                                        <!-----------------------------
                                            START OF INFO TAB
                                        ------------------------------>
                                        <div class="tab-pane active" id="tab-info">
                                            <form action="/admin/ajax/add-product/" class="form-horizontal ProductForm">
                                                <input type="hidden" name="section" value="info">
                                                <input type="hidden" name="ProductID" value="<?=$ID?>">
                                                <div class="clearfix">
                                                    <div class="col-md-9">
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label required">Category</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <select class="selectric" multiple="multiple" name="ParentID[]">
                                                                    <option value="">Select Category</option>
                                                                    <?php
                                                                    foreach(OtherCategories() as $CatID => $CatTitle)
                                                                    {
                                                                        echo '<option'.(in_array($CatID, $_POST['ParentID']) ? ' selected' : '').' value="'.$CatID.'">'.$CatTitle.'</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label required">Product name</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input class="form-control" max="100" name="ProductName" required type="text" value="<?=$_POST['ProductName']?>">
                                                            </div>
                                                        </div>
                                                        <!--
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label required">Short Description</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <textarea class="form-control" name="ShortDescription" required ShowCounter="250"><?=$_POST['ShortDescription']?></textarea>
                                                            </div>
                                                        </div>
                                                        -->
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label required">Description</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <textarea class="form-control" name="FullDescription" rows="10"><?=$_POST['FullDescription']?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label required">Color</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <select class="selectric" name="Color">
                                                                    <option value="">Select Color</option>
                                                                    <?=$ProductColors?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label required">Rate</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <div class="input-group-addon">Rs.</div>
                                                                    <input class="form-control" maxlength="50" name="Rate" required step="0.01" type="number" value="<?=$_POST['Rate']?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label">Weight</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" maxlength="5" name="Weight" step="0.01" type="number" value="<?=$_POST['Weight']?>">
                                                                    <div class="input-group-addon select">
                                                                        <select name="WeightUnit">
                                                                            <option value="gm">gm</option>
                                                                            <option value="kg">kg</option>
                                                                            <option value="lt">lt</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label">Discount</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" maxlength="50" name="Discount" step="0.01" type="number" value="<?=$_POST['Discount']?>">
                                                                    <div class="input-group-addon select">
                                                                        <select name="DiscountType">
                                                                            <option value="%">%</option>
                                                                            <option value="Rs">Rs</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label required">Tax Rate</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <div class="input-group">
                                                                    <input class="form-control" maxlength="5" name="TaxRate" required step="0.01" type="number" value="<?=$_POST['TaxRate']?>">
                                                                    <div class="input-group-addon">%</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                            </div>
                                                            <div class="col-md-9">
                                                                <button class="btn btn-info">
                                                                    <i class="fa fa-floppy-o"></i>&nbsp; Save Details
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">

                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <!-----------------------------
                                            END OF INFO TAB
                                        ------------------------------>

                                        <!-----------------------------
                                            START OF PICTURES TAB
                                        ------------------------------>
                                        <div class="tab-pane" id="tab-pictures">
                                            <?php
                                            if(!isset($ID))
                                            {
                                                echo 'You need to add Product before uploading the images';
                                            }
                                            else
                                            {
                                            ?>

                                            <div class="cat-grid row">
                                                <div class="products-wrapper">
                                                    <?php
                                                    if(count($ProductPictures) > 0)
                                                    {
                                                        foreach($ProductPictures as $Image)
                                                        {
                                                            if($Image['DefaultImage'] == '1')
                                                            {
                                                                $SetDefault = ' active';
                                                                $DefaultOption = ' hidden';
                                                            }
                                                            else
                                                            {
                                                                $SetDefault = '';
                                                                $DefaultOption = '';
                                                            }
                                                    ?>
                                                        <!-- Product Item Start -->
                                                        <div class="product">
                                                            <div class="col-md-3 col-sm-6 col-xs-12">
                                                                <div class="product-item">
                                                                    <!-- Product Image -->
                                                                    <figure class="product-grid-image">
                                                                        <img class="set-default<?=$SetDefault?>" src="/images/mark-default.png" title="Default Image">
                                                                        <a href="<?=_ProductImageDir.$Image['FileName']?>" data-lightbox="product">
                                                                            <img class="img-responsive" src="<?=_ProductImageDir.$Image['FileName']?>">
                                                                        </a>
                                                                        <!-- Product Buttons -->
                                                                        <div class="figure-caption">
                                                                            <ul class="icons">
                                                                                <li>
                                                                                    <a class="default-pic<?=$DefaultOption?>" href="javascript:void(0)" data-id="<?=$ID?>" data-pk="<?=$Image['ImageID']?>" data-opt="product_image" onclick="SetDefaultImage(this)"><i class="fa fa-check"></i></a>
                                                                                </li>
                                                                                <li>
                                                                                    <a href="javascript:void(0)" data-id="<?=$Image['ImageID']?>" data-opt="product_image" onclick="DeletejFilerThumbnail(this)"><i class="fa fa-trash"></i></a>
                                                                            	</li>
                                                                            </ul>
                                                                        </div>
                                                                    </figure>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Product Item Start -->
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <form action="/admin/ajax/add-product-pictures/" class="form-horizontal ProductForm">
                                                <input type="hidden" name="section" value="pictures">
                                                <input type="hidden" name="ProductID" value="<?=$ID?>">

                                                <div class="clearfix">
                                                    <div class="col-md-12 mg-t-20">
                                                        <input type="file" name="ProductImage" data-jfiler-changeInput='<div class="btn btn-white"><div><i class="fa fa-upload"></i>&nbsp; Add Picture</div></div>' data-jfiler-extensions="jpg,png" data-jfiler-caption="Only JPG & PNG files are allowed to be uploaded.">
                                                    </div>
                                                    <div class="col-md-12 mg-t-10 text-danger">
                                                        Note: Image dimension must be 1000 x 1400 px (width x height)
                                                    </div>
                                                </div>
                                            </form>

                                            <?php
                                            }   // END OF ELSE
                                            ?>
                                        </div>
                                        <!-----------------------------
                                            END OF PICTURES TAB
                                        ------------------------------>

                                        <!-----------------------------
                                            START OF STOCK TAB
                                        ------------------------------
                                        <div class="tab-pane" id="tab-stock">
                                            <?php
                                            if(!isset($ID))
                                            {
                                                echo 'You need to add Product before updating the Stock details';
                                            }
                                            else
                                            {
                                            ?>

                                            <form action="/admin/ajax/add-product/" class="form-horizontal ProductForm">
                                                <input type="hidden" name="section" value="stock">
                                                <input type="hidden" name="ProductID" value="<?=$ID?>">
                                                <div class="clearfix">
                                                    <div class="col-md-8">
                                                        <table class="table table-borderless">
                                                            <thead>
                                                                <tr>
                                                                    <th>Size</th>
                                                                    <th class="text-center">SKU</th>
                                                                    <th class="text-center">Quantity</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                        <?php
                                                        foreach($SizeLabels as $k => $s)
                                                        {
                                                        ?>
                                                                <tr>
                                                                    <td><?=$k?></td>
                                                                    <td>
                                                                        <input type="text" class="form-control sku" maxlength="25" name="Stock[<?=$k?>][SKU]" value="<?=$_POST['Stock'][$k]['SKU']?>">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control" maxlength="5" name="Stock[<?=$k?>][Quantity]" value="<?=$_POST['Stock'][$k]['Quantity']?>">
                                                                    </td>
                                                                </tr>
                                                        <?php
                                                        }
                                                        ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-12 mg-t-20">
                                                        <button class="btn btn-info">
                                                            <i class="fa fa-floppy-o"></i>&nbsp; Save Details
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>

                                            <?php
                                            }   // END OF ELSE
                                            ?>
                                        </div>
                                        <!-----------------------------
                                            END OF STOCK TAB
                                        ------------------------------>

                                        <!-----------------------------
                                            START OF SEO TAB
                                        ------------------------------>
                                        <div class="tab-pane" id="tab-seo">
                                            <?php
                                            if(!isset($ID))
                                            {
                                                echo 'You need to add Product before updating the SEO details';
                                            }
                                            else
                                            {
                                            ?>

                                            <form action="/admin/ajax/add-product/" class="form-horizontal ProductForm">
                                                <input type="hidden" name="section" value="seo">
                                                <input type="hidden" name="ProductID" value="<?=$ID?>">
                                                <input type="hidden" name="MetaID" value="<?=$_POST['MetaID']?>">
                                                <div class="clearfix">
                                                    <div class="col-md-8">
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label">Meta Keywords</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input class="form-control" max="100" name="MetaKeywords" required type="text" value="<?=$_POST['MetaKeywords']?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label">Meta Description</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <textarea class="form-control" name="MetaDescription" required ShowCounter="150"><?=$_POST['MetaDescription']?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label">Meta Title</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input class="form-control" max="70" name="MetaTitle" required type="text" value="<?=$_POST['MetaTitle']?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                                <label class="control-label">Search Engine Friendly Page Name</label>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input class="form-control" max="100" name="MetaPageName" type="text" value="<?=$_POST['MetaTitle']?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-md-3">
                                                            </div>
                                                            <div class="col-md-9">
                                                                <button class="btn btn-info">
                                                                    <i class="fa fa-floppy-o"></i>&nbsp; Save Details
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </form>

                                            <?php
                                            }   // END OF ELSE
                                            ?>
                                        </div>
                                        <!-----------------------------
                                            END OF SEO TAB
                                        ------------------------------>
                                    </div>
                                </div>
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