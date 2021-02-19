<?php
    include_once('vars.php');
    include_once('connect.php');
    include_once('funcs.php');

    $baseUrl = explode('?', $_SERVER['REQUEST_URI']);
    $params = $baseUrl[1];
    $baseUrl = $baseUrl[0];
    $FolderLevels = array_values(array_filter(explode('/', $baseUrl)));

    if($FolderLevels[0] == 'admin')
    {
        include_once(_ROOT._AdminIncludesDir."admin-login-check.php");
    }
    else
    {
        include_once("site-config.php");
        include_once("login-check.php");
    }
?>