<?php
echo phpinfo();
exit;
    include_once("include-files/autoload-server-files.php");

    
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php include_once("include-files/common-css.php"); ?>
        <link rel="stylesheet" type="text/css" href="/css/slick.css"/>
        <link rel="stylesheet" type="text/css" href="/css/slick-theme.css"/>

        <style>
            body, html{ height: 100%; }
            svg{ max-width: 500px; }
            #CUFF-04, #CUFF-05, #CUFF-06, #BTN-04, #BTN-02, #BTN-03, [id^=Elbow_Patch]{ display: none; }
            #Collar-3, #Collar-3-shadow, #Collar-2, #Collar-2-shadow{ display: none; }
            #Collar-3-placket, #Collar-2-placket{ display: none; }
            #Collar-3-buttons, #Collar-2-buttons{ display: none; }
            #Texture-full, #Texture-full-shadow, #Texture-half, #Texture-half-shadow{ display: none; }
        </style>

        <?php include_once("include-files/common-js.php"); ?>

        <script>
        $(document).ready(function()
        {
            $('svg').find("#Texture-three-fourth image").attr("xlink:href", '/images/fabric-swatches/dn_13.jpg');
            $('svg').find("#Collar-1 image").attr("xlink:href", '/images/fabric-swatches/dn_13.jpg');
            //$('svg').find("#Sleeves image").attr("xlink:href", '/images/fabric-swatches/dark_gray_121.jpg');
        });
        </script>

</head>
<body>

    <div class="svg-container">
        <?=file_get_contents(_ROOT."/images/svgs/Ladies-Shirt-B-Rahul-02.svg")?>
    </div>

</body>
</html>
