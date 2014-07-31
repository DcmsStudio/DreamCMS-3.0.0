if (typeof jQuery != 'undefined') {

    jQuery.fx.interval = 12;
    jQuery.event.props.push("dataTransfer");
    $.gsap.enabled(true);

    Modernizr.addTest('pointerevents', function(){
        var element = document.createElement('x');
        element.style.cssText = 'pointer-events:auto';
        return element.style.pointerEvents === 'auto';
    });
}

// Internationalization strings
var dateFormat = {};
dateFormat.i18n = {};
dateFormat.i18n.en = {
    dayNames: [
        ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]
    ],
    monthNames: [
        ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"]
    ]
};

dateFormat.i18n.de = {
    dayNames: [
        ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
        ["Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"]
    ],
    monthNames: [
        ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
        ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"]
    ]
};


var Application = {
    gridT: null,
    /**
     *
     * @param {type} url
     * @returns {undefined}
     */
    setActiveUrl: function (url) {
        this.activeUrl = url;
    },
    setMenuUrlForID: function (url) {

    },
    enableMenuitems: function () {

    },
    hideMenuitems: function () {

    },
    sendRollback: function (event, wm, callback) {
        if (wm.win.attr('rollback')) {
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
                success: function (data) {
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
    },
    Grid: function (elementID, options) {
        var self = this, windowEl = $('#' + elementID).parents('div.core-tab-content:first'), element = windowEl.find('#' + elementID);

        if (this.runnerGrid || element.length == 0) {
            this.gridT = setTimeout(function () {
                self.Grid(elementID, options);
            }, 50);
        }
        else {
            windowEl.parent().parent().addClass('no-padding');
            clearTimeout(this.gridT);

            if (!windowEl.data('windowGrid')) {
                this.runnerGrid = true;
                var g = new DataGrid();

                //
                options.convertOptionsColumn = true;

                g.create(element, windowEl, options);
                this.runnerGrid = false;
                windowEl.data('windowGrid', g);

            }
        }
    }

};



var gAllImages = [];
$.fn.image = function (src, f) {
    return this.each(function () {
        var i = new Image();
        i.src = src[0];
        i.onload = function () {
            f(src);
        }

        $(i).on('complete', function () {
            f(src);
        });

        this.appendChild(i);
    });
};

var Core = {
    loaded: false,
    userData: null,
    basicCMSData: null,
    SessionID: null,
    isSeemode: false,
    settings: {},
    dashboard: null,
    eventregistry: {},
    defaults: {
    },

    WindowTabs: [],
    WindowRollbacks: [],

    /**
     *
     * @param string key
     * @param mixed value
     * @returns Desktop
     */
    set: function (key, value) {
        this.settings[key] = value;
        return this;
    },
    /**
     *
     * @param string key
     * @returns mixed
     */
    get: function (key) {
        return (typeof this.settings[key] !== undefined ? this.settings[key] : null);
    },


    /**
     * Set/get data into/from localStorage
     *
     * @param String key
     * @param String|void value
     * @return String
     */
    localStorage: function (key, val) {
        var s = window.localStorage;

        key = 'admcore-' + key;

        if (val === null) {
            //    console.log('remove', key)
            return s.removeItem(key);
        }

        if (val !== void(0)) {

            if (typeof val == 'object') {
                val = 'json:' + JSON.stringify(val);
            }

            try {
                s.setItem(key, val);
            } catch (e) {
                s.clear();
                s.setItem(key, val);
            }
        }

        var str = s.getItem(key);

        if (typeof str == 'string' && str.match(/^json:.*/g)) {
            str = str.replace(/^json:(.*)/g, '$1');
            str = JSON.parse(str);
        }

        return str;


    },

    /**
     * Get/set cookie
     *
     * @param String cookie name
     * @param String|void cookie value
     * @return String
     */
    cookie: function (name, value) {
        var d, o, c, i;

        name = 'admcore-' + name;

        if (value === void(0)) {
            if (document.cookie && document.cookie != '') {
                c = document.cookie.split(';');
                name += '=';
                for (i = 0; i < c.length; i++) {
                    c[i] = $.trim(c[i]);
                    if (c[i].substring(0, name.length) == name) {
                        return decodeURIComponent(c[i].substring(name.length));
                    }
                }
            }
            return '';
        }


        o = $.extend({}, this.options.cookie);
        if (value === null) {
            value = '';
            o.expires = -1;
        }

        if (typeof value == 'object') {
            value = 'json:' + value.toJSONString();
        }


        if (typeof (o.expires) == 'number') {
            d = new Date();
            d.setTime(d.getTime() + (o.expires * 86400000));
            o.expires = d;
        }
        document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + o.expires.toUTCString() + (o.path ? '; path=' + o.path : '') + (o.domain ? '; domain=' + o.domain : '') + (o.secure ? '; secure' : '');
        return value;
    },

    storage: function (k, v) {

        if (this.allowLocalStorage()) {
            return (typeof v != 'undefined' ? this.localStorage(k, v) : this.localStorage(k) );
        }
        else {
            return (typeof v != 'undefined' ? this.cookie(k, v) : this.cookie(k) );
        }

    },

    allowLocalStorage: function () {
        return 'localStorage' in window && window['localStorage'] !== null ? true : false;
    },


    Mac: false,
    Windows: false,
    isWindowSkin: false,

    init: function (options) {
        var self = this;



        this.Mac = /^(Mac|iPhone|iPad|iOS)/i.test(navigator.platform);
        this.Windows = /^Win/i.test(navigator.platform);
        this.settings = $.extend(this.defaults, options);

        $('body').addClass('boot').removeClass('auth');
        $('#fullscreenContainer,#auth').removeClass('in');

        if (this.settings.isSeemode) {
            this.settings.EnableBootScreen = false;
        }

        $('#auth-form-container').css({
            top: $(window).height() / 2 - $('#auth-form-container').height() / 2,
            left: $(window).width() / 2 - $('#auth-form-container').width() / 2
        });

        $(window).resize(function () {

            $('#auth-form-container').css({
                top: $(window).height() / 2 - $('#auth-form-container').height() / 2,
                left: $(window).width() / 2 - $('#auth-form-container').width() / 2
            });

        });

        Config.init(options);


        $(document).ajaxStart(function () {
            if (typeof Pace != 'undefined') {
                //Pace.start();
            }
        });

        $(document).ajaxComplete(function (event, xhr, settings) {

            if (typeof xhr.responseJSON == 'object') {
                var data = xhr.responseJSON;
                if (typeof data.csrfToken === 'string') {
                    Config.set('token', data.csrfToken);
                    $('#content-container input[name=token],#main-content-buttons input[name=token]').val(data.csrfToken);
                }

                if (typeof data.debugoutput === 'string') {
                    DesktopConsole.setDebug(data.debugoutput);
                }
            }
            if (typeof self.eventregistry['ajaxComplete'] != 'undefined' && self.eventregistry['ajaxComplete'].length) {
                for (var i = 0; i < self.eventregistry['ajaxComplete'].length; ++i) {
                    if (typeof self.eventregistry['ajaxComplete'][i] == 'function') {
                        self.eventregistry['ajaxComplete'][i](event, xhr, settings);
                    }
                }
            }
            // if (typeof Pace != 'undefined') { Pace.stop(); }
        });

        $(document).ajaxSuccess(function (event, xhr, settings) {
            // console.log(xhr);

            if (typeof xhr.responseJSON == 'object') {
                var data = xhr.responseJSON;

                if (typeof data.csrfToken === 'string') {
                    Config.set('token', data.csrfToken);
                    $('#content-container input[name=token],#main-content-buttons input[name=token]').val(data.csrfToken);
                }

                if (typeof data.debugoutput === 'string') {
                    DesktopConsole.setDebug(data.debugoutput);
                }
            }

            if (typeof self.eventregistry['ajaxSuccess'] != 'undefined' && self.eventregistry['ajaxSuccess'].length) {
                for (var i = 0; i < self.eventregistry['ajaxSuccess'].length; ++i) {
                    if (typeof self.eventregistry['ajaxSuccess'][i] == 'function') {
                        self.eventregistry['ajaxSuccess'][i](event, xhr, settings);
                    }
                }
            }

        });

        this.run(options);
    },

    startPreload: function (preloadImages, callback) {

        var complete = function () {
            var preload = $("#preload");
            preload.find('>div:first').show();
            var prespan = $(preload).find("span.a");
            var descript = preload.find('div.description').empty().hide();
            var preall = preloadImages.length;
            preload.find('span.b').text(preall);
            var limg = $("#loadedimages");

            pgeladen = 0;

            for (var x = 0; x < preloadImages.length; x++) {
                var src = preloadImages[x].src.split('|');
                $(limg).image(src, function (src) {

                    // descript.text(src[1]);
                    if (pgeladen < preall) {
                        pgeladen++;
                        $(prespan).html(pgeladen);
                    }

                    if (pgeladen >= preall) {
                        $('body').removeClass('fail').removeClass('boot').addClass('auth');
                        preload.find('>div:first').hide();
                        descript.text('Generiere GUI...').show();
                        setTimeout(function () {
                            callback();
                        }, 50);
                    }
                });
            }
        };

        $('#boot-maskmsg,#boot-mask').show().addClass('in');

        $.support.transition && $('#boot-mask').hasClass('fade') ?
            $('#boot-mask').one($.support.transition.end, complete)
                .emulateTransitionEnd(150) :
            complete();

    },


    run: function (options, fromAuth) {
        var self = this;


        this.addShortcutHelp('Meta+Alt+H', 'Display this help');
        this.addShortcutHelp('Ctrl+Alt+X', 'Close the active Tab');
        this.addShortcutHelp('Ctrl+Alt+P', 'Toggle Sidepanel');
        this.addShortcutHelp('Ctrl+Alt+C', 'Toggle Console');
        this.addShortcutHelp('Ctrl+Alt+D', 'Switch to Dashboard');

        this.addShortcutHelp('Ctrl+Alt+F12', 'Toggle Fullscreen');

        // register basic shortcuts
        $(document).bind('keydown.core', function (e) {
            if (window.focusedAceEdit !== null) {
                return;
            }

            var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
            if (e.altKey && e.ctrlKey && char == 'h' /* Tab */) {
                // display ShortcutHelp
                self.displayShortcuts();
                e.preventDefault();
            }

            if (e.altKey && e.keyCode == 123 /* F12 */) {
                $('#switch-fullscreen').trigger('click');
                e.preventDefault();
            }

            if (e.altKey && e.ctrlKey && char == 'd') {
                $('#toggle-dashboard').trigger('click');
            }

            if (e.altKey && e.ctrlKey && char == 'c') {
                $('#console').trigger('click');
            }

            if (e.altKey && e.ctrlKey && char == 'p') {
                $('#toggle-pane-content').trigger('click');
            }

            if (e.altKey && e.ctrlKey && char == 'x') {
                // close document
                var hash = self.getActiveTabHash();

                if (hash) {
                    Core.Tabs.closeActiveTab();
                    e.preventDefault();
                }
            }

        });


        if (fromAuth) {
            this.prepareConfig((options || self.settings || {}));
            if (typeof self.basicCMSData.userdata != 'undefined' && typeof self.basicCMSData.userdata.userid != 'undefined' && self.basicCMSData.userdata.userid > 0) {
                self.settings = $.extend({}, self.settings, self.basicCMSData || {});
                Config.set('UserName', self.basicCMSData.userdata.username);
                self.animateToDesktop(options);

                return;
            }
        }


        this.loadBasicConfig(function (data) {

            if (typeof data != 'undefined' && typeof data.success != 'undefined' && data.success != true && typeof data.msg == 'string' && data.msg != '') {
                $('body').addClass('fail');
                var msg = data.msg;

                var complete = function () {
                    $('.custom-msg', $('#fail')).append(msg).show();
                    $('#fail,#custom-error').show();
                };

                $('#auth,#fullscreenContainer').removeClass('in');
                $.support.transition && $('#auth').hasClass('fade') ?
                    $('#auth').one($.support.transition.end, complete)
                        .emulateTransitionEnd(150) :
                    complete();

                return;
            }

            self.prepareConfig(data, (options || self.settings || {}));


            if (typeof data.bootImages != 'undefined') {

                //self.startPreload(data.bootImages, function () {
                if (typeof self.basicCMSData.userdata == 'undefined' || typeof self.basicCMSData.userdata.userid == 'undefined') {

                    setTimeout(function () {
                        self.animateToLogin();
                    }, 1000);

                }
                else if (typeof self.basicCMSData.userdata != 'undefined' && typeof self.basicCMSData.userdata.userid != 'undefined' && self.basicCMSData.userdata.userid > 0) {
                    self.settings = $.extend({}, self.settings, self.basicCMSData || {});
                    Config.set('UserName', self.basicCMSData.userdata.username);

                    self.animateToDesktop(data);

                }
                ///  });
            }
            else {
                if (typeof self.basicCMSData.userdata == 'undefined' || typeof self.basicCMSData.userdata.userid == 'undefined') {
                    setTimeout(function () {
                        self.animateToLogin();
                    }, 1000);

                }
                else if (typeof self.basicCMSData.userdata != 'undefined' && typeof self.basicCMSData.userdata.userid != 'undefined' && self.basicCMSData.userdata.userid > 0) {
                    self.settings = $.extend({}, self.settings, self.basicCMSData || {});
                    Config.set('UserName', self.basicCMSData.userdata.username);

                    self.animateToDesktop(data);

                }
            }

        });
    },
    prepareConfig: function (data, options) {
        if (typeof data.sid == 'string') {
            this.SessionID = data.sid;
        }
        else {
            this.SessionID = typeof options.sid == 'string' && options.sid != '' ? options.sid : null;
        }


        this.basicCMSData = data;
        Config.set(this.basicCMSData.sysconfig);

        if (typeof options != 'undefined' && typeof options.token != 'undefined') {
            Config.set('token', options.token || null);
        }

        var baseUrl = Config.get('portalurl', '');
        Config.set('SSL_portalurl', baseUrl.replace(/https?:\/\//, 'https://'));
        Config.set('SSL_MODE', false);

        if (document.location.href.match(/https:\/\//i)) {
            Config.set('SSL_MODE', true);
        }

        this.settings = $.extend({}, this.settings, (this.basicCMSData || {}));

        delete(this.settings.sysconfig);
        delete(this.basicCMSData.sysconfig);

        Config.set('tinymceFileBrowserUrl', 'Vendor/tinymce/pdw_file_browser/index.php?editor=tinymce&sid=' + this.SessionID + '&filter=');

        if (data.tinymce) {
            Doc.prepareTinyMceSetup(data.tinymce);
            delete(data.tinymce);
        }

        if (Config.get('autosave')) {
            Form.defaults.autosave = Config.get('autosave');
        }
    },
    updateContentHeight: function () {
        var baseHeight = $('#main-content').height();
        var statusBarHeight = parseInt($('#main-content-statusbar').outerHeight(true), 10);
        var diffHeight = parseInt($('#main-content-header').outerHeight(true), 10);
        var innerSpace = (parseInt($('#content-container').css('padding-top'), 10) + parseInt($('#content-container').css('padding-bottom'), 10));
        $('#content-container,#main-content-inner > .core-tab-content').height(baseHeight - statusBarHeight - diffHeight - innerSpace);
    },
    updateViewPort: function () {
        var consoleHeight = 0;
        if ($('#gui-console').is(':visible')) {
            consoleHeight = parseInt($('#gui-console').outerHeight(true), 10);
        }

        $('#main-content-tabs').show();

        var headerHeight = parseInt($('#header').outerHeight(true), 10);
        var statusBarHeight = parseInt($('#main-content-statusbar').outerHeight(true), 10);
        var innerSpace = (parseInt($('#content-container').css('padding-top'), 10) + parseInt($('#content-container').css('padding-bottom'), 10));
        var mainHeaderHeight = $('#main-header').outerHeight(true);
        var diffHeight = parseInt($('#main-content-header').height(), 10);
        var docHeight = $('html,body').height();


        if (this.panelIsVisible) {
            $('#panel,#main-content,#main-content-inner').height(docHeight - consoleHeight - headerHeight - mainHeaderHeight);
        }
        else {
            $('#main-content,#main-content-inner').height(docHeight - consoleHeight - headerHeight - mainHeaderHeight);
        }


        $('#main-content-mid').height(docHeight - headerHeight - consoleHeight - mainHeaderHeight - diffHeight - statusBarHeight);

        //$('#dashboard,#dashboard div.widget-column').height(docHeight - headerHeight - consoleHeight - mainHeaderHeight - diffHeight);
        $('#dashboard').height(docHeight - headerHeight - consoleHeight - mainHeaderHeight - diffHeight);

        $('#content-container,#main-content-inner > .core-tab-content')
            .height(docHeight - headerHeight - mainHeaderHeight - consoleHeight - statusBarHeight - diffHeight - innerSpace);

        //  $('.core-tab-content > div.in').height(docHeight - headerHeight - mainHeaderHeight - statusBarHeight - diffHeight - innerSpace - 4);

        $('#content-container .core-tab-content').height($('#content-container').height());
        $('#panel-content > div').height(docHeight - headerHeight - consoleHeight - mainHeaderHeight);
        $('#panel-content > div > div.panel-content').height(docHeight - consoleHeight - headerHeight - mainHeaderHeight - 10 - $('#panel-content > div > div.header:first').outerHeight());

    },
    initWindowResizeEvents: function () {
        var self = this, ri;

        this.updateViewPort();

        this.initSize = {
            width: $(window).width(),
            height: $(window).height()
        };


        $(window).unbind('resize').resize(function (e) {
            //clearTimeout(ri);

            // determine resize deltas
            var delta_x = $(window).width() - self.initSize.width;
            var delta_y = $(window).height() - self.initSize.height;

            // ri = setTimeout(function() {
            var w = $('#' + Win.windowID);

            if (e.target === window) {
                self.updateViewPort();

                if (w.data('windowGrid') && Win.windowID !== null) {
                    self.disablePanelScrollbar();
                    w.data('windowGrid').updateDataTableSize(w);
                }
                else {
                    self.enablePanelScrollbar();
                }
            }

            var tc = self.getTabContent();
            if (tc && tc.length == 1) {
                var events = tc.data('events');
                if (events && typeof events.onResize != 'undefined' && events.onResize.length > 0) {
                    for (var i = 0; i < events.onResize.length; ++i) {
                        if (typeof events.onResize[i] == 'function') {
                            events.onResize[i](tc, delta_x, delta_y);
                        }
                    }
                }
            }

            if (typeof resizeAce === 'function') {
                if (Win.windowID !== null) {
                    if (w.data('ace')) {
                        resizeAce(w, delta_x, delta_y);
                    }
                }
            }
            if (self.dashboard.visible) {
                Tools.scrollBar( $( '#dashboard #dropbox' ) );
            }
            //}, 200);

            if (delta_x < 1 || delta_x > 1) {
                $('#main-tabs ul').trigger('change');
            }

            // reset cache size
            self.initSize = {
                width: $(window).width(),
                height: $(window).height()
            };
        });


        $(window).unbind('resizescrollbar').on('resizescrollbar', function (e) {
            self.enablePanelScrollbar();

            if (self.dashboard.visible) {
                Tools.scrollBar( $( '#dashboard #dropbox' ) );
            }


            $('#main-content-mid').find('.nano').each(function () {
                var data = $(this).data('nano');
                if (data) {
                    Tools.refreshScrollBar(data.$content);
                }
            });
        });


    },
    loadBasicConfig: function (callback) {
        $.ajax({
            url: 'admin.php',
            dataType: "json",
            cache: false,
            ajax: false,
            method: 'POST',
            data: {
                token: Config.get('token'),
                getBasics: true,
                screensize: $(window).width() + '|' + $(window).height()
            }
        }).done(function (data) {

            if (Tools.responseIsOk(data)) {
                if (typeof callback == 'function') {
                    return callback(data);
                }
                else {
                    return data;
                }
            }

        });
    },
    animateToLogin: function () {
        if ($('#boot-mask').hasClass('in')) {
            $('#boot-mask,#boot-maskmsg,#fullscreenContainer').removeClass('in');
            var complete = function () {
                $('#boot-mask,#boot-maskmsg,#fullscreenContainer').hide();
            };

            $.support.transition && $('#boot-mask').hasClass('fade') ?
                $('#boot-mask').one($.support.transition.end, complete)
                    .emulateTransitionEnd(150) :
                complete();
        }

        var top = ($('#auth').outerHeight() / 2) - ($('#auth-form-container').outerHeight() / 2);

        $('.auth-logo').css({
            top: top - ($('.auth-logo').outerHeight() + 30),
            left: ($('#auth').width() / 2) - $('.auth-logo').width() / 2
        });

        $('#auth-form-container').css({
            top: top,
            left: ($('#auth').width() / 2) - $('#auth-form-container').width() / 2
        });

        $('body').removeClass('boot').removeClass('fail').addClass('auth');


        var callAuth = function () {
            $('body').removeClass('boot');
            Auth.init();
        }

        setTimeout(function () {
            $('#auth').show().addClass('in');

            $.support.transition && $('#auth').hasClass('fade') ?
                $('#auth').one($.support.transition.end, callAuth)
                    .emulateTransitionEnd(150) :
                callAuth();
        }, 10);

    },

    animateToDesktop: function (_data) {

        var self = this;

        // this.loadBasicConfig(function (_data) {

        if (typeof _data != 'undefined' && typeof _data.success != 'undefined' && _data.success != true && typeof _data.msg == 'string' && _data.msg != '') {

            var msg = _data.msg;
            $('body').removeClass('boot').addClass('fail');
            var complete = function () {
                $('#auth,#fullscreenContainer').hide();
                $('.custom-msg', $('#fail')).append(msg).show();
                $('#fail,#custom-error').show();

            };

            $('#auth,#fullscreenContainer').removeClass('in');
            $.support.transition && $('#auth').hasClass('fade') ?
                $('#auth').one($.support.transition.end, complete)
                    .emulateTransitionEnd(150) :
                complete();
            return;
        }

        self.prepareConfig(_data, self.settings);

        if (typeof self.basicCMSData.userdata == 'undefined' || typeof self.basicCMSData.userdata.userid == 'undefined' || typeof self.basicCMSData.userdata.userid < 1) {
            self.animateToLogin();
            return;
        }

        self.settings = $.extend({}, self.settings, self.basicCMSData || {});
        Config.set('UserName', self.basicCMSData.userdata.username);

        $('#auth').removeClass('in');
        $.support.transition && $('#auth').hasClass('fade') ?
            $('#auth').one($.support.transition.end, $.proxy(this.completeDesktopCallback, this, _data))
                .emulateTransitionEnd(150) :
            this.completeDesktopCallback(_data);

        // });
    },

    completeDesktopCallback: function (_data) {

        var self = this;

        /*
         $(document).unbind('keydown.desktopShortcuts');
         $(document).on('keydown.desktopShortcuts', function (e) {
         if (window.focusedAceEdit !== null) {
         return;
         }

         var ctrl = e.ctrlKey, meta = e.metaKey;

         if (!ctrl && !meta) {
         return;
         }

         // F5 + CTRL refresh active Window
         if (e.keyCode === 116 && ctrl) {
         act = Core.Tabs.getActiveTab();
         if (act && act.length === 1) {
         Core.Tabs.refreshActiveTab();
         return;
         }

         //    console.log('ctrl + F5');
         }

         // A + CTRL About active Window
         if (e.keyCode === 65 && ctrl) {

         act = $('#App-Menu').find('ul:first .root-item:first').find('li:first a');
         if (act && act.length === 1) {
         act.trigger('click');
         return;
         }
         }

         // C + CTRL Console
         if (e.keyCode === 67 && ctrl) {

         act = $('#toggle-console');
         if (act && act.length === 1) {
         act.trigger('click');
         return;
         }
         }

         // S + CTRL Sidebar
         if (e.keyCode === 83 && ctrl) {

         act = $('#toggle-pane-content');
         if (act && act.length === 1) {
         act.trigger('click');
         return;
         }
         }
         });
         */
        self.panelIsVisible = false;
        if ($('#panel-content').is(':visible')) {
            self.panelIsVisible = true;
        }
        $('#toggle-pane-content').unbind().click(function (e) {
            e.preventDefault();

            var el = $(this);
            if ($('#panel-content').is(':visible'))
            {

                $('#panel').height($('#panel-buttons').outerHeight());
                $('#panel-content').stop().animate({
                    marginLeft: 0 - $('#panel-content').width()

                }, {
                    duration: 300,
                    step: function () {
                        self.panelIsVisible = false;

                        if ($('div.core-tab-content[isfileman]').length) {
                            Core.Tabs.triggerFilemanSizing(false);
                        }
                    },
                    complete: function () {
                        self.panelIsVisible = false;

                        $(this).hide().css({'left': '', opacity: '0'}).resizable('disable');
                        el.addClass('open');


                        setTimeout(function () {
                            $(window).trigger('resize');
                            if ($('.core-tab-content[isfileman]').length) {
                                Core.Tabs.triggerFilemanSizing();
                            }
                        }, 5);

                    }
                });
                // $('#panel').resizable('disable');
            }
            else
            {
                $('#panel').height($('#main-content').height());
                $('#panel-content').css('marginLeft', 0 - $('#panel').width()).show();

                setTimeout(function () {
                    $('#panel-content').animate({
                        marginLeft: 0,
                        opacity: '1'
                    }, {
                        duration: 300,
                        step: function () {
                            self.panelIsVisible = true;
                            if ($('div.core-tab-content[isfileman]').length) {
                                Core.Tabs.triggerFilemanSizing(false);
                            }
                        },
                        complete: function () {
                            self.panelIsVisible = true;

                            $(this).resizable('enable');
                            el.removeClass('open');

                            setTimeout(function () {
                                $(window).trigger('resize');
                                if ($('.core-tab-content[isfileman]').length) {
                                    Core.Tabs.triggerFilemanSizing();
                                }
                            }, 5);
                        }
                    });
                });
                //  $('#panel').resizable('enable');
            }
        });

        $('#main').unbind('click.main').bind('click.main', function (e) {
            //e.preventDefault();

            setTimeout(function () {

                if (!$(e.target).parents('#task-cal').length && !$(e.target).parents('.ui-datepicker-header').length && !$(e.target).attr('id') != 'task-cal') {
                    $('#task-cal').removeClass('in');
                    $('#menu-extras .calOpen').removeClass('active calOpen');
                }

                if (!$(e.target).parents('#header').length) {
                    DesktopMenu.hide();
                }
            }, 100);
        });

        $(document).unbind('click.visibles').bind('click.visibles', function (e) {
            $('body > div.dockBubbleContent').remove();


            if (!$(e.target).parents('#header').length) {
                $('#contenttrans-selector,#fav-selector').hide();
                $('#menu-extras .active:not(#console,#indexer,#toggle-dashboard)').removeClass('active');
                if (!$(e.target).parents('div.search-result-preview').length && !$(e.target).parents('div#searchPopup').length) {
                    Indexer.hide();
                }
                if (!$(e.target).parents('#task-cal').length && !$(e.target).parents('.ui-datepicker-header').length && !$(e.target).attr('id') != 'task-cal') {
                    $('#menu-extras .calOpen').removeClass('active calOpen');
                    $('#task-cal').removeClass('in');
                }
            }

            if ($(e.target).parents('#menu').length) {
                $('#contenttrans-selector,#fav-selector').hide();
                if (!$(e.target).parents('div#task-cal').length && !$(e.target).parents('.ui-datepicker-header').length && !$(e.target).attr('id') != 'task-cal') {
                    $('#task-cal').removeClass('in');
                    $('#menu-extras .calOpen').removeClass('active calOpen');
                }
                $('#menu-extras .active:not(#console,#indexer,#toggle-dashboard)').removeClass('active');
                Indexer.hide();
            }

        });

        DesktopMenu.createCoreMenu();
        Tasks.init();

        this.dashboard = new Dashboard();
        if (typeof _data.widgets == 'object') {
            this.dashboard.init({widgets: _data.widgets});
            Tools.scrollBar( $( '#dashboard #dropbox' ) );
        }
        else {
            this.dashboard.init();
            Tools.scrollBar( $( '#dashboard #dropbox' ) );
        }

        ContentTree.init($('#pages-tree-content').find('>div:not(.pane)'));
        if (typeof _data.modules == 'object') {
            ContentTree.createTree({modules: _data.modules});
        }
        else {
            ContentTree.build();
        }

        Core.Tabs.init(this.dashboard);

        self.initPanelButtons();
        Panel.init((typeof _data.logs == 'object' ? {logs: _data.logs} : null));

        DesktopConsole.init();

        if (_data.debugoutput && _data.debugoutput !== null) {
            DesktopConsole.setDebug(_data.debugoutput);
        }

        var t, t2;
        $('#panel-content').resizable({
            minWidth: 200,
            cursor: 'resize',
            handles: 'e',
            resize: function (e, ui) {
                clearTimeout(t);
                clearTimeout(t2);
                $('#panel').width(ui.size.width);

                if ($('#' + Win.windowID).data('windowGrid')) {
                    t = setTimeout(function () {
                        $('#' + Win.windowID).data('windowGrid').updateDataTableSize($('#' + Win.windowID));
                    }, 300);
                }

                if ($('#' + Win.windowID).attr('isfileman')) {
                    t2 = setTimeout(function () {
                        Core.Tabs.triggerFilemanSizing();
                    }, 300);
                }
            }
        });

        $('#panel-content').width($('#panel').width());

        $('#copyright').click(function (e) {
            e.preventDefault();
            self.checkVersion();
        });



        var completeBoot = function () {
            $('body').removeClass('fail').removeClass('boot').removeClass('auth');
            $('#auth,#boot-mask,#boot-maskmsg').hide();
            $('#fullscreenContainer').show().addClass('in');

            self.initWindowResizeEvents();
/*
            setTimeout(function () {
                self.checkVersion();
            }, 3000);
            */
        };

        setTimeout(function () {
            if ($('#boot-mask').hasClass('in')) {
                $('#boot-mask,#boot-maskmsg').removeClass('in');
                $.support.transition && $('#boot-mask').hasClass('fade') ?
                    $('#boot-mask').one($.support.transition.end, $.proxy(completeBoot, this))
                        .emulateTransitionEnd(150) :
                    completeBoot();
            }
            else {
                $('#auth').removeClass('in');
                $.support.transition && $('#auth').hasClass('fade') ?
                    $('#auth').one($.support.transition.end, $.proxy(completeBoot, this))
                        .emulateTransitionEnd(150) :
                    completeBoot();
            }
        }, 500);

    },

    updateContentScrollbars: function () {

        // $('#content-container').nanoScroller({scrollContent: $('#content-container-inner')});
    },
    activePanelContent: null,
    initPanelButtons: function () {
        var self = this;
        this.activePanelContent = $('#panel-buttons').find('li.active').attr('rel');

        $('#panel-buttons').find('li[rel]').unbind('click.panelbuttons').bind('click.panelbuttons', function (e) {
            e.preventDefault();

            var rel = $(this).attr('rel');
            self.disablePanelScrollbar();

            if ($('#toggle-pane-content').hasClass('open') && !$(this).hasClass('active')) {
                $('#toggle-pane-content').trigger('click');
            }
            self.activePanelContent = rel;
            $(this).parent().find('li.active').removeClass('active');
            $(this).addClass('active');
            $('#panel-content >div:visible:not(.ui-resizable-handle)').hide();
            $('#panel-content #panel-' + rel).show();

            self.enablePanelScrollbar();
            self.updateViewPort();

            $(window).trigger('resizescrollbar');
        });

        $('#panel-buttons').find('li[rel]:first').trigger('click.panelbuttons');

        //this.updateViewPort();
        //this.enablePanelScrollbar();


    },
    disablePanelScrollbar: function () {
        $('#panel-' + this.activePanelContent + ' .panel-content').removeNanoScroller();
    },
    enablePanelScrollbar: function () {
        $('#panel-' + this.activePanelContent + ' .panel-content').nanoScroller({
            scrollContent: $('#panel-' + this.activePanelContent + ' .panel-content')
        });
    },
    closeTab: function (callback, formExit, unlockaction) {
        Core.Tabs.closeActiveTab(callback, formExit, unlockaction);
    },
    setDirty: function (fromForm) {
        var tab = Core.Tabs.getActiveTab();
        tab.addClass('dirty');
        var statusbar = Core.Tabs.getActiveStatusbar();

        if (statusbar.length) {
            if (fromForm) {


                // set statusbar
                var div = '<div class="state-global"><span class="state"></span><span class="state-msg">' + cmslang.form_dirty + '</span></div>';
                statusbar.removeClass('saveing').addClass('dirty');

                if (statusbar.find('.autosave-msg').length) {
                    statusbar.find('.autosave-msg').hide();
                }
                if (statusbar.find('div.state-global').length) statusbar.find('div.state-global').replaceWith(div);
                else statusbar.prepend(div);

            }
        }
    },

    setSaving: function (fromForm) {
        // var tab = Core.Tabs.getActiveTab();
        var statusbar = Core.Tabs.getActiveStatusbar();
        if (statusbar.length) {

            if (fromForm) {
                // set statusbar
                var div = '<div class="state-global"><span class="state"></span><span class="state-msg">' + cmslang.save_notifystatus + '</span></div>';
                statusbar.removeClass('dirty').addClass('saveing');
                if (statusbar.find('.autosave-msg').length) {
                    statusbar.find('.autosave-msg').hide();
                }
                if (statusbar.find('div.state-global').length) statusbar.find('div.state-global').replaceWith(div);
                else statusbar.prepend(div);

                // set statusbar
                // statusbar.addClass('saveing').html('<span class="state"></span><span class="state-msg">' + cmslang.save_notifystatus);
            }
        }
    },

    resetDirty: function (fromForm, btn) {
        var tab = Core.Tabs.getActiveTab();
        tab.removeClass('dirty');

        var statusbar = Core.Tabs.getActiveStatusbar();
        if (statusbar.length) {
            if (fromForm) {
                // set statusbar
                var div = '<div class="state-global"><span class="state"></span><span class="state-msg">' + cmslang.default_status + '</span></div>';
                statusbar.removeClass('saveing').removeClass('dirty');


                if (!statusbar.find('.autosave-msg').length) {
                    if (statusbar.find('div.state-global').length) statusbar.find('div.state-global').replaceWith(div);
                    else statusbar.prepend(div);
                }
                else {
                    if (statusbar.find('div.state-global').length) statusbar.find('div.state-global').replaceWith('<div class="state-global"><span class="state"></span></div>');
                    else statusbar.prepend('<div class="state-global"><span class="state"></span></div>');

                    statusbar.find('.autosave-msg').css({
                        display: 'inline-block'
                    });
                }
                // reset statusbar
                //  statusbar.removeClass('saveing').removeClass('dirty').html('<span></span>' + cmslang.default_status);
            }
        }

        // reset tinymce dirty trigger :)
        var tc = this.getTabContent();
        if (tc) {

            var editors = tinymce.editors;

            if (editors.length) {
                tc.find('.tinymce-editor').each(function () {
                    var ed = tinyMCE.get('inline-' + this.id);
                    if (ed) {
                        ed.target.fire('focus');
                        ed.dirty = false;
                        ed.lastString = ed.getContent();
                        ed.isNotDirty = true;
                        ed.undoManager.clear();
                        ed.target.fire('blur');

                        $(this).trigger('blur');
                    }
                });
            }

        }
    },
    /**
     *
     * @param {type} url
     * @param {type} postData
     * @returns object
     */
    convertGetToPost: function (url, postData) {
        if (url == '') {
            return {};
        }
        var json;
        url = url.replace('&amp;', '&');
        var str = (url.match(/\?/g) ? url.slice(url.indexOf('?', 0) + 1) : '');
        json = JSON.parse('{"' + decodeURI(str.replace(/&/g, "\",\"").replace(/=/g, "\":\"")) + '"}');
        return json;
    },
    getAppKey: function (url) {

        var adm = $.getURLParam('adm', url);
        if (adm === 'plugin') {
            var plugin = $.getURLParam('plugin', url);

            if (plugin) {
                return plugin;
            }
        }

        return adm;
    },
    /**
     *
     * @param {type} url
     * @returns {unresolved}
     */
    getHash: function (url) {
        return md5(url);
    },
    /**
     *
     * @param {type} use
     * @param {type} hash
     * @returns {undefined}
     */
    initMetaData: function (use, hash) {
        if (use && $('#meta-' + hash).length) {

            if (!$('#panel-buttons .documentsettings').hasClass('active')) {
                $('#panel-buttons .documentsettings').show();
            }
            else {
                $('#panel-buttons .documentsettings').show().trigger('click.panelbuttons');
            }
        }
        else {
            if ($('#panel-buttons .documentsettings').hasClass('active')) {
                $('#panel-buttons li[rel=sites]').trigger('click.panelbuttons');
                $('#panel-buttons .documentsettings').hide();
            }
            else {
                $('#panel-buttons .documentsettings').hide();
            }
        }

        // Tools.scrollBar($('#panel-content #panel-documentsettings > div:last>div:first'));
    },
    /**
     *
     *
     *
     */

    getScript: jQuery.getScript,
    loadCss: function (url, callback) {
        var hash = this.getHash(url);
        if ($('#' + hash, $("head")).length == 0) {
            var styleTag = $('<link/>').attr('type', 'text/css').attr('id', hash).attr('href', url);
            $("head").append(styleTag);
            $.ajax({
                url: url,
                dataType: "text",
                error: function () {
                    Debug.error('Could not get the CSS File: ' + url);
                },
                success: function (data) {
                    var styleTag = $('<style>').attr('id', hash);
                    $("head").prepend(styleTag.text(data));

                    if (typeof callback == 'function') {
                        callback(styleTag);
                    }
                }
            });
        }
        else {
            if (typeof callback == 'function') {
                callback(styleTag);
            }
        }
    },
    jqGetScript: jQuery.getScript,
    loadScripts: function (scripts, _callback) {
        var self = this;

        if (typeof scripts != 'object') {
            Debug.error('To load external scripts must give an object');

            if (typeof _callback == 'function') {
                _callback();
            }
            return false;
        }
        /*
         jQuery.getScript = function (resources, jqCallbackIn) {
         var // reference declaration & localization
         length = resources.length,
         handler = function () {
         counter++;
         },
         deferreds = [],
         counter = 0,
         idx = 0;

         for (; idx < length; idx++) {
         deferreds.push(
         Core.getScript(resources[ idx ], handler)
         );
         }

         jQuery.when.apply(null, deferreds).then(function () {
         jqCallbackIn && jqCallbackIn();
         jQuery.getScript = Core.getScript;

         });
         };
         */
        for (var x = 0; x < scripts.length; x++) {
            if (scripts[x].substr(scripts[x].length - 3, scripts[x].length) !== '.js') {
                scripts[x] += '.js';
            }
        }

        if (scripts.length > 0) {
            var ev = [];

            for (var x = 0; x < scripts.length; x++) {
                ev[x] = scripts[x];
            }

            var cb = _callback;

            if (_callback) {
                cb = function () {
                    $.ajaxSetup({
                        async: false
                    });
                    _callback();
                };
            }

            $.ajaxSetup({
                async: true
            });

            Loader.require(ev, cb);
        }
    },
    loadTab: function(opts, callback) {
        "use strict";

        var tab = new Tab(this.dashboard);
        this.WindowTabs.push(tab);
        var loaded, forceRemove = false;

        loaded = tab.load(opts, function(valid, ajaxdata) {
            if (valid) {
                loaded = true;
            }
            else {
                loaded = false;
                forceRemove = true;

                // error popup

            }
        });

        if ( loaded === false || forceRemove === true ) {
            this.WindowTabs.pop();
        }
    },

    getActive: function() {
        "use strict";
        for (var x in this.WindowTabs) {
            if (this.WindowTabs[x].isActive ) {
                return this.WindowTabs[x];
            }
        }
        return false;
    },



    getToolbar: function () {
        return Core.Tabs.getActiveToolbar();
        var act = this.getActive();

        if (act) {
            return act.getActiveToolbar();
        }

        return false;

    },
    getActiveStatusbar: function () {
        return Core.Tabs.getActiveStatusbar();

        var act = this.getActive();

        if (act) {
            return act.getActiveStatusbar();
        }

        return false;

    },
    getStatusbar: function () {
        var hash = Core.Tabs.getActiveTabHash();

        if (hash) {
            return $('#statusbar-' + hash);
        }

        return false;

        var act = this.getActive();

        if (act) {
            return act.getActiveStatusbar();
        }

        return false;
    },
    getContent: function () {
        var hash = Core.Tabs.getActiveTabHash();

        if (hash) {
            return $('#content-' + hash);
        }

        return false;


        var act = this.getActive();

        if (act) {
            return act.getActiveTabContent();
        }

        return false;

    },
    getTabContent: function () {
        return Core.Tabs.getActiveTabContent();

        var act = this.getActive();

        if (act) {
            return act.getActiveTabContent();
        }

        return false;

    },
    getActiveTabHash: function () {
        return Core.Tabs.getActiveTabHash();
    },
    getContentTabs: function () {
        var hash = Core.Tabs.getActiveTabHash();

        if (hash) {
            return $('#content-tabs-' + hash + ':visible');
        }

        return false;
    },
    refreshTab: function (callback) {
        var hash = Core.Tabs.getActiveTabHash();
        Core.Tabs.refreshTab(hash, callback);
    },


    reloadAllTinyMCEs: function (newmceconfig, callback) {
        Doc.prepareTinyMceSetup(newmceconfig, true);
        Doc.repaintTinyMceEditors(callback);
    },

    cacheShortcutHelp: [],
    addShortcutHelp: function (key, description) {
        var tabHash = this.getActiveTabHash();

        if (tabHash) {
            if (typeof this.cacheShortcutHelp[tabHash] != 'undefined') {
                this.cacheShortcutHelp[tabHash].push([key, description]);
            }
            else {
                this.cacheShortcutHelp[tabHash] = [];
                this.cacheShortcutHelp[tabHash].push([key, description]);
            }
        }
        else {
            if (typeof this.cacheShortcutHelp['core'] != 'undefined') {
                this.cacheShortcutHelp['core'].push([key, description]);
            }
            else {
                this.cacheShortcutHelp['core'] = [];
                this.cacheShortcutHelp['core'].push([key, description]);
            }
        }
    },

    removeShortcutHelp: function (key, toolbar) {

        if (toolbar) {
            var tabHash = this.getActiveTabHash();
            if (typeof this.cacheShortcutHelp[tabHash] != 'undefined') {
                for (var i = 0; i < this.cacheShortcutHelp[tabHash].length; i++) {
                    var d = this.cacheShortcutHelp[tabHash][i];
                    if (d[0] == key) {
                        delete this.cacheShortcutHelp[tabHash][i];
                        Tools.reindexArray(delete this.cacheShortcutHelp[tabHash]);
                        break;
                    }
                }
                if (typeof this.cacheShortcutHelp[tabHash] != 'undefined') {
                    if (!this.cacheShortcutHelp[tabHash].length) {
                        delete this.cacheShortcutHelp[tabHash];
                        Tools.reindexArray(delete this.cacheShortcutHelp);
                    }
                }
            }
        }

    },

    displayShortcuts: function () {
        if ($('#shortcuts').length) {
            return;
        }

        var container = $('<div></div>');
        var shortcutContainer = $('<div id="shortcuts"></div>');

        var tabHash = this.getActiveTabHash();
        if (tabHash) {

            var found = 0;

            shortcutContainer.empty();
            shortcutContainer.append('<h3>Core Shortcuts</h3>');

            var tb = this.getToolbar();

            if (typeof this.cacheShortcutHelp['core'] != 'undefined') {

                for (var i = 0; i < this.cacheShortcutHelp['core'].length; i++) {
                    var d = this.cacheShortcutHelp['core'][i];

                    // require toolbar
                    if (d[2] && !tb.length) {
                        continue;
                    }

                    var keyCode = d[0];
                    var parsed = this.parseShortcut(keyCode);

                    if (parsed) {
                        var length = parsed.length, div = $('<div class="row"></div>');
                        var keyboard = $('<div class="kb"></div>');

                        for (var y = 0; y < parsed.length; ++y) {

                            keyboard.append('<span>' + parsed[y].keycodename + '</span>');

                            if (length > 1 && y < length - 1) {
                                keyboard.append(' + ');
                            }
                        }


                        found++;

                        div.append(keyboard).append('<div class="description">' + d[1] + '</div>');
                        shortcutContainer.append(div);
                    }

                }
            }


            if (typeof this.cacheShortcutHelp[tabHash] != 'undefined') {

                shortcutContainer.append('<h3>Tab Shortcuts</h3>');

                for (var i = 0; i < this.cacheShortcutHelp[tabHash].length; i++) {
                    var d = this.cacheShortcutHelp[tabHash][i];

                    var keyCode = d[0];
                    var parsed = this.parseShortcut(keyCode);

                    if (parsed) {
                        var length = parsed.length, div = $('<div class="row"></div>');
                        var keyboard = $('<div class="kb"></div>');


                        for (var y = 0; y < parsed.length; ++y) {

                            keyboard.append('<span>' + parsed[y].keycodename + '</span>');

                            if (length > 1 && y < length - 1) {
                                keyboard.append(' + ');
                            }
                        }

                        div.append(keyboard).append('<div class="description">' + d[1] + '</div>');
                        shortcutContainer.append(div);

                        found++;
                    }
                }


            }

            if (found) {

                container.append(shortcutContainer);

                Tools.createPopup(container.html(), {nopadding: true, title: 'Shortcuts', Width: 550, Height: 400});

                return;
            }

        }
        else {
            if (typeof this.cacheShortcutHelp['core'] != 'undefined') {

                shortcutContainer.empty();

                for (var i = 0; i < this.cacheShortcutHelp['core'].length; i++) {
                    var d = this.cacheShortcutHelp['core'][i];

                    // require toolbar
                    if (d[2] && !tb.length) {
                        continue;
                    }

                    var keyCode = d[0];
                    var parsed = this.parseShortcut(keyCode);

                    if (parsed) {
                        var length = parsed.length, div = $('<div class="row"></div>');
                        var keyboard = $('<div class="kb"></div>');

                        for (var y = 0; y < parsed.length; ++y) {

                            keyboard.append('<span>' + parsed[y].keycodename + '</span>');

                            if (length > 1 && y < length - 1) {
                                keyboard.append(' + ');
                            }
                        }

                        div.append(keyboard).append('<div class="description">' + d[1] + '</div>');
                        shortcutContainer.append(div);
                    }
                }

                container.append(shortcutContainer);

                Tools.createPopup(container.html(), {nopadding: true, title: 'Shortcuts', Width: 550, Height: 400});

                return;
            }
        }

        Tools.createPopup('<div id="shortcuts"><em>No shortcuts found.</em></div>', {nopadding: true, title: 'Shortcuts', Width: 320, Height: 100});
    },


    _keys: function () {

        return {
            keys: {
                3: 'Cancel',
                8: 'Backspace',
                9: 'Tab',
                12: 'Clear',
                13: 'Return',
                14: 'Enter',
                16: 'Shift',
                17: 'Ctrl',
                18: this.Mac ? 'Option' : 'Alt',
                19: 'Pause',
                20: 'Caps\u00a0Lock',
                27: 'Escape',
                32: 'Space',
                33: 'Page\u00a0Up',
                34: 'Page\u00a0Down',
                35: 'End',
                36: 'Home',
                37: 'Left',
                38: 'Up',
                39: 'Right',
                40: 'Down',
                41: 'Select',
                42: 'Print',
                43: 'Execute',
                44: 'Print\u00a0Screen',
                45: 'Insert',
                46: 'Delete',
                47: 'Help',
                58: ':',
//			 59: ';',
                60: '<',
//			 61: '=',
                62: '>',
                63: '?',
                64: '@',

                65: "a", 66: "b", 67: "c", 68: "d", 69: "e", 70: "f", 71: "g", 72: "h", 73: "i", 74: "j", 75: "k", 76: "l",
                77: "m", 78: "n", 79: "o", 80: "p", 81: "q", 82: "r", 83: "s", 84: "t", 85: "u", 86: "v", 87: "w", 88: "x", 89: "y", 90: "z",


                91: this.Windows ? 'Left\u00a0Win' : this.Mac ? 'Left\u00a0Cmd' : 'Left\u00a0Meta',
                92: this.Windows ? 'Right\u00a0Win' : this.Mac ? 'Right\u00a0Cmd' : 'Right\u00a0Meta',
                93: 'Context\u00a0Menu',
                95: 'Sleep',
                96: 'Numpad\u00a00',
                97: 'Numpad\u00a01',
                98: 'Numpad\u00a02',
                99: 'Numpad\u00a03',
                100: 'Numpad\u00a04',
                101: 'Numpad\u00a05',
                102: 'Numpad\u00a06',
                103: 'Numpad\u00a07',
                104: 'Numpad\u00a08',
                105: 'Numpad\u00a09',
                106: 'Multiply',
                107: 'Add',
                108: 'Separator',
                109: 'Subtract',
                110: 'Decimal',
                111: 'Divide',


                112: "F1", 113: "F2", 114: "F3", 115: "F4", 116: "F5", 117: "F6", 118: "F7", 119: "F8", 120: "F9", 121: "F10", 122: "F11", 123: "F12",


                144: 'Num\u00a0Lock',
                145: 'Scroll\u00a0Lock',
                160: '^',
                161: '!',
                162: '"',
                163: '#',
                164: '$',
                165: '%',
                166: '&',
                167: '_',
                168: '(',
                169: ')',
                170: '*',
                171: '+',
                172: '|',
//			173: '-',
                174: '{',
                175: '}',
                176: '~',
                181: 'Volume\u00a0Mute',
                182: 'Volume\u00a0Down',
                183: 'Volume\u00a0Up',
                186: ';',
                187: '=',
                188: ',',
                189: '-',
                190: '.',
                191: '/',
                192: '´',
                219: '[',
                220: '\\',
                221: ']',
                222: "'",
                224: this.Windows ? 'Win' : this.Mac ? 'Cmd' : 'Meta',
                225: 'Alt\u00a0Graph',
//			226: ???, XXX: is it '<', '|' or '\' on IE?
                250: 'Play',
                251: 'Zoom'
            },
            modifiers: {
                ctrlKey: 'Ctrl',
                altKey: this.Mac ? 'Option' : 'Alt',
                shiftKey: 'Shift',
                metaKey: this.Windows ? 'Win' : this.Mac ? 'Cmd' : 'Meta',
                altgraphKey: 'Alt\u00a0Graph'
            },
            modifierKeys: {
                16: 'shiftKey',
                17: 'ctrlKey',
                18: 'altKey',
                91: 'metaKey',
                92: 'metaKey',
                224: 'metaKey',
                225: 'altgraphKey'
            },
            modifierAliases: {
                'cmd': 'metaKey',
                'command': 'metaKey',
                'win': 'metaKey',
                'windows': 'metaKey',
                'meta': 'metaKey',
                'os': 'metaKey',
                'option': 'altKey',
                'altgr': 'altgraphKey',
                'alt\u00a0gr': 'altgraphKey',
            }

        };
    },


    parseShortcut: function (code) {

        var modifier = {
            ctrl: 'Ctrl',
            shift: 'Shift',
            alt: this.Mac ? 'Option' : 'Alt',
            meta: this.Windows ? 'Win' : this.Mac ? 'Cmd' : 'Meta',
        };
        code = code.replace('++', '+Plus');

        var chars = code.split('+');
        if (chars.length >= 2) {

            var keys = this._keys();

            var ret = [];
            for (var i = 0; i < chars.length; i++) {

                var keycodename = false, keycode = false, key = chars[i].toLowerCase();

                if (key == 'Plus') {
                    key = '+'
                }

                if (typeof modifier[key] != 'undefined') {
                    keycodename = modifier[key];
                    /*
                     for (var num in keys.keys) {
                     if (keycodename == keys.keys[num]) {
                     keycode = num;
                     break;
                     }
                     }

                     if (keycodename && !keycode) {
                     return false;
                     }
                     */
                }
                else {
                    keycodename = key.toUpperCase();
                }

                ret.push({
                    keycode: keycode,
                    keycodename: keycodename
                });
            }

            if (ret.length) return ret;
        }

        return false;
    },


    /**
     *
     * eventName eg: onBeforeClose, onBeforeShow, onAfterShow
     *
     *
     *
     * @param {type} eventName
     * @param {type} call
     * @returns {undefined}
     */
    addEvent: function (eventName, call, global) {
        if (!global) {
            var useContent = this.getTabContent();

            if (useContent.length) {
                var events = useContent.data('events');

                if (typeof events != 'undefined' && events) {
                    if (events[eventName]) {
                        events[eventName].push(call);
                    }
                    else {
                        events[eventName] = [];
                        events[eventName].push(call);
                    }
                }
                else {
                    events = {};
                    events[eventName] = [];
                    events[eventName].push(call);
                }

                useContent.data('events', events);
            }
        }
        else {

            if (this.eventregistry[eventName]) {
                this.eventregistry[eventName].push(call);
            }
            else {
                this.eventregistry[eventName] = [];
                this.eventregistry[eventName].push(call);
            }
        }
    },
    prepareContent: function (data) {

    },
    getAjaxContent: function (opts, callback) {

    },


    preparePostColumn: function (contentObj) {
        var box = contentObj.find('div.postbox'), boxlength = box.length;

        contentObj.find('div.postbox').each(function () {

            var b = $(this);
            var header = b.find('>h3'); // h3
            var content = b.find('>div.postbox-content');
            var rel = content.attr('rel');

            if (rel && content.length) {

                if (!header.prev().is('span.toggle')) {

                    var defaultOpen = Core.storage('postbox-' + rel);
                    if (defaultOpen == 0) {
                        content.hide();
                        $(this).addClass('closed');
                    }

                    $('<span class="toggle"></span>').insertBefore(header);

                    b.find('span.toggle:first').click(function (e) {
                        e.preventDefault();
                        var rel = content.attr('rel');
                        if (!content.is(':visible')) {
                            content.fastshow();
                            content.parent().removeClass('closed');
                            Core.storage('postbox-' + rel, null);
                        }
                        else {
                            content.fasthide();
                            content.parent().addClass('closed');
                            Core.storage('postbox-' + rel, 0);
                        }

                        $(window).trigger('resizescrollbar');

                    });
                }
            }

        });
    },


    checkVersion: function () {
        $.get('admin.php?adm=dashboard&action=checkversion', function (data) {
            if (Tools.responseIsOk(data)) {

                if (data.body === false) {
                    return;
                }
                else {
                    $('#version-info').css({
                        left: $(window).width() / 2 - ($('#version-info').width() / 2)
                    });
                    if (typeof data.title == 'string') {
                        $('#version-info .modal-header').empty().append(data.title);
                    }

                    if (typeof data.icon == 'string') {
                        $('#version-info .modal-header').prepend(data.icon);
                    }

                    $('#version-info .modal-header').append('<button type="button" class="close" data-dismiss="modal">&times;</button>');

                    if (typeof data.body == 'string') {
                        $('#version-info .modal-body').empty().append(data.body);
                    }

                    setTimeout(function () {
                        $('#version-info').stop().modal('show');
                    }, 100);
                }

            }
        });
    },

    /**
     *
     *
     *
     */
    loadTab: function (event, opt /*url, obj, label, useOpener*/) {

        if (!opt.obj) {
            Debug.error('Empty object for loadTab!');
            return;
        }
    },
    BootstrapInit: function (contentObj, metaObj, hash) {
        if (contentObj && contentObj.length == 1) {
            this.preparePostColumn(contentObj);
            Bootstraper.init(contentObj, metaObj, hash);
        }

        if (metaObj && metaObj.length == 1) {
            Bootstraper.init(metaObj, metaObj, hash);
        }
    },
    refreshMenu: function () {
        DesktopMenu.coreMenuCache = null;

        $('#NavItems').empty().mask('Loading...');
        jQuery.ajax({
            type: "GET",
            url: Tools.prepareAjaxUrl('admin.php?action=menu'),
            async: true,
            success: function () {
                DesktopMenu.coreMenuCache = top.menuItems;
                DesktopMenu.buildMenu(DesktopMenu.coreMenuCache, 0);
                //top.menuItems = null;
            },
            dataType: "script",
            cache: false
        });
        $('#NavItems').unmask();

    }
};

(function (jQuery) {
    var _get = jQuery.get;
    var _post = jQuery.post;


    jQuery.get = function (url, opts, call, type) {

        if (typeof type !== 'string') {
            type = 'json';
        }

        if (typeof call == 'function') {
            $.ajax({
                url: url,
                type: 'GET',
                data: opts,
                dataType: type,
                cache: false,
                async: false
            }).done(function (data) {

                call(data);
            });
        }
        else if (typeof opts == 'function') {
            $.ajax({
                url: url,
                type: 'GET',
                dataType: type,
                cache: false,
                async: false
            }).done(function (data) {

                opts(data);
            });
        }
        else {
            $.ajax({
                url: url,
                type: 'GET',
                dataType: type,
                cache: false,
                async: false
            }).done(function (data) {

            });
        }

    };

    jQuery.post = function (url, opts, call, type) {

        if (typeof type !== 'string') {
            type = 'json';
        }

        if (typeof call == 'function') {
            $.ajax({
                url: url,
                type: 'POST',
                data: (typeof opts == 'object' || typeof opts == 'string' ? opts : ''),
                dataType: type,
                cache: false,
                async: false
            }).done(function (data) {

                call(data);
            });
        }
        else if (typeof opts == 'function') {
            $.ajax({
                url: url,
                type: 'POST',
                dataType: type,
                cache: false,
                async: false
            }).done(function (data) {

                opts(data);
            });
        }
        else {
            $.ajax({
                url: url,
                type: 'POST',
                data: (typeof opts == 'object' || typeof opts == 'string' ? opts : ''),
                dataType: type,
                cache: false,
                async: false
            }).done(function (data) {

            });
        }

    };

    $.fn.empty = function () {

        if (this.length == 1) {
            var len = this[0].childNodes.length;
            while (len--) {
                this[0].removeChild(this[0].lastChild);
            }
            return $(this);
        }
        return this.each(function () {
            var len = this.childNodes.length;
            while (len--) {
                this.removeChild(this.lastChild);
            }
        })
    };

    $.fn.fasthide = function () {
        if (this.length == 1) {
            this[0].style.display = 'none';
            return $(this);
        }

        return this.each(function () {
            this.style.display = 'none';
        })
    };

    $.fn.fastshow = function () {
        if (this.length == 1) {
            if (this[0].style.display == 'none') {
                this[0].style.display = 'block';
            }
            else {
                this[0].style.display = 'block';
            }

            return $(this);
        }

        return this.each(function () {
            if (this.style.display === 'none') {

                this.style.display = 'block';

            }
            else {
                this.style.display = 'block';

            }

        })
    };

    $.fn.toggleDisplay = function (opts, callback) {

        var opt = {
            duration: 200
        };

        if (opts.duration === 'fast' || opts === 'fast') {
            opt.duration = 200;
        }

        if (opts.duration === 'slow' || opts === 'slow') {
            opt.duration = 350;
        }

        if (typeof opts === 'number') {
            opt.duration = opts;
        }

        return this.each(function () {
            if ($(this).is(':visible')) {
                $(this).animate({
                    height: "toggle",
                    opacity: "0"
                }, {
                    duration: opt.duration,
                    complete: function () {
                        $(this).stop().hide();

                        if (typeof callback == 'function') {
                            callback(false);
                        }
                    }
                });
            }
            else {
                $(this).animate({
                    height: "toggle",
                    opacity: "1"
                }, {
                    duration: opt.duration,
                    complete: function () {
                        $(this).stop().show();

                        if (typeof callback == 'function') {
                            callback(true);
                        }
                    }
                });
            }
        });
    };
})(jQuery, window, document);

var BBCodeConverter = function () {
    var me = this;            // stores the object instance
    var token_match = /{[A-Z_]+[0-9]*}/ig;

    // regular expressions for the different bbcode tokens
    var tokens = {
        'URL': '((?:(?:[a-z][a-z\\d+\\-.]*:\\/{2}(?:(?:[a-z0-9\\-._~\\!$&\'*+,;=:@|]+|%[\\dA-F]{2})+|[0-9.]+|\\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\\])(?::\\d*)?(?:\\/(?:[a-z0-9\\-._~\\!$&\'*+,;=:@|]+|%[\\dA-F]{2})*)*(?:\\?(?:[a-z0-9\\-._~\\!$&\'*+,;=:@\\/?|]+|%[\\dA-F]{2})*)?(?:#(?:[a-z0-9\\-._~\\!$&\'*+,;=:@\\/?|]+|%[\\dA-F]{2})*)?)|(?:www\\.(?:[a-z0-9\\-._~\\!$&\'*+,;=:@|]+|%[\\dA-F]{2})+(?::\\d*)?(?:\\/(?:[a-z0-9\\-._~\\!$&\'*+,;=:@|]+|%[\\dA-F]{2})*)*(?:\\?(?:[a-z0-9\\-._~\\!$&\'*+,;=:@\\/?|]+|%[\\dA-F]{2})*)?(?:#(?:[a-z0-9\\-._~\\!$&\'*+,;=:@\\/?|]+|%[\\dA-F]{2})*)?)))',
        'LINK': '([a-z0-9\-\./]+[^"\' ]*)',
        'EMAIL': '((?:[\\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*(?:[\\w\!\#$\%\'\*\+\-\/\=\?\^\`{\|\}\~]|&)+@(?:(?:(?:(?:(?:[a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(?:\\d{1,3}\.){3}\\d{1,3}(?:\:\\d{1,5})?))',
        'TEXT': '(.*?)',
        'SIMPLETEXT': '([a-zA-Z0-9-+.,_ ]+)',
        'INTTEXT': '([a-zA-Z0-9-+,_. ]+)',
        'IDENTIFIER': '([a-zA-Z0-9-_]+)',
        'COLOR': '([a-z]+|#[0-9abcdef]+)',
        'NUMBER': '([0-9]+)'
    };

    var bbcode_matches = [];        // matches for bbcode to html

    var html_tpls = [];             // html templates for html to bbcode

    var html_matches = [];          // matches for html to bbcode

    var bbcode_tpls = [];           // bbcode templates for bbcode to html

    /**
     * Turns a bbcode into a regular rexpression by changing the tokens into
     * their regex form
     */
    var _getRegEx = function (str) {
        var matches = str.match(token_match);
        var nrmatches = matches.length;
        var i = 0;
        var replacement = '';

        if (nrmatches <= 0) {
            return new RegExp(preg_quote(str), 'g');        // no tokens so return the escaped string
        }

        for (; i < nrmatches; i += 1) {
            // Remove {, } and numbers from the token so it can match the
            // keys in tokens
            var token = matches[i].replace(/[{}0-9]/g, '');

            if (tokens[token]) {
                // Escape everything before the token
                replacement += preg_quote(str.substr(0, str.indexOf(matches[i]))) + tokens[token];

                // Remove everything before the end of the token so it can be used
                // with the next token. Doing this so that parts can be escaped
                str = str.substr(str.indexOf(matches[i]) + matches[i].length);
            }
        }

        replacement += preg_quote(str);      // add whatever is left to the string

        return new RegExp(replacement, 'gi');
    };

    /**
     * Turns a bbcode template into the replacement form used in regular expressions
     * by turning the tokens in $1, $2, etc.
     */
    var _getTpls = function (str) {
        var matches = str.match(token_match);
        var nrmatches = matches.length;
        var i = 0;
        var replacement = '';
        var positions = {};
        var next_position = 0;

        if (nrmatches <= 0) {
            return str;       // no tokens so return the string
        }

        for (; i < nrmatches; i += 1) {
            // Remove {, } and numbers from the token so it can match the
            // keys in tokens
            var token = matches[i].replace(/[{}0-9]/g, '');
            var position;

            // figure out what $# to use ($1, $2)
            if (positions[matches[i]]) {
                position = positions[matches[i]];         // if the token already has a position then use that
            } else {
                // token doesn't have a position so increment the next position
                // and record this token's position
                next_position += 1;
                position = next_position;
                positions[matches[i]] = position;
            }

            if (tokens[token]) {
                replacement += str.substr(0, str.indexOf(matches[i])) + '$' + position;
                str = str.substr(str.indexOf(matches[i]) + matches[i].length);
            }
        }

        replacement += str;

        return replacement;
    };

    /**
     * Adds a bbcode to the list
     */
    me.addBBCode = function (bbcode_match, bbcode_tpl) {
        // add the regular expressions and templates for bbcode to html
        bbcode_matches.push(_getRegEx(bbcode_match));
        html_tpls.push(_getTpls(bbcode_tpl));

        // add the regular expressions and templates for html to bbcode
        html_matches.push(_getRegEx(bbcode_tpl));
        bbcode_tpls.push(_getTpls(bbcode_match));
    };

    /**
     * Turns all of the added bbcodes into html
     */
    me.bbcodeToHtml = function (str) {
        var nrbbcmatches = bbcode_matches.length;
        var i = 0;

        for (; i < nrbbcmatches; i += 1) {
            str = str.replace(bbcode_matches[i], html_tpls[i]);
        }

        return str;
    };

    /**
     * Turns html into bbcode
     */
    me.htmlToBBCode = function (str) {
        var nrhtmlmatches = html_matches.length;
        var i = 0;

        for (; i < nrhtmlmatches; i += 1) {
            str = str.replace(html_matches[i], bbcode_tpls[i]);
        }

        return str;
    }

    /**
     * Quote regular expression characters plus an optional character
     * taken from phpjs.org
     */
    function preg_quote(str, delimiter) {
        return (str + '').replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
    }

    // adds BBCodes and their HTML
    me.addBBCode('[b]{TEXT}[/b]', '<strong>{TEXT}</strong>');
    me.addBBCode('[i]{TEXT}[/i]', '<em>{TEXT}</em>');
    me.addBBCode('[u]{TEXT}[/u]', '<span style="text-decoration:underline;">{TEXT}</span>');
    me.addBBCode('[s]{TEXT}[/s]', '<span style="text-decoration:line-through;">{TEXT}</span>');

    me.addBBCode('[link={URL}]{TEXT}[/link]', '<a href="{URL}" title="link" target="_blank">{TEXT}</a>');

    me.addBBCode('[url={URL}]{TEXT}[/url]', '<a href="{URL}" title="link" target="_blank">{TEXT}</a>');
    me.addBBCode('[url]{URL}[/url]', '<a href="{URL}" title="link" target="_blank">{URL}</a>');
    me.addBBCode('[url={LINK}]{TEXT}[/url]', '<a href="{LINK}" title="link" target="_blank">{TEXT}</a>');
    me.addBBCode('[url]{LINK}[/url]', '<a href="{LINK}" title="link" target="_blank">{LINK}</a>');

    me.addBBCode('[img={URL} width={NUMBER1} height={NUMBER2}]{TEXT}[/img]', '<img src="{URL}" width="{NUMBER1}" height="{NUMBER2}" alt="{TEXT}" />');
    me.addBBCode('[img={URL}]', '<img src="{URL}" alt="{URL}" />');
    me.addBBCode('[img]{URL}[/img]', '<img src="{URL}" alt="{URL}" />');
    me.addBBCode('[img={LINK} width={NUMBER1} height={NUMBER2}]{TEXT}[/img]', '<img src="{LINK}" width="{NUMBER1}" height="{NUMBER2}" alt="{TEXT}" />');
    me.addBBCode('[img]{LINK}[/img]', '<img src="{LINK}" alt="{LINK}" />');
    me.addBBCode('[color=COLOR]{TEXT}[/color]', '<span style="{COLOR}">{TEXT}</span>');
    me.addBBCode('[highlight={COLOR}]{TEXT}[/highlight]', '<span style="background-color:{COLOR}">{TEXT}</span>');
    me.addBBCode('[quote="{TEXT1}"]{TEXT2}[/quote]', '<div class="quote"><cite>{TEXT1}</cite><p>{TEXT2}</p></div>');
    me.addBBCode('[quote]{TEXT}[/quote]', '<cite>{TEXT}</cite>');
    me.addBBCode('[blockquote]{TEXT}[/blockquote]', '<blockquote>{TEXT}</blockquote>');
};
