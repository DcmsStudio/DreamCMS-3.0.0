var panelLogger = function (inst) {
    /**
     * Wire up events using specified WebSocket
     * @param {WebSocket} ws
     */
    this.connect = function (ws) {

        var instance = this;


        /**
         * @param {MessageEvent} messageEvent
         */
        ws.onmessage = function (data) {
            inst.logUpdater(data);
        };

        ws.onclose = function (e) {

        };

        ws.onerror = function (e) {
            Debug.log([e]);
        };
    };
};
var messengerLogger = function (inst) {


    this.instance = null;


    /**
     * Wire up events using specified WebSocket
     * @param {WebSocket} ws
     */
    this.connect = function (ws) {

        this.instance = this;


        /**
         * @param {MessageEvent} messageEvent
         */
        ws.onmessage = function (data) {
            inst.updateMessenger(data);
        };

        ws.onclose = function (e) {

        };

        ws.onerror = function (e) {
            Debug.log([e]);
        };
    };

    this.reconnect = function () {
        this.instance.reconnect();
    };
};


Desktop.Sidepanel = {
    isInited: false,
    isVisible: false,
    defaultPanel: 'documents',
    activePanel: 'documents',
    panel: null,
    panelTabs: null,
    currentPanelContainer: null,
    tabPanel: null,
    tabContents: null,
    desktopContainer: null,
    panelMinWidth: 250,
    panelMaxWidth: 500,
    panelWidth: null,
    lastWidth: 250,
    resizePanel: false,
    panelLogUpdateTimeout: 60000,
    panelMessengerUpdateTimeout: 20000,
    panelAnimationTime: 200,
    lastMessengerCount: 0,
    logger: null,
    messengerlogger: null,
    scrollbarOpts: {
        showArrows: false,
        scrollbarWidth: 10,
        arrowSize: 16
    },
    init: function ()
    {
        var self = this;
        if (this.isInited)
        {
            setTimeout(function () {
                self.bindEvents();
            }, 1400);

            return;
        }

        this.panel = $('#desktop-side-panel');
        this.panel.css({width: this.panelMinWidth, height: $(document).height() - $('#Taskbar').outerHeight(), top: $('#Taskbar').outerHeight(true), right: 0 - this.panelMinWidth}).hide();
        this.panelTabs = this.panel.find('div.tab-panel li');

        this.tabPanel = this.panel.find('div.tab-panel:first');
        this.tabContents = this.panel.find('>div:not(.tab-panel)');

        this.currentPanelContainer = $('#panel-' + this.defaultPanel);
        this.currentPanelContainer.show();

        this.desktopContainer = $('#desktop');
        this.tabContents.height(this.panel.height() - this.tabPanel.outerHeight(true));

        this.panelTabs.each(function () {

            if ($(this).attr('rel') == self.defaultPanel)
            {
                $(this).addClass('active');
            }
        });

        Desktop.Sidepanel.Tree.init($('#panel-documents'));
        Desktop.Sidepanel.Tree.build();

        this.isInited = true;
        this.initPanelLogUpdater();


        this.logger = new panelLogger(this);
        var ws = $.DcmsSocket("admin.php", {
            fallbackPollMethod: 'POST',
            fallbackPollInterval: this.panelLogUpdateTimeout,
            fallbackPollParams: {
                adm: 'logs',
                action: 'getpanellogs'
            }
        });
        this.logger.connect(ws);



        this.messengerlogger = new messengerLogger(this);
        var ws = $.DcmsSocket("admin.php", {
            fallbackPollMethod: 'POST',
            fallbackPollInterval: this.panelMessengerUpdateTimeout,
            fallbackPollParams: {
                adm: 'messenger',
                action: 'getnew'
            }
        });
        this.messengerlogger.connect(ws);


        setTimeout(function () {
            self.bindEvents();
            self.updatePanelSize();
            setTimeout(function () {
                self.enableScrollbar();
            }, 50);
        }, 100);

    },
    logUpdater: function (data) {

        var self = this, logContent = $('#lastlogs-content');

        if (logContent.find('ul').length == 0)
        {
            logContent.empty().append('<ul></ul>');
        }

        var ul = logContent.find('ul:first'), tmpUl = $('<ul>');

        for (var i = 0; i < data.logs.length; ++i)
        {
            var listTemplate = $('<li>');
            var item = data.logs[i];

            var tpl = '<div><span>IP:</span><span>' + item.ip + '</span></div>';
            tpl += '<div><span>Browser:</span><span>' + item.browser + '</span></div>';
            tpl += '<div><span>Message:</span><span>' + item.message + '</span></div>';

            var icon;
            switch (item.logtype)
            {
                case 'warn':
                case 'critical':
                    icon = 'critical';
                    break;
                case 'note':
                    icon = 'note';
                    break;
                case 'info':
                default:
                    icon = 'info';
                    break;
            }

            listTemplate.append('<span class="logicon logicon-' + icon + '"></span> ' + item.time + (item.username != '' && item.username != 0 && item.username != null ? ' - ' + item.username : ''));
            listTemplate.append('<div style="display:none">' + tpl + '</div>');
            tmpUl.append(listTemplate);
        }

        logContent.empty().append(tmpUl);
        logContent.find('li').bind('click', function () {
            logContent.find('li.active').removeClass('active').find('div:first').hide();
            $(this).addClass('active').find('div:first').slideDown(250);
            self.updatePanelSize();
            setTimeout(function () {
                self.enableScrollbar();
            }, 50);
        });

        self.updatePanelSize();

        setTimeout(function () {
            self.enableScrollbar();
        }, 50);
    },
    updateMessenger: function (data) {

        $('#desktop-side-panel .tab-panel li[rel=messages] .bubble').remove();

        if (data.result.length > 0) {
            $('#desktop-side-panel .tab-panel li[rel=messages]').append('<span class="bubble">' + data.result.length + '</span>');
        }
        else {

        }

        if (typeof Desktop.Sidepanel.lastMessengerCount == 'undefined')
        {
            Desktop.Sidepanel.lastMessengerCount = data.result.length;
            Tools.html5Audio('html/audio/Glass');
        }
        else {
            if (data.result.length > Desktop.Sidepanel.lastMessengerCount && Desktop.Sidepanel.lastMessengerCount != 0) {
                Desktop.Sidepanel.lastMessengerCount = data.result.length;

                // Play audio 
                Tools.html5Audio('html/audio/Glass');
                Notifier.info('Sie haben ' + (data.result.length - Desktop.Sidepanel.lastMessengerCount) + ' neue Nachrichten');
            }
            else if (data.result.length > Desktop.Sidepanel.lastMessengerCount && Desktop.Sidepanel.lastMessengerCount == 0) {
                Desktop.Sidepanel.lastMessengerCount = data.result.length;

                // Play audio 
                Tools.html5Audio('html/audio/Glass');
                Notifier.info('Sie haben ' + data.result.length + ' neue ungelesene Nachrichten');
            }
        }

        var messengerContent = $('#messages-content');


        if (messengerContent.length) {

            var lastActiveID = messengerContent.find('li.active').attr('id');
            if (messengerContent.find('ul').length == 0)
            {
                messengerContent.empty().append('<ul></ul>');
            }


            var self = this, tmpUl = $('<ul>');
            var DELAY = 500, clicks = 0, timer = null;

            for (var i = 0; i < data.result.length; ++i)
            {
                var item = data.result[i];
                var listTemplate = $('<li>').attr('id', 'msg-' + item.id);
                listTemplate.append('<div class="item"><span class="fromuser">Von: ' + item.username + '</span><span class="time">' + item.time + '</span><span class="title">' + item.subject + '</span></div><div class="message" style="display:none">' + item.message + '</div>');
                tmpUl.append(listTemplate);
            }

            messengerContent.empty().append(tmpUl);

            if (!data.result.length) {
                messengerContent.find('ul').empty().append('<li>Keine neuen Nachrichten vorhanden</li>');
            }
            else {



                messengerContent.find('li').data('clicks', 0).bind('click', function (e) {
                    var li = $(this);

                    clicks++;

                    if (clicks === 1) {
                        clearTimeout(timer);
                        timer = setTimeout(function () {
                            clicks = 0;
                            messengerContent.find('li.active').removeClass('active').find('div.message').hide();
                            li.addClass('active').find('div.message').slideDown(250, function () {
                                self.updatePanelSize();
                                self.enableScrollbar();
                            });
                        }, DELAY);
                    }
                    else {
                        clearTimeout(timer);
                        clicks = 0;

                        if ($('#LaunchPadCase_messenger').length && $('#desktop').find('div.isWindowContainer[app=messenger]').length == 0) {
                            var id = $(this).attr('id').replace('msg-', '');
                            Cookie.set('wg-read-messenger', id);
                            $('#LaunchPadCase_messenger').trigger('click');
                        }
                        else if ($('#desktop').find('div.isWindowContainer[app=messenger]').length == 1 && $('#desktop').find('div.isWindowContainer[app=messenger]').data('messenger')) {
                            var id = $(this).attr('id').replace('msg-', '');
                            $('#desktop').find('div.isWindowContainer[app=messenger]').data('WindowManager').focus();
                            $('#desktop').find('div.isWindowContainer[app=messenger]').data('messenger').readMessage(id);
                        }
                    }
                });


            }





            if (lastActiveID) {
                $('#' + lastActiveID).addClass('active').find('div.message').show()
            }




        }

    },
    initPanelLogUpdater: function ()
    {
        var self = this;
        $.post('admin.php', {adm: 'logs', action: 'getpanellogs'}, function (data) {
            if (Tools.responseIsOk(data))
            {
                if (data.logs) {
                    self.logUpdater(data);
                }
            }
        }, 'json');

    },
    scrollRemoved: false,
    updatePanelSize: function (removeScrollBar)
    {
        if (this.isInited)
        {
            if (removeScrollBar === true && !this.scrollRemoved)
            {
                this.scrollRemoved = true;
                this.tabContents.find('>div').each(function () {
                    if ($(this).is(':visible'))
                    {
                        var panel = $(this).attr('id').replace('panel-', '');
                        var self = this, scollTo = parseInt($('#panel-' + panel).attr('lastScrollPosY'));

                        //$('#panel-' + this.activePanel).jScrollPane(this.scrollbarOpts);
                        $(this).nanoScroller({scrollContent: $('#' + panel + '-content')});

                        if (scollTo > 0)
                        {
                            setTimeout(function () {
                                $(self).nanoScroller({'scrollTo': scollTo});
                            }, 50);
                        }
                    }
                });

            }

            this.panel.css({height: $(document).innerHeight() - $('#Taskbar').outerHeight(true)});
            this.tabContents.height($(document).innerHeight() - $('#Taskbar').outerHeight(true) - this.tabPanel.outerHeight(true) - 10).width(this.panel.width() - 10);
            this.tabContents.find('>div').height(this.tabContents.height()).width(this.tabContents.width() - 10);
        }
    },
    /***
     * Update only the Visible Content Tab Scrollbar
     * @returns {undefined}
     */
    updatePanelSizeStop: function ()
    {
        var self = this;
        if (this.isInited)
        {
            this.tabContents.find('>div').each(function () {
                if ($(this).is(':visible'))
                {
                    var panel = $(this).attr('id').replace('panel-', '');
                    var self = this, scollTo = parseInt($('#panel-' + panel).attr('lastScrollPosY'));

                    //$('#panel-' + this.activePanel).jScrollPane(this.scrollbarOpts);
                    $(this).nanoScroller({scrollContent: $('#' + panel + '-content')});

                    if (scollTo > 0)
                    {
                        setTimeout(function () {
                            $(self).nanoScroller({'scrollTo': scollTo});
                        }, 50);
                    }
                }
            });

            this.scrollRemoved = false;
        }
    },
    /**
     * Update only the Visible Content Tab Scrollbar
     * @returns {undefined}
     */
    updateScrollbar: function ()
    {
        if (this.isInited)
        {
            var self = this;
            var i = 0, length = this.tabContents.find('>div').length;

            this.tabContents.find('>div').each(function ()
            {
                if ($(this).is(':visible') && $(this).hasClass('scroll-content'))
                {
                    $(this).attr('lastScrollPosY', $(this).get(0).offsetTop);
                }
                i++;

                if (i + 1 >= length)
                {
                    self.updatePanelSizeStop();
                }
            });

        }

    },
    /**
     * 
     * @returns {undefined}
     */
    disableScrollbar: function ()
    {
        return;

        var self = this;

        if ($('#panel-' + this.activePanel).data('jsp')) {
            $('#panel-' + this.activePanel).attr('lastScrollPosY', $('#panel-' + this.activePanel).data('jsp').getContentPositionY());
        }

        setTimeout(function () {
            if ($('#panel-' + self.activePanel).data('jsp')) {
                $('#panel-' + self.activePanel).jScrollPaneRemove();
            }
        }, 80);
    },
    /**
     * 
     * @returns {undefined}
     */
    enableScrollbar: function ()
    {
        var self = this, scollTo = parseInt($('#panel-' + this.activePanel).attr('lastScrollPosY'));

        //$('#panel-' + this.activePanel).jScrollPane(this.scrollbarOpts);
        $('#panel-' + this.activePanel).nanoScroller({scrollContent: $('#' + this.activePanel + '-content')});

        if (scollTo > 0)
        {
            setTimeout(function () {
                $('#panel-' + this.activePanel).nanoScroller({'scrollTo': scollTo});
            }, 50);
        }

    },
    /**
     * 
     * @returns {undefined}
     */
    bindEvents: function ()
    {
        var self = this;

        $('#TaskbarShowDesktop').unbind('click');
        $('#TaskbarShowDesktop').bind('click', function () {
            if (!self.isVisible)
            {
                $(this).addClass('active');
                self.open();
            }
            else
            {
                self.close();
                $(this).removeClass('active');
            }
        });


        this.panelTabs.each(function () {
            $(this).unbind().click(function (e) {
                if ($(this).attr('rel') == self.activePanel)
                {
                    return;
                }

                $(this).parents('ul:first').find('li.active').removeClass('active');
                $(this).addClass('active');
                var rel = $(this).attr('rel');
                var doShow = $('#panel-' + rel);


                self.tabContents.find('>div').hide().each(function () {
                    doShow.show();
                    self.activePanel = rel;
                    self.currentPanelContainer = doShow;
                    self.enableScrollbar();
                });

            });
        });


        this.panel.filter(':ui-resizable').resizable('destroy');
        this.panel.resizable({
            handles: 'w',
            minWidth: this.panelMinWidth,
            maxWidth: this.panelMaxWidth,
            start: function ()
            {
                self.resizePanel = true;
                self.disableScrollbar();
            },
            resize: function (event, ui)
            {

                self.resizePanel = true;
                self.panelWidth = ui.size.width;

                $(this).css({left: '', width: ui.size.width});

                self.tabContents.width(ui.size.width);
                self.tabContents.find('>div').width(self.tabContents.innerWidth());

                $('#desktop-container').css({left: 0 - ui.size.width});

                Dock.updateDockPos(ui.size.width);
            },
            stop: function (event, ui)
            {
                self.lastWidth = ui.size.width;
                self.panelWidth = ui.size.width;
                $(this).css({left: '', width: ui.size.width});
                $('#desktop-container').css({left: 0 - ui.size.width});

                self.tabContents.width(ui.size.width - 10);
                self.tabContents.find('>div').width(self.tabContents.innerWidth() - 10);

                Dock.updateDockPos(ui.size.width);

                setTimeout(function () {
                    $('#desktop-container').css({left: 0 - ui.size.width});
                    self.enableScrollbar();
                    self.resizePanel = false;
                }, 50);


            }
        });


    },
    open: function ()
    {
        var self = this;

        $('body').addClass('auth');

        if ($('#desktop-container').offset().left == 0)
        {
            this.panel.css({
                minWidth: '',
                opacity: 1,
                width: this.lastWidth,
                height: $(document).height() - $('#Taskbar').outerHeight(),
                top: $('#Taskbar').outerHeight(true),
                right: 0 - this.lastWidth
            }).show();
            
           // this.panel.stop();
            
          //  $('#desktop-bg-container,#desktop').stop();

            var $d = $('#desktop-container,#dock');
            var $bg = $('#desktop-bg-container');

            self.tabContents.width(self.lastWidth - 10);
            self.tabContents.find('>div').width(self.lastWidth - 10);


            setTimeout(function () {

                $d.animate({left: '-=' + self.lastWidth}, {
                    duration: self.panelAnimationTime,
                    complete: function () {
                        $(this).css({left: 0 - self.lastWidth});
                    }
                });

                self.panel.animate({right: 0}, {
                    duration: self.panelAnimationTime,
                    complete: function () {

                        self.panelWidth = self.lastWidth;
                        self.tabContents.width(self.lastWidth - 10);
                        self.tabContents.find('>div').width(self.lastWidth - 10);
                        self.enableScrollbar();
                        Dock.updateDockPos(self.lastWidth);
                        $(this).resizable('enable');
                        self.isVisible = true;
                    }
                });


            }, 50);

        }
    },
    close: function ()
    {
        var self = this;

        $('#desktop-container').stop().animate({left: 0}, {duration: self.panelAnimationTime});
        $('#dock').stop().animate({left: '+=' + this.panel.outerWidth()}, {duration: self.panelAnimationTime});

        this.panel.stop().animate({
            right: 0 - self.lastWidth,
        }, {
            duration: self.panelAnimationTime,
            complete: function () {
                self.panelWidth = 0;
                $(this).resizable('disable').hide();
                $('body').removeClass('auth');
                self.isVisible = false;
                Dock.updateDockPos();
            }
        });
    }

};