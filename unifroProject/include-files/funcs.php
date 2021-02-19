<?php
	require _ROOT.'/phpmailer/class.phpmailer.php';

	/*****************************************************************************
	IMP : NO OTHER FILE SHOULD BE INCLUDED IN THIS FILE
	******************************************************************************/

    function MysqlQuery($query)
    {
        global $DBCon;
        $res = mysqli_query($DBCon, $query);
        if(mysqli_error($DBCon) != '')
        {
            $res = mysqli_error($DBCon);
        }
        return $res;
    }

    function MysqlFetchAll($resource)
    {
        for(;$row = mysqli_fetch_assoc($resource);)
        {
            $array[] = $row;
        }
        return $array;
    }

    function MysqlAffectedRows()
    {
        global $DBCon;
        $AffectedRows = mysqli_affected_rows($DBCon);
        return $AffectedRows;
    }

    function MysqlInsertID()
    {
        global $DBCon;
        $InsertID = mysqli_insert_id($DBCon);
        return $InsertID;
    }

    /******************************************************/

    function JsonPrettyPrint($str)
    {
        if(is_array($str))
        {
            // Do nothing
        }
        else
        {
            $str = json_decode($str);
        }
        printf("<pre>%s</pre>", json_encode($str, JSON_PRETTY_PRINT));
    }

    function ClearFilter()
    {
        return '<button type="button" class="btn btn-white btn-xs" onclick="window.location = \''.strtok($_SERVER['REQUEST_URI'],'?').'\'"><i class="fa fa-refresh"></i> &nbsp;Clear</button>';
    }

    function LocalHost()
    {
        if(strpos($_SERVER['SERVER_ADDR'], '127.0.0.1', 0) !== false)
            return true;
        else
            return false;
    }

    function GetTableFields($dbName, $TableName, $Separator = ', ')
    {
        /*-------------------------------------------------------------------------------------------------
        FUNCTION WILL RETURN ALL FIELD NAMES WITH DEFAULT COMMA SEPARATED. MAINLY USEFUL FOR INSERT QUERIES
        -------------------------------------------------------------------------------------------------*/

        $query = "SELECT group_concat(column_name order by ordinal_position SEPARATOR '".$Separator."') AS FieldNames from information_schema.columns
                            WHERE table_schema = '".$dbName."' and table_name = '".$TableName."'";
        return mysqli_fetch_assoc(MysqlQuery($query))['FieldNames'];
    }

    function GetMemberDetails($MemberID, $AllInfo = false)
    {
        if($AllInfo)
        {
            $MemberDetails = MysqlQuery("SELECT * FROM members WHERE MemberID = '".$MemberID."' LIMIT 1");
            if(mysqli_num_rows($MemberDetails) == 1)
            {
                return mysqli_fetch_assoc($MemberDetails);
            }
            else
            {
                return false;
            }
        }
        else
        {
            if(mysqli_num_rows(MysqlQuery("SELECT MemberID FROM members WHERE MemberID = '".$MemberID."' LIMIT 1")))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    function ValidateEmail($emails)
    {
        if(is_array($emails))
        {
            $emails = array_map('trim', array_filter($emails));
            if(count($emails) == 0)
            {
                return false;
            }
            else
            {
    			foreach($emails as $email)
    			{
    				if(!preg_match("/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $email))
    				{
    					return false;
    				}
    			}
            }
        }
        else
        {
            if(!preg_match("/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $emails))
                return false;
        }

        return true;
    }

    function isEmailRegistered($email, $MemberType, $id = 0)
    {
        //  Pass the third param to skip the record. Useful when editing an existing record of the account
        global $MasterMemberTypes;

        $MemberType = array_search($MemberType, $MasterMemberTypes);

        if(isset($MasterMemberTypes[$MemberType]))
        {
            if(mysqli_num_rows(MysqlQuery("SELECT MemberID FROM members WHERE MemberType = '".$MemberType."' AND EmailID = '".$email."'".($id > 0 ? " AND MemberID <> '".$id."'" : "")." LIMIT 1")) == 1)
            {
                return true;
            }
            else
            {
                //  Return false to denote that the email id is "not registered" to allow creation of a new account
                return false;
            }
        }
        else
        {
            //  For sake of halting the script in case of invalid account type passed, return true
            //  If false is return here, it'll allow the script to assume that email id is not registered and proceed with creation of new account
            return true;
        }
    }

    function JSONEncode($content)
    {
        if(is_array($content))
        {
            return addslashes(json_encode(array_map('DeSanitizeVar',$content)));
        }
        else
            return addslashes(json_encode(DeSanitizeVar($content)));
    }

    function SanitizePOST($var)
    {
        /*******************************************
        DEPENDENT FUNCTION(s)
        CleanText
        *******************************************/
        if(is_array($var))
        {
            return array_map('SanitizePOST', $var);
        }
        else
        {
            return addslashes(CleanText($var));
        }
    }

    function cURLRequest($request_url, $methodPOST = false)
    {
        if($methodPOST)
        {
            $url_string = explode('?', $request_url);
            $request_url = $url_string[0];
            $params = $url_string[1];

            $ch = curl_init($request_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	// to stop verifying the SSL Certificate
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'Content-Length: '.strlen($params)));
            $response = curl_exec($ch);
            curl_close($ch);
        }
        else
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request_url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	// to stop verifying the SSL Certificate
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }
        return $response;
    }

    function BulkInsert($TargetTable, $fields, $ValuesToInsert, $MaxInserts = 5000)
    {
        //  Return no. of successful inserts or the error
        $FieldCount = count(explode(',', $fields));
        if($FieldCount == 0)
        {
            return 'No fields found!';
        }
        elseif($FieldCount != count(array_filter(explode(',', $fields))))
        {
            return 'Invalid field found!';
        }
        //  Let's start builing the query
        $InsertQuery = "INSERT INTO ".$TargetTable." (".$fields.") VALUES ";

        foreach($ValuesToInsert as $values)
        {
            $InsertValues[] = "(".$values.")";
        }

        $InsertResult = false;
        while(count($InsertValues) > 0)
        {
            $ValuesToInsert = array_splice($InsertValues, 0, $MaxInserts);
            $QueryResult = MysqlQuery($InsertQuery.implode(',', $ValuesToInsert));
            $NoOfInserts = MysqlAffectedRows();
            if($NoOfInserts > 0)
            {
                $InsertResult = true;
                $TotalInserts = $TotalInserts + $NoOfInserts;
            }
            else
            {
                $InsertResult = false;
                break;  //  Let's stop further inserts on error
            }
        }
        return $InsertResult ? $TotalInserts : $QueryResult;    //  Return the no of rows affected or the mysql error in case of failure
    }

    function CleanText($s)
    {
        for(; strpos($s,"  ",0) !== false;)
        {
            $s = str_replace("  "," ",$s);
        }
        $s = trim($s);
        return $s;
    }

    function SanitizeVar($var)
    {
        if(is_array($var))
        {
            return array_map('SanitizeVar', $var);
        }
        else
        {
            return addslashes(CleanText($var));
        }
    }

    function DeSanitizeVar($var)
    {
        if(is_array($var))
        {
            return array_map('DeSanitizeVar', $var);
        }
        else
        {
            return stripslashes(htmlentities($var));
        }
    }

    function CheckBrute($id) //THIS IS USED TO CHECK NUMBER OF LOGIN ATTEMPTS IN THE LAST TWO HOURS
    {
        // All login attempts are counted from the past 2 hours.
        $valid_attempts = time() - (2 * 60 * 60);
        $q = MysqlQuery("SELECT TimeStamp FROM login_attempts WHERE LoginID = '".$id."' AND TimeStamp > '".$valid_attempts."'");
        // If there have been more than 3 failed logins
        if(mysqli_num_rows($q) > 2)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function LoginAttempts($LoginID)
    {
        $q = MysqlQuery("INSERT INTO login_attempts(LoginID, IP, TimeStamp) VALUES ('".$LoginID."', '".GetIP()."', '".time()."')");
    }

    function AlphaNumericCode($l, $alpha = false)
    {
        $AlphaNumbers = $alpha ? array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","P","Q","R","S","T","U","V","W","X","Y","Z") : array("1","2","3","4","5","6","7","8","9","A","B","C","D","E","F","G","H","I","J","K","L","M","N","P","Q","R","S","T","U","V","W","X","Y","Z","1","2","3","4","5","6","7","8","9");
        for($code = '', $c = 1; $c <= $l; $c++)
        {
            $code .= $AlphaNumbers[mt_rand(0, count($AlphaNumbers) - 1)];
        }
        return $code;
    }

        function GenerateOTP($l, $alpha = false)
    {
        $AlphaNumbers = $alpha ? array("1","2","3","4","5","6","7","8","9","A","B","C","D","E","F","G","H","I","J","K","L","M","N","P","Q","R","S","T","U","V","W","X","Y","Z","1","2","3","4","5","6","7","8","9") : array("0","1","2","3","4","5","6","7","8","9");
        for($c = 1; $c <= $l; $c++)
        {
            $code .= $AlphaNumbers[rand(0,count($AlphaNumbers)-1)];
        }
        return $code;
    }

    function GetBrowser()
    {
        return substr($_SERVER['HTTP_USER_AGENT'], 0, 250);
    }

    //	FUNCTION TO DISPLAY DATE AND TIME
    function FormatDateTime($format, $t = '')
    {
        if($t == '')
        {
            $t = time();
        }
        elseif($t == 0)
        {
            return 'NA';
        }
        if($format == "dm")		//	for displaying only day and month
            return date("jS M", $t);
        elseif($format == "d")
            return date("jS M Y", $t);
        elseif($format == "dayd")
            return date("l, jS M Y", $t);
        elseif($format == "dt")
            return date("jS M Y, g:ia", $t);
        elseif($format == "dts")
            return date("jS M Y, g:i:sa", $t);
        elseif($format == "dtz")
            return date("jS M Y, g:ia T", $t);
        elseif($format == "t")
            return date("g:ia", $t);
    }

    function SpamProtect($str)
    {
        //	DEPENDENCY ON 'AlphaNumericCode' FUNCTION AND CSS CLASS 'hidden'
        $str = str_split($str);
        for($c = 0; $c < count($str); $c++)
        {
            $str[$c] = $str[$c].'<SPAN class="hidden">'.AlphaNumericCode(2).'</SPAN>';
        }
        return implode('', $str);
    }

    function GetIP()
    {
        return substr($_SERVER['REMOTE_ADDR'], 0, 15);
    }

    function ValidatePassword($str)
    {
        if(strpos($str, ' ', 0) !== false || CleanText($str) == '')
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    function ValidateUserID($str)
    {
        $strArray = str_split(strtolower($str));
        $count = count($strArray);
        if($count < 3)
        {
            return false;
        }
        else
        {
            if(preg_match('/^[a-z\d_.]{3,20}$/i', $str))
            {
                return true;
            }
            else
                return false;
        }
    }
    function GenerateHash($rounds = 7)
    {
            $salt = "";
            $salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9));
            for($i=0; $i < 22; $i++) {
              $salt .= $salt_chars[array_rand($salt_chars)];
            }
            return crypt(microtime(), sprintf('2a$%02d', $rounds) . $salt);
    }

    function GenerateFormToken($form)
    {
        if(isset($_POST['token']))
        {
            return $_POST['token'];
        }
        else
        {
            $token = md5(GenerateHash(microtime()));
                    	$_SESSION[$form.'_Token'] = $token;
                    	return $token;
        }
    }

    function VerifyFormToken($form)
    {
        // CHECK IF THE FORM IS SUBMITTED WITH THE TOKEN
        if(!isset($_POST['token']))
        {
            return false;
        }
        // CHECK IF FORM TOKEN SESSION IS SET
        if(!isset($_SESSION[$form.'_Token']))
        {
            return false;
        }
        // COMPARE THE TOKENS
        if($_SESSION[$form.'_Token'] !== $_POST['token'])
        {
            return false;
        }
        return true;
    }

    function GenerateHackLog($where)
    {
        $ip = $_SERVER["REMOTE_ADDR"];
        $host = gethostbyaddr($ip);
        $date = date("d M Y");

        $q = "INSERT INTO hack_logs(AttackDate, AttackPage, IP, HostName) VALUES ('".time()."', '".$where."', '".$ip."', '".$host."')";
        MysqlQuery($q);
    }

    //	FUNCTION TO CHECK IF SPECIAL CHARACTERS EXIST IN STRING OR ALLOWS SPECIFIC SPECIAL CHARACTERS | $allowed = 0 means no special characters allowed at all
    function NoSpecialCharacters($string, $allowed = 0)
    {
        if(trim($string) == "")
        {
            return false;
        }
        else
        {
            $AlphaNumbers = array("1","2","3","4","5","6","7","8","9","0","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"," ");
            if($allowed == 0)
            {
                if(strlen(str_replace($AlphaNumbers,"",$string)) > 0)
                    return false;
                else
                    return true;
            }
            elseif(is_array($allowed))
            {
                $AlphaNumbers = array_merge($AlphaNumbers,$allowed);
                if(strlen(str_replace($AlphaNumbers,"",$string)) > 0)
                    return false;
                else
                    return true;
            }
            else
                return false;
        }
    }

    function FormatForURL($link)
    {
        return urlencode(strtolower($link));
    }

    function SendMailHTML($ToEmail, $subject, $body, $AttachmentPath = '', $ReplyToEmail = 'admin@unifro.com', $ReplyToName = 'Unifro.com')
    {
        if($ReplyToEmail == '')
        {
            global $AdminEmail;
            $ReplyToEmail = $AdminEmail;
        }

        $AttachmentPath = array_filter(explode(',', $AttachmentPath));
        $ToEmail = array_filter(explode(',', $ToEmail));

        //Create a new PHPMailer instance
        $mail = new PHPMailer();
        //Tell PHPMailer to use SMTP
        $mail->IsSMTP();
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug  = 0;
        //Ask for HTML-friendly debug output
        $mail->Debugoutput = 'html';
        //Set the hostname of the mail server
        $mail->Host       = "smtp.gmail.com";
        //Set the SMTP port number - likely to be 25, 465 or 587
        $mail->Port       = 465;
        //Set the protocol for sending request
        $mail->SMTPSecure = "ssl";
        //Whether to use SMTP authentication
        $mail->SMTPAuth   = true;
        //Username to use for SMTP authentication
        $mail->Username   = "noreply@unifro.com";
        //Password to use for SMTP authentication
        $mail->Password   = "star123456";
        //Set who the message is to be sent from
        $mail->SetFrom('noreply@unifro.com', 'Unifro');
        //Set an alternative reply-to address
        $mail->AddReplyTo($ReplyToEmail, $ReplyToName);


        //Set who the message is to be sent to
        for($MailCounter = 0; $MailCounter < count($ToEmail); $MailCounter++)
        {
            $mail->AddAddress($ToEmail[$MailCounter], '');
        }

        //	LETS ADD ATTACHMENTS
        for($AttachmentCounter = 0; $AttachmentCounter < count($AttachmentPath); $AttachmentCounter++)
        {
            $mail->AddAttachment($AttachmentPath[$AttachmentCounter]);
        }

        //Set the subject line
        $mail->Subject = $subject;
        //Read an HTML message body from an external file, convert referenced images to embedded, convert HTML into a basic plain-text alternative body
        $mail->MsgHTML($body);
        //Replace the plain text body with one created manually
        $mail->AltBody = '';

        //Send the message, check for errors
        if(!$mail->Send())
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    function GetParams($drop = '', $add = '', $glue = false, $url = '')
    {
        //	ASSIGN THE "REQUEST_URI" AS URL IF NO URL IS PASSED
        $url = $url != '' ? $url : $_SERVER['REQUEST_URI'];

        if($drop != '')
        {
            $drop = explode(',', $drop);
        }
        $baseUrl = explode('?', $url);
        $params = $baseUrl[1];
        $baseUrl = $baseUrl[0];

        $params = explode('&', $params);
        $ParamArray = array();
        for($c = 0; $c < count($params); $c++)
        {
            $value = explode('=', $params[$c]);
            if($value[1] != '' && @!in_array($value[0], $drop))
            {
                $ParamArray[] = $value[0].'='.$value[1];
            }
        }
        $params = implode('&', $ParamArray);
        $params = $params != '' ? $params : '';
        $params = $params != '' && $add != '' ? $params.'&'.$add : ($add != '' ? $add : $params);

        if($params != '')
        {
            return $glue ? '?'.$params : $params;
        }
    }

    function CreateThumbnail($ImagePath, $ThumbDir, $MaxAllowedWidth = 200, $WatermarkPath = '')
    {
        $file = strrchr($ImagePath, '/');

        list($currwidth, $currheight, $type, $attr) = getimagesize($ImagePath);
        if($currwidth > $MaxAllowedWidth)
        {
            $ImageRatio = $currheight/$currwidth;
            $NewHeight = round($MaxAllowedWidth*$ImageRatio,0);
            $MaxAllowedWidth = $MaxAllowedWidth;
            $NewHeight = $NewHeight;
        }
        else
        {
            $NewHeight = $currheight;
            $MaxAllowedWidth = $currwidth;
        }

        if($WatermarkPath != '')
        {
            $watermark = imagecreatefrompng($WatermarkPath);
        }

        // Set the margins for the stamp and get the height/width of the watermark image
        $marge_right = 10;
        $marge_bottom = 10;
        $sx = imagesx($watermark);
        $sy = imagesy($watermark);

        if(strrchr($file,".") == ".jpg" || strrchr($file,".") == ".JPG" || strrchr($file,".") == ".jpeg" || strrchr($file,".") == ".JPEG")
        {
            $simg = @imagecreatefromjpeg($ImagePath);   // Make A New Temporary Image To Create The Thumbanil From
            if($WatermarkPath != '')
                imagecopy($simg, $watermark, imagesx($simg) - $sx - $marge_right, imagesy($simg) - $sy - $marge_bottom, 0, 0, imagesx($watermark), imagesy($watermark));
            $dimg = imagecreatetruecolor($MaxAllowedWidth, $NewHeight);   // Make New Image For Thumbnail
            @imagecopyresampled($dimg, $simg, 0, 0, 0, 0, $MaxAllowedWidth, $NewHeight, $currwidth, $currheight);

            if(imagejpeg($dimg, $ThumbDir.$file, 85))   // Saving The Image
                $counter++;
            @imagedestroy($simg);   // Destroying The Temporary Image
            @imagedestroy($dimg);   // Destroying The Other Temporary Image
        }
        if(strrchr($file,".") == ".png" || strrchr($file,".") == ".PNG")
        {
            $simg = imagecreatefrompng($ImagePath);   // Make A New Temporary Image To Create The Thumbanil From
            if($WatermarkPath != '')
                imagecopy($simg, $watermark, imagesx($simg) - $sx - $marge_right, imagesy($simg) - $sy - $marge_bottom, 0, 0, imagesx($watermark), imagesy($watermark));
            $dimg = imagecreatetruecolor($MaxAllowedWidth, $NewHeight);   // Make New Image For Thumbnail

            imagealphablending($dimg, false);
            $colorTransparent = imagecolorallocatealpha($dimg, 0, 0, 0, 127);
            imagefill($dimg, 0, 0, $colorTransparent);
            imagesavealpha($dimg, true);

            imagecopyresampled($dimg, $simg, 0, 0, 0, 0, $MaxAllowedWidth, $NewHeight, $currwidth, $currheight);

            if(imagepng($dimg, $ThumbDir.$file, 5))   // Saving The Image
                $counter++;
            imagedestroy($simg);   // Destroying The Temporary Image
            imagedestroy($dimg);   // Destroying The Other Temporary Image
        }
        if(strrchr($file,".") == ".gif" || strrchr($file,".") == ".GIF")
        {
            $simg = imagecreatefromgif($ImagePath);   // Make A New Temporary Image To Create The Thumbanil From
            if($WatermarkPath != '')
                imagecopy($simg, $watermark, imagesx($simg) - $sx - $marge_right, imagesy($simg) - $sy - $marge_bottom, 0, 0, imagesx($watermark), imagesy($watermark));
            $dimg = imagecreatetruecolor($MaxAllowedWidth, $NewHeight);   // Make New Image For Thumbnail
            imagecopyresampled($dimg, $simg, 0, 0, 0, 0, $MaxAllowedWidth, $NewHeight, $currwidth, $currheight);

            if(imagegif($dimg, $ThumbDir.$file))   // Saving The Image
                $counter++;
            imagedestroy($simg);   // Destroying The Temporary Image
            imagedestroy($dimg);   // Destroying The Other Temporary Image
        }
        if(file_exists($ThumbDir.$file))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function CreateNavs($NoOfRecords, $PerPage, $NavName, $NoOfNavs = 5, $message = '', $ShowTotalPages = true, $link = '', $hash = '')
    {
        /************ FIRST FIND THE FORMAT THE URL TO BE LINKED FOR NAVS ***********/
        if($link == '')
        {
            $url = $_SERVER['REQUEST_URI'];

            $baseUrl = explode('?', $url);
            $params = $baseUrl[1];
            $baseUrl = $baseUrl[0];

            $params = explode('&', $params);
            $ParamArray = array();
            for($c = 0; $c < count($params); $c++)
            {
                $value = explode('=', $params[$c]);
                if($value[1] != '' && strpos($value[0], $NavName, 0) === false)
                {
                    $ParamArray[] = $value[0].'='.$value[1];
                }
            }
            $params = implode('&', $ParamArray);
            if($params != '')
            {
                $link = $baseUrl.'?'.$params;
            }
            else
            {
                $link = $baseUrl;
            }
        }
        $link = strpos($link, '?', 0)?$link.'&':$link.'?';

        /************ START CREATING NAVS ***********/
        $CurrentPage = !is_numeric($_GET[$NavName])?1:$_GET[$NavName];	//	SET DEFAULT PAGE VALUE AS 1 IF PASSES VALUE IS NON-NUMERIC
        $TotalPages = ceil($NoOfRecords/$PerPage);
        $CurrentPage = $CurrentPage;
        $prev = $CurrentPage - 1;
        $next = $CurrentPage + 1;
        $navs = '';

        if($NoOfRecords > $PerPage)
        {
            $StartNav = $CurrentPage - ceil($NoOfNavs/2) <= 1 ? 2 : $CurrentPage - ceil($NoOfNavs/2);
            if($TotalPages - $StartNav < $NoOfNavs && $TotalPages > $NoOfNavs + 1)
                $StartNav = $StartNav - 1;
            if($CurrentPage == $TotalPages && $TotalPages > $NoOfNavs)
                $StartNav = $CurrentPage - $NoOfNavs == 1 ? 2 : $CurrentPage - $NoOfNavs;

                        $navs .= '<div class="navs-holder">';

            $navs .= '<A class="fixedNav" href="'.$link.$NavName.'=1'.($hash != '' ? '#'.$hash : '').'"><SPAN class="font21" style="color:#1776b5">&laquo;</SPAN><SPAN class="font15" style="color:#1776b5"> First</SPAN></A>';

            if($CurrentPage == 1)
            {
                $navs .= '<SPAN class="navsOn">1</SPAN>';
            }
            else
            {
                $navs .= '<A class="navs" href="'.$link.$NavName.'=1'.($hash != '' ? '#'.$hash : '').'">1</A>';
            }

            for($NavCounter = 1; $NavCounter <= $NoOfNavs && $StartNav < $TotalPages; $NavCounter++, $StartNav++)
            {
                if($StartNav == $CurrentPage)
                {
                    $navs .= '<SPAN class="navsOn">'.$CurrentPage.'</SPAN>';
                }
                else
                {
                    $navs .= '<A class="navs" href="'.$link.'page='.$StartNav.($hash != '' ? '#'.$hash : '').'">'.$StartNav.'</A>';
                }
                //$navs .= '&nbsp;&nbsp;';
            }

            if($CurrentPage == $TotalPages)
            {
                $navs .= '<SPAN class="navsOn">'.$TotalPages.'</SPAN>';
            }
            else
            {
                $navs .= '<A class="navs" href="'.$link.$NavName.'='.$TotalPages.($hash != '' ? '#'.$hash : '').'">'.$TotalPages.'</A>';
            }

            $navs .= '<A class="fixedNavLast" href="'.$link.$NavName.'='.$TotalPages.($hash != '' ? '#'.$hash : '').'"><SPAN class="font15" style="color:#1776b5">Last </SPAN><SPAN class="font21" style="color:#1776b5">&raquo;</SPAN></A>';
                        $navs .= '</div">';
        }
        $navs = $navs != '' ? $navs.'&nbsp;&nbsp;&nbsp;': $navs;
        $message = $message != '' ? $message : 'Total Records ';
        $navs = $NoOfRecords > 0 ? ($ShowTotalPages ? $navs.$message.$NoOfRecords : $navs) : '';
        return $navs;
    }

    function FormatAmount($amount, $decimal = 0, $prefix = true)
    {
        if(is_numeric($amount))
        {
            if($prefix)
            {
                return '<span>Rs. </span>'.number_format($amount, $decimal);
            }
            else
            {
                return number_format($amount, $decimal);
            }
        }
        else
        {
            return $amount;
        }
    }

    function MakeFriendlyURL($str, $prefix = '', $KeepSlash = true)
    {
        $str = $prefix.' '.$str;
        $AlphaNumbers = array("1","2","3","4","5","6","7","8","9","0","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"," ","-",".");
        if($KeepSlash)
        {
            array_unshift($AlphaNumbers, "/");
        }

        $str = str_split(strtolower($str));
        for($c = 0; $c < count($str); $c++)
        {
            if(!in_array($str[$c], $AlphaNumbers))
            {
                $str[$c] = '';
            }
        }
        $str = implode('', $str);
        for(; strpos($str,"  ",0) !== false;)
        {
            $str = str_replace("  "," ",$str);
        }
        $str = trim($str);
        return str_replace(' ', '-', $str);
    }

    function CheckLogin($ReturnURL = '', $LoginURL = '/login/')
    {
        global $LoggedIn, $MasterMemberTypes, $MemberType;

        if(!$LoggedIn)
        {
            if($ReturnURL != '')
            {
                $_SESSION['ReturnURL'] = $ReturnURL;
            }
            else
            {
                $_SESSION['ReturnURL'] = $_SERVER['REQUEST_URI'];
            }

            header('Location: '.($LoginURL != '' ? $LoginURL : '/'));
            exit();
        }
        elseif($_SESSION['ReturnURL'] == $_SERVER['REQUEST_URI'])
        {
            unset($_SESSION['ReturnURL']);
        }

        if(isset($_COOKIE['ssid']))
        {
            if($MemberType != 'Sales')
            {
                header('Location: '.$MasterMemberTypes[1]['MyAccountURL']);
                exit();
            }
        }
        elseif(isset($_COOKIE['sid']))
        {
            if($MemberType != 'Customer')
            {
                header('Location: '.$MasterMemberTypes[2]['MyAccountURL']);
                exit();
            }
        }
    }

    function PerPageOption($PerPage = 0)
    {
        if(isset($_GET['PerPage']))
        {
            $_SESSION['PerPage'][$_SERVER['PHP_SELF']] = $_GET['PerPage'];
        }
        elseif(!isset($_SESSION['PerPage'][$_SERVER['PHP_SELF']]))
        {
            $_SESSION['PerPage'][$_SERVER['PHP_SELF']] = $PerPage > 0 ? $PerPage : $_SESSION['DefaultPerPage'];
        }

        $_SESSION['DefaultPerPage'] = $_SESSION['PerPage'][$_SERVER['PHP_SELF']];

        return '<FORM method="get">
                    <div class="PerpageWidget">
                        <span>Show</span>
                        <SELECT name="PerPage" onChange="this.form.submit()" style="width:65px;">
                            <OPTION'.($_SESSION['PerPage'][$_SERVER['PHP_SELF']] == 10 ? ' selected' : '').' value="10">10</OPTION>
                            <OPTION'.($_SESSION['PerPage'][$_SERVER['PHP_SELF']] == 25 ? ' selected' : '').' value="25">25</OPTION>
                            <OPTION'.($_SESSION['PerPage'][$_SERVER['PHP_SELF']] == 50 ? ' selected' : '').' value="50">50</OPTION>
                            <OPTION'.($_SESSION['PerPage'][$_SERVER['PHP_SELF']] == 100 ? ' selected' : '').' value="100">100</OPTION>
                        </SELECT>
                        <rec>Records</rec>
                    </div>
                </FORM>';

    }

    function GeneratePwd($input, $rounds = 7)
    {
    		$salt = "";
        	$salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9));
        	for($i=0; $i < 22; $i++)
        	{
            		$salt .= $salt_chars[array_rand($salt_chars)];
        	}
        	return crypt($input, sprintf('$2a$%02d$', $rounds) . $salt);
    }

    function VerifyPwd($input, $password_hash)
    {
        	if(crypt($input, $password_hash) == $password_hash)
                		return true;
        	else
                		return false;
    }

    function ThousandsToK($num)
    {
      $x = round($num);
      $x_number_format = number_format($x);
      $x_array = explode(',', $x_number_format);
      $x_parts = array('k', 'm', 'b', 't');
      $x_count_parts = count($x_array) - 1;
      $x_display = $x;
      $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
      $x_display .= $x_parts[$x_count_parts - 1];
      return $x_display;
    }

    function RecordAdminActivity($activity, $TableName = '', $FieldID = 0, $Description = '')
    {
        global $AID;

        $q = MysqlQuery("INSERT INTO activities_admin (AdminID, TableName, FieldID, Activity, Description, Timestamp, Browser, IPAddress) VALUES ('".$AID."', '".$TableName."', '".$FieldID."', '".$activity."', '".$Description."', '".time()."', '".GetBrowser()."', '".GetIP()."')");

        if(MysqlAffectedRows() == 1)
            return true;
        else
            return false;
    }

    function RecordMemberActivity($Activity, $TableName, $FieldID, $Description = '', $MemberID = 0)
    {
        global $MemberID, $MemberDetails, $MasterMemberTypes;

        if(is_numeric($MemberID) && $MemberID > 0)
        {
            $q = MysqlQuery("INSERT INTO activities_customer (MemberID, TableName, FieldID, Activity, Description, Source, Timestamp, Browser, IPAddress)
            VALUES ('".$MemberID."', '".$TableName."', '".$FieldID."', '".$Activity."', '".$Description."', '"._RequestSource."', '".time()."', '".GetBrowser()."', '".GetIP()."')");

            if(MysqlAffectedRows() == 1)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    function RecordSalesActivity($Activity, $TableName, $FieldID, $Description = '', $MemberID = 0)
    {
        global $MemberID, $MemberDetails, $MasterMemberTypes;

        if(is_numeric($MemberID) && $MemberID > 0)
        {
            $q = MysqlQuery("INSERT INTO activities_sales (SalesID, TableName, FieldID, Activity, Description, Source, Timestamp, Browser, IPAddress)
            VALUES ('".$MemberID."', '".$TableName."', '".$FieldID."', '".$Activity."', '".$Description."', '"._RequestSource."', '".time()."', '".GetBrowser()."', '".GetIP()."')");

            if(MysqlAffectedRows() == 1)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    function FormatElapsedTime($timestamp, $ReturnArray = false)
    {
        //	DEPENDENCY FormatDateTime();
        $timestamp = is_numeric($timestamp) ? $timestamp : time();
        $now = time();
                $DiffSeconds = $now - $timestamp;
        if($DiffSeconds < 0)
        {
            return '';
        }
        else
        {
            $years = floor($DiffSeconds / (365*60*60*24));
            $months = floor($DiffSeconds / (30*3600*24));
            $days = floor($DiffSeconds / (3600*24));
            $hours = floor($DiffSeconds / 3600);
            $minutes = floor($DiffSeconds / 60);
            $seconds = $DiffSeconds;

            if($days > 0)
                        {
                                if($ReturnArray)
                                {
                        	    return array('int' => $days, 'suffix' => 'DAY');
                                }
                                else
                                {
                                        return $days.($days > 1 ? ' DAY' : ' DAY');
                                }
                        }
                        elseif($hours > 0)
                        {
                                if($ReturnArray)
                                {
                        	    return array('int' => $hours, 'suffix' => 'HRS');
                                }
                                else
                                {
                                        return $hours.($hours > 1 ? ' HRS' : ' HRS');
                                }
                        }
                        elseif($minutes > 0)
                        {
                                if($ReturnArray)
                                {
                        	    return array('int' => $minutes, 'suffix' => 'MIN');
                                }
                                else
                                {
                                        return $minutes.($minutes > 1 ? ' MIN' : ' MIN');
                                }
                        }
                        else
                        {
                                if($ReturnArray)
                                {
                        	    return array('int' => $seconds, 'suffix' => 'SEC');
                                }
                                else
                                {
                                        return $seconds.($seconds > 1 ? ' SEC' : ' SEC');
                                }

                        }
        }
    }

        function TimeDifference($fromtime, $totime, $ReturnArray = false)
    {
        //	DEPENDENCY FormatDateTime();
        $fromtime = is_numeric($fromtime) ? $fromtime : time();
        $now = $totime;
                $DiffSeconds = $now - $fromtime;
        if($DiffSeconds < 0)
        {
            return '';
        }
        else
        {
            $years = floor($DiffSeconds / (365*60*60*24));
            $months = floor($DiffSeconds / (30*3600*24));
            $days = floor($DiffSeconds / (3600*24));
            $hours = floor($DiffSeconds / 3600);
            $minutes = floor($DiffSeconds / 60);
            $seconds = $DiffSeconds;

            if($days > 0)
                        {
                                if($ReturnArray)
                                {
                        	    return array('int' => $days, 'suffix' => 'DAY');
                                }
                                else
                                {
                                        return $days.($days > 1 ? ' DAY' : ' DAY');
                                }
                        }
                        elseif($hours > 0)
                        {
                                if($ReturnArray)
                                {
                        	    return array('int' => $hours, 'suffix' => 'HRS');
                                }
                                else
                                {
                                        return $hours.($hours > 1 ? ' HRS' : ' HRS');
                                }
                        }
                        elseif($minutes > 0)
                        {
                                if($ReturnArray)
                                {
                        	    return array('int' => $minutes, 'suffix' => 'MIN');
                                }
                                else
                                {
                                        return $minutes.($minutes > 1 ? ' MIN' : ' MIN');
                                }
                        }
                        else
                        {
                                if($ReturnArray)
                                {
                        	    return array('int' => $seconds, 'suffix' => 'SEC');
                                }
                                else
                                {
                                        return $seconds.($seconds > 1 ? ' SEC' : ' SEC');
                                }

                        }
        }
    }

    function convert_number_to_words($number)
    {
            $no = round($number);
            $point = round($number - $no, 2) * 100;
            $hundred = null;
            $digits_1 = strlen($no);
            $i = 0;
            $str = array();
            $words = array('0' => '', '1' => 'One', '2' => 'Two', '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six', '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
            '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve', '13' => 'Thirteen', '14' => 'Fourteen', '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen', '18' => 'Eighteen',
            '19' =>'Nineteen', '20' => 'Twenty', '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty', '60' => 'Sixty', '70' => 'Seventy', '80' => 'Eighty', '90' => 'Ninety');
            $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
            while ($i < $digits_1)
            {
                    $divider = ($i == 2) ? 10 : 100;
                    $number = floor($no % $divider);
                    $no = floor($no / $divider);
                    $i += ($divider == 10) ? 1 : 2;
                    if ($number)
                    {
                            $plural = (($counter = count($str)) && $number > 9) ? '' : null;
                            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                            $str [] = ($number < 21) ? $words[$number] .
                            " " . $digits[$counter] . $plural . " " . $hundred
                            :
                            $words[floor($number / 10) * 10]
                            . " " . $words[$number % 10] . " "
                            . $digits[$counter] . $plural . " " . $hundred;
                    }
                    else $str[] = null;
            }
            $str = array_reverse($str);
            $result = implode('', $str);
            $points = ($point) ?
            "." . $words[$point / 10] . " " .
                        $words[$point = $point % 10] : '';
            return $result . "Rupees  " . ($points != '' ? $points . " Paise" : '');
    }

    function hasPermission($page, $redirect = true)
    {
        global $PermissionIDs;

        if($PermissionIDs == '' OR is_null($PermissionIDs))
        {
            return true;
        }
        else
        {
            if(isset($PermissionIDs[$page]))
            {
                return true;
            }
            else
            {
                if($redirect)
                {
                    $_SESSION['AlertMessage'] = 'You do not have permissions to access the page.';
                    header('Location: /admin/dashboard/');
                    exit;
                }
                else
                    return false;
            }
        }
    }

    function GenerateCaptcha($grids, $text = 'Click the [color] box')
    {
        $CaptchaColors = array('Gray' => '#CCCCCC', 'Blue' => '#5D9CEC', 'Red' => '#e40000', 'Green' => '#1b9c5a', 'Yellow' => '#FFEB3B', 'Black' => '#0c0702', 'Pink' => '#EC87C0', 'Orange' => '#ff7f00', 'Brown' => '#795548');
        $OtherColors = array('Purple' => '#875F9A', 'Ibis' => '#F58F84', 'Wisteria' => '#BE90D4', 'Indigo' => '#264348', 'Hanada' => '#044F67');

        $min = 1 * mt_rand(1, 100000);
        $max = $min + $grids - 1;
        $number = mt_rand($min, $max);
        $_SESSION['Captcha'] = $number;
        $randColorKey = array_rand($CaptchaColors);
        $randColor = $CaptchaColors[$randColorKey];
        unset($CaptchaColors[$randColorKey]);
        $CaptchaColors = array_merge($CaptchaColors,$OtherColors);

        //  Now let's convert the text to image
        $text = str_replace('[color]', $randColorKey, $text);
        $CanvasWidth = strlen($text) * 7;
        $im = imagecreatetruecolor($CanvasWidth, 18);

        // Create some colors
        $white = imagecolorallocate($im, 255, 255, 255);
        $grey = imagecolorallocate($im, 128, 128, 128);
        $black = imagecolorallocate($im, 40, 40, 40);
        imagefilledrectangle($im, 0, 0, $CanvasWidth, 18, $white);

        // Replace path by your own font path
        $font = realpath(_ROOT).'/captcha/arial.ttf';

        // Add the text
        imagettftext($im, 10.5, 0, 0, 13, $black, $font, $text);

        // Using imagepng() results in clearer text compared with imagejpeg()
        $captchaImagePath = _ROOT.'/captcha/captcha-image.png';
        imagepng($im, $captchaImagePath);
        imagedestroy($im);

        $type = pathinfo($captchaImagePath, PATHINFO_EXTENSION);
        $data = file_get_contents($captchaImagePath);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        //unlink($captchaImagePath);

        $captcha = '<div class="myCaptcha">';
                $captcha .=  '<div class="text"><IMG src="'.$base64.'"></div>';
                $captcha .=  '<div class="captcha-container">';

        for($r = 0, $n = $min; $r < $grids; $r++)
        {
            $style = ' style="background-color:'.($n == $number ? $randColor : $CaptchaColors[array_rand($CaptchaColors)]).'"';
            $captcha .= '<div class="captcha-div">
                            <button class="captcha-btn" data-value="'.$n.'"'.$style.' type="button"></button>
                         </div>';
            $n++;
        }
                $captcha .= '</div>';
        $captcha .= '</div>';

        return $captcha;
    }

    function FormatEmail($content)
    {
            $EmailMessage = '<html><head><link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet"><style>table td {border-collapse:collapse;} body{ font-family: "Open Sans", Helvetica, Arial, sans-serif; color: #333333; } ul{list-style: none; margin: 0; padding: 0;} .thumbnail{display: inline-block; height: 80px; width: 80px; background-size: cover !important; background-repeat: no-repeat !important; margin-bottom: 0; overflow: hidden; position: relative; vertical-align: middle;}</style></head><body><table align="center" width="600" border="0" cellspacing="0" cellpadding="0" style="font-family: "Open Sans", Helvetica, Arial, sans-serif;"><tbody><tr><td height="25px">&nbsp;</td></tr><tr><td style="border:1px solid #cccccc;" valign="bottom"><table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff"><tbody><tr><td valign="top" style="background-color: #01509D;padding:20px 0 15px 20px;"><a href="'._HOST.'" target="_blank"><img src="'._HOST._LOGO.'" alt="'._WebsiteName.' Logo" width="200px" /></a></td></tr></tbody></table></td></tr><tr><td style="border-bottom:1px solid #cccccc;border-left:1px solid #cccccc;border-right:1px solid #cccccc"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td style="padding:20px" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size:14px; line-height: 1.5">';

    	$EmailMessage .= $content;

            $EmailMessage .= '<tr><td style="padding-top:45px;">Warm Regards,<br><a href="'._HOST.'" target="_blank" style="color:#137fc3;text-decoration:none">Team '._WebsiteName.'</a></td></tr></table></td></tr></table></td></tr><tr><td valign="bottom" style="border:1px solid #cccccc;border-top:none"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tbody><tr><td valign="top" style="color:#777777; font-size:11px; padding:10px 0 10px 20px">This is a system generated email. Kindly do not reply to this email. You can email us at <a href="mailto:@'._SupportEmail.'" target="_top">'._SupportEmail.'</td></tr></tbody></table></td></tr></tbody></table></body></html>';

            return $EmailMessage;
    }

    function ProcessingTime()
    {
        return number_format(microtime(true) - _StartTime, 2);
    }

    function SortArray($MasterArray, $SortKeys, $direction = 'a')
    {
        $SortKeys = explode(',', $SortKeys);
        /******************************************************************************
        SORTS A MULTI-DEMENSIONAL ARRAY ON BASIS OF 'KEY' PASSES. USE THIRD PARAM
        AS a or d TO DENOTE 'ASC' OR 'DESC'
        ******************************************************************************/
        //	LETS COLLECT ALL VALUES OF 'SORT KEY'
        $SortKeyValues = array();
        foreach($MasterArray as $key => $value)
        {
            $ValueForSort = array();
            foreach($SortKeys as $SortKey)
            {
                $ValueForSort[] = $value[$SortKey];
            }
            $SortKeyValues[$key] = implode('', $ValueForSort);
        }

        if($direction == 'a')
        {
            natcasesort($SortKeyValues);
        }
        else
        {
            natcasesort($SortKeyValues);
            $SortKeyValues = array_reverse($SortKeyValues, true);
        }

        $OutputArray = array();
        foreach($SortKeyValues as $key => $value)
        {
            $OutputArray[$key] = $MasterArray[$key];
        }
        return $OutputArray;
    }

    function LogServerLoad($ServerLoadBase = 0.25, $ProcessingTimeBase = 0, $params = array())
    {
        if(!empty($params))
        {
            $URLparams = array();
            foreach($params as $key => $val)
            {
                $URLparams[] = $key.'='.$val;
            }
            $URLparams = implode('&', $URLparams);
        }
        else
        {
            $URLparams = urldecode($_SERVER['QUERY_STRING']);
        }

        if(strpos($_SERVER['SERVER_ADDR'], '127.0.0.1', 0) === false)
        {
            $EndServerLoad = sys_getloadavg();
            $EndServerLoad = $EndServerLoad[0];
        }
        else
        {
            $EndServerLoad = 0;
        }

        $ProcessingServerLoad = $EndServerLoad - _StartServerLoad;
        $ProcessingTime = number_format(microtime(true) - _StartTime, 2);
        if($ProcessingServerLoad > $ServerLoadBase || ($ProcessingTimeBase > 0 && $ProcessingTime > $ProcessingTimeBase))
        {
            $script = $_SERVER['PHP_SELF'];
            MysqlQuery("INSERT INTO slow_scripts (Script, ProcessingServerLoad, ServerLoad, ProcessingTime, URLParams, Added)
            VALUES ('".$script."', '".$ProcessingServerLoad."', '".$EndServerLoad."', '".$ProcessingTime."', '".addslashes($URLparams)."', '".time()."')");
        }
    }

    function SetReturnURL()
    {
        $_SESSION['AdminReturnURL'] = $_SERVER['REQUEST_URI'];
    }

    function GoToLastPage()
    {
        return $_SESSION['AdminReturnURL'];
    }

    function HeaderMenuOld($ParentID, $Category, $ActivePage = '', $dropdown = false, $ShowIcon = false)
    {
        $html = '';

        if(isset($Category['List'][$ParentID]))
        {
            if($dropdown)
                $html = '<ul class="dropdown-menu">';
            else
                $html = '<ul class="nav navbar-nav">';

            foreach($Category['List'][$ParentID] as $ChildID)
            {
                if($ShowIcon)
                {
                    // Show Icon only for first Parent Category
                    $Icon = $Category['Data'][$ChildID]['ParentID'] == 0 ? '<i class="'.$Category['Data'][$ChildID]['Icon'].'"></i> ' : '';
                }

                // Set the class for li
                if($ParentID == 0 && isset($Category['List'][$ChildID]))
                {
                    $li_class = ' class="dropdown"';
                }
                else if(isset($Category['List'][$ChildID]))
                {
                    $li_class = ' class="dropdown"';
                }
                else
                {
                    $li_class = '';
                }

                // If sub-category exists then show dropdown arrow against the parent category
                if( strpos($li_class, 'dropdown') !== false)
                {
                    $html .= '<li'.$li_class.'><a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$Category['Data'][$ChildID]['CategoryTitle'].' <span class="fa fa-angle-down"></span></a>';
                }
                else
                {
                    $html .= '<li'.$li_class.'><a'.($ActivePage == $Category['Data'][$ChildID]['CategoryTitle'] ? ' class="active"' : '').' href="'.$Category['Data'][$ChildID]['CategoryURL'].'">'.$Icon.$Category['Data'][$ChildID]['CategoryTitle'].'</a>';
                }

                // re-calls the function to find parent with child-items recursively
                $html .= HeaderMenu($ChildID, $Category, $ActivePage, true);

                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        return $html;
    }

    function HeaderMenu($ParentID, $Category, $ActivePage = '', $dropdown = false, $level = 0, $MenuType = '')
    {
        $html = '';

        if(isset($Category['List'][$ParentID]))
        {
            if($level == 0)
            {
                $html = '<ul class="nav navbar-nav">';
            }
            elseif($dropdown && $level == 1)
            {
                $html = '<ul class="dropdown-menu '.$MenuType.'">';
            }
            else
                $html = '<ul>';

            foreach($Category['List'][$ParentID] as $ChildID)
            {
                if($ParentID == 0 && isset($Category['List'][$ChildID]))
                {
                    $li_class = ' class="dropdown-submenu"';
                }
                else if(isset($Category['List'][$ChildID]) || $level == 1)
                {
                    $li_class = ' class="title"';
                }
                else
                {
                    $li_class = '';
                }

                // If sub-category exists then show dropdown arrow against the parent category
                if( strpos($li_class, 'dropdown-submenu') !== false)
                {
                    $html .= '<li'.$li_class.'><a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$Category['Data'][$ChildID]['CategoryTitle'].' <span class="fa fa-angle-down"></span></a>';
                }
                elseif( strpos($li_class, 'title') !== false)
                {
                    $html .= '<li'.$li_class.'><a>'.$Category['Data'][$ChildID]['CategoryTitle'].'</a>';
                }
                else
                {
                    $html .= '<li'.$li_class.'><a'.($ActivePage == $Category['Data'][$ChildID]['CategoryTitle'] ? ' class="active"' : '').' href="'.$Category['Data'][$ChildID]['CategoryURL'].'">'.$Icon.$Category['Data'][$ChildID]['CategoryTitle'].'</a>';
                }

                // re-calls the function to find parent with child-items recursively
                $html .= HeaderMenu($ChildID, $Category, $ActivePage, true, $level+1, $Category['Data'][$ChildID]['MenuType']);

                $html .= '</li>';
            }

            if($level == 0)
            {
                $html .= '<li><a href="/upload-your-design/">Upload Your Design</a></li>';
				$html .= '<li><a href="https://unifro.wordpress.com/" target="_blank">Blog</a></li>';
            }

            $html .= '</ul>';
        }
        return $html;
    }

    function MultilevelMenu($ParentID, $Category, $ActivePage = '', $ShowIcon = false)
    {
        global $PermissionIDs;

        $html = '';

        if(isset($Category['List'][$ParentID]))
        {
            $html = '<ul>';

            foreach($Category['List'][$ParentID] as $ChildID)
            {
                if($ShowIcon)
                {
                    // Show Icon only for first Parent Category
                    $Icon = $Category['Data'][$ChildID]['ParentID'] == 0 ? '<i class="'.$Category['Data'][$ChildID]['Icon'].'"></i> ' : '';
                }

                // Set the class for li
                if($ParentID == 0 && isset($Category['List'][$ChildID]))
                {
                    $li_class = ' class="has_sub"';
                }
                else if(isset($Category['List'][$ChildID]))
                {
                    $li_class = ' class="has_sub"';
                }
                else
                {
                    $li_class = '';
                }

                if(empty($PermissionIDs) || isset($PermissionIDs[$ChildID]))
                {
                    // If sub-category exists then show dropdown arrow against the parent category
                    if( strpos($li_class, 'has_sub') !== false)
                    {
                        $html .= '<li'.$li_class.'><a href="#">'.$Icon.'<span> '.$Category['Data'][$ChildID]['PageTitle'].' </span> <span class="arrow"></span></a>';
                    }
                    else
                    {
                        $html .= '<li'.$li_class.'><a'.($ActivePage == $Category['Data'][$ChildID]['PageTitle'] ? ' class="active"' : '').' href="'.$Category['Data'][$ChildID]['PageURL'].'">'.$Icon.$Category['Data'][$ChildID]['PageTitle'].'</a>';
                    }
                }


                // re-calls the function to find parent with child-items recursively
                $html .= MultilevelMenu($ChildID, $Category, $ActivePage);

                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        return $html;
    }

    function AccessSettings($ParentID, $Category, $PermissionIDs = '')
    {
        $html = '';

        if($PermissionIDs == 'All')
        {
            $CheckAll = true;
        }
        else
        {
            $PageIDs = array_filter(explode(',', $PermissionIDs));
            $PageIDs = array_flip($PageIDs);
        }

        if(isset($Category['List'][$ParentID]))
        {
            $html = '<ul>';

            foreach($Category['List'][$ParentID] as $ChildID)
            {
                // Set the class for li
                if($ParentID == 0 && isset($Category['List'][$ChildID]))
                {
                    $li_class = ' class="has_sub"';
                }
                else if(isset($Category['List'][$ChildID]))
                {
                    $li_class = ' class="has_sub"';
                }
                else
                {
                    $li_class = '';
                }

                $checked = $CheckAll ? ' checked' : (isset($PageIDs[$Category['Data'][$ChildID]['PageID']]) ? ' checked' : '');

                $html .= '<li'.$li_class.'>
                                <div class="custom-checkbox">
                                    <label>
                                        <input type="checkbox"'.$checked.' name="PageIDs[]" value="'.$Category['Data'][$ChildID]['PageID'].'" />
                                        <span></span> '.$Category['Data'][$ChildID]['PageTitle'].'
                                    </label>
                                </div>';

                // re-calls the function to find parent with child-items recursively
                $html .= AccessSettings($ChildID, $Category, $PermissionIDs);

                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        return $html;
    }

    function MasterCategories($ParentID, $Category)
    {
        $html = '';

        if(isset($Category['List'][$ParentID]))
        {
            $html = '<ul>';

            foreach($Category['List'][$ParentID] as $ChildID)
            {
                // Set the class for li
                if($ParentID == 0 && isset($Category['List'][$ChildID]))
                {
                    $li_class = ' class="has_sub"';
                }
                else if(isset($Category['List'][$ChildID]))
                {
                    $li_class = ' class="has_sub"';
                }
                else
                {
                    $li_class = '';
                }

                $checked = $CheckAll ? ' checked' : (isset($PageIDs[$Category['Data'][$ChildID]['CategoryID']]) ? ' checked' : '');

                $html .= '<li'.$li_class.'>
                                <div class="">
                                    <a href="'.$Category['Data'][$ChildID]['CategoryURL'].'">
                                        '.$Category['Data'][$ChildID]['CategoryTitle'].'
                                    </a>
                                </div>';

                // re-calls the function to find parent with child-items recursively
                $html .= MasterCategories($ChildID, $Category);

                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        return $html;
    }

    function OtherCategories($ParentID = 0, $ParentTitle = "", $Parent = false)
    {
        global $OptionList;

        $GetCategories = MysqlQuery("SELECT * FROM master_categories WHERE CategoryID != '1' AND ParentID = '".$ParentID."' ORDER By SortOrder");
        $data = MysqlFetchAll($GetCategories);

        foreach ($data as $key => $cat)
        {
            if($ParentTitle != '')
            {
                $ParentLevel[$ParentID] = $ParentTitle.' &raquo; ';
                $CatTitle = implode(' ',$ParentLevel).$cat["CategoryTitle"];

                //$CatTitle = $cat["CategoryTitle"];

            }
            else
            {
                $CatTitle = $cat["CategoryTitle"];
            }

            if($Parent)
            {
                $OptionList[$cat["CategoryID"]] = $CatTitle;
            }
            else
            {
                if($ParentID != 0)
                {
                    $OptionList[$cat["CategoryID"]] = $CatTitle;
                }
            }

            OtherCategories($cat["CategoryID"], $CatTitle);
        }

        return $OptionList;
    }

    function ReadymadeCategories($ParentID = 0, $ParentTitle = "", $Parent = false)
    {
        global $OptionList;

        $GetCategories = MysqlQuery("SELECT CategoryID, CategoryTitle, CategoryURL, WidgetImage FROM master_categories WHERE CategoryID != '1' AND ParentID = '".$ParentID."' ORDER By SortOrder");
        $data = MysqlFetchAll($GetCategories);

        foreach ($data as $key => $cat)
        {
            if($cat["CategoryID"] != 14 && $cat["CategoryID"] != 24)
            {
                if($Parent)
                {
                    $OptionList[$cat["CategoryID"]] = $cat;
                }
                else
                {
                    if($ParentID != 0)
                    {
                        $OptionList[$cat["CategoryID"]] = $cat;
                    }
                }
            }

            ReadymadeCategories($cat["CategoryID"]);
        }

        return $OptionList;
    }

    function GetFabricCategories($ParentID = 0, $ParentTitle = "")
    {
        $GetCategories = MysqlQuery("SELECT * FROM master_categories WHERE CategoryID != '2' AND ParentID = '".$ParentID."' ORDER By SortOrder");
        $data = MysqlFetchAll($GetCategories);

        foreach ($data as $key => $cat)
        {
            if($ParentTitle != '')
            {
                $ParentLevel[$ParentID] = $ParentTitle.' &raquo; ';
                $CatTitle = implode(' ',$ParentLevel).$cat["CategoryTitle"];
            }
            else
            {
                $CatTitle = $cat["CategoryTitle"];
            }

            if($ParentID != 1)
                echo '<option'.($cat["CategoryID"] == $_POST['CategoryID'] ? ' selected' : '').' value="'.$cat["CategoryID"].'">'.$CatTitle.'</option>';

            GetFabricCategories($cat["CategoryID"], $CatTitle);
        }
    }

    function CustomCategories($ParentID = 0, $ParentTitle = "")
    {
        $GetCategories = MysqlQuery("SELECT * FROM master_categories WHERE CategoryID != '2' AND ParentID = '".$ParentID."' ORDER By SortOrder");
        $data = MysqlFetchAll($GetCategories);

        foreach ($data as $key => $cat)
        {
            if($ParentTitle != '')
            {
                $ParentLevel[$ParentID] = $ParentTitle.' &raquo; ';
                $CatTitle = implode(' ',$ParentLevel).$cat["CategoryTitle"];
            }
            else
            {
                $CatTitle = $cat["CategoryTitle"];
            }

            if($ParentID != 1)
                echo '<option'.(in_array($cat["CategoryID"], $_POST['ParentID']) ? ' selected' : '').' value="'.$cat["CategoryID"].'">'.$CatTitle.'</option>';

            CustomCategories($cat["CategoryID"], $CatTitle);
        }
    }

    function GetParentCats($CatID, $Category, $start = true)
    {
        global $ParentTitle;

        if($start)
        {
            $ParentTitle = array();
        }

        if(isset($Category['Data'][$CatID]))
        {
            $parentID = $Category['Data'][$CatID]['ParentID'];
            if($parentID > 0)
            {
                $ParentTitle[] = $Category['Data'][$parentID]['CategoryTitle'];
                GetParentCats($parentID, $Category, false);
            }
        }
        return array_filter(array_reverse($ParentTitle));
    }

    function GetChildCats($CatID, $start = true, $Parent = false)
    {
        global $AllCats;

        if($start)
        {
            $AllCats = array();
            if($Parent)
            {
                $AllCats[] = $CatID;
            }
        }
        else
        {
            $AllCats[] = $CatID;
        }
        $GetChildCat = MysqlQuery("SELECT CategoryID FROM master_categories WHERE ParentID = '".$CatID."'");
        for(;$row = @mysqli_fetch_assoc($GetChildCat);)
        {
            GetChildCats($row['CategoryID'], false);
        }
        return $AllCats;
    }

    function GetChildHeadings($HeadingID, $start = true, $row = '')
    {
        global $AllCats;

        if($start)
        {
            $AllCats = array();
            if($row != '')
            {
                $AllCats[] = $row;
            }
        }
        else
        {
            $AllCats[] = $row;
        }
        $GetChildCat = MysqlQuery("SELECT h.HeadingID, h.Heading, h.Attribute, s.SymbolID FROM master_element_headings h LEFT JOIN element_styles s ON s.HeadingID = h.HeadingID WHERE h.ParentID = '".$HeadingID."'");
        for(;$row = @mysqli_fetch_assoc($GetChildCat);)
        {
            GetChildHeadings($row['HeadingID'], false, $row);
        }
        return $AllCats;
    }

    function GenerateCategoryURL($CategoryID)
    {
        //  Dependencies: MakeFriendlyURL()
        //  First of all get all titles of all the parent categories
        $titles = array();
        while(mysqli_num_rows($res = MysqlQuery("SELECT CategoryTitle, ParentID FROM master_categories WHERE CategoryID = '".$CategoryID."' LIMIT 1")))
        {
            $res = mysqli_fetch_assoc($res);
            $titles[] = MakeFriendlyURL($res['CategoryTitle']);
            $CategoryID = $res['ParentID'];
        }
        krsort($titles);
        return '/'.implode('/', $titles);
    }

    function GetStyleNameByID($StyleID)
    {
         // GET THE STYLE NAME BY ID
        $StyleNames = MysqlQuery("SELECT StyleName FROM element_styles WHERE StyleID = '".$StyleID."' LIMIT 1");
        return mysqli_fetch_assoc($StyleNames)['StyleName'];
    }

    function GetButtonImage($ButtonID)
    {
         // GET THE STYLE NAME BY ID
        $ImageName = MysqlQuery("SELECT ImageName FROM master_buttons WHERE ButtonID = '".$ButtonID."' LIMIT 1");
        return mysqli_fetch_assoc($ImageName)['ImageName'];
    }

    function GetStyleDetails($StyleID, $Field = '')
    {
        if(strpos($StyleID, '-', 0) !== false)
        {
            $StyleID = explode('-',$StyleID);
            $StyleDetails = MysqlQuery("SELECT * FROM element_sub_styles WHERE SubStyleID = '".$StyleID[1]."' AND StyleID = '".$StyleID[0]."' LIMIT 1");
        }
        else
        {
            $StyleDetails = MysqlQuery("SELECT * FROM element_styles WHERE StyleID = '".$StyleID."' LIMIT 1");
        }

        $StyleDetails = mysqli_fetch_assoc($StyleDetails);
        $StyleDetails['ImageName'] = _StyleIconsDir.$StyleDetails['ImageName'];

        if($Field != '')
            return $StyleDetails[$Field];
        else
            return $StyleDetails;
    }

    function GetFabricCodeByID($FabricID)
    {
         // GET THE FABRIC CODE BY ID
        $FabricCode = MysqlQuery("SELECT FabricCode FROM fabrics WHERE FabricID = '".$FabricID."' LIMIT 1");
        return mysqli_fetch_assoc($FabricCode)['FabricCode'];
    }

    function GetFabricDetails($FabricID, $Field = '')
    {
        $FabricDetails = MysqlQuery("SELECT * FROM fabrics WHERE FabricID = '".$FabricID."' LIMIT 1");
        $FabricDetails = mysqli_fetch_assoc($FabricDetails);
        $FabricDetails['FabricImageThumb'] = _FabricImageThumbDir.$FabricDetails['FabricImage'];
        $FabricDetails['FabricImage'] = _FabricImageDir.$FabricDetails['FabricImage'];

        if($Field != '')
            return $FabricDetails[$Field];
        else
            return $FabricDetails;
    }

    function GetClientDetails($ClientID, $Field = '')
    {
        global $MemberID;

        if(is_numeric($MemberID))
        {
            if($Field != '')
            {
                $ClientDetails = MysqlQuery("SELECT ".$Field." FROM clients WHERE ClientID = '".$ClientID."' AND SalesID = '".$MemberID."' LIMIT 1");
                $ClientDetails = mysqli_fetch_assoc($ClientDetails)[$Field];
            }
            else
            {
                $ClientDetails = MysqlQuery("SELECT c.*, s.Name, ct.CityName FROM clients c LEFT JOIN states s ON s.StateID = c.State LEFT JOIN cities ct ON ct.CityID = c.City WHERE c.ClientID = '".$ClientID."' AND c.SalesID = '".$MemberID."' LIMIT 1");
                $ClientDetails = mysqli_fetch_assoc($ClientDetails);
            }
        }
        else
        {
            if($Field != '')
            {
                $ClientDetails = MysqlQuery("SELECT ".$Field." FROM clients WHERE ClientID = '".$ClientID."' LIMIT 1");
                $ClientDetails = mysqli_fetch_assoc($ClientDetails)[$Field];
            }
            else
            {
                $ClientDetails = MysqlQuery("SELECT ClientID, ClientName FROM clients WHERE ClientID = '".$ClientID."' LIMIT 1");
                $ClientDetails = mysqli_fetch_assoc($ClientDetails);
            }
        }

        return $ClientDetails;
    }

    function GetCategoryDetails($CategoryID, $Field = '')
    {
        if($Field != '')
        {
            $CategoryDetails = MysqlQuery("SELECT ".$Field." FROM master_categories WHERE CategoryID = '".$CategoryID."' LIMIT 1");
            $CategoryDetails = mysqli_fetch_assoc($CategoryDetails)[$Field];
        }
        else
        {
            $CategoryDetails = MysqlQuery("SELECT * FROM master_categories WHERE CategoryID = '".$CategoryID."' LIMIT 1");
            $CategoryDetails = mysqli_fetch_assoc($CategoryDetails);
        }
        return $CategoryDetails;
    }

    function GetProductDetails($ProductID, $Field = '')
    {
        if($Field != '')
        {
            $ProductDetails = MysqlQuery("SELECT ".$Field." FROM products WHERE ProductID = '".$ProductID."' LIMIT 1");
            $ProductDetails = mysqli_fetch_assoc($ProductDetails)[$Field];
        }
        else
        {
            $ProductDetails = MysqlQuery("SELECT * FROM products WHERE ProductID = '".$ProductID."' LIMIT 1");
            $ProductDetails = mysqli_fetch_assoc($ProductDetails);
        }
        return $ProductDetails;
    }

    function GetOrderProducts($ID, $Field = '')
    {
        if($Field != '')
        {
            $data = MysqlQuery("SELECT ".$Field." FROM client_order_details WHERE OrderDetailsID = '".$ID."' LIMIT 1");
            $data = mysqli_fetch_assoc($data)[$Field];
        }
        else
        {
            $data = MysqlQuery("SELECT * FROM client_order_details WHERE OrderDetailsID = '".$ID."' LIMIT 1");
            $data = mysqli_fetch_assoc($data);
        }
        return $data;
    }

    function GetStateDetails($ID, $Field = '')
    {
        if($Field != '')
        {
            $data = MysqlQuery("SELECT ".$Field." FROM states WHERE StateID = '".$ID."' LIMIT 1");
            $data = mysqli_fetch_assoc($data)[$Field];
        }
        else
        {
            $data = MysqlQuery("SELECT * FROM states WHERE StateID = '".$ID."' LIMIT 1");
            $data = mysqli_fetch_assoc($data);
        }
        return $data;
    }

    function GetCouponDetails($ID, $Field = '')
    {
        if($Field != '')
        {
            $data = MysqlQuery("SELECT ".$Field." FROM coupons WHERE CouponID = '".$ID."' LIMIT 1");
            $data = mysqli_fetch_assoc($data)[$Field];
        }
        else
        {
            $data = MysqlQuery("SELECT * FROM coupons WHERE CouponID = '".$ID."' LIMIT 1");
            $data = mysqli_fetch_assoc($data);
        }
        return $data;
    }

    function GetSalesAgentName($SalesID)
    {
         // GET THE SALES AGENT NAME BY ID
        $SalesAgentName = MysqlQuery("SELECT FirstName, LastName FROM sales WHERE SalesID = '".$SalesID."' LIMIT 1");
        $Agent = mysqli_fetch_assoc($SalesAgentName);
        return $Agent['FirstName'].' '.$Agent['LastName'];
    }

    function GetRoleName($RoleID)
    {
         // GET THE ROLE NAME BY ID
        $RoleName = MysqlQuery("SELECT Role FROM roles WHERE RoleID = '".$RoleID."' LIMIT 1");
        $RoleName = mysqli_fetch_assoc($RoleName)['Role'];
        return $RoleName;
    }

    function GetCustomProductPrice($CustomData)
    {
        $CustomData = json_decode($CustomData, true);
        $CustomData = $CustomData['Selections'];

        $CategoryDetails = GetCategoryDetails($CustomData['CategoryID']);
        $SizePercentage = $CategoryDetails['Size'];
        $Price['BasePrice'] = $CategoryDetails['BasePrice'];
        $Price['FabricPrice'] = GetFabricDetails($CustomData['FabricID'], 'FabricPrice');
        $CustomData['Price']['BasePrice'] = floatval($Price['BasePrice']);
        $CustomData['Price']['FabricPrice'] = floatval($Price['FabricPrice']);


        // LETS LOOP THROUGH THE STYLES TO GET THE STYLE IDS
        foreach($CustomData['Styles'] as $Heading => $styleArr)
        {
            foreach($styleArr as $key => $subArr)
            {
                if(is_array($subArr))
                {
                    if(is_numeric($subArr['FabricID']) && $CustomData['FabricID'] != $subArr['FabricID'])
                    {
                        $StylePercentage = GetStyleDetails($subArr['StyleID'], 'Percentage');
                        $CustomData['Price'][$subArr['StyleID']] = floatval(GetFabricDetails($subArr['FabricID'], 'FabricPrice') * $StylePercentage / 100);
                        $StyleTotalPrice = $StyleTotalPrice + $CustomData['Price'][$subArr['StyleID']];
                    }

                    foreach($subArr as $key => $subStyles)
                    {
                        if(is_array($subStyles))
                        {
                            if($CustomData['FabricID'] != $subStyles['FabricID'])
                            {
                                $StylePercentage = GetStyleDetails($subStyles['StyleID'], 'Percentage');
                                $CustomData['Price'][$subStyles['StyleID']] = floatval(GetFabricDetails($subStyles['FabricID'], 'FabricPrice') * $StylePercentage / 100);
                                $StyleTotalPrice = $StyleTotalPrice + $CustomData['Price'][$subStyles['StyleID']];
                            }
                        }
                    }

                }
                else
                {
                    if($key == 'StyleID')
                    {
                        $StyleID = $subArr;
                        $StylePercentage = GetStyleDetails($StyleID, 'Percentage');
                    }
                    elseif($StylePercentage > 0 && $key == 'FabricID')
                    {
                        if($CustomData['FabricID'] != $subArr)
                        {
                            $FabricID = $subArr;
                            $CustomData['Price'][$StyleID] = floatval(GetFabricDetails($FabricID, 'FabricPrice') * $StylePercentage / 100);
                            $StyleTotalPrice = $StyleTotalPrice + $CustomData['Price'][$StyleID];
                        }
                    }
                }
            }
        }
        $CustomData['Price']['TotalPrice'] = $CustomData['Price']['BasePrice'] + $CustomData['Price']['FabricPrice'] + $StyleTotalPrice;
        return array('Selections' => $CustomData);
    }

    function GetAllFabricsFromCustomData($CustomData)
    {
        $CustomData = json_decode($CustomData, true);
        $CustomData = $CustomData['Selections'];

        $FabricIDs[] = $CustomData['FabricID'];

        // LETS LOOP THROUGH THE STYLES TO GET THE STYLE IDS
        foreach($CustomData['Styles'] as $Heading => $styleArr)
        {
            foreach($styleArr as $key => $subArr)
            {
                if(is_array($subArr))
                {
                    if(is_numeric($subArr['FabricID']) && $CustomData['FabricID'] != $subArr['FabricID'])
                    {
                        $FabricIDs[] = $subArr['FabricID'];
                    }

                    foreach($subArr as $key => $subStyles)
                    {
                        if(is_array($subStyles))
                        {
                            if(is_numeric($subStyles['FabricID']) && $CustomData['FabricID'] != $subStyles['FabricID'])
                            {
                                $FabricIDs[] = $subStyles['FabricID'];
                            }
                        }
                    }

                }
                else
                {
                    if($key == 'StyleID')
                    {
                        $StyleID = $subArr;
                        $StylePercentage = GetStyleDetails($StyleID, 'Percentage');
                    }
                    elseif($StylePercentage > 0 && $key == 'FabricID')
                    {
                        if($CustomData['FabricID'] != $subArr)
                        {
                            $FabricIDs[] = $subArr;
                        }
                    }
                }
            }
        }

        return $FabricIDs;
    }

    function ExportToExcel($query, $colheads, $RequiredFields, $filename, $SheetTitle = '', $funcs = '')
    {
       /**********THIS IS USED TO GET THE CELL REFERENCES OF COLUMN HEADERS***********/
        $min = ord("A"); // ord returns the ASCII value of the first character of string.
        $max = count($colheads[0]);
        $firstChar = ""; // Initialize the First Character
        $abc = $min;   // Initialize our alphabetical counter

        for($j = 1; $j <= $max; ++$j)
        {
            $col = $firstChar.chr($abc);   // This is the Column Label.
            $last_char = substr($col, -1);

            if($j == 1)
                $StartCol = $col;

            if ($last_char == "Z") // At the end of the alphabet. Time to Increment the first column letter.
            {
                $abc = $min; // Start Over
                if ($firstChar == "") // Deal with the first time.
                    $firstChar = "A";
                else
                {
                    $fchrOrd = ord($firstChar);// Get the value of the first character
                    $fchrOrd++; // Move to the next one.
                    $firstChar = chr($fchrOrd); // Reset the first character.
                }
                $col = $firstChar.chr($abc); // This is the column identifier
            }
            //echo $col.'<br>';
            $abc++; // Move on to the next letter
        }
        $HeaderCellRef = $StartCol.'3'.':'.$col.'3';
        /********************************************************/
        $exec = MysqlQuery($query);
        for(;$res = mysqli_fetch_assoc($exec);)
        {
            $recs = array();
            foreach($RequiredFields as $val)
            {
                if($res[$val] != '')
    			{
    			    if($val == 'FabricImage')
                        $res[$val] = _HOST._FabricImageDir.$res[$val];

    			    foreach($funcs as $a => $b)
    				{
    				    if($val == $a)
    					{
    					    $v = str_replace('###', $res[$val], $b);
    						eval("\$v = $v;");
                            $res[$val] = $v;
    					}
    				}
    			}
                $recs[] = $res[$val];
            }
            $colheads[] = $recs;
        }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $styleBorderCells = array(
            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '999999')))
        );

        //$objPHPExcel->getActiveSheet()->setShowGridlines(false);

        $NoOfRows = 0;

        foreach($colheads as $row => $columns)
        {
            foreach($columns as $column => $data)
            {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column + 1, $row + 3, $data);
            }
            /*
            if($row == 0)
                $objPHPExcel->getActiveSheet()->getStyle($HeaderCellRef)->applyFromArray($styleArrayColHeads);
            */

            $NoOfRows++;
        }

        //ADD BORDERS TO DATA CELLS
        //$objPHPExcel->getActiveSheet()->getStyle($StartCol.'4'.':'.$col.($NoOfRows +2))->applyFromArray($styleBorderCells);

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle($SheetTitle);

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client�s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.$filename);
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    function GenerateLog($flag, $description)
    {
        $fileName = __DIR__ . "/../logs/failed_queries.txt";

        if(file_get_contents($fileName) != '')
        {
            $json = json_decode(file_get_contents($fileName), true);
        }
        $json[md5(AlphaNumericCode(30).microtime())] = array('Date' => time(), 'flag' => $flag, 'Description' => $description);
        file_put_contents($fileName, json_encode($json));
    }

    function CalculatePrice($UpdatedPrice)
    {
        if($UpdatedPrice['Discount'] > 0)
        {
            if($UpdatedPrice['DiscountType'] == '%')
            {
                $UpdatedPrice['DiscountPrice'] = round(($UpdatedPrice['Rate'] * $UpdatedPrice['Discount']) / 100);
                $UpdatedPrice['FinalPrice'] = round($UpdatedPrice['Rate'] - $UpdatedPrice['DiscountPrice']);
            }
            else
            {
                $UpdatedPrice['DiscountPrice'] = $UpdatedPrice['Discount'];
                $UpdatedPrice['FinalPrice'] = round($UpdatedPrice['Rate'] - $UpdatedPrice['DiscountPrice']);
            }
        }
        else
        {
            $UpdatedPrice['DiscountPrice'] = 0;
            $UpdatedPrice['FinalPrice'] = $UpdatedPrice['Rate'];
        }

        return $UpdatedPrice;
    }

    function ProductWidget($product)
    {
        if($product['Discount'] > 0)
        {
            if($product['DiscountType'] == '%')
            {
                $DiscountPrice = round(($product['Rate'] * $product['Discount']) / 100);
                $FinalPrice = $product['Rate'] - $DiscountPrice;
                $Discount = floatval($product['Discount']).'% off';
            }
            else
            {
                $FinalPrice = $product['Rate'] - $product['Discount'];
                $Discount = '<i class="fa fa-rupee"></i> '.floatval($product['Discount']).' off';
            }

            $price =    '<span class="strike-through"><i class="fa fa-rupee"></i> '.$product['Rate'].'</span>';
            $price .=   '<span class="price"><i class="fa fa-rupee"></i> '.$FinalPrice.'</span>';
            $price .=   '<span class="discount">'.$Discount.'</span>';
        }
        else
        {
            $price = '<span class="price"><i class="fa fa-rupee"></i> '.$product['Rate'].'</span>';
        }

        ob_start();
        ?>
        <!-- Product Item Start -->
        <div class="product">
            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                <div class="product-item">
                    <!-- Product Image -->
                    <figure class="product-grid-image">
                        <a href="<?=$product['ProductURL']?>" title="<?=$product['ProductName']?>">
                            <img class="img-responsive" src="<?=_ProductImageThumbDir.$product['FileName']?>">
                        </a>
                        <!-- Product Buttons
                        <div class="figure-caption">
                            <ul class="icons">
                                <li>
                                    <a href="javascript:void(0)" data-id="<?=$ID?>" data-pk="<?=$product['ImageID']?>" data-opt="product_image"><i class="fa fa-shopping-bag"></i></a>
                                </li>
                            </ul>
                        </div>
                        -->
                    </figure>
                    <div class="product-content">
                        <div class="product-inner-content">
                            <!-- Product Title -->
                            <div class="product-title">
                                <h4>
                                    <a href="<?=$product['ProductURL']?>"><?=$product['ProductName']?></a>
                                </h4>
                            </div>
                            <div class="product-price">
                                <?=$price?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Product Item End -->

        <?php
        return ob_get_clean();
    }

    function GetProductWeightQuantity($product)
    {
        if($product['CustomData'] != '')
        {
            $GetCategoryDetails = MysqlQuery("SELECT Weight FROM master_categories WHERE CategoryID = '".$product['ProductID']."' LIMIT 1");
            $Shipping[$product['CartID']]['Weight'] = mysqli_fetch_assoc($GetCategoryDetails)['Weight'];
        }
        else
        {
            // GET THE PRODUCT READYMADE WEIGHT
            $ProductWeight = MysqlQuery("SELECT Weight FROM products WHERE ProductID = '".$product['ProductID']."' LIMIT 1");
            $Shipping[$product['CartID']]['Weight'] = mysqli_fetch_assoc($ProductWeight)['Weight'];
        }

        // Get the Product Quantity
        $Quantity = json_decode($product['Size'], true);

        foreach($Quantity as $size => $qty)
        {
            if($qty > 0)
            {
                $Shipping[$product['CartID']]['Quantity'] = $Shipping[$product['CartID']]['Quantity'] + $qty;
            }
        }
        return $Shipping[$product['CartID']];
    }

    function GetOrderedProducts($ID)
    {
        $GetProducts = MysqlQuery("SELECT * FROM customer_order_details WHERE OrderID = '".$ID."'");
        if(mysqli_num_rows($GetProducts) > 0)
        {
            $ProductDetails = array();

            for(; $row = mysqli_fetch_assoc($GetProducts);)
            {
                $ButtonDetails = array();

                $ProductDetails[$row['OrderDetailsID']]['OrderID'] = $row['OrderID'];
                $ProductDetails[$row['OrderDetailsID']]['ProductID'] = $row['ProductID'];
                $ProductDetails[$row['OrderDetailsID']]['Size'] = $row['Size'];
                $ProductDetails[$row['OrderDetailsID']]['TotalCost'] = $row['TotalCost'];
                $ProductDetails[$row['OrderDetailsID']]['TaxRate'] = $row['TaxRate'];
                $ProductDetails[$row['OrderDetailsID']]['AdditionalDetails'] = $row['AdditionalDetails'];

                // GET THE CATEGORY URL
                $GetCategoryDetails = MysqlQuery("SELECT CategoryTitle FROM master_categories WHERE CategoryID = '".$row['ProductID']."' LIMIT 1");
                $ProductDetails[$row['OrderDetailsID']]['CategoryTitle'] = mysqli_fetch_assoc($GetCategoryDetails)['CategoryTitle'];

                if($row['CustomData'] != '')
                {
                    $FabricID = 0;

                    $row['data'] = json_decode($row['CustomData'], true);
                    ksort($row['data']['Selections']['Styles']);

                    $ProductDetails[$row['OrderDetailsID']]['image'] = '<img class="thumbnail" src="'._HOST.GetFabricDetails($row['data']['Selections']['FabricID'], 'FabricImage').'">';

                    $ProductDetails[$row['OrderDetailsID']]['html'][] = '<ul class="custom-product-details">';

                    $ProductDetails[$row['OrderDetailsID']]['html'][] = '<li class="bold text-inverse">Custom Designed '.$row['ProductName'].'</li>';
                    $ProductDetails[$row['OrderDetailsID']]['html'][] = '<li><b>Main Fabric:</b> '.GetFabricDetails($row['data']['Selections']['FabricID'], 'FabricName').'</li>';


                    foreach($row['data']['Selections']['Styles'] as $element => $styleID)
                    {
                        if( isset($styleID['ButtonID']) )
                        {
                            $ButtonDetails[] = '<li>
                                                    <img src="'._HOST._ButtonsDir.GetButtonImage($styleID['ButtonID']).'" style="width:60px">&nbsp;
                                                    <br>'.$element.'
                                                </li>';
                        }
                        else
                        {
                            $ProductDetails[$row['OrderDetailsID']]['html'][] = GetStyleNameByID($styleID['StyleID']) != '' ? '<li>'.$element.' : '.GetStyleNameByID($styleID['StyleID']).'</li>' : '';
                        }

                        foreach($styleID as $subKey => $subArr)
                        {
                            if(is_array($subArr))
                            {
                                foreach($subArr as $key => $subStyles)
                                {
                                    if(is_numeric($subStyles))
                                    {
                                        $FabricName =  GetFabricDetails($subStyles, 'FabricName');
                                        $key = '';
                                    }
                                    else
                                    {
                                        if($FabricID != $subStyles['FabricID'])
                                        {
                                            $FabricName =  GetFabricDetails($subStyles['FabricID'], 'FabricName');
                                            $key = $key == 'Fabric' ? '' : ' ('.$key.')';
                                        }
                                    }
                                    $ProductDetails[$row['OrderDetailsID']]['html'][] = $FabricName != '' ? '<li>- '.$subKey.$key.' : '.$FabricName.'</li>' : '';
                                }
                            }
                        }
                    }

                    $ProductDetails[$row['OrderDetailsID']]['html'][] = '</ul>';
                    //$ProductDetails = implode('', $ProductDetails);

                    if(count($ButtonDetails) > 0)
                    {
                        $ProductDetails[$row['OrderDetailsID']]['button'] = '<ul class="custom-product-details">'.implode('', $ButtonDetails).'</ul>';
                    }
                }
                else
                {
                    // GET THE PRODUCT DEFAULT IMAGE
                    $GetDefaultImage = MysqlQuery("SELECT FileName FROM product_images
                                                   WHERE ProductID = '".$row['ProductID']."' AND DefaultImage = '1' LIMIT 1");
                    $GetDefaultImage = mysqli_fetch_assoc($GetDefaultImage)['FileName'];

                    $ProductDetails[$row['OrderDetailsID']]['html'] = '<div class="bold text-inverse">'.$row['ProductName'].'</div>';
                    $ProductDetails[$row['OrderDetailsID']]['image'] = '<img class="thumbnail" src="'._HOST._ProductImageThumbDir.$GetDefaultImage.'">';
                }
            }
        }
        return $ProductDetails;
    }

    function DisplayOrderedProducts($ProductDetails)
    {
        foreach($ProductDetails as $key => $row)
        {
            ?>
            <tr>
                <td style="vertical-align: top"><?=$row['image']?></td>
                <td style="vertical-align: top">
                    <?=is_array($row['html']) ? implode('',$row['html']) : $row['html']?>
                    <?=$row['button']?>
                </td>
                <td style="vertical-align: top">
                    <ul>
                    <?php
                    foreach(json_decode($row['Size'], true) as $size => $qty)
                    {
                        if($qty > 0)
                        {
                            echo    '<li class="form-inline">
                                        <p class="form-control-static font12">Size: '.$size.' / Quantity: '.$qty.'</p>
                                    </li>';
                        }
                    }
                    ?>
                    </ul>
                </td>
                <td class="product-total bold font15" style="vertical-align: top">
                    <?=FormatAmount($row['TotalCost'])?>
                </td>

            </tr>
            <?php
        }
    }

    function SendOrderConfirmationEmail($ID)
    {
        // Get Customer details
        $OrderDetails = MysqlQuery("SELECT * FROM customer_orders WHERE OrderID = '".$ID."'");
        $OrderDetails = mysqli_fetch_assoc($OrderDetails);

        // GET THE PRODUCT DETAILS
        $ProductDetails = GetOrderedProducts($ID);

        ob_start();
        ?>

        <tr>
            <td>Dear <?=$OrderDetails['ShippingName']?>,</td>
        </tr>
        <tr>
            <td style="padding-top: 20px">Thank you for shopping on Unifro!</td>
        </tr>
        <tr>
            <td style="padding-top: 20px">
                <div style="display: inline-block; width: 100px">Order Number</div>
                <div style="font-weight: bold;display: inline-block">: &nbsp;<?=$ID?></div><br>

                <div style="display: inline-block; width: 100px">Total Cost</div>
                <div style="font-weight: bold;display: inline-block">: &nbsp;<?=FormatAmount($OrderDetails['TotalCost'])?></div><br>

                <div style="display: inline-block; width: 100px">Discount</div>
                <div style="font-weight: bold;display: inline-block">: &nbsp;<?=FormatAmount($OrderDetails['DiscountAmount'])?></div><br>

                <div style="display: inline-block; width: 100px">Shipping Cost</div>
                <div style="font-weight: bold;display: inline-block">: &nbsp;<?=FormatAmount($OrderDetails['ShippingCharges'])?></div><br>

                <div style="display: inline-block; width: 100px">Net Amount</div>
                <div style="font-weight: bold;display: inline-block">: &nbsp;<?=FormatAmount($OrderDetails['FinalTotal'])?></div>
            </td>
        </tr>
        <tr>
            <td style="border-bottom: 1px solid #dddddd;font-weight: bold;padding-top: 20px">Ordered Products:</td>
        </tr>

        <tr>
            <td style="padding-top: 10px">
                <?php
                foreach($ProductDetails as $key => $row)
                {
                    ?>

                        <div style="margin-bottom: 15px; border-bottom: 1px solid #dddddd; padding-bottom: 10px">
                            <div style="display: inline-block; margin-right: 10px; vertical-align: top">
                                <?=$row['image']?>
                            </div>
                            <div style="display: inline-block; font-size: 12px">
                                <?=is_array($row['html']) ? implode('',$row['html']) : $row['html']?>
                                <?=$row['button']?>
                                <div style="margin-top: 5px; margin-bottom: 10px">
                                    <?php
                                    foreach(json_decode($row['Size'], true) as $size => $qty)
                                    {
                                        if($qty > 0)
                                        {
                                            echo '<div>Size: <strong>'.$size.'</strong> / Quantity: <strong>'.$qty.'</strong></div>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div style="font-weight: bold"><?=FormatAmount($row['TotalCost'])?></div>
                            </div>
                        </div>
                    <?php
                }
                ?>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 10px">
                <strong>Shipping Address:</strong>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 5px">
                <?=$OrderDetails['ShippingName']?><br>
                <?=$OrderDetails['ShippingAddress'].', '.$OrderDetails['ShippingCity'].' - '.$OrderDetails['ShippingPincode'].', '.$OrderDetails['ShippingState']?><br>
                Phone: <?=$OrderDetails['ShippingPhone']?>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 20px; text-align: center">
                <a href="<?=_HOST?>/account/orders/<?=$ID?>/" style="text-decoration:none; color:#000;"><img src="<?=_HOST?>/images/track-order-btn.png" border="0"/>
    </a>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 10px">
                Hope you enjoyed shopping on Unifro.
            </td>
        </tr>

        <?php
        $content = ob_get_clean();

        SendMailHTML($OrderDetails['ShippingEmail'], 'Unifro Order Confirmation - '.$ID, FormatEmail($content));
    }

    /*----------------------------------------------------
        USED FOR ANALYTICS
    ------------------------------------------------------*/
    function TotalRegistrations($MemberType)
    {
        if($MemberType == 'client')
        {
            return mysqli_fetch_assoc(MysqlQuery("SELECT COUNT(ClientID) AS TotalClients FROM clients"))['TotalClients'];
        }
        elseif($MemberType == 'customer')
        {
            return mysqli_fetch_assoc(MysqlQuery("SELECT COUNT(MemberID) AS TotalCustomers FROM customers"))['TotalCustomers'];
        }
    }

    function TotalOrders($MemberType)
    {
        if($MemberType == 'client')
        {
            return mysqli_fetch_assoc(MysqlQuery("SELECT COUNT(OrderID) AS Orders FROM client_orders"))['Orders'];
        }
        elseif($MemberType == 'customer')
        {
            return mysqli_fetch_assoc(MysqlQuery("SELECT COUNT(OrderID) AS Orders FROM customer_orders WHERE PaymentStatus = 'Successful'"))['Orders'];
        }
    }

    function TotalAbandonedCart($MemberType)
    {
        if($MemberType == 'client')
        {
            return mysqli_fetch_assoc(MysqlQuery("SELECT COUNT(CartID) AS CartProducts FROM client_shopping_cart"))['CartProducts'];
        }
        elseif($MemberType == 'customer')
        {
            return mysqli_fetch_assoc(MysqlQuery("SELECT COUNT(CartID) AS CartProducts FROM customer_shopping_cart"))['CartProducts'];
        }
    }

	function TotalSales($MemberType)
    {
        if($MemberType == 'client')
        {
            $query = "SELECT SUM(o.FinalTotal) AS TotalSales FROM client_orders o
                        LEFT JOIN client_order_status os ON os.OrderID = o.OrderID
                        WHERE os.Status = 'Completed'";

        }
        elseif($MemberType == 'customer')
        {
            $query = "SELECT SUM(o.FinalTotal) AS TotalSales FROM customer_orders o
                        LEFT JOIN customer_order_status os ON os.OrderID = o.OrderID
                        WHERE os.Status = 'Completed'";
        }
        $TotalSales = mysqli_fetch_assoc(MysqlQuery($query))['TotalSales'];
		$TotalSales = $TotalSales > 0 ? $TotalSales : 0;
        return FormatAmount($TotalSales);
    }


	//	LETS SANITIZE COOKIES & GETs
	$_COOKIE    = array_map('SanitizePOST', $_COOKIE);
	$_GET       = array_map('SanitizePOST', $_GET);
?>