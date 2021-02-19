            <!-- Top Bar Start -->
            <div class="topbar">

                <!-- Button mobile view to collapse sidebar menu -->
                <div class="navbar navbar-default" role="navigation">
                    <div class="container">

                            <div class="menu-bar">
                                <button class="button-menu-mobile open-left">
                                    <i class="ti-menu"></i>
                                </button>
                                <span class="clearfix"></span>
                            </div>

                            <div class="page-title pull-left">
                                 <?=$SelectedPage?>
                            </div>

                            <!--
                            <form role="search" class="navbar-left app-search pull-left hidden-xs">
                                 <input type="text" placeholder="Search..." class="form-control">
                                 <a href=""><i class="ion-ios-search-strong"></i></a>
                            </form>
                            -->


                            <ul class="nav navbar-nav navbar-right pull-right">
                                <li class="hidden-xs">
                                    <a href="#" id="btn-fullscreen"><i class="ti-fullscreen"></i></a>
                                </li>
                                <li class="dropdown">
                                    <a href="" class="dropdown-toggle profile" data-toggle="dropdown" aria-expanded="true"><?=$AdminName?></a>
                                    <ul class="dropdown-menu">
                                        <li><a href="javascript:void(0)"><i class="ti-user mg-r-5"></i> Profile</a></li>
                                        <li><a href="javascript:void(0)"><i class="ti-settings mg-r-5"></i> Settings</a></li>
                                        <li><a href="javascript:void(0)"><i class="ti-lock mg-r-5"></i> Lock screen</a></li>
                                        <li class="divider"></li>
                                        <li><a href="/admin/?logout"><i class="ti-power-off mg-r-5"></i> Logout</a></li>
                                    </ul>
                                </li>
                            </ul>

                        <!--/.nav-collapse -->
                    </div>
                </div>
            </div>
            <!-- Top Bar End -->