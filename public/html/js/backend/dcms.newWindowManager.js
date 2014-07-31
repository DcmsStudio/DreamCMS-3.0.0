var Win = {
    windowID: null
};
String.prototype.hashCode = function () {
    var hash = 0, len = this.length;
    if (len === 0)
        return hash;
    for (var i = 0; i < len; ++i) {
        char = this.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash; // Convert to 32bit integer
    }
    return hash;
};
(function (jQuery) {
    $.WindowManager = function (el, options) {

        // To avoid scope issues, use 'base' instead of 'this'
        // to reference this class from internal events and functions.
        var self = wm = this;
        var windowOpenAnimationTime = 400;
        var windowCloseAnimationTime = 250;
        var windowRestoreAnimationTime = 500;
        var windowMinAnimationTime = 250;
        var windowMaxAnimationTime = 300;



        // Access to jQuery and DOM versions of element
        wm.$el = $(el);
        wm.el = el;
        wm.OriginalEl = $(el).clone();
        if (typeof options.WindowID == 'undefined' && wm.$el.attr('id') != '') {
            options.WindowID = wm.$el.attr('id');
        }


        wm.windowOpenAnimationTime = 350;
        wm.windowCloseAnimationTime = 250;
        wm.windowRestoreAnimationTime = 350;
        wm.windowMinAnimationTime = 200;
        wm.windowMinAnimationTime = 300;

        wm.isResizeing = false;
        wm.win = null;
        wm.TitleBar = null;
        wm.Toolbar = null;
        wm.Body = null;
        wm.BodyContent = null;
        wm.Content = null;
        wm.Statusbar = null;
        wm.WindowPanelLeft = null;
        wm.WindowPanelRight = null;
        wm.state = '';
        wm.activeWindowZIndex = 1000,
                wm.WindowZIndex = 1000;
        wm.WindowMinZIndex = 1000;
        wm.stopWinContentChangeTrigger = true;
        wm.settings = {};
        wm.restoreSettings = {};
        wm.baseSize = null;
        wm.lastFocusedWindowID = null;


        function cleanUrl (str) {
            str = str.replace(/^http(s):\/\//i, '#');
            str = str.replace(/&amp;/, '&');
            // str = str.replace(/&amp;/, '&');

            var strArr = str.split('/');
            var tmp = strArr.shift();
            if (strArr[0] !== '#') {
                strArr.unshift(tmp);
            }

            return strArr.join('/').replace(/^#/, '');
        }
        ;


        wm.init = function () {

            if ($('body').data('WindowZIndex') === null || $('body').data('WindowZIndex') === undefined) {
                $('body').data('WindowZIndex', this.WindowZIndex);
            }

            if ($('body').data('Windows') === null || $('body').data('Windows') === undefined) {
                $('body').data('Windows', []);
            }

            if ($('body').data('FocusWindow') === null || $('body').data('FocusWindow') === undefined) {
                $('body').data('FocusWindow', false);
            }


            this.lastFocusedWindowID = $('body').data('FocusWindow');

            this.unfocusOthers();
            document.body.style.cursor = 'progress';

            this.settings = $.extend({}, $.WindowManager.defaultOptions, options);
            this.settings.isActive = false;

            if ((!Tools.isString(this.settings.WindowID) && this.settings.isSingleWindow === false) && (!Tools.isString(this.settings.Url) || this.settings.Url === '')) {
                document.body.style.cursor = 'default';
                console.error('Please set a ID for the window, before create the window!');
                return false;
            }

            if ((!this.settings.Controller || !this.settings.Action)) {
                var appInfo = Tools.extractAppInfoFromUrl(this.settings.Url);
                if (!this.settings.Controller && typeof appInfo.controller === 'string') {
                    this.settings.Controller = appInfo.controller;
                }

                if (!this.settings.Action && typeof appInfo.action === 'string') {
                    this.settings.Action = appInfo.action;
                }
            }

            if (!Tools.isString(this.settings.WindowID) && !Tools.isString(this.settings.Url)) {
                document.body.style.cursor = 'default';
                console.error('Please set a ID for the window, before create the window!');
                return false;
            }



            this.settings.urlClean = null;
            if (!Tools.isString(this.settings.WindowID)) {
                this.settings.urlClean = cleanUrl(this.settings.Url);
                this.settings.WindowID = 'w' + md5(this.settings.urlClean).substr(0, 12);
            }

            Win.windowID = this.settings.WindowID;
            this.id = this.settings.WindowID;


            if (this.settings.UseTaskbar) {
                this.settings.TaskbarHeight = $('#Taskbar').outerHeight();
            }

            if (!this.settings.WindowContent) {
                this.settings.WindowContent = $('#' + this.id).html();
            }


            if (this.settings.Controller && $('.isWindowContainer[app=' + this.settings.Controller + ']').length === 0) {
                this.settings.isRootApplication = true;
            }



            if (this.settings.isSingleWindow === false || this.settings.isRootApplication === true || (this.settings.isSingleWindow != false && this.settings.singleWindow === null)) {
                if ($('#' + this.id).length && $('#' + this.id).data('WindowManager')) {
                    document.body.style.cursor = 'default';
                    $('#' + this.id).trigger('click');
                    return;
                }

                this.activeWindowZIndex = $('body').data('activeWindowZIndex');
                this.WindowZIndex = $('body').data('WindowZIndex');

                this.settings.originalTitle = this.settings.WindowTitle;

                var $win = buildWindow();

                var winWidth = (this.settings.minWidth > this.settings.Width ? this.settings.minWidth : this.settings.Width);
                var winHeight = (this.settings.minHeight > this.settings.Height ? this.settings.minHeight : this.settings.Height);

                $('#' + this.id).attr('class', 'isWindowContainer ' + this.settings.Skin + 'Container active loading').css({
                    visibility: 'hidden',
                    opacity: '0',
                    //  zIndex: this.WindowZIndex
                }).height(winHeight).width(winWidth);

                if (this.settings.Controller != null) {
                    $('#' + this.id).attr('app', this.settings.Controller);
                }


                if (this.settings.nopadding) {
                    $('#' + this.id).addClass('no-padding');
                }


                if (this.settings.Controller === 'plugin') {
                    var pluginName = $.getURLParam('plugin', this.settings.Url);
                    if (pluginName) {
                        $('#' + this.id).attr('app', pluginName);
                    }
                }


                $('#' + this.id).attr('activespace', Desktop.currentSpace).attr('uniqueid', new Date().getTime());


                // Add a reverse reference to the DOM object
                //$win.data("WindowManager", this);

                $('#' + this.id).data("WindowManager", this).append($win);


                //    $win.insertBefore(this.$el);
                //     var $_w = this.$el.prev();
                //     this.$el.remove();
                //     this.$el = null;
                //     this.$el = $_w;
                // ------------------------------------
                //this.$el.replaceWith($win);

                //this.$el = $($win); // refresh el
                this.win = this.$el;
                this.el = this.$el.get(0); // refresh el


                //
                //
                // ------------------------------------

                if (this.settings.rollback === true) {
                    this.win.attr('rollback', true);
                }


                // $('body').data('WindowZIndex', this.WindowZIndex );

                var wins = $('body').data('Windows');
                if (typeof wins != 'undefined') {
                    wins.push(this.id);
                }
                else {
                    wins = [this.id];
                }

                $('body').data('Windows', wins);
                this.TitleBar = $('.window-tl', this.$el);
                this.Toolbar = $('#toolbar-' + this.id);
                this.Body = $('#body-' + this.id);
                this.BodyContent = $('#body-content-' + this.id);
                this.Content = $('.win-content:first', this.BodyContent);
                this.Statusbar = $('#statusbar-' + this.id);
                this.WindowPanelLeft = $('#panel-left-' + this.id);
                this.WindowPanelRight = $('#panel-right-' + this.id);
                this.WindowWrapper = $('#wrapper-' + this.id);

                $('#pane-toggle-left-' + this.id).on('click', function () {
                    self.toggleWindowPane('left');
                });

                $('#pane-toggle-right-' + this.id).on('click', function () {
                    self.toggleWindowPane('right');
                });

                // Buttons
                this.closeBtn = $('.win-close-btn', this.$el);
                this.minBtn = $('.win-min-btn', this.$el);
                this.maxBtn = $('.win-max-btn', this.$el);
                this.fullscreenBtn = $('.win-fullscreen-btn', this.$el);

                if (this.Toolbar.find('#window-nav').length === 0 && this.settings.isSingleWindow) {
                    var historyTpl = $('<div id="window-nav"><button type="button" class="switchHome"><span></span></button> <button type="button" class="switchLeft"><span></span></button> <button type="button" class="switchRight"><span></span></button></div>');
                    historyTpl.show();
                    this.Toolbar.prepend(historyTpl).show();
                    this.Toolbar.css({
                        visibility: ''
                    }).find('.root:first').attr('id', this.id + '-toolbar').show();
                    // var doBodyHeight = $(this.win).height() - this.getHeaderHeight() - this.getStatusbarHeight();
                    // this.BodyContent.height(doBodyHeight);
                    this.TitleBar.find('.no-toolbar').removeClass('no-toolbar');
                    bindSingleWindowSwitchButtons();
                }

                if (this.settings.versioning !== false && this.settings.versioning !== null) {
                    var versHtml = $('<div class="versioning-container"/>');
                    versHtml.html(this.settings.versioning);
                    versHtml.append('<span class="button-group-label">' + cmslang.versions + '</span>');
                    this.Toolbar.append(versHtml);
                }



                //   $('body').data('FocusWindow', false);
                this.settings.isActive = false;

//                this.focus();
                //this.$el.trigger('click');



                if (typeof this.settings.onAfterCreated === 'function') {
                    this.settings.status = 'restore';
                    this.settings.onAfterCreated(this, function (_wm) {
                        getCallBack(_wm);
                    });
                }
                else {
                    this.settings.status = 'restore';
                    getCallBack();
                }
            }
            else if (this.settings.isRootApplication !== true && this.settings.isSingleWindow !== false && this.settings.singleWindow !== null) {
                if (typeof this.settings.singleWindow.createSingleWindow !== 'function') {
                    var w = $(this.settings.singleWindow).data('WindowManager');
                    if (w) {
                        w.createSingleWindow(this.settings);
                        return;
                    }
                }
                else if (typeof this.settings.singleWindow.createSingleWindow === 'function') {
                    this.settings.singleWindow.createSingleWindow(this.settings);
                    return;
                }

                document.body.style.cursor = 'default';
                console.error('Could not create the Single Window! Instance for the root window not exists!');
            }
            else {
                document.body.style.cursor = 'default';
                console.error('Could not create the Window! You will create a undefined Window??? Please check the window config.');
            }


        };




        /**
         * file selector sub slide window
         * 
         */
        wm.toggleFileSelectorPanel = function (show) {
            if (this.settings.addFileSelector) {
                var self = this;

                if (!show) {

                    var h = this.$el.find('#fm-slider').height();
                    $('#body-' + this.id).css({
                        overflow: 'hidden'
                    });
                    this.$el.find('#fm-slider').animate({
                        marginTop: 0 - h
                    }, {
                        queue: false,
                        duration: 350,
                        complete: function () {
                            $(this).hide(); //.find('div:first').empty();
                        }
                    });

                }
                else {
                    $('#body-' + this.id).css({
                        overflow: 'hidden'
                    });


                    this.$el.find('#fm-slider').show();

                    this.$el.find('#fm-slider').css({
                        top: 0,
                        marginTop: 0 - this.$el.find('#fm-slider').height()
                    }).height($('#body-' + this.id).height() - 40).show();

                    var $fm = this.$el.find('#fm');
                    $fm.find('.treelistInner,.body').css({overflow: ''});
                    $fm.resizePanels(false);


                    this.$el.find('#fm-slider').animate({
                        marginTop: 0
                    }, {
                        queue: true,
                        duration: 350,
                        complete: function () {

                            $('#body-' + self.id).css({
                                overflow: 'visible'
                            });

                            var $fm = $(this).find('#fm');

                            setTimeout(function () {

                                $fm.resizePanels(function () {
                                    $fm.find('.treelistInner,.body').css({overflow: ''});
                                    Tools.scrollBar($fm.find('.treelistInner'));
                                    Tools.scrollBar($fm.find('.listview .body>:first-child'));
                                    Tools.scrollBar($fm.find('iconview.body'));

                                    setTimeout(function () {
                                        $fm.fixTableWidth();
                                    }, 50);
                                });
                            }, 10);
                        }
                    });
                }
            }
        };

        function buildFileSelectorPanel () {
            if (self.settings.addFileSelector) {
                if ($('#LaunchPadCase_fileman').length == 1) {
                    var width = (self.$el.width() - 50);
                    var height = self.$el.find('.window-body-content').height() - 40;
                    self.$el.addClass('use-overflow');
                    var l, container = $('<div id="fm-slider" class="inline-window-slider" style="position:absolute;"></div>');
                    container.height(height).width(width);


                    if (self.$el.width() > width) {
                        l = ((self.$el.width() - width) / 2);
                    }
                    else {
                        l = 0 - ((width - self.$el.width()) / 2);
                    }

                    container.css({
                        left: l,
                        marginTop: (0 - height)
                    });

                    container.append($('<div style="padding:0">'));
                    container.hide();

                    $('#body-' + self.id).append(container);

                    var itemData = $('#LaunchPadCase_fileman').data('itemData');
                    if (typeof itemData.selectFile == 'undefined') {
                        itemData.selectFile = true;
                    }
                    itemData.url = itemData.url + '&mode=fileselector';


                    /**
                     * 
                     * 
                     */
                    if (self.$el.find('#fm-slider>div>div:first').length == 0) {

                        $.ajax({
                            url: itemData.url,
                            type: 'GET',
                            dataType: 'json',
                            timeout: 10000,
                            data: {},
                            async: false,
                            global: false,
                            success: function (data)
                            {
                                if (Tools.responseIsOk(data))
                                {
                                    container.find('div:first').append(data.maincontent);
                                    container.height(height).show();
                                    container.find('#fm').Filemanager({
                                        isInlineFileman: true,
                                        connectorUrl: 'admin.php?adm=fileman',
                                        //      mode: '{$fm.mode}',
                                        dirSep: '/',
                                        toolbarContainer: container.find('#fm-toolbar'),
                                        externalScrollbarContainer: '.pane',
                                        scrollTo: function (c, toObject)
                                        {
                                            if (c === 'tree')
                                            {
                                                Tools.scrollBar(container.find('#fm .treelistInner'), toObject);
                                            }
                                        },
                                        externalScrollbarDestroy: function ()
                                        {

                                        },
                                        externalScrollbarCreate: function ()
                                        {
                                            var fm = container.find('#fm');

                                            fm.find('.treelistInner,.body').css({overflow: ''});
                                            Tools.scrollBar(fm.find('.treelistInner'));
                                            Tools.scrollBar(fm.find('.listview .body>:first-child'));
                                            Tools.scrollBar(fm.find('.iconview.body'));

                                            setTimeout(function ()  {
                                                //if ($('#fm .foldercontentInner .body', _win).hasClass('jspScrollable'))
                                                // {
                                                fm.fixTableWidth();
                                                // }
                                            }, 80);

                                        },
                                        onResizeStart: function ()
                                        {
                                            var fm = container.find('#fm');
                                            fm.find('.treelistInner,.body').css({width: '', overflow: ''});

                                        },
                                        onResizeStop: function ()
                                        {
                                            var fm = container.find('#fm');

                                            fm.resizePanels(function () {

                                                fm.find('.treelistInner,.body').css({overflow: ''});
                                                Tools.scrollBar(fm.find('.treelistInner'));
                                                Tools.scrollBar(fm.find('.listview .body>:first-child'));
                                                Tools.scrollBar(fm.find('.iconview.body'));

                                                setTimeout(function ()  {
                                                    fm.fixTableWidth();
                                                }, 80);

                                            });
                                        },
                                        onBeforeLoad: function ()
                                        {

                                        },
                                        onAfterLoad: function ()
                                        {
                                            var fm = container.find('#fm');
                                            fm.find('.treelistInner,.body').css({overflow: ''});
                                            Tools.scrollBar(fm.find('.treelistInner'));
                                            Tools.scrollBar(fm.find('.listview .body>:first-child'));
                                            Tools.scrollBar(fm.find('.iconview.body'));

                                            setTimeout(function ()  {
                                                fm.resizePanels(function () {
                                                    fm.find('.treelistInner,.body').css({overflow: ''});
                                                    Tools.scrollBar(fm.find('.treelistInner'));
                                                    Tools.scrollBar(fm.find('.listview .body>:first-child'));
                                                    Tools.scrollBar(fm.find('.iconview.body'));

                                                    setTimeout(function ()  {
                                                        fm.fixTableWidth();
                                                    }, 80);
                                                });
                                            }, 50);



                                        }
                                    });

                                    container.height(0).hide();

                                    setTimeout(function () {
                                        container.resizable({
                                            handles: 's',
                                            minHeight: self.$el.find('.window-body-content').height() - 100,
                                            maxHeight: self.$el.find('.window-body-content').height() + 200,
                                            autoHide: true,
                                            start: function () {
                                                self.$el.find('#fm div.header th,#fm div.body tr:first td').attr('style', '');
                                            },
                                            resize: function () {
                                                self.$el.find('#fm').resizePanels(false);
                                            },
                                            stop: function () {
                                                var fm = self.$el.find('#fm');
                                                var h = self.$el.find('.window-body-content').height() - 40;
                                                var l, sw = self.$el.find('#fm-slider').width(), elwidth = self.$el.width();


                                                if (sw > elwidth) {
                                                    sw = (elwidth - 50);
                                                    self.$el.find('#fm-slider').width(sw);
                                                }
                                                else {
                                                    if (sw < (elwidth - 50)) {
                                                        self.$el.find('#fm-slider').width((elwidth - 50));
                                                    }
                                                }

                                                if (elwidth > sw) {
                                                    l = ((elwidth - sw) / 2);
                                                }
                                                else {
                                                    l = 0 - ((sw - elwidth) / 2);
                                                }



                                                self.$el.find('#fm-slider').css({'left': l /*, height: h*/});
                                                setTimeout(function () {
                                                    fm.resizePanels(function ()
                                                    {
                                                        fm.find('.treelistInner,.body').css({overflow: ''});
                                                        Tools.scrollBar(fm.find('.treelistInner'));
                                                        Tools.scrollBar(fm.find('.listview .body>:first-child'));
                                                        Tools.scrollBar(fm.find('iconview.body'));

                                                        setTimeout(function () {
                                                            fm.fixTableWidth();
                                                        }, 80);
                                                    });
                                                }, 10);
                                            }
                                        });

                                        // register cancel button
                                        container.find('#cancel-fm').on('click', function (e) {
                                            self.toggleFileSelectorPanel(false);
                                        });

                                        self.set('onResizeFS', function (event, ui, wm, sizes) {
                                            var l, sw = self.$el.find('#fm-slider').width();


                                            if (sw > sizes.width) {
                                                sw = (sizes.width - 50);
                                                self.$el.find('#fm-slider').width(sw);
                                            }
                                            else {
                                                if (sw < (sizes.width - 50)) {
                                                    self.$el.find('#fm-slider').width((sizes.width - 50));
                                                }
                                            }

                                            if (sizes.width > sw) {
                                                l = ((sizes.width - sw) / 2);
                                            }
                                            else {
                                                l = 0 - ((sw - sizes.width) / 2);
                                            }


                                            //     var h = self.$el.find('.window-body-content').height() - 40;


                                            self.$el.find('#fm-slider').css({'left': l /*, height: h*/});
                                            self.$el.find('#fm').resizePanels(false);
                                        });

                                        self.set('onResizeStartFS', function () {
                                            self.$el.find('#fm div.header th,#fm div.body tr:first td').attr('style', '');
                                        });

                                        self.set('onResizeStopFS', function (event, ui, wm, sizes) {
                                            var fm = self.$el.find('#fm');
                                            var l, sw = self.$el.find('#fm-slider').width();


                                            if (sw > sizes.width) {
                                                sw = (sizes.width - 50);
                                                self.$el.find('#fm-slider').width(sw);
                                            }
                                            else {
                                                if (sw < (sizes.width - 50)) {
                                                    self.$el.find('#fm-slider').width((sizes.width - 50));
                                                }
                                            }

                                            if (sizes.width > sw) {
                                                l = ((sizes.width - sw) / 2);
                                            }
                                            else {
                                                l = 0 - ((sw - sizes.width) / 2);
                                            }

                                            self.$el.find('#fm-slider').css({'left': l/*, height: h*/});
                                            setTimeout(function () {
                                                fm.resizePanels(function ()
                                                {
                                                    fm.find('.treelistInner,.body').css({overflow: ''});
                                                    Tools.scrollBar(fm.find('.treelistInner'));
                                                    Tools.scrollBar(fm.find('.listview .body>:first-child'));
                                                    Tools.scrollBar(fm.find('iconview.body'));

                                                    setTimeout(function () {
                                                        fm.fixTableWidth();
                                                    }, 80);
                                                });
                                            }, 10);
                                        });

                                    }, 100);

                                }
                            }
                        });

                    }
                    else {
                        container.show();
                    }

                }
            }
        }
        ;

        // End file selector sub slide window




        wm.toggleWindowPane = function (pos) {
            var button, panel, panelWidth;
            if (pos == 'right') {
                button = $('#pane-toggle-right-' + this.id);
                panel = this.WindowPanelRight;
                panelWidth = panel.outerWidth(true);
                if (panel.is(':visible')) {


                    panel.stop(true, true).animate({right: 0}, {
                        queue: true,
                        duration: 350,
                        complete: function () {
                            $(this).hide();
                            button.removeClass('open');
                        }
                    });


                    if (this.settings.status == 'max') {
                        this.win.stop(true, true).animate({width: '+=' + panel.outerWidth(true), overflow: 'visible'}, {
                            queue: false,
                            duration: 350
                        });
                        this.BodyContent.stop(true, true).animate({width: '+=' + panel.outerWidth(true)}, {
                            queue: false,
                            duration: 350
                        });
                    }
                }
                else {
                    button.addClass('open');

                    panel.css({right: 0}).show();
                    panel.stop(true, true).delay(10).animate({right: '-=' + panel.outerWidth(true)}, {
                        duration: 350,
                        queue: true,
                        complete: function () {
                            Tools.scrollBar($(this).find('.window-panel-content:first'));
                        }
                    });

                    if (this.settings.status == 'max') {
                        this.win.stop(true, true).animate({width: '-=' + panel.outerWidth(true), overflow: 'visible'}, {
                            queue: true,
                            duration: 350
                        });
                        this.BodyContent.stop(true, true).animate({width: '-=' + panel.outerWidth(true)}, {
                            queue: true,
                            duration: 350
                        });
                    }
                }
            }
            else if (pos == 'left') {
                button = $('#pane-toggle-left-' + this.id);
                panel = this.WindowPanelLeft;
                panelWidth = panel.outerWidth(true);
                if (panel.is(':visible')) {
                    if (this.settings.status == 'max') {
                        this.BodyContent.stop(true, true).animate({width: '+=' + panel.outerWidth(true)}, 150);
                        this.$el.stop(true, true).animate({left: 0, width: '+=' + panel.outerWidth(true)}, 150);
                    }

                    panel.stop(true, true).animate({left: 0}, 150, function () {
                        $(this).hide();
                        button.removeClass('open');
                    });
                }
                else {
                    button.addClass('open');
                    if (this.settings.status == 'max') {
                        this.BodyContent.stop(true, true).animate({width: '-=' + panel.outerWidth(true)}, 150);
                        this.$el.stop(true, true).animate({left: panel.outerWidth(true), width: '-=' + panel.outerWidth(true)}, 150);
                    }

                    panel.css({left: 0}).show().stop(true, true).animate({left: 0 - panel.outerWidth(true)}, 150, function () {
                        Tools.scrollBar($(this).find('.window-panel-content:first'));
                    });
                }
            }
        };

        wm.getWindowPosRight = function () {
            return this.win.offset().left + this.win.outerWidth(true);
        };

        function getCallBack (_wm) {

            if (!self.settings.nopadding) {
                self.Content.addClass('add-padding');
            }

            if (self.settings.hasGridAction === true) {
                self.TitleBar.find('.no-toolbar').removeClass('no-toolbar');
            }

            // set window sizes
            initWindowSizes();
            self.stopWinContentChangeTrigger = true;

            // init all window Events
            initWindowResizeDragging();
            initButtonEvents();

            self.firstRun = true;

            setTimeout(function () {
                self.focus(null, self.id);

                // show the window
                animateWindowView('show');

                initFocusAndBlur();
                self.Toolbar.trigger('changeHeight');
                self.stopWinContentChangeTrigger = false;
                document.body.style.cursor = 'default';
                updateWindowSizeCache(true);
            }, 50);


        }
        ;

        wm.enableLoading = function () {
            this.win.addClass('loading');
        };

        wm.disableLoading = function () {
            this.win.removeClass('loading');
        };

        function initWindowSizes () {
            wm.win.css({
                visibility: 'hidden'
            });
            var bodyWidth = (wm.settings.minWidth > wm.settings.Width ? wm.settings.minWidth : wm.settings.Width);
            var winContentHeight = (wm.settings.minHeight > wm.settings.Height ? wm.settings.minHeight : wm.settings.Height);
            wm.win.width(bodyWidth);
            wm.BodyContent.height(winContentHeight);
            wm.win.css({
                visibly: ''
            });
        }
        ;

        function initFocusAndBlur () {

            self.$el.on('click.windows', function (e) {
                self.focus(e, self.id);
                $('body').data('FocusWindow', self.id);

            }).on('mousedown.windows', function (e) {
                self.focus(e, self.id);
                $('body').data('FocusWindow', self.id);
            });

            self.Toolbar.on('changeHeight', function (e) {
                if ($(this).height() <= 10) {
                    self.TitleBar.find('.window-tc').addClass('no-toolbar');
                }
                else {
                    self.TitleBar.find('.window-tc').removeClass('no-toolbar');
                }
            });



            /*
             * Trigger window changes
             */
            self.$el.triggerHandler('winContentChange');
            self.$el.on('winContentChange', function (e) {
                if (self.settings.enableContentScrollbar && !self.stopWinContentChangeTrigger) {
                    //self.disableWindowScrollbar();
                    var doBodyHeight = $(self.win).height() - self.getHeaderHeight() - self.getStatusbarHeight();
                    self.BodyContent.height(doBodyHeight);
                    self.enableWindowScrollbar();
                }
            });
        }
        ;

        /**
         * Set focus to the current window and reset focus to other
         * active windows
         */
        wm.focus = function (e, setFocusWinID) {
            var setZindex = 0, focusedWinID = $('body').data('FocusWindow');

            if (focusedWinID == this.id) {
                return;
            }

            var activeWindowZIndex = parseInt($('body').data('activeWindowZIndex'), 10);
            var lastWindowZindex = parseInt($('body').data('WindowZIndex'), 10);

            if (setFocusWinID) {

                if (this.lastFocusedWindowID && !focusedWinID) {
                    focusedWinID = this.lastFocusedWindowID;
                }

                if (focusedWinID) {
                    var activeWindow = $('#' + focusedWinID);
                    var thisWindowZindex = parseInt($('#' + setFocusWinID).css('zIndex'), 10);

                    if (activeWindow) {

                        if (activeWindowZIndex > 100 && thisWindowZindex > 100) {
                            setZindex = activeWindowZIndex;

                            activeWindow
                                    .css('zIndex', thisWindowZindex)
                                    .removeClass('active');
                            activeWindow.data("WindowManager").set('isActive', false);

                            if ($('#' + setFocusWinID).length) {
                                $('#' + setFocusWinID)
                                        .css('zIndex', activeWindowZIndex)
                                        .addClass('active');
                                $('#' + setFocusWinID).data("WindowManager").settings.isActive = true;
                            }

                        }
                        else if (activeWindowZIndex > 100 && thisWindowZindex < 100) {
                            setZindex = activeWindowZIndex + 1;

                            activeWindow
                                    .removeClass('active');
                            activeWindow.data("WindowManager").set('isActive', false);

                            if ($('#' + setFocusWinID).length) {
                                $('#' + setFocusWinID)
                                        .css('zIndex', setZindex)
                                        .addClass('active');
                                $('#' + setFocusWinID).data("WindowManager").set('isActive', true);
                            }
                            $('body').data('WindowZIndex', setZindex);

                        }
                        else {
                            setZindex = (activeWindowZIndex > 100 ? activeWindowZIndex + 1 : lastWindowZindex + 1);

                            if ($('#' + setFocusWinID).length) {
                                $('#' + setFocusWinID)
                                        .css('zIndex', setZindex)
                                        .addClass('active');
                                $('#' + setFocusWinID).data("WindowManager").set('isActive', true);
                            }

                            $('body').data('WindowZIndex', lastWindowZindex + 1);

                        }

                        $('body').data('activeWindowZIndex', setZindex);

                    }
                }
                else {

                    // the first window

                    if ($('#' + setFocusWinID).length) {
                        $('#' + setFocusWinID)
                                .css('zIndex', lastWindowZindex)
                                .addClass('active');
                        $('#' + setFocusWinID).data("WindowManager").set('isActive', true);
                    }



                    $('body').data('activeWindowZIndex', lastWindowZindex);
                    $('body').data('WindowZIndex', lastWindowZindex);

                }

            }

            $('body').data('FocusWindow', setFocusWinID);

            if (typeof this.settings.onFocus === 'function') {
                this.settings.onFocus(e, this);
            }
        };


        /**
         * Set the current window unfocused
         */
        wm.unfocus = function () {

            var focusedWinID = $('body').data('FocusWindow');
            if (this.settings.isActive === true || focusedWinID === this.id) {

                if (typeof this.settings.onUnFocus === 'function') {
                    var self = this;
                    this.settings.onUnFocus(null, this, function () {
                        $('body').data('FocusWindow', false);
                        self.settings.isActive = false;
                        self.$el.removeClass('active');
                        $('body').data('activeWindowZIndex', false);
                    });
                }
                else {
                    $('body').data('FocusWindow', false);
                    $('body').data('activeWindowZIndex', false);
                    this.$el.removeClass('active');
                    this.settings.isActive = false;

                }
                return;
            }
        };


        /**
         * Unfocus all active Window
         */
        wm.unfocusOthers = function (callback) {
            var wins = $('body').data('Windows');
            if (wins.length === 0) {
                if (callback) {
                    callback();
                    return;
                }
                else {
                    return;
                }
            }


            var focusedWinID = $('body').data('FocusWindow');
            var data = $('#' + focusedWinID).data("WindowManager");
            if (focusedWinID && data) {
                if (data.get('isActive') === true) {
                    data.unfocus();
                    if (callback) {
                        callback();
                        return;
                    }
                    else {
                        return;
                    }
                }
            }

            for (var x = 0; x < wins.length; ++x) {
                data = $('#' + wins[x]).data("WindowManager");
                if (data) {
                    data.unfocus();

                    if (data.get('isActive') === true) {
                        data.unfocus();
                    }

                    if ((x + 1) >= wins.length && callback) {
                        callback();
                    }
                }
            }
        };

        /**
         * Get the active Window or geht the maximum zindexed Window if not found a
         * active Window. If not found a Window then retun null.
         * @returns {winObj}
         */
        wm.getZindexStack = function () {
            var zIndex = 1000, lastObject = null;
            var wins = $('body').data('Windows');

            if (wins) {

                for (var x = 0; x < wins.length; ++x) {
                    var el = $('#' + wins[x]);
                    if (el.length === 1) {
                        if (el[0].style && parseInt(el[0].style.zIndex) > zIndex) {
                            zIndex = el[0].style.zIndex;
                            lastObject = el;
                        }
                    }
                }
            }

            return lastObject; // returns the max zIndexd window
        };


        wm.updateZindexStack = function (item) {
            if ($(item).data("ui-draggable")) {

                var focusedWinID = $('body').data('FocusWindow');

                if (focusedWinID === this.id) {
                    //    Debug.log('Identical Stack object...');
                    return;
                }


                if (!focusedWinID) {
                    var min, group = $('#desktop').find('div.isWindowContainer');


                    if (group.length < 1)
                        return;

                    min = parseInt(group[0].style.zIndex, 10) || this.WindowMinZIndex;


                    $(group).each(function (i) {
                        $(this).css("zIndex", min + i).removeClass('active').data("WindowManager").settings.isActive = false;
                    });


                    $(item).css({'zIndex': min + group.length}).data("WindowManager").settings.isActive = true;
                    //  $('body').data('WindowZIndex', min + group.length);
                }
                else {
                    //  var focusZindex = parseInt($('#'+focusedWinID).css("zIndex"), 10);
                    //  var itemZindex = parseInt($(item).css("zIndex"), 10);

                    $('#' + focusedWinID).css("zIndex", 1000).removeClass('active').data("WindowManager").settings.isActive = false;
                    $(item).css('zIndex', 1001).addClass('active').data("WindowManager").settings.isActive = true;
                    // $('body').data('WindowZIndex', 1001);
                }

                $('body').data('FocusWindow', $(item).attr('id'));
            }
        };

        /**
         * get the current window title
         * @returns
         */
        wm.getTitle = function () {
            return this.TitleBar.find('.win-title').text();
        };

        /**
         * change the window title
         * @param {string} title
         */
        wm.setTitle = function (title) {
            if (title) {
                this.TitleBar.find('.win-title').html(title);
            }
            return this;
        };

        /**
         * reset title to original window title
         */
        wm.resetTitle = function () {
            if (this.settings.originalTitle && this.settings.isSingleWindow) {
                this.TitleBar.find('.win-title').html(this.settings.originalTitle);
            }

            return this;
        };

        wm.mask = function (title) {
            if (title) {
                this.BodyContent.mask(title);
            }
            else {
                this.BodyContent.mask('Bitte warten...');
            }
            return this;
        };

        wm.unmask = function () {
            this.BodyContent.unmask();
            return this;
        };

        function initButtonEvents () {

            self.closeBtn.click(function (e) {
                self.focus();
                self.close('close', e);
                // self.settings.status == 'closed'
            });
            if (self.settings.Minimize === true) {

                self.minBtn.click(function (e) {

                    if (self.settings.status == 'closed') {
                        return;
                    }

                    self.focus(null, self.id);
                    // update the cache before change the size
                    updateWindowSizeCache(true);
                    self.enableWindowDraggable();
                    self.enableWindowResizeable();
                    self.unfocus();
                    animateWindowView('min', e);
                });
            }


            if (self.settings.Maximize == true) {

                self.maxBtn.click(function () {

                    self.focus();
                    if (self.settings.status === 'max') {
                        self.enableWindowDraggable();
                        self.enableWindowResizeable();
                        //self.settings.status = 'default';
                        animateWindowView('restore');
                        return;
                    }

                    // update the cache before change the size
                    updateWindowSizeCache();
                    animateWindowView('max');
                });

                self.fullscreenBtn.click(function (e) {
                    Desktop.switchFullscreen = true;
                    $('#fullscreenContainer').click();
                    return (false);
                });

                /**
                 *
                 * bind window header dblclick
                 */
                self.TitleBar.dblclick(function () {

                    self.focus(null, self.id);
                    if (self.settings.status === 'max') {
                        self.enableWindowDraggable();
                        self.enableWindowResizeable();
                        //self.settings.status = 'default';
                        animateWindowView('restore');
                    }
                    else {
                        // update the cache before change the size
                        updateWindowSizeCache(true);
                        self.disableWindowDraggable();
                        self.disableWindowResizeable();
                        animateWindowView('max');
                    }
                });
            }
        }
        ;

        function setWindowRestoreSettings () {
            var winWidth = self.win.width(), h = self.win.height(), offset = self.win.offset();
            this.restoreSettings = {
                status: self.settings.status,
                top: offset.top,
                left: offset.left,
                winWidth: winWidth,
                winHeight: h,
                bodyHeight: parseInt(h, 0) - (self.getHeaderHeight() + self.getStatusbarHeight()),
                bodyWidth: winWidth
            };
        }
        ;

        function animateWindowView (mode, ev, callback) {
            var init = false;
            if (!self.settings.status) {
                self.settings.status = '';
                init = true;
            }

            switch (mode) {
                case 'restore':
                    // console.log('Restore');
                    if (self.settings.enableContentScrollbar) {
                        self.disableWindowScrollbar();
                    }

                    if (self.settings.hasGridAction === true) {
                        self.TitleBar.find('.no-toolbar').removeClass('no-toolbar');
                    }

                    if (typeof self.settings.onResizeStart === 'function') {
                        self.settings.onResizeStart(null, null, self, {height: self.settings.windowSizeCache.bodyHeight, width: self.settings.windowSizeCache.bodyWidth});
                    }


                    WindowAnimation.AnimateRestore(self);


                    break;
                case 'show':
                    //    console.log('Show');
                    if ((self.settings.status === 'default' && !init) || self.win.hasClass('animated')) {
                        return;
                    }

                    if (self.settings.enableContentScrollbar && !init) {
                        self.disableWindowScrollbar();
                    }


                    if (self.settings.hasGridAction === true) {
                        self.TitleBar.find('.no-toolbar').removeClass('no-toolbar');
                    }


                    if (self.Toolbar.find('div:not(.root,.empty-toolbar)').length > 0) {
                        self.TitleBar.find('.no-toolbar').removeClass('no-toolbar');
                    }


                    if (typeof self.settings.onBeforeShow === 'function') {
                        self.settings.onBeforeShow(null, self, function () {
                            self.animateShow();
                        });
                    }
                    else {
                        this.animateShow();
                    }


                    break;
                case 'min':

                    setWindowRestoreSettings();

                    if (self.settings.enableContentScrollbar) {
                        self.disableWindowScrollbar();
                    }

                    if (typeof self.settings.onBeforeShow === 'function') {
                        self.settings.onBeforeShow(null, self);
                        if (self.settings.nopadding) {
                            self.Content.removeClass('add-padding');
                        }
                    }

                    if (Dock) {
                        if (ev && $(ev.target).parents('.isWindowContainer').length == 1) {
                            Dock.hideApplication($(ev.target).parents('.isWindowContainer:first').attr('app'), $(ev.target).parents('.isWindowContainer:first').attr('id'), function (aniamteToObj) {
                                if (aniamteToObj && aniamteToObj.length === 1) {
                                    // console.log('animate to dock');

                                    WindowAnimation.AnimateMin(self, aniamteToObj);
                                }
                                else {
                                    WindowAnimation.AnimateMin(self, false);
                                }
                            });
                        }
                        else {
                            Dock.hideApplication(self.settings.Controller, self.id, function (aniamteToObj) {
                                if (aniamteToObj && aniamteToObj.length === 1) {
                                    // console.log('animate to dock');

                                    WindowAnimation.AnimateMin(self, aniamteToObj);
                                }
                                else {
                                    WindowAnimation.AnimateMin(self, false);
                                }
                            });
                        }
                    }
                    else {
                        WindowAnimation.AnimateMin(self, false);
                    }

                    return;


                    break;
                case 'max':

                    setWindowRestoreSettings();

                    if (self.settings.enableContentScrollbar) {
                        self.disableWindowScrollbar();
                    }


                    if (typeof self.settings.onResizeStart === 'function') {
                        self.settings.onResizeStart(null, null, self);
                    }

                    WindowAnimation.AnimateMax(self);

                    break;
                case 'close':

                    if (self.settings !== null && self.win !== null) {
                        if (self.settings.enableContentScrollbar !== null && self.settings.enableContentScrollbar) {
                            self.disableWindowScrollbar();
                        }

                        if (self.settings.onBeforeClose !== null && typeof self.settings.onBeforeClose === 'function') {

                            self.settings.onBeforeClose(ev, self, function () {

                                self.animateClose(ev, callback);
                                self.settings.status = 'closed';
                                self.unfocus();
                            });
                        }
                        else {
                            self.animateClose(ev, callback);
                        }

                    }
                    break;
            }
        }
        ;

        wm.animateRestore = function () {

        };

        var winshowTimout = null, windowInAnimation = false;

        wm.animateShow = function () {
            var self = this;

            if (windowInAnimation) {
                winshowTimout = setTimeout(function () {
                    self.animateShow();
                }, 5);
            }
            else {
                clearTimeout(winshowTimout);

                if (self.settings.hasGridAction === true) {
                    self.TitleBar.find('.no-toolbar').removeClass('no-toolbar');
                }

                if (self.Toolbar.find('.root').children().length == 0) {
                    self.Toolbar.find('.root').append($('<div/>').addClass('empty-toolbar'));
                }

                windowInAnimation = true;

                if (this.settings.nopadding) {
                    this.Content.removeClass('add-padding');
                }

                this.win.css({visibility: 'hidden'}).show();
                var winWidth = (this.settings.minWidth > this.settings.Width ? this.settings.minWidth : this.settings.Width);
                var winHeight = (this.settings.minHeight > this.settings.Height ? this.settings.minHeight : this.settings.Height);
                var Top = 0;
                var Left = 0;

                if (typeof document.clickOffset === 'object')
                {
                    Top = document.clickOffset.top;
                    Left = document.clickOffset.left;
                    this.settings.closeToPos = {left: Left, top: Top};
                }




                var sH = this.getStatusbarHeight();
                var hH = this.getHeaderHeight();
                var doBodyHeight = winHeight - hH - sH, doBodyWidth = winWidth;

                this.BodyContent.stop(true, true).css({WebkitTransform: 'translateX(0px)', visibility: '', opacity: '1', height: 1, width: 1}).show();

                var animateLeft = $(window).width() / 2 - (winWidth / 2), animateTop = $(window).height() / 2 - (winHeight / 2);

                if (this.settings.PositionLeft !== false && this.settings.PositionLeft >= 0) {
                    animateLeft = this.settings.PositionLeft;
                }
                if (this.settings.PositionTop !== false && this.settings.PositionTop > 0) {
                    animateTop = this.settings.PositionTop;
                }

                var gridHeaderHeight = 0, gridFooterHeight = 0, gridData = this.win.data('windowGrid');
                if (gridData) {
                    gridHeaderHeight = gridData.headerTable.outerHeight();
                    gridFooterHeight = gridData.gridFooter.outerHeight(true);
                }

                // set start position
                this.win/*.addClass('no-shadow')*/
                        .css({
                            visibility: '',
                            opacity: '0.1',
                            height: 16,
                            width: 16,
                            left: animateLeft + (winWidth / 2),
                            top: animateTop + (winHeight / 2)
                        })
                        .addClass('animated')
                        .show();


                this.BodyContent.animate({
                    width: doBodyWidth,
                    height: doBodyHeight,
                }, {
                    duration: windowOpenAnimationTime
                });



                this.win.animate(
                        {
                            //         zoom: '1',
                            opacity: '1',
                            top: animateTop,
                            left: animateLeft,
                            width: winWidth,
                            height: winHeight
                        },
                {
                    duration: windowOpenAnimationTime,
                    complete: function () {
                        self.win.stop(true, true).removeClass('animated no-shadow loading');

                        self.updateContentHeight();
                        $.pagemask.hide();
                        self.focus();
                        updateWindowSizeCache(true);

                        if (self.settings.enableContentScrollbar) {
                            self.enableWindowScrollbar();
                        }

                        if (typeof self.settings.onAfterShow === 'function' && self.settings.status !== 'default') {
                            self.settings.onAfterShow(null, self, {height: doBodyHeight, width: self.win.width()});
                        }

                        self.settings.status = 'default';
                        windowInAnimation = false;
                        setWindowRestoreSettings();


                        if (self.baseSize === null) {
                            self.baseSize = {
                                toolbarheight: self.Toolbar.outerHeight(true),
                                winheight: self.win.height(),
                                winwidth: self.win.width(),
                                bodyheight: self.getContentHeight(),
                                bodywidth: self.win.width()
                            };
                        }
                        setTimeout(function () {
                            if (self.firstRun) {
                                buildFileSelectorPanel();
                            }
                            self.firstRun = false;
                        }, 20);
                        
                        

                    }
                });
            }

        };

        wm.animateClose = function (ev, callback) {
            if (this.stopClose && !this.$el.length) {
                this.stopClose = false;
                return;
            }


            var top = 0, left = 0;
            if (this.settings.closeToPos && this.settings.closeToPos.left) {
                top = this.settings.closeToPos.top;
                left = this.settings.closeToPos.left;
            }

            WindowAnimation.AnimateClose(this, {top: top, left: left}, callback);

        };
        // ------------------------- End Animations


        function deleteBodyDataWindowID (id) {
            var wins = $('body').data('Windows');
            var newData = [];
            for (var x = 0; x < wins.length; ++x) {
                if (id !== wins[x]) {
                    newData.push(wins[x]);
                }
                else {
                    var focusedWinID = $('body').data('FocusWindow');
                    if (focusedWinID === id) {
                        $('body').data('FocusWindow', false);
                    }
                }
            }

            $('body').data('Windows', newData);
        }
        ;


        function getContentHeightDiffBetweenRootAndSubcontent (subcontentID) {

            if (self.baseSize !== null) {
                // make visible
                self.Body.find('#' + subcontentID + '-content').css({'visibility': 'hidden', height: ''}).show();
                var diff = 0, mode = 'larger', contentHeight = self.Body.find('#' + subcontentID + '-content').height();


                if (parseInt(contentHeight, 10) > parseInt(self.baseSize.bodyheight, 10)) {
                    mode = 'larger';
                    diff = parseInt(contentHeight, 10) - parseInt(self.baseSize.bodyheight, 10);
                }
                else {
                    mode = 'smaller';
                    diff = parseInt(self.baseSize.bodyheight, 10) - parseInt(contentHeight, 10);
                }

                // reset subcontent css
                self.Body.find('#' + subcontentID + '-content').css({'visibility': '', height: ''}).hide();

                return {
                    mode: mode,
                    showContentHeight: contentHeight,
                    contentheightDiff: diff
                };
            }

            return false;
        }
        ;


        /**
         * Will animate to other Window Content and Toolbar
         *
         */
        function animateToSingleWindow (hiddeID, showID, isSubContent, initCall) {

            if (isSubContent && typeof self.settings.onBeforeShow === 'function') {
                self.settings.onBeforeShow(null, self);
            }

            var taskbarHeight = parseInt($('#Taskbar').outerHeight(true), 10), dockHeight = parseInt($('#dock').outerHeight(true), 10), desktopHeight = parseInt($('#desktop').height(), 10);
            var maxWindowHeight = desktopHeight - taskbarHeight - dockHeight;
            var Body = $('#body-' + self.id);
            var BodyContent = $('#body-content-' + self.id);
            var winContent = BodyContent.find('.win-content:first');
            var bodyOverflowBase = Body.css('overflow') || '';
            var bodyContentOverflowBase = BodyContent.css('overflow') || '';
            var contentOverflowBase = $('#' + showID + '-content', self.$el).css('overflow') || '';
            var showContent = $('#' + showID + '-content', self.$el);
            var hiddenContent = $('#' + hiddeID + '-content', self.$el);


            if (showID !== self.id) {
                $('.switchHome', self.$el).removeClass('disabled');
                $('.switchLeft', self.$el).removeClass('disabled');
                $('.switchRight', self.$el).addClass('disabled');
            }
            else {
                $('.switchHome', self.$el).addClass('disabled');
                $('.switchLeft', self.$el).addClass('disabled');
                $('.switchRight', self.$el).addClass('disabled');
            }


            if (isSubContent) {

                if (self.settings.enableContentScrollbar) {
                    self.disableWindowScrollbar();
                }

                BodyContent.width(Body.width());

                //      showContent.width( (this.BodyContent.width() - this.BodyContent.find('div.pane:first').outerWidth(true)+2) );

                var opt = getContentHeightDiffBetweenRootAndSubcontent(showID);
                var hiddenHeight = hiddenContent.outerHeight(true);
                var hiddenWidth = hiddenContent.width();

                winContent.css('height', '100%');

                if (opt !== false) {

                    $('#' + hiddeID + '-toolbar').fadeOut(300);
                    $('#' + showID + '-toolbar').fadeIn(300);

                    Body.css('overflow', 'hidden!important');
                    BodyContent.css({'overflow': 'hidden!important', height: Body.outerHeight(), paddingRight: '', marginRight: '', width: '100%'}).find('div.pane').hide();
                    showContent.css({'overflow': 'hidden', height: 0, opacity: '0', top: 0}).show();

                    // the subcontent height is larger as the Root Content height
                    if (opt.mode === 'larger') {
                        // no animation

                        var setHeight = parseInt(self.baseSize.winheight, 10) + opt.contentheightDiff;

                        if (self.win.height() + opt.contentheightDiff <= maxWindowHeight) {

                            BodyContent.height('');

                            var setContentHeight = BodyContent.height() + opt.contentheightDiff;

                            hiddenContent.css({'position': 'absolute', zIndex: '1', opacity: '1', top: 0});
                            showContent.css({'position': 'absolute', zIndex: '10', top: hiddenHeight, opacity: '0.1', height: '', width: hiddenWidth});


                            hiddenContent.animate({
                                //  height: 0,
                                top: '-=' + hiddenHeight
                            }, {
                                duration: 350,
                                complete: function () {
                                    $(this).hide().css({height: '', zIndex: '', opacity: '', top: '', position: ''});
                                }
                            });


                            showContent.animate({
                                // height: '+=' + opt.contentheightDiff,
                                top: '-=' + hiddenHeight,
                                opacity: '1'
                            }, {
                                duration: 350,
                                complete: function () {

                                    Body.css({overflow: ''});
                                    BodyContent.css({overflow: '', top: '', position: ''}); //.find('div.pane').show();


                                    $(this).css({overflow: '', zIndex: '', top: '', height: '', position: '', width: ''});

                                    if (self.settings.enableContentScrollbar) {
                                        self.enableWindowScrollbar();
                                    }

                                    updateWindowSizeCache(false);

                                    if (
                                            typeof self.settings.onAfterCreated === 'function' &&
                                            typeof self.settings.subCache[showID] !== 'undefined' &&
                                            typeof self.settings.subCache[showID].baseContent !== 'undefined'
                                            ) {
                                        self.settings.onAfterCreated(this, function () {
                                        }, self.settings.subCache[showID].baseContent);
                                    }


                                    if (isSubContent && typeof self.settings.onAfterShow === 'function') {
                                        self.settings.onAfterShow(null, self);
                                    }


                                    if (typeof self.settings.onResizeStop == 'function') {
                                        self.settings.onResizeStop(null, null, self, {height: setContentHeight - 10, width: $(this).width()});
                                    }

                                    $.pagemask.hide();


                                }
                            });


                            $('#body-' + self.id + ',#body-content-' + self.id).css({
                                height: '+=' + opt.contentheightDiff
                            });

                            $('#' + self.id).animate({
                                height: '+=' + opt.contentheightDiff
                            }, {
                                duration: 350
                            });


                        }
                        else {

                            var winDiff = maxWindowHeight - self.win.height();
                            var setContentHeight = BodyContent.outerHeight() - winDiff;


                            hiddenContent.css({'position': 'absolute', zIndex: 1, top: 0, opacity: '1'});
                            showContent.css({'position': 'absolute', zIndex: 10, opacity: '0.1', top: hiddenHeight, height: 0, width: hiddenWidth});


                            hiddenContent.animate({
                                top: '-=' + hiddenHeight,
                                height: 0
                            }, {
                                duration: 350,
                                complete: function () {
                                    $(this).hide().css({height: '', opacity: '', zIndex: '', position: '', top: ''});
                                }
                            });

                            showContent.animate({
                                top: '-=' + hiddenHeight,
                                height: setContentHeight + 10,
                                opacity: '1'
                            }, {
                                duration: 350,
                                complete: function () {

                                    Body.css({overflow: ''});
                                    BodyContent.css({overflow: ''}); //.find('div.pane').show();
                                    $(this).css({overflow: '', height: '', zIndex: '', width: '', position: '', top: ''});



                                    if (self.settings.enableContentScrollbar) {
                                        self.enableWindowScrollbar();
                                    }

                                    updateWindowSizeCache(false);
                                    if (
                                            typeof self.settings.onAfterCreated === 'function' &&
                                            typeof self.settings.subCache[showID] !== 'undefined' &&
                                            typeof self.settings.subCache[showID].baseContent !== 'undefined'
                                            ) {
                                        self.settings.onAfterCreated(this, function () {
                                        }, self.settings.subCache[showID].baseContent);
                                    }


                                    if (isSubContent && typeof self.settings.onAfterShow === 'function') {
                                        self.settings.onAfterShow(null, self);
                                    }


                                    if (typeof self.settings.onResizeStop == 'function') {
                                        self.settings.onResizeStop(null, null, self, {height: $(this).height(), width: $(this).width()});
                                    }

                                    $.pagemask.hide();
                                }
                            });

                            $('#' + self.id).animate({
                                height: maxWindowHeight
                            }, {
                                duration: 350
                            });

                            $('#body-' + self.id + ',#body-content-' + self.id).css({
                                height: '+=' + winDiff
                            });

                        }


                    }
                    else {

                        var setContentHeight = BodyContent.height() - opt.contentheightDiff;
                        var $a = BodyContent, $b = Body;

                        $('#' + self.id).animate({
                            height: '-=' + (opt.contentheightDiff - 20)
                        }, {
                            duration: 350
                        });


                        $('#body-' + self.id + ',#body-content-' + self.id).css({
                            height: '-=' + (opt.contentheightDiff - 20)
                        });



                        hiddenContent.css({'position': 'absolute', zIndex: '1', top: 0, opacity: '1'});
                        showContent.css({'position': 'absolute', zIndex: '10', opacity: '0.1', top: hiddenHeight, height: setContentHeight + 10, width: hiddenContent.width()}).show();


                        hiddenContent.animate({
                            top: '-=' + hiddenHeight,
                            opacity: '0'
                        }, {
                            duration: 350,
                            complete: function () {
                                $(this).hide().css({height: '', zIndex: '', opacity: '', 'top': '', 'position': ''});
                            }
                        });

                        showContent.height(setContentHeight).animate({
                            top: '-=' + hiddenHeight,
                            opacity: '1'
                        }, {
                            duration: 350,
                            complete: function () {

                                Body.css({overflow: bodyOverflowBase});
                                BodyContent.css({overflow: bodyContentOverflowBase});

                                $(this).css({overflow: contentOverflowBase, zIndex: '', 'top': '', 'position': '', width: '', height: ''});

                                updateWindowSizeCache(false);

                                if (self.settings.enableContentScrollbar) {
                                    self.enableWindowScrollbar();
                                }


                                if (
                                        typeof self.settings.onAfterCreated === 'function' &&
                                        typeof self.settings.subCache[showID] !== 'undefined' &&
                                        typeof self.settings.subCache[showID].baseContent !== 'undefined'
                                        ) {
                                    self.settings.onAfterCreated(this, function () {
                                    }, self.settings.subCache[showID].baseContent);
                                }


                                if (isSubContent && typeof self.settings.onAfterShow === 'function') {
                                    self.settings.onAfterShow(null, self);
                                }


                                if (typeof self.settings.onResizeStop == 'function') {
                                    self.settings.onResizeStop(null, null, self, {height: setContentHeight, width: $(this).width()});
                                }

                                $.pagemask.hide();


                            }
                        });




                    }
                }
            }
            else {

                if (self.settings.enableContentScrollbar) {
                    self.disableWindowScrollbar();
                }

                setTimeout(function () {

                    /**
                     * Animate to the Root Window
                     */

                    var smaller = false, diffRootContentHeight = 0, currentHeight = parseInt($('#' + hiddeID + '-content', self.$el).outerHeight(true), 10);

                    if (currentHeight > parseInt(self.baseSize.bodyheight, 10)) {
                        diffRootContentHeight = currentHeight - parseInt(self.baseSize.bodyheight, 10);
                        smaller = true;
                    }
                    else if (currentHeight < parseInt(self.baseSize.bodyheight, 10)) {
                        diffRootContentHeight = parseInt(self.baseSize.bodyheight, 10) - currentHeight;
                        smaller = false;
                    }


                    $('#' + hiddeID + '-toolbar').fadeOut(300, function () {
                        $('#' + showID + '-toolbar').fadeIn(300);
                    });


                    Body.css('overflow', 'hidden!important');
                    BodyContent.css({'overflow': 'hidden!important'}).find('div.pane').hide();

                    var hh = hiddenContent.height();
                    //hiddenContent.hide();
                    showContent.css({'overflow': 'hidden', top: 0, height: 0, opacity: '0.1', width: '100%'}).show();
                    var showHeight = showContent.outerHeight(true);



                    $('#' + self.id).animate({
                        height: self.baseSize.winheight
                    }, {
                        duration: 200
                    });
                    $('#body-' + self.id + ',#body-content-' + self.id).animate({
                        height: self.baseSize.bodyheight
                    }, {
                        duration: 300
                    });

                    $('#body-' + self.id).find('.win-content:first').animate({
                        height: self.baseSize.bodyheight
                    }, {
                        duration: 300,
                        complete: function () {

                            //  winContent.height(self.baseSize.bodyheight);
                            Body.css({overflow: bodyOverflowBase /*, height: self.baseSize.bodyheight*/});
                            BodyContent.css({overflow: bodyContentOverflowBase/*, height: self.baseSize.bodyheight*/});
                            showContent.css({overflow: '' /*, width: '', height: self.baseSize.bodyheight*/});


                            if (self.settings.enableContentScrollbar) {
                                self.enableWindowScrollbar();
                            }

                            updateWindowSizeCache(false);
                            $.pagemask.hide();
                        }
                    });

                    hiddenContent.animate({
                        height: 0,
                        opacity: '0'
                    }, {
                        duration: 200,
                        complete: function () {
                            $(this).hide();
                        }
                    });

                    showContent.animate({
                        height: showHeight,
                        opacity: '1'
                    }, {
                        duration: 200
                    });
                }, 10);

            }

        }
        ;

        wm.getToolbarHeight = function () {
            return this.Toolbar.is(':visible') ? parseInt(this.Toolbar.outerHeight(true), 0) : 0;

        };

        wm.getStatusbarHeight = function () {
            return this.Statusbar.length && this.Statusbar.is(':visible') ? parseInt(this.Statusbar.parents('.window-footer:first').outerHeight(true), 0) : 0;

        };

        wm.getHeaderHeight = function () {
            var head = 0;

            if (this.Body.prev().hasClass('tabcontainer')) {
                head += parseInt(this.Body.prev().outerHeight(true), 0);
            }

            return (parseInt(this.TitleBar.outerHeight(true), 0) + this.getToolbarHeight() + head);
        };

        wm.t;

        wm.getContentHeight = function () {
            var self = this;

            if (this.$el.hasClass('animated') || this.win === null || typeof this.win == 'undefined') {
                this.t = setTimeout(function () {
                    return self.getContentHeight();
                }, 1);
            }
            else {

                clearTimeout(this.t);

                return parseInt(this.win.height(), 0) - (this.getHeaderHeight() + this.getStatusbarHeight());
            }
        };


        wm.updateWindowInnerWrap = function () {
            var ch = this.getContentHeight();
            var hh = this.getHeaderHeight();
            var sb = this.getStatusbarHeight();

            this.WindowWrapper.height(ch + sb + hh);
        };

        wm.t2 = null;
        wm.updateContentHeight = function () {

            var self = this;

            if (this.$el.hasClass('animated') || this.win === null || typeof this.win == 'undefined') {
                this.t2 = setTimeout(function () {
                    return self.updateContentHeight();
                }, 1);
            }
            else {
                clearTimeout(this.t2);

                var b = self.BodyContent.get(0);
                b.style.height = self.getContentHeight();
                b.style.width = self.$el.innerWidth();
                // self.BodyContent.css({height: self.getContentHeight()}).width(self.$el.innerWidth());

                //        self.updateWindowInnerWrap();

                updateWindowSizeCache(false);
                if (!self.win.hasClass('no-scroll') && self.settings.enableContentScrollbar === true) {
                    Tools.refreshScrollBar(self.BodyContent);
                }
            }

        };

        function updateWindowSizeCache (updateBase) {

            var pos = $(self.$el).offset();
            if (typeof self.settings.windowSizeCache !== 'object') {
                self.settings.windowSizeCache = {};
            }

            self.settings.windowSizeCache = $.extend(self.settings.windowSizeCache, {
                width: self.$el.width(),
                height: self.$el.height(),
                left: pos.left,
                top: pos.top,
                bodyScrollheight: self.BodyContent.get(0).scrollHeight,
                bodyHeight: self.getContentHeight(),
                bodyWidth: self.BodyContent.width()
            });

            if (updateBase !== false && (typeof self.settings.windowSizeCache.baseWidth == 'undefined' || self.settings.windowSizeCache.baseWidth === null)) {
                self.settings.windowSizeCache.baseWidth = self.settings.windowSizeCache.width;
                self.settings.windowSizeCache.baseHeight = self.settings.windowSizeCache.height;
                self.settings.windowSizeCache.baseBodyWidth = self.settings.windowSizeCache.bodyWidth;
                self.settings.windowSizeCache.baseBodyHeight = self.settings.windowSizeCache.bodyHeight;
            }
        }
        ;

        function bindSingleWindowSwitchButtons (id) {

            self.Toolbar.find('.switchHome', self.$el).unbind().click(function () {
                if (!$(this).hasClass('disabled')) {
                    self.settings.isSubContentVisible = false;
                    $(this).addClass('disabled');
                    var visibleSubId = $('.sub-content:visible', self.$el).attr('id').replace('-content', '');

                    Win.unload(self.id);

                    self.resetTitle();
                    animateToSingleWindow(visibleSubId, self.id, false);

                }
            });

            self.Toolbar.find('.switchLeft', self.$el).unbind().click(function () {

            });

            self.Toolbar.find('.switchRight', self.$el).unbind().click(function () {

            });
        }
        ;

        /**
         *
         * @param object opts
         * @returns void
         */
        wm.createSingleWindow = function (opts) {
            var self = this, id = this.id, settings = this.settings, windowTemplate;
            var singlesettings = $.extend({}, $.WindowManager.defaultOptions, opts), isSubContent = false;

            this.stopWinContentChangeTrigger = true;

            if (Tools.isString(singlesettings.Url)) {
                var clean_Url = cleanUrl(singlesettings.Url);
                singlesettings.WindowID = 'w' + md5(clean_Url).substr(0, 12);
                isSubContent = true;
            }

            if (this.settings.enableContentScrollbar) {
                this.disableWindowScrollbar();
            }

            this.Content.removeClass('add-padding');

            if (singlesettings.nopadding) {
                this.Content.addClass('force-no-padding');
            }

            // 
            Win.unload(this.id);


            // create hashes for dynamic caching :)
            var md5Co = md5(singlesettings.WindowContent);
            var md5Tb = md5(singlesettings.WindowToolbar);

            if ($('#' + singlesettings.WindowID + '-content', this.$el).length == 1) {
                setTimeout(function () {
                    self.$el.removeClass('loading').unmask();
                    $('#' + id + '-toolbar .switchHome', self.$el).removeClass('disabled');
                    var cChanged = self.settings.subCache[singlesettings.WindowID].content != md5Co ? true : false;
                    var tbChanged = self.settings.subCache[singlesettings.WindowID].toolbar != md5Tb ? true : false;

                    // checking cache
                    if (cChanged || tbChanged) {
                        self.settings.subCache[singlesettings.WindowID] = {
                            baseContent: singlesettings.WindowContent,
                            toolbar: md5Tb,
                            content: md5Co
                        };
                        // change the content if is changed

                        if (tbChanged) {
                            self.$el.find('#' + singlesettings.WindowID + '-toolbar').empty().append(singlesettings.WindowToolbar);
                        }

                        if (cChanged) {
                            self.$el.find('#' + singlesettings.WindowID + '-content').empty().append(singlesettings.WindowContent);
                        }
                    }
                    else {

                        // 
                        Tools.eval(singlesettings.WindowContent);
                    }

                    if (singlesettings.WindowContent && typeof self.settings.onAfterCreated === 'function') {
                        self.settings.onAfterCreated(self, function () {

                            self.setTitle(singlesettings.WindowTitle);
                            self.settings.isSubContentVisible = true;
                            animateToSingleWindow(id, singlesettings.WindowID, true);
                            self.$el.find('#' + id + '-toolbar .switchHome').removeClass('disabled');
                            self.$el.find('#' + id + '-toolbar .switchLeft').addClass('disabled');
                            self.$el.find('#' + id + '-toolbar .switchRight').addClass('disabled');
                            self.stopWinContentChangeTrigger = false;
                        }, singlesettings.WindowContent);
                    }
                    else {

                        self.setTitle(singlesettings.WindowTitle);
                        self.settings.isSubContentVisible = true;
                        animateToSingleWindow(id, singlesettings.WindowID, true);
                        self.$el.find('#' + id + '-toolbar .switchHome').removeClass('disabled');
                        self.$el.find('#' + id + '-toolbar .switchLeft').addClass('disabled');
                        self.$el.find('#' + id + '-toolbar .switchRight').addClass('disabled');
                        self.stopWinContentChangeTrigger = false;
                    }

                }, 80);

                return;
            }

            this.$el.removeClass('loading').unmask();

            var rootContent = $('#' + id + '-content');

            if (rootContent.length === 0) {
                rootContent = $('<div id="' + id + '-content" class="sub-content"/>');
                rootContent.append($(this.Content).clone().html());
                $(this.Content).empty().wrapInner(rootContent);
                //this.Content.empty().append(rootContent);

                if (self.$el.find('#window-nav').length === 0) {
                    var historyTpl = $('<div id="window-nav"><button type="button" class="switchHome"><span></span></button> <button type="button" class="switchLeft"><span></span></button> <button type="button" class="switchRight"><span></span></button></div>');
                    historyTpl.show();
                    this.Toolbar.prepend(historyTpl).show();
                }


                this.Toolbar.find('.root:first').attr('id', id + '-toolbar').show();
                bindSingleWindowSwitchButtons();
            }
            else if (self.$el.find('#window-nav').length === 0) {
                var historyTpl = $('<div id="window-nav"><button type="button" class="switchHome"><span></span></button> <button type="button" class="switchLeft"><span></span></button> <button type="button" class="switchRight"><span></span></button></div>');
                historyTpl.show();
                this.Toolbar.prepend(historyTpl).show();
                this.Toolbar.find('.root:first').attr('id', id + '-toolbar').show();
                bindSingleWindowSwitchButtons();
            }


            if (typeof this.settings.windowSizeCache.baseWidth == 'undefined' || this.settings.windowSizeCache.baseWidth === null) {
                this.settings.windowSizeCache.baseWidth = this.settings.windowSizeCache.width;
                this.settings.windowSizeCache.baseHeight = this.settings.windowSizeCache.height;
                this.settings.windowSizeCache.baseBodyWidth = this.settings.windowSizeCache.bodyWidth;
                this.settings.windowSizeCache.baseBodyHeight = this.settings.windowSizeCache.bodyHeight;
            }


            $('#' + id + '-toolbar', this.$el).removeClass('disabled');
            $('#' + id + '-toolbar .switchLeft', this.$el).removeClass('disabled');
            $('#' + id + '-toolbar .switchRight', this.$el).addClass('disabled');

            this.settings.subCache[singlesettings.WindowID] = {
                baseContent: singlesettings.WindowContent,
                toolbar: md5Tb,
                content: md5Co
            };

            var singleContent = $('<div id="' + singlesettings.WindowID + '-content" class="sub-content"></div>');

            if (Tools.isString(singlesettings.Url)) {
                singleContent.data('WindowURL', singlesettings.Url);
            }

            singleContent.append($(singlesettings.WindowContent)).hide();
            this.Content.append(singleContent);
            var singleToolbar = $('<div id="' + singlesettings.WindowID + '-toolbar" class="sub-toolbar"></div>');
            singleToolbar.append($(singlesettings.WindowToolbar)).hide();
            this.Toolbar.append(singleToolbar);
            this.setTitle(singlesettings.WindowTitle);
            this.settings.isSubContentVisible = true;

            if (singlesettings.WindowContent && typeof this.settings.onAfterCreated === 'function') {
                this.settings.onAfterCreated(this, function () {
                    animateToSingleWindow(id, singlesettings.WindowID, isSubContent, true);
                    self.stopWinContentChangeTrigger = false;
                }, singlesettings.WindowContent);
            }
            else {
                animateToSingleWindow(id, singlesettings.WindowID, isSubContent, true);
                this.stopWinContentChangeTrigger = false;
            }


        };


        /**
         * Create a new Window
         * @returns object the new window
         */
        function buildWindow () {
            var id = self.id,
                    settings = self.settings,
                    windowTemplate;



            var winWidth = (settings.minWidth > settings.Width ? settings.minWidth : settings.Width);
            var winHeight = (settings.minHeight > settings.Height ? settings.minHeight : settings.Height);


            /*
             windowTemplate =
             '<div id="' + id + '"'
             + (settings.Controller != null ? ' app="' + settings.Controller + '"' : '')
             + ' class="isWindowContainer ' + settings.Skin + 'Container" style="height:' + winHeight + 'px; width:' + winWidth + 'px;z-index: ' + (this.WindowZIndex) + '">' // with width only
             */
            windowTemplate = '  <div class="window-tl">'
                    + '      <div class="window-tr">'
                    + '          <div class="window-tc';

            if (typeof settings.WindowToolbar != 'string' || settings.WindowToolbar == '') {
                windowTemplate += ' no-toolbar';
            }

            windowTemplate += '"'; // close class attribute


            if (settings.UseWindowIcon === true && typeof settings.DesktopIconFile === 'string' && settings.DesktopIconFile !== '') {
                windowTemplate += ' style="background-image: url(\'' + settings.DesktopIconFile + '\')"';
            }

            windowTemplate += '>'; // close div window-tc


            // -----------------------------------------------------------------
            // window title and window buttons here
            windowTemplate += '<div class="win-buttons">';
            if (settings.Closable == true) {
                windowTemplate += '     <div class="winbtn win-close-btn"></div>';
            }

            if (settings.Minimize == true) {
                windowTemplate += '     <div class="winbtn win-min-btn"></div>';
            }

            if (settings.Maximize == true) {
                windowTemplate += '     <div class="winbtn win-max-btn"></div>';
            }
            windowTemplate += '</div>';
            if (settings.Maximize == true) {
                windowTemplate += '     <div class="winbtn win-fullscreen-btn"></div>';
            }

            windowTemplate += '<span class="win-title">' + settings.WindowTitle + '</span>';
            windowTemplate +=
                    '           </div>' // end window-tc
                    + '      </div>'
                    + '  </div>'; // end window-tl


            windowTemplate +=
                    '   <div id="wrapper-' + id + '" class="window-inner-wrap">'

                    + '      <div id="panel-left-' + id + '" class="window-panel-left"><div class="window-panel-content"></div></div>'
                    + '      <div id="panel-right-' + id + '" class="window-panel-right"><div class="window-panel-content"></div></div>'


                    + '      <div class="window-ml">'
                    + '          <div class="window-mr">'
                    + '              <div class="window-mc">';
            // -----------------------------------------------------------------
            // toolbar with width only


            if (typeof settings.WindowToolbar == 'string' && settings.WindowToolbar != '') {
                windowTemplate
                        += '<div id="toolbar-' + id + '" class="window-toolbar" style="">'   // add with width only
                        + '     <div class="root">' + settings.WindowToolbar + '</div>'
                        + '</div>';
            }
            else {
                windowTemplate
                        += '<div id="toolbar-' + id + '" class="window-toolbar" style="display:none;">'   // add with width only
                        + '     <div class="root"></div>'
                        + '</div>';
            }

            // 
            // content
            //

            var rootContent = '<div id="' + id + '-content" class="sub-content root"><div class="sub-content-inner">' + settings.WindowContent + '</div></div>';

            windowTemplate
                    += '<div id="body-' + id + '" class="window-body" style="">' // with css width
                    + '      <div class="window-body-wrap">'
                    + '          <div id="body-content-' + id + '" class="window-body-content" style="width:' + winWidth + 'px;">' // with css width and height and scrolling
                    //+ '               <div class="win-content-scrollbar scrollbar"><div class="slider"></div></div>'
                    + '               <div class="win-content">' + $(rootContent).html() + '</div>'
                    + '          </div>'
                    + '      </div>'
                    + '</div>';
            //
            // -----------------------------------------------------------------


            windowTemplate +=
                    '               </div>' // end window-mc
                    + '          </div>'
                    + '      </div>' // end window-ml

                    + '      <div class="window-footer window-bl">'
                    + '          <div class="window-br">'
                    + '              <div class="window-bc" id="statusbar-' + id + '">'
                    + '                 <div class="win-statusbar" style="' + (typeof settings.WindowStatusbar != 'string' ? 'display:none' : '') + '">'

                    + '<div class="window-panel-btn-left" id="pane-toggle-left-' + id + '"><span></span></div>'
                    + '<div class="win-statusbar-content">'
                    + (typeof settings.WindowStatusbar == 'string' ? '' + settings.WindowStatusbar + '' : '')
                    + '</div><div class="window-panel-btn-right" id="pane-toggle-right-' + id + '"><span></span></div>'
                    + '                 </div>'
                    + '              </div>' // end window-bc
                    + '          </div>'
                    + '      </div>' // end window-footer

                    + '  </div>'; // end window-inner-wrap
            //      + '</div>'; // end isWindowContainer


            //$(windowTemplate).find('.win-content').html(rootContent);

            return $(windowTemplate);
        }
        ;

        function initWindowResizeDragging () {
            //  var self = this;
            if (self.settings.Draggable) {
                self.$el.draggable({
                    distance: 1,
                    cancel: '.window-toolbar,.tinyMCE-Toolbar,.tabcontainer,.window-inner-wrap',
                    scroll: false,
                    revert: false,
                    cursor: 'move!important',
                    containment: '#desktop',
                    handle: '.window-tc',
                    //     stack: '#desktop > div.isWindowContainer',
                    start: function (event, ui) {
                        self.focus(event, self.id);
                        $(this).addClass('no-shadow');
                    },
                    drag: function (event, ui) {
                        if (ui.offset.top <= self.settings.TaskbarHeight || ui.position.top <= self.settings.TaskbarHeight) {
                            if (ui.offset.top - 1 <= self.settings.TaskbarHeight) {
                                self.$el.css({
                                    top: self.settings.TaskbarHeight
                                });
                            }
                            return false;
                        }
                    },
                    stop: function (event, ui) {
                        $(this).removeClass('no-shadow');
                        setTimeout(function () {
                            event.stopPropagation();
                            updateWindowSizeCache(true);
                            if (typeof self.settings.onWindowDragStop == 'function') {
                                self.settings.onWindowDragStop(self);
                            }

                            setWindowRestoreSettings();
                        }, 5);

                    }//,
                    //stack: '#' + self.id
                });
            }

            if (self.settings.Resizable) {
                self.$el.resizable({
                    handles: 'n, e, s, w, ne, se, sw, nw',
                    scroll: false,
                    minWidth: (self.settings.minWidth > 10 ? self.settings.minWidth : $.WindowManager.defaultOptions.minWidth),
                    minHeight: (self.settings.minHeight > 10 ? self.settings.minHeight : $.WindowManager.defaultOptions.minHeight),
                    start: function (event, ui) {

                        self.focus(event, self.id);


                        self.isResizeing = true;
                        $(self.win).addClass('active').addClass('no-shadow');
                        self.settings.isActive = true;
                        if (self.settings.enableContentScrollbar) {
                            // self.disableWindowScrollbar();
                        }

                        if (typeof self.settings.onResizeStartFS == 'function') {
                            self.settings.onResizeStartFS(event, ui, self);
                        }


                        if (typeof self.settings.onResizeStart == 'function') {
                            self.settings.onResizeStart(event, ui, self);
                        }

                    },
                    resize: function (event, ui) {

                        var w = $(this).width(), h = $(this).height();

                        self.Body.width(w).height(h - self.getHeaderHeight() - self.getStatusbarHeight());
                        self.BodyContent.width(w).height(h - self.getHeaderHeight() - self.getStatusbarHeight()).each(function () {
                            if (typeof self.settings.onResizeFS === 'function') {
                                self.settings.onResizeFS(event, ui, self, {height: h, width: w});
                            }

                            if (typeof self.settings.onResize === 'function') {
                                self.settings.onResize(event, ui, self, {height: h, width: w});
                            }
                        });
                    },
                    stop: function (event, ui) {

                        if (self.settings.isSubContentVisible !== true && typeof self.settings.windowSizeCache.baseWidth != 'undefined') {
                            delete (self.settings.windowSizeCache.baseWidth);
                            delete (self.settings.windowSizeCache.baseHeight);
                            delete (self.settings.windowSizeCache.baseBodyWidth);
                            delete (self.settings.windowSizeCache.baseBodyHeight);
                        }




                        $(this).removeClass('no-shadow');
                        var w = $(this).width(), h = $(this).height();
                        self.Body.width(w).height(h - self.getHeaderHeight() - self.getStatusbarHeight());
                        //self.Body.width($(this).width()).height(ui.size.height - self.getHeaderHeight() - self.getStatusbarHeight());
                        self.BodyContent.width(w).height(h - self.getHeaderHeight() - self.getStatusbarHeight()).each(function () {

                            self.isResizeing = false;
                            updateWindowSizeCache(true);
                            if (self.settings.enableContentScrollbar) {

                                self.enableWindowScrollbar();
                            }

                            setWindowRestoreSettings();
                            if (typeof self.settings.onResizeStopFS === 'function') {
                                var _self = $(this);
                                setTimeout(function () {
                                    self.settings.onResizeStopFS(event, ui, self, {height: _self.height(), width: _self.width()});
                                }, 10);
                            }

                            if (typeof self.settings.onResizeStop === 'function') {
                                var _self = $(this);
                                setTimeout(function () {
                                    self.settings.onResizeStop(event, ui, self, {height: _self.height(), width: _self.width()});
                                }, 50);
                            }

                        });
                        event.stopPropagation();
                    }
                });
            }
        }
        ;

        wm.disableWindowDraggable = function () {
            this.win.filter('ui-draggable').draggable('disable');
            return this;
        };

        wm.enableWindowDraggable = function () {
            if (this.settings.Draggable) {
                this.win.filter('ui-draggable').draggable('enable');
            }
            return this;
        };

        wm.disableWindowResizeable = function () {
            this.win.filter('ui-resizable').resizable('disable');
            return this;
        };

        wm.enableWindowResizeable = function () {
            if (this.settings.Resizable) {
                this.win.filter('ui-resizable').resizable('enable');
            }
            return this;
        };

        wm.disableWindowScrollbar = function () {
            return this;
            if (this.BodyContent) {
                //Tools.removeScrollBar(this.BodyContent);
            }

            return this;
        };

        wm.enableWindowScrollbar = function () {
            if (this.BodyContent) {
                if (!this.win.hasClass('no-scroll') && this.settings.enableContentScrollbar === true /* && this.get('hasTinyMCE') != true*/) {
                    Tools.scrollBar(this.BodyContent, null, this.settings.onScroll);
                }
            }
            return this;
        };



        /**
         *
         * @param {string} key
         * @param {mixed} defaultValue
         * @returns {mixed}
         */
        wm.get = function (key, defaultValue) {
            return (typeof this.settings[key] != 'undefined' ? this.settings[key] : (typeof defaultValue != 'undefined' ? defaultValue : null));
        };

        /**
         *
         * @param {string} key
         * @param {mixed} value
         * @returns {$.WindowManager.wm}
         */
        wm.set = function (key, value) {
            this.settings[key] = value;
            return this;
        };

        wm.restore = function () {
            animateWindowView('restore');
            this.focus();
        };

        wm.close = function (mode, e, callback) {
            animateWindowView('close', e, callback);
        };

        wm.stopEvent = function (what) {
            if (what == 'close') {
                this.stopClose = true;
            }
        };

        wm.getToolbar = function () {
            return (this.settings.isSingleWindow ? this.$el.find('.window-toolbar .sub-toolbar') : this.$el.find('.window-toolbar'));
        };

        wm.ReloadWindow = function (callback) {
            var self = this;
            var enabledScroll = options.enableContentScrollbar;
            if (this.settings.WindowURL !== null) {
                this.disableWindowScrollbar(true);
                this.win.addClass('loading');
                var url = this.settings.WindowURL, w = self.$el.find('.sub-content:visible');

                // only used for single Windows
                if (w.data('WindowURL')) {
                    url = w.data('WindowURL');
                }

                self.$el.removeData('windowGrid');
                self.$el.removeData('formConfig');
                self.$el.removeData('formID');

                if (typeof this.settings.onBeforeReload === 'function') {
                    self.disableWindowScrollbar(true);

                    return this.settings.onBeforeReload(this, function () {
                        self.win.removeClass('no-scroll');


                        Desktop.getAjaxContent({url: url}, function (data) {

                            if (enabledScroll) {
                                self.settings.enableContentScrollbar = enabledScroll;
                            }

                            if (data.pageCurrentTitle) {
                                self.settings.WindowTitle = data.pageCurrentTitle;
                                self.settings.originalTitle = false;
                                self.set('WindowTitle', data.pageCurrentTitle);
                            }

                            w.slideUp(10, function () {
                                w.empty().append(data.maincontent);

                                w.slideDown(20, function () {
                                    if (typeof self.settings.onAfterCreated === 'function') {
                                        self.settings.onAfterCreated(self, function () {

                                            self.win.removeClass('loading');
                                            if (typeof self.settings.onBeforeShow === 'function') {
                                                self.settings.onBeforeShow(null, self, function () {
                                                    self.enableWindowScrollbar();
                                                    if (typeof self.settings.onAfterShow === 'function') {
                                                        var doBodyHeight = self.$el.height() - self.getHeaderHeight() - self.getStatusbarHeight();
                                                        self.settings.onAfterShow(null, self, {height: doBodyHeight, width: self.win.width()});
                                                    }

                                                    if (data.pageCurrentTitle) {
                                                        self.setTitle(data.pageCurrentTitle);
                                                        self.settings.WindowTitle = data.pageCurrentTitle;
                                                        self.settings.originalTitle = false;
                                                    }

                                                    Win.redrawWindowHeight(false, (self.settings.enableContentScrollbar ? true : false));
                                                    if (typeof callback === 'function') {
                                                        callback(data);
                                                    }

                                                });
                                            }
                                            else {
                                                self.enableWindowScrollbar();
                                                if (typeof self.settings.onAfterShow === 'function') {
                                                    var doBodyHeight = self.$el.height() - self.getHeaderHeight() - self.getStatusbarHeight();
                                                    self.settings.onAfterShow(null, self, {height: doBodyHeight, width: self.win.width()});
                                                    Win.redrawWindowHeight(false, (self.settings.enableContentScrollbar ? true : false));
                                                }

                                                if (data.pageCurrentTitle) {
                                                    self.setTitle(data.pageCurrentTitle);
                                                    self.settings.WindowTitle = data.pageCurrentTitle;
                                                    self.settings.originalTitle = false;
                                                }


                                                if (typeof callback === 'function') {
                                                    callback(data);
                                                }
                                            }


                                        }, $(w));
                                    }
                                    else {
                                        self.win.removeClass('loading');
                                        if (typeof self.settings.onBeforeShow === 'function') {
                                            self.settings.onBeforeShow(null, self, function () {
                                                self.enableWindowScrollbar();
                                                if (typeof self.settings.onAfterShow === 'function') {
                                                    var doBodyHeight = self.$el.height() - self.getHeaderHeight() - self.getStatusbarHeight();
                                                    self.settings.onAfterShow(null, self, {height: doBodyHeight, width: self.win.width()});
                                                }

                                                if (data.pageCurrentTitle) {
                                                    self.setTitle(data.pageCurrentTitle);
                                                    self.settings.WindowTitle = data.pageCurrentTitle;
                                                    self.settings.originalTitle = false;
                                                }

                                                Win.redrawWindowHeight(false, (self.settings.enableContentScrollbar ? true : false));
                                                if (typeof callback === 'function') {
                                                    callback(data);
                                                }
                                            });
                                        }
                                        else {
                                            self.enableWindowScrollbar();
                                            if (typeof self.settings.onAfterShow === 'function') {
                                                var doBodyHeight = self.$el.height() - self.getHeaderHeight() - self.getStatusbarHeight();
                                                self.settings.onAfterShow(null, self, {height: doBodyHeight, width: self.win.width()});
                                            }

                                            if (data.pageCurrentTitle) {
                                                self.setTitle(data.pageCurrentTitle);
                                                self.settings.WindowTitle = data.pageCurrentTitle;
                                                self.settings.originalTitle = false;
                                            }

                                            Win.redrawWindowHeight(false, (self.settings.enableContentScrollbar ? true : false));
                                            if (typeof callback === 'function') {
                                                callback(data);
                                            }
                                        }

                                    }


                                });
                            });
                        });
                    });
                }
                else {
                    this.disableWindowScrollbar(true);
                    self.win.removeClass('no-scroll');
                    Desktop.getAjaxContent({url: url}, function (data) {

                        w.slideUp(10, function () {
                            if (data.pageCurrentTitle) {
                                self.settings.WindowTitle = data.pageCurrentTitle;
                                self.settings.originalTitle = false;
                                self.set('WindowTitle', data.pageCurrentTitle);
                            }

                            if (enabledScroll) {
                                self.settings.enableContentScrollbar = enabledScroll;
                            }


                            w.empty().append(data.maincontent);


                            w.slideDown(50, function () {

                                if (typeof self.settings.onAfterCreated === 'function') {
                                    self.settings.onAfterCreated(self, function () {

                                        self.win.removeClass('loading');
                                        if (typeof self.settings.onBeforeShow === 'function') {
                                            self.settings.onBeforeShow(null, self, function () {
                                                self.enableWindowScrollbar();
                                                if (typeof self.settings.onAfterShow === 'function') {
                                                    var doBodyHeight = self.$el.height() - self.getHeaderHeight() - self.getStatusbarHeight();
                                                    self.settings.onAfterShow(null, self, {height: doBodyHeight, width: self.win.width()});
                                                }

                                                if (data.pageCurrentTitle) {
                                                    self.setTitle(data.pageCurrentTitle);
                                                    self.settings.WindowTitle = data.pageCurrentTitle;
                                                    self.settings.originalTitle = false;
                                                }
                                                Win.redrawWindowHeight(false, (self.settings.enableContentScrollbar ? true : false));
                                                if (typeof callback === 'function') {
                                                    callback(data);
                                                }

                                                return 'loaded';
                                            });
                                        }
                                        else {
                                            self.enableWindowScrollbar();
                                            if (typeof self.settings.onAfterShow === 'function') {
                                                var doBodyHeight = self.$el.height() - self.getHeaderHeight() - self.getStatusbarHeight();
                                                self.settings.onAfterShow(null, self, {height: doBodyHeight, width: self.win.width()});
                                            }

                                            if (data.pageCurrentTitle) {
                                                self.setTitle(data.pageCurrentTitle);
                                                self.settings.WindowTitle = data.pageCurrentTitle;
                                                self.settings.originalTitle = false;
                                            }

                                            Win.redrawWindowHeight(false, (self.settings.enableContentScrollbar ? true : false));
                                            if (typeof callback === 'function') {
                                                callback(data);
                                            }

                                            return 'loaded';
                                        }


                                    }, $(w));
                                }
                                else {
                                    self.win.removeClass('loading');
                                    if (typeof self.settings.onBeforeShow === 'function') {
                                        self.settings.onBeforeShow(null, self, function () {
                                            self.enableWindowScrollbar();
                                            if (typeof self.settings.onAfterShow === 'function') {
                                                var doBodyHeight = self.$el.height() - self.getHeaderHeight() - self.getStatusbarHeight();
                                                self.settings.onAfterShow(null, self, {height: doBodyHeight, width: self.win.width()});
                                            }

                                            if (data.pageCurrentTitle) {
                                                self.setTitle(data.pageCurrentTitle);
                                                self.settings.WindowTitle = data.pageCurrentTitle;
                                                self.settings.originalTitle = false;
                                            }

                                            Win.redrawWindowHeight(false, (self.settings.enableContentScrollbar ? true : false));
                                            if (typeof callback === 'function') {
                                                callback(data);
                                            }

                                            return 'loaded';
                                        });
                                    }
                                    else {
                                        self.enableWindowScrollbar();
                                        if (typeof self.settings.onAfterShow === 'function') {
                                            var doBodyHeight = self.$el.height() - self.getHeaderHeight() - self.getStatusbarHeight();
                                            self.settings.onAfterShow(null, self, {height: doBodyHeight, width: self.win.width()});
                                        }

                                        if (data.pageCurrentTitle) {
                                            self.setTitle(data.pageCurrentTitle);
                                            self.settings.WindowTitle = data.pageCurrentTitle;
                                            self.settings.originalTitle = false;
                                        }

                                        Win.redrawWindowHeight(false, (self.settings.enableContentScrollbar ? true : false));
                                        if (typeof callback === 'function') {
                                            callback(data);
                                        }

                                        return 'loaded';
                                    }


                                }
                            });
                        });
                    });
                }
            }

            return false;
        };
        /**
         *
         */
        wm.destroy = function () {
            deleteBodyDataWindowID(this.id);
            $(this.OriginalEl).hide().insertAfter(this.$el);
            this.$el.remove();
            $(this.OriginalEl).show();
            $('body').data('FocusWindow', false);
            this.OriginalEl = null;
        };

        // Run initializer
        wm.init();

        return wm;
    };



    $.WindowManager.defaultOptions = {
        enableContentScrollbar: true,
        rollback: false,
        scrollbarOpts: {
            //height: parseInt($('.table-mm-container > :first', $('#'+windowID)).height() ),
            showArrows: false,
            scrollbarWidth: 15,
            arrowSize: 16,
            onScroll: null
        },
        subCache: [],
        WindowID: null,
        Skin: 'Apple',
        ExtraClasses: '',
        UseWindowIcon: true,
        nopadding: false,
        Url: null,
        WindowTitle: 'DreamCMS Window...',
        WindowToolbar: false,
        WindowContent: false,
        WindowStatusbar: false,
        Controller: null, // this will set the attribute app to the window
        Action: null,
        isGridWindow: false,
        hasGridAction: false,
        isRootApplication: false,
        isSingleWindow: false,
        singleWindow: null, // the root window instance will used in $.fn.windowManager
        isActive: false,
        versioning: false,
        onBeforeShow: function (event, _wm, callback) {
            //Application.onBeforeWindowShow(event, _wm);

            if (typeof callback === 'function') {
                callback();
            }

        },
        onAfterShow: function (event, _wm, callback) {
            Application.onAfterWindowShow(event, _wm, callback);
        },
        onBeforeClose: function (event, _wm, callback) {
            Application.onBeforeWindowClose(event, _wm, callback);
        },
        onAfterClose: function (event, _wm, callback) {
            Application.onAfterWindowClose(event, _wm, callback);
        },
        onFocus: function (event, _wm, callback) {
            //Application.onFocusWindow(event, _wm);

            if (typeof callback === 'function') {
                callback();
            }
        },
        onUnFocus: function (event, _wm, callback) {
            Application.onUnFocusWindow(event, _wm, callback);
        },
        onBeforeReload: function (_wm, callback) {
            if (typeof callback === 'function') {
                callback();
            }
        },
        onWindowDragStop: function (_wm, callback) {
            Application.onWindowDragStop(_wm, callback);
        },
        PositionLeft: false,
        PositionTop: false,
        minWidth: 300,
        minHeight: 200,
        Width: 800,
        Height: 400,
        //
        UseTaskbar: true,
        TaskbarHeight: 0,
        ShowTaskbarLabel: false,
        DesktopIconWidth: 48,
        DesktopIconHeight: 48,
        UseDesktopIcon: true,
        DesktopIconFile: false,
        // Window events
        Draggable: true,
        Resizable: true,
        Maximize: true,
        Minimize: true,
        Closable: true,
        isSubContentVisible: false,
        addFileSelector: false

    };
    $.fn.windowManager = function (options) {

        return this.each(function () {
            if (!$(this).data("WindowManager")) {


                var wm = new $.WindowManager(this, options);
                return $(this).data("WindowManager");
            }
            else {

                if (typeof options.singleWindow === 'object') {
                    return $(this).data("WindowManager").createSingleWindow(options);
                }
                else if (options.isSingleWindow === true && typeof options.singleWindow !== 'object') {
                    options.singleWindow = $(this).data("WindowManager");
                    return $(this).data("WindowManager").createSingleWindow(options);
                }

                return $(this).data("WindowManager");
            }
        });
    };

    // This function breaks the chain, but returns
    // the WindowManager if it has been attached to the object.
    $.fn.getWindow = function () {
        if ($(this).data("WindowManager"))
            return $(this).data("WindowManager");
        return null;
    };

    $.fn.refreshScrollbars = function (event) {
        this.each(function () {
            if ($(this).hasClass('isWindowContainer')) {
                var instance = $(this).data("WindowManager");
                if (instance) {
                    instance.disableWindowScrollbar();
                    instance.enableWindowScrollbar();
                }
            }
        });
    };

    $.fn.focus = function (event) {
        this.each(function () {
            if ($(this).hasClass('isWindowContainer')) {
                var instance = $(this).data("WindowManager");
                if (instance) {
                    instance.focus();
                }
            }
        });
    };
    $.fn.unfocusWindow = function (event) {
        this.each(function () {

            if ($(this).hasClass('isWindowContainer')) {
                var instance = $(this).data("WindowManager");
                if (instance) {
                    ///  Debug.log('Unfocus window');
                    instance.unfocus();
                }
            }
            else if (!$(event.target).hasClass('isWindowContainer') && $(event.target).parents('.isWindowContainer').length === 0) {
                var instance = $(this).data("WindowManager");
                if (instance) {
                    instance.unfocus();
                }
            }

        });
    };
})(jQuery);

