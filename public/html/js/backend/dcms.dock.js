
var Dock = {
    inited: false,
    disableDatabaseUpdate: false,
    defaultSettings: {
        dockautohide: false,
        resizeable: true,
        height: 24,
        position: 'center'
    },
    tmpOrgPosition: null,
    mouseY: 0,
    mouseX: 0,
    redrawReady: false,
    maxDockHeight: 78,
    minDockHeight: 40,
    dockResizerMinWidth: 15,
    options: {},
    docObj: null,
    dockResizer: null,
    dockSize: {
        height: 0,
        width: 0
    },
    currentDockHeight: 40,
    minimizedItems: 0,
    activeItems: 0,
    lastActiveItemCount: 0,
    lastItemsCount: 0,
    init: function (options)
    {
        if (this.inited && $('#dock', $('#fullscreenContainer')).length == 1)
        {
            this.animateDockToShow();
            return;
        }

        this.options = $.extend({}, this.defaultSettings, options);

        var dockData = Desktop.get('dock');

        if (typeof dockData == 'object')
        {
            if (typeof dockData.dockHeight != 'undefined' && parseInt(dockData.dockHeight) >= this.minDockHeight)
            {
                this.currentDockHeight = parseInt(dockData.dockHeight);
            }


            if (typeof dockData.dockposition == 'string' && dockData.dockposition !== '')
            {
                this.options.position = dockData.dockposition;
            }

            if (typeof dockData.dockautohide != 'undefined')
            {
                this.options.dockautohide = dockData.dockautohide ? true : false;
            }


            if (typeof dockData.mintoappicon != 'undefined')
            {
                this.options.mintoappicon = dockData.mintoappicon ? true : false;
            }

        }

        Config.set('dockMinToAppIcon', this.options.mintoappicon);



        var self = this, _dock = $('#dock', $('#fullscreenContainer'));
        if (_dock.length == 0)
        {
            _dock = $('<div id="dock">').css({
                bottom: 0 - this.currentDockHeight,
                //minWidth: this.currentDockHeight,
                minHeight: this.minDockHeight
            }).css('display', 'inline-table');

            if (this.options.position == 'center')
            {
                _dock.css({left: '', right: '', bottom: 0 - this.currentDockHeight, height: 'auto'});
            }
            else if (this.options.position == 'left')
            {
                _dock.css({right: '', bottom: '', left: 0, height: 'auto'});
            }
            else if (this.options.position == 'right')
            {
                _dock.css({left: '', bottom: '', right: 0, height: 'auto'});
            }


            var dockInner = $('<div class="dock-left">');



            var dockIn = $('<div class="container">');
            this.dockResizer = $('<div id="dock-resizer">');
            var holder = $('<div>');
            this.dockResizer.append(holder);
            dockIn.append($('<div id="dock-inner">'));
            dockIn.append(this.dockResizer);
            dockIn.append($('<div id="dock-appmin">'));
            //_dock.append(dockIn);


            dockInner.append(dockIn);
            dockInner.append($('<div class="dock-r">'));
            _dock.append(dockInner);
            $('#fullscreenContainer').append(_dock);


            var resizeTimeout = null;
            if (this.options.resizeable)
            {
                if (this.options.position == 'center')
                {
                    holder.resizable({
                        handles: "ns",
                        maxHeight: self.maxDockHeight,
                        minHeight: self.minDockHeight,
                        start: function (event, ui) {
                            _dock.addClass('resizing');
                            self.updateDockResize(event, ui);
                        },
                        resize: function (event, ui) {
                            self.currentDockHeight = ui.size.height;
                            Dock.updateDockResize(event, ui);
                            _dock.addClass('resizing');
                        },
                        stop: function (event, ui)
                        {
                            self.currentDockHeight = ui.size.height;
                            Dock.updateDockResize(event, ui);
                            _dock.removeClass('resizing');
                            clearTimeout(self.updaterTimer);
                            self.updateDatabase();
                        }
                    });
                }
                else
                {
                    holder.resizable({
                        handles: "e,w",
                        maxWidth: self.maxDockHeight,
                        minWidth: self.minDockHeight + 10,
                        start: function (event, ui) {
                            _dock.addClass('resizing');
                            self.updateDockResize(event, ui);
                        },
                        resize: function (event, ui) {
                            self.currentDockHeight = ui.size.height;
                            Dock.updateDockResize(event, ui);
                            _dock.addClass('resizing');
                        },
                        stop: function (event, ui)
                        {
                            self.currentDockHeight = ui.size.width;
                            Dock.updateDockResize(event, ui);
                            _dock.removeClass('resizing');
                            clearTimeout(self.updaterTimer);
                            self.updateDatabase();
                        }
                    });
                }
            }

            if (self.options.position == 'center')
            {
                _dock.css({
                    left: '', right: '',
                    bottom: 0
                }).removeClass('right-pos').removeClass('left-pos');
            }
            else if (self.options.position == 'left')
            {
                _dock.css({
                    left: 0,
                    right: '', bottom: '',
                    top: ($(window).height() - $('#Taskbar').outerHeight(true)) / 2 - (_dock.innerHeight() / 2)
                }).removeClass('right-pos').addClass('left-pos');
            }
            else if (self.options.position == 'right')
            {
                _dock.css({
                    left: '', bottom: '',
                    right: 0,
                    top: ($(window).height() - $('#Taskbar').outerHeight(true)) / 2 - (_dock.innerHeight() / 2)
                }).removeClass('left-pos').addClass('right-pos');
            }

        }
        else
        {
//$('#dock').css({opacity: '1'}).show();

            this.activeItems = $('.appicn', $('#dock-inner')).length;
            this.minimizedItems = $('.appicn', $('#dock-appmin')).length;



            this.dockResizer = $('#dock-resizer', _dock);
            holder = $('#dock-resizer div', _dock);
            if (this.options.resizeable)
            {
                if (this.options.position == 'center')
                {
                    holder.resizable({
                        handles: "n, s",
                        maxHeight: this.maxDockHeight,
                        minHeight: this.minDockHeight,
                        start: function (event, ui) {
                            _dock.addClass('resizing');
                            Dock.updateDockResize(event, ui);
                        },
                        resize: function (event, ui) {
                            self.currentDockHeight = ui.size.height;
                            Dock.updateDockResize(event, ui);
                            _dock.addClass('resizing');
                        },
                        stop: function (event, ui)
                        {
                            self.currentDockHeight = ui.size.height;
                            Dock.updateDockResize(event, ui);
                            _dock.removeClass('resizing');
                            clearTimeout(self.updaterTimer);
                            self.updateDatabase();
                        }
                    });
                }
                else
                {
                    holder.resizable({
                        handles: "w,e",
                        maxWidth: this.maxDockHeight,
                        minWidth: this.minDockHeight + 10,
                        start: function (event, ui) {
                            _dock.addClass('resizing');
                            Dock.updateDockResize(event, ui);
                        },
                        resize: function (event, ui) {
                            Dock.updateDockResize(event, ui);
                            _dock.addClass('resizing');
                        },
                        stop: function (event, ui)
                        {
                            Dock.updateDockResize(event, ui);
                            _dock.removeClass('resizing');
                            clearTimeout(self.updaterTimer);
                            self.updateDatabase();
                        }
                    });
                }


            }
        }







        if (typeof dockData == 'object')
        {
            if (typeof dockData.dockHeight != 'undefined' && parseInt(dockData.dockHeight) > 0)
            {
                $('#dock .dock-left img').css({
                    'width': parseInt(dockData.dockHeight - 10) + 'px',
                    'height': parseInt(dockData.dockHeight - 10) + 'px'
                }).each(function () {

                    self.currentDockHeight = parseInt(dockData.dockHeight);
                    self.updateDockPos();
                });
            }




            setTimeout(function () {


                if (typeof dockData.dockItems == 'object' && dockData.dockItems.length > 0)
                {
                    self.disableDatabaseUpdate = true;
                    var x;
                    for (x = 0; x < dockData.dockItems.length; x++)
                    {
                        var dat = dockData.dockItems[x];
                        var opts = {
                            WindowURL: Tools.prepareAjaxUrl(dat.url),
                            Controller: dat.controller,
                            controller: dat.controller,
                            Action: dat.action,
                            action: dat.action,
                            WindowTitle: dat.WindowTitle,
                            isStatic: true,
                            isRootApplication: dat.isRootApplication,
                            label: dat.WindowTitle,
                            title: dat.WindowTitle,
                            url: Tools.prepareAjaxUrl(dat.url),
                            WindowID: Desktop.getHash(dat.url)
                        };
                        opts.loadWithAjax = true;
                        opts.allowAjaxCache = false;
                        opts.WindowToolbar = false;
                        opts.DesktopIconWidth = 36;
                        opts.DesktopIconHeight = 36;
                        opts.UseWindowIcon = false;
                        opts.Skin = Desktop.settings.Skin;
                        opts.WindowDesktopIconFile = '';
                        self.createDockIcon(null, opts);
                    }



                    self.disableDatabaseUpdate = false;
                    setTimeout(function () {

                        if (typeof dockData.activeItems == 'object' && dockData.activeItems.length > 0)
                        {
                            self.disableDatabaseUpdate = true;
                            var x = 0, len = dockData.activeItems.length;
                            for (x = 0; x < len; x++)
                            {
                                self.loadDefaultWindows(dockData.activeItems[x], (x == (len - 1) ? true : false));
                                //      Debug.log('L: ' + len);
                            }
                        }


                    }, 150);
                }


            }, 100);
        }


        _dock.hide();

        this.docObj = _dock;
        this.updateDockPos();
        this.resigerDropEvent();

        clearTimeout(this.updaterTimer);

        this.updateDatabase(true);

        $('#dock-inner').sortable({
            cursor: "move",
            items: "div.appicn",
            scroll: false,
            tolerance: "pointer",
            appendTo: $('#desktop'),
            forceHelperSize: true,
            forcePlaceholderSize: false,
            placeholder: 'dock-sort-placeholder',
            cursorAt: {
                left: 3,
                top: 3
            },
            start: function (event, ui) {
                $(this).addClass('ismove');
            },
            sort: function (event, ui) {


                if ($(ui.item).attr('id') !== 'launchpad_action' && !$(ui.item).hasClass('active'))
                {
                    var _posTop = ui.offset.top;
                    var posLeft = ui.offset.left;

                    //  console.log('remove from dock posTop ' + _posTop + ' ui ' + ui.offset.top);

                    // remove Dock Icon
                    if (self.options.position == 'center' && _posTop < (_dock.offset().top - (_dock.outerHeight() + 20)))
                    {
                        //     console.log('remove from dock center');
                        $(ui.item).find('span:first').addClass('trash-dock-item');
                    }
                    else if (self.options.position == 'left' && posLeft >= (_dock.outerWidth() + 20))
                    {
                        //    console.log('remove from dock left');
                        $(ui.item).find('span:first').addClass('trash-dock-item');
                    }
                    else if (self.options.position == 'right' && posLeft <= (_dock.offset().left - (_dock.outerWidth() + 20)))
                    {
                        //   console.log('remove from dock right');
                        $(ui.item).find('span:first').addClass('trash-dock-item');
                    }
                    else
                    {
                        $(ui.item).find('span:first').removeClass('trash-dock-item');
                    }

                }
            },
            stop: function (event, ui) {

                $(ui.item).find('span:first').removeClass('trash-dock-item');

                if ($(ui.item).attr('id') !== 'launchpad_action' && !$(ui.item).hasClass('active'))
                {
                    var trash = false;
                    var _posTop = ui.offset.top;
                    var posLeft = ui.offset.left;

                    //  console.log('remove from dock posTop ' + _posTop + ' ui ' + ui.offset.top);

                    // remove Dock Icon
                    if (self.options.position == 'center' && _posTop < (_dock.offset().top - (_dock.outerHeight() + 20)))
                    {
                        trash = true;
                        //     console.log('remove from dock center');
                    }
                    else if (self.options.position == 'left' && posLeft >= (_dock.outerWidth() + 20))
                    {
                        trash = true;
                        //   console.log('remove from dock left');
                    }
                    else if (self.options.position == 'right' && posLeft <= (_dock.offset().left - (_dock.outerWidth() + 20)))
                    {
                        trash = true;
                        //  console.log('remove from dock right');
                    }


                    if (trash)
                    {
                        $(ui.item).css({top: _posTop, left: posLeft, 'position': 'absolute'});
                        Desktop.Trash.empty($(ui.item), event);

                        setTimeout(function () {
                            $(ui.item).remove();
                            self.updateDockPos();
                            self.dockBubbles();


                            self.updateDatabase(true);
                        }, 800);
                    }
                }

                $(ui.item).removeClass('ismove');
            }

        });



        $("#dock-inner div.appicn").disableSelection();

        /*
         //below are methods for maintaining a constant 60fps redraw for the dock without flushing
         $(document).bind("mousemove", function(e) {
         if (self.docObj.is(":visible")) {
         self.mouseX = e.pageX;
         self.mouseY = e.pageY;
         self.redrawReady = true;
         self.registerConstantCheck();
         }
         });
         
         */




        setTimeout(function () {


            // Disable dock if is Seemode
            if (Desktop.settings.isSeemode)
            {
                self.docObj.hide();
            }
            else {


                if (dockData.dockautohide)
                {
                    self.toggleDockView();
                    self.enableAutoHide();

                }
                else
                {
                    self.toggleDockView();
                }

                self.dockBubbles();



                if (self.options.position == 'center') {
                    $('#dock-inner,#dock .container,#dock-appmin').width('');
                }
                else {
                    $('#dock-inner,#dock .container,#dock-appmin').height('');
                }

            }
        }, 100);


        this.inited = true;
    },
    enableAutoHide: function ()
    {
        $(document).unbind('mousemove.dock');
        var self = this;

        if (self.options.position == 'center')
        {
            $('#dock').stop().css({
                bottom: 0 - $('#dock').outerHeight()});
        }
        else if (self.options.position == 'left')
        {
            $('#dock').stop().css({
                left: 0 - $('#dock').outerHeight()});
        }
        else if (self.options.position == 'right')
        {
            $('#dock').stop().css({
                right: 0 - $('#dock').outerHeight()});
        }

        $(document).on('mousemove.dock', function (e) {
            var de = document.documentElement;
            var docHeight = window.innerHeight || (de && de.clientHeight) || document.body.clientHeight;
            var docWidth = window.innerWidth || (de && de.clientWidth) || document.body.clientWidth;

            if (self.options.position == 'center' && e.pageY > docHeight - $('#dock').height())
            {
                if (parseInt($('#dock').css('bottom')) < 0) {
                    $('#dock').stop().animate({
                        bottom: 0}, 200);
                }
            }
            else if (self.options.position == 'left' && e.pageX < $('#dock').width())
            {
                if (parseInt($('#dock').css('left')) < 0) {
                    $('#dock').stop().animate({
                        left: 0}, 200);
                }
            }
            else if (self.options.position == 'right' && e.pageX > docWidth - $('#dock').width())
            {
                if (parseInt($('#dock').css('right')) < 0) {
                    $('#dock').stop().animate({
                        right: 0}, 200);
                }
            }
            else
            {
                if (self.options.position == 'center') {
                    $('#dock').stop().animate({
                        bottom: 0 - $('#dock').height()}, 200);
                }
                else if (self.options.position == 'left') {
                    $('#dock').stop().animate({
                        left: 0 - $('#dock').width()}, 200);
                }
                else if (self.options.position == 'right') {
                    $('#dock').stop().animate({
                        right: 0 - $('#dock').width()}, 200);
                }
            }
        });
    },
    disableAutoHide: function ()
    {
        $(document).unbind('mousemove.dock');
        $('#dock').css({bottom: 0}).show();
    },
    setDockPosition: function (pos)
    {
        if (this.tmpOrgPosition === null)
        {
            this.tmpOrgPosition = this.options.position;
        }

        this.options.position = pos;
        var _dock = $('#dock');

        if (this.options.position == 'center')
        {
            _dock.css({
                left: '',
                right: '',
                bottom: 0, top: ''
            }).removeClass('right-pos').removeClass('left-pos');
        }
        else if (this.options.position == 'left')
        {
            _dock.css({
                left: 0,
                right: '',
                bottom: '',
                top: ($(window).height() - $('#Taskbar').outerHeight(true)) / 2 - (_dock.innerHeight() / 2)
            }).removeClass('right-pos').addClass('left-pos');
        }
        else if (this.options.position == 'right')
        {
            _dock.css({
                left: '',
                bottom: '',
                right: 0,
                top: ($(window).height() - $('#Taskbar').outerHeight(true)) / 2 - (_dock.innerHeight() / 2)
            }).removeClass('left-pos').addClass('right-pos');
        }

        this.updateDockPos();
    },
    resetDockPosition: function ()
    {
        if (this.tmpOrgPosition !== null)
        {
            this.options.position = this.tmpOrgPosition;
            var _dock = $('#dock');

            if (this.options.position == 'center')
            {
                _dock.css({
                    left: '',
                    right: '',
                    bottom: 0, top: ''
                }).removeClass('right-pos').removeClass('left-pos');
            }
            else if (this.options.position == 'left')
            {
                _dock.css({
                    left: 0,
                    right: '', bottom: '',
                    top: ($(window).height() - $('#Taskbar').outerHeight(true)) / 2 - (_dock.innerHeight() / 2)
                }).removeClass('right-pos').addClass('left-pos');
            }
            else if (this.options.position == 'right')
            {
                _dock.css({
                    left: '', bottom: '',
                    right: 0,
                    top: ($(window).height() - $('#Taskbar').outerHeight(true)) / 2 - (_dock.innerHeight() / 2)
                }).removeClass('left-pos').addClass('right-pos');
            }


            this.updateDockPos();
        }
    },
    registerConstantCheck: function ()
    {
        if (!this.animating)
        {
            var self = this;
            this.animating = true;
            window.setTimeout(function () {
                self.callCheck();
            }, 15);
        }
    },
    callCheck: function () {
        this.sizeDockIcons();
        this.animating = false;
        if (this.redrawReady)
        {
            this.redrawReady = false;
            this.registerConstantCheck();
        }
    },
    distance: function (x0, y0, x1, y1) {
        var xDiff = x1 - x0;
        var yDiff = y1 - y0;

        return Math.sqrt(xDiff * xDiff + yDiff * yDiff);
    },
    //do the maths and resize each icon
    sizeDockIcons: function ()
    {
        var self = this;
        var baseHeight = self.currentDockHeight;

        this.docObj.find(".appicn img").each(function ()
        {
            var proximity = 180;
            var iconSmall = baseHeight, iconLarge = 128; //css also needs changing to compensate with size
            var iconDiff = (iconLarge - iconSmall);

            //find the distance from the center of each icon
            var centerX = $(this).offset().left + ($(this).outerWidth() / 2.0);
            var centerY = $(this).offset().top + ($(this).outerHeight() / 2.0);
            var dist = self.distance(centerX, centerY, self.mouseX, self.mouseY);

            //determine the new sizes of the icons from the mouse distance from their centres
            var newSize = (1 - Math.min(1, Math.max(0, dist / proximity))) * iconDiff + iconSmall;

            $(this).css({width: '', height: newSize});
        });
    },
    loadDefaultWindowsW: false,
    loadDefaultWindowsT: null,
    animateDockToShow: function ()
    {
        this.docObj.css({opacity: '1'}).hide();
        this.toggleDockView();
    },
    animateDockToHide: function ()
    {
        this.toggleDockView(true);
    },
    loadDefaultWindows: function (dat, enableUpdate)
    {
        var self = this;
        if (this.loadDefaultWindowsW || Desktop.ajaxWorkerOn || Desktop.windowWorkerOn)
        {
            this.loadDefaultWindowsT = setTimeout(function () {
                self.loadDefaultWindows(dat, enableUpdate);
            }, 150);
        }
        else
        {
            this.loadDefaultWindowsW = true;
            if (enableUpdate == true)
            {
                this.disableDatabaseUpdate = false;
                //   console.log('enable database updateer');
            }


            if ($('#dock-inner .dock-' + dat.controller).length == 1) {
                // console.log('click .dock-' + dat.controller);
                $('#dock-inner .dock-' + dat.controller).click();
            }
            else
            {
                //  console.log('click .dock-' + dat.controller + ' not found');
                this.loadDefaultWindowsW = false;
            }
        }
    },
    toggleDockView: function (hide)
    {
        if (this.docObj.is(':visible') || hide === true)
        {
            if (this.options.position == 'center')
            {
                this.docObj.css({
                    bottom: 0
                }).stop(true).animate({
                    bottom: 0 - this.docObj.outerHeight(true)
                }, 400, function () {
                    $(this).hide();
                });
            }
            else if (this.options.position == 'left')
            {
                this.docObj.stop(true).animate({
                    left: 0 - this.docObj.outerWidth(true)
                }, 400, function () {
                    $(this).hide();
                });
            }
            else if (this.options.position == 'right')
            {
                this.docObj.stop(true).animate({
                    right: 0 - this.docObj.outerWidth(true)
                }, 400, function () {
                    $(this).hide();
                });
            }
        }
        else
        {
            if (this.options.position == 'center')
            {
                this.docObj.css({
                    bottom: 0 - this.docObj.outerHeight(true)
                }).show().stop(true).animate({
                    bottom: 0
                }, 400, function () {
                    $(this).css('display', 'inline-table');
                });
            }
            else if (this.options.position == 'left')
            {
                this.docObj.show().stop(true).animate({
                    left: 0
                }, 400, function () {
                    $(this).css('display', 'inline-table');
                });
            }
            else if (this.options.position == 'right')
            {
                this.docObj.show().stop(true).animate({
                    right: 0
                }, 400, function () {
                    $(this).css('display', 'inline-table');
                });
            }
        }
    },
    ApplicationOpenError: function (controller)
    {
        var self = this;
        $('#dock-inner').find('.dock-' + controller + '.bounced').removeClass('active').stop(true).animate({
            width: 0
        }, 350, function () {
            $(this).remove();
            self.activeItems = $('#dock-inner .appicn').length;
            Dock.updateDockPos();
        });
    },
    bounceInterval: null,
    stopBounceInterval: null,
    hardStopCounter: 0,
    step: 0,
    doBounce: function (dockItem, bounce)
    {
        if (this.step > 5 || !bounce)
        {
            clearInterval(this.bounceInterval);
            clearTimeout(this.stopBounceInterval);
            //if (bounce)
            //  console.log('doBounce call more as 20 loops');


            if (typeof dockItem != 'object' && dockItem == null)
            {
                $('.bounced', $('#dock-inner')).stop(true, true).css({top: '', left: '', right: ''});
            }
            else if (typeof dockItem == 'object' && dockItem != null)
            {
                dockItem.stop(true, true).css({top: '', left: '', right: ''});
            }

            if (!bounce) {
                setTimeout(function () {
                    dockItem.css({top: '', left: '', right: ''}).removeClass('bounced');
                    ;
                }, 600);
            }

            this.step = 0;
            return;
        }

        this.step++;
        dockItem.queue('fx', []);
        if (this.options.position == 'center')
        {
            dockItem.stop(true, true).addClass('bounced').css({
                WebkitTransform: 'translate(0px,0px)'
            }).queue(
                    function ()
                    {
                        $(this).animate({top: '-=9'}, 300, function () {
                            $(this).animate({top: '+=9'}, 300)
                        });
                        //De-queue our newly queued function so that queues
                        //can keep running.
                        $(this).dequeue();
                    }
            );
        }
        else if (this.options.position == 'left')
        {
            dockItem.stop(true, true).addClass('bounced').css({
                WebkitTransform: 'translate(0px,0px)'
            }).queue(
                    function ()
                    {
                        $(this).animate({right: '-=9'}, 300, function () {
                            $(this).animate({right: '+=9'}, 300)
                        });
                        //De-queue our newly queued function so that queues
                        //can keep running.
                        $(this).dequeue();
                    }
            );
        }
        else if (this.options.position == 'right')
        {
            dockItem.stop(true, true).addClass('bounced').css({
                WebkitTransform: 'translate(0px,0px)'
            }).queue(
                    function ()
                    {
                        $(this).animate({left: '-=9'}, 300, function () {
                            $(this).animate({left: '+=9'}, 300)
                        });
                        //De-queue our newly queued function so that queues
                        //can keep running.
                        $(this).dequeue();
                    }
            );
        }


    },
    stopBounceHard: function (windowID)
    {

        var self = this;
        if (windowID && $('#' + windowID).data('WindowManager'))
        {
            if ($('#' + windowID).data('WindowManager').get('complete') === true)
            {
                clearTimeout(this.stopBounceInterval);
                clearInterval(this.bounceInterval);
                $('#dock-inner').find('.dock-' + $('#' + windowID).attr('app')).stop(true, true).css({'top': '', left: '', right: ''}).removeClass('bounced');
                this.updateDockPos();
            }
            else
            {
                this.hardStopCounter++;
                if (this.hardStopCounter <= 20)
                {
                    this.stopBounceInterval = setTimeout(function () {
                        // console.log('stopBounceHard run ');
                        self.stopBounceHard(windowID);
                    }, 300);
                }
                else
                {
                    this.hardStopCounter = 0;
                    clearTimeout(this.stopBounceInterval);
                    clearInterval(this.bounceInterval);
                    $('#dock-inner').find('.dock-' + $('#' + windowID).attr('app')).stop(true, true).css({'top': '', left: '', right: ''}).removeClass('bounced');
                    this.updateDockPos();
                    console.error('stopBounceHard not work 1');
                }
            }
        }
        else
        {
            this.hardStopCounter++;
            if (this.hardStopCounter <= 20)
            {
                this.stopBounceInterval = setTimeout(function () {
                    //  console.log('stopBounceHard run ');
                    self.stopBounceHard(Win.windowID);
                }, 300);
            }
            else
            {
                this.hardStopCounter = 0;
                clearTimeout(this.stopBounceInterval);
                clearInterval(this.bounceInterval);
                $('#dock-inner').find('.dock-' + $('#' + Win.windowID).attr('app')).stop(true, true).css({'top': '', left: '', right: ''}).removeClass('bounced');
                console.error('stopBounceHard not work 2');
            }
        }
    },
    stopBounce: function (app, windowID)
    { //return;

        var self = this;
        if (typeof windowID == 'string')
        {
            if ($('#' + windowID).data('WindowManager').get('complete') === true)
            {
                clearTimeout(this.stopBounceInterval);
                clearInterval(this.bounceInterval);
                $('#dock-inner').find('.dock-' + app).stop(true, true).css({'top': '', left: '', right: ''}).removeClass('bounced');
                this.updateDockPos();
            }
            else
            {
                this.stopBounceInterval = setTimeout(function () {
                    self.stopBounce(app, windowID);
                }, 300);
            }
        }
        else
        {
            clearTimeout(this.stopBounceInterval);
            clearInterval(this.bounceInterval);
            $('#dock-inner').find('.dock-' + app).stop(true, true).css({'top': '', left: '', right: ''}).removeClass('bounced');
            this.updateDockPos();
        }
    },
    resigerDropEvent: function ()
    {

        $('#dock-inner').droppable({
            accept: ".fromMenu,.fromlaunchpad",
            revert: true,
            tolerance: "pointer",
            over: function (event, ui) {
                $("#DesktopIcons").droppable('disable');
                //    console.log('drop over dock');
            },
            out: function (event, ui)
            {
                if (!$(event.target).parents('#dock').length)
                {
                    $("#DesktopIcons").droppable('enable');
                }
            },
            drop: function (event, ui) {

                $(this).find(".placeholder").remove();
                var icon;
                // move item out of a sub container
                if ($(ui.draggable).hasClass('fromBalloon'))
                {

                }
                else if ($(ui.draggable).hasClass('fromlaunchpad'))
                {
                    var itemData = $(ui.draggable).removeClass('fromlaunchpad').data('itemData');
                    var currentElement = $(ui.helper).removeClass('fromlaunchpad');
                    currentElement.data('itemData', itemData);
                    currentElement.removeClass('fromlaunchpad').hide();
                    currentElement.appendTo($(this));
                    if (!itemData)
                    {
                        //console.log('empty itemData ');
                    }

                    Launchpad.hide();
                    DesktopMenu.hideMenu(0);

                    icon = '';
                    if (typeof itemData.sprite_icon == 'string')
                    {
                        if ($('span', $(ui.helper)).hasClass('cfg'))
                        {
                            icon = Desktop.baseURL + 'html/style/c9/img/' + 'cfgitems/' + itemData.sprite_icon + '.png';
                        }
                        else
                        {
                            icon = Desktop.baseURL + 'html/style/c9/img/' + 'pulldownmenu/' + itemData.sprite_icon + '.png';
                        }
                    }
                    else
                    {
                        icon = $(ui.draggable).find('img').attr('src');
                    }

                    var opts = {};
                    opts.loadWithAjax = true;
                    opts.WindowToolbar = false;
                    opts.DesktopIconWidth = 24;
                    opts.DesktopIconHeight = 24;
                    opts.UseWindowIcon = false;
                    opts.WindowURL = Tools.prepareAjaxUrl(itemData.url);
                    opts.WindowStatus = 'closed';

                    if (itemData.ajax)
                    {
                        opts.loadWithAjax = true;
                    }

                    var hashID = $.fn.getWindowHash(itemData.url);
                    opts.Skin = Desktop.settings.Skin;
                    opts.WindowDesktopIconFile = (icon ? icon : '');
                    opts = $.extend({}, opts, itemData);
                    opts.WindowTitle = itemData.label;
                    var ctl = Tools.extractAppInfoFromUrl(itemData.url);
                    opts.Controller = ctl.controller;
                    opts.Action = ctl.action;
                    opts.WindowID = hashID;
                    opts.isStatic = true;

                    Dock.createDockIcon(event, opts);
                }
            }
        });
    },
    // create a new dock icon if dragged from launchpad or menu
    createDockIcon: function (event, options, fromInit)
    {
        var self = this, opts = {
            Controller: null,
            WindowTitle: null,
            isStatic: false,
            WindowID: null
        };

        var opt = $.extend({}, opts, options);

        if (opt.Controller == 'plugin' && opt.WindowURL && opt.WindowURL != '')
        {
            var pluginName = $.getURLParam('plugin', opt.WindowURL);
            if (pluginName)
            {
                opt.Controller = pluginName;

                opt.isAddon = true;
            }
        }


        if ($('#dock').find('.dock-' + opt.Controller + '[windowid="' + opt.WindowID + '"]').length == 0)
        {
            var min = $('<div/>')
                    .attr('windowid', opt.WindowID)
                    .attr('app', opt.Controller)
                    .attr('label', opt.WindowTitle)
                    .addClass('dock-' + opt.Controller)
                    .addClass('appicn')
                    .addClass('bounced')
                    .data('windowData', opt);

            if (opt.isStatic)
            {
                min.addClass('static');
            }

            var dockHeight = self.currentDockHeight;
            var img = $('<img/>').attr('src', Application.getAppIcon(opt.Controller, 128, (typeof opt.isAddon != 'undefined' ? opt.isAddon : false)));

            img.height(dockHeight - 10).width(dockHeight - 10);
            // min.height(dockHeight - 10).width(dockHeight - 10);

            min.append(img).append($('<span>'));
            min.click(function (e) {
                var _self = $(this);
                $(this).addClass('active');
                Launchpad.hide();
                if ($('#' + $(this).attr('windowid')).length == 0)
                {
                    self.activeItems = $('#dock-inner .appicn').length;
                    self.doBounce(_self, true);
                    self.bounceInterval = setInterval(function () {
                        self.doBounce(_self, true);
                    }, 600);

                    var openerid = Win.windowID, windowOpts = _self.data('windowData');
                    //windowOpts.DockUpdateData = self.getDockChangeRequestOptions();


                    $('#desktop').mask('Bitte warten...');
                    setTimeout(function () {
                        Desktop.getAjaxContent(windowOpts, function (data)
                        {
                            if (Tools.exists(data, 'isSingleWindow'))
                            {
                                if (data.isSingleWindow) {
                                    windowOpts.isSingleWindow = true;
                                }
                            }


                            Application.cacheCurrentApp(windowOpts.Controller, windowOpts.Action, data);



                            if (typeof Desktop.ajaxData.Error != 'undefined' && Desktop.ajaxData.Error == true)
                            {
                                self.ApplicationOpenError(windowOpts.Controller);
                                Debug.error((typeof Desktop.ajaxData.msg == 'string' ? Desktop.ajaxData.msg : 'Error'));
                                Notifier.display('error', Desktop.ajaxData.msg);
                                Desktop.windowWorkerOn = self.loadDefaultWindowsW = false;
                                clearInterval(self.bounceInterval);
                                clearTimeout(self.loadDefaultWindowsT);
                                self.doBounce(_self, false);
                                self.disableDatabaseUpdate = false;
                                clearTimeout(self.updaterTimer);
                                Dock.updateDatabase(true);
                            }
                            else
                            {



                                var isSingleWindow = typeof data.isSingleWindow != 'undefined' && data.isSingleWindow != null ? data.isSingleWindow : false;
                                var nopadding = (typeof data.nopadding != 'undefined' && data.nopadding != null ? data.nopadding : isSingleWindow);
                                windowOpts.nopadding = nopadding;
                                windowOpts.enableContentScrollbar = (typeof data.scrollable != 'undefined' && data.scrollable != null ? (data.scrollable ? true : false) : true);

  
                                var Top = _self.offset().top + 10;
                                var Left = _self.offset().left + 10;
                                windowOpts.closeToPos = {left: Left, top: Top, fromDock: true};


                                Desktop.GenerateNewWindow(windowOpts, e, function (winObject, winDataObj, id) {

                                    if (openerid) {
                                        winDataObj.set('openerID', openerid);
                                    }
                                    winObject.unmask('');
                                    Desktop.windowWorkerOn = true;
                                    Dock.doBounce(_self, false);
                                    winObject.attr('app', windowOpts.Controller);
                                    winDataObj.set('isRootApplication', true);
                                    setTimeout(function () {
                                        Application.createAppMenu(windowOpts.Controller, windowOpts.Action);
                                        Desktop.windowWorkerOn = self.loadDefaultWindowsW = false;
                                        if (self.disableDatabaseUpdate != true)
                                        {
                                            clearTimeout(self.updaterTimer);
                                            self.updateDatabase(true);
                                        }

                                        clearInterval(self.bounceInterval);
                                    }, 20);
                                });
                            }


                        });

                    }, 80);
                }
                else
                {
                    var app = $(this).attr('app');
                    if (app && $('#' + $(this).attr('windowid')))
                    {
                        self.showApplication(app, $(this).hasClass('static'));
                        var windowOpts = $(this).data('windowData');
                        
                        
                        
                        
                        $('#' + windowOpts.WindowID).data('WindowManager').focus();
                        Application.focus(e, app, $('#' + windowOpts.WindowID).data('WindowManager').get('Action'), $('#' + windowOpts.WindowID));
                    }
                }
                return false;
            });




            $('#dock-inner').append(min).each(function () {
                self.activeItems = $('#dock-inner .appicn').length;

                if (self.activeItems > 0)
                {
                    $('.appicn img', $('#dock-inner')).css({
                        width: dockHeight - 10,
                        height: dockHeight - 10
                    });
                }

                if (self.minimizedItems > 0)
                {
                    $('.appicn img', $('#dock-appmin')).css({
                        width: dockHeight - 10,
                        height: dockHeight - 10
                    });
                }

                if (fromInit != true)
                {
                    setTimeout(function () {

                        // save to database
                        if (opt.isStatic)
                        {
                            clearTimeout(self.updaterTimer);
                            self.updateDatabase();
                        }

                        self.updateDockPos();
                        self.dockBubbles();
                    }, 300);
                }

            });
            return min;
        }


        return $('#dock').find('.dock-' + opt.Controller + '[windowid="' + opt.WindowID + '"]');
    },
    runApplication: function (options)
    {
        var self = this;
        var self = this, opts = {
            Controller: null,
            WindowTitle: null,
            isStatic: false,
            WindowID: null
        };

        var option = $.extend({}, opts, options);

        if ($('#dock').find('.dock-' + option.Controller + '[windowid="' + option.WindowID + '"]').length == 0)
        {
            clearTimeout(self.updaterTimer);
            var min = $('<div>').attr('windowid', option.WindowID).attr('app', option.Controller).attr('label', option.WindowTitle).addClass('dock-' + option.Controller).css({
                height: $('#dock .dock-left', $('#desktop')).outerHeight() - 10
            }).addClass('appicn').addClass('active').addClass('bounced').data('windowData', option);
            if (option.isStatic)
            {
                min.addClass('static');
            }

            var img = $('<img>').attr('src', Application.getAppIcon(option.Controller, 48));
            var dockHeight = this.currentDockHeight;
            img.height(dockHeight);


            min.empty().append(img).append($('<span>'));
            min.css({
                height: dockHeight
            }).click(function (e) {
                var app = $(e.target).attr('app');
                if (app)
                {
                    self.showApplication(app, $(e.target).hasClass('static'));
                }
            });

            $('#dock-inner').append(min).each(function () {

                self.activeItems = $('#dock-inner .appicn').length;
                self.minimizedItems = $('#dock-appmin .appicn').length;

                /*
                 $('.container,#dock-inner,#dock-resizer,#dock-appmin,.dock-r,.dock-left', $('#dock')).css({
                 height: dockHeight + 10
                 });
                 
                 $('#dock-inner').css({
                 width: self.dockSize.height * self.activeItems
                 });
                 */
                /*
                 if (Dock.activeItems > 0)
                 {
                 
                 
                 $('.appicn', $('#dock-inner')).css({
                 width: dockHeight
                 });
                 
                 }
                 
                 $('#dock-appmin').css({
                 width: self.dockSize.height * self.minimizedItems
                 });
                 */
                if (self.minimizedItems > 0)
                {
                    $('.appicn img', $('#dock-appmin')).css({
                        width: dockHeight
                    });
                }

                /*
                 $('.container', $('#dock')).css({
                 width: $('#dock-appmin').outerWidth() + $('#dock-inner').outerWidth() + $('#dock-resizer').outerWidth() + $('.dock-r', $('#dock')).outerWidth()
                 });
                 
                 */

                self.updateDockPos();
                self.dockBubbles();
                self.doBounce(min, true);
                self.bounceInterval = setInterval(function () {
                    self.doBounce(min, true);
                    setTimeout(function () {
                        self.doBounce(min, false);
                    }, 600);
                }, 601);
                clearTimeout(self.updaterTimer);
                self.updateDatabase(true);
            });
        }
        else
        {
            this.showApplication(option.Controller);
            $('#dock', $('#desktop')).find('.dock-' + option.Controller + '[windowid="' + option.WindowID + '"]').addClass('active');
        }

        this.dockBubbles();
    },
    showApplication: function (controller, event)
    {
        var self = this;
        var winID;

        if (this.options.mintoappicon)
        {
            if ($('#dock-inner').find('.dock-' + controller).length == 1)
            {
                winID = $('#dock-inner').find('.dock-' + controller).attr('windowid');
            }
            else
            {
                return;
            }
        }
        else
        {
            winID = $('#dock-appmin .dock-' + controller + '-min').attr('windowid');
        }


        if ($('#' + winID).data('WindowManager'))
        {
            $('#' + winID).data('WindowManager').restore();
            $('#dock-inner').find('.dock-' + controller).addClass('active');

            if (!this.options.mintoappicon)
            {
                $('#dock-appmin .dock-' + controller + '-min').css('overflow', 'hidden').animate({
                    width: 0
                }, 150, 'linear',
                        function () {
                            $('#dock-inner').find('.dock-' + controller + '[windowid="' + winID + '"]').addClass('active');
                            Win.redrawWindowHeight(winID, true);


                            //Win.refreshWindowScrollbars(winID);
                            $(this).remove();
                        });
            }
        }

        setTimeout(function () {
            self.activeItems = $('#dock-inner .appicn').length;
            self.minimizedItems = $('#dock-appmin .appicn').length;
            self.updateDockPos();

        }, 200);
    },
    hideApplication: function (controller, winID, callback)
    {
        var self = this, ret = false;

        if (this.options.mintoappicon)
        {
            if ($('#dock-inner').find('.dock-' + controller).length == 1)
            {
                ret = $('#dock-inner').find('.dock-' + controller);
            }
            else {
                ret = false;
            }
        }


        if ($('#dock-appmin').find('.dock-' + controller + '-min').length == 0)
        {
            var dockHeight = this.currentDockHeight;
            if ($('#' + winID).data('WindowManager'))
            {
                var dat = $('#' + winID).data('WindowManager');
                var min = $('<div>')
                        .attr('id', 'min-' + winID)
                        .attr('windowid', winID)
                        .attr('app', dat.get('Controller'))
                        .attr('label', dat.getTitle())
                        .addClass('dock-' + dat.get('Controller') + '-min dock-' + dat.get('Controller') + ' appicn').height(dockHeight - 10).width(dockHeight - 10);

                controller = dat.get('Controller');
            }
            else if ($('.dock-' + controller, $('#dock')).length)
            {
                var min = $('<div>')
                        .attr('id', 'min-' + $('.dock-' + controller, $('#dock')).attr('windowid'))
                        .attr('windowid', $('.dock-' + controller, $('#dock')).attr('windowid'))
                        .attr('app', controller)
                        .attr('label', $('.dock-' + controller, $('#dock')).attr('label'))
                        .addClass('dock-' + controller + '-min dock-' + controller + ' appicn').height(dockHeight - 10).width(dockHeight - 10);
            }
            else
            {
                ret = false;
            }

            if (min !== false && controller) {
                var img = $('<img>').attr('src', Application.getAppIcon(controller, 48));

                min.append(img);
                min.click(function (e) {
                    self.showApplication(controller, e);
                });

                $('#dock-appmin').append(min);
                self.activeItems = $('#dock-inner .appicn').length;
                self.minimizedItems = $('#dock-appmin .appicn').length;

                setTimeout(function () {
                    self.dockBubbles();
                    self.updateDockPos();
                }, 10);


                ret = min;
            }
            else {
                ret = false;
            }

        }
        else
        {
            ret = $('#dock-appmin').find('.dock-' + controller + '-min:first');
        }


        setTimeout(function () {
            if (typeof callback === 'function')
            {
                callback(ret);
            }
            else {
                return ret;
            }
        }, 50);
    },
    closeApplication: function (controller, data, forceClose)
    {
        var isRootApp = false, self = this, winID = '';
        if (data != null && typeof data == 'object' && typeof data.settings == 'object')
        {
            winID = data.id;
            isRootApp = data.get('isRootApplication'); //(data.settings.Action == 'index' || data.settings.Action == null ? true : false);
        }

        $('.dock-' + controller + '-min', $('#dock-appmin')).stop(true).animate({
            opacity: '0'
        }, 250, function () {
            $(this).remove();
            self.minimizedItems = $('#dock-appmin .appicn').length;
            self.updateDockPos();
        });

        if (isRootApp) {

            $('#dock-inner').find('.dock-' + controller + '[windowid="' + winID + '"]:not(.static)').removeClass('active').stop(true).animate({
                opacity: '0'
            }, 250, function () {
                $(this).remove();
                self.activeItems = $('#dock-inner .appicn').length;
                self.updateDockPos();
            });

        }

        if (isRootApp || forceClose == true)
        {

            $('#dock-inner').find('.dock-' + controller + '[windowid="' + winID + '"]').removeClass('active');
        }
    },
    updateDockResize: function (event, ui)
    {
        var self = this, dock = $('#dock', $('#desktop'));
        if (this.options.position == 'center')
        {
            $('#dock-resizer,.dock-r,.dock-left,#dock-inner', dock).css({
                height: ui.size.height
            });

            // set proportional size for dock resizer            
            $('#dock-inner .appicn img').css({
                width: ui.size.height - 10,
                height: ui.size.height - 10
            });

            if (self.minimizedItems > 0)
            {
                $('#dock-appmin .appicn').css({
                    width: ui.size.height - 10,
                    height: ui.size.height - 10
                });
            }

            self.currentDockHeight = ui.size.height;
            self.updateDockPos();
        }
        else if (this.options.position == 'left' || this.options.position == 'right')
        {
            $('#dock-resizer,.dock-r,.dock-left', dock).css({
                width: ui.size.width
            });

            $('#dock-appmin,#dock-inner').css({
                width: ui.size.width,
                height: ''
            });

            // set proportional size for dock resizer
            $('#dock-inner .appicn img,#dock-appmin .appicn').css({
                width: ui.size.width - 10,
                height: ui.size.width - 10
            });

            self.currentDockHeight = ui.size.width;
            self.updateDockPos();
        }

    },
    updateDockPos: function (desktopSidePanelWidth)
    {
        var self = this, dock = $('#dock');


        if (this.options.position == 'center')
        {
            dock.find('#dock-inner,.container,#dock-resizer,.dock-r,.dock-left').css({
                height: self.currentDockHeight,
                width: ''
            });

            $('#dock-inner img').css({
                height: self.currentDockHeight - 10,
                width: self.currentDockHeight - 10
            });

            $('#dock-appmin').css({
                width: '',
                height: self.currentDockHeight
            });



            dock.find('#dock-inner').css({
                width: dock.find('#dock-inner').outerWidth()
            });
        }
        else if (this.options.position == 'left' || this.options.position == 'right')
        {
            dock.find('#dock-inner,.container,#dock-resizer,.dock-r,.dock-left').css({
                height: '',
                width: self.currentDockHeight
            });

            $('#dock-inner img').css({
                height: self.currentDockHeight - 10,
                width: self.currentDockHeight - 10
            });


            $('#dock-appmin').css({
                height: '',
                width: self.currentDockHeight
            });



            dock.find('#dock-inner').css({
                height: dock.find('#dock-inner').outerHeight()
            });





        }



        /*
         $('#dock .container').css({
         width: $('#dock-appmin').outerWidth() + $('#dock-inner').outerWidth() + $('#dock-resizer').outerWidth() + $('.dock-r', dock).outerWidth()
         });
         */
        self.dockSize = {
            height: self.currentDockHeight,
            width: $('#dock').outerWidth()
        };


        if (self.options.position == 'center')
        {
            dock.css({
                bottom: 0,
                left: $(window).width() / 2 - (self.dockSize.width / 2) - (desktopSidePanelWidth > 0 ? desktopSidePanelWidth : (Desktop.Sidepanel.panelWidth > 0 ? Desktop.Sidepanel.panelWidth : 0)), 'top': '', right: ''
            }).removeClass('right-pos').removeClass('left-pos');
        }
        else if (self.options.position == 'left')
        {
            dock.css({
                left: 0,
                top: ($(window).height() - $('#Taskbar').outerHeight(true)) / 2 - (dock.innerHeight() / 2)
            }).removeClass('right-pos').addClass('left-pos');
        }
        else if (self.options.position == 'right')
        {
            dock.css({
                right: 0,
                top: ($(window).height() - $('#Taskbar').outerHeight(true)) / 2 - (dock.innerHeight() / 2)
            }).removeClass('left-pos').addClass('right-pos');
        }
    },
    dockBubbles: function ()
    {

        var self = this;


        $('#dock').find('.appicn').each(function () {
            $this = $(this);

            $this.unbind('mouseleave.bubble').bind('mouseleave.bubble', function () {
                $('.dockBubbleContent', $('#dock')).remove();
            });


            $this.unbind('mouseover.bubble').bind('mouseover.bubble', function (e) {


                var isInMin = false;
                var appid = $(this).attr('app');
                e.preventDefault();



                if ($(this).hasClass('ui-sortable-helper'))
                {
                    $('.dockBubbleContent', $('#dock')).remove();

                    return;
                }



                if ($(e.target).parents('#dock-appmin:first').length || $(e.target).prop('id') == 'dock-appmin')
                {
                    isInMin = true;
                }


                if ($('#dockBubbleContent_' + appid, $('#dock' + (isInMin ? '-appmin' : '-inner'))).length == 0)
                {
                    var AppTitle = appid;

                    var dockIcon = $('.dock-' + appid, $('#dock' + (isInMin ? '-appmin' : '-inner')));




                    if (dockIcon.hasClass('ui-sortable-helper'))
                    {
                        $('.dockBubbleContent', $('#dock')).remove();
                        return;
                    }








                    if (!isInMin && $('.dock-' + appid, $('#dock')).attr('label') != '')
                    {
                        AppTitle = $('.dock-' + appid, $('#dock')).attr('label');
                    }
                    else if (isInMin && $('.dock-' + appid + '-min', $('#dock')).attr('label') != '')
                    {
                        AppTitle = dockIcon.attr('label');
                    }

                    var bubbleContent = $('<div>').attr({
                        'class': 'dockBubbleContent',
                        'id': 'dockBubbleContent_' + appid
                    }).html('<center></center>');

                    var bubble = $('<div>').addClass('dockBubble').html(AppTitle);
                    var bulleArrow = $('<span>').addClass('bubbleArrow').css('display', 'block');
                    bubbleContent.children('center').append(bubble);
                    bubbleContent.children('center').append(bulleArrow);

                    var icnsOffsetTop, icnsOffsetLeft, bulleLeft, bulleTop;




                    dockIcon.append(bubbleContent);

                    var bulleWidth = $('#dockBubbleContent_' + appid, $('#dock' + (isInMin ? '-appmin' : '-inner'))).outerWidth(true);
                    var bulleHeight = $('#dockBubbleContent_' + appid, $('#dock' + (isInMin ? '-appmin' : '-inner'))).outerHeight(true);


                    var icnContainer = $('#dock-inner'), _min = '';
                    if (isInMin)
                    {
                        icnContainer = $('#dock-appmin');
                        _min = '-min'
                    }

                    if (self.options.position == 'center')
                    {
                        icnsOffsetTop = $('.dock-' + appid + _min, icnContainer).position().top;
                        icnsOffsetLeft = $('.dock-' + appid + _min, icnContainer).position().left;
                        bulleLeft = $('.dock-' + appid + ' img', icnContainer).parent().width() / 2 - (bulleWidth / 2);
                        bulleTop = icnsOffsetTop - bulleHeight - 20;
                    }
                    else if (self.options.position == 'left')
                    {
                        icnsOffsetTop = $('.dock-' + appid + _min, icnContainer).height() / 2;
                        icnsOffsetLeft = $('.dock-' + appid + _min, icnContainer).position().left;
                        bulleLeft = icnsOffsetLeft + $('.dock-' + appid + _min + ' img', icnContainer).width() + 20;
                        bulleTop = icnsOffsetTop - (bulleHeight / 2);
                    }
                    else if (self.options.position == 'right')
                    {
                        icnsOffsetTop = $('.dock-' + appid + _min, icnContainer).height() / 2;
                        icnsOffsetLeft = $('.dock-' + appid + _min, icnContainer).position().left;
                        bulleLeft = icnsOffsetLeft - bulleWidth - 20;
                        bulleTop = icnsOffsetTop - (bulleHeight / 2);
                    }

                    bubbleContent.css({
                        'top': bulleTop,
                        'left': bulleLeft
                    }).show();

                }


            });

        });


    },
    updaterTimer: null,
    updaterOn: false,
    getDockChangeRequestOptions: function ()
    {
        var allData = {};
        allData.dockItems = [];
        $('.appicn', $('#dock-inner')).each(function () {
            if ($(this).hasClass('static') && $(this).data('windowData'))
            {
                allData.dockItems.push($(this).data('windowData'));
            }
        });
        if (allData.dockItems.length == 0)
        {
            allData.dockItems = false;
        }

        allData.activeItems = [];
        $('.appicn.active', $('#dock-inner')).each(function () {
            if ($(this).hasClass('static') && $(this).data('windowData'))
            {
                allData.activeItems.push($(this).data('windowData'));
            }
        });
        if (allData.activeItems.length == 0)
        {
            allData.activeItems = false;
        }

        allData.saveDock = true;
        if (this.options.position == 'center')
        {
            allData.dockHeight = $('#dock img:first').height() + 10;
        }
        else
        {
            allData.dockHeight = $('#dock img:first').width() + 10;
        }
        return allData;
    },
    updateDatabase: function (activeApps)
    {
        var self = this;
        if (this.updaterOn || this.disableDatabaseUpdate)
        {
            self.updaterTimer = setTimeout(function () {
                self.updateDatabase(activeApps);
            }, 300);
        }
        else
        {
            this.updaterOn = true;
            clearTimeout(this.updaterTimer);
            var data = this.getDockChangeRequestOptions();

            if (data.dockHeight != this.currentDockHeight || data.dockItems !== false) {
                data.adm = 'dashboard';
                $.post(Tools.prepareAjaxUrl('admin.php'), data, function () {
                    self.updaterOn = false;
                }, 'json');
            }
        }
    }




};