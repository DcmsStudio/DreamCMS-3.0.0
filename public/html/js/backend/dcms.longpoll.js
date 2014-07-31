function dcmsLongpoll() {

    this.updateTimeout = 1000;
    this.timer;
    this.timestamp = null;

    this.opts = {
        url: false,
        method: 'get',
        postdata: {},
        callback: function(data)
        {

        }
    };

    this.init = function(options)
    {
        if (!options.url)
        {
            return;
        }

        this.updateTimeout = options.updateTimeout || this.updateTimeout;

        if (this.updateTimeout < 500)
        {
            this.updateTimeout = 500;
        }

        if (typeof options.callback == 'function')
        {
            opts.callback = options.callback;
        }

        this.opts.url = options.url;
        this.opts.method = options.method || this.opts.method;
        this.opts.method = this.opts.method.toLowerCase();

        this.opts.postdata = options.postdata || this.opts.postdata;
    };

    this.execute = function()
    {
        if (!this.opts.url)
        {
            return;
        }

        var __self = this;
        if (this.opts.method == 'get')
        {
            $.ajax({
                type: "GET",
                url: this.opts.url + '&timestamp=' + timestamp,
                async: false,
                cache: false,
                success: function(data) {
                    
                    __self.opts.callback(data, function() {
                        
                        __self.timestamp = data["timestamp"];
                        __self.timer = setTimeout(function() {
                            __self.execute();
                        }, __self.updateTimeout);
                        
                    });
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    __self.timer = setTimeout(function() {
                        __self.execute();
                    }, __self.updateTimeout + 15000);
                }
            });
        }

        if (this.opts.method == 'post')
        {

            this.opts.postdata += '&timestamp=' + timestamp;

            $.ajax({
                type: "POST",
                url: this.opts.url,
                data: this.opts.postdata,
                async: false,
                cache: false,
                success: function(data) {
                    
                    __self.opts.callback(data, function() {
                        
                        __self.timestamp = data["timestamp"];
                        __self.timer = setTimeout(function() {
                            __self.execute();
                        }, __self.updateTimeout);
                        
                    });
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    __self.timer = setTimeout(function() {
                        __self.execute();
                    }, __self.updateTimeout + 15000);
                }
            });
        }
    };

    this.stop = function()
    {
        clearTimeout(this.timer);
        this.opts = {};
        this.timestamp = null;
    }


}