/**
 * Created by marcel on 12.06.14.
 */


function openTab(opt) {
    var activeTab = Core.Tabs.getActiveTab();
    var hash = opt.url ? md5(opt.url) : false;
    opt.forceNewTab = true;

    if (activeTab.length && activeTab.attr('single')) {
        /*
         if ($('#content-' + activeTab.attr('id').replace('tab-', '')).find('.subwindow:visible').length == 1 && !opt.isSingleWindow) {
         opt.forceNewTab = true;
         }
         */
        if ($('#content-' + activeTab.attr('id').replace('tab-', '')).find('#sub-window-' + hash).length || opt.isSingleWindow === true) {
            opt.forceNewTab = false;
        }

        if (opt.forceNewTab && opt.isSingleWindow !== true) {
            opt.forceNewTab = true;
        }
    }

    Core.Tabs.load(opt);

    if (opt.ajax && opt.ajax != 0) {
        $.get(opt.url, function (data) {
            if (opt.obj) {
                $(opt.obj).find('.fa').removeClass('fa-spin');
            }

            if (Tools.responseIsOk(data)) {
                if (data.msg && data.msg != '') {
                    Notifier.info(data.msg);
                }
            }
            else {
                if (typeof data.msg != 'undefined') {
                    Notifier.warn(data.msg);
                }
                console.log(data);
            }
        });

        return false;
    }
    else if (opt.popup && opt.popup != 0) {
        $.ajax({
            type: 'GET',
            url: opt.url + '&ajax=true',
            cache: false,
            async: false,
            global: false,
            beforeSend: function () {
                document.body.style.cursor = 'progress';
            },
            success: function (data) {
                if (opt.obj) {
                    $(opt.obj).find('.fa').removeClass('fa-spin');
                }

                if (Tools.responseIsOk(data)) {

                    Tools.createPopup(
                        data.maincontent, {
                            resizeable: (data.resizeable ? data.resizeable : null),
                            title: (data.applicationTitle ? data.applicationTitle : data.pageTitle)
                        }
                    );

                    if ($(data.maincontent).filter('script').length) {
                        //   console.log('Eval Scripts after window Created');
                        Tools.eval($(data.maincontent));
                    }
                }
                else {
                    document.body.style.cursor = '';
                    if (typeof data.msg != 'undefined') {
                        Notifier.warn(data.msg);
                    }
                    console.log(data);
                }
            }
        });

        return false;
    }
    else if (opt.url && opt.url !== '' && opt.url !== '#' && opt.url !== 'void()') {

        var tabWin = new Tab( Core.dashboard );
        var loaded = tabWin.load(opt, function() {
            "use strict";

        });

        if (!loaded) {

        }

    }
}





var Tab = function (dashboard) {
    "use strict";

    this.tabContainer = $('#main-tabs ul');
    this.contentContainer = $('#content-container-inner');
    this.statusbarContainer = $('#main-content-statusbar');
    this.contentTabsContainer = $('#main-content-tabs');
    this.toolbarContainer = $('#main-content-buttons');


    /**

     <div id="tab-template" style="display: none">
         <li>
             <span class="first"></span>
             <span class="label"><i></i><span>laden...</span></span>
             <span class="remove-tab"><i></i></span>
             <span class="last"></span>
         </li>
     </div>

     */
    this.TabTemplate = $('#tab-template').html();

    this.dashboard = dashboard;

};

Tab.prototype = {

    events: {}, // onBeforeShow, onBeforeClose, onAfterShow ....
    shortcuts: [],
    hash: null,
    app: null,
    openerHash: null,
    isRootTab: false,
    tab_content_template: '<div id="content-{hash}" class="core-tab-content" style="display:none"></div>',
    tabConfig: {},
    /**
     *
     * @param itemData
     * @param callback
     * @returns {*}
     */
    load: function (itemData, callback) {
        "use strict";
        var self = this, valid = false;

        var hash = md5(itemData.url);
        var tab, openerHash, subCall = false, isSingle = false, current_active = this.getActiveTab();

        if (current_active && itemData.forceNewTab == false) {
            if (current_active.$tab.attr('single')) {
                isSingle = current_active.$tab.attr('id');
            }
        }

        if (typeof itemData.forceNewTab != 'undefined' && itemData.forceNewTab == true) {
            isSingle = false;
            openerHash = false;
            current_active = null;
        }


        if (isSingle) {
            openerHash = isSingle.replace('tab-', '');
            current_active.$tab.removeClass('loaded');
            current_active.$tab.find('.label span').text('laden...');
            tab = current_active.$tab;
        }
        else {
            openerHash = false;
        }

        if ( this.hash && $('#root-window-'+ this.hash).length && (isSingle || itemData.singleWindow) ) {
            subCall = true;
        }

        if (!this.tabExists(hash)) {



            if (!openerHash) {
                this.hash = hash;
                var tpl = this.tab_content_template.replace('{hash}', this.hash);
                $(tpl).appendTo($('#content-container-inner'));

                this.createTab(itemData, hash);
                this.tabContainer.append(this.$tab);
                this.setActiveHash = this.hash;

                $('#main-tabs ul').trigger('change');
            }

            this.bindTabEvents();

            if (!openerHash) {
                this.onBeforeLoad();
            }
            else {
                $('#root-buttons-' + openerHash + ',#root-window-' + openerHash + ',#root-status-' + openerHash).fadeOut(300);
                this.setActiveHash = openerHash;
            }

            if (this.dashboard) {
                this.dashboard.hide();
            }


            $.ajax({
                type: 'GET',
                url: itemData.url + '&ajax=true',
                cache: false,
                async: false,
                global: false,
                beforeSend: function () {
                    document.body.style.cursor = 'progress';
                },
                success: function (data) {

                    if (Tools.responseIsOk(data))
                    {
                        self.tabConfig = itemData;

                        if (itemData.obj) {
                            $(itemData.obj).find('.fa').removeClass('fa-spin');
                        }

                        if (self.singleWindow || isSubwindowCall || data.isSingleWindow)
                        {
                            self.prepareSingleWindowTab(data, itemData, callback, subCall);
                        }
                        else {
                            self.prepareWindowTab(data, itemData, callback);
                        }

                        if (callback) {
                            return callback(true, data);
                        }
                        else {
                            return true;
                        }
                    }
                    else {
                        if (!openerHash) {
                            self.$tab.remove();
                        }

                        if (callback) {
                            return callback(false, data);
                        }
                        else {
                            return false;
                        }
                    }
                }
            });

        }
        else {
            if (current_active) {
                valid = true;
                current_active.$tab.trigger('click');
                return null;
            }

            return false;
        }
    },

    /**
     *
     * @param itemData
     * @param hash
     */
    createTab: function (itemData, hash) {
        "use strict";
        this.$tab = $(this.TabTemplate); //$('<li id="tab-' + hash + '"></li>');
        this.$tab.attr('id', 'tab-' + hash);

        var app = Core.getAppKey(itemData.url);
        var currentActiveTab = this.tabContainer.find('li.active');

        if (currentActiveTab.length) {
            this.$tab.attr('opener', currentActiveTab.attr('id'));
        }

        if (!$('#main-tabs ul').find('li[app=' + app + ']').length) {
            this.$tab.attr('isroot', '1');
            this.isRootTab = true;
        }

        this.app = app;
        this.$tab.data('itemData', itemData).attr('app', app);

        /*
        this.$tab.append($('<span class="first"></span>'));
        this.$tab.append($('<span class="label"><i></i><span>laden...</span></span>'));
        this.$tab.append($('<span class="remove-tab"><i></i></span>'));
        this.$tab.append($('<span class="last"></span>'));
        */
    },

    /**
     *
     * @param data
     * @param itemData
     * @param callback
     * @param isSubCall
     */
    prepareSingleWindowTab: function (data, itemData, callback, isSubCall) {
        "use strict";



        if (!isSubCall)
        {
            // create new single window
            this.prepareWindowTab(data, itemData);
        }
        else {
            // add new sub window
            this.$statusbarWrapper.append('<div class="sub-status" id="sub-status-' + this.hash + '"></div>');

            // toolbar
            var toolbar = $('<div class="sub-buttons" id="sub-buttons-'+ this.hash +'"></div>');
            if (data.toolbar) {
                toolbar.append( data.toolbar );
            }

            // content
            var content = $('<div class="sub-content" id="sub-window-'+ this.hash +'"></div>');
            if (data.maincontent) {
                content.append( data.maincontent );
            }

            // versioning
            var useVersioning = false;
            if (typeof data.versioning != 'undefined' && data.versioning != '') {
                toolbar.append($('<div>').addClass('content-versions').append(data.versioning));
                useVersioning = true;
            }

            // content Tabs
            if (content.find('.tabcontainer').length) {

                if (!$('#sub-tabs-' + hash).length) {
                    var contentTabContainer = $('<div class="sub-content-tabs" style="display:block"></div>').attr('id', 'sub-tabs-' + this.hash );
                    contentTabContainer.appendTo(this.contentTabsContainer);
                    content.find('.tabcontainer').appendTo($('#content-tabs-' + this.hash ));
                    $('#main-content-tabs').show();
                }
                else {
                    this.$content.find('.tabcontainer').remove();
                    $('#main-content-tabs').hide();
                }

                this.bindContentTabEvents($('#sub-tabs-' + this.hash ), this.hash );
            }

            if (data.nopadding) {
                content.data('nopadding', true);
                this.$contentWrapper.addClass('no-padding');
            }


            // insert
            this.$contentWrapper.append(content);
            this.$toolbarWrapper.append(toolbar);

            var tmpActive = Win.windowID;
            Win.setActive( this.hash );

            if (this.$content.filter('script').length) {
                console.log('Eval Scripts after window Created');
                Tools.eval(this.$content);
            }

            this.postInit(data);

        }
    },

    /**
     *
     * @param data
     * @param itemData
     * @param callback
     */
    prepareWindowTab: function (data, itemData, callback) {
        "use strict";

        // statusbar
        this.$statusbarWrapper = $('<div class="status" id="statusbar-' + this.hash + '">');
        this.$statusbar = $('<div class="root-status" id="root-status-' + this.hash + '">');
        this.$statusbarWrapper.append(this.$statusbar);


        // toolbar
        this.$toolbarWrapper = $('<div id="buttons-'+ this.hash +'"></div>');
        this.$toolbar = $('<div id="root-buttons-'+ this.hash +'"></div>');
        this.$toolbarWrapper.append(this.$toolbar);
        if (data.toolbar) {
            this.$toolbar.append( data.toolbar );
        }

        // content
        this.$contentWrapper = $('<div class="scroll-content"></div>');
        this.$content = $('<div class="root-content" id="root-window-'+ this.hash +'"></div>');
        this.$contentWrapper.append(this.$content);
        if (data.maincontent) {
            this.$content.append( data.maincontent );
        }

        // versioning
        var useVersioning = false;
        if (typeof data.versioning != 'undefined' && data.versioning != '') {
            this.$versioning = $('<div>').addClass('content-versions root-versions');
            this.$versioning.append(data.versioning);
            this.$toolbar.append(versioning);
            useVersioning = true;
        }


        // content Tabs
        if (this.$content.find('.tabcontainer').length) {

            if (!$('#content-tabs-' + hash).length) {
                this.$contentTabContainer = $('<div class="root-content-tabs" style="display:block"></div>').attr('id', 'root-content-tabs-' + this.hash );
                this.$contentTabContainer.appendTo(this.contentTabsContainer);
                this.$content.find('.tabcontainer').appendTo($('#root-content-tabs-' + this.hash ));
                $('#main-content-tabs').show();

                this.bindContentTabEvents($('#root-content-tabs-' + this.hash ), this.hash );
            }
            else {
                this.$content.find('.tabcontainer').remove();
                $('#main-content-tabs').hide();
            }
        }

        if (data.nopadding) {
            this.$content.data('nopadding', true);
            this.$contentWrapper.addClass('no-padding');
        }

        // insert
        this.contentContainer.append(this.$contentWrapper);
        this.statusbarContainer.append(this.$statusbarWrapper);
        this.toolbarContainer.append(this.$toolbarWrapper);

        var tmpActive = Win.windowID;
        Win.setActive( this.hash );

        if (this.$content.filter('script').length) {
            //console.log('Eval Scripts after window Created');
            Tools.eval(this.$content);
        }

        this.postInit(data);
    },

    /**
     *
     * @param data
     */
    postInit: function(data) {
        "use strict";

        var self = this, hasMeta = false;
        var formID = this.$content.data('formID');

        if (!this.$content.data('windowGrid'))
        {
            Doc.loadTinyMce(this.$content, false, function () {
                if (self.$content.find('textarea.sourceEdit').length) {

                    if (data.nopadding) {
                        self.$content.data('nopadding', true);
                        self.$contentWrapper.addClass('no-padding');
                    }


                    createTemplateEditor(self.$content.attr('id'), function () {
                        Core.updateViewPort();

                        if (formID) {
                            Form.setContentLockAction(data.contentlockaction, formID, self.hash);
                            Form.registerAutosave(formID, self.hash);
                            Form.makeReset(formID, self.hash);
                        }

                        resizeAce(self.$content, false);

                        if (self.events && typeof self.events.onAfterShow != 'undefined' && self.events.onAfterShow.length > 0) {
                            for (var i = 0; i < self.events.onAfterShow.length; ++i) {
                                if (typeof self.events.onAfterShow[i] == 'function') {
                                    self.events.onAfterShow[i](self.$content, self);
                                }
                            }
                        }

                        self.$tab.addClass('loaded');
                        document.body.style.cursor = '';
                    });
                }
                else {
                    if (data.nopadding) {
                        self.$content.data('nopadding', true);
                        self.$contentWrapper.addClass('no-padding');
                    }

                    self.bindBasicEvents(self.hash);
                    Win.prepareWindowFormUi();


                    if (formID) {
                        Form.setContentLockAction(data.contentlockaction, formID, self.hash);
                        Form.registerAutosave(formID, self.hash);
                        Form.makeReset(formID, self.hash);
                    }

                    if (self.events && typeof self.events.onAfterShow != 'undefined' && self.events.onAfterShow.length > 0) {
                        for (var i = 0; i < self.events.onAfterShow.length; ++i) {
                            if (typeof self.events.onAfterShow[i] == 'function') {
                                self.events.onAfterShow[i](self.$content, self);
                            }
                        }
                    }

                    Core.updateViewPort();
                    self.$tab.addClass('loaded');
                    document.body.style.cursor = '';
                }
            });
        }
        else
        {
            // execute grid
            this.$content.data('nopadding', true);
            this.$contentWrapper.addClass('no-scoll');
            var g = this.$content.data('windowGrid');

            g.createGrid(g, function () {

                Win.prepareWindowFormUi();


                setTimeout(function () {
                    self.$contentWrapper.addClass('no-padding');
                    Core.updateViewPort();

                    g.updateDataTableSize(self.$content );

                    if (self.events && typeof self.events.onAfterShow != 'undefined' && self.events.onAfterShow.length > 0) {
                        for (var i = 0; i < self.events.onAfterShow.length; ++i) {
                            if (typeof self.events.onAfterShow[i] == 'function') {
                                self.events.onAfterShow[i](self.$content, self);
                            }
                        }
                    }

                    document.body.style.cursor = '';
                    $('#main-content-mid').unmask();
                    self.$tab.addClass('loaded');
                }, 1);
            });
        }



    },



    /**
     * Tab Events
     */

    bindTabEvents: function () {
        "use strict";

        var self = this;

        this.tabContainer.find('li').unbind('click.tabremove').bind('click.tabremove', function (e) {
            e.preventDefault();
            var error = false, li = $(this);
            var id = li.attr('id').replace('tab-', '');
            var app = li.attr('app');
            var isroot = li.attr('isroot');
            var opener = li.attr('opener');
            var meta = li.hasClass('meta');
            var nodedata = li.data();
            var allAppTabs = self.tabContainer.find('li[app=' + app + ']');

            if (isroot && allAppTabs.length > 1) {
                for (var t in allAppTabs) {
                    var atHash = $(allAppTabs[t]).attr('id').replace('tab-', '');

                    if (atHash && id != atHash) {
                        var selectedTab = self.getTabInstanceByHash(atHash);

                        if (selectedTab) {
                            if (!selectedTab.close(atHash)) {
                                error = true;
                                break;
                            }
                        }
                        else {
                            error = true;
                            break;
                        }
                    }
                }
            }

            if (error) {
                return;
            }


            // find a other tab to activate this
            var doActivate;
            if ($(this).next().is('li') && !$(this).prev().is('li')) {
                doActivate = $(this).next();
            }
            else if ($(this).prev().is('li') && !$(this).next().is('li')) {
                doActivate = $(this).prev();
            }

            // now close the tab
            if (self.close(id, true)) {

                $(this).remove();

                // if other tab exists then activate this
                if (doActivate) {
                    doActivate.trigger('click');
                }
                else {
                    // no other tab found the activate the dashboard
                    Win.setActive(null);

                    if (self.dashboard) {
                        self.dashboard.show();
                    }

                    if (meta) {
                        $('#panel-buttons').find('li[rel]:first').trigger('click');
                    }
                }
            }
        });

        this.tabContainer.find('li').unbind('click.tab').bind('click.tab', function (e) {
            var id = $(this).attr('id').replace('tab-', '');
            var selectedTab = self.getTabInstanceByHash(id);




            if ( selectedTab ) {
                selectedTab.show();
                Win.setActive(id);
            }
        });
    },

    /**
     *
     */
    show: function() {
        "use strict";

        if (this.isActive) {
            return;
        }

        var current = this.tabContainer.find('li.active');
        if ( current.length ) {
            var selectedTab = self.getTabInstanceByHash( current.attr('id').replace('tab-', '') );
            if ( selectedTab ) {
                // hide current activated tab content
                selectedTab.hide();
                selectedTab.isActive = false;
            }

            current.removeClass('active');
        }

        // hide the dashboard
        if (this.dashboard) {
            this.dashboard.hide();
        }

        // hide the tab filemanager
        $('#fm-slider', $('#main-content-mid')).hide();

        this.isActive = true;
        this.$tab.addClass('active');   // add activetion css class

        // show the tab content
        $('#content-' + this.hash + ',#buttons-' + this.hash + ',#content-tabs-' + this.hash + ',#meta-' + this.hash + ',#statusbar-' + this.hash).show();

        // show tab popups
        $('div.popup[opener=' + this.hash + ']').each(function () {
            if ($(this).attr('reopen')) {
                $(this).removeAttr('reopen').show();
            }
        });

        // now execute events
        if (this.events && typeof this.events.onShowTabContent != 'undefined' && this.events.onShowTabContent.length > 0) {
            for (var i = 0; i < this.events.onShowTabContent.length; ++i) {
                if (typeof this.events.onShowTabContent[i] == 'function') {
                    this.events.onShowTabContent[i](this.$content, this.hash);
                }
            }
        }
    },

    /**
     *
     */
    hide: function() {
        "use strict";

        if (!this.isActive) {
            return;
        }

        if (this.isActive) {
            if (this.events && typeof this.events.onBeforeHideTabContent != 'undefined' && this.events.onBeforeHideTabContent.length > 0) {
                for (var i = 0; i < this.events.onBeforeHideTabContent.length; ++i) {
                    if (typeof this.events.onBeforeHideTabContent[i] == 'function') {
                        this.events.onBeforeHideTabContent[i](this.$content, this.hash);
                    }
                }
            }
        }

        $('#content-' + this.hash + ',#buttons-' + this.hash + ',#content-tabs-' + this.hash + ',#meta-' + this.hash + ',#statusbar-' + this.hash).hide();
    },


    close: function (hash, isLastTab) {
        "use strict";
        var self = this, i, tabs = Core.WindowTabs;
        var ret = false, rollbacks;

        for (var x in tabs)
        {
            if (tabs[x].hash == hash)
            {
                var tabInstance = tabs[x];
                var isRootTab = tabInstance.isRootTab;
                var subid = tabInstance.hash;



                ret = tabInstance.onBeforeClose(hash, function (valid) {
                    if (valid === true) {

                        var selfRollback = tabInstance.$tab.attr('rollback');
                        var selfopener = tabInstance.$tab.attr('opener');

                        Doc.unload(subid, $('#content-' + subid));

                        $('#content-' + subid + ',#buttons-' + subid + ',#content-tabs-' + subid + ',#meta-' + subid + ',#statusbar-' + subid).fadeOut(150);

                        tabInstance.$tab.fadeOut(150, function () {

                            Win.removeWindowFormUi(subid);
                            var subdata = tabInstance.$tab.data();


                            if (selfopener) {
                                $(this).remove();
                                $('#content-' + subid + ',#buttons-' + subid + ',#content-tabs-' + subid + ',#meta-' + subid + ',#statusbar-' + subid).remove();
                                $('#' + selfopener).trigger('click'); // focus opener tab

                                if (selfRollback) {
                                    Core.WindowRollbacks.push([selfRollback, false, subdata]);
                                    /*
                                    setTimeout(function () {
                                        self.sendRollback(selfRollback, false, subdata);
                                    }, 500);
                                    */
                                }
                            }
                            else {
                                $(this).remove();
                                $('#content-' + subid + ',#buttons-' + subid + ',#content-tabs-' + subid + ',#meta-' + subid + ',#statusbar-' + subid).remove();

                                if (selfRollback) {
                                    Core.WindowRollbacks.push([selfRollback, false, subdata]);
                                    /*
                                    setTimeout(function () {
                                        self.sendRollback(selfRollback, false, subdata);
                                    }, 500);
                                    */
                                }
                            }

                        });

                        delete tabs[x];
                        return true;
                    }
                    else {
                        return false;
                    }
                });

                if (ret && Core.WindowRollbacks.length ) {
                    for (var i = 0; i<Core.WindowRollbacks.length; ++i) {
                        var call = Core.WindowRollbacks[i];
                        self.sendRollback(call[0], call[1], call[2]);
                    }

                    Core.WindowRollbacks = []; // reset
                }

                break;
            }
        }

        return ret;
    },

    /**
     *
     * @param hash
     * @param callback
     */
    onBeforeClose: function (hash, callback) {
        "use strict";
        var self = this, useContent, rollback;
        var opener = self.$tab.attr('opener');
        useContent = self.$content;




        if (this.$tab.hasClass('dirty')) {
            jConfirm(cmslang.form_dirty, cmslang.alert, function (ok) {
                if (ok) {
                    var events = useContent ? useContent.data('events') : false;
                    if (events && typeof events.onBeforeClose != 'undefined' && events.onBeforeClose.length > 0) {
                        for (var i = 0; i < events.onBeforeClose.length; ++i) {
                            if (typeof events.onBeforeClose[i] == 'function') {
                                events.onBeforeClose[i](useContent, opener, self.hash);
                            }
                        }
                    }

                    Doc.unload(self.hash, useContent, self.isRootTab /*$tab.attr('isroot')*/);
                    callback(true);
                }
            });
        }
        else {
            if (useContent) {
                var events = useContent.data('events');
                if (events && typeof events.onBeforeClose != 'undefined' && events.onBeforeClose.length > 0) {
                    for (var i = 0; i < events.onBeforeClose.length; ++i) {
                        if (typeof events.onBeforeClose[i] == 'function') {
                            events.onBeforeClose[i](useContent, opener, self.hash);
                        }
                    }
                }

                Doc.unload(self.hash, useContent, this.isRootTab /* self.$tab.attr('isroot')*/);
            }
            callback(true);
        }
    },


    /**
     * Helper Functions
     */



    /**
     *
     * @param key
     * @param description
     */
    addShortcut: function (key, description) {
        "use strict";
        this.shortcuts[key] = description;
    },

    /**
     *
     * @param key
     */
    removeShortcut: function (key) {
        "use strict";
        for (var i = 0; i < this.shortcuts.length; i++) {
            var d = this.shortcuts[i];
            if (d[0] == key) {
                delete this.shortcuts[i];
                Tools.reindexArray(this.shortcuts);
                break;
            }
        }
    },

    /**
     *
     * @param name
     * @param call
     */
    addEvent: function (name, call) {
        "use strict";

        if (typeof this.events[name] === 'undefined') {
            this.events[name] = [];
        }

        this.events[name].push(call);
    },

    /**
     *
     * @param name
     */
    removeEvent: function (name) {
        "use strict";
        if (typeof this.events[name] != 'undefined') {
            delete this.events[name];
            Tools.reindexArray(this.events);
        }
    },
    /**
     *
     * @param callback
     * @param formExit
     * @param unlockaction
     */
    closeActiveTab: function (callback, formExit, unlockaction) {
        var currentActiveTab = this.getActiveTab();
        if (currentActiveTab) {
            currentActiveTab.$tab.find('.remove-tab').trigger('click', formExit, unlockaction);
            if (typeof callback === 'function') {
                setTimeout(function () {
                    callback();
                }, 300);
            }
        }
        else {
            if (typeof callback === 'function') {
                callback();
            }
        }
    },

    /**
     *
     */
    onBeforeLoad: function () {
        "use strict";

        var currentActiveTab = this.getActiveTab();
        if (currentActiveTab)
        {
            var hash = currentActiveTab.hash;

            $('#content-container-inner').find('#content-' + hash).hide();
            $('#main-content-buttons').find('#buttons-' + hash).hide();
            $('#main-content-tabs').find('#content-tabs-' + hash).hide();
            $('#main-content-statusbar').find('#statusbar-' + hash).hide();
            $('#panel-documentsettings').find('#meta-' + hash).hide();

            currentActiveTab.$tab.removeClass('active');

            Core.updateViewPort();
        }
    },

    /**
     *
     * @param hash
     * @returns {boolean}
     */
    tabExists: function (hash) {
        "use strict";

        if (this.tabContainer.find('#tab-' + hash).length) {
            return true;
        }

        return false;
    },

    /**
     *
     * @returns {*}
     */
    getActiveTab: function () {
        "use strict";
        var wins = Core.WindowTabs;

        for (var x in wins) {
            if (wins[x].isActive) {
                return wins[x];
            }
        }

        return false;
    },

    /**
     * Helper
     * @param hash
     * @returns {*}
     */
    getTabInstanceByHash: function (hash) {
        "use strict";
        var tabs = Core.WindowTabs;
        var ret = false;

        for (var x in tabs) {
            if (tabs[x].hash === hash) {
                return tabs[x];
            }
        }

        return ret;
    },


    /**
     *
     * @param tab
     * @param label
     */
    setTabLabel: function (tab, label) {
        this.$tab.find('.label').find('span').html(label);
        this.$tab.attr('alt', label);
    },

    /**
     *
     * @param hash
     */
    removeScrollbar: function (hash) {
        "use strict";
    },
    /**
     *
     * @param hash
     */
    updateScrollbar: function (hash) {
        "use strict";

    },

    /**
     *
     * @returns {*}
     */
    refreshTab: function() {
        "use strict";
        var act = this.getActiveTab();

        if ( act && act.hash ) {

            var self = this, instance = this.getTabInstanceByHash(act.hash);

            if (typeof instance.tabConfig != 'object' || (typeof instance.tabConfig == 'object' && typeof instance.tabConfig.url != 'string' ) ) {
                console.log('Invalid tab configuration for refresh tab!');
                return;
            }

            var sub = instance.$contentWrapper.find('div.sub-content:visible');
            var usecontent = instance.$content;
            if (sub.length) {
                usecontent = sub;
            }

            usecontent.mask('reloading...');

            instance.$tab.removeClass('loaded');
            var oldLabel = instance.$tab.find('.label span').text();
            instance.$tab.find('.label span').text('laden...');


            $.ajax({
                type: 'GET',
                url: instance.tabConfig.url + '&ajax=true',
                cache: false,
                async: false,
                global: false,
                beforeSend: function () {
                    document.body.style.cursor = 'progress';
                },
                success: function (data) {

                    usecontent.unmask();

                    if (Tools.responseIsOk(data))
                    {


                        usecontent.removeData();
                        instance.$toolbar.removeData();
                        instance.$toolbar.empty();

                        if (data.toolbar) {
                            instance.$toolbar.append( data.toolbar );
                        }

                        if (data.maincontent) {
                            usecontent.empty().append( data.maincontent );
                        }

                        // versioning
                        var useVersioning = false;
                        if (typeof data.versioning != 'undefined' && data.versioning != '') {
                            instance.$versioning.append(data.versioning);
                            instance.$toolbar.append(versioning);
                            useVersioning = true;
                        }

                        // content Tabs
                        if (instance.$content.find('.tabcontainer').length) {

                            if (!$('#content-tabs-' + instance.hash ).length) {
                                if (!sub.length) {
                                    instance.$contentTabContainer = $('<div class="root-content-tabs" style="display:block"></div>').attr('id', 'root-content-tabs-' + instance.hash );
                                    instance.$contentTabContainer.appendTo(instance.contentTabsContainer);
                                    usecontent.find('.tabcontainer').appendTo($('#root-content-tabs-' + instance.hash ));
                                }
                                else {
                                    instance.$contentTabContainer = $('<div class="sub-content-tabs" style="display:block"></div>').attr('id', 'sub-content-tabs-' + instance.hash );
                                    instance.$contentTabContainer.appendTo(instance.contentTabsContainer);
                                    usecontent.find('.tabcontainer').appendTo($('#sub-content-tabs-' + instance.hash ));
                                }

                                $('#main-content-tabs').show();

                                instance.bindContentTabEvents($('#root-content-tabs-' + instance.hash ), instance.hash );
                            }
                            else {
                                usecontent.find('.tabcontainer').remove();
                                $('#main-content-tabs').hide();
                            }
                        }

                        if (usecontent.filter('script').length) {
                            //console.log('Eval Scripts after window Created');
                            Tools.eval(usecontent);
                        }

                        instance.postInit(data);
                    }
                    else {
                        instance.$tab.addClass('loaded');
                        instance.$tab.find('.label span').text(oldLabel);

                        console.log('Ajax Error: ', [data]);
                        jAlert( (data.msg ? data.msg : 'Invalid Ajax request!'), 'Ajax Error' );

                    }
                }
            });
        }
    },


    // -----------------------------------------

    /**
     *
     * @returns {*}
     */
    getActiveTabContent: function () {
        "use strict";

        var act = this.getActiveTab();
        if ( act ) {
            var sub = act.$contentWrapper.find('div.sub-content:visible');

            if ( sub.length )
            {
                return sub;
            }

            if ( act.$content.length )
            {
                return act.$content;
            }
        }

        return act.$content;
    },

    /**
     *
     * @returns {*}
     */
    getActiveToolbar: function() {
        "use strict";

        var act = this.getActiveTab();
        if ( act ) {
            var sub = act.$toolbarWrapper.find('div.sub-buttons:visible');

            if ( sub.length )
            {
                return sub;
            }

            if ( act.$toolbar.length )
            {
                return act.$toolbar;
            }
        }

        return false;
    },

    /**
     *
     * @returns {*}
     */
    getActiveStatusbar: function() {
        "use strict";
        var act = this.getActiveTab();

        if ( act ) {
            var sub = act.$statusbarWrapper.find('div.sub-status:visible');

            if ( sub.length )
            {
                return sub;
            }

            if ( act.$statusbar.length )
            {
                return act.$statusbar;
            }
        }

        return false;
    }
};