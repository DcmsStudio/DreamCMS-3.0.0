var trieCommands, hintNodes, hintsTimer,
        selectedHint = null,
        commands = {},
        cmdTries = {},
        cmdFetched = false,
        cmdHistory = [],
        cmdBuffer = "",
        lastSearch = null, gui_matrix = null;

var DesktopConsole = {
    commands: {
        "help": {
            hint: "show general help information and a list of available commands"
        },
        "clear": {
            hint: "clear all the messages from the console"
        },
        "send": {
            hint: "send a message to the server"
        }
    },
    cmdHistory: [],
    lineBuffer: "",
    // config:

    // chars to be parsed as white space
    whiteSpace: {
        ' ': true,
        '\t': true
    },
    // chars to be parsed as quotes
    quoteChars: {
        '"': true,
        "'": true,
        '`': true
    },
    // chars to be parsed as escape char
    singleEscapes: {
        '\\': true
    },
    // chars that mark the start of an option-expression
    // for use with getopt
    optionChars: {
        '-': true
    },
    // chars that start escape expressions (value = handler)
    // plugin handlers for ascii escapes or variable substitution
    escapeExpressions: {
        '%': this.hexExpression
    },
    argv: [''],
    argQL: [''],
    argc: 0,
    cwd: '',
    minHeight: 38,
    maxHeight: 152,
    console: null,
    consoleText: null,
    consoleDebug: null,
    consoleErrors: null,
    consoleBase: null,
    consoleFooter: null,
    visibleMode: null,
    txtOutput: null,
    inited: false,
    textPre: null,
    screenSaver: null,
    scrollpos: {
        console: 0,
        debug: 0
    },
	visible: false,
    init: function ()
    {
        if (this.inited)
            return;



        this.console = $('#gui-console');
        this.consoleContent = $('#gui-console-content', this.console);
        this.consoleText = $('#gui-console-text', this.console);
        this.consoleDebug = $('#gui-console-debug', this.console);
        this.consoleErrors = $('#gui-console-errors', this.console);

        this.consoleBase = $('#gui-console-base');
        this.consoleFooter = $('.gui-console', this.console);




        this.textPre = this.consoleText.find('pre:first');
        this.bindEvents();
        this.inited = true;

        this.cwd = Config.get('workdir', '');



        this.console.click(function () {
            if ($(this).hasClass('focus'))
            {
                return;
            }

            var thisZindex = $(this).css('zIndex');

            $('.isWindowContainer').each(function () {

                if ($(this).css('zIndex') > thisZindex)
                {
                    thisZindex = $(this).css('zIndex');
                }

            });

            $(this).addClass('focus').css('zIndex', thisZindex + 1);
        });




        if (!this.consoleDebug.find('pre:first').length)
        {
            $(this.consoleDebug).append($('<pre>'));
        }

        var self = this;
        var buttonsHeight = $('.gui-console-buttons', this.console).height();

        this.consoleContent.height( 220);

        this.consoleBase.resizable(
                {
                    "handles": 'ns',
                    resize: function (event, ui)
                    {
                        var h = $(self.consoleBase).height() - buttonsHeight;
                        self.consoleContent.height(h);

                        if (!Desktop.isWindowSkin) {
                        //    Core.updateViewPort();
                       //     $(window).trigger('resize');
                        }

                        self.updateScroll(self.visibleMode);
                    },
                    stop: function (event, ui) {
                        var h = $(self.consoleBase).height() - buttonsHeight;
                        self.consoleContent.height(h);

                        if (!Desktop.isWindowSkin) {
                        //    Core.updateViewPort();
                        //    $(window).trigger('resize');
                        }

                        self.updateScroll(self.visibleMode);
                    }
                });

        $('.ui-resizable-handle', this.console).addClass('n-sizer');

        this.visibleMode = 'debug';
        this.log( this.getPrompt() , 'prompt');

        $('.debuginfo', self.consoleBase).click();


        Tools.scrollBar(this.consoleContent.find('>div:first'), (this.debugTop || 'top') );

        if (Config.get('ConsoleOpen', null))
        {
            $('#toggle', self.consoleFooter).click();
        }

        this.console.hide();

    },
    resetZindex: function ()
    {
        $('#gui-console').removeClass('focus').css('zIndex', 100);
    },
    oh: 0,
    toggle: function (clickObj)
    {
        var self = this;
        if ($(this.console).is(':visible'))
        {
            self.oh = $(self.console).innerHeight();
            $(this.console).addClass('noshadow').animate({
                height: "-5"
            }, 300, function () {
                $(clickObj).removeClass('active');
                $(this).hide().removeClass('noshadow').removeClass('open');

				self.visible = false;
                if (!Desktop.isWindowSkin) {
                //    Core.updateViewPort();
                //    $(window).trigger('resize');
                }
            });


        }
        else
        {
            this.console.click();
            $(clickObj).parent().find('.active').click();

            var h = $(self.console).innerHeight();
            if (self.oh > 0)
            {
                h = self.oh;
            }

            $(this.console).css({
                'bottom': '0',
                height: '0'
            }).show();

            $(this.console).animate({
                height: h
            }, 300, function () {
                $(this).css({
                    height: 'auto'
                }).addClass('open');
                $(clickObj).addClass('active');

				self.visible = true;

                if (!Desktop.isWindowSkin) {
                //    Core.updateViewPort();
                //    $(window).trigger('resize');
                }

                self.updateScroll(self.visibleMode);


            //    Tools.scrollBar($(self.consoleText).find('pre:first'));
            //    Tools.scrollBar($(self.consoleDebug).find('pre:first'));
            //    Tools.scrollBar($(self.consoleErrors).find('div:first'));
            });


        }
    },
    getHeight: function () {
        return ($(this.consoleBase).outerHeight() + $(this.consoleFooter).outerHeight());
    },
    help: function () {
        var words = trieCommands.getWords(),
                text = [];

        for (var i = 0, l = words.length; i < l; ++i) {
            if (!commands[words[i]])
                continue;
            text.push(words[i] + "\t\t\t\t" + commands[words[i]].hint);
        }
        this.logNodeStream(text.join("\n"));
    },
    clear: function () {
        this.inited && this.consoleText.empty();
    },
    hideConsole: function ()
    {

    },
    setErrors: function (text)
    {
        if (typeof text == 'string')
        {
            if (!$('#gui-console-errors').find('.contain').length)
            {
                $('#gui-console-errors').append('<div class="contain"></div>');
            }

            var div = $('#gui-console-errors').find('div.contain');

            if (div.length == 1)
            {
                var html = $.parseHTML(text);
                div.empty().append(html);
                this.updateScroll(this.visibleMode);
            }

        }
    },
    setDebug: function (text)
    {
        if (typeof text == 'string')
        {
            if (!$('#gui-console-debug').find('pre').length)
            {
                $('#gui-console-debug').append('<pre></pre>');
            }

            var pre = $('#gui-console-debug').find('pre:first');
            if (pre.length == 1)
            {
                var html = $.parseHTML(text);
                pre.empty().append(html);
                this.updateScroll(this.visibleMode);
            }

        }
    },
    display: function (wath)
    {
        var console = $('.console', this.consoleBase);
        var debugoutput = $('.debuginfo', this.consoleBase);
        var errors = $('.errors', this.consoleBase);

        if (wath == 'console')
        {
            // this.scrollpos.debug = this.consoleDebug.getScrollbarPos();

            errors.removeClass('act');
            debugoutput.removeClass('act');
            console.addClass('act');

            this.visibleMode = 'console';

            this.consoleErrors.hide();
            this.consoleDebug.hide();
            this.consoleText.show();

            this.updateScroll('console');
        }
        else if (wath == 'debug')
        {
            errors.removeClass('act');
            console.removeClass('act');
            debugoutput.addClass('act');

            this.visibleMode = 'debug';
            this.consoleErrors.hide();
            this.consoleText.hide();
            this.consoleDebug.show();


            this.updateScroll('debug');
        }
        else if (wath == 'errors')
        {
            console.removeClass('act');
            debugoutput.removeClass('act');
            errors.addClass('act');
            this.visibleMode = 'errors';

            this.consoleText.hide();
            this.consoleDebug.hide();
            this.consoleErrors.show();


            this.updateScroll('errors');
        }

        $(window).trigger('resize');

    },

    consoleTop: 0,
    debugTop: 0,
    errorTop: 0,

    storeScrollPos: function() {
        "use strict";
        var top = 0;

        if ( this.consoleText.is(':visible') ){
            this.consoleTop = ($(this.consoleText).length ? $(this.consoleText).get(0).scrollTop : 0);
        }

        if ( this.consoleDebug.is(':visible') ){
            this.debugTop = ($(this.consoleDebug).length ? $(this.consoleDebug).get(0).scrollTop : 0);
        }

        if ( this.consoleErrors.is(':visible') ){
            this.errorTop = ($(this.consoleErrors).length ? $(this.consoleErrors).get(0).scrollTop : 0);
        }

    },


    getPosTop: function() {
        "use strict";
        if (!this.visible) {
            return;
        }

        var top = 0;

        if (this.visibleMode == 'console') {
            top = ($(this.consoleText).length ? $(this.consoleText).get(0).scrollTop : 0);
            this.consoleTop = top;
        }

        if (this.visibleMode == 'debug') {
            top = ($(this.consoleDebug).length ? $(this.consoleDebug).get(0).scrollTop : 0);
            this.debugTop = top;
        }

        if (this.visibleMode == 'errors') {
            top = ($(this.consoleErrors).length ? $(this.consoleErrors).get(0).scrollTop : 0);
            this.errorTop = top;
        }

        return top;
    },

    updateScroll: function (wath, to)
    {
		if (!this.visible) {
			return;
		}




        if (wath == 'console') {
            Tools.scrollBar(this.consoleContent.find('>div:first'), (this.consoleTop || 'bottom') );
            //Tools.scrollBar($(this.consoleText).find('pre:first'), 'bottom');
        }
        else if (wath == 'debug')
        {
            Tools.scrollBar(this.consoleContent.find('>div:first'), (this.debugTop || 'top') );
            //Tools.scrollBar($(this.consoleDebug).find('pre:first'), top);
        }
        else if (wath == 'errors')
        {
            Tools.scrollBar(this.consoleContent.find('>div:first'), (this.errorTop || 'top') );
            //Tools.scrollBar($(this.consoleErrors).find('div:first'), 'top');
        }

    },
    bindEvents: function ()
    {
        var self = this, console = $('.console', this.consoleBase), debugoutput = $('.debuginfo', this.consoleBase), errors = $('.errors', this.consoleBase);

        console.click(function () {

            self.storeScrollPos();

            errors.removeClass('act');
            debugoutput.removeClass('act');
            console.addClass('act');

            $('#gui-console-debug').hide();
            $('#gui-console-errors').hide();
            $('#gui-console-text').show();

            self.updateScroll('console');
        });

        debugoutput.click(function () {
            self.storeScrollPos();

            console.removeClass('act');
            errors.removeClass('act');
            debugoutput.addClass('act');


            $('#gui-console-errors').hide();
            $('#gui-console-text').hide();
            $('#gui-console-debug').show();

            self.updateScroll('debug');
        });

        errors.click(function () {
            self.storeScrollPos();

            console.removeClass('act');
            debugoutput.removeClass('act');
            errors.addClass('act');

            $('#gui-console-text').hide();
            $('#gui-console-debug').hide();
            $('#gui-console-errors').show();
            self.updateScroll('errors');
        });


        $('.close', this.consoleBase).click(function () {
            if (self.consoleBase.is(':visible'))
            {
                $(self.consoleBase).hide();
                self.console.removeClass('open');
				self.visibleMode = '';

                if (!Desktop.isWindowSkin) {
                    Core.updateViewPort();
                    $(window).trigger('resize');
                }

                // update personal settings
                $.get('admin.php?adm=sidebar&ajax=1&height=0', {}, function (data)
                {
                    if (!Tools.responseIsOk(data))
                    {
                        alert('Error ' + data.msg);
                    }
                }, 'json');
            }
        });

        $('#toggle', this.consoleFooter).click(function () {

            var consoleHeight = 0;


            if (self.consoleBase.is(':visible'))
            {
				self.visibleMode = '';
                $(self.consoleBase).hide();
                self.console.removeClass('open');
            }
            else
            {
                $(self.consoleBase).show();
                consoleHeight = 1;
                self.console.addClass('open');
            }

            if (!Desktop.isWindowSkin) {
                Core.updateViewPort();
                $(window).trigger('resize');
            }

            // update personal settings
            $.get('admin.php?adm=sidebar&ajax=1&height=' + consoleHeight, {}, function (data)
            {
                if (!Tools.responseIsOk(data))
                {
                    alert('Error ' + data.msg);
                }
            }, 'json');

        });


        $('.clear', this.consoleBase).click(function () {
            self.consoleText.empty();
            self.log(self.getPrompt(), "prompt");
        });


        $('#console-exec', this.consoleFooter).bind('keydown', self.keydownHandler);
        $('#console-exec', this.consoleFooter).bind('keyup', self.keyupHandler);

        //this.addScreenSaver();
    },
    removeScreenSaver: function ()
    {
        clearTimeout(this.screenSaver);
        $('#gui-console-matrix').hide();
        //   this.addScreenSaver();
    },
    addScreenSaver: function ()
    {
        clearTimeout(this.screenSaver);

        this.screenSaver = setTimeout(function () {
            $('#gui-console-matrix').show();
            /*
             gui_matrix = new Matrix('gui_matrix', 'gui-console-matrix');
             gui_matrix.SetChars('01','€');
             gui_matrix.SetSize(10,24,20,14);
             gui_matrix.SetWormLengthRange(1,10);
             gui_matrix.SetColors('#ffffff','#000000','#cccccc','#999999');
             gui_matrix.SetTiming(100,0,50);
             gui_matrix.SetMode('random'); //random or sequence
             gui_matrix.Run();
             */
        }, 3000);


    },
    setCwd: function (cwd)
    {
        if (typeof cwd != 'string')
        {
            return;
        }

        if (cwd != this.cwd)
        {
            this.write("Working directory changed.");
        }

        this.cwd = cwd;
    },
    getCwd: function ()
    {
        return "/" + this.cwd;
    },
    getPrompt: function () {
        return "[" + Config.get('UserName', 'Guest') + "@" + Config.get('WebsiteDomain', 'DreamCMS') + "]:" + this.getCwd() + "$";
    },
    logNodeStream: function (data, stream, useOutput) {

        if ( !this.consoleBase.is(':visible') ) {
            $('#toggle', this.consoleFooter).trigger('click');
        }

        var colors = {
            30: "#eee",
            31: "red",
            32: "green",
            33: "yellow",
            34: "blue",
            35: "magenta",
            36: "cyan",
            37: "#eee"
        };

        var lines = data.output.split("\n");
        var style = "color:#eee;";
        var log = [];

        // absolute workspace files
        var wsRe = new RegExp("\\/([^:]*)(:\\d+)(:\\d+)*", "g");

        // relative workspace files
        var wsrRe = /(?:\s|^|\.\/)([\w\_\$-]+(?:\/[\w\_\$-]+)+(?:\.[\w\_\$]+))?(\:\d+)(\:\d+)*/g;

        for (var i = 0; i < lines.length; i++) {
            if (!lines[i])
                continue;


            log.push("<div class='item'><span style='" + style + "'>" + lines[i]
                    .replace(wsRe, "<a href='javascript:void(0)' onclick='require(\"ext/console/console\").jump(\"" + "/$1\", \"$2\", \"$3\")'>" + workspaceDir + "/$1$2$3</a>")
                    .replace(wsrRe, "<a href='javascript:void(0)' onclick='require(\"ext/console/console\").jump(\"" + "/$1\", \"$2\", \"$3\")'>$1$2$3</a>")
                    .replace(/\s{2,}/g, function (str)
                    {
                        return str.replace(" ", '&nbsp;')
                    })
                    .replace(/(((http:\/\/)|(www\.))[\w\d\.-]*(:\d+)?(\/[\w\d]+)?)/, "<a href='$1' target='_blank'>$1</a>")

                    // tty escape sequences (http://ascii-table.com/ansi-escape-sequences.php)

                    .replace(/(\u0007|\u001b)\[(K|2J)/g, "")
                    .replace(/\033\[(?:(\d+);)?(\d+)m/g, function (m, extra, color)
                    {
                        style = "color:" + (colors[color] || "#eee");

                        if (extra == 1)
                        {
                            style += ";font-weight=bold"
                        }
                        else if (extra == 4)
                        {
                            style += ";text-decoration=underline";
                        }

                        return "</span><span style='" + style + "'>"

                    }) + "</span></div>");
        }

        this.consoleText.append(log.join(""));
        this.updateScroll(this.visibleMode);

        //(useOutput ? txtOutput : txtConsole).addValue(log.join(""));
    },
    log: function (msg, type, pre, post, otherOutput)
    {
        msg = String(msg);


        if ( !this.consoleBase.is(':visible') ) {
            $('#toggle', this.consoleFooter).trigger('click');
        }

        if (!type)
            type = "log";
        else if (type == "divider") {
            msg = "<span style='display:block;border-top:1px solid #444; margin:6px 0 6px 0;'></span>";
        }
        else if (type == "prompt") {
            msg = "<span style='color:#86c2f6'>" + msg + "</span>";
        }
        else if (type == "command") {
            msg = "<span style='color:#86c2f6'><span style='float:left'>&gt;&gt;&gt;</span><div style='margin:0 0 0 25px'>"
                    + msg + "</div></span>";
        }

        if (!this.consoleText.find('pre:first').length)
        {
            // this.consoleText.append('<pre>');


            //if (!this.consoleText.hasClass('isscrollable') ) { this.consoleText.append( $('<pre>') ); }
            //else {

            $(this.consoleText).append($('<pre>'));
            // }


            this.textPre = this.consoleText.find('pre:first');
            //this.consoleText.scrollbars();
        }

        this.textPre.append("<div class='item console_" + type + "'>" + (pre || "") + msg + (post || "") + "</div>");
        //    var self = this;

        //  this.scrollpos.console = this.textPre.outerHeight(true);

    },
    scrollDown: function ()
    {
        this.updateScroll(this.visibleMode, 'bottom');
    },
    write: function (aLines) {
        if (typeof aLines == "string")
            aLines = aLines.split("\n");
        for (var i = 0, l = aLines.length; i < l; ++i)
            this.log(aLines[i], "log");
        this.log("", "divider");

    },
    keyupHandler: function (e) {
        var code = e.keyCode;
        if (code != 9 && code != 13 && code != 38 && code != 40 && code != 27) {
            return DesktopConsole.commandTextHandler(e);
        }
    },
    keydownHandler: function (e) {
        var code = e.keyCode;
        if (code == 9 || code == 13 || code == 38 || code == 40 || code == 27) {
            return DesktopConsole.commandTextHandler(e);
        }
    },
    hexExpression: function (charindex, escapechar, quotelevel) {
        /* example for a plugin for Parser.escapeExpressions
         params:
         charindex:  position in parser.lineBuffer (escapechar)
         escapechar: escape character found
         quotelevel: current quoting level (quote char or empty)
         (quotelevel is not used here, but this is a general approach to plugins)
         the character in position charindex will be ignored
         the return value is added to the current argument
         */
        // convert hex values to chars (e.g. %20 => <SPACE>)
        if (!this.lineBuffer)
            return null;

        if (this.lineBuffer.length > charindex + 2) {
            // get next 2 chars
            var hi = this.lineBuffer.charAt(charindex + 1);
            var lo = this.lineBuffer.charAt(charindex + 2);
            lo = lo.toUpperCase();
            hi = hi.toUpperCase();
            // check for valid hex digits
            if ((((hi >= '0') && (hi <= '9')) || ((hi >= 'A') && ((hi <= 'F')))) &&
                    (((lo >= '0') && (lo <= '9')) || ((lo >= 'A') && ((lo <= 'F')))))
            {

                // next 2 chars are valid hex, so strip them from lineBuffer
                this._escExprStrip(this, charindex + 1, charindex + 3);

                // and return the char
                return String.fromCharCode(parseInt(hi + lo, 16));
            }
        }
        // if not handled return the escape character (=> no conversion)
        return escapechar;
    },
    _escExprStrip: function (termref, from, to) {
        // strip characters from termref.lineBuffer (for use with escape expressions)
        termref.lineBuffer =
                termref.lineBuffer.substring(0, from) +
                termref.lineBuffer.substring(to);
    },
    parseLine: function (lineBuffer) {
        this.lineBuffer = lineBuffer || "";
        // stand-alone parser, takes a Terminal instance as argument
        // parses the command line and stores results as instance properties
        //   argv:  list of parsed arguments
        //   argQL: argument's quoting level (<empty> or quote character)
        //   argc:  cursur for argv, set initinally to zero (0)
        // open quote strings are not an error but automatically closed.
        var argv = [''];     // arguments vector
        var argQL = [''];    // quoting level
        var argc = 0;        // arguments cursor
        var escape = false; // escape flag
        for (var i = 0; i < this.lineBuffer.length; i++) {
            var ch = this.lineBuffer.charAt(i);
            if (escape) {
                argv[argc] += ch;
                escape = false;
            }
            else if (this.escapeExpressions[ch]) {
                var v = this.escapeExpressions[ch](this, i, ch, argQL[argc]);
                if (typeof v != 'undefined')
                    argv[argc] += v;
            }
            else if (this.quoteChars[ch]) {
                if (argQL[argc]) {
                    if (argQL[argc] == ch) {
                        argc++;
                        argv[argc] = argQL[argc] = '';
                    }
                    else {
                        argv[argc] += ch;
                    }
                }
                else {
                    if (argv[argc] != '') {
                        argc++;
                        argv[argc] = '';
                        argQL[argc] = ch;
                    }
                    else {
                        argQL[argc] = ch;
                    }
                }
            }
            else if (this.whiteSpace[ch]) {
                if (argQL[argc]) {
                    argv[argc] += ch;
                }
                else if (argv[argc] != '') {
                    argc++;
                    argv[argc] = argQL[argc] = '';
                }
            }
            else if (this.singleEscapes[ch]) {
                escape = true;
            }
            else {
                argv[argc] += ch;
            }
        }

        if ((argv[argc] == '') && (!argQL[argc])) {
            argv.length--;
            argQL.length--;
        }

        this.argv = argv;
        this.argQL = argQL;
        this.argc = 0;
    },
    commandTextHandler: function (e)
    {
        var cmdBuffer = null, line = $(e.target).val(),
                idx = this.cmdHistory.indexOf(line),
                hisLength = this.cmdHistory.length,
                newVal = "",
                code = e.keyCode;

        if (cmdBuffer === null || (this.commandHistoryIndex == 0 && cmdBuffer !== line))
            cmdBuffer = line;

        this.parseLine(line);

        if (code == 38) { //UP
            //   if (this.$winHints.visible) {
            //this.selectHintUp();
            //   }
            //   else {
            if (!hisLength)
                return;
            newVal = this.cmdHistory[--this.commandHistoryIndex];
            if (this.commandHistoryIndex < 0)
                this.commandHistoryIndex = 0;
            if (newVal)
                $(e.target).val(newVal);
            //  }
            return false;
        }
        else if (code == 40) { //DOWN
            //    if (this.$winHints.visible) {
            //this.selectHintDown();
            //    }
            //     else {
            if (!hisLength)
                return;
            newVal = this.cmdHistory[++this.commandHistoryIndex] || "";//(++idx > hisLength - 1 || idx === 0) ? (cmdBuffer || "") :
            if (this.commandHistoryIndex >= this.cmdHistory.length)
                this.commandHistoryIndex = this.cmdHistory.length;
            $(e.target).val(newVal);
            //      }
            return false;
        }
        else if (code == 27 && this.$winHints.visible)
        {
            return; // this.hideHints();
        }
        else if (code != 13 && code != 9) {
            //this.autoComplete(e, parser, 2);
            return;
        }

        //if (this.$winHints.visible && selectedHint && hintNodes)
        //return this.hintClick(hintNodes[selectedHint]);

        if (this.argv.length === 0) {
            // no commmand line input
            if (e.name == "keydown") {
                this.log(this.getPrompt(), "prompt");
                //this.enable();
            }

            this.updateScroll('console', 'bottom');
        }
        else if (this.argQL[0]) {
            $('.console', this.consoleBase).trigger('click');
            // first argument quoted -> error
            this.write("Syntax error: first argument quoted.");

            this.updateScroll('console', 'bottom');
        }
        else {
            var s, cmd = this.argv[this.argc++];

            if (code == 9) {
                //this.autoComplete(e, parser, 1);
                return false;
            }

            this.commandHistoryIndex = this.cmdHistory.push(line);
            this.cmdBuffer = null;
            $(e.target).val(newVal);

            $('.console', this.consoleBase).trigger('click');

            switch (cmd) {
                case "help":
                    this.log(this.getPrompt() + " " + this.argv.join(" "), "prompt");
                    this.sendCommand(cmd);

                    break;
                case "clear":
                    this.consoleText.empty();
                    this.log(this.getPrompt() + " " + this.argv.join(" "), "prompt");

                    this.updateScroll('console', 'bottom');
                    break;
                case "sudo":

                    this.log(this.getPrompt() + " " + this.argv.join(" "), "prompt");
                    s = this.argv.join(" ").trim();

                    if (s == "sudo make me a sandwich") {
                        this.write("Okay.");

                        this.updateScroll('console', 'bottom');
                        break;
                    }

                    else if (s == "sudo apt-get moo") {
                        //this.clear();
                        this.write(["\n ",
                            "           (__)",
                            "           (oo)",
                            "     /------\\/ ",
                            "    / |    ||  ",
                            "   *  /\\---/\\  ",
                            "      ~~   ~~  ",
                            "  ....\"Have you mooed today?\"...",
                            " "]);
                        this.updateScroll('console', 'bottom');
                        break;
                    }
                    else {
                        this.write("Invalid operation ...\"" + this.argv[this.argc++] + "\"...");
                        this.updateScroll('console', 'bottom');
                        break;
                    }
                case "man":
                    var pages = {
                        "last": "Man, last night was AWESOME.",
                        "help": "Man, help me out here.",
                        "next": "Request confirmed; you will be reincarnated as a man next.",
                        "cat": "You are now riding a half-man half-cat."
                    };
                    this.log(this.getPrompt() + " " + this.argv.join(" "), "prompt");
                    this.write((pages[this.argv[this.argc++]]
                            || "Oh, I'm sure you can figure it out."));
                    this.updateScroll('console', 'bottom');
                    break;
                case "locate":
                    var keywords = {
                        "ninja": "Ninja can not be found!",
                        "keys": "Have you checked your coat pocket?",
                        "joke": "Joke found on user.",
                        "problem": "Problem exists between keyboard and chair.",
                        "raptor": "BEHIND YOU!!!"
                    };
                    this.log(this.getPrompt() + " " + this.argv.join(" "), "prompt");
                    this.write((keywords[this.argv[this.argc++]] || "Locate what?"));
                    this.updateScroll('console', 'bottom');
                    break;




                default:
                    var jokes = {
                        "make me a sandwich": "What? Make it yourself.",
                        "make love": "I put on my robe and wizard hat.",
                        "i read the source code": "<3",
                        "pwd": "You are in a maze of twisty passages, all alike.",
                        "lpr": "PC LOAD LETTER",
                        "hello joshua": "How about a nice game of Global Thermonuclear War?",
                        "xyzzy": "Nothing happens.",
                        //     "date": "March 32nd",
                        "hello": "Why hello there!",
                        "who": "Doctor Who?",
                        "su": "God mode activated. Remember, with great power comes great ... aw, screw it, go have fun.",
                        "fuck": "I have a headache.",
                        "whoami": "You are Richard Stallman.",
                        "nano": "Seriously? Why don't you just use Notepad.exe? Or MS Paint?",
                        "top": "\n" +
                                "                                    ¶¶¶¶¶¶¶¶¶¶¶¶ \n" +
                                "                                   ¶¶            ¶¶ \n" +
                                "                    ¶¶¶¶¶        ¶¶                ¶¶ \n" +
                                "                    ¶     ¶     ¶¶      ¶¶    ¶¶     ¶¶ \n" +
                                "                     ¶     ¶    ¶¶       ¶¶    ¶¶      ¶¶ \n" +
                                "                      ¶    ¶   ¶¶        ¶¶    ¶¶      ¶¶ \n" +
                                "                       ¶   ¶   ¶                         ¶¶ \n" +
                                "                     ¶¶¶¶¶¶¶¶¶¶¶¶                         ¶¶ \n" +
                                "                    ¶            ¶    ¶¶            ¶¶    ¶¶ \n" +
                                "                   ¶¶            ¶    ¶¶            ¶¶    ¶¶ \n" +
                                "                  ¶¶   ¶¶¶¶¶¶¶¶¶¶¶      ¶¶        ¶¶     ¶¶ \n" +
                                "                  ¶               ¶       ¶¶¶¶¶¶¶       ¶¶ \n" +
                                "                  ¶¶              ¶                    ¶¶ \n" +
                                "                   ¶   ¶¶¶¶¶¶¶¶¶¶¶¶                   ¶¶ \n" +
                                "                   ¶¶           ¶  ¶¶                ¶¶ \n" +
                                "                   ¶¶¶¶¶¶¶¶¶¶¶¶    ¶¶            ¶¶\n" +
                                "                                     ¶¶¶¶¶¶¶¶¶¶¶\n",
                        "moo": "moo",
                        "ping": "There is another submarine three miles ahead, bearing 225, forty fathoms down.",
                        "find": "What do you want to find? Kitten would be nice.",
                        "more": "Oh, yes! More! More!",
                        "your gay": "Keep your hands off it!",
                        "hi": "Hi.",
                        "echo": "Echo ... echo ... echo ...",
                        "bash": "You bash your head against the wall. It's not very effective.",
                        "ssh": "ssh, this is a library.",
                        "uname": "Illudium Q-36 Explosive Space Modulator",
                        "finger": "Mmmmmm...",
                        "kill": "Terminator deployed to 1984.",
                        "use the force luke": "I believe you mean source.",
                        "use the source luke": "I'm not luke, you're luke!",
                        "serenity": "You can't take the sky from me.",
                        "enable time travel": "TARDIS error: Time Lord missing.",
                        "ed": "You are not a diety."
                    };
                    s = this.argv.join(" ").trim();
                    if (jokes[s]) {
                        this.log(this.getPrompt() + " " + this.argv.join(" "), "prompt");
                        this.write(jokes[s]);
                        this.updateScroll('console', 'bottom');
                        break;
                    }
                    else
                    {
                        var data = {
                            command: cmd,
                            argv: this.argv,
                            line: line,
                            cwd: this.getCwd()
                        };

                        this.sendCommand(cmd, data);

                        /*
                         
                         ide.dispatchEvent("track_action", {
                         type: "console",
                         cmd: cmd
                         });
                         
                         if (ext.execCommand(cmd, data) !== false) {
                         if (ide.dispatchEvent("consolecommand." + cmd, {
                         data: data
                         }) !== false) {
                         if (!ide.onLine)
                         this.write("Cannot execute command. You are currently offline.");
                         else
                         ide.socket.send(JSON.stringify(data));
                         }
                         }
                         */

                        return;
                    }
            }
        }
    },
    sendCommand: function (cmd, _data)
    {
        var cacheCommands = false;

        if (cmd == 'help')
        {
            cacheCommands = true;
        }

        if (typeof _data != 'undefined')
        {

            _data.adm = 'console';
            _data.ajax = true;
            _data.cmd = cmd;
			_data.token = Config.get('token');
        }
        else
        {
            _data = {
                adm: 'console',
                'ajax': true,
                'cmd': cmd,
				token: Config.get('token')
            };
        }

        var self = this;

        $.post('admin.php', _data, function (data) {

            if (data.hasOwnProperty('commands') && cacheCommands)
            {
                //   self.write(  data.commands   );
            }

            if (data.hasOwnProperty('output'))
            {
                self.setCwd(data.cwd);
                self.log(self.getPrompt() + " " + (Tools.exists(_data, 'argv') ? _data.argv.join(" ") : ''), "prompt");
                self.write(data.output);
            }
            else if (data.hasOwnProperty('msg'))
            {
                self.write(data.msg);
            }
            else if (data.hasOwnProperty('offline'))
            {
                self.write("Cannot execute command. You are currently offline.");
            }
            else
            {
                self.write('Hmmm commandline error...');
            }

            self.updateScroll('console', 'bottom');

            // self.addScreenSaver();

        }, 'json');
    }






};
