
var Tools = {
    exists: function(object, name )
    {
        return object.hasOwnProperty(name);
    },
    
    isUndefined: function (test)
    {
        if (typeof test == "undefined")
        {
            return true;
        }
        return false;
    },
    isObject: function (test)
    {
        if (typeof test == "object")
        {
            return true;
        }
        return false;
    },
    isFunction: function (test)
    {
        if (typeof test == "function")
        {
            return true;
        }
        return false;
    },
    isInteger: function (_test)
    {
        if (/^[0-9]+$/.test(_test))
        {
            return true;
        }
        return false;
    },
    isString: function (_test)
    {
        if (typeof _test == 'string')
        {
            return true;
        }
        return false;
    },

    $bdetect: null,

    browserDetect: function ()
    {
        if (this.$bdetect !== null)
        {
            return;
        }
        var Browser = this.$bdetect = (function ()
        {
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
                    xpath: !! (document.evaluate),
                    air: !! (window.runtime),
                    query: !! (document.querySelector),
                    json: !! (window.JSON)
                },
                Plugins: {}
            };
            b[b.name] = true;
            b[b.name + parseInt(b.version, 10)] = true;
            b.Platform[b.Platform.name] = true;
            return b;
        })();
        var UA = navigator.userAgent.toLowerCase();
        this.isGecko = !! Browser.firefox;
        this.isChrome = !! Browser.chrome;
        this.isSafari = !! Browser.safari;
        this.isSafariOld = Browser.safari && Browser.version === 2.4;
        this.isWebkit = this.isSafari || this.isChrome || UA.indexOf("konqueror") != -1;
        this.isOpera = !! Browser.opera;
        this.isIE = !! Browser.ie;
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


    getDirname: function (url)
    {
        return ((url || "").match(/^([^#]*\/)[^\/]*(?:$|\#)/) || {})[1];
    },
    getFilename: function (url)
    {
        return ((url || "").split("?")[0].match(/(?:\/|^)([^\/]+)$/) || {})[1];
    },
    getAbsolutePath: function (base, url)
    {
        return url && url.charAt(0) == "/" ? url : (!url || !base || url.match(/^\w+\:\/\//) ? url : base.replace(/\/$/, "") + "/" + url.replace(/^\//, ""));
    }
};


// ==========================================
// CookieRegistry

function CookieRegistry()
{
    var self = this;
    var registryName = '';
    var rawCookie = '';
    var cookie = {};
    this.initialize = function (name)
    {
        self.registryName = name;
        name = name + '=';
        cookies = document.cookie.split(';');
        for (i = 0; i < cookies.length; i++)
        {
            var cookie = cookies[i];
            while (cookie.charAt(0) == ' ') cookie = cookie.substring(1, cookie.length);
            if (cookie.indexOf(name) == 0) self.rawCookie = decodeURIComponent(cookie.substring(name.length, cookie.length));
        }
        if (self.rawCookie)
        {
            self.cookie = eval('(' + self.rawCookie + ')');
        }
        else
        {
            self.cookie = {};
        }
        self.write();
    }
    this.get = function (name, def)
    {
        def = typeof def != 'undefined' ? def : false;
        return typeof self.cookie[name] != 'undefined' ? self.cookie[name] : def;
    }
    this.set = function (name, value)
    {
        self.cookie[name] = value;
        self.write();
    }
    this.erase = function (name)
    {
        delete self.cookie[name];
        self.write();
    }
    this.encode = function ()
    {
        var results = [];
        for (var property in self.cookie)
        {
            value = self.cookie[property];
            if (typeof value != "number" && typeof value != "boolean")
            {
                value = '"' + value + '"';
            }
            results.push('"' + property + '":' + value);
        }
        return '{' + results.join(', ') + '}';
    }
    this.write = function ()
    {
        var date = new Date();
        date.setTime(date.getTime() + 1209600000);
        var expires = "; expires=" + date.toGMTString();
        document.cookie = self.registryName + "=" + self.encode() + expires + "; path=/";
    }
}
var Cookie = new CookieRegistry;
Cookie.initialize(cookiePrefix + '_registry');

/* Copyright (c) 2006 Mathias Bank (http://www.mathias-bank.de)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) 
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * 
 * Thanks to Hinnerk Ruemenapf - http://hinnerk.ruemenapf.de/ for bug reporting and fixing.
 */
jQuery.extend(
{
    /**
     * Returns get parameters.
     *
     * If the desired param does not exist, null will be returned
     *
     * @example value = $.getURLParam("paramName");
     */
    getURLParam: function (strParamName, str)
    {
        var strReturn = "";
        var strHref = window.location.href;
        var bFound = false;
        if (typeof str == 'string')
        {
            strHref = str;
        }
        var cmpstring = strParamName + "=";
        var cmplen = cmpstring.length;
        if (strHref.indexOf("?") > -1)
        {
            var strQueryString = strHref.substr(strHref.indexOf("?") + 1);
            var aQueryString = strQueryString.split("&");
            for (var iParam = 0; iParam < aQueryString.length; iParam++)
            {
                if (aQueryString[iParam].substr(0, cmplen) == cmpstring)
                {
                    var aParam = aQueryString[iParam].split("=");
                    strReturn = aParam[1];
                    bFound = true;
                    break;
                }
            }
        }
        if (bFound == false) return null;
        return strReturn;
    }
});