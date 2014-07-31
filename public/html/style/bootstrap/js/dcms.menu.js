DesktopMenu = {
    coreMenuCache: null,
    AppMenuCache: [],
    zIndex: 99900,
    menuContainer: null,
    createCoreMenu: function ()
    {
        var self = this, settings = Desktop.settings;

        if (this.coreMenuCache === null)
        {
            jQuery.ajax({
                type: "GET",
                url: Tools.prepareAjaxUrl('admin.php?action=menu'),
				async: true,
                success: function () {
                    self.coreMenuCache = top.menuItems;
                    self.buildMenu(self.coreMenuCache, 0);
                    //top.menuItems = null;
                },
                dataType: "script",
                cache: false
            });
        }
        else
        {
            this.buildMenu(self.coreMenuCache, 0);
        }
    },
    getMenuContainer: function ()
    {
        return this.menuContainer;
    },
    buildMenu: function (data, isAppMenu, changeItemUrl)
    {
        var container, settings = Desktop.settings, ul;

        if (isAppMenu)
        {
            container = $('#App-Menu');

            this.zIndex = (container.css('zIndex') ? container.css('zIndex') : this.zIndex);
            this.menuContainer = container;

            if (container.find('ul').length) {

                ul = container.find('ul:first');
            }
            else {
                ul = $('<ul>');
                container.append(ul);
            }
        }
        else
        {
            if ($('#Start-Menu').find('.inner:first').length === 0)
            {
                $('#Start-Menu').append($('<div class="inner"></div>').css({
                    position: 'relative'
                }));
            }
            else if ($('#Start-Menu').find('.inner:first').length === 1)
            {
                $('#Start-Menu .inner:first').empty();
            }

            container = $('#main-menu');

            data = this.coreMenuCache;

            this.zIndex = $('#Start-Menu').css('zIndex');

            ul = container.find('ul:first');
            ul.addClass('loading-small');

        }


        if (!isAppMenu)
        {
            ul.attr('id', 'NavItems');
        }


        var x = 0;
        for (var menu_idx in data)
        {
            var menu = data[menu_idx];

            if (menu.label) {
                var li = $('<li>');
                var _a = $('<span>');

                if (!isAppMenu)
                {
                    li.addClass('root-item');
                    _a.addClass('root-group');
                }
                else
                {
                    if (typeof changeItemUrl === 'object' && isAppMenu)
                    {
                        menu.extraOptions = [changeItemUrl];
                    }

                    // skip item if not used for the window
                    if (menu.require)
                    {
                        var w = Application.getWindow();
                        if (w != null)
                        {
                            if (typeof w.data('windowGrid') !== 'object' && typeof w.data('grid') !== 'object' && menu.require === 'grid')
                            {
                                continue;
                            }
                        }
                    }



                    menu.controller = isAppMenu;
                    if (!menu.label)
                    {
                        menu.label = menu.title;
                    }

                    li.addClass('root-item');
                    _a.addClass('root-group');
                    _a.data('itemData', menu);
                }






                _a.append(menu.label);
                li.append(_a);
                if (menu.items && menu.items.length)
                {
                    _a.append('<em/>');

                    var subContainer = $('<div>').addClass('container');
                    var div = $('<div>').addClass('submenu').hide();
                    if (isAppMenu)
                    {
                        // make it horizontal
                        div.css({
                            position: 'relative'
                        });
                    }
                    else
                    {
                        // make it vertical
                        div.css({
                            position: 'absolute'
                        });
                    }



                    var subul = $('<ul>');
                    subul.appendTo(container);
                    var i = 0, child;
                    var prev = null;
                    while (child = menu.items[i++])
                    {

                        // skip multiple seperators
                        if (menu.items[prev] && menu.items[prev].type && menu.items[prev].type === 'separator')
                        {
                            if (typeof menu.items[child].type !== 'undefined' && menu.items[child].type === 'separator') {
                                continue;
                            }
                        }

                        prev = child;
                        if (typeof changeItemUrl == 'object')
                        {
                            child.extraOptions = [changeItemUrl];
                        }

                        subul.append(this.buildChildNode(child, isAppMenu, changeItemUrl, 0));
                    }

                    // remove if the last item is a separator
                    if (menu.items[prev] && typeof menu.items[prev].type !== 'undefined' && menu.items[prev].type === 'separator')
                    {
                        subul.find('li:last').remove();
                    }

                    div.append(subul);
                    li.append(div).addClass('fold');

                }
            }

            if (x === 0) {
                ul.empty();
            }

            if (li.children().length > 2 && !isAppMenu) {
                li.appendTo(ul);
            }
            else
            {
                li.appendTo(ul);
            }

            ++x;
        }


        if (!isAppMenu)
        {
            /**
             *  Add Startmenu Item Events
             */
            $("#Start-Menu .inner li:not(.separator)").bind("mouseover mouseout", function (event) {
                if (event.type === 'mouseover')
                {
                    $(this).addClass("active");
                    $(this).find('.submenu:first').show();
                }
                else
                {
                    //   $(this).removeClass("active");
                    $(this).find('.submenu').hide();
                }
            });

            $("#Start-Menu li.separator").bind("mouseover mouseout", function (event) {
                if (event.type === 'mouseover')
                {
                    $(this).parents().show();
                    $(this).parents('li').addClass("active");
                }
                else
                {
                    $(this).parents('li').addClass("active");
                    $(this).parents().show();
                }
            });

            // bind events
            container.unbind('click');
            container.bind('click', function () {
                $(this).children('ul:first').show();
            });



            $('#Start-Menu-Button').unbind("click");
            $('#Start-Menu-Button').bind('click', function (e) {
                DesktopConsole.resetZindex();

                if (settings.TaskbarIsTop)
                {
                    $('#Start-Menu').css({
                        top: ($('#Taskbar').offset().top + $('#Taskbar').outerHeight(true))
                    });
                }

                if ($(this).hasClass('active')) {
                    $(this).removeClass('active');
                    $('#Start-Menu').hide();
                }
                else
                {
                    $('#App-Menu .submenu').hide();
                    $('#App-Menu .active').removeClass('active');
                    $(this).addClass('active');

                    $('#Start-Menu').show();

                    var StartMenuHeight = $('#Start-Menu ul:first').height();

                    $('#Start-Menu').css({
                        height: StartMenuHeight
                    }, 300).show();
                }
            });



            $('#Start-Menu-Button').unbind("mouseover mouseout");
            $('#Start-Menu-Button').bind("mouseover mouseout", function (event) {
                if (event.type === 'mouseover')
                {
                    if ($('#App-Menu .active').length)
                    {
                        $(this).trigger('click');
                    }

                    $(this).addClass('hover');
                }
                else
                {
                    $(this).removeClass('hover');
                    $(this).find('.active').removeClass('active');
                    $(this).find('.submenu').hide();
                }
            });


            $('#Start-Menu #NavItems li').unbind("mouseover mouseout");
            $('#Start-Menu #NavItems li').bind("mouseover mouseout", function (event) {
                if (event.type === 'mouseover')
                {
                    $(this).addClass('active');
                }
                else
                {
                    $(this).removeClass('active');
                }
            });


            this.bindEvents(container.attr('id'));

            setTimeout(function () {
                DesktopMenu.addMainMenuItemEvents();
            }, 10);

        }
        else
        {
            var containerID = container.attr('id');
            this.bindEvents(containerID, true);
        }

        ul.removeClass('loading-small');

        return true;
    },
    buildChildNode: function (nodedata, isAppMenu, changeItemUrl, level)
    {
        level = level || 0;

        var itemLi = $('<li>');
        if (typeof changeItemUrl === 'object')
        {
            nodedata.extraOptions = [changeItemUrl];
        }

        if (!nodedata.items && nodedata.type !== 'separator' && nodedata.type !== 'line')
        {
            if (!nodedata.label)
            {
                nodedata.label = nodedata.title;
            }


            var img = '', a, href = nodedata.url;
            if (!nodedata.url && isAppMenu)
            {
                href = 'void(0)';
                nodedata.url = 'void(0)';
            }



            if (isAppMenu)
            {
                if (typeof nodedata.label == 'undefined' || nodedata.label === '') {
                    nodedata.label = nodedata.title;
                }

                nodedata.controller = isAppMenu;

                if (typeof nodedata.action === 'string')
                {
                    itemLi.attr('action', nodedata.action);
                }
            }

            if (!nodedata.url)
            {
                nodedata.url = '#';
            }



            // Seperator
            if (nodedata.url === '#' && !nodedata.click)
            {
                return '';
            }


            var a;

            if (nodedata.items && nodedata.items.length)
            {
                a = $('<span>');

                if (isAppMenu)
                {
                    a.data('itemData', nodedata);
                }
            }
            else
            {
                a = $('<span class="item">').attr({
                    href: 'javascript:void(0);',
                    title: nodedata.label
                });
            }


            if (nodedata.id)
            {
                a.attr('id', nodedata.id);
            }

            var icon_url;
            var sprite_icon;
            if (nodedata.icon && nodedata.icon !== '' && nodedata.icon !== null && nodedata.icon !== false)
            {
                /*
                 var isSprite = this.getSpriteIcon(nodedata);
                 
                 if (isSprite && nodedata.controller !== 'options')
                 {
                 img = $('<span class="menu-sprite ' + isSprite + '"></span>');
                 sprite_icon = isSprite;
                 }
                 else if (isSprite && nodedata.controller === 'options')
                 {
                 img = $('<span class="cfg ' + isSprite + '"></span>');
                 sprite_icon = isSprite;
                 }
                 else
                 {
                 
                 icon_url = this.getIcon(nodedata);
                 img = $('<img>').attr({
                 width: 16,
                 height: 16,
                 alt: '',
                 src: icon_url
                 });
                 
                 }
                 
                 */
                icon_url = this.getIcon(nodedata);
                img = $('<img>').attr({
                    width: 16,
                    height: 16,
                    alt: '',
                    src: icon_url
                });

            }
            else if (!isAppMenu)
            {

                img = $('<img>').attr({
                    width: 16,
                    height: 16,
                    alt: '',
                    src: 'html/style/c9/img/spacer.gif'
                });

            }


            // add click events
            if (nodedata.url !== '#' && !nodedata.click && !nodedata.call)
            {



                a.append(img).append(nodedata.label);

                a.data('itemData', {
                    id: nodedata.id,
                    label: nodedata.label,
                    popup: nodedata.popup,
                    resizeable: nodedata.resizeable,
                    url: nodedata.url,
                    sprite_icon: sprite_icon,
                    icon: icon_url,
                    isAddon: nodedata.isAddon,
                    ajax: (typeof nodedata.ajax != 'undefined' ? nodedata.ajax : false),
                    isCoreIcon: nodedata.isCoreIcon,
                    modal: nodedata.modal,
                    useWindow: nodedata.useWindow,
                    minwidth: nodedata.minwidth,
                    minheight: nodedata.minheight,
                    controller: nodedata.controller,
                    action: nodedata.action,
                    extraOptions: [changeItemUrl],
                    onBeforeCall: nodedata.onBeforeCall,
                    onAfterCall: nodedata.onAfterCall
                });

                if (nodedata.controller) {
                    itemLi.attr('controller', nodedata.controller);
                }
                if (nodedata.action) {
                    itemLi.attr('action', nodedata.action);
                }

                itemLi.append(a);
            }
            else if (typeof nodedata.call === 'string')
            {

                a.append(img).append(nodedata.label).addClass('interncall');

                var d = $.extend({
                    id: nodedata.id,
                    label: nodedata.label,
                    resizeable: nodedata.resizeable,
                    popup: nodedata.popup,
                    url: nodedata.url,
                    sprite_icon: sprite_icon,
                    icon: icon_url,
                    isAddon: nodedata.isAddon,
                    ajax: (typeof nodedata.ajax != 'undefined' ? nodedata.ajax : false),
                    isCoreIcon: nodedata.isCoreIcon,
                    modal: nodedata.modal,
                    useWindow: nodedata.useWindow,
                    minwidth: nodedata.minwidth,
                    minheight: nodedata.minheight,
                    controller: nodedata.controller,
                    action: nodedata.action,
                    extraOptions: [changeItemUrl],
                    onBeforeCall: nodedata.onBeforeCall,
                    onAfterCall: nodedata.onAfterCall
                }, nodedata);

                a.data('itemData', d);


                if (nodedata.controller) {
                    itemLi.attr('controller', nodedata.controller);
                }
                if (nodedata.action) {
                    itemLi.attr('action', nodedata.action);
                }

                itemLi.append(a);
            }
            else
            {
                // is only a label
                a.append(img).append(nodedata.label);
                itemLi.append(a);
            }

        }




        // add seperator
        if (nodedata.type && (nodedata.type === 'separator' || nodedata.type === 'line'))
        {
            $('<div>').addClass('menu-separator').appendTo(itemLi);
            itemLi.addClass('separator');
            return itemLi;
        }




        // has onclick
        if (nodedata.click)
        {
            itemLi.addClass('noclick');
            itemLi.click(function (e) {
                e.preventDefault();
                this.blur();
                eval(nodedata.click);
                return false;
            });
        }



        // add sub items
        if (Tools.isObject(nodedata.items) && nodedata.items.length)
        {


            if (nodedata.icon && nodedata.icon !== '' && nodedata.icon !== null)
            {

                icon_url = this.getIcon(nodedata);
                img = $('<img>').attr({
                    width: 16,
                    height: 16,
                    alt: '',
                    src: icon_url
                });

                /*
                 img = $('<img>').attr({
                 width: 16,
                 height: 16,
                 alt: '',
                 'class': 'spacer',
                 src: 'html/style/c9/img/spacer.gif'
                 });
                 
                 */
            }
            else
            {
                /*
                 img = $('<img>').attr({
                 width: 16,
                 height: 16,
                 alt: '',
                 src: 'html/style/c9/img/spacer.gif'
                 });
                 */
            }

            var ie_fix = '';
            if ($.browser.msie)
            {
                ie_fix = ' style="float:left"';
            }


            // add submenu label
            itemLi.append($('<span' + ie_fix + ' class="menu-group">').append(img).append(nodedata.label).append('<em/>'));
            var container = $('<div>').addClass('submenu').css({
                position: 'absolute'
            }).hide();

            var ul = $('<ul>');
            ul.appendTo(container);
            var i = 0, childs;

            for (childs in nodedata.items) {
                ul.append(this.buildChildNode(nodedata.items[childs], isAppMenu, changeItemUrl));
            }

            itemLi.append(container);
            container.appendTo(itemLi);
        }



        return itemLi;
    },
    bindEvents: function (containerID, isAppMenu)
    {
        var showTimer, myself = this;

        if (isAppMenu)
        {
            if (Tools.isString(containerID))
            {
                $("#" + containerID + " ul:first li").unbind("mouseover");
                $("#" + containerID + " ul:first li").bind('mouseover', function (e) {

                    if (!$(this).hasClass('active') && $(this).parent().find('li.active').length || $('#Start-Menu').hasClass('active'))
                    {
                        $(this).parent().find('li.active').click();
                        $(this).click();
                    }
                });
            }
        }


        if (Tools.isString(containerID))
        {
            var selector = (isAppMenu == true ? "#" + containerID + " ul:first li" : "#" + containerID);

            $(selector).find('li').unbind("click mouseenter mouseleave");

            $(selector).find('> ul > li').addClass('is-root');

            $(selector).find('li.is-root').bind("click", function (e) {

                // menuitem has no children
                if (!$(this).find('li').length)
                {
                    // $(this).removeClass("active");
                    return;
                }


                var lastActive = $(this).parent().find('li.active');

                lastActive.removeClass("active").find('div.submenu:first').hide();
                // lastActive.find('li.active').removeClass("active");

                $(this).addClass("active");
                $(this).parents('li').addClass("active");

                $(this).find('div.submenu:first').show();
            }).hover(function () {
                if ($(this).parent().find('li.is-root.active').length && $(this).parent().find('li.is-root.active').get(0) !== this && $(this).hasClass('is-root')) {
                    $(this).trigger('click');
                }
            }, function () {

            });

            if (!isAppMenu) {

                $(selector).find('li:not(.is-root)').unbind('click').bind("click", function (e) {

                    if (!$(e.target).hasClass('is-root')) {
                        // click menu
                        if ($(this).find('> span.item').length == 1 && $(this).find('> span.item').data('itemData')) {
                            myself.executeMenuItem($(this), $(this).find('> span.item:first').data('itemData'), selector);
                        }
                    }
                });
            }

            var t, rootLi = $(selector).find('li.is-root');

            $(selector).find('li:not(.is-root)').bind("mouseenter", function (e) {
                $(this).addClass('o');



                $(this).parents('li:not(.o)').addClass('o');
                $(selector).find('li:not(.o)').removeClass("active").find('li:not(.o)').removeClass("active");

                $(selector).find('li.o').removeClass('o').addClass("active");
                $(this).removeClass('o').addClass("active");



            }).bind("mouseleave", function (e) {
                var self = this;
                setTimeout(function () {
                    if (!$(self).find('div.submenu:first').length) {
                        $(self).removeClass("active");
                    }
                }, 50);
            });
        }
    },
    executeMenuItem: function (el, data, selector) {
        //console.log(data);
        var self = this;

        setTimeout(function () {
            $(selector).find('li.active').removeClass('active');
            $(selector).find('div.submenu:visible').hide();
        }, 50);


        if (data.ajax && data.ajax != 0) {


            $.get(data.url, function (dat) {
                if (Tools.responseIsOk(dat)) {


                    if (dat.msg && dat.msg != '') {
                        Notifier.info(dat.msg);
                    }
                }
                else {
                    console.log(dat);
                }
            });
        }
		else if ( data.call && data.call == 'appSettings') {

			var type = '';
			if ( data.controller === 'plugin')
			{
				type = 'plugin';
			}

			if ( data.url.match(/plugin=([a-z0-9]+)/ig) ) {

				var res = data.url.match(/plugin=([a-z0-9]+)/ig);
				var n = res[0].split('=');
				var name = n[1];
				data.url = 'admin.php?adm=settings&action=edit&group=' + name + '&type=' + type;
			}


			Core.Tabs.load(data);
		}
        else {
            Core.Tabs.load(data);
        }
    },
    addMainMenuItemEvents: function (isAppMenu)
    {
        var self = this;

        // make menuitem draggable & clickable
        $('#Start-Menu').find('a').each(function () {
            var _self = $(this), itemData = $(this).data('itemData');

            if (itemData)
            {
                var icon = '', li = $(this).parents('li:first');
                if (itemData.sprite_icon !== false)
                {
                    if ($('span', $(this)).hasClass('cfg'))
                    {
                        icon = Desktop.baseURL + 'html/style/apple/img/' + 'cfgitems/' + itemData.sprite_icon + '.png';
                    }
                    else
                    {
                        icon = Desktop.baseURL + 'html/style/apple/img/' + 'pulldownmenu/' + itemData.sprite_icon + '.png';
                    }
                }
                else
                {
                    icon = $(this).find('img').attr('src');
                }

                li.data('itemData', $.extend({}, itemData));

                // li.unbind('click');
                li.bind('click', function (ev)
                {
                    if ($(this).is(':ui-draggable'))
                    {
                        $(this).draggable('disable');
                    }
                    ev.preventDefault();

                    document.clickOffset = {left: 10, top: 10}; // used for window animation from to :) and used for close window animate back to open pos



                    var _itemData = $(this).data('itemData');

                    self.hideMenu();
                    if (_self.hasClass('interncall'))
                    {
                        Application.callAction(ev, $(this).attr('rel'));
                        return;
                    }

                    if (typeof _itemData.ajax !== 'undefined' && _itemData.ajax == 2)
                    {
                        window.open(_itemData.url);
                        return;
                    }
                    else if (typeof _itemData.ajax !== 'undefined' && _itemData.ajax == 1)
                    {

                        Desktop.Tools.maskDesktop('Bitte warten...');
                        $.get(Tools.prepareAjaxUrl(_itemData.url), function (_data)
                        {
                            if (Tools.responseIsOk(_data))
                            {
                                Desktop.Tools.unmaskDesktop();
                                if (typeof _data.msg === 'string')
                                {
                                    Notifier.display('info', _data.msg);
                                }


                                // Build Float Box
                                if (typeof _data.html === 'string')
                                {
                                    //display_info(_data.html, false, menu.label);
                                    var options = {};
                                    options.title = _itemData.label;
                                    Tools.createPopup(_data.html, options);
                                    return;
                                }
                            }
                            else
                            {
                                Desktop.Tools.unmaskDesktop();
                                alert(_data.msg);
                            }

                        }, 'json');
                        return;
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

                        var appData = Tools.extractAppInfoFromUrl(_itemData.url);
                        opts.Controller = appData.controller;
                        opts.Action = appData.action;
                        opts.WindowURL = Tools.prepareAjaxUrl(_itemData.url);
                        opts.WindowID = Desktop.getHash(opts.WindowURL);
                        // Desktop.ajaxData = data ;
                        opts.Skin = Desktop.settings.Skin;
                        opts.WindowTitle = _itemData.label;
                        opts.WindowDesktopIconFile = (icon ? icon : '');
                        opts = $.extend({}, opts, _itemData);

                        Application.setAppFromUrl(opts.WindowURL);

                        // create Dock Icon
                        var dockIcon = Dock.createDockIcon(ev, opts);

                        if (dockIcon)
                        {
                            // reset current ajaxData
                            Desktop.ajaxData = {};
                            //      Desktop.getAjaxContent(opts, function() {
                            dockIcon.trigger('click');
                            //      });
                        }

                        return;
                    }


                });
            }

        });
    },
    getSpriteIcon: function (menu)
    {
        var icon = false;
        if (typeof menu.isCoreIcon == 'undefined' && typeof menu.isPluginIcon == 'undefined' || menu.isCoreIcon === true || menu.controller === 'options')
        {
            var names = menu.icon.split('.');
            names.pop();
            var realName = names.join('');
            var name = realName.split('/');
            icon = name.pop();
        }

        return icon;
    },
    getIcon: function (menu)
    {

        var icon = null;
        if (typeof menu.isCoreIcon == 'undefined' && typeof menu.isPluginIcon == 'undefined' || menu.isCoreIcon === true)
        {
            icon = menu.icon;
        }
        else if (typeof menu.isPluginIcon !== 'undefined' || menu.isPluginIcon === true)
        {
            icon = menu.icon;
        }
        else {
            icon = 'html/style/c9/img/' + menu.icon;
        }

        return icon;
    },
    hide: function (el) {
        $('#NavItems').find('li.active').removeClass('active');
        $('#NavItems').find('div.submenu').hide();
    },
    hideMenu: function (animationSpeed)
    {
        if (animationSpeed === 0)
        {
            $('#Start-Menu .active:not(#console)').removeClass('active');
            $('#Start-Menu, #Start-Menu div.submenu').hide();
        }
        else
        {
            $('#Start-Menu').animate({
                height: 0
            }, 150, function () {
                $(this).hide();
                $('#Start-Menu .active:not(#console)').removeClass('active');
                $('#Start-Menu div.submenu').hide();
            });
        }

        $('#Start-Menu-Button').removeClass('active');
        this.hideAppMenu();
    },
    removeAppMenu: function (event, app, removeCache)
    {
        if (removeCache === true)
        {
            for (var i = 0; i < this.AppMenuCache.length; i++) {
                if (this.AppMenuCache[i][0] === app) {
                    delete(this.AppMenuCache[i]);
                    break;
                }
            }
        }

        $('#App-Menu').empty();
        return true;
    },
    /**
     * 
     * @param {type} app
     * @param {type} controller
     * @param {type} action
     * @param {type} extraOptions
     * @returns {undefined}
     */
    changeAppMenuCache: function (app, controller, action, extraOptions)
    {
        var i = 0, cache = null, l = this.AppMenuCache.length;
        for (; i < l; i++) {
            if (this.AppMenuCache[i][0] === app) {
                cache = this.AppMenuCache[i][1];
                break;
            }
        }

        if (typeof cache === 'object')
        {
            for (var x = 0; x < cache.length; x++)
            {
                if (typeof cache[x].extraOptions === 'object')
                {
                    cache[x].extraOptions.push(extraOptions.shift());
                }
                else
                {
                    cache[x].extraOptions = [extraOptions.shift()];
                }

                if (typeof cache[x].items === 'object' && cache[x].items.length > 0)
                {
                    cache[x].items = this.changeSubs(cache[x].items, extraOptions);
                }

            }
            this.AppMenuCache[i] = cache;
        }
    },
    changeSubs: function (items, extraOptions)
    {
        var l = items.length;
        for (var x = 0; x < l; x++)
        {
            if (typeof items[x].items === 'object' && items[x].items.length > 0)
            {
                items[x].items = this.changeSubs(items[x].items, extraOptions);
            }
            if (typeof items[x].extraOptions === 'object')
            {
                items[x].extraOptions.push(extraOptions.shift());
            }
            else
            {
                items[x].extraOptions = [extraOptions.shift()];
            }
        }

        return items;
    },
    /**
     * 
     * @param {type} app
     * @param {type} data
     * @param {type} changeItemUrl
     * @returns {unresolved}
     */
    setAppMenu: function (app, data, changeItemUrl)
    {


        var cache = null, l = this.AppMenuCache.length;

        for (var i = 0; i < l; i++) {
            if (this.AppMenuCache[i][0] == app) {
                cache = this.AppMenuCache[i][1];
                break;
            }
        }

        if (cache === null)
        {
            this.AppMenuCache.push([app, data]);
            cache = this.AppMenuCache[this.AppMenuCache.length - 1][1];
        }


        if (typeof cache === 'object')
        {
            this.buildMenu(cache, app, changeItemUrl);

            // bind menuitem events
            this.bindAppMenuEvents(app, changeItemUrl);
        }
        else
        {
            $('#App-Menu').empty();
        }

        return;




        var prepared = [];
        if (Tools.isObject(data.apiMenu) &&
                Tools.isObject(data.apiMenu.mainMenu) &&
                Tools.isObject(data.apiMenu.mainMenu.item)) {


            for (var i = 0; i < data.apiMenu.mainMenu.item.length; i++)
            {

                var main = data.apiMenu.mainMenu.item[i].attributes; // root

                var dat = {};
                var label = main.label;
                var id = main.id;
                var subitems = null;
                if (Tools.isObject(data.apiMenu.items))
                {
                    subitems = [];
                    for (var itm in data.apiMenu.items)
                    {

                        var item = data.apiMenu.items[itm].attributes;
                        if (item.rel == id)
                        {
                            for (var x in data.apiMenu.items[itm].item)
                            {
                                var itemData = data.apiMenu.items[itm].item[x].attributes;
                                subitems.push(itemData);
                            }
                        }
                    }
                }

                prepared.push({
                    id: id,
                    controller: app,
                    call: main.call,
                    type: main.type,
                    dynamicItem: main.dynamicItem,
                    mode: main.mode,
                    label: label,
                    items: subitems
                });
            }
        }



        var cache = null;
        for (var i = 0; i < this.AppMenuCache.length; i++) {
            if (this.AppMenuCache[i][0] == app) {
                cache = this.AppMenuCache[i][1];
                break;
            }
        }


        if (cache == null)
        {

            if (
                    Tools.isObject(data.apiMenu) &&
                    Tools.isObject(data.apiMenu.mainMenu) &&
                    Tools.isObject(data.apiMenu.mainMenu.item))
            {
                var prepared = [];
                for (var i = 0; i < data.apiMenu.mainMenu.item.length; i++)
                {

                    var main = data.apiMenu.mainMenu.item[i].attributes; // root

                    var dat = {};
                    var label = main.label;
                    var id = main.id;
                    var subitems = null;
                    if (Tools.isObject(data.apiMenu.items))
                    {
                        subitems = [];
                        for (var itm in data.apiMenu.items)
                        {

                            var item = data.apiMenu.items[itm].attributes;
                            if (item.rel == id)
                            {
                                for (var x in data.apiMenu.items[itm].item)
                                {
                                    var itemData = data.apiMenu.items[itm].item[x].attributes;
                                    subitems.push(itemData);
                                }
                            }
                        }
                    }

                    prepared.push({
                        id: id,
                        controller: app,
                        call: main.call,
                        type: main.type,
                        dynamicItem: main.dynamicItem,
                        mode: main.mode,
                        label: label,
                        items: subitems
                    });
                }

                this.AppMenuCache.push([app, prepared]);
                for (var i = 0; i < this.AppMenuCache.length; i++) {
                    if (this.AppMenuCache[i][0] == app) {
                        cache = this.AppMenuCache[i][1];
                        break;
                    }
                }


            }

        }

        //console.log([cache]);

        if (cache)
        {
            this.buildMenu(cache, app);
            // bind menuitem events
            this.bindAppMenuEvents(app);
        }
        else
        {
            $('#App-Menu').empty();
        }
    },
    hideAppMenu: function ()
    {
        $('#App-Menu li.active').removeClass('active');
        $('.submenu', $('#App-Menu')).removeClass('active').hide();
    },
    bindAppMenuEvents: function (app, changeItemUrl)
    {
        var self = this;
        $('#App-Menu').find('li.root-item li').attr('app', app).unbind('click');
        $('#App-Menu').find('li.root-item li').click(function (e)
        {
            // DesktopConsole.resetZindex();
            self.handleAppMenuClick(e, $(this).attr('app'));
            $(e.target).parents('.submenu').hide();
            $('.submenu', $('#App-Menu')).hide();
            $('.active', $('#App-Menu')).removeClass('active');
            return false;
        });

        $('#App-Menu').find('ul:first li.root-item').attr('app', app).unbind('click hover');
        $('#App-Menu').find('ul:first li.root-item').click(function (e)
        {
            if ($(e.target).parents('.submenu').length)
            {
                return true;
            }

            //  DesktopConsole.resetZindex();
            self.handleAppMenuClick(e, $(this).attr('app'));
            $('#Start-Menu').hide();
            $('#NavItems .submenu').hide();
            $('#NavItems .active,#Start-Menu-Button').removeClass('active');

            if ($(this).hasClass('active'))
            {
                $('.submenu', $('#App-Menu')).hide();
                $('.active', $(this)).removeClass('active');
                $(this).parents().removeClass('active');
                $(this).removeClass('active');
            }
            else
            {

                $('#App-Menu li.active').removeClass('active');
                $('.submenu', $('#App-Menu')).removeClass('active').hide();
                $(this).parent().find('.active').removeClass('active');
                $(this).addClass('active');
                $('.submenu:first', $(this)).css({
                    left: $(this).position().left,
                    position: 'absolute',
                    top: ($('#Taskbar').offset().top + $('#Taskbar').outerHeight(true)),
                    zIndex: 99999
                }).show();
            }

            return false;
        });


        $('#App-Menu').find('ul:first li.root-item').bind('mouseover', function (e) {
            if ($('#Start-Menu-Button').hasClass('active') || (!$(this).hasClass('active') && $(this).parent().find('li.root-item.active').length > 0)) {
                $(this).click();
                //return;
            }
        });

        $(document).bind('click', function (e) {
            if ($(e.target).parents('#App-Menu').length > 0 || $(e.target).attr('id') == 'App-Menu')
            {
                return;
            }

            $('#App-Menu li.active').removeClass('active');
            $('.submenu', $('#App-Menu')).removeClass('active').hide();
        });





        $('ul:first li.root-item li', $('#App-Menu')).unbind("mouseover mouseout");
        $('ul:first li.root-item li', $('#App-Menu')).bind("mouseover mouseout", function (event) {
            if (event.type == 'mouseover') {
                $(this).addClass('active');
            }
            else {
                $(this).removeClass('active');
            }
        });
    },
    handleAppMenuClick: function (event, app)
    {
        var target = $(event.target);
        if (typeof target.data('itemData') == 'object')
        {
            var data = target.data('itemData');
            if (typeof data.controller == 'undefined' || data.controller == null)
            {
                console.log('error data.call ' + data.call);
                return;
            }
            $('#App-Menu li.active').removeClass('active');
            $('.submenu', $('#App-Menu')).removeClass('active').hide();


            if ((typeof data.dynamicItem != 'undefined' && (data.dynamicItem == 'true' || data.dynamicItem == true)) &&
                    typeof data.call != 'undefined' && data.call != '') {

                if (typeof data.mode != 'undefined' && data.mode != '')
                {
                    // console.log('data.call ' + data.call + ' mode:' + data.mode);
                    Application.callAction(event, data.call, data.controller, data);
                }
                else
                {
                    //  console.log('dynamicItem data.call ' + data.call + ' callAjaxAction');
                }
            }
            else if (typeof data.call != 'undefined' && data.call != '')
            {
                //  console.log('data.call ' + data.call + ' App:' + app);
                Application.callAction(event, data.call, data.controller, (data.action ? data.action : null), data);
            }
            else if (typeof data.action != 'undefined' && data.action != '')
            {
                //  console.log('data.call ' + data.call + ' callAjaxAction');
                Application.callAjaxAction(event, data, data.controller, data.action, data.ajax);
            }

        }
    }

};