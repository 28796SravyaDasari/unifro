    <!-- jQuery  -->
    <script src="/js/jquery-2.1.4.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/admin/js/detect.js"></script>
    <script src="/admin/js/fastclick.js"></script>
    <script src="/admin/js/jquery.slimscroll.js"></script>
    <script src="/js/wow.min.js"></script>
    <script src="/admin/js/jquery.scrollTo.min.js"></script>
    <script src="/js/selectric.js"></script>
    <script src="/js/footable.min.js"></script>
    <script src="/js/jquery.filer.min.js"></script>
    <script src="/js/flatpickr.min.js"></script>
    <script src="/js/jquery.bootstrap-responsive-tabs.min.js"></script>

    <script src="/js/common.js?v=<?=time()?>"></script>
    <script src="/admin/js/admin-js.js?v=<?=time()?>"></script>

    <script>
    //START OF DOCUMENT READY FUNTION
    $(document).ready(function(e)
    {
        $('.table').footable();

        $('.selectric').selectric();
        $('.selectric').css({ 'opacity' : '1'});
        $('.selectric-is-native .selectric').css({ 'opacity' : '0'});

        $('.help-icon').tooltip();
        $('[data-toggle="tooltip"]').tooltip();

        $('input[name=CheckAll]').on('change', function(){
            $('input[data-primary]').prop('checked', this.checked);
        });

        if($('.action-list').length)
        {
            $(".action-list > a").click(function (e)
            {
                if ($(this).hasClass('active'))
                {
                    $(this).parent().find(".sub-menu").hide();
                    $(this).removeClass("active");
                }
                else
                {
                    $(this).addClass("active");
                    $(this).parent().find(".sub-menu").show();
                }
                e.stopPropagation();
            });

            $("html").click(function ()
            {
                $(".action-list > a.active").next('.sub-menu').hide();
                $(".action-list > a.active").removeClass("active");
            });
        }
        
        // OPTIONS FOR JFILER
        options = {
                    showThumbs: true,
                    limit: 1,
                    maxSize: null,
                    changeInput: true,
                    templates: {
                                    box: '<ul class="jFiler-item-list"></ul>',
                                    item: '<li class="jFiler-item">\
                                                <div class="jFiler-item-container">\
                                                    <div class="jFiler-item-inner">\
                                                        <div class="jFiler-item-thumb">\
                                                            {{fi-image}}\
                                                        </div>\
                                                        <div class="jFiler-item-assets jFiler-row">\
                                                            <ul class="list-inline pull-left">\
                                                                <li><span class="jFiler-item-others">{{fi-size2}}</span></li>\
                                                            </ul>\
                                                            <ul class="list-inline pull-right">\
                                                                <li><a class="icon-jfi-trash jFiler-item-trash-action"><i class="fa fa-trash" title="Delete"></i></a></li>\
                                                            </ul>\
                                                        </div>\
                                                    </div>\
                                                </div>\
                                            </li>',

                                    removeConfirmation: true,
                                    _selectors: {
                                                    list: '.jFiler-item-list',
                                                    item: '.jFiler-item',
                                                    remove: '.jFiler-item-trash-action',
                                                }
                                },
                    };

    }); //END OF DOCUMENT READY
    </script>