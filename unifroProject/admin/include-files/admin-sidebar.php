            <div class="left side-menu">
                <div class="sidebar-inner slimscrollleft">

                    <!-- LOGO -->
                    <div class="logo pd-tb-10">
                        <a href="/admin/"><img src="<?=_LOGO?>" alt="<?=_WebsiteName?> Logo" /></a>
                    </div>

                    <div id="sidebar-menu">
                        <?=MultilevelMenu(0, $AdminPages, $SelectedPage, true);?>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>