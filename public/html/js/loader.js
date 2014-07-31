/* Copyright (c) 2010 Chris O'Hara <cohara87@gmail.com>. MIT Licensed 
(function(exports) {

    exports = exports || {};

    var handlers = {}, createChain, add;

    createChain = function (context, stack, lastMethod) {

        var inHandler = context.halt = false;

        //The default error handler
        context.error = function (e) {
            throw e;
        }

        //Run the next method in the chain
        context.next = function (exit) {
            if (exit) {
                inHandler = false;
            }
            if (!context.halt && stack && stack.length) {
                var args = stack.shift(), method = args.shift();
                inHandler = true;
                try {
                    handlers[method].apply(context, [args, args.length, method]);
                } catch (e) {
                    context.error(e);
                }
            }
            return context;
        }

        //Bind each method to the context
        for (var alias in handlers) {
            if (typeof context[alias] === 'function') {
                continue;
            }
            (function (alias) {
                context[alias] = function () {
                    var args = Array.prototype.slice.call(arguments);
                    if (alias === 'onError') {
                        if (stack) {
                            handlers.onError.apply(context, [args, args.length]);
                            return context;
                        } else {
                            var new_context = {};
                            handlers.onError.apply(new_context, [args, args.length]);
                            return createChain(new_context, null, 'onError');
                        }
                    }
                    args.unshift(alias);
                    if (!stack) {
                        return createChain({}, [args], alias);
                    }
                    context.then = context[alias];
                    stack.push(args);
                    return inHandler ? context : context.next();
                }
            }(alias));
        }

        //'then' is an alias for the last method that was called
        if (lastMethod) {
            context.then = context[lastMethod];
        }

        //Used to call run(), chain() or another existing method when defining a new method
        //See load.js (https://github.com/chriso/load.js/blob/master/load.js) for an example
        context.call = function (method, args) {
            args.unshift(method);
            stack.unshift(args);
            context.next(true);
        }

        return context.next();
    }

    //Add a custom method/handler (see below)
    add = exports.addMethod = function (method /, alias1, alias2, ..., callback /) {
        var args = Array.prototype.slice.call(arguments),
        handler = args.pop();
        for (var i = 0, len = args.length; i < len; i++) {
            if (typeof args[i] === 'string') {
                handlers[args[i]] = handler;
            }
        }
        //When no aliases have been defined, automatically add 'then<Method>'
        //e.g. adding 'run' also adds 'thenRun' as a method
        if (!--len) {
            handlers['then' + method.substr(0,1).toUpperCase() + method.substr(1)] = handler;
        }
        createChain(exports);
    }

    //chain() - Run each function sequentially
    add('then', function (args) {
        var self = this, next = function () {
            if (self.halt) {
                return;
            } else if (!args.length) {
                return self.next(true);
            }
            try {
                if (null != args.shift().call(self, next, self.error)) {
                    next();
                }
            } catch (e) {
                self.error(e);
            }
        }
        next();
    });

    //run() - Run each function in parallel and progress once all functions are complete
    add('run', function (args, arg_len) {
        var self = this, chain = function () {
            if (self.halt) {
                return;
            } else if (!--arg_len) {
                self.next(true);
            }
        }
        var error = function (e) {
            self.error(e);
        };
        for (var i = 0, len = arg_len; !self.halt && i < len; i++) {
            if (null != args[i].call(self, chain, error)) {
                chain();
            }
        }
    });

    //defer() - Defer execution of the next method
    add('defer', function (args) {
        var self = this;
        setTimeout(function () {
            self.next(true);
        }, args.shift());
    });

    //onError() - Attach an error handler
    add('onError', function (args, arg_len) {
        var self = this;
        this.error = function (err) {
            self.halt = true;
            for (var i = 0; i < arg_len; i++) {
                args[i].call(self, err);
            }
        }
    });

}(this)); */

/* Copyright (c) 2010 Chris O'Hara <cohara87@gmail.com>. MIT Licensed */
(function(exports) {

    exports = exports || {};

    var handlers = {}, createChain, add;

    createChain = function (context, stack, lastMethod) {

        var inHandler = context.halt = false;

        //The default error handler
        context.error = function (e) {
            printStackTrace(e);
            throw e;
        }

        //Run the next method in the chain
        context.next = function (exit) {
            if (exit) {
                inHandler = false;
            }
            if (!context.halt && stack && stack.length) {
                printStackTrace();
                var args = stack.shift(), method = args.shift();
                inHandler = true;
                try {
                    handlers[method].apply(context, [args, args.length, method]);
                } catch (e) {
                    
                    context.error(e);
                }
            }
            return context;
        }

        //Bind each method to the context
        for (var alias in handlers) {
            if (typeof context[alias] === 'function') {
                continue;
            }
            (function (alias) {
                context[alias] = function () {
                    var args = Array.prototype.slice.call(arguments);
                    if (alias === 'onError') {
                        if (stack) {
                            printStackTrace();
                            handlers.onError.apply(context, [args, args.length]);
                            return context;
                        } else {
                            var new_context = {};
                            handlers.onError.apply(new_context, [args, args.length]);
                            return createChain(new_context, null, 'onError');
                        }
                    }
                    args.unshift(alias);
                    if (!stack) {
                        printStackTrace();
                        return createChain({}, [args], alias);
                    }
                    context.then = context[alias];
                    stack.push(args);
                    return inHandler ? context : context.next();
                }
            }(alias));
        }

        //'then' is an alias for the last method that was called
        if (lastMethod) {
            context.then = context[lastMethod];
        }

        //Used to call run(), chain() or another existing method when defining a new method
        //See load.js (https://github.com/chriso/load.js/blob/master/load.js) for an example
        context.call = function (method, args) {
            printStackTrace();
            args.unshift(method);
            stack.unshift(args);
            context.next(true);
        }

        return context.next();
    }

    //Add a custom method/handler (see below)
    add = exports._addMethod = function (method /*, alias1, alias2, ..., callback */) {
        var args = Array.prototype.slice.call(arguments),
        handler = args.pop();
        for (var i = 0, len = args.length; i < len; i++) {
            if (typeof args[i] === 'string') {
                handlers[args[i]] = handler;
            }
        }
        //When no aliases have been defined, automatically add 'then<Method>'
        //e.g. adding 'run' also adds 'thenRun' as a method
        if (!--len) {
            handlers['then' + method.substr(0,1).toUpperCase() + method.substr(1)] = handler;
        }
        createChain(exports);
    }



    add('then', function (args) {
        var self = this, next = function () {
            if (self.halt) {
                return;
            } else if (!args.length) {
                return self.next(true);
            }
            try {
                if (null != args.shift().call(self, next, self.error)) {
                    next();
                }
            } catch (e) {
                self.error(e);
            }
        }
        next();
    });

    //chain() - Run each function sequentially
    add('chain', function (args) {
        var self = this, next = function () {
            if (self.halt) {
                return;
            } else if (!args.length) {
                return self.next(true);
            }
            try {
                if (null != args.shift().call(self, next, self.error)) {
                    next();
                }
            } catch (e) {
                self.error(e);
            }
        }
        next();
    });


    //run() - Run each function in parallel and progress once all functions are complete
    add('run', function (args, arg_len) {
        var self = this, chain = function () {
            if (self.halt) {
                return;
            } else if (!--arg_len) {
                self.next(true);
            }
        }
        var error = function (e) {
            self.error(e);
        };
        for (var i = 0, len = arg_len; !self.halt && i < len; i++) {
            if (null != args[i].call(self, chain, error)) {
                chain();
            }
        }
    });

    //defer() - Defer execution of the next method
    add('wait', function (args) {
        var self = this;
        setTimeout(function () {
            self.next(true);
        }, args.shift());
    });

    //onError() - Attach an error handler
    add('onError', function (args, arg_len) {
        var self = this;
        this.error = function (err) {
            self.halt = true;
            for (var i = 0; i < arg_len; i++) {
                args[i].call(self, err);
            }
        }
    });

}(this));


_addMethod('then', function (args) {
    var self = this, next = function () {
        if (self.halt) {
            return;
        } else if (!args.length) {
            return self.next(true);
        }
        try {
            if (null != args.shift().call(self, next, self.error)) {
                next();
            }
        } catch (e) {
            self.error(e);
        }
    }
    next();
});








_addMethod('load', function (args, argc) {
    console.log(args);
    console.log(argc);
  
    for (var queue = [], i = 0; i < argc; i++) {
        (function (i) {
            queue.push(function (next, error) {
                loadScript(args[i], next, error);
            });
        }(i));
    }
    this.call('run', queue);
});

_addMethod('_dcmsload', function (args, argc) {
    console.log(args);
    console.log(argc);
  
    for (var queue = [], i = 0; i < argc; i++) {
        (function (i) {
            queue.push(function (next, error) {
                _loadScript(args[i], next, error);
            });
        }(i));
    }
    this.call('run', queue);
});

_addMethod('error', function (args, argc) {

    if (Debug)
    {
        Debug.error(args);
        return;
    }
    
    console.log(args);
    console.log(argc);
});




var __head = document.getElementsByTagName('head')[0] || document.documentElement;
var _baseLoadeLoaded = false;



function _loadScript(src, onload, onerror) {
    Loader.load(src, onload, onerror);
}

function loadScript(src, onload, onerror) {

    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = src;
    script.onload = onload;
    script.onerror = onerror;
    script.onreadystatechange = function () {
        var state = this.readyState;
        if (state === 'loaded' || state === 'complete') {
            script.onreadystatechange = null;
            onload();
        }
    };
    __head.insertBefore(script, __head.lastChild);

}
function printStackTrace() {
    var callstack = [];
    var isCallstackPopulated = false;
    try {
        i.dont.exist+=0; //doesn't exist- that's the point
    } catch(e) {
        if (e.stack) { //Firefox
            var lines = e.stack.split("\n");
            for (var i=0, len=lines.length; i<len; i++) {
                if (lines[i].match(/^\s*[A-Za-z0-9\-_\$]+\(/)) {
                    callstack.push(lines[i]);
                }
            }
            //Remove call to printStackTrace()
            callstack.shift();
            isCallstackPopulated = true;
        }
        else if (window.opera && e.message) { //Opera
            var lines = e.message.split("\n");
            for (var i=0, len=lines.length; i<len; i++) {
                if (lines[i].match(/^\s*[A-Za-z0-9\-_\$]+\(/)) {
                    var entry = lines[i];
                    //Append next line also since it has the file info
                    if (lines[i+1]) {
                        entry += " at " + lines[i+1];
                        i++;
                    }
                    callstack.push(entry);
                }
            }
            //Remove call to printStackTrace()
            callstack.shift();
            isCallstackPopulated = true;
        }
    }
    if (!isCallstackPopulated) { //IE and Safari
        var currentFunction = arguments.callee.caller;
        while (currentFunction) {
            var fn = currentFunction.toString();
            var fname = fn.substring(fn.indexOf("function") + 8, fn.indexOf("(")) || "anonymous";
            callstack.push(fname);
            currentFunction = currentFunction.caller;
        }
    }
    output(callstack);
}
 
function output(arr) {
    //Output whatever you want
    console.info('stack trace', arr.join("nn"));
}

// load DCMS Basics
load('html/js/dcms.config.js', 'html/js/dcms.tools.js', 
    'html/js/dcms.debug.js', 'html/js/dcms.base.js').thenRun(function(){
    Base.init();
});