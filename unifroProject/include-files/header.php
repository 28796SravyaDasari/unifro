<?php
    // GET ALL CATEGORIES

    $NavigationMenu = MysqlQuery("SELECT * FROM master_categories WHERE Status = '1' ORDER By SortOrder");
    for(;$row = mysqli_fetch_assoc($NavigationMenu);)
    {
        $MasterCategory['List'][$row['ParentID']][] = $row['CategoryID'];
        $MasterCategory['Data'][$row['CategoryID']] = $row;
    }
    
?>

    <header>
        <div class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="<?=$HomeURL?>" title="Unifro.in"><img src="<?=_LOGO?>" alt="Unifro"></a>
                </div>

                <div class="collapse navbar-collapse">
                    <?=HeaderMenu(0, $MasterCategory)?>

                    <ul class="nav navbar-nav navbar-right">
                        <?php
                        if($LoggedIn)
                        {
                            ob_start();
                        ?>
                            <?php if($MemberType != 'Sales') { ?>
                            <li class="cart">
                                 <a href="<?=$CartURL?>">
                                    <span id="cart-total"><span class="cart-number"><?=$ProductsInCart?></span></span>
                                </a>
                            </li>

                            <?php } ?>

                            <li class="account">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                    <i class="fa fa-user"></i> <?=$MemberDetails['FirstName']?> <i class="fa fa-caret-down"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?=$MyAccountURL?>">My Account</a></li>
                                    <li><a href="<?=$ChangePasswordURL?>">Change Password</a></li>
                                    <li><a href="/logout/">Logout</a></li>
                                </ul>
                            </li>
                        <?php
                            ob_get_contents();
                        }
                        else
                          echo '<li class="bold"><A href="/login/"><i class="fa fa-lock"></i> Login</A></li>';
                        ?>
                   </ul>
                </div>
            </div>
        </div>
    </header>

    <script>

        // Set the position of parent LI of Megamenu to Static. This will make Megamenu full width
        $('.megamenu').parent('li').css({ 'position' : 'static' });

    </script>