/**
 LazyLoad makes it easy and painless to lazily load one or more external
 JavaScript or CSS files on demand either during or after the rendering of a web
 page.
 
 Supported browsers include Firefox 2+, IE6+, Safari 3+ (including Mobile
 Safari), Google Chrome, and Opera 9+. Other browsers may or may not work and
 are not officially supported.
 
 Visit https://github.com/rgrove/lazyload/ for more info.
 
 Copyright (c) 2011 Ryan Grove <ryan@wonko.com>
 All rights reserved.
 
 Permission is hereby granted, free of charge, to any person obtaining a copy of
 this software and associated documentation files (the 'Software'), to deal in
 the Software without restriction, including without limitation the rights to
 use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 the Software, and to permit persons to whom the Software is furnished to do so,
 subject to the following conditions:
 
 The above copyright notice and this permission notice shall be included in all
 copies or substantial portions of the Software.
 
 THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 
 @module lazyload
 @class LazyLoad
 @static
 @version 2.0.3 (git)
 */

window.head_conf = {};
window.head_conf.head = 'xload';

/**
 Head JS The only script in your <HEAD>
 Copyright Tero Piirainen (tipiirai)
 License MIT / http://bit.ly/mit-license
 Version 0.96
 
 http://headjs.com
 */
(function (doc) {

    var head = doc.documentElement,
            isHeadReady,
            isDomReady,
            domWaiters = [],
            queue = [], // waiters for the "head ready" event
            handlers = {}, // user functions waiting for events
            scripts = {}, // loadable scripts in different states
            css = {}, // loadable scripts in different states

            loadedScripts = {},
            loadedCss = [],
            isAsync = doc.createElement("script").async === true || "MozAppearance" in doc.documentElement.style || window.opera;


    var Browser = (function ()
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





    /*** public API ***/
    var head_var = window.head_conf && head_conf.head || "head",
            api = window[head_var] = (window[head_var] || function () {
        api.ready.apply(null, arguments);
    });

    // states
    var PRELOADED = 1,
            PRELOADING = 2,
            LOADING = 3,
            LOADED = 4;


    // Method 1: simply load and let browser take care of ordering
    if (isAsync) {
        api.css = function () {
            var args = arguments, fn = args[args.length - 1], els = {};
            if (!isFunc(fn)) {
                fn = null;
            }

            each(args, function (el, i) {
                if (el != fn) {
                    el = getCss(el);
                    els[el.name] = el;

                    load(el, null);
                }
            });



        };

        api.js = function () {

            var args = arguments,
                    fn = args[args.length - 1],
                    els = {};

            if (!isFunc(fn)) {
                fn = null;
            }

           // console.log('api.js Method 1');

            // If urls is a string, wrap it in an array. Otherwise assume it's an
            // array and create a copy of it so modifications won't be made to the
            // original.
            // args = typeof args === 'string' ? [args] : args.concat();

            //if ( fn )
            //{
            args[0].push(fn);
            args = args[0];
            //}

            each(args, function (el, i) {
                if (el != fn) {

                //    console.log([el]);
                    el = getScript(el);
                 //   console.log([el]);


                    if (!isLoaded(loadedScripts, el.name))
                    {
                        els[el.name] = el;
                        load(el, fn && i == args.length - 2 ? function () {
                            if (allLoaded(els)) {
                                one(fn);
                            }

                        } : null);
                    }
                    else if (isLoaded(loadedScripts, el.name) && fn && i == args.length - 2)
                    {
                        fn();
                    }
                }
            });

            return api;
        };


        // Method 2: preload with text/cache hack
    } else {


        api.js = function () {

            var args = arguments,
                    fn = args[args.length - 1],
                    els = {};

            if (!isFunc(fn)) {
                fn = null;
            }

       //     console.log('api.js Method 2');

            // If urls is a string, wrap it in an array. Otherwise assume it's an
            // array and create a copy of it so modifications won't be made to the
            // original.
            // args = typeof args === 'string' ? [args] : args.concat();

            //if ( fn )
            //{
            args[0].push(fn);
            args = args[0];
            //}

            each(args, function (el, i) {


                if (el != fn) {
                    el = getScript(el);
                    if (!isLoaded(loadedScripts, el.name))
                    {
                        els[el.name] = el;
                        load(el, fn && i == args.length - 2 ? function () {
                            if (allLoaded(els)) {
                                one(fn);
                            }

                        } : null);
                    }
                    else if (isLoaded(loadedScripts, el.name) && i == args.length - 2)
                    {
                        if (fn && typeof fn === 'function') {
                            fn();
                        }
                    }
                }
            });

            return api;
        };
    }

    api.ready = function (key, fn) {

        // DOM ready check: head.ready(document, function() { });
        if (key == doc) {
            if (isDomReady) {
                one(fn);
            }
            else {
                domWaiters.push(fn);
            }
            return api;
        }

        // shift arguments
        if (isFunc(key)) {
            fn = key;
            key = "ALL";
        }

        // make sure arguments are sane
        if (typeof key != 'string' || !isFunc(fn)) {
            return api;
        }

        var script = scripts[key];

        // script already loaded --> execute and return
        if (script && script.state == LOADED || key == 'ALL' && allLoaded() && isDomReady) {
            one(fn);
            return api;
        }

        var arr = handlers[key];
        if (!arr) {
            arr = handlers[key] = [fn];
        }
        else {
            arr.push(fn);
        }
        return api;
    };


    // perform this when DOM is ready
    api.ready(doc, function () {

        if (allLoaded()) {
            each(handlers.ALL, function (fn) {
                one(fn);
            });
        }

        if (api.feature) {
            api.feature("domloaded", true);
        }
    });


    /*** private functions ***/


    // call function once
    function one (fn) {
        if (fn._done) {
            return;
        }
        fn();
        fn._done = 1;
    }


    function toLabel (url) {
        if (typeof url === 'string') {

            var els = url.split("/"),
                    name = els[els.length - 1],
                    i = name.indexOf("?");
            name = name.replace(/([^a-z0-9_])/ig, '_'); // clean name
            return i != -1 ? name.substring(0, i) : name;
        }
        return '';
    }


    function getScript (url) {

        var script;

        //console.log(url);

        if (typeof url === 'object') {
            script = {
                name: toLabel(url.url),
                type: (url.hasOwnProperty('type') ? url.type : 'javascript'),
                src: url.url.toString(),
                rel: url.rel
            };
        } else {
            script = {
                name: toLabel(url.toString()),
                type: 'javascript',
                src: url.toString()
            };
        }

        var existing = scripts[script.name];

        if (typeof scripts[script.name] != 'undefined' && existing.url === script.src) {
            return existing;
        }

        scripts[script.name] = script;

        return script;
    }


    function each (arr, fn) {
        if (!arr) {
            return;
        }

        // arguments special type
        if (typeof arr == 'object') {
            arr = [].slice.call(arr);
        }

        // do the job
        for (var i = 0; i < arr.length; i++) {
            fn.call(arr, arr[i], i);
        }
    }

    function isFunc (el) {
        return Object.prototype.toString.call(el) == '[object Function]';
    }

    function isLoaded (els, name)
    {
        return (typeof loadedScripts[name] != 'undefined' && loadedScripts[name] == true ? true : false);
    }

    function allLoaded (els) {

        els = els || scripts;

        var loaded = false;

        for (var name in els) {
            if (els.hasOwnProperty(name) && els[name].state != LOADED) {
                return false;
            }
            loaded = true;
            loadedScripts[name] = true;
        }

        return loaded;
    }


    function onPreload (script) {
        script.state = PRELOADED;

        each(script.onpreload, function (el) {
            el.call();
        });
    }

    function preload (script, callback) {

        if (script.state === undefined) {

            script.state = PRELOADING;
            script.onpreload = [];

            scriptTag({
                src: script.url,
                rel: script.rel || null,
                type: 'cache'
            }, function () {
                onPreload(script);
            });
        }
    }

    function load (script, callback) {

        if (script.state == LOADED) {
            return callback && callback();
        }

        if (script.state == LOADING) {
            return api.ready(script.name, callback);
        }

        if (script.state == PRELOADING) {
            return script.onpreload.push(function () {
                load(script, callback);
            });
        }

        script.state = LOADING;

        scriptTag(script, function () {

            script.state = LOADED;

            if (callback) {
                callback();
            }

            // handlers for this script
            each(handlers[script.name], function (fn) {
                one(fn);
            });

            // everything ready
            if (allLoaded() && isDomReady) {
                each(handlers.ALL, function (fn) {
                    one(fn);
                });
            }
        });
    }


    function scriptTag (script, callback) {
        var s;
        if (typeof script.type == 'string' && script.type != 'css')
        {
            s = doc.createElement('script');
            s.src = (typeof script.src == 'string' ? script.src : script);
        }
        else
        {
            s = doc.createElement('link');
            s.href = (typeof script.src == 'string' ? script.src : script);
        }

        s.type = 'text/' + (typeof script.type == 'string' ? script.type : 'javascript');


        // 
        if (typeof script.rel == 'string' && script.hasOwnProperty('rel'))
        {
            s.setAttribute('rel', script.rel);
        }


        s.async = false;
        s.onreadystatechange = s.onload = function () {

            var state = s.readyState;
            if (!callback.done && (!state || /loaded|complete/.test(state))) {
                loadedScripts[script.name] = true;
                callback.done = true;
                callback();
            }
        };

        // use body if available. more safe in IE
        // (doc.body || head).appendChild(s);

        doc.getElementsByTagName('head')[0].appendChild(s);

    }

    /*
     The much desired DOM ready check
     Thanks to jQuery and http://javascript.nwbox.com/IEContentLoaded/
     */

    function fireReady () {
        if (!isDomReady) {
            isDomReady = true;
            each(domWaiters, function (fn) {
                one(fn);
            });
        }
    }

    // W3C
    if (window.addEventListener) {
        doc.addEventListener("DOMContentLoaded", fireReady, false);

        // fallback. this is always called
        window.addEventListener("load", fireReady, false);

        // IE
    } else if (window.attachEvent) {

        // for iframes
        doc.attachEvent("onreadystatechange", function () {
            if (doc.readyState === "complete") {
                fireReady();
            }
        });


        // avoid frames with different domains issue
        var frameElement = 1;

        try {
            frameElement = window.frameElement;

        } catch (e) {
        }


        if (!frameElement && head.doScroll) {

            (function () {
                try {
                    head.doScroll("left");
                    fireReady();

                } catch (e) {
                    setTimeout(arguments.callee, 1);
                    return;
                }
            })();
        }

        // fallback
        window.attachEvent("onload", fireReady);
    }


    // enable document.readyState for Firefox <= 3.5
    if (!doc.readyState && doc.addEventListener) {
        doc.readyState = "loading";
        doc.addEventListener("DOMContentLoaded", handler = function () {
            doc.removeEventListener("DOMContentLoaded", handler, false);
            doc.readyState = "complete";
        }, false);
    }

    /*
     We wait for 300 ms before script loading starts. for some reason this is needed
     to make sure scripts are cached. Not sure why this happens yet. A case study:
     
     https://github.com/headjs/headjs/issues/closed#issue/83
     */
    setTimeout(function () {
        isHeadReady = true;
        each(queue, function (fn) {
            fn();
        });

    }, 300);

})(document);



var Loader = (function (doc) {
    // User agent and feature test information.
    var env,
            // Reference to the <head> element (populated lazily).
            head,
            // Requests currently in progress, if any.
            pending = {},
            // Number of times we've polled to check whether a pending stylesheet has
            // finished loading. If this gets too high, we're probably stalled.
            pollCount = 0,
            // Queued requests.
            queue = {
                css: []
            },
    loadedCss = [];

    /**
     Populates the <code>env</code> variable with user agent and feature test
     information.
     
     @method getEnv
     @private
     */
    function getEnv () {
        var ua = navigator.userAgent;

        env = {
            // True if this browser supports disabling async mode on dynamically
            // created script nodes. See
            // http://wiki.whatwg.org/wiki/Dynamic_Script_Execution_Order
            async: doc.createElement('script').async === true
        };

        (env.webkit = /AppleWebKit\//.test(ua))
                || (env.ie = /MSIE/.test(ua))
                || (env.opera = /Opera/.test(ua))
                || (env.gecko = /Gecko\//.test(ua))
                || (env.unknown = true);
    }

    /**
     Creates and returns an HTML element with the specified name and attributes.
     
     @method createNode
     @param {String} name element name
     @param {Object} attrs name/value mapping of element attributes
     @return {HTMLElement}
     @private
     */
    function createNode (name, attrs) {
        var node = doc.createElement(name), attr;

        for (attr in attrs)
        {
            if (attrs.hasOwnProperty(attr))
            {
                if (attr === 'rel' && attrs[attr] == null) {
                    continue;
                }

                node.setAttribute(attr, attrs[attr]);
            }
        }

        return node;
    }

    function loadCss (type, urls)
    {
        var _finish = function (isLastCall) {
            finish(type, isLastCall);
        }, head, isCSS = true,
                nodes = [], async = false,
                i, len, node, p, pendingUrls, url;

        env || getEnv();
        urls = typeof urls === 'string' ? [urls] : urls.concat();

        // If a previous load request of this type is currently in progress, we'll
        // wait our turn. Otherwise, grab the next item in the queue.
        if (pending['css'] || !queue['css'].length) {
            return false;
        }

        if (queue['css'].length > 0)
        {
            p = pending['css'] = queue['css'].shift();
        }


        head || (head = doc.head || doc.getElementsByTagName('head')[0]);


        pendingUrls = p.urls;

        var urlObj;
        var lastCall = false;
        for (i = 0, len = pendingUrls.length; i < len; ++i)
        {
            urlObj = pendingUrls[i];
            var hash = urlObj.url.replace(/([^a-zA-Z0-9_])/g, '-');

            // skip loaded script/css files
            if (typeof loadedCss[hash] != 'undefined') {
                pendingUrls[i] = null;
                continue;
            }


            if (isCSS) {
                node = env.gecko ? createNode('style') : createNode('link', {
                    href: urlObj.url,
                    rel: 'stylesheet'
                });
            }

            node.className = 'lazyload pending';
            node.setAttribute('charset', 'utf-8');


            if (i == (len - 1))
            {
                lastCall = true;
            }

            node.lastCall = lastCall;

            if (isCSS && (env.gecko || env.webkit)) {
                // Gecko and WebKit don't support the onload event on link nodes.
                if (env.webkit) {
                    // In WebKit, we can poll for changes to document.styleSheets to
                    // figure out when stylesheets have loaded.
                    p.urls[i] = node.href; // resolve relative URLs (or polling won't work)
                    pollWebKit();
                    loadedCss[hash] = urlObj.url;
                } else {
                    // In Gecko, we can import the requested URL into a <style> node and
                    // poll for the existence of node.sheet.cssRules. Props to Zach
                    // Leatherman for calling my attention to this technique.
                    node.innerHTML = '@import "' + urlObj.url + '";';
                    pollGecko(node);
                    loadedCss[hash] = urlObj.url;
                }
            }
            else {

                node.onload = function () {
                    $(this).removeClass('pending');
                    loadedCss[hash] = urlObj.url;
                };

                node.onerror = function () {
                    $(this).addClass('err');
                    Debug.error('could not load: ' + urlObj.url);

                };


                // one browser needs special treatment - have a guess
                node.onreadystatechange = (function () {
                    var _scriptNode = node;

                    return function () {
                        if (_scriptNode.readyState === 'loaded' || _scriptNode.readyState === 'complete') {
                            _scriptNode.onreadystatechange = null;
                            _scriptNode.onload();
                        }
                    }
                })();
            }

            nodes.push(node);
        }

        for (i = 0, len = nodes.length; i < len; ++i) {
            head.appendChild(nodes[i]);
        }
    }
    /**
     Called when the current pending resource of the specified type has finished
     loading. Executes the associated callback (if any) and loads the next
     resource in the queue.
     
     @method finish
     @param {String} type resource type ('css' or 'js')
     @param {Bool} isLastCall 
     @private
     */
    function finish (type, isLastCall) {
        var p = pending[type],
                callback,
                urls;

        if (p) {

            callback = p.callback;
            urls = p.urls;

            urls.shift();
            pollCount = 0;

            // If this is the last of the pending URLs, execute the callback and
            // start the next request in the queue (if any).
            if (!urls.length) {


                if (isLastCall && type == 'js')
                {
                    Loader.lasCallDone = true;

                    Debug.info('Call last JS load');
                }
                else if (isLastCall && type == 'css')
                {
                    Loader.lasCallDone = true;

                    Debug.info('Call last CSS load');
                }


                pending[type] = null;
                if (queue[type].length)
                {
                    load(type);
                }

                if (typeof callback == 'function') {

                    Debug.info('Execute Callback function in ' + type + ' ' + callback);
                    callback && callback.call(p.context, p.obj);
                }
            }
        }
    }

    /**
     Begins polling to determine when the specified stylesheet has finished loading
     in Gecko. Polling stops when all pending stylesheets have loaded or after 10
     seconds (to prevent stalls).
     
     Thanks to Zach Leatherman for calling my attention to the @import-based
     cross-domain technique used here, and to Oleg Slobodskoi for an earlier
     same-domain implementation. See Zach's blog for more details:
     http://www.zachleat.com/web/2010/07/29/load-css-dynamically/
     
     @method pollGecko
     @param {HTMLElement} node Style node to poll.
     @private
     */
    function pollGecko (node) {
        var hasRules;

        try {
            // We don't really need to store this value or ever refer to it again, but
            // if we don't store it, Closure Compiler assumes the code is useless and
            // removes it.
            hasRules = !!node.sheet.cssRules;
        } catch (ex) {
            // An exception means the stylesheet is still loading.
            pollCount += 1;

            if (pollCount < 500) {
                setTimeout(function () {
                    pollGecko(node);
                }, 80);
            } else {
                // We've been polling for 10 seconds and nothing's happened. Stop
                // polling and finish the pending requests to avoid blocking further
                // requests.
                hasRules && finish('css');
            }

            return;
        }
    }

    /**
     Begins polling to determine when pending stylesheets have finished loading
     in WebKit. Polling stops when all pending stylesheets have loaded or after 10
     seconds (to prevent stalls).
     
     @method pollWebKit
     @private
     */
    function pollWebKit () {
        var css = pending.css, i;

        if (css) {
            i = styleSheets.length;

            // Look for a stylesheet matching the pending URL.
            while (--i >= 0) {
                if (styleSheets[i].href === css.urls[0]) {
                    finish('css');
                    break;
                }
            }

            pollCount += 1;

            if (css) {
                if (pollCount < 500) {
                    setTimeout(pollWebKit, 50);
                }
                else
                {
                    finish('css');
                }
            }
        }
    }


    return {
        _loads: [], // cache for require arguments

        loaded: {},
        loadedCss: {},
        internalLoads: {},
        _runCssCache: [],
        _runJsCache: [],
        _resources: {},
        doBase: false,
        callbackFunction: null,
        to: false,
        lasCallDone: false,
        init: function (call)
        {
            if (typeof call == 'function') {
                call();
            }
            Debug.start();
        },
        extractScriptsFromArgs: function (_arguments, iscss)
        {
            var loads = [];
            var callbackFunction = null;




            for (var i = 0; i < _arguments.length; ++i)
            {

                if (typeof _arguments[i] === 'string')
                {

                    var substr3 = _arguments[i].substr(_arguments[i].length - 3), substr4 = _arguments[i].substr(_arguments[i].length - 4);

                    // add js extension
                    if ((substr3 != '.js' && substr4 != '.css' && substr4 != '.php' && !_arguments[i].match(/\.php/)) || (substr3 != '.js' && substr4 != '.css' && substr4 != '.php'))
                    {
                        _arguments[i] += '.js';
                    }

                    var sSrc = this.doBase ? Tools.getAbsolutePath(Base.basePath || "", _arguments[i]) : _arguments[i];

                    if (typeof iscss != 'boolean' && substr4 == '.css')
                    {
                        iscss = true;
                    }
                    else if (typeof iscss == 'boolean' && iscss && substr4 != '.css')
                    {
                        // add css extension
                        _arguments[i] += '.css';
                    }



                    if (typeof sSrc == 'string' && sSrc.length > 0)
                    {
                        var _d = {
                            url: sSrc,
                            rel: (typeof iscss == 'boolean' && iscss == true ? 'stylesheet' : null)
                        };
                        if (typeof iscss == 'boolean' && iscss == true)
                        {
                            _d.type = 'css';
                        }
                        loads.push(_d);
                    }
                }
                else if (typeof _arguments[i] === 'function')
                {
                    callbackFunction = _arguments[i];
                }

                else if (typeof _arguments[i] === 'object')
                {
                    for (var k in _arguments[i])
                    {
                        var data = _arguments[i][k];
                        if (typeof data == 'string')
                        {


                            var substr3 = data.substr(data.length - 3), substr4 = data.substr(data.length - 4);


                            // add js extension
                            if ((substr3 != '.js' && substr4 != '.css' && substr4 != '.php' && !data.match(/\.php/)) || (substr3 != '.js' && substr4 != '.css' && substr4 != '.php'))
                            {
                                data += '.js';
                            }

                            var sSrc = this.doBase ? Tools.getAbsolutePath(Base.basePath || "", data) : data;


                            if (typeof iscss != 'boolean' && substr4 == '.css')
                            {
                                iscss = true;
                            }
                            else if (typeof iscss == 'boolean' && iscss && substr4 != '.css')
                            {
                                // add css extension
                                _arguments[i] += '.css';
                            }

                            if (typeof sSrc == 'string' && sSrc.length > 0)
                            {
                                var _d = {
                                    url: sSrc,
                                    rel: (typeof iscss == 'boolean' && iscss == true ? 'stylesheet' : null)
                                };

                                if (typeof iscss == 'boolean' && iscss == true)
                                {
                                    _d.type = 'css';
                                }
                                loads.push(_d);
                            }

                        }
                        else if (!callbackFunction && typeof data == 'function')
                        {
                            callbackFunction = data;
                        }
                    }
                }
            }

            return [loads, callbackFunction];
        },
        require: function ()
        {
            this.lasCallDone = false;
            this.callbackFunction = null;
            // console.log([arguments[0]]);
            var loads = this.extractScriptsFromArgs(arguments);
            //this.extractScriptsFromArgs(arguments);

            xload.js(loads[0], loads[1]);


            if (this.lasCallDone)
            {
                this.callbackFunction = null;
                return this;
            }
        },
        /**
         Requests the specified CSS URL or URLs and executes the specified
         callback (if any) when they have finished loading. If an array of URLs is
         specified, the stylesheets will be loaded in parallel and the callback
         will be executed after all stylesheets have finished loading.
         
         @method css
         @param {String|Array} urls CSS URL or array of CSS URLs to load
         @param {Function} callback (optional) callback function to execute when
         the specified stylesheets are loaded
         @param {Object} obj (optional) object to pass to the callback function
         @param {Object} context (optional) if provided, the callback function
         will be executed in this object's context
         @static
         */
        loadCss: function ()
        {
            var loads = this.extractScriptsFromArgs(arguments, 'stylesheet');
            xload.js(loads[0], null);

        },
        then: function ()
        {

            var loads;
            loads = this.extractScriptsFromArgs(arguments);

            xload.js(loads[0], loads[1]);

            if (this.lasCallDone)
            {
                this.callbackFunction = null;
                return this;
            }
        },
        run: function (callback)
        {
            if (this.lasCallDone && typeof callback === 'function')
            {
                callback();
                return this;
            }
        },
        wait: function (milliseconds)
        {
            var start = new Date().getTime();
            while ((new Date().getTime() - start) < milliseconds) {
            }
            if (typeof Debug != 'undefined')
                Debug.info('Wait for ' + milliseconds + 'ms');
            return this;
        },
        setResourceMap: function (c)
        {
            if (!c)
                return;
            for (var b in c) {
                if (!c[b].name) {
                    c[b].name = b;
                }
                this._resources[b] = c[b];
            }
            this._prepareResources();

            return this;
        },
        _prepareResources: function ()
        {
            var jsregex = /\.js$/;
            var phpregex = /\.php/;

            for (var b in this._resources) {
                var load = this._resources[b];

                if (load.type == "js" && !jsregex.test(load.src) && !phpregex.test(load.src))
                {
                    load.src += '.js';
                    this._resources[b].url = load.src;
                }

                if (load.type == "js")
                {
                    this._runJsCache.push({
                        url: load.src,
                        rel: (typeof load.rel != 'undefined' ? load.rel : null)
                    });
                }
                else if (load.type == "css")
                {
                    this._runCssCache.push({
                        url: load.src
                    });
                }
            }

        },
        setupResources: function (jsCallback)
        {

            this.lasCallDone = false;
            this.callbackFunction = null;
            var max = this._runCssCache.length;
            if (max)
            {
                xload.js(this._runCssCache, null);
                this._runCssCache = [];
            }


            this.lasCallDone = false;
            max = this._runJsCache.length;
            if (max)
            {
                xload.js(this._runJsCache, jsCallback);
                this._runJsCache = [];

            }


            this.callbackFunction = null;

            return this;
        }
    }

})(document);


