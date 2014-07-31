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
DCMS.define('Loader', DCMS);
window.head_conf = {};
window.head_conf.head = 'xload';

/**
Head JS The only script in your <HEAD>
Copyright Tero Piirainen (tipiirai)
License MIT / http://bit.ly/mit-license
Version 0.96

http://headjs.com
 */
(function(doc) {

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


    /*** public API ***/
    var head_var = window.head_conf && head_conf.head || "head",
    api = window[head_var] = (window[head_var] || function() {
        api.ready.apply(null, arguments);
    });

    // states
    var PRELOADED = 1,
    PRELOADING = 2,
    LOADING = 3,
    LOADED = 4;


    // Method 1: simply load and let browser take care of ordering
    if (isAsync) {
        api.css = function() { 
            var args = arguments, fn = args[args.length -1], els = {};
            if (!isFunc(fn)) {
                fn = null;
            }
            
            each(args, function(el, i) {
                if (el != fn) {
                    el = getCss(el);
                    els[el.name] = el;

                    load(el, null);
                }
            });
            
            
            
        };
        
        api.js = function() {

            var args = arguments,
            fn = args[args.length -1],
            els = {};

            if (!isFunc(fn)) {
                fn = null;
            }
            // If urls is a string, wrap it in an array. Otherwise assume it's an
            // array and create a copy of it so modifications won't be made to the
            // original.
            // args = typeof args === 'string' ? [args] : args.concat();
            
            //if ( fn )
            //{
            args[0].push(fn);
            args = args[0];
            //}
            
            each(args, function(el, i) {

                if (el != fn) {
                    el = getScript(el);
                    

                    if (!isLoaded(loadedScripts, el.name))
                    {
                        els[el.name] = el;
                        load(el, fn && i == args.length -2 ? function() {
                            if (allLoaded(els)) {
                                one(fn);
                            }

                        } : null);
                    }
                    else if (isLoaded(loadedScripts, el.name) && fn && i == args.length -2)
                    {
                        fn();
                    }
                }
            });

            return api;
        };


    // Method 2: preload with text/cache hack
    } else {

        api.js = function() {

            var args = arguments,
            rest = [].slice.call(args, 1),
            next = rest[0];

            // wait for a while. immediate execution causes some browsers to ignore caching
            if (!isHeadReady) {
                queue.push(function() {
                    api.js.apply(null, args);
                });
                return api;
            }

            // multiple arguments
            if (next) {

                // load
                each(rest, function(el) {
                    if (!isFunc(el)) {
                        preload(getScript(el));
                    }
                });

                // execute
                load(getScript(args[0]), isFunc(next) ? next : function() {
                    api.js.apply(null, rest);
                });


            // single script
            } else {
                load(getScript(args[0]));
            }

            return api;
        };
    }

    api.ready = function(key, fn) {

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
    api.ready(doc, function() {

        if (allLoaded()) {
            each(handlers.ALL, function(fn) {
                one(fn);
            });
        }

        if (api.feature) {
            api.feature("domloaded", true);
        }
    });


    /*** private functions ***/
    
    
    // call function once
    function one(fn) {
        if (fn._done) {
            return;
        }
        fn();
        fn._done = 1;
    }


    function toLabel(url) {
        var els = url.split("/"),
        name = els[els.length -1],
        i = name.indexOf("?");
        name = name.replace(/([^a-z0-9_]*)/gi, '_'); // clean name
        return i != -1 ? name.substring(0, i) : name;
    }

    
    function getScript(url) {

        var script;

        if (typeof url == 'object') {
            
            
            script = {
                name: toLabel(url.url),
                type: (url.hasOwnProperty('type') ? url.type : 'javascript'),
                src: url.url,
                rel: url.rel
            };
            
            
        /*
            for (var key in url) {
                if (url[key]) {
                    
                    
                    if (typeof url[key].url != 'string')
                    {
                        Debug.warn('' + url[key]);
                        continue;
                    }
                    
                    script = {
                        name: key,
                        type: 'javascript',
                        url: url[key].url,
                        rel: url[key].rel
                    };
                }
            }
            */
        } else {
            script = {
                name: toLabel(url), 
                type: 'javascript',
                src: url
            };
        }

        var existing = scripts[script.name];
        if (existing && existing.url === script.src) {
            return existing;
        }

        scripts[script.name] = script;
        return script;
    }


    function each(arr, fn) {
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

    function isFunc(el) {
        return Object.prototype.toString.call(el) == '[object Function]';
    }
    
    function isLoaded(els, name)
    {
        return (typeof loadedScripts[name] != 'undefined' && loadedScripts[name] == true ? true : false);
    }

    function allLoaded(els) {

        els = els || scripts;

        var loaded;
        
        for (var name in els) {
            if (els.hasOwnProperty(name) && els[name].state != LOADED) {
                return false;
            }
            loaded = true;
            loadedScripts[name] = true;
        }
        
        return loaded;
    }


    function onPreload(script) {
        script.state = PRELOADED;

        each(script.onpreload, function(el) {
            el.call();
        });
    }

    function preload(script, callback) {

        if (script.state === undefined) {

            script.state = PRELOADING;
            script.onpreload = [];

            scriptTag({
                src: script.url, 
                rel: script.rel || null, 
                type: 'cache'
            }, function() {
                onPreload(script);
            });
        }
    }

    function load(script, callback) {

        if (script.state == LOADED) {
            return callback && callback();
        }

        if (script.state == LOADING) {
            return api.ready(script.name, callback);
        }

        if (script.state == PRELOADING) {
            return script.onpreload.push(function() {
                load(script, callback);
            });
        }

        script.state = LOADING;

        scriptTag(script, function() {

            script.state = LOADED;

            if (callback) {
                callback();
            }

            // handlers for this script
            each(handlers[script.name], function(fn) {
                one(fn);
            });

            // everything ready
            if (allLoaded() && isDomReady) {
                each(handlers.ALL, function(fn) {
                    one(fn);
                });
            }
        });
    }


    function scriptTag(script, callback) {
        var s;
        if ( typeof script.type == 'string' && script.type != 'css' )
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

        s.onreadystatechange = s.onload = function() {

            var state = s.readyState;

            if (!callback.done && (!state || /loaded|complete/.test(state))) {
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

    function fireReady() {
        if (!isDomReady) {
            isDomReady = true;
            each(domWaiters, function(fn) {
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
        doc.attachEvent("onreadystatechange", function() {
            if (doc.readyState === "complete" ) {
                fireReady();
            }
        });


        // avoid frames with different domains issue
        var frameElement = 1;

        try {
            frameElement = window.frameElement;

        } catch(e) {}


        if (!frameElement && head.doScroll) {

            (function() {
                try {
                    head.doScroll("left");
                    fireReady();

                } catch(e) {
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
    setTimeout(function() {
        isHeadReady = true;
        each(queue, function(fn) {
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
    function getEnv() {
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
    function createNode(name, attrs) {
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
        nodes   = [], async = false,
        i, len, node, p, pendingUrls, url;

        env || getEnv();
        urls = typeof urls === 'string' ? [urls] : urls.concat();
        
        // If a previous load request of this type is currently in progress, we'll
        // wait our turn. Otherwise, grab the next item in the queue.
        if (pending['css'] || !queue['css'].length ) {
            return false;
        }
 
        if ( queue['css'].length > 0 )
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
            if (typeof loadedCss[hash] != 'undefined' ) { 
                pendingUrls[i] = null;
                continue;
            }
            
            
            if (isCSS) {
                node = env.gecko ? createNode('style') : createNode('link', {
                    href: urlObj.url,
                    rel : 'stylesheet'
                });
            }

            node.className = 'lazyload pending';
            node.setAttribute('charset', 'utf-8');
            

            if (i == (len - 1) )
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
                
                node.onload = function(){
                    $(this).removeClass('pending');
                    loadedCss[hash] = urlObj.url;
                };
                
                node.onerror = function(){
                    $(this).addClass('err');
                    Debug.error('could not load: '+ urlObj.url);
                //window.console.error('could not load: '+ urlObj.url);
                }
                
                
                // one browser needs special treatment - have a guess
                node.onreadystatechange = (function() {
                    var _scriptNode = node;
                    
                    return function() {
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
      @private
     */
    function finish(type, isLastCall) {
        var p = pending[type],
        callback,
        urls;

        if (p) {
            
            callback = p.callback;
            urls     = p.urls;

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
                    
                    Debug.info('Execute Callback function in '+ type + ' '+callback);
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
    function pollGecko(node) {
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
    function pollWebKit() {
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
        
        
        init: function(call)
        {
            if (typeof call == 'function') {
                call();
            }
            Debug.start();
        },
        

        extractScriptsFromArgs: function(_arguments, iscss)
        {
            var loads = [];
            var callbackFunction = null;
            
            for(var i=0; i<_arguments.length; ++i)
            {
                if (typeof _arguments[i] === 'string') 
                {
                    
                    
                    if ( 
                    
                        (
                            _arguments[i].substr( _arguments[i].length - 3) != '.js' && 
                            _arguments[i].substr( _arguments[i].length - 4) != '.css' && 
                            !_arguments[i].match(/\.php/)
                            ) 
                        || 
                        (
                            _arguments[i].substr( _arguments[i].length - 3) != '.js' && 
                            _arguments[i].substr( _arguments[i].length - 4) != '.css'
                            )
                
            
                        )
                        {
                            
                        
                        _arguments[i] += '.js';
                    }
                    
                    var sSrc = this.doBase ? Tools.getAbsolutePath(Base.basePath || "", _arguments[i]) : _arguments[i];
                    
                    
                    if (typeof iscss != 'boolean' && _arguments[i].substr( _arguments[i].length - 4) == '.css')
                    {
                        iscss = true;
                    }

                    
                    
                    if ( typeof sSrc == 'string' && sSrc.length > 0 )
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
                    for(var k in _arguments[i])
                    {
                        var data = _arguments[i][k];
                        if ( 
                    
                            (
                                data.substr( data.length - 3) != '.js' && 
                                data.substr( data.length - 4) != '.css' && 
                                !data.match(/\.php/)
                                ) 
                            || 
                            (
                                data.substr( data.length - 3) != '.js' && 
                                data.substr( data.length - 4) != '.css'
                                )
                
            
                            )
                            {
                            
                        
                            data += '.js';
                        }
                    
                        var sSrc = this.doBase ? Tools.getAbsolutePath(Base.basePath || "", data) : data;
                    
                    
                        if (typeof iscss != 'boolean' && data.substr( data.length - 4) == '.css')
                        {
                            iscss = true;
                        }
                        if ( typeof sSrc == 'string' && sSrc.length > 0 )
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
                }
            }
            
            return [loads, callbackFunction];
        },
        
        
        require: function () 
        {
            this.lasCallDone = false;
            this.callbackFunction = null;
            var loads = this.extractScriptsFromArgs( arguments);
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
        then: function()
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
        
        run: function(callback)
        {
            if (this.lasCallDone && typeof callback === 'function')
            {
                callback();                
                return this;
            }
        },
    
        wait: function(milliseconds)
        {        
            var start = new Date().getTime();
            while ((new Date().getTime() - start) < milliseconds){}        
            if (typeof Debug != 'undefined') Debug.info('Wait for '+ milliseconds + 'ms' );
            return this;
        },
        setResourceMap: function(c)
        {
            if (!c) return;
            for (var b in c) {
                if (!c[b].name) {
                    c[b].name = b;
                }
                this._resources[b] = c[b];
            }
            this._prepareResources();
        
            return this;
        },

        _prepareResources: function()
        {        
            var jsregex = /\.js$/;
            var phpregex = /\.php/;
        
            for (var b in this._resources) {
                var load = this._resources[b];
            
                if (load.type == "js" && !jsregex.test( load.src ) && !phpregex.test( load.src )) 
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
        
        
        setupResources: function( jsCallback )
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







var Loader0 = (function (doc) {
    // -- Private Variables ------------------------------------------------------

    // User agent and feature test information.
    var env,

    // Reference to the <head> element (populated lazily).
    head,

    // Requests currently in progress, if any.
    pending = {
        
        
    },

    // Number of times we've polled to check whether a pending stylesheet has
    // finished loading. If this gets too high, we're probably stalled.
    pollCount = 0,

    // Queued requests.
    queue = {
        css: [], 
        js: []
    },
    
    loadedScripts = {},

    // Reference to the browser's list of stylesheets.
    styleSheets = doc.styleSheets,
    isLastCallsDone = false,    
    isLastCSSCallsDone = false;
    
    function utf8_encode(argString)
    {
        var string = (argString + ''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
        var utftext = "";
        var start, end;
        var stringl = 0;
        start = end = 0;
        stringl = string.length;
        for (var n = 0; n < stringl; n++)
        {
            var c1 = string.charCodeAt(n);
            var enc = null;
            if (c1 < 128)
            {
                end++;
            }
            else if (c1 > 127 && c1 < 2048)
            {
                enc = String.fromCharCode((c1 >> 6) | 192) + String.fromCharCode((c1 & 63) | 128);
            }
            else
            {
                enc = String.fromCharCode((c1 >> 12) | 224) + String.fromCharCode(((c1 >> 6) & 63) | 128) + String.fromCharCode((c1 & 63) | 128);
            }
            if (enc !== null)
            {
                if (end > start)
                {
                    utftext += string.substring(start, end);
                }
                utftext += enc;
                start = end = n + 1;
            }
        }
        if (end > start)
        {
            utftext += string.substring(start, string.length);
        }
        return utftext;
    }
    
    
    
    function getHash( str )
    {
        var xl;
        var rotateLeft = function (lValue, iShiftBits)
        {
            return (lValue << iShiftBits) | (lValue >>> (32 - iShiftBits));
        };
        var addUnsigned = function (lX, lY)
        {
            var lX4, lY4, lX8, lY8, lResult;
            lX8 = (lX & 0x80000000);
            lY8 = (lY & 0x80000000);
            lX4 = (lX & 0x40000000);
            lY4 = (lY & 0x40000000);
            lResult = (lX & 0x3FFFFFFF) + (lY & 0x3FFFFFFF);
            if (lX4 & lY4)
            {
                return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
            }
            if (lX4 | lY4)
            {
                if (lResult & 0x40000000)
                {
                    return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
                }
                else
                {
                    return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
                }
            }
            else
            {
                return (lResult ^ lX8 ^ lY8);
            }
        };
        var _F = function (x, y, z)
        {
            return (x & y) | ((~x) & z);
        };
        var _G = function (x, y, z)
        {
            return (x & z) | (y & (~z));
        };
        var _H = function (x, y, z)
        {
            return (x ^ y ^ z);
        };
        var _I = function (x, y, z)
        {
            return (y ^ (x | (~z)));
        };
        var _FF = function (a, b, c, d, x, s, ac)
        {
            a = addUnsigned(a, addUnsigned(addUnsigned(_F(b, c, d), x), ac));
            return addUnsigned(rotateLeft(a, s), b);
        };
        var _GG = function (a, b, c, d, x, s, ac)
        {
            a = addUnsigned(a, addUnsigned(addUnsigned(_G(b, c, d), x), ac));
            return addUnsigned(rotateLeft(a, s), b);
        };
        var _HH = function (a, b, c, d, x, s, ac)
        {
            a = addUnsigned(a, addUnsigned(addUnsigned(_H(b, c, d), x), ac));
            return addUnsigned(rotateLeft(a, s), b);
        };
        var _II = function (a, b, c, d, x, s, ac)
        {
            a = addUnsigned(a, addUnsigned(addUnsigned(_I(b, c, d), x), ac));
            return addUnsigned(rotateLeft(a, s), b);
        };
        var convertToWordArray = function (str)
        {
            var lWordCount;
            var lMessageLength = str.length;
            var lNumberOfWords_temp1 = lMessageLength + 8;
            var lNumberOfWords_temp2 = (lNumberOfWords_temp1 - (lNumberOfWords_temp1 % 64)) / 64;
            var lNumberOfWords = (lNumberOfWords_temp2 + 1) * 16;
            var lWordArray = new Array(lNumberOfWords - 1);
            var lBytePosition = 0;
            var lByteCount = 0;
            while (lByteCount < lMessageLength)
            {
                lWordCount = (lByteCount - (lByteCount % 4)) / 4;
                lBytePosition = (lByteCount % 4) * 8;
                lWordArray[lWordCount] = (lWordArray[lWordCount] | (str.charCodeAt(lByteCount) << lBytePosition));
                lByteCount++;
            }
            lWordCount = (lByteCount - (lByteCount % 4)) / 4;
            lBytePosition = (lByteCount % 4) * 8;
            lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80 << lBytePosition);
            lWordArray[lNumberOfWords - 2] = lMessageLength << 3;
            lWordArray[lNumberOfWords - 1] = lMessageLength >>> 29;
            return lWordArray;
        };
        var wordToHex = function (lValue)
        {
            var wordToHexValue = "",
            wordToHexValue_temp = "",
            lByte, lCount;
            for (lCount = 0; lCount <= 3; lCount++)
            {
                lByte = (lValue >>> (lCount * 8)) & 255;
                wordToHexValue_temp = "0" + lByte.toString(16);
                wordToHexValue = wordToHexValue + wordToHexValue_temp.substr(wordToHexValue_temp.length - 2, 2);
            }
            return wordToHexValue;
        };
        var x = [],
        k, AA, BB, CC, DD, a, b, c, d, S11 = 7,
        S12 = 12,
        S13 = 17,
        S14 = 22,
        S21 = 5,
        S22 = 9,
        S23 = 14,
        S24 = 20,
        S31 = 4,
        S32 = 11,
        S33 = 16,
        S34 = 23,
        S41 = 6,
        S42 = 10,
        S43 = 15,
        S44 = 21;
        str = utf8_encode(str);
        x = convertToWordArray(str);
        a = 0x67452301;
        b = 0xEFCDAB89;
        c = 0x98BADCFE;
        d = 0x10325476;
        xl = x.length;
        for (k = 0; k < xl; k += 16)
        {
            AA = a;
            BB = b;
            CC = c;
            DD = d;
            a = _FF(a, b, c, d, x[k + 0], S11, 0xD76AA478);
            d = _FF(d, a, b, c, x[k + 1], S12, 0xE8C7B756);
            c = _FF(c, d, a, b, x[k + 2], S13, 0x242070DB);
            b = _FF(b, c, d, a, x[k + 3], S14, 0xC1BDCEEE);
            a = _FF(a, b, c, d, x[k + 4], S11, 0xF57C0FAF);
            d = _FF(d, a, b, c, x[k + 5], S12, 0x4787C62A);
            c = _FF(c, d, a, b, x[k + 6], S13, 0xA8304613);
            b = _FF(b, c, d, a, x[k + 7], S14, 0xFD469501);
            a = _FF(a, b, c, d, x[k + 8], S11, 0x698098D8);
            d = _FF(d, a, b, c, x[k + 9], S12, 0x8B44F7AF);
            c = _FF(c, d, a, b, x[k + 10], S13, 0xFFFF5BB1);
            b = _FF(b, c, d, a, x[k + 11], S14, 0x895CD7BE);
            a = _FF(a, b, c, d, x[k + 12], S11, 0x6B901122);
            d = _FF(d, a, b, c, x[k + 13], S12, 0xFD987193);
            c = _FF(c, d, a, b, x[k + 14], S13, 0xA679438E);
            b = _FF(b, c, d, a, x[k + 15], S14, 0x49B40821);
            a = _GG(a, b, c, d, x[k + 1], S21, 0xF61E2562);
            d = _GG(d, a, b, c, x[k + 6], S22, 0xC040B340);
            c = _GG(c, d, a, b, x[k + 11], S23, 0x265E5A51);
            b = _GG(b, c, d, a, x[k + 0], S24, 0xE9B6C7AA);
            a = _GG(a, b, c, d, x[k + 5], S21, 0xD62F105D);
            d = _GG(d, a, b, c, x[k + 10], S22, 0x2441453);
            c = _GG(c, d, a, b, x[k + 15], S23, 0xD8A1E681);
            b = _GG(b, c, d, a, x[k + 4], S24, 0xE7D3FBC8);
            a = _GG(a, b, c, d, x[k + 9], S21, 0x21E1CDE6);
            d = _GG(d, a, b, c, x[k + 14], S22, 0xC33707D6);
            c = _GG(c, d, a, b, x[k + 3], S23, 0xF4D50D87);
            b = _GG(b, c, d, a, x[k + 8], S24, 0x455A14ED);
            a = _GG(a, b, c, d, x[k + 13], S21, 0xA9E3E905);
            d = _GG(d, a, b, c, x[k + 2], S22, 0xFCEFA3F8);
            c = _GG(c, d, a, b, x[k + 7], S23, 0x676F02D9);
            b = _GG(b, c, d, a, x[k + 12], S24, 0x8D2A4C8A);
            a = _HH(a, b, c, d, x[k + 5], S31, 0xFFFA3942);
            d = _HH(d, a, b, c, x[k + 8], S32, 0x8771F681);
            c = _HH(c, d, a, b, x[k + 11], S33, 0x6D9D6122);
            b = _HH(b, c, d, a, x[k + 14], S34, 0xFDE5380C);
            a = _HH(a, b, c, d, x[k + 1], S31, 0xA4BEEA44);
            d = _HH(d, a, b, c, x[k + 4], S32, 0x4BDECFA9);
            c = _HH(c, d, a, b, x[k + 7], S33, 0xF6BB4B60);
            b = _HH(b, c, d, a, x[k + 10], S34, 0xBEBFBC70);
            a = _HH(a, b, c, d, x[k + 13], S31, 0x289B7EC6);
            d = _HH(d, a, b, c, x[k + 0], S32, 0xEAA127FA);
            c = _HH(c, d, a, b, x[k + 3], S33, 0xD4EF3085);
            b = _HH(b, c, d, a, x[k + 6], S34, 0x4881D05);
            a = _HH(a, b, c, d, x[k + 9], S31, 0xD9D4D039);
            d = _HH(d, a, b, c, x[k + 12], S32, 0xE6DB99E5);
            c = _HH(c, d, a, b, x[k + 15], S33, 0x1FA27CF8);
            b = _HH(b, c, d, a, x[k + 2], S34, 0xC4AC5665);
            a = _II(a, b, c, d, x[k + 0], S41, 0xF4292244);
            d = _II(d, a, b, c, x[k + 7], S42, 0x432AFF97);
            c = _II(c, d, a, b, x[k + 14], S43, 0xAB9423A7);
            b = _II(b, c, d, a, x[k + 5], S44, 0xFC93A039);
            a = _II(a, b, c, d, x[k + 12], S41, 0x655B59C3);
            d = _II(d, a, b, c, x[k + 3], S42, 0x8F0CCC92);
            c = _II(c, d, a, b, x[k + 10], S43, 0xFFEFF47D);
            b = _II(b, c, d, a, x[k + 1], S44, 0x85845DD1);
            a = _II(a, b, c, d, x[k + 8], S41, 0x6FA87E4F);
            d = _II(d, a, b, c, x[k + 15], S42, 0xFE2CE6E0);
            c = _II(c, d, a, b, x[k + 6], S43, 0xA3014314);
            b = _II(b, c, d, a, x[k + 13], S44, 0x4E0811A1);
            a = _II(a, b, c, d, x[k + 4], S41, 0xF7537E82);
            d = _II(d, a, b, c, x[k + 11], S42, 0xBD3AF235);
            c = _II(c, d, a, b, x[k + 2], S43, 0x2AD7D2BB);
            b = _II(b, c, d, a, x[k + 9], S44, 0xEB86D391);
            a = addUnsigned(a, AA);
            b = addUnsigned(b, BB);
            c = addUnsigned(c, CC);
            d = addUnsigned(d, DD);
        }
        var temp = wordToHex(a) + wordToHex(b) + wordToHex(c) + wordToHex(d);
        return temp.toLowerCase();
        
    }
    
    
    

    // -- Private Methods --------------------------------------------------------

    /**
      Creates and returns an HTML element with the specified name and attributes.

      @method createNode
      @param {String} name element name
      @param {Object} attrs name/value mapping of element attributes
      @return {HTMLElement}
      @private
     */
    function createNode(name, attrs) {
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

    /**
      Called when the current pending resource of the specified type has finished
      loading. Executes the associated callback (if any) and loads the next
      resource in the queue.

      @method finish
      @param {String} type resource type ('css' or 'js')
      @private
     */
    function finish(type, isLastCall) {
        var p = pending[type],
        callback,
        urls;

        if (p) {
            
            callback = p.callback;
            urls     = p.urls;

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
                    
                    Debug.info('Execute Callback function in '+ type + ' '+callback);
                    callback && callback.call(p.context, p.obj);
                }
                
                
                

            }
        }
    }

    /**
      Populates the <code>env</code> variable with user agent and feature test
      information.

      @method getEnv
      @private
     */
    function getEnv() {
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
      Loads the specified resources, or the next resource of the specified type
      in the queue if no resources are specified. If a resource of the specified
      type is already being loaded, the new request will be queued until the
      first request has been finished.

      When an array of resource URLs is specified, those URLs will be loaded in
      parallel if it is possible to do so while preserving execution order. All
      browsers support parallel loading of CSS, but only Firefox and Opera
      support parallel loading of scripts. In other browsers, scripts will be
      queued and loaded one at a time to ensure correct execution order.

      @method load
      @param {String} type resource type ('css' or 'js')
      @param {String|Array} urls (optional) URL or array of URLs to load
      @param {Function} callback (optional) callback function to execute when the
        resource is loaded
      @param {Object} obj (optional) object to pass to the callback function
      @param {Object} context (optional) if provided, the callback function will
        be executed in this object's context
      @private
     */
    function load(type, urls, callback, obj, context)
    {
        var _finish = function (isLastCall) {
            finish(type, isLastCall);
        },
        
        isCSS   = type === 'css',
        nodes   = [], async = false,
        i, len, node, p, pendingUrls, url;

        env || getEnv();
        
        

        if (urls) {
            // If urls is a string, wrap it in an array. Otherwise assume it's an
            // array and create a copy of it so modifications won't be made to the
            // original.
            urls = typeof urls === 'string' ? [urls] : urls.concat();

            // Create a request object for each URL. If multiple URLs are specified,
            // the callback will only be executed after all URLs have been loaded.
            //
            // Sadly, Firefox and Opera are the only browsers capable of loading
            // scripts in parallel while preserving execution order. In all other
            // browsers, scripts must be loaded sequentially.
            //
            // All browsers respect CSS specificity based on the order of the link
            // elements in the DOM, regardless of the order in which the stylesheets
            // are actually downloaded.
            if (isCSS || env.async || env.gecko || env.opera) {
                // Load in parallel.

                queue[type].push({
                    urls    : urls,

                    callback: callback,
                    obj     : obj,
                    context : context
                });
                
                async = true;
            } else {
                async = false;
                // Load sequentially.
                for (i = 0, len = urls.length; i < len; ++i) {
                    queue[type].push({
                        urls    : [urls[i].url],
                        rel    : [urls[i].rel],
                        callback: (i == (len - 1) ? callback : null), // callback is only added to the last URL
                        obj     : obj,
                        context : context
                    });
                }
            }
        }

        // If a previous load request of this type is currently in progress, we'll
        // wait our turn. Otherwise, grab the next item in the queue.
        if (pending[type] || !queue[type].length ) {
            return false;
        }
 
        if ( queue[type].length > 0 )
        {
            p = pending[type] = queue[type].shift();
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
            if (typeof loadedScripts[hash] != 'undefined' ) { 
                pendingUrls[i] = null;
                continue;
            }
            
            
            if (isCSS) {
                node = env.gecko ? createNode('style') : createNode('link', {
                    href: urlObj.url,
                    rel : 'stylesheet'
                });
            } else {
                                
                node = createNode('script', {
                    src: urlObj.url,
                    rel: urlObj.rel
                });
                
                if ( async ) node.async = true;
                
            //node.setAttribute('async', false);
            }

            node.className = 'lazyload pending';
            node.setAttribute('charset', 'utf-8');
            

            if (i == (len - 1) )
            {
                lastCall = true;
            }

            node.lastCall = lastCall;

            if (env.ie && !isCSS) {
                
                
                node.onreadystatechange = function () {
                    if (/loaded|complete/.test(node.readyState)) {
                        node.onreadystatechange = null;
                        if (lastCall) {
                            _finish(lastCall);
                        }
                        
                        loadedScripts[hash] = urlObj.url;
                    }
                };
            } else if (isCSS && (env.gecko || env.webkit)) {
                // Gecko and WebKit don't support the onload event on link nodes.
                if (env.webkit) {
                    // In WebKit, we can poll for changes to document.styleSheets to
                    // figure out when stylesheets have loaded.
                    p.urls[i] = node.href; // resolve relative URLs (or polling won't work)
                    pollWebKit();
                    loadedScripts[hash] = urlObj.url;
                } else {
                    // In Gecko, we can import the requested URL into a <style> node and
                    // poll for the existence of node.sheet.cssRules. Props to Zach
                    // Leatherman for calling my attention to this technique.
                    node.innerHTML = '@import "' + urlObj.url + '";';
                    pollGecko(node);
                    loadedScripts[hash] = urlObj.url;
                }
            } else {
                
                node.onload = function(){
                    if (lastCall) {
                        _finish(lastCall);
                    }
                    
                    $(this).removeClass('pending');
                    loadedScripts[hash] = urlObj.url;
                };
                
                node.onerror = function(){
                    $(this).addClass('err');
                    Debug.error('could not load: '+ urlObj.url);
                //window.console.error('could not load: '+ urlObj.url);
                }
                
                
                // one browser needs special treatment - have a guess
                node.onreadystatechange = (function() {
                    var _scriptNode = node;
                    
                    return function() {
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
        
        
        return true;
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
    function pollGecko(node) {
        var hasRules;

        try {
            // We don't really need to store this value or ever refer to it again, but
            // if we don't store it, Closure Compiler assumes the code is useless and
            // removes it.
            hasRules = !!node.sheet.cssRules;
        } catch (ex) {
            // An exception means the stylesheet is still loading.
            pollCount += 1;

            if (pollCount < 200) {
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

        // If we get here, the stylesheet has loaded.
        finish('css');
    }

    /**
      Begins polling to determine when pending stylesheets have finished loading
      in WebKit. Polling stops when all pending stylesheets have loaded or after 10
      seconds (to prevent stalls).

      @method pollWebKit
      @private
     */
    function pollWebKit() {
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
                if (pollCount < 200) {
                    setTimeout(pollWebKit, 50);
                } else {
                    // We've been polling for 10 seconds and nothing's happened, which may
                    // indicate that the stylesheet has been removed from the document
                    // before it had a chance to load. Stop polling and finish the pending
                    // request to prevent blocking further requests.
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
        
        init: function(call)
        {
            if (typeof call == 'function') {
                call();
            }
            Debug.start();
        },
    

        extractScriptsFromArgs: function(_arguments)
        {
            var loads = [];
            
            for(var i=0; i<_arguments.length; ++i)
            {
                if (typeof _arguments[i] === 'string') 
                {
                    
                    
                    if ( 
                    
                        (
                            _arguments[i].substr( _arguments[i].length - 3) != '.js' && 
                            _arguments[i].substr( _arguments[i].length - 4) != '.css' && 
                            !_arguments[i].match(/\.php/)
                            ) 
                        || 
                        (
                            _arguments[i].substr( _arguments[i].length - 3) != '.js' && 
                            _arguments[i].substr( _arguments[i].length - 4) != '.css'
                            )
                
            
                        )
                        {
                            
                        
                        _arguments[i] += '.js';
                    }
                    
                    var sSrc = this.doBase ? Tools.getAbsolutePath(Base.basePath || "", _arguments[i]) : _arguments[i];
                    if ( typeof sSrc == 'string' && sSrc.length > 0 )
                    {
                        loads.push({
                            url: sSrc, 
                            rel: null
                        });
                    }
                }
                else if (typeof _arguments[i] === 'function') 
                {
                    this.callbackFunction = _arguments[i];        
                }
            }
            
            return loads;
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
        loadCss: function (urls, obj, context)
        {
            /*
            if (!this.lasCallDone)
            {
                setTimeout(function(){
                    Loader.loadCss(urls, obj, context)
                }, 10);
            }*/
            
            this.lasCallDone = false;
            this.callbackFunction = null;
            
            var loads = this.extractScriptsFromArgs(arguments);
            
            load('css', loads, null, obj, context);
            
            if (this.lasCallDone)
            {
                this.callbackFunction = null;
                return this;
            }
            
        },

        /**
        Requests the specified JavaScript URL or URLs and executes the specified
        callback (if any) when they have finished loading. If an array of URLs is
        specified and the browser supports it, the scripts will be loaded in
        parallel and the callback will be executed after all scripts have
        finished loading.

        Currently, only Firefox and Opera support parallel loading of scripts while
        preserving execution order. In other browsers, scripts will be
        queued and loaded one at a time to ensure correct execution order.

        @method js
        @param {String|Array} urls JS URL or array of JS URLs to load
        @param {Function} callback (optional) callback function to execute when
          the specified scripts are loaded
        @param {Object} obj (optional) object to pass to the callback function
        @param {Object} context (optional) if provided, the callback function
          will be executed in this object's context
        @static
         */

        require: function (urls, callback, obj, context) 
        {

            
            /*
            
            if ( typeof this.lasCallDone != 'undefined' && !this.lasCallDone )
            {
                this.to = setTimeout(function(){
                    Loader.require(urls, callback, obj, context);
                }, 10);
            }
            else
            {
                clearTimeout(this.to);
            }
            
             */

            this.lasCallDone = false;
            this.callbackFunction = null;
            
            
 
            var loads = this.extractScriptsFromArgs( arguments);
            //this.extractScriptsFromArgs(arguments);
            
            load('js', loads, this.callbackFunction, obj, context);
            

            if (this.lasCallDone)
            {
                this.callbackFunction = null;
                return this;
            }
        },
        
        then: function()
        {
            
            var loads;
            loads = this.extractScriptsFromArgs(arguments);
            
            load('js', loads, this.callbackFunction, obj, context);
            
            if (this.lasCallDone)
            {
                this.callbackFunction = null;
                return this;
            }
        },
        
        run: function(callback)
        {
            if (this.lasCallDone && typeof callback === 'function')
            {
                this.callbackFunction = null;
                
                callback();
                
                return this;
            }
        },
    
        wait: function(milliseconds)
        {        
            var start = new Date().getTime();
            while ((new Date().getTime() - start) < milliseconds){}        
            if (typeof Debug != 'undefined') Debug.info('Wait for '+ milliseconds + 'ms' );
            return this;
        },
        setResourceMap: function(c)
        {
            if (!c) return;
            for (var b in c) {
                if (!c[b].name) {
                    c[b].name = b;
                }
                this._resources[b] = c[b];
            }
            this._prepareResources();
        
            return this;
        },

        _prepareResources: function()
        {        
            var jsregex = /\.js$/;
            var phpregex = /\.php/;
        
            for (var b in this._resources) {
                var load = this._resources[b];
            
                if (load.type == "js" && !jsregex.test( load.src ) && !phpregex.test( load.src )) 
                {			
                    load.src += '.js';
                    this._resources[b].src = load.src;
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
        
        
        setupResources: function( jsCallback )
        {
            
            this.lasCallDone = false;
            this.callbackFunction = null;
            var max = this._runCssCache.length;
            if (max)
            {
                load('css', this._runCssCache, null);
                this._runCssCache = [];
            }


            this.lasCallDone = false;
            max = this._runJsCache.length;
            if (max)
            {
                /*
                var x;
                var str = {};
                for( x=0;x<max;x++)
                {
                    var s = this._runJsCache[x];
                    
                    
                    if ((typeof s.src == 'string' && s.src.match(/\.php.* /g)) || typeof s.rel == 'string')
                    {
                        
                        str += (str.length > 0 ? ';' : '')+s.src;
                        this._runJsCache[x] = null;
                    }
                    
                }
                if (str.length)
                {
                    load('js', str);
                }
                
                
                str = '';
                for( x=0;x<this._runJsCache.length;x++)
                {
                    var s = this._runJsCache[x];
                    str += (str.length > 0 ? ';' : '')+s.src;
                }
                
                
                
                
                var urls = str;
                
                 */
                load('js', this._runJsCache, jsCallback);
                this._runJsCache = [];
                
            }
            
            if (this.lasCallDone)
            {
                this.callbackFunction = null;
                return this;
            }
        }
        
    };
    
    
})(document);


DCMS.Loader = Loader;