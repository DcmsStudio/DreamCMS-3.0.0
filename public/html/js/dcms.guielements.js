

/* jQuery Custom Radio Checkbox Plugin
 * ----------------------------------------------------------
 * Author: Denis Ciccale (dciccale@gmail.com)
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 */
(function($) {
    $.fn.customRadioCheckbox = (function (options) {
        var // initial context
        context = $('body'),
        // checked prefix
        checkedPrefix = ' checked',
        // function to force the input change when clicking on the fake input
        forceChange = function() {
            // only trigger if the input is not inside a label
            if (this.parentNode.nodeName.toLowerCase() !== 'label') $(this.previousSibling).trigger('change.crc', [true]);
        },
        // fake input tag
        fakeInputTag = $(document.createElement('div')).bind('click.crc', forceChange),
        // object with each fake input and checked class
        fakeInput = {
            radio: fakeInputTag.clone(true).addClass('radiobox gui-form-element'),
            checkbox: fakeInputTag.clone(true).addClass('checkbox gui-form-element')
        },
        // function that inserts the fake input
        insertFakeInput = function(input, type) {
            // fake input
            var fakeInputClone = fakeInput[type].clone(true);

            // if is already checked add checked class
            if (input.checked) fakeInputClone.addClass(type + checkedPrefix);

            // insert the fake input after the input
            input.parentNode.insertBefore(fakeInputClone[0], input.nextSibling);
        };

        // the main function
        function customRadioCheckbox(_context) {
            // if context is defined means is the first init, if not use 'this'
            var context = _context || this;

            // if context element is not present return nothing, can't chain anyway
            if(!context.length || $(context).find('.gui-form-element').length) return;

            var rds = context.find('input[type=radio]:not(.hideInput)').addClass('guiHide'), // find & hide radios
            chs = context.find('input[type=checkbox]:not(.hideInput)').addClass('guiHide'); // find & hide checkboxes

            // only if there are radios
            if(rds.length) {
                rds.type = 'radio';
                // insert each fake radio
                $.each(rds, function (i) {
                    insertFakeInput(rds[i], rds.type);
                });

                // bind radio change event
                rds.bind('change.crc', function (e, force) {
                    // uncheck previous and remove checked class
                    if (!force || !this.checked) {
                        // filter by name and remove class from the last radio checked
                        rds.filter('[name=' + this.name + ']').next().removeClass(rds.type + checkedPrefix);
                        // add checked class to this input
                        $(this).next().addClass(rds.type + checkedPrefix);
                    }
                    // if force set to true and is not already checked, check the input
                    if (force && !this.checked) this.checked = true;
                });
            }

            // only if there are checkboxes
            if(chs.length) {
                chs.type = 'checkbox';
                // insert each fake checkbox
                $.each(chs, function (i) {
                    insertFakeInput(chs[i], chs.type);
                });

                // bind checkbox change event
                chs.bind('change.crc', function (e, force) {
                    // if force set to true, change state
                    if (force) this.checked = !this.checked;

                    // toggle checked class
                    $(this).next().toggleClass(chs.type + checkedPrefix);
                });
            }

            // make it chainable
            return context;
        }

        // first init
        customRadioCheckbox(context);

        // return the function for future calls
        // for example on ajax callback for the loaded content if needed
        return customRadioCheckbox;
    })();
})(jQuery);

var GUI_ELEMENTS = {
    
    uuid: 0,
    
    showOriginal: function(el)
    {
        el.removeClass('guiHide');
    },
    
    hiddenOriginal: function(el)
    {
        el.addClass('guiHide');
    },
    
    /**
     * must give a jquery object
     * @param inContainer object or string
     */
    prepareElements: function(inContainer)
    {
        
        if (typeof inContainer === 'string')
        {
            inContainer = $(inContainer);
        }
        
        
        
        
        if (inContainer)
        {
            var self = this;
            
            //$('input[type=checkbox]', inContainer).each(function(){
            //    if (!$(this).prev().hasClass('checkbox') && !$(this).parents().hasClass('grid-table-wrapper') ) self.createCheckbox( $(this) );
            //});

            /*
            $('input[type=checkbox],input[type=radio]', inContainer).each(function(){
                
                var cssName;
                var type = $(this).attr('type');
                if (type == 'checkbox')
                {
                    cssName = 'check';
                }
                else
                {
                    cssName = 'radio';
                }
                
                if (!$(this).prev().hasClass(cssName + 'box') && !$(this).parents().hasClass('grid-table-wrapper')) 
                {
                    self.createRadio( $(this), cssName );
                }
            });
            */
            

            
            $('select', inContainer).each(function(){
                if ( !$(this).attr('multiple')&& !($(this).attr('size') && parseInt($(this).attr('size'))>0 )&& 
                    !$(this).hasClass('bdone') && !$(this).prev().hasClass('dropdown-relative') )
                    {
                    self.createSelect( $(this) );
                }

                if (($(this).attr('size') && parseInt($(this).attr('size'))>0 ))
                {
                    $(this).addClass('multipledropdown');
                }                
            });
           
        }
        
        this.prepareCalenderInputs();
    },
    
    
    
    
    /**
     * will all styled elements reset to defaults
     * 
     */
    reset: function( container )
    {
        Debug.info('Reset Gui Form Elements');
        $('.gui-form-element', $(container)).each(function(){
            
            if ($(this).hasClass('dropdown'))
            {
                var i = $(this).attr('id');
                if ( i )
                {
                    var defaultVal = $(this).attr('default');
                    $('#'+ i.replace('dropdown-', 'list-') ).find('.item[val="'+ defaultVal  +'"]').click();
                }
            }
            
            
            if ($(this).hasClass('checkbox') || $(this).hasClass('radiobox') )
            {                
                var defaultVal = $(this).attr('default');                
                if ( defaultVal == 'on')
                {
                    $(this).addClass('checked');                
                    $(this).next().attr('checked', true);
                    $(this).next().checked = true;
                }
                else
                {
                    $(this).removeClass('checked');                
                    $(this).next().removeAttr('checked');
                    $(this).next().checked = false;
                }
            }
            
            
        });
    },
    
    
    prepareCalenderInputs: function()
    {

        $('input.cal_input').each(function(){
            var now = new Date();
            var _date = now.getDay() +"." + now.getMonth() + "." + now.getFullYear();

            var id = $(this).attr('id');
            $(this).removeData().dynDateTime({
                ifFormat:'%d.%m.%Y',
                daFormat:'%d.%m.%Y',
                date: _date,
                onClose: onCalClose
            });
        });
        
    },
	
	
    textWidth: function( eltext )
    {
        var calc = '<div style="display:none;margin:0!important;padding:0!important;font-size:11px!important;">' + eltext + '</div>';
        $('body').append(calc);
		
        var width = $('body').find('div:last').width();
        $('body').find('div:last').remove();

        return width;
    },
    
    
    _downdownListPositionIsInverted: false,
    
    getDropDownListTop: function (_container)
    {        
        var comboboxTop = $(_container).offset().top;        
        var cid = $(_container).attr('id');
        var dropdownListHeight = $('#'+cid.replace('dropdown-', 'list-')).height();
        var comboboxBottom = (comboboxTop + $(_container).height());
        var windowScrollTop = jQuery(window).scrollTop();
        var windowHeight = jQuery(window).height();        
        var availableSpaceBelow = (windowHeight - (comboboxBottom - windowScrollTop));
        var dropdownListTop;

        // Set values to display dropdown list below combobox as default				
        dropdownListTop = comboboxBottom;
        this._downdownListPositionIsInverted = false;

        // Check if there is enough space below to display the full height of the drop down list
        if (availableSpaceBelow < dropdownListHeight)
        {
            // There is no available space below the combobox to display the dropdown list
            // Check if there is available space above. If not, then display below as default
            //if ((comboboxTop - windowScrollTop) < dropdownListHeight)
            //{
            // There is space above
            dropdownListTop = (comboboxTop - dropdownListHeight - 4);
            this._downdownListPositionIsInverted = true;
        //}
        }
				
        return dropdownListTop;
    },
    
    
    createSelect: function(el)
    {

        
        
        var self = this;
        var _self = this;
        var position = el.position();
        var orginalWidth = el.width();
        var _width = 1;
        var _len = 0;
        el.addClass('bdone');
        
        el.find('option').each(function(){
            if ( _len < $(this).text().length )
            {
                _len = $(this).text().length;
                _width = self.textWidth( $(this).text() );
            }
        });


        orginalWidth = _width;
		
		
		
		
        var options = $('option', el);
        var optionSelected = $('option:selected', el);
        
        
        var isDisabled = ( (el.prop('disabled') || el.hasClass('disabled')) ? true : false) ;
        var extraClass = '';
        
        if (el.parents('.tablegrid-toolbar:first').length)
        {
            extraClass += ' griddropdown';
        }
        
        // prepare Disable
        if (isDisabled) {
            extraClass += ' isdisabled';
            el.change(function(){
                if (!$(this).prop('disabled') )
                {
                    $(this).prev().prev('.isdisabled').removeClass('isdisabled');
                }
            });
        }
        
        
        var na = el.prop('name');
        na = na.replace('[', '-');
        na = na.replace(']', '-');
        
        

        
        var relativeContainer = $('<div>').addClass('dropdown-relative');

        
        var container = $('<div>').addClass('dropdown gui-form-element' + extraClass).attr('id', 'dropdown-'+ na ).attr('guiel', el.prop('name') ).attr('default', optionSelected.val() );

            
        var containerLabel = $('<div>').addClass('dropdownlabel');
        containerLabel.text( optionSelected.text() );
        
        var containerArrow = $('<div>').addClass('dropdownArrow');
        containerArrow.append($('<span></span><div></div>'));
        
        $(container).append(containerLabel);
        $(container).append(containerArrow);
        
        //relativeContainer.append(container);
        
        if (el.parents('.tablegrid-toolbar:first').length)
        {
            extraClass = ' griddropdownList';
        }
        
        var optionList;
        if ($('#list-'+na).length)
        {
            optionList = $('#list-'+na);
            optionList.empty();
        }
        else 
        { 
            optionList = $('<div>').addClass('optionList' + extraClass).attr('id', 'list-'+na).hide();
        }
        
        
        var optionListInner = $('<div>').css({
            width: '100%'
        }); //.css({display: 'inline-block', height: '100%', width: '100%'});
        //relativeContainer.append(optionList);
        
        /*
        $(container).css({
            'width': el.outerWidth() + parseInt(el.css('margin-right')) + parseInt( el.css('margin-left') )
        });
         */
        optionList.css({
            width: orginalWidth + 12
        });

        

        
        options.each(function(){
            if ( $(this).text() )
            {
                var item = $('<span>').addClass('item').attr('val', $(this).val());          
                if ( $(this).is(':selected') == true )
                {
                
                    item.addClass('selected');
                    containerLabel.text($(this).text() );
                }
           
                item.text( $(this).text() );
                item.prepend($('<span>'));
                item.click(function(e){
                    // 
                    var Lis = $(this).parents('.optionList:first').attr('id');
                    var dropdownContainer = $('#'+ Lis.replace('list-', 'dropdown-') );
                    
                    $(this).parent().find('.selected').removeClass('selected');
                    $(this).addClass('selected');
                
                    $(dropdownContainer).find('.dropdownlabel').text( $(this).text() );
                    var currentsel = $(el).find('option:selected');
                    
                    if (currentsel)
                    {
                        currentsel.removeAttr('selected');
                        currentsel.attr('selected', false);
                        currentsel.selected = false;
                        currentsel.prop('selected', false);
                    }
                    
                    
                    var value = $(this).attr('val');
                    
                    $(el).find('option[value="'+ value +'"]').prop('selected', true);
                    $(el).find('option[value="'+ value +'"]').selected = true;
                    $(el).val( value );
                    $(el).change();
                
                    dropdownContainer.removeClass('listOpen Inverted');
                    //$(this).parents('.optionList:not(.multilines)').slideUp('fast');
                    _self.closeDropDown( dropdownContainer );
                    
                    e.preventDefault();
                
                });
            
                item.appendTo(optionListInner);
            }
        });
        
        optionListInner.appendTo(optionList);
        
        
        optionList.attr('grel', el.attr('name') );        
        optionList.bind('mouseenter', function(e){
            
            
            $('body').unbind('mousedown.dropdownList').bind('mousedown.dropdownList', function(e){
                /*
                if ( !$(e.target).parents().hasClass('optionList') && !$(e.target).parents().hasClass('multilines') ) {
                    $('.optionList:not(.multilines)').each(function(){
                        var Li = $(this).attr('id');
                        var dropdownContainer = $('#'+ Li.replace('list-', 'dropdown-') );
                        _self.closeDropDown(dropdownContainer);
                    });

                }
                else */if ( !$(e.target).parents().hasClass('optionList') && !$(e.target).parents().hasClass('multilines')) { 
                    
                    // e.preventDefault();
                    $('.optionList:not(.multilines)').each(function(){
                        var Li = $(this).attr('id');
                        var dropdownContainer = $('#'+ Li.replace('list-', 'dropdown-') );
                        _self.closeDropDown(dropdownContainer);
                    });
                }
            });

            
        })
        
        $('body').click(function(e)
        {
            if (!$(e.target).parents().hasClass('optionList') && !$(e.target).hasClass('dropdown') && !$(e.target).parents().hasClass('dropdown') )
            {
                $('.optionList:not(.multilines)').each(function(){
                    if (!$(this).hasClass('Inverted')) {
                        var Li = $(this).attr('id');
                        var dropdownContainer = $('#'+ Li.replace('list-', 'dropdown-') );
                        _self.closeDropDown(dropdownContainer);
                    }
                });
            }
            
            e.preventDefault();
        });
        
        var isLargeDropdown = false;


        if (el.attr('size') && parseInt( el.attr('size') ) > 1)
        { 
            var size = parseInt( el.attr('size') );
            optionList.show().prev().addClass('multilines');
            optionList.css({
                position: 'relative',
                left:0,
                height: (16 * size)
            });
            $(container).css({
                //    position: 'absolute'
                });
            
            
            // container.appendTo( relativeContainer );
            optionList.appendTo( relativeContainer );
            relativeContainer.insertBefore(el);
            optionList.show();
            
            
            isLargeDropdown = true;
            
        }
        else
        {
            // $(container).css({ width: el.outerWidth() });
            /*
            $(container).css({
          //      left: position.left, 
                top: position.top// + parseInt(el.css('margin-top')),            
          //      position: 'absolute'
            });
             */
                
                
            $(container).insertBefore(el);
            
            /*
            if (Config.get('useWindowStyledTabs', false))
            {
                var w = WindowCreator.getWinFromObject(el);
                if ( w.length )
                {
                    w.parent().append(optionList);
                }
            
            }
            else { */
            $('body').append(optionList); //optionList.insertAfter(container);
            //}
            
            
            
            
            
            $(container).css({
                width: orginalWidth + parseInt(el.css('margin-right')) + parseInt( el.css('margin-left') ) 
            });
        }
        
        
        
        
        
        if (options.length > 8)
        {
            optionList.css({
                'overflow': 'hidden' , 
                height: (16 * 8) +'px'
            });            
            
            optionListInner.css({
                position: 'relative', 
                height: 'auto'
            });
        }
        else
        {
            optionListInner.css({
                position: 'relative', 
                height: 'auto'
            });
            
            
            optionList.css({
                height: optionList.height(),
                zIndex: 99999
            });   
            
            
            // patch for scrollbars
            optionList.bind('mouseenter', function(){
                $(this).parents('div.'+ Config.get('wrapperClass') ).each(function(){
                    $(this).addClass('stopScroll');
                });
            });
                
            optionList.bind('mouseleave', function(){
                $(this).parents('div.'+ Config.get('wrapperClass') ).each(function(){
                    $(this).removeClass('stopScroll');
                });
            });
        }
        
        
        
       
        
        $(container).bind('click.dropdown', function(e){
            e.preventDefault();
            
            if ( $(this).hasClass('isdisabled') )
            {
                return;
            }

            if (!$(this).hasClass('listOpen'))
            {
                _self.openDropDown(this);                
            //_self.setScroll($(this).next('.optionList'));                
            }
            else
            {
                _self.closeDropDown(this);
            }
            
            return;
            

            $('.listOpen').removeClass('listOpen').next('.optionList:not(.multilines)').slideUp('fast');
            
            
            var parentContainerHeight = $(this).parents('.mwindow-body-content:first').height() - $(this).position().top;
            var coffset = $(this).offset();
            
            
            
            
            
            
            
            var list = $(this).next('.optionList');
            var listHeight = $('.item', list ).height();
            
            var dropdownListTop = _self.getDropDownListTop(this);
            
            //alert(' '+parentContainerHeight);
            
            
            
            if ( GUI_ELEMENTS._downdownListPositionIsInverted )
            {
                $(list).css({
                    // top: dropdownListTop,
                    left: coffset.left,
                    'width': $(self).outerWidth()
                });
                    
                // slideup to open
                if ($(self).hasClass('listOpen')) {
                    $(list).slideDown('fast');
                    /*
                    $(list).animate(
                    {
                        height: "toggle",
                        top: 0
                    },
                    "fast"); */
                    $(self).removeClass('listOpen').removeClass('Inverted');
                }
                else
                {
                    $(list).animate(
                    {
                        height: "toggle",
                        top: dropdownListTop
                    },
                    "fast");
                    //.slideUp('fast', function() {$(this).show()});
                    $(self).addClass('Inverted').addClass('listOpen', function(){
                        GUI_ELEMENTS.setScroll(list);
                    });
                }
            }
            else
            {
                // slidedown to open
               
                if ($(self).hasClass('listOpen'))
                {                    
                    if (!$(self).hasClass('Inverted'))
                    {
                        $(list).slideUp('fast' );
                    }
                    else
                    {
                        /*
                        $(list).animate(
                        {
                            height: "toggle",
                            top: coffset.top
                        },
                        "fast");
                        */
                        $(list).slideDown('fast' );
                    }
                
                    $(self).removeClass('listOpen').removeClass('Inverted');
                }
                else
                {
                    
                    if (!$(self).hasClass('Inverted'))
                    {
                        $(list).css({
                            top: dropdownListTop,
                            left: coffset.left,
                            'width': $(self).outerWidth() 
                        });                
                    }
                    
                    
                    $(list).slideDown('fast', function(){
                        GUI_ELEMENTS.setScroll(list);
                    });
                    $(self).addClass('listOpen');
                }
                
            }
            
            
            
            
            
            
            
            
            /*
            
            
            
                
            var top = $(container).position().top + $(container).outerHeight();
            var left = $(container).position().left;
            $(this).css({
                //    'width': $(self).outerWidth()
                });
            

            $(this).next('.optionList').css({
                'width': $(self).outerWidth() , 
                left: left,
                top: top
            } ).slideToggle('fast', function(){                 
                if ($(this).is(':visible')) {
                    $(self).addClass('listOpen');
 
                    if ($('.item', $(this) ).length > 8 && !$(this).children('div:first').hasClass('GuiScrollDiv') )
                    {
                        if (!$(this).children('div:first').hasClass('GuiScrollDiv') )
                        {
                            $(this).children('div:first').css({
                                position: 'relative', 
                                height: 'auto'
                            }).scrollbars({
                                height: $(this).height(), 
                                isDropDown : true
                            });
                        }
                        else
                        {
                            $(this).find('.GuiScrollDiv').css({
                                height: 'auto'
                            });
                        }
                    }

                }
                else {
                    $(self).removeClass('listOpen');
                    
                }
            });  
            
            */
            
            return false;
        });

        el.copyEventsTo(containerLabel);
        this.hiddenOriginal( el );
    },
    
    
    
    openDropDown: function(container)
    {
        var self = this;
        
        // close all other opened dropdowns
        $('.listOpen').each(function(){
            self.closeDropDown(this);
        });
        
        /*
        var parentContainerHeight = $(this).parents('.mwindow-body-content:first').height() - $(this).position().top;
        var coffset = $(container).position();
        */
        
        
        
        
        var conId = $(container).attr('id');
        if ( typeof conId != 'string') return;
        
        var list = $('#'+conId.replace('dropdown-', 'list-'));
        var listHeight = $('.item', list ).height(); 
        var dropdownListTop = self.getDropDownListTop(container);
        
        $(list).css({
            left: $(container).offset().left,
            width: $(container).outerWidth()
        });
        
        
        if ( self._downdownListPositionIsInverted )
        {
            $(list).css({
                top:$(container).offset().top
            }).animate(

            {
                    height: "toggle",
                    top: dropdownListTop
                },
                "fast");
            
            $(container).addClass('listOpen').addClass('Inverted');
        }
        else
        {
            $(list).css({
                top: ($(container).offset().top + $(container).outerHeight())
            }).slideDown('fast', function(){
                self.setScroll(list);
            });
            $(container).addClass('listOpen');
        }
        
        
        
        
    },
    
    closeDropDown: function(container)
    {
        var conId = $(container).attr('id');
        
        if ( typeof conId != 'string') return;
        
        
        var list = $('#'+conId.replace('dropdown-', 'list-'));
        
        if ($(container).hasClass('multilines'))
        {
            return;
        }
        
        
        if ($(container).hasClass('Inverted') )
        {
            
            var comboboxTop = $(list).position().top;
            var containerHeight = list.height();

            $(list).animate(
            {
                height: "toggle",
                top: (comboboxTop + containerHeight)
            },
            "fast", function(){
                $(list).css({
                    top: ($(container).position().top + $(container).outerHeight())
                });
                $(list).stop(true,true);
                $(container).removeClass('Inverted').removeClass('listOpen');
            });
            
        }
        else
        {
            $(list).slideUp('fast', function() {
                $(container).removeClass('Inverted').removeClass('listOpen');
            });
        }
    },
    
    
    setScroll: function(list)
    {
        if ($('.item', $(list) ).length > 8 && !$(list).children('div:first').hasClass('GuiScrollDiv') )
        {
            if (!$(list).children('div:first').hasClass('GuiScrollDiv') )
            {
                $(list).children('div:first').css({
                    position: 'relative', 
                    height: 'auto'
                }).scrollbars({
                    height: $(list).height(), 
                    isDropDown : true
                });
            }
            else
            {
                $(list).find('.GuiScrollDiv').css({
                    height: 'auto'
                });
            }
        }
    },
    
    
    createCheckbox000: function(el)
    {
        var e = $(el);
        var wrapTag =  '<div class="checkbox gui-form-element"></div>';
        var isDisabled = ( (el.attr('disabled') || el.hasClass('disabled')) ? true : false) ;    

        e.addClass('guiHide').wrap(wrapTag).change(function()
        {
            // radio button may contain groups! - so check for group
            $('input[name="'+$(this).attr('name')+'"]').each(function() {
                if( $(this).is(':checked') ) {
                    $(this).parent().addClass('checked');
                } else {
                    $(this).parent().removeClass('checked');
                }
            });
        });
    
        if( e.is(':checked') ) {
            e.parent().addClass('checked');
        }
        
        if( isDisabled ) {
            e.parent().addClass('isdisabled');
        }
        
    },
    
    createRadio00: function(el)
    {
        var e = $(el);
        var wrapTag =  '<div class="radiobox gui-form-element"></div>';
        var isDisabled = ( (el.attr('disabled') || el.hasClass('disabled')) ? true : false) ;    

        e.addClass('guiHide').wrap(wrapTag).change(function()
        {
            // radio button may contain groups! - so check for group
            $('input[name="'+$(this).attr('name')+'"]').each(function() {
                if( $(this).is(':checked') ) {
                    $(this).parent().addClass('checked');
                } else {
                    $(this).parent().removeClass('checked');
                }
            });
        });
    
        if( e.is(':checked') ) {
            e.parent().addClass('checked');
        }
        
        if( isDisabled ) {
            e.parent().addClass('isdisabled');
        }
    },
    
    
    
    
    
    
    
    
    
    
    
    
    createCheckbox: function(el)
    {

        var position = el.position();
        var orginalWidth = el.width();        
        var isDisabled = ( (el.prop('disabled') || el.hasClass('disabled')) ? true : false) ;        
        var extraClass = '';
        var id = 'cbk-'+ this.uuid + 1;
        
        
        if (el.attr('id') )
        {
            id = el.attr('id');
        }
        
        
        
        
        // prepare Disable
        if (isDisabled) {
            extraClass += ' isdisabled';
            el.change(function(){
                if (!$(this).prop('disabled') )
                {
                    $(this).prev().prev('.isdisabled').removeClass('isdisabled');
                }
            });
        }

        if ( $(el).attr('class') )
        {
            extraClass += $(el).attr('class');
        }

        var container = $('<div></div>').addClass('checkbox gui-form-element' + extraClass).attr('boxname', el.attr('name') ).attr('default', ($(el).is(':checked') ? 'on' : 'off') );
        /*
        container.css({
            left: position.left, 
            top: position.top + parseInt(el.css('margin-top')) - 2,            
            position: 'absolute'
        });
         */
        container.insertBefore(el);
        
        if ( $(el).is(':checked') || $(el).checked  ) container.addClass('checked');

        container.click( function(e) { 
            
            $(el).parent().click(e);
            
        });
        
        
        $(el).parent().click( function(e) { 
            //e.preventDefault();
            
            
            var checkbox = $('.checkbox', $(this) );
            if ( $(this).find('input[type=checkbox]:first').prop('disabled')  )
            {
                return;
            }
            
            var name = checkbox.attr('boxname');
            checkbox.addClass('curr');
            
            $('.checkbox').each(function()
            {                
                if ($(this).attr('boxname') == name && $(this).hasClass('checked')  && !$(this).hasClass('curr') )
                {
                    $(this).removeClass('checked'); 
                    $(this).next().removeAttr('checked');
                    $(this).next().checked = false;
                    $(this).next().prop('selected', false);
                }
            });
            
            checkbox.removeClass('curr');
            
            if (!checkbox.hasClass('checked') ) 
            {
                checkbox.addClass('checked');                
                checkbox.next().attr('checked', true);
                checkbox.next().checked = true;
                checkbox.next().prop('selected', true);
            }
            else
            {
                checkbox.removeClass('checked');                
                checkbox.next().removeAttr('checked');
                checkbox.next().checked = false;
                checkbox.next().prop('selected', false);
            }
            
            if ($(this).find('input[name="'+ name +'"]').length)
                $(this).find('input[name="'+ name +'"]').change();
            
            return false;
        });
        
        
        $(el).change(function(){ 
            
            if ( $(this).prop('disabled') )
            {
                return;
            }
            
            if ($(this).checked || $(this).prop('checked') )
            {
                container.addClass('checked');
            }
            else
            {
                container.removeClass('checked'); 
            }
        });
        
        this.hiddenOriginal( el );
        
        
        // register label event
        if ( el.attr('id') ) {
            $('label[for="' + el.attr('id') +'"]').bind('click', function (e) {
                if ($(this).find('.checkbox').length == 0 ) {
                    $(el).click();
                }
            });
        }
        
    },
    
    
    
    createRadio: function(el, mode)
    {
        
        var position = el.position();
        var orginalWidth = el.width();        
        var isDisabled = ( (el.attr('disabled') || el.hasClass('disabled')) ? true : false) ;        
        var extraClass = '';
        
        // prepare Disable
        if (isDisabled) {
            extraClass += ' isdisabled';
            el.change(function(){
                if (!$(this).attr('disabled') )
                {
                    $(this).prev().prev('.isdisabled').removeClass('isdisabled');
                }
            });
        }
        
        var wrapTag =  '<div class="'+mode+'box gui-form-element'+ extraClass +'"></div>';

        $(el).wrap(wrapTag).change(function(e){
            e.preventDefault();
            
            var radio = $('.'+mode+'box', $(this) );
            radio.addClass('curr');
            var name = radio.attr('boxname');
            
            $('.'+mode+'box').each(function()
            {                
                if ($(this).attr('name') == name && $(this).hasClass('checked') && !$(this).hasClass('curr')  )
                {
                    $(this).parent().removeClass('checked');
                }                
            });
            
            radio.removeClass('curr').addClass('checked');
        });
        
        if ( $(el).is(':checked') || $(el).checked  ) $(el).parent().addClass('checked');
        
        $(el).parent().click(function(e) {
            var input = $(this).find('input:first');
            var name = input.attr('name');

            $(this).addClass('curr');
            
            $('.'+mode+'box').each(function()
            {                
                if ($(this).find('input:first').attr('name') == name && $(this).hasClass('checked') && !$(this).hasClass('curr')  )
                {
                    $(this).removeClass('checked');              
                    $(this).find('input:first').removeAttr('checked').prop('checked', true).checked = false;
                //$(this).find('input:first').change();
                }                
            });
            
            $(this).removeClass('curr').addClass('checked');
            
            input.attr('checked', true);
            input.checked = true;
            input.prop('checked', true);
        //input.change();
            
        });
        
        
        // register label event
        /*
        if ( el.attr('id') && $('label[for="' + el.attr('id') +'"]').length == 1 ) {
            $('label[for="' + el.attr('id') +'"]').bind('click', function (e) {

                if ($(this).find('.'+mode+'box:first') ) {
                    //$(this).find('input:first').change();
                    $(this).find('.'+mode+'box:first').click();   
                    
                }

            });
        }
        */
        this.hiddenOriginal( el );
    }
    
};
