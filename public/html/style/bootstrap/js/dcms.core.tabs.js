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
        if ($('#content-' + activeTab.attr('id').replace('tab-', '')).find('#sub-window-' + hash).length || opt.isSingleWindow == true) {
            opt.forceNewTab = false;
        }

        if (opt.forceNewTab && opt.isSingleWindow !== true) {
            opt.forceNewTab = true;
        }
    }

    Core.Tabs.load(opt);
}


Core.Tabs0 = function (opt) {
    "use strict";
    this.windows = [];
    this.activeWindow = null;
};
Core.Tabs0.prototype = {


    init: function (dashboard) {
        "use strict";
        this.dashboard = dashboard;
        this.window = new Core.Window();
    },
    load: function (itemData) {
        "use strict";

        if (itemData.ajax && itemData.ajax != 0) {
            $.get(itemData.url, function (data) {
                if (itemData.obj) {
                    $(itemData.obj).find('.fa').removeClass('fa-spin');
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
        }
        else if (itemData.popup && itemData.popup != 0) {
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
                    if (itemData.obj) {
                        $(itemData.obj).find('.fa').removeClass('fa-spin');
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
        }
        else if (itemData.url && itemData.url !== '' && itemData.url !== '#' && itemData.url !== 'void()') {
            var win = this.window.load(opts);
            if (win) {
                this.windows.push(win);
            }
        }
        else {

        }
    },

    getActiveWindow: function () {
        "use strict";

    },
    setTabLabel: function () {
        "use strict";

    },

}

Core.Windows = function (opt) {
    "use strict";

    this.opts = opt || {};


    this.windowTemplate = '<div class="window">' +
        '<div class="window-wrapper">' +
        '   <header>' +
        '       <div class="window-header">' +
        '           <h1></h1>' +
        '           <div class="window-group-btns">' +
        '               <div class=""></div>' +
        '               <div class=""></div>' +
        '               <div class=""></div>' +
        '           </div>' +
        '       </div>' +
        '       <div class="window-toolbar"></div>' +
        '   </header>' +
        '   <section><div class="window-content"></div></section>' +
        '   <footer><div class="window-footer"></div></footer>' +
        '' +
        '</div>' +
        '<div class="window-overlay"></div>' +
        '</div>';


};


Core.Windows.prototype = {


    load: function (itemData) {
        var self = this, newWin;
        this.hash = md5(itemData.url);

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
                "use strict";

                if (Tools.responseIsOk(data)) {
                    newWin = self.prepareWindow(data);
                }
                else {

                }
            }
        });
    },

    // creating the window
    prepareWindow: function (data) {

        this.preInit(data);

        if (Core.isWindowSkin) {

        }
        else {

        }

        this.postInit(data);

    },

    preInit: function (ajaxdata) {
        // for tabs
        if (this.opts.obj) {
            $(this.opts.obj).find('.fa').removeClass('fa-spin');
        }


        // for windows

    },

    postInit: function (ajaxdata, opts) {


    },


    // Button Group events
    restore: function () {


    },

    min: function () {

    },

    close: function () {


    },

};


Core.Tabs = {
    options: {
        tab_tooltip_position: 'center'
    },
    elementCache: [],
    cssLoadCache: {},
    tabCachedAjaxData: {},
    tab_content_template: '<div id="content-{hash}" class="core-tab-content" style="display:none"></div>',
    activeTabHash: null,
    setActiveHash: null,
    dashboard: false,
    /**
     *
     * @param dashboard
     */
    init: function (dashboard) {

        // Instance of dashboard
        this.dashboard = dashboard;

        $('#main-tabs').prepend('<div id="move-left"></div>');
        $('#main-tabs').append('<div id="move-right"></div>');
        this.bindTabOverflow();
    },
    /**
     *
     */
    bindTabOverflow: function () {
        var interval;
        $('#main-tabs ul').change(function () {
            if ($(this).get(0).scrollWidth > $('#main-tabs').width() - 18) {
                $('#main-tabs').find('#move-left,#move-right').show();
            }
            else {
                $('#main-tabs').find('#move-left,#move-right').hide();
                $(this).css({left: 0});
            }
        });

        $('#main-tabs').find('#move-left').bind('mousedown',function () {
            interval = setInterval(function () {
                var pos = $('#main-tabs ul').position();
                if (pos.left < 0 && pos.left + 1 < 0) {
                    $('#main-tabs ul').css({
                        left: '+=' + 1
                    });
                }
                else {
                    $('#main-tabs ul').css({
                        left: 0
                    });
                    clearInterval(interval);
                }
            }, 2);

        }).bind('mouseup', function () {
            clearInterval(interval);
        });

        $('#main-tabs').find('#move-right').bind('mousedown',function () {
            interval = setInterval(function () {
                var ulWidth = $('#main-tabs ul').width();
                var ulInnerWidth = $('#main-tabs ul').innerWidth();
                var pos = $('#main-tabs ul').position();
                var scrollwidth = $('#main-tabs ul').get(0).scrollWidth;
                var diff = scrollwidth - ulWidth - ($('#main-tabs').find('#move-right').width());
                if (pos.left - 1 > 0 - diff) {
                    $('#main-tabs ul').css({
                        left: '-=' + 1
                    });
                }
                else {
                    $('#main-tabs ul').css({
                        left: 0 - diff
                    });
                    clearInterval(interval);
                }
            }, 2);
        }).bind('mouseup', function () {
            clearInterval(interval);
        });
    },


    /**
     *
     * @param itemData
     */
    load: function (itemData) {
        var self = this;


        if (itemData.ajax && itemData.ajax != 0) {
            $.get(itemData.url, function (data) {
                if (itemData.obj) {
                    $(itemData.obj).find('.fa').removeClass('fa-spin');
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
        }
        else if (itemData.popup && itemData.popup != 0) {
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
                    if (itemData.obj) {
                        $(itemData.obj).find('.fa').removeClass('fa-spin');
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
        }
        else {
            if (itemData.url && itemData.url !== '' && itemData.url !== '#' && itemData.url !== 'void()') {
                var hash = md5(itemData.url);
                var tab, isSingle = false, activeTab = this.getActiveTab();
                this.openerHash = false;

                if (activeTab && activeTab.length)
                {
                    if (activeTab.attr('single') && itemData.forceNewTab == false) {
                        isSingle = activeTab.attr('id').replace('tab-', '');
                    }
                }

                if (typeof itemData.forceNewTab != 'undefined' && itemData.forceNewTab === true) {
                    isSingle = false;
                    this.openerHash = false;
                    activeTab = null;
                }
/*
                if (this.activeTabHash && this.openerHash && this.activeTabHash != this.openerHash )
                {
                    this.openerHash = this.activeTabHash;
                    isSingle = this.activeTabHash;
                }
*/

                $('#main-content-mid').mask('Laden...');

                if (isSingle)
                {





                    if (!this.openerHash) {
                        this.openerHash = isSingle;
                    }

                    activeTab.removeClass('loaded');
                    activeTab.find('.label span').text('laden...');
                    tab = activeTab;
                }
                else {
                    this.openerHash = false;
                }

                if ( this.openerHash )
                {
                    if ($('#sub-window-' + hash).length)
                    {
                        $('#main-content-mid').unmask();

                        var winData = $('#sub-window-' + hash).data('win');
                        if (tab && winData.pageCurrentTitle && winData.pageCurrentTitle != '') {
                            this.setTabLabel(tab, winData.pageCurrentTitle);
                        }
                        else if (tab && winData.applicationTitle && winData.applicationTitle != '') {
                            this.setTabLabel(tab, winData.applicationTitle);
                        }
                        else {
                            this.setTabLabel(tab, itemData.label);
                        }
                        if (winData.rollback) {
                            tab.attr('rollback', itemData.url);
                        }
                        else {
                            tab.removeAttr('rollback');
                        }
                        if (winData.nopadding === true || $('#sub-window-' + hash).find('div.gc').length) {
                            $('#content-' + this.openerHash).data('nopadding', true);
                        }
                        else {
                            $('#content-' + this.openerHash).removeData('nopadding');
                        }

                        $('#root-buttons-' + this.openerHash + ',#root-window-' + this.openerHash + ',#root-status-' + this.openerHash).fadeOut(200, function () {
                            // $(window).trigger('resize');
                        });

                        //$( '#root-window-' + this.openerHash ).hide();
                        //$( '#root-status-' + this.openerHash ).hide();

                        if ($('#sub-window-' + hash).hasClass('dirty')) {
                            tab.addClass('dirty');
                            $('#sub-window-' + hash).removeClass('dirty');
                        }

                        $('#sub-buttons-' + hash + ',#sub-window-' + hash + ',#sub-status-' + hash).fadeIn(300);
                        //	$( '#sub-window-' + hash ).show();
                        //	$( '#sub-status-' + hash ).show();

                        this.enableHomeButton(this.openerHash);
                        activeTab.addClass('loaded');

                        if (itemData.obj) {
                            $(itemData.obj).find('.fa').removeClass('fa-spin');
                        }

                        activeTab.trigger('click');

                        setTimeout(function () {
                            $(window).trigger('resize');
                        }, 320);

                        return;
                    }
                }

                if (!this.tabExists(hash) && !$('#sub-window-' + hash).length)
                {
                    if (!this.openerHash) {
                        var tpl = this.tab_content_template.replace('{hash}', hash);
                        $(tpl).appendTo($('#content-container-inner'));
                        $('#main-tabs ul').find('.active');
                        tab = this.createTab(itemData, hash);
                        $('#main-tabs ul').append(tab);
                        this.setActiveHash = hash;
                        $('#main-tabs ul').trigger('change');
                    }

                    this.bindTabEvents();

                    if (!this.openerHash) {
                        this.onBeforeLoad();
                    }
                    else {
                        $('#root-buttons-' + this.openerHash + ',#root-window-' + this.openerHash + ',#root-status-' + this.openerHash).fadeOut(300);
                        //$( '#root-window-' + this.openerHash ).hide();
                        //$( '#root-status-' + this.openerHash ).hide();
                        this.setActiveHash = this.openerHash;
                    }

                    // hide the dashboard
                    if (self.dashboard) {
                        self.dashboard.hide();
                    }

                    tab.addClass('active');


                    $.ajax({
                        type: 'GET',
                        url: itemData.url + '&ajax=true',
                        cache: false,
                        async: true,
                        global: false,
                        beforeSend: function () {
                            document.body.style.cursor = 'progress';
                        },
                        success: function (data) {
                            if (itemData.obj) {
                                $(itemData.obj).find('.fa').removeClass('fa-spin');
                            }

                            if (Tools.responseIsOk(data)) {
                                if (!isSingle) {
                                    Win.setActive(hash);
                                }
                                else if (self.openerHash) {
                                    Win.setActive(self.openerHash);
                                }

                                // show filemanager tree panel & button
                                if (itemData.url.match(/fileman/)) {
                                    $('#panel-buttons li[rel=files]').show();
                                }


                                if (typeof data.lock_content != 'undefined') {
                                    if (itemData.isTreeNode && itemData.obj) {

                                        var icon = itemData.obj.find('.tree-node-icon:first');
                                        icon.addClass('locked').parents('div.tree-node:first').addClass('locked');

                                        /*
                                         var src = itemData.obj.find( '.tree-node-icon:first' ).attr( 'class' );
                                         if ( src && !src.match( /-locked/ ) ) {
                                         var n = src.replace( /^([^\.]*)(\.gif|\.png)$/ig, '$1-locked.$2' );
                                         itemData.obj.find( '.tree-node-icon:first' ).attr( 'src', n );
                                         }
                                         */

                                        if (itemData.obj.data('nodeData')) {
                                            var d = itemData.obj.data('nodeData');
                                            d.locked = 1;
                                            itemData.obj.data('nodeData', d);
                                        }

                                        tab.data('treeNodeItem', itemData.obj);
                                    }
                                }

                                // self.prepareData(data, itemData, tab, hash);
                                if (Tools.exists(data, 'loadScripts')) {
                                    var load = 0;
                                    if (Tools.exists(data.loadScripts, 'css') && data.loadScripts.css.length) {
                                        load += data.loadScripts.css.length;
                                    }
                                    if (Tools.exists(data.loadScripts, 'js') && data.loadScripts.js.length) {
                                        load += data.loadScripts.js.length;
                                    }
                                    if (load) {
                                        if (Tools.exists(data.loadScripts, 'css') && data.loadScripts.css.length) {
                                            for (var x = 0; x < data.loadScripts.css.length; x++) {
                                                if (data.loadScripts.css[x].substr(data.loadScripts.css[x].length - 4, data.loadScripts.css[x].length) != '.css') {
                                                    data.loadScripts.css[x] += '.css';
                                                }

                                                var cssh = md5(data.loadScripts.css[x]);

                                                if (typeof self.cssLoadCache[cssh] == 'undefined') {
                                                    Tools.loadCss(data.loadScripts.css[x], function (styleTag) {
                                                        self.cssLoadCache[cssh] = true;
                                                        styleTag.attr('id', 'css-' + cssh);
                                                    });
                                                }
                                            }

                                            if (Tools.exists(data.loadScripts, 'js') && !data.loadScripts.js.length || !Tools.exists(data.loadScripts, 'js')) {
                                                self.prepareData(data, itemData, tab, hash);
                                            }
                                        }

                                        if (Tools.exists(data.loadScripts, 'js') && data.loadScripts.js.length) {
                                            Tools.loadScripts(data.loadScripts.js, function () {
                                                self.prepareData(data, itemData, tab, hash);
                                            });
                                        }
                                    }
                                    else {
                                        self.prepareData(data, itemData, tab, hash);
                                    }
                                }
                                else {
                                    self.prepareData(data, itemData, tab, hash);
                                }
                            }
                            else {
                                $('#main-content-mid').unmask();
                                document.body.style.cursor = '';
                                self.openerHash = false;
                                self.setActiveHash = null;

                                // remove tab
                                if (data && typeof data.permissionerror != 'undefined' && data.permissionerror) {
                                    if (tab && !activeTab) {
                                        tab.removeAttr('rollback');
                                        tab.find('.remove-tab').trigger('click');
                                    }
                                }
                                else if (data && typeof data.lockerror != 'undefined' && data.lockerror) {
                                    if (tab && !activeTab) {
                                        tab.removeAttr('rollback');
                                        tab.find('.remove-tab').trigger('click');
                                    }
                                }


                                if (data && typeof data.msg != 'undefined') {
                                    Notifier.warn(data.msg);
                                }
                                console.log(data);
                            }
                        }
                    });
                }
                else {

                    $('#tab-' + hash).trigger('click');
                    $('#main-content-mid').unmask();

                    if (itemData.obj)
                    {
                        $(itemData.obj).find('.fa').removeClass('fa-spin');
                    }
                }
            }
        }
    },
    /**
     *
     * @param hash
     */
    removeScrollbar: function (hash) {
        //$('#content-' + hash).removeNanoScroller();
    },
    /**
     *
     * @param hash
     */
    updateScrollbar: function (hash) {
        var use = $('#content-' + hash);


        if ($('#content-' + hash).attr('fm') && use.length) {
            use = $('#content-' + hash).find('.content-wrap:first');

            if (use && use.length && typeof use.removeNanoScroller == 'function' && !$('#content-' + hash).find('.inline-window-slider').length) {
                use.removeNanoScroller();
                return;
            }
        }

        if (!use || (use && !use.length)) {
            return;
        }

        if ($('#content-' + hash).find('.subwindow:visible').length == 1) {
            var sub = $('#content-' + hash).find('.subwindow:visible');
            if (!sub.find('.gc').length && !sub.hasClass('gc') && use.length) {
                use.nanoScroller({scrollContent: $('#content-' + hash)});

                var nanos = use.find('.nano'), nlen = nanos.length;
                for (var x = 0; x < nlen; ++x) {
                    var data = $(nanos[x]).data('nano');
                    if (data) {
                        Tools.refreshScrollBar(data.$content);
                    }
                }


            }
            else {
                sub.css({overflow: 'hidden'});
            }
        }
        else if ($('#content-' + hash).find('.rootwindow:visible').length == 1) {
            var sub = $('#content-' + hash).find('.rootwindow:visible');

            if (!sub.find('.gc').length && !sub.hasClass('gc') && use.length && typeof use.nanoScroller == 'function') {

                use.nanoScroller({scrollContent: $('#content-' + hash)});
                var nanos = use.find('.nano'), nlen = nanos.length;
                for (var x = 0; x < nlen; ++x) {
                    var data = $(nanos[x]).data('nano');
                    if (data) {
                        Tools.refreshScrollBar(data.$content);
                    }
                }
            }
            else {
                sub.css({overflow: 'hidden'});

            }
        }
        else {
            if ($('#content-' + hash).data('windowGrid')) {

                if (use && use.length && typeof use.removeNanoScroller == 'function') {
                    use.removeNanoScroller();
                }
            }

        }
    },
    /**
     *
     * @param tab
     * @param label
     */
    setTabLabel: function (tab, label) {
        tab.find('.label').find('span').html(label);
        tab.attr('alt', label);
    },
    /**
     *
     * @param hash
     * @param tab
     * @returns {boolean}
     */
    createSingleWindowButtons: function (hash, tab) {
        tab.attr('single', true);
        var self = this, container = $('<div class="singlewindow-buttons">');
        var homeBtn = $('<button class="btn-home disabled" hash="' + hash + '"><span class="fa fa-home"></span></button>');
        homeBtn.click(function (e) {
            if (!$(this).hasClass('disabled')) {
                e.preventDefault();

                var h = $(this).attr('hash');
                var data = $('#content-' + h).find('div.rootwindow').data('win');
                var t = $('#tab-' + h);
                if (tab && data.pageCurrentTitle && data.pageCurrentTitle != '') {
                    self.setTabLabel(t, data.pageCurrentTitle);
                }
                else if (tab && data.applicationTitle && data.applicationTitle != '') {
                    self.setTabLabel(t, data.applicationTitle);
                }
                else {
                    if (data.label) {
                        self.setTabLabel(t, data.label);
                    }
                }
                if (t.hasClass('dirty')) {
                    $('#content-' + h).find('div.subwindow:visible').addClass('dirty');
                    t.removeClass('dirty');
                }
                if (data.nopadding || $('#content-' + h).find('div.rootwindow').find('.gc').length) {
                    $('#content-' + h).data('nopadding', true);
                    $('#content-' + h).addClass('no-padding');
                }
                else {
                    $('#content-' + h).removeData('nopadding');
                    $('#content-' + h).removeClass('no-padding');
                }
                // hide sub window
                $('#content-' + h).find('div.subwindow:visible').hide();
                $('#content-' + h).find('div.rootwindow').show();

                // hide sub buttons
                $('#buttons-' + h).find('div.subbuttons:visible').hide();
                $('#buttons-' + h).find('div.rootbuttons').show();

                // hide statusbar
                $('#statusbar-' + h).find('div.sub-status').hide();
                $('#statusbar-' + h).find('div.root-status').show();

                tab.trigger('click');
                $(this).addClass('disabled');
                $(window).trigger('resize');
            }
        });
        container.append(homeBtn);
        $('#buttons-' + hash).prepend(container);
        return true;
    },
    /**
     *
     * @param hash
     */
    enableHomeButton: function (hash) {
        $('#buttons-' + hash).find('.singlewindow-buttons button.btn-home').removeClass('disabled');
    },
    /**
     *
     * @param itemData
     * @param hash
     * @returns {String|tinymce.html.Node|*|jQuery}
     */
    createTab: function (itemData, hash) {
        var li = $('<li>').attr('id', 'tab-' + hash);
        var app = Core.getAppKey(itemData.url);
        var currentActiveTab = $('#main-tabs ul').find('li.active');
        if (currentActiveTab.length) {
            li.attr('opener', currentActiveTab.attr('id'));
        }
        if (!$('#main-tabs ul').find('li[app=' + app + ']').length) {
            li.attr('isroot', '1');
        }
        li.data('itemData', itemData).attr('app', app);
        li.append($('<span class="first"></span>'));
        li.append($('<span class="label"><i></i><span>laden...</span></span>'));
        li.append($('<span class="remove-tab"><i></i></span>'));
        li.append($('<span class="last"></span>'));
        return li;
    },
    /**
     *
     * @returns {boolean}
     */
    getActiveTabContent: function () {
        var useContent = false, hash = this.getActiveTabHash();
        if (hash) {

            if ($('div.subwindow.creating', $('#content-container')).length == 1) {
                useContent = $('div.subwindow.creating', $('#content-container'));
            }
            else if ($('div.subwindow:visible', $('#content-' + hash)).length == 1) {
                useContent = $('div.subwindow:visible', $('#content-' + hash));
            }
            else if ($('#root-window-' + hash).length == 1) {
                useContent = $('#root-window-' + hash);
            }
        }
        return useContent;
    },


    /**
     *
     * @returns {*}
     */
    getActiveTabHash: function () {
        if (this.setActiveHash !== null) {
            var t = $('#main-tabs ul').find('li[id="tab-' + this.setActiveHash + '"]');
            if (t.length == 1) {
                return this.setActiveHash;
            }
            else {
                return false;
            }
        }
        var tab = $('#main-tabs ul').find('li.active');
        if (tab.length == 1) {
            return tab.attr('id').replace('tab-', '');
        }
        else {
            return false;
        }
    },

    /**
     *
     * @returns {*|jQuery}
     */
    getActiveTab: function () {
        if (this.setActiveHash !== null)
        {
            return $('#main-tabs ul').find('#tab-' + this.setActiveHash);
        }

        return $('#main-tabs ul').find('li.active');
    },

    /**
     *
     * @param callback
     */
    refreshActiveTab: function (callback) {
        this.refreshTab(this.getActiveTabHash(), callback);
    },

    /**
     *
     * @returns {*}
     */
    getActiveToolbar: function () {
        var tab = this.getActiveTab();
        if (tab.length == 1)
        {
            var hash = tab.attr('id').replace('tab-', '');

            if (tab.attr('single'))
            {
                // find in single window
                if ($('#buttons-' + hash).find('.subbuttons:visible').length == 1) {
                    return $('#buttons-' + hash).find('.subbuttons:visible');
                }
                if ($('#buttons-' + hash).find('.rootbuttons:visible').length == 1) {
                    return $('#buttons-' + hash).find('.rootbuttons:visible');
                }
                return false;
            }
            else {
                return $('#buttons-' + hash);
            }
        }
        return false;
    },

    /**
     *
     * @returns {*}
     */
    getActiveStatusbar: function () {
        var tab = this.getActiveTab();
        if (tab.length == 1) {
            var hash = tab.attr('id').replace('tab-', '');
            if (tab.attr('single')) {
                if ($('#statusbar-' + hash).find('.sub-status:visible').length == 1) {
                    return $('#statusbar-' + hash).find('.sub-status:visible');
                }
                if ($('#statusbar-' + hash).find('.root-status:visible').length == 1) {
                    return $('#statusbar-' + hash).find('.root-status:visible');
                }
                return false;
            }
            else {
                return $('#statusbar-' + hash).find('.root-status:visible');
            }
        }
        return false;
    },

    /**
     *
     * @param callback
     * @param formExit
     * @param unlockaction
     */
    closeActiveTab: function (callback, formExit, unlockaction) {
        var currentActiveTab = $('#main-tabs ul').find('li.active');
        if (currentActiveTab.length) {
            currentActiveTab.find('.remove-tab').trigger('click', formExit, unlockaction);
            if (typeof callback === 'function') {
                setTimeout(function () {
                    callback();
                }, 500);
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
        var currentActiveTab = this.getActiveTab();
        if (currentActiveTab.length == 1) {
            var hash = currentActiveTab.attr('id').replace('tab-', '');
            $('#content-container-inner').find('#content-' + hash).hide();
            $('#main-content-buttons').find('#buttons-' + hash).hide();
            $('#main-content-tabs').find('#content-tabs-' + hash).hide();
            $('#main-content-statusbar').find('#statusbar-' + hash).hide();
            $('#panel-documentsettings').find('#meta-' + hash).hide();
            currentActiveTab.removeClass('active');
            Core.updateViewPort();
        }
    },


    /**
     *
     * @param hash
     * @param callback
     * @param formExit
     * @param unlockaction
     */
    onBeforeClose: function (hash, callback, formExit, unlockaction) {
        var self = this, useContent, rollback;
        var opener = $('#tab-' + hash).attr('opener');
        useContent = this.getActiveTabContent();

        if ($('#tab-' + hash).hasClass('dirty')) {
            jConfirm(cmslang.form_dirty, cmslang.alert, function (ok) {
                if (ok) {
                    var events = useContent ? useContent.data('events') : false;
                    if (events && typeof events.onBeforeClose != 'undefined' && events.onBeforeClose.length > 0) {
                        for (var i = 0; i < events.onBeforeClose.length; ++i) {
                            if (typeof events.onBeforeClose[i] == 'function') {
                                events.onBeforeClose[i](useContent, opener, hash);
                            }
                        }
                    }

                    Doc.unload(hash, useContent, $('#tab-' + hash).attr('isroot'));

                    callback();
                }
            });
        }
        else {
            if (useContent) {
                var events = useContent.data('events');
                if (events && typeof events.onBeforeClose != 'undefined' && events.onBeforeClose.length > 0) {
                    for (var i = 0; i < events.onBeforeClose.length; ++i) {
                        if (typeof events.onBeforeClose[i] == 'function') {
                            events.onBeforeClose[i](useContent, opener, hash);
                        }
                    }
                }
                Doc.unload(hash, useContent, $('#tab-' + hash).attr('isroot'));
            }

            callback();
        }
    },

    /**
     *
     * @param url
     * @param callback
     * @param lidata
     * @param formExit
     * @param unlockaction
     */
    sendRollback: function (url, callback, lidata, formExit, unlockaction) {

        if (formExit == true) {
            if (lidata && lidata.treeNodeItem) {

                var node = lidata.treeNodeItem;
                var icon = node.find('.tree-node-icon:first');
                if (icon) {
                    icon.removeClass('locked').parents('div.tree-node:first').addClass('locked');
                    icon.attr('class', icon.attr('class').replace('-locked', ''));
                }

                if (node.data('nodeData')) {
                    var d = node.data('nodeData');
                    d.locked = 0;
                    node.data('nodeData', d);
                }
            }

            if (typeof callback == 'function') {
                callback();
            }
        }
        else {

            if (typeof url == 'string') {

                var postData = Tools.convertUrlToObject(url);
                postData.ajax = true;
                postData.transrollback = true;

                $.ajax({
                    type: "POST",
                    url: 'admin.php',
                    'data': postData,
                    dataType: 'json',
                    cache: false,
                    async: false,
                    success: function (data) {
                        if (lidata && lidata.treeNodeItem) {

                            var node = lidata.treeNodeItem;
                            var icon = node.find('.tree-node-icon:first');
                            if (icon) {
                                icon.removeClass('locked').parents('div.tree-node:first').addClass('locked');
                                icon.attr('class', icon.attr('class').replace('-locked', ''));
                            }

                            if (node.data('nodeData')) {
                                var d = node.data('nodeData');
                                d.locked = 0;
                                node.data('nodeData', d);
                            }
                        }

                        if (typeof callback == 'function') {
                            callback();
                        }
                    }
                });
            }
            else {
                if (typeof callback == 'function') {
                    callback();
                }
            }
        }
    },

    /**
     *
     * @param hash
     * @param callback
     */
    refreshTab: function (hash, callback) {
        var self = this;
        var activeTab = this.getActiveTab();
        var itemData = $('#tab-' + hash).data('itemData');
        if (itemData && itemData.url) {
            var tab = $('#tab-' + hash);
            tab.removeClass('loaded dirty');
            if ($('#content-' + hash).data('windowGrid')) {
                tab.trigger('click');
                $('#content-' + hash).data('windowGrid').refresh(function () {
                    tab.addClass('loaded');
                    activeTab.trigger('click');
                    if (typeof callback === 'function') {
                        callback();
                    }
                });
            }
            else {
                $.ajax({
                    type: 'GET',
                    url: itemData.url + '&ajax=true',
                    cache: false,
                    async: true,
                    global: false,
                    beforeSend: function () {
                        document.body.style.cursor = 'progress';
                    },
                    success: function (data) {
                        if (Tools.responseIsOk(data)) {
                            tab.trigger('click');
                            var formID = false;
                            var contentToolbar = $('buttons-' + hash);
                            contentToolbar.empty();
                            if (typeof data.toolbar != 'undefined' && data.toolbar != '') {
                                contentToolbar.append(data.toolbar);
                            }
                            if (typeof data.versioning != 'undefined' && data.versioning != '') {
                                var versioning = $('<div>').addClass('content-versions');
                                versioning.append(data.versioning);
                                contentToolbar.append(versioning);
                            }
                            var useContent;
                            $('#content-' + hash).removeData();
                            if ($('#content-' + hash).find('.rootwindow').is(':visible')) {
                                useContent = $('#content-' + hash).find('.rootwindow:visible');
                                Doc.unloadAce(useContent);
                                Doc.unloadTinyMce(useContent);
                                useContent.empty().append(data.maincontent);
                            }
                            else {
                                useContent = $('#content-' + hash).find('.subwindow:visible');
                                Doc.unloadAce(useContent);
                                Doc.unloadTinyMce(useContent);
                                useContent.empty().append(data.maincontent);
                            }
                            if (useContent.find('.tabcontainer').length) {
                                var contentTabs = $('content-tabs-' + hash);
                                if (!$('#content-tabs-' + hash).length) {
                                    contentTabs.empty().append($('#content-' + hash).find('.tabcontainer'));
                                    $('#content-' + hash).find('.tabcontainer').remove();
                                    $('#main-content-tabs').show();
                                }
                                else {
                                    $('#content-' + hash).find('.tabcontainer').remove();
                                    $('#main-content-tabs').hide();
                                }
                                self.bindContentTabEvents($('#content-tabs-' + hash), hash);
                            }
                            var hasMeta = false;
                            var meta = $('meta-' + hash);
                            if (useContent.find('#document-metadata').length) {
                                formID = $('#content-' + hash).data('formID');
                                if (formID) {
                                    var html = $('<form>')
                                        .attr('id', 'documentmetadata' + formID)
                                        .data('windowID', 'content-' + hash)
                                        .data('realFormID', formID)
                                        .append(useContent
                                            .find('#document-metadata')
                                            .html().trim());
                                    meta.empty().append(html);
                                }
                                useContent.find('#document-metadata').remove();
                                hasMeta = true;
                            }
                            if (formID) {
                                $('#meta-' + hash).find('#documentmetadata' + formID).data('formConfig', $('#content-' + hash).data('formConfig'));
                            }
                            if (data.nopadding) {
                                $('#content-' + hash).data('nopadding', true);
                            }
                            else {
                                $('#content-' + hash).removeData('nopadding');
                            }
                            self.activeTabHash = hash;
                            Win.setActive(hash);
                            tab.addClass('set');
                            tab.parent().find('li:not(.set)').each(function () {
                                $(this).removeClass('active');
                                var id = $(this).attr('id').replace('tab-', '');
                                $('#content-container-inner').find('#content-' + id).hide();
                                $('#main-content-buttons').find('#buttons-' + id).hide();
                                $('#main-content-tabs').find('#content-tabs-' + id).hide();
                                $('#main-content-statusbar').find('#statusbar-' + id).hide();
                                $('#panel-documentsettings').find('#meta-' + id).hide();
                            });
                            tab.removeClass('set').addClass('active');
                            $('#content-container-inner').find('#content-' + hash).show();
                            $('#main-content-buttons').find('#buttons-' + hash).show();
                            $('#main-content-tabs').find('#content-tabs-' + hash).show();
                            $('#main-content-statusbar').find('#statusbar-' + hash).show();
                            $('#panel-documentsettings').find('#meta-' + hash).show();

                            if ($(data.maincontent).filter('script').length) {
                                //   console.log('Eval Scripts after window Created');
                                Tools.eval($(data.maincontent));
                            }
                            Doc.loadTinyMce($('#content-' + hash));
                            createTemplateEditor('content-' + hash);

                            if ($('#content-' + hash).data('windowGrid')) {
                                $('#content-container').addClass('no-padding');
                                var g = $('#content-' + hash).data('windowGrid');
                                g.createGrid(g, function () {
                                    document.body.style.cursor = '';
                                    Core.initMetaData(hasMeta, hash);
                                    tab.trigger('click');
                                    Win.prepareWindowFormUi();
                                    $('#content-container').addClass('no-padding');
                                    // Core.updateViewPort();
                                    g.updateDataTableSize($('#content-' + hash));

                                    $('#main-content-mid').unmask();

                                    $(window).trigger('resize');
                                    tab.addClass('loaded');
                                    activeTab.trigger('click');
                                });
                            }
                            else {
                                document.body.style.cursor = '';

                                $('#main-content-mid').unmask();

                                tab.trigger('click');
                                Core.initMetaData(hasMeta, hash);
                                if (contentTabs) {
                                    self.bindContentTabEvents(contentTabs, hash);
                                }
                                if (formID) {
                                    Form.registerLiveEventsForMetadata(formID);
                                }
                                Win.prepareWindowFormUi();
                                Core.updateViewPort();
                                self.updateScrollbar(hash);
                                self.bindEasyEvents(hash);
                                ToolTip.rebuildTooltips();
                                ToolTip.buildTips();

                                if ($('#content-' + hash).attr('isfileman')) {
                                    self.triggerFilemanSizing();
                                }

                                if (typeof resizeAce === 'function' && $('#content-' + hash).data('ace')) {
                                    setTimeout(function () {
                                        resizeAce($('#content-' + hash), false);
                                    }, 1000);
                                }

                                activeTab.trigger('click');
                                tab.addClass('loaded');
                            }
                            document.body.style.cursor = '';
                            tab.addClass('loaded');
                            activeTab.trigger('click');
                            if (typeof callback === 'function') {
                                callback();
                            }
                        }
                        else {
                            console.log(data);
                            document.body.style.cursor = '';
                            tab.addClass('loaded');
                            activeTab.trigger('click');
                        }
                    }
                });
            }
        }
    },
    /**
     *
     * @param {type} tab
     * @returns {undefined}
     * @todo fix positions works not 100%!!!
     */
    fixActiveTabPos: function (tab) {
        var index = $(tab).index();
        var ulLeft = parseInt($('#main-tabs ul').css('left'), 10);
        var ul = $('#main-tabs ul');
        var ulOffset = $('#main-tabs ul').offset();
        if (ul.width() <= ul.get(0).scrollWidth) {
            return;
        }
        //    var scrollWidth = ul.get(0).scrollWidth;
        var thisPos = $(tab).position();
        var thisOffset = $(tab).offset();
        var isLeftOut = thisOffset.left - ulOffset.left - 18;
        var active = $('#main-tabs ul').find('li.active');
        var activePos = false, moveLeft = false;
        if (active.length == 1) {
            activePos = $(active).position();
        }
        if (activePos.left > thisPos.left) {
            moveLeft = true;
        }
        if (isLeftOut < 0 || moveLeft) {
            if (index == 0) {
                $('#main-tabs ul').animate({
                    left: 0
                }, {
                    duration: 200
                });
            }
            else {
                var ml = $('#move-left').offset();
                var w = $(tab).width();
                var l = thisOffset.left;
                l -= $('#move-left').width();
                if (!isNaN(parseInt(ul.css("margin-left")))) {
                    l -= parseInt(ul.css("margin-left"));
                }
                if (ulLeft + l > 0) {
                    $('#main-tabs ul').stop().animate({
                        left: 0
                    }, {
                        duration: 200
                    });
                }
                else {
                    $('#main-tabs ul').stop().animate({
                        left: '+=' + l
                    }, {
                        duration: 200
                    });
                }
            }
        }
        else if (ulLeft + thisPos.left + $(tab).width() > ul.width() && index > 0) {
            var mr = $('#move-right').offset();
            var l = thisPos.left + $(tab).width();
            //l -= $('#move-right').width();
            if (!isNaN(parseInt(ul.css("padding-left")))) {
                l -= parseInt(ul.css("padding-left"));
            }
            if (!isNaN(parseInt(ul.css("margin-right")))) {
                l += parseInt(ul.css("margin-right"));
            }
            l -= $('#main-tabs').width();
            l -= $('#move-left').width();
            // l -= $('#move-right').outerWidth(true);
            if (!$(tab).hasClass('loaded')) {
                l -= 16;
            }
            $('#main-tabs ul').stop().animate({
                left: 0 - (l)
            }, {
                duration: 200
            });
        }
    },

    /**
     *
     */
    bindTabEvents: function () {
        var self = this;
        $('#main-tabs ul').find('li .remove-tab').unbind('click.remove').bind('click.remove', function (e, formExit, unlockaction) {
            e.preventDefault();

            var iself = this;
            var li = $(this).parents('li:first');
            var id = li.attr('id').replace('tab-', '');
            var app = li.attr('app');
            var isroot = li.attr('isroot');
            var opener = li.attr('opener');
            var meta = li.hasClass('meta');
            var nodedata = li.data();

            self.onBeforeClose(id, function () {
                var thisrollback = li.attr('rollback');

                $('#fm-slider', $('#main-content-mid')).hide();
                var allAppTabs = $('#main-tabs ul').find('li[app=' + app + ']');

                if (isroot && allAppTabs.length > 1) {
                    Win.removeWindowFormUi(id);

                    allAppTabs.each(function (i) {
                        var subid = $(this).attr('id').replace('tab-', '');
                        var selfRollback = $(this).attr('rollback');
                        var selfopener = $(this).parent().attr('opener');

                        Doc.unload(subid, $('#content-' + subid));

                        $('#content-' + subid + ',#buttons-' + subid + ',#content-tabs-' + subid + ',#meta-' + subid + ',#statusbar-' + subid).fadeOut(150);
                        $(this).fadeOut(150, function () {
                            Win.removeWindowFormUi(subid);

                            if (meta) {
                                Core.initMetaData(false);
                            }

                            var subdata = $(this).data();

                            if (selfopener) {

                                $(this).remove();
                                $('#content-' + subid + ',#buttons-' + subid + ',#content-tabs-' + subid + ',#meta-' + subid + ',#statusbar-' + subid).remove();

                                $('#' + selfopener).trigger('click');
                                //$('#main-tabs ul').trigger('change');
                                if (selfRollback) {
                                    setTimeout(function () {
                                        self.sendRollback(selfRollback, false, subdata);
                                    }, 1000);
                                }
                                if (i + 1 >= allAppTabs.length && rollback) {
                                    setTimeout(function () {
                                        self.sendRollback(rollback, false, data);
                                    }, 1000);
                                }

                                if (i + 1 >= allAppTabs.length) {
                                    Doc.unload(id, $('#content-' + id));
                                }

                            }
                            else {
                                if ($(this).next().is('li') && !$(this).prev().is('li')) {
                                    $(this).next().trigger('click');
                                    $(this).remove();
                                    $('#content-' + subid + ',#buttons-' + subid + ',#content-tabs-' + subid + ',#meta-' + subid + ',#statusbar-' + subid).remove();
                                }
                                else if ($(this).prev().is('li') && !$(this).next().is('li')) {
                                    $(this).prev().trigger('click');
                                    $(this).remove();
                                    $('#content-' + subid + ',#buttons-' + subid + ',#content-tabs-' + subid + ',#meta-' + subid + ',#statusbar-' + subid).remove();
                                }
                                else {
                                    $(this).remove();
                                    $('#content-' + subid + ',#buttons-' + subid + ',#content-tabs-' + subid + ',#meta-' + subid + ',#statusbar-' + subid).remove();

                                    self.activeTabHash = null;
                                    Win.setActive(null);

                                    if (self.dashboard) {
                                        self.dashboard.show();
                                    }

                                    if (meta) {
                                        $('#panel-buttons').find('li[rel]:first').trigger('click');
                                    }
                                }

                                $('#main-tabs ul').trigger('change');

                                if (selfRollback) {
                                    setTimeout(function () {
                                        self.sendRollback(selfRollback, false, subdata, formExit, unlockaction);
                                    }, 1000);
                                }

                                if (i + 1 >= allAppTabs.length) {
                                    Doc.unload(id, $('#content-' + id));
                                }

                                if (i + 1 >= allAppTabs.length && thisrollback) {
                                    setTimeout(function () {
                                        self.sendRollback(thisrollback, false, nodedata, formExit, unlockaction);
                                    }, 1000);
                                }
                            }
                        });
                    });
                }
                else {


                    /*
                     $( '#content-' + id ).remove();
                     $( '#buttons-' + id ).remove();
                     $( '#content-tabs-' + id ).remove();
                     $( '#meta-' + id ).remove();
                     $( '#statusbar-' + id ).remove();
                     */

                    var setVisible = opener;
                    if (!setVisible) {
                        if ($(this).next().is('li') && !$(this).prev().is('li')) {
                            setVisible = $(this).next().attr('id');
                        }
                        else if ($(this).prev().is('li') && !$(this).next().is('li')) {
                            setVisible = $(this).prev().attr('id');
                        }
                    }

                    if (!setVisible) {
                        $('#content-container,#main-content-buttons,#main-content-tabs,#content-' + id + ',#buttons-' + id + ',#content-tabs-' + id + ',#meta-' + id + ',#statusbar-' + id)
                            .css({opacity: 1, top: 0, position: 'absolute', zIndex: 100})
                            .parent()
                            .css({position: 'relative'});

                        $('#dashboard')
                            .css({opacity: '0', top: 0, position: 'absolute', zIndex: 99})
                            .show()
                            .parent()
                            .css({position: 'relative'});

                        $('#content-container,#main-content-buttons,#main-content-tabs,#tab-' + id + ',#content-' + id + ',#buttons-' + id + ',#content-tabs-' + id + ',#meta-' + id + ',#statusbar-' + id)
                            .animate(
                            {
                                opacity: '0'
                            },
                            {
                                duration: 350,
                                complete: function () {
                                    $(this).stop();

                                    Win.removeWindowFormUi(id);

                                    $('#tab-' + id + ',#content-' + id + ',#buttons-' + id + ',#content-tabs-' + id + ',#meta-' + id + ',#statusbar-' + id).remove();
                                    $('#main-content-buttons,#main-content-tabs,#content-container').css({opacity: '', position: '', zIndex: '', display: '', height: ''});
                                    $(iself).parents('li:first').remove();

                                }
                            });

                        $('#dashboard')
                            .animate(
                            {
                                opacity: 1
                            },
                            {
                                duration: 350,
                                complete: function () {

                                    $(this).stop().css({position: '', top: '', zIndex: ''});


                                    if (meta) {
                                        Core.initMetaData(false);
                                    }
                                    self.activeTabHash = null;
                                    Win.setActive(null);

                                    if (thisrollback) {

                                        $('#panel-buttons').find('li[rel]:first').trigger('click');

                                        // show filemanager tree panel & button
                                        if (app == 'fileman') {
                                            $('#panel-buttons li[rel=files]').hide();
                                        }


                                        Core.updateViewPort();

                                        setTimeout(function () {
                                            self.sendRollback(thisrollback, false, nodedata, formExit, unlockaction);
                                        }, 1000);
                                    }
                                    else {
                                        $('#panel-buttons').find('li[rel]:first').trigger('click');

                                        // show filemanager tree panel & button
                                        if (app == 'fileman') {
                                            $('#panel-buttons li[rel=files]').hide();
                                        }

                                        Core.updateViewPort();
                                    }
                                }
                            });

                    }
                    else {

                        setVisible = setVisible.replace('tab-', '').replace('content-', '');
                        if ($('#content-' + setVisible).data('windowGrid')) {
                            self.removeScrollbar(setVisible);
                        }

                        $('#content-' + id + ',#meta-' + id + ',#statusbar-' + id)
                            .css({position: 'absolute', zIndex: 99, top: 0})
                            .parent().css({position: 'relative'});

                        //$('#content-' + setVisible + ',#buttons-' + setVisible + ',#meta-' + setVisible + ',#statusbar-' + setVisible).css({position: 'relative'});

                        $('#content-' + setVisible + ',#buttons-' + setVisible + ',#content-tabs-' + setVisible + ',#meta-' + setVisible + ',#statusbar-' + setVisible).css({
                            position: 'absolute',
                            zIndex: 100,
                            top: 0,
                            opacity: '0'
                        }).parent().css({position: 'relative'});


                        $('#tab-' + id).removeClass('active');
                        $('#tab-' + setVisible).addClass('active');

                        $('#tab-' + id + ',#content-' + id + ',#buttons-' + id + ',#content-tabs-' + id + ',#meta-' + id + ',#statusbar-' + id)
                            .animate(
                            {
                                opacity: '0'
                            },
                            {
                                duration: 350,
                                complete: function () {
                                    $(this).remove();
                                }
                            });




                        $('#content-' + setVisible + ',#buttons-' + setVisible + ',#content-tabs-' + setVisible + ',#meta-' + setVisible + ',#statusbar-' + setVisible)
                            .animate(
                            {
                                opacity: 1
                            },
                            {
                                duration: 350,
                                complete: function () {

                                    if ($('#main-content-tabs').find('#content-tabs-' + setVisible).length) {
                                        $('#main-content-tabs').show();
                                    }
                                    else {
                                        $('#main-content-tabs').hide();
                                    }

                                    Win.setActive(setVisible);
                                    self.activeTabHash = setVisible;

                                    $(this).show().stop();


                                    $('#content-' + setVisible + ',#buttons-' + setVisible + ',#content-tabs-' + setVisible + ',#meta-' + setVisible + ',#statusbar-' + setVisible).css({
                                        position: '',
                                        top: '',
                                        opacity: '',
                                        zIndex: ''
                                    }).parent().css({position: ''});

                                    if (meta) {
                                        Core.initMetaData(false);
                                    }

                                    if ($('#content-' + setVisible).data('windowGrid')) {

                                        if ($('#content-' + setVisible).data('nopadding')) {
                                            $('#content-container').addClass('no-padding');
                                        }
                                        else {
                                            $('#content-container').removeClass('no-padding');
                                        }
                                    }
                                    else {
                                        if ($('#content-' + setVisible).data('nopadding')) {
                                            $('#content-container').addClass('no-padding');
                                        }
                                        else {
                                            $('#content-container').removeClass('no-padding');
                                        }
                                    }

                                    $('div.popup[opener=' + setVisible + ']').each(function () {
                                        if ($(this).attr('reopen')) {
                                            $(this).removeAttr('reopen').show();
                                        }
                                    });


                                    if (thisrollback) {

                                        Core.initMetaData($('#tab-' + setVisible).hasClass('meta'), setVisible);

                                        if ($('#content-' + setVisible).data('windowGrid')) {
                                            self.removeScrollbar(setVisible);
                                        }


                                        Core.updateViewPort();
                                        $(window).trigger('resize');

                                        $('#content-container').unmask();

                                        if (typeof resizeAce === 'function' && $('#content-' + setVisible).data('ace')) {
                                            setTimeout(function () {
                                                resizeAce('#content-' + setVisible);
                                            }, 300);
                                        }

                                        setTimeout(function () {
                                            self.sendRollback(thisrollback, false, nodedata, formExit, unlockaction);
                                        }, 1000);
                                    }
                                    else {

                                        // show filemanager tree panel & button
                                        if (app == 'fileman') {
                                            $('#panel-buttons li[rel=files]').hide();
                                            $('#panel-buttons').find('li[rel]:first').trigger('click');
                                        }

                                        if ($('#content-' + setVisible).data('windowGrid')) {
                                            self.removeScrollbar(setVisible);


                                            Core.updateViewPort();
                                            $(window).trigger('resize');

                                            $('#content-container').unmask();
                                        }
                                        else {

                                            if ($('#main-content-tabs').find('#content-tabs-' + id).length) {
                                                $('#main-content-tabs').show();
                                            }
                                            else {
                                                $('#main-content-tabs').hide();
                                            }


                                            Core.initMetaData($('#tab-' + setVisible).hasClass('meta'), setVisible);
                                            Core.updateViewPort();

                                            if (typeof resizeAce === 'function' && $('#content-' + setVisible).data('ace')) {
                                                setTimeout(function () {
                                                    resizeAce('#content-' + setVisible);
                                                }, 500);
                                            }

                                            $('#content-container').unmask();
                                        }
                                    }
                                }
                            });
                    }
                }
            }, formExit);

            return false;
        });

        $('#main-tabs ul').find('li').unbind('click.tab').bind('click.tab', function (e) {
            if ($(e.target).hasClass('remove-tab') || $(e.target).parents('.remove-tab').length) {
                return true;
            }

            e.preventDefault();

            var at = self.getActiveTab();
            if (at.length && at.attr('id') != $(this).attr('id')) {
                var act = self.getActiveTabContent();
                var events = (act.length ? act.data('events') : null);
                if (events && typeof events.onBeforeHideTabContent != 'undefined' && events.onBeforeHideTabContent.length > 0) {
                    for (var i = 0; i < events.onBeforeHideTabContent.length; ++i) {
                        if (typeof events.onBeforeHideTabContent[i] == 'function') {
                            events.onBeforeHideTabContent[i](act, at.attr('id').replace('tab-', ''));
                        }
                    }
                }
            }

            if (self.dashboard) {
                self.dashboard.hide();
            }


            $('#fm-slider', $('#main-content-mid')).hide();
            var id = $(this).attr('id').replace('tab-', '');
            self.activeTabHash = id;
            Win.setActive(id);
            self.fixActiveTabPos($(this));

            if (!$('#buttons-' + id).find('div.forceVisible').length) {
                $('#buttons-' + id).find('div.mce-tinymce-inline').hide().removeClass('forceVisible');
                $('#buttons-' + id).find('div.mce-tinymce-inline:first').show().addClass('forceVisible');
            }

            if ($('#content-' + id).data('windowGrid')) {
                self.removeScrollbar(id);
            }

            $(this).addClass('set');

            $(this).parent().find('li:not(.set)').each(function () {
                $(this).removeClass('active');
                var otherid = $(this).attr('id').replace('tab-', '');
                if (otherid != id) {

                    $('#content-' + otherid).hide();
                    $('#buttons-' + otherid).hide();
                    $('#content-tabs-' + otherid).hide();
                    $('#statusbar-' + otherid).hide();
                    $('#meta-' + otherid).hide();

                    $('div.popup[opener=' + otherid + ']').each(function () {
                        if ($(this).is(':visible')) {
                            $(this).attr('reopen', 1).hide();
                        }
                    });
                }

            });

            $(this).removeClass('set').addClass('active');

            if ($('#content-' + id).data('windowGrid')) {
                if ($('#content-' + id).data('nopadding')) {
                    $('#content-container').addClass('no-padding');
                }
                else {
                    $('#content-container').removeClass('no-padding');
                }
            }
            else {
                if ($('#content-' + id).data('nopadding')) {
                    $('#content-container').addClass('no-padding');
                }
                else {
                    $('#content-container').removeClass('no-padding');
                }
            }

            $('#content-' + id).show();
            $('#buttons-' + id).show();
            $('#content-tabs-' + id).show();
            $('#statusbar-' + id).show();
            $('#meta-' + id).show();

            if ($('#main-content-tabs').find('#content-tabs-' + id).length) {
                $('#main-content-tabs').show();
            }
            else {
                $('#main-content-tabs').hide();
            }

            $('div.popup[opener=' + id + ']').each(function () {
                if ($(this).attr('reopen')) {
                    $(this).removeAttr('reopen').show();
                }
            });


            var act = self.getActiveTabContent();
            var events = (act.length ? act.data('events') : null);
            if (events && typeof events.onShowTabContent != 'undefined' && events.onShowTabContent.length > 0) {
                for (var i = 0; i < events.onShowTabContent.length; ++i) {
                    if (typeof events.onShowTabContent[i] == 'function') {
                        events.onShowTabContent[i](act, id);
                    }
                }
            }


            if ($('#content-' + id).data('windowGrid')) {
                Core.initMetaData($(this).hasClass('meta'), id);
                Core.updateViewPort();
                $('#content-' + id).data('windowGrid').updateDataTableSize($('#content-' + id));
                //        Core.initMetaData($(this).hasClass('meta'), id);
                Core.updateViewPort();

            }
            else {
                Core.initMetaData($(this).hasClass('meta'), id);
                Core.updateViewPort();
                //	self.updateScrollbar( id );
                //    Core.updateViewPort();


                //    $( window ).trigger( 'resize' );

                if (typeof resizeAce === 'function' && $('#content-' + id).data('ace')) {
                    setTimeout(function () {
                        resizeAce('#' + Win.windowID);
                    }, 500);
                }
            }
            setTimeout(function () {
                $(window).trigger('resize');
            }, 200);

            setTimeout(function () {

                $('#content-container').unmask();
            }, 500);

        });
    },


    /**
     *
     * @param tabContainer
     * @param hash
     */
    bindContentTabEvents: function (tabContainer, hash) {
        var actTab = null;
        var self = this, tabs = tabContainer.find('li');
        var actTabContent = this.getActiveTabContent();
        var actTabContentHash = actTabContent.attr('id');

        tabs.each(function (i) {
            if ($(this).hasClass('actTab')) {
                actTab = i;
                $(this).addClass('active')
            }
            $(this).attr('data-target', '#' + actTabContentHash + ' #tc' + i).find('>span:first').attr('data-target', '#' + actTabContentHash + ' #tc' + i).attr('data-toggle', 'tab');

            $('#' + actTabContentHash).find('#tc' + i).addClass('contenttab tab-content tab-pane fade').css('display', '');
            $(this).unbind('click.tab');

        });

        if (actTab === null) {
            $(tabs).removeClass('actTab');
            tabs.eq(0).addClass('actTab active');
            if ($('#' + actTabContentHash).find('#tc0').hasClass('use-nopadding')) {
                $('#content-container-inner').parent().addClass('no-padding');
            }
            //	$( '#content-' + hash ).find( '#tc0' ).addClass( 'contenttab tab-content in' );
            $('#' + actTabContentHash).find('#tc0').addClass('tab-pane fade in active');
        }
        else {
            $(tabs).removeClass('actTab');
            tabs.eq(actTab).addClass('actTab active');

            if ($('#' + actTabContentHash).find('#tc' + actTab).hasClass('use-nopadding')) {
                $('#content-container-inner').parent().addClass('no-padding');
            }
            $('#' + actTabContentHash).find('#tc' + actTab).addClass('tab-pane fade in active');

            //	$( '#content-' + hash ).find( '#tc' + actTab ).addClass( 'contenttab tab-content in' );
        }

        Core.updateViewPort();
        var events = actTabContent.data('events');

        tabs.each(function (y) {
            if ($(this).hasClass('active')) {
                var tab = $('#' + actTabContentHash).find('#tc' + y);
                if ($('#' + actTabContentHash).find('.tinyMCE-Toolbar').length == 1) {
                    if ($('#tc' + y, $('#content-' + hash)).find('.tinymce-editor').length >= 1) {
                        $('.tinyMCE-Toolbar #disabler', $('#' + actTabContentHash)).removeClass('forceDisable');
                    }
                    else {
                        $('.tinyMCE-Toolbar #disabler', $('#' + actTabContentHash)).addClass('forceDisable');
                    }

                    $('#tc' + y, $('#' + actTabContentHash)).css('display', '');
                }
                var useNoPadding = false;
                if (tab.hasClass('use-nopadding')) {
                    $('#content-container-inner').parent().addClass('no-padding');
                    useNoPadding = true;
                }
                else {
                    $('#content-container-inner').parent().removeClass('no-padding');
                }
                //tab.show();
                if (useNoPadding) {
                    setTimeout(function () {
                        if (tab.find('.sourceEdit').length == 1) {
                            var ace = tab.find('.sourceEdit').data('ace');
                            if (ace && typeof resizeAce === 'function') {
                                resizeAce(false);
                            }
                        }
                    }, 10);
                }
            }
            else {
                $('#tc' + y, $('#' + actTabContentHash)).css('display', '');
                $('.tinyMCE-Toolbar #disabler', $('#' + actTabContentHash)).addClass('forceDisable');
                //$( '#content-' + hash ).find( '#tc' + i );
            }

            /*
             $( this ).find('>span:first').bind( 'click', function ( e ){
             e.preventDefault()
             $( this ).tab( 'show' )
             } );
             */

            /**
             * Add event to optimize tab
             */
            if (y + 1 >= tabs.length) {
                if (events && typeof events.onContentTabInited != 'undefined' && events.onContentTabInited.length > 0) {
                    for (var i = 0; i < events.onContentTabInited.length; ++i) {
                        if (typeof events.onContentTabInited[i] == 'function') {
                            events.onContentTabInited[i]($(this), hash);
                        }
                    }
                }
            }
        });

        tabs.each(function (y) {
            $(this).find('span').unbind('click').click(function (e) {
                e.preventDefault();

                var actTabContent = self.getActiveTabContent();
                var actTabContentHash = actTabContent.attr('id');
                var tabID = $(this).parent().attr('id');
                tabID = tabID.replace('tab_', '');

                var w = $('#root-window-' + hash + ':visible,#sub-window-' + hash + ':visible');
                w.find('#tc' + tabID).addClass('fade');

                $(this).tab('show', function (selectedTab, lastTab) {
                    if (lastTab) {
                        var tabID = $(lastTab).attr('id');

                        if (tabID) {
                            tabID = tabID.replace('tab_', '');

                            var actTabContent = self.getActiveTabContent();
                            var actTabContentHash = actTabContent.attr('id');
                            var hash = $(lastTab).parents('.content-tabs:first').attr('id').replace('content-tabs-', '');
                            var w = $('#root-window-' + hash + ':visible,#sub-window-' + hash + ':visible');
                            w.find('#tc' + tabID).addClass('fade');

                            var tabContent = w.find('#tc' + tabID);
                            if (tabContent.is(':visible')) {
                                $(lastTab).parent().find('.actTab:first').removeClass('actTab').addClass('defTab');
                                $(lastTab).removeClass('defTab').addClass('actTab');

                                var useNoPadding = false;
                                if (tabContent.hasClass('use-nopadding')) {
                                    $('#content-container-inner').parent().addClass('no-padding');
                                    useNoPadding = true;
                                }
                                else {
                                    $('#content-container-inner').parent().removeClass('no-padding');
                                }

                                w.find('.tab-content.active').removeClass('in').removeClass('active');//.toggleDisplay( 300 );
                                tabContent.addClass('active').addClass('in');//.toggleDisplay( 300 );

                                var wtoolbar = w.find('.tinyMCE-Toolbar');
                                if (wtoolbar.length == 1) {
                                    if (tabContent.find('.tinymce-editor').length >= 1) {
                                        wtoolbar.removeClass('forceDisable');
                                        wtoolbar.find('#disabler').removeClass('forceDisable');
                                    }
                                    else {
                                        wtoolbar.addClass('forceDisable');
                                        wtoolbar.find('#disabler').addClass('forceDisable');
                                    }
                                }
                                if (useNoPadding) {
                                    setTimeout(function () {
                                        if (tabContent.find('.sourceEdit').length == 1) {
                                            var ace = tabContent.find('.sourceEdit').data('ace');
                                            if (ace && typeof resizeAce === 'function') {
                                                resizeAce(false);
                                            }
                                        }
                                    }, 10);
                                }
                            }
                            else {
                                if (tabContent.hasClass('use-nopadding')) {
                                    $('#content-container-inner').parent().addClass('no-padding');
                                }
                                else {
                                    $('#content-container-inner').parent().removeClass('no-padding');
                                }
                            }
                            Core.updateViewPort();
                            self.updateScrollbar(hash);

                            setTimeout(function () {
                                $(window).trigger('resize');
                            }, 50);
                        }
                    }
                });
            });

        });

    },


    /**
     *
     * @param data
     * @param itemData
     * @param tab
     * @param hash
     */
    prepareSingleWindowData: function (data, itemData, tab, hash) {
        var self = this;
        var baseHash = this.getActiveTabHash(); // this.openerHash.replace( 'tab-', '' );

        if (baseHash == false) {
            console.log('Invalid active tab for Single Window');
            return;
        }

        var tab = $('#tab-' + baseHash);

        if (tab && data.pageCurrentTitle && data.pageCurrentTitle != '') {
            this.setTabLabel(tab, data.pageCurrentTitle);
        }
        else if (tab && data.applicationTitle && data.applicationTitle != '') {
            this.setTabLabel(tab, data.applicationTitle);
        }
        else {
            this.setTabLabel(tab, itemData.label);
        }

        if (data.rollback) {
            tab.attr('rollback', itemData.url);
        }

        if (data.nopadding) {
            $('#content-' + baseHash).data('nopadding', true);
            $('#content-' + baseHash).addClass('no-padding');
        }
        else {
            $('#content-' + baseHash).removeData('nopadding');
            $('#content-' + baseHash).removeClass('no-padding');
        }

        $('#statusbar-' + baseHash).find('.root-status').hide();
        $('#statusbar-' + baseHash).append($('<div class="sub-status" id="sub-status-' + hash + '">'));
        var buttonBar = $('#buttons-' + baseHash);
        buttonBar.find('div.rootbuttons,div.subbuttons').hide();

        var subbuttons = $('<div class="subbuttons" id="sub-buttons-' + hash + '"></div>');
        buttonBar.append(subbuttons);
        subbuttons.show();

        if (typeof data.toolbar != 'undefined' && data.toolbar != '') {
            subbuttons.append(data.toolbar);
        }

        if (typeof data.versioning != 'undefined' && data.versioning != '') {
            var versioning = $('<div>').addClass('content-versions');
            versioning.append(data.versioning);
            subbuttons.append(versioning);
        }

        var innerScrollContent = $('<div class="subwindow creating" id="sub-window-' + hash + '">').append(data.maincontent);
        var tmp = data;
        delete tmp.toolbar;
        delete tmp.maincontent;
        delete tmp.versioning;
        delete tmp.debugoutput;
        delete tmp.session_history;
        delete tmp.applicationMenu;
        innerScrollContent.data('win', tmp);
        innerScrollContent.show();
        $('#content-' + baseHash).find('div.subwindow,div.rootwindow').hide();
        var subWindow = $('#sub-window-' + hash);
        this.activeTabHash = baseHash;
        $('#content-' + baseHash).find('div.scroll-content:first').append(innerScrollContent);

        if (subWindow.find('.tabcontainer').length) {
            if (!$('#content-tabs-' + hash).length) {
                var contentTabContainer = $('<div class="content-tabs" style="display:block"></div>').attr('id', 'content-tabs-' + hash);
                contentTabContainer.appendTo($('#main-content-tabs'));
                $('#content-' + hash).find('.tabcontainer').appendTo($('#content-tabs-' + hash));
                $('#main-content-tabs').show();
            }
            else {
                $('#content-' + hash).find('.tabcontainer').remove();
                $('#main-content-tabs').hide();
            }

            this.bindContentTabEvents($('#content-tabs-' + hash), hash);
        }

        Win.setActive(baseHash);
        if ($(data.maincontent).filter('script').length) {
            //   console.log('Eval Scripts after window Created');
            Tools.eval($(data.maincontent));
        }

        Doc.loadTinyMce($('#sub-window-' + hash));
        createTemplateEditor('sub-window-' + hash);

        setTimeout(function () {
            innerScrollContent.removeClass('creating');

            var hasMeta = false;
            var formID = false;
            if (subWindow.find('#document-metadata').length) {
                formID = $('#content-' + hash).data('formID');
                if (formID) {
                    var htmlStr = subWindow.find('#document-metadata');
                    var to = $('#documentsettings-content').find('>div.scroll-content'), meta = $('<div id="meta-' + hash + '" style="height:101%"></div>').appendTo(to);
                    var html = $('<form>')
                        .attr('id', 'documentmetadata' + formID)
                        .data('windowID', 'content-' + hash)
                        .data('realFormID', formID)
                        .append(htmlStr);
                    $('#meta-' + hash).prepend(html);
                    tab.addClass('meta');
                    hasMeta = true;
                }
                subWindow.find('#document-metadata').remove();
            }

            //  tab.trigger('click');
            self.enableHomeButton(baseHash);

            var events = $('#sub-window-' + hash).data('events');
            if (events && typeof events.onBeforeShow != 'undefined' && events.onBeforeShow.length > 0) {
                var sub;

                for (var i = 0; i < events.onBeforeShow.length; ++i) {
                    if (typeof events.onBeforeShow[i] == 'function') {

                        sub = $('#sub-window-' + hash);

                        try {
                            events.onBeforeShow[i](sub, hash);
                        }
                        catch (e) {
                            console.error(e);
                        }

                        if (i + 1 >= events.onBeforeShow.length) {
                            self.endSingleWindow(tab, hasMeta, formID, hash, baseHash, sub);
                        }
                    }
                }
            }
            else {
                self.endSingleWindow(tab, hasMeta, formID, hash, baseHash, subWindow);
            }

        }, 5);
    },

    /**
     *
     * @param tab
     * @param hasMeta
     * @param formID
     * @param hash
     * @param baseHash
     * @param subWindow
     */
    endSingleWindow: function (tab, hasMeta, formID, hash, baseHash, subWindow) {
        var self = this;
        if ($('#content-' + baseHash).data('windowGrid') && $('#sub-window-' + hash).find('div.grid-window').length) {
            var g = $('#content-' + baseHash).data('windowGrid');
            g.createGrid(g, function () {
                document.body.style.cursor = '';
                Core.initMetaData(hasMeta, hash);
                tab.trigger('click');
                Win.prepareWindowFormUi();
                self.bindTabTooltips();
                setTimeout(function () {
                    $('#content-container').addClass('no-padding');
                    // Core.updateViewPort();
                    g.updateDataTableSize($('#content-' + baseHash));
                    $('#main-content-mid').unmask();
                    $(window).trigger('resize');
                    tab.addClass('loaded');


                    var events = $('#sub-window-' + hash).data('events');
                    if (events && typeof events.onBeforeShow != 'undefined' && events.onBeforeShow.length > 0) {
                        var sub;

                        for (var i = 0; i < events.onBeforeShow.length; ++i) {
                            if (typeof events.onBeforeShow[i] == 'function') {

                                sub = $('#sub-window-' + hash);

                                try {
                                    events.onBeforeShow[i](sub, hash);
                                }
                                catch (e) {
                                    console.error(e);
                                }
                            }
                        }
                    }

                }, 200);
            });
        }
        else {

            self.updateScrollbar(baseHash);

            document.body.style.cursor = '';
            Core.initMetaData(hasMeta, hash);

            $('#main-content-mid').unmask();
            tab.trigger('click');

            if (formID) {
                if (typeof data.contentlockaction == 'string') {
                    Form.setContentLockAction(data.contentlockaction, formID, hash);
                }

                Form.registerAutosave(formID, hash);
                Form.makeReset(formID, hash);
            }

            Win.prepareWindowFormUi();
            Core.updateViewPort();

            setTimeout(function () {
                self.updateScrollbar(baseHash);
                self.bindEasyEvents(baseHash, subWindow);
                self.bindTabTooltips();
                ToolTip.rebuildTooltips();
                ToolTip.buildTips();

                var baseWin = $('#content-' + baseHash);

                if (typeof resizeAce === 'function' && baseWin.data('ace')) {
                    setTimeout(function () {
                        resizeAce(baseWin, false);
                    }, 1000);
                }

                tab.addClass('loaded');

                var rootwin = $('#root-window-' + hash);
                if ( rootwin.length ) {
                    var events = rootwin.data('events');
                    if (events && typeof events.onBeforeShow != 'undefined' && events.onBeforeShow.length > 0) {
                        for (var i = 0; i < events.onBeforeShow.length; ++i) {
                            if (typeof events.onBeforeShow[i] == 'function') {
                                   try {
                                    events.onBeforeShow[i](rootwin, hash);
                                }
                                catch (e) {
                                    console.error(e);
                                }
                            }
                        }
                    }
                }
                else {

                    var events = baseWin.data('events');
                    if (events && typeof events.onBeforeShow != 'undefined' && events.onBeforeShow.length > 0) {
                        for (var i = 0; i < events.onBeforeShow.length; ++i) {
                            if (typeof events.onBeforeShow[i] == 'function') {
                                try {
                                    events.onBeforeShow[i](baseWin, hash);
                                }
                                catch (e) {
                                    console.error(e);
                                }
                            }
                        }
                    }
                }

                //	setTimeout( function () { self.updateScrollbar( baseHash ); }, 500);
            }, 500);
            //setTimeout( function () { $(window).trigger('resize'); }, 500);
        }
    },
    /**
     *
     * @param {type} data
     * @param {type} itemData
     * @param {type} tab
     * @param {type} hash
     * @returns {undefined}
     */
    prepareData: function (data, itemData, tab, hash) {
        var self = this;

        if (this.openerHash)
        {
            this.prepareSingleWindowData(data, itemData, tab, hash);
            this.setActiveHash = null; // reset
            return;
        }

        if (tab && data.pageCurrentTitle && data.pageCurrentTitle != '') {
            this.setTabLabel(tab, data.pageCurrentTitle);
        }
        else if (tab && data.applicationTitle && data.applicationTitle != '') {
            this.setTabLabel(tab, data.applicationTitle);
        }
        else {
            this.setTabLabel(tab, itemData.label);
        }
        var rollback = false;
        if (data.rollback) {
            rollback = itemData.url;
            tab.attr('rollback', itemData.url);
        }
        var content = $('#content-' + hash);
        if (data.nopadding) {
            content.data('nopadding', true);
        }
        else {
            content.removeData('nopadding');
        }

        $('#main-content-tabs').find('.content-tabs:visible').hide();

        var statusBar = $('<div class="status" id="statusbar-' + hash + '">');
        statusBar.append('<div class="root-status" id="root-status-' + hash + '"></div>');
        $('#main-content-statusbar').find('>div:visible').hide();
        $('#main-content-statusbar').append(statusBar);
        var contentToolbar = $('<div></div>').attr('id', 'buttons-' + hash);
        var toolbarContainer = $('<div class="rootbuttons" id="root-buttons-' + hash + '">');
        if (typeof data.toolbar != 'undefined' && data.toolbar != '') {
            toolbarContainer.append(data.toolbar);
        }
        var useVersioning = false;
        if (typeof data.versioning != 'undefined' && data.versioning != '') {
            var versioning = $('<div>').addClass('content-versions');
            versioning.append(data.versioning);
            toolbarContainer.append(versioning);
            useVersioning = true;
        }
        toolbarContainer.appendTo(contentToolbar);
        $('#main-content-buttons').find('>div:visible').hide();
        contentToolbar.appendTo($('#main-content-buttons'));
        if (data.isSingleWindow) {
            this.createSingleWindowButtons(hash, tab);
        }
        var innerScrollContent = $('<div class="rootwindow" id="root-window-' + hash + '"></div>').append(data.maincontent);
        if (useVersioning) {
            this.setDocumentVersioning(hash);
        }

        var tmp = data;
        delete tmp.toolbar;
        delete tmp.maincontent;
        delete tmp.versioning;
        delete tmp.debugoutput;
        delete tmp.session_history;
        delete tmp.applicationMenu;
        innerScrollContent.data('win', tmp);

        $('#content-container-inner').find('>div:visible').not('.pane').hide();

        content.append($('<div class="scroll-content"></div>').append(innerScrollContent)).height($('#content-container-inner').height());
        if (data.scrollable === false) {
            $('.rootwindow', content).addClass('gc');
            content.addClass('gc');
        }
        var opener;
        if ($('#tab-' + hash).attr('opener')) {
            opener = $('#tab-' + hash).attr('opener');
            content.attr('opener', opener);
        }
        if (content.find('.tabcontainer').length) {

            if (!$('#content-tabs-' + hash).length) {
                var contentTabContainer = $('<div class="content-tabs" style="display:block"></div>').attr('id', 'content-tabs-' + hash);
                contentTabContainer.appendTo($('#main-content-tabs'));
                content.find('.tabcontainer').appendTo($('#content-tabs-' + hash));
                $('#main-content-tabs').show();
            }
            else {
                content.find('.tabcontainer').remove();
                $('#main-content-tabs').hide();
            }
            this.bindContentTabEvents($('#content-tabs-' + hash), hash);
        }


        Win.setActive(hash);

        if (data.addFileSelector) {
            this.addFileSelector(hash);
        }


        if ($(innerScrollContent).filter('script').length) {
            console.log('Eval Scripts after window Created');
            Tools.eval($(innerScrollContent));
        }

        var hasMeta = false;
        var formID = content.data('formID');


        if (!content.data('windowGrid')) {


            Doc.loadTinyMce($('#content-' + hash), false, function () {

                if ($(innerScrollContent).find('textarea.sourceEdit').length) {
                    createTemplateEditor('content-' + hash, function () {
                        Core.initMetaData(hasMeta, hash);

                        tab.trigger('click');
                        Win.setActive(hash);

                        self.bindBasicEvents(hash);
                        Win.prepareWindowFormUi();
                        Core.updateViewPort();
                        self.updateScrollbar(hash);
                        self.bindEasyEvents(hash);
                        self.bindTabTooltips();

                        if (formID) {
                            Form.setContentLockAction(data.contentlockaction, formID, hash);
                            setTimeout(function () {
                                Form.registerAutosave(formID, hash);
                                Form.makeReset(formID, hash);
                            }, 500);
                        }

                        if (content.attr('isfileman')) {
                            self.triggerFilemanSizing();
                        }

                        resizeAce(content, false);


                        self.setActiveHash = null; // reset
                        tab.trigger('click');
                        var events = innerScrollContent.data('events');
                        if (events && typeof events.onAfterShow != 'undefined' && events.onAfterShow.length > 0) {
                            for (var i = 0; i < events.onAfterShow.length; ++i) {
                                if (typeof events.onAfterShow[i] == 'function') {
                                    events.onAfterShow[i](content.find('.rootwindow'), opener, rollback, hash);
                                }
                            }
                        }

                        tab.addClass('loaded');

                        setTimeout(function () {
                            document.body.style.cursor = '';
                            $('#main-content-mid').unmask();
                            if ($('#buttons-' + hash).length == 1) {
                                if (!$('#buttons-' + hash).find('div.forceVisible').length) {
                                    $('#buttons-' + hash).find('div.mce-tinymce-inline').hide().removeClass('forceVisible');
                                    $('#buttons-' + hash).find('div.mce-tinymce-inline:first').show().addClass('forceVisible');
                                }
                            }
                        }, 10);
                    });
                }
                else {
                    Core.initMetaData(hasMeta, hash);


                    tab.trigger('click');
                    Win.setActive(hash);
                    self.bindBasicEvents(hash);

                    Win.prepareWindowFormUi();
                    Core.updateViewPort();
                    self.updateScrollbar(hash);
                    self.bindEasyEvents(hash);
                    self.bindTabTooltips();
                    ToolTip.rebuildTooltips();
                    ToolTip.buildTips();

                    if (content.attr('isfileman')) {
                        self.triggerFilemanSizing();
                    }

                    if (formID) {
                        Form.setContentLockAction(data.contentlockaction, formID, hash);

                        setTimeout(function () {
                            Form.registerAutosave(formID, hash);
                            Form.makeReset(formID, hash);
                        }, 500);
                    }

                    if (typeof resizeAce === 'function' && content.data('ace')) {
                        setTimeout(function () {
                            resizeAce(content, false);
                        }, 1000);
                    }

                    self.setActiveHash = null; // reset
                    tab.trigger('click');

                    var events = innerScrollContent.data('events');
                    if (events && typeof events.onAfterShow != 'undefined' && events.onAfterShow.length > 0) {
                        for (var i = 0; i < events.onAfterShow.length; ++i) {
                            if (typeof events.onAfterShow[i] == 'function') {
                                events.onAfterShow[i](content.find('.rootwindow'), opener, rollback, hash);
                            }
                        }
                    }

                    tab.addClass('loaded');

                    setTimeout(function () {
                        document.body.style.cursor = '';
                        $('#main-content-mid').unmask();
                        if ($('#buttons-' + hash).length == 1) {
                            if (!$('#buttons-' + hash).find('div.forceVisible').length) {
                                $('#buttons-' + hash).find('div.mce-tinymce-inline').hide().removeClass('forceVisible');
                                $('#buttons-' + hash).find('div.mce-tinymce-inline:first').show().addClass('forceVisible');
                            }
                        }
                    }, 10);
                }


            });

        }
        else {

            content.data('nopadding', true);
            content.find('.scroll-content').addClass('no-scoll');
            var g = content.data('windowGrid');

            g.createGrid(g, function () {
                document.body.style.cursor = '';
                Core.initMetaData(hasMeta, hash);
                self.setActiveHash = null; // reset
                tab.trigger('click');


                Win.setActive(hash);
                Win.prepareWindowFormUi();
                self.bindTabTooltips();

                setTimeout(function () {
                    $('#content-container').addClass('no-padding');
                    // Core.updateViewPort();
                    g.updateDataTableSize($('#content-' + hash));

                    var events = innerScrollContent.data('events');
                    if (events && typeof events.onAfterShow != 'undefined' && events.onAfterShow.length > 0) {
                        for (var i = 0; i < events.onAfterShow.length; ++i) {
                            if (typeof events.onAfterShow[i] == 'function') {
                                events.onAfterShow[i](content.find('.rootwindow'), opener, rollback, hash);
                            }
                        }
                    }


                    document.body.style.cursor = '';
                    $('#main-content-mid').unmask();

                    tab.addClass('loaded');
                }, 1);
            });

        }

        return;

        setTimeout(function () {

            var hasMeta = false;
            var formID = content.data('formID');

            if (content.data('windowGrid')) {
                content.data('nopadding', true);
            }

            // var active = self.getActiveTab();
            // tab.trigger('click');
            var events = innerScrollContent.data('events');
            if (events && typeof events.onBeforeShow != 'undefined' && events.onBeforeShow.length > 0) {
                for (var i = 0; i < events.onBeforeShow.length; ++i) {
                    if (typeof events.onBeforeShow[i] == 'function') {
                        events.onBeforeShow[i](content.find('.rootwindow'), opener, rollback, hash);
                    }
                }
            }
            if (content.data('windowGrid')) {

                content.find('.scroll-content').addClass('no-scoll');


                var g = content.data('windowGrid');
                g.createGrid(g, function () {
                    document.body.style.cursor = '';
                    Core.initMetaData(hasMeta, hash);
                    self.setActiveHash = null; // reset
                    tab.trigger('click');


                    Win.setActive(hash);
                    Win.prepareWindowFormUi();
                    self.bindTabTooltips();
                    setTimeout(function () {
                        $('#content-container').addClass('no-padding');
                        // Core.updateViewPort();
                        g.updateDataTableSize($('#content-' + hash));
                        if (events && typeof events.onAfterShow != 'undefined' && events.onAfterShow.length > 0) {
                            for (var i = 0; i < events.onAfterShow.length; ++i) {
                                if (typeof events.onAfterShow[i] == 'function') {
                                    events.onAfterShow[i](content.find('.rootwindow'), opener, rollback, hash);
                                }
                            }
                        }


                        $('#main-content-mid').unmask();

                        //$( window ).trigger( 'resize' );
                        tab.addClass('loaded');
                    }, 1);
                });
            }
            else {


                Core.initMetaData(hasMeta, hash);
                if (formID) {
                    // Form.registerLiveEventsForMetadata(formID);

                    if (typeof data.contentlockaction == 'string') {
                        Form.setContentLockAction(data.contentlockaction, formID, hash);
                    }
                }

                tab.trigger('click');
                Win.setActive(hash);

                Win.prepareWindowFormUi();
                Core.updateViewPort();
                self.updateScrollbar(hash);
                self.bindEasyEvents(hash);
                self.bindTabTooltips();
                ToolTip.rebuildTooltips();
                ToolTip.buildTips();

                if (content.attr('isfileman')) {
                    self.triggerFilemanSizing();
                }

                if (formID) {
                    setTimeout(function () {
                        Form.registerAutosave(formID, hash);
                        Form.makeReset(formID, hash);
                    }, 500);
                }

                if (typeof resizeAce === 'function' && content.data('ace')) {
                    setTimeout(function () {
                        resizeAce(content, false);
                    }, 1000);
                }

                self.setActiveHash = null; // reset
                tab.trigger('click');
                if (events && typeof events.onAfterShow != 'undefined' && events.onAfterShow.length > 0) {
                    for (var i = 0; i < events.onAfterShow.length; ++i) {
                        if (typeof events.onAfterShow[i] == 'function') {
                            events.onAfterShow[i](content.find('.rootwindow'), opener, rollback, hash);
                        }
                    }
                }

                tab.addClass('loaded');

                setTimeout(function () {
                    document.body.style.cursor = '';
                    $('#main-content-mid').unmask();
                    if ($('#buttons-' + hash).length == 1) {
                        if (!$('#buttons-' + hash).find('div.forceVisible').length) {
                            $('#buttons-' + hash).find('div.mce-tinymce-inline').hide().removeClass('forceVisible');
                            $('#buttons-' + hash).find('div.mce-tinymce-inline:first').show().addClass('forceVisible');
                        }
                    }
                }, 500);

            }
        }, 20);
    },

    /**
     *
     * @param panelResize
     */
    triggerFilemanSizing: function (panelResize) {
        var win = $('#' + Win.windowID);
        if (panelResize === false) {
            win.find('#fm').resizePanels(false);
        }
        else {
            win.find('#fm').resizePanels(function () {
                //$('#fm').addResizeable();
                win.find('#fm .treelistInner,#fm .body').css({overflow: ''});
                Tools.scrollBar(win.find('#fm .treelistInner'));
                Tools.scrollBar(win.find('#fm .listview .body>:first-child'));
                Tools.scrollBar(win.find('#fm iconview.body'));

                setTimeout(function () {
                    win.find('#fm').fixTableWidth();
                }, 50);
            });
        }
    },

    /**
     *
     * @param baseHash
     * @param useContent
     */
    bindBasicEvents: function (baseHash, useContent) {
        var publishval = $('#meta-published', $('#root-window-' + baseHash)).val();
        if (publishval < 2) {
            $('#timecontrol input[name*=documentmeta]', $('#root-window-' + baseHash)).val('');
        }


        $('#meta-published', $('#root-window-' + baseHash)).bind('change.publishingstate', function () {
            if ($(this).val() < 2) {
                $('#timecontrol input[name*=documentmeta]', $('#root-window-' + baseHash)).val('');
            }
        });
    },

    /**
     *
     * @param baseHash
     * @param useContent
     */
    bindEasyEvents: function (baseHash, useContent) {
        var self = this;

        if (typeof useContent == 'undefined' || !useContent) {
            useContent = this.getActiveTabContent();
        }


        if ($('#meta-' + baseHash).length) {
            if ($('#meta-' + baseHash).find('#meta-published').length) {
                var val = parseInt($('#meta-' + baseHash).find('#meta-published').find(':selected').val());

                if (val === 2) {
                    $('#timecontrol', $('#meta-' + baseHash)).show();
                }
                else {
                    $('#timecontrol', $('#meta-' + baseHash)).hide();
                }

                $('#meta-' + baseHash).find('#meta-published').unbind('change.publish').bind('change.publish', function () {
                    var val = parseInt($('#meta-' + baseHash).find('#meta-published').find(':selected').val());
                    if (val === 2) {
                        $('#timecontrol', $('#meta-' + baseHash)).show();
                    }
                    else {
                        $('#timecontrol', $('#meta-' + baseHash)).hide();
                    }
                });

                if ($('#timecontrol', $('#meta-' + baseHash)).length) {
                    $('#timecontrol', $('#meta-' + baseHash)).find('input.cal_input').each(function () {

                        $(this).datepicker({
                            firstDay: 1,
                            changeMonth: true,
                            changeYear: true,
                            dateFormat: 'dd.mm.yy',
                            showButtonPanel: true,
                            beforeShow: function () {
                                $(this).addClass('popup-cal');
                                $(this).find('button:first').addClass('action-button');
                                $(this).find('.ui-datepicker-close').remove();
                                $(this).removeClass('ui-corner-all').find('.ui-corner-all').removeClass('ui-corner-all');
                                setTimeout(function () {
                                    $(this).find('.ui-datepicker-current').addClass('action-button');
                                }, 50);
                            }
                        });
                        /*
                         $(this ).unbind('focus.cal' ).bind('focus.cal', function(){
                         $(this).datepicker('show');
                         });

                         $(this ).unbind('blur.cal' ).bind('blur.cal', function(e){
                         if (!$( e.target ).hasClass('popup-cal') && !$( e.target ).parents('.popup-cal' ).length ) { $(this).datepicker('hide'); }
                         });
                         */
                    });
                }

            }
        }
        else {


            if (useContent && useContent.length && useContent.find('#meta-published').length) {
                var val = parseInt(useContent.find('#meta-published').find(':selected').val());

                if (val === 2) {
                    $('#timecontrol', useContent).show();
                }
                else {
                    $('#timecontrol', useContent).hide();
                }

                useContent.find('#meta-published').unbind('change.publish').bind('change.publish', function () {
                    var val = parseInt(useContent.find('#meta-published').find(':selected').val());
                    if (val === 2) {
                        $('#timecontrol', useContent).show();
                    }
                    else {
                        $('#timecontrol', useContent).hide();
                    }
                });

                if ($('#timecontrol', useContent).length) {
                    $('#timecontrol', useContent).find('input.cal_input').each(function () {

                        $(this).datepicker({
                            firstDay: 1,
                            changeMonth: true,
                            changeYear: true,
                            dateFormat: 'dd.mm.yy',
                            showButtonPanel: true,
                            beforeShow: function () {
                                $(this).addClass('popup-cal');
                                $(this).find('button:first').addClass('action-button');
                                $(this).find('.ui-datepicker-close').remove();
                                $(this).removeClass('ui-corner-all').find('.ui-corner-all').removeClass('ui-corner-all');
                                setTimeout(function () {
                                    $(this).find('.ui-datepicker-current').addClass('action-button');
                                }, 50);
                            }
                        });
                    });
                }
            }
        }

        $('#content-' + baseHash).find('.doTab').unbind('click.newtab').bind('click.newtab', function (e) {
            e.preventDefault();

            $(this).find('.fa').addClass('fa-spin');
            var iself = this;

            setTimeout(function () {
                openTab({
                    obj: iself,
                    url: $(iself).attr('href'),
                    label: $(iself).text().trim()
                });
            }, 10);

            return false;
        });

        $('#content-' + baseHash).find('.doPopup').unbind('click.newpopup').bind('click.newpopup', function (e) {
            e.preventDefault();
            Tools.popup($(this).attr('href'), $(this).text().trim());
            return false;
        });

        $('#content-' + baseHash).find('.delconfirm').unbind('click.delconfirm').bind('click.delconfirm', function (e) {
            e.preventDefault();

            var href = $(this).attr('href');
            jConfirm('Möchtest du diesen Inhalt wirklich löschen?', 'Bestätigung...', function (r) {
                if (r) {
                    $.get(Tools.prepareAjaxUrl(href + '&send=1'), {}, function (data) {
                        if (Tools.responseIsOk(data)) {
                            if (data.msg) {
                                Notifier.display('info', data.msg);
                            }
                            else {
                                Notifier.display('info', 'Daten wurden erfolgreich gelöscht...');
                            }
                        }
                        else {
                            jAlert(data.msg);
                        }
                    }, 'json');
                }
            });
        });

        $('#content-' + baseHash).find('a[publishurl]').unbind('click.publish').bind('click.publish', function (e) {
            e.preventDefault();
            if ($(this).attr('rel') && $(this).attr('publishurl')) {
                self.changePublish($(this).attr('rel'), $(this).attr('publishurl'));
            }
        });

        if (useContent) {
            Core.BootstrapInit(useContent, $('#meta-' + baseHash), baseHash);
        }
        else {
            Core.BootstrapInit($('#root-window-' + baseHash), $('#meta-' + baseHash), baseHash);
        }
    },


    /**
     *
     * @param id
     * @param url
     */
    changePublish: function (id, url) {
        var actTabContentHash = Core.getContent().attr('id');
        var spinner = $('#' + actTabContentHash + ' #' + id);
        if (spinner.hasClass('fa-spin')) {
            return;
        }

        spinner.addClass('fa-spin');

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            async: true,
            cache: false,
            success: function (data) {
                spinner.removeClass('fa-spin');

                if (Tools.responseIsOk(data)) {

                    if (data.msg && data.msg == '0') {
                        spinner.removeClass('published').addClass('unpublished').parents('a:first').find('span.pub-label').text(cmslang.unpublished);
                        return false;
                    }

                    if (data.msg && data.msg == '1') {
                        spinner.removeClass('unpublished').addClass('published').parents('a:first').find('span.pub-label').text(cmslang.published);
                        return false;
                    }

                    if (spinner.hasClass('published')) {
                        spinner.removeClass('published').addClass('unpublished').parents('a:first').find('span.pub-label').text(cmslang.unpublished);
                    }
                    else {
                        spinner.removeClass('unpublished').addClass('published').parents('a:first').find('span.pub-label').text(cmslang.published);
                    }

                    if (typeof data.msg != "undefined") {
                        Notifier.info(data.msg);
                    }
                }
                else {
                    if (typeof data.msg != "undefined") {
                        Notifier.error(data.msg);
                    }
                }
            }
        });

    },

    /**
     *
     * @param buttonshash
     */
    setDocumentVersioning: function (buttonshash) {
        var self = this, bar;
        if ($('#root-buttons-' + buttonshash).is(':visible')) {
            bar = $('#root-buttons-' + buttonshash);
        }
        else if ($('#sub-buttons-' + buttonshash).is(':visible')) {
            bar = $('#sub-buttons-' + buttonshash);
        }
        else {
            return;
        }
        bar.find('#VersioningForm').each(function () {
            $(this).find('select').unbind();
            var currentVersion = $(this).find('select').val();
            $(this).find('select').unbind('change.versioning').bind('change.versioning', function (e) {
                $(this).parents('form:first').find('#changeVersion').enableButton();
            });

            $(this).find('button[name=changeVersion]').unbind('click.versioning').bind('click.versioning', function (e) {
                e.preventDefault();

                var params = $(this).parents('form:first').serialize();
                $.post('admin.php', params, function (data) {
                    if (Tools.responseIsOk(data)) {
                    }
                    else {
                        if (data.msg) {
                            Notifier.display('error', data.msg);
                        }
                    }
                });
            });


            bar.find('#diffVersion').unbind('click.versioning').bind('click.versioning', function (e) {
                e.preventDefault();

                var selectedVersion = $(this).parents('form:first').find('select').val();
                var modul = $(this).parents('form:first').find('input[name="adm"]').val();
                var id = $(this).parents('form:first').find('input[name="id"]').val();
                if (id) {
                    $.post('admin.php', {adm: 'dashboard', action: 'diff', modul: modul, id: id, sourceversion: currentVersion, targetversion: selectedVersion}, function (data) {
                        if (Tools.responseIsOk(data)) {
                            Tools.createPopup(data.maincontent, {
                                minWidth: 640,
                                WindowTitle: data.pageCurrentTitle ? data.pageCurrentTitle : 'Merge',
                                WindowMaximize: true,
                                WindowMinimize: true,
                                WindowResizeable: true,
                                WindowToolbar: data.toolbar,
                                app: modul,
                                onAfterOpen: function (popup) {
                                    Win.prepareWindowFormUi();
                                }
                            });
                        }
                        else {
                            if (data.msg) {
                                Notifier.display('error', data.msg);
                            }
                        }
                    });
                }
            });
            bar.find('#changeVersion').disableButton();
        });
    },

    /**
     *
     * @param hash
     * @returns {Number|window.jQuery.length|*|jQuery.length|jQuery}
     */
    tabExists: function (hash) {
        return $('#content-container-inner').find('#content-' + hash).length;
    },

    /**
     *
     * @param show
     */
    toggleFileSelectorPanel: function (show) {
        var self = this;
        var hash = this.getActiveTabHash();
        if (hash) {
            var container = $('#content-' + hash);
            if (container.length && $('#fm-slider', container).length) {
                if ((!show && $('#fm-slider', container).is(':visible')) || $('#fm-slider', container).is(':visible')) {
                    $('#fm-slider', container).hide(); //.find('div:first').empty();
                    $('#content-' + hash).find('>:first-child').css({marginRight: ''});
                    $('#content-' + hash).get(0).filemanVisible = false;
                }
                else {
                    var splitWidth = container.width() / 2;
                    $('#content-' + hash).find('>:first-child').css({marginRight: splitWidth});
                    $('#fm-slider', container).width(splitWidth).show();
                    var $fm = container.find('#fm');
                    $fm.find('.treelistInner,.body').css({overflow: ''});
                    $fm.resizePanels(false);
                    setTimeout(function () {
                        $fm.resizePanels(function () {
                            $fm.find('.treelistInner,.body').css({overflow: ''});
                            Tools.scrollBar($fm.find('.treelistInner'));
                            Tools.scrollBar($fm.find('.listview .body>:first-child'));
                            Tools.scrollBar($fm.find('iconview.body'));
                            setTimeout(function () {
                                $fm.fixTableWidth();
                            }, 50);
                        });
                    }, 10);


                    $('#content-' + hash).get(0).filemanVisible = true;
                }
            }
        }
    },

    /**
     *
     * @param hash
     */
    addFileSelector: function (hash) {
        var self = this, container = $('#content-' + hash);
        if (container.attr('fm')) {
            return;
        }
        container.attr('fm', true);


        var fmcontainer = $('<div id="fm-slider" class="inline-window-slider"><div></div></div>');
        var width = container.width();
        var height = container.height();


        var wrapper = $('<div class="content-wrap" style="width:auto;height:100%;position: relative">');
        container.children().appendTo(wrapper);
        wrapper.appendTo(container);
        container.append(fmcontainer);


        //container.children().wrap( $( '<div class="content-wrap" style="width:auto;height:100%;position: relative">' ) );


        if (container.find('#fm-slider>div>div:first').length == 0) {
            var maxWidth = ($('#content-container').width() / 2);
            fmcontainer.width(maxWidth).hide();

            $.ajax({
                url: 'admin.php?adm=fileman&mode=fileselector',
                type: 'GET',
                dataType: 'json',
                timeout: 8000,
                data: {},
                async: false,
                global: false,
                success: function (data) {
                    if (Tools.responseIsOk(data)) {
                        fmcontainer.attr('rel', hash);
                        fmcontainer.find('div:first').append(data.maincontent);

                        container.get(0).filemanVisible = false;

                        fmcontainer.find('#fm').Filemanager({
                            isInlineFileman: true,
                            connectorUrl: 'admin.php?adm=fileman',
                            //      mode: '{$fm.mode}',
                            dirSep: '/',
                            toolbarContainer: fmcontainer.find('#fm-toolbar'),
                            externalScrollbarContainer: '.pane',
                            scrollTo: function (c, toObject) {
                                if (c === 'tree') {
                                    Tools.scrollBar(fmcontainer.find('#fm .treelistInner'), toObject);
                                }

                            },
                            externalScrollbarDestroy: function () {

                            },
                            externalScrollbarCreate: function () {
                                var fm = fmcontainer.find('#fm');
                                fm.find('.treelistInner,.body').css({overflow: ''});
                                Tools.scrollBar(fm.find('.treelistInner'));
                                Tools.scrollBar(fm.find('.listview .body>:first-child'));
                                Tools.scrollBar(fm.find('.iconview.body'));
                                setTimeout(function () {
                                    //if ($('#fm .foldercontentInner .body', _win).hasClass('jspScrollable'))
                                    // {
                                    fm.fixTableWidth();
                                    // }
                                }, 80);
                            },
                            onResizeStart: function () {
                                var fm = fmcontainer.find('#fm');
                                fm.find('.treelistInner,.body').css({width: '', overflow: ''});
                            },
                            onResizeStop: function () {
                                var fm = fmcontainer.find('#fm');
                                fm.resizePanels(function () {
                                    fm.find('.treelistInner,.body').css({overflow: ''});
                                    Tools.scrollBar(fm.find('.treelistInner'));
                                    Tools.scrollBar(fm.find('.listview .body>:first-child'));
                                    Tools.scrollBar(fm.find('.iconview.body'));
                                    setTimeout(function () {
                                        fm.fixTableWidth();
                                    }, 80);
                                });
                            },
                            onBeforeLoad: function () {
                            },
                            onAfterLoad: function () {
                                var fm = fmcontainer.find('#fm');
                                fm.find('.treelistInner,.body').css({overflow: ''});

                                Tools.scrollBar(fm.find('.treelistInner'));
                                Tools.scrollBar(fm.find('.listview .body>:first-child'));
                                Tools.scrollBar(fm.find('.iconview.body'));

                                setTimeout(function () {
                                    fm.resizePanels(function () {
                                        fm.find('.treelistInner,.body').css({overflow: ''});
                                        Tools.scrollBar(fm.find('.treelistInner'));
                                        Tools.scrollBar(fm.find('.listview .body>:first-child'));
                                        Tools.scrollBar(fm.find('.iconview.body'));
                                        setTimeout(function () {
                                            fm.fixTableWidth();
                                        }, 80);
                                    });
                                }, 50);
                            }
                        });

                        fmcontainer.hide();
                        setTimeout(function () {
                            fmcontainer.resizable({
                                handles: 'w',
                                minWidth: 450,
                                maxWidth: maxWidth,
                                autoHide: false,
                                start: function (e, ui) {
                                    fmcontainer.find('#fm div.header th,#fm div.body tr:first td').attr('style', '');
                                },
                                resize: function (e, ui) {
                                    var w = $('#content-' + hash).width() - ui.size.width;
                                    $('#content-' + hash).find('div.content-wrap:first').css({marginRight: ui.size.width});
                                    $(this).css({left: ''});
                                    fmcontainer.find('#fm').resizePanels(false);
                                },
                                stop: function (e, ui) {
                                    var fm = fmcontainer.find('#fm');
                                    var w = $('#content-' + hash).width() - ui.size.width;
                                    $('#content-' + hash).find('div.content-wrap:first').css({marginRight: ui.size.width});

                                    setTimeout(function () {
                                        fm.resizePanels(function () {
                                            fm.find('.treelistInner,.body').css({overflow: ''});
                                            Tools.scrollBar(fm.find('.treelistInner'));
                                            Tools.scrollBar(fm.find('.listview .body>:first-child'));
                                            Tools.scrollBar(fm.find('iconview.body'));
                                            setTimeout(function () {
                                                fm.fixTableWidth();
                                            }, 80);
                                        });
                                    }, 10);
                                }
                            });
                            // register cancel button
                            fmcontainer.find('#cancel-fm').on('click', function (e) {
                                self.toggleFileSelectorPanel(false);
                            });
                        }, 100);
                    }
                }
            });
        }
        else {
            self.toggleFileSelectorPanel(true);
        }
    },

    /**
     *
     */
    bindTabTooltips: function () {
        var self = this;
        $('#main-tabs ul').find('span.label').each(function () {
            $(this).unbind('mouseleave.bubble').bind('mouseleave.bubble', function (e) {
                e.preventDefault();

                $('div.dockBubbleContent').remove();
            });

            $(this).unbind('mouseover.bubble').bind('mouseover.bubble', function (e) {
                e.preventDefault();

                var li = $(this).parent();
                var appid = li.attr('app');
                var label = li.attr('alt');
                e.preventDefault();
                if (label && $('#dockBubbleContent_' + appid).length == 0) {
                    var bubbleContent = $('<div>').attr({
                        'class': 'dockBubbleContent',
                        'id': 'dockBubbleContent_' + appid
                    }).html('<center></center>');
                    var bubble = $('<div>').addClass('dockBubble').html(label);
                    var bulleArrow = $('<span>').addClass('bubbleArrow').css('display', 'block');
                    bubbleContent.children('center').append(bubble);
                    bubbleContent.children('center').prepend(bulleArrow);
                    var icnsOffsetTop, icnsOffsetLeft, bulleLeft, bulleTop;
                    /*
                     if ($('#fullscreenContainer').length) {
                     $('#fullscreenContainer').append(bubbleContent);
                     }
                     else {
                     $('body').append(bubbleContent);
                     }*/
                    $('body').append(bubbleContent);
                    var bulleWidth = $('#dockBubbleContent_' + appid).outerWidth(true);
                    var bulleHeight = $('#dockBubbleContent_' + appid).outerHeight(true);
                    var windowWidth = $(window).width(), liWidth = li.width(), liHeight = li.height(), offset = li.offset();
                    var pos = 'center';
                    if (self.options.tab_tooltip_position == 'center') {
                        icnsOffsetTop = offset.top;
                        icnsOffsetLeft = offset.left;
                        bulleLeft = icnsOffsetLeft + (liWidth / 2 - (bulleWidth / 2));
                        bulleTop = icnsOffsetTop + bulleHeight + 3;
                        if (bulleLeft + bulleWidth > windowWidth) {
                            pos = 'left';
                        }
                    }
                    if (self.options.tab_tooltip_position == 'right' || pos == 'right') {
                        icnsOffsetTop = offset.top;
                        icnsOffsetLeft = offset.left;
                        bulleLeft = icnsOffsetLeft + liWidth - 15;
                        bulleTop = icnsOffsetTop + (liHeight / 2) - (bulleHeight / 2);
                        pos = 'right';
                    }
                    if (self.options.tab_tooltip_position == 'left' || pos == 'left') {
                        icnsOffsetTop = offset.top;
                        icnsOffsetLeft = offset.left;
                        bulleLeft = icnsOffsetLeft - bulleWidth + 15;
                        bulleTop = icnsOffsetTop + (liHeight / 2) - (bulleHeight / 2);
                        pos = 'left';
                    }
                    bubbleContent.addClass(pos).css({
                        'top': bulleTop,
                        'left': bulleLeft
                    }).show();
                }
            });
        });
    }
};