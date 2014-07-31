Desktop.Taskbar = ns('Desktop.Taskbar');
Desktop.Taskbar.Menu = {
    createStartMenu: function()
    {
        var self = this, settings = Desktop.settings;

        if ($('#Start-Menu').find('.inner:first').length == 0)
        {
            $('#Start-Menu').append($('<div class="inner"></div>').css({
                position: 'relative'
            }));
        }
        else if ($('#Start-Menu').find('.inner:first').length == 1)
        {
            $('#Start-Menu .inner:first').empty();
        }


        this.zIndex = $('#Start-Menu').css('zIndex');


        // get the menu and create it
        $.getScript(Desktop.baseURL + 'admin.php?action=menu', function() {
            setTimeout(function() {
                self.createMenu();

                self.addMenuItemEvents();
                Desktop.refreshDesktopIconDagnDropEvents();

            }, 100);

        });






        // set desktop icon position top
        if (settings.TaskbarIsTop && settings.WindowDesktopIcon)
        {
            if ($('#DesktopIcons').length == 0)
            {
                $('#desktop').append('<div id="DesktopIcons"></div>');
            }


            $('#DesktopIcons').css({
                position: 'absolute',
                width: '90%',
                left: 30,
                top: $('#Taskbar').outerHeight(true)
            });
        }






        $('#Start-Menu-Button').unbind("click").click(function(e) {
            DesktopConsole.resetZindex();
            if (settings.TaskbarIsTop)
            {
                $('#Start-Menu').css({
                    top: ($('#Taskbar').offset().top + $('#Taskbar').height())
                });
            }


            if ($(this).hasClass('active')) {
                $(this).removeClass('active');

                $('#Start-Menu').animate({
                    height: 0
                }, 300, function() {

                    $('#Start-Menu').hide();
                    $('#NavItems .submenu').hide();
                    $('#NavItems .active').removeClass('active');
                });


            }
            else
            {
                $(this).addClass('active');
                $('#Start-Menu').show();



                var StartMenuHeight = $('#Start-Menu ul:first').height();


                $('#Start-Menu').css({
                    height: 0
                }).animate({
                    height: StartMenuHeight
                }, 300, function() {

                    //$('#Start-Menu').show();
                });

            }
        });





        $('#Start-Menu #NavItems li').unbind("mouseover mouseout").on("mouseover mouseout", function(event) {
            if (event.type == 'mouseover') {
                $(this).addClass('active');
                $('.submenu:first', $(this)).show();
            }
            else {
                $(this).removeClass('active');
                $('#NavItems .submenu').hide();
            }
        });

        $('#Start-Menu-Button').unbind("mouseover mouseout").on("mouseover mouseout", function(event) {
            if (event.type == 'mouseover')
            {
                $(this).addClass('hover');
                $(this).find('.submenu:first').show();
            }
            else {
                $(this).removeClass('hover');
                $(this).find('.active').removeClass('active');
                $(this).find('.submenu').hide();
            }
        });



        $('.upScroll,.downScroll').unbind("mouseover mouseout").on("mouseover mouseout", function(event) {
            if (event.type == 'mouseover') {
                $(this).parent().find('li.active').removeClass('active');
            }
            else {
                $(this).removeClass('active');
            }
        });

    },
    // 
    doScrollMenu: function(el, event)
    {
        var dir = ($(event.target).parent().hasClass('upScroll') ? 'up' : ($(event.target).parent().hasClass('downScroll') ? 'down' : null));
        if (dir == null)
        {
            console.log('click scroll not found');
            return;
        }

        var ul = $(el).find('ul:first');
        var wrapper = ul.parent();
        var wrapperHeight = wrapper.innerHeight(true);
        var scrollDiff = ul.innerHeight(true) - wrapperHeight + 20; //(dir == 'up' ? wrapperHeight - ul.height() : -(ul.height() - wrapperHeight) );
        var top = ul.position().top + this.scrollIncrement * (dir == 'up' ? 1 : -1);

        if (top > 0 && dir == 'up')
        {
            $(el).prev().addClass('disabled').removeClass('force');
            return;
        }
        else if (top <= -scrollDiff && dir != 'up')
        {
            $(el).next().addClass('disabled').removeClass('force');
            return;
        }

        $(el).prev().removeClass('disabled').addClass('force');
        $(el).next().removeClass('disabled').addClass('force');


        //console.log('wrapperHeight: '+wrapperHeight+' ulHeight: '+ ul.height() +' positionTop: '+ul.position().top+' offsetTop: '+ ul.offset().top +' scrollDiff: ' + scrollDiff + ' currentTop = '+currentTop + ' top: '+top);
        $(ul).css({
            top: top
        });
    },
    createMenu: function()
    {
        if (!top.menuItems)
        {
            return;
        }

        var ul = $('<ul>').attr('id', 'NavItems');
        for (var menu_idx in top.menuItems) {
            var tmenu = top.menuItems[menu_idx];

            // create the li
            var node = this.buildTopLevelNode(tmenu);


            ul.append(node);
        }

        $('#Start-Menu .inner').append(ul);

        var interval, _self = this;
        $('#Start-Menu .inner').find('.upScroll').unbind('click').unbind("mouseover mouseout").on('click mouseover mouseout', function(e) {
            //e.preventDefault();
            var self = this;

            if (e.type == 'click')
            {
                _self.doScrollMenu($(this).next(), e);
            }
            else if (e.type == 'mouseover')
            {
                _self.doScrollMenu($(this).next(), e);


                interval = setInterval(function() {
                    _self.doScrollMenu($(self).next(), e);
                }, 50);
            }
            else if (e.type == 'mouseout')
            {
                clearInterval(interval);
                $(this).parents('.submenu:first').css({top: 0});
            }

        }).hide();


        $('#Start-Menu .inner').find('.downScroll').unbind('click').unbind("mouseover mouseout").on('click mouseover mouseout', function(e) {
            //e.preventDefault();

            var self = this;

            if (e.type == 'click')
            {
                _self.doScrollMenu($(this).prev(), e);
            }
            else if (e.type == 'mouseover')
            {
                _self.doScrollMenu($(this).prev(), e);


                interval = setInterval(function() {
                    _self.doScrollMenu($(self).prev(), e);
                }, 50);
            }
            else if (e.type == 'mouseout')
            {
                clearInterval(interval);
                $(this).parents('.submenu:first').css({top: 0});
            }

        }).hide();


        var TaskbarEndPos = $('#Taskbar').position().top + $('#Taskbar').height();


        /**
         *  Add Startmenu Item Events
         */
        $("#Start-Menu .inner li:not(.separator)").hover(function() {
            $(this).addClass("active");

        }, function() {
            $(this).removeClass("active");
        });


        var showTimer;



        $("#Start-Menu li.separator").hover(function() {
            $(this).parents().show();
            $(this).parents('li').addClass("active");
        }, function() {
            $(this).parents('li').addClass("active");
            $(this).parents().show();
        });



        $("#Start-Menu li:not(.separator)").hover(function() {
            clearTimeout(showTimer);
            var newZindex;

            if (!this.zIndex)
            {
                newZindex = _self.zIndex - 1;
                this.zIndex = newZindex;
            }
            else
            {
                newZindex = this.zIndex;
            }

            $(this).addClass("active");
            $(this).parents('li').addClass("active");

            var top = $(this).offset().top, self = this;

            //$(this).parents('li.active .submenu:first').hide();
            var nextSubmenu = $(this).find('.submenu:first');
            var ULLIST = $(nextSubmenu).find('.container:first');
            ULLIST.css({
                height: null,
                display: 'inline-block'
            });

            $(nextSubmenu).css({
                left: $(this).position().left + $(this).outerWidth(),
                maxHeight: null,
                height: null
            }).show();


            var nextSubmenuHeight = nextSubmenu.outerHeight(true);


            var ULHeight = ULLIST.height();
            var maxHeight = ($(window).height() - TaskbarEndPos);

            $(nextSubmenu).css({
                maxHeight: maxHeight
            });

            var scrollerHeight = $(this).find('.downScroll').outerHeight(true);
            var outsideHeight = ((ULHeight + $(this).offset().top) - $(window).height());

            $(nextSubmenu).hide();



            // console.log('outsideHeight '+ outsideHeight);


            maxHeight = ($(window).height() - TaskbarEndPos);
            var maxULHeight = maxHeight - (scrollerHeight * 2);

            // ist höher als das fenster
            if (ULHeight >= maxHeight)
            {
                // show arrows
                ULLIST.prev().addClass('disabled').show();
                ULLIST.next().removeClass('disabled').show();

                if (!ULLIST.prev().hasClass('force'))
                {
                    ULLIST.prev().addClass('disabled');
                }
                else
                {
                    ULLIST.prev().removeClass('disabled');
                }

                top = 0;


                // set max height to UL container
                ULLIST.css({
                    height: maxULHeight
                });

                $(nextSubmenu).css({
                    height: maxHeight
                }).addClass('hasscroll no-border-radius');

               // console.log('1 must scroll');
            }
            else if (ULHeight < maxHeight && $(nextSubmenu).hasClass('hasscroll'))
            {
                // show arrows
                ULLIST.prev().addClass('v').show();
                ULLIST.next().addClass('v').show();

                if (!ULLIST.prev().hasClass('force'))
                {
                    ULLIST.prev().addClass('disabled');
                }
                else
                {
                    ULLIST.prev().removeClass('disabled');
                }

                ULLIST.addClass('no-border-radius');

                top = (outsideHeight > 0 && !$(nextSubmenu).hasClass('hasscroll') ? $(this).position().top : (!$(nextSubmenu).hasClass('hasscroll') ? $(this).offset().top : 0));
               // console.log('1.1');
            }
            else if (outsideHeight > 0 && ULHeight < maxHeight)
            {

                if (!ULLIST.prev().hasClass('force'))
                {
                    ULLIST.prev().addClass('disabled');
                }
                else
                {
                    ULLIST.prev().removeClass('disabled');
                }


                top = $(this).position().top - (outsideHeight + 10);
              //  console.log('2');
            }
            else
            {
                if (!ULLIST.prev().hasClass('force'))
                {
                    ULLIST.prev().addClass('disabled');
                }
                else
                {
                    ULLIST.prev().removeClass('disabled');
                }

                top = $(this).position().top;
              //  console.log('3');
            }

            outsideHeight = 0;

            $(nextSubmenu).css({
                left: $(this).offset().left + $(this).outerWidth(),
                top: top
            });


            $(nextSubmenu).hide();
            showTimer = setTimeout(function() {
                $(nextSubmenu).show();
            }, 100)

        }, function(e) {

            if ($(e.target).hasClass('separator') || $(e.target).parent().hasClass('separator'))
            {
                return;
            }

            var self = this;
            clearTimeout(showTimer);
            $('.submenu:first', self).removeClass('hasscroll no-border-radius').hide();
            $(self).find('.upScroll').hide();
            $(self).find('.downScroll').hide();
            $(self).removeClass("active");

        });


    },
    getSpriteIcon: function(menu)
    {
        var icon = false;

        if (typeof menu.isCoreIcon == 'undefined' && typeof menu.isPluginIcon == 'undefined' || menu.isCoreIcon == true || menu.controller == 'options')
        {
            var names = menu.icon.split('.');
            names.pop();
            var realName = names.join('');
            var name = realName.split('/');
            icon = name.pop();
        }

        return icon;
    },
    getIcon: function(menu)
    {

        var icon = null;

        if (typeof menu.isCoreIcon == 'undefined' && typeof menu.isPluginIcon == 'undefined' || menu.isCoreIcon == true)
        {
            icon = Desktop.baseURL + 'html/style/c9/img/pulldownmenu/' + menu.icon;
        }
        else if (typeof menu.isPluginIcon != 'undefined' || menu.isPluginIcon == true)
        {
            icon = menu.icon;
        }
        else {
            icon = Desktop.baseURL + 'html/style/c9/img/' + menu.icon;
        }

        return icon;
    },
    buildTopLevelNode: function(menu)
    {
        var li = $('<li>').addClass('root-item');
        var _a = $('<span>').addClass('root-group');

        if (menu.icon != '' && menu.icon != null)
        {
            // create the icon
            var img = $('<img>').attr({
                width: 16,
                height: 16,
                alt: '',
                src: this.getIcon(menu)
            });

            // append the icon
            _a.append(img);
        }


        _a.append(menu.label);
        li.append(_a);

        if (menu.items && menu.items.length)
        {

            var container = $('<div>').addClass('container');
            var div = $('<div>').addClass('submenu').css({
                position: 'absolute'
            }).hide();

            div.append($('<div>').addClass('upScroll').append('<span>'));



            var ul = $('<ul>');
            ul.appendTo(container);

            var i = 0, child;
            var prev = null;
            while (child = menu.items[i++])
            {

                if (menu.items[prev] && typeof menu.items[prev].type != 'undefined' && menu.items[prev].type == 'separator') {
                    if (typeof menu.items[child].type != 'undefined' && menu.items[child].type == 'separator') {
                        continue;
                    }
                }

                prev = child;

                ul.append(this.buildChildNode(child));


            }


            if (menu.items[prev] && typeof menu.items[prev].type != 'undefined' && menu.items[prev].type == 'separator') {
                ul.find('li:last').remove();
            }

            div.append(container);
            div.append($('<div>').addClass('downScroll').append('<span>'));

            li.append(div).addClass('fold');
        }




        if (li.children().length < 2) {
            return false;
        } else {
            return li;
        }

        return li;
    },
    buildChildNode: function(menu)
    {
        var li = $('<li>');
        var self = this;


        if (!menu.items && menu.type != 'separator')
        {

            if (!menu.url)
            {
                menu.url = '#'
            }

            var img, a, href = menu.url;

            if (menu.items && menu.items.length)
            {
                a = $('<span>');
            }
            else
            {
                a = $('<a>').attr({
                    href: 'javascript:void(0);',
                    title: menu.label
                });
            }

            var icon_url;
            var sprite_icon;

            if (menu.icon != '' && menu.icon != null && typeof menu.icon != 'undefined')
            {
                var isSprite = this.getSpriteIcon(menu);

                if (isSprite && menu.controller != 'options')
                {
                    img = $('<span class="menu-sprite ' + isSprite + '"></span>');
                    sprite_icon = isSprite;
                }
                else if (isSprite && menu.controller == 'options')
                {
                    img = $('<span class="cfg ' + isSprite + '"></span>');
                    sprite_icon = isSprite;
                }
                else
                {
                    icon_url = this.getIcon(menu);

                    img = $('<img>').attr({
                        width: 16,
                        height: 16,
                        alt: '',
                        src: icon_url
                    });
                }
            }
            else
            {
                img = $('<img>').attr({
                    width: 16,
                    height: 16,
                    alt: '',
                    src: 'spacer.gif'
                });
            }




            // Seperator
            if (menu.url == '#' && !menu.click)
            {
                return '';
            }
            // add click events
            else if (menu.url != '#' && !menu.click)
            {

                a.data('itemData', {
                    label: menu.label,
                    url: menu.url,
                    sprite_icon: sprite_icon,
                    icon: icon_url,
                    ajax: (typeof menu.ajax != 'undefined' ? menu.ajax : false),
                    isCoreIcon: menu.isCoreIcon,
                    modal: menu.modal,
                    minwidth: menu.minwidth,
                    minheight: menu.minheight,
                    controller: menu.controller,
                    action: menu.action
                }).append(img).append(menu.label);
                li.append(a);


            }
            else
            {
                // is only a label
                a.append(menu.label);
                li.append(a);
            }

        }





        // has onclick
        if (menu.click)
        {
            $(li).click(function(e) {

                e.preventDefault();

                this.blur();

                eval(menu.click);

                return false;
            });
        }


        if (menu.items && menu.items.length)
        {

            if (menu.icon != '' && menu.icon != null && typeof menu.icon != 'undefined')
            {
                img = $('<img>').attr({
                    width: 16,
                    height: 16,
                    alt: '',
                    'class': 'spacer',
                    src: Desktop.baseURL + 'html/style/c9/img/' + 'spacer.gif'
                });
            }
            else
            {
                img = $('<img>').attr({
                    width: 16,
                    height: 16,
                    alt: '',
                    src: Desktop.baseURL + 'html/style/c9/img/' + 'spacer.gif'
                });
            }

            var ie_fix = '';
            if ($.browser.msie)
            {
                ie_fix = ' style="float:left"';
            }




            li.append($('<span' + ie_fix + ' clsss="menu-group">').append(img).append(menu.label));
        }


        // add seperator
        if (menu.type && menu.type == 'separator')
        {
            $('<div>').addClass('menu-separator').appendTo(li);
            li.addClass('separator');
        }


        // add sub items
        if (menu.items && menu.items.length)
        {


            var div = $('<div>').addClass('submenu').css({
                position: 'absolute'
            }).hide();




            div.append($('<div>').addClass('upScroll').append('<span>'));
            var container = $('<div>').addClass('container');

            var ul = $('<ul>');
            ul.appendTo(container);

            var i = 0, childs;
            for (childs in menu.items) {
                ul.append(this.buildChildNode(menu.items[childs]));
            }

            div.append(container);
            div.append($('<div>').addClass('downScroll').append('<span>'));

            li.append(div).addClass('fold');
        }

        return li;
    },
    addMenuItemEvents: function()
    {
        var self = this;

        // make menuitem draggable & clickable
        $('#Start-Menu').find('a').each(function()
        {

            var itemData = $(this).data('itemData');

            if (typeof itemData != 'undefined')
            {
                var icon = '', li = $(this).parent();


                if (itemData.sprite_icon != false)
                {
                    if ($('span', $(this)).hasClass('cfg'))
                    {
                        icon = Desktop.baseURL + 'html/style/c9/img/' + 'cfgitems/' + itemData.sprite_icon + '.png';
                    }
                    else
                    {
                        icon = Desktop.baseURL + 'html/style/c9/img/' + 'pulldownmenu/' + itemData.sprite_icon + '.png';
                    }
                }
                else
                {
                    icon = $(this).find('img').attr('src');
                }


                li.data('itemData', $.extend({}, itemData)).click(function(ev) {
                    self.hideMenu();

                    if (typeof itemData.ajax != 'undefined' && itemData.ajax == 2)
                    {
                        window.open(itemData.url);
                        return false;
                    }

                    else if (typeof itemData.ajax != 'undefined' && itemData.ajax == 1)
                    {
                        //$('#content').mask(cmslang.mask_pleasewait);

                        Desktop.Tools.maskDesktop('Bitte warten...');

                        $.get(Tools.prepareAjaxUrl(itemData.url), function(_data)
                        {
                            if (Desktop.responseIsOk(_data))
                            {
                                // $('#content').unmask();

                                Desktop.Tools.unmaskDesktop();

                                if (typeof _data.msg == 'string')
                                {
                                    Notifier.display('info', _data.msg);
                                    setTimeout('Notifier.hide()', 3000);
                                }

                                // Build Float Box
                                if (typeof _data.html == 'string')
                                {
                                    //display_info(_data.html, false, menu.label);
                                    var options = {};
                                    options.autoResize = true;
                                    options.title = itemData.label;


                                    createPopup(_data.html, options);


                                    return false;
                                }


                            }
                            else
                            {
                                //$('#content').unmask();
                                Desktop.Tools.unmaskDesktop();
                                alert(_data.msg);
                            }
                        }, 'json');

                        return false;
                    }
                    else
                    {

                        var opts = {};
                        opts.loadWithAjax = true;
                        opts.allowAjaxCache = false;
                        opts.WindowToolbar = false;
                        opts.DesktopIconWidth = 36;
                        opts.DesktopIconHeight = 36;
                        opts.UseWindowIcon = false;


                        opts.WindowURL = Tools.prepareAjaxUrl(itemData.url);

                        // reset current ajaxData
                        Desktop.ajaxData = {};
                        // Desktop.ajaxData = $.extend({}, itemData );
                        Desktop.getAjaxContent(opts);

                        if (typeof Desktop.ajaxData.sessionerror != 'undefined' && Desktop.ajaxData.sessionerror)
                        {
                            $('#userMenu,#Taskbar').remove();
                            Desktop.runAfterBoot(true);
                            return false;
                        }






                        // Desktop.ajaxData = data ;
                        opts.Skin = Desktop.settings.Skin;
                        opts.WindowTitle = itemData.label;
                        opts.WindowDesktopIconFile = (icon ? icon : '');

                        if (typeof Desktop.ajaxData.toolbar != 'undefined')
                        {
                            opts.WindowToolbar = Desktop.ajaxData.toolbar;
                        }

                        opts = $.extend({}, opts, itemData);

                        Desktop.GenerateNewWindow(opts, ev);

                        return false;
                    }

                }).draggable({
                    appendTo: "body",
                    helper: function(event) {
                        var iconSpan = $(this).find('span').clone();
                        var label = $(this).text();

                        var helper = $('<div class="fromMenu menu-drag-helper"></div>');
                        $(helper).data('itemData', $(this).data('itemData'));

                        return $(helper).append(iconSpan).append($('<p>').append(label)).appendTo("body");
                    },
                    revert: true,
                    start: function(event, ui)
                    {
                        $(this).addClass('fromMenu');
                        var iconSpan = $(this).find('span').clone();
                        var label = $(this).text();

                        var helperTemplate = '<div class="fromMenu menu-drag-helper"></div>';


                        $(helperTemplate).data('itemData', $(this).data('itemData')).addClass(iconSpan.attr('class')).append(iconSpan).append($('<p>').append(label));

                    }
                });
            }
        });

    },
    hideMenu: function(animationSpeed)
    {
        if (animationSpeed === 0)
        {
            $('#Start-Menu .active:not(#console)').removeClass('active');
            $('#Start-Menu, #Start-Menu .submenu').hide();
        }
        else
        {
            $('#Start-Menu').animate({
                height: 0
            }, 150, function() {
                $(this).hide();

                $('#Start-Menu .active:not(#console)').removeClass('active');
                $('#Start-Menu .submenu').hide();
            });
        }

        $('#Start-Menu-Button').removeClass('active');

    }

};