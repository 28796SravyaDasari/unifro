    var resizefunc = [];

    var Sidemenu = function() {
        this.$body = $("body"),
        this.$openLeftBtn = $(".open-left"),
        this.$menuItem = $("#sidebar-menu a")
    };

    Sidemenu.prototype.openLeftBar = function()
    {
        $("#wrapper").toggleClass("enlarged");
        $("#wrapper").addClass("forced");

        if($("#wrapper").hasClass("enlarged"))
        {
            $("body").removeClass("enlarged");
            //$(".left ul").removeAttr("style");
        }
        else
        {
            $("body").addClass("enlarged");
            //$(".subdrop").siblings("ul:first").show();
        }

        if(!$("#wrapper").hasClass("enlarged") && $("body").hasClass("smallscreen"))
        {
            $('<div class="modal-backdrop" onclick="HideSideBar()"></div>').appendTo(document.body);
        }

        toggle_slimscroll(".slimscrollleft");
        $("body").trigger("resize");
    },
    //menu item click
    Sidemenu.prototype.menuItemClick = function(e)
    {
        if(!$("#wrapper").hasClass("enlarged"))
        {
            if($(this).parent().hasClass("has_sub"))
            {
                e.preventDefault();
            }
            if(!$(this).hasClass("subdrop"))
            {
                // hide any open menus and remove all other classes
                $("ul",$(this).parents("ul:first")).slideUp(350);
                $("a",$(this).parents("ul:first")).removeClass("subdrop");
                $("#sidebar-menu .pull-right i").removeClass("md-remove").addClass("md-add");

                // open our new menu and add the open class
                $(this).next("ul").slideDown(350);
                $(this).addClass("subdrop");
                $(".pull-right i",$(this).parents(".has_sub:last")).removeClass("md-add").addClass("md-remove");
                $(".pull-right i",$(this).siblings("ul")).removeClass("md-remove").addClass("md-add");
            }
            else if($(this).hasClass("subdrop"))
            {
                $(this).removeClass("subdrop");
                $(this).next("ul").slideUp(350);
                $(".pull-right i",$(this).parent()).removeClass("md-remove").addClass("md-add");
            }
        }
    },

    //init sidemenu
    Sidemenu.prototype.init = function()
    {
        var $this  = this;
        //bind on click
        $(".open-left").click(function(e)
        {
            e.stopPropagation();
            $this.openLeftBar();
        });

        // LEFT SIDE MAIN NAVIGATION
        $this.$menuItem.on('click', $this.menuItemClick);
    };

    var FullScreen = function() {
        this.$body = $("body"),
        this.$fullscreenBtn = $("#btn-fullscreen")
    };

    //turn on full screen
    // Thanks to http://davidwalsh.name/fullscreen
    FullScreen.prototype.launchFullscreen  = function(element) {
      if(element.requestFullscreen) {
        element.requestFullscreen();
      } else if(element.mozRequestFullScreen) {
        element.mozRequestFullScreen();
      } else if(element.webkitRequestFullscreen) {
        element.webkitRequestFullscreen();
      } else if(element.msRequestFullscreen) {
        element.msRequestFullscreen();
      }
    },
    FullScreen.prototype.exitFullscreen = function() {
      if(document.exitFullscreen) {
        document.exitFullscreen();
      } else if(document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
      } else if(document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
      }
    },
    //toggle screen
    FullScreen.prototype.toggle_fullscreen  = function() {
      var $this = this;
      var fullscreenEnabled = document.fullscreenEnabled || document.mozFullScreenEnabled || document.webkitFullscreenEnabled;
      if(fullscreenEnabled) {
        if(!document.fullscreenElement && !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
          $this.launchFullscreen(document.documentElement);
        } else{
          $this.exitFullscreen();
        }
      }
    },
    //init sidemenu
    FullScreen.prototype.init = function() {
      var $this  = this;
      //bind
      $this.$fullscreenBtn.on('click', function() {
        $this.toggle_fullscreen();
      });
    };

    var App = function() {
        this.pageScrollElement = "html, body",
        this.$body = $("body")
    };

    //on doc load
    App.prototype.onDocReady = function(e) {
      FastClick.attach(document.body);
      resizefunc.push("initscrolls");
      resizefunc.push("changeptype");

      //RUN RESIZE ITEMS
      $(window).resize(debounce(resizeitems,100));
      $("body").trigger("resize");

    },
    //initilizing
    App.prototype.init = function() {
        var $this = this;
        //document load initialization
        $(document).ready($this.onDocReady);
        //init side bar - left
        $.Sidemenu.init();
        //init fullscreen
        $.FullScreen.init();
    },

$(document).ready(function()
{
    // NAVIGATION HIGHLIGHT & OPEN PARENT
    $("#sidebar-menu ul li.has_sub a.active").parents('ul').show();

    //init Sidemenu
    $.Sidemenu = new Sidemenu, $.Sidemenu.Constructor = Sidemenu

    //init FullScreen
    $.FullScreen = new FullScreen, $.FullScreen.Constructor = FullScreen

    $.App = new App, $.App.Constructor = App

    $.App.init();
});     // END OF DOCUMENT READY

/* ------------ some utility functions ----------------------- */

var w,h,dw,dh;
var changeptype = function(){
    w = $(window).width();
    h = $(window).height();
    dw = $(document).width();
    dh = $(document).height();

    if(!$("#wrapper").hasClass("forced"))
    {
        if(w > 990)
        {
            $("body").removeClass("smallscreen").addClass("widescreen");
            $("#wrapper").removeClass("enlarged");
        }
        else
        {
            $("body").removeClass("widescreen").addClass("smallscreen");
            $("#wrapper").addClass("enlarged");
            //$(".left ul").removeAttr("style");
        }

        if($("#wrapper").hasClass("enlarged"))
        {
            $("body").removeClass("enlarged");
            //$(".left ul").removeAttr("style");
        }
        else
        {
            $("body").addClass("enlarged");
            //$(".subdrop").siblings("ul:first").show();
        }
    }
    else
    {
        if(w > 990)
        {
            $("body").removeClass("smallscreen").addClass("widescreen");
            $('.modal-backdrop').remove();
        }
        else
        {
            $("body").removeClass("widescreen").addClass("smallscreen");
            $("#wrapper").removeClass("forced");
        }
    }
    toggle_slimscroll(".slimscrollleft");
}


var debounce = function(func, wait, immediate) {
  var timeout, result;
  return function() {
    var context = this, args = arguments;
    var later = function() {
      timeout = null;
      if (!immediate) result = func.apply(context, args);
    };
    var callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) result = func.apply(context, args);
    return result;
  };
}

function resizeitems(){
  if($.isArray(resizefunc)){
    for (i = 0; i < resizefunc.length; i++) {
        window[resizefunc[i]]();
    }
  }
}

function initscrolls(){

      //SLIM SCROLL
      $('.slimscroller').slimscroll({
        height: 'auto',
        size: "5px"
      });

      $('.slimscrollleft').slimScroll({
          height: 'auto',
          position: 'right',
          size: "5px",
          color: '#dcdcdc',
          wheelStep: 5
      });

}
function toggle_slimscroll(item){
    if($("#wrapper").hasClass("enlarged")){
      $(item).css("overflow","inherit").parent().css("overflow","inherit");
      $(item).siblings(".slimScrollBar").css("visibility","hidden");
    }else{
      $(item).css("overflow","hidden").parent().css("overflow","hidden");
      $(item).siblings(".slimScrollBar").css("visibility","visible");
    }
}
function HideSideBar()
{
    if($(".modal-backdrop").length)
    {
        $(".modal-backdrop").remove();

        $("#wrapper").toggleClass("enlarged");
        $("#wrapper").addClass("forced");

        if($("#wrapper").hasClass("enlarged"))
        {
            $(".left ul").removeAttr("style");
        }
        else
        {
            $(".subdrop").siblings("ul:first").show();
        }
    }
}

function ToggleCheckboxSiblings(t)
{
    $(t).closest('li').find(':checkbox').prop('checked', t.checked);

    var sibs = false;

    $(t).closest('ul').children('li').each(function ()
    {
        if($(this).find(':checkbox').prop('checked'))
            sibs=true;
        else
        {
            $('.CheckAll').prop('checked', false);
        }
    });

    if(!sibs)
    {
        if($(t).closest('ul').prev('div').find('input[type=checkbox]').length)
        {
            $(t).closest('ul').prev('div').find('input[type=checkbox]').prop('checked', false);
            ToggleCheckboxSiblings($(t).closest('ul').prev('div').find('input[type=checkbox]'));
        }
    }
    else
    {
        $(t).parents('ul').prev('div').find('input[type=checkbox]:not(.CheckAll)').prop('checked', sibs);
    }
}

function SavePermissions(t)
{
    AjaxResponse = AjaxFormSubmit(t, 'AccessSettingsForm');

    $.when(AjaxResponse).done(function (response)
    {
        response = $.parseJSON(response);

        if(response.status == 'success')
        {
            $('.access-settings-menu').addClass('sleep');
            AlertBox(response.message, 'success');
        }
        else if(response.status == 'login')
        {
            AlertBox('Oops! Your session has expired! Please login again.', 'error', function(){ location.href = response.redirect } );
        }
        else
        {
            AlertBox(response.message);
        }
    });
}

function ChangeStatus(t, trigger)
{
    var trigger = typeof trigger != 'undefined' ? trigger : '';
    var id = $(t).data('id');
    var opt = $(t).data('value');
    var status = $(t).prop('checked');

	if(status)
    {
        if(opt == "Subscription")
            var message = 'Are you sure to Subscribe this email?';
        else
            var message = 'Are you sure to activate this '+ opt +'?';

        status = 1;
    }
	else
    {
        if(opt == "Subscription")
            var message = 'Are you sure to UnSubscribe this email?';
        else
            var message = 'Are you sure to deactivate this '+ opt +'?';
        
        status = 0;
    }

    if(trigger)
    {
        $.post( "/admin/ajax/update-status/", { option: opt, status: status, id: id })
        .done(function( response )
        {
            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    return true;
        	}
            else if(data.status == 'login')
        	{
        	    AlertBox('Oops! You session has expired! Please login again.', 'error', function(){ window.location = data.redirect } )
        	}
        	else
        	{
                $(t).prop('checked') ? $(t).prop('checked', false) : $(t).prop('checked', true);
    			AlertBox(data.message);
        	}
        });
    }
    else
    {
        ConfirmBox(message, function() { ChangeStatus(t, true) }, function() { $(t).prop('checked') ? $(t).prop('checked', false) : $(t).prop('checked', true) } );
    }
}

function DeleteSelected(t, trigger)
{
    var trigger = typeof trigger != 'undefined' ? trigger : '';

    var formID = $(t).attr('data-id');

    if(!$("#"+formID+" tr > td:first-child input:checkbox:checked").length)
    {
        AlertBox("Please select the records to be deleted");
        return false;
    }

    if(trigger)
    {
        AjaxResponse = AjaxFormSubmit(t, formID);

        $.when(AjaxResponse).done(function(response)
        {
            response = $.parseJSON(response);

            if(response.status == 'success')
            {
                $('#'+formID).find(response.rows).fadeOut().remove();
                AlertBox(response.message, 'success');
            }
            else if(response.status == 'login')
            {
                AlertBox('Oops! Your session has expired! Please login again.', 'error', function(){ location.href = response.redirect } );
            }
            else
            {
                AlertBox(response.message);
            }
        });
    }
    else
    {
        ConfirmBox('Are you sure to delete?', function() { DeleteSelected(t, true) }, function() { $(t).prop('checked') ? $(t).prop('checked', false) : $(t).prop('checked', true) } );
    }
}

function SaveCategory(t)
{
    ShowProcessing();

    AjaxResponse = AjaxFormSubmit(t);

    $.when(AjaxResponse).done(function(response)
    {
        response = $.parseJSON(response);

        if(response.status == 'success')
        {
            AlertBox(response.message, 'success', function(){ location.href = response.redirect });
        }
        else if(response.status == 'login')
        {
            AlertBox('Oops! Your session has expired! Please login again.', 'error', function(){ location.href = response.redirect } );
        }
        else if(response.status == 'validation')
    	{
    	    ThrowError(response.error, true);
    	}
        else
        {
            AlertBox(response.message);
        }
    });
}

function DeletejFilerThumbnail(t, trigger)
{
    var trigger = typeof trigger != 'undefined' ? trigger : '';
    var id = $(t).data('id');
    var opt = $(t).data('opt');

    if(trigger)
    {
        $.post( "/admin/ajax/delete/jfiler-thumbnail/", { option: opt, id: id })
        .done(function( response )
        {
            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    $(t).closest(data.element).fadeOut();
        	}
            else if(data.status == 'login')
        	{
        	    AlertBox('Oops! You session has expired! Please login again.', 'error', function(){ window.location = data.redirect } )
        	}
        	else
        	{
    			AlertBox(data.message);
        	}
        });
    }
    else
    {
        ConfirmBox("Are you sure to delete?", function() { DeletejFilerThumbnail(t, true) } );
    }
}

function OpenAddModal(t)
{
    $('#AttributeID').val($(t).attr('data-id'));
    $('#AddModal').find('.modal-title').html($(t).html());
    $('#AddModal').modal({'backdrop': 'static'});
}

function EditElementStyle(t)
{
    $('#StyleID').val($(t).attr('data-id'));
    $('#StyleName').val($(t).attr('data-name'));
    $('#SymbolID').val($(t).attr('data-symbol'));

    var imgSrc = $(t).closest('a').find('.style-img').attr('src');

    $('.style-thumb').find('img').attr('src', imgSrc);
    $('.style-thumb').removeClass('hidden');

    $('#AddModal').find('.modal-title').html('Edit Details');
    $('#AddModal').modal({'backdrop': 'static'});
}

function SetDefault(t)
{
    var id = $(t).attr('data-id');
    var pk = $(t).attr('data-pk');
    var opt = $(t).attr('data-opt');

    $.post( "/admin/ajax/set-default/", { id: id, pk: pk, option: opt })
    .done(function( response )
    {
        data = $.parseJSON(response);

        if(data.status == 'success')
    	{
    	    AlertBox(data.message, 'success', function(){ location.reload() });
    	}
        else if(data.status == 'login')
    	{
    	    AlertBox('Oops! You session has expired! Please login again.', 'error', function(){ window.location = data.redirect } )
    	}
    	else
    	{
			AlertBox(data.message);
    	}
    });
}

function SetDefaultImage(t)
{
    var id = $(t).attr('data-id');
    var pk = $(t).attr('data-pk');
    var opt = $(t).attr('data-opt');

    $.post( "/admin/ajax/set-default/", { id: id, pk: pk, option: opt })
    .done(function( response )
    {
        data = $.parseJSON(response);

        if(data.status == 'success')
    	{
    	    $('.set-default').removeClass('active');
    	    $('.default-pic').removeClass('hidden');

    	    $('.set-default').each(function()
            {
                $(t).closest('figure').find('.set-default').addClass('active');
                $(t).addClass('hidden');
    	    })
    	}
        else if(data.status == 'login')
    	{
    	    AlertBox('Oops! You session has expired! Please login again.', 'error', function(){ window.location = data.redirect } )
    	}
    	else
    	{
			AlertBox(data.message);
    	}
    });
}

function AddProductAttribute(t, save)
{
    var save = typeof save !== 'undefined' ? true : false;

    if(save)
    {
        $(t).addClass('disabled spinner');

        var formData = new FormData($('#AdditionalDetailsForm')[0]);

        $.ajax({
            url: '/admin/ajax/save-additional-details/',
            type: 'POST',
            data: formData,
            async: true,
    		cache: false,
    		contentType: false,
    		processData: false,
        }).done(function(response)
        {
            $(t).removeClass('disabled spinner');

            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    $(t).after(data.response_message);
        	}
            else if(data.status == 'login')
        	{
        	    AlertBox(data.response_message, 'error', function(){ window.location = data.redirect });
        	}
            else if(data.status == 'validation')
        	{
        	    ThrowError(data.error);
        	}
        	else
        	{
                $(t).after(data.response_message);
        	}
        });
    }
    else
    {
        $(t).addClass('disabled spinner');
        var dataTitle = $(t).attr('data-title');

        $.post( "/admin/ajax/additional-details-form/", { option: dataTitle, id: $(t).attr('data-id') })
        .done(function( response )
        {
            $(t).removeClass('disabled spinner');

            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    ShowModal(data.html, dataTitle);
        	}
            else if(data.status == 'login')
        	{
        	    AlertBox(data.response_message, 'error', function(){ window.location = data.redirect });
        	}
        	else
        	{
                AlertBox(data.response_message);
        	}
        });
    }
}

function RemoveOrderProduct(t, remove)
{
    var remove = typeof remove !== 'undefined' ? remove : '';

    if(remove)
    {
        $(t).addClass('disabled spinner');

        $.post( "/admin/ajax/edit-order/", { option: 'remove', OrderDetailsID: $(t).attr('data-id'), ProductID: $(t).attr('data-product') })
        .done(function( response )
        {
            $(t).removeClass('disabled spinner');

            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    $(t).closest('tr').fadeOut();
        	}
            else if(data.status == 'login')
        	{
        	    AlertBox(data.response_message, 'error', function(){ window.location = data.redirect });
        	}
        	else
        	{
                AlertBox(data.response_message);
        	}
        });
    }
    else
    {
        ConfirmBox("Are you sure to remove the product?", function() { RemoveOrderProduct(t, true) } );
    }
}

function UpdateOrderQuantity()
{
    $(".CartForm").submit(function(e)
    {
        e.preventDefault();
        ShowProcessing();

        var t = this;

        AjaxResponse = AjaxFormSubmit(this);

        $.when(AjaxResponse).done(function(response)
        {
            response = $.parseJSON(response);

            if(response.status == 'success')
            {
                AlertBox('Order Updated!', 'success', function(){ location.reload(); });
            }
            else if(response.status == 'login')
            {
                AlertBox('Oops! Your session has expired! Please login again.', 'error', function(){ location.href = response.redirect } );
            }
            else
            {
                AlertBox(response.response_message);
            }
        });
    });
}

function EditShippingAddress(save)
{
    var save = typeof save !== 'undefined' ? true : false;

    if(save)
    {
        var formData = new FormData($('#ShippingAddressForm')[0]);

        $.ajax({
            url: '/admin/ajax/save-additional-details/',
            type: 'POST',
            data: formData,
            async: true,
    		cache: false,
    		contentType: false,
    		processData: false,
        }).done(function(response)
        {
            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    AlertBox(data.response_message);
        	}
            else if(data.status == 'login')
        	{
        	    AlertBox(data.response_message, 'error', function(){ window.location = data.redirect });
        	}
            else if(data.status == 'validation')
        	{
        	    ThrowError(data.error);
        	}
        	else
        	{
                $(t).after(data.response_message);
        	}
        });
    }
    else
    {
        $.post( "/admin/ajax/update-shipping-address/", { option: 'form', id: $(t).attr('data-id') })
        .done(function( response )
        {
            $(t).removeClass('disabled spinner');

            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    ShowModal(data.html, dataTitle);
        	}
            else if(data.status == 'login')
        	{
        	    AlertBox(data.response_message, 'error', function(){ window.location = data.redirect });
        	}
        	else
        	{
                AlertBox(data.response_message);
        	}
        });
    }
}

function DeleteOrder(t, remove)
{
    var remove = typeof remove !== 'undefined' ? remove : '';

    if(remove)
    {
        ShowProcessing('Deleting the order...')

        $.post( "/admin/ajax/delete-order/", { id: $(t).attr('data-id') })
        .done(function( response )
        {
            CloseProcessing();

            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    AlertBox(data.response_message, 'success', function(){ window.location = data.redirect });
        	}
            else if(data.status == 'login')
        	{
        	    AlertBox(data.response_message, 'error', function(){ window.location = data.redirect });
        	}
        	else
        	{
                AlertBox(data.response_message);
        	}
        });
    }
    else
    {
        ConfirmBox('<div class="text-danger font18">Are you sure to delete the order? <br>You won\'t be able to get it back.</div>', function() { DeleteOrder(t, true) } );
    }
}

function DeleteCustomerOrder(t, remove)
{
    var remove = typeof remove !== 'undefined' ? remove : '';

    if(remove)
    {
        ShowProcessing('Deleting the order...')

        $.post( "/admin/ajax/customer/delete-order/", { id: $(t).attr('data-id') })
        .done(function( response )
        {
            CloseProcessing();

            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    AlertBox(data.response_message, 'success', function(){ window.location = data.redirect });
        	}
            else if(data.status == 'login')
        	{
        	    AlertBox(data.response_message, 'error', function(){ window.location = data.redirect });
        	}
        	else
        	{
                AlertBox(data.response_message);
        	}
        });
    }
    else
    {
        ConfirmBox('<div class="text-danger font18">Are you sure to delete the order? <br>You won\'t be able to get it back.</div>', function() { DeleteCustomerOrder(t, true) } );
    }
}

function DeleteAccount(t, remove)
{
    var remove = typeof remove !== 'undefined' ? remove : '';

    if(remove)
    {
        ShowProcessing('Deleting the account...')

        $.post( "/admin/ajax/delete-account/", { id: $(t).attr('data-id') })
        .done(function( response )
        {
            CloseProcessing();

            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    AlertBox(data.response_message, 'success', function(){ window.location = data.redirect });
        	}
            else if(data.status == 'login')
        	{
        	    AlertBox(data.response_message, 'error', function(){ window.location = data.redirect });
        	}
        	else
        	{
                AlertBox(data.response_message);
        	}
        });
    }
    else
    {
        ConfirmBox('<div class="text-danger font18">Are you sure to delete the account? <br>All the orders placed, reviews & ratings of the customer would be deleted.</div>', function() { DeleteAccount(t, true) } );
    }
}

function DeleteReview(t, remove)
{
    var remove = typeof remove !== 'undefined' ? remove : '';

    if(remove)
    {
        ShowProcessing('Deleting review...')

        $.post( "/admin/ajax/delete-review/", { id: $(t).attr('data-id') })
        .done(function( response )
        {
            CloseProcessing();

            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    //AlertBox(data.response_message, 'success', function(){ window.location = data.redirect });
                $(t).closest('tr').fadeOut().remove();
        	}
            else if(data.status == 'login')
        	{
        	    AlertBox(data.response_message, 'error', function(){ window.location = data.redirect });
        	}
        	else
        	{
                AlertBox(data.response_message);
        	}
        });
    }
    else
    {
        ConfirmBox('<div class="text-danger font18">Are you sure to delete this review?</div>', function() { DeleteReview(t, true) } );
    }
}

function ToggleMaxDiscount(t)
{
    $('input[name="MaxDiscount"]').val(0);

    if(t.value == 'Rs')
    {
        $('#MaxDiscount').hide();
    }
    else
    {
        $('#MaxDiscount').show();
    }
}

function AddProduct2Combo(t)
{
    var t = typeof t !== 'undefined' ? t : '';

    var lastRow = $('#ComboProducts li:last-child');

    if(t)
    {
        $(t).closest('li').remove();

        var TotalRows = $('#ComboProducts li').length;
        if(TotalRows == 2)
        {
            $('.btn-delete').addClass('hidden');
        }
        UpdateComboTotal();
    }
    else
    {
        $(lastRow).clone().insertAfter(lastRow).find(".btn-delete").removeClass('hidden');
        var x = $('#ComboProducts li').length - 1;

        $('#ComboProducts li:last-child').find(":input").each(function()
        {
            var name = $(this).attr("name");
            if(typeof name !== 'undefined')
            {
                newname = name.split("[");
                $(this).attr('name', newname[0]+'['+x+']');

                if(newname[0] != 'WeightUnit')
                    $(this).val("");
            }
        });

        $('#ComboProducts input[type=number]').on('change', function()
        {
            UpdateComboTotal()
        })

        $('.search').autocomplete({
            serviceUrl: '/admin/ajax/search-product/?combo=n',
            paramName: 'term',
            groupBy: 'category',
            minChars: 2,
            onSelect: function (suggestion)
            {
                if(suggestion.data.ProductID != 'null')
                {
                    $(this).closest('li').find('[name^="ComboProductID"]').val(suggestion.data.ProductID);
                    $(this).closest('li').find('[name^="Rate"]').val(suggestion.data.Rate);
                    $(this).closest('li').find('[name^="Weight"]').val(suggestion.data.Weight);
                    $(this).closest('li').find('[name^="TaxRate"]').val(suggestion.data.TaxRate);
                    if(suggestion.data.WeightUnit != '')
                    {
                        $(this).closest('li').find('[name^="WeightUnit"]').val(suggestion.data.WeightUnit);
                    }
                    else
                    {
                        var val = $(this).closest('li').find('[name^="WeightUnit"] option:first').val();
                        $(this).closest('li').find('[name^="WeightUnit"]').val(val);
                    }
                    UpdateComboTotal();
                }
            }
        });
    }


}

function UpdateComboTotal()
{
    $('#ComboProducts li:first-child').find('input[data-name]').each(function()
    {
        var sum  = 0;
        var name = $(this).attr('data-name');

        $('#ComboProducts li').find('input[data-name='+name+']').each(function()
        {
            val = $(this).val();

            if($.isNumeric(val))
                sum+= parseFloat(val);
        });

        $('.product-total').find('[data-name='+name+'] input').val( sum.toFixed(2) );
    });
}