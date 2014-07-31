(function (jQuery) {

    $.WindowManager = function (el, options) {
        var self = this;
        var windowOpenAnimationTime = 400;
        var windowCloseAnimationTime = 250;
        var windowRestoreAnimationTime = 500;
        var windowMinAnimationTime = 250;
        var windowMaxAnimationTime = 300;

        // Access to jQuery and DOM versions of element
        self.$el = $(el);
        self.el = el;
        self.OriginalEl = $(el).clone();




        if (!defined(options.WindowID) && self.$el.attr('id') != '') {
            options.WindowID = self.$el.attr('id');
        }

        // default zIndex
        self.WindowZIndex = 1000;
        self.activeWindowZIndex = false;

        // 
        self.lastFocusedWindowID = false;

        // the window id
        self.id = false;

        // get settings
        self.settings = $.extend({}, $.WindowManager.defaultOptions, options);

        // is in resize event
        self.isResizeing = false;

        self.baseSize = null;


        function defined (x) {
            return (typeof x != "undefined");
        }

        function isFunction (x) {
            return (typeof x != "undefined" && typeof x == "function");
        }

        function isObject (x) {
            return (typeof x != "undefined" && typeof x == "object");
        }


        function cleanUrl (str) {
            str = str.replace(/^http(s):\/\//i, '#');
            str = str.replace(/&amp;/, '&');
            var strArr = str.split('/'), tmp = strArr.shift();

            if (strArr[0] !== '#') {
                strArr.unshift(tmp);
            }

            return strArr.join('/').replace(/^#/, '');
        }



        function getWindowTemplate (tplName) {
            var id = self.id,
                    settings = self.settings,
                    windowTemplate;


            self.hasToolbar = true;

            var useToolbar = true, winWidth = (settings.minWidth > settings.Width ? settings.minWidth : settings.Width);
            var winHeight = (settings.minHeight > settings.Height ? settings.minHeight : settings.Height);

            windowTemplate = '<div id="windowheader-' + id + '" class="window-tl">'
                    + '      <div class="window-tr">'
                    + '          <div class="window-tc';


            if (settings.Toolbar === false || settings.Toolbar === null || settings.Toolbar === '') {
                windowTemplate += ' no-toolbar';
                useToolbar = false;
                self.hasToolbar = false;
            }

            windowTemplate += '"'; // close class attribute


            if (settings.UseWindowIcon === true && typeof settings.DesktopIconFile === 'string' && settings.DesktopIconFile !== '') {
                windowTemplate += ' style="background-image: url(\'' + settings.DesktopIconFile + '\')"';
            }

            windowTemplate += '>'; // close div window-tc


            // -----------------------------------------------------------------
            // window title and window buttons here
            windowTemplate += '<div class="win-buttons">';

            if (settings.Closable === true) {
                windowTemplate += '     <div class="winbtn win-close-btn"></div>';
            }

            if (settings.Minimize === true) {
                windowTemplate += '     <div class="winbtn win-min-btn"></div>';
            }

            if (settings.Maximize === true) {
                windowTemplate += '     <div class="winbtn win-max-btn"></div>';
            }

            windowTemplate += '</div>';

            if (settings.Maximize === true) {
                windowTemplate += '     <div class="winbtn win-fullscreen-btn"></div>';
            }

            windowTemplate += '<span class="win-title">' + settings.Title + '</span>';
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


            if (useToolbar) {
                windowTemplate
                        += '<div id="toolbar-' + id + '" class="window-toolbar" style="">'   // add with width only
                        + '     <div class="root">' + settings.Toolbar + '</div>'
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

            var rootContent = '<div id="' + id + '-content" class="sub-content root"><div class="sub-content-inner">' + settings.Content + '</div></div>';

            windowTemplate
                    += '<div id="body-' + id + '" class="window-body" style="">' // with css width
                    + '      <div class="window-body-wrap">'
                    + '          <div id="body-content-' + id + '" class="window-body-content" style="width:' + winWidth + 'px;">' // with css width and height and scrolling
                    //+ '               <div class="win-content-scrollbar scrollbar"><div class="slider"></div></div>'
                    + '               <div class="win-content">' + $(rootContent).html() + '</div>'
                    + '          </div>'
                    + '      </div>'
                    + '</div>';

            windowTemplate +=
                    '               </div>' // end window-mc
                    + '          </div>'
                    + '      </div>' // end window-ml

                    + '      <div class="window-footer window-bl">'
                    + '          <div class="window-br">'
                    + '              <div class="window-bc" id="statusbar-' + id + '">'
                    + '                 <div class="win-statusbar" style="' + (typeof settings.Statusbar != 'string' ? 'display:none' : '') + '">'

                    + '<div class="window-panel-btn-left" id="pane-toggle-left-' + id + '"><span></span></div>'
                    + '<div class="win-statusbar-content">'
                    + (typeof settings.Statusbar == 'string' ? '' + settings.Statusbar + '' : '')
                    + '</div><div class="window-panel-btn-right" id="pane-toggle-right-' + id + '"><span></span></div>'
                    + '                 </div>'
                    + '              </div>' // end window-bc
                    + '          </div>'
                    + '      </div>' // end window-footer

                    + '  </div>'; // end window-inner-wrap

            return $(windowTemplate);
        }


        createSingleWindow = function (opts) {
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

                    self.executeEvent('onAfterCreated', null, function () {
                        self.setTitle(singlesettings.WindowTitle);
                        self.settings.isSubContentVisible = true;
                        animateToSingleWindow(id, singlesettings.WindowID, true);
                        self.$el.find('#' + id + '-toolbar .switchHome').removeClass('disabled');
                        self.$el.find('#' + id + '-toolbar .switchLeft').addClass('disabled');
                        self.$el.find('#' + id + '-toolbar .switchRight').addClass('disabled');
                        self.stopWinContentChangeTrigger = false;
                    }, singlesettings.WindowContent);



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



            self.executeEvent('onAfterCreated', null, function () {
                animateToSingleWindow(id, singlesettings.WindowID, isSubContent, true);
                self.stopWinContentChangeTrigger = false;
            }, singlesettings.WindowContent);



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



        function setWindowRestoreSettings () {
            var winWidth = self.win.width(), h = self.win.height(), offset = self.win.offset();
            self.restoreSettings = {
                status: self.settings.status,
                top: offset.top,
                left: offset.left,
                winWidth: winWidth,
                winHeight: h,
                bodyHeight: parseInt(h, 0) - (self.getHeaderHeight() + self.getStatusbarHeight()),
                bodyWidth: winWidth
            };
        }

        function initWindowSizes () {
            self.win.css({
                visibility: 'hidden'
            }).show();

            var bodyWidth = (self.settings.minWidth > self.settings.Width ? self.settings.minWidth : self.settings.Width);
            var winContentHeight = (self.settings.minHeight > self.settings.Height ? self.settings.minHeight : self.settings.Height);
            self.win.width(bodyWidth);
            self.BodyContent.height(winContentHeight);

            self.win.hide().css({
                visibility: ''
            });
        }

        function updateWindowSizeCache (updateBase) {

            var pos = self.$el.offset();

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

        function initFocusAndBlur () {

            self.$el.bind('click.windows', function (e) {
                self.focus(e, self.id);
                $('body').data('FocusWindow', self.id);

            }).bind('mousedown.windows', function (e) {
                self.focus(e, self.id);
                $('body').data('FocusWindow', self.id);
            });

            self.Toolbar.bind('changeHeight.windows', function (e) {
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
            self.$el.bind('winContentChange', function (e) {
                if (self.settings.enableContentScrollbar && !self.stopWinContentChangeTrigger) {
                    var doBodyHeight = $(self.win).height() - self.getHeaderHeight() - self.getStatusbarHeight();
                    self.BodyContent.height(doBodyHeight);
                    self.enableWindowScrollbar();
                }
            });
        }

        function initButtonEvents () {

            self.closeBtn.bind('click.winbutton', function (e) {
                self.focus();
                self.close(e);
            });

            if (self.settings.Minimize === true) {
                self.minBtn.bind('click.winbutton', function (e) {

                    if (self.settings.status == 'closed') {
                        return;
                    }

                    self.focus(null, self.id);

                    // update the cache before change the size
                    updateWindowSizeCache(true);

                    self.enableWindowDraggable();
                    self.enableWindowResizeable();

                    self.unfocus();
                    animateWindow('min', e);
                });
            }

            if (self.settings.Maximize == true) {

                self.maxBtn.bind('click.winbutton', function () {

                    self.focus();
                    if (self.settings.status === 'max') {

                        self.enableWindowDraggable();
                        self.enableWindowResizeable();

                        animateWindow('restore');
                        return;
                    }

                    // update the cache before change the size
                    updateWindowSizeCache();
                    animateWindow('max');
                });

                self.fullscreenBtn.bind('click.winbutton', function (e) {
                    Desktop.switchFullscreen = true;
                    $('#fullscreenContainer').click();
                    return (false);
                });

                /**
                 *
                 * bind window header dblclick
                 */
                self.TitleBar.bind('dblclick.winbutton', function () {

                    self.focus(null, self.id);
                    if (self.settings.status === 'max') {
                        self.enableWindowDraggable();
                        self.enableWindowResizeable();
                        //self.settings.status = 'default';
                        animateWindow('restore');
                    }
                    else {
                        // update the cache before change the size
                        updateWindowSizeCache(true);
                        self.disableWindowDraggable();
                        self.disableWindowResizeable();
                        animateWindow('max');
                    }
                });
            }
        }

        function initWindowResizeDragging () {

            if (self.settings.Draggable) {
                self.$el.draggable({
                    distance: 1,
                    cancel: '.window-toolbar,.tinyMCE-Toolbar,.tabcontainer,.window-inner-wrap',
                    scroll: false,
                    revert: false,
                    cursor: 'move!important',
                    containment: '#desktop',
                    handle: '.window-tc',
                    start: function (event, ui) {
                        self.focus(event, self.id);
                        $(this).addClass('no-shadow');

                        // trigger drag start event
                        self.executeEvent('onWindowDragStart', event);
                    },
                    drag: function (event, ui)
                    {
                        if (ui.offset.top <= self.settings.TaskbarHeight || ui.position.top <= self.settings.TaskbarHeight) {
                            if (ui.offset.top - 1 <= self.settings.TaskbarHeight) {
                                self.el.style.top = self.settings.TaskbarHeight;
                            }
                            return false;
                        }
                    },
                    stop: function (event, ui) {
                        $(this).removeClass('no-shadow');

                        setTimeout(function () {
                            event.stopPropagation();

                            updateWindowSizeCache(true);

                            setWindowRestoreSettings();

                            // trigger drag stop event
                            self.executeEvent('onWindowDragStop', event);

                        }, 2);

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
                        self.settings.isActive = true;

                        self.win.addClass('active').addClass('no-shadow');


                        if (typeof self.settings.onResizeStartFS == 'function') {
                            self.settings.onResizeStartFS(event, ui, self);
                        }


                        self.executeEvent('onResizeStart', event, ui);


                    },
                    resize: function (event, ui) {

                        var w = ui.size.width, h = ui.size.height, setHeight = h - self.getHeaderHeight() - self.getStatusbarHeight();

                        self.Body.width(w).height(setHeight);
                        self.BodyContent.width(w).height(setHeight).each(function () {
                            if (typeof self.settings.onResizeFS === 'function') {
                                self.settings.onResizeFS(event, ui, self, {height: h, width: w, contentHeight: setHeight});
                            }

                            self.executeEvent('onResize', event, ui, {height: h, width: w, contentHeight: setHeight});
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

                        var w = ui.size.width, h = ui.size.height, setHeight = h - self.getHeaderHeight() - self.getStatusbarHeight();

                        self.Body.width(w).height(setHeight);
                        self.BodyContent.width(w).height(setHeight).each(function () {

                            self.isResizeing = false;

                            updateWindowSizeCache(true);

                            if (self.settings.enableContentScrollbar) {

                                self.enableWindowScrollbar();
                            }

                            setWindowRestoreSettings();

                            if (typeof self.settings.onResizeStopFS === 'function') {
                                var _self = $(this);
                                setTimeout(function () {
                                    self.settings.onResizeStopFS(event, ui, self, {height: setHeight, width: _self.width(), contentHeight: setHeight});
                                }, 10);
                            }

                            self.executeEvent('onResizeStop', event, ui, {height: h, width: w, contentHeight: setHeight});

                        });
                        event.stopPropagation();
                    }
                });
            }
        }

        function onAfterCreatedCallBack (_wm) {
            if (!self.settings.nopadding) {
                self.Content.addClass('add-padding');
            }

            if (self.settings.hasGridAction === true || self.hasToolbar) {
                self.TitleBar.find('.no-toolbar').removeClass('no-toolbar');
            }

            self.stopWinContentChangeTrigger = true;
            self.firstRun = true;

            // set window sizes
            initWindowSizes();

            // init all window Events
            initWindowResizeDragging();

            // init the titlebar button events
            initButtonEvents();

            setTimeout(function () {
                self.focus(null, self.id);

                // show the window
                animateWindow('show');

                initFocusAndBlur();
                self.Toolbar.trigger('changeHeight');
                self.stopWinContentChangeTrigger = false;

                document.body.style.cursor = 'default';

                updateWindowSizeCache(true);
            }, 50);
        }


        function animateWindow (what, e, callback) {
            self.$el.stop();

            switch (what)
            {
                case 'show':
                    if ((self.settings.status === 'default' && !self.firstRun) || self.win.hasClass('animated')) {
                        return;
                    }

                    if (self.settings.enableContentScrollbar && !self.firstRun) {
                        self.disableWindowScrollbar();
                    }

                    self.focus(e, self.id);

                    self.executeEvent('onBeforeShow', e, function () {

                        if (self.settings.hasGridAction === true || self.hasToolbar || self.Toolbar.find('div:not(.root,.empty-toolbar)').length > 0) {
                            self.TitleBar.find('.no-toolbar').removeClass('no-toolbar');
                        }

                        self.animateShow();
                    });

                    break;


                case 'restore':

                    if (self.settings.hasGridAction === true) {
                        self.TitleBar.find('.no-toolbar').removeClass('no-toolbar');
                    }

                    WindowAnimation.AnimateRestore(self);
                    break;

                case 'min':
                    setWindowRestoreSettings();

                    if (self.settings.enableContentScrollbar) {
                        self.disableWindowScrollbar();
                    }


                    self.executeEvent('onBeforeShow', e, function () {
                        if (self.settings.nopadding) {
                            self.Content.removeClass('add-padding');
                        }
                    });


                    if (Dock) {
                        if (e && $(e.target).parents('div.isWindowContainer').length == 1) {
                            Dock.hideApplication($(e.target).parents('div.isWindowContainer:first').attr('app'), $(e.target).parents('div.isWindowContainer:first').attr('id'), function (aniamteToObj) {
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

                    break;

                case 'max':

                    setWindowRestoreSettings();

                    if (self.settings.enableContentScrollbar) {
                        self.disableWindowScrollbar();
                    }

                    self.executeEvent('onResizeStart', e);

                    WindowAnimation.AnimateMax(self);

                    break;

                case 'close':
                    if (self.settings !== null && self.win !== null) {
                        var id = self.id;

                        if (self.settings.enableContentScrollbar !== null && self.settings.enableContentScrollbar) {
                            self.disableWindowScrollbar();
                        }

                        self.executeEvent('onBeforeClose', e, function (callback) {

                            delete self.settings.subCache[id];

                            self.animateClose(e, callback);
                            self.settings.status = 'closed';
                            self.unfocus()
                        });
                    }
                    break;
            }
        }



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


        function bindSingleWindowSwitchButtons () {

            $('.switchHome', self.$el).unbind('click.windownav').bind('click.windownav', function () {
                if (!$(this).hasClass('disabled')) {
                    self.settings.isSubContentVisible = false;
                    $(this).addClass('disabled');
                    var visibleSubId = $('.sub-content:visible', self.$el).attr('id').replace('-content', '');
                    Win.unload(self.id);
                    self.resetTitle();
                    animateToSingleWindow(visibleSubId, self.id, false);

                }
            });

            $('.switchLeft', self.$el).unbind('click.windownav').bind('click.windownav', function () {

            });

            $('.switchRight', self.$el).unbind('click.windownav').bind('click.windownav', function () {

            });
        }
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

            self.win.css({
                'overflow': 'hidden'
            });

            self.BodyContent.css({
                'overflow': 'hidden'
            }).find('.win-content').css({
                'overflow': 'hidden'
            });

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

                                    self.settings.isSubContentVisible = true;

                                    self.win.css({
                                        'overflow': ''
                                    });

                                    self.BodyContent.css({
                                        'overflow': ''
                                    }).find('.win-content').css({
                                        'overflow': ''
                                    });
                                    Body.css({overflow: ''});
                                    BodyContent.css({overflow: '', top: '', position: ''}); //.find('div.pane').show();


                                    $(this).css({overflow: '', zIndex: '', top: '', height: '', position: '', width: ''});

                                    if (self.settings.enableContentScrollbar) {
                                        self.enableWindowScrollbar();
                                    }

                                    updateWindowSizeCache(false);
                                    self.executeEvent('onAfterCreated', null, function () {
                                        self.stopWinContentChangeTrigger = false;
                                    }, showContent.html());

                                    self.executeEvent('onAfterShow', null);
                                    self.executeEvent('onResizeStop', null, null, self, {height: setContentHeight, width: $(this).width()});

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


                                    self.win.css({
                                        'overflow': 'hidden'
                                    });
                                    self.win.css({
                                        'overflow': ''
                                    });

                                    self.BodyContent.css({
                                        'overflow': ''
                                    }).find('.win-content').css({
                                        'overflow': ''
                                    });
                                    self.settings.isSubContentVisible = true;

                                    Body.css({overflow: ''});
                                    BodyContent.css({overflow: ''}); //.find('div.pane').show();
                                    $(this).css({overflow: '', height: '', zIndex: '', width: '', position: '', top: ''});



                                    if (self.settings.enableContentScrollbar) {
                                        self.enableWindowScrollbar();
                                    }

                                    updateWindowSizeCache(false);
                                    self.executeEvent('onAfterCreated', null, function () {
                                        self.stopWinContentChangeTrigger = false;
                                    }, showContent.html());
                                    self.executeEvent('onAfterShow', null);
                                    self.executeEvent('onResizeStop', null, null, self, {height: setContentHeight, width: $(this).width()});

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

                                self.settings.isSubContentVisible = true;

                                self.win.css({
                                    'overflow': ''
                                });

                                self.BodyContent.css({
                                    'overflow': ''
                                }).find('.win-content').css({
                                    'overflow': ''
                                });
                                Body.css({overflow: bodyOverflowBase});
                                BodyContent.css({overflow: bodyContentOverflowBase});

                                $(this).css({overflow: contentOverflowBase, zIndex: '', 'top': '', 'position': '', width: '', height: ''});

                                updateWindowSizeCache(false);

                                if (self.settings.enableContentScrollbar) {
                                    self.enableWindowScrollbar();
                                }
                                self.executeEvent('onAfterCreated', null, function () {
                                    self.stopWinContentChangeTrigger = false;
                                }, showContent.html());

                                self.executeEvent('onAfterShow', null);
                                self.executeEvent('onResizeStop', null, null, self, {height: setContentHeight, width: $(this).width()});

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

                            self.settings.isSubContentVisible = false;

                            self.win.css({
                                'overflow': ''
                            });

                            self.BodyContent.css({
                                'overflow': ''
                            }).find('.win-content').css({
                                'overflow': ''
                            });
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


        self.updateWindowSizeCache = function (updateBase) {
            updateWindowSizeCache(updateBase);
        };

        self.init = function () {

            if ($('body').data('WindowZIndex') === null || $('body').data('WindowZIndex') === undefined) {
                $('body').data('WindowZIndex', this.WindowZIndex);
            }

            if ($('body').data('Windows') === null || $('body').data('Windows') === undefined) {
                $('body').data('Windows', []);
            }

            if ($('body').data('FocusWindow') === null || $('body').data('FocusWindow') === undefined) {
                $('body').data('FocusWindow', false);
            }

            if ((!Tools.isString(self.settings.WindowID) && self.settings.isSingleWindow === false) && (!Tools.isString(self.settings.Url) || self.settings.Url === '')) {
                document.body.style.cursor = '';
                Debug.error('Please set a ID for the window, before create the window!');
                return false;
            }

            if (!Tools.isString(self.settings.WindowID) && !Tools.isString(self.settings.Url)) {
                document.body.style.cursor = '';
                Debug.error('Please set a ID for the window, before create the window!');
                return false;
            }

            document.body.style.cursor = 'progress';
            self.unfocusOthers();
            
            
            self.settings = $.extend({}, $.WindowManager.defaultOptions, options);



            if ((!self.settings.Controller || !self.settings.Action)) {
                var appInfo = Tools.extractAppInfoFromUrl(self.settings.Url);
                if (!self.settings.Controller && typeof appInfo.controller === 'string') {
                    self.settings.Controller = appInfo.controller;
                }

                if (!self.settings.Action && typeof appInfo.action === 'string') {
                    self.settings.Action = appInfo.action;
                }
            }

            // generate windowid by the url
            if (!Tools.isString(self.settings.WindowID)) {
                self.settings.urlClean = cleanUrl(self.settings.Url);
                self.settings.WindowID = 'w' + md5(self.settings.urlClean).substr(0, 12);
            }

            // Win.windowID = this.settings.WindowID;
            self.id = self.settings.WindowID;

            // if not content is giving the set the html code of the element
            if (!self.settings.Content && $('#' + self.id).length == 1) {
                self.settings.Content = $('#' + self.id).html();
            }


            if (self.settings.Controller && $('div.isWindowContainer[app=' + self.settings.Controller + ']').length === 0) {
                self.settings.isRootApplication = true;
            }



            if (self.settings.isSingleWindow === false || self.settings.isRootApplication === true || (self.settings.isSingleWindow != false && self.settings.singleWindow === null)) {

                // if exists then return
                if ($('#' + self.id).length == 1 && $('#' + self.id).data('WindowManager')) {
                    document.body.style.cursor = '';
                    $('#' + self.id).trigger('click');
                    return;
                }

                self.buildDefaultWindow();
            }
            else if (self.settings.isRootApplication !== true && self.settings.isSingleWindow !== false && self.settings.singleWindow !== null) {
                if (typeof self.settings.singleWindow.createSingleWindow !== 'function') {
                    var w = $(self.settings.singleWindow).data('WindowManager');
                    if (w) {
                        w.createSingleWindow(self.settings);
                        return;
                    }
                }
                else if (typeof self.settings.singleWindow.createSingleWindow === 'function') {
                    self.settings.singleWindow.createSingleWindow(self.settings);
                    return;
                }

                document.body.style.cursor = '';
                console.error('Could not create the Single Window! Instance for the root window not exists!');
            }
            else {
                document.body.style.cursor = '';
                console.error('Could not create the Window! You will create a undefined Window??? Please check the window config.');
            }
        };




        self.buildDefaultWindow = function () {
            self.activeWindowZIndex = $('body').data('activeWindowZIndex');
            self.WindowZIndex = $('body').data('WindowZIndex');

            self.settings.originalTitle = self.settings.Title;


            var winWidth = (self.settings.minWidth > self.settings.Width ? self.settings.minWidth : self.settings.Width);
            var winHeight = (self.settings.minHeight > self.settings.Height ? self.settings.minHeight : self.settings.Height);

            $('#' + self.id).attr('class', 'isWindowContainer ' + self.settings.Skin + 'Container active loading').css({
                visibility: 'hidden',
                opacity: '0',
                zIndex: self.WindowZIndex + 1
            }).height(winHeight).width(winWidth);


            // create by template
            var $win = getWindowTemplate('default');


            if (self.settings.Controller === 'plugin') {
                var pluginName = $.getURLParam('plugin', self.settings.Url);
                if (pluginName) {
                    $('#' + self.id).attr('app', pluginName);
                }
            }
            else {
                if (self.settings.Controller) {
                    $('#' + self.id).attr('app', self.settings.Controller);
                }
            }

            if (self.settings.nopadding) {
                $('#' + self.id).addClass('no-padding');
            }



            $('#' + self.id)
                    .attr('activespace', Desktop.currentSpace).attr('uniqueid', new Date().getTime())
                    .data("WindowManager", self).append($win);



            self.win = self.$el;
            self.el = self.$el.get(0); // refresh el

            if (self.settings.rollback === true) {
                self.win.attr('rollback', true);
            }


            var wins = $('body').data('Windows');

            if (typeof wins != 'undefined') {
                wins.push(self.id);
            }
            else {
                wins = [self.id];
            }
            $('body').data('Windows', wins);



            self.TitleBar = $('#windowheader-' + self.id);
            self.Toolbar = $('#toolbar-' + self.id);
            self.Body = $('#body-' + self.id);
            self.BodyContent = $('#body-content-' + self.id);
            self.Content = $('div.win-content:first', self.BodyContent);
            self.Statusbar = $('#statusbar-' + self.id);
            self.WindowPanelLeft = $('#panel-left-' + self.id);
            self.WindowPanelRight = $('#panel-right-' + self.id);
            self.WindowWrapper = $('#wrapper-' + self.id);

            self.closeBtn = $('.win-close-btn', self.TitleBar);
            self.minBtn = $('.win-min-btn', self.TitleBar);
            self.maxBtn = $('.win-max-btn', self.TitleBar);
            self.fullscreenBtn = $('.win-fullscreen-btn', self.TitleBar);


            if (self.Toolbar.find('#window-nav').length === 0 && self.settings.isSingleWindow) {
                var historyTpl = $('<div id="window-nav"><button type="button" class="switchHome"><span></span></button> <button type="button" class="switchLeft"><span></span></button> <button type="button" class="switchRight"><span></span></button></div>');
                historyTpl.show();

                self.Toolbar.prepend(historyTpl).show();
                self.Toolbar.css({
                    visibility: ''
                }).find('.root:first').attr('id', self.id + '-toolbar').show();

                self.TitleBar.find('.no-toolbar').removeClass('no-toolbar');


                var rootContent = $('#' + self.id + '-content');

                if (rootContent.length == 0) {
                    rootContent = $('<div id="' + self.id + '-content" class="sub-content"/>');
                    rootContent.append($(self.Content).clone().html());
                    $(self.Content).empty().wrapInner(rootContent);

                    if (self.$el.find('#window-nav').length === 0) {
                        var historyTpl = $('<div id="window-nav"><button type="button" class="switchHome"><span></span></button> <button type="button" class="switchLeft"><span></span></button> <button type="button" class="switchRight"><span></span></button></div>');
                        historyTpl.show();
                        self.Toolbar.prepend(historyTpl).show();
                    }

                    self.Toolbar.find('.root:first').attr('id', self.id + '-toolbar').show();
                }


                bindSingleWindowSwitchButtons();

            }

            if (self.settings.versioning !== false && self.settings.versioning !== null) {

                var versHtml = $('<div class="versioning-container"/>');
                versHtml.html(self.settings.versioning);
                versHtml.append('<span class="button-group-label">' + cmslang.versions + '</span>');

                self.Toolbar.append(versHtml);
            }

            self.$el.data("WindowManager", self);

            self.executeEvent('onAfterCreated', null, function () {
                onAfterCreatedCallBack(self);
            });
        };
        /**
         *
         * @param object opts
         * @returns void
         */
        self.createSingleWindow = function (opts) {
            var id = self.id, settings = self.settings, windowTemplate;
            var singlesettings = $.extend({}, $.WindowManager.defaultOptions, opts), isSubContent = false;

            self.stopWinContentChangeTrigger = true;

            if (typeof singlesettings.Url == 'string') {
                var clean_Url = cleanUrl(singlesettings.Url);
                singlesettings.WindowID = 'w' + md5(clean_Url).substr(0, 12);
                isSubContent = true;
            }

            if (self.settings.enableContentScrollbar) {
                self.disableWindowScrollbar();
            }

            self.Content.removeClass('add-padding');

            if (singlesettings.nopadding) {
                self.Content.addClass('force-no-padding');
            }

            // 
            Win.unload(self.id);


            // create hashes for dynamic caching :)
            var md5Co = md5(singlesettings.WindowContent);
            var md5Tb = md5(singlesettings.WindowToolbar);

            if ($('#' + singlesettings.WindowID + '-content', self.$el).length == 1) {
                setTimeout(function () {
                    self.$el.removeClass('loading').unmask();

                    self.$el.find('#window-nav .switchHome').removeClass('disabled');

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

                    self.setTitle(singlesettings.WindowTitle);
                    animateToSingleWindow(id, singlesettings.WindowID, true);
                    self.setTitle(singlesettings.WindowTitle);

                    self.settings.isSubContentVisible = true;
                    self.$el.find('.switchHome').removeClass('disabled');
                    self.$el.find('.switchLeft').addClass('disabled');
                    self.$el.find('.switchRight').addClass('disabled');




                }, 80);

                return;
            }

            self.$el.removeClass('loading').unmask();

            var rootContent = $('#' + id + '-content');

            if (rootContent.length === 0) {

                console.log('build rootContent');

                rootContent = $('<div id="' + id + '-content" class="sub-content"/>');
                rootContent.append($(self.Content).clone().html());
                $(self.Content).empty().wrapInner(rootContent);
                //this.Content.empty().append(rootContent);

                if (self.$el.find('#window-nav').length === 0) {
                    var historyTpl = $('<div id="window-nav"><button type="button" class="switchHome"><span></span></button> <button type="button" class="switchLeft"><span></span></button> <button type="button" class="switchRight"><span></span></button></div>');
                    historyTpl.show();
                    self.Toolbar.prepend(historyTpl).show();
                }


                self.Toolbar.find('.root:first').attr('id', id + '-toolbar').show();
                bindSingleWindowSwitchButtons();
            }
            else if (self.$el.find('#window-nav').length === 0) {
                console.log('build window-nav');

                var historyTpl = $('<div id="window-nav"><button type="button" class="switchHome"><span></span></button> <button type="button" class="switchLeft"><span></span></button> <button type="button" class="switchRight"><span></span></button></div>');
                historyTpl.show();
                self.Toolbar.prepend(historyTpl).show();
                self.Toolbar.find('.root:first').attr('id', id + '-toolbar').show();
                bindSingleWindowSwitchButtons();
            }


            if (typeof self.settings.windowSizeCache.baseWidth == 'undefined' || self.settings.windowSizeCache.baseWidth === null) {
                self.settings.windowSizeCache.baseWidth = self.settings.windowSizeCache.width;
                self.settings.windowSizeCache.baseHeight = self.settings.windowSizeCache.height;
                self.settings.windowSizeCache.baseBodyWidth = self.settings.windowSizeCache.bodyWidth;
                self.settings.windowSizeCache.baseBodyHeight = self.settings.windowSizeCache.bodyHeight;
            }


            $('.switchHome', self.$el).removeClass('disabled');
            $('.switchLeft', self.$el).removeClass('disabled');
            $('.switchRight', self.$el).addClass('disabled');

            self.settings.subCache[singlesettings.WindowID] = {
                baseContent: singlesettings.WindowContent,
                toolbar: md5Tb,
                content: md5Co
            };

            var singleContent = $('<div id="' + singlesettings.WindowID + '-content" class="sub-content"></div>');

            if (Tools.isString(singlesettings.Url)) {
                singleContent.data('WindowURL', singlesettings.Url);
            }

            singleContent.append($(singlesettings.WindowContent)).hide();
            self.Content.append(singleContent);

            var singleToolbar = $('<div id="' + singlesettings.WindowID + '-toolbar" class="sub-toolbar"></div>');
            singleToolbar.append($(singlesettings.WindowToolbar)).hide();

            self.Toolbar.append(singleToolbar);

            self.setTitle(singlesettings.WindowTitle);
            self.settings.isSubContentVisible = true;

            animateToSingleWindow(id, singlesettings.WindowID, isSubContent, true);
            self.stopWinContentChangeTrigger = false;



        };

        /**
         * file selector sub slide window
         * 
         */
        self.toggleFileSelectorPanel = function (show) {
            if (self.settings.addFileSelector) {

                if (!show) {

                    var h = self.$el.find('#fm-slider').height();
                    $('#body-' + self.id).css({
                        overflow: 'hidden'
                    });
                    self.$el.find('#fm-slider').animate({
                        marginTop: 0 - h
                    }, {
                        queue: false,
                        duration: 250,
                        complete: function () {
                            $(this).hide(); //.find('div:first').empty();
                        }
                    });

                }
                else {
                    $('#body-' + self.id).css({
                        overflow: 'hidden'
                    });


                    self.$el.find('#fm-slider').show().css({
                        top: 0,
                        marginTop: 0 - self.$el.find('#fm-slider').height()
                    }).height($('#body-' + self.id).height() - 40).show();

                    var $fm = self.$el.find('#fm');
                    $fm.find('.treelistInner,.body').css({overflow: ''});
                    $fm.resizePanels(false);


                    self.$el.find('#fm-slider').animate({
                        marginTop: 0
                    }, {
                        queue: true,
                        duration: 250,
                        complete: function () {

                            $('#body-' + self.id).css({
                                overflow: 'visible'
                            });
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
                            }, 1);
                        }
                    });
                }
            }
        };

        /**
         * Set focus to the current window and reset focus to other
         * active windows
         */
        self.focus = function (event, setFocusWinID) {
            var setZindex = 0, focusedWinID = $('body').data('FocusWindow');

            if (focusedWinID == self.id) {
                return;
            }

            var activeWindowZIndex = parseInt($('body').data('activeWindowZIndex'), 10);
            var lastWindowZindex = parseInt($('body').data('WindowZIndex'), 10);

            if (setFocusWinID) {

                if (self.lastFocusedWindowID && !focusedWinID) {
                    focusedWinID = self.lastFocusedWindowID;
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
                    lastWindowZindex++;

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

            // trigger on focus event
            self.executeEvent('onFocus', event);

        };

        self.unfocus = function () {
            var focusedWinID = $('body').data('FocusWindow');
            if (self.settings.isActive === true || focusedWinID === self.id) {

                self.executeEvent('onUnFocus', null, function () {
                    $('body').data('FocusWindow', false);
                    self.settings.isActive = false;
                    self.$el.removeClass('active');
                    $('body').data('activeWindowZIndex', false);
                });
            }
        };

        /**
         * Unfocus all active Window
         */
        self.unfocusOthers = function (callback) {
            var wins = $('body').data('Windows');
            if (wins.length === 0) {
                if (isFunction(callback)) {
                    callback();
                    return;
                }
                else {
                    return;
                }
            }

            var focusedWinID = $('body').data('FocusWindow'), data = $('#' + focusedWinID).data("WindowManager");
            if (focusedWinID && data) {
                if (data.get('isActive') === true) {
                    data.unfocus();
                    if (isFunction(callback)) {
                        callback();
                        return;
                    }
                    else {
                        return;
                    }
                }
            }
            else {

                for (var x = 0; x < wins.length; ++x) {
                    var data = $('#' + wins[x]).data("WindowManager");
                    if (data)
                    {
                        data.unfocus();

                        if ((x + 1) >= wins.length && isFunction(callback)) {
                            callback();
                        }
                    }
                }
            }
        };

        var winshowTimout = null;

        self.animateShow = function () {

            if (self.windowInAnimation) {
                winshowTimout = setTimeout(function () {
                    self.animateShow();
                }, 1);
            }
            else {
                clearTimeout(winshowTimout);

                if (self.settings.hasGridAction === true) {
                    self.TitleBar.find('.no-toolbar').removeClass('no-toolbar');
                }

                if (self.Toolbar.find('.root').children().length == 0) {
                    self.Toolbar.find('.root').append($('<div/>').addClass('empty-toolbar'));
                }

                self.windowInAnimation = true;

                if (self.settings.nopadding) {
                    self.Content.removeClass('add-padding');
                }

                self.win.css({visibility: 'hidden', 'overflow': 'hidden'}).show();
                var winWidth = (self.settings.minWidth > self.settings.Width ? self.settings.minWidth : self.settings.Width);
                var winHeight = (self.settings.minHeight > self.settings.Height ? self.settings.minHeight : self.settings.Height);




                var sH = self.getStatusbarHeight();
                var hH = self.getHeaderHeight();
                var doBodyHeight = winHeight - hH - sH, doBodyWidth = winWidth;

                self.BodyContent.stop(true, true).css({WebkitTransform: 'translateX(0px)', visibility: '', 'overflow': 'hidden', opacity: '1', height: 1, width: 1})
                        .show()
                        .find('.win-content').css({
                    'overflow': 'hidden'
                });

                var animateLeft = $(window).width() / 2 - (winWidth / 2), animateTop = $(window).height() / 2 - (winHeight / 2);

                if (self.settings.PositionLeft !== false && self.settings.PositionLeft >= 0) {
                    animateLeft = self.settings.PositionLeft;
                }
                if (self.settings.PositionTop !== false && self.settings.PositionTop > 0) {
                    animateTop = self.settings.PositionTop;
                }

                var gridHeaderHeight = 0, gridFooterHeight = 0, gridData = self.$el.data('windowGrid');
                if (gridData) {
                    gridHeaderHeight = gridData.headerTable.outerHeight();
                    gridFooterHeight = gridData.gridFooter.outerHeight(true);
                }

                // set start position
                self.$el.stop()/*.addClass('no-shadow')*/
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


                self.BodyContent.animate({
                    width: doBodyWidth,
                    height: doBodyHeight,
                }, {
                    duration: windowOpenAnimationTime
                });

                self.$el.animate(
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

                        self.win.removeClass('animated no-shadow loading').css({
                            'overflow': ''
                        });

                        self.BodyContent.css({
                            'overflow': ''
                        }).find('.win-content').css({
                            'overflow': ''
                        });

                        self.updateContentHeight();
                        $.pagemask.hide();
                        self.focus();
                        updateWindowSizeCache(true);

                        if (self.settings.enableContentScrollbar) {
                            self.enableWindowScrollbar();
                        }

                        self.executeEvent('onAfterShow', null, {height: doBodyHeight, width: self.win.width()});

                        self.settings.status = 'default';
                        self.windowInAnimation = false;

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

        self.animateClose = function (ev, callback) {

            if (self.windowInAnimation) {
                winshowTimout = setTimeout(function () {
                    self.animateClose(ev, callback);
                }, 1);
            }
            else {
                clearTimeout(winshowTimout);

                self.windowInAnimation = true;

                if (self.stopClose && !self.$el.length) {
                    self.stopClose = false;
                    self.windowInAnimation = false;
                    return;
                }


                WindowAnimation.AnimateClose(self, self.settings.closeToPos, callback);
            }
        };



        self.enableLoading = function () {
            self.win.addClass('loading');
        };

        self.disableLoading = function () {
            self.win.removeClass('loading');
        };

        self.mask = function (title) {
            if (title) {
                self.BodyContent.mask(title);
            }
            else {
                self.BodyContent.mask('Bitte warten...');
            }
            return self;
        };

        self.unmask = function () {
            self.BodyContent.unmask();
            return self;
        };


        self.restore = function () {
            animateWindow('restore');
        };

        self.close = function (e) {
            animateWindow('close', e);
        };

        self.getWindowPos = function () {
            var offset = self.win.offset();
            return {
                left: offset.left,
                top: offset.top,
                right: offset.left + self.win.outerWidth(true),
                bottom: offset.top + self.win.outerHeight(true)
            };
        };

        self.getWindowPosRight = function () {
            return self.win.offset().left + self.win.outerWidth(true);
        };

        self.getToolbarHeight = function () {
            return self.Toolbar.is(':visible') ? parseInt(self.Toolbar.outerHeight(true), 0) : 0;

        };

        self.getStatusbarHeight = function () {
            return self.Statusbar.length && self.Statusbar.is(':visible') ? parseInt(self.Statusbar.parents('.window-footer:first').outerHeight(true), 0) : 0;

        };

        self.getHeaderHeight = function () {
            var head = 0;

            if (self.Body.prev().hasClass('tabcontainer')) {
                head += parseInt(self.Body.prev().outerHeight(true), 0);
            }

            return (parseInt(self.TitleBar.outerHeight(true), 0) + self.getToolbarHeight() + head);
        };

        self.t;
        self.getContentHeight = function () {

            if (self.$el.hasClass('animated') || self.win === null || typeof self.win == 'undefined') {
                self.t = setTimeout(function () {
                    return self.getContentHeight();
                }, 1);
            }
            else {

                clearTimeout(self.t);
                return parseInt(self.win.height(), 0) - (self.getHeaderHeight() + self.getStatusbarHeight());
            }
        };

        self.t2;
        self.updateContentHeight = function () {

            if (self.$el.hasClass('animated') || self.win === null || typeof self.win == 'undefined') {
                self.t2 = setTimeout(function () {
                    return self.updateContentHeight();
                }, 1);
            }
            else {
                clearTimeout(self.t2);

                var b = self.BodyContent.get(0);
                b.style.height = self.getContentHeight();
                b.style.width = self.$el.innerWidth();

                updateWindowSizeCache(false);

                if (!self.win.hasClass('no-scroll') && self.settings.enableContentScrollbar === true) {
                    Tools.refreshScrollBar(self.BodyContent);
                }
            }

        };



        // Settings
        self.set = function (key, value) {
            self.settings[key] = value;
            return self;
        };

        self.get = function (key, defaultValue) {
            return (typeof self.settings[key] != 'undefined' ? self.settings[key] : (typeof defaultValue != 'undefined' ? defaultValue : null));
        };

        // Title functions
        self.getTitle = function () {
            return self.TitleBar.find('span.win-title').html();
        };

        self.setTitle = function (title) {
            self.settings.Title = title;
            self.TitleBar.find('span.win-title').html(title);
        };

        self.resetTitle = function () {
            self.settings.Title = self.settings.originalTitle;
            self.TitleBar.find('span.win-title').html(self.settings.Title);
        };


        self.disableWindowDraggable = function () {
            self.win.filter('ui-draggable').draggable('disable');
            return self;
        };

        self.enableWindowDraggable = function () {
            if (self.settings.Draggable) {
                self.win.filter('ui-draggable').draggable('enable');
            }
            return self;
        };

        self.disableWindowResizeable = function () {
            self.win.filter('ui-resizable').resizable('disable');
            return self;
        };

        self.enableWindowResizeable = function () {
            if (self.settings.Resizable) {
                self.win.filter('ui-resizable').resizable('enable');
            }
            return self;
        };

        self.disableWindowScrollbar = function () {
            return self;
        };

        self.enableWindowScrollbar = function () {
            if (self.BodyContent) {
                if (!self.win.hasClass('no-scroll') && self.settings.enableContentScrollbar === true /* && self.get('hasTinyMCE') != true*/) {
                    Tools.scrollBar(self.BodyContent, null, self.settings.onScroll);
                }
            }
            return self;
        };



        self.getToolbar = function () {
            return (self.settings.isSingleWindow ? self.$el.find('.window-toolbar .sub-toolbar:visible') : self.$el.find('.window-toolbar:visible'));
        };


        // execute all existing events
        self.executeEvent = function (key, event, callback, arg2) {
            if (key == 'onFocus')
            {
                if (defined(self.settings.onFocus)) {
                    if (typeof self.settings.onFocus == 'object')
                    {
                        for (var i = 0; i < self.settings.onFocus.length; ++i) {
                            if (typeof self.settings.onFocus[i] == 'function') {
                                self.settings.onFocus[i](event, self, callback);
                            }
                        }
                    }
                    else if (typeof self.settings.onFocus == 'function') {
                        self.settings.onFocus(event, self, callback);
                    }
                    else {
                        if (isFunction(callback)) {
                            callback();
                        }
                    }
                }
                else {
                    if (isFunction(callback)) {
                        callback();
                    }
                }
            }
            else if (key == 'onUnFocus') {
                if (defined(self.settings.onUnFocus)) {
                    if (typeof self.settings.onUnFocus == 'object')
                    {
                        for (var i = 0; i < self.settings.onUnFocus.length; ++i) {
                            if (typeof self.settings.onUnFocus[i] == 'function') {
                                self.settings.onUnFocus[i](event, self, callback);
                            }
                        }
                    }
                    else if (typeof self.settings.onUnFocus == 'function') {
                        self.settings.onUnFocus(event, self);
                    }
                    else {
                        if (isFunction(callback)) {
                            callback();
                        }
                    }
                }
                else {
                    if (isFunction(callback)) {
                        callback();
                    }
                }
            }
            else if (key == 'onAfterCreated') {
                if (defined(self.settings.onAfterCreated)) {
                    if (typeof self.settings.onAfterCreated == 'object')
                    {
                        for (var i = 0; i < self.settings.onAfterCreated.length; ++i) {
                            if (typeof self.settings.onAfterCreated[i] == 'function') {
                                self.settings.onAfterCreated[i](self, callback, arg2);
                            }
                        }
                    }
                    else if (typeof self.settings.onAfterCreated == 'function') {
                        self.settings.onAfterCreated(self, callback, arg2);
                    }
                    else {
                        if (isFunction(callback)) {
                            callback();
                        }
                    }
                }
                else {
                    if (isFunction(callback)) {
                        callback();
                    }
                }
            }
            else if (key == 'onBeforeShow') {
                if (defined(self.settings.onBeforeShow)) {
                    if (typeof self.settings.onBeforeShow == 'object')
                    {
                        for (var i = 0; i < self.settings.onBeforeShow.length; ++i) {
                            if (typeof self.settings.onBeforeShow[i] == 'function') {
                                self.settings.onBeforeShow[i](event, self, callback, arg2);
                            }
                        }
                    }
                    else if (typeof self.settings.onBeforeShow == 'function') {
                        self.settings.onBeforeShow(event, self, callback, arg2);
                    }
                    else {
                        if (isFunction(callback)) {
                            callback();
                        }
                    }
                }
                else {
                    if (isFunction(callback)) {
                        callback();
                    }
                }
            }
            else if (key == 'onAfterShow') {
                if (defined(self.settings.onAfterShow)) {
                    if (typeof self.settings.onAfterShow == 'object')
                    {
                        for (var i = 0; i < self.settings.onAfterShow.length; ++i) {
                            if (typeof self.settings.onAfterShow[i] == 'function') {
                                self.settings.onAfterShow[i](event, self, callback);
                            }
                        }
                    }
                    else if (typeof self.settings.onAfterShow == 'function') {
                        self.settings.onAfterShow(event, self, callback);
                    }
                    else {
                        if (isFunction(callback)) {
                            callback();
                        }
                    }
                }
                else {
                    if (isFunction(callback)) {
                        callback();
                    }
                }
            }
            else if (key == 'onBeforeClose') {
                if (defined(self.settings.onBeforeClose)) {
                    if (typeof self.settings.onBeforeClose == 'object')
                    {
                        for (var i = 0; i < self.settings.onBeforeClose.length; ++i) {
                            if (typeof self.settings.onBeforeClose[i] == 'function') {
                                self.settings.onBeforeClose[i](event, self, callback);
                            }
                        }
                    }
                    else if (typeof self.settings.onBeforeClose == 'function') {
                        self.settings.onBeforeClose(event, self, callback);
                    }
                    else {
                        if (isFunction(callback)) {
                            callback();
                        }
                    }
                }
                else {
                    if (isFunction(callback)) {
                        callback();
                    }
                }
            }
            else if (key == 'onAfterClose') {
                if (defined(self.settings.onAfterClose)) {
                    if (typeof self.settings.onAfterClose == 'object')
                    {
                        for (var i = 0; i < self.settings.onAfterClose.length; ++i) {
                            if (typeof self.settings.onAfterClose[i] == 'function') {
                                self.settings.onAfterClose[i](event, self, callback);
                            }
                        }
                    }
                    else if (typeof self.settings.onAfterClose == 'function') {
                        self.settings.onAfterClose(event, self, callback);
                    }
                    else {
                        if (isFunction(callback)) {
                            callback();
                        }
                    }
                }
                else {
                    if (isFunction(callback)) {
                        callback();
                    }
                }
            }
            else if (key == 'onBeforeReload') {
                if (defined(self.settings.onBeforeReload)) {
                    if (typeof self.settings.onBeforeReload == 'object')
                    {
                        for (var i = 0; i < self.settings.onBeforeReload.length; ++i) {
                            if (typeof self.settings.onBeforeReload[i] == 'function') {
                                self.settings.onBeforeReload[i](event, self, callback);
                            }
                        }
                    }
                    else if (typeof self.settings.onBeforeReload == 'function') {
                        self.settings.onBeforeReload(event, self, callback);
                    }
                    else {
                        if (isFunction(callback)) {
                            callback();
                        }
                    }
                }
                else {
                    if (isFunction(callback)) {
                        callback();
                    }
                }
            }
            else if (key == 'onResizeStart') {
                if (defined(self.settings.onResizeStart)) {
                    if (typeof self.settings.onResizeStart == 'object')
                    {
                        for (var i = 0; i < self.settings.onResizeStart.length; ++i) {
                            if (typeof self.settings.onResizeStart[i] == 'function') {
                                self.settings.onResizeStart[i](event, self, callback, arg2);
                            }
                        }
                    }
                    else if (typeof self.settings.onResizeStart == 'function') {
                        self.settings.onResizeStart(event, self, callback, arg2);
                    }
                    else {
                        if (isFunction(callback)) {
                            callback();
                        }
                    }
                }
                else {
                    if (isFunction(callback)) {
                        callback();
                    }
                }
            }
            else if (key == 'onResize') {
                if (defined(self.settings.onResize)) {
                    if (typeof self.settings.onResize == 'object')
                    {
                        for (var i = 0; i < self.settings.onResize.length; ++i) {
                            if (typeof self.settings.onResize[i] == 'function') {
                                self.settings.onResize[i](event, self, callback, arg2); // callback is the size
                            }
                        }
                    }
                    else if (typeof self.settings.onResize == 'function') {
                        self.settings.onResize(event, self, callback, arg2); // callback is the size
                    }
                    else {
                        if (isFunction(callback)) {
                            callback();
                        }
                    }
                }
                else {
                    if (isFunction(callback)) {
                        callback();
                    }
                }
            }
            else if (key == 'onResizeStop') {
                if (defined(self.settings.onResizeStop)) {
                    if (typeof self.settings.onResizeStop == 'object')
                    {
                        for (var i = 0; i < self.settings.onResizeStop.length; ++i) {
                            if (typeof self.settings.onResizeStop[i] == 'function') {
                                self.settings.onResizeStop[i](event, self, callback, arg2); // callback is the size
                            }
                        }
                    }
                    else if (typeof self.settings.onResizeStop == 'function') {
                        self.settings.onResizeStop(event, self, callback, arg2); // callback is the size
                    }
                    else {
                        if (isFunction(callback)) {
                            callback();
                        }
                    }
                }
                else {
                    if (isFunction(callback)) {
                        callback();
                    }
                }
            }
            else if (key == 'onWindowDragStart') {
                if (defined(self.settings.onWindowDragStart)) {
                    if (typeof self.settings.onWindowDragStart == 'object')
                    {
                        for (var i = 0; i < self.settings.onWindowDragStart.length; ++i) {
                            if (typeof self.settings.onWindowDragStart[i] == 'function') {
                                self.settings.onWindowDragStart[i](event, self, callback);
                            }
                        }
                    }
                    else if (typeof self.settings.onWindowDragStart == 'function') {
                        self.settings.onWindowDragStart(event, self, callback);
                    }
                    else {
                        if (isFunction(callback)) {
                            callback();
                        }
                    }
                }
                else {
                    if (isFunction(callback)) {
                        callback();
                    }

                }
            }
            else if (key == 'onWindowDragStop') {
                if (defined(self.settings.onWindowDragStop)) {
                    if (typeof self.settings.onWindowDragStop == 'object')
                    {
                        for (var i = 0; i < self.settings.onWindowDragStop.length; ++i) {
                            if (typeof self.settings.onWindowDragStop[i] == 'function') {
                                self.settings.onWindowDragStop[i](event, self, callback);
                            }
                        }
                    }
                    else if (typeof self.settings.onWindowDragStop == 'function') {
                        self.settings.onWindowDragStop(event, self, callback);
                    }
                    else {
                        if (isFunction(callback)) {
                            callback();
                        }
                    }
                }
                else {
                    if (isFunction(callback)) {
                        callback();
                    }
                }
            }
        };



        self.addEvent = function (key, callback) {

            var tmp = [];

            if (key == 'onFocus') {
                if (defined(self.settings.onFocus) && isFunction(self.settings.onFocus)) {
                    tmp.push(self.settings.onFocus);
                    tmp.push(callback);
                    self.settings.onFocus = tmp;
                }
                else if (defined(self.settings.onFocus) && isObject(self.settings.onFocus)) {
                    self.settings.onFocus.push(callback);
                }
                else {
                    self.settings.onFocus = [];
                    self.settings.onFocus.push(callback);
                }
            }
            else if (key == 'onUnFocus') {
                if (defined(self.settings.onUnFocus) && isFunction(self.settings.onUnFocus)) {
                    tmp.push(self.settings.onUnFocus);
                    tmp.push(callback);
                    self.settings.onUnFocus = tmp;
                }
                else if (defined(self.settings.onUnFocus) && isObject(self.settings.onUnFocus)) {
                    self.settings.onUnFocus.push(callback);
                }
                else {
                    self.settings.onUnFocus = [];
                    self.settings.onUnFocus.push(callback);
                }
            }
            else if (key == 'onBeforeShow') {
                if (defined(self.settings.onBeforeShow) && isFunction(self.settings.onBeforeShow)) {
                    tmp.push(self.settings.onBeforeShow);
                    tmp.push(callback);
                    self.settings.onBeforeShow = tmp;
                }
                else if (defined(self.settings.onBeforeShow) && isObject(self.settings.onBeforeShow)) {
                    self.settings.onBeforeShow.push(callback);
                }
                else {
                    self.settings.onBeforeShow = [];
                    self.settings.onBeforeShow.push(callback);
                }
            }
            else if (key == 'onAfterShow') {
                if (defined(self.settings.onAfterShow) && isFunction(self.settings.onAfterShow)) {
                    tmp.push(self.settings.onAfterShow);
                    tmp.push(callback);
                    self.settings.onAfterShow = tmp;
                }
                else if (defined(self.settings.onAfterShow) && isObject(self.settings.onAfterShow)) {
                    self.settings.onAfterShow.push(callback);
                }
                else {
                    self.settings.onAfterShow = [];
                    self.settings.onAfterShow.push(callback);
                }
            }
            else if (key == 'onBeforeClose') {
                if (defined(self.settings.onBeforeClose) && isFunction(self.settings.onBeforeClose)) {
                    tmp.push(self.settings.onBeforeClose);
                    tmp.push(callback);
                    self.settings.onBeforeClose = tmp;
                }
                else if (defined(self.settings.onBeforeClose) && isObject(self.settings.onBeforeClose)) {
                    self.settings.onBeforeClose.push(callback);
                }
                else {
                    self.settings.onBeforeClose = [];
                    self.settings.onBeforeClose.push(callback);
                }
            }
            else if (key == 'onAfterClose') {
                if (defined(self.settings.onAfterClose) && isFunction(self.settings.onAfterClose)) {
                    tmp.push(self.settings.onAfterClose);
                    tmp.push(callback);
                    self.settings.onAfterClose = tmp;
                }
                else if (defined(self.settings.onAfterClose) && isObject(self.settings.onAfterClose)) {
                    self.settings.onAfterClose.push(callback);
                }
                else {
                    self.settings.onAfterClose = [];
                    self.settings.onAfterClose.push(callback);
                }
            }
            else if (key == 'onBeforeReload') {
                if (defined(self.settings.onBeforeReload) && isFunction(self.settings.onBeforeReload)) {
                    tmp.push(self.settings.onBeforeReload);
                    tmp.push(callback);
                    self.settings.onBeforeReload = tmp;
                }
                else if (defined(self.settings.onBeforeReload) && isObject(self.settings.onBeforeReload)) {
                    self.settings.onBeforeReload.push(callback);
                }
                else {
                    self.settings.onBeforeReload = [];
                    self.settings.onBeforeReload.push(callback);
                }
            }
            else if (key == 'onResizeStart') {
                if (defined(self.settings.onResizeStart) && isFunction(self.settings.onResizeStart)) {
                    tmp.push(self.settings.onResizeStart);
                    tmp.push(callback);
                    self.settings.onResizeStart = tmp;
                }
                else if (defined(self.settings.onResizeStart) && isObject(self.settings.onResizeStart)) {
                    self.settings.onResizeStart.push(callback);
                }
                else {
                    self.settings.onResizeStart = [];
                    self.settings.onResizeStart.push(callback);
                }
            }
            else if (key == 'onResize') {
                if (defined(self.settings.onResize) && isFunction(self.settings.onResize)) {
                    tmp.push(self.settings.onResize);
                    tmp.push(callback);
                    self.settings.onResize = tmp;
                }
                else if (defined(self.settings.onResize) && isObject(self.settings.onResize)) {
                    self.settings.onResize.push(callback);
                }
                else {
                    self.settings.onResize = [];
                    self.settings.onResize.push(callback);
                }
            }
            else if (key == 'onResizeStop') {
                if (defined(self.settings.onResizeStop) && isFunction(self.settings.onResizeStop)) {
                    tmp.push(self.settings.onResizeStop);
                    tmp.push(callback);
                    self.settings.onResizeStop = tmp;
                }
                else if (defined(self.settings.onResizeStop) && isObject(self.settings.onResizeStop)) {
                    self.settings.onResizeStop.push(callback);
                }
                else {
                    self.settings.onResizeStop = [];
                    self.settings.onResizeStop.push(callback);
                }
            }
            else if (key == 'onWindowDragStart') {
                if (defined(self.settings.onWindowDragStart) && isFunction(self.settings.onWindowDragStart)) {
                    tmp.push(self.settings.onWindowDragStart);
                    tmp.push(callback);
                    self.settings.onWindowDragStart = tmp;
                }
                else if (defined(self.settings.onWindowDragStart) && isObject(self.settings.onWindowDragStart)) {
                    self.settings.onWindowDragStart.push(callback);
                }
                else {
                    self.settings.onWindowDragStart = [];
                    self.settings.onWindowDragStart.push(callback);
                }
            }
            else if (key == 'onWindowDragStop') {
                if (defined(self.settings.onWindowDragStop) && isFunction(self.settings.onWindowDragStop)) {
                    tmp.push(self.settings.onWindowDragStop);
                    tmp.push(callback);
                    self.settings.onWindowDragStop = tmp;
                }
                else if (defined(self.settings.onWindowDragStop) && isObject(self.settings.onWindowDragStop)) {
                    self.settings.onWindowDragStop.push(callback);
                }
                else {
                    self.settings.onWindowDragStop = [];
                    self.settings.onWindowDragStop.push(callback);
                }
            }
        };

        self.removeEvent = function (key, callback) {

            if (key == 'onFocus') {

            }
            else if (key == 'onUnFocus') {

            }
            else if (key == 'onBeforeShow') {

            }
            else if (key == 'onAfterShow') {

            }
            else if (key == 'onBeforeClose') {

            }
            else if (key == 'onAfterClose') {

            }
            else if (key == 'onBeforeReload') {

            }
            else if (key == 'onResizeStart') {

            }
            else if (key == 'onResize') {

            }
            else if (key == 'onResizeStop') {

            }
            else if (key == 'onWindowDragStart') {

            }
            else if (key == 'onWindowDragStop') {

            }
        };

        // Run initializer
        self.init();

        return self;
    };




    $.WindowManager.defaultOptions = {
        status: 'restore', // the init window status
        isActive: false,
        urlClean: false,
        WindowID: false,
        enableContentScrollbar: true,
        rollback: false,
        ExtraClasses: '',
        nopadding: false,
        Url: false,
        originalTitle: false,
        Title: 'DreamCMS Window...',
        Toolbar: false,
        Content: false,
        Statusbar: false,
        Controller: false, // this will set the attribute app to the window
        Action: false,
        isGridWindow: false,
        hasGridAction: false,
        isRootApplication: false,
        isSingleWindow: false,
        singleWindow: false, // the root window instance will used in $.fn.windowManager
        versioning: false,
        isSubContentVisible: false,
        addFileSelector: false,
        scrollbarOpts: {
            showArrows: false,
            scrollbarWidth: 15,
            arrowSize: 16,
            onScroll: false
        },
        subCache: {},
        TaskbarHeight: 0,
        // Window size and positions
        minWidth: 300,
        minHeight: 200,
        Width: 800,
        Height: 400,
        PositionLeft: false,
        PositionTop: false,
        // Window events
        Draggable: true,
        Resizable: true,
        Maximize: true,
        Minimize: true,
        Closable: true,
        //
        Skin: 'Apple'
    };





    $.fn.windowManager = function (options) {
        return this.each(function () {
            if (!$(this).data("WindowManager")) {
                var wm = new $.WindowManager(this, options);
                return $(this).data("WindowManager");
            }
            else {

                // create single window?
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



    $.fn.getWindow = function () {
        return this.each(function () {
            if ($(this).data("WindowManager")) {
                return $(this).data("WindowManager");
            }
        });
    };


    $.fn.addWindowEvent = function (key, callback) {
        return this.each(function () {
            if ($(this).data("WindowManager")) {
                $(this).data("WindowManager").addEvent(key, callback);
            }
        });
    };

    $.fn.removeWindowEvent = function (key, callback) {
        return this.each(function () {
            if ($(this).data("WindowManager")) {
                $(this).data("WindowManager").removeEvent(key, callback);
            }
        });
    };

    $.fn.resetWindowTitle = function () {
        return this.each(function () {
            if ($(this).data("WindowManager")) {
                $(this).data("WindowManager").resetTitle();
            }
        });
    };

    $.fn.setWindowTitle = function (title) {
        return this.each(function () {
            if ($(this).data("WindowManager")) {
                $(this).data("WindowManager").setTitle(title);
            }
        });
    };



    $.fn.getToolbar = function () {
        if ($(this).data("WindowManager")) {
            return $(this).data("WindowManager").getToolbar();
        }

        return null;
    };

    $.fn.setWindowOption = function (key, value) {
        if ($(this).data("WindowManager")) {
            $(this).data("WindowManager").set(key, value);
        }
    };

    $.fn.getWindowOption = function (key, defaultvalue) {
        if ($(this).data("WindowManager")) {
            return $(this).data("WindowManager").get(key, defaultvalue);
        }

        return defaultvalue;
    };


})(jQuery);