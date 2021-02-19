
function CustomDesign()
{
    this.selections = {};
    this.symbols = {};
}

CustomDesign.prototype = {

    LoadDefaults : function(data)
    {
        var t = this;
        this.NextSVG = false;
        this.Sleeves = 'full';
        this.SVGType = data.SVGType;
        this.DefaultStyles = data.Styles;
        this.selections.CategoryID = data.CategoryID;
        this.selections.FabricID = data.FabricID;
        this.FabricPath = GetFabricPath(data.FabricID);
        this.selections.Styles = {};
        this.selections.Price = {};

        this.selections.Price.BasePrice = data.Price.BasePrice;
        this.selections.Price.FabricPrice = data.Price.FabricPrice;
        this.selections.Price.TotalPrice = 0;

        TotalPrice = parseInt(data.Price.BasePrice) + parseInt(data.Price.FabricPrice);
        product.UpdatePrice(TotalPrice);

        $.each(data.Styles, function(index, value)
        {
            $.each(value, function(k,v)
            {
                if(typeof v.SymbolID !== 'undefined')
                {
                    t.SetStyle(k, v.SymbolID, v.StyleID);

                    if(typeof v.ColorID !== 'undefined')
                    {
                        t.SetColor(k, v.ColorID, v.ColorCode, v.SymbolID);
                    }

                    if(t.SVGType == 'y')
                    {

                        if(t.selections.CategoryID == 7 && k == 'Pinafore Variants')
                        {
                            t.ElementFabric(k, '', data.FabricIDSec, GetFabricPath(data.FabricIDSec), v.SymbolID, 'Pinafore Fabric', 168);
                        }
                        else
                        {
                            if(v.StyleType == 'Fabric')
                            {
                                t.ElementFabric(k, '', data.FabricID, t.FabricPath, v.SymbolID, '', v.StyleID);
                            }
                        }
                    }
                }

                $.each(v, function(a,b)
                {
                    if(typeof b.FabricID !== 'undefined')
                    {
                        var path = GetFabricPath(b.FabricID);
                        t.ElementFabric(k, '', b.FabricID, path, b.SymbolID, b.StyleName, b.StyleID);
                    }
                    else if($.isPlainObject( b ))
                    {
                        $.each(b, function(subKey, subArr)
                        {
                            var path = GetFabricPath(subArr.FabricID);
                            t.ElementFabric(k, a, subArr.FabricID, path, subArr.SymbolID, subArr.StyleName, subArr.StyleID);
                        });
                    }
                });
            });
        });

        this.SetMainTextture(this.FabricPath);
        this.LoadSidebarStyles();
    },

    SetMainTextture : function(fabricPath)
    {
        activeSVGID = $('.svg-container').find('li.active').attr('data-id');

        if(this.SVGType == 'y')
        {
            symbols = SVGData[activeSVGID].MainTexture.split(',');
            for(var i = 0; i < symbols.length; i++)
            {
                $('svg').find(symbols[i]+" image").attr("xlink:href", fabricPath);
            }
            /*
            $.each(this.symbols, function(k, v)
            {
                symbols = v.split(',');
                for(var i = 0; i < symbols.length; i++)
                {
                    $('svg').find(symbols[i]+" image").attr("xlink:href", fabricPath);
                }
            });
            */
        }
        else
        {
            // for shirt
            $('li.active').find('svg').find(""+ SVGData[activeSVGID].MainTexture +" image").attr("xlink:href", fabricPath);
        }

        this.FabricPath = fabricPath;
    },

    defineSymbols : function(elementHeading, SymbolIDs)
    {
        this.symbols[elementHeading] = SymbolIDs;
    },

    SetStyle : function(elementHeading, SymbolID, StyleID, SubStyleID)
    {
        $('.sub-styles').hide();
        $('#fabric-wrapper').removeClass('show');
        $('.fabric-filter-holder').html('');

        var SubStyleID = typeof SubStyleID !== 'undefined' ? SubStyleID : '';

        if(SubStyleID != '')
        {
            $('.sub-styles').show();
        }
        else
        {
            $('.sub-styles').hide();

            // For T-Shirt
            if(this.selections.CategoryID == 8)
            {
                if(SymbolID == '#Collar-2' || SymbolID == '#Collar-3' || SymbolID == '#Collar-4' || SymbolID == '#Collar-5')
                {
                    product.ResetElementFabric('Collar Style', 'MoonPatch Fabric', '#Collar-1-moonpatch');
                    product.ResetElementFabric('Collar Style', 'Placket Fabric', '#Collar-1-placket');
                    product.ResetElementFabric('Collar Style', 'Twill Fabric', '#Collar-1-twill');

                    if(typeof this.selections.Styles['MoonPatch Fabric'] !== 'undefined')
                    {
                        delete this.selections.Styles['MoonPatch Fabric'];
                    }
                    if(typeof this.selections.Styles['Placket Fabric'] !== 'undefined')
                    {
                        delete this.selections.Styles['Placket Fabric'];
                    }
                    if(typeof this.selections.Styles['Twill Fabric'] !== 'undefined')
                    {
                        delete this.selections.Styles['Twill Fabric'];
                    }
                }

                if(SymbolID == '#Half-sleeve' || SymbolID == '#Texture-Body-V-cut')
                {
                    $('.li-edge').show();
                }
                if(SymbolID == '#Full-sleeve')
                {
                    $('.li-edge').hide();
                    product.ResetElementFabric('Sleeve Variant', 'Sleeve Edge', '#Half-sleeve-Edge');
                }
            }
            else if(this.selections.CategoryID == 7)    // For Pinafore
            {
                product.ResetElementFabric('Pinfore Variants', 'Pinafore Edge', '#Pinafore-6-collar_edge');

                if(typeof this.selections.Styles['Pinafore Edge'] !== 'undefined')
                {
                    delete this.selections.Styles['Pinafore Edge'];
                }
            }
            else if(this.selections.CategoryID == 18 || this.selections.CategoryID == 3)    // For Men's & Ladies Shirt
            {
                if(SymbolID == '#Sleeve-full')
                {
                    this.Sleeves = 'full';

                    $('.li-edge').show();
                    $('#Cuffs-full').show();
                    $('#Cuffs-three-fourth').hide();

                    $('.slick-slide').find('[data-element="ElbowPatch"]').parent().show();
                }
                else if(SymbolID == '#Sleeve-three-fourth')
                {
                    this.Sleeves = 'three-fourth';

                    $('.li-edge').show();
                    $('#Cuffs-full').hide();
                    $('#Cuffs-three-fourth').show()

                    $('.slick-slide').find('[data-element="ElbowPatch"]').parent().show();
                }
                else if(SymbolID == '#Sleeve-half')
                {
                    this.Sleeves = 'half';

                    product.ResetElementFabric('Sleeve Variant', 'Cuffs', '#Cuffs-full');
                    product.ResetElementFabric('Sleeve Variant', 'Cuffs', '#Cuffs-three-fourth');
                    $('#Cuffs-full,#Cuffs,#Cuffs-three-fourth').hide();

                    $('.slick-slide').find('[data-element="ElbowPatch"]').parent().hide();
                }
                else
                {
                    $('.li-edge').hide();
                }
            }
        }

        if(this.SVGType == 'y')
        {
            symbol_array = this.symbols[elementHeading].split(',');
            for(var i = 0; i < symbol_array.length; i++)
            {
                $(symbol_array[i]).hide();

                if($(symbol_array[i] + "-shadow").length > 0)
                {
                    $(symbol_array[i] + "-shadow").hide();
                }

                if($(symbol_array[i] + "-Group").length > 0)
                {
                    $(symbol_array[i] + "-Group").hide();
                }
            }

            if(SymbolID != '')
            {
                if($(SymbolID + "-shadow").length > 0)
                {
                    $(SymbolID + "-shadow").show();
                }

                if($(SymbolID + "-Group").length > 0)
                {
                    $(SymbolID + "-Group").show();
                }
            }
        }
        else
        {
            $(this.symbols[elementHeading]).hide();
        }

        if(SymbolID != '')
        {
            $(SymbolID).show();
        }

        if(typeof this.selections.Styles[elementHeading] !== 'undefined')
        {
            this.selections.Styles[elementHeading].StyleID = StyleID;
            this.selections.Styles[elementHeading].SymbolID = SymbolID;
        }
        else
        {
            if(SymbolID != '')
            {
                this.selections.Styles[elementHeading] = {StyleID: StyleID, SymbolID: SymbolID};
            }
        }
    },

    LoadSidebarStyles : function(t)
    {
        $('#fabric-wrapper').parent().find('.fabric-filter-holder').html('');
        $('#fabric-wrapper').html('');

        // We call this function on document ready without param. So that initially it will load the Fabrics in the sidebar
        if(typeof t !== 'undefined')
        {
            // Get the Section name of the clicked element so that we can display this Section and hide the rest
            var section = $(t).attr('data-element');

            // data-rel is not set for Fabric because Fabric is same for all the SVGS
            if(typeof $(t).attr('data-rel') !== 'undefined')
            {
                // If data-rel is set then get the SVG ID from the data-rel attribute
                var SVGID = $(t).attr('data-rel');

                // Get the active SVG ID
                var activeSVGID = $('.svg-container').find('li.active').attr('data-id');

                // Check if current SVG ID and the new SVG ID both are same or not
                if(activeSVGID == SVGID)
                {
                    // Do nothing
                }
                else
                {
                    // Store the active XML to SVg Object
                    SVGObjects[activeSVGID] = $('.svg-container li.active').html();

                    // Set the XML content of new SVG
                    $('.svg-container').find('ul').html('<li data-id="'+ SVGID +'" class="active">'+ SVGObjects[SVGID] +'</li>');

                    this.SetDefaultStyles();
                    this.SetMainTextture(this.FabricPath);

                }
            }
        }
        else
        {
            // Assign Fabric so it will load the Fabrics in the sidebar by default
            var section = 'Fabric';
        }

        $('.elements-nav').find('.slide a').removeClass('active');

        $('.elements-nav').find('.slide a').each(function()
        {
            if($(this).attr('data-element') == section)
            {
                $(this).addClass('active');
            }
        });

        $('.fabric-wrapper').removeClass('show');
        $('.color-swatch').removeClass('show');

        $('.sidebar-content').find('section').hide();
        $('#' + section).show();

        if(typeof t !== 'undefined')
            ShowSidebar();
    },

    SetDefaultStyles : function()
    {
        if(!this.NextSVG)
        {
            this.NextSVG = true;

            $.each(this.DefaultStyles, function(index, value)
            {
                $.each(value, function(k,v)
                {
                    if(typeof v.SymbolID !== 'undefined')
                    {
                        if($(v.SymbolID).length)
                        {
                            product.SetStyle(k, v.SymbolID, v.StyleID);

                            if(product.SVGType == 'y')
                            {
                                //alert(JSON.stringify(v));
                                //product.ElementFabric(k, '', v.FabricID, product.selections.FabricPath, v.SymbolID, '', v.StyleID);
                            }
                        }
                    }


                    $.each(v, function(a,b)
                    {
                        if(typeof b.FabricID !== 'undefined')
                        {
                            var path = GetFabricPath(b.FabricID);
                            product.ElementFabric(k, '', b.FabricID, path, b.SymbolID, b.StyleName, b.StyleID);
                        }
                        else if($.isPlainObject( b ))
                        {
                            $.each(b, function(subKey, subArr)
                            {
                                var path = GetFabricPath(subArr.FabricID);
                                product.ElementFabric(k, a, subArr.FabricID, path, subArr.SymbolID, subArr.StyleName, subArr.StyleID);
                            });
                        }
                    });

                });
            });
        }

        if(this.selections.CategoryID == 18 || this.selections.CategoryID == 3)    // For Men's & Ladies Shirt
        {
            if(this.Sleeves == 'half')
            {
                $('#Full-sleeve-back').hide();
                $('#Full-sleeve-back-shadow').hide();
                $('#Three-fourth-sleeve-back').hide();
                $('#Three-fourth-sleeve-back-shadow').hide();
                $('#Elbow-patch').hide();

                $('#Half-sleeve-back').show();
                $('#Half-sleeve-back-shadow').show();
            }
            else if(this.Sleeves == 'three-fourth')
            {
                $('#Full-sleeve-back').hide();
                $('#Full-sleeve-back-shadow').hide();
                $('#Half-sleeve-back').hide();
                $('#Half-sleeve-back-shadow').hide();
                $('#Elbow-patch').hide();

                $('#Three-fourth-sleeve-back').show();
                $('#Three-fourth-sleeve-back-shadow').show();
            }
            else
            {
                $('#Three-fourth-sleeve-back').hide();
                $('#Three-fourth-sleeve-back-shadow').hide();
                $('#Half-sleeve-back').hide();
                $('#Half-sleeve-back-shadow').hide();

                $('#Full-sleeve-back').show();
                $('#Full-sleeve-back-shadow').show();
                $('#Elbow-patch').show();
            }
        }
    },

    MainFabric : function(FabricID, FabricPath)
    {
        this.SetMainTextture(FabricPath);

        // If selected Fabric is not the same Fabric which is currently set
        if(this.selections.FabricID != FabricID)
        {
            // Before assigning the new FabricID, first get the Price of the old Fabric
            var oldFabricPrice = parseInt(FabricsList[this.selections.FabricID].FabricPrice);

            // Deduct Old Fabric Price from the Total Price
            TotalPrice = TotalPrice - parseInt(oldFabricPrice);

            // Assigning the new FabricID
            this.selections.FabricID = FabricID;

            // Get the Fabric Price of the selected Fabric
            this.selections.Price.FabricPrice = parseInt(FabricsList[FabricID].FabricPrice);

            // Add the new Fabric Price to the Total Price
            TotalPrice = TotalPrice + parseInt(this.selections.Price.FabricPrice);

            // Update the Price
            product.UpdatePrice(TotalPrice);
        }
    },

    ElementFabric : function(Heading, SubHeading, FabricID, FabricPath, SymbolID, StyleName, StyleID)
    {
        StyleID = typeof StyleID !== 'undefined' ? StyleID : '';

        if(this.SVGType == 'y')
        {
            if(this.selections.CategoryID == 8 && StyleName == 'Moon Patch')
            {
                $('svg').find("#Collar-1-moonpatch image").attr("xlink:href", FabricPath);
                StyleID = '132-1';
            }
            else if(this.selections.CategoryID == 8 && StyleName == 'TShirt Placket')
            {
                $('svg').find("#Collar-1-placket image").attr("xlink:href", FabricPath);
                StyleID = '132-2';
            }
            else if(this.selections.CategoryID == 8 && StyleName == 'Twill')
            {
                $('svg').find("#Collar-1-twill image").attr("xlink:href", FabricPath);
                StyleID = '132-3';
            }
            else if(this.selections.CategoryID == 8 && SubHeading == 'Sleeve Edge')
            {
                $('svg').find("#Half-sleeve-Edge image").attr("xlink:href", FabricPath);
            }
            else if(this.selections.CategoryID == 8 && SubHeading == 'V-Cut Fabric')
            {
                $('svg').find("#V-cut-Edge image").attr("xlink:href", FabricPath);
            }
            else if(this.selections.CategoryID == 7 && StyleName == 'Edge Fabric')
            {
                $('svg').find("#Pinafore-6-collar_edge image").attr("xlink:href", FabricPath);
                StyleID = '165-4';
            }
            else if((this.selections.CategoryID == 3 || this.selections.CategoryID == 18) && StyleName == 'Collar Contrast')
            {
                $('svg').find("#Col-Contrast image").attr("xlink:href", FabricPath);
            }
            else if((this.selections.CategoryID == 3 || this.selections.CategoryID == 18) && Heading == 'Placket Style')
            {
                $('svg').find("#Placket-Normal-placket image").attr("xlink:href", FabricPath);
                $('svg').find("#Placket-Covered-placket image").attr("xlink:href", FabricPath);
            }
            else if(this.selections.CategoryID == 18 && SubHeading == 'Cuffs')
            {
                $('svg').find("#Cuffs-full image").attr("xlink:href", FabricPath);
                $('svg').find("#Cuffs-three-fourth image").attr("xlink:href", FabricPath);
            }
            else if(this.selections.CategoryID == 3 && SubHeading == 'Cuffs')
            {
                $('svg').find("#Cuffs image").attr("xlink:href", FabricPath);
            }
            else if(this.selections.CategoryID == 5)
            {
                if((Heading == 'Pleat Style' || Heading == 'Side Pocket') && SubHeading != 'Edge Fabric')
                {
                    // Do nothing
                }
                else if(Heading == 'Side Pocket' && SubHeading == 'Edge Fabric')
                {
                    symbol_array = SymbolID.split(',');
                    for(var i = 0; i < symbol_array.length; i++)
                    {
                        $('svg').find(symbol_array[i]+" image").attr("xlink:href", FabricPath);
                    }
                }
            }
            else if(Heading.indexOf("Monogram") < 0)
            {
                symbol_array = this.symbols[Heading].split(',');
                for(var i = 0; i < symbol_array.length; i++)
                {
                    if(symbol_array[i].indexOf("shadow") < 0)
                    {
                        if(symbol_array[i])
                        {
                            $('svg').find(symbol_array[i]+" image").attr("xlink:href", FabricPath);
                        }
                    }
                }
            }
        }
        else
        {
            // for shirt
            symbol_array = SymbolID.split(',');
            for(var i = 0; i < symbol_array.length; i++)
            {
                $('svg').find(symbol_array[i]+" image").attr("xlink:href", FabricPath);
            }
        }

        // IF SELECTED ELEMENT FABRIC IS NOT EQUAL TO THE MAIN FABRIC THEN ADD PRICE
        if(this.selections.FabricID != FabricID)
        {
            var percent = GetStylePercentage(StyleID);
            if(percent > 0)
            {
                product.AddPrice(FabricID, percent, StyleID);
            }
        }
        else
        {
            if(Heading.indexOf("Monogram") < 0)
            {
                if(this.selections.CategoryID == 5 && Heading == 'Side Pocket')
                {
                    // Do nothing
                }
                else
                {
                    if(SubHeading != '')
                    {

                    }
                    product.ResetElementFabric(Heading, SubHeading, SymbolID);
                }
            }
        }

        if(StyleName != '')
        {
            if(SubHeading != '')
            {
                if (typeof this.selections.Styles[Heading][SubHeading] === "undefined")
                {
                    this.selections.Styles[Heading][SubHeading] = {};
                }

                this.selections.Styles[Heading][SubHeading][StyleName] = {FabricID: FabricID, StyleID: StyleID, StyleName: StyleName, SymbolID: SymbolID};
            }
            else
            {
                if (typeof this.selections.Styles[Heading] === "undefined")
                {
                    this.selections.Styles[Heading] = {};
                }
                this.selections.Styles[Heading][StyleName] = {FabricID: FabricID, StyleID: StyleID, StyleName: StyleName, SymbolID: SymbolID};
            }
        }
    },

    ResetElementFabric : function(Heading, SubHeading, SymbolID)
    {
        if(this.SVGType == 'y')
        {
            if(SymbolID != '')
            {
                symbol_array = SymbolID.split(',');
                for(var i = 0; i < symbol_array.length; i++)
                {
                    if(symbol_array[i] != '' && symbol_array[i].indexOf("shadow") < 0)
                    {
                        $('svg').find(symbol_array[i]+" image").attr("xlink:href", this.FabricPath);
                    }
                }
            }
        }
        else
        {
            symbol_array = SymbolID.split(',');
            for(var i = 0; i < symbol_array.length; i++)
            {
                $('svg').find(symbol_array[i]+" image").attr("xlink:href", "");
            }
            delete this.selections.Styles[Heading];
        }

        if(SubHeading != '')
        {
            product.RemovePrice(SubHeading);
        }
    },

    RemoveElement : function(Heading)
    {
        if(this.SVGType == 'y')
        {
            $.each(SubHeadings, function(heading, row)
            {
                if(Heading == heading)
                {
                    $.each(row, function(index, value)
                    {
                        if(value.Attribute == 'xlink' || value.Attribute == 'edge')
                        {
                            if(value.Heading == 'Sleeve Edge')
                            {
                                product.ResetElementFabric('Sleeve Variant', 'Sleeve Edge', '#Half-sleeve-Edge');
                            }
                            else if(value.Heading == 'V-Cut Edge')
                            {
                                product.ResetElementFabric('Bottom Variant', 'V-Cut Edge', '#V-cut-Edge');
                            }
                            else if(value.Heading == 'Cuffs')
                            {
                                product.ResetElementFabric('Sleeve Variant', 'Sleeve Edge', '#Cuffs-full');
                                product.ResetElementFabric('Sleeve Variant', 'Sleeve Edge', '#Cuffs-three-fourth');
                            }
                            else
                            {
                                product.ResetElementFabric(Heading, value.Heading, value.SymbolID);
                            }
                        }
                    });
                }
            });

            symbol_array = this.symbols[Heading].split(',');

            for(var i = 0; i < symbol_array.length; i++)
            {
                $(symbol_array[i]).hide();

                if($(symbol_array[i] + "-shadow").length > 0)
                {
                    $(symbol_array[i] + "-shadow").hide();
                }

                if($(symbol_array[i] + "-Group").length > 0)
                {
                    $(symbol_array[i] + "-Group").hide();
                }
            }
        }
        else
        {
            $(this.symbols[Heading]).hide();
        }


        delete this.selections.Styles[Heading];
    },

    SetColor : function(Heading, ColorID, ColorCode, SymbolID)
    {
        $('svg').find(SymbolID).css({ fill: ColorCode });
        this.selections.Styles[Heading] = {ColorID: ColorID, ColorCode: ColorCode, SymbolID: SymbolID};
    },

    ChooseButton : function(Heading, ButtonID)
    {
        this.selections.Styles[Heading] = {ButtonID: ButtonID};
    },

    AddPrice : function(FabricID, percent, StyleID)
    {
        if(typeof this.selections.Price[StyleID] !== 'undefined')
        {
            TotalPrice = TotalPrice - this.selections.Price[StyleID];
        }

        var price = ( parseInt(FabricsList[FabricID].FabricPrice) * parseInt(percent) ) / 100;
        this.selections.Price[StyleID] = price;

        TotalPrice += price;

        product.UpdatePrice(TotalPrice);
    },

    RemovePrice : function(Heading)
    {
        var t = this;

        if(typeof StyleIDByHeading[Heading] !== 'undefined')
        {
            $.each(StyleIDByHeading[Heading], function(index, value)
            {
                if(typeof t.selections.Price[value] !== 'undefined')
                {
                    TotalPrice = TotalPrice - t.selections.Price[value];
                    product.UpdatePrice(TotalPrice);
                    delete t.selections.Price[value];
                }
            });
        }
    },

    UpdatePrice : function(TotalPrice)
    {
        $('.price').html(TotalPrice);
        this.selections.Price.TotalPrice = TotalPrice;
    },

    FabricPopup: function(Heading, SubHeading, StyleName, SymbolID, StyleID)
    {
        ListStyleFabrics({
            id: this.selections.CategoryID,
            ContentHolder: '#fabric-wrapper',
            Heading: Heading,
            SubHeading: SubHeading,
            StyleName: StyleName,
            SymbolID: SymbolID,
            StyleID: StyleID
        });
        $('#fabric-wrapper').addClass('show');
    },
    ShowEdge: function(Heading, SubHeading, StyleName, SymbolID, StyleID)
    {
        $(SymbolID).show();

        EdgeSymbolID = this.selections.Styles[Heading].SymbolID;
        EdgeStyleID = this.selections.Styles[Heading].StyleID;

        product.SetStyle(Heading, EdgeSymbolID, EdgeStyleID);

        ListStyleFabrics({
            id: this.selections.CategoryID,
            ContentHolder: '#fabric-wrapper',
            Heading: Heading,
            SubHeading: SubHeading,
            StyleName: StyleName,
            SymbolID: SymbolID,
            StyleID: StyleID
        });
        $('#fabric-wrapper').addClass('show');
    },

    toJson : function()
    {
        return JSON.stringify({
            Selections: this.selections,
        });
    },
}

/*----------------------------------------------------
    INITIALIZE THE FUNCTION
------------------------------------------------------*/
var product = new CustomDesign();

/*----------------------------------------------------
    DEFINE ELEMENT SYMBOL IDS
------------------------------------------------------*/

function ResetFilters(id, ContentHolder)
{
    //$('#FilterFabricsForm').find('input[type=checkbox]').prop('checked', false);
    if(ContentHolder == '#content')
    {
        ListFabrics({ id: id, ContentHolder: '#content' });
    }
    else
    {
        ListStyleFabrics({ id: id, ContentHolder: '#fabric-wrapper' });
    }
}

function ListFabrics(params)
{
    var page = 1;
    var parentElement = $(params.ContentHolder).parent();

    if(typeof params.page !== 'undefined')
    {
        //var formData = new FormData($('#FilterFabricsForm')[0]);
        //var formData = new FormData($('.fabric-filter-holder').find('form')[0]);
        var formData = new FormData($(parentElement).find('.fabric-filter-holder').find('form')[0]);
        formData.append('page', params.page);
    }
    else
    {
        var formData = new FormData();
    }

    formData.append('id', params.id);
    formData.append('ContentHolder', params.ContentHolder);

    $.ajax({
        url: '/get/fabric-list/',
        type: 'POST',
        data: formData,
        async: true,
        cache: false,
        contentType: false,
        processData: false,
    })
    .done(function( response )
    {
        CloseProcessing();

        data = $.parseJSON(response);

        if(data.status == 'success')
    	{
            $(params.ContentHolder).html(data.content);
            $(params.ContentHolder).parent().find('.fabric-filter-holder').html(data.filters);

            if(typeof data.FabricList != 'undefined')
            {
                $.extend(FabricsList, data.FabricList);
            }

            if(data.total > 1)
            {
                // init bootpag
                $('.page-selection').bootpag({
                    total: data.total,
                    page: data.page,
                    maxVisible: 5,
                }).on("page", function(event, num)
                {
                    ListFabrics({
                        id: params.id,
                        ContentHolder: params.ContentHolder,
                        page: num,
                    });
                });
            }

            if(data.clear)
            {
                $('.clear-filter').removeClass('hidden');
            }
            else
            {
                $('.clear-filter').addClass('hidden');
            }

            // If noUISlider is not initialized

            if(typeof $(params.ContentHolder).parent().find('.noUi-base').html() == 'undefined')
            {
                var str = params.ContentHolder.replace("#", "");

                var parent = document.getElementById(str).parentElement;
                var priceSlider = parent.getElementsByClassName('price-slider')[0];

                noUiSlider.create(priceSlider, {
                    start: [100, 1000],
                    step: 100,
                    tooltips: true,
                    range: {
                        'min': 100,
                        'max': data.max
                    },
                    format: wNumb({
                        decimals: 0,
                    })
                });

                priceSlider.noUiSlider.on('change', function()
                {
                    $('#price').val(priceSlider.noUiSlider.get());

                    ListFabrics({
                        id: params.id,
                        ContentHolder: params.ContentHolder,
                        page: page,
                    });

                });
            }

            $(parentElement).find('.fabric-filter-holder').find('input[type=checkbox]').on('click', function()
            {
                ListFabrics({
                    id: params.id,
                    ContentHolder: params.ContentHolder,
                    page: page,
                });
            })
    	}
        else
    	{
            AlertBox(data.response_message);
    	}
    });
}

function ListStyleFabrics(params)
{
    var page = 1;
    var parentElement = $(params.ContentHolder).parent();

    if(typeof params.page !== 'undefined')
    {
        //var formData = new FormData($('#FilterFabricsForm')[0]);
        var formData = new FormData($(parentElement).find('.fabric-filter-holder').find('form')[0]);
        formData.append('page', params.page);
    }
    else
    {
        if($(parentElement).find('.fabric-filter-holder').html() != '')
        {
            var formData = new FormData($(parentElement).find('.fabric-filter-holder').find('form')[0]);
        }
        else
            var formData = new FormData();
    }

    formData.append('id', params.id);
    formData.append('ContentHolder', params.ContentHolder);
    formData.append('Heading', params.Heading);
    formData.append('SubHeading', params.SubHeading);
    formData.append('StyleName', params.StyleName);
    formData.append('StyleID', params.StyleID);
    formData.append('SymbolID', params.SymbolID);

    $.ajax({
        url: '/get/fabric-list/',
        type: 'POST',
        data: formData,
        async: true,
        cache: false,
        contentType: false,
        processData: false,
    })
    .done(function( response )
    {
        CloseProcessing();

        data = $.parseJSON(response);

        if(data.status == 'success')
    	{
            $(params.ContentHolder).html(data.content);

            if($(parentElement).find('.fabric-filter-holder').html() == '')
            {
                $(parentElement).find('.fabric-filter-holder').html(data.filters);
            }

            if(typeof data.FabricList != 'undefined')
            {
                $.extend(FabricsList, data.FabricList);
            }

            if(data.total > 1)
            {
                // init bootpag
                $('.page-selection').bootpag({
                    total: data.total,
                    page: data.page,
                    maxVisible: 5,
                }).on("page", function(event, num)
                {
                    ListStyleFabrics({
                        id: params.id,
                        ContentHolder: params.ContentHolder,
                        Heading: params.Heading,
                        SubHeading: params.SubHeading,
                        StyleName: params.StyleName,
                        SymbolID: params.SymbolID,
                        StyleID: params.StyleID,
                        page: num,
                    });
                });
            }

            if(data.clear)
            {
                $('.clear-filter').removeClass('hidden');
            }
            else
            {
                $('.clear-filter').addClass('hidden');
            }

            // If noUISlider is not initialized

            if(typeof $(params.ContentHolder).parent().find('.noUi-base').html() == 'undefined')
            {
                var str = params.ContentHolder.replace("#", "");

                var parent = document.getElementById(str).parentElement;
                var priceSlider = parent.getElementsByClassName('price-slider')[0];

                noUiSlider.create(priceSlider, {
                    start: [100, 1000],
                    step: 100,
                    tooltips: true,
                    range: {
                        'min': 100,
                        'max': data.max
                    },
                    format: wNumb({
                        decimals: 0,
                    })
                });

                priceSlider.noUiSlider.on('change', function()
                {
                    $('#price').val(priceSlider.noUiSlider.get());

                    ListStyleFabrics({
                        id: params.id,
                        ContentHolder: params.ContentHolder,
                        Heading: params.Heading,
                        SubHeading: params.SubHeading,
                        StyleName: params.StyleName,
                        SymbolID: params.SymbolID,
                        StyleID: params.StyleID,
                        page: page,
                    });

                });
            }

            $(parentElement).find('.fabric-filter-holder').find('input[type=checkbox]').on('click', function()
            {
                ListStyleFabrics({
                    id: params.id,
                    ContentHolder: params.ContentHolder,
                    Heading: params.Heading,
                    SubHeading: params.SubHeading,
                    StyleName: params.StyleName,
                    SymbolID: params.SymbolID,
                    StyleID: params.StyleID,
                    page: page,
                });
            })
    	}
        else
    	{
            AlertBox(data.response_message);
    	}
    });
}

function FabricPopup1(Heading, SubHeading, StyleName, SymbolID, StyleID)
{
    ListStyleFabrics({
        id: 3,
        ContentHolder: '#fabric-wrapper',
        Heading: Heading,
        SubHeading: SubHeading,
        StyleName: StyleName,
        SymbolID: SymbolID,
        StyleID: StyleID
    });
    $('#fabric-wrapper').addClass('show');
/*
    content = '<ul class="fabric-list">';

    $.each(FabricsList, function(index, value)
    {
        content += '<li><a class="thumbnail" onclick="product.ElementFabric(\''+ Heading +'\', \''+ SubHeading +'\', '+ value.FabricID +', \''+ value.FabricImage +'\', \''+ SymbolID +'\', \''+ StyleName +'\', \''+ StyleID +'\')"><img src="'+ value.FabricImageThumb +'"></a></li>';
    });

    content += '</ul>';

    $('.fabric-wrapper').html(content);
    $('.fabric-wrapper').addClass('show');
*/
}

function GetFabricPath(FabricID)
{
    if(typeof FabricID !== 'undefined')
    {
        return FabricsList[FabricID].FabricImage;
    }
}

function GetStylePercentage(StyleID)
{
    if(typeof StyleID !== 'undefined')
    {
        return Percentage[StyleID];
    }
}

function LoadColors(Heading, StyleName, SymbolID, StyleID)
{
    content = '<ul class="color-list">';

    $.each(ColorList, function(index, value)
    {
        content += '<li><a class="color-button" onclick="product.SetColor(\''+ Heading +'\', '+ index +', \''+ value +'\', \''+ SymbolID +'\', \''+ StyleName +'\')" style="background-color:'+ value +'"></li>';
    });

    content += '</ul>';

    $('.color-swatch').html(content);
    $('.color-swatch').addClass('show');
}

function ShowSidebar()
{
    if($(window).width() < 992)
    {
        $('.custom-design-sidebar').animate({ 'margin-left' : '0px' });
        $('.navbar-toggle').show();
    }
}
function CloseSidebar()
{
    $('.custom-design-sidebar').animate({ 'margin-left' : '-450px' });
    $('.navbar-toggle').hide();
}

function Add2CartCustom(json)
{
    checkout = typeof checkout !== 'undefined' ? checkout : false;

    ShowProcessing();

    $.ajax({
        url: $('.cartBtn').attr('data-url'),
        type: 'POST',
        data: { data : json, id : $('.cartBtn').attr('data-id') },
    }).done(function(response)
    {
        CloseProcessing();

    	data = $.parseJSON(response);

        if(data.status == 'success')
    	{
    	    $('#cart-total').find('.cart-number').html(data.count);

    	    CustomBox({
    	        message : data.response_message,
                btn1Label : 'View Cart',
                btn2Label : 'Continue Shopping',
                btn1fn : function(){ window.location = data.btn1fn },
                btn2fn : '',
    	    });
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

    return false;
}

function AddToBag(t)
{
    $('.error').remove();

    if(typeof $('.product-stock li a').html() !== 'undefined')
    {
        if(typeof $('.product-stock li.active a').html() === 'undefined')
        {
            $('.product-stock').append('<div class="error" style="font-size:15px">Please select a size</div>');
            return false;
        }
        else
        {
            var size = $('.product-stock li.active a').html();
        }
    }

    $(t).addClass('disabled spinner');

    $.ajax({
        url: $(t).attr('data-url'),
        type: 'POST',
        data: { size : size, pid : $(t).attr('data-id') },
    }).done(function(response)
    {
        $(t).removeClass('disabled spinner');

    	data = $.parseJSON(response);

        if(data.status == 'success')
    	{
    	    $('.cart-number').html(data.count);

    	    CustomBox({
    	        message : data.response_message,
                btn1Label : 'View Cart',
                btn2Label : 'Continue Shopping',
                btn1fn : function(){ window.location = data.btn1fn },
                btn2fn : '',
    	    });
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

    return false;
}

function RemoveCartItem(t)
{
    $(t).addClass('disabled spinner');

    $.post( $(t).attr('data-url'), { option: 'remove', id: $(t).attr('data-id') })
    .done(function( response )
    {
        $(t).removeClass('disabled spinner');

        data = $.parseJSON(response);

        if(data.status == 'success')
    	{
    	    $(t).closest('tr').fadeOut();
            location.reload();
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

function AddCartAttribute(t, save)
{
    var save = typeof save !== 'undefined' ? true : false;

    if(save)
    {
        $(t).addClass('disabled spinner');

        var formData = new FormData($('#AdditionalDetailsForm')[0]);

        $.ajax({
            url: $('#AdditionalDetailsForm').attr('action'),
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

        var url = $(t).attr('data-url');

        $.post( url, { option: $(t).attr('data-title'), id: $(t).attr('data-id') })
        .done(function( response )
        {
            $(t).removeClass('disabled spinner');

            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    ShowModal(data.html, $(t).attr('data-title'));
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

function ForgotPassword(membertype)
{
    if(!ValidateEmail($('#user_login').val()))
    {
        $('#submit-btn')
                .popover({
                    html: true,
                    trigger: 'manual',
                    content: 'Type your email id then click on Forgot Password',
                    placement: 'top',
                }).popover('show');
    }
    else
    {
        ShowProcessing('Sending password reset link to your email id');

        if(membertype == 'sales')
        {
            var url = '/sales/reset-password/';
        }
        else if(membertype == 'customer')
        {
            var url = '/customers/reset-password/';
        }
        else
        {
            AlertBox('Invalid Access!');
            return false;
        }

        var formData = new FormData($('#loginform')[0]);
        $.ajax({
    	    url: url,
    	    type: 'POST',
    		data: formData,
    		async: true,
    		cache: false,
    		contentType: false,
    		processData: false,
    	}).done(function(response)
        {
            CloseProcessing();

            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
                AlertBox(data.message);
        	}
        	else
        	{
        	    AlertBox(data.message);
        	}
    	});
    }
    setTimeout(function(){ $('#submit-btn').popover('hide') }, 5000);

    return false;
}

function SetDefaultAddress(t)
{
    var id = $(t).attr('data-id');
    var pk = $(t).attr('data-pk');
    var url = $(t).attr('data-url');

    $.post( url, { cid: id, pk: pk })
    .done(function( response )
    {
        data = $.parseJSON(response);

        if(data.status == 'success')
    	{
    	    $('input[type=radio]').prop('checked', false);
    	    $('.default-address').removeClass('hidden');

            if($(t).closest('li').find('input[type=radio]').length)
            {
                $(t).closest('li').find('input[type=radio]').prop('checked', true);
            }
            else
            {
                location.reload();
            }

    	    $(t).closest('li').find('.default-address').addClass('hidden');
    	}
        else if(data.status == 'login')
    	{
    	    AlertBox('Oops! You session has expired! Please login again.', 'error', function(){ window.location = data.redirect } )
    	}
    	else
    	{
            AlertBox(data.response_message);
    	}
    });
}

function DeleteAddress(t)
{
    var id = $(t).attr('data-id');
    var pk = $(t).attr('data-pk');
    var url = $(t).attr('data-url');

    $.post( url, { cid: id, pk: pk, opt: 'delete' })
    .done(function( response )
    {
        data = $.parseJSON(response);

        if(data.status == 'success')
    	{
    	    $(t).closest('li').fadeOut();
    	}
        else if(data.status == 'login')
    	{
    	    AlertBox('Oops! You session has expired! Please login again.', 'error', function(){ window.location = data.redirect } )
    	}
    	else
    	{
            AlertBox(data.response_message);
    	}
    });
}

function LoadCartProducts()
{
    $('.footable-empty').find('td').html('<img src="/images/loader.gif"><br>Loading cart products...');

    if($('body').attr('data-relation') == 'Customer')
    {
        var url = '/ajax/load-bag/';
    }
    else
    {
        var url = '/ajax/sales/load-cart/';
    }

    if($('body').attr('data-location') == 'Address')
    {
        var label = 'Address';
    }
    else
    {
        var label = 'Checkout';
    }

    $.ajax({
        url: url,
        type: 'POST',
        data: { opt : 'cart', label: label },
    }).done(function(response)
    {
        $('.footable-empty').find('td').html('Your bag is empty!');

    	data = $.parseJSON(response);

        if(data.status == 'success')
    	{
    	    // Update the Cart Details
    	    $('#CartTable').find('tbody').html(data.html);

            // Update the Order Summary Section
    	    $('#order-summary').html(data.summary);

            $('.footable').footable();

            UpdateCartQuantity();

            $('input[name*=CartID]').on('change', function(){
                UpdateSelectedCartTotal(url);
            });

            $('input[type="file"]').filer(options);

            $('input[type="file"]').change(function(e)
            {
                var id = e.target.id;
                $('#Cart'+id).submit();
            });

            $(".UploadForm").submit(function(e)
            {
                e.preventDefault();
                AjaxResponse = AjaxFormSubmit(this);
                $.when(AjaxResponse).done(function(response)
                {
                    response = $.parseJSON(response);

                    if(response.status == 'success')
                    {
                        location.reload();
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
        else if(data.status == 'login')
    	{
            AlertBox(data.response_message, 'error', function(){ window.location = data.redirect });
    	}
        else if(data.status == 'update')
    	{
            AlertBox(data.response_message, 'error', function(){ window.location = data.redirect });
    	}
    });

    return false;
}

function UpdateCartQuantity()
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
                //AlertBox('Cart Updated!', 'success', function(){ location.reload(); });
                location.reload();
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

function UpdateSelectedCartTotal(url)
{
    ShowProcessing();

    var checked = []
    $("input[name='CartID[]']:checked").each(function ()
    {
        checked.push(parseInt($(this).val()));
    });

    $.post( url, { id: checked, opt: 'cart-summary' })
    .done(function( response )
    {
        CloseProcessing();

        data = $.parseJSON(response);

        if(data.status == 'success')
    	{
    	    // Update the Order Summary Section
    	    $('#order-summary').html(data.summary);
    	}
        else if(data.status == 'login')
    	{
    	    AlertBox('Oops! Your session has expired! Please login again.', 'error', function(){ location.href = response.redirect } );
    	}
    	else
    	{
            AlertBox(data.response_message);
    	}
    });
}

function ApplyDiscount(t, apply)
{
    var apply = typeof apply !== 'undefined' ? apply : false;

    if(apply)
    {
        var val = $(t).closest('div').find('input').val();

        if(val)
        {
            ShowProcessing('Applying discount...');

            var checked = []
            $("input[name='CartID[]']:checked").each(function ()
            {
                checked.push(parseInt($(this).val()));
            });

            $.ajax({
                url: '/ajax/sales/load-cart/',
                type: 'POST',
                data: { id: checked, percent : val, opt : 'discount' },
            }).done(function(response)
            {
                CloseProcessing();

            	data = $.parseJSON(response);

                if(data.status == 'success')
            	{
            	    // Update the Order Summary Section
               	    $('#order-summary').html(data.summary);
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
            AlertBox('Incorrect value for Discount');
        }
    }
    else
    {
        $(t).hide();
        $(t).parent().append('<i class="fa fa-close" onclick="$(\'.apply-discount\').remove();$(this).parent().find(\'a\').show();$(this).remove();" style="margin-right:5px;margin-top:8px"></i><div class="input-group pull-right apply-discount" style="width:100px"><span class="input-percent"><input autofocus type="number" class="form-control" name="discount" style="height:34px;line-height:34px;text-align:right"><span class="percent">%</span></span><span class="input-group-btn"><button class="btn btn-info" onclick="ApplyDiscount(this, true)" type="button" style="padding: 5px 10px;"><i class="fa fa-check"></i></button></span></div>');
    }
}

function ApplyCoupon(t, apply)
{
    var apply = typeof apply !== 'undefined' ? apply : false;

    if(apply)
    {
        var val = $(t).closest('div').find('input').val();

        if(val)
        {
            ShowProcessing('Applying coupon...');

            var checked = []
            $("input[name='CartID[]']:checked").each(function ()
            {
                checked.push(parseInt($(this).val()));
            });

            $.ajax({
                url: '/ajax/load-bag/',
                type: 'POST',
                data: { id: checked, code : val, opt : 'coupon' },
            }).done(function(response)
            {
                CloseProcessing();

            	data = $.parseJSON(response);

                if(data.status == 'success')
            	{
            	    // Update the Order Summary Section
               	    $('#order-summary').html(data.summary);
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
            AlertBox('Incorrect value for Discount');
        }
    }
    else
    {
        $(t).hide();
        $(t).parent().append('<i class="fa fa-close" onclick="$(\'.apply-discount\').remove();$(this).parent().find(\'a\').show();$(this).remove();" style="margin-right:5px;margin-top:8px"></i><div class="input-group pull-right apply-discount" style="width:150px"><span class="input-percent"><input autofocus type="text" class="form-control" name="discount" style="height:34px;line-height:34px;text-align:right;text-transform:uppercase"></span><span class="input-group-btn"><button class="btn btn-info" onclick="ApplyCoupon(this, true)" type="button" style="padding: 5px 10px;"><i class="fa fa-check"></i></button></span></div>');
    }
}

function RemoveCoupon()
{
    $.ajax({
        url: '/ajax/load-bag/',
        type: 'POST',
        data: { opt : 'remove-coupon' },
    }).done(function(response)
    {
        CloseProcessing();

    	data = $.parseJSON(response);

        if(data.status == 'success')
    	{
    	    // Update the Order Summary Section
       	    $('#order-summary').html(data.summary);
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

function PlaceClientOrder(t)
{
    var aid = $('input[type=radio]:checked').val();

    if(aid)
    {
        ShowProcessing('Placing order...');

        $.ajax({
            url: '/ajax/sales/place-order/',
            type: 'POST',
            data: { AddressID : aid },
        }).done(function(response)
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
            else if(data.status == 'update')
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
        AlertBox('Choose Delivery Address');
    }

    return false;
}

function NewsletterSubscription(t, id)
{
    var email = $('#'+id).val();

    if(!ValidateEmail(email))
    {
        AlertBox('Please enter a valid email address');
        return false;
    }

    $(t).addClass('disabled spinner');

    $.post( "/ajax/newsletter-subscription/", { Email: email })
    .done(function( response )
    {
        $(t).removeClass('disabled spinner');

        data = $.parseJSON(response);

        if(data.status == 'success')
    	{
    	    AlertBox(data.response_message, 'success');
            $('#'+id).val('');

            if($(t).attr('name') == 'popup')
            {
                document.cookie = "newsletter_popup=dontshowitagain";
                $('.show-modal').modal('hide');
            }
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

function Register()
{
    ShowProcessing('Creating account...');

    var formData = new FormData($('#RegistrationForm')[0]);

    $.ajax({
        url: '/ajax/register/',
        type: 'POST',
        data: formData,
        async: true,
        cache: false,
        contentType: false,
        processData: false,
    }).done(function(response)
    {
        CloseProcessing();

    	data = $.parseJSON(response);

        if(data.status == 'success')
    	{
    	    AlertBox(data.response_message, 'success', function(){ window.location = data.redirect });
    	}
        else if(data.status == 'validation')
    	{
    	    ThrowError(data.error);
    	}
        else
    	{
            AlertBox(data.response_message);
    	}
    });

    return false;
}

function LoadQuantityList(t, id)
{
    var size = t.value;
    sel = $('#CartForm'+id).find("[name=Quantity]");

    $.each(QtyList[id], function(index, value)
    {
        if(typeof value[size] !== 'undefined')
        {
            sel.html('');
            for(c = 1; c <= value[size]; c++ )
            {
                sel.append($("<option>").attr('value',c).text(c));
            }

            return true;
        }
    })
}

function ToggleFilterBox()
{
    if($('.filters-main-div').hasClass('sleep'))
    {
        $('.filters-main-div').removeClass('sleep');
        $('.btn-filter').addClass('btn-filter-close');
    }
    else
    {
        $('.filters-main-div').addClass('sleep');
        $('.btn-filter').removeClass('btn-filter-close');
    }
}

function LoadProducts(page)
{
    var page = typeof page != 'undefined' ? page : 0;

    $('.products-wrapper').html('<div class="no-data"><img src="/images/loader.gif"><br>Loading...</div>');

    var formData = new FormData($('#FiltersForm')[0]);
    formData.append('page', page);

    $.ajax({
        url: '/ajax/load-products/',
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
    	    $('#ProductCount').html("(" + data.count + " Products)");
    	    $('.products-wrapper').html(data.response_message);

    	    if(data.total > 1)
            {
                // init bootpag
                $('.page-selection').bootpag({
                    total: data.total,
                    maxVisible: 5,
                }).on("page", function(event, num)
                {
                    LoadProducts(num);
                });
            }

            if(data.clear)
            {
                $('.clear-filter').removeClass('hidden');
            }
            else
            {
                $('.clear-filter').addClass('hidden');
            }
    	}
        else
        {
            $('.products-wrapper').html(data.response_message);
        }
    });

    return false;
}

function ClearProductFilters()
{
    $('#FiltersForm').find('input[type=checkbox]').prop('checked', false);
    $('.clear-filter').addClass('hidden');
    $('#price').val('');
    priceSlider.noUiSlider.set([100, 1000]);

    LoadProducts();
}

function SortProducts(t)
{
    $('#sort').val(t.value);
    LoadProducts();
}

function AddMeasurement(t, save)
{
    var save = typeof save !== 'undefined' ? true : false;

    if(save)
    {
        $(t).find('.btn-info').addClass('disabled spinner');

        var formData = new FormData($('#MeasurementForm')[0]);

        $.ajax({
            url: '/ajax/customer/add-measurement/',
            type: 'POST',
            data: formData,
            async: true,
    		cache: false,
    		contentType: false,
    		processData: false,
        }).done(function(response)
        {
            $(t).find('.btn-info').removeClass('disabled spinner');

            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    $('.show-modal').modal('hide');
        	    AlertBox(data.response_message, 'success');
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
                $(t).find('.btn-info').after(data.response_message);
        	}
        });
    }
    else
    {
        $(t).addClass('disabled spinner');

        $.post( "/ajax/customer/add-measurement/", { id: $(t).attr('data-id'), CartID: $(t).attr('data-relation') })
        .done(function( response )
        {
            $(t).removeClass('disabled spinner');

            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    ShowModal(data.html, data.title);
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

    return false;
}

function ValidateDeliveryPincode(t)
{
    $('.error').remove();

    var pincode = $(t).closest('.input-group').find('input').val();

    if(!$.isNumeric(pincode) || pincode.length < 6)
    {
        $(t).closest('.input-group').after('<div class="error">Enter a valid pincode</div>');
        return false;
    }

    $(t).addClass('disabled spinner');

    $.post( "/ajax/delivery-pincode/", { pin: pincode })
    .done(function( response )
    {
        $(t).removeClass('disabled spinner');

        data = $.parseJSON(response);

        if(data.status == 'success')
    	{
    	    $(t).closest('.input-group').find('.input-group-addon').html('<i class="fa fa-check-circle font17 text-success"></i>');
            $(t).closest('.input-group').after('<div class="error"><span class="bold mg-t-5 text-primary">Delivery Available</span/div>');
    	}
    	else
    	{
    	    $(t).closest('.input-group').find('.input-group-addon').html('<i class="fa fa-times-circle font17 text-danger"></i>');
            $(t).closest('.input-group').after('<div class="error"><span class="font13">Delivery not available at this location</span></div>');
    	}
    });
}

function DeleteReview(t, remove)
{
    var remove = typeof remove !== 'undefined' ? remove : '';

    if(remove)
    {
        $(t).addClass('disabled spinner');

        $.post( "/ajax/delete-review/", { id: $(t).attr('data-id') })
        .done(function( response )
        {
            $(t).removeClass('disabled spinner');

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

function SetDeliveryAddress(t)
{
    $('.address-list').find('.fa-check').remove();
    $(t).addClass('disabled spinner');

    if($('body').attr('data-location') == 'Address')
    {
        var label = 'Address';
    }
    else
    {
        var label = 'Checkout';
    }

    $.post( "/ajax/load-bag/", { aid: $(t).attr('data-id'), opt : 'address', label : label })
    .done(function( response )
    {
        $(t).removeClass('disabled spinner').prepend('<i class="fa fa-check text-warning"></i>&nbsp;');

        data = $.parseJSON(response);

        // Update the Order Summary Section
	    $('#order-summary').html(data.summary);
        //$('.footable').footable();
    });
}

function PlaceCustomerOrder(t)
{
    ShowProcessing('Placing order...');

    $.ajax({
        url: '/ajax/customer/place-order/',
        type: 'POST',
        data: { opt : 'order' },
    }).done(function(response)
    {
        CloseProcessing();

    	data = $.parseJSON(response);

        if(data.status == 'success')
    	{
    	    window.location = data.redirect;
    	}
        else if(data.status == 'login')
    	{
            AlertBox(data.response_message, 'error', function(){ window.location = data.redirect });
    	}
        else if(data.status == 'update')
    	{
            AlertBox(data.response_message, 'error', function(){ window.location = data.redirect });
    	}
        else
    	{
            AlertBox(data.response_message);
    	}
    });

    return false;
}

function CustomerRetryOrder(t)
{
    OrderID = $(t).attr('data-id');

    if(OrderID > 0)
    {
        $(t).addClass('disabled spinner');

        $.post( "/ajax/retry-order/", { id: OrderID })
        .done(function( response )
        {
            $(t).removeClass('disabled spinner');

            data = $.parseJSON(response);

            if(data.status == 'success')
        	{
        	    window.location = data.redirect;
        	}
            else if(data.status == 'login')
        	{
        	    AlertBox(data.response_message, 'error', function(){ window.location = '/login/' });
        	}
        	else
        	{
                AlertBox(data.response_message);
        	}
        });
    }
    else
    {
        AlertBox('Invalid Access!');
    }
}

function CancelOrder(t, cancel)
{
    var cancel = typeof cancel !== 'undefined' ? cancel : '';

    if(cancel)
    {
        OrderID = $(t).attr('data-id');

        if(OrderID > 0)
        {
            $(t).addClass('disabled spinner');

            $.post( "/ajax/cancel-order/", { id: OrderID })
            .done(function( response )
            {
                $(t).removeClass('disabled spinner');

                data = $.parseJSON(response);

                if(data.status == 'success')
            	{
            	    location.reload();
            	}
                else if(data.status == 'login')
            	{
            	    AlertBox(data.response_message, 'error', function(){ window.location = '/login/' });
            	}
            	else
            	{
                    AlertBox(data.response_message);
            	}
            });
        }
        else
        {
            AlertBox('Invalid Access!');
        }
    }
    else
    {
        ConfirmBox('<div class="text-danger font18">Are you sure to cancel this order?</div>', function() { CancelOrder(t, true) } );
    }
}