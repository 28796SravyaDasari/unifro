$(document).ready(function(){
    $('textarea, .CharacterCount').on('keyup', function(e)
    {
        // MAXLENGTH
        var ShowCounter = $(this).attr('ShowCounter');
        if(ShowCounter > 0)
        {
            $(this).next('.character-counter').remove();

            var limit = ShowCounter;
            var text_length = this.value.length;
            var text = this.value;

            if(this.value.length == limit)
            {
                e.preventDefault();
            }
            else if (this.value.length > limit)
            {
                this.value = this.value.substring(0, limit);
                text_length = this.value.length;
            }

            if(text_length > 0 && typeof ShowCounter !== typeof undefined)
                $(this).after('<span class="character-counter" style="float: right; font-size: 12px; ">' + text_length + '/' + limit +'</span>');
            else
                $(this).next('.character-counter').remove();
        }

        // AUTO SIZE
        var attr = $(this).attr('autosize');
        if(typeof attr !== typeof undefined && attr !== false)
        {
            $(this).autosize();
        }
    });

});

var delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();

function StopNonInt(e, AllowNegative, AllowDecimal)
{
	AllowNegative = typeof AllowNegative !== 'undefined' ? AllowNegative : false;
	AllowDecimal = typeof AllowDecimal !== 'undefined' ? AllowDecimal : false;
	var r=e.which?e.which:event.keyCode;
	if(AllowNegative && AllowDecimal)
	{
		return r>31&&(48>r||r>57)&&45!=r&&46!=r?!1:void 0
	}
	else if(AllowNegative)
	{
		return r>31&&(48>r||r>57)&&45!=r?!1:void 0
	}
	else if(AllowDecimal)
	{
		return r>31&&(48>r||r>57)&&46!=r?!1:void 0
	}
	else
	{
		return r>31&&(48>r||r>57)?!1:void 0
	}
}

function AlertBox(message, type, fn)
{
	fn = typeof fn !== 'undefined' ? fn : '';		//	ANY 'ACTION' IF 'CANCEL' IS SELECTED BY USER
	type = typeof type !== 'undefined' ? type : 'error';

    if(!$('#AlertBox').length)
    {
        var alertBoxHTML = '<div class="modal fade" id="AlertBox" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-body" style="text-align: center"></div><div class="modal-footer" style="border: none; text-align: center"><button autofocus type="button" class="btn btn-default" data-dismiss="modal">OK</button></div></div></div></div>';
        $('body').append(alertBoxHTML);
    }

    message = type == 'success' ? '<div class="alertbox-success">'+ message +'</div>' : '<div class="alertbox-error">'+ message +'</div>';

	$('#AlertBox').find('.modal-body').html(message);
    $("#AlertBox").modal({'backdrop': 'static'});
    $('#AlertBox').on('hidden.bs.modal', function (e)
    {
        $('#AlertBox').remove();

        if(fn != '')
		{
		    fn();
		}
    });
}

function ClosePopup(id)
{
	id = typeof id !== 'undefined' ? id : '';
	if(id != '')
	{
		document.getElementById(id).style.display = 'none';
	}
	document.getElementById("dim").style.display = 'none';
}

function ConfirmBox(message, fn, cancelFn, closeBox)
{
    fn = typeof fn !== 'undefined' ? fn : '';
	cancelFn = typeof cancelFn !== 'undefined' ? cancelFn : '';
	closeBox = typeof closeBox !== 'undefined' ? closeBox : 'y';

    if(!$('#ConfirmDiv').length)
    {
        var confirmBoxHTML = '<div class="modal fade" id="ConfirmDiv" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-body" style="text-align: center"></div><div class="modal-footer" style="border: none; text-align: center"><button type="button" class="btn btn-success" id="ConfirmProceed">YES</button>&nbsp;&nbsp;<button type="button" class="btn btn-danger" id="ConfirmProceedCancel">NO</button></div></div></div></div>';
        $('body').append(confirmBoxHTML);
    }

    $('#ConfirmDiv').find('.modal-body').html(message);
    $("#ConfirmDiv").modal({'backdrop': 'static'});
	$('#ConfirmProceed').click(function(){
											if(closeBox == 'y')
											{
											   $('#ConfirmDiv').modal('hide');
                                               $('#ConfirmDiv').on('hidden.bs.modal', function (e)
                                               {
                                                    $('#ConfirmDiv').remove();
                                               });
											}
                                            if(fn != '')
                                    		{
                                    		    fn();
                                    		}
									});

	//	NOW LETS SETUP 'CANCEL' BUTTON
	$('#ConfirmProceedCancel').click(function(){
												$('#ConfirmDiv').modal('hide');
                                                $('#ConfirmDiv').on('hidden.bs.modal', function (e)
                                                {
                                                    $('#ConfirmDiv').remove();
                                                    if(cancelFn != '')
                                            		{
                                            		    cancelFn();
                                            		}
                                                });
									});
}

function CustomBox(settings)
{
    fn = typeof fn !== 'undefined' ? fn : '';
	cancelFn = typeof cancelFn !== 'undefined' ? cancelFn : '';
	closeBox = typeof closeBox !== 'undefined' ? closeBox : 'y';

    if(!$('#ConfirmDiv').length)
    {
        var confirmBoxHTML = '<div class="modal fade" id="ConfirmDiv" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-body" style="text-align: center"></div><div class="modal-footer" style="border: none; text-align: center"><button type="button" class="btn btn-success" id="ConfirmProceed">'+ settings.btn1Label +'</button>&nbsp;&nbsp;<button type="button" class="btn btn-danger" id="ConfirmProceedCancel">'+ settings.btn2Label +'</button></div></div></div></div>';
        $('body').append(confirmBoxHTML);
    }

    $('#ConfirmDiv').find('.modal-body').html(settings.message);
    $("#ConfirmDiv").modal({'backdrop': 'static'});
	$('#ConfirmProceed').click(function(){
											if(closeBox == 'y')
											{
											   $('#ConfirmDiv').modal('hide');
                                               $('#ConfirmDiv').on('hidden.bs.modal', function (e)
                                               {
                                                    $('#ConfirmDiv').remove();
                                               });
											}
                                            if(settings.btn1fn != '')
                                    		{
                                    		    settings.btn1fn();
                                    		}
									});

	//	NOW LETS SETUP 'CANCEL' BUTTON
	$('#ConfirmProceedCancel').click(function(){
												$('#ConfirmDiv').modal('hide');
                                                $('#ConfirmDiv').on('hidden.bs.modal', function (e)
                                                {
                                                    $('#ConfirmDiv').remove();
                                                    if(settings.btn2fn != '')
                                            		{
                                            		    settings.btn2fn();
                                            		}
                                                });
									});
}

function CloseConfirmBox()
{
	$('#ConfirmDiv').hide();
	$('.modal-backdrop').remove();
	$('#ConfirmDiv').find('.modal-body').html('');
}

function ShowProcessing(message)
{
    if(!$('#HolderDiv').length)
    {
        var holderDivHTML = '<DIV class="modal processingModal" id="HolderDiv"><div class="modal-dialog"></div></DIV>';
        $('body').append(holderDivHTML);
    }
    DimToggle(true);

	message = typeof message !== 'undefined' ? message : '';

	$('#HolderDiv').find('.modal-dialog').html('<div class="sk-spinner sk-spinner-fading-circle"><div class="sk-circle1 sk-circle"></div><div class="sk-circle2 sk-circle"></div><div class="sk-circle3 sk-circle"></div><div class="sk-circle4 sk-circle"></div><div class="sk-circle5 sk-circle"></div><div class="sk-circle6 sk-circle"></div><div class="sk-circle7 sk-circle"></div><div class="sk-circle8 sk-circle"></div><div class="sk-circle9 sk-circle"></div><div class="sk-circle10 sk-circle"></div><div class="sk-circle11 sk-circle"></div><div class="sk-circle12 sk-circle"></div></div>' + (message != '' ? message : 'Please wait...') +'</SPAN>');
	$('#HolderDiv').show();
}

function DimToggle(opt, fn)
{
    fn = typeof fn !== 'undefined' ? fn : '';		//	ANY 'ACTION' IF USER CLICKS ON THE BACKDROP
    opt = typeof opt !== 'undefined' ? opt : ($('.modal-backdrop').length ? false : true);
    if(opt)
    {
        //  Make sure remove the previous dim before appending new
        if($('.modal-backdrop').length)
        {
            $('.modal-backdrop').remove();
        }

        var boxHTML = '<DIV class="modal-backdrop in"></DIV>';
        $('body').append(boxHTML);
        $('body').addClass('modal-open');
    }
    else
    {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    }

    $('.modal-backdrop').click(function(){
        if(fn != '')
        {
    	    fn();
        }
    });
}

function CloseProcessing()
{
	$('#HolderDiv').remove();
	DimToggle(false);
}

function ToggleDiv(DivID, ClassName)
{
    var ClassName = typeof ClassName !== 'undefined' ? ClassName : 'slideInDiv';

    $('#'+DivID).toggleClass(ClassName);
    if($('.modal-backdrop').length)
    {
        $('.modal-backdrop').fadeOut(500).remove();
    }
    else
    {
        var boxHTML = '<DIV class="modal-backdrop in"></DIV>';
        $('body').append(boxHTML);
    }
}

function ThrowError(obj, after)
{
    var after = typeof after !== 'undefined' ? after : false;

    $('.error').remove();

    $.each(obj, function(index, value)
    {
        var input = $(':input[name='+ index +']');

        if($('input[name=' + index + ']').attr('type') == 'file' && typeof $('input[name=' + index + ']').attr('type') !== 'undefined')
        {
            input = $('input[name=' + index + ']').parent();
            input.after('<div class="error text-danger"><i class="fa fa-exclamation-triangle"></i>&nbsp; '+ obj[index] +'</div>');
        }
        else if($('input[name="' + index + '[]"]').attr('type') == 'checkbox' && typeof $('input[name="' + index + '[]"]').attr('type') !== 'undefined')
        {
            input = $('input[name="' + index + '[]"]').parent();
            input.after('<div class="error text-danger"><i class="fa fa-exclamation-triangle"></i>&nbsp; '+ obj[index] +'</div>');
        }
        else if($('input[name="' + index + '"]').attr('type') == 'radio')
        {
            input = $('input[name="' + index + '"]').closest('.custom-radiobutton');
            input.append('<div class="error text-danger"><i class="fa fa-exclamation-triangle"></i>&nbsp; '+ obj[index] +'</div>');
        }
        else if($(':input[name="' + index + '[]"]').attr('class') == 'selectric')
        {
            input = $(':input[name="' + index + '[]"]').parent().parent();
            input.after('<div class="error text-danger"><i class="fa fa-exclamation-triangle"></i>&nbsp; '+ obj[index] +'</div>');
        }
        else if(input.attr('class') == 'selectric')
        {
            input = $(input).parent().parent();
            input.after('<div class="error text-danger"><i class="fa fa-exclamation-triangle"></i>&nbsp; '+ obj[index] +'</div>');
        }
        else
        {
            if($.type(value) == 'object' || $.type(value) == 'array')
            {
                $.each(value, function(i,v)
                {
                    if($.type(v) == 'object')
                    {
                        $.each(v, function(key, val)
                        {
                            input = $('input[name="' + index + '['+ i +']['+ key +']"]');
                            input.after('<div class="error text-danger"><i class="fa fa-exclamation-triangle"></i>&nbsp; '+ val +'</div>');
                        });
                    }
                    else
                    {
                        input = $('input[name="' + index + '['+ i +']"]');
                        if(input.closest('.input-group').length > 0)
                        {
                            input.closest('.input-group').after('<div class="error text-danger"><i class="fa fa-exclamation-triangle"></i>&nbsp; '+ v +'</div>');
                        }
                        else
                        {
                            input.after('<div class="error text-danger"><i class="fa fa-exclamation-triangle"></i>&nbsp; '+ v +'</div>');
                        }
                    }
                });
            }
            else
            {
                if(after)
                {
                    if(input.closest('.input-group').length > 0)
                    {
                        input.closest('.input-group').after('<div class="error text-danger"><i class="fa fa-exclamation-triangle"></i>&nbsp; '+ obj[index] +'</div>');
                    }
                    else
                    {
                        input.after('<div class="error text-danger"><i class="fa fa-exclamation-triangle"></i>&nbsp; '+ obj[index] +'</div>');
                    }
                }
                else
                {
                    input.closest('.form-group').append('<div class="error text-danger"><i class="fa fa-exclamation-triangle"></i>&nbsp; '+ obj[index] +'</div>');

                }
            }
        }

    });
}

function DropParam(sParam)
{
    sParam = typeof sParam !== 'undefined' ? sParam.split(',') : '';
    if(sParam != '')
    {
        if(!$.isArray(sParam))
        {
            sParam[0] = sParam;
        }

        var sPageURL = window.location.search.substring(1);
        var sURLVariables = sPageURL.split('&');
        var params = [];

        for(var i = 0; i < sURLVariables.length; i++)
        {
            var sParameterName = sURLVariables[i].split('=');
            if ($.inArray(sParameterName[0], sParam) < 0)
            {
                params.push(sURLVariables[i]);
            }
        }
        location.href = window.location.pathname + '?' + params.join('&');
    }
    else
    {
        location.href = window.location.pathname;
    }
}

function AjaxFormSubmit(t, formID, spinner)
{
    $('.error').remove();
    
    spinner = typeof spinner !== 'undefined' ? spinner : false;

    if(spinner)
    {
        $(t).addClass('spinner medium');
        $(t).attr('disabled', true);
    }

    var formID = typeof formID !== 'undefined' ? formID : '';

    if(formID != '')
    {
        var $form = $('#' + formID)[0];
        var url = $('#' + formID).attr('action');
    }
    else
    {
        var $form = $(t).closest('form')[0];
        var url = $(t).closest('form').attr('action');
    }

    var formData = new FormData($form);

    if($('#' + formID).data('id'))
    {
        formData.append('id', $('#' + formID).data('id'));
    }
    if($('#' + formID).data('opt') != '')
    {
        formData.append('opt', $('#' + formID).data('opt'));
    }

    AjaxResponse = $.ajax({
        url: url,
        type: "post",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
    });

    AjaxResponse.fail(function (jqXHR, textStatus, errorThrown)
    {
        alert("ERROR: "+ errorThrown);
    });

    // Callback handler that will be called regardless
    // if the request failed or succeeded
    AjaxResponse.always(function ()
    {
        $(t).removeClass('spinner medium');
        $(t).attr('disabled', false);

        if($('#HolderDiv').length)
            CloseProcessing();
    });

    return AjaxResponse;
}
/*******************/

function ShowModal(content, header)
{
    header = typeof header !== 'undefined' ? header : '';

    if(!$('.show-modal').length)
    {
        if(header != '')
        {
            var modalBox = '<div class="modal fade show-modal" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">'+ header +'</h4></div><div class="modal-body"></div></div></div></div>';
        }
        else
        {
            var modalBox = '<div class="modal fade show-modal" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-body"></div></div></div></div>';
        }

        $('body').append(modalBox);
    }

	$('.show-modal').find('.modal-body').html(content);
    $(".show-modal").modal({'backdrop': 'static'});
}

function GetCities(id, selectedCity)
{
    selectedCity = typeof selectedCity !== 'undefined' ? selectedCity : '';

    if (id > 0)
    {
        $.ajax({
                url: '/ajax/cities/',
                data: {id : id, selectedCity : selectedCity},
                success: function(results)
                {
                    $('#City').html(results);
                    if(selectedCity > 0)
                        $('#City').val(selectedCity);
                    else
                        $('#City').prop('selectedIndex', 0);
                    $('#City').selectric('refresh');
                }
        });
    }
}

function ValidateEmail(email)
{
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}