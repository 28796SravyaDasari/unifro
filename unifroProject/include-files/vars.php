<?php
    ini_set('display_errors', 0);

    /***********************************************************************************************************************************************
    IMP: $_SERVER['REQUEST_URI'] DOES NOT RETURN EXPECTED VALUE ON IIS WEB SERVER, SO IT'S NECESSARY TO MANIPULATE IT BEFORE FURTHER SCRIPT EXECUTES
    ***********************************************************************************************************************************************/
    if(stripos($_SERVER['REQUEST_URI'], $_SERVER['HTTP_HOST'].':80', 0) === 0)
    {
        $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $_SERVER['HTTP_HOST'].':80', 0) + strlen($_SERVER['HTTP_HOST'].':80'));
    }
    elseif(stripos($_SERVER['REQUEST_URI'], $_SERVER['HTTP_HOST'], 0) === 0)
    {
        $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $_SERVER['HTTP_HOST'], 0) + strlen($_SERVER['HTTP_HOST']));
    }
    /**********************************************************************************************************************************************/

    //CHECK PHP VERSION INSTALLED ON THE SERVER
    $phpver = phpversion();
    if($phpver < 5.3)
    {
        echo '<DIV align="center" style="color:#CC0000;font-family:Arial;padding:10px">Currently PHP version '.$phpver.' is installed on your system and it is not compatible. Please upgrade it to PHP version 5.3 or higher</DIV>';
        exit;
    }
    /*******************************************************************************************************************************/
    @session_start();

    define('_StartTime', microtime(true));

    if(strpos($_SERVER['SERVER_ADDR'], '127.0.0.1', 0) === false)
    {
        $GlobalServerLoad = sys_getloadavg();
        $GlobalServerLoad = $GlobalServerLoad[0];
    }
    else
    {
        $GlobalServerLoad = 0;
    }

    define('_PROTO', 'http://');

    define('_ROOT', substr($_SERVER['DOCUMENT_ROOT'], -1, 1) == '/' ? substr($_SERVER['DOCUMENT_ROOT'], 0, -1) : $_SERVER['DOCUMENT_ROOT']);
    define('_HOST', _PROTO.$_SERVER['SERVER_NAME']);

    define('_SessionCookie', $_COOKIE['PHPSESSID']);

    define('_WebsiteName', 'Unifro');
    define('_WebsiteURL', 'www.unifro.com');

    define('_IncludesDir', '/include-files/');
    define('_AdminIncludesDir', '/admin/include-files/');

    define('_LOGO', '/images/unifro-logo.png');

    define('_AdminEmail', 'admin@unifro.com');
    define('_SupportEmail', 'support@unifro.com');
    define('_SupportPhone', '');

    define('_CategoryPicDir', '/images/category-pics/');
    define('_CategoryPicWidth', '500px');

    define('_ElementIconDir', '/images/element-icons/');
    define('_ElementIconWidth', '100px');

    define('_FabricImageDir', '/images/fabric-swatches/');
    define('_FabricImageThumbDir', '/images/fabric-swatches/thumbs/');
    define('_FabricImageWidth', '1024px');
    define('_FabricImageThumbWidth', '100px');

    define('_StyleIconsDir', '/images/style-icons/');
    define('_StyleIconWidth', '100px');

    define('_SVGDir', '/images/svgs/');

    define('_MonogramDir', '/images/monograms/');
    define('_ButtonsDir', '/images/buttons/');

    define('_ProductNoImage', '/images/no-option.jpg');

    define('_ProductImageDir', '/images/products/');
    define('_ProductImageThumbDir', '/images/products/thumbs/');
    define('_ProductImageWidth', '1000px');
    define('_ProductImageThumbWidth', '300px');

    define('_SampleDesignDir', '/images/sample-uploaded-designs/');
    define('_ClientMeasurementsDir', '/client-measurement-files/');


    define('_BannerDir', '/images/banner/');
    define('_HomepageBannerWidth', '1440');
    define('_HomepageBannerHeight', '648');

    define('_StartServerLoad', $GlobalServerLoad);

    define('_LoginURL', '/login/');
    define('_SalesLoginURL', '/sales-login/');
    
    define('_CustomTaxRate', '18');
    define('_HomeState', 'Maharashtra');
    define('_CompanyName', 'KART4SCHOOL');
    define('_GSTIN', '27AARFK2517P1ZO');
    define('_InvoiceSignature', '/images/signature.png');

    /*--------------------------------------------------------------------------------
        Let's set a constant to define if the request is comming from an app or web
    --------------------------------------------------------------------------------*/
    if (!function_exists('getallheaders'))
    {
        function getallheaders()
        {
           $headers = array ();
           foreach ($_SERVER as $name => $value)
           {
               if (substr($name, 0, 5) == 'HTTP_')
               {
                   $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
               }
           }
           return $headers;
        }
    }

    //  Let's check if the headers has the "php session key", which means the request is from a web browser
    $IfSessionIDExists = _SessionCookie != '' && strpos(implode(',', getallheaders()), '='._SessionCookie, 0) !== false ? true : false;

    if(isset($_POST['api_key']) && $_POST['api_key'] == _AndroidKey)
    {
        define('_RequestSource', 'Android');
        define('_AppVersion', '1.0');    //  Define the android app version here
    }
    elseif(isset($_POST['api_key']) && $_POST['api_key'] == _iOSKey)
    {
        define('_RequestSource', 'iPhone');
        define('_AppVersion', '1.0');    //  Define the iphone app version here
    }
    elseif($IfSessionIDExists)
    {
        define('_RequestSource', 'Web');
    }

    /*----------------------------------------------------
        CC Avenue Variables
    ------------------------------------------------------*/
    $Mode = 'Test';

    $PGMerchantID   = '127543';

    if($Mode == 'Test')
    {
        $working_key='1E61A72F9A1CEFBBD1881C1043B3CD8F';
        $access_code='AVLP74EK53CG81PLGC';
    }
    else
    {
        $working_key='3ABF310AF3FE4ADDEA1091813C117A6C';
        $access_code='AVBM74EK34BJ91MBJB';
    }

    $PGRedirectURL  = _HOST.'/ccavenue/ccavResponseHandler.php';
    $PGCancelURL    = _HOST.'/ccavenue/ccavResponseHandler.php';

    /**********************************************************************************/



    $LogoutURL = '/logout.php';	//	MAKE SURE TO USE THIS VARIABLE FOR THE LOGOUT LINK
    $OwnerEmail = '';
    $DeveloperEmail = 'sanjeev@myzow.com';

    $AllowedImageTypes = array('jpg' => 'jpg', 'jpeg' => 'jpeg', 'png' => 'png');
    $AllowedExcelFiles = array('csv' => 'csv', 'xls' => 'xls', 'xlsx' => 'xlsx');

    $FileSizeLimit = 50000;	//	ALLOWED FILE SIZE FOR FILES TO BE UPLOADED

    $_SESSION['DefaultPerPage'] = 20;	//THIS IS USED IN ADMIN PANEL FOR NO OF RECORDS TO BE DISPLAYED PER PAGE

    $GlobalMinOrderTotal    = '100000';
    $GlobalShippingCost     = '50';

    $MasterStatus = array('0' => 'Inactive', '1' => 'Active');

    $FabricWashIcons = array(
                                '1' => array('Title' => 'Use Iron', 'ImagePath' => '/images/iron.png'),
                                '2' => array('Title' => 'Do not use Washing Machine', 'ImagePath' => '/images/no-machine.png'),
                                '3' => array('Title' => 'Do not use', 'ImagePath' => '/images/triangle-cross.png'),
                                '4' => array('Title' => 'Washing Temperature', 'ImagePath' => '/images/washing-temperature.png'),
                            );

    $MasterFileTypeIcons = array('doc' => 'fa fa-file-word-o', 'docx' => 'fa fa-file-word-o', 'xls' => 'fa fa-file-excel-o', 'xlsx' => 'fa fa-file-excel-o', 'ppt' => 'fa fa-file-powerpoint-o', 'pdf' => 'fa fa-file-pdf-o', 'txt' => 'fa fa-file-text-o');

    $MasterMemberTypes = array(
                                '1' => array('Label' => 'Sales', 'LoginURL' => '/sales-login/', 'MyAccountURL' => '/sales/'),
                                '2' => array('Label' => 'Customer', 'LoginURL' => '/login/', 'MyAccountURL' => '/account/'),
                              );

    $MasterOrderStatus = array(
                                '1' => 'Awaiting Payment',  // customer has completed the checkout process, but payment has yet to be confirmed
                                '2' => 'Awaiting Fulfillment',  // customer has completed the checkout process and payment has been confirmed
                                '3' => 'Awaiting Shipment', // order has been pulled and packaged and is awaiting collection from a shipping provider
                                '4' => 'Awaiting Pickup',   // order has been packaged and is awaiting customer pickup from a seller-specified location
                                '5' => 'Partially Shipped', // only some items in the order have been shipped
                                '6' => 'Shipped', // order has been shipped, but receipt has not been confirmed
                                '7' => 'Completed', // order has been shipped and receipt is confirmed
                                '8' => 'Cancelled',
                                '9' => 'Declined',  // seller has marked the order as declined for lack of manual payment, or other reasons
                                '10' => 'Refunded',
                              );


    /*********************************************************** COMMON HEADER & FOOTER FOR EMAIL *******************************************************************************/

    $Master_Mail_Header = '<table align="center" width="600" border="0" cellspacing="0" cellpadding="0" style="font-family: Tahoma, Arial, Helvetica, sans-serif"><tbody><tr><td height="25px">&nbsp;</td></tr><tr><td valign="bottom" style="border:1px solid #cccccc;border-bottom:none"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tbody><tr><td valign="top" style="border-bottom:4px solid #ADCDEC;padding:15px 0 15px 20px"><a href="'._HOST.'" target="_blank"><img src="'._HOST._LOGO.'" alt="SafeStructure Logo" /></a></td></tr></tbody></table></td></tr><tr><td style="border-bottom:1px solid #cccccc;border-left:1px solid #cccccc;border-right:1px solid #cccccc"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td style="padding:20px" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0" style="color:#454545;font-size:14px; line-height: 1.5">';

    $Master_Mail_Footer = '<tr><td style="padding-top:45px;">Warm Regards,<br><a href="http://www.safestructure.in" target="_blank" style="color:#006cb5;font-weight:bold;text-decoration:none">Team SafeStructure</a></td></tr></table></td></tr></table></td></tr><tr><td valign="bottom" style="border:1px solid #cccccc;border-top:none"><table width="100%" border="0" bgcolor="#f4f4f4" cellspacing="0" cellpadding="0"><tbody><tr><td valign="top" style="color:#777777; font-size:11px; padding:10px 0 10px 20px">This is a system generated email. Kindly do not reply to this email. You can email us at '._SupportEmail.'</td></tr></tbody></table></td></tr></tbody></table>';

    /**********************************************************************************************************************************************/

?>