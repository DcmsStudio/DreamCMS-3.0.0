var WindowAnimation = {
    AnimateMax: function (winDataObj)
    {
        var gridData = winDataObj.$el.data('windowGrid');
        var basewinoverflow = winDataObj.win.css('overflow') || null;
        var dockHeight = 0, windowData = winDataObj;

        if (parseInt($('#dock').css('bottom')) >= 0)
        {
            dockHeight = parseInt($('#dock').outerHeight(true), 0);
        }

        var self = winDataObj,
                doBodyHeight = $(window).height() - dockHeight - parseInt(winDataObj.settings.TaskbarHeight, 0) - winDataObj.getHeaderHeight() - winDataObj.getStatusbarHeight(), doBodyWidth = $(window).width();

        // for css restoring
        winDataObj.BodyContent.attr('restoreheight', winDataObj.BodyContent.height());
        winDataObj.BodyContent.attr('restorewidth', winDataObj.win.width());
        winDataObj.win.attr('restorewidth', winDataObj.win.width());
        winDataObj.win.attr('restoreheight', winDataObj.win.innerHeight());
        winDataObj.win.addClass('animated').addClass('no-shadow').css({opacity: '1', WebkitTransform: 'translateX(0px)'});
        winDataObj.BodyContent.css({opacity: '1', WebkitTransform: 'translateX(0px)'});

        var aceEdit = winDataObj.BodyContent.find('.sourceEdit:first');
        var winOpts = {
            left: 0,
            top: parseInt(winDataObj.settings.TaskbarHeight, 0) ? winDataObj.settings.TaskbarHeight : 0,
            width: $(window).width(),
            height: $(window).height() - (parseInt(winDataObj.settings.TaskbarHeight, 0) ? winDataObj.settings.TaskbarHeight : 0) - dockHeight
        };
        var bodyOpts = {
            height: doBodyHeight,
            width: doBodyWidth
        };
        doBodyHeight = winOpts.height - winDataObj.getHeaderHeight() - winDataObj.getStatusbarHeight();

        if (gridData === null || !gridData)
        {
            

            winDataObj.Body.css('height', '');
            winDataObj.BodyContent.animate(bodyOpts, {
                queue: false,
                duration: winDataObj.windowMaxAnimationTime,
                step: function () {
                    
                    if (winDataObj.settings.enableContentScrollbar)
                    {
                        winDataObj.enableWindowScrollbar();
                    }
                    
                    if (aceEdit.length && typeof resizeAce === 'function') {
                        resizeAce(winDataObj);
                    }
                }
            });
            
            winDataObj.win.animate(winOpts, {
                queue: true,
                duration: winDataObj.windowMaxAnimationTime,
                complete: function (instance) {
                    winDataObj.settings.status = 'max';
                    winDataObj.win.removeClass('animated').removeClass('no-shadow')
                            .css(Modernizr.prefixed('transition'), '');
                    winDataObj.BodyContent.css(Modernizr.prefixed('transition'), '');
                    //self.reinitElements();
                    winDataObj.disableWindowDraggable();
                    winDataObj.disableWindowResizeable();
                    if (winDataObj.settings.enableContentScrollbar)
                    {
                        winDataObj.enableWindowScrollbar();
                    }



                    winDataObj.executeEvent('onResizeStop', null, null, {height: winDataObj.BodyContent.height(), width: winDataObj.BodyContent.width()});


                }
            });

        }
        else
        {
            var gridHeaderHeight = 0, gridFooterHeight = 0, viewPort = false, dataWrapper = false, dataTable = false, headerTable = false;
            if (gridData)
            {
                gridHeaderHeight = gridData.headerTable.outerHeight();
                gridFooterHeight = gridData.gridFooter.outerHeight(true);
                viewPort = gridData.viewport.get(0);
                dataTable = gridData.dataTable.get(0);
                headerTable = gridData.headerTable.get(0);
                dataWrapper = gridData.dataTableWrapper.get(0);
                dataWrapper.style.width = '100%';
                headerTable.style.width = '100%';
                dataTable.style.width = '100%';
            }

            winDataObj.win.animate({
                left: 0,
                top: parseInt(winDataObj.settings.TaskbarHeight, 0) ? winDataObj.settings.TaskbarHeight : 0,
                width: $(window).width(),
                height: $(window).height() - (parseInt(winDataObj.settings.TaskbarHeight, 0) ? winDataObj.settings.TaskbarHeight : 0) - dockHeight
            }, {
                duration: winDataObj.windowMaxAnimationTime,
                queue: true
            });
            winDataObj.Body.css('height', '');
            winDataObj.BodyContent.animate({
                width: doBodyWidth,
                height: doBodyHeight
            }, {
                queue: true,
                duration: winDataObj.windowMaxAnimationTime,
                /*
                 step: function(now, fx)
                 {
                 if (gridData)
                 {
                 if (fx.prop == 'height')
                 {
                 viewPort.style.height = now - gridFooterHeight;
                 dataWrapper.style.height = now - gridHeaderHeight;
                 }
                 }
                 },
                 */
                complete: function (instance) {

                    winDataObj.settings.status = 'max';
                    self.win.removeClass('animated').removeClass('no-shadow');
                    self.win.css('overflow', basewinoverflow);
                    //self.reinitElements();
                    self.disableWindowDraggable();
                    self.disableWindowResizeable();
                    if (gridData)
                    {
                        gridData.updateDataTableSize(winDataObj.$el, false);
                        //     gridData.updateDataTableSize(winDataObj.$el, false, {height: doBodyHeight, width: doBodyWidth});
                    }

                    if (self.settings.enableContentScrollbar)
                    {
                        self.enableWindowScrollbar();
                    }


                    self.executeEvent('onResizeStop', null, null, {height: self.BodyContent.height(), width: self.BodyContent.width()});

                }
            });
            if (gridData)
            {
                $(gridData.dataTableWrapper)
                        .animate({height: doBodyHeight - gridHeaderHeight}, {duration: winDataObj.windowMaxAnimationTime});
                $(gridData.viewport)
                        .animate({height: doBodyHeight - gridFooterHeight}, {duration: winDataObj.windowMaxAnimationTime});
            }
        }
    },
    AnimateMin: function (winDataObj, aniamteToObj, toPos, doClose)
    {
        var gridData = winDataObj.$el.data('windowGrid');
        var self = winDataObj, baseoverflow = winDataObj.win.css('overflow') || null;
        var minTo = {
            opacity: '0'
        };
        if (!aniamteToObj)
        {
            minTo.height = 0;
            minTo.width = 0;
            minTo.top = $(window).height();
            minTo.left = $(window).width() / 2;
            if (toPos && doClose)
            {
                if (toPos.left)
                {
                    minTo.left = toPos.left;
                }
                if (toPos.top)
                {
                    minTo.top = toPos.top;
                }
            }
        }
        else
        {
            var width = aniamteToObj.outerWidth(true), height = aniamteToObj.outerHeight(true);
            var winWidth = winDataObj.win.outerWidth(true), winHeight = winDataObj.win.outerHeight(true);
            minTo.height = height ? height : 0;
            minTo.width = width ? width : 0;
            minTo.top = aniamteToObj.offset().top + (height ? height / 2 : 0);
            minTo.left = aniamteToObj.offset().left + (width ? width / 2 : 0);
        }



        winDataObj.BodyContent.css({WebkitTransform: 'translateX(0px)'});
        winDataObj.win.css({WebkitTransform: 'translateX(0px)', overflow: 'hidden'}).addClass('animated').addClass('no-shadow');


        winDataObj.win.css({
            'overflow': 'hidden'
        });

        winDataObj.BodyContent.css({
            'overflow': 'hidden'
        }).find('.win-content').css({
            'overflow': 'hidden'
        });



        winDataObj.BodyContent.animate({
            opacity: '0.1',
            width: 0,
            height: 0
        }, {
            duration: (doClose ? winDataObj.get('windowCloseAnimationTime') : winDataObj.get('windowMinAnimationTime'))
        });
        
        winDataObj.win.animate(minTo, {
            duration: (doClose ? winDataObj.get('windowCloseAnimationTime') : winDataObj.get('windowMinAnimationTime')),
            complete: function (instance) {

                winDataObj.settings.status = 'min';

                if (!doClose)
                {
                    winDataObj.win.removeClass('animated').removeClass('no-shadow');
                }
                winDataObj.win.css({
                    'overflow': ''
                });

                winDataObj.BodyContent.css({
                    'overflow': ''
                }).find('.win-content').css({
                    'overflow': ''
                });

                winDataObj.unfocus();
                $('body').data('FocusWindow', false);
                winDataObj.win.hide();
                if (doClose)
                {
                    doClose();
                }
            }
        });
        return;
        Anim.animate(winDataObj.BodyContent, {
            width: 0,
            height: 0
        }, {
            queue: true,
            duration: doClose ? winDataObj.windowCloseAnimationTime : winDataObj.windowMinAnimationTime
        });
        Anim.animate(winDataObj.win, minTo, {
            duration: doClose ? winDataObj.windowCloseAnimationTime : winDataObj.windowMinAnimationTime,
            complete: function (instance) {
                winDataObj.settings.status = 'min';
                if (!doClose)
                {
                    winDataObj.win.removeClass('animated').removeClass('no-shadow');
                    winDataObj.reinitElements();
                }

                winDataObj.unfocus();
                $('body').data('FocusWindow', false);
                $(instance).hide();
                if (doClose)
                {
                    doClose();
                }
            }
        });
    },
    AnimateRestore: function (winDataObj, callback)
    {

        var self = winDataObj, cssOpts, animopts;
        var gridData = winDataObj.$el.data('windowGrid');
        var isRestore = (winDataObj.restoreSettings.status == 'max' ? true : false);


        var cssWin = {
            width: winDataObj.restoreSettings.winWidth,
            height: winDataObj.restoreSettings.winHeight,
            top: winDataObj.restoreSettings.top,
            left: winDataObj.restoreSettings.left,
            opacity: '1'
        }, basewinoverflow = winDataObj.win.css('overflow') || null, baseBodyoverflow = winDataObj.BodyContent.css('overflow') || null;


        if (gridData && winDataObj.settings.status != 'min' && winDataObj.settings.status != 'restore')
        {
            var gridHeaderHeight = gridData.headerTable.outerHeight();
            var gridFooterHeight = gridData.gridFooter.outerHeight(true);
            var viewPort = gridData.viewport.get(0);
            var dataTable = gridData.dataTable.get(0);
            var headerTable = gridData.headerTable.get(0);
            var dataWrapper = gridData.dataTableWrapper.get(0);
            var cols = gridData.dataTable.find('tr:eq(0) td');
            gridData.headerTable.find('thead th:eq(0):visible').each(function (i) {
                cols.eq(i).css('width', $(this).attr('width'));
            });
            dataWrapper.style.width = '100%';
            headerTable.style.width = '100%';
            dataTable.style.width = '100%';
        }

        if (winDataObj.settings.status == 'min') {
            winDataObj.win.stop(true, true).css(Modernizr.prefixed('Transition'), '')
                    .css(Modernizr.prefixed('Transform'), 'scale(1)')
                    .css({opacity: '0', WebkitTransform: 'translateX(0px)', width: 16, height: 16});
            winDataObj.BodyContent.stop(true, true).css(Modernizr.prefixed('Transition'), '')
                    .css(Modernizr.prefixed('Transform'), '')
                    .css({opacity: '0', WebkitTransform: 'translateX(0px)', width: 16, height: 16});
            cssOpts = {
                opacity: 1,
                width: winDataObj.restoreSettings.bodyWidth,
                height: winDataObj.restoreSettings.bodyHeight
            };
            animopts = {
                queue: false,
                duration: winDataObj.windowRestoreAnimationTime
            };
        }
        else
        {
            winDataObj.win.css({opacity: '1', WebkitTransform: 'translateX(0px) translateY(0px)', overflow: 'hidden'});
            winDataObj.BodyContent.css({opacity: '1', WebkitTransform: 'translateX(0px) translateY(0px)', overflow: 'hidden'});
            cssOpts = {
                width: winDataObj.restoreSettings.bodyWidth,
                height: winDataObj.restoreSettings.bodyHeight
            };
            if (gridData) {
                var vpstyle = viewPort.style, dwstyle = dataWrapper.style, dtstyle = dataTable.style, htstyle = headerTable.style;
            }

            animopts = {
                queue: false,
                duration: winDataObj.windowRestoreAnimationTime,
                /*
                 step: function(now, fx)
                 {
                 if (gridData)
                 {
                 if (fx.prop == 'height')
                 {
                 vpstyle.height = (now - gridFooterHeight);
                 dwstyle.height = (now - gridFooterHeight - gridHeaderHeight);
                 }
                 }
                 }
                 */
            };
        }





        winDataObj.win.show();
        if (winDataObj.settings.status == 'min') {

            winDataObj.focus(false, winDataObj.id);
            winDataObj.win.delay(10).animate({
                
                opacity: 1,
                width: winDataObj.restoreSettings.winWidth,
                height: winDataObj.restoreSettings.winHeight,
                top: winDataObj.restoreSettings.top,
                left: winDataObj.restoreSettings.left
            }, {
                queue: false,
                duration: winDataObj.windowRestoreAnimationTime,
                complete: function () {

                    winDataObj.win.removeClass('animated').removeClass('no-shadow');
                    winDataObj.win.css(Modernizr.prefixed('Transition'), '')
                            .css(Modernizr.prefixed('Transform'), '');



                    winDataObj.updateContentHeight();

                    if (winDataObj.restoreSettings.status == 'max')
                    {
                        winDataObj.disableWindowDraggable();
                        winDataObj.disableWindowResizeable();
                    }

                    if (winDataObj.settings.enableContentScrollbar)
                    {
                        winDataObj.enableWindowScrollbar();
                    }

                    winDataObj.win.css({overflow: basewinoverflow});
                    winDataObj.focus(false, winDataObj.id);
                    winDataObj.updateWindowSizeCache(true);

                    if (gridData)
                    {
                        gridData.updateDataTableSize(winDataObj.$el);
                    }




                    winDataObj.executeEvent('onResizeStop', null, null, {height: winDataObj.restoreSettings.winHeight, width: winDataObj.restoreSettings.winWidth});

                    if (typeof callback === 'function') {
                        callback();
                    }

                    winDataObj.settings.status = winDataObj.restoreSettings.status;
                    winDataObj.restoreSettings.status = 'default';
                }
            });

            if (gridData)
            {
                $(gridData.dataTableWrapper).
                        animate({height: winDataObj.restoreSettings.bodyHeight - gridHeaderHeight}, {queue: false, duration: winDataObj.windowRestoreAnimationTime});
                $(gridData.viewport)
                        .animate({height: winDataObj.restoreSettings.bodyHeight - gridFooterHeight}, {queue: false, duration: winDataObj.windowRestoreAnimationTime});
            }

            winDataObj.BodyContent.animate(cssOpts, animopts);
        }
        else
        {

            winDataObj.BodyContent.animate(cssOpts, animopts);
            if (gridData)
            {
                $(gridData.dataTableWrapper)
                        .animate({height: winDataObj.restoreSettings.bodyHeight - gridHeaderHeight}, {queue: false, duration: winDataObj.windowRestoreAnimationTime});
                $(gridData.viewport)
                        .animate({height: winDataObj.restoreSettings.bodyHeight - gridFooterHeight}, {queue: false, duration: winDataObj.windowRestoreAnimationTime});
            }


            winDataObj.win.animate({
                
                width: winDataObj.restoreSettings.winWidth,
                height: winDataObj.restoreSettings.winHeight,
                top: winDataObj.restoreSettings.top,
                left: winDataObj.restoreSettings.left
            }, {
                queue: false,
                duration: winDataObj.windowRestoreAnimationTime,
                complete: function () {

                    if (gridData)
                    {
                        //viewPort.style.height = (winDataObj.restoreSettings.bodyHeight - gridFooterHeight);
                        //dataWrapper.style.height = (winDataObj.restoreSettings.bodyHeight - gridFooterHeight - gridHeaderHeight);
                        // gridData.updateDataTableSize(winDataObj.$el, false, {height: winDataObj.restoreSettings.bodyHeight, width: winDataObj.restoreSettings.bodyWidth});
                    }

                    winDataObj.win.removeClass('animated').removeClass('no-shadow');



                    winDataObj.BodyContent
                            .css('overflow', baseBodyoverflow)
                            .css(Modernizr.prefixed('Transition'), '')
                            .css(Modernizr.prefixed('Transform'), '');
                    if (winDataObj.settings.enableContentScrollbar)
                    {
                        winDataObj.enableWindowScrollbar();
                    }

                    var isRestore = false;
                    if (winDataObj.restoreSettings.status == 'max')
                    {
                        winDataObj.disableWindowDraggable();
                        winDataObj.disableWindowResizeable();
                        isRestore = true;
                    }


                    winDataObj.win
                            .removeClass('animated').removeClass('no-shadow')
                            .css('overflow', basewinoverflow)
                            .css(Modernizr.prefixed('Transition'), '')
                            .css(Modernizr.prefixed('Transform'), '');

                    winDataObj.updateWindowSizeCache(true);
                    winDataObj.focus(false, winDataObj.id);
                    if (gridData)
                    {
                        //   gridData.updateDataTableSize(winDataObj.$el);
                    }

                    winDataObj.executeEvent('onResizeStop', null, null, {height: winDataObj.win.height(), width: winDataObj.BodyContent.width(), contentHeight: winDataObj.BodyContent.height()});

                    if (typeof callback === 'function') {
                        callback();
                    }

                    winDataObj.settings.status = winDataObj.restoreSettings.status;
                    winDataObj.restoreSettings.status = 'default';


                }
            });
        }

        winDataObj.restoreSettings = {};
    },
    AnimateOpen: function (winDataObj)
    {

    },
    AnimateClose: function (winDataObj, closeTo, callback)
    {
        var self = winDataObj;

        if (!closeTo)
        {

            // is min clicked
            closeTo = {};
            closeTo.top = $(window).height() / 2;
            closeTo.left = $(window).width() / 2;
        }
        else if (closeTo && closeTo.fromDock) {

        }
        else {
            // center close animation
            closeTo = {};
            var offset = winDataObj.win.offset();
            closeTo.top = offset.top + (winDataObj.win.outerHeight(true) / 2);
            closeTo.left = offset.left + (winDataObj.win.outerWidth(true) / 2);
        }


        this.AnimateMin(winDataObj, false, closeTo, function () {

            winDataObj.windowInAnimation = false;


            if (typeof deleteBodyDataWindowID !== 'function') {
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
            }




            if (!self.settings.isForceClose && Tools.isFunction(self.settings.onAfterClose))
            {
                self.settings.onAfterClose(null, self, function () {

                    /**
                     * Seemode action
                     */
                    if (typeof top.closeSeemodeIframe === 'function')
                    {
                        top.closeSeemodeIframe();
                    }
                    else if (typeof parent.closeSeemodeIframe === 'function')
                    {
                        parent.closeSeemodeIframe();
                    }
                    else
                    {
                        if (Desktop.settings.isSeemode)
                        {
                            Cookie.erase('isSeemodePopup');
                            window.close();
                        }
                    }

                    deleteBodyDataWindowID(self.id);
                    self.$el.remove();

                    if (callback) {
                        callback();
                    }
                });
            }
            else
            {
                /**
                 * Seemode action
                 */
                if (typeof top.closeSeemodeIframe === 'function')
                {
                    top.closeSeemodeIframe();
                }
                else if (typeof parent.closeSeemodeIframe === 'function')
                {
                    parent.closeSeemodeIframe();
                }
                else
                {
                    if (Desktop.settings.isSeemode)
                    {
                        Cookie.erase('isSeemodePopup');
                        window.close();
                    }
                }

                deleteBodyDataWindowID(self.id);
                self.$el.remove();

                if (callback) {
                    callback();
                }
            }
        });
    }
};