<?php
    $LoggedIn = false;
    $CartBtnLabel = 'Add to Bag';
    $CartURL = '/shopping-bag/';
    $AjaxCartURL = '/ajax/cart-customer-add/';
    $HomeURL = _HOST;
    $ProductsInCart = 0;

    if(!isset($_SESSION['cart']))
	{
		$CartSessionID = md5(time().AlphaNumericCode(4));
		$_SESSION['cart'] = $CartSessionID;
	}
	else
	{
		$CartSessionID = $_SESSION['cart'];
	}

	if(isset($_COOKIE['ssid']))
	{
	    $q = MysqlQuery("SELECT * FROM sales WHERE SID = '".$_COOKIE['ssid']."' AND Status = '1' LIMIT 1");
		if(mysqli_num_rows($q) == 1)
		{
			$MemberDetails = mysqli_fetch_assoc($q);
            $MemberID = $MemberDetails['SalesID'];
            $MemberType = 'Sales';
            $LoggedIn = true;
            $MyAccountURL = '/sales/';
            $ChangePasswordURL = '/sales/change-password/';
            $AjaxCartURL = '/ajax/cart-sales-add/';
            $CartBtnLabel = 'Add Product';
            $HomeURL = '/sales/';

            /*-------------------------------------------------------------------
            UPDATE MEMBER ID IN CLIENT SHOPPING CART TABLE AGAINST LOGGED IN USER
            --------------------------------------------------------------------*/
			MysqlQuery("UPDATE client_shopping_cart SET SessionID = '".$CartSessionID."' WHERE SalesID = '".$MemberID."'");

			if(MysqlAffectedRows == 0)
			{
				MysqlQuery("UPDATE client_shopping_cart SET SalesID = '".$MemberID."' WHERE SessionID = '".$CartSessionID."'");
			}

            $GetCartContent = MysqlQuery("SELECT * FROM client_shopping_cart WHERE SessionID = '".$CartSessionID."'");
            $ProductsInCart = mysqli_num_rows($GetCartContent);
            if($ProductsInCart > 0)
            {
                $CartContent = MysqlFetchAll($GetCartContent);
            }
		}
	}
    elseif(isset($_COOKIE['sid']))
	{
	    $q = MysqlQuery("SELECT * FROM customers WHERE SID = '".$_COOKIE['sid']."' AND Status = '1' LIMIT 1");
		if(mysqli_num_rows($q) == 1)
		{
			$MemberDetails = mysqli_fetch_assoc($q);
            $MemberID = $MemberDetails['MemberID'];
            $MemberType = 'Customer';
            $LoggedIn = true;
            $MyAccountURL = '/account/';
            $ChangePasswordURL = '/account/change-password/';

            /*-------------------------------------------------------------------
            UPDATE MEMBER ID IN CLIENT SHOPPING CART TABLE AGAINST LOGGED IN USER
            --------------------------------------------------------------------*/
			MysqlQuery("UPDATE customer_shopping_cart SET SessionID = '".$CartSessionID."' WHERE CustomerID = '".$MemberID."'");

			if(MysqlAffectedRows == 0)
			{
				MysqlQuery("UPDATE customer_shopping_cart SET CustomerID = '".$MemberID."' WHERE SessionID = '".$CartSessionID."'");
			}

            $GetCartContent = MysqlQuery("SELECT * FROM customer_shopping_cart WHERE SessionID = '".$CartSessionID."'");
            $ProductsInCart = mysqli_num_rows($GetCartContent);
            if($ProductsInCart > 0)
            {
                $CartContent = MysqlFetchAll($GetCartContent);
            }
		}
	}
	elseif($_SERVER['REQUEST_URI'] != '/login/' && $_SERVER['REQUEST_URI'] != '/forgot-password/' && strpos($_SERVER['REQUEST_URI'], 'include-files/') === false && strpos($_SERVER['REQUEST_URI'], '/ajax/') === false && strpos($_SERVER['REQUEST_URI'], '/reset-password/') === false)
	{
		$dsfdsfsd = explode('?', $_SERVER['REQUEST_URI']);
		$ext = strrchr(strtolower($dsfdsfsd[0]), '.');
		$AllowedExts = array('.php', '.html', '.htm');
		if($ext === false || in_array($ext, $AllowedExts))
		{
			$_SESSION['ReturnURL'] = $_SERVER['REQUEST_URI'];
		}
	}
?>