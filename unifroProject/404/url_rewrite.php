<?php
    include_once("../include-files/autoload-server-files.php");

	/********NOW LET'S CHECK IF THE URL ENDS WITH A '/', IF NOT REDIRECT TO PROPER URL*******************/
	$FileExt = strrchr(strtolower($baseUrl), '.');
	$AllowedExts = array('.php', '.html', '.htm', '.css', '.js', '.jpg', '.jpeg', '.png', '.gif');
	if(($FileExt === false || !in_array($FileExt, $AllowedExts)) && substr($baseUrl, -1, 1) != '/')
	{
		header('HTTP/1.0 301 Permanently Moved');
		header('Location: '.$baseUrl.'/'.($params != '' ? $params : ''));
		exit();
	}
	/****************************************************************************************************/

	$params = explode('&', $params);
	$ParamArray = array();
	for($c = 0; $c < count($params); $c++)
	{
		$value = explode('=', $params[$c]);
		if($value[1] != '')
		{
			$_GET[$value[0]] = urldecode($value[1]);
		}
	}
	/**********************************************************************************************************************************************/

	$FolderURL = '/'.implode('/', $FolderLevels).'/';
    $RewritePageFound = '';
	$RewritePageRedirect = '';
    $HomeURL = '/';

    if($FolderLevels[0] == 'admin')
    {
        $HomeURL = '/admin/';

        if(count($FolderLevels) == 2)
        {
            if($FolderURL == '/admin/dashboard/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/dashboard.php';
        	}
            elseif($FolderURL == '/admin/change-password/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/change-password.php';
        	}
        }
        elseif(count($FolderLevels) == 3)
        {
            if($FolderURL == '/admin/ajax/update-status/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/include-files/status.php';
        	}
            elseif($FolderURL == '/admin/ajax/delete-selected/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/include-files/delete-selected.php';
        	}
            elseif($FolderURL == '/admin/fabrics/add/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/fabrics/add-fabric.php';
                $PageTitle = 'Add New Fabric';
        	}
            elseif($FolderURL == '/admin/fabrics/edit/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/fabrics/add-fabric.php';
                $PageTitle = 'Edit Fabric Details';
                $EditMode = true;
        	}
            elseif($FolderURL == '/admin/fabrics/bulk-upload/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/fabrics/bulk-upload.php';
        	}
            elseif($FolderURL == '/admin/ajax/fabric-bulk-upload/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/fabrics/ajax-bulk-upload.php';
        	}
            elseif($FolderURL == '/admin/ajax/add-fabric/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/fabrics/save-fabric.php';
        	}
            elseif($FolderURL == '/admin/ajax/upload-svg/')
            {
                $RewritePageFound = _ROOT.'/admin/categories/custom/upload-svg.php';
            }
            elseif($FolderURL == '/admin/ajax/set-default/')
            {
                $RewritePageFound = _ROOT.'/admin/include-files/set-default.php';
            }
            elseif($FolderURL == '/admin/categories/elements/')
            {
                $RewritePageFound = _ROOT.'/admin/categories/custom/elements.php';
            }
            elseif($FolderURL == '/admin/ajax/save-element-style/')
            {
                $RewritePageFound = _ROOT.'/admin/categories/custom/save-element-styles.php';
            }
            elseif($FolderURL == '/admin/sales/clients/')
            {
                $RewritePageFound = _ROOT.'/admin/sales/clients.php';
            }
            elseif($FolderURL == '/admin/sales/add/')
            {
                $RewritePageFound = _ROOT.'/admin/sales/create-account.php';
            }
            elseif($FolderURL == '/admin/products/add/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/products/add-product.php';
                $PageTitle = 'Add Product';
        	}
            elseif($FolderURL == '/admin/ajax/add-product/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/products/save-product.php';
        	}
            elseif($FolderURL == '/admin/ajax/add-product-pictures/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/products/add-product-pictures.php';
        	}
            elseif($FolderURL == '/admin/category/update-baseprice/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/categories/custom/ajax-update-baseprice.php';
        	}
            elseif($FolderURL == '/admin/ajax/edit-order/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/clients/orders/edit-order.php';
        	}
            elseif($FolderURL == '/admin/ajax/additional-details-form/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/include-files/additional-details-form.php';
        	}
            elseif($FolderURL == '/admin/ajax/save-additional-details/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/clients/orders/additional-details-save.php';
        	}
            elseif($FolderURL == '/admin/ajax/delete-order/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/clients/orders/delete-order.php';
        	}
            elseif($FolderURL == '/admin/ajax/element-percentage/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/categories/custom/update-element-percentage.php';
        	}
            elseif($FolderURL == '/admin/shipping/edit/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/shipping/edit.php';
                $ID = $_GET['id'];
        	}
            elseif($FolderURL == '/admin/ajax/delete-account/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/customers/delete-account.php';
        	}
            elseif($FolderURL == '/admin/ajax/delete-review/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/reviews/delete-review.php';
        	}
            elseif($FolderURL == '/admin/coupons/add/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/coupons/add-coupon.php';
                $PageTitle = 'Add New Coupon';
        	}
            elseif($FolderURL == '/admin/coupons/edit/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/coupons/add-coupon.php';
                $PageTitle = 'Edit Coupon Details';
                $ID = $_GET['id'];
        	}
            elseif($FolderURL == '/admin/ajax/add-coupon/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/coupons/save-coupon.php';
        	}
            elseif($FolderURL == '/admin/ajax/add-role/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/roles/save-role.php';
        	}
            elseif($FolderURL == '/admin/accounts/add/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/accounts/add-account.php';
                $PageTitle = 'Add New Account';
        	}
            elseif($FolderURL == '/admin/accounts/edit/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/accounts/add-account.php';
                $PageTitle = 'Edit Account Details';
                $ID = $_GET['id'];
        	}
            elseif($FolderURL == '/admin/ajax/create-account/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/accounts/save-account.php';
        	}
            elseif($FolderURL == '/admin/banners/add/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/banners/add-banner.php';
                $PageTitle = 'Add New Banner';
        	}
            elseif($FolderURL == '/admin/banners/edit/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/banners/add-banner.php';
                $PageTitle = 'Edit Banner';
                $ID = $_GET['id'];
        	}
            elseif($FolderURL == '/admin/ajax/save-banner/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/banners/save-banner.php';
        	}
            elseif($FolderURL == '/admin/ajax/search-product/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/include-files/ajax-search-product.php';
        	}
            elseif($FolderURL == '/admin/ajax/add-combox-product/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/products/combo/save-combo.php';
        	}
            elseif($FolderURL == '/admin/abandoned-cart/clients/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/abandoned-cart/client-cart.php';
        	}
            elseif($FolderURL == '/admin/abandoned-cart/customers/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/abandoned-cart/customer-cart.php';
        	}

        }
        elseif(count($FolderLevels) == 4)
        {
            if($FolderLevels[1] == 'categories' && $FolderLevels[2] == 'custom')
            {
                $CatURL = '/'.$FolderLevels[2].'/'.$FolderLevels[3].'/';

                $ValidateURL = MysqlQuery("SELECT CategoryTitle FROM master_categories WHERE CategoryURL = '".$CatURL."' LIMIT 1");
                if(mysqli_num_rows($ValidateURL) == 1)
            	{
            	    $ValidateURL = mysqli_fetch_assoc($ValidateURL);

                    $PageTitle = $ValidateURL['CategoryTitle'].' Elements';
                    $ID = $_GET['id'];
                    $RewritePageFound = _ROOT.'/admin/categories/custom/category.php';
                }
            }
            elseif($FolderURL == '/admin/categories/other/add/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/categories/other/add-category.php';
                $PageTitle = 'Add New Category';
        	}
            elseif($FolderURL == '/admin/categories/other/edit/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/categories/other/add-category.php';
                $PageTitle = 'Edit Category Details';
        	}
            elseif($FolderURL == '/admin/ajax/categories/other-save/')
            {
                $RewritePageFound = _ROOT.'/admin/categories/other/save-category.php';
            }
            elseif($FolderURL == '/admin/ajax/delete/jfiler-thumbnail/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/include-files/delete-jfiler-thumbnail.php';
        	}
            elseif($FolderURL == '/admin/ajax/sales/add/')
            {
                $RewritePageFound = _ROOT.'/admin/sales/ajax-create-sales-account.php';
            }
            elseif($FolderLevels[1] == 'clients' && $FolderLevels[2] == 'products' && $FolderLevels[3] > 0)
            {
                $ClientID = $FolderLevels[3];
                $RewritePageFound = _ROOT.'/admin/clients/products.php';
            }
            elseif($FolderLevels[2] == 'edit')
            {
                if($FolderLevels[1] == 'products')
                {
                    $RewritePageFound = _ROOT.'/admin/products/add-product.php';
                    $PageTitle = 'Edit Product Details';
                    $ID = $FolderLevels[3];
                }
            }
            elseif($FolderURL == '/admin/clients/orders/details/')
            {
                $RewritePageFound = _ROOT.'/admin/clients/orders/order-details.php';
                $PageTitle = 'Order Details';
                $ID = $_GET['id'];
            }
            elseif($FolderURL == '/admin/customers/orders/details/')
            {
                $RewritePageFound = _ROOT.'/admin/customers/orders/order-details.php';
                $PageTitle = 'Order Details';
                $ID = $_GET['id'];
            }
            elseif($FolderURL == '/admin/ajax/download/fabrics/')
            {
                $RewritePageFound = _ROOT.'/admin/include-files/download-csv.php';
                $Option = 'Fabric';
            }
            elseif($FolderURL == '/admin/ajax/shipping/save/')
            {
                $RewritePageFound = _ROOT.'/admin/shipping/save-shipping-details.php';
            }
            elseif($FolderURL == '/admin/ajax/customers/edit-order/')
            {
                $RewritePageFound = _ROOT.'/admin/customers/orders/edit-order.php';
            }
            elseif($FolderURL == '/admin/ajax/customer/delete-order/')
            {
                $RewritePageFound = _ROOT.'/admin/customers/orders/delete-order.php';
            }
            elseif($FolderURL == '/admin/products/combo/add/')
        	{
        	    $RewritePageFound = _ROOT.'/admin/products/combo/add-combo.php';
                $PageTitle = 'Add Combo Product';
        	}

        }
        elseif(count($FolderLevels) == 5)
        {
            if($FolderLevels[1] == 'categories' && $FolderLevels[2] == 'custom')
            {
                $CatURL = '/'.$FolderLevels[2].'/'.$FolderLevels[3].'/'.$FolderLevels[4].'/';

                $ValidateURL = MysqlQuery("SELECT CategoryTitle FROM master_categories WHERE CategoryURL = '".$CatURL."' LIMIT 1");
                if(mysqli_num_rows($ValidateURL) == 1)
            	{
            	    $ValidateURL = mysqli_fetch_assoc($ValidateURL);

                    $PageTitle = $ValidateURL['CategoryTitle'].' Elements';
                    $ID = $_GET['id'];
                    $RewritePageFound = _ROOT.'/admin/categories/custom/category.php';
                }
            }
            elseif($FolderLevels[2] == 'combo' && $FolderLevels[3] == 'edit')
            {
                if($FolderLevels[1] == 'products')
                {
                    $RewritePageFound = _ROOT.'/admin/products/combo/add-combo.php';
                    $PageTitle = 'Edit Product Details';
                    $ID = $FolderLevels[4];
                }
            }
        }
    }
    elseif($FolderLevels[0] == 'custom')
    {
        /*------------------------------
        let's validate the URL
        -------------------------------*/
        if(is_numeric(end($FolderLevels)))
        {
            $CartID = end($FolderLevels);
            array_pop($FolderLevels);
            $FolderURL = '/'.implode('/', $FolderLevels).'/';
            $EditMode = true;
        }

        $ValidateURL = MysqlQuery("SELECT CategoryID, CategoryTitle, CategoryURL, BasePrice, SVGType FROM master_categories WHERE CategoryURL = '".$FolderURL."' LIMIT 1");
        if(mysqli_num_rows($ValidateURL) == 1)
    	{
    	    $CategoryDetails = mysqli_fetch_assoc($ValidateURL);

            // FETCH THE SVG IMAGES
            $SVGs = MysqlQuery("SELECT * FROM master_category_svg WHERE CategoryID = '".$CategoryDetails['CategoryID']."' AND SVGStatus = '1' ORDER BY SortOrder");
            if(mysqli_num_rows($SVGs) > 0)
            {
                for(; $row = mysqli_fetch_assoc($SVGs);)
                {
                    $ProductSVGs[$row['SVGID']] = array('SVGName' => _ROOT._SVGDir.$row['SVGName'], 'SVGTitle' => $row['SVGTitle'], 'MainTexture' => $row['MainTexture'], 'SortOrder' => $row['SortOrder']);
                    $SVGObjects[$row['SVGID']] = file_get_contents(_ROOT._SVGDir.$row['SVGName']);
                }
                $SVGObjects = json_encode($SVGObjects);
            }

            $RewritePageFound = _ROOT.'/custom-design/index.php';
    	}
    }
    elseif($FolderLevels[0] == 'products')
    {
        if($FolderURL == '/products/')
        {
            $RewritePageFound = _ROOT.'/readymade/categories.php';
            $PageTitle = 'Readymade Products';
        }
        else
        {
            /*------------------------------
            let's validate the Category URL
            -------------------------------*/
            $ValidateURL = MysqlQuery("SELECT * FROM master_categories WHERE CategoryURL = '".$FolderURL."' LIMIT 1");
            if(mysqli_num_rows($ValidateURL) == 1)
        	{
        	    $CategoryDetails = mysqli_fetch_assoc($ValidateURL);

                $MetaTitle = $CategoryDetails['CategoryTitle'].' Online';
                $CategoryHeading = str_replace('Products', '', ucwords( str_replace('/', ' ', GenerateCategoryURL($CategoryDetails['CategoryID'])) ));

        	    $RewritePageFound = _ROOT.'/readymade/product-list.php';
        	}
        }
    }
    elseif($FolderLevels[0] == 'buy')
    {
        /*------------------------------
        let's validate the product URL
        -------------------------------*/
        $ValidateURL = MysqlQuery("SELECT * FROM products WHERE ProductURL = '".$FolderURL."' LIMIT 1");
        if(mysqli_num_rows($ValidateURL) == 1)
    	{
    	    $ProductDetails = mysqli_fetch_assoc($ValidateURL);
            $MetaTitle = 'Buy '.$ProductDetails['ProductName'].' | '._WebsiteName;
            $ProductID = $ProductDetails['ProductID'];

    	    $RewritePageFound = _ROOT.'/readymade/product.php';
    	}
    }
    elseif($FolderLevels[0] == 'upload-your-design')
    {
        $RewritePageFound = _ROOT.'/upload-your-design.php';
    }
    elseif($FolderLevels[0] == 'sales')
    {
        if($FolderLevels[1] == 'clients' && is_numeric($FolderLevels[2]))
        {
            $ClientID = $FolderLevels[2];
            $RewritePageFound = _ROOT.'/sales/client-products.php';
        }
        elseif($FolderLevels[1] == 'clients' && $FolderLevels[2] == 'addresses' && is_numeric($FolderLevels[3]))
        {
            $ClientID = $FolderLevels[3];
            $RewritePageFound = _ROOT.'/sales/delivery-address.php';
        }
        elseif($FolderLevels[1] == 'clients' && $FolderLevels[2] == 'additional-details' && is_numeric($FolderLevels[3]))
        {
            $ClientID = $FolderLevels[3];
            $RewritePageFound = _ROOT.'/sales/additional-details.php';
        }
        elseif($FolderLevels[1] == 'clients' && $FolderLevels[2] == 'orders' && is_numeric($FolderLevels[3]))
        {
            $ClientID = $FolderLevels[3];
            $RewritePageFound = _ROOT.'/sales/client-orders.php';
        }
        elseif($FolderURL == '/sales/add-client/')
        {
            $RewritePageFound = _ROOT.'/sales/add-client.php';
        }
        elseif($FolderURL == '/sales/change-password/')
        {
            $RewritePageFound = _ROOT.'/sales/change-password.php';
        }
        elseif($FolderLevels[1] == 'clients' && $FolderLevels[2] == 'add-address' && is_numeric($FolderLevels[3]))
        {
            $ClientID = $FolderLevels[3];
            $RewritePageFound = _ROOT.'/sales/add-address.php';
        }
        elseif($FolderLevels[1] == 'clients' && $FolderLevels[2] == 'edit-address' && is_numeric($FolderLevels[3]))
        {
            $ClientID = $FolderLevels[3];
            $EditMode = true;
            $RewritePageFound = _ROOT.'/sales/add-address.php';
        }
        elseif($FolderURL == '/sales/reset-password/')
    	{
    	    $RewritePageFound = _ROOT.'/sales/ajax-files/ajax-reset-password.php';
    	}
        elseif($FolderLevels[1] == 'reset-password')
    	{
    	    $PasswordHash = $FolderLevels[2];

            // LETS VALIDATE THE LINK
            $ValidateLink = MysqlQuery("SELECT SalesID FROM sales WHERE ForgotPasswordLink = '".$PasswordHash."' LIMIT 1");
            if(mysqli_num_rows($ValidateLink) == 1)
            {
                $SalesDetails = mysqli_fetch_assoc($ValidateLink);

                $RewritePageFound = _ROOT.'/sales/reset-password.php';
            }
    	}
    }
    else
    {
        if(count($FolderLevels) == 1)
        {
            if($FolderURL == '/login/')
            {
                $RewritePageFound = _ROOT.'/login.php';
            }
            elseif($FolderURL == '/about/')
            {
                $RewritePageFound = _ROOT.'/about-us.php';
            }
            elseif($FolderURL == '/privacy-policy/')
            {
                $RewritePageFound = _ROOT.'/privacy-policy.php';
            }
            elseif($FolderURL == '/return-policy/')
            {
                $RewritePageFound = _ROOT.'/return-policy.php';
            }
            elseif($FolderURL == '/cancellation-policy/')
            {
                $RewritePageFound = _ROOT.'/cancellation-policy.php';
            }
            elseif($FolderURL == '/terms/')
            {
                $RewritePageFound = _ROOT.'/terms.php';
            }
            elseif($FolderURL == '/contact/')
            {
                $RewritePageFound = _ROOT.'/contact.php';
            }
            elseif($FolderURL == '/how-it-works/')
            {
                $RewritePageFound = _ROOT.'/how-it-works.php';
            }
            elseif($FolderURL == '/faq/')
            {
                $RewritePageFound = _ROOT.'/faq.php';
            }
            elseif($FolderURL == '/sales-login/')
            {
                $RewritePageFound = _ROOT.'/sales/sales-login.php';
            }
            elseif($FolderURL == '/logout/')
            {
                $RewritePageFound = _ROOT.'/logout.php';
            }
            elseif($FolderURL == '/register/')
            {
                $RewritePageFound = _ROOT.'/registration.php';
            }
            elseif($FolderURL == '/shopping-bag/')
            {
                $RewritePageFound = _ROOT.'/shopping-bag.php';
            }
        }
        elseif(count($FolderLevels) == 2)
        {
            if($FolderURL == '/ajax/cart-customer-add/')
        	{
        	    $RewritePageFound = _ROOT.'/include-files/ajax-add-cart.php';
        	}
            elseif($FolderURL == '/ajax/cart-sales-add/')
        	{
        	    $RewritePageFound = _ROOT.'/sales/ajax-files/ajax-sales-cart.php';
        	}
            elseif($FolderURL == '/ajax/login/')
        	{
        	    $RewritePageFound = _ROOT.'/include-files/ajax-login.php';
        	}
            elseif($FolderURL == '/ajax/sales-login/')
        	{
        	    $RewritePageFound = _ROOT.'/sales/ajax-files/ajax-sales-login.php';
        	}
            elseif($FolderURL == '/ajax/cities/')
        	{
        	    $RewritePageFound = _ROOT.'/include-files/ajax-cities-list.php';
        	}
            elseif($FolderURL == '/get/fabric-list/')
        	{
        	    $RewritePageFound = _ROOT.'/include-files/ajax-list-fabrics.php';
        	}
            elseif($FolderURL == '/ajax/newsletter-subscription/')
        	{
        	    $RewritePageFound = _ROOT.'/include-files/ajax-subscribe-newsletter.php';
        	}
            elseif($FolderURL == '/customers/reset-password/')
        	{
        	    $RewritePageFound = _ROOT.'/include-files/ajax-reset-password.php';
        	}
            elseif($FolderURL == '/ajax/register/')
        	{
        	    $RewritePageFound = _ROOT.'/include-files/ajax-register.php';
        	}
            elseif($FolderURL == '/ajax/load-bag/')
        	{
        	    $RewritePageFound = _ROOT.'/include-files/ajax-load-bag-products.php';
        	}
            elseif($FolderURL == '/checkout/additional-details/')
        	{
        	    $RewritePageFound = _ROOT.'/account/additional-details.php';
        	}
            elseif($FolderURL == '/checkout/addresses/')
        	{
        	    $RewritePageFound = _ROOT.'/account/delivery-address.php';
        	}
            elseif($FolderURL == '/checkout/add-address/')
        	{
        	    $RewritePageFound = _ROOT.'/account/add-address.php';
                $CancelURL = '/checkout/addresses/';
                $ReturnURL = 'Checkout';
        	}
            elseif($FolderURL == '/checkout/edit-addresses/')
            {
                $RewritePageFound = _ROOT.'/account/add-address.php';
                $ID = $_GET['id'];
                $CancelURL = '/checkout/addresses/';
                $ReturnURL = 'Checkout';
            }
            elseif($FolderURL == '/checkout/payment/')
        	{
        	    $RewritePageFound = _ROOT.'/account/payment.php';
        	}
            elseif($FolderURL == '/account/change-password/')
            {
                $RewritePageFound = _ROOT.'/account/change-password.php';
            }
            elseif($FolderURL == '/account/addresses/')
        	{
        	    $RewritePageFound = _ROOT.'/account/saved-addresses.php';
        	}
            elseif($FolderURL == '/account/profile/')
        	{
        	    $RewritePageFound = _ROOT.'/account/profile.php';
        	}
            elseif($FolderURL == '/account/reviews/')
        	{
        	    $RewritePageFound = _ROOT.'/account/reviews.php';
        	}
            elseif($FolderURL == '/account/orders/')
        	{
        	    $RewritePageFound = _ROOT.'/account/orders.php';
        	}
            elseif($FolderURL == '/ajax/load-products/')
        	{
        	    $RewritePageFound = _ROOT.'/readymade/ajax-load-products.php';
        	}
            elseif($FolderURL == '/ajax/delivery-pincode/')
        	{
        	    $RewritePageFound = _ROOT.'/include-files/ajax-delivery-pincode.php';
        	}
            elseif($FolderURL == '/ajax/upload-design/')
        	{
        	    $RewritePageFound = _ROOT.'/include-files/ajax-save-uploaded-design.php';
        	}
            elseif($FolderURL == '/ajax/delete-review/')
        	{
        	    $RewritePageFound = _ROOT.'/account/ajax-files/delete-review.php';
        	}
            elseif($FolderURL == '/ajax/retry-order/')
        	{
        	    $RewritePageFound = _ROOT.'/account/orders/retry-order.php';
        	}
            elseif($FolderURL == '/ajax/cancel-order/')
        	{
        	    $RewritePageFound = _ROOT.'/account/orders/cancel-order.php';
        	}
            elseif($FolderURL == '/ajax/upload-measurement/')
        	{
        	    $RewritePageFound = _ROOT.'/sales/ajax-files/upload-measurements.php';
        	}


        }
        elseif(count($FolderLevels) == 3)
        {
            if($FolderURL == '/ajax/client/add/')
            {
                $RewritePageFound = _ROOT.'/sales/ajax-files/ajax-add-client.php';
            }
            elseif($FolderURL == '/ajax/sales/change-password/')
            {
                $RewritePageFound = _ROOT.'/sales/ajax-files/ajax-change-password.php';
            }
            elseif($FolderURL == '/ajax/sales/set-default-address/')
            {
                $RewritePageFound = _ROOT.'/sales/ajax-files/set-default-address.php';
            }
            elseif($FolderURL == '/ajax/sales/add-address/')
            {
                $RewritePageFound = _ROOT.'/sales/ajax-files/add-client-address.php';
            }
            elseif($FolderURL == '/ajax/sales/place-order/')
            {
                $RewritePageFound = _ROOT.'/sales/ajax-files/place-order.php';
            }
            elseif($FolderURL == '/ajax/sales/load-cart/')
            {
                $RewritePageFound = _ROOT.'/sales/ajax-files/load-cart-products.php';
            }
            elseif($FolderURL == '/ajax/sales/save-additional-details/')
            {
                $RewritePageFound = _ROOT.'/sales/ajax-files/save-additional-details.php';
            }
            elseif($FolderURL == '/ajax/sales/additional-details-form/')
            {
                $RewritePageFound = _ROOT.'/sales/ajax-files/additional-details-form.php';
            }
            elseif($FolderURL == '/ajax/sales/add-monogram/')
            {
                $RewritePageFound = _ROOT.'/sales/ajax-files/save-monogram.php';
            }
            elseif($FolderLevels[0] == 'customers' && $FolderLevels[1] == 'reset-password')
        	{
        	    $PasswordHash = $FolderLevels[2];

                // LETS VALIDATE THE LINK
                $ValidateLink = MysqlQuery("SELECT MemberID FROM customers WHERE ForgotPasswordLink = '".$PasswordHash."' LIMIT 1");
                if(mysqli_num_rows($ValidateLink) == 1)
                {
                    $MemberDetails = mysqli_fetch_assoc($ValidateLink);

                    $RewritePageFound = _ROOT.'/customer-reset-password.php';
                }
        	}
            elseif($FolderURL == '/ajax/customer/add-monogram/')
            {
                $RewritePageFound = _ROOT.'/account/ajax-files/save-monogram.php';
            }
            elseif($FolderURL == '/ajax/customer/additional-details-form/')
            {
                $RewritePageFound = _ROOT.'/account/ajax-files/additional-details-form.php';
            }
            elseif($FolderURL == '/ajax/customer/save-additional-details/')
            {
                $RewritePageFound = _ROOT.'/account/ajax-files/save-additional-details.php';
            }
            elseif($FolderURL == '/ajax/customer/set-default-address/')
            {
                $RewritePageFound = _ROOT.'/account/ajax-files/set-default-address.php';
            }
            elseif($FolderURL == '/ajax/customer/save-address/')
            {
                $RewritePageFound = _ROOT.'/account/ajax-files/save-delivery-address.php';
            }
            elseif($FolderURL == '/ajax/customer/save-review/')
            {
                $RewritePageFound = _ROOT.'/account/ajax-files/save-review.php';
            }
            elseif($FolderURL == '/ajax/customer/change-password/')
            {
                $RewritePageFound = _ROOT.'/account/ajax-files/save-password.php';
            }
            elseif($FolderURL == '/account/addresses/add/')
            {
                $RewritePageFound = _ROOT.'/account/add-address.php';
                $CancelURL = '/account/addresses/';
                $ReturnURL = 'Account';
            }
            elseif($FolderURL == '/account/addresses/edit/')
            {
                $RewritePageFound = _ROOT.'/account/add-address.php';
                $ID = $_GET['id'];
                $CancelURL = '/account/addresses/';
                $ReturnURL = 'Account';
            }
            elseif($FolderURL == '/ajax/customer/save-profile/')
            {
                $RewritePageFound = _ROOT.'/account/ajax-files/save-profile.php';
            }
            elseif($FolderURL == '/ajax/customer/add-measurement/')
            {
                $RewritePageFound = _ROOT.'/account/ajax-files/add-measurement.php';
            }
            elseif($FolderURL == '/ajax/customer/place-order/')
            {
                $RewritePageFound = _ROOT.'/account/ajax-files/place-order.php';
            }
            elseif($FolderLevels[0] == 'account' && $FolderLevels[1] == 'orders' && is_numeric($FolderLevels[2]))
            {
                $RewritePageFound = _ROOT.'/account/orders/order-details.php';
                $ID = $FolderLevels[2];
            }
        }
    }

	if($RewritePageFound != '')
	{
		$_SERVER['PHP_SELF'] = $RewritePageFound;
		header('HTTP/1.0 200 OK');
		include($RewritePageFound);
		exit;
	}
	elseif($RewritePageRedirect != '')
	{
		header('HTTP/1.0 301 Permanently Moved');
		header('Location: '.$RewritePageRedirect);
		exit;
	}
	header('HTTP/1.0 404 Page Not Found');
?>
<!DOCTYPE>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="robots" content="noindex,follow" />
    <title>Error 404 | Sorry the page you are looking for is renamed, moved or deleted!</title>
    <?php include_once(_ROOT._IncludesDir."common-css.php"); ?>
    <style>
        .col-centered{ float: none; margin: 0 auto; padding: 50px 15px 100px 15px; text-align: center; }
    </style>
</head>

<body class="home">
    <div class="container">
        	<div class="row col-centered" style="background: rgba(255, 255, 255, 0.5) !important; margin-top: 10%">
            	<H1 class="lighter">
                	<span class="blue"><i class="fa fa-sitemap"></i> 404</span>
                    	Page Not Found
                </H1>

                <H3 class="lighter">Oops! The page you are looking is either moved or not available</H3>

                <div style="margin-top:60px">
                   <a href="<?=$HomeURL?>" class="btn btn-primary btn-lg"><i class="fa fa-home"></i> &nbsp;Return Home</a>
                </div>
			</div>
		</div>
</body>
</html>