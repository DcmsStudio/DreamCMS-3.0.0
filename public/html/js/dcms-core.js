if (typeof jQuery != 'undefined') {
    (function (jQuery, window, undefined) {
        "use strict";

        var matched, browser;

        jQuery.uaMatch = function (ua) {
            ua = ua.toLowerCase();

            var match = /(chrome)[ \/]([\w.]+)/.exec(ua) ||
                /(webkit)[ \/]([\w.]+)/.exec(ua) ||
                /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) ||
                /(msie) ([\w.]+)/.exec(ua) ||
                ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) ||
                [];

            var platform_match = /(ipad)/.exec(ua) ||
                /(iphone)/.exec(ua) ||
                /(android)/.exec(ua) ||
                [];

            return {
                browser: match[ 1 ] || "",
                version: match[ 2 ] || "0",
                platform: platform_match[0] || ""
            };
        };

        matched = jQuery.uaMatch(window.navigator.userAgent);
        browser = {msie: false, opera: false, mozilla: false, webkit: false};

        if (matched.browser) {
            browser[ matched.browser ] = true;
            browser.version = matched.version;
        }

        if (matched.platform) {
            browser[ matched.platform ] = true
        }

        // Chrome is Webkit, but Webkit is also Safari.
        if (browser.chrome) {
            browser.webkit = true;
        } else if (browser.webkit) {
            browser.safari = true;
        }

        jQuery.browser = browser;

    })(jQuery, window);

    (function ($) {
        $.fn.tabby = function (options) {
            var opts = $.extend({}, $.fn.tabby.defaults, options);


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


}

/**
 * Detect if the browser can play MP3 audio using native HTML5 Audio.
 * Invokes the callack function with first parameter is the boolean success
 * value; if that value is false, a second error parameter is passed. This error
 * is either HTMLMediaError or some other DOMException or Error object.
 * Note the callback is likely to be invoked asynchronously!
 * @param {function(boolean, Object|undefined)} callback
 */
function canPlayAudioMP3(callback) {
    try {


        if ($.browser.webkit || $.browser.chrome) {
            callback(true);
            return;
        }

        var audio = new Audio();
        //Shortcut which doesn't work in Chrome (always returns ""); pass through
        // if "maybe" to do asynchronous check by loading MP3 data: URI
        if (audio.canPlayType('audio/mpeg') == "probably")
            callback(true);

        //If this event fires, then MP3s can be played
        audio.addEventListener('canplaythrough', function (e) {
            callback(true);
        }, false);

        //If this is fired, then client can't play MP3s
        audio.addEventListener('error', function (e) {
            callback(false, this.error)
        }, false);

        //Smallest base64-encoded MP3 I could come up with (<0.000001 seconds long)
        audio.src = "data:audio/mpeg;base64,/+MYxAAAAANIAAAAAExBTUUzLjk4LjIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";
        audio.load();
    }
    catch (e) {
        callback(false, e);
    }
}

var Skin;
var ajax_loading_content = '<img src="html/style/default/img/loading.gif" class="float:left" /> loading...';
var authKey = null;
var isSeemodePopup;
var page = 0;

function getLoadingImage() {
    return '<img src="html/style/default/img/loading.gif" class="loading"/>';
}

function embedNavi(id, url) {

    if ($('#embedView-' + id).length) {
        $('#embedView-' + id).slideToggle();
        return;
    }


    $('a#embedLink' + id).addClass('embedLink').append($('<div>').attr('id', 'embedView-' + id).addClass('embedView').hide()).mouseout(function () {
        $('#embedView-' + id).slideToggle();
    });

    $.ajax({
        url: url,
        type: "GET",
        success: function (data) {
            var navpointLeft = $('#embedLink' + id).offset().left;

            $('#embedView-' + id).append(data.code).slideToggle();

            var layerLeft = $('#embedView' + id).offset().left;
            var wrapperRight = $('#wrapper').offset().left + $('#wrapper').width();

            if (layerLeft < navpointLeft) {
                $('#embedView' + id).css('left', String(navpointLeft - layerLeft - 6) + 'px');
            }

            var layerRight = $('#embedView' + id).offset().left + $('#embedView' + id).width();
            if (layerRight > wrapperRight) {
                $('#embedView' + id).css('marginLeft', String(-(layerRight - wrapperRight) - 22) + 'px');
            }

            $('#embedView' + id).mouseover(function () {
                $('#embedView' + id).show();
            });

            $('#navEmbed_' + id).mouseout(function () {
                $('#embedView' + id).hide();
            });
        }

    });
}


function strtolower(str) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Onno Marsman
    // *     example 1: strtolower('Kevin van Zonneveld');
    // *     returns 1: 'kevin van zonneveld'
    return (str + '').toLowerCase();
}

function ucfirst(str) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: ucfirst('kevin van zonneveld');
    // *     returns 1: 'Kevin van zonneveld'
    str += '';
    var f = str.charAt(0).toUpperCase();
    return f + str.substr(1);
}


function utf8_decode(str_data) {
    // http://kevin.vanzonneveld.net
    // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
    // +      input by: Aman Gupta
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Norman "zEh" Fuchs
    // +   bugfixed by: hitwork
    // +   bugfixed by: Onno Marsman
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: utf8_decode('Kevin van Zonneveld');
    // *     returns 1: 'Kevin van Zonneveld'

    var tmp_arr = [], i = 0, ac = 0, c1 = 0, c2 = 0, c3 = 0;

    str_data += '';

    while (i < str_data.length) {
        c1 = str_data.charCodeAt(i);
        if (c1 < 128) {
            tmp_arr[ac++] = String.fromCharCode(c1);
            i++;
        } else if ((c1 > 191) && (c1 < 224)) {
            c2 = str_data.charCodeAt(i + 1);
            tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
            i += 2;
        } else {
            c2 = str_data.charCodeAt(i + 1);
            c3 = str_data.charCodeAt(i + 2);
            tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
            i += 3;
        }
    }

    return tmp_arr.join('');
}

function utf8_encode(argString) {
    // http://kevin.vanzonneveld.net
    // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: sowberry
    // +    tweaked by: Jack
    // +   bugfixed by: Onno Marsman
    // +   improved by: Yves Sucaet
    // +   bugfixed by: Onno Marsman
    // +   bugfixed by: Ulrich
    // *     example 1: utf8_encode('Kevin van Zonneveld');
    // *     returns 1: 'Kevin van Zonneveld'

    var string = (argString + ''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");

    var utftext = "";
    var start, end;
    var stringl = 0;

    start = end = 0;
    stringl = string.length;
    for (var n = 0; n < stringl; n++) {
        var c1 = string.charCodeAt(n);
        var enc = null;

        if (c1 < 128) {
            end++;
        } else if (c1 > 127 && c1 < 2048) {
            enc = String.fromCharCode((c1 >> 6) | 192) + String.fromCharCode((c1 & 63) | 128);
        } else {
            enc = String.fromCharCode((c1 >> 12) | 224) + String.fromCharCode(((c1 >> 6) & 63) | 128) + String.fromCharCode((c1 & 63) | 128);
        }
        if (enc !== null) {
            if (end > start) {
                utftext += string.substring(start, end);
            }
            utftext += enc;
            start = end = n + 1;
        }
    }

    if (end > start) {
        utftext += string.substring(start, string.length);
    }

    return utftext;
}


function base64_decode(data) {
    // http://kevin.vanzonneveld.net
    // +   original by: Tyler Akins (http://rumkin.com)
    // +   improved by: Thunder.m
    // +      input by: Aman Gupta
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   bugfixed by: Pellentesque Malesuada
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // -    depends on: utf8_decode
    // *     example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
    // *     returns 1: 'Kevin van Zonneveld'
    // mozilla has this native
    // - but breaks in 2.0.0.12!
    //if (typeof this.window['btoa'] == 'function') {
    //    return btoa(data);
    //}
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
        ac = 0,
        dec = "",
        tmp_arr = [];

    if (!data) {
        return data;
    }

    data += '';

    do { // unpack four hexets into three octets using index points in b64
        h1 = b64.indexOf(data.charAt(i++));
        h2 = b64.indexOf(data.charAt(i++));
        h3 = b64.indexOf(data.charAt(i++));
        h4 = b64.indexOf(data.charAt(i++));

        bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

        o1 = bits >> 16 & 0xff;
        o2 = bits >> 8 & 0xff;
        o3 = bits & 0xff;

        if (h3 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1);
        } else if (h4 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1, o2);
        } else {
            tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
        }
    } while (i < data.length);

    dec = tmp_arr.join('');
    dec = utf8_decode(dec);

    return dec;
}

function base64_encode(data) {
    // http://kevin.vanzonneveld.net
    // +   original by: Tyler Akins (http://rumkin.com)
    // +   improved by: Bayron Guevara
    // +   improved by: Thunder.m
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Pellentesque Malesuada
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // -    depends on: utf8_encode
    // *     example 1: base64_encode('Kevin van Zonneveld');
    // *     returns 1: 'S2V2aW4gdmFuIFpvbm5ldmVsZA=='
    // mozilla has this native
    // - but breaks in 2.0.0.12!
    //if (typeof this.window['atob'] == 'function') {
    //    return atob(data);
    //}
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
        ac = 0,
        enc = "",
        tmp_arr = [];

    if (!data) {
        return data;
    }

    data = utf8_encode(data + '');

    do { // pack three octets into four hexets
        o1 = data.charCodeAt(i++);
        o2 = data.charCodeAt(i++);
        o3 = data.charCodeAt(i++);

        bits = o1 << 16 | o2 << 8 | o3;

        h1 = bits >> 18 & 0x3f;
        h2 = bits >> 12 & 0x3f;
        h3 = bits >> 6 & 0x3f;
        h4 = bits & 0x3f;

        // use hexets to index into b64, and append result to encoded string
        tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
    } while (i < data.length);

    enc = tmp_arr.join('');

    switch (data.length % 3) {
        case 1:
            enc = enc.slice(0, -2) + '==';
            break;
        case 2:
            enc = enc.slice(0, -1) + '=';
            break;
    }

    return enc;
}

function phpdate(format, timestamp) {
    // http://kevin.vanzonneveld.net
    // +   original by: Carlos R. L. Rodrigues (http://www.jsfromhell.com)
    // +      parts by: Peter-Paul Koch (http://www.quirksmode.org/js/beat.html)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: MeEtc (http://yass.meetcweb.com)
    // +   improved by: Brad Touesnard
    // +   improved by: Tim Wiel
    // +   improved by: Bryan Elliott
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: David Randall
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +  derived from: gettimeofday
    // +      input by: majak
    // +   bugfixed by: majak
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Alex
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +   improved by: Thomas Beaucourt (http://www.webapp.fr)
    // +   improved by: JT
    // +   improved by: Theriault
    // +   improved by: Rafal Kukawski (http://blog.kukawski.pl)
    // %        note 1: Uses global: php_js to store the default timezone
    // *     example 1: date('H:m:s \\m \\i\\s \\m\\o\\n\\t\\h', 1062402400);
    // *     returns 1: '09:09:40 m is month'
    // *     example 2: date('F j, Y, g:i a', 1062462400);
    // *     returns 2: 'September 2, 2003, 2:26 am'
    // *     example 3: date('Y W o', 1062462400);
    // *     returns 3: '2003 36 2003'
    // *     example 4: x = date('Y m d', (new Date()).getTime()/1000);
    // *     example 4: (x+'').length == 10 // 2009 01 09
    // *     returns 4: true
    // *     example 5: date('W', 1104534000);
    // *     returns 5: '53'
    // *     example 6: date('B t', 1104534000);
    // *     returns 6: '999 31'
    // *     example 7: date('W U', 1293750000.82); // 2010-12-31
    // *     returns 7: '52 1293750000'
    // *     example 8: date('W', 1293836400); // 2011-01-01
    // *     returns 8: '52'
    // *     example 9: date('W Y-m-d', 1293974054); // 2011-01-02
    // *     returns 9: '52 2011-01-02'
    var that = this,
        jsdate, f, formatChr = /\\?([a-z])/gi, formatChrCb,
    // Keep this here (works, but for code commented-out
    // below for file size reasons)
    //, tal= [],
        _pad = function (n, c) {
            if ((n = n + "").length < c) {
                return new Array((++c) - n.length).join("0") + n;
            } else {
                return n;
            }
        },
        txt_words = ["Sun", "Mon", "Tues", "Wednes", "Thurs", "Fri", "Satur",
            "January", "February", "March", "April", "May", "June", "July",
            "August", "September", "October", "November", "December"],
        txt_ordin = {
            1: "st",
            2: "nd",
            3: "rd",
            21: "st",
            22: "nd",
            23: "rd",
            31: "st"
        };
    formatChrCb = function (t, s) {
        return f[t] ? f[t]() : s;
    };
    f = {
        // Day
        d: function () { // Day of month w/leading 0; 01..31
            return _pad(f.j(), 2);
        },
        D: function () { // Shorthand day name; Mon...Sun
            return f.l().slice(0, 3);
        },
        j: function () { // Day of month; 1..31
            return jsdate.getDate();
        },
        l: function () { // Full day name; Monday...Sunday
            return txt_words[f.w()] + 'day';
        },
        N: function () { // ISO-8601 day of week; 1[Mon]..7[Sun]
            return f.w() || 7;
        },
        S: function () { // Ordinal suffix for day of month; st, nd, rd, th
            return txt_ordin[f.j()] || 'th';
        },
        w: function () { // Day of week; 0[Sun]..6[Sat]
            return jsdate.getDay();
        },
        z: function () { // Day of year; 0..365
            var a = new Date(f.Y(), f.n() - 1, f.j()),
                b = new Date(f.Y(), 0, 1);
            return Math.round((a - b) / 864e5) + 1;
        },
        // Week
        W: function () { // ISO-8601 week number
            var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3),
                b = new Date(a.getFullYear(), 0, 4);
            return 1 + Math.round((a - b) / 864e5 / 7);
        },
        // Month
        F: function () { // Full month name; January...December
            return txt_words[6 + f.n()];
        },
        m: function () { // Month w/leading 0; 01...12
            return _pad(f.n(), 2);
        },
        M: function () { // Shorthand month name; Jan...Dec
            return f.F().slice(0, 3);
        },
        n: function () { // Month; 1...12
            return jsdate.getMonth() + 1;
        },
        t: function () { // Days in month; 28...31
            return (new Date(f.Y(), f.n(), 0)).getDate();
        },
        // Year
        L: function () { // Is leap year?; 0 or 1
            var y = f.Y(), a = y & 3, b = y % 4e2, c = y % 1e2;
            return 0 + (!a && (c || !b));
        },
        o: function () { // ISO-8601 year
            var n = f.n(), W = f.W(), Y = f.Y();
            return Y + (n === 12 && W < 9 ? -1 : n === 1 && W > 9);
        },
        Y: function () { // Full year; e.g. 1980...2010
            return jsdate.getFullYear();
        },
        y: function () { // Last two digits of year; 00...99
            return (f.Y() + "").slice(-2);
        },
        // Time
        a: function () { // am or pm
            return jsdate.getHours() > 11 ? "pm" : "am";
        },
        A: function () { // AM or PM
            return f.a().toUpperCase();
        },
        B: function () { // Swatch Internet time; 000..999
            var H = jsdate.getUTCHours() * 36e2, // Hours
                i = jsdate.getUTCMinutes() * 60, // Minutes
                s = jsdate.getUTCSeconds(); // Seconds
            return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
        },
        g: function () { // 12-Hours; 1..12
            return f.G() % 12 || 12;
        },
        G: function () { // 24-Hours; 0..23
            return jsdate.getHours();
        },
        h: function () { // 12-Hours w/leading 0; 01..12
            return _pad(f.g(), 2);
        },
        H: function () { // 24-Hours w/leading 0; 00..23
            return _pad(f.G(), 2);
        },
        i: function () { // Minutes w/leading 0; 00..59
            return _pad(jsdate.getMinutes(), 2);
        },
        s: function () { // Seconds w/leading 0; 00..59
            return _pad(jsdate.getSeconds(), 2);
        },
        u: function () { // Microseconds; 000000-999000
            return _pad(jsdate.getMilliseconds() * 1000, 6);
        },
        // Timezone
        e: function () { // Timezone identifier; e.g. Atlantic/Azores, ...
            // The following works, but requires inclusion of the very large
            // timezone_abbreviations_list() function.
            /*              var abbr = '', i = 0, os = 0;
             if (that.php_js && that.php_js.default_timezone) {
             return that.php_js.default_timezone;
             }
             if (!tal.length) {
             tal = that.timezone_abbreviations_list();
             }
             for (abbr in tal) {
             for (i = 0; i < tal[abbr].length; i++) {
             os = -jsdate.getTimezoneOffset() * 60;
             if (tal[abbr][i].offset === os) {
             return tal[abbr][i].timezone_id;
             }
             }
             }
             */
            return 'UTC';
        },
        I: function () { // DST observed?; 0 or 1
            // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
            // If they are not equal, then DST is observed.
            var a = new Date(f.Y(), 0), // Jan 1
                c = Date.UTC(f.Y(), 0), // Jan 1 UTC
                b = new Date(f.Y(), 6), // Jul 1
                d = Date.UTC(f.Y(), 6); // Jul 1 UTC
            return 0 + ((a - c) !== (b - d));
        },
        O: function () { // Difference to GMT in hour format; e.g. +0200
            var a = jsdate.getTimezoneOffset();
            return (a > 0 ? "-" : "+") + _pad(Math.abs(a / 60 * 100), 4);
        },
        P: function () { // Difference to GMT w/colon; e.g. +02:00
            var O = f.O();
            return (O.substr(0, 3) + ":" + O.substr(3, 2));
        },
        T: function () { // Timezone abbreviation; e.g. EST, MDT, ...
            // The following works, but requires inclusion of the very
            // large timezone_abbreviations_list() function.
            /*              var abbr = '', i = 0, os = 0, default = 0;
             if (!tal.length) {
             tal = that.timezone_abbreviations_list();
             }
             if (that.php_js && that.php_js.default_timezone) {
             default = that.php_js.default_timezone;
             for (abbr in tal) {
             for (i=0; i < tal[abbr].length; i++) {
             if (tal[abbr][i].timezone_id === default) {
             return abbr.toUpperCase();
             }
             }
             }
             }
             for (abbr in tal) {
             for (i = 0; i < tal[abbr].length; i++) {
             os = -jsdate.getTimezoneOffset() * 60;
             if (tal[abbr][i].offset === os) {
             return abbr.toUpperCase();
             }
             }
             }
             */
            return 'UTC';
        },
        Z: function () { // Timezone offset in seconds (-43200...50400)
            return -jsdate.getTimezoneOffset() * 60;
        },
        // Full Date/Time
        c: function () { // ISO-8601 date.
            return 'Y-m-d\\Th:i:sP'.replace(formatChr, formatChrCb);
        },
        r: function () { // RFC 2822
            return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
        },
        U: function () { // Seconds since UNIX epoch
            return jsdate.getTime() / 1000 | 0;
        }
    };

    this.date = function (format, timestamp) {
        that = this;
        jsdate = (
            (typeof timestamp === 'undefined') ? new Date() : // Not provided
                (timestamp instanceof Date) ? new Date(timestamp) : // JS Date()
                    new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
            );
        return format.replace(formatChr, formatChrCb);
    };

    return this.date(format, timestamp);
}


function dbg() {
    if (typeof console != 'undefined' && typeof console.info != 'undefined' && settings.debug == 1) {
        for (var i = 0; i < arguments.length; i++) {
            console.info(arguments[i]);
        }
    }
}

function sleep(ms) {
    var zeit = (new Date()).getTime();
    var stoppZeit = zeit + ms;
    while ((new Date()).getTime() < stoppZeit) {
    }
    ;
}

// standart string replace functionality
function str_replace(haystack, needle, replacement) {
    var temp = haystack.split(needle);
    return temp.join(replacement);
}

// needle may be a regular expression
function str_replace_reg(haystack, needle, replacement) {
    var r = new RegExp(needle, 'g');
    return haystack.replace(r, replacement);
}

function trim(str, chars) {
    return ltrim(rtrim(str, chars), chars);
}

function ltrim(str, chars) {
    chars = chars || "\\s";
    return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
}

function rtrim(str, chars) {
    chars = chars || "\\s";
    return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
}

function strpos(haystack, needle, offset) {
    // Finds position of first occurrence of a string within another
    //
    // version: 810.1317
    // discuss at: http://kevin.vanzonneveld.net/techblog/article/javascript_equivalent_for_phps_strpos

    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Onno Marsman
    // *     example 1: strpos('Kevin van Zonneveld', 'e', 5);
    // *     returns 1: 14
    var i = (haystack + '').indexOf(needle, offset);
    return i === -1 ? false : i;
}

function responseIsOk(data) {


    if (typeof data == 'object' && data.hasOwnProperty('sessionerror')) {
        top.location.href = 'admin.php';
        return false;
    }


    if (typeof data == 'object' && typeof data.success == 'boolean' && data.success == false) {
        return false;
    } else {
        return true;
    }
}

function format_size(size) {
    units = new Array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    i = 0;
    while (size > 1024) {
        i++;
        size = size / 1024;
    }
    return size.toFixed(1) + ' ' + units[i];
}

//==========================================
// Get cookie
//==========================================
function CookieRegistry() {
    var self = this;
    var registryName = '';
    var rawCookie = '';
    var cookie = {};

    this.initialize = function (name) {
        self.registryName = name;
        name = name + '=';
        cookies = document.cookie.split(';');
        for (i = 0; i < cookies.length; i++) {
            var cookie = cookies[i];
            while (cookie.charAt(0) == ' ')
                cookie = cookie.substring(1, cookie.length);
            if (cookie.indexOf(name) == 0)
                self.rawCookie = decodeURIComponent(cookie.substring(name.length, cookie.length));
        }
        if (self.rawCookie) {
            self.cookie = eval('(' + self.rawCookie + ')');
        } else {
            self.cookie = {};
        }
        self.write();
    }

    this.get = function (name, def) {
        def = typeof def != 'undefined' ? def : false;
        return typeof self.cookie[name] != 'undefined' ? self.cookie[name] : def;
    }

    this.set = function (name, value) {
        self.cookie[name] = value;
        self.write();
    }

    this.erase = function (name) {
        delete self.cookie[name];
        self.write();
    }

    this.encode = function () {
        var results = [];
        for (var property in self.cookie) {
            value = self.cookie[property];
            if (typeof value != "number" && typeof value != "boolean") {
                value = '"' + value + '"';
            }
            results.push('"' + property + '":' + value);
        }
        return '{' + results.join(', ') + '}';
    }

    this.write = function () {
        var date = new Date();
        date.setTime(date.getTime() + 1209600000);
        var expires = "; expires=" + date.toGMTString();
        document.cookie = self.registryName + "=" + self.encode() + expires + "; path=/";
    }
}
Cookie = new CookieRegistry;
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
    getURLParam: function (strParamName) {
        var strReturn = "";
        var strHref = window.location.href;
        var bFound = false;

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


// JavaScript Document
/*************************************************
 Star Rating System
 First Version: 21 November, 2006
 Author: Ritesh Agrawal
 Inspriation: Will Stuckey's star rating system (http://sandbox.wilstuckey.com/jquery-ratings/)
 Demonstration: http://php.scripts.psu.edu/rja171/widgets/rating.php
 Usage: $('#rating').rating('www.url.to.post.com', {maxvalue:5, curvalue:0});

 arguments
 url : required -- post changes to options
 maxvalue: number of stars
 curvalue: number of selected stars

 ************************************************/

(function ($) {

    $.fn.rating = function (options) {

        var settings = {
            url: '', // post changes to
            maxvalue: 5, // max number of stars
            value: 0, // number of selected stars
            votes: '0', // number of votes
            rating: '0.00'    // number of rating
        };

        var respSet = false;
        var options = options || {};
        $.extend(settings, options);
        $.extend(settings, {
            cancel: (settings.maxvalue > 1) ? false : false
        });

        var container = this;

        if (settings.url == '') {
            container.append('Rating url not set!');
            return;
        }

        if (!settings.url.match(/\$/)) {
            settings.url += '/';
        }


        $.extend(container, {
            averageRating: settings.value,
            url: settings.url
        });

        var rateContainer = $('<div></div>').addClass('ui-rating-container');
        var ul = $('<ul>').addClass('rating');

        for (var i = 1; i <= settings.maxvalue; i++) {
            ul.append('<li title="Give it ' + i + ' of ' + settings.maxvalue + ' Stars" style="background-image: url(\'' + coreHtmlPath + 'img/star.png\');"><span>' + i + '</span></li>');
        }

        rateContainer.append(ul);
        rateContainer.append('<div style="clear:both;"></div>');

        var votes_container = $('<span class="votes">' + settings.votes + ' Votes / </span>');
        var rating_container = $('<span class="result">Result: ' + settings.rating + '</span>');
        var respmsg_container = $('<div class="msg"></div>');
        var _container = $('<div class="rating-msg"></div>');

        _container.append(votes_container);
        _container.append(rating_container);
        _container.append(respmsg_container);
        rateContainer.append(_container);
        container.append(rateContainer);

        var stars = $(ul).children('li');
        var cancel = $(rateContainer).children('.cancel');

        var event = {
            reset: function () { // Reset the stars to the default index.
                ul.children("li").css('background-position', '0px 0px');
                ul.children("li").slice(0, settings.value).css('background-position', '0px -50px');
            }
        };


        ul.children("li").unbind('hover').hover(function () {
            if (respSet)
                return;
            $(this).parent().children("li").css('background-position', '0px 0px');
            var a = $(this).parent().children("li").index($(this));
            $(this).parent().children("li").slice(0, a + 1).css('background-position', '0px -25px')
        }, function () {
        });

        ul.unbind('hover').hover(function () {
        }, function () {
            if (respSet)
                return;
            if (settings.value == "" || settings.value == 0) {
                $(this).children("li").slice(0, 0).css('background-position', '0px 0px')
            } else {
                $(this).children("li").css('background-position', '0px 0px');
                event.reset();
            }
        });


        stars.unbind('click').click(function (e) {

            e.preventDefault();

            if (settings.maxvalue > 1) {
                var feedback = $('#ajax-feedback');
                if (feedback && feedback.length) {
                    feedback.animate({
                        'height': 'toggle',
                        'opacity': 'toggle'
                    }, 'slow');
                }

                var val = stars.index(this) + 1;

                $.get(container.url + val, {}, function (data) {


                    if (feedback && feedback.length) {
                        feedback.animate({
                            'height': 'toggle',
                            'opacity': 'toggle'
                        }, 'slow');
                    }


                    if (responseIsOk(data)) {
                        var retvalue = Math.round(data.rating);

                        settings.value = retvalue;

                        respSet = true;
                        $('.rating-msg .votes').text(data.votes + ' Votes / ');
                        $('.rating-msg .result').text('Result: ' + data.rating);
                        respmsg_container.text(data.msg).show();


                        $(container).parents().find('h6:first').text(data.rating);
                        event.reset();

                        respmsg_container.effect('pulsate', {
                            times: 4,
                            easing: 'easeInOutBounce'
                        }, 500);

                        setTimeout(function () {
                            respmsg_container.fadeOut('slow', function () {
                                $(this).empty();
                            })
                        }, 5000);

                        stars.unbind('click');
                        stars.unbind('hover');
                        ul.unbind('hover');
                        ul.children("li").css({
                            'cursor': 'default'
                        });
                    }
                    else {
                        alert(data.msg);
                    }

                }, 'json');

                return false;
            }
            else if (settings.maxvalue == 1) {
                settings.value = (settings.value == 0) ? 1 : 0;
                $(this).toggleClass('active');
                $.get(container.url + stars.index(this) + 1, {}, function () {

                }, 'json');
                return false;
            }

            return true;

        });



        event.reset();
        return(this);
    }

})
    (jQuery);


/**
 * jQuery Plugins Pagemask and registerForm
 *
 */
(function ($) {
    $.pagemask = {
        show: function (label) {

            $("body").append('<div id="popup_overlay"></div>');
            $("#popup_overlay").css({
                position: 'fixed',
                zIndex: 999998,
                top: '0',
                left: '0',
                width: '100%',
                height: '100%',
                background: '#555555',
                opacity: .4
            });
            if (typeof label == "string") {
                var maskMsgDiv = $('<div class="loadmask-msg" id="popup_overlay_msg" style="display:none;"></div>');
                maskMsgDiv.append('<div>' + label + '</div>');
                $('body').append(maskMsgDiv);
                maskMsgDiv.css({
                    zIndex: 999999,
                    width: maskMsgDiv.width(),
                    height: maskMsgDiv.height(),
                    position: 'fixed',
                    left: '50%',
                    top: '50%',
                    marginLeft: 0 - Math.floor(maskMsgDiv.outerWidth() / 2),
                    marginTop: 0 - Math.floor(maskMsgDiv.outerHeight() / 2)
                });
                maskMsgDiv.show();
            }
            var maskRmvDiv = $('<div class="loadmask-remove" id="popup_overlay_remove" style="display:none;"></div>');
            maskRmvDiv.append($('<img>').attr({
                src: systemUrl + '/html/img/cancel.png',
                width: 16,
                height: 16,
                title: ''
            }));
            $('body').append(maskRmvDiv);
            maskRmvDiv.css({
                opacity: .3,
                zIndex: 999999,
                width: 16,
                height: 16,
                position: 'fixed',
                right: '10px',
                top: '10px',
                cursor: 'pointer'
            });
            maskRmvDiv.hover(
                function () {
                    $(this).css({
                        opacity: 1
                    });
                },
                function () {
                    $(this).css({
                        opacity: .3
                    });
                }
            );
            maskRmvDiv.unbind('click').bind('click', function () {
                $.pagemask.hide();
            });
            maskRmvDiv.show();
        },
        hide: function () {

            $("#popup_overlay").remove();
            $("#popup_overlay_msg").remove();
            $("#popup_overlay_remove").remove();
        }
    };
    /**
     * Displays loading mask over selected element.
     *
     * @param label Text message that will be displayed on the top of a mask besides a spinner (optional).
     *                 If not provided only mask will be displayed without a label or a spinner.
     */
    $.fn.mask = function (label) {
        if (!$(this).find('div.loadmask:first').length) {
            $.fn.maskElement($(this), label);
        }
        return this;
    };
    $.fn.maskElement = function (element, label) {

        if (element.isMasked()) {
            element.unmask();
        }

        if (element.css("position") == "static") {
            element.addClass("masked-relative");
        }

        element.addClass("masked");

        var maskDiv = $('<div class="loadmask"></div>');
        var maskHeight = $(element).height() - parseInt($(element).css("padding-top"));
        var maskWidth = $(element).width() + parseInt($(element).css("padding-left")) + parseInt($(element).css("padding-right"));
        maskDiv.height(maskHeight);
        maskDiv.width(maskWidth);

        //fix for z-index bug with selects in IE6
        if (navigator.userAgent.toLowerCase().indexOf("msie 6") > -1) {
            //element.find("select").addClass("masked-hidden");
        }

        element.append(maskDiv);

        if (typeof label == "string") {
            var maskMsgDiv = $('<div class="loadmask-msg" style="display:none;"></div>');


            var d = $('<div>');
            if ($('body').find('img.loading-indicator').length) {
                var clone = $('body').find('img.loading-indicator').clone();
                clone.css({visibility: ''}).show();
                d.append(clone);
            }

            d.append(label);
            maskMsgDiv.append(d);


            //calculate center position
            // maskMsgDiv.css("top", Math.round(element.height() / 2 - (maskMsgDiv.height() - parseInt(maskMsgDiv.css("padding-top")) - parseInt(maskMsgDiv.css("padding-bottom"))) / 2)+"px");
            element.append(maskMsgDiv);
            maskMsgDiv.show();
            //calculate center position
            maskMsgDiv.css("top", Math.round((maskHeight / 2) - (maskMsgDiv.outerHeight(true) / 2)) + "px");
            maskMsgDiv.css("left", Math.round((maskWidth / 2) - (maskMsgDiv.outerWidth(true)) / 2) + "px");
        }


    };
    /**
     * Checks if a single element is masked. Returns false if mask is delayed or not displayed.
     */
    $.fn.isMasked = function () {
        return $(this).hasClass("masked");
    };
    /**
     * Removes mask from the element.
     */
    $.fn.unmask = function () {
        var element = $(this);
        element.find("div.loadmask-msg:first,div.loadmask:first").fadeOut(350, function () {
            element.removeClass("masked").removeClass("masked-relative");
            element.find("select").removeClass("masked-hidden");
            element.find("div.loadmask-msg:first,div.loadmask:first").remove();
        });
    };
    $.unmaskElement = function (element) {

        element.find("div.loadmask-msg:first,div.loadmask:first").fadeOut(350, function () {
            element.removeClass("masked").removeClass("masked-relative");
            element.find("select").removeClass("masked-hidden");
            element.find("div.loadmask-msg:first,div.loadmask:first").remove();
        });
    };
    $.fn.registerFormFE = function (options) {

        var config = {
            focus_first: false,
            error: function (data) {
                if (data.errors != undefined) {
                    displayFormErrors(data, self.attr('id'));
                }
                else {
                    alert(data.msg);
                }
            }
        };

        var options = options || {};
        this._config = $.extend({}, config, options);
        var self = this;

        $(self).unbind('submit').submit(function (e) {
            e.preventDefault();
            self._config.save(false);

        });

        $(self).find('.save-exit-button').unbind('click').bind('click', function (e) {
            self._config.save(true);
        });
        $(self).find('button.save,.save-button').unbind('click').bind('click', function (e) {
            self._config.save(false);
        });


        $(self).find('button.reset,.reset-button').unbind('click').bind('click', function (e) {
            $(self).get(0).reset();
        });

        if (self._config.focus_first !== false) {
            if (typeof self._config.focus_first != 'undefined' && typeof self._config.focus_first == 'string') {
                focus_string = '#' + self._config.focus_first + ':first';
            } else {
                focus_string = ':input:visible:enabled:first';
            }

            // @todo focus patch
            $(document).ready(function () {
                // $(this).find(focus_string).focus();
            });
        }

        return self;
    };
})(jQuery);


(function ($) {
    $.fn.w3cValidator = function (options) {
        var opts = $.extend({}, $.fn.w3cValidator.defaults, options);

        function str_replace(search, replace, subject) {
            return subject.split(search).join(replace);
        }

        return this.each(function () {
            currentURL = "" + encodeURIComponent(window.location);
            var $this = $(this);
            $.ajax({
                type: "POST",
                url: opts["parserLocation"],
                data: "url=" + currentURL,
                success: function (isValid) {
                    if (isValid == "true") {
                        $this.append(opts["URL_isValid"]);
                    } else {
                        $this.append(opts["URL_isInvalid"]);
                    }
                }
            });
        });
    };

    // plugin defaults
    $.fn.w3cValidator.defaults = {
        URL_isValid: '<a href="http://validator.w3.org/check?uri=referer">valid HTML</a>',
        URL_isInvalid: '<a href="http://validator.w3.org/check?uri=referer">invalid HTML</a>'
    };
})(jQuery);


/**
 *    Form Error functions
 *    call from $.registerForm.error()
 */
var errorHider;
function buildErrorDisplay(want_ol, selector) {
    if ($('#error-display').length == 0) {
        var err_disp = $(
            '<div id="error-display" style="display: none;">' +
                '<div id="error-wrap">' +
                '<div id="error-title">' +
                '<div id="error-title-text">Es ist ein Fehler aufgetreten...</div>' +
                '<div id="error-closer">[ Schließen ]</div>' +
                '<br style="clear: both;"/>' +
                '</div>' +
                '<div id="error-content"></div>' +
                '</div>' +
                '</div>'
        );
        err_disp.appendTo($('body'));


        $('#error-closer').unbind('click').bind('click', function () {
            $('#error-display').slideToggle('fast');
        });
    }


    $('#error-content').empty();
    $('#error-display').css({
        opacity: 1
    }).hide();

    if (typeof want_ol != 'undefined' && want_ol) {
        $('#error-content').append($('<ol>').attr({
            id: 'error-list'
        }));
    }
}

function displayErrorDlg(data) {
    if (data.errors != undefined) {
        displayFormErrors(data, $('body'));
    }
    else {
        jAlert(data.msg);
    }
}

function displayFormErrors(data, formid, errorContainerID) {

    var inline = false, selector = $('#' + formid);
    if (typeof errorContainerID == 'string') {
        var c = $(errorContainerID);
        if (c.length == 1) {
            inline = true;
            c.empty().addClass('fade').show();

            var ul = $('<ul id="error-list"></ul>');
            ul.appendTo(c);

            for (var i in data.errors) {
                for (var x in data.errors[i]) {
                    var li = $('<li>');

                    if (selector.find('#' + i).length > 0 || selector.find("input[name='" + i + "']").length > 0) {
                        var error_link = $('<a>').attr({
                            href: '#',
                            rel: i
                        });
                        error_link.append(data.errors[i][x]);

                        li.append(error_link);
                        ul.append(li);
                        error_link.bind('click', function (e) {
                            e.preventDefault();

                            var target = selector.find('#' + $(this).attr('rel'));
                            if (target.length == 0) {
                                target = selector.find("input[name='" + $(this).attr('rel') + "']")
                            }

                            var panel = target.parents('div.panel-inner');
                            if (panel) {
                                if (!panel.is(':visible')) {
                                    panel.parent().find('h2').click();
                                }
                            }

                            var tab = target.parents('div.tab');
                            if (tab) {
                                if (!tab.is(':visible')) {
                                    pos = tab.prevAll('.tab').length;
                                    // find tab container
                                    var tabToggles = tab.parents('div.tab-panel').find('ul.tab-header .tab-toggle:visible');
                                    $(tabToggles[pos]).click();
                                }
                            }


                            var viewTop = $(window).scrollTop();
                            var viewBottom = viewTop + $(window).height();
                            var pos = target.offset();
                            var wtop = pos.top - 110;

                            if (wtop < viewTop || wtop > viewBottom) {
                                window.scrollTo(0, wtop);
                            }

                            $(e.target).effect("transfer", {
                                to: target,
                                className: "error-transfer"
                            }, 600);

                            //try { target.get(0).focus(); } catch(e) {}

                            return false;
                        });


                    }
                    else {
                        li.append(data.errors[i][x]);
                        ul.append(li);

                        // 'errorize' the field
                        var el = false;
                        if (selector.find('#' + i).length > 0) {
                            el = selector.find('#' + i);
                        }
                        if (!el && selector.find("input[name='" + i + "']").length > 0) {
                            el = selector.find("input[name='" + i + "']");
                        }
                        if (el) {
                            el.parents('.form-item').addClass('error');
                            el.parents('fieldset').addClass('error');

                            var frm_img = el.find('.form-item-images');
                            if (frm_img.length > 0) {
                                if (frm_img.find('img').length == 1) {
                                    frm_img.append(
                                        $('<img>').attr({
                                            src: 'html/style/default/img/not-ok.png',
                                            width: 16,
                                            height: 16,
                                            title: ''
                                        }).css({
                                            marginLeft: 4,
                                            cursor: 'pointer'
                                        }).addClass('field-error-indicator').unbind('click').bind('click', function () {

                                            if (!$('#error-display').is(':visible')) {
                                                $('#error-display').fadeIn('fast');
                                            }

                                            $(this).effect("transfer", {
                                                to: $('#error-list'),
                                                className: "error-transfer"
                                            }, 600);
                                        })
                                    );
                                }
                            }

                            el.one('change', function (event) {
                                removeError(this, c);
                            });
                        }
                    }
                }
            }

            c.addClass('in');

        }
    }


    if (!inline) {
        buildErrorDisplay(true, selector);

        for (var i in data.errors) {
            for (var x in data.errors[i]) {
                // append a message to the error-display
                var container = $('<li>');

                // console.log(data.errors[i][x]);

                if (selector.find('#' + i).length > 0 || selector.find("input[name='" + i + "']").length > 0) {


                    var error_link = $('<a>').attr({
                        href: '#',
                        rel: i
                    });
                    error_link.append(data.errors[i][x]);

                    container.append(error_link);
                    $('#error-list').append(container);

                    error_link.bind('click', function (e) {
                        var target = selector.find('#' + $(this).attr('rel'));
                        if (target.length == 0) {
                            target = selector.find("input[name='" + $(this).attr('rel') + "']")
                        }

                        var panel = target.parents('div.panel-inner');
                        if (panel) {
                            if (!panel.is(':visible')) {
                                panel.parent().find('h2').click();
                            }
                        }

                        var tab = target.parents('div.tab');
                        if (tab) {
                            if (!tab.is(':visible')) {
                                pos = tab.prevAll('.tab').length;
                                // find tab container
                                var tabToggles = tab.parents('div.tab-panel').find('ul.tab-header .tab-toggle:visible');
                                $(tabToggles[pos]).click();
                            }
                        }


                        var viewTop = $(window).scrollTop();
                        var viewBottom = viewTop + $(window).height();
                        var pos = target.offset();
                        var wtop = pos.top - 110;
                        if (wtop < viewTop || wtop > viewBottom) {
                            window.scrollTo(0, wtop);
                        }
                        $('#error-display').effect("transfer", {
                            to: target,
                            className: "error-transfer"
                        }, 600);

                        //try { target.get(0).focus(); } catch(e) {}
                        e.preventDefault();
                        return false;
                    });

                } else {
                    container.append(data.errors[i][x]);


                    $('#error-list').append(container);

                    // 'errorize' the field
                    var el = false;
                    if (selector.find('#' + i).length > 0) {
                        el = selector.find('#' + i);
                    }
                    if (!el && selector.find("input[name='" + i + "']").length > 0) {
                        el = selector.find("input[name='" + i + "']");
                    }
                    if (el) {
                        el.parents('.form-item').addClass('error');
                        el.parents('fieldset').addClass('error');

                        frm_img = el.find('.form-item-images');
                        if (frm_img.length > 0) {
                            if (frm_img.find('img').length == 1) {
                                frm_img.append(
                                    $('<img>').attr({
                                        src: 'html/style/default/img/not-ok.png',
                                        width: 16,
                                        height: 16,
                                        title: ''
                                    }).css({
                                        marginLeft: 4,
                                        cursor: 'pointer'
                                    }).addClass('field-error-indicator').unbind('click').bind('click', function () {
                                        if (!$('#error-display').is(':visible')) {
                                            $('#error-display').fadeIn('fast');
                                        }
                                        $(this).effect("transfer", {
                                            to: $('#error-display'),
                                            className: "error-transfer"
                                        }, 1000);
                                    })
                                );
                            }
                        }
                        el.one('change', function (event) {
                            removeError(this);
                        });
                    }
                }
            }

            $('#error-display').stop().show().slideDown(300); //.animate({opacity: '1'}, 250, function(){ $(this).css('opacity', 1); });
        }
    }


}

function removeError(el, errorContainer) {
    var row = $(el).parents('.form-item');
    row.removeClass('error');
    row.find('.form-item-images img:first').next().remove();

    var row = $(el).parents('fieldset');
    row.removeClass('error');
    row.find('.form-item-images img:first').next().remove();


    var element = false;
    if ($('#error-list a[rel="' + $(el).attr('id') + '"]').length > 0) {
        $('#error-list a[rel="' + $(el).attr('id') + '"]').parent('li').remove();
        element = true;
    }

    var name = $(el).attr('name');

    if (name.indexOf('[') > 0)
        name = name.substr(0, name.indexOf('['));

    if (!element && $('#error-list a[rel="' + name + '"]').length > 0) {
        $('#error-list a[rel="' + name + '"]').parent('li').remove();
    }

    if ($('#error-list').is(':empty')) {
        $('#error-display').hide();

        if (errorContainer.length) {
            errorContainer.hide();
        }
    }
}

function hideFormErrors() {
    $('#error-display').hide();
    $('div.error').removeClass('error');
    $('fieldset.error').removeClass('error');
    $('.field-error-indicator').remove();
}

/*** End Form Error Call ***/




function getIndexPage(url, value) {
    if (!value)
        return;
    $('#ajax-feedback').animate({
        'height': 'toggle',
        'opacity': 'toggle'
    }, 'slow');
    var docname = document.location.href.split('/');
    if (docname[ docname.length - 1]) {
        document.location.href = systemUrl + url + value + '/' + docname[ docname.length - 1 ];
    }
    else {
        document.location.href = systemUrl + url + value;
    }
}

/**
 * IE not tested!
 * Firefox 27.x, Chrome 32.x and Safari 6.x works
 * @param element
 * @param hash
 */
function captchAudio(element, hash) {
    var url = 'main/captcha/audio/' + hash;
    var el = $(element);

    if (element.mp3) {
        if (element.mp3.paused) {
            el.removeClass('play').addClass('pause');
            element.mp3.play();
        }
        else {
            el.removeClass('pause').addClass('play');
            element.mp3.pause();
        }
    }
    else {
        el.addClass('pause');
        element.mp3 = new Audio(url);
        $(element.mp3).bind('ended', function () {
            element.mp3 = null;
            el.removeClass('play pause');
        });

        element.mp3.play();
    }
}


/**
 * Reload Captcha
 *
 * @param elementId
 */
function reloadcaptch(elementId) {
    var player = $('#' + elementId).parents('div.captcha-container:first').find('a.captcha-audio').removeClass('play pause').get(0);
    if (player.mp3) {
        player.mp3.pause();
        player.mp3 = null;
    }

    $('#' + elementId).attr('src', 'main/captcha/' + elementId + '/refresh?' + new Date().getTime());
}


function printPage() {
    var name = 'print';
    var width = 800;
    var height = 600;

    url = document.location.href;
    if (url.match(/\?/)) {
        url += '&print=1';
    }
    else {
        url += '?print=1';
    }

    options = ',location=no, menubar=no,toolbar=no,resizable=no,scrollbars=yes,status=no';
    width = parseInt(width);
    height = parseInt(height);

    var posLeft = (screen.width / 2) - (width / 2);
    var posTop = (screen.height / 2) - (height / 2);
    window.open(url, name, 'top=' + posTop + ',left=' + posLeft + ',width=' + width + ',height=' + height + ',' + options);
}


// Setup Syntax Highlighter
function findSyntaxes() {
    if (typeof SyntaxHighlighter != 'undefined') {
        SyntaxHighlighter.highlight();
    }
}

function loadComponentContent(componentename, params, callback) {
    if (componentename == '' || typeof componentename == 'undefined') {
        return null;
    }

    var _params = ((typeof params != 'undefined' && params != '') ? '&' + params : '');


    var post = 'cp=main&action=runcomponent&com=' + componentename + _params
    alert(post);
    $.ajax({
        dataType: 'json',
        cache: false,
        url: 'index.php?' + post,
        success: function (data) {
            if (responseIsOk(data)) {
                if (typeof callback == 'function') {
                    callback(data);
                }
                else {
                    return req;
                }
            }
            else {
                alert(data);
            }
        }



    });
}

function loadImage(image, call) {
    if (call != 'undefined') {
        eval(call(data));
    }
}
;

//
function replyComment(postid) {
    $('#parentComment').val(postid);

    if (!$('#parentComment').parents('form:first').is(':visible')) {
        $('#parentComment').parents('form:first').slideToggle();
    }
    else {

    }

    $('.bbcodeCommentTextarea').focus();


    $('html, body').animate({scrollTop: $('#parentComment').parents('form:first').offset().top - 60}, 350, 'swing');
    return false;
}
;

//
function getPageTranslation() {

    $.translate(function () {

        function translateTo(destLang) {
            originalColor = '#262c33';
            var tlc = $.translate().toLanguageCode;
            if (tlc(destLang) == "de" && tlc(Cookie.get('destLang')) == "de") {
                $('ul#flags').find('span').removeClass('load').addClass('finish');
                $.pagemask.hide();
                return;
            }

            $('body').translate('de', destLang, {
                not: '#jq-translate-ui',
                fromOriginal: true,
                start: function () {
                    $('body').css('color', '#a0a8b2');
                },
                complete: function () {
                    $('body').css('color', originalColor);
                    $.pagemask.hide();
                    $('ul#flags').find('span.load').removeClass('load').addClass('finish');
                },
                error: function () {
                    $('body').css('color', originalColor);
                    $.pagemask.hide();
                }
            });
        }

        $('ul#flags').find('span').click(function () {
            $('ul#flags').find('span').removeClass('finish');
            $.pagemask.show('Translate...');
            $(this).addClass('load');
            var lang = $(this).attr('id');
            translateTo(lang);
            Cookie.set('destLang', lang);
            return false;
        });
        var destLang = Cookie.get('destLang');
        if (destLang) {
            $('ul#flags').find('span').removeClass('finish');
            $('ul#flags').find('span[id=' + destLang + ']').addClass('load');
            $.pagemask.show('Translate...');
            translateTo(destLang);
        }
    });

}
;


$.fn.disableButton = function () {
    return this.each(function () {
        if ($(this).hasClass('pretty-button') || $(this).hasClass('action-button')) {
            $(this).attr('disabled', 'disabled');
            $(this).addClass('button-disabled');
        }
    });
}

$.fn.enableButton = function () {
    return this.each(function () {
        if ($(this).hasClass('pretty-button') || $(this).hasClass('action-button')) {
            $(this).removeAttr('disabled');
            $(this).removeClass('button-disabled');
        }
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

$.fn.signature = function (opts) {
    var self = this;
    var defaults = {
        imgupload: false,
        bodyClass: "content",
        buttons: 'undo,redo,|,bold,italic,underline,|,quote,bullist,numlist,code,|,img,link,smilebox,fontsize,fontcolor,|,removeFormat',
        autoresize: false,
        minheight: 250,
        resize_maxheight: 420
    };

    opts = $.extend(defaults, opts);

    if (typeof $.wysibb == 'undefined') {

        Loader.loadCss('html/js/jquery/wysibb/theme/default/wbbtheme.css');
        return Loader.require(
            'html/js/jquery/wysibb/jquery.wysibb.js',
            'html/js/jquery/wysibb/lang/en.js',
            'html/js/jquery/wysibb/lang/de.js',
            function () {
                CURLANG = WBBLANG['de'] || WBBLANG['en'] || CURLANG;

                return self.each(function () {
                    $(this).wysibb(opts);
                });
            }
        );
    }
    else {

        CURLANG = WBBLANG['de'] || WBBLANG['en'] || CURLANG;

        return this.each(function () {
            $(this).wysibb(opts);
        });
    }
};

$.fn.comment = function (opts) {
    var self = this;

    var defaults = {
        imgupload: false,
        bodyClass: "content",
        buttons: 'undo,redo,|,bold,italic,underline,|,quote,bullist,numlist,|,code,|,img,linkfontsize,fontcolor,smilebox,|,removeFormat',
        autoresize: false,
        minheight: 250,
        resize_maxheight: 420
    };
    var opt = $.extend({}, defaults, opts);

    if (typeof opts.smilies != 'undefined' && opts.smiliepath) {
        for (var x in opts.smilies) {
            opts.smilies[x].img = '<img src="' + opts.smiliepath.replace(/\/$/, '') + '/' + opts.smilies[x].imgpath + '" class="sm">';
        }

        opt.smileList = opts.smilies;
    }


    if (typeof $.wysibb == 'undefined') {

        Loader.loadCss('html/js/jquery/wysibb/theme/default/wbbtheme.css');
        return Loader.require(
            'html/js/jquery/wysibb/jquery.wysibb.js',
            'html/js/jquery/wysibb/lang/en.js',
            'html/js/jquery/wysibb/lang/de.js',
            function () {
                CURLANG = WBBLANG['de'] || WBBLANG['en'] || CURLANG;

                return self.each(function () {
                    $(this).wysibb(opt);
                });
            }
        );
    }
    else {

        CURLANG = WBBLANG['de'] || WBBLANG['en'] || CURLANG;

        return this.each(function () {
            $(this).wysibb(opt);
        });
    }
};
$.fn.syncComment = function (opts) {
    if ($(this).data('wbb')) {
        return $(this).sync();
    }
};
$.fn.getComment = function (opts) {
    if ($(this).data('wbb')) {
        return $(this).getBBCode();
    }
};
/**
 * jQuery sound plugin (no flash)
 *
 * port of script.aculo.us' sound.js (http://script.aculo.us), based on code by Jules Gravinese (http://www.webveteran.com/)
 *
 * Copyright (c) 2007 JÃ¶rn Zaefferer (http://bassistance.de)
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * $Id$
 */

/**
 * API Documentation
 *
 * // play a sound from the url
 * $.sound.play(url)
 *
 * // play a sound from the url, on a track, stopping any sound already running on that track
 * $.sound.play(url, {
 *   track: "track1"
 * });
 *
 * // increase the timeout to four seconds before removing the sound object from the dom for longer sounds
 * $.sound.play(url, {
 *   timeout: 4000
 * });
 *
 * // stop a sound by removing the element returned by play
 * var sound = $.sound.play(url);
 * sound.remove();
 *
 * // disable playing sounds
 * $.sound.enabled = false;
 *
 * // enable playing sounds
 * $.sound.enabled = true
 */
(function ($) {

    $.sound = {
        tracks: {},
        enabled: true,
        template: function (src) {
            return '<embed style="height:0" loop="false" src="' + src + '" autostart="true" hidden="true"/>';
        },
        play: function (url, options) {
            if (!this.enabled)
                return;
            var settings = $.extend({
                url: url,
                timeout: 2000
            }, options);

            if (settings.track) {
                if (this.tracks[settings.track]) {
                    var current = this.tracks[settings.track];
                    // TODO check when Stop is avaiable, certainly not on a jQuery object
                    current.Stop && current.Stop();
                    current.remove();
                }
            }

            var element = $.browser.msie
                ? $('<bgsound/>').attr({
                src: settings.url,
                loop: 1,
                autostart: true
            }) : $(this.template(settings.url));

            element.appendTo("body");

            if (settings.track) {
                this.tracks[settings.track] = element;
            }

            if (options) {
                setTimeout(function () {
                    element.remove();
                }, options.timeout)
            }

            return element;
        }
    };

})(jQuery);

var loaded = false;

$(document).ready(function () {

    if (loaded)
        return;

    loaded = true;


    if (!$.fn.rating) {
        $('head').append('<script type="text/javascript" src="' + coreHtmlPath + 'js/rating_star.js?_' + cookiePrefix + '"></script>');
        $('head').append('<link rel="stylesheet" href="' + coreHtmlPath + 'img/rating_star.css" type="text/css"/>');
    }

    $(document).find('.print-button a').click(function (e) {
        e.preventDefault();
        printPage();
        return false;
    });
    $(document).find('.print-button').click(function (e) {
        printPage();
    });


    $('.audiable').each(function () {
        $(this).click(function () {

            if ($(this).data('sound')) {
                var sound = $(this).data('sound');
                sound.remove();
                $(this).removeData('sound');


                return;
            }


            var path = window.location.pathname;


            if (path.match(/^\//)) {
                path = path.substr(1);
            }


            var newURL = window.location.protocol + "//" + window.location.host + "/" + path;

            if (!newURL.match(/\?/)) {
                newURL += '?speak=1';
            }
            else {
                newURL += '&speak=1';
            }

            console.log(newURL);

            $(this).data('sound', $.sound.play(newURL));


        });
    });

    /*
     // find all captcha images an add actions
     $('.captchaImage').css({
     cursor: 'pointer'
     }).click(function () {
     $('#ajax-feedback').animate({
     'height': 'toggle',
     'opacity': 'toggle'
     }, 'slow');
     $(this).attr('src', 'main/captcha/' + $(this).attr('id') + '/refresh?' + new Date().getTime());
     $('#ajax-feedback').animate({
     'height': 'toggle',
     'opacity': 'toggle'
     }, 'slow');
     });
     */


    // make forum jump
    $('#forumjump').change(function () {
        if ($(this).val() != '') {
            window.location.href = 'plugin/forum/' + $(this).val();
        }
    });


    // ajax setup
    if (typeof $.ajaxSetup == 'function') {
        $.ajaxSetup({
            dataType: "json",
            cache: false,
            async: true
        });
    }

    // $('head').append($( '<script type="text/javascript" src="main/js/jquery/jquery.scrollTo.js"></script>') );
    // $.getScript(systemUrl +'/html/js/jquery/jquery.beautyOfCode.js', function() { getPageTranslation(); });
    // $.getScript('main/js/jquery/jquery.beautyOfCode.js', function() { reInitbeautyOfCode(); });


    // apply tabby to textareas?
    if (typeof $().tabby == 'undefined') {
        $.getScript(systemUrl + '/html/js/jquery/jquery.textarea.js?_' + cookiePrefix, function () {
            if ($.fn.tabby) {
                $(document).find('textarea').tabby();
            }
        });
    }


    if ($('#commentFrom').length && typeof registerComments !== 'function') {
        $.getScript(systemUrl + '/html/js/dcms.comments.js?_' + cookiePrefix, function () {
            registerComments();
        });
    }
});



