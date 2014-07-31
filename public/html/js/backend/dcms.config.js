
jQuery.fx.interval = 12;
jQuery.event.props.push("dataTransfer");
if (typeof $.gsap != 'undefined' ) { $.gsap.enabled(true); }






/*
 jQuery.noConflict();
 
 if ( typeof $ === 'undefined' ) { var $ = jQuery; }
 
 
 if (!$.parseHTML)
 {
 $.parseHTML = function(data) {
 if (!data || typeof data !== "string") {
 return null;
 }
 return $(data);
 };
 
 }
  
 
 if (!$.on)
 {
 $.fn.on = function(what, event) {
 return $(this).bind(what, event);
 };
 }*/

Array.prototype.exists = function (needle, strictMode) {
    var i = this.length;
    while (i--) {
        if (!strictMode && this[i] == needle) {
            return true;
        }
        if (strictMode && this[i] === needle) {
            return true;
        }
    }
    return false;
}

if (typeof jQuery != 'undefined' && typeof $.browser == 'undefined')
{



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
        browser = {};

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
}



/*
 * jQuery Framerate 1.0.1
 *
 ** IMPORTANT: THIS HAS ONLY BEEN TESTED WITH JQUERY 1.4.2.  SINCE THIS PLUGIN MODIFIES PARTS OF THE
 ** CORE CODE, IT MAY NOT WORK CORRECTLY IN OTHER VERSIONS. LET ME KNOW IF YOU FIND ANOTHER
 ** VERSION IT DOESN'T WORK IN AND I'LL SEE IF I CAN MODIFY TO WORK WITH IT
 *
 *
 * Summary:
 * Override some of the core code of JQuery to allow for custom framerates
 * The default framerate is very high (@77fps) and can therefore lead to choppy motion on
 * complicated animations on slower machines
 *
 * Usage:
 * takes two parameters, one for desired framerate (default of 30) and other to display
 * framerate in console while animation is running.
 *
 * example basic usage: $().framerate();
 * example advanced usage: $().framerate({framerate: 20, logframes: true});
 *
 *
 *
 * TERMS OF USE - jQuery Framerate
 *
 * Copyright © 2010 James Snodgrass: jim@skookum.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * Redistributions of source code must retain the above copyright notice, this list of 
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list 
 * of conditions and the following disclaimer in the documentation and/or other materials 
 * provided with the distribution.
 * 
 * Neither the name of the author nor the names of contributors may be used to endorse 
 * or promote products derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED 
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *
 * Changes:
 * 1.0.1: July 30,2010 - fixed global variable leaks
 *
 */

jQuery.extend({
    framerate: function (options) {


        var settings = jQuery.extend({
            framerate: 30,
            logframes: false
        }, options);

        var frameInterval = Math.floor(1000 / settings.framerate);

        jQuery.extend(jQuery.fx.prototype, {
            // Start an animation from one number to another
            custom: function (from, to, unit) {
                this.startTime = new Date().getTime();
                this.start = from;
                this.end = to;
                this.unit = unit || this.unit || "px";
                this.now = this.start;
                this.pos = this.state = 0;

                var self = this;
                function t (gotoEnd) {
                    return self.step(gotoEnd);
                }

                t.elem = this.elem;

                if (typeof (jQuery.timerId) == 'undefined')
                    jQuery.timerId = false;

                if (t() && jQuery.timers.push(t) && !jQuery.timerId) {
                    jQuery.timerId = setInterval(jQuery.fx.tick, frameInterval);
                }
            }
        });

        var lastTimeStamp = new Date().getTime();

        jQuery.extend(jQuery.fx, {
            tick: function () {

                if (settings.logframes) {
                    var now = new Date().getTime();
                   // console.log(Math.floor(1000 / (now - lastTimeStamp)));
                    lastTimeStamp = now;
                }

                var timers = jQuery.timers;
                for (var i = 0; i < timers.length; i++) {
                    if (!timers[i]()) {
                        timers.splice(i--, 1);
                    }
                }

                if (!timers.length) {
                    jQuery.fx.stop();
                }
            },
            stop: function () {
                clearInterval(jQuery.timerId);
                jQuery.timerId = null;
            }
        });
    }

});
















var Personal = {
    opts: {},
    init: function (options) {
        if (typeof options == 'object')
        {
            for (var x in options)
            {
                this.opts[x] = options[x];
            }
        }
    },
    reset: function ()
    {
        this.opts = {};
    },
    set: function (keyname, value)
    {
        this.opts[keyname] = value;
    },
    get: function (keyname, _default)
    {
        if (typeof this.opts[keyname] != 'undefined')
        {
            return this.opts[keyname];
        }
        else
        {
            if (typeof _default != 'undefined')
            {
                return _default;
            }
        }
    }
};

var Config = {
    cfg: {
        railColor: '#555',
        railOpacity: '0.2',
        railClass: 'GuiScrollRail',
        barClass: 'GuiScrollBar',
        wrapperClass: 'GuiScrollDiv',
        size: '7px',
        color: '#000',
        position: 'right',
        distance: '2px',
        loadingImgSmall: '',
        loadingImgLarge: '',
		fullscreenContainerId: 'fullscreenContainer',
    },
    jsDebug: true,
    init: function (options) {

        if (typeof options == 'object')
        {
            for (var x in options)
            {
                this.cfg[x] = options[x];
            }

            this.cfg['loadingImgSmall'] = this.cfg['SkinPath'] + 'img/loading.gif';
            this.cfg['loadingImgLarge'] = this.cfg['SkinPath'] + 'img/loading.gif';
        }

    },
    set: function (keyname, value)
    {

        if (typeof keyname == 'object') {
            for (var k in keyname) {
                if (typeof k == 'string') {
                    this.cfg[k] = keyname[k];
                }
            }
        }
        else {
            this.cfg[keyname] = value;
        }
    },
    get: function (keyname, _default)
    {
        if (typeof this.cfg[keyname] != 'undefined')
        {
            return this.cfg[keyname];
        }
        else
        {
            if (typeof _default != 'undefined')
            {
                return _default;
            }
            else
            {
                return false;
            }
        }
    }


};

