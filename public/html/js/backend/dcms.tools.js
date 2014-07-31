window.GetQueryString = function (q) {
    return (function (a) {
        if (a == "")
            return {};
        var b = {};
        for (var i = 0; i < a.length; ++i) {
            var p = a[i].split('=');
            if (p.length != 2)
                continue;
            b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
        }
        return b;
    })(q.split("&"));
};


/*
 * jquery.requestAnimationFrame
 * https://github.com/gnarf37/jquery-requestAnimationFrame
 * Requires jQuery 1.8+
 *
 * Copyright (c) 2012 Corey Frang
 * Licensed under the MIT license.
 
 
 (function ($) {
 
 // requestAnimationFrame polyfill adapted from Erik Möller
 // fixes from Paul Irish and Tino Zijdel
 // http://paulirish.com/2011/requestanimationframe-for-smart-animating/
 // http://my.opera.com/emoller/blog/2011/12/20/requestanimationframe-for-smart-er-animating
 
 
 var animating,
 lastTime = 0,
 vendors = ['webkit', 'moz'],
 requestAnimationFrame = window.requestAnimationFrame,
 cancelAnimationFrame = window.cancelAnimationFrame;
 
 for (; lastTime < vendors.length && !requestAnimationFrame; lastTime++) {
 requestAnimationFrame = window[ vendors[lastTime] + "RequestAnimationFrame" ];
 cancelAnimationFrame = cancelAnimationFrame ||
 window[ vendors[lastTime] + "CancelAnimationFrame" ] ||
 window[ vendors[lastTime] + "CancelRequestAnimationFrame" ];
 }
 
 function raf () {
 if (animating) {
 requestAnimationFrame(raf);
 jQuery.fx.tick();
 }
 }
 
 if (requestAnimationFrame) {
 // use rAF
 window.requestAnimationFrame = requestAnimationFrame;
 window.cancelAnimationFrame = cancelAnimationFrame;
 jQuery.fx.timer = function (timer) {
 if (timer() && jQuery.timers.push(timer) && !animating) {
 animating = true;
 raf();
 }
 };
 
 jQuery.fx.stop = function () {
 animating = false;
 };
 } else {
 // polyfill
 window.requestAnimationFrame = function (callback, element) {
 var currTime = new Date().getTime(),
 timeToCall = Math.max(0, 16 - (currTime - lastTime)),
 id = window.setTimeout(function () {
 callback(currTime + timeToCall);
 }, timeToCall);
 lastTime = currTime + timeToCall;
 return id;
 };
 
 window.cancelAnimationFrame = function (id) {
 clearTimeout(id);
 };
 
 }
 
 }(jQuery));
 */

(function ($) {
    $.fn.getStyleObject = function () {
        var dom = this.get(0);
        var style;
        var returns = {};
        if (window.getComputedStyle) {
            var camelize = function (a, b) {
                return b.toUpperCase();
            }
            style = window.getComputedStyle(dom, null);
            if (style) {
                for (var i = 0; i < style.length; i++) {
                    var prop = style[i];
                    var camel = prop;
                    var val = style.getPropertyValue(prop);
                    returns[camel] = val;
                }
            }
            return returns;
        }

        if (dom.currentStyle) {
            style = dom.currentStyle;
            if (style) {
                for (var prop in style) {
                    returns[prop] = style[prop];
                }
            }
            return returns;
        }

        return this.css();
    };

    // Note that scale is unitless.
    $.fn.scale = function (val, duration, options) {
        var style = $(this).css('transform');

        if (typeof val == 'undefined') {
            if (style) {
                var m = style.match(/scale\(([^)]+)\)/);
                if (m && m[1]) {
                    return m[1];
                }
            }

            return 1;
        }

        $(this).css(
                'transform',
                style.replace(/none|scale\([^)]*\)/, '') + 'scale(' + val + ')'
                );

        return this;
    };


    $.fn.tabby = function (options) {
        var opts = $.extend({}, options);


        return this.each(function () {

            $(this).addClass('tabby').bind('keydown', function (e) {
                var pressedKey = e.charCode || e.keyCode || -1;
                var meta = e.ctrlKey || e.metaKey;

                if (pressedKey == 9) {

                    var textArea = this;

                    if (window.event) {
                        window.event.cancelBubble = true;
                        window.event.returnValue = false;
                    } else {
                        e.preventDefault();
                        e.stopPropagation();
                    }

                    // save current scroll position for later restoration
                    var oldScrollTop = this.scrollTop;


                    if (jQuery.browser.msie) {
                        //internet explorer is Rtarded!
                        var range = document.selection.createRange();
                        range.text = '\t';
                    } else {
                        var start = textArea.selectionStart;
                        var end = textArea.selectionEnd;
                        var value = textArea.value;
                    }


                    if (start != end) {
                        var lines = textArea.value.substring(start, end).split('\n');
                        var lastI = lines.length;
                        var tmpStr = '';
                        $.each(lines, function (key, obj) {
                            if (!meta) {
                                tmpStr += '\t' + obj + (lastI != key + 1 ? '\n' : '');
                            }
                            else {
                                tmpStr += obj.replace(/\t{1}/, '') + (lastI != key + 1 ? '\n' : '');
                            }
                        });
                        textArea.value = (value.substring(0, start) + tmpStr + value.substring(end, value.length));
                        textArea.setSelectionRange(start, end + lastI);
                    } else {
                        if (!meta) {
                            textArea.value = (value.substring(0, start) + '\t' + value.substring(end, value.length));
                        }
                        else {
                            textArea.value = value.replace(/\t{1}/, '');
                        }

                        start++;
                        textArea.setSelectionRange(start, start);
                    }

                    this.focus();
                    this.scrollTop = oldScrollTop;

                    return false;


                    if (this.createTextRange) {
                        document.selection.createRange().text = "\t";
                        this.onblur = function () {
                            this.focus();
                            this.onblur = null;
                        };
                    } else if (this.setSelectionRange) {
                        start = this.selectionStart;
                        end = this.selectionEnd;
                        this.value = this.value.substring(0, start) + "\t" + this.value.substr(end);
                        this.setSelectionRange(start + 1, start + 1);
                        this.focus();
                    }

                    this.scrollTop = oldScrollTop;

                    return false;
                }
            });
        });
    };
})(jQuery);


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


var sessionRefreshTimer;
var Tools = {
    mimeTypes: {
        "js": ["application/javascript", "application/x-javascript"],
        "javascript": ["application/javascript", "application/x-javascript"],
        "xml": ["application/xml", "text/xml", "application/x-google-gadget"],
        "groovy": ["script/groovy", "application/x-groovy", "application/x-jaxrs+groovy", "application/x-groovy+html", "application/x-chromattic+groovy"],
        "html": ["text/html", "application/x-uwa-widget"],
        "jpg": "image/jpeg",
        "ai": "application/postscript",
        "aif": "audio/x-aiff",
        "aifc": "audio/x-aiff",
        "aiff": "audio/x-aiff",
        "any": "text/any",
        "asc": "text/plain",
        "au": "audio/basic",
        "avi": "video/x-msvideo",
        "bcpio": "application/x-bcpio",
        "bin": "application/octet-stream",
        "bz2": "application/x-bzip2",
        "cdf": "application/x-netcdf",
        "class": "application/octet-stream",
        "cpio": "application/x-cpio",
        "cpt": "application/mac-compactpro",
        "cq": "application/cq-durboser",
        "csh": "application/x-csh",
        "css": "text/css",
        "dcr": "application/x-director",
        "dir": "application/x-director",
        "dms": "application/octet-stream",
        "doc": "application/msword",
        "dvi": "application/x-dvi",
        "dxr": "application/x-director",
        "ecma": "text/qhtml",
        "eps": "application/postscript",
        "esp": "text/qhtml",
        "etx": "text/x-setext",
        "exe": "application/octet-stream",
        "ez": "application/andrew-inset",
        "gif": "image/gif",
        "gtar": "application/x-gtar",
        "gz": "application/x-gzip",
        "hdf": "application/x-hdf",
        "hqx": "application/mac-binhex40",
        "htm": "text/html",
        "ice": "x-conference/x-cooltalk",
        "ief": "image/ief",
        "iges": "model/iges",
        "igs": "model/iges",
        "jpeg": "image/jpeg",
        "jpe": "image/jpeg",
        "bmp": "image/bmp",
        "kar": "audio/midi",
        "latex": "application/x-latex",
        "lha": "application/octet-stream",
        "lzh": "application/octet-stream",
        "man": "application/x-troff-man",
        "manifest": ["text/plain", "text/cache-manifest"],
        "me": "application/x-troff-me",
        "mesh": "model/mesh",
        "mid": "audio/midi",
        "midi": "audio/midi",
        "mif": "application/vnd=mif",
        "mov": "video/quicktime",
        "m4v": "video/x-m4v",
        "m4a": "audio/x-m4a",
        "movie": "video/x-sgi-movie",
        "mp2": "audio/mp2",
        "mp3": "audio/mp3",
        "mp4": "video/mp4",
        "mpe": "video/mpe",
        "mpeg": "video/mpeg",
        "mpg": "video/mpeg",
        "mpg": "video/mpg",
                "mpga": "audio/mpga",
        "ms": "application/x-troff-ms",
        "msh": "model/mesh",
        "nc": "application/x-netcdf",
        "oda": "application/oda",
        "pbm": "image/x-portable-bitmap",
        "pdb": "chemical/x-pdb",
        "pdf": "application/pdf",
        "pgm": "image/x-portable-graymap",
        "pgn": "application/x-chess-pgn",
        "php": "application/x-httpd-php",
        "png": "image/png",
        "pnm": "image/x-portable-anymap",
        "ppm": "image/x-portable-pixmap",
        "ppt": "application/ppt",
        "properties": "text/plain",
        "ps": "application/postscript",
        "qhtml": "text/qhtml",
        "qt": "video/quicktime",
        "ra": "audio/x-realaudio",
        "ram": "audio/x-pn-realaudio",
        "rm": "audio/x-pn-realaudio",
        "ras": "image/x-cmu-raster",
        "rgb": "image/x-rgb",
        "roff": "application/x-troff",
        "rpm": "application/x-rpm",
        "rtf": "text/rtf",
        "rtx": "text/richtext",
        "sgm": "text/sgml",
        "sgml": "text/sgml",
        "sh": "application/x-sh",
        "shar": "application/x-shar",
        "silo": "model/mesh",
        "sit": "application/x-stuffit",
        "skd": "application/x-koan",
        "skm": "application/x-koan",
        "skp": "application/x-koan",
        "skt": "application/x-koan",
        "smi": "application/smil",
        "smil": "application/smil",
        "snd": "audio/basic",
        "spl": "application/x-futuresplash",
        "src": "application/x-wais-source",
        "sv4cpio": "application/x-sv4cpio",
        "sv4crc": "application/x-sv4crc",
        "swf": "application/x-shockwave-flash",
        "t": "application/x-troff",
        "tar": "application/x-tar",
        "tcl": "application/x-tcl",
        "tex": "application/x-tex",
        "texi": "application/x-texinfo",
        "texinfo": "application/x-texinfo",
        "tgz": "application/x-gzip",
        "tif": "image/tiff",
        "tiff": "image/tiff",
        "tr": "application/x-troff",
        "tsv": "text/tab-separated-values",
        "txt": "text/plain",
        "odt": "application/vnd.oasis.opendocument.text",
        "ods": "application/vnd.oasis.opendocument.spreadsheet",
        "odp": "application/vnd.oasis.opendocument.presentation",
        "odb": "application/vnd.oasis.opendocument.database",
        'ogv': 'video/ogg',
        'ogm': 'video/ogg',
        'ogg': 'audio/ogg',
        'oga': 'audio/ogg',
        "ustar": "application/x-ustar",
        "vcd": "application/x-cdlink",
        "vm": "text/plain",
        "vrml": "model/vrml",
        "wav": ["audio/x-wav", 'audio/wav'],
        'webm': 'video/webm',
        "wrl": "model/vrml",
        "xbm": "image/x-xbitmap",
        "xls": "application/xls",
        "xpdl": "text/xml",
        "xpm": "image/x-xpixmap",
        "xwd": "image/x-xwindowdump",
        "xyz": "chemical/x-pdb",
        "zip": "application/zip",
        "rar": "application/rar",
        "msg": "application/vnd.ms-outlook"
    },
    R: {
        Element: function (t) {
            this.element = e(t)
        },
        promise: function (e, t) {
            e.is(":visible") || e.css({
                display: e.data("olddisplay") || "block"
            }).css("display"), t.hide && e.data("olddisplay", e.css("display")).hide(), t.init && t.init(), t.completeCallback && t.completeCallback(e), e.dequeue()
        },
        transitionPromise: function (e, t, n) {
            var i = ot.wrap(e);
            return i.append(t), e.hide(), t.show(), n.completeCallback && n.completeCallback(e), e
        }
    },
    g: function (e, t, n, i) {

        return typeof e === 'string' && (e.proxy(t) && (i = t, t = 400, n = !1), e.proxy(n) && (i = n, n = !1), typeof t === 'boolean' && (n = t, t = 400), e = {
            effects: e,
            duration: t,
            reverse: n,
            complete: i
        }), at({
            effects: {},
            duration: 400,
            reverse: !1,
            init: e.noop,
            teardown: e.noop,
            hide: !1
        }, e, {
            completeCallback: e.complete,
            complete: e.noop
        })
    },
    m: function (t, n, i, r, o) {
        for (var a, s = 0, l = t.length; l > s; s++)
            a = e(t[s]), a.queue(function () {
                R.promise(a, g(n, i, r, o))
            });
        return t
    },
    /**
     *
     at(e.fn, {
     kendoStop: function (e, t) {
     return this.stop(e, t)
     },
     kendoAnimate: function (e, t, n, i) {
     return m(this, e, t, n, i)
     },
     kendoAnimateTo: function (e, t, n, i, r) {
     return v(this, e, t, n, i, r)
     },
     kendoAddClass: function (e, t) {
     return ot.toggleClass(this, e, t, !0)
     },
     kendoRemoveClass: function (e, t) {
     return ot.toggleClass(this, e, t, !1)
     },
     kendoToggleClass: function (e, t, n) {
     return ot.toggleClass(this, e, t, n)
     }
     })
     */



    getLoadingImage: function () {
        return '<img src="' + Config.get('loadingImgSmall') + '" class="loading"/>';
    },
    animate: function () {

    },
    responseIsOk: function (data) {

        if (data == null || Tools.isString(data) || (Tools.isObject(data) && this.exists(data, 'success') && data.success == false)) {
            $.pagemask.hide();
            document.body.style.cursor = 'default';


            if (data != null && typeof data == 'object') {
                Notifier.isConsoleErrorOutput = false;

                if (!Config.get('isSeemode') && data.debugoutput != null) {
                    DesktopConsole.setDebug(data.debugoutput);
                }

                if (data.sessionerror === true) {
                    $('#desktop').unmask();
                    document.body.style.cursor = 'auto';
                    Cookie.erase('uhash');
                    setTimeout(function () {
                        if (!Config.get('isSeemode')) {
                            $.get('admin.php?adm=auth&action=logout', function () {
                                delete(Desktop.basicCMSData.userdata);

                                $('body').removeClass('boot').addClass('auth');
                                $('#userMenu,#Taskbar').remove();
                                Dock.toggleDockView(true);
                                Desktop.animateToLogin();
                            });
                        }
                        else {
                            document.location.href = document.location.href;
                        }
                    }, 2);
                    return false;
                }


                if (this.exists(data, 'fatalError')) {
                    $('#desktop').unmask();

                    if (this.exists(data, 'error')) {
                        Notifier.isConsoleErrorOutput = true;

                        var error = data.error;

                        if (this.exists(data, 'backtrace')) {
                            error += "\n\n<br/><br/>" + data.backtrace;
                        }
                        if (!Config.get('isSeemode')) {
                            DesktopConsole.setErrors(error);
                        }
                    }
                    else if (this.exists(data, 'msg')) {

                        Notifier.isConsoleErrorOutput = true;
                        if (!Config.get('isSeemode')) {
                            DesktopConsole.setErrors(data.msg);
                        }
                    }
                }
            }


            Tools.html5Audio('html/audio/error');

            return false;
        }

        /**
         *  @deprecated will remove in the next version
         */
        if (data == null || (typeof data == 'object' && this.exists(data, 'success') && data.success == false)) {
            document.body.style.cursor = 'default';

            if (data != null && typeof data == 'object' && this.exists(data, 'sessionerror')) {
                if (data.sessionerror == true) {
                    //document.location.href = cmsurl + 'admin.php';	
                    Tools.html5Audio('html/audio/error');
                    return false;
                }
            }

            if (!Config.get('isSeemode') && typeof data == 'object' && typeof data.debugoutput == 'string' && data.debugoutput != '') {
                DesktopConsole.setDebug(data.debugoutput);
            }
            Tools.html5Audio('html/audio/error');
            return false;
        }
        else {

            if (!Config.get('isSeemode') && typeof data == 'object' && typeof data.debugoutput == 'string' && data.debugoutput != '') {
                DesktopConsole.setDebug(data.debugoutput);
            }

            return true;
        }
    },
    escapeJqueryRegex: function (name) {
        return name.replace(/[#;&,.+*~':"!^$[\]()=>|\/]/g, "\\\\$&");
    },
    trans: function () {
        var returnStr, str = arguments.shift();

        try {
            eval('returnStr = sprintf(str, ' + arguments.join(',') + ');');
        }
        catch (e) {
            console.log('trans Error: ' + e);

            return '';
        }

        return returnStr;
    },
    loadScript: function (url, callback) {
        if (typeof Desktop === 'object' && typeof Desktop.getScript === 'function') {
            Desktop.getScript(url, callback);
        }
        else {
            $.getScript(url, function () {
                if (typeof callback == 'function') {
                    callback();
                }
            });

        }
    },
    globalEval: function (data) {
        if (typeof data === 'string' && jQuery.trim(data)) {
            // We use execScript on Internet Explorer
            // We use an anonymous function so that context is window
            // rather than jQuery in Firefox
            try {
                (window.execScript || function (data) {
                    window[ "eval" ].call(window, data);
                })(data);
            }
            catch (e) {
                console.log(e + ' Data: ' + data);
            }

        }
    },
    eval: function (strObject) {

        $(strObject).filter('script').each(function () {
            if (typeof this.text == 'string' || typeof this.textContent == 'string' || typeof this.innerHTML == 'string') {
                Tools.globalEval(this.text || this.textContent || this.innerHTML);
            }
        });
    },
    spinner: function (el, colorOrStop) {
        if (colorOrStop === false) {
            $(el).spin(false);
            return;
        }

        var opts = {
            lines: 11, // The number of lines to draw
            length: 7, // The length of each line
            width: 3, // The line thickness
            radius: 8, // The radius of the inner circle
            corners: 0, // Corner roundness (0..1)
            rotate: 0, // The rotation offset
            direction: 1, // 1: clockwise, -1: counterclockwise
            color: (colorOrStop ? colorOrStop : '#000'), // #rgb or #rrggbb
            speed: 1.6, // Rounds per second
            trail: 65, // Afterglow percentage
            shadow: false, // Whether to render a shadow
            hwaccel: false, // Whether to use hardware acceleration
            className: 'spinner', // The CSS class to assign to the spinner
            zIndex: 2e9, // The z-index (defaults to 2000000000)
            top: 'auto', // Top position relative to parent in px
            left: 'auto' // Left position relative to parent in px
        };

        $(el).spin(opts);

    },
    scrollBar: function (elObj, scollToObj, onScrollEvent) {
        // elObj is the scrollContent
        var $el = elObj.parent(); // use parent as Container
        var $scrollContent = elObj;

        if (elObj.hasClass('window-body-content')) {
            $el = elObj;
            $scrollContent = elObj.find(':visible:first');
        }

        $scrollContent.addClass('scroll-content').height('');
        $el.addClass('nano');

        var opt = {scrollContent: $scrollContent};

        if (typeof scollToObj === 'object') {
            opt.scrollTo = scollToObj;
        }
        else if (scollToObj === 'bottom') {
            opt.scroll = 'bottom';
        }
        else if (scollToObj === 'top') {
            opt.scroll = 'top';
        }
        else if (typeof scollToObj !== 'undefined' && scollToObj !== null && scollToObj !== false) {
            opt.scrollTo = scollToObj;
        }

        if (typeof onScrollEvent === 'function') {
            opt.onScroll = onScrollEvent;
        }

        $el.nanoScroller(opt);
    },
    refreshScrollBar: function (elObj) {
        // elObj is the scrollContent
        var $el = elObj.parent(); // use parent as Container
        var $scrollContent = elObj;

        if (elObj.hasClass('window-body-content')) {
            $el = elObj;
            $scrollContent = elObj.children(':visible:first');
        }
        var el = $el.get(0);
        if (el && el.hasOwnProperty('nanoscroller')) {
            $el.nanoScroller({scrollContent: $scrollContent});
        }
    },
    getScrollPosTop: function (elObj) {
        var $el = elObj.parent(); // use parent as Container
        if (elObj.hasClass('window-body-content')) {
            $el = elObj;
        }

        var el = $el.get(0);
        if (el && el.hasOwnProperty('nanoscroller')) {
            return $el.nanoScroller('scrollPosTop');
        }

        return 0;
    },
    removeScrollBar: function (elObj) {
        // elObj is the scrollContent
        var $el = elObj.parent(); // use parent as Container
        var $scrollContent = elObj;

        if (elObj.hasClass('window-body-content')) {
            $el = elObj;
            $scrollContent = elObj.children('>:visible:first');
        }


        if ($el.hasClass('has-scrollbar')) {
            $el.removeNanoScroller({scrollContent: $scrollContent});
            $el.removeClass('nano');
            $scrollContent.removeClass('scroll-content');
        }
    },
    sleep: function (ms) {
        var time = (new Date()).getTime();
        var stopTime = time + ms;
        while ((new Date()).getTime() < stopTime) {
        }
        ;
    },
    exists: function (object, name) {

        if (object === null || typeof object == 'undefined' || !Tools.isObject(object) || !object) {
            // Debug.log('Could not check type exists! Key to check:' + name);
            return false;
        }
        return object.hasOwnProperty(name);
    },
    isUndefined: function (test) {
        if (test !== null && typeof test == "undefined") {
            return true;
        }
        return false;
    },
    isObject: function (test) {
        if (typeof test === "object") {
            return true;
        }
        return false;
    },
    isFunction: function (test) {
        if (test !== null && typeof test === "function") {
            return true;
        }
        return false;
    },
    isInteger: function (_test) {
        if (_test !== null && /^[0-9]+$/.test(_test)) {
            return true;
        }
        return false;
    },
    isString: function (_test) {
        if (_test !== null && typeof _test === 'string') {
            return true;
        }
        return false;
    },
    $bdetect: null,
    browserDetect: function () {
        if (this.$bdetect !== null) {
            return;
        }

        var Browser = this.$bdetect = (function () {
            var ua = navigator.userAgent.toLowerCase(),
                    platform = navigator.platform.toLowerCase(),
                    UA = ua.match(/(opera|ie|firefox|chrome|version)[\s\/:]([\w\d\.]+)?.*?(safari|version[\s\/:]([\w\d\.]+)|$)/) || [null, "unknown", 0],
                    mode = UA[1] == "ie" && document.documentMode;
            var b = {
                name: (UA[1] == "version") ? UA[3] : UA[1],
                version: mode || parseFloat((UA[1] == "opera" && UA[4]) ? UA[4] : UA[2]),
                Platform: {
                    name: ua.match(/ip(?:ad|od|hone)/) ? "ios" : (ua.match(/(?:webos|android)/) || platform.match(/mac|win|linux/) || ["other"])[0]
                },
                Features: {
                    xpath: !!(document.evaluate),
                    air: !!(window.runtime),
                    query: !!(document.querySelector),
                    json: !!(window.JSON)
                },
                Plugins: {}
            };
            b[b.name] = true;
            b[b.name + parseInt(b.version, 10)] = true;
            b.Platform[b.Platform.name] = true;
            return b;
        })();
        var UA = navigator.userAgent.toLowerCase();
        this.isGecko = !!Browser.firefox;
        this.isChrome = !!Browser.chrome;
        this.isSafari = !!Browser.safari;
        this.isSafariOld = Browser.safari && Browser.version === 2.4;
        this.isWebkit = this.isSafari || this.isChrome || UA.indexOf("konqueror") != -1;
        this.isOpera = !!Browser.opera;
        this.isIE = !!Browser.ie;
        this.isWin = Browser.Platform.win;
        this.isMac = Browser.Platform.mac;
        this.isLinux = Browser.Platform.linux;
        this.isIphone = Browser.Platform.ios || UA.indexOf("aspen simulator") != -1;
        this.isAIR = Browser.Features.air;
        this.versionWebkit = this.isWebkit ? Browser.version : null;
        this.versionGecko = this.isGecko ? Browser.version : null;
        this.isGecko3 = Browser.firefox3;
        this.isGecko35 = this.isGecko3 && Browser.version >= 3.5;
        this.versionFF = this.isGecko ? Browser.version : null;
        this.versionSafari = this.isSafari ? Browser.version : null;
        this.versionChrome = this.isChrome ? Browser.version : null;
        this.versionOpera = this.isOpera ? Browser.version : null;
        this.isIE6 = this.isIE && Browser.ie6;
        this.isIE7 = this.isIE && Browser.ie7;
        this.isIE8 = this.isIE && Browser.ie8;
        this.isIE9 = this.isIE && Browser.ie9;
        this.isIE7Emulate = this.isIE && document.documentMode && Browser.ie7;
        this.isIE8Emulate = this.isIE && document.documentMode && Browser.ie8;
        this.isIE = this.isIE ? Browser.version : null;
    },
    getDirname: function (url) {
        return ((url || "").match(/^([^#]*\/)[^\/]*(?:$|\#)/) || {})[1];
    },
    getFilename: function (url) {
        return ((url || "").split("?")[0].match(/(?:\/|^)([^\/]+)$/) || {})[1];
    },
    getAbsolutePath: function (base, url) {
        return url && url.charAt(0) == "/" ? url : (!url || !base || url.match(/^\w+\:\/\//) ? url : base.replace(/\/$/, "") + "/" + url.replace(/^\//, ""));
    },
    prepareSSLUrl: function (url) {

    },
    prepareAjaxUrl: function (url) {
        var base = Config.get('portalurl', '');

        if (url !== null && typeof url === 'string' && !url.match(/^\w+\:\/\//)) {
            url = base.replace(/\/$/, "") + "/" + url.replace(/^\//, "");
        }

        if (Config.get('SSL_MODE', false)) {
            url = (url !== null && typeof url == 'string' ? url.replace(/^https?:/i, "https:") : Config.get('SSL_portalurl', ''))
        }

        return url;
    },
    extractAppInfoFromUrl: function (url) {
        var to = (typeof url);

        if (url !== null && typeof url === 'object') {
            return {
                controller: url.adm,
                action: url.action
            };
        }
        else if (url !== null && typeof url === 'string') {
            return {
                controller: $.getURLParam('adm', url), //url.replace(/.*adm=([\w0-9_]*).*/g, '$1'),
                action: $.getURLParam('action', url) //url.replace(/.*([&\?]?)action=([\w0-9_]*).*/g, '$2')
            };
        }

        return {
            controller: null,
            action: null
        };
    },
    convertUrlToObject: function (urlStr) {

        var strQueryString = urlStr.substr(urlStr.indexOf("?") + 1);
        strQueryString = strQueryString.replace('&amp;', '&');


        var obj = {};

        if (strQueryString != '') {
            strQueryString += '&';
            var params = strQueryString.split('&');
            for (var x = 0; x < params.length; x++) {
                var p = params[x].split('=');
                if (p[0] && p[1] != '') {
                    obj[p.shift()] = p.shift();
                }
            }
        }

        return obj;
    },
    formatSize: function (size) {
        var units = new Array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        i = 0;
        while (size > 1024) {
            i++;
            size = size / 1024;
        }
        return size.toFixed(1) + ' ' + units[i];
    },
    unformatSize: function (size) {
        if (size.match(/b/i) || size.match(/byte/i)) {
            var inSize = parseInt(size, 10);
            return inSize;
        }

        if (size.match(/k/i) || size.match(/kb/i)) {
            var inSize = parseInt(size, 10);
            return inSize * 1024;
        }

        if (size.match(/m/i) || size.match(/mb/i)) {
            var inSize = parseInt(size, 10);
            return inSize * 1024 * 1024;
        }

        if (size.match(/g/i) || size.match(/gb/i)) {
            var inSize = parseInt(size, 10);
            return inSize * 1024 * 1024 * 1024;
        }
    },
    sessionRefresher: function () {

		var params = {
			adm: 'dashboard',
			action: 'checkversion',
			token: Config.get('token')
		};


        $.post('admin.php', {adm: 'dashboard', action: 'checkversion'}, function (data) {
            if (Tools.responseIsOk(data)) {
                Notifier.info('Your Session has refreshed.');
            }
            else {
                clearTimeout(sessionRefreshTimer);
            }
        });
    },
    popup: function (url, title, width, nopadding) {
        var opt = {
            icon: '',
            title: 'DreamCMS...',
            loadWithAjax: true,
            allowAjaxCache: false,
            WindowToolbar: false,
            WindowMaximize: false,
            WindowMinimize: false,
            WindowResizeable: false,
            DesktopIconWidth: 36,
            DesktopIconHeight: 36,
            UseWindowIcon: false,
            WindowContent: null,
            onBeforeShow: null,
            onBeforeClose: null,
            onBeforeOpen: null,
            onAfterCreated: null,
            onClose: null,
            Skin: Desktop.settings.Skin,
        };

        if (width && width > 100) {
            opt.minWidth = width;
            opt.Width = width;
        }

        if (nopadding) {
            opt.nopadding = nopadding;
        }

        opt.WindowTitle = title;
        opt.WindowDesktopIconFile = '';


        $.get(url, function (data) {
            if (Tools.responseIsOk(data)) {
                opt.WindowContent = data.maincontent;


                Tools.createPopup(data.maincontent, opt);

            }
        }, 'json');

    },
    // returns the window object
    createPopup: function (htmldata, options) {
        var defaults = {
            icon: '',
            title: 'DreamCMS...',
            loadWithAjax: true,
            allowAjaxCache: false,
            WindowToolbar: false,
            WindowMaximize: false,
            WindowMinimize: false,
            WindowResizeable: false,
            DesktopIconWidth: 36,
            DesktopIconHeight: 36,
            UseWindowIcon: false,
            WindowContent: null,
            onBeforeShow: null,
            onBeforeClose: null,
            onBeforeOpen: null,
            onAfterCreated: null,
            onClose: null,
            ajaxData: null,
            addFileSelector: options.addFileSelector || false
        };

        if (typeof options != 'object') {
            options = {};
        }

        var opts = $.extend({}, defaults, options);
        var actWin = Desktop.getActiveWindow();
        var openerid;

        // reset current ajaxData
        Desktop.ajaxData = {
            modal: false
        };


        if (typeof opts.ajaxData != 'undefined') {
            Desktop.ajaxData = {};
            Desktop.ajaxData = $.extend(Desktop.ajaxData, opts.ajaxData);
        }

        if (actWin) {
            openerid = actWin.attr('id');

            if (typeof opts.onBeforeOpen == 'function') {
                opts.onBeforeOpen();
            }
        }

        opts.Skin = Desktop.settings.Skin;
        opts.WindowTitle = opts.WindowTitle ? opts.WindowTitle : (opts.title ? opts.title : '');
        opts.WindowDesktopIconFile = (opts.icon ? opts.icon : '');
        opts.WindowContent = htmldata ? htmldata : opts.WindowContent;



        if (typeof opts.modal != 'undefined' && opts.modal) {
            Desktop.ajaxData = {
                modal: true
            };
        }

        Desktop.GenerateNewWindow(opts, null, function (obj, objectData, id) {
            obj.unmask();
            obj.attr('opener', openerid);
            objectData.set('opener', openerid);

            if (typeof opts.app == 'string') {
                obj.attr('app', opts.app);
            }

            if (typeof opts.onAfterOpen == 'function') {
                setTimeout(function () {
                    opts.onAfterOpen(obj, objectData, id);
                }, 100);
            }
            return obj;
        });

    },
    /**
     * Play automatic the sound
     * @param {string} pathToFile without extension!!!
     * @returns {undefined}
     */
    lastPlay: null,
    html5Audio: function (pathToFile) {

        if (this.lastPlay === pathToFile) {
            return;
        }


        if (window.HTMLAudioElement) {
            var snd = new Audio('');
            this.lastPlay = pathToFile;

            if (snd.canPlayType('audio/ogg')) {
                snd = new Audio(pathToFile + '.ogg');
                snd.volume = 0.7;
                snd.play();
                this.lastPlay = null;
                //     console.log('Play audio');
            }
            else if (snd.canPlayType('audio/mp3')) {
                snd = new Audio(pathToFile + '.mp3');
                snd.volume = 0.7;
                snd.play();
                this.lastPlay = null;
                //      console.log('Play audio');
            }
            else {
                //    console.log('Skip audio');
            }
        }
        else {
            //     console.log('Skip audio');
        }
    },
    swfUpload: [],
    destroyUpload: function () {

        // clear old upload objects        
        if (this.swfUpload.length) {

            for (var x = 0; x < this.swfUpload.length; ++x) {
                var obj = this.swfUpload[x];
                if (obj && obj.winID && typeof obj.winID != 'undefined' && obj.winID === Win.windowID) {
                    if (typeof SWFUpload.instances !== 'undefined' && typeof obj.swf.movieName !== 'undefined' && typeof SWFUpload.instances[obj.swf.movieName] !== 'undefined') {
                        SWFUpload.instances[obj.swf.movieName].destroy();
                    }
                    delete this.swfUpload[x];
                    $('#' + obj.winID).find('#start-upload-btn').hide();
                }
            }
        }
    },
    rgb2hex: function (rgb) {
        var rgbm = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);

        if (typeof rgbm != 'undefined' && rgbm) {
            return "#" +
                    ("0" + parseInt(rgbm[1], 10).toString(16)).slice(-2) +
                    ("0" + parseInt(rgbm[2], 10).toString(16)).slice(-2) +
                    ("0" + parseInt(rgbm[3], 10).toString(16)).slice(-2);
        }

        return rgb;
    },
    /**
     * create a single file upload
     * @param object opts
     * @returns {undefined}
     */
    UploadControl: function (opts) {

        var uploadControl;

        if (typeof opts.control == 'string') {
            uploadControl = $('#' + opts.control);
        }
        else if (typeof opts.control == 'object') {
            uploadControl = opts.control;
        }
        else {
            jAlert('Invalid upload Control container', 'error');
            return false;
        }

        if (uploadControl.length == 0) {
            jAlert('Invalid upload Control container', 'error');
            return false;
        }

        if (this.swfUpload.length) {
            this.destroyUpload();
        }

        //  uploadControl.find('object').remove();

        var swfu, upload_max_filesizeString, total_selected_files = 0, max_file_uploads = 1, max_upload_files = 1, upload_max_filesize = Config.get('upload_max_filesize', '1M');

        if (file_upload_limit == undefined) {
            var file_upload_limit = 1;
        }

        if (file_queue_limit == undefined) {
            var file_queue_limit = 1;
        }

        upload_max_filesizeString = (opts.max_file_size || upload_max_filesize);
        upload_max_filesize = (opts.max_file_size || upload_max_filesize);
        file_queue_limit = (opts.file_queue_limit || (max_file_uploads || file_queue_limit));
        max_upload_files = (opts.max_upload_files || max_upload_files);


        if (upload_max_filesize.match(/\d+?\s*M/)) {
            var mb = upload_max_filesize.replace(/(\d+?)\s*M/, '$1');
            if (mb) {
                mb = parseInt(mb);
                upload_max_filesize = mb * 1024;
            }
        }

        var self = this, options = opts;
        var control = uploadControl;

        var button_id = 'mu_button';
        var display_id = 'mu_display';


        $('#mu_button,#mu_display', control).remove();

        var $ul = uploadControl.parents('form:first').find('ul');
        var uploadform = uploadControl.parents('form:first');

        $(uploadform).find('.drop-here').hide();

        var browseBtn = uploadControl.find('span.browse');
        var fallbackInput = uploadControl.find('span.browse').parent().find('input');
        $ul.empty();

        browseBtn.hide();

        var cssString = '', cssBtnTextString = '', css = browseBtn.getStyleObject();

        if (typeof css === 'object') {
            for (var k in css) {
                cssString += (cssString ? ';' : '') + k + ':' + Tools.rgb2hex(css[k]);

                if (k == 'color' || k == 'font-size' || k == 'font-family' || k == 'padding') {
                    cssBtnTextString += (cssBtnTextString ? ';' : '') + k + ':' + Tools.rgb2hex(css[k]);

                    if (k == 'color') {
                        cssBtnTextString += (cssBtnTextString ? ';' : '') + 'font-' + k + ':' + Tools.rgb2hex(css[k]);
                    }


                }
            }
        }


        $('<span>').attr('id', button_id).hide().insertAfter(browseBtn);


        var uploadButton;


        // var css = browseBtn.css();

        var formatedMaxFileSize = this.formatSize(this.unformatSize(upload_max_filesizeString));
        uploadControl.find('span.allowed-filesize').text('Maximale Dateigröße: %s'.replace('%s', formatedMaxFileSize));
        uploadControl.find('span.allowed-extensions').text('Erlaubt sind: %s'.replace('%s', opts.file_type_mask));


        var display = $ul;
        var current = null, url, cmsurl = Config.get('portalurl'), session_id = Desktop.SessionID;

        if (cmsurl.substr(cmsurl.length - 1, cmsurl.length) != '/') {
            url = cmsurl + '/';
        }
        else {
            url = cmsurl;
        }

        if (!session_id) {
            jAlert('Your Session is invalid!', 'Session Error');
            return;
        }

        var postparams = {
            "sid": session_id,
            "swfupload_sid": session_id,
            "is_flash": true,
            "format": "raw",
            "adm": opts.postParams.adm,
            "action": opts.postParams.action,
            "uploadpath": opts.postParams.uploadpath,
            "swfupload": 1,
            "ajax": 1
        };

        if (opts.type == 'gal') {
            postparams = {
                "sid": session_id,
                "swfupload_sid": session_id,
                "is_flash": true,
                "format": "raw",
                "adm": opts.postParams.adm,
                "plugin": opts.postParams.plugin,
                "action": opts.postParams.action,
                "galid": opts.postParams.galid,
                "swfupload": 1,
                "ajax": 1
            };
        }


        function dbg (text) {
            console.log(text);
        }

        if (typeof opts.file_type_mask == 'string') {
            var masks = opts.file_type_mask.split(',');
            var tmp = [];
            for (var i = 0; i < masks.length; ++i) {
                if (masks[i] != '' && masks[i] != '*.*') {
                    tmp.push(masks[i]);
                }
            }

            if (tmp.length) {
                opts.file_type_mask = tmp.join(';');
            }
            else {
                opts.file_type_mask = '*.*';
            }
        }
        else {
            opts.file_type_mask = '*.*';
        }


        if (!uploadform.find('#start-upload-btn').length) {
            $('<span id="start-upload-btn" />').append('Upload').insertAfter(uploadform.find('span#' + button_id))
        }

        uploadButton = uploadform.find('#start-upload-btn');

        uploadButton.hide();
        uploadButton.unbind().on('click', function () {
            for (var x = 0; x < self.swfUpload.length; ++x) {
                if (self.swfUpload[x] && self.swfUpload[x].winID && self.swfUpload[x].winID === Win.windowID) {
                    SWFUpload.instances[self.swfUpload[x].swf.movieName].startUpload();
                }
            }
        });

        var relID = 0;


        var swfUpload = new SWFUpload({
            // general configuration
            minimum_flash_version: '9.0.28',
            upload_url: url + options.url + '?swfupload_sid=' + session_id + '&sid=' + session_id,
            flash_url: url + 'public/html/js/swfupload/swfupload.swf',
            prevent_swf_caching: false,
            file_post_name: (options.filePostParamName ? options.filePostParamName : 'Filedata'),
            // set the session_id to the post
            post_params: postparams,
            use_query_string: false,
            // button config
            button_placeholder_id: button_id,
            button_action: SWFUpload.BUTTON_ACTION.SELECT_FILES,
            button_disabled: false,
            button_cursor: SWFUpload.CURSOR.HAND,
            button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
            button_width: 60,
            button_height: 27,
            button_text: '<span class="browse-btn" style="' + cssBtnTextString + ';color: #fff">Browse</span>',
            button_text_style: ".browse-btn {" + cssBtnTextString + "}",
            //    button_image_url: url + 'public/html/img/upload_button.png',
            button_text_top_padding: 3,
            button_text_bottom_padding: 3,
            button_text_left_padding: 5,
            button_text_right_padding: 5,
            // file type config
            file_size_limit: upload_max_filesize,
            file_types: opts.file_type_mask,
            file_types_description: opts.file_type_mask,
            file_upload_limit: max_upload_files,
            file_queue_limit: file_queue_limit,
            // debug?
            debug: true,
            // callbacks
            debug_handler: function (data) {
                dbg(data);
            },
            file_dialog_complete_handler: function (selected, queued, totalQueue) {

                if (totalQueue > 0) {
                    total_selected_files = totalQueue;
                    uploadButton.show();
                    //  this.startUpload();
                }
            },
            upload_start_handler: function (file) {
                $('.upload-panel:hidden').remove();
                $('#' + file.id).find('.bar').parent().show();
                $('#' + file.id).find('.bar').empty().addClass('progress-uploading');
            },
            upload_progress_handler: function (file, complete, total) {
                var done = Math.floor((complete / total) * 100);
                $('#' + file.id).find('.bar').css({
                    width: done + '%'
                });
            },
            upload_error_handler: function (file, code, message) {
                switch (code) {
                    case (SWFUpload.UPLOAD_ERROR.HTTP_ERROR) :
                        jAlert('The server did not return a HTTP 200 status code.', 'File Upload Error');
                        break;
                    case (SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL) :
                        jAlert('Upload URL not set, cannot upload file.', 'File Upload Error');
                        break;
                    case (SWFUpload.UPLOAD_ERROR.IO_ERROR) :
                        jAlert('There was an error uploading to the server, perhaps the connection was broken.', 'File Upload Error');
                        break;
                    case (SWFUpload.UPLOAD_ERROR.SECURITY_ERROR) :
                        jAlert('The upload violates a security restriction.', 'File Upload Error');
                        break;
                    case (SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED) :
                        jAlert('You have uploaded too many files.', 'File Upload Error');
                        break;
                    case (SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED) :
                        jAlert('The attempt to initiate the upload caused an error.', 'File Upload Error');
                        break;
                    case (SWFUpload.UPLOAD_ERROR.SPECIFIED_FILE_ID_NOT_FOUND) :
                        jAlert('The file to upload cannot be found.', 'File Upload Error');
                        break;
                    case (SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED) :
                        jAlert('The upload was not started.', 'File Upload Error');
                        break;
                    case (SWFUpload.UPLOAD_ERROR.FILE_CANCELLED) :
                        jAlert('The upload was cancelled.', 'File Upload Error');
                        break;
                    case (SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED) :
                        jAlert('The upload was stopped.', 'File Upload Error');
                        break;
                    default :
                        jAlert('An unknown error occurred while trynig to upload the file.', 'File Upload Error');
                }
            },
            upload_success_handler: function (file, data, response) {

                if (data.length == 0) {
                    jAlert('No server response for uploaded file.', 'File Upload Error');
                }
                else {
                    if (typeof data == 'string' && data.match(/<html/i)) {
                        //jAlert(data, 'File Upload Error');
                        alert(" " + data);
                        return;
                    }

                    total_selected_files--;
                    var data0 = eval("(" + data + ")");

                    if (Tools.responseIsOk(data) || Tools.responseIsOk(data0)) {
                        $('#' + file.id).find('.progress').removeClass('progress-uploading').html('Upload fertig');
                        $('#' + file.id).addClass('upload-done');
                        $('#' + file.id).find('img').attr({
                            src: cmsurl + 'public/html/img/form-ok.png'
                        });

                        if (typeof opts.onSuccess === 'function') {
                            opts.onSuccess(data, data0, file);
                        }

                        if (opts.type == 'gal') {
                            // addImgContainer(data.thumbid);
                            window.setTimeout('$("#' + file.id + '").hide("fast");', 600);
                        }
                        else {
                            window.setTimeout('$("#' + file.id + '").hide("slow");', 1000);
                        }

                        if (total_selected_files > 0) {
                            this.startUpload();
                        }
                        else {
                            postparams.removeRelSession = 1;

							if (typeof postparams.token == 'undefined' ) {
								postparams.token = Config.get('token');
							}

                            $.post(url + opts.url, postparams, function (dat) {
                            });
                        }
                    }
                    else {
                        $('#' + file.id).find('.progressbar').remove();
                        $('#' + file.id).addClass('upload-error');
                        $('#' + file.id).find('.progressbar').append(data.error);

                        if (opts.type == 'gal') {
                            window.setTimeout('$("#' + file.id + '").hide("fast");', 600);
                        }
                        else {
                            window.setTimeout('$("#' + file.id + '").hide("slow");', 2000);
                        }

                        if (total_selected_files > 0) {
                            this.startUpload();
                        }
                        else {
                            postparams.removeRelSession = 1;
							if (typeof postparams.token == 'undefined' ) {
								postparams.token = Config.get('token');
							}
                            $.post(url + opts.url, postparams, function (dat) {
                            });
                        }
                    }
                }

                current = file.index + 1;

            },
            file_queued_handler: function (file) {
                writeNode(file, false);
            },
            file_queue_error_handler: function (file, code, message) {
                switch (code) {
                    case (SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) :
                        jAlert('The file upload queue limit has been exceeded.', 'File Upload Error');
                        break;
                    case (SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT) :
                        jAlert('The file is larger than the allowed size limit for this upload (%s).'.replace('%s', upload_max_filesizeString), 'File Upload Error');
                        break;
                    case (SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE) :
                        jAlert('The file has no content and cannot be uploaded.', 'File Upload Error');
                        break;
                    case (SWFUpload.QUEUE_ERROR.INVALID_FILETYPE) :
                        jAlert('This type of file cannot be uploaded here.', 'File Upload Error');
                        break;
                    default :
                        jAlert('An unknown error occurred while trynig to upload the file.', 'File Upload Error');
                }

                writeNode(file, true, message);
            }
        });


        this.swfUpload.push({
            winID: Win.windowID, swf: swfUpload
        });


        writeNode = function (file, error, message) {
            if (error) {
                if (message)
                    jAlert(message);
                else {
                    //    console.log('Upload Error');
                }
            }
            else {
                var upload_tpl = $('<li class="working upload-panel" id="' + file.id + '">'
                        + '<div class="progressbar"><div class="bar" style="width:0%;"></div></div>'
                        + '<div class="file-info"></div><span class="start"></span><span class="cancel"></span></li>');

                upload_tpl.find('.file-info').append($('<span class="filename"/>').text(file.name));
                upload_tpl.find('.file-info').append($('<span class="filesize"/>').text(Tools.formatSize(file.size)));

                if (error) {
                    upload_tpl.find('.file-info').append(message);
                    upload_tpl.addClass('upload-error');
                    window.setTimeout('$("#' + file.id + '").hide("fast");', 1000);
                }

                display.append(upload_tpl);
            }
        }

        setSettings = function (_options) {
            options = _options;
        }


    },
    getMime: function (ext) {
        return (this.mimeTypes[ ext.toLowerCase() ] != null ? this.mimeTypes[ ext.toLowerCase() ] : false);
    },
    /**
     * create a multi file upload
     * @param object opts
     * @returns {undefined}
     */
    MultiUploadControl: function (opts)
    {
        var HTML5_UploaderEnabled = (!!window.FileReader || typeof window.FileReader !== 'undefined') && Modernizr.draganddrop;
        var uploadControl;

        opts = opts || {};

        if (typeof opts.control == 'string') {
            uploadControl = $('#' + opts.control);
        }
        else if (typeof opts.control != 'undefined') {
            uploadControl = opts.control;
        }
        else {
            jAlert('Invalid upload Control container', 'error');
            return false;
        }

        if (uploadControl.length == 0) {
            Debug.error('Invalid upload Control container');
            return false;
        }


        /*
         
         if (HTML5_UploaderEnabled)
         {
         */

        var self = this, dropControl = uploadControl;

        if (opts.refresh === true) {
            if (typeof opts.postParams == 'object') {
                dropControl.filedrop({refresh: true, data: opts.postParams});
            }

            return;
        }


        var $ul = dropControl.parents('form:first').find('ul.dropped-files');
        if (!$ul.length)
        {
            Debug.error('Invalid List for dropped files! create a ul.dropped-files element');
            return;
        }


        var uploadform = dropControl.parents('form:first');
        var fallbackInput = dropControl.find('span.browse').parent().find('input');
        $ul.empty().hide();



        dropControl.removeData();
        dropControl.unbind();

        uploadform.unbind();
        uploadform.removeData();

        dropControl.find('input[type=file]').unbind();
        dropControl.find('span.browse').unbind('click.upload');
        dropControl.find('span.browse').bind('click.upload', function () {
            // Simulate a click on the file input button
            // to show the file browser dialog
            $(this).parent().find('input[type=file]').click();
        });

        var fileMaskRegex = '.*';

        if (typeof opts.file_type_mask == 'string') {
            var masks = opts.file_type_mask.split(',');
            fileMaskRegex = '';
            var tmp = [];
            for (var i = 0; i < masks.length; ++i) {
                if (masks[i] != '' && masks[i] != '*.*') {
                    tmp.push(masks[i].replace('*.', ''));
                }
            }

            if (tmp.length) {
                fileMaskRegex = new RegExp('.*\\.(' + tmp.join('|') + ')$', 'i');
            }
            else {
                fileMaskRegex = /.*/;
                fileMaskRegex = new RegExp('.*$', 'i');
                opts.file_type_mask = '*.*';
            }
        }
        else {
            opts.file_type_mask = '*.*';
        }


        if (file_upload_limit == undefined) {
            var file_upload_limit = 1;
        }

        if (file_queue_limit == undefined) {
            var file_queue_limit = 1;
        }

        if (max_upload_files == undefined) {
            var max_upload_files = 1;
        }


        var url,
                cmsurl = Config.get('portalurl'),
                session_id = Desktop.SessionID,
                upload_max_filesize = Config.get('upload_max_filesize', '1M'),
                max_file_uploads = Config.get('max_file_uploads', '1'),
                post_max_size = Config.get('post_max_size', '1M');


        upload_max_filesize = (opts.max_file_size || upload_max_filesize);
        file_queue_limit = (opts.file_queue_limit || (max_file_uploads || file_queue_limit));
        max_upload_files = (opts.max_upload_files || max_upload_files);

        if (post_max_size < upload_max_filesize) {
            //console.log('Post size is lower as upload_max_filesize size ');
        }


        var warnSet = false;
        var formatedMaxFileSize = this.formatSize(this.unformatSize(upload_max_filesize));


        dropControl.find('span.allowed-filesize').text('Maximale Dateigröße: %s'.replace('%s', formatedMaxFileSize));
        dropControl.find('span.allowed-extensions').text('Erlaubt sind: %s'.replace('%s', opts.file_type_mask));

        if (!dropControl.find('#start-upload-btn').length) {
            $('<span id="start-upload-btn" />').append('Upload').insertAfter(dropControl.find('span.browse'))
        }

        if (!dropControl.find('#cancel-upload-btn').length) {
            $('<span id="cancel-upload-btn" />').append('Abbrechen').insertAfter(dropControl.find('span.browse'))
        }

        var queued = 0; //queued files counter
        var uploadButton = dropControl.find('#start-upload-btn');
        var cancelButton = dropControl.find('#cancel-upload-btn');
        uploadButton.hide();
        cancelButton.hide();

        uploadButton.unbind();
        uploadButton.on('click', function (e) {
            if (parseInt(queued) > 0) {//if queued files

                if (typeof opts.onUploadStart === 'function') {
                    opts.onUploadStart();
                }

                uploadform.on('submit', true);//enabling submit

                dropControl.trigger('upload');

                $(this).hide();
                cancelButton.css({
                    display: 'inline-block'
                });
            }
        });

        cancelButton.unbind();
        cancelButton.on('click', function (e) {

            if ($ul.find('li').length) {
                //if queued files
                dropControl.trigger('cancelAll');
            }

            $(this).hide();
            $ul.empty().hide();

            // all uploads done
            if (typeof opts.onComplite == 'function') {
                opts.onComplite(false);
            }

        });


        var dropbox, message, maxFilesCount;
        var upload_tpl = $('<li class="working">'
                + '<div class="progressbar"><div class="bar" style="width:0%;"></div></div>'
                + '<div class="file-info"></div><div class="control"><span class="start"></span><span class="cancel"></span></div></li>');


        var dropbox = dropControl, message = dropbox.next();

        // dropbox.addClass('dragAndDropUploadZone');

        if (!message.hasClass('upload-message')) {
            message = $('<div class="upload-message dragAndDropUploadZone"></div>');
            message.insertAfter(dropbox);
        }
        else {
            message.addClass('dragAndDropUploadZone');
        }


        if (file_upload_limit == undefined) {
            var file_upload_limit = 1;
        }

        if (file_queue_limit == undefined) {
            var file_queue_limit = 1;
        }

        if (max_upload_files == undefined) {
            var max_upload_files = 1;
        }


        var url,
                cmsurl = Config.get('portalurl'),
                session_id = Desktop.SessionID,
                upload_max_filesize = Config.get('upload_max_filesize', '1M'),
                max_file_uploads = Config.get('max_file_uploads', '1'),
                post_max_size = Config.get('post_max_size', '1M');


        upload_max_filesize = (opts.max_file_size || upload_max_filesize);
        file_queue_limit = (opts.file_queue_limit || (max_file_uploads || file_queue_limit));
        max_upload_files = (opts.max_upload_files || max_upload_files);

        if (post_max_size < upload_max_filesize) {
            // console.log('Post size is lower as upload_max_filesize size ');
        }

        if (cmsurl.substr(cmsurl.length - 1, cmsurl.length) != '/') {
            url = cmsurl + '/';
        }
        else {
            url = cmsurl;
        }


        var _types = opts.file_type_mask || '*.*';
        var types = _types.split(',');

        var postparams = {
            "sid": session_id,
            //    "swfupload_sid": session_id,
            //    "is_flash": true,
            "adm": opts.postParams.adm,
            "action": opts.postParams.action,
            // "setpage": webSite,
            "uploadpath": opts.postParams.uploadpath,
            //     "swfupload": 1,
            "ajax": 1
        };

        if (!opts.postParams.uploadpath) {
            delete postparams.uploadpath;
        }

        if (opts.type == 'gal') {
            postparams = {
                "sid": session_id,
                //   "swfupload_sid": session_id,
                //     "is_flash": true,
                "adm": opts.postParams.adm,
                // "setpage": webSite,
                "plugin": opts.postParams.plugin,
                "action": opts.postParams.action,
                "galid": opts.postParams.galid,
                //         "swfupload": 1,
                "ajax": 1
            };
        }


        if (opts.postParams) {
            postparams = $.extend({}, postparams, opts.postParams);
        }

        if (opts.dropHereLabel && dropControl.find(opts.dropHereLabel).length) {
            dropControl.on('dragenter', function () {
                $(this).find(opts.dropHereLabel).addClass('drag-over');
            }).on('dragover', function () {
                $(this).find(opts.dropHereLabel).addClass('drag-over');
            }).on('dragleave', function () {
                $(this).find(opts.dropHereLabel).removeClass('drag-over');
            }).on('drop', function () {
                $(this).find(opts.dropHereLabel).removeClass('drag-over');
            });
        }



        dropControl.filedrop({
            // The name of the $_FILES entry:
            fallbackInput: fallbackInput,
            paramname: (opts.filePostParamName ? opts.filePostParamName : 'Filedata'),
            autoUpload: false,
            queuewait: 10,
            refresh: 500,
            queuefiles: file_queue_limit,
            maxfiles: max_upload_files,
            maxfilesize: upload_max_filesize,
            url: opts.url,
            data: postparams,
            uploadFinished: function (index, file, response, timeDiff, xhr) {
                var id = gethash(file.name);
                var uploadc = $('li#' + id);

                if (response) {
                    queued--;
                    uploadc.removeClass('working').addClass('done');
                    uploadc.find('.progressbar,.filespeed,.filesize').hide();

                    if (Tools.responseIsOk(response)) {
                        // for external events (eg: create thumb or other functions)
                        if (Tools.isFunction(opts.onSuccess)) {
                            opts.onSuccess(response, null, file, uploadc);
                        }
                        else {
                            setTimeout(function () {
                                uploadc.fadeOut(400, function () {
                                    $(this).remove();
                                });
                            }, 2000);
                        }
                    }
                    else {
                        uploadc.addClass('upload-error');

                        if (Tools.isFunction(opts.onError)) {
                            opts.onError(response, null, file, uploadc);
                        }
                        else {
                            uploadc.find('.progressbar,.filespeed,.filesize').hide();
                            uploadc.find('.filename').empty().append((response.error ? response.error : (response.msg ? response.msg : 'Error')));

                            setTimeout(function () {
                                uploadc.fadeOut(400, function () {
                                    $(this).remove();
                                });
                            }, 4000);
                        }
                    }
                }


                if (!queued) {
                    cancelButton.hide();
                    uploadButton.hide();
					if (typeof postparams.token == 'undefined' ) {
						postparams.token = Config.get('token');
					}
                    postparams.removeRelSession = 1;
                    $.post(url + opts.url, postparams, function (dat) {
                    });
                    // all uploads done
                    if (typeof opts.onComplite == 'function') {
                        opts.onComplite(response, null, file, uploadc);
                    }

                    $ul.hide();
                }


            },
            error: function (err, file) {
                queued--;

                switch (err) {
                    case 'BrowserNotSupported':
                        message = 'Your browser does not support HTML5 file uploads!';
                        break;
                    case 'TooManyFiles':
                        message = 'Too many files! Please select ' + file_queue_limit + ' at most! (configurable)';
                        break;
                    case 'FileTooLarge':
                        message = file.name + ' is too large! Please upload files up to ' + Tools.formatSize(Tools.unformatSize(upload_max_filesize));
                        break;
                    default:
                        message = 'Upload error!'
                        break;
                }


                if (!file) {
                    jAlert(message);

                    if (!queued) {
                        cancelButton.hide();
                        uploadButton.hide();
                    }
                }

                var id = gethash(file.name);
                var uploadc = $('#' + id);

                uploadc.find('.progressbar,.filespeed,.filesize').hide();
                uploadc.find('.filename').text(message);

                if (!queued) {
                    postparams.removeRelSession = 1;
					if (typeof postparams.token == 'undefined' ) {
						postparams.token = Config.get('token');
					}
                    $.post(url + opts.url, postparams, function (dat) {
                    });

                    cancelButton.hide();
                    uploadButton.hide();
                    $ul.hide();
                }
            },
            onCancel: function (file) {
                queued--;

                var id = gethash(file.name);
                var uploadc = $('#' + id);
                uploadc.find('.progressbar,.filespeed,.filesize').hide();


                setTimeout(function () {
                    uploadc.fadeOut(400, function () {
                        $(this).remove();
                    });
                }, 2000);

                if (!queued) {
					if (typeof postparams.token == 'undefined' ) {
						postparams.token = Config.get('token');
					}
                    postparams.removeRelSession = 1;
                    $.post(url + opts.url, postparams, function (dat) {
                    });


                    cancelButton.hide();
                    uploadButton.hide();

                    // all uploads done
                    if (typeof opts.onComplite == 'function') {
                        opts.onComplite(false, null, file, uploadc);
                    }

                    $ul.hide();
                }
            },
            // Called before each upload is started
            beforeEach: function (file) {
                if (!validateFile(file)) {
                    return false;
                }
            },
            uploadStarted: function (file, hash) {

            },
            progressUpdated: function (index, file, currentProgress) {
                var id = gethash(file.name);
                $('#' + id).find('.progressbar').show();
                $('#' + id).find('.bar').css({width: (currentProgress + '%')});
            },
            speedUpdated: function (index, file, speed, loaded, diffTime) {
                var id = gethash(file.name);

                var formatedSpeed = '';
                if (parseFloat(speed) > 1024.0) {
                    formatedSpeed = (parseFloat(speed / 1024).toFixed(2)).toString() + 'MB/s';
                } else if (parseFloat(speed) < 1024.0) {
                    formatedSpeed = (parseFloat(speed).toFixed(2)).toString() + 'KB/s';
                }

                $('#' + id).find('.filespeed').text(formatedSpeed).show();
            },
            uploadAbort: function (e, xhr, file) {
                var id = gethash(file.name);
                $('#' + id).addClass('abort').find('.progressbar,.filespeed,.filesize').hide();
                $('#' + id).find('.filename').text('Upload der Datei `%s` abgebrochen.'.replace('%s', file.name));

                setTimeout(function () {
                    $('#' + id).fadeOut(400, function () {
                        $(this).remove();
                    });
                }, 2000);

                queued--;

                if (!queued) {
                    cancelButton.hide();
                    uploadButton.hide();
					if (typeof postparams.token == 'undefined' ) {
						postparams.token = Config.get('token');
					}
                    postparams.removeRelSession = 1;
                    $.post(url + opts.url, postparams, function (dat) {
                    });

                    if (typeof opts.onComplite == 'function') {
                        opts.onComplite(false, null, file, uploadc);
                    }

                    $ul.hide();

                    if (queued < 0) {
                        queued = 0;

                    }
                }
            },
            add: function (file) {
                if (queued <= 0 && $ul.find('li').length) {
                    $ul.empty();
                    queued = 0;
                }

                if (file.size > Tools.unformatSize(upload_max_filesize)) {
                    Notifier.warn('Die Datei %f ist größer als %s.<br/>Die maximale Dateigröße beträgt %s!'.replace('%s', Tools.formatSize(Tools.unformatSize(upload_max_filesize))).replace('%f', file.name));
                    return false;
                }

                if (!validateFile(file)) {
                    Notifier.warn('Die Datei "%f" hat keine der erlaubten Endung `%s` erlaubt'.replace('%s', _types).replace('%f', file.name));
                    return false;
                }

                queued++;


                var id = gethash(file.name);
                $(upload_tpl).clone(false, false).attr('id', id).data(file).appendTo($ul);

                $('#' + id).find('.file-info').append($('<span class="filename"/>').text(file.name));
                $('#' + id).find('.file-info').append($('<span class="filesize"/>').text(Tools.formatSize(file.size)));
                $('#' + id).find('.file-info').append($('<span class="filespeed"/>'));
                $('#' + id).find('.progressbar .bar').width('0%');

                $ul.show();

                $('#' + id).find('.cancel').click(function (e) {
                    if (dropControl.trigger('cancel', $(e.target).parents('li:first').data()) === true) {
                        queued--;
                        $('#' + id).addClass('abort').find('.progressbar,.filespeed,.filesize').hide();
                        $('#' + id).find('.filename').text('Upload der Datei `%s` abgebrochen.'.replace('%s', file.name));

                        setTimeout(function () {
                            $('#' + id).fadeOut(400, function () {
                                $(this).remove();
                            });
                        }, 2000);

                        if (!queued) {
                            cancelButton.hide();
                            uploadButton.hide();
                            postparams.removeRelSession = 1;
							if (typeof postparams.token == 'undefined' ) {
								postparams.token = Config.get('token');
							}
                            $.post(url + opts.url, postparams, function (dat) {
                            });
                            if (queued < 0) {
                                queued = 0;
                            }
                        }
                    }
                });

                if (typeof opts.onAdd == 'function') {
                    opts.onAdd();
                }

                uploadButton.css({
                    display: 'inline-block'
                });

                return true;
            }
        });


        function gethash (s) {
            var char, hash, i, len, test, _i;
            hash = 0;
            len = s.length;
            if (len === 0) {
                return hash;
            }
            for (i = _i = 0; 0 <= len ? _i <= len : _i >= len; i = 0 <= len ? ++_i : --_i) {
                char = s.charCodeAt(i);
                test = ((hash << 5) - hash) + char;
                if (!isNaN(test)) {
                    hash = test & test;
                }
            }
            return 'file-' + Math.abs(hash);
        }


        function validateFile (file) {
            var currentMime = file.type;
            var regex = '';

            if (currentMime.match(/^image\//)) {
                file.isImage = true;
            }
            else {
                file.isImage = false;
            }

            if (!types.length) {
                return true;
            }

            if (fileMaskRegex) {
                if (!file.name.match(fileMaskRegex) /* fileMaskRegex.test(file.name)*/) {
                    return false;
                }

                return true;
            }

            for (var i = 0; i < types.length; ++i) {
                if (types[i].length) {
                    if (types[i] != '*.*' && types[i]) {
                        var strExt = types[i].split('.');
                        if (!strExt[1]) {
                            continue;
                        }

                        var val = Tools.getMime(strExt[1]);

                        if (val != false) {
                            if (Tools.isObject(val)) {
                                regex += val.join('|');
                            }
                            else if (Tools.isString(val)) {
                                regex += (regex != '' ? '|' + val : val);
                            }
                        }
                    }
                }
            }

            if (regex !== '') {
                regex = regex.replace('/', '\/');
                regex = regex.replace('.', '\.');

                var reg = new RegExp('(' + regex + ')', 'i');
                if (!reg.test(currentMime)) {
                    jAlert('This Filetype is not allowed! Only Filetype: ' + types, 'Upload Error...');
                    // Returning false will cause the
                    // file to be rejected
                    return false;
                }
            }

            return true;
        }


        function createPreview (file, len) {

            if (max_upload_files === 1) {
                $('.dragAndDropUploadZone.preview', $('#' + Win.windowID)).remove();
            }

            var uploadc = $.data(file, 'uploaddata');
            var preview = uploadc, image = $('img', preview);
            var reader = new FileReader();


            preview.find('.item-errormessage').empty().hide();


            if (file.isImage && opts.type != 'gal') {
                reader.onload = function (e) {
                    // e.target.result holds the DataURL which
                    // can be used as a source of the image:
                    image.attr('src', e.target.result);
                    image.attr('width', 100).height(100);
                };
                preview.find('img').hide();
            }
            else {
                preview.find('img').remove();
            }

            // Reading the file as a DataURL. When finished,
            // this will trigger the onload function above:
            reader.readAsDataURL(file);

            message.hide();

            var _after = message;

            if (message.parent().find('.upload-file').length) {
                _after = message.parent().find('.upload-file:last');
            }

            preview.insertAfter(_after);

            // Associating a preview container
            // with the file, using jQuery's $.data():
            //$.data(file, 'uploadcontainer').append(preview);
            //$.data(file, 'preview', preview);
        }

        function showMessage (msg) {
            message.html(msg);
        }

        /*
         return;
         }
         else
         {
         if (typeof SWFUpload == 'undefined')
         {
         Loader.require('public/html/js/swfupload/swfupload.js', function () {
         setTimeout(function () {
         Tools.UploadControl(opts);
         }, 50);
         });
          }
         else
         {
         Tools.UploadControl(opts);
         }
         }
         */
    },
    Blur: function (object, options) {
        var opts = $.extend({}, {
            cache: false

        }, options);
        $.blurjs(opts);
    }

};


// ==========================================
// CookieRegistry

function CookieRegistry () {
    var self = this;
    var registryName = '';
    var rawCookie = '';
    var cookie = {};
    this.initialize = function (name) {
        self.registryName = name;
        name = name + '=';
        var cookies = document.cookie.split(';');
        for (i = 0; i < cookies.length; i++) {
            var cookie = cookies[i];
            while (cookie.charAt(0) == ' ')
                cookie = cookie.substring(1, cookie.length);
            if (cookie.indexOf(name) == 0)
                self.rawCookie = decodeURIComponent(cookie.substring(name.length, cookie.length));
        }
        if (self.rawCookie) {
            self.cookie = eval('(' + self.rawCookie + ')');
        }
        else {
            self.cookie = {};
        }
        self.write();
    };
    this.get = function (name, def) {
        def = typeof def != 'undefined' ? def : false;
        return typeof self.cookie[name] != 'undefined' ? self.cookie[name] : def;
    };
    this.set = function (name, value) {
        self.cookie[name] = value;
        self.write();
    };
    this.erase = function (name) {
        if (name) {
            delete self.cookie[name];
        }
        else {
            self.cookie = {};
        }
        self.write();
    };
    this.encode = function () {
        var results = [];
        for (var property in self.cookie) {
            var value = self.cookie[property];
            if (typeof value != "number" && typeof value != "boolean") {
                value = '"' + value + '"';
            }
            results.push('"' + property + '":' + value);
        }
        return '{' + results.join(', ') + '}';
    };
    this.write = function () {
        var date = new Date();
        date.setTime(date.getTime() + Config.get('cookie_timer', 3600));
        var expires = "; expires=" + date.toGMTString();
        document.cookie = self.registryName + "=" + self.encode() + expires + "; path=/";
    };
}
var Cookie = new CookieRegistry;
Cookie.initialize(cookiePrefix + '_registry');

/* Copyright (c) 2006 Mathias Bank (http://www.mathias-bank.de)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) 
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * 
 * Thanks to Hinnerk Ruemenapf - http://hinnerk.ruemenapf.de/ for bug reporting and fixing.
 */
jQuery.extend({
    /**
     * Returns get parameters.
     *
     * If the desired param does not exist, null will be returned
     *
     * @example value = $.getURLParam("paramName");
     */
    getURLParam: function (strParamName, str) {
        var strReturn = "";
        var strHref = window.location.href;
        var bFound = false;
        if (typeof str == 'string') {
            strHref = str;
        }
        var cmpstring = strParamName + "=";
        var cmplen = cmpstring.length;
        if (strHref.indexOf("?") > -1) {
            var strQueryString = strHref.substr(strHref.indexOf("?") + 1);
            var aQueryString = strQueryString.split("&");
            for (var iParam = 0; iParam < aQueryString.length; iParam++) {
                if (aQueryString[iParam].substr(0, cmplen) == cmpstring) {
                    var aParam = aQueryString[iParam].split("=");
                    strReturn = aParam[1];
                    bFound = true;
                    break;
                }
            }
        }
        if (bFound == false)
            return null;
        return strReturn;
    }
});


(function ($) {
    var _sleeptimer;
    $.sleep = function (time2sleep, callback) {
        $.sleep._sleeptimer = time2sleep;
        $.sleep._cback = callback;
        $.sleep.timer = setInterval('$.sleep.count()', 1000);
    };

    $.extend($.sleep, {
        current_i: 1,
        _sleeptimer: 0,
        _cback: null,
        timer: null,
        count: function () {
            if ($.sleep.current_i === $.sleep._sleeptimer) {
                clearInterval($.sleep.timer);
                $.sleep._cback.call(this);
            }
            $.sleep.current_i++;
        }
    });
})(jQuery);


/*
 (function (factory) {
 
 if (typeof exports == 'object') {
 // CommonJS
 factory(require('jquery'), require('spin'))
 }
 else if (typeof define == 'function' && define.amd) {
 // AMD, register as anonymous module
 define(['jquery', 'spin'], factory)
 }
 else {
 // Browser globals
 if (!window.Spinner)
 throw new Error('Spin.js not present')
 factory(window.jQuery, window.Spinner)
 }
 
 }(function ($, Spinner) {
 
 $.fn.spin = function (opts, color) {
 
 return this.each(function () {
 var $this = $(this),
 data = $this.data();
 
 if (data.spinner) {
 data.spinner.stop();
 delete data.spinner;
 }
 if (opts !== false) {
 opts = $.extend(
 {color: color || $this.css('color')},
 $.fn.spin.presets[opts] || opts
 )
 data.spinner = new Spinner(opts).spin(this)
 }
 })
 }
 
 $.fn.spin.presets = {
 tiny: {lines: 8, length: 2, width: 2, radius: 3},
 small: {lines: 8, length: 4, width: 3, radius: 5},
 large: {lines: 10, length: 8, width: 4, radius: 8}
 }
 
 }));
 
 
 */
/*********************************************************************
 SCRIPT SECTION:    pagemask
 Displays a page mask. Borrows code and options from
 $.alerts and $.fn.mask. Thanks!
 *********************************************************************/
(function ($) {


    /**
     * Copyright 2012, Digital Fusion
     * Licensed under the MIT license.
     * http://teamdf.com/jquery-plugins/license/
     *
     * @author Sam Sehnert
     * @desc A small plugin that checks whether elements are within
     *       the user visible viewport of a web browser.
     *       only accounts for vertical position, not horizontal.
     */
    var $w = $(window);

    $.fn.visible = function (partial, hidden, direction) {

        if (this.length < 1)
            return;

        var $t = this.length > 1 ? this.eq(0) : this,
                t = $t.get(0),
                vpWidth = $w.width(),
                vpHeight = $w.height(),
                direction = (direction) ? direction : 'both',
                clientSize = hidden === true ? t.offsetWidth * t.offsetHeight : true;

        if (typeof t.getBoundingClientRect === 'function') {

            // Use this native browser method, if available.
            var rec = t.getBoundingClientRect(),
                    tViz = rec.top >= 0 && rec.top < vpHeight,
                    bViz = rec.bottom > 0 && rec.bottom <= vpHeight,
                    lViz = rec.left >= 0 && rec.left < vpWidth,
                    rViz = rec.right > 0 && rec.right <= vpWidth,
                    vVisible = partial ? tViz || bViz : tViz && bViz,
                    hVisible = partial ? lViz || lViz : lViz && rViz;

            if (direction === 'both')
                return clientSize && vVisible && hVisible;
            else if (direction === 'vertical')
                return clientSize && vVisible;
            else if (direction === 'horizontal')
                return clientSize && hVisible;
        } else {

            var viewTop = $w.scrollTop(),
                    viewBottom = viewTop + vpHeight,
                    viewLeft = $w.scrollLeft(),
                    viewRight = viewLeft + vpWidth,
                    offset = $t.offset(),
                    _top = offset.top,
                    _bottom = _top + $t.height(),
                    _left = offset.left,
                    _right = _left + $t.width(),
                    compareTop = partial === true ? _bottom : _top,
                    compareBottom = partial === true ? _top : _bottom,
                    compareLeft = partial === true ? _right : _left,
                    compareRight = partial === true ? _left : _right;

            if (direction === 'both')
                return !!clientSize && ((compareBottom <= viewBottom) && (compareTop >= viewTop)) && ((compareRight <= viewRight) && (compareLeft >= viewLeft));
            else if (direction === 'vertical')
                return !!clientSize && ((compareBottom <= viewBottom) && (compareTop >= viewTop));
            else if (direction === 'horizontal')
                return !!clientSize && ((compareRight <= viewRight) && (compareLeft >= viewLeft));
        }
    };


    $.fn.buildColorPicker = function (options) {
        return this.each(function () {
            var $this = $(this);

            if (!$this.parent().hasClass('input-append')) {
                $this.wrap('<div class="input-append color"/>')
                $('<span class="add-on"><i></i></span>').insertAfter($this);
            }

            var color = $this.val();
            if (options.color != '') {
                color = '#' + options.color;
            }
            $this.addClass('colorpicker-input');
            $this.val(color.replace('#', ''));
            $this.next().find('i').css({backgroundColor: '#' + color.replace('#', '')}).on('click', function () {

                $(document).find('input.colorpicker-input').ColorPicker('hide');
                $this.ColorPicker('show');

                $(document).unbind('click.colorpicker');
                $(document).bind('click.colorpicker', function (e) {
                    if (!$(e.target).parents('div.colorpicker').length && !$(e.target).parents('div.input-append').length) {
                        $this.ColorPicker('hide');


                    }
                });

            });

            $this.ColorPicker({isInput: false, format: 'hex'}).on('changeColor', function (ev) {
                $this.next().find('i').css({backgroundColor: ev.color.toHex()});
                $this.val(ev.color.toHex().replace('#', ''));
            }).on('hide', function (ev) {
                $this.val(ev.color.toHex().replace('#', ''));
            });


            // $this.ColorPicker('isInput',  false);
            $this.ColorPicker('setValue', '#' + color.replace('#', ''));
            $this.unbind('click focus');
        });
    };


    $.fn.triggerAll = function () {
        return this.each(function () {
            var $this = $(this);
            var $data = $this.data('events');
            if ($data) {
                $.each($data, function (k, v) {
                    $this.trigger(k);
                });
            }
        });
    };


    // 
    $.fn.disableTextSelection = function () {
        return this.each(function () {

            if ($(this).get(0).tagName == 'SELECT' || $(this).get(0).tagName == 'INPUT') {
                return;
            }

            $(this).find('input,textarea').css({
                '-moz-user-select': 'all',
                '-webkit-user-select': 'all',
                'user-select': 'all',
                '-ms-user-select': 'all',
                cursor: 'auto'
            });

            $(this).css({
                '-moz-user-select': 'none',
                '-webkit-user-select': 'none',
                'user-select': 'none',
                '-ms-user-select': 'none',
                cursor: 'default'
            });
        });
    };


    $.fn.getWindowHash = function (url) {
        url = url.replace(Config.get('portalurl') + '/', '');
        url = url.replace('&amp;', '&');
        return 'w' + Strings.truncate(HashGen.md5(url), 20);
    };


    $.pagemask = {
        show: function (label) {
            $.alerts._overlay('hide');
            $("body").append($('<div id="popup_overlay"></div>'));
            $("#popup_overlay").css(
                    {
                        position: 'absolute',
                        zIndex: 50000,
                        top: '0',
                        left: '0',
                        width: '100%',
                        'height': $(window).height()
                    }).hide();


            if (typeof label != "string") {
                label = cmslang.loading;
            }
            if (typeof label == "string") {
                var maskMsgDiv = $('<div class="loadmask-msg" id="popup_overlay_msg" style="display:none;"></div>');
                maskMsgDiv.append('<div>' + label + '</div>');
                $('body').append(maskMsgDiv);
                maskMsgDiv.css(
                        {
                            zIndex: 99990,
                            width: maskMsgDiv.width(),
                            height: maskMsgDiv.height(),
                            position: 'relative',
                            left: '50%',
                            top: '40%',
                            marginLeft: 0 - Math.floor(maskMsgDiv.outerWidth() / 2)
                        });
                maskMsgDiv.show();
            }
            var maskRmvDiv = $('<div class="loadmask-remove" id="popup_overlay_remove" style="display:none;"></div>');


            maskRmvDiv.append(
                    $('<img>').attr({
                src: Config.get('backendImagePath') + 'cancel.png',
                width: 16,
                height: 16,
                title: ''
            })
                    );

            $('body').append(maskRmvDiv);
            maskRmvDiv.css(
                    {
                        opacity: .7,
                        zIndex: 50000,
                        width: 16,
                        height: 16,
                        position: 'fixed',
                        right: '10px',
                        top: '10px',
                        cursor: 'pointer'
                    }).hide();
            maskRmvDiv.hover(
                    function () {
                        $(this).css(
                                {
                                    opacity: 1
                                });
                    }, function () {
                $(this).css(
                        {
                            opacity: .7
                        });
            });
            maskRmvDiv.bind('click', function () {
                $.pagemask.hide();
            });
            $('#popup_overlay,#popup_overlay_remove').fadeIn(300);
        },
        hide: function () {
            $('#popup_overlay,#popup_overlay_remove').fadeOut(300);
            $("#popup_overlay").remove();
            $("#popup_overlay_msg").remove();
            $("#popup_overlay_remove").remove();
        }
    };


    /**
     * Displays loading mask over selected element.
     *
     * @param label Text message that will be displayed on the top of a mask besides a spinner (optional).
     *              If not provided only mask will be displayed without a label or a spinner.
     */
    $.fn.mask = function (label, timeout) {
        if ($(this).hasClass('masked')) {
            return this;
        }

        if (typeof timeout != 'undefined' && timeout > 0) {
            var element = $(this);

            element.data("_mask_timeout", setTimeout(function () {
                $.maskElement(element, label);
            }, timeout));
        }
        else {
            $.fn.maskElement($(this), label);
        }

        return this;
    };


    $.fn.maskElement = function (element, label) {
        //if this element has delayed mask scheduled then remove it and display the new one
        if (element.data("_mask_timeout") !== undefined) {
            clearTimeout(element.data("_mask_timeout"));
            element.removeData("_mask_timeout");
        }

        if (element.hasClass("masked")) {
            $.fn.unmask(element);
        }
        element.addClass("masked");
        var height = element.outerHeight(true);
        var width = element.outerWidth(true);

        element.addClass("masked");

     //   var height = element.outerHeight(true);
     //   var width = element.outerWidth(true);
        var mask = $('#masking').clone();
        mask.removeAttr('id');
        mask.css({
            zIndex: 99998
        }).addClass('masking').show();

        mask.appendTo(element);


        if (typeof label == "string" && label != '') {
            $('#masking-msg', mask).css({
                zIndex: 99999
            }).append(label);

            //calculate center position
            $('#masking-msg', mask)
                    .css("top", (mask.height() / 2))
                    .css("left", (mask.outerWidth(true) / 2) - ($('#masking-msg', mask).outerWidth(true) / 2));
        }
        else {
            $('#masking-msg', mask).remove();
        }



        return element;


        var maskDiv = $('<div class="loadmask">');

        maskDiv.css({
            zIndex: 99998
        });



        maskDiv.height(height).width(width).show();
        element.append(maskDiv);

        if (typeof label == "string" && label != '') {
            var bgPath, maskMsgDiv = $('<div class="loadmask-msg"></div>');
            var labelDiv = $('<div/>');

            var cloned = $('body').find('#load-indicator-small').clone();

            if (cloned.length) {
                cloned.removeAttr('id');
                cloned.show().appendTo(labelDiv);
            }
            else {
                labelDiv.append('<span class="load-indicator"></span>');
            }

            labelDiv.append($('<span class="load-msg"></span>').append(label));
            maskMsgDiv.append(labelDiv);
            element.append(maskMsgDiv);

            //calculate center position
            maskMsgDiv.css("top", Math.round(element.height() / 2 - maskMsgDiv.height()));

            //maskMsgDiv.css("top", '50%');
            maskMsgDiv.css("left", Math.round(element.outerWidth(true) / 2 - (maskMsgDiv.width() - parseInt(maskMsgDiv.css("padding-left"), 10) - parseInt(maskMsgDiv.css("padding-right"), 10)) / 2) + "px");


            maskMsgDiv.css({
                zIndex: 99999
            });


            maskMsgDiv.show();
        }

        return element;

    };

    /**
     * Checks if a single element is masked. Returns false if mask is delayed or not displayed.
     */
    $.fn.isMasked = function () {
        return this.hasClass("masked");
    };

    /**
     * Removes mask from the element.
     */
    $.fn.unmask = function () {

        if ($(this).attr('id') == 'maincontent') {
            var self = this;
            $(this).parent().each(function () {
                $.unmaskElement($(self).parent());
            });
        }
        else {
            $.unmaskElement($(this));
        }

    };

    $.unmaskElement = function (element) {
        //if this element has delayed mask scheduled then remove it
        if (typeof element.data("_mask_timeout") != 'undefined') {
            clearTimeout(element.data("_mask_timeout"));
            element.removeData("_mask_timeout");
        }

        element.find('.masking,.loadmask').hide().remove();
        element.removeClass("masked").removeClass("masked-relative");
        $("select", element).removeClass("masked-hidden");
    };

    $.fn.disableButton = function () {
        return this.each(function () {
            //   if ($(this).hasClass('pretty-button') || $(this).hasClass('action-button'))
            //   {
            $(this).attr('disabled', 'disabled');
            $(this).addClass('button-disabled');
            //   }
        });
    };

    $.fn.enableButton = function () {
        return this.each(function () {
            //   if ($(this).hasClass('pretty-button') || $(this).hasClass('action-button'))
            //   {
            $(this).removeAttr('disabled');
            $(this).removeClass('button-disabled');
            //   }
        });
    };


    $.fn.disableContext = function () {
        return this.each(function () {
            $(this).attr('disabled', 'disabled');
            $(this).addClass('disabled');
        });
    };

    $.fn.enableContext = function () {
        return this.each(function () {
            // if($(this).hasClass('pretty-button') || $(this).hasClass('action-button')) {
            $(this).removeAttr('disabled');
            $(this).removeClass('disabled');
            //  }
        });
    };
    $.fn.cleardefault = function () {
        return this.focus(function () {
            if (this.value == this.defaultValue) {
                this.value = "";
            }
        }).blur(function () {
            if (!this.value.length) {
                this.value = this.defaultValue;
            }
        });
    };


    $.fn.modal = function (opt) {
        return this.each(function () {

            Tools.createPopup($(this).html(), {
                modal: true
            });

        });
    };

    /**
     * jQuery-Plugin "addTab"
     * by Marcel Domke
     */

    $.fn.dcmsAddTab = function (label, options) {

        var settings = jQuery.extend({
            tab_container: null, // object
            tabcontent_container: null, // object
            pos: 'after'
        }, options);

        var self = $(this);


        var tabCounter = 0;
        var id = $(this).find('.tab:not(.add-tab):last').attr('id');
        var x = id.replace('tab-', '');
        tabCounter = x;
        tabCounter++;

        var icon = $('<span class="icon"></span>');
        var labeltab = $('<a href="#tab-content-' + tabCounter + '"></a>');
        labeltab.append(label);

        var _tab = $('<li id="tab-' + tabCounter + '" class="tab"></li>');

        _tab.append(labeltab);
        _tab.append(icon);

        var tab_content = $('<div id="tab-content-' + tabCounter + '" class="tab-content"><ul class="sortable"></ul></div>');

        if (settings.pos == 'after') {
            _tab.insertAfter('#tab-' + x);
        }
        else {
            $(self).prepend(_tab);
        }

        $(settings.tabcontent_container).append(tab_content);

        /*
         $(settings.tabcontent_container).find('#tab-content-'+ tabCounter).hide();
         
         $(self).find('.active').removeClass('active');
         $(settings.tabcontent_container).find('.tab-content').hide();
         
         
         $(self).find('#tab-'+ tabCounter).addClass('active');
         $(settings.tabcontent_container).find('#tab-content-'+ tabCounter).show();
         
         $('#tab-'+ tabCounter ).unbind('click');
         $('#tab-'+ tabCounter ).click(function() {
         $(self).find('.tab').removeClass('active');
         $(settings.tabcontent_container).find('.tab-content').hide();
         $('#tab-'+tabCounter ).addClass('active');
         $('#tab-content-'+ tabCounter).show();
         });
         */
        return false;
    };

    /**
     * Removes mask from the element.
     */
    $.fn.removeTab = function () {
        this.find(".loadmask-msg").remove();
        this.find(".loadmask").remove();
        this.removeClass("masked");
        this.removeClass("masked-relative");
    };

})(jQuery);

/*
 * old functions
 */

function openTab (opt /*iconIn, label, isSingleWindow*/) {
    var icon = null, opts = {};

    if (!opt.url) {
        Debug.log('No has giving! Cancel openTab()');
        return;
    }


    if (Tools.exists(opt, 'isSingleWindow') && opt.isSingleWindow) {
        var currentWindow = Desktop.getActiveWindow();
        if (!currentWindow.data('WindowManager')) {
            return;
        }
    }

    opts.loadWithAjax = true;
    opts.allowAjaxCache = false;
    opts.WindowToolbar = false;
    opts.DesktopIconWidth = 36;
    opts.DesktopIconHeight = 36;
    opts.UseWindowIcon = false;
    opts.addFileSelector = opt.addFileSelector || false;


    var appData = Tools.extractAppInfoFromUrl(opt.url);
    opts.Controller = appData.controller;
    opts.Action = appData.action;


    if (typeof opt.obj == 'object') {
        if ($(opt.obj).attr('src')) {
            icon = $(opt.obj).attr('src');
        }
        else if ($(opt.obj).css('background-image')) {
            var bgstr = $(opt.obj).css('background-image').trim();
            icon = bgstr.replace(/.*(url\s*\(([a-z0-9_\-\.:\/"']+?)\)).*/gi, '$2');

        }
        else if ($(opt.obj).css('background')) {
            var bgstr = $(opt.obj).css('background').trim();
            icon = bgstr.replace(/.*(url\s*\(([a-z0-9_\-\.:\/"']+?)\)).*/gi, '$2');
        }
        else {
            icon = '';
        }
    }
    else {
        if (typeof opt.obj == 'string') {
            icon = opt.obj;
        }
    }


    // Desktop.ajaxData = data ;
    opts.Skin = Desktop.settings.Skin;
    opts.WindowTitle = opt.label;
    opts.WindowDesktopIconFile = (icon ? icon : '');
    opts.WindowURL = Tools.prepareAjaxUrl(opt.url);
    opts.isSingleWindow = (opt.isSingleWindow == true ? true : false);
    opts.onAfterOpen = opt.onAfterOpen;
    opts.beforeShow = opt.onBeforeShow || false;
    opts.versioning = (typeof opt.versioning == 'string' && opt.versioning != '' ? opt.versioning : false);

    opts.opener = opt.opener || false;


    if (Tools.exists(opt, 'isSingleWindow') && opt.isSingleWindow) {
        if (currentWindow.data('WindowManager').get('isSingleWindow')) {
            opts.isSingleWindow = true;
        }
    }

    // reset current ajaxData
    Desktop.ajaxData = {};

    // Window exists?
    var hash = Desktop.getHash(Tools.prepareAjaxUrl(opts.WindowURL));
    var wFound = $('#' + hash);
    if (wFound.length == 1 && !wFound.data('WindowManager').get('isSingleWindow') && !opts.isSingleWindow) {
        // Timeout for button click events
        setTimeout(function () {
            wFound.data('WindowManager').focus();
            document.body.style.cursor = 'default';
        }, 50);

        return;
    }


    $('#desktop').mask('Bitte warten...');
    setTimeout(function () {

        var activeWin = Desktop.getActiveWindow();
        var openerid = (activeWin.length ? activeWin.attr('id') : false);

        if (openerid) {

            var instance = activeWin.data('WindowManager');
            if (!instance.get('isSingleWindow') && opts.isSingleWindow) {
                instance.set('isSingleWindow', opts.isSingleWindow);
                console.log('isSingleWindow');

                opts.SingleWindowID = openerid;

            }
            else if (instance.get('isSingleWindow')) {
                opts.SingleWindowID = openerid;
            }

        }

        Desktop.getAjaxContent(opts, function (data) {

            if (Tools.responseIsOk(data)) {
                opts.rollback = data.rollback || false;
                opts.addFileSelector = data.addFileSelector || false;

                if (Tools.exists(data, 'pageCurrentTitle') && data.pageCurrentTitle != '') {
                    opts.WindowTitle = data.pageCurrentTitle;
                }

                if (Tools.exists(data, 'versioning') && data.versioning != '') {
                    opts.versioning = data.versioning;
                }

                Application.cacheCurrentApp(opts.Controller, opts.Action, data);

                if (typeof data.toolbar != 'undefined') {
                    opts.WindowToolbar = data.toolbar;
                }

                Desktop.GenerateNewWindow(opts, null, function (obj, objdata, id) {
                    obj.unmask();

                    if (opts.opener) {
                        obj.attr('opener', opts.opener);
                    }

                    $('#desktop').unmask();

                    if (openerid) {
                        objdata.set('opener', openerid);
                        obj.attr('opener', openerid);
                    }

                    obj.attr('app', opts.Controller);

                    Application.currentWindowID = id;
                    Application.createAppMenu(opts.Controller, opts.Action, opts);

                    document.body.style.cursor = 'default';


                    if (typeof opts.onAfterOpen == 'function') {
                        opts.onAfterOpen();
                    }


                    if (opts.SingleWindowID) {
                        if (Tools.exists(data, 'pageCurrentTitle') && data.pageCurrentTitle != '' && activeWin.length) {
                            activeWin.data('WindowManager').setTitle(data.pageCurrentTitle);
                        }
                    }
                });
            }
            else {

            }

        });
    }, 80);
}


//==========================================
// Publish/Unpublish per Ajax
function changePublish (imageid, url, callback) {
    var orgsrc = $('#' + imageid).attr('src');
    $('#' + imageid).attr('src', Config.get('backendImagePath', '') + 'loading.gif');
    url = url.replace("/&amp;/", "&");
    url = url + '&ajax=1';

    setTimeout(function () {

        $.get(url, {}, function (data) {
            if (Tools.responseIsOk(data)) {
                if (typeof listViewTbl != "undefined" && listViewTbl != '' && listViewTbl != null) {
                    eval(listViewTbl + '.Reload(\'' + document.location + '\')');
                }
                else {
                    console.log(typeof callback);


                    if (data.msg && data.msg == '0') {
                        $('#' + imageid).attr('src', Config.get('backendImagePath', '') + 'offline.gif');
                        if (typeof callback == 'function') {
                            callback();
                        }
                        return false;
                    }

                    if (data.msg && data.msg == '1') {
                        $('#' + imageid).attr('src', Config.get('backendImagePath', '') + 'online.gif');
                        if (typeof callback == 'function') {
                            callback();
                        }
                        return false;
                    }

                    if (orgsrc.match(/online\.gif/ig)) {
                        $('#' + imageid).attr('src', Config.get('backendImagePath', '') + 'offline.gif');
                    }
                    else {
                        $('#' + imageid).attr('src', Config.get('backendImagePath', '') + 'online.gif');
                    }

                    if (typeof data.msg != "undefined") {
                        Notifier.info(data.msg);
                    }

                    /*
                     
                     if (typeof data.msg != "undefined")
                     {
                     
                     if (parseInt(data.msg) == 1)
                     {
                     $('#' + imageid).attr('src', Config.get('backendImagePath', '') + 'online.gif');
                     if (typeof callback == 'function')
                     {
                     callback();
                     }
                     return false;
                     }
                     else
                     {
                     $('#' + imageid).attr('src', Config.get('backendImagePath', '') + 'offline.gif');
                     if (typeof callback == 'function')
                     {
                     callback();
                     }
                     return false;
                     }
                     }
                     */

                    if (typeof callback == 'function') {
                        callback();
                    }
                }
            }
            else {
                $('#' + imageid).attr('src', orgsrc);
                alert("Error:\r\n" + data.msg);
            }
        }, 'json');
    }, 100);


    return false;

}


(function ($) {
    $.fn.blurjs = function (options) {
        var canvas = document.createElement('canvas');
        var isCached = false;
        var selector = ($(this).selector).replace(/[^a-zA-Z0-9]/g, "");
        if (!canvas.getContext) {
            Debug.log('Blur is unsupported');
            return;
        }

        options = $.extend({
            source: 'body',
            radius: 5,
            overlay: '',
            offset: {
                x: 0,
                y: 0
            },
            optClass: '',
            cache: false,
            cacheKeyPrefix: 'blurjs-',
            draggable: false,
            debug: false
        }, options);
        // Stackblur, courtesy of Mario Klingemann: http://www.quasimondo.com/StackBlurForCanvas/StackBlurDemo.html
        var mul_table = [512, 512, 456, 512, 328, 456, 335, 512, 405, 328, 271, 456, 388, 335, 292, 512, 454, 405, 364, 328, 298, 271, 496, 456, 420, 388, 360, 335, 312, 292, 273, 512, 482, 454, 428, 405, 383, 364, 345, 328, 312, 298, 284, 271, 259, 496, 475, 456, 437, 420, 404, 388, 374, 360, 347, 335, 323, 312, 302, 292, 282, 273, 265, 512, 497, 482, 468, 454, 441, 428, 417, 405, 394, 383, 373, 364, 354, 345, 337, 328, 320, 312, 305, 298, 291, 284, 278, 271, 265, 259, 507, 496, 485, 475, 465, 456, 446, 437, 428, 420, 412, 404, 396, 388, 381, 374, 367, 360, 354, 347, 341, 335, 329, 323, 318, 312, 307, 302, 297, 292, 287, 282, 278, 273, 269, 265, 261, 512, 505, 497, 489, 482, 475, 468, 461, 454, 447, 441, 435, 428, 422, 417, 411, 405, 399, 394, 389, 383, 378, 373, 368, 364, 359, 354, 350, 345, 341, 337, 332, 328, 324, 320, 316, 312, 309, 305, 301, 298, 294, 291, 287, 284, 281, 278, 274, 271, 268, 265, 262, 259, 257, 507, 501, 496, 491, 485, 480, 475, 470, 465, 460, 456, 451, 446, 442, 437, 433, 428, 424, 420, 416, 412, 408, 404, 400, 396, 392, 388, 385, 381, 377, 374, 370, 367, 363, 360, 357, 354, 350, 347, 344, 341, 338, 335, 332, 329, 326, 323, 320, 318, 315, 312, 310, 307, 304, 302, 299, 297, 294, 292, 289, 287, 285, 282, 280, 278, 275, 273, 271, 269, 267, 265, 263, 261, 259];
        var shg_table = [9, 11, 12, 13, 13, 14, 14, 15, 15, 15, 15, 16, 16, 16, 16, 17, 17, 17, 17, 17, 17, 17, 18, 18, 18, 18, 18, 18, 18, 18, 18, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24];

        function stackBlurCanvasRGB (a, b, c, d, f, g) {
            if (isNaN(g) || g < 1)
                return;
            g |= 0;
            var h = a.getContext("2d");
            var j;
            try {
                try {
                    j = h.getImageData(b, c, d, f)
                } catch (e) {
                    try {
                        netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
                        j = h.getImageData(b, c, d, f)
                    } catch (e) {
                        alert("Cannot access local image");
                        throw new Error("unable to access local image data: " + e);
                    }
                }
            } catch (e) {
                alert("Cannot access image");
                throw new Error("unable to access image data: " + e);
            }
            var k = j.data;
            var x, y, i, p, yp, yi, yw, r_sum, g_sum, b_sum, r_out_sum, g_out_sum, b_out_sum, r_in_sum, g_in_sum, b_in_sum, pr, pg, pb, rbs;
            var l = g + g + 1;
            var m = d << 2;
            var n = d - 1;
            var o = f - 1;
            var q = g + 1;
            var r = q * (q + 1) / 2;
            var s = new BlurStack();
            var t = s;
            for (i = 1; i < l; i++) {
                t = t.next = new BlurStack();
                if (i == q)
                    var u = t
            }
            t.next = s;
            var v = null;
            var w = null;
            yw = yi = 0;
            var z = mul_table[g];
            var A = shg_table[g];
            for (y = 0; y < f; y++) {
                r_in_sum = g_in_sum = b_in_sum = r_sum = g_sum = b_sum = 0;
                r_out_sum = q * (pr = k[yi]);
                g_out_sum = q * (pg = k[yi + 1]);
                b_out_sum = q * (pb = k[yi + 2]);
                r_sum += r * pr;
                g_sum += r * pg;
                b_sum += r * pb;
                t = s;
                for (i = 0; i < q; i++) {
                    t.r = pr;
                    t.g = pg;
                    t.b = pb;
                    t = t.next
                }
                for (i = 1; i < q; i++) {
                    p = yi + ((n < i ? n : i) << 2);
                    r_sum += (t.r = (pr = k[p])) * (rbs = q - i);
                    g_sum += (t.g = (pg = k[p + 1])) * rbs;
                    b_sum += (t.b = (pb = k[p + 2])) * rbs;
                    r_in_sum += pr;
                    g_in_sum += pg;
                    b_in_sum += pb;
                    t = t.next
                }
                v = s;
                w = u;
                for (x = 0; x < d; x++) {
                    k[yi] = (r_sum * z) >> A;
                    k[yi + 1] = (g_sum * z) >> A;
                    k[yi + 2] = (b_sum * z) >> A;
                    r_sum -= r_out_sum;
                    g_sum -= g_out_sum;
                    b_sum -= b_out_sum;
                    r_out_sum -= v.r;
                    g_out_sum -= v.g;
                    b_out_sum -= v.b;
                    p = (yw + ((p = x + g + 1) < n ? p : n)) << 2;
                    r_in_sum += (v.r = k[p]);
                    g_in_sum += (v.g = k[p + 1]);
                    b_in_sum += (v.b = k[p + 2]);
                    r_sum += r_in_sum;
                    g_sum += g_in_sum;
                    b_sum += b_in_sum;
                    v = v.next;
                    r_out_sum += (pr = w.r);
                    g_out_sum += (pg = w.g);
                    b_out_sum += (pb = w.b);
                    r_in_sum -= pr;
                    g_in_sum -= pg;
                    b_in_sum -= pb;
                    w = w.next;
                    yi += 4
                }
                yw += d
            }
            for (x = 0; x < d; x++) {
                g_in_sum = b_in_sum = r_in_sum = g_sum = b_sum = r_sum = 0;
                yi = x << 2;
                r_out_sum = q * (pr = k[yi]);
                g_out_sum = q * (pg = k[yi + 1]);
                b_out_sum = q * (pb = k[yi + 2]);
                r_sum += r * pr;
                g_sum += r * pg;
                b_sum += r * pb;
                t = s;
                for (i = 0; i < q; i++) {
                    t.r = pr;
                    t.g = pg;
                    t.b = pb;
                    t = t.next
                }
                yp = d;
                for (i = 1; i <= g; i++) {
                    yi = (yp + x) << 2;
                    r_sum += (t.r = (pr = k[yi])) * (rbs = q - i);
                    g_sum += (t.g = (pg = k[yi + 1])) * rbs;
                    b_sum += (t.b = (pb = k[yi + 2])) * rbs;
                    r_in_sum += pr;
                    g_in_sum += pg;
                    b_in_sum += pb;
                    t = t.next;
                    if (i < o) {
                        yp += d
                    }
                }
                yi = x;
                v = s;
                w = u;
                for (y = 0; y < f; y++) {
                    p = yi << 2;
                    k[p] = (r_sum * z) >> A;
                    k[p + 1] = (g_sum * z) >> A;
                    k[p + 2] = (b_sum * z) >> A;
                    r_sum -= r_out_sum;
                    g_sum -= g_out_sum;
                    b_sum -= b_out_sum;
                    r_out_sum -= v.r;
                    g_out_sum -= v.g;
                    b_out_sum -= v.b;
                    p = (x + (((p = y + q) < o ? p : o) * d)) << 2;
                    r_sum += (r_in_sum += (v.r = k[p]));
                    g_sum += (g_in_sum += (v.g = k[p + 1]));
                    b_sum += (b_in_sum += (v.b = k[p + 2]));
                    v = v.next;
                    r_out_sum += (pr = w.r);
                    g_out_sum += (pg = w.g);
                    b_out_sum += (pb = w.b);
                    r_in_sum -= pr;
                    g_in_sum -= pg;
                    b_in_sum -= pb;
                    w = w.next;
                    yi += d
                }
            }
            h.putImageData(j, b, c)
        }

        function BlurStack () {
            this.r = 0;
            this.g = 0;
            this.b = 0;
            this.a = 0;
            this.next = null
        }

        return this.each(function () {
            var $glue = $(this);
            var $source = $(options.source);
            var formattedSource = ($source.css('backgroundImage')).replace(/"/g, "").replace(/url\(|\)$/ig, "");
            ctx = canvas.getContext('2d');
            tempImg = new Image();
            tempImg.onload = function () {
                if (!isCached) {
                    canvas.style.display = "none";
                    canvas.width = tempImg.width;
                    canvas.height = tempImg.height;
                    ctx.drawImage(tempImg, 0, 0);
                    stackBlurCanvasRGB(canvas, 0, 0, canvas.width, canvas.height, options.radius);
                    if (options.overlay != false) {
                        ctx.beginPath();
                        ctx.rect(0, 0, tempImg.width, tempImg.width);
                        ctx.fillStyle = options.overlay;
                        ctx.fill();
                    }
                    var blurredData = canvas.toDataURL();
                    if (options.cache) {
                        try {
                            if (options.debug) {
                                Debug.log('Cache Set');
                            }
                            localStorage.setItem(options.cacheKeyPrefix + selector + '-' + formattedSource + '-data-image', blurredData);
                        } catch (e) {
                            Debug.log(e);
                        }
                    }
                } else {
                    var blurredData = tempImg.src;
                }
                var attachment = $source.css('backgroundAttachment');
                var position = (attachment == 'fixed') ? '' : '-' + (($glue.offset().left) - ($source.offset().left) - (options.offset.x)) + 'px -' + (($glue.offset().top) - ($source.offset().top) - (options.offset.y)) + 'px';
                $glue.css({
                    'background-image': 'url("' + blurredData + '")',
                    'background-repeat': $source.css('backgroundRepeat'),
                    'background-position': position,
                    'background-attachment': attachment
                });
                if (options.optClass != false) {
                    $glue.addClass(options.optClass);
                }
                if (options.draggable) {
                    $glue.css({
                        'background-attachment': 'fixed',
                        'background-position': '0 0'
                    });
                    $glue.draggable();
                }
            };
            Storage.prototype.cacheChecksum = function (opts) {
                var newData = '';
                for (var key in opts) {
                    var obj = opts[key];
                    if (obj.toString() == '[object Object]') {
                        newData += ((obj.x).toString() + (obj.y).toString() + ",").replace(/[^a-zA-Z0-9]/g, "");
                    } else {
                        newData += (obj + ",").replace(/[^a-zA-Z0-9]/g, "");
                    }
                }
                var originalData = this.getItem(options.cacheKeyPrefix + selector + '-' + formattedSource + '-options-cache');
                if (originalData != newData) {
                    this.removeItem(options.cacheKeyPrefix + selector + '-' + formattedSource + '-options-cache');
                    this.setItem(options.cacheKeyPrefix + selector + '-' + formattedSource + '-options-cache', newData);
                    if (options.debug) {
                        Debug.log('Settings Changed, Cache Emptied');
                    }
                }
            };
            var cachedData = null;
            if (options.cache) {
                localStorage.cacheChecksum(options);
                cachedData = localStorage.getItem(options.cacheKeyPrefix + selector + '-' + formattedSource + '-data-image');
            }
            if (cachedData != null) {
                if (options.debug) {
                    Debug.log('Cache Used');
                }
                isCached = true;
                tempImg.src = (cachedData);
            } else {
                if (options.debug) {
                    Debug.log('Source Used');
                }
                tempImg.src = formattedSource;
            }
        });
    };
})(jQuery);


/*
 * Default text - jQuery plugin for html5 dragging files from desktop to browser
 *
 * Author: Weixi Yen
 *
 * Email: [Firstname][Lastname]@gmail.com
 *
 * Copyright (c) 2010 Resopollution
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   http://www.github.com/weixiyen/jquery-filedrop
 *
 * Version:  0.1.0
 *
 * Features:
 *      Allows sending of extra parameters with file.
 *      Works with Firefox 3.6+
 *      Future-compliant with HTML5 spec (will work with Webkit browsers and IE9)
 * Usage:
 *  See README at project homepage
 *
 */
;
(function ($) {

    jQuery.event.props.push("dataTransfer");

    var default_opts = {
        autoUpload: true,
        fallback_id: '',
        fallbackInput: false,
        url: '',
        refresh: 1000,
        requestType: 'POST',
        paramname: 'userfile',
        allowedfiletypes: [],
        maxfiles: 25, // Ignored if queuefiles is set > 0
        maxfilesize: 1, // MB file size limit
        queuefiles: 0, // Max files before queueing (for large volume uploads)
        queuewait: 200, // Queue wait time if full
        data: {},
        headers: {},
        add: empty,
        drop: empty,
        dragStart: empty,
        dragEnter: empty,
        dragOver: empty,
        dragLeave: empty,
        docEnter: empty,
        docOver: empty,
        docLeave: empty,
        beforeEach: empty,
        afterAll: empty,
        rename: empty,
        error: function (err, file, i, status) {
            jAlert(err);
        },
        uploadStarted: empty,
        uploadFinished: empty,
        progressUpdated: empty,
        globalProgressUpdated: empty,
        speedUpdated: empty,
        uploadError: empty,
        uploadAbort: function (event, xhr, file) {

        },
        onCancel: false,
        beforeSend: false,
        stop: false
    },
    errors = ["BrowserNotSupported", "TooManyFiles", "FileTooLarge", "FileTypeNotAllowed", "NotFound", "NotReadable", "AbortError", "ReadError"],
            doc_leave_timer, stop_loop = false,
            files_count = 0,
            files, stop = false;

    var uploadFiles, uploadFiles_count = 0, opts = {};

    function reindexArray (array) {
        var result = [];
        for (var key in array)
            result.push(array[key]);
        return result;
    }

    $.fn.filedrop = function (options) {

        if (options.refresh === true) {
            if (typeof options.data == 'object') {
                opts.data = options.data;
            }

            if (typeof options.url == 'string') {
                opts.url = options.url;
            }

            if (typeof options.filePostParamName == 'string') {
                opts.paramname = options.filePostParamName;
            }

            return;
        }

        if (options.stop && options.stop === true) {
            stop = true;
            return;
        }
        else {
            opts = $.extend({}, default_opts, options);
            stop = false;
            var global_progress = [];
        }

        this.on('drop', drop).on('dragstart', opts.dragStart).on('dragenter', dragEnter).on('dragover', dragOver).on('dragleave', dragLeave);

        this.on('upload', function (e) {

            uploadFiles_count = files.length;

            if (uploadFiles_count) {
                uploadFiles = [];
                for (var i = 0; i < uploadFiles_count; i++) {
                    uploadFiles.push(files[i].originalFile);
                }

                upload();
            }
        });

        this.on('cancelAll', function () {
            if (uploadFiles && uploadFiles.length) {
                for (var i = 0; i < uploadFiles.length; i++) {
                    if (uploadFiles[i].xhr) {
                        uploadFiles[i].xhr.abort();
                    }
                }
            }
        });

        this.on('cancel', function (event, data) {
            if (data && data.name) {
                uploadFiles_count = files.length;

                if (uploadFiles && uploadFiles.length) {
                    for (var i = 0; i < uploadFiles.length; i++) {
                        if (uploadFiles[i].name == data.name && uploadFiles[i].xhr) {
                            uploadFiles[i].xhr.abort();

                            delete(uploadFiles[i]);
                            delete (files[i]);

                            uploadFiles = reindexArray(uploadFiles);
                            uploadFiles_count = uploadFiles.length;

                            files = reindexArray(files);
                            files_count = files.length;
                            break;
                        }
                    }
                }
                else if (files && files.length) {
                    for (var i = 0; i < files.length; i++) {
                        if (files[i].name == data.name) {

                            if (typeof opts.uploadAbort === 'function') {
                                opts.uploadAbort(event, false, files[i])
                            }

                            delete (files[i]);

                            files = reindexArray(files);
                            files_count = files.length;
                            uploadFiles_count = files.length;
                            return true;
                        }
                    }
                }
            }
        });

        //   $(document).on('drop', docDrop).on('dragenter', docEnter).on('dragover', docOver).on('dragleave', docLeave);

        if (opts.fallbackInput) {
            opts.fallbackInput.change(function (e) {
                var data = {
                    fileInput: $(e.target),
                    form: $(e.target.form)
                };

                _getFileInputFiles(data.fileInput).always(function (xfiles) {
                    files = xfiles;
                });

                files_count = files.length;

                for (var i = 0; i < files_count; i++) {
                    var f = files[i];
                    f.originalFile = files[i];
                }

                if (typeof opts.add == 'function') {
                    for (var i = 0; i < files_count; i++) {
                        var f = files[i];
                        f.upload = function () {
                            uploadFiles = [];
                            uploadFiles.push(this.originalFile);
                            uploadFiles_count = 1;
                            upload();
                        }

                        if (!opts.add(f)) {
                            files = false;
                            return false;
                        }
                    }
                }

                if (opts.autoUpload) {
                    uploadFiles = files;
                    uploadFiles_count = files_count;
                    upload();
                }
            });
        }

        function _handleFileTreeEntries (entries, path) {

            return $.when.apply(
                    $,
                    $.map(entries, function (entry) {
                        return _handleFileTreeEntry(entry, path);
                    })
                    ).pipe(function () {
                return Array.prototype.concat.apply(
                        [],
                        arguments
                        );
            });
        }

        function _getSingleFileInputFiles (fileInput) {
            fileInput = $(fileInput);
            var entries = fileInput.prop('webkitEntries') ||
                    fileInput.prop('entries'),
                    files,
                    value;

            if (entries && entries.length) {
                return _handleFileTreeEntries(entries);
            }

            files = $.makeArray(fileInput.prop('files'));
            if (!files.length) {
                value = fileInput.prop('value');
                if (!value) {
                    return $.Deferred().resolve([]).promise();
                }
                // If the files property is not available, the browser does not
                // support the File API and we add a pseudo File object with
                // the input value as name with path information removed:
                files = [
                    {name: value.replace(/^.*\\/, '')}
                ];
            } else if (files[0].name === undefined && files[0].fileName) {
                // File normalization for Safari 4 and Firefox 3:
                $.each(files, function (index, file) {
                    file.name = file.fileName;
                    file.size = file.fileSize;
                });
            }
            return $.Deferred().resolve(files).promise();
        }

        function _getFileInputFiles (fileInput) {

            if (!(fileInput instanceof $) || fileInput.length === 1) {
                return _getSingleFileInputFiles(fileInput);
            }

            return $.when.apply(
                    $,
                    $.map(fileInput, _getSingleFileInputFiles)
                    ).pipe(function () {
                return Array.prototype.concat.apply(
                        [],
                        arguments
                        );
            });
        }


        function drop (e) {
            if (opts.drop.call(this, e) === false)
                return false;

            var dataTransfer = e.dataTransfer = e.originalEvent.dataTransfer;

            if (dataTransfer === null || dataTransfer === undefined || dataTransfer.files.length === 0) {
                opts.error(errors[0]);
                return false;
            }

            files = dataTransfer.files;
            files_count = files.length;

            for (var i = 0; i < files_count; i++) {
                var f = files[i];
                f.originalFile = files[i];
            }

            if (typeof opts.add == 'function') {
                for (var i = 0; i < files_count; i++) {
                    var f = files[i];
                    f.upload = function () {
                        uploadFiles = [];
                        uploadFiles.push(this.originalFile);
                        uploadFiles_count = 1;
                        upload();
                    }


                    if (!opts.add(f)) {
                        files = false;
                        return false;
                    }
                }
            }

            if (opts.autoUpload) {
                uploadFiles = files;
                uploadFiles_count = files_count;
                upload();
            }

            e.preventDefault();
            return false;
        }

        /**
         * OLD Scool
         *
         * @param {type} filename
         * @param {type} filedata
         * @param {type} mime
         * @param {type} boundary
         * @returns {builder}
         */
        function getBuilder (filename, filedata, mime, boundary) {
            var dashdash = '--',
                    crlf = '\r\n',
                    builder = '';

            if (opts.data) {
                var params = opts.data; //$.param(opts.data).replace(/\+/g, '%20').split(/&/);

                $.each(params, function (name, val) {
                    /*
                     
                     
                     var pair = this.split("=", 2),
                     name = pair[0],
                     //name = decodeURIComponent(pair[0]),
                     //name = name.replace(/%5B/g, '[').replace(/%5D/g, ']'),
                     
                     val = decodeURIComponent(pair[1]),
                     val = val.replace(/%5B/g, '[').replace(/%5D/g, ']');
                     
                     
                     
                     if (pair.length !== 2) {
                     return;
                     }
                     */

                    builder += dashdash;
                    builder += boundary;
                    builder += crlf;
                    builder += 'Content-Disposition: form-data; name="' + name + '"';
                    builder += crlf;
                    builder += crlf;
                    builder += val;
                    builder += crlf;
                });
            }

            builder += dashdash;
            builder += boundary;
            builder += crlf;
            builder += 'Content-Disposition: form-data; name="' + opts.paramname + '"';
            builder += '; filename="' + filename + '"';
            builder += crlf;

            builder += 'Content-Type: ' + mime;
            builder += crlf;
            builder += crlf;

            builder += filedata;
            builder += crlf;

            builder += dashdash;
            builder += boundary;
            builder += dashdash;
            builder += crlf;
            return builder;
        }

        function progress (e) {
            if (e.lengthComputable) {
                var percentage = Math.round((e.loaded * 100) / e.total);
                if (this.currentProgress !== percentage) {

                    this.currentProgress = percentage;
                    opts.progressUpdated(this.index, this.file, this.currentProgress);

                    global_progress[this.global_progress_index] = this.currentProgress;
                    globalProgress();

                    var elapsed = new Date().getTime();
                    var diffTime = elapsed - this.currentStart;

                    if (diffTime >= opts.refresh) {
                        var diffData = e.loaded - this.startData;
                        var speed = diffData / diffTime; // KB per second

                        opts.speedUpdated(this.index, this.file, speed, e.loaded, diffTime);

                        this.startData = e.loaded;
                        this.currentStart = elapsed;
                    }
                }
            }
        }

        function globalProgress () {
            if (global_progress.length === 0) {
                return;
            }

            var total = 0, index;
            for (index in global_progress) {
                if (global_progress.hasOwnProperty(index)) {
                    total = total + global_progress[index];
                }
            }

            opts.globalProgressUpdated(Math.round(total / global_progress.length));
        }


        // Respond to an upload
        function upload (f) {
            stop_loop = false;

            if (!uploadFiles) {
                opts.error(errors[0]);
                return false;
            }

            if (opts.allowedfiletypes.push && opts.allowedfiletypes.length) {
                for (var fileIndex = uploadFiles.length; fileIndex--; ) {
                    if (!uploadFiles[fileIndex].type || $.inArray(uploadFiles[fileIndex].type, opts.allowedfiletypes) < 0) {
                        opts.error(errors[3], uploadFiles[fileIndex]);
                        return false;
                    }
                }
            }

            var filesDone = 0, filesRejected = 0;

            if (uploadFiles_count > opts.maxfiles && opts.queuefiles === 0) {
                opts.error(errors[1], uploadFiles[fileIndex]);
                return false;
            }

            // Define queues to manage upload process
            var workQueue = [];
            var processingQueue = [];
            var doneQueue = [];

            // Add everything to the workQueue
            for (var i = 0; i < uploadFiles_count; i++) {
                workQueue.push(i);
            }

            // Helper function to enable pause of processing to wait
            // for in process queue to complete
            var pause = function (timeout) {
                setTimeout(process, timeout);
                return;
            };

            var cancel = function (e) {
                if (stop) {
                    if (opts.onCancel) {
                        opts.onCancel(uploadFiles[e.target.index]);
                        return;
                    }
                }
            };


            var send = function (e) {

                var fileIndex = ((typeof (e.srcElement) === "undefined") ? e.target : e.srcElement).index;

                // Sometimes the index is not attached to the
                // event object. Find it by size. Hack for sure.
                if (e.target.index === undefined) {
                    e.target.index = getIndexBySize(e.total);
                }

                var xhr = new XMLHttpRequest(),
                        upload = xhr.upload,
                        file = uploadFiles[e.target.index],
                        index = e.target.index,
                        start_time = new Date().getTime(),
                        boundary = '------multipartformboundary' + (new Date()).getTime(), // for the old scool
                        global_progress_index = global_progress.length,
                        builder,
                        newName = rename(file.name),
                        mime = file.type;

                uploadFiles[e.target.index].xhr = xhr;

                if (opts.withCredentials) {
                    xhr.withCredentials = opts.withCredentials;
                }

                var useFormData = false;

                // prepare FormData
                // new scool html5 only if exists FormData
                if (typeof FormData != 'undefined') {
                    useFormData = true;
                    var formData = new FormData();

                    if (opts.data) {
                        var params = $.param(opts.data).replace(/\+/g, '%20').split(/&/);
                        $.each(params, function () {
                            var pair = this.split("=", 2);
                            if (pair.length == 2) {
                                formData.append(pair[0], pair[1]);
                            }
                        });
                    }

                    formData.append(opts.paramname, file);
                }
                else {
                    if (typeof newName === "string") {
                        builder = getBuilder(newName, e.target.result, mime, boundary);
                    } else {
                        builder = getBuilder(file.name, e.target.result, mime, boundary);
                    }
                }


                upload.index = index;
                upload.file = file;
                upload.downloadStartTime = start_time;
                upload.currentStart = start_time;
                upload.currentProgress = 0;
                upload.global_progress_index = global_progress_index;
                upload.startData = 0;
                upload.addEventListener("progress", progress, false);

                // Allow url to be a method
                if (jQuery.isFunction(opts.url)) {
                    xhr.open(opts.requestType, opts.url(), true);
                } else {
                    xhr.open(opts.requestType, opts.url, true);
                }


                // Add headers
                $.each(opts.headers, function (k, v) {
                    xhr.setRequestHeader(k, v);
                });


                if (useFormData) {
                    // new scool
                    xhr.send(formData);
                }
                else {
                    // old scool
                    xhr.setRequestHeader('Content-Type', 'multipart/form-data; boundary=' + boundary);
                    xhr.sendAsBinary(builder);
                }

                global_progress[global_progress_index] = 0;

                globalProgress();

                opts.uploadStarted(index, file, uploadFiles_count);

                xhr.addEventListener('abort', function (e) {
                    if (opts.uploadAbort) {
                        opts.uploadAbort(e, this, file);
                    }
                }, false);

                xhr.onload = function () {
                    var serverResponse = null;

                    if (xhr.responseText) {
                        try {
                            serverResponse = jQuery.parseJSON(xhr.responseText);
                        }
                        catch (e) {
                            serverResponse = xhr.responseText;
                        }
                    }

                    var now = new Date().getTime(),
                            timeDiff = now - start_time,
                            result = opts.uploadFinished(index, file, serverResponse, timeDiff, xhr);
                    filesDone++;

                    // Remove from processing queue
                    processingQueue.forEach(function (value, key) {
                        if (value === fileIndex) {
                            processingQueue.splice(key, 1);
                        }
                    });

                    // Add to donequeue
                    doneQueue.push(fileIndex);

                    // Make sure the global progress is updated
                    global_progress[global_progress_index] = 100;
                    globalProgress();

                    if (filesDone === (uploadFiles_count - filesRejected)) {
                        afterAll();
                    }

                    if (result === false) {
                        stop_loop = true;
                    }
                    // Pass any errors to the error option
                    if (xhr.status < 200 || xhr.status > 299) {
                        opts.error(xhr.statusText, file, fileIndex, xhr.status);
                    }
                };
            };


            // Process an upload, recursive
            var process = function () {

                var fileIndex;

                if (stop_loop) {
                    return false;
                }

                // Check to see if are in queue mode
                if (opts.queuefiles > 0 && processingQueue.length >= opts.queuefiles) {
                    return pause(opts.queuewait);
                } else {
                    // Take first thing off work queue
                    fileIndex = workQueue[0];
                    workQueue.splice(0, 1);

                    // Add to processing queue
                    processingQueue.push(fileIndex);
                }

                try {
                    if (beforeEach(uploadFiles[fileIndex]) !== false) {
                        if (fileIndex === uploadFiles_count) {
                            return;
                        }
                        var reader = new FileReader(),
                                max_file_size = 1048576 * parseInt(opts.maxfilesize);

                        reader.index = fileIndex;
                        if (uploadFiles[fileIndex].size > parseInt(max_file_size)) {
                            opts.error(errors[2], uploadFiles[fileIndex], fileIndex);

                            // Remove from queue
                            processingQueue.forEach(function (value, key) {
                                if (value === fileIndex) {
                                    processingQueue.splice(key, 1);
                                }
                            });
                            filesRejected++;
                            return true;
                        }

                        reader.onerror = function (e) {
                            switch (e.target.error.code) {
                                case e.target.error.NOT_FOUND_ERR:
                                    opts.error(errors[4], uploadFiles[fileIndex]);
                                    return false;
                                case e.target.error.NOT_READABLE_ERR:
                                    opts.error(errors[5], uploadFiles[fileIndex]);
                                    return false;
                                case e.target.error.ABORT_ERR:
                                    opts.error(errors[6], uploadFiles[fileIndex]);
                                    return false;
                                default:
                                    opts.error(errors[7], uploadFiles[fileIndex]);
                                    return false;
                            }
                            ;
                        };

                        reader.onloadend = !opts.beforeSend ? send : function (e) {
                            opts.beforeSend(uploadFiles[fileIndex], fileIndex, function () {
                                return send(e);
                            });
                        };

                        reader.readAsBinaryString(uploadFiles[fileIndex]);

                    } else {


                        filesRejected++;
                    }
                } catch (err) {
                    // Remove from queue
                    processingQueue.forEach(function (value, key) {
                        if (value === fileIndex) {
                            processingQueue.splice(key, 1);
                        }
                    });
                    opts.error(errors[0], uploadFiles[fileIndex]);
                    return false;
                }

                // If we still have work to do,
                if (workQueue.length > 0) {
                    process();
                }
            };


            // Initiate the processing loop
            process();

        }

        function getIndexBySize (size) {
            for (var i = 0; i < uploadFiles_count; i++) {
                if (uploadFiles[i].size === size) {
                    return i;
                }
            }

            return undefined;
        }

        function rename (name) {
            return opts.rename(name);
        }

        function beforeEach (file) {
            return opts.beforeEach(file);
        }

        function afterAll () {
            return opts.afterAll();
        }

        function dragEnter (e) {
            clearTimeout(doc_leave_timer);
            e.preventDefault();
            opts.dragEnter.call(this, e);
        }

        function dragOver (e) {
            clearTimeout(doc_leave_timer);
            e.preventDefault();
            opts.docOver.call(this, e);
            opts.dragOver.call(this, e);
        }

        function dragLeave (e) {
            clearTimeout(doc_leave_timer);
            opts.dragLeave.call(this, e);
            e.stopPropagation();
        }

        function docDrop (e) {
            e.preventDefault();
            opts.docLeave.call(this, e);
            return false;
        }

        function docEnter (e) {
            clearTimeout(doc_leave_timer);
            e.preventDefault();
            opts.docEnter.call(this, e);
            return false;
        }

        function docOver (e) {
            clearTimeout(doc_leave_timer);
            e.preventDefault();
            opts.docOver.call(this, e);
            return false;
        }

        function docLeave (e) {
            doc_leave_timer = setTimeout((function (_this) {
                return function () {
                    opts.docLeave.call(_this, e);
                };
            })(this), 200);
        }

        return this;
    };

    function empty () {
    }

    try {

        if (XMLHttpRequest.prototype.sendAsBinary) {
            return;
        }

        XMLHttpRequest.prototype.sendAsBinary = function (datastr) {
            function byteValue (x) {
                return x.charCodeAt(0) & 0xff;
            }

            var ords = Array.prototype.map.call(datastr, byteValue);
            var ui8a = new Uint8Array(ords);

            try {
                this.send(ui8a);
            }
            catch (er) {
                this.send(ui8a.buffer);
            }


        };
    } catch (e) {
    }

})(jQuery);


/***************************************************
 *
 * taboverflow plugin - allows tabs scrolling when the window's width is too small to display all tabs
 *
 * **************************************************/

(function ($) {


    $.fn.taboverflow = function (options) {

        var opts = $.extend({}, $.fn.taboverflow.defaults, options);

        $(document).resize(function () {
            $('.tabbedMenu').toggleOverflowArrow();
        });

        return this.each(function () {
            $this = $(this);


            $.fn.taboverflow.prepareDom($this);
            $.fn.taboverflow.setListWidth($this);
            $.fn.taboverflow.toggleTabslist($this);
            $this.toggleOverflowArrow();

            $this.parents('.tabbedMenuWrap:first').find('.menuScrollRight:first').scrollTabs({"direction": "right"});
            $this.parents('.tabbedMenuWrap:first').find('.menuScrollLeft:first').scrollTabs({"direction": "left"});


        });
    };

    /**
     * Wraps the tabs with necessary DIVs and add anchors to scroll left and
     * right
     */
    $.fn.taboverflow.prepareDom = function ($this) {

        if ($this.parents(".tabHeader").find(".tabbedMenuWrap").length === 0) {

            $this.wrap('<div class="tabbedMenuWrap"><div class="tabbedScrollWrap"></div></div>');
            $this.parents(".tabHeader").find(".tabbedMenuWrap").prepend('<a href="javascript:void(0);" style="display:none;" class="menuScrollLeft scrollArrows">&nbsp;</a><a href="javascript:void(0);" style="display:none;" class="menuScrollRight scrollArrows">&nbsp;</a>');

            //  $('<div class="tabList"><a class="tabListLink">&nbsp;</a></div>').insertAfter($this.parents(".tabHeader").find('.menuScrollRight:first));

            /*
             var thisTabList = $this.parents(".tabHeader").find(".tabList");
             $this.clone()
             .removeAttr("class")
             .appendTo(thisTabList)
             .find("li")
             .removeAttr("style")
             .removeAttr("id")
             .find("a")
             .attr("id", function() {
             return this.id + "_tabList";
             });
             */
        }
    };

    /**
     * Sets the width of the UL containing the tabs
     * */
    $.fn.taboverflow.setListWidth = function ($this) {
        var accountForBorders, tabsTotalWidth = 0;

        $this.find('li').each(function () {
            tabsTotalWidth = ($(this).outerWidth(true) + (tabsTotalWidth));
        });

        if (($.browser.msie) && ($.browser.version == 7)) {
            accountForBorders = 10;
        } else {
            accountForBorders = 2;
        }

        tabsTotalWidth = (tabsTotalWidth + accountForBorders);

        $this.width(tabsTotalWidth + "px");
    };

    $.fn.taboverflow.toggleTabslist = function ($this) {
        var $tabList = $this.parents().prev(".tabList");

        $tabList.find('.tabListLink').click(function (event) {
            event.stopPropagation();
            $(this).siblings('ul').toggle("fast");
        });

        $tabList.click(function (event) {
            event.stopPropagation();
        });
    };


    /****************************************************************************
     *
     * toggleOverflowArrow plugin to toggle the elements allowing to scroll
     *
     * ***************************************************************************/
    $.fn.toggleOverflowArrow = function (options) {

        var opts = $.extend({}, $.fn.toggleOverflowArrow.defaults, options);

        return this.each(function () {
            $this = $(this);

            var tabsTotalWidth = $this.width();
            var wrapperWidth = $this.parents(".tabbedScrollWrap").outerWidth();
            $this.parents(".tabbedScrollWrap").scrollLeft(0);

            if (wrapperWidth > tabsTotalWidth) {
                $this.parents(".tabHeader").find(".scrollArrows, .tabList").hide();
            } else {
                $this.parents(".tabHeader").find(".scrollArrows, .tabList").show();
            }
        });
    };

    /****************************************************************************
     *
     * scrollTabs plugin to allow the scrolling of the tabs
     *
     * ***************************************************************************/
    $.fn.scrollTabs = function (options) {

        var opts = $.extend({}, $.fn.scrollTabs.defaults, options);
        var scrollingLength = 200;

        return this.each(function () {
            $this = $(this);

            $this.unbind().click(function () {
                var maxoffset = $(this).parent().find('ul.tabbedMenu li:last').width() + $(this).parent().find('ul.tabbedMenu li:last').offset().left;
                var curentoffset = Math.abs(parseInt($(this).parent().find('ul.tabbedMenu').offset().left));


                var offset, offsetOrg = $(this).siblings(".tabbedScrollWrap").scrollLeft();
                switch (opts.direction) {
                    case "right":
                        if (curentoffset >= maxoffset) {
                            return;
                        }
                        offset = offsetOrg + scrollingLength;
                        break;
                    case "left":

                        if (curentoffset <= 0) {
                            return;
                        }

                        offset = offsetOrg - scrollingLength;
                        break;
                }

                $(this).siblings(".tabbedScrollWrap").stop().animate({scrollLeft: offset}, "fast", "linear", function () {

                });
            });
        });

        $.fn.scrollTabs.defaults = {};
    };
})(jQuery);

/*
 * jQuery plugin: fieldSelection - v0.1.0 - last change: 2006-12-16
 * (c) 2006 Alex Brem <alex@0xab.cd> - http://blog.0xab.cd
 */
(function () {

    var fieldSelection = {
        getSelection: function () {

            var e = (this.jquery) ? this[0] : this;

            return (
                    /* mozilla / dom 3.0 */
                            ('selectionStart' in e && function () {
                                var l = e.selectionEnd - e.selectionStart;
                                return {start: e.selectionStart, end: e.selectionEnd, length: l, text: e.value.substr(e.selectionStart, l)};
                            }) ||
                            /* exploder */
                                    (document.selection && function () {

                                        e.focus();

                                        var r = document.selection.createRange();
                                        if (r === null) {
                                            return {start: 0, end: e.value.length, length: 0}
                                        }

                                        var re = e.createTextRange();
                                        var rc = re.duplicate();
                                        re.moveToBookmark(r.getBookmark());
                                        rc.setEndPoint('EndToStart', re);

                                        return {start: rc.text.length, end: rc.text.length + r.text.length, length: r.text.length, text: r.text};
                                    }) ||
                                    /* browser not supported */
                                            function () {
                                                return null;
                                            }

                                    )();

                                },
                        replaceSelection: function () {

                            var e = (typeof this.id == 'function') ? this.get(0) : this;
                            var text = arguments[0] || '';

                            return (
                                    /* mozilla / dom 3.0 */
                                            ('selectionStart' in e && function () {
                                                e.value = e.value.substr(0, e.selectionStart) + text + e.value.substr(e.selectionEnd, e.value.length);
                                                return this;
                                            }) ||
                                            /* exploder */
                                                    (document.selection && function () {
                                                        e.focus();
                                                        document.selection.createRange().text = text;
                                                        return this;
                                                    }) ||
                                                    /* browser not supported */
                                                            function () {
                                                                e.value += text;
                                                                return jQuery(e);
                                                            }

                                                    )();

                                                }

                                    };

                                    jQuery.each(fieldSelection, function (i) {
                                        jQuery.fn[i] = this;
                                    });

                                })();