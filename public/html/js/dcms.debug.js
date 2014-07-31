var Debug = {

    lastTimeRef: null,
    execCount: 0,
    totalTime: 0,
    
    minTime: 1000000,
    maxTime: 0,
    entries: [],
    lastEntry: {endTime: 0, totalTime: 0, startTime: 0},
    
    
    start : function () {
        this.lastTimeRef = new Date().getTime();
        this.execCount++;
        this.lastEntry.startTime = this.lastTimeRef;
    },
    
    
    /**
     * Method execution ends.
     */
    finalize : function() {
        this.lastEntry.endTime = new Date().getTime();
        this.lastEntry.totalTime = this.lastEntry.endTime - this.lastEntry.startTime;
        this.totalTime += (this.lastEntry.endTime - this.lastEntry.startTime);

        if (this.lastEntry.totalTime > this.maxTime) {
            this.maxTime = this.lastEntry.totalTime;
        }

        if (this.lastEntry.totalTime < this.minTime) {
            this.minTime = this.lastEntry.totalTime;
        }
    },
    
    
    /**
   * Returns the total time, in milliseconds, that the method related to
   * this profile has been executing.
   */
    getTotalTime : function() {
        return this.totalTime;
    },

    /**
     * Returns how many times this method has been called.
     */
    getExecutionCount : function() {
        return this.execCount;
    },
    
    
    
    printStackTrace: function() {
        var callstack = [];
        var isCallstackPopulated = false;
        
        try {
            i.dont.exist+=0; //doesn't exist- that's the point
        } 
        catch(e) 
        {
            if (e.stack) { //Firefox
                var lines = e.stack.split("\n");
                lines.shift();
                //lines.shift();
                
                for (var i=0, len=lines.length; i<len; i++) {
                    if (lines[i].match(/^\s*[A-Za-z0-9\-_\$]+\(?/)) {
                        callstack.push(lines[i]);
                    }
                }
                //Remove call to printStackTrace()
                callstack.shift();
                isCallstackPopulated = true;
            }
            else if (window.opera && e.message) { //Opera
                var lines = e.message.split("\n");
                
                lines.shift();
                lines.shift();
                
                for (var i=0, len=lines.length; i<len; i++) {

                    if (lines[i].match(/^\s*[A-Za-z0-9\-_\$]+\(?/) ) {
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
        
        if (callstack.length) this.outputStack(callstack);
    },
 
    outputStack: function(arr)
    {
        console.info('stack trace', arr.join("\n\n"));
    },




    write: function (text)
    {
        if (Config.jsDebug && !Tools.isUndefined(window.console))
        {
            console.log(text);
        }
    },
    dir: function (values)
    {
        if (Config.jsDebug && !Tools.isUndefined(window.console))
        {
            console.dir(values);
        }
    },
    error: function (text)
    {
        if (Config.jsDebug && !Tools.isUndefined(window.console))
        {
            console.error(text);
            this.printStackTrace();
        }
    },
    warn: function (text)
    {
        if (Config.jsDebug && !Tools.isUndefined(window.console))
        {
            console.warn(text);
            this.printStackTrace();
        }
    },
    info: function (text)
    {
        if (Config.jsDebug && !Tools.isUndefined(window.console))
        {
            console.info(text);
        }
    }
};
