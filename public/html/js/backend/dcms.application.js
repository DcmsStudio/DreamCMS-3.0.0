var gridT;
Application = {
    pathCache: [],
    Cache: [],
    appController: null,
    focusWindowID: null,
    currentAction: null,
    currentWindowID: null,
    activeUrl: null,
    menu: {},
    error: function (msg)
    {
        Debug.error(msg);
    },
    log: function (msg)
    {
        Debug.info(msg);
    },
    runnerGrid: false,
    runnerGridT: null,
    onWindowDragStop: function (wm, callback)
    {
        if (wm.settings.status != 'max' && wm.settings.status != 'min' && wm.settings.status != 'restore') {
            $.post('admin.php', {
                storeWindowPosition: true,
                windowID: wm.id,
                url: wm.settings.WindowURL,
                windowpos: wm.$el.position().left + '|' + wm.$el.position().top,
                windowsize: wm.$el.width() + '|' + wm.$el.height(),
                screensize: $(window).width() + '|' + $(window).height(),
            }, function (data) {

                if (!Tools.responseIsOk(data))
                {
                    Debug.info([data]);
                }

                if (typeof callback == 'function')
                {
                    callback();
                }
            });
        }
    },
    onBeforeWindowShow: function (event, wmObj, callback)
    {

        if (typeof callback == 'function')
        {
            callback();
        }
    },
    onAfterWindowShow: function (event, wm, callback)
    {
        if (typeof callback == 'function')
        {
            callback();
        }
    },
    onBeforeWindowClose: function (event, wm, _callback)
    {
        var self = this;

        var openers = $('div.isWindowContainer[opener="' + wm.id + '"]');

        if (wm.$el.data('formConfig'))
        {
            if (wm.$el.data('formConfig').isDirty)
            {



                jConfirm(cmslang.form_dirty, cmslang.alert, function (ok) {
                    if (ok)
                    {

                        $('.ace-intellisense').hide();

                        if (openers.length) {

                            SidePanel.hide();

                            openers.each(function (i) {

                                Win.removeWindowFormUi(wm.id);
                                Doc.unload(wm.id);

                                SidePanel.empty();

                                if (typeof destroyTemplateEditor == 'function') {
                                    destroyTemplateEditor(wm.id);
                                }

                                $(this).remove();

                                if (i + 1 == openers.length) {
                                    wm.$el.removeData('formConfig'); // remove the form data (little patch if loop to onBeforeWindowClose)

                                    self.sendRollback(event, wm, function () {
                                        if (typeof _callback == 'function')
                                        {
                                            _callback();
                                        }
                                    });
                                }
                            });
                        }
                        else {
                            SidePanel.hide();

                            wm.$el.removeData('formConfig'); // remove the form data (little patch if loop to onBeforeWindowClose)

                            Win.removeWindowFormUi(wm.id);
                            Doc.unload(wm.id);

                            SidePanel.empty();

                            if (typeof destroyTemplateEditor == 'function') {
                                destroyTemplateEditor(wm.id);
                            }

                            self.sendRollback(event, wm, function () {
                                if (typeof _callback == 'function')
                                {
                                    _callback();
                                }
                            });
                        }
                    }
                    else
                    {
                        wm.stopEvent('close');
                    }
                });
            }
            else
            {
                $('.ace-intellisense').hide();


                this.sendRollback(event, wm, function () {

                    SidePanel.hide();

                    Doc.unload(wm.id);
                    Win.removeWindowFormUi(wm.id);

                    if (openers.length) {

                        openers.each(function (i) {

                            Win.removeWindowFormUi(wm.id);


                            SidePanel.empty();

                            if (typeof destroyTemplateEditor == 'function') {
                                destroyTemplateEditor(wm.id);
                            }


                            $(this).remove();

                            if (i + 1 == openers.length) {

                                if (typeof _callback == 'function')
                                {
                                    _callback();
                                }
                            }
                        });

                    }
                    else {
                        Win.removeWindowFormUi(wm.id);
                        Doc.unload(wm.id);

                        SidePanel.empty();

                        if (typeof destroyTemplateEditor == 'function') {
                            destroyTemplateEditor(wm.id);
                        }

                        if (typeof _callback == 'function')
                        {
                            _callback();
                        }
                    }
                }
                );
            }
        }
        else
        {
            $('.ace-intellisense').hide();


            this.sendRollback(event, wm, function () {
                Win.removeWindowFormUi(wm.id);
                Doc.unload(wm.id);
                SidePanel.hide();

                if (openers.length) {
                    openers.each(function (i) {

                        Win.removeWindowFormUi(wm.id);
                        Doc.unload(wm.id);
                        SidePanel.empty();
                        if (typeof destroyTemplateEditor == 'function') {
                            destroyTemplateEditor(wm.id);
                        }

                        $(this).remove();

                        if (i + 1 == openers.length) {


                            if (typeof _callback == 'function')
                            {
                                _callback();
                            }
                        }
                    });
                }
                else {


                    Win.removeWindowFormUi(wm.id);
                    Doc.unload(wm.id);
                    SidePanel.empty();

                    if (typeof destroyTemplateEditor == 'function') {
                        destroyTemplateEditor(wm.id);
                    }

                    if (typeof _callback == 'function')
                    {
                        _callback();
                    }
                }
            });
         }
    }
    ,
    onAfterWindowClose: function (event, wm, callback)
    {

        // @todo add default menu
        if (wm.$el)
        {
            this.closeApplication(event, wm.$el);
        }

        // remove menu for the window
        //$('#App-Menu').empty();
        Win.removeWindowFormUi(wm.id);

        // focus the opener window after close current window
        if (Tools.isString(wm.settings.opener))
        {
            $('#' + wm.settings.opener).trigger('click');
        }
        else {
            $('#App-Menu').empty();
        }
        if (typeof callback == 'function')
        {
            callback();

        }
    }
    ,
    onFocusWindow: function (event, wm, callback)
    {
        // set menu for the window
        Win.setActive(wm.id);
        this.currentWindowID = wm.id;
        this.createAppMenu(wm.settings.Controller, wm.settings.Action, wm.settings);

        if (typeof callback == 'function')
        {
            callback();
        }
    }
    ,
    onUnFocusWindow: function (event, wm, callback)
    {
        // remove menu for the window
        $('#App-Menu').empty();

        if (typeof callback == 'function')
        {
            callback();
        }
    }
    ,
    sendRollback: function (event, wm, callback)
    {
        if (wm.win.attr('rollback'))
        {
            var postData = Tools.convertUrlToObject(wm.get('Url'));
            postData.ajax = true;
            postData.transrollback = true;

            $.ajax({
                type: "POST",
                url: 'admin.php',
                'data': postData,
                timeout: 10000,
                dataType: 'json',
                cache: false,
                async: false,
                success: function (data)
                {
                    if (typeof callback == 'function') {
                        callback();
                    }
                }
            });
        }
        else
        {
            if (typeof callback == 'function') {
                callback();
            }
        }
    }
    ,
    /**
     * 
     * @param {type} elementID
     * @param {type} options
     * @returns {undefined}
     */
    Grid: function (elementID, options)
    {
        var self = this, windowEl = $('#' + this.currentWindowID), element = windowEl.find('#' + elementID);

        if (this.runnerGrid || element.length == 0 || !windowEl.data('WindowManager'))
        {
            gridT = setTimeout(function () {
                self.Grid(elementID, options);
            }, 50);
        }
        else
        {
            clearTimeout(gridT);

            if (!element.data('windowGrid'))
            {
                this.runnerGrid = true;
                var g = new DataGrid();
                windowEl.data('WindowManager').set('gridloaded', false);
                g.create(element, windowEl, options);

                this.runnerGrid = false;
            }
        }
    }
    ,
    /**
     * 
     * @param {type} url
     * @returns {undefined}
     */
    setAppFromUrl: function (url)
    {
        var info = Tools.extractAppInfoFromUrl(url);
        this.appController = info.controller;
        this.currentAction = info.action;
    }
    ,
    /**
     * 
     * @returns {@exp;@call;$@call;data}
     */
    getWindowData: function ()
    {
        return $('#' + Win.windowID).data('WindowManager');
    }
    ,
    /**
     * 
     * @type type
     */
    getWindow: function ()
    {
        return $('#' + Win.windowID);
    }
    ,
    /**
     * 
     * @param {type} controller
     * @param {type} size
     * @returns {String}
     */
    getAppIcon: function (controller, size, isPlugin)
    {
        var path;

        if (isPlugin !== true)
        {
            path = 'System/Modules/' + controller.charAt(0).toUpperCase() + controller.slice(1).toLowerCase() + '/Resources/' +
                    controller.charAt(0).toUpperCase() + controller.slice(1).toLowerCase();
        }
        else
        {
            path = 'Packages/plugins/' + controller.charAt(0).toUpperCase() + controller.slice(1).toLowerCase() + '/Resources/' +
                    controller.charAt(0).toUpperCase() + controller.slice(1).toLowerCase();
        }

        switch (size)
        {
            case 128:
                return path + '_128x128.png';
            case 48:
                return path + '_48x48.png';
            case 32:
                return path + '_32x32.png';
            case 16:
                return path + '_16x16.png';
        }



        this.error('Invalid Icon size: ' + size);
    }
    ,
    getCurrentApp: function ()
    {
        return this.appController;
    }
    ,
    menuTimer: null,
    /**
     * 
     * @param {type} controller
     * @param {type} action
     * @param {type} extraData
     * @returns {undefined}
     */
    appendExtraDataToCurrentMenu: function (controller, action, extraData)
    {
        var actionRequired = typeof action == 'string' ? true : false;
        if (action == 'index') {
            action = false;
            actionRequired = false;
        }

        var cache = null;
        for (var i = 0; i < this.Cache.length; i++)
        {
            if (this.Cache[i][0] == controller && this.Cache[i][1] == 'menu' && actionRequired && this.Cache[i][2] == action) {
                cache = this.Cache[i][3];
            }
            else if (this.Cache[i][0] == controller && this.Cache[i][1] == 'menu' && !actionRequired && this.Cache[i][2] == false) {
                cache = this.Cache[i][3];
            }
        }
    }
    ,
    currentActiveMenu: null,
    /**
     * 
     * @param {type} controller
     * @param {type} action
     * @returns {unresolved}
     */
    createAppMenu: function (controller, action, opt)
    {


        if (Desktop.windowWorkerOn || Grid.runner)
        {
            this.menuTimer = setTimeout(function () {
                Application.createAppMenu(controller, action, opt);
            }, 10);
        }
        else
        {
            clearTimeout(this.menuTimer);



            var actionRequired = typeof action == 'string' ? true : false;
            if (action === 'index') {
                action = false;
                actionRequired = false;
            }

            var cache = null, l = this.Cache.length;
            for (var i = 0; i < l; i++) {
                if (this.Cache[i][0] === controller && this.Cache[i][1] === 'menu' && actionRequired && this.Cache[i][2] === action) {
                    cache = this.Cache[i][3];
                }
                else if (this.Cache[i][0] === controller && this.Cache[i][1] === 'menu' && !actionRequired && this.Cache[i][2] === false) {
                    cache = this.Cache[i][3];
                }
            }

            if (cache === null)
            {
                return;
            }

            DesktopMenu.setAppMenu(controller, cache, opt);

            if (typeof opt != 'undefined' && typeof opt.onAfterMenuCreated === 'function')
            {
                opt.onAfterMenuCreated(DesktopMenu.getMenuContainer());

            }
            else if (typeof opt != 'undefined' && typeof opt.onAfterMenuCreated === 'string' && opt.onAfterMenuCreated !== '')
            {

                if (function_exists(opt.onAfterMenuCreated))
                {
                    eval(opt.onAfterMenuCreated + '(DesktopMenu.getMenuContainer())');
                }


            }
        }
    }
    ,
    triggerMenuFocus: function ()
    {

    }
    ,
    enableMenuitems: function (items)
    {
        if (typeof items == 'undefined' || items == '*')
        {
            $('#App-Menu').find('li').each(function () {
                $(this).removeClass('menuitem-disabled').removeClass('menuitem-hide');
            });
        }
        else if (typeof items == 'string')
        {
            var _items = items.split(',');
            for (var x = 0; x < _items.length; ++x) {
                $('#App-Menu').find('li[action=' + _items[x] + ']').each(function () {
                    $(this).removeClass('menuitem-disabled').removeClass('menuitem-hide');
                });
            }
        }
    }
    ,
    disableMenuitems: function (items)
    {
        if (typeof items == 'string' && items != '*')
        {
            var _items = items.split(',');
            for (var x = 0; x < _items.length; ++x) {
                $('#App-Menu').find('li[action=' + _items[x] + ']').each(function () {
                    $(this).addClass('menuitem-disabled');
                });
            }
        }
    }
    ,
    hideMenuitems: function (items)
    {
        if (typeof items == 'string' && items != '*')
        {
            var _items = items.split(',');
            for (var x = 0; x < _items.length; ++x) {
                $('#App-Menu').find('li[action=' + _items[x] + ']').each(function () {
                    $(this).addClass('menuitem-hide');
                });
            }
        }
    }
    ,
    /**
     * 
     * @param {type} controller
     * @returns {unresolved}
     */
    getAppPath: function (controller)
    {
        for (var i = 0; i < this.pathCache.length; i++) {
            if (this.pathCache[i][0] == controller) {
                return this.pathCache[i][1];
            }
        }

        var url = 'admin.php';
        $.post(Tools.prepareAjaxUrl(url), {
            'ajax': true,
            'getAppPath': 1,
            'app': controller
        }, function (data) {
            if (Tools.responseIsOk(data))
            {
                this.pathCache.push([controller, data.path]);
                return data.path;
            }
            else
            {
                Application.error(data.msg);
                return null;
            }
        });
    }
    ,
    /**
     * 
     * @param {type} controller
     * @param {type} action
     * @param {type} options
     * @returns {undefined}
     */
    loadApp: function (controller, action, options)
    {
        this.appController = controller;
        var url = 'admin.php';
        options = $.extend({}, {
            'ajax': true,
            'loadapp': 1,
            'controller': controller,
            'action': (action ? action : 'index')
        }, options);
        $.post(Tools.prepareAjaxUrl(url), options, function (data) {
            if (Tools.responseIsOk(data))
            {
                return data;
            }
            else
            {
                Application.error(data.msg);
                return null;
            }
        });
    }
    ,
    /**
     * 
     * @param {type} controller
     * @param {type} action
     * @param {type} data
     * @returns {unresolved}
     */
    cacheCurrentApp: function (controller, action, data)
    {

        if (typeof controller == 'undefined' || typeof controller != 'string' || !Tools.exists(data, 'applicationMenu'))
        {
            this.error('Application error in cache current app');
            return;
        }

        if (typeof data.applicationMenu != 'object' || (typeof data.applicationMenu == 'object' && data.applicationMenu.length == 0))
        {

            data.applicationMenu = [{
                    'title': data.applicationTitle,
                    'label': data.applicationTitle,
                    id: 'basic',
                    items: [
                        {
                            label: 'Über ' + data.applicationTitle + ' ...',
                            title: 'Über ' + data.applicationTitle + ' ...',
                            call: 'aboutApp'
                        }, {
                            label: 'Beenden',
                            title: 'Beenden',
                            call: 'closeApp',
                            shortcut: 'CMD-Q'
                        }]

                }, {
                    'title': 'Hilfe',
                    'label': 'Hilfe',
                    id: 'help',
                    items: [{
                            title: 'Hilfe zum Modul \'' + data.applicationTitle + '\'',
                            label: 'Hilfe zum Modul \'' + data.applicationTitle + '\'',
                            call: 'help'
                        }, {
                            label: 'Update',
                            title: 'Update',
                            call: 'updateApp'
                        }]
                }];
        }

        var actionRequired = typeof action == 'string' ? true : false;
        if (action == 'index') {
            action = null;
            actionRequired = false;
        }

        for (var i = 0; i < this.Cache.length; i++) {

            if (this.Cache[i][0] == controller && this.Cache[i][1] == 'menu' && actionRequired && this.Cache[i][2] == action && this.Cache[i][3] === data.applicationMenu)
            {
                return;
            }
            else if (this.Cache[i][0] == controller && this.Cache[i][1] == 'menu' && !actionRequired && this.Cache[i][2] == false && this.Cache[i][3] === data.applicationMenu)
            {
                return;
            }
        }

        this.Cache.push([controller, 'menu', (actionRequired ? action : false), data.applicationMenu]);
    }
    ,
    /**
     * 
     * @param {type} url
     * @returns {undefined}
     */
    setActiveUrl: function (url)
    {
        this.activeUrl = url;
    }
    ,
    /**
     * 
     * @param object data
     * @returns data
     */
    getActiveUrl: function (data)
    {
        if (typeof this.activeUrl == 'string')
        {
            data.useInputDataUrl = true;
            data.url = this.activeUrl;
        }

        return data;
    }
    ,
    setMenuUrlForID: function (id, url)
    {
        this.menu[id] = url;
    }
    ,
    getMenuUrlForID: function (data)
    {
        if (typeof data.id == 'string')
        {
            if (typeof this.menu[data.id] == 'string')
            {
                data.url = this.menu[data.id];
                data.useInputDataUrl = true;
            }
        }
        return data;
    }
    ,
    /**
     * 
     * @param {type} e
     * @param {type} callAction
     * @param {type} controller
     * @param {type} action
     * @returns {undefined}
     */
    callAction: function (e, callAction, controller, action, data)
    {
        if (typeof callAction == 'string')
        {
            eval('Application.' + callAction + '(e, controller, action, data)');
        }
    }
    ,
    /**
     * 
     * @param {type} e
     * @param {type} _data
     * @param {type} controller
     * @param {type} action
     * @param {type} ajaxOnly
     * @returns {undefined}
     */
    callAjaxAction: function (e, _data, controller, action, ajaxOnly)
    {
        var self = this, useInputDataUrl = false, appData = null, isAddon = false;

        if (typeof _data.isAddon != 'undefined' && _data.isAddon)
        {
            isAddon = true;
        }




        if (typeof _data.onBeforeCall == 'string')
        {
            eval('_data = Application.' + _data.onBeforeCall + '(_data)');

            var str = _data.url;

            str = str.replace(/([\?&])(controller|adm)=([^&]*)/, '');

            str = str.replace(/([\?&])action=([^&]*)/, '');
            str = str.replace(/^([a-z][a-zA-Z0-9_\-\/:\.]*)\.php&(.*)/, '$1.php?$2');



            if (isAddon)
            {
                str += '&adm=plugin';
                str += '&plugin=' + controller;
            }
            else
            {
                str += '&adm=' + controller;
            }

            str += '&action=' + action;

            _data.url = str;

            useInputDataUrl = _data.useInputDataUrl;
        }
        else
        {
            var str = _data.url;
            str = str.replace(/\s*void\(0\)\s*;?/, '');

            if (isAddon)
            {
                str = str.replace(/([\?&])adm=([^&]*)/, '$1adm=plugin&plugin=$2');
            }

            _data.url = str;

        }






        if (typeof controller == 'string' && typeof action == 'string')
        {
            var options = {};
            var url = (useInputDataUrl ? _data.url : 'admin.php?adm=' + (isAddon ? 'plugin&plugin=' + controller : controller) + '&action=' + action + '&ajax=1');
            $.ajaxSetup(
                    {
                        dataType: (typeof _data.dataType == 'string' && _data.dataType ? _data.dataType : "json"),
                        cache: (typeof _data.caching != 'undefined' && _data.caching ? true : false),
                        async: false,
                        timeout: 10000
                    });



            if (typeof ajaxOnly == 'undefined' || !ajaxOnly)
            {
                this.runAjaxRequest(url, controller, function (data) {


                    var winOpt = {
                        WindowURL: url,
                        title: _data.label,
                        WindowTitle: _data.label,
                        Controller: controller,
                        Action: action,
                        WindowMaximize: true,
                        WindowMinimize: true
                    };


                    var content = (typeof data.maincontent === 'string' ? data.maincontent : false)

                    if (data.loadScripts && ((data.loadScripts.css && data.loadScripts.css.length) || (data.loadScripts.js && data.loadScripts.js.length)))
                    {
                        var jsExists = false;

                        if (Tools.exists(data.loadScripts, 'js') && data.loadScripts.js.length)
                        {
                            jsExists = true;
                        }


                        if (Tools.exists(data.loadScripts, 'css'))
                        {
                            for (var x = 0; x < data.loadScripts.css.length; x++)
                            {
                                if (data.loadScripts.css[x].substr(data.loadScripts.css[x].length - 4, data.loadScripts.css[x].length) != '.css')
                                {
                                    data.loadScripts.css[x] += '.css';
                                }

                                var cssh = Desktop.getHash(data.loadScripts.css[x]);
                                if (!$('#' + cssh).length)
                                {

                                    Desktop.loadCss(data.loadScripts.css[x], function (styleTag) {
                                        styleTag.attr('id', cssh).attr('controller', controller);
                                    });

                                }
                            }

                            if (!jsExists)
                            {

                                if (!_data.useWindow)
                                {

                                    winOpt.onBeforeClose = function (event, _wm, callback)
                                    {
                                        Application.runOnBeforeCloseWindow(event, _wm, callback);
                                    };
                                    winOpt.onAfterOpen = function (e, wm, uiContent)
                                    {
                                        $(wm.$el).data('app', controller);
                                        Application.runOnAfterOpenWindow(wm.$el, data);
                                    };
                                    Tools.createPopup(content, winOpt);
                                }
                                else if (_data.useWindow)
                                {
                                    if (Tools.exists(data, 'pageCurrentTitle') && data.pageCurrentTitle != '')
                                    {
                                        winOpt.WindowTitle = data.pageCurrentTitle;
                                    }

                                    if (typeof data.toolbar != 'undefined' || data.toolbar)
                                    {
                                        winOpt.WindowToolbar = data.toolbar;
                                    }
                                    winOpt.loadWithAjax = true;
                                    winOpt.allowAjaxCache = false;
                                    winOpt.WindowURL = url;
                                    winOpt.isSingleWindow = (data.isSingleWindow == true ? true : false);


                                    winOpt.WindowContent = data.maincontent;


                                    Desktop.GenerateNewWindow(winOpt, null, function (obj, objdata, id) {

                                        if (winOpt.SingleWindowID)
                                        {
                                            if (Tools.exists(data, 'pageCurrentTitle') && data.pageCurrentTitle != '') {
                                                Desktop.getActiveWindow().data('WindowManager').setTitle(data.pageCurrentTitle);
                                            }
                                        }
                                    });
                                }
                            }
                        }



                        if (Tools.exists(data.loadScripts, 'js') && data.loadScripts.js.length)
                        {
                            Desktop.loadScripts(data.loadScripts.js, function () {

                                if (!_data.useWindow)
                                {

                                    winOpt.onBeforeClose = function (event, _wm, callback)
                                    {
                                        Application.runOnBeforeCloseWindow(event, _wm, callback);
                                    };
                                    winOpt.onAfterOpen = function (e, wm, uiContent)
                                    {
                                        $(wm.$el).data('app', controller);
                                        Application.runOnAfterOpenWindow(wm.$el, data);
                                    };
                                    Tools.createPopup(content, winOpt);
                                }
                                else if (_data.useWindow)
                                {
                                    if (Tools.exists(data, 'pageCurrentTitle') && data.pageCurrentTitle != '')
                                    {
                                        winOpt.WindowTitle = data.pageCurrentTitle;
                                    }

                                    if (typeof data.toolbar != 'undefined' || data.toolbar)
                                    {
                                        winOpt.WindowToolbar = data.toolbar;
                                    }


                                    winOpt.loadWithAjax = true;
                                    winOpt.allowAjaxCache = false;
                                    winOpt.WindowURL = url;
                                    winOpt.isSingleWindow = (data.isSingleWindow == true ? true : false);

                                    winOpt.WindowContent = data.maincontent;


                                    Desktop.GenerateNewWindow(winOpt, null, function (obj, objdata, id) {

                                        if (winOpt.SingleWindowID)
                                        {
                                            if (Tools.exists(data, 'pageCurrentTitle') && data.pageCurrentTitle != '') {
                                                Desktop.getActiveWindow().data('WindowManager').setTitle(data.pageCurrentTitle);
                                            }
                                        }
                                    });
                                }


                            });
                        }
                    }
                    else
                    {

                        if (content) {


                            if (!_data.useWindow)
                            {

                                winOpt.onBeforeClose = function (event, _wm, callback)
                                {
                                    Application.runOnBeforeCloseWindow(event, _wm, callback);
                                };
                                winOpt.onAfterOpen = function (e, wm, uiContent)
                                {
                                    $(wm.$el).data('app', controller);
                                    Application.runOnAfterOpenWindow(wm.$el, data);
                                };
                                Tools.createPopup(content, winOpt);
                            }
                            else if (_data.useWindow)
                            {
                                if (Tools.exists(data, 'pageCurrentTitle') && data.pageCurrentTitle != '')
                                {
                                    winOpt.WindowTitle = data.pageCurrentTitle;
                                }

                                if (typeof data.toolbar != 'undefined' || data.toolbar)
                                {
                                    winOpt.WindowToolbar = data.toolbar;
                                }

                                winOpt.loadWithAjax = true;
                                winOpt.allowAjaxCache = false;
                                winOpt.WindowURL = url;
                                winOpt.isSingleWindow = (data.isSingleWindow == true ? true : false);

                                //        Application.cacheCurrentApp(winOpt.Controller, winOpt.Action, data);



                                winOpt.WindowContent = content;


                                Desktop.GenerateNewWindow(winOpt, null, function (obj, objdata, id) {
                                    //  obj.unmask();
                                    //  obj.attr('app', winOpt.Controller);
                                    //  Application.currentWindowID = id;
                                    //  Application.createAppMenu(winOpt.Controller, winOpt.Action);
                                    if (winOpt.SingleWindowID)
                                    {
                                        if (Tools.exists(data, 'pageCurrentTitle') && data.pageCurrentTitle != '') {
                                            Desktop.getActiveWindow().data('WindowManager').setTitle(data.pageCurrentTitle);
                                        }
                                    }

                                    if (typeof _data.onAfterCall === 'function')
                                    {
                                        _data.onAfterCall(data);
                                    }
                                    else if (function_exists(_data.onAfterCall))
                                    {
                                        eval(_data.onAfterCall + '(data);');
                                    }

                                });
                            }
                        }
                    }
                });
            }
            else
            {
                var url = (_data.url ? _data.url : 'admin.php?adm=' + (isAddon ? 'plugin&plugin=' + controller : controller) + '&action=' + action + '&ajax=1');

                this.runAjaxRequest(url, controller, function (data)
                {
                    if (Tools.isObject(data) && Tools.exists(data, 'msg'))
                    {
                        Notifier.display('info', data.msg);
                    }
                    else if (Tools.isObject(data) && Tools.exists(data, 'error'))
                    {
                        Notifier.display('error', data.error);
                    }

                    if (typeof _data.onAfterCall == 'function')
                    {
                        _data.onAfterCall(data);
                    }
                    else if (function_exists(_data.onAfterCall))
                    {
                        eval(_data.onAfterCall + '(data);');
                    }

                });
            }
        }
        else
        {
            this.error('Invalid Ajax action for controller: ' + controller);
        }
    }
    ,
    /**
     * 
     * @param {type} url
     * @param {type} app
     * @param {type} callback
     * @returns {undefined}
     */
    runAjaxRequest: function (url, app, callback)
    {
        document.body.style.cursor = 'progress';
        var self = this;
        Desktop.ajaxData = {};



        $.get(Tools.prepareAjaxUrl(url), {
            ajax: true
        }, function (data) {

            Desktop.ajaxData = data; //$.extend({}, Desktop.ajaxData, data );

            document.body.style.cursor = 'auto';
            if (Tools.responseIsOk(data)) {

                if (typeof callback == 'function')
                {
                    callback(data);
                }
            }
            else
            {
                Desktop.ajaxData.Error = true;
                Desktop.ajaxData.maincontent = data.msg;

                if (typeof callback == 'function')
                {
                    callback(data);
                }
            }

        }, 'json');
    }
    ,
    /**
     * 
     * @param {type} windowObj
     * @param {type} data
     * @returns {undefined}
     */
    runOnAfterOpenWindow: function (windowObj, data)
    {

    }
    ,
    /**
     * 
     * @param {type} windowObj
     * @param {type} data
     * @returns {undefined}
     */
    runOnBeforeOpenWindow: function (windowObj, data)
    {

    }
    ,
    /**
     * 
     * @param {type} windowObj
     * @returns {undefined}
     */
    runOnBeforeCloseWindow: function (windowObj)
    {
        if (windowObj) {
            if (windowObj.data('WindowManager').get('WindowURL') == this.activeUrl)
            {
                this.activeUrl = null;
            }
        }
    }
    ,
    /**
     * 
     * @param {type} event
     * @param {type} controller
     * @param {type} action
     * @param {type} windowObj
     * @returns {Boolean}
     */
    focus: function (event, controller, action, windowObj)
    {
        var self = this, actionRequired = (typeof action == 'string' ? true : false);


        if (typeof windowObj == 'object')
        {

            if (action == 'index') {
                action = false;
                actionRequired = false;
            }
        }
        else
        {

            if ($(event.target).hasClass('popup') || ($(event.target).parents('.isWindowContainer').length && $(event.target).parents('.isWindowContainer').hasClass('popup')))
            {
                if (action == 'index') {
                    action = false;
                    actionRequired = false;
                }
            }
            else
            {
                if (action == 'index') {
                    action = false;
                    actionRequired = false;
                }
            }

        }



        var cache = null;
        for (var i = 0; i < this.Cache.length; i++) {
            if (this.Cache[i][0] == controller && this.Cache[i][1] == 'menu' && actionRequired && this.Cache[i][2] == action) {
//   Dock.runApplication(controller); 
                DesktopMenu.setAppMenu(controller, this.Cache[i][3]);

                this.triggerMenuFocus();

                return;
            }
            else if (this.Cache[i][0] == controller && this.Cache[i][1] == 'menu' && !actionRequired && this.Cache[i][2] == false) {
//  Dock.runApplication(controller); 
                DesktopMenu.setAppMenu(controller, this.Cache[i][3]);

                this.triggerMenuFocus();

                return;
            }
        }

        return false;



        $.post(Tools.prepareAjaxUrl('admin.php'), {
            'ajax': true,
            'action': 'getmenu',
            'adm': controller
        }, function (data) {
            if (Tools.responseIsOk(data))
            {
                self.Cache.push([controller, 'menu', (actionRequired ? action : false), data]);
                // Dock.runApplication(controller);
                DesktopMenu.setAppMenu(controller, self.Cache[self.Cache.length][3]);
            }
            else
            {
                Application.error(data.msg);
                return null;
            }

        }, 'json');
    }
    ,
    /**
     * 
     * @param {type} event
     * @param {type} controller
     * @returns {unresolved}
     */
    aboutApp: function (event, controller, action, _data)
    {
        var self = this, useCache = null;


        if (typeof _data.isAddon == 'undefined' && _data.controller === 'plugin')
        {
            _data.isAddon = true;

            if (_data.WindowURL) {
                controller = _data.WindowURL.replace(/.*plugin=([a-zA-Z0-9_]+)/ig, '$1');
            }
        }


        if ($('.about.' + controller).is(':visible'))
        {
            return;
        }




        for (var i = 0; i < this.Cache.length; i++) {
            if (this.Cache[i][0] === controller && this.Cache[i][1] === 'about') {
                useCache = this.Cache[i][2];
                break;
            }
        }

        var winOpt = {
            title: 'Über...',
            WindowToolbar: false,
            WindowMinimize: false,
            WindowMaximize: false,
            WindowResizable: false,
            minWidth: 310,
            minHeight: 180,
            Width: 310,
            Height: 300,
            Controller: controller,
            app: controller,
            Action: 'index',
            WindowID: controller + '-about-window',
            AddExtraClass: 'no-padding ' + controller,
            enableContentScrollbar: false,
            nopadding: true,
            onAfterClose: function (e, wm, callback)
            {
                var opener = wm.settings.opener;

                if (Tools.isFunction(callback))
                {
                    callback();

                    if (Tools.isString(opener))
                    {
                        setTimeout(function () {
                            $('#' + opener).trigger('click');
                        }, 1);
                    }

                }
            },
            onBeforeShow: function (e, wm, callback)
            {
                // console.log([wm.win]);
                wm.settings.enableContentScrollbar = false;
                wm.settings.nopadding = true;
                $(wm.win).addClass('popup about no-padding ' + controller);

                if (Tools.isFunction(callback))
                {
                    callback();
                }
            }
        };




        var container = $('<div class="about-application">');
        var icon = $('<div class="app-icon">');
        var title = $('<div class="app-title">');
        var version = $('<div class="app-version">');
        var description = $('<div class="app-description">');
        var license = $('<div class="app-license">');
        var copyright = $('<div class="app-copyright">');
        if (useCache == null)
        {

            $.post(Tools.prepareAjaxUrl('admin.php'), {
                'ajax': true,
                'action': 'index',
                'getModulInfo': controller,
                'isAddon': (typeof _data.isAddon != 'undefined' ? (_data.isAddon ? 1 : 0) : 0)
            }, function (data) {

                if (Tools.responseIsOk(data))
                {
                    var tmp = data.info;
                    self.Cache.push([controller, 'about', tmp]);
                    useCache = self.Cache[self.Cache.length];
                    winOpt.title = 'Über ' + tmp.modulelabel + ' ...';
                    winOpt.WindowTitle = winOpt.title;
                    icon.append($('<center>').append($('<img>').attr('src', self.getAppIcon(controller, 48, (typeof _data.isAddon != 'undefined' ? _data.isAddon : false)))));
                    container.append(icon);

                    title.append(tmp.modulelabel);
                    container.append(title);

                    if (typeof tmp.version == 'string' && tmp.version != '')
                    {
                        version.append(tmp.version);
                        container.append(version);
                    }

                    if (typeof tmp.moduledescription == 'string' && tmp.moduledescription != '')
                    {
                        description.append(tmp.moduledescription);
                        container.append(description);
                    }

                    if (typeof tmp.license == 'string' && tmp.license != '')
                    {
                        license.append(tmp.license);
                        container.append(license);
                    }

                    if (typeof tmp.copyright == 'string' && tmp.copyright != '')
                    {
                        copyright.append(tmp.copyright);
                        container.append(copyright);
                    }



                    container.css({
                        display: 'block',
                        width: winOpt.minWidth,
                        opacity: 0,
                        padding: 10,
                        visibility: "hidden",
                        position: "absolute"
                    }).appendTo($('body'));

                    setTimeout(function () {
                        var w = $('body .about-application').height();
                        if (winOpt.Height > w && w > 0)
                        {
                            winOpt.minHeight = w + 40;
                            winOpt.Height = w + 40;
                        }

                        var c = $('body .about-application').clone();
                        $('body .about-application').remove();
                        c.css({
                            padding: 0,
                            opacity: 1
                        });

                        Tools.createPopup(c.html(), winOpt);

                    }, 150);
                }
                else
                {
                    Application.error(data.msg);
                    return null;
                }

            }, 'json');



        }
        else
        {
            var tmp = useCache;
            winOpt.title = 'Über ' + tmp.modulelabel + ' ...';
            winOpt.WindowTitle = winOpt.title;
            icon.append($('<center>').append($('<img>').attr('src', self.getAppIcon(controller, 48, (typeof _data.isAddon != 'undefined' ? _data.isAddon : false)))));
            container.append(icon);
            title.append(tmp.modulelabel);
            container.append(title);
            if (typeof tmp.version == 'string' && tmp.version != '')
            {
                version.append(tmp.version);
                container.append(version);
            }

            if (typeof tmp.moduledescription == 'string' && tmp.moduledescription != '')
            {
                description.append(tmp.moduledescription);
                container.append(description);
            }

            if (typeof tmp.license == 'string' && tmp.license != '')
            {
                license.append(tmp.license);
                container.append(license);
            }

            if (typeof tmp.copyright == 'string' && tmp.copyright != '')
            {
                copyright.append(tmp.copyright);
                container.append(copyright);
            }


            container.css({
                display: 'block',
                width: winOpt.minWidth,
                opacity: 0,
                padding: 10,
                visibility: "hidden",
                position: "absolute"
            }).appendTo($('body'));

            setTimeout(function () {
                var w = $('body .about-application').height();
                if (winOpt.Height > w && w > 0)
                {
                    winOpt.minHeight = w + 40;
                    winOpt.Height = w + 40;
                }



                var c = $('body .about-application');
                $('body .about-application').remove();
                c.css({
                    padding: 0,
                    opacity: 1
                });




                Tools.createPopup(c.html(), winOpt);
            }, 150);
        }
    }
    ,
    /**
     * 
     * @returns {undefined}
     */

    getDesktopRaster: function () {
        var gutter = (Desktop.Icons.iconLabelPos === 'right' ? (Desktop.Icons.iconGutterSize + 30 + Desktop.Icons.iconsize.iconWidth) : Desktop.Icons.iconGutterSize) + 20;
        var maxwidth = $('#DesktopIcons').width(), firstIconHeight = $('#DesktopIcons').find('>.DesktopIconContainer,.DesktopIconContainer-Folder').height();
        return (maxwidth / gutter);
    },
    t: null,
    sendIconUpdate: function (delay) {
        clearTimeout(this.t);

        delay = delay || 500;

        this.t = setTimeout(function () {
            Desktop.Icons.saveDesktopIconsToDatabase();
        }, delay);
    },
    animateBySort: function (elementsSorted, animateSpeed, delay) {

        animateSpeed = animateSpeed || 450;
        delay = delay || (animateSpeed + 100);

        if (delay <= animateSpeed) {
            delay += 50;
        }

        var self = this,
                firstIconHeight = $('#DesktopIcons').find('>.DesktopIconContainer,.DesktopIconContainer-Folder').height(),
                left = 10,
                top = 10,
                h = $('#DesktopIcons').height();


        var el = [];
        elementsSorted.each(function () {
            var id = $(this).attr('id');
            var dataid = $(this).data('id');

            if ($('#DesktopIcons').find('>.DesktopIconContainer[data-id="' + dataid + '"]').length == 1) {
                el.push({element: $('#DesktopIcons').find('>.DesktopIconContainer[data-id="' + dataid + '"]')});
            }
            else if ($('#DesktopIcons').find('.DesktopIconContainer-Folder[data-id="' + dataid + '"]').length == 1) {
                el.push({element: $('#DesktopIcons').find('.DesktopIconContainer-Folder[data-id="' + dataid + '"]')});
            }
        });

        if (!el.length)
        {
            return false;
        }


        if (Desktop.Icons.iconSort == 'none') {
            self.sendIconUpdate(500);
        }
        else {
            for (var i = 0; i < el.length; i++) {
                var element = el[i].element;

                if (top + firstIconHeight >= h) {
                    top = 10;
                    left += (Desktop.Icons.iconLabelPos === 'right' ? (Desktop.Icons.iconGutterSize + 30 + Desktop.Icons.iconsize.iconWidth) : Desktop.Icons.iconGutterSize) + 10;
                }

                $(element).stop(true).animate({
                    left: left,
                    top: top
                }, animateSpeed, function () {
                    self.sendIconUpdate(delay);
                });

                top += firstIconHeight + 25;
            }
        }

    },
    animateByRaster: function (animateSpeed, delay) {


        if (Desktop.Icons.iconSort == 'name') {
            var $filteredData = $('#DesktopIcons').find('.DesktopIconContainer-Folder,>.DesktopIconContainer');
            var $sortedData = $filteredData.clone(true, true).sorted({
                by: function (e) {

                    if ($(e).hasClass('DesktopIconContainer-Folder'))
                    {
                        return $(e).find(".folder-label span").text().toLowerCase();
                    }

                    return $(e).find(".icon-label span,.folder-label span").text().toLowerCase();
                }
            });

            this.animateBySort($sortedData, animateSpeed, delay);
        }

        else if (Desktop.Icons.iconSort == 'size') {
            var $filteredData = $('#DesktopIcons').find('.DesktopIconContainer-Folder,>.DesktopIconContainer');
            var $sortedData = $filteredData.clone(true, true).sorted({
                reversed: true,
                by: function (e) {
                    //console.log($(e).data("size"));
                    return parseInt($(e).data("size"), 10);
                }
            });

            this.animateBySort($sortedData, animateSpeed, delay);
        }

        else if (Desktop.Icons.iconSort == 'none') { // by grid

            var $filteredData = $('#DesktopIcons').find('.DesktopIconContainer-Folder,>.DesktopIconContainer');
            var $sortedData = $filteredData.clone(true, true).sorted({
                by: function (e) {
                    //       console.log($(e).data("id"));
                    return $(e).data("id").toLowerCase();
                }
            });

            this.animateBySort($sortedData, animateSpeed, delay);
        }
        else {
            var $sortedData = $('#DesktopIcons').find('>*:not(.sub-container)');
            this.animateBySort($sortedData, animateSpeed, delay);
        }

        return;



        clearTimeout(this.rt);
        var self = this,
                firstIconHeight = $('#DesktopIcons').find('>.DesktopIconContainer,.DesktopIconContainer-Folder').height(),
                left = 10,
                top = 10,
                h = $('#DesktopIcons').height();

        $('#DesktopIcons').find('.DesktopIconContainer-Folder,>.DesktopIconContainer').each(function () {
            if (top + firstIconHeight >= h) {
                top = 10;
                left += (Desktop.iconLabelPos === 'right' ? (Desktop.iconGutterSize + 10 + Desktop.iconsize.iconWidth) : Desktop.iconGutterSize) + 10;
            }

            $(this).stop().animate({
                left: left,
                top: top
            }, 450, function () {
                self.sendIconUpdate(500);
            });

            top += firstIconHeight + 25;
        });

    },
    bindDesktopOptions: function (winEl) {

        var self = this, symbolsizeSlider = winEl.find('#slide-symbolsize');


        symbolsizeSlider.slider({
            range: "min",
            step: 6,
            min: parseInt(symbolsizeSlider.attr('from'), 10),
            max: parseInt(symbolsizeSlider.attr('to'), 10),
            value: parseInt(Desktop.Icons.iconsize.iconWidth, 10),
            slide: function (event, ui) {


                $('#DesktopIcons').find('>.DesktopIconContainer img').attr('width', ui.value).attr('height', ui.value);
                $('#DesktopIcons').find('.DesktopIconContainer-Folder .folder-wrapper').width(ui.value).height(ui.value);
                $('#DesktopIcons').find('.DesktopIconContainer-Folder .folder-wrapper img').attr('width', (ui.value / 2) - 4).attr('height', (ui.value / 2) - 4);

                Desktop.Icons.iconsize = {
                    iconWidth: ui.value,
                    subIconWidth: (ui.value / 2) - 4
                };

                self.animateByRaster(450, 500);
            }
        });



        var guttersizeSlider = winEl.find('#slide-guttersize');
        guttersizeSlider.slider({
            range: "min",
            step: 10,
            min: parseInt(guttersizeSlider.attr('from'), 10),
            max: parseInt(guttersizeSlider.attr('to'), 10),
            value: parseInt((Desktop.Icons.iconsize.iconWidth + Desktop.Icons.iconGutterSize), 10),
            slide: function (event, ui) {

                Desktop.Icons.iconGutterSize = ui.value;
                $('#DesktopIcons').find('.DesktopIconContainer-Folder >.folder-label,>.DesktopIconContainer .icon-label').width(ui.value);

                self.animateByRaster(450, 500);
            }
        });


        winEl.find(':radio').each(function () {
            if ($(this).attr('name') === 'labelpos') {

                $(this).on('change', function () { 

                    if ($(this).is(':checked')) {
                        Desktop.Icons.iconLabelPos = $(this).val();
                        $('#DesktopIcons').find('.DesktopIconContainer-Folder,>.DesktopIconContainer').removeClass('labelpos-right').removeClass('labelpos-bottom').addClass('labelpos-' + $(this).val());

                        $('#DesktopIcons').find('.DesktopIconContainer-Folder >.folder-label,>.DesktopIconContainer .icon-label').width(Desktop.Icons.iconGutterSize);

                        self.animateByRaster(450, 500);

                    }
                    else {
                        $('#DesktopIcons').find('.DesktopIconContainer-Folder,>.DesktopIconContainer').removeClass('labelpos-' + $(this).val());
                        self.sendIconUpdate(500);
                    }
                });

            }

        });


        winEl.find('#icon-objectinfo').each(function () {
            $(this).on('change', function () {
                if ($(this).is(':checked')) {
                    Desktop.Icons.showObjectInfo = true;
                    self.animateByRaster(450, 500);
                    $('#DesktopIcons').find('.DesktopIconContainer-Folder >.folder-label .object-info,>.DesktopIconContainer .object-info').show();
                }
                else {
                    Desktop.Icons.showObjectInfo = false;
                    $('#DesktopIcons').find('.DesktopIconContainer-Folder >.folder-label .object-info,>.DesktopIconContainer .object-info').hide();
                    self.sendIconUpdate(500);
                }
            });
        });


        winEl.find('select').each(function () {
            if ($(this).attr('name') === 'icon-sorting') {
                $(this).on('change', function () {

                    var selectedVal = $(this).find(':selected').val();

                    if (selectedVal) {
                        Desktop.Icons.iconSort = selectedVal;
                        self.animateByRaster(450, 500);
                    }
                });
            }
        });






    },
    showDesktopOptions: function () {
        var self = this;
        if (!$('.opts-desktop').length) {
            var container = $('<div class="opts-desktop" style="width:200px;height:300px">');

            container.css({
                display: 'inline-block',
                opacity: 0,
                padding: 10,
                visibility: "hidden",
                position: "absolute"
            }).appendTo($('body'));

            var opt = {
                WindowTitle: 'Darstellungsoptionen',
                WindowMinimize: false,
                WindowResizeable: false,
                Controller: 'desktopopts',
                minWidth: 200,
                minHeight: 300,
                Width: 200,
                Height: 300,
                modal: true,
                onAfterOpen: function (o, wm)
                {
                    wm.set('isSingleWindow', false);
                    wm.$el.attr('app', 'desktopopts');
                    self.bindDesktopOptions(wm.$el);
                },
                onAfterClose: function (e, wm, callback)
                {
                    if (Tools.isFunction(callback))
                    {
                        callback();
                        $('.opts-desktop').remove();
                    }
                }
            };

            setTimeout(function () {
                var tpl = Desktop.Templates.DesktopOptions;

                if (Desktop.Icons.iconLabelPos == 'bottom') {
                    tpl = tpl.replace('{checkiconpos-bottom}', ' checked="checked"').replace('{checkiconpos-right}', '');
                }
                else if (Desktop.Icons.iconLabelPos == 'right') {
                    tpl = tpl.replace('{checkiconpos-bottom}', '').replace('{checkiconpos-right}', ' checked="checked"');
                }
                else {
                    tpl = tpl.replace('{checkiconpos-bottom}', '').replace('{checkiconpos-right}', '');
                }

                if (Desktop.Icons.iconSort == 'none') {
                    tpl = tpl.replace('{checksort-none}', ' selected="selected"').replace('{checksort-name}', '').replace('{checksort-size}', '').replace('{checksort-default}', '');
                }
                else if (Desktop.Icons.iconSort == 'name') {
                    tpl = tpl.replace('{checksort-none}', '').replace('{checksort-size}', '').replace('{checksort-name}', ' selected="selected"').replace('{checksort-default}', '');
                }
                else if (Desktop.Icons.iconSort == 'size') {
                    tpl = tpl.replace('{checksort-size}', ' selected="selected"').replace('{checksort-none}', '').replace('{checksort-name}', '').replace('{checksort-default}', '');
                }
                else if (Desktop.Icons.iconSort == 'default') {
                    tpl = tpl.replace('{checksort-default}', ' selected="selected"').replace('{checksort-none}', '').replace('{checksort-name}', '').replace('{checksort-size}', '');
                }

                if (Desktop.Icons.showObjectInfo) {
                    tpl = tpl.replace('{checkicon-objectinfo}', ' checked="checked"');
                }
                else {
                    tpl = tpl.replace('{checkicon-objectinfo}', '');
                }


                container.append(tpl);
                self.bindDesktopOptions(container);

                var w = $('body .opts-desktop').height();
                opt.Width = $('body .opts-desktop').width();
                opt.WindowWidth = opt.Width + 15;

                if (opt.WindowHeight > w && w > 0)
                {
                    opt.Height = w + 10;
                    opt.WindowHeight = w + 10;
                }

                opt.WindowContent = container.html();

                // create new window
                Tools.createPopup(false, opt);
            }, 5);


        }
    },
    /**
     * 
     * @param {type} event
     * @param {type} controller
     * @returns {undefined}
     */
    appSettings: function (event, controller, action, _data)
    {
        var title = $(event).parents('li:first span:first').text();
        event.stopPropagation();
        /*
         openTab({
         url: 'admin.php?adm=settings&action=edit&group=' + controller + '&type=' + (_data.isAddon ? 'plugin' : ''),
         obj: $(event).parents('li:first span:first'),
         label: title,
         onBeforeShow: function (wm)
         {
         wm.set('isSingleWindow', false);
         wm.$el.find('#window-nav').remove();
         }
         });
         
         return;
         
         */

        var container = $('<div class="opts-application">');
        var opt = {
            url: 'admin.php?adm=settings&action=edit&group=' + controller + '&type=' + (_data.isAddon ? 'plugin' : ''),
            WindowUrl: 'admin.php?adm=settings&action=edit&group=' + controller + '&type=' + (_data.isAddon ? 'plugin' : ''),
            WindowTitle: title,
            WindowMinimize: true,
            WindowResizeable: true,
            Controller: controller,
            WindowWidth: 100,
            WindowHeight: 300,
            onAfterOpen: function (o, wm)
            {
                wm.set('isSingleWindow', false);
                wm.$el.attr('app', controller).find('#window-nav').remove();
            }
        };



        //  if (useCache == null)
        //  {
        $.post(Tools.prepareAjaxUrl('admin.php'), {
            'ajax': true,
            'action': 'edit',
            'adm': 'settings',
            group: controller,
            type: (_data.isAddon ? 'plugin' : 'modules')
        }, function (data) {
            if (Tools.responseIsOk(data))
            {

                if (data.maincontent) {

                    container.css({
                        display: 'inline-block',
                        opacity: 0,
                        padding: 10,
                        visibility: "hidden",
                        position: "absolute"
                    }).appendTo($('body'));

                    setTimeout(function () {
                        var w = $('body .opts-application').height();
                        opt.Width = $('body .opts-application').width();
                        opt.WindowWidth = opt.Width + 15;

                        if (opt.WindowHeight > w && w > 0)
                        {
                            opt.WindowHeight = w + 10;
                        }

                        // this.Cache.push([controller, 'settings', data]);
                        opt.WindowToolbar = data.toolbar;
                        opt.WindowTitle = data.pageCurrentTitle;

                        if (data.onAfterOpen)
                        {
                            opt.onAfterOpen = data.onAfterOpen;
                        }

                        // create new window
                        Tools.createPopup(data.maincontent, opt);
                    }, 50);

                }
                else {
                    Notifier.display('warn', 'Settings for this modul/plugin not exists');
                }

            }
            else
            {
                Application.error(data.msg);
                return null;
            }

        }, 'json');
        //   }
        //    else
        //   {
        // create new window
        //      Tools.createPopup(useCache.maincontent, opt);
        //  }
    },
    /**
     * 
     * @param {type} event
     * @param {type} winObj
     * @returns {undefined}
     */
    refreshMenu: function (event, winObj)
    {
        var data = winObj.data('WindowManager');
        // load first the new Menu


        // remove the old Menu
        DesktopMenu.removeAppMenu(event, data.Controller, true);
    },
    /**
     * 
     * @param {type} event
     * @param {type} winObj
     * @returns {Boolean}
     */
    closeApplication: function (event, winObj)
    {
        var self = this, winData = (event != null ? $(event.target).parents('.isWindowContainer') : $(winObj));
        var counted = 0, current = 0, data = winData.data('WindowManager');
        var winID = '';

        if (winData.length == 1 && (typeof data == 'object' && data.get('isRootApplication') != true))
        {

            data.set('isForceClose', true).close();
            var controller = data.get('Controller');
            // es ist nicht das root fenster was geschlossen werden soll
            Dock.closeApplication(controller, data, false, data);
            //data.ResizeWindow('close');
            Dock.updateDatabase(true);

            Win.removeWindowFormUi(winData.id);

        }
        else if (winData.length == 1 && (data != null && typeof data == 'object' && data.get('isRootApplication') == true))
        {
            // der fall dafür falls das root fenster geschlossen wird, aber noch andere fenster offen sind
            var controller = data.get('Controller');
            var Action = data.get('Action');

            winID = winData.attr('id');
            var find = '.isWindowContainer[app="' + controller + '"]';
            /*
             if (winID)
             {
             find = '#' + winID + ',.isWindowContainer[opener="' + winID + '"]';
             }
             */

            // count first all opened widows
            $(find).each(function () {
                if ($(this).attr('app') == controller)
                {
                    counted++;
                }
            });


            $(find).each(function () {

                var id = $(this).attr('id');
                $(this).data('WindowManager').set('isForceClose', true).close();
                //$(this).data('WindowManager').ResizeWindow('close');
                current++;
                if (current >= counted)
                {
                    // remove menu cache and empty the menu container
                    DesktopMenu.removeAppMenu(event, controller, false);
                    // destroy cache
                    /*
                     for (var i = 0; i < self.Cache.length; i++) {
                     if (self.Cache[i][0] == controller) {
                     //      delete(self.Cache[i]);
                     }
                     }
                     */
                    Dock.closeApplication(controller, data, true);
                    Dock.updateDatabase(true);

                    Win.removeWindowFormUi(id);
                }
            });
        }
        else if (typeof winObj == 'string' && winData.length == 0 && $(event.target).parents('#App-Menu').length == 1)
        {
            // click wurde vom Menü abgesetzt
            // Clicked on the menu


            // count first all opened widows
            $('.isWindowContainer[app="' + winObj + '"]').each(function () {
                if ($(this).attr('app') == winObj)
                {
                    counted++;
                }
            });


            $('.isWindowContainer[app="' + winObj + '"]').each(function () {

                var dat = $(this).data('WindowManager');

                if (dat) {

                    dat.set('isForceClose', true).close();
                    //$(this).data('WindowManager').ResizeWindow('close');
                    current++;
                    if (current >= counted)
                    {
                        // remove menu cache and empty the menu container
                        DesktopMenu.removeAppMenu(event, winObj, false);
                        // destroy cache
                        /*
                         for (var i = 0; i < self.Cache.length; i++) {
                         if (self.Cache[i][0] == winObj) {
                         //      delete(self.Cache[i]);
                         }
                         }
                         */
                        Dock.closeApplication(winObj, dat, true);
                        Dock.updateDatabase(true);

                        Win.removeWindowFormUi(dat.id);
                    }
                }
                else {
                    Dock.closeApplication(winObj, dat, true);
                    Dock.updateDatabase(true);
                }
            });
        }
        else
        {
            // falls alles nicht hilft dann schließe alles
            /*
             if (typeof data == 'undefined' || !data.Controller)
             {
             if (winObj.length)
             {
             data = {
             Controller: $(winObj).attr('app'),
             // dummy functions
             set: function() {
             return this;
             },
             close: function() {
             
             }};
             
             if (!data.Controller)
             {
             Debug.error('Controller not set!');
             }
             }
             }
             */
            // If used new Menu data then run Application.refreshMenu()
            DesktopMenu.removeAppMenu(event, data.Controller, false);
            // here remove the menu
            // User has click on the menu "Close the Application"
            Dock.closeApplication(data.Controller, data, true);

            //data.set('isForceClose', true).close();

            //data.ResizeWindow('close');
            Dock.updateDatabase(true);

            Win.removeWindowFormUi(data.id);
        }

        return true;
    },
    /**
     * Alias function for the Application menu
     * @param {type} event
     * @param {type} controller
     * @returns {unresolved}
     */
    closeApp: function (event, controller)
    {
        return this.closeApplication(event, controller);
    },
    /**
     * is the window object a root application
     * returns null if not a window object
     * @param {type} windowObject
     * @returns {@exp;windowObject@pro;data@call;@call;get|@exp;@exp;windowObject@pro;data@call;@call;get}
     */
    isRootApplication: function (windowObject)
    {
        if (typeof windowObject == 'object')
        {
            return windowObject.data('WindowManager').get('isRootApplication');
        }
        else
        {
            this.error('windowObject is not an windows');
            return null;
        }
    },
    /**
     * 
     * @param {type} event
     * @param {type} controller
     * @param {type} action
     * @returns {unresolved}
     */
    aliasBuilder: function (event, controller, action)
    {

        if ($('.aliasbuilder.' + controller).is(':visible'))
        {
            return;
        }

        var container = $('<div/>').addClass('alias-builder'), winOpt = {
            title: 'Alias-Builder...',
            Resizable: false,
            WindowToolbar: false,
            Minimize: false,
            minWidth: 510,
            minHeight: 400,
            Width: 510,
            Height: 400,
            Controller: controller,
            app: controller,
            Action: 'index',
            AddExtraClass: 'no-padding ' + controller,
            onBeforeClose: function (e, wm, callback)
            {
                //winObj.data('WindowManager').set('isForceClose', false);
                if (Tools.isFunction(callback))
                {
                    callback();
                }
            },
            onBeforeShow: function (e, wm, callback)
            {
                wm.settings.enableContentScrollbar = false;
                wm.settings.nopadding = true;
                wm.win.addClass('popup aliasbuilder no-padding ' + controller).removeClass('alias-builder');

                if (Tools.isFunction(callback))
                {
                    callback();
                }
            }
        };


        $.ajax({
            url: Tools.prepareAjaxUrl('admin.php'),
            type: 'POST',
            dataType: 'json',
            data: {
                'ajax': true,
                'action': 'index',
                'modulaction': action,
                'modul': controller,
                'getAliasBuilder': true
            },
            global: false,
            success: function (data)
            {
                if (Tools.responseIsOk(data))
                {
                    container.html(data.html);
                    container.css({
                        display: 'block',
                        width: winOpt.WindowMinWidth,
                        opacity: 0,
                        padding: 10,
                        visibility: "hidden",
                        position: "absolute"
                    }).appendTo($('body'));

                    setTimeout(function () {
                        var w = $('body .alias-builder').outerHeight(true);

                        if (winOpt.Height > w && w > 0)
                        {
                            winOpt.minHeight = w + 33;
                            winOpt.Height = w + 33;
                        }
                        else if (w > 0)
                        {
                            winOpt.minHeight = w + 33;
                            winOpt.Height = w + 33;
                        }

                        var c = $('body .alias-builder');
                        $('body .alias-builder').remove();
                        c.css({
                            padding: 0,
                            opacity: 1
                        });

                        Tools.createPopup(c.html(), winOpt);
                        setTimeout(function () {
                            $(data.html).filter('script').each(function () {
                                $.globalEval(this.text || this.textContent || this.innerHTML || '');
                            });
                        }, 200);
                    }, 150);
                }
                else
                {

                }
            }

        });
    },
    /**
     * 
     * @param {type} event
     * @param {type} controller
     * @returns {undefined}
     */
    updateApp: function (event, controller)
    {
        $.post(Tools.prepareAjaxUrl('admin.php'), {
            'ajax': true,
            'action': 'checkupdate',
            'modul': controller,
            'adm': 'modules'
        }, function (data) {
            if (Tools.responseIsOk(data))
            {

                if (data.canupdate == false)
                {
                    Notifier.display('info', data.msg);
                }



                return data;
            }
            else
            {
                Application.error(data.msg);
                return null;
            }
        }, 'json');
    },
    /**
     * 
     * @param {type} event
     * @param {type} controller
     * @returns {undefined}
     */
    help: function (event, controller)
    {


        var container = $('<div/>').addClass('help-content'), winOpt = {
            title: 'Help...',
            Resizable: true,
            WindowToolbar: true,
            Minimize: true,
            Maximize: true,
            minWidth: 510,
            minHeight: 400,
            Width: 510,
            Height: 400,
            Controller: controller,
            app: controller,
            Action: 'index',
            AddExtraClass: 'no-padding ' + controller,
            onBeforeClose: function (e, wm, callback)
            {
                if (Tools.isFunction(callback))
                {
                    callback();
                }
            },
            onBeforeShow: function (e, wm, callback)
            {
                wm.settings.enableContentScrollbar = false;
                wm.settings.nopadding = true;
                wm.win.addClass('popup help no-padding ' + controller).removeClass('help-content');

                if (Tools.isFunction(callback))
                {
                    callback();
                }
            }
        };



        $.post(Tools.prepareAjaxUrl('admin.php'), {
            'ajax': true,
            'action': 'gethelp',
            'modul': controller,
            'adm': 'modules'
        }, function (data) {
            if (Tools.responseIsOk(data))
            {
                var html = '<div>' + data.htmlCode + '</div>';




                container.html(html);
                container.css({
                    display: 'block',
                    width: winOpt.WindowMinWidth,
                    opacity: 0,
                    padding: 10,
                    visibility: "hidden",
                    position: "absolute"
                }).appendTo($('body'));

                setTimeout(function () {
                    var w = $('body .help-content').outerHeight(true);

                    if (winOpt.Height > w && w > 0)
                    {
                        winOpt.minHeight = w + 33;
                        winOpt.Height = w + 33;
                    }
                    else if (w > 0)
                    {
                        winOpt.minHeight = w + 33;
                        winOpt.Height = w + 33;
                    }

                    var c = $('body .help-content').clone();
                    $('body .help-content').remove();
                    c.css({
                        padding: 0,
                        opacity: 1
                    });

                    Tools.createPopup(c.html(), winOpt);

                    setTimeout(function () {

                        $(html).filter('script').each(function () {
                            $.globalEval(this.text || this.textContent || this.innerHTML || '');
                        });

                    }, 200);
                }, 150);


            }
            else
            {
                Application.error(data.msg);
                return null;
            }
        }, 'json');
    },
    /**
     * 
     * @param {type} event
     * @param {type} controller
     * @param {type} dataFromMenu
     * @returns {unresolved}
     */
    gridViewMode: function (event, controller, dataFromMenu)
    {
        var gridCount = 0, dataToUse = null, windows = $('.isWindowContainer[app=' + dataFromMenu.controller + ']');
        if (windows.length == 0)
        {
            return;
        }

        var element = $(event.target);
        if ($(event.target).get(0).tagName != 'li')
        {
            element = $(event.target).parents('li:first');
        }

        if (element.find('.getViewOptions').length == 1)
        {
            return;
        }

        windows.each(function () {
            if (typeof $(this).data('windowGrid') == 'object')
            {
                dataToUse = $(this).data('windowGrid');
                gridCount++;
            }
        });

        if (gridCount > 1)
        {
            dataToUse = null;
            windows.each(function () {
                if (typeof $(this).data('windowGrid') == 'object' && $(this).attr('id') == Win.windowID)
                {
                    dataToUse = $(this).data('windowGrid');
                    gridCount++;
                }
            });
        }

        if (dataToUse == null)
        {
            return;
        }

        var grid = dataToUse;
        if (element.find('.getViewOptions').length == 0)
        {
            var divContainer = $('<div>').addClass('submenu getViewOptions active').show();
            grid.getViewOptions(divContainer);
            element.append(divContainer);
        }
        else
        {
            var divContainer = $('<div>').addClass('submenu getViewOptions active').show();
            grid.getViewOptions(divContainer);
            element.find('.getViewOptions').remove().append(divContainer);
        }

        element.addClass('fold');
        $('.getViewOptions:first input', element).each(function () {
            if ($(this).is(':checkbox'))
            {
                $.Zebra_TransForm($(this));
                $(this).addClass('inputC');
            }
            else if ($(this).is(':checkbox'))
            {
                $.Zebra_TransForm($(this));
                $(this).addClass('inputC');
            }


        });

        Win.prepareWindowFormUi(wm.id);

        //DesktopMenu.bindAppMenuEvents(controller);


        $('.submenu:first', element).css({
            left: $(element).position().left,
            position: 'absolute',
            top: ($('#Taskbar').offset().top + $('#Taskbar').outerHeight(true)),
            zIndex: 99999
        }).show();
    }


};










