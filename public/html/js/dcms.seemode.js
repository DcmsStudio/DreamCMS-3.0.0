/**
 * Created by marcel on 30.05.14.
 */
var opener, notifyTimeout;
var interval;
var authKey = null;
var authSite = null;
var interval = null, notifyTimeout;
var clickAnalyserOn = false;
var SeemodeCookie = new CookieRegistry;

var tmp = cookiePrefix;
tmp = tmp.replace(/_fe$/, '_be');
SeemodeCookie.initialize(tmp + '_registry');
function openTab() {
    "use strict";

}
var Core = {
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
    addEvent: function () {


    },
    updateContentHeight: function () {
    },
    updateViewPort: function () {
    },
    disablePanelScrollbar: function () {
    },
    enablePanelScrollbar: function () {
    },
    setDirty: function (fromForm) {
        "use strict";
        SeemodeEdit.setDirty();
    },
    setSaving: function (fromForm) {
    },

    resetDirty: function (fromForm, btn) {
        "use strict";
        SeemodeEdit.removeDirty();
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
    },

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

    getToolbar: function () {
        return $('#seemode-header');
    },
    getActiveStatusbar: function () {
        return null;
    },
    getStatusbar: function () {
        return null;
    },
    getContent: function () {
        return $('#seemode-panel-content');
    },
    getTabContent: function () {
        return $('#seemode-panel-content');
    },
    getActiveTabHash: function () {
        return 'seemode-panel-content';
    },
    getContentTabs: function () {
        var hash = Core.Tabs.getActiveTabHash();

        if (hash) {
            return $('#content-tabs-' + hash + ':visible');
        }

        return false;
    },
    refreshTab: function (callback) {

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
        for (var x = 0; x < boxlength; ++x) {
            //contentObj.find('div.postbox').each(function () {

            var b = $(box[x]);
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
        }
        //});
    },

};


Core.Tabs = {
    /**
     *
     */
    toggleFileSelectorPanel: function (show) {
        var self = this;
        var hash = Core.getActiveTabHash();
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
    addFileSelector: function (hash) {
        var self = this, container = $('#content-' + hash);
        if (container.attr('fm') ) {
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
};



var SeemodeEdit = {
    isDirty: false,
    actions: [],
    fields: [],
    panel: null,
    controlBar: null,
    panelEdit: null,
    panelEditControl: null,
    leftSide: null,
    contentSide: null,
    inited: false,
    authKey: null,
    settings: {},
    panelMinHeight: 300,
    clickAnalyserOn: false,

    init: function (opts) {
        var self = this;

        opts.isSeemode = true;

        this.settings = $.extend({}, opts);

        Config.init(opts);

        this.prepareTinyMceSetup(opts);

        this.getAuthKey();

        this.controlBar = $('#seemode-panel-control');
        this.panelEdit = $('#seemode-main');
        this.panelEditControl = $('#seemode-header');
        this.leftSide = $('#seemode-panel-left');
        this.contentSide = $('#seemode-panel-content');

        Win.windowID = 'seemode-panel-content';

        this.buildBaseButton('openclose');
        this.buildBaseButton('config');
        this.buildBaseButton('spacer');
        this.buildBaseButton('clearcache');
        this.buildBaseButton('clearfullcache');
        this.buildBaseButton('clearpagecache');
        this.buildBaseButton('spacer');
        this.buildBaseButton('debug');
        this.buildBaseButton('firewall');
        this.buildBaseButton('spacer');
        this.buildBaseButton('clickanalyser');

        this.prepareSeemodeItems();

        window.onbeforeunload = function (e) {
            if (self.isDirty) {
                message = cmslang.form_dirty;
                if (typeof e == 'undefined') {
                    e = window.event;
                }

                if (e) {
                    e.returnValue = message;
                }

                return message;
            }
        };
    },


    getAuthKey: function () {
        var self = this, tmpAuthKey = SeemodeCookie.get('loginpermanet');

        if (tmpAuthKey !== null && tmpAuthKey !== false && tmpAuthKey != '') {
            self.authKey = tmpAuthKey;
            Cookie.set('loginpermanet', self.authKey);
            SeemodeCookie.set('loginpermanet', self.authKey);
        }

        if (self.authKey !== null) {
            return;
        }

        // check authKey
        $.ajax({
            type: "POST",
            url: systemUrl + '/index.php',
            'data': {'cp': 'main', 'getAuthKey': 1, 'ajax': 1},
            timeout: 4000,
            dataType: 'json',
            cache: false,
            async: false,
            success: function (data) {
                if (Tools.responseIsOk(data)) {
                    self.authKey = data.authKey;
                    Cookie.set('loginpermanet', self.authKey);
                    SeemodeCookie.set('loginpermanet', self.authKey);
                }
                else {
                    Cookie.set('loginpermanet', '');
                    SeemodeCookie.set('loginpermanet', '');
                    self.authKey = null;
                    if (typeof data.msg != 'undefined') {
                        console.log(data.msg);
                    }
                }
            }
        });
    },





    buildBaseButton: function (type) {
        var btn, self = this;

        switch (type.toLowerCase()) {
            case 'spacer':
                btn = $('<div class="base-spacer"><span></span></div>');
                break;
            case 'openclose':
                btn = $('<div class="base-btn toggle-open"><span></span></div>').attr('title', 'Dokument bearbeiten');
                btn.hide();
                btn.click(function () {
                    if (!$(this).hasClass('active')) {
                        $(this).addClass('active');
                        $('.current-edit').addClass('seemode-editing');
                        self.panelEdit.show();
                    }
                    else {
                        $(this).removeClass('active');
                        $('.current-edit').removeClass('seemode-editing');
                        self.panelEdit.hide();
                    }
                });
                break;
            case 'config':
                btn = $('<div class="base-btn config"><span></span></div>').attr('title', 'Konfiguration');
                btn.click(function () {
                    var _self = this, stop = false;

                    if (self.isDirty) {
                        Check = confirm(cmslang.form_dirty);
                        if (Check != true) {
                            stop = true;
                        }
                    }

                    if (!stop) {
                        // send Rollback only if is dirty and content editing mode
                        if ($('body').attr('rollback') && self.isDirty) {
                            var postData = Tools.convertUrlToObject('admin.php?' + $('body').attr('rollbackUrl'));
                            postData.ajax = true;
                            postData.seemodePopup = true;
                            postData.transrollback = true;
                            postData.authKey = self.authKey;






                            $.post('admin.php', postData, function () {
                                $('body').removeAttr('rollback').removeAttr('rollbackUrl');
                            });
                        }

                        // Send unlock dokument??? hmmm


                        self.controlBar.find('.toggle-open,.toggle-close').removeClass('active').removeClass('toggle-close').addClass('toggle-open').hide();

                        if (self.isDirty) {
                            self.removeDirty();
                        }

                        if (!$(this).hasClass('active')) {

                            if (!self.leftSide.find('.config-sections').length) {
                                self.callAjax('admin.php?adm=settings', false, function (data) {
                                    $(_self).addClass('active');

                                    self.prepareSettings(data, function () {
                                        self.controlBar.find('.toggle-open').hide();
                                        self.panelEdit.show();
                                        self.evalAjaxScript(data);
                                    });
                                });
                            }
                            else {
                                $(_self).addClass('active');
                                self.panelEdit.show();
                            }

                        }
                        else {
                            $(this).removeClass('active');
                            self.panelEdit.hide();
                        }
                    }
                });
                break;
            case 'clearcache':
                btn = $('<div class="base-btn clearcache"><span></span></div>').attr('title', 'Quick-Cache leeren');
                btn.click(function () {
                    self.clearCache('short');
                });
                break;
            case 'clearfullcache':
                btn = $('<div class="base-btn clearcache-full"><span></span></div>').attr('title', 'Ganzen Cache leeren');
                btn.click(function () {
                    self.clearCache('full');
                });
                break;
            case 'clearpagecache':
                btn = $('<div class="base-btn pagecache"><span></span></div>').attr('title', 'Seiten-Cache leeren');
                btn.click(function () {
                    self.clearCache('pagecache');
                });
                break;
            case 'debug':
                btn = $('<div class="base-btn debug"><span></span></div>');
                if (Config.get('debugger')) {
                    btn.attr('title', 'Debbugger aktivieren');
                }
                else {
                    btn.attr('title', 'Debbugger deaktivieren').addClass('off');
                }


                btn.click(function () {
                    var xself = this;


                    self.callAjax('admin.php?adm=dashboard&action=switchdebug', false, function () {
                        if (Config.get('debugger')) {
                            Config.set('debugger', false);
                            $(xself).addClass('off').attr('title', 'Debbugger aktivieren');
                            self.notify('Debbugger wurde deaktiviert');
                            notifyTimeout = setTimeout(function () {
                                self.unNotify();
                            }, 3000);
                        }
                        else {
                            Config.set('debugger', true);
                            $(xself).removeClass('off').attr('title', 'Debbugger deaktivieren');
                            self.notify('Debbugger wurde aktiviert');
                            notifyTimeout = setTimeout(function () {
                                self.unNotify();
                            }, 3000);
                        }
                    });
                });
                break;
            case 'firewall':
                btn = $('<div class="base-btn firewall"><span></span></div>');
                if (Config.get('firewall')) {
                    btn.attr('title', 'Firewall aktivieren');
                }
                else {
                    btn.attr('title', 'Firewall deaktivieren').addClass('off');
                }


                btn.click(function () {
                    var xself = this;
                    self.callAjax('admin.php?adm=dashboard&action=switchfirewall', false, function () {
                        if (Config.get('firewall')) {
                            Config.set('firewall', false);
                            $(xself).addClass('off').attr('title', 'Firewall aktivieren');
                            self.notify('Firewall wurde deaktiviert');
                            notifyTimeout = setTimeout(function () {
                                self.unNotify();
                            }, 3000);
                        }
                        else {
                            Config.set('firewall', true);
                            $(xself).removeClass('off').attr('title', 'Firewall deaktivieren');
                            self.notify('Firewall wurde aktiviert');
                            notifyTimeout = setTimeout(function () {
                                self.unNotify();
                            }, 3000);
                        }
                    });
                });
                break;
            case 'clickanalyser':
                btn = $('<div class="base-btn clickanalyser"><span></span></div>').attr('title', 'Klick-Analyse anzeigen');
                btn.click(function () {
                    if (!$(this).hasClass('active')) {
                        $(this).addClass('active');
                        self.clickAnalyserOn = true;

                        if (typeof analyseClicks != 'function') {
                            $.getScript(systemUrl + '/html/js/seemode.analyseclicks.js?_' + cookiePrefix, function () {
                                analyseClicks();
                            });
                        }
                        else {
                            analyseClicks();
                        }

                        $(this).attr('title', 'Klick-Analyse ausblenden');
                    }
                    else {
                        self.clickAnalyserOn = false;

                        $(this).removeClass('active');
                        $(this).attr('title', 'Klick-Analyse anzeigen');
                        $(document).find('.analysePoint').remove();

                    }
                });
                break;
        }


        if (btn) {
            this.controlBar.append(btn);
        }
    },

    prepareLiveTinyMCEData: function (element, contentid, controller) {

        if (contentid) {
            var value = $($.parseHTML($(element).html()));
            var tag = $('#seemode-var-' + contentid + '-' + $(element).attr('fieldname'));

            if (tag.attr('noimages') > 0) {
                tag.empty().append($(element).html());
                tag.find('img').each(function () {
                    var parentTag = $(this).parent().get(0).tagName.toLowerCase();
                    var parentText = $(this).parent().text().trim();
                    if (parentTag == 'p' && parentText == '') {
                        $(this).parent().remove();
                    }
                    else {
                        $(this).remove();
                    }
                });
            }
            else {
                tag.empty().append($(element).html());
            }

            if ($('.footnotes').length == 1) {
                this.prepareFootnotes(tag, $('.footnotes'));
            }

            if (tag.attr('allowedtags')) {
                var allowedtags = tag.attr('allowedtags'), allowed = allowedtags.split(','), allow = [];
                for (var i = 0; i < allowed.length; ++i) {
                    if (allowed[i] != '') {
                        allow.push('<' + allowed[i] + '>');
                    }
                }

                if (allow.length > 0) {
                    tag.html(this.stripTags(tag.get(0).innerHTML, allow.join('')));
                }
            }

            if (tag.attr('length') > 0) {
                tag.html(this.truncateHtml(tag.get(0).innerHTML, tag.attr('length')));
            }

        }
    },
    prepareLiveChangeData: function (e, element, contentid, controller) {

        if (contentid) {
            var value = $($.parseHTML($(e.target).val()));
            var tag = $('#seemode-var-' + contentid + '-' + $(element).attr('name'));

            if (tag.attr('noimages') > 0) {
                tag.empty().append($(value)).find('img').each(function () {
                    var parentTag = $(this).parent().get(0).tagName.toLowerCase();
                    var parentText = $(this).parent().text().trim();
                    if (parentTag == 'p' && parentText == '') {
                        $(this).parent().remove();
                    }
                    else {
                        $(this).remove();
                    }
                });
            }
            else {
                tag.empty().append($(value));
            }

            if ($('.footnotes').length == 1) {
                this.prepareFootnotes(tag, $('.footnotes'));
            }

            if (tag.attr('allowedtags')) {
                var allowedtags = tag.attr('allowedtags'), allowed = allowedtags.split(','), allow = [];
                for (var i = 0; i < allowed.length; ++i) {
                    if (allowed[i] != '') {
                        allow.push('<' + allowed[i] + '>');
                    }
                }

                if (allow.length > 0) {
                    tag.html(this.stripTags(tag.get(0).innerHTML, allow.join('')));
                }
            }

            if (tag.attr('length') > 0) {
                tag.html(this.truncateHtml(tag.get(0).innerHTML, tag.attr('length')));
            }
        }

    },
    bindFormEditEvents: function (contentid, controller) {
        var self = this;


        var to, to1;
        this.contentSide.find('input,textarea,select').unbind('change').on('change', function (e) {
            clearTimeout(to1);

            var s = this;
            to1 = setTimeout(function () {
                self.prepareLiveChangeData(e, s, contentid, controller);
                self.setDirty();
            }, 200);
        });

        this.contentSide.find('input,textarea').unbind('keyup').on('keyup', function (e) {
            if (e.keyCode < 16 || e.keyCode > 40 || e.keyCode >= 93 && e.keyCode <= 111) {
                clearTimeout(to1);
                clearTimeout(to);

                var s = this;
                to = setTimeout(function () {
                    self.prepareLiveChangeData(e, s, contentid, controller);
                    self.setDirty();
                }, 200);

            }
        });
    },

    bindCfgItems: function(){
        var self = this;
        this.leftSide.find('.cfg-btn').each(function () {
            var cfgItem = $(this);
            $(this).parents('li:first').unbind().click(function () {

                self.contentSide.mask('Laden...');
                cfgItem.parents('ul:last').find('.cfg-btn.active').removeClass('active');
                cfgItem.addClass('active');
                self.callAjax(cfgItem.attr('data-url'), false, function (data) {
                    if (data.toolbar) {
                        self.panelEditControl.empty().append(data.toolbar);
                    }

                    self.contentSide.empty().append($('<div id="content-'+ self.contentSide.attr('id') +'"></div>').append( data.maincontent));
                    self.evalAjaxScript(data);
                    self.initDocument();

                    self.contentSide.find('h3,h2,h1').remove();
                    self.contentSide.unmask();
                });
            })
        });

    },
    prepareSettings: function (data, callback) {
        var self = this;
        if (data.toolbar) {
            this.panelEditControl.empty().append(data.toolbar);
        }


        this.panelEdit.css({visible: 'hidden'}).show();
        var minHeight = this.panelEdit.height();
        this.panelEdit.css({visible: ''}).hide();
        if (data.maincontent) {

            this.contentSide.empty().append( $('<div id="content-'+ this.contentSide.attr('id') +'"></div>').append( data.maincontent) );
            this.contentSide.find('.tabcontainer').remove();
            this.leftSide.empty();
            var contain = $('<div class="config-sections">');
            this.leftSide.append(contain);
            var ul = $('<ul>');
            this.contentSide.find('.cfg-group').each(function () {

                var label = $(this).find('>div>:first-child').text();
                var li = $('<li>').append($('<span>').append(label).append('<em/>'));
                var _sub = $('<ul>').hide();
                $(this).find('.cfg-btn').each(function () {
                    _sub.append($('<li>').append($(this)));
                });
                li.append(_sub);
                ul.append(li);
            });

            ul.find('.cfg-icon').each(function () {
                var src = $(this).unbind().find('img:first').attr('src');
                if (src) {
                    $(this).css({background: 'url(' + src + ')'}).find('img:first').remove();
                }

            })

            ul.find('.cfg-btn').each(function () {
                var cfgItem = $(this);
                $(this).parents('li:first').unbind().click(function () {

                    self.contentSide.mask('Laden...');
                    cfgItem.parents('ul:last').find('.cfg-btn.active').removeClass('active');
                    cfgItem.addClass('active');
                    self.callAjax(cfgItem.attr('data-url'), false, function (data) {
                        if (data.toolbar) {
                            self.panelEditControl.empty().append(data.toolbar);
                        }

                        self.contentSide.empty().append($('<div id="content-'+ self.contentSide.attr('id') +'"></div>').append( data.maincontent));
                        self.evalAjaxScript(data);
                        self.initDocument();

                        self.bindCfgItems();
                        self.contentSide.find('h3,h2,h1').remove();
                        self.contentSide.unmask();
                    });
                })
            });
            contain.append(ul);
            var firstUl = this.leftSide.find('ul:eq(0)');
            firstUl.find('>li').each(function () {
                $(this).on('click', function () {
                    if (!$(this).hasClass('open')) {
                        firstUl.find('>li.open').removeClass('open').find('ul:first').slideUp(200);
                        $(this).addClass('open').find('ul:first').slideDown(200);
                    }

                });
            });
            this.contentSide.empty();
        }

        self.controlBar.find('.toggle-open').addClass('active').show();
        var h = parseInt(self.panelEditControl.outerHeight(true), 10);
        var height = minHeight;
        self.contentSide.css({height: height - h});
        self.leftSide.css({height: height - h});
        self.panelEdit.css({height: height});

        self.initDocument();
        //self.bindFormEditEvents();

        if (callback) {
            callback();
        }
    },

    getPanelEditHeight: function ()
    {
        return this.panelEdit.outerHeight();
    },
    addLoading: function (message) {
        var winH = $(document).height(), winW = $(document).width();
        if (!$('#seemode-loading-page').length)
        {
            var mask = $('<div class="loding-mask" id="seemode-loading-page"></div>').css({zIndex: 99990}).hide();
            mask.appendTo($('body'));
            var maskLabel = $('<div class="loding-mask-label" id="seemode-loading-pagelabel"><span></span></div>').css({zIndex: 99991}).hide();
            maskLabel.appendTo($('body'));
            var labelHeight = 0, labelWidth = 0;
            if (message)
            {
                maskLabel.find('span').append(message);
                maskLabel.css({visible: 'hidden'}).show();
                labelHeight = maskLabel.outerHeight(true);
                labelWidth = maskLabel.outerWidth(true);
                maskLabel.hide().css({visible: ''});
            }

            var h = this.getPanelEditHeight();
            mask.height(parseInt(winH, 10) - h);
            mask.show();
            if (message)
            {
                maskLabel.css({left: winW / 2 - labelWidth / 2, top: h / 2 - labelHeight / 2});
                maskLabel.show();
            }
        }
        else
        {

            mask = $('#seemode-loading-page');
            maskLabel = $('#seemode-loading-pagelabel');
            maskLabel.find('span').empty().append(message);
            //   maskLabel.css({visible: 'hidden'}).show();
            labelHeight = maskLabel.outerHeight(true);
            labelWidth = maskLabel.outerWidth(true);
            //   maskLabel.hide().css({visible: ''});
            var h = this.getPanelEditHeight();
            mask.height(parseInt(winH, 10) - h);
            mask.show();
            if (message)
            {
                maskLabel.css({left: winW / 2 - labelWidth / 2, top: h / 2 - labelHeight / 2}).show();
            }
        }
    },
    removeLoading: function () {
        $('#seemode-loading-page,#seemode-loading-pagelabel').hide();
    },
    evalAjaxScript: function(data) {
        if ($(data.maincontent).filter('script').length) {
            Tools.eval($(data.maincontent));
        }
    },
    getContentControls: function (params, callback) {
        var self = this;
        $.post('index.php', {getContentTrans: true}, function (data0) {
            if (Tools.responseIsOk(data0))
            {
                params += '&ajax=1&seemodePopup=1&authKey=' + self.authKey;
                params += '&settranslation=' + data0.code;


                self.callAjax('admin.php', params, function(data) {
                    if (Tools.responseIsOk(data))
                    {
                        if (callback)
                        {
                            callback(data);
                        }
                    }
                    else
                    {
                        console.log([data]);
                        self.removeLoading();

                        if (data.msg) {
                            self.notify(data.msg);
                            notifyTimeout = setTimeout(function () {
                                self.unNotify();
                            }, 3000);
                        }
                    }

                });
            }
        });
    },
    getBuildData: function (contentid, controller, callback)
    {
        var self = this;
        if (!this.actions.length)
        {
            return;
        }

        this.getContentControls('adm=' + controller + '&' + self.actions[contentid].editurl.replace('%s', contentid), function (data) {

            if (data.toolbar)
            {
                self.panelEditControl.empty().append(data.toolbar);
            }

            self.panelEdit.css({visible: 'hidden'}).show();
            var minHeight = self.panelEdit.height();
            self.panelEdit.css({visible: ''}).hide();
            if (data.maincontent)
            {
//var html = $.parseHTML(data.maincontent)

                self.contentSide.empty().append( $('<div id="content-'+ self.contentSide.attr('id') +'"></div>').append( data.maincontent) );

                self.contentSide.prepend(self.contentSide.find('.tabcontainer'));
                self.leftSide.empty();
                var sections = [];
                var i = 0;
                self.contentSide.find('fieldset').each(function (x) {
                    var fs = $(this);
                    if ($(this).find('legend').length >= 1)
                    {
                        var el = $('<div>').attr('id', 'field-' + i).addClass('section');
                        if (i > 0)
                        {
                            el.hide();
                        }

                        $(this).find('legend:first').each(function ()
                        {
                            sections.push($(this).text());
                        });
                        i++;
                        $(fs).wrap(el);
                    }
                });

                if (sections.length > 0)
                {
                    var contain = $('<div class="edit-sections">');
                    for (var x = 0; x < sections.length; ++x)
                    {
                        contain.append($('<div class="section-item" rel="field-' + x + '">').append(sections[x]));
                    }

                    self.leftSide.append(contain);
                    contain.find('.section-item:eq(0)').addClass('active');
                    contain.find('.section-item').click(function () {
                        $(this).parent().find('.active').removeClass('active');
                        $(this).addClass('active');
                        self.contentSide.find('.section:visible').hide();
                        self.contentSide.find('#' + $(this).attr('rel')).show();
                    });
                }
                self.evalAjaxScript(data);
            }

            $('body').attr('contentid', contentid).attr('modul', controller);

            if (data.rollback === true)
            {

                $('body').attr('rollback', true).attr('rollbackUrl', 'adm=' + controller + '&' + self.actions[contentid].editurl.replace('%s', contentid));

                if (data.contentlockaction)
                {
                    $('body').attr('lockaction', data.contentlockaction);
                }
            }



            self.controlBar.find('.toggle-open').addClass('active').show();
            var h = parseInt(self.panelEditControl.outerHeight(true), 10);
            var height = minHeight;


            self.contentSide.find('textarea').attr('contentid', contentid).attr('modul', controller);
            self.contentSide.find('textarea').each(function () {
                if ($(this).hasClass('tinymce-editor'))
                {
                    var liveTiny = $('<div class="live-tiny-mcecontent" style="display:none"/>').attr('modul', controller).attr('contentid', contentid).attr('fieldname', $(this).attr('name')).attr('id', $(this).attr('id') + '-hidden');

                    liveTiny.insertAfter($(this));
                }
            });

            self.contentSide.css({height: height - h});
            self.leftSide.css({height: height - h});
            self.panelEdit.css({height: height});

            if (callback)
            {
                self.initDocument();
                //self.initTinyMCE();
                callback();
            }
        });
    },




    initDocument: function() {

        var act;
        this.contentSide.find('.tabcontainer li').each(function(){
            if ($(this).hasClass('actTab')) {
                act = $(this);
            }
        });

        if (!act) {
            this.contentSide.find('.tabcontainer li').each(function(i){
                var id = $(this).attr('id').slice(4);
                if (i == 0) {
                    $(this).addClass('actTab');
                    $('#tc'+id).show();
                }
                else {
                    $('#tc'+id).hide();
                }
            });

        }
        else {
            this.contentSide.find('.tabcontainer li').each(function(){
                var id = $(this).attr('id').slice(4);
                if ($(this).hasClass('actTab')) {
                    $('#tc'+id).hide();
                }
                else {
                    $('#tc'+id).show();
                }
            });
        }

        this.contentSide.find('.tabcontainer li').unbind().click(function() {
            var current = $(this).parent().find('li.actTab');
            var currentid = current.attr('id').slice(4);

            current.removeClass('actTab').addClass('defTab');
            $(this).removeClass('defTab').addClass('actTab');

            $('#tc'+ currentid).hide();
            $('#tc'+ $(this).attr('id').slice(4) ).show();

        });



        Core.preparePostColumn(this.contentSide);
        Bootstraper.init(this.contentSide, null, Win.windowID);
        this.initTinyMCE();
    },














    prepareSeemodeItems: function() {
        var self = this, actions = [], fields = [];
        $('div.seemode-item').each(function () {

            var item = $(this);
            var contentid = item.attr('contentid'), modul = item.attr('modul');
            if (contentid > 0 && modul != '')
            {
                var container = $('<div class="seemode-content"/>');
                var itemButton = $('<div class="seemode-button"/>').append('<span>Inhalt</span>');
                var seemActions = $('<div class="seemode-actions"/>');
                container.append(itemButton);
                if (item.attr('edit'))
                {
                    seemActions.append('<span class="edit" title="Edit"></span>');
                }

                if (item.attr('publish') && item.attr('state') > 0)
                {
                    seemActions.append('<span class="publish" title="Change Publishing to Offline"></span>');
                    item.addClass('online-content');
                }
                else if (item.attr('publish') && item.attr('state') <= 0)
                {
                    seemActions.append('<span class="publish off" title="Change Publishing to Online"></span>');
                    item.addClass('offline-content');
                }

                if (item.attr('delete'))
                {
                    seemActions.append('<span class="delete" title="Edit"></span>');
                }


                actions[contentid] = {contentid: contentid, controller: modul, publishurl: (item.attr('publish') || null), editurl: (item.attr('edit') || null), deleteurl: (item.attr('delete') || null)};
                item.find('.seemode-var').each(function () {

                    var fieldName = $(this).attr('id').replace('seemode-var-', '');
                    $(this).attr('id', 'seemode-var-' + contentid + '-' + fieldName);
                    if (fieldName) {

                        if (!fields[contentid]) {
                            fields[contentid] = [];
                        }
                        if (!fields[contentid][modul]) {
                            fields[contentid][modul] = [];
                        }
                        fields[contentid][modul].push(fieldName);
                    }
                });



                seemActions.find('span').each(function () {

                    var btn = $(this);
                    if (btn.hasClass('edit')) {
                        btn.on('click', function () {

                            var stop = false;

                            if (self.isDirty)
                            {
                                Check = confirm(cmslang.form_dirty);
                                if (Check != true)
                                {
                                    stop = true;
                                }
                            }

                            if (!stop)
                            {
                                // send Rollback only if is dirty and content editing mode
                                if ($('body').attr('rollback') && self.isDirty)
                                {
                                    self.sendDokumentRollback();
                                }

                                // Send unlock dokument??? hmmm

                                self.addLoading('Hole daten...');

                                if (self.isDirty)
                                {
                                    self.removeDirty();
                                }


                                self.controlBar.find('.active').removeClass('active');


                                var btn = $(this);
                                var itm = btn.parents('.seemode-item:first');

                                $('.seemode-editing').removeClass('seemode-editing current-edit');
                                self.getBuildData(itm.attr('contentid'), itm.attr('modul'), function () {

                                    if (!self.panelEdit.is(':visible')) {
                                        $('.toggle-open', self.controlBar).removeClass('toggle-open').addClass('toggle-close').addClass('active');
                                        self.panelEdit.show();
                                    }

                                    self.bindFormEditEvents(contentid, modul);
                                    itm.addClass('seemode-editing current-edit');

                                    self.removeLoading();
                                });

                            }
                        });

                    }

                    if (btn.hasClass('delete'))
                    {
                        btn.on('click', function () {

                            var btn = $(this);
                            var itm = btn.parents('.seemode-item:first');
                            self.addLoading('löschen...');


                            $.get('admin.php?adm=' + itm.attr('modul') + '&' + itm.attr('delete').replace('%s', itm.attr('contentid')), function (data) {
                                self.removeLoading();
                                if (responseIsOk(data))
                                {
                                    self.notify('Inhalt wurde gelöscht');

                                    itm.stop(true).animate({height: 0, opacity: '0'}, 300, function () {
                                        $(this).remove();
                                    });
                                }
                            });
                        });
                    }

                    if (btn.hasClass('publish'))
                    {
                        btn.on('click', function () {

                            var btn = $(this);
                            var itm = btn.parents('.seemode-item:first');
                            self.addLoading('Bitte warten...');
                            if (btn.hasClass('off'))
                            {
                                btn.attr('title', 'Change Publishing to Offline');
                            }
                            else
                            {
                                btn.attr('title', 'Change Publishing to Online');
                            }


                            self.callAjax('admin.php?seemodePopup=1&authKey='+self.authKey+'&adm=' + itm.attr('modul') + '&' + itm.attr('publish').replace('%s', itm.attr('contentid')), false, function(_data) {
                                self.removeLoading();
                                if (responseIsOk(_data))
                                {
                                    if (_data.msg == 1)
                                    {
                                        btn.removeClass('off');
                                        itm.addClass('online-content').removeClass('offline-content');
                                        self.notify('Inhalt wurde aktiviert');

                                    }
                                    else
                                    {
                                        btn.addClass('off');
                                        itm.removeClass('online-content').addClass('offline-content');
                                        self.notify('Inhalt wurde deaktiviert');

                                    }


                                }
                                else
                                {
                                    alert(_data.msg);
                                }

                            })

                        });
                    }
                });

                container.append(seemActions);
                $(this).prepend(container);
            }

        });
        //  $('.seemode-button').unbind();
        // $('.seemode-button').prev().unbind();
        $('.seemode-button').click(function () {

            if (!$(this).hasClass('active'))
            {
                $(this).next().stop();
                $(this).addClass('active').next().slideToggle(200, function () {
                    var container = $(this);
                    $(this).parent().bind('mouseleave', function () {
                        $(this).unbind('mouseleave');
                        container.hide(0, function () {
                            $(this).prev().removeClass('active');
                        });
                    });
                });
            }
            else
            {
                $(this).removeClass('active').next().slideToggle(200);
                $(this).parent().unbind('mouseleave');
            }


        });
        this.actions = actions;
        this.fields = fields;

    },

    getAjaxData: function (url, postData, callback, type)
    {
        if ( !postData) {
            postData = Core.convertGetToPost(url);
        }

        if (typeof type !== 'string') {
            type = 'json';
        }

        $.ajax({
            type: 'POST',
            url: 'admin.php',
            data: postData,
            cache: false,
            async: false,
            global: false,
            dataType: type,
            success: function (data) {
                if ( callback ) {
                    callback(data);
                }
            }
        });
    },
    /**
     *
     * @param url
     * @param postData
     * @param callback
     * @param type
     */
    callAjax: function (url, postData, callback, type) {
        this.getAjaxData(url, postData, callback, type);
        /*
        if (typeof type !== 'string') {
            type = 'json';
        }

        if (typeof postData == 'undefined' || postData === false || postData === null || postData === '') {
            url += '&ajax=1&seemodePopup=1&authKey=' + this.authKey;
            $.get(url, function (data) {

                if (typeof callback === 'function') {
                    callback(data);
                }
            }, type);
        }
        else {
            postData.seemodePopup = true;
            postData.authKey = this.authKey;

            $.post(url, postData, function (data) {

                if (typeof callback === 'function') {
                    callback(data);
                }
            }, type);
        }
        */
    },

    sendDokumentRollback: function (close) {
        var self = this, postData = Tools.convertUrlToObject('admin.php?' + $('body').attr('rollbackUrl'));
        postData.ajax = true;
        postData.transrollback = true;
        $.post('admin.php', postData, function () {

            if (close) {
                var modul = $('body').attr('modul');
                var contentid = $('body').attr('contentid');
                var lockaction = $('body').attr('lockaction');

                if (contentid && modul) {
                    self.sendUnlock(modul, lockaction, contentid);
                }
            }

            self.controlBar.find('.toggle-open,.toggle-close').removeClass('active').removeClass('toggle-close').addClass('toggle-open').hide();
            self.panelEdit.hide();
            self.panelEditControl.empty();
            self.contentSide.empty();

            $('body').removeAttr('rollback').removeAttr('rollbackUrl').removeAttr('contentid').removeAttr('modul').removeAttr('lockaction');
        });
    },
    sendRollback: function (rollbackUrl, callback) {
        var postData = Tools.convertUrlToObject(rollbackUrl);
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
                if (callback) {
                    callback();
                }
            }
        });
    },
    sendUnlock: function (modul, action, contentid, callback) {
        $.ajax({
            type: "POST",
            url: 'admin.php',
            'data': {
                action: 'unlock',
                unlock: true,
                modul: modul,
                modulaction: action,
                contentid: contentid
            },
            timeout: 10000,
            dataType: 'json',
            cache: false,
            async: false,
            success: function (data) {
                if (callback) {
                    callback();
                }
            }
        });
    },
    triggerFormSave: function (exit, data)
    {
        // unlock content
        if (exit && $('body').attr('rollback'))
        {
            var modul = $('body').attr('modul');
            var contentid = $('body').attr('contentid');
            var self = this, lockaction = $('body').attr('lockaction');

            if (contentid && modul)
            {
                this.sendUnlock(modul, lockaction, contentid);
            }
        }


        this.controlBar.find('.toggle-open,.toggle-close').removeClass('active').removeClass('toggle-close').addClass('toggle-open').hide();
        this.panelEdit.hide();
        this.panelEditControl.empty();
        this.contentSide.empty();

        $('body').removeAttr('rollback').removeAttr('rollbackUrl').removeAttr('contentid').removeAttr('modul').removeAttr('lockaction');
    },
    fdirty: false,
    setDirty: function () {
        if (!this.fdirty) {
            this.fdirty = true;
            $('#seemode-dirty').css({visible: 'hidden'}).show();
            var h = $('#seemode-dirty').outerHeight(true);
            $('#seemode-dirty').css({visible: '', top: 0 - h});
            $('#seemode-dirty').animate({
                top: 0
            }, {
                duration: 300
            });
        }
    },
    removeDirty: function () {
        var self = this;

        $('#seemode-dirty').animate({
            top: 0 - $('#seemode-dirty').outerHeight(true)
        }, {
            duration: 300,
            complete: function () {
                $(this).hide();
                self.fdirty = false;
            }
        });
    },

    nt: null,
    notify: function (message) {

        if ( message ) {
            clearTimeout(this.nt);
            if ($('#seemode_notifier').is(':visible')) {
                $('#seemode_notifier').css({opacity: '1'}).hide();
            }
            var self = this;

            $('#notifier', $('#seemode_notifier')).html(message).show();
            $('#seemode_notifier').stop(true).fadeIn(400, function() {
                self.nt = setTimeout(function(){ self.unNotify() }, 3000);
            });
        }

    },

    unNotify: function () {

        if ($('#seemode_notifier').is(':visible')) {
            $('#seemode_notifier').stop(true).show().fadeOut(800, function () {
                $('#seemode_notifier').css({opacity: '1'}).hide();
            });
        }
    },


    /**
     *
     */
    tinyMce4Setup: {

        menubar: false,
        selector: "div.inline-mce",
        theme: "modern",
        skin: "dcms",
        toolbar_items_size: "small",
        // entity_encoding: "raw",
        onchange_callback: "TinyCallback.onChangeHandler",
        language: "{language}",
        plugins: "{plugins}",
        content_css: "html/css/bootstrap/bootstrap.min.css,html/css/tinymce.css,html/css/subcols.css,html/css/subcols_extended.css{extraTemplateCss}",
        baseURL: '{url}/Vendor/tinymce4',
        script_url: '{url}/Vendor/tinymce4/tinymce.gzip.php',
        inline: true,
        add_unload_trigger: false,
        statusbar: true,
        inlinestatusbar: true,
        convert_urls: false,
        relative_urls: true,
        fix_list_elements: true,
        remove_trailing_brs: true,
        indent: true,
        indent_before: 'p,em,small,span,a,h1,h2,h3,h4,h5,h6,blockquote,div,title,style,pre,script,td,ul,li,area,table,thead,tfoot,tbody,tr,section,article,hgroup,aside,figure,option,optgroup,datalist',
        indent_after: 'p,em,small,span,a,h1,h2,h3,h4,h5,h6,blockquote,div,title,style,pre,script,td,ul,li,area,table,thead,tfoot,tbody,tr,section,article,hgroup,aside,figure,option,optgroup,datalist',
        style_formats_merge: true,
        style_formats: [
            {title: 'Headers', items: [
                {title: 'Header 1', block: 'h1'},
                {title: 'Header 2', block: 'h2'},
                {title: 'Header 3', block: 'h3'},
                {title: 'Header 4', block: 'h4'},
                {title: 'Header 5', block: 'h5'},
                {title: 'Header 6', block: 'h6'}
            ]},
            {title: 'Inline', items: [
                {title: 'Bold', icon: "bold", inline: 'strong'},
                {title: 'Italic', icon: "italic", inline: 'em'},
                {title: 'Underline', icon: "underline", inline: 'span', styles: {'text-decoration': 'underline'}},
                {title: 'Strikethrough', icon: "strikethrough", inline: 'span', styles: {'text-decoration': 'line-through'}},
                {title: 'Superscript', icon: "superscript", inline: 'sup'},
                {title: 'Subscript', icon: "subscript", inline: 'sub'},
                {title: 'Code', icon: "code", inline: 'code'}
            ]},
            {title: 'Blocks', items: [
                {title: 'Paragraph', block: 'p'},
                {title: 'Blockquote', block: 'blockquote'},
                {title: 'Div', block: 'div'},
                {title: 'Pre', block: 'pre'}
            ]},
            {title: 'Alignment', items: [
                {title: 'Left', icon: "alignleft", block: 'div', styles: {'text-align': 'left'}},
                {title: 'Center', icon: "aligncenter", block: 'div', styles: {'text-align': 'center'}},
                {title: 'Right', icon: "alignright", block: 'div', styles: {'text-align': 'right'}},
                {title: 'Justify', icon: "alignjustify", block: 'div', styles: {'text-align': 'justify'}}
            ]}
        ],

        // valid_children: '*[*]',
        valid_elements: ""
            + "@[accesskey|draggable|style|class|hidden|tabindex|contenteditable|id|title|contextmenu|lang|dir<ltr?rtl|spellcheck|"
            + "onabort|onerror|onmousewheel|onblur|onfocus|onpause|oncanplay|onformchange|onplay|oncanplaythrough|onforminput|onplaying|onchange|oninput|onprogress|onclick|oninvalid|onratechange|oncontextmenu|onkeydown|onreadystatechange|ondblclick|onkeypress|onscroll|ondrag|onkeyup|onseeked|ondragend|onload|onseeking|ondragenter|onloadeddata|onselect|ondragleave|onloadedmetadata|onshow|ondragover|onloadstart|onstalled|ondragstart|onmousedown|onsubmit|ondrop|onmousemove|onsuspend|ondurationmouseout|ontimeupdate|onemptied|onmouseover|onvolumechange|onended|onmouseup|onwaiting],"
            + "a[target<_blank?_self?_top?_parent|ping|media|href|hreflang|type"
            + "|rel<alternate?archives?author?bookmark?external?feed?first?help?index?last?license?next?nofollow?noreferrer?prev?search?sidebar?tag?up"
            + "],"
            + "abbr,"
            + "address,"
            + "area[alt|coords|shape|href|target<_blank?_self?_top?_parent|ping|media|hreflang|type|shape<circle?default?poly?rect"
            + "|rel<alternate?archives?author?bookmark?external?feed?first?help?index?last?license?next?nofollow?noreferrer?prev?search?sidebar?tag?up"
            + "],"
            + "article,"
            + "aside,"
            + "audio[src|preload<none?metadata?auto|autoplay<autoplay|loop<loop|controls<controls|mediagroup],"
            + "blockquote[cite],"
            + "body,"
            + "br,"
            + "button[autofocus<autofocus|disabled<disabled|form|formaction|formenctype|formmethod<get?put?post?delete|formnovalidate?novalidate|"
            + "formtarget<_blank?_self?_top?_parent|name|type<reset?submit?button|value],"
            + "canvas[width,height],"
            + "caption,"
            + "cite,"
            + "code,"
            + "col[span],"
            + "colgroup[span],"
            + "command[type<command?checkbox?radio|label|icon|disabled<disabled|checked<checked|radiogroup|default<default],"
            + "datalist[data],"
            + "dd,"
            + "del[cite|datetime],"
            + "details[open<open],"
            + "dfn,"
            + "div,"
            + "dl,"
            + "dt,"
            + "em/i,"
            + "embed[src|type|width|height],"
            + "eventsource[src],"
            + "fieldset[disabled<disabled|form|name],"
            + "figcaption,"
            + "figure,"
            + "footer,"
            + "form[accept-charset|action|enctype|method<get?post?put?delete|name|novalidate<novalidate|target<_blank?_self?_top?_parent],"
            + "-h1,-h2,-h3,-h4,-h5,-h6,"
            + "header,"
            + "hgroup,"
            + "hr,"
            + "iframe[name|src|srcdoc|seamless<seamless|width|height|sandbox],"
            + "img[alt=|src|ismap|usemap|width|height],"
            + "input[accept|alt|autocomplete<on?off|autofocus<autofocus|checked<checked|disabled<disabled"
            + "|form|formaction|formenctype|formmethod<get?put?post?delete|formnovalidate?novalidate|formtarget<_blank?_self?_top?_parent"
            + "|height|list|max|maxlength|min|multiple<multiple|name|pattern|placeholder|readonly<readonly|required<required"
            + "|size|src|step|type<hidden?text?search?tel?url?email?password?datetime?date?month?week?time?datetime-local?number?range?color"
            + "?checkbox?radio?file?submit?image?reset?button?value|width],"
            + "ins[cite|datetime],"
            + "kbd,"
            + "keygen[autofocus<autofocus|challenge|disabled<disabled|form|name],"
            + "label[for|form],"
            + "legend,"
            + "li[value],"
            + "mark,"
            + "map[name],"
            + "menu[type<context?toolbar?list|label],"
            + "meter[value|min|low|high|max|optimum],"
            + "nav,"
            + "noscript,"
            + "object[data|type|name|usemap|form|width|height],"
            + "ol[reversed|start],"
            + "optgroup[disabled<disabled|label],"
            + "option[disabled<disabled|label|selected<selected|value],"
            + "output[for|form|name],"
            + "-p,"
            + "param[name,value],"
            + "-pre,"
            + "progress[value,max],"
            + "q[cite],"
            + "ruby,"
            + "rp,"
            + "rt,"
            + "samp,"
            + "script[src|async<async|defer<defer|type|charset],"
            + "section,"
            + "select[autofocus<autofocus|disabled<disabled|form|multiple<multiple|name|size],"
            + "small,"
            + "source[src|type|media],"
            + "span,"
            + "-strong/b,"
            + "-sub,"
            + "summary,"
            + "-sup,"
            + "table,"
            + "tbody,"
            + "td[colspan|rowspan|headers],"
            + "textarea[autofocus<autofocus|disabled<disabled|form|maxlength|name|placeholder|readonly<readonly|required<required|rows|cols|wrap<soft|hard],"
            + "tfoot,"
            + "th[colspan|rowspan|headers|scope],"
            + "thead,"
            + "time[datetime],"
            + "tr,"
            + "ul,"
            + "var,"
            + "video[preload<none?metadata?auto|src|crossorigin|poster|autoplay<autoplay|"
            + "mediagroup|loop<loop|muted<muted|controls<controls|width|height],"
            + "wbr",

        schema: "html5",
        element_format: 'xhtml',
        isNotDirtyCalled: false,
        gApiKey: '{googleApiKey}',
        code_dialog_width: ($(window).width() / 1.5),
        plugin_preview_width: ($(window).width() / 1.5),
        plugin_preview_height: ($(window).height() / 1.2),
        custom_undo_redo_levels: 40,
        isNotDirtyCalled: false,


        /* disable the gecko spellcheck since AtD provides one */
        gecko_spellcheck: false,
        /* the URL to the button image to display */
        atd_button_url: "Vendor/tinymce4/plugins/atd/atdbuttontr.gif",
        /* the URL of your proxy file */
        atd_rpc_url: "Vendor/tinymce4/plugins/atd/proxy.php?lang=%s&url=",
        /* set your API key */
        atd_rpc_id: "dashnine",
        /* edit this file to customize how AtD shows errors */
        atd_css_url: "Vendor/tinymce4/plugins/atd/css/content.css",
        /* this list contains the categories of errors we want to show */
        atd_show_types: 'Bias Language,Cliches,Complex Expression,Diacritical Marks,Double Negatives,Hidden Verbs,Jargon Language,Passive voice,Phrases to Avoid,Redundant Expression',
        /* strings this plugin should ignore */
        atd_ignore_strings: ['AtD', 'rsmudge'],
        /* enable "Ignore Always" menu item, uses cookies by default. Set atd_ignore_rpc_url to a URL AtD should send ignore requests to. */
        atd_ignore_enable: false,

        image_advtab: true,
        file_browser_url: 'admin.php?adm=fileman&mode=tinymce',
        baseUrl: '',

        // spellchecker_rpc_url: '/Vendor/tinymce4/plugins/spellchecker/tinymce_spellchecker/spellchecker.php',

        onFullscreenResize: function (editor, tb, editorContain, realContainer, fullscreenContainer) {

        },
        onFullscreen: function (editor, toFullscreen, tb, editorContain, realContainer, fullscreenContainer) {

        },
        init_instance_callback: function (ed) {
            ed.focus(true);
            $('#' + ed.id).addClass('tinymce-basic');

            if (ed.theme.panel._id) {
                $('#' + ed.theme.panel._id).addClass('tinymce-basic');
            }
            $('#' + ed.id).parents('form:first').unbind('reset').unbind('submit');
            ed.fire('blur');
        },

        setup: function (editor) {
            /**
             * Only for the Preview Mode
             */
            if (typeof editor.previewScripts == 'undefined') {
                editor.previewScripts = [];
            }

            $('script[src*="/jquery-"]').each(function () {
                if ($(this).attr('src')) {
                    editor.previewScripts.push($(this).attr('src'));
                }
            });
            $('script[src*="/bootstrap.js"]').each(function () {
                editor.previewScripts.push($(this).attr('src'));
            });
            $('script[src*="/dcms.bootstrap."]').each(function () {
                editor.previewScripts.push($(this).attr('src'));
            });

            editor.previewScripts.push('https://maps.googleapis.com/maps/api/js?sensor=true');
            editor.previewScripts.push('html/js/dcms.googlemap.js');

            $('link[href*="bootstrap.css"]').each(function () {
                editor.contentCSS.push($(this).attr('href'));
            });
            $('link[href*="bootstrap-"]').each(function () {
                editor.contentCSS.push($(this).attr('href'));
            });
            $('link[href*="contentgrid.css"]').each(function () {
                editor.contentCSS.push($(this).attr('href'));
            });

            var configKeyUpEvent = (typeof Config == 'object' && typeof Config.get('onTinyMCEKeyUp') === 'function' ? Config.get('onTinyMCEKeyUp') : false);
            var t, t1, t2;


            if (typeof configKeyUpEvent === 'function') {
                editor.on('keyUp', function (e) {
                    clearTimeout(t);

                    t = setTimeout(function () {
                        $('#' + this.id + '-hidden').html(tinyMCE.activeEditor.getContent());
                        SeemodeEdit.prepareLiveTinyMCEData($('#' + this.id + '-hidden'), $('#' + this.id + '-hidden').attr('contentid'), $('#' + this.id + '-hidden').attr('modul'));
                        SeemodeEdit.setDirty();
                    }, 300);

                    configKeyUpEvent(editor, e);
                });
            }
            else {
                editor.on('keyUp', function (e) {
                    clearTimeout(t1);

                    t1 = setTimeout(function () {
                        $('#' + this.id + '-hidden').html(tinyMCE.activeEditor.getContent());
                        SeemodeEdit.prepareLiveTinyMCEData($('#' + this.id + '-hidden'), $('#' + this.id + '-hidden').attr('contentid'), $('#' + this.id + '-hidden').attr('modul'));
                        SeemodeEdit.setDirty();
                    }, 300);
                });
            }


            editor.on('change', function (ed) {
                clearTimeout(t2);

                if (ed.target.isDirty() && !ed.target.dirty && ed.target.settings.isNotDirtyCalled == true) {
                    ed.target.dirty = true;
                    // clearTimeout( Form.autosaveT ); clear autosave timeout

                    var id = ed.target.id.replace('inline-', '');
                    var field = $('#' + id);

                    t2 = setTimeout(function () {
                        $('#' + id + '-hidden').html(tinyMCE.activeEditor.getContent());
                        SeemodeEdit.prepareLiveTinyMCEData($('#' + id + '-hidden'), $('#' + id + '-hidden').attr('contentid'), $('#' + id + '-hidden').attr('modul'));
                        SeemodeEdit.setDirty();
                    }, 200);
                }

                ed.target.settings.isNotDirtyCalled = true;
                Doc.lastActiveTinyMCESelect = tinymce.activeEditor.selection.getBookmark();
            });


            editor.on('init', function (ed, e) {
                // ed.target.fire('focus');
            });

            editor.on('focus', function (ed) {
                // ed.target.show();
                Doc.lastActiveTinyMCE = ed.target.id;

                if (ed.blurredEditor) {
                    Doc.lastActiveTinyMCESelect = null;
                }
            });

        }

    },

    prepareTinyMceSetup: function (config, isreload) {

        var opts = this.tinyMce4Setup;
        opts.baseUrl = Config.get('portalurl', '') + '/';
        tinymce.baseURL = opts.baseURL.replace('{url}', Config.get('portalurl'));
        opts.script_url = opts.script_url.replace('{url}', Config.get('portalurl'));

        if (Config.get('googleapikey', false)) {
            opts.gApiKey = Config.get('googleapikey');
        }
        else {
            delete opts.gApiKey;
        }

        if (typeof config.plugins != 'undefined') {
            if (typeof config.plugins == 'object') {
                var outArray = Tools.convertObjectToArray(config.plugins);
                opts.plugins = outArray.join(',');
            }
            else {
                opts.plugins = config.plugins;
            }
        }


        if (typeof config.language != 'undefined') {
            opts.language = config.language;
        }

        if (typeof config.content_css != 'undefined') {
            opts.content_css = opts.content_css.replace('{extraTemplateCss}', config.content_css);
        }
        else {
            opts.content_css = opts.content_css.replace('{extraTemplateCss}', '');
        }

        if (typeof config.templates != 'undefined' && config.templates.length) {
            opts.templates = [];
            for (var i = 0; i < config.templates.length; ++i) {
                if (config.templates[i].title && config.templates[i].content) {
                    opts.templates.push(config.templates[i]);
                }
            }
        }

        if (typeof config.toolbar_1 == 'object') {
            var toolbar_1 = Tools.convertObjectToArray(config.toolbar_1);
            if (toolbar_1 && toolbar_1.length) {
                toolbar_1.pop();
                opts.toolbar1 = toolbar_1.join(',');
            }
        }

        if (typeof config.toolbar_2 == 'object') {
            var toolbar_2 = Tools.convertObjectToArray(config.toolbar_2);
            if (toolbar_2 && toolbar_2.length) {
                toolbar_2.pop();
                opts.toolbar2 = toolbar_2.join(',');
            }
        }

        if (typeof config.toolbar_3 == 'object') {
            var toolbar_3 = Tools.convertObjectToArray(config.toolbar_3);
            if (toolbar_3 && toolbar_3.length) {
                toolbar_3.pop();
                opts.toolbar3 = toolbar_3.join(',');
            }
        }

        if (typeof config.toolbar_4 == 'object') {
            var toolbar_4 = Tools.convertObjectToArray(config.toolbar_4);
            if (toolbar_4 && toolbar_4.length) {
                toolbar_4.pop();
                opts.toolbar4 = toolbar_4.join(',');
            }
        }

        delete window.tinymceConfig;
        var t;
        // @see theme advanced/editor_template_src.js
        opts.onResize = function () {
            clearTimeout(t);
            t = setTimeout(function () {
                $(window).trigger('resize');
            }, 300);
        };

        // @see theme advanced/editor_template_src.js
        opts.onResizeStop = function () {
            clearTimeout(t);
            $(window).trigger('resize');
        };

        opts.selector = 'textarea.tinymce-editor';
        opts.inline = false;

        window.tinymceConfig = opts;
        return;
        // load plugins
        if (window.tinymce != 'undefined') {
            /*
             var plugins = opts.plugins;
             if (opts.plugins.match(/ /)) {
             var pl = opts.plugins.split(' ');
             plugins = pl.join(',');
             }
             */
            jQuery.getScript(opts.script_url + '?js=true&disk_cache=true&plugins=' + opts.plugins + '&src=true&core=true&languages=' + opts.language + '&themes=' + opts.theme, function () {
                "use strict";
            });
        }
    },

    initTinyMCE: function () {
        if (this.contentSide.find('.tinymce-editor:not(.inited)').length) {
            tinymce.init(this.tinyMce4Setup);
            this.contentSide.find('.tinymce-editor').addClass('inited');
        }
    },


    stripTags: function (input, allowed) {
        allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)

        var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
            commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
        return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
            return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
        });
    },
    /**
     * Truncate HTML string and keep tag safe.
     *
     * @method truncate
     * @param {String} string string needs to be truncated
     * @param {Number} maxLength length of truncated string
     * @param {Object} options (optional)
     * @param {Boolean} [options.keepImageTag] flag to specify if keep image tag, false by default
     * @param {Boolean|String} [options.ellipsis] omission symbol for truncated string, '...' by default
     * @return {String} truncated string
     */
    truncateHtml: function (string, maxLength, options) {
        var EMPTY_OBJECT = {},
            EMPTY_STRING = '',
            DEFAULT_TRUNCATE_SYMBOL = '...',
            EXCLUDE_TAGS = ['img'], // non-closed tags
            items = [], // stack for saving tags
            total = 0, // record how many characters we traced so far
            content = EMPTY_STRING, // truncated text storage
            KEY_VALUE_REGEX = '([\\w|-]+\\s*=\\s*"[^"]*"\\s*)*',
            IS_CLOSE_REGEX = '\\s*\\/?\\s*',
            CLOSE_REGEX = '\\s*\\/\\s*',
            SELF_CLOSE_REGEX = new RegExp('<\\/?\\w+\\s*' + KEY_VALUE_REGEX + CLOSE_REGEX + '>'),
            HTML_TAG_REGEX = new RegExp('<\\/?\\w+\\s*' + KEY_VALUE_REGEX + IS_CLOSE_REGEX + '>'),
            URL_REGEX = /(((ftp|https?):\/\/)[\-\w@:%_\+.~#?,&\/\/=]+)|((mailto:)?[_.\w\-]+@([\w][\w\-]+\.)+[a-zA-Z]{2,3})/g, // Simple regexp
            IMAGE_TAG_REGEX = new RegExp('<img\\s*' + KEY_VALUE_REGEX + IS_CLOSE_REGEX + '>'),
            matches = true,
            result,
            index,
            tail,
            tag,
            selfClose;

        /**
         * Remove image tag
         *
         * @private
         * @method _removeImageTag
         * @param {String} string not-yet-processed string
         * @return {String} string without image tags
         */
        function _removeImageTag(string) {
            var match = IMAGE_TAG_REGEX.exec(string),
                index,
                len;
            if (!match) {
                return string;
            }

            index = match.index;
            len = match[0].length;
            return string.substring(0, index) + string.substring(index + len);
        }

        /**
         * Dump all close tags and append to truncated content while reaching upperbound
         *
         * @private
         * @method _dumpCloseTag
         * @param {String[]} tags a list of tags which should be closed
         * @return {String} well-formatted html
         */
        function _dumpCloseTag(tags) {
            var html = '';
            tags.reverse().forEach(function (tag, index) {
                // dump non-excluded tags only
                if (-1 === EXCLUDE_TAGS.indexOf(tag)) {
                    html += '</' + tag + '>';
                }
            });
            return html;
        }

        /**
         * Process tag string to get pure tag name
         *
         * @private
         * @method _getTag
         * @param {String} string original html
         * @return {String} tag name
         */
        function _getTag(string) {
            var tail = string.indexOf(' ');
            // TODO:
            // we have to figure out how to handle non-well-formatted HTML case
            if (-1 === tail) {
                tail = string.indexOf('>');
                if (-1 === tail) {
                    throw new Error('HTML tag is not well-formed : ' + string);
                }
            }

            return string.substring(1, tail);
        }

        options = options || EMPTY_OBJECT;
        options.ellipsis = (undefined !== options.ellipsis) ? options.ellipsis : DEFAULT_TRUNCATE_SYMBOL;
        while (matches) {
            matches = HTML_TAG_REGEX.exec(string);
            if (!matches) {
                if (total >= maxLength) {
                    break;
                }

                matches = URL_REGEX.exec(string);
                if (!matches || matches.index >= maxLength) {
                    content += string.substring(0, maxLength - total);
                    break;
                }

                while (matches) {
                    result = matches[0];
                    index = matches.index;
                    content += string.substring(0, (index + result.length) - total);
                    string = string.substring(index + result.length);
                    matches = URL_REGEX.exec(string);
                }
                break;
            }

            result = matches[0];
            index = matches.index;
            if (total + index > maxLength) {
                // exceed given `maxLength`, dump everything to clear stack
                content += (string.substring(0, maxLength - total));
                break;
            } else {
                total += index;
                content += string.substring(0, index);
            }

            if ('/' === result[1]) {
                // move out open tag
                items.pop();
            } else {
                selfClose = SELF_CLOSE_REGEX.exec(result);
                if (!selfClose) {
                    tag = _getTag(result);
                    items.push(tag);
                }
            }

            if (selfClose) {
                content += selfClose[0];
            } else {
                content += result;
            }
            string = string.substring(index + result.length);
        }

        if (string.length > maxLength && options.ellipsis) {
            content += options.ellipsis;
        }
        content += _dumpCloseTag(items);
        if (!options.keepImageTag) {
            content = _removeImageTag(content);
        }

        return content;
    }
};


var Notifier = {
    display: function(type, msg) {
        SeemodeEdit.notify(msg);
    },
    info: function(type, msg) {
        SeemodeEdit.notify(msg);
    },
    warn: function(type, msg) {
        SeemodeEdit.notify(msg);
    },
    error: function(type, msg) {
        SeemodeEdit.notify(msg);
    }
};