
var resizeCallback = (function () {

    'use strict';
    var timer = 0;
    return function (callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
})(window, document);

// Custom sorting plugin
(function ($) {
    $.fn.sorted = function (customOptions) {
        var options = {
            reversed: false,
            by: function (a) {
                return a.text();
            }
        };
        $.extend(options, customOptions);
        $data = $(this);
        var arr = $data.get();
        arr.sort(function (a, b) {
            var valA = options.by($(a));
            var valB = options.by($(b));
            if (options.reversed) {
                return (valA < valB) ? 1 : (valA > valB) ? -1 : 0;
            } else {
                return (valA < valB) ? -1 : (valA > valB) ? 1 : 0;
            }
        });
        return $(arr);
    };
})(jQuery);

// patch
var Core = {
    addShortcutHelp: function() {
        "use strict";

    },
    BootstrapInit: function() {
        "use strict";

    }
};

var Desktop = (function () {

    return {
        isWindowSkin: true,
        loaded: false,
        baseURL: '',
        switchFullscreen: false,
        timerID: null,
        timerBlinking: null,
        timerRunning: false,
        loginClockInterval: null,
        zIndex: 10,
        scrollerHeight: 5,
        scrollIncrement: 10,
        basicCMSData: {}, // will store the config and session
        // default icon size
        iconsize: {
            iconWidth: 36,
            subIconWidth: 14
        },
        iconGutterSize: 80,
        iconLabelPos: 'bottom',
        iconSort: 'none',
        showObjectInfo: false,
        // 
        iconIdNum: 1,
        folderIdNum: 1,
        // store all the app (controller) infos
        appCache: [],
        /* Workspace */
        currentSpace: 1,
        spacesActualLength: 1,
        maxSpaces: 4,
        SessionID: null,
        isSeemode: false,
        defaults: {
            isSeemode: false,
            EnableBootScreen: false,
            DesktopIconWidth: 36,
            DesktopIconHeight: 36,
            loadWithAjax: true,
            allowAjaxCache: false,
            WindowID: null,
            StartPosTop: 0,
            StartPosLeft: 0,
            StartWindowOuterWidth: 10,
            StartWindowOuterHeight: 10,
            EndWindowOuterWidth: 800,
            EndWindowOuterHeight: 400,
            WindowContent: null,
            WindowURL: null,
            WindowTitle: 'Windows for jQuery',
            WindowDesktopIconFile: 'icons/default.png',
            WindowStatus: 'window',
            WindowPositionTop: 'center',
            WindowPositionLeft: 'center',
            WindowOuterWidth: 800,
            WindowOuterHeight: 500,
            WindowMinWidth: 100,
            WindowMinHeight: 200,
            WindowDesktopIcon: true,
            WindowResizable: true,
            WindowMaximize: true,
            WindowMinimize: true,
            WindowClosable: true,
            WindowDraggable: true,
            WindowAnimationSpeed: 0,
            WindowAnimation: '',
            WindowTransparentAnimationSpeed: 400,
            UseWindowIcon: true,
            Skin: null,
            ShowTaskbar: true,
            ShowTaskbarClock: true,
            TaskbarClockSeperatorBlinking: false,
            TaskbarIsTop: false,
            ShowTaskbarLabel: false


        },
        settings: {},
        windowDefaultSettings: {
            loadWithAjax: true,
            allowAjaxCache: false,
            WindowMaximize: true,
            WindowMinimize: true,
            WindowResizable: true,
            ShowDesktopIcon: false,
            WindowID: null,
            WindowToolbar: false,
            StartPosTop: 0,
            StartPosLeft: 0,
            WindowTransparentAnimationSpeed: 400,
            WindowAnimationSpeed: 0,
            StartWindowOuterWidth: 0,
            StartWindowOuterHeight: 0,
            EndWindowOuterWidth: 800,
            EndWindowOuterHeight: 400,
            minWidth: 400,
            minHeight: 200,
            WindowMaxWidth: 800,
            WindowMaxHeight: 400,
            WindowStatus: 'window',
            WindowPositionTop: 'center',
            WindowPositionLeft: 'center',
            MinSpacing: 10,
            WindowType: 'Link',
            WindowOuterHeight: 0,
            WindowOuterWidth: 0,
            WindowTitle: '',
            UseWindowIcon: true,
            NewWindowURL: '',
            WindowIcon: '',
            WindowContent: null,
            Skin: null,
            ShowTaskbarLabel: true,
            AddExtraClass: null
        },
        windowSettings: {},
        ajaxData: null,
        credits: function () {
            $('.credit-link', $('#' + Win.windowID)).click(function (e) {
                window.open($(this).attr('href'));
                e.preventDefault();
                return false;
            });

            $('.logo', $('#' + Win.windowID)).each(function () {
                $(this).parents('.credit:first').show();
                var w = $(this).width();
                var h = $(this).height();
                $(this).parents('.credit:first').hide();
                if (w >= 120) {
                    $(this).width(120);
                    var height = Math.ceil(h * (120 / w));
                    $(this).height(height);
                    h = height;
                }
            });

            setTimeout(function () {
                $('.credit', $('#' + Win.windowID)).show();
                $(".items", $('#' + Win.windowID)).height(($(".items", $('#' + Win.windowID)).length * 200));
                $(".credit-scrollable", $('#' + Win.windowID)).scrollable({
                    steps: 1,
                    vertical: true,
                    autoplay: true,
                    circular: true,
                    speed: 800,
                    easing: 'easeOutBounce'
                }).autoscroll(2000);

            }, 500);
        },
        fullscreenDesktop: function () {

            var el = document.getElementById("desktop");

            if (!document.mozFullScreen && !document.webkitFullScreen) {
                if (el.mozRequestFullScreen) {
                    el.mozRequestFullScreen();
                } else {
                    el.webkitRequestFullScreen(el.ALLOW_KEYBOARD_INPUT);
                }
            } else {
                if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else {
                    document.webkitCancelFullScreen();
                }
            }
        },
        /**
         * 
         * @param string key
         * @param mixed value
         * @returns Desktop
         */
        set: function (key, value) {
            this.settings[key] = value;
            return this;
        },
        /**
         * 
         * @param string key
         * @returns mixed
         */
        get: function (key) {
            return (typeof this.settings[key] !== undefined ? this.settings[key] : null);
        },
        /**
         * 
         * @param object options
         * @returns 
         */
        init: function (options) {
            var settings = $.extend(Desktop.defaults, options);
            settings.isSeemode = this.isSeemode = (isSeemodePopup ? true : false);

            /**
             * 
             */
            $.ajaxSetup({
                cache: false,
                async: true
            });


            if (settings.isSeemode) {
                settings.EnableBootScreen = false;
            }

            Config.init(options);

            setTimeout(function () {

                $('#missioncontrol-bg,#dashboard-bg,#desktop,#bootscreen,#loginscreen,#desktop-bg,#desktop-bg-container').css({
                    'position': 'absolute',
                    'top': '0px',
                    'left': '0px',
                    'width': window.innerWidth + 'px',
                    'height': window.innerHeight + 'px'
                });

                $('body').css('width', window.innerWidth);
                $('body').css('height', window.innerHeight);
            }, 10);


            $(document).ajaxStop(function () {
                document.body.style.cursor = '';
            });

            $(document).ajaxSend(function () {
                document.body.style.cursor = '';

            });

            $(document).ajaxStart(function () {
                document.body.style.cursor = '';

                // little progress patch
                $('.masking').show();/*
                 if ( $('.masking').length ) {
                 var i = 0;
                 while (i<10000) {
                 ++i;
                 }
                 }*/
            });

            $(document).ajaxError(function () {
                $.pagemask.hide();
                document.body.style.cursor = '';
                Tools.html5Audio('html/audio/error');
            });

            if (typeof settings.enableDebug !== 'undefined') {
                Debug.enableDebug = settings.enableDebug;
            }

            if (typeof settings.Skin === 'undefined') {
                settings.Skin = this.defaults.Skin;
            }

            if (typeof settings.loadWithAjax === 'undefined') {
                settings.loadWithAjax = this.defaults.loadWithAjax;
            }

            this.baseURL = settings.baseURL;


            if (($('body').data('DcmsWindows')) === null) {
                $('body').data('DcmsWindows', new Array());
            }

            if (settings.EnableBootScreen) {
                //$('#boot-console').css({left: $('body').width()/2-$('#boot-console').width()/2 });
                $('#bootscreen .logo').unbind().click(function () {
                    $('#boot-console').is(':visible') ? $('#boot-console').hide() : $('#boot-console').show();
                });



                $('#desktop,#loginscreen').hide();
                $('#bootscreen').show();
                this.settings = settings;
                this.showBootup(settings);

            } else {
                $('#bootscreen .logo').unbind();
                $('#desktop,#bootscreen').hide();
                $('#loginscreen').show();
                this.settings = settings;
                this.runAfterBoot();
            }

            return this;

        },
        bindDesktopShortcuts: function () {
            var self = Desktop, act;
            $(document).unbind('keydown.desktopShortcuts');


            $(document).on('keydown.desktopShortcuts', function (e) {
                if (window.focusedAceEdit !== null) {
                    return;
                }

                var ctrl = e.ctrlKey, meta = e.metaKey;

                if (!ctrl && !meta) {
                    return;
                }

                // F5 + CTRL refresh active Window
                if (e.keyCode === 116 && ctrl) {
                    act = self.getActiveWindow();
                    if (act && act.length === 1) {
                        act.data('WindowManager').ReloadWindow();
                        return;
                    }

                    //    console.log('ctrl + F5');
                }

                // F1 + CTRL/Meta Help active Window
                if (e.keyCode === 112 && (ctrl || meta)) {
                    act = $('#App-Menu').find('ul:first .root-item:last').find('li:first a');
                    if (act && act.length === 1) {
                        act.trigger('click');
                        return;
                    }
                }

                // Q + CTRL close active Window
                if (e.keyCode === 81 && ctrl) {
                    act = $('#App-Menu').find('ul:first .root-item:first').find('li:last a');
                    if (act && act.length === 1) {
                        act.trigger('click');
                        return;
                    }
                }


                // A + CTRL About active Window
                if (e.keyCode === 65 && ctrl) {

                    act = $('#App-Menu').find('ul:first .root-item:first').find('li:first a');
                    if (act && act.length === 1) {
                        act.trigger('click');
                        return;
                    }
                }

                // C + CTRL Console
                if (e.keyCode === 67 && ctrl) {

                    act = $('#console');
                    if (act && act.length === 1) {
                        act.trigger('click');
                        return;
                    }
                }

                // S + CTRL Sidebar
                if (e.keyCode === 83 && ctrl) {

                    act = $('#TaskbarShowDesktop');
                    if (act && act.length === 1) {
                        act.trigger('click');
                        return;
                    }
                }

                return;
            });
        },
        /**
         * 
         * @returns {undefined}
         */
        initWindowResize: function () {
            var to, resize1 = $('#missioncontrol-bg').get(0),
                    resize2 = $('#dashboard-bg').get(0),
                    resize3 = $('#bootscreen').get(0),
                    resize4 = $('#loginscreen').get(0),
                    _desktop1 = $('#desktop').get(0),
                    _desktop2 = $('#desktop-bg-container').get(0),
                    _desktop3 = $('#desktop-bg').get(0),
                    TaskbarButtonsWrapper = $('#TaskbarButtonsWrapper'),
                    loginscreen = $('#loginscreen'),
                    _body = $('body').get(0),
                    Tasks = $('#Tasks'),
                    TasksCore = $('#Tasks-Core'),
                    scrollpos = $('#scroll-pos'),
                    TaskbarShowDesktop = $('#TaskbarShowDesktop'),
                    StartMenuButton = $('#Start-Menu-Button'),
                    resizeTimer, t, t2, globaltimer;

            $(window).resize(function (e) {
                if (e.target != window) {
                    return;
                }


                clearTimeout(to);
                clearTimeout(globaltimer);

                if (Desktop.Sidepanel.resizePanel && Desktop.Sidepanel.isVisible) {
                    return;
                }

                var wwidth = $(this).width(), wh = $(this).height();


                resize1.style.width = wwidth;
                resize1.style.height = wh;
                resize2.style.width = wwidth;
                resize2.style.height = wh;
                resize3.style.width = wwidth;
                resize3.style.height = wh;
                resize4.style.width = wwidth;
                resize4.style.height = wh;



                _body.style.width = wwidth;
                _body.style.height = wh;
                _desktop1.style.width = wwidth;
                _desktop1.style.height = wh;
                _desktop2.style.width = wwidth;
                _desktop2.style.height = wh;
                _desktop3.style.width = wwidth;
                _desktop3.style.height = wh;

                var self = this;

                globaltimer = setTimeout(function () {
                    resize1.style.width = wwidth;
                    resize1.style.height = wh;
                    resize2.style.width = wwidth;
                    resize2.style.height = wh;
                    resize3.style.width = wwidth;
                    resize3.style.height = wh;
                    resize4.style.width = wwidth;
                    resize4.style.height = wh;



                    _body.style.width = wwidth;
                    _body.style.height = wh;
                    _desktop1.style.width = wwidth;
                    _desktop1.style.height = wh;
                    _desktop2.style.width = wwidth;
                    _desktop2.style.height = wh;
                    _desktop3.style.width = wwidth;
                    _desktop3.style.height = wh;
                    Dock.updateDockPos();

                    var AppMenu = $('#App-Menu');
                    TaskbarButtonsWrapper.css({
                        'width':
                                ($(self).width() -
                                        Tasks.outerWidth(true) -
                                        TasksCore.outerWidth(true) -
                                        scrollpos.outerWidth(true)
                                        - TaskbarShowDesktop.outerWidth(true)
                                        - StartMenuButton.outerWidth(true) - (AppMenu.length ? AppMenu.outerWidth(true) : 0))
                                + 'px'
                    });

                    if (!loginscreen.is(':visible')) {


                        $('#desktop').find('.isWindowContainer').each(function () {

                            var offset = $(this).offset();
                            var w = $(this).width(), h = $(this).height();

                            var setleft = ((offset.left + w) - wwidth) + 10;
                            var settop = ((offset.top + h) - wh) + 10;

                            if (wwidth > w && offset.left + w > wwidth) {
                                if (offset.left - setleft > 0) {
                                    $(this).animate({
                                        left: '-=' + setleft
                                    }, 400);
                                }
                                else {
                                    $(this).animate({
                                        left: 0
                                    }, 400);
                                }
                            }

                            if (h < wh && offset.top + h > wh) {
                                if (offset.top - settop > 20) {
                                    $(this).animate({
                                        top: '-=' + settop
                                    }, 400);
                                }
                                else {
                                    $(this).animate({
                                        top: 20
                                    }, 400);
                                }
                            }

                        });

                        // var $sortedData = $('#DesktopIcons').find('>*:not(.sub-container)');
                        // update the icon positions
                        Application.animateByRaster(400, 800);



                        Launchpad.updateSize();
                        Dashboard.updateSize();
                        Desktop.Sidepanel.updatePanelSize(true);
                        Desktop.Sidepanel.updatePanelSizeStop();
                    }
                    else {
                        to = setTimeout(function (e) {

                            loginscreen.find('#login-logo,.form-content').each(function () {
                                var FinalWidth = ($(window).innerWidth() / 2) - (parseInt($(this).outerWidth(true)) / 2);
                                var FinalHeight = (($(window).innerHeight() / 2) - ((parseInt($(this).outerHeight()) / 2) - 85));
                                var top = $(this).position().top;

                                if ($(this).attr('id') == 'login-logo') {
                                    FinalHeight = FinalHeight - $(this).outerHeight() - 45;
                                }
                                $(this).css({
                                    top: top,
                                    left: $(this).position().left
                                }).stop(true).animate({
                                    top: FinalHeight + 'px',
                                    left: FinalWidth + 'px'
                                }, 300);
                            });

                        }, 100);
                    }

                }, 500);
            });
        },
        /**
         * 
         * @param {type} settings
         * @returns {undefined}
         */
        showBootup: function (settings)
        {
            var self = this;
            self.settings = settings;


            $('#boot-console pre').empty().append('Load Resources' + "\n");

            self.loadBasicConfig(function (_data)
            {
                //self.runAfterBoot();
                // console.log([_data]);
                if (_data.bootImages)
                {
                    var loaded = 0, max = _data.bootImages.length;
                    var images = _data.bootImages;
                    var baseHref = window.location.href;
                    var baseURLarr = baseHref.split('/');//split href at / to make array
                    baseURLarr.pop();//remove file path from baseURL array
                    var baseURL = baseURLarr.join('/');//create base url for the images in this sheet (css file's dir)
                    if (baseURL != "")
                        baseURL += '/'; //tack on a / if needed

                    var allImgs = [];

                    for (var i = 0; i < max; ++i)
                    {
                        var img = images[i].src.split('|');
                        var url = img[0];
                        var $img = $('<img/>')
                                .attr('src', (url[0] == '/' || url.match('http://')) ? url : baseURL + url)
                                .attr('class', 'preloaded')
                                .attr('width', '120')
                                .attr('height', '120').css({visibility: 'hidden'});

                        $img.on('load', function () {
                            $('#boot-console pre').append('loaded: ' + $(this).attr('src') + "\n");
                            $('#boot-console').animate({
                                scrollTop: $('#boot-console').get(0).scrollHeight
                            }, 0);
                            loaded++;
                            if (loaded >= max)
                            {
                                setTimeout(function () {
                                    self.runAfterBoot();
                                }, 100);
                            }
                        }).on('onerror', function () {
                            $('#boot-console pre').append('faild loaded: ' + $(this).attr('src') + "\n");
                            $('#boot-console').animate({
                                scrollTop: $('#boot-console').get(0).scrollHeight
                            }, 0);
                            loaded++;
                            if (loaded >= max)
                            {
                                setTimeout(function () {
                                    self.runAfterBoot();
                                }, 100);
                            }
                        }).appendTo($('body'));


                        /*
                         allImgs[i] = new Image(); //new img obj                        
                         allImgs[i].src = (url[0] == '/' || url.match('http://')) ? url : baseURL + url;
                         allImgs[i].className = 'preloaded';
                         allImgs[i].width = 1;
                         allImgs[i].height = 1;
                         
                         if ((i + 1) >= max)
                         {
                         allImgs[i].onload = function () {
                         loaded++;
                         
                         $('#boot-console pre').append('loaded: ' + $(this).attr('src') + "\n");
                         
                         if (loaded >= max)
                         {
                         self.runAfterBoot();
                         }
                         
                         };
                         
                         allImgs[i].onerror = function ()
                         {
                         $('#boot-console pre').append('faild loaded: ' + $(this).attr('src') + "\n");
                         loaded++;
                         if (loaded >= max)
                         {
                         console.log('Boot Loader Image: ' + this.src + ' not exists');
                         
                         self.runAfterBoot();
                         }
                         };
                         
                         }
                         $('body').append(allImgs[i]);
                         //$(allImgs[i]).css({width: 1, height: 1, visible: 'hidden'}).appendTo($('body'));
                         
                         if (i + 1 >= max && $.browser.msie)
                         {
                         setTimeout(function() {
                         $('img.preloaded').remove();
                         
                         setTimeout(function() {
                         self.runAfterBoot();
                         }, 100);
                         }, 50);
                         }
                         */
                    }
                }
                else
                {
                    // $('img.preloaded').remove();
                    setTimeout(function () {
                        self.runAfterBoot();
                    }, 100);
                }
            });
        },
        /**
         *  Animate the boot screen to login screen
         */
        animateToLogin: function ()
        {
            $('body').removeClass('boot').addClass('auth');

            $('#desktop,#desktop-bg,#Taskbar,#Document-Settings,#auth-clock,#dock').css({
                'opacity': "0"
            }).hide(0);

            $('#loginscreen').css('left', '');

            $('.form-content', $('#loginscreen')).css({
                'opacity': "0",
                'left': (window.innerWidth / 2) - $('.form-content', $('#loginscreen')).outerWidth() / 2,
                'top': window.innerHeight - $('.form-content', $('#loginscreen')).outerHeight()
            });

            $('#loginscreen').find('.charge').removeClass('charge');

            $('#login-logo').css({
                'left': (window.innerWidth / 2) - $('#login-logo').outerWidth() / 2,
                'top': window.innerHeight - $('#login-logo').outerHeight() - $('.form-content', $('#loginscreen')).outerHeight(),
                'opacity': "0"
            });

            var self = this;
            var t = this.Taskbar.getClock();
            var clock = $('<div>').attr('id', 'clock').append(t.time);
            $('#auth-clock').empty().append(clock);


            this.loginClockInterval = setInterval(function () {
                var t = self.Taskbar.getClock();
                $('#clock', $('#auth-clock')).html(t.time)
            }, 1000);

            // do blinking the time seperator?
            if (this.settings.TaskbarClockSeperatorBlinking)
            {
                this.Taskbar._clockSecoundBlinking();
            }

            $('#loginscreen,#auth-clock').show().animate({
                opacity: '1'
            }, {
                duration: 1000
            });

            var logoTop = ((window.innerHeight / 2) - (parseInt($('#login-logo').show().outerHeight(), 0) / 2)) - 120;




            $('#login-logo').animate({
                opacity: 1,
                top: logoTop
            }, {
                duration: 500,
                complete: function () {

                    $('#loginscreen').find('.form-content').show().animate({
                        opacity: 1,
                        top: (logoTop + parseInt($('#login-logo').outerHeight(), 0) + 20)
                    }, {
                        duration: 300,
                        complete: function () {
                            self.initWindowResize();
                            Desktop.loaded = true;
                        }
                    });

                }
            });

        },
        /**
         *  Animate the boot screen or login screen to the desktop
         */
        animateToDesktop: function ()
        {
            var self = this;
            $('body').removeClass('boot').addClass('auth');
            if (this.loginClockInterval)
            {
                clearInterval(this.loginClockInterval);
            }

            if (this.settings.ShowTaskbar == true)
            {
                $('#Taskbar').remove();
                this.Taskbar.createTaskBar();
                DesktopMenu.createCoreMenu();
                // Desktop.Taskbar.Menu.createStartMenu();
            }

            if (this.settings.isSeemode)
            {
                $('body').removeClass('auth').removeClass('boot');
                $('#loginscreen').hide();
                $('#desktop,#desktop-bg,#desktop-bg-container').css({width: $(window).width(), height: $(window).height(), opacity: '1', left: 0}).show();

                $('#Taskbar').css({top: 0, opacity: '1'}).show();

                $('#Document-Settings').animate({
                    left: $('.document-settings-toggler', $('#Document-Settings')).width() - $('#Document-Settings').outerWidth()
                }, {
                    duration: 700,
                    quoue: true,
                    complete: function () {
                        $('#Document-Settings').stop().css({
                            left: $('.document-settings-toggler', $('#Document-Settings')).width() - $('#Document-Settings').outerWidth(),
                            opacity: '1'
                        }).show();
                    }
                });

                self.showDesktop();
                Desktop.loaded = true;
                return;
            }

            var x = false;
            if (Modernizr && Modernizr.csstransitions && x == true) {

                $('#desktop-container').removeClass('exec').addClass('fullwidth');
                $('#desktop,#desktop-bg,#desktop-bg-container').css({width: $(window).width(), height: $(window).height(), opacity: '1'}).show();


                setTimeout(function () {
                    $('#desktop-container').addClass('exec');


                    setTimeout(function () {
                        $('body').removeClass('auth').removeClass('boot');
                        $('#loginscreen').hide();
                        $('#desktop,#desktop-bg,#Taskbar,#dock').show();


                        $('#Taskbar').css({
                            top: 0 - $('#Taskbar').outerHeight(),
                            opacity: '1'
                        }).stop().show().animate({
                            top: 0
                        }, {
                            duration: 700,
                            quoue: true,
                            complete: function () {
                                $('body').get(0).className = ''; //.removeClass('boot').removeClass('auth');
                                self.showDesktop();
                                Desktop.loaded = true;
                            }
                        });


                        $('#Document-Settings').animate({
                            left: $('.document-settings-toggler', $('#Document-Settings')).width() - $('#Document-Settings').outerWidth()
                        }, {
                            duration: 700,
                            quoue: true,
                            complete: function () {
                                $('#Document-Settings').stop().css({
                                    left: $('.document-settings-toggler', $('#Document-Settings')).width() - $('#Document-Settings').outerWidth(),
                                    opacity: '1'
                                }).show();
                            }
                        });
                    }, 1100);
                }, 100);


            }
            else
            {
                $('#desktop,#desktop-bg,#Taskbar,#Document-Settings,#auth-clock').css({
                    'opacity': "0"
                }).hide(0);

                $('#desktop,#desktop-bg,#desktop-bg-container').css({
                    opacity: '1',
                    width: 0,
                    left: 0 - $(window).width()
                });

                $('#desktop,#desktop-bg,#desktop-bg-container').stop(true).show();
                $('#desktop,#desktop-bg,#desktop-bg-container').animate({
                    opacity: '1',
                    width: $(window).width(),
                    left: 0
                }, {
                    duration: 1000,
                    complete: function () {

                        $('body').removeClass('auth').removeClass('boot');

                        $('#loginscreen').hide();
                        $('#desktop,#desktop-bg,#Taskbar,#dock').show();


                        $('#Taskbar').css({
                            top: 0 - $('#Taskbar').outerHeight(),
                            opacity: '1'
                        }).stop().show().animate({
                            top: 0
                        }, {
                            duration: 700,
                            quoue: true,
                            complete: function () {
                                $('body').get(0).className = ''; //.removeClass('boot').removeClass('auth');
                                self.showDesktop();
                                Desktop.loaded = true;
                            }
                        });


                        $('#Document-Settings').animate({
                            left: $('.document-settings-toggler', $('#Document-Settings')).width() - $('#Document-Settings').outerWidth()
                        }, {
                            duration: 700,
                            quoue: true,
                            complete: function () {
                                $('#Document-Settings').stop().css({
                                    left: $('.document-settings-toggler', $('#Document-Settings')).width() - $('#Document-Settings').outerWidth(),
                                    opacity: '1'
                                }).show();
                            }
                        });



                    }
                });

            }

        },
        /**
         * 
         * @param {type} forceReboot
         * @returns {undefined}
         */
        runAfterBoot: function (forceReboot)
        {
            jQuery.fx.off = false;
            var self = this;

            // $('img.preloaded').remove();
            $('#desktop').destroyContextMenu();


            if (typeof forceReboot != 'undefined' && forceReboot === true)
            {
                clearInterval(self.loginClockInterval);
                self.SessionID = null;
                self.basicCMSData = {};
                $('#Taskbar,#task-cal,#userMenu').remove();
                $("#DesktopIcons").empty();
                $('#desktop,#desktop-bg,#bootscreen,#auth-clock').hide();
                $('#loginscreen').css({
                    left: 0
                }).show();

                $('form', $('#loginscreen')).find('p').remove();
                $('#auth-submit').removeClass('charge').addClass('submit');
                $('#userMenu').find('.active').removeClass('active');
            }

            self.loadBasicConfig(function (_data)
            {

                if (typeof _data != 'undefined' && typeof _data.success != 'undefined' && _data.success != true && typeof _data.msg == 'string' && _data.msg != '')
                {
                    $('body').removeClass('boot').addClass('fail');
                    $('#browser-error').hide();

                    var msg = _data.msg;

                    $('.custom-msg', $('#fail')).append(msg).show();
                    $('#loginscreen').hide();
                    $('#fail,#custom-error').show();
                    return;
                }


                if (typeof _data.sid == 'string')
                {
                    self.SessionID = _data.sid;
                }


                self.basicCMSData = _data;

                Config.set(self.basicCMSData.config);


                var baseUrl = Config.get('portalurl', '');
                Config.set('SSL_portalurl', baseUrl.replace(/https?:\/\//, 'https://'));
                Config.set('SSL_MODE', false);

                if (document.location.href.match(/https:\/\//i))
                {
                    Config.set('SSL_MODE', true);
                }

                Desktop.settings = $.extend({}, Desktop.settings, (self.basicCMSData || {}));

                delete(Desktop.settings.config);
                delete(self.basicCMSData.config);

                if (typeof self.basicCMSData.userdata == 'undefined' || typeof self.basicCMSData.userdata.userid == 'undefined' || forceReboot)
                {
                    self.loaded = false;

                    $('#bootscreen').hide(0);
                    $('body').removeClass('boot').addClass('auth');

                    Personal.reset();

                    self.animateToLogin();
                }
                else if (typeof self.basicCMSData.userdata != 'undefined' && typeof self.basicCMSData.userdata.userid != 'undefined' && self.basicCMSData.userdata.userid > 0)
                {
                    self.loaded = false;



                    $('#bootscreen').hide(0);
                    $('body').removeClass('boot').addClass('');

                    self.settings = $.extend({}, self.settings, self.basicCMSData || {});
                    Config.set('UserName', self.basicCMSData.userdata.username);

                    // init personal settings
                    Personal.init(self.basicCMSData.personalsettings);

                    // init desktop background
                    self.changeDesktopBackground();

                    delete(self.basicCMSData.personalsettings);


                    setTimeout(function () {
                        self.animateToDesktop();
                        if (!seeMode) {
                            Tools.html5Audio('html/audio/startup');
                        }
                    }, 200);
                }


                $('#clock', $('#loginscreen')).remove();

                // 
                $('input', $('#loginscreen')).unbind('keyup.loginscreeninput').bind('keyup.loginscreeninput', function (e) {
                    e.preventDefault();

                    if (e.keyCode == 13)
                    {
                        if ($(e.target).attr('id') == 'logusername')
                        {
                            $(e.target).next('input').focus();
                        }
                        else
                        {
                            self.Auth.doAuth();
                        }
                    }
                });

                $('input#auth-submit', $('#loginscreen')).unbind('click').bind('click', function (e)
                {
                    self.Auth.doAuth();
                });

                $('input', $('#loginscreen')).unbind('focus').bind('focus', function (e)
                {
                    $('#login-error').remove();
                });

            });
        },
        showDesktop: function (callback)
        {
            var self = this, settings = this.settings;

            $('#login-screen').hide();
            SidePanel.hide();

            if (!settings.isSeemode)
            {
                Desktop.Icons.initDesktopIcons();
            }


            // now init the Dock
            Dock.init();


            if (!settings.isSeemode)
            {
                // now init the Dashboard
                Dashboard.init();

                MissionControl.init();
                //
                DesktopConsole.init();
            }

            // init the desktop sidepanel
            Desktop.Sidepanel.init();


            this.initWindowResize();

            // init the launchpad
            if (typeof Launchpad == 'object' && !settings.isSeemode)
            {
                Launchpad.init();
            }

            if (settings.isSeemode)
            {
                $('#dock,#gui-console,#dcmsFav,#console,#task-cal').hide();
            }



            if (!$('#desktop-context-menu').length)
            {
                $('#desktop').append(Desktop.Templates.DesktopContextmenu);
            }


            $('#desktop-context-menu').hide();

            Desktop.loaded = true;
            self.loaded = true;


            $('#desktop').contextMenu({
                menu: 'desktop-context-menu',
                onBeforeShow: function (e, menuObj, callbackContext)
                {
                    $('ul.contextmenu').hide();
                    $('#desktop').find('.current-context-icon').removeClass('current-context-icon');




                    // pure desktop
                    if (!$(e.target).parents('.isWindowContainer').length &&
                            !$(e.target).parents('.DesktopIconContainer-Folder').length &&
                            !$(e.target).parents('.DesktopIconContainer').length &&
                            !$(e.target).parents('.sub-container').length
                            )
                    {

                        $('#desktop-context-menu').hideContextItems('#delete-desktopicon');
                        $('#desktop-context-menu').showContextItems('#changedesktopbackground');
                        $('#desktop-context-menu').showContextItems('#desktop-view-options');
                        if ($('#launchpadMain').find('#LaunchPadCase_sys').length) {
                            $('#desktop-context-menu').showContextItems('#systeminfos');
                        }
                        $('#desktop-context-menu').hideContextItems('#appinfos');
                        callbackContext();
                    }

                    if (!$(e.target).parents('.isWindowContainer').length &&
                            !$(e.target).parents('.DesktopIconContainer-Folder').length &&
                            ($(e.target).parents('.DesktopIconContainer').length == 1 && !$(e.target).parents('.sub-container').length) // is icon and not in a icon folder
                            )
                    {
                        $('#desktop-context-menu').hideContextItems('#changedesktopbackground');
                        $('#desktop-context-menu').hideContextItems('#systeminfos');
                        $('#desktop-context-menu').hideContextItems('#desktop-view-options');
                        $('#desktop-context-menu').showContextItems('#appinfos');
                        $('#desktop-context-menu').showContextItems('#delete-desktopicon');


                        $(e.target).parents('.DesktopIconContainer').addClass('current-context-icon');
                        callbackContext();
                    }


                    if (!$(e.target).parents('.isWindowContainer').length && $(e.target).parents('.sub-container').length == 1) {
                        $('#desktop-context-menu').hideContextItems('#changedesktopbackground');
                        $('#desktop-context-menu').hideContextItems('#systeminfos');
                        $('#desktop-context-menu').showContextItems('#desktop-view-options');
                        $('#desktop-context-menu').showContextItems('#appinfos');
                        $('#desktop-context-menu').showContextItems('#delete-desktopicon');


                        $(e.target).parents('.DesktopIconContainer').addClass('current-context-icon');
                        callbackContext();
                    }



                }
            },
            function (action, el, pos, e) {

                e.preventDefault();

                switch (action)
                {
                    case 'desktop-view-options':
                        Application.showDesktopOptions();
                        break;
                    case 'systeminfos':
                        if ($('#launchpadMain').find('#LaunchPadCase_sys').length) {
                            $('#launchpadMain').find('#LaunchPadCase_sys').trigger('click');
                        }
                        break;

                    case 'appinfos':
                        if ($('#desktop').find('.current-context-icon').length == 1) {
                            var itemData = $('#desktop').find('.current-context-icon').data('itemData');
                            if (itemData) {
                                Application.aboutApp(null, $('#desktop').find('.current-context-icon').attr('rel'), '', itemData);
                            }
                        }
                        break;


                    case 'delete-desktopicon':

                        $('#trashDesktopItem', $('#desktop').find('.current-context-icon')).trigger('click');

                        break;

                    case 'changedesktopbackground':

                        $('#userMenu').find('.preferences').trigger('click');

                        break;


                }

                /*
                 alert(
                 'Action: ' + action + '\n\n' +
                 'Element ID: ' + $(el).attr('id') + '\n\n' +
                 'X: ' + pos.x + '  Y: ' + pos.y + ' (relative to element)\n\n' +
                 'X: ' + pos.docX + '  Y: ' + pos.docY + ' (relative to document)'
                 );
                 */
            });













            $('#fullscreenContainer').click(function () {
                if (self.switchFullscreen)
                {
                    var _element = document.getElementById("fullscreenContainer");

                    if (_element.requestFullScreen) {

                        if (!document.fullScreen) {
                            $(this).addClass('fullscreen');
                            _element.requestFullscreen();
                        } else {
                            $(this).removeClass('fullscreen');
                            document.exitFullScreen();
                        }

                    } else if (_element.mozRequestFullScreen) {

                        if (!document.mozFullScreen) {
                            $(this).addClass('fullscreen');
                            _element.mozRequestFullScreen();
                        } else {
                            $(this).removeClass('fullscreen');
                            document.mozCancelFullScreen();
                        }

                    } else if (_element.webkitRequestFullScreen) {

                        if (!document.webkitIsFullScreen) {
                            $(this).addClass('fullscreen');
                            _element.webkitRequestFullScreen();
                        } else {
                            $(this).removeClass('fullscreen');
                            document.webkitCancelFullScreen();
                        }
                    }
                    self.switchFullscreen = false;
                }
            });



            $('body').unbind('mouseup.desktop').on('mouseup.desktop', function (event) {
                if (window.focusedAceEdit !== null) {
                    return;
                }
                if (Indexer.isVisible)
                {
                    if (!$(event.target).parents('#searchPopup').length && !$(event.target).parents('div.search-result-preview').length) {
                        Indexer.hide();
                    }
                }

                if ($(event.target).parents('#Start-Menu').length) {
                    document.clickOffset = {left: 10, top: 10}; // used for window animation from to :) and used for close window animate back to open pos
                }
                else {
                    document.clickOffset = {left: event.clientX - $(event.target).width() / 2, top: event.clientY - $(event.target).height() / 2}; // used for window animation from to :) and used for close window animate back to open pos
                }

                if ($('.fileman-ql:visible').length)
                {
                    if (!$(event.target).parents('.fileman').length && !$(event.target).parents('.fileman-ql').length)
                    {
                        $('.fileman-ql').hide();
                        return;
                    }
                }

                if ($('.fileman-contextmenu:visible').length)
                {
                    if (!$(event.target).parents('.fileman').length && !$(event.target).parents('.fileman-contextmenu').length)
                    {
                        $('.fileman-contextmenu').hide();
                        return;
                    }
                }


                if ($('#fav-selector:visible').length && !$(event.target).parents('#fav-selector').length && !$(event.target).parents('#dcmsFav').length && event.target.id != 'dcmsFav')
                {
                    $('#fav-selector').hide();
                    $('#dcmsFav', $('#Tasks-Core')).removeClass('active').removeClass('calOpen');
                    return;
                }

                if ($('#task-cal:visible').length && !$(event.target).parents('#task-cal').length && !$(event.target).parents('#clock').length && event.target.id != 'clock')
                {
                    $('#task-cal').hide();
                    $('#clock', $('#Tasks')).removeClass('active').removeClass('calOpen');
                    return;
                }

                if ($('#searchPopup:visible').length && !$(event.target).parents('#searchPopup').length && !$(event.target).parents('#indexer').length && event.target.id != 'indexer') {
                    $('#searchPopup').hide();
                    $('#indexer', $('#Tasks-Core')).removeClass('active');
                    return;
                }


                if ($('#userMenu:visible').length && !$(event.target).parents('#user-login').length && event.target.id != 'user-login')
                {
                    $('#userMenu').hide();
                    $('#user-login').removeClass('active');
                    return;
                }

                if ($('#contenttrans-selector:visible').length && !$(event.target).parents('#content-translations').length && event.target.id != 'content-translations')
                {
                    $('#content-translations').removeClass('active');
                    $('#contenttrans-selector').hide();
                    return;
                }


                if (!$(event.target).parents('#Start-Menu-Button').length && event.target.id != 'Start-Menu-Button')
                {
                    if (!$(event.target).parents('#Start-Menu').length)
                    {
                        DesktopMenu.hideMenu(0);
                        return;
                    }
                }

                if ($('.submenu:visible', $('#App-Menu')).length && event.target.id != 'App-Menu')
                {
                    $('#App-Menu li.active').removeClass('active');
                    $('.submenu', $('#App-Menu')).removeClass('active').hide();
                    return;
                }

            });


            $('body').bind('mouseup.desktop', function (event) {
                if (window.focusedAceEdit !== null) {
                    return;
                }
                document.clickOffset = {left: event.clientX, top: event.clientY}; // used for window animation from to :) and used for close window animate back to open pos

                var outClicked_startmenu = true;
                var outClicked_desktopicon = true;
                var outClicked_desktopIconBalloon = true;
                var outClicked_window = true;
                var outClicked_Taskbar = false;


                var target = $(event.target);

                /*
                 
                 if ($('.dcmscontextmenu').is(':visible') && !$(event.target).parents('.dcmscontextmenu').length)
                 {
                 //$('.dcmscontextmenu').hide();
                 }
                 
                 
                 if (!$(event.target).hasClass('isWindowContainer') && !$(event.target).parents('.isWindowContainer').length)
                 {
                 
                 }
                 */


                if (target.parents('#Taskbar').length)
                {
                    outClicked_Taskbar = true;
                }

                /*
                 
                 if (outClicked_Taskbar)
                 {
                 if (event.target.id == 'Start-Menu' ||
                 $(event.target).parents('#Start-Menu-Button').length || event.target.id == 'Start-Menu-Button' ||
                 (($(event.target).parents().hasClass('upScroll') ||
                 $(event.target).parents().hasClass('downScroll')) && $(event.target).parents('#Start-Menu'))
                 
                 ||
                 (
                 !$(event.target).parents('ul#NavItems').length
                 &&
                 $(event.target).get(0).tagName == 'SPAN') ||
                 $(event.target).get(0).tagName == 'SPAN'
                 
                 ) {
                 outClicked_startmenu = false;
                 }
                 }
                 
                 if ($(event.target).parents('#App-Menu').length > 0 || event.target.id == 'App-Menu')
                 {
                 }
                 else
                 {
                 $('#App-Menu li.active').removeClass('active');
                 $('.submenu', $('#App-Menu')).removeClass('active').hide();
                 }
                 
                 
                 */

                // only if not the right mouse button is clicked
                if (event.which && event.which != 3) {
                    if (target.parents('.sub-container').length || target.hasClass('.sub-container')) {
                        outClicked_desktopIconBalloon = false;
                    }

                    if (!target.parents('.DesktopIconContainer.ui-selected').length &&
                            !target.parents('.DesktopIconContainer-Folder.ui-selected').length &&
                            !target.parents('.DesktopIconContainer').length &&
                            !target.parents('.DesktopIconContainer-Folder').length &&
                            outClicked_desktopIconBalloon) {

                        $('.sub-container:visible').fadeOut(250);
                    }

                    if (!target.parents('#DesktopIcons').length) {
                        $('#DesktopIcons div:not(.ui-selected)').removeClass('mouseoverclicked');
                        $('#DesktopIcons div:not(.ui-selected)').removeClass('mouseoverclickedmouseout');
                    }
                }


                /*
                 if ($(event.target).parents('.isWindowContainer:first').length || $(event.target).hasClass('isWindowContainer') ||
                 $(event.target).parents('.Taskbar-Item:first').length || $(event.target).hasClass('Taskbar-Item') || $(event.target).parents('#App-Menu').length) {
                 outClicked_window = false;
                 }
                 if (outClicked_window == true)
                 {
                 //  $('.isWindowContainer').unfocusWindow();
                 
                 //$("." + settings.Skin).removeClass('active');
                 }
                 */





                //event.preventDefault();


            });

            this.bindDesktopShortcuts();
            if (!Desktop.settings.isSeemode)
            {
                setTimeout(function ()
                {
                    Notifier.display('info', 'Ihr Desktop wurde erfolgreich erstellt');
                    if (typeof callback === 'function')
                    {
                        callback();
                        Config.set('UserName', Desktop.basicCMSData.userdata.username);
                    }
                }, 500);
            }

        },
        showLoginScreen: function ()
        {
            Desktop.Auth.display(true);
        },
        responseIsOk: function (data)
        {
            if (data == null || (typeof data == 'object' && typeof data.success == 'boolean' && data.success == false))
            {
                if (data != null && typeof data == 'object' && data.sessionerror != 'undefined' && typeof data.sessionerror != null)
                {
                    if (data.sessionerror == true)
                    {
                        //document.location.href = cmsurl + 'admin.php';				
                        return false;
                    }
                }


                if (data != null && typeof data == 'object' && data._log != 'undefined' && typeof data._log != null)
                {
                    // Debug.warn(data._log);
                }
                return false;
            }
            else
            {

                if (data != null && typeof data == 'object' && data.debugoutput != 'undefined' && typeof data.debugoutput != null)
                {
                    DesktopConsole.setDebug(data.debugoutput);
                }

                return true;
            }
        },
        ajaxWorkerOn: false,
        ajaxWorkerTimeout: null,
        convertGetToPost: function (url, postData)
        {
            if (url == '')
            {
                return {};
            }

            var json;
            url = url.replace('&amp;', '&');
            var str = (url.match(/\?/g) ? url.slice(url.indexOf('?', 0) + 1) : '');
            json = JSON.parse('{"' + decodeURI(str.replace(/&/g, "\",\"").replace(/=/g, "\":\"")) + '"}');


            return json;
        },
        loadCss: function (url, callback)
        {
            var hash = this.getHash(url);


            if ($('#' + hash, $("head")).length == 0)
            {
                var styleTag = $('<link/>').attr('type', 'text/css').attr('id', hash).attr('href', url);
                $("head").append(styleTag);


                $.ajax({
                    url: url,
                    dataType: "text",
                    error: function ()
                    {
                        Debug.error('Could not get the CSS File: ' + url);
                    },
                    success: function (data)
                    {
                        var styleTag = $('<style>').attr('id', hash);
                        $("head").prepend(styleTag.text(data));

                        if (typeof callback == 'function')
                        {
                            callback(styleTag);
                        }
                    }
                });

            }
            else
            {

                if (typeof callback == 'function')
                {
                    callback(styleTag);
                }


            }

        },
        loadedScripts: [],
        getScript: jQuery.getScript,
        loadScripts: function (scripts, _callback)
        {
            var self = this;

            if (typeof scripts != 'object')
            {
                Debug.error('To load external scripts must give an object');

                if (typeof _callback == 'function')
                {
                    _callback();
                }
                return false;
            }

            jQuery.getScript = function (resources, jqCallbackIn)
            {
                var // reference declaration & localization
                        length = resources.length,
                        handler = function () {
                            counter++;
                        },
                        deferreds = [],
                        counter = 0,
                        idx = 0;

                for (; idx < length; idx++) {
                    deferreds.push(
                            Desktop.getScript(resources[ idx ], handler)
                            );
                }

                jQuery.when.apply(null, deferreds).then(function () {
                    jqCallbackIn && jqCallbackIn();


                    jQuery.getScript = Desktop.getScript;

                });
            };



            for (var x = 0; x < scripts.length; x++)
            {
                if (scripts[x].substr(scripts[x].length - 3, scripts[x].length) !== '.js')
                {
                    scripts[x] += '.js';
                }
                //console.log('Load script:' + scripts[x]);
                var hash = this.getHash(scripts[x]);
                if ($.inArray(hash, this.loadedScripts))
                {
                    // delete scripts[x];
                }
                else {
                    // $('head').append('<script src="' + scripts[x] + '" type="text/javascript" class="dyn-script"></script>');
                }
            }

            if (scripts.length > 0) {
                var ev = [];

                for (var x = 0; x < scripts.length; x++)
                {
                    ev[x] = scripts[x];
                }

                var cb = _callback;


                if (_callback) {
                    cb = function () {

                        $.ajaxSetup({
                            async: false
                        });


                        _callback();
                    };
                }

                $.ajaxSetup({
                    async: true
                });
                Loader.require(ev, cb);
            }

        },
        getAjaxContent: function (opts, callback)
        {
            var self = this;

            if (Desktop.ajaxWorkerOn)
            {
                Desktop.ajaxWorkerTimeout = setTimeout(function () {
                    Desktop.getAjaxContent(opts, callback);
                }, 10);
            }
            else
            {
                clearTimeout(this.ajaxWorkerTimeout);
                Desktop.ajaxWorkerOn = true;

                //     $.pagemask.show(cmslang.load);


                var url = (typeof opts.WindowURL != 'undefined' && opts.WindowURL != '' ? opts.WindowURL : opts.url);
                url = Tools.prepareAjaxUrl(url);


                var controllerParams = Tools.extractAppInfoFromUrl(url), postData = {
                    ajax: true
                };

                var timoutForEval = 500;
                if (controllerParams.controller == 'layouter' && controllerParams.action == 'edit')
                {
                    timoutForEval = 700;
                }
                document.body.style.cursor = 'progress';

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    timeout: 30000,
                    data: postData,
                    screensize: $(window).width() + '|' + $(window).height(),
                    async: true,
                    global: false,
                    beforeSend: function () {
                        document.body.style.cursor = 'progress';
                        self.ajaxData = {};
                    },
                    error: function (data)
                    {
                        Tools.html5Audio('html/audio/error');
                        //  console.log('Invalid Ajax request.');
                        self.ajaxData = {};
                        self.ajaxData.Error = true;


                        Desktop.ajaxWorkerOn = false;
                        if (Desktop.windowWorkerOn)
                        {
                            clearTimeout(Desktop.windowWorkerTimeout);
                        }
                        Desktop.windowWorkerOn = Desktop.windowWorkerOn ? false : Desktop.windowWorkerOn;

                        clearTimeout(self.ajaxWorkerTimeout);
                        document.body.style.cursor = 'default';



                        jAlert((Tools.exists(data, 'msg') ? data.msg : 'Internal error'), 'Error...', function ()
                        {

                            if (typeof callback == 'function')
                            {
                                callback(data);
                            }
                            else
                            {
                                return data;
                            }
                        });

                    },
                    success: function (data)
                    {

                        var sessionError = false;

                        // document.body.style.cursor = 'auto';
                        // console.log([data]);
                        self.ajaxData = typeof data == 'object' && data != null ? data : {};


                        if (Tools.responseIsOk(data))
                        {
                            self.ajaxData.Error = false;
                            self.ajaxData.maincontent = data.maincontent;

                        }
                        else
                        {
                            $('#desktop').unmask();

                            if (Desktop.windowWorkerOn)
                            {
                                clearTimeout(Desktop.windowWorkerTimeout);
                            }
                            Desktop.windowWorkerOn = Desktop.windowWorkerOn ? false : Desktop.windowWorkerOn;

                            self.ajaxData.Error = true;
                            self.ajaxData.maincontent = Tools.exists(data, 'msg') ? data.msg : 'Internal error';


                            if (Tools.exists(data, 'sessionerror'))
                            {

                            }
                            else
                            {
                                // Debug.log([data]);
                            }
                        }


                        if (typeof data != 'object')
                        {
                            if (Desktop.windowWorkerOn)
                            {
                                clearTimeout(Desktop.windowWorkerTimeout);
                            }
                            Desktop.windowWorkerOn = Desktop.windowWorkerOn ? false : Desktop.windowWorkerOn;

                            Tools.html5Audio('html/audio/error');

                            jAlert('Invalid Ajax data', 'Ajax Error', function () {
                                return false;
                            });

                            return null;
                        }


                        if (!sessionError && Tools.exists(data, 'loadScripts'))
                        {
                            if (Tools.exists(data.loadScripts, 'css'))
                            {
                                for (var x = 0; x < data.loadScripts.css.length; x++)
                                {
                                    if (data.loadScripts.css[x].substr(data.loadScripts.css[x].length - 4, data.loadScripts.css[x].length) != '.css')
                                    {
                                        data.loadScripts.css[x] += '.css';
                                    }

                                    var cssh = self.getHash(data.loadScripts.css[x]);
                                    if (!$('#' + cssh).length)
                                    {

                                        self.loadCss(data.loadScripts.css[x], function (styleTag) {
                                            styleTag.attr('id', cssh).attr('controller', controllerParams.controller);
                                        });

                                    }
                                }
                            }



                            if (Tools.exists(data.loadScripts, 'js') && data.loadScripts.js.length)
                            {
                                self.loadScripts(data.loadScripts.js, function () {
                                    Desktop.ajaxWorkerOn = false;

                                    if (typeof callback == 'function' && !sessionError)
                                    {
                                        callback(data);
                                    }

                                });
                            }
                            else
                            {
                                Desktop.ajaxWorkerOn = false;

                                if (typeof callback == 'function' && !sessionError)
                                {
                                    callback(data);
                                }
                            }
                        }
                        else if (!sessionError && !Tools.exists(data, 'loadScripts'))
                        {
                            Desktop.ajaxWorkerOn = false;

                            if (typeof callback == 'function' && !sessionError)
                            {
                                callback(data);
                            }
                        }
                        else
                        {
                            Desktop.ajaxWorkerOn = false;
                        }
                    }
                });
            }
        },
        loadBasicConfig: function (callback)
        {
            if (this.ajaxWorkerOn)
            {
                this.ajaxWorkerTimeout = setTimeout(function () {
                    Desktop.loadBasicConfig(callback);
                }, 50);
            }
            else
            {
                clearTimeout(this.ajaxWorkerTimeout);
                this.ajaxWorkerOn = true;

                var self = this;

                document.body.style.cursor = 'progress';

                // reset data store
                var ajaxData = Desktop.ajaxData = {};

                $.post(Tools.prepareAjaxUrl(self.baseURL + 'admin.php'), {
                    dataType: "json",
                    getBasics: true,
                    cache: false,
                    screensize: $(window).width() + '|' + $(window).height(),
                    ajax: false
                },
                function (data)
                {
                    clearTimeout(self.ajaxWorkerTimeout);
                    self.ajaxWorkerOn = false;
                    document.body.style.cursor = 'auto';

                    if (self.responseIsOk(data)) {

                        if (typeof data.debugoutput != 'undefined')
                        {
                            data.debugoutput = null;
                        }

                        //
                        if (typeof data.userdata == 'undefined')
                        {
                            ajaxData.SessionError = true;
                            ajaxData = data;

                        }
                        else
                        {
                            ajaxData = data;
                            ajaxData.SessionError = false;
                        }
                    }
                    else
                    {
                        ajaxData.Error = true;
                        ajaxData.maincontent = 'Error:<p/>' + data.msg;
                        ajaxData.SessionError = false;
                    }

                    Desktop.ajaxData = ajaxData;

                    if (typeof callback == 'function')
                    {
                        return callback(data);
                    }
                    else
                    {

                        return ajaxData;
                    }

                }, 'json');
            }
        },
        refreshAllWindowsAfterChangeContentLang: function (callback)
        {
            var wins = $('#desktop div.isWindowContainer');
            var i = 0, length = wins.length;
            wins.each(function () {
                var self = this;


                setTimeout(function () {
                    if (i + 1 >= length)
                    {
                        $(self).getWindow().ReloadWindow(callback);
                    }
                    else
                    {
                        $(self).getWindow().ReloadWindow();
                    }
                    i++;
                }, 50);
            });
        },
        getHash: function (url)
        {
            return $.fn.getWindowHash(url);
        },
        windowWorkerOn: false,
        windowWorkerTimeout: null,
        sleepWindowShow: function (event, wm, _callback)
        {
            var self = this;
            var gridData = $(wm.$el).data('windowGrid');

            if (gridData.runner != false && gridData.processing)
            {
                setTimeout(function () {
                    self.sleepWindowShow(event, wm, _callback);
                }, 10);
            }
            else
            {
                if (Tools.isFunction(_callback)) {
                    setTimeout(function () {
                        _callback();
                    }, 100);
                }
            }
        },
        wincreateSkips: 0,
        // options the window options
        // event default null
        // callback callback( window obj, window obj.data('DcmsWindow'), WindowID );
        GenerateNewWindow: function (options, event, xcallback)
        {
            var self = this;

            if (Desktop.wincreateSkips > 200)
            {
                // Debug.log('GenerateNewWindow faild');
                Desktop.wincreateSkips = 0;
                return;
            }

            if (Desktop.windowWorkerOn == true || Grid.runner)
            {
                // Debug.log('GenerateNewWindow sleep 150ms');
                // wait 50ms
                Desktop.windowWorkerTimeout = setTimeout(function () {
                    Desktop.wincreateSkips++;
                    Desktop.GenerateNewWindow(options, event, xcallback);
                }, 10);
            }
            else
            {
                Desktop.wincreateSkips = 0;
                document.body.style.cursor = 'progress';

                //  Debug.log('GenerateNewWindow run');
                clearTimeout(Desktop.windowWorkerTimeout);

                if (typeof options != 'object') {
                    options = {};
                }

                this.windowSettings = null;
                var ajax = this.ajaxData;

                if (ajax.Error == true)
                {
                    $.gritter.add({
                        title: 'Fehler',
                        time: 4000,
                        text: ajax.maincontent
                    });

                    return null;
                }


                Desktop.windowWorkerOn = true;

                var defaults = this.windowDefaultSettings;


                var settings = {};

                this.windowSettings = $.extend({}, defaults, options);
                this.windowSettings.Skin = this.settings.Skin;

                settings = $.extend(settings, options);
                settings.Skin = this.settings.Skin;

                if (settings.isSingleWindow == true)
                {
                    if (settings.SingleWindowID)
                    {
                        settings.WindowID = settings.SingleWindowID;
                    }
                    else
                    {
                        settings.WindowID = (settings.loadWithAjax && Tools.isString(settings.WindowURL) ? this.getHash(Tools.prepareAjaxUrl(settings.WindowURL)) : 'w' + Math.floor(Math.random() * 1000000));
                    }
                }
                else
                {
                    if (!settings.WindowID)
                    {
                        settings.WindowID = (settings.loadWithAjax && Tools.isString(settings.WindowURL) ? this.getHash(Tools.prepareAjaxUrl(settings.WindowURL)) : 'w' + Math.floor(Math.random() * 1000000));
                    }
                }

                Application.currentWindowID = settings.WindowID;
                Application.setActiveUrl(settings.WindowURL);

                settings.WindowContent = (settings.WindowContent != null && Tools.isString(settings.WindowContent) ? settings.WindowContent : ajax.maincontent);
                // settings.WindowContent = $.parseHTML(settings.WindowContent);




                if (ajax.onAfterMenuCreated)
                {
                    settings.onAfterMenuCreated = ajax.onAfterMenuCreated;
                }


                if (!settings.Controller)
                {
                    settings.Controller = Tools.extractAppInfoFromUrl(settings.WindowURL).controller;
                }

                if (!settings.Action)
                {
                    settings.Action = Tools.extractAppInfoFromUrl(settings.WindowURL).action;
                }

                if (ajax.applicationMenu)
                {
                    Application.cacheCurrentApp(settings.Controller, settings.Action, ajax);
                }

                var isRoot = false;

                if ($('#' + settings.WindowID).length == 0 && settings.isSingleWindow != true)
                {
                    $('#desktop').append('<div id="' + settings.WindowID + '" class="loading"></div>');
                }
                else if ($('#' + settings.WindowID).length == 0 && settings.isSingleWindow == true)
                {
                    $('#desktop').append('<div id="' + settings.WindowID + '" class="loading"></div>');
                }
                else if ($('#' + settings.WindowID).length == 1 && settings.isSingleWindow == true && settings.WindowID)
                {

                }

                if (settings.WindowID)
                {
                    Win.setActive(settings.WindowID);
                }




                var isSingleWindow = typeof ajax.isSingleWindow != 'undefined' && ajax.isSingleWindow != null ? ajax.isSingleWindow : false;
                var nopadding = (typeof ajax.nopadding != 'undefined' && ajax.nopadding != null ? ajax.nopadding : (Tools.exists(settings, 'nopadding') ? settings.nopadding : false));

                if (!isSingleWindow && settings.isSingleWindow === true)
                {
                    isSingleWindow = true;
                }


                if (settings.Controller == 'plugin')
                {
                    var pluginName = $.getURLParam('plugin', settings.WindowURL);
                    if (pluginName)
                    {
                        settings.Controller = pluginName;
                    }
                }
                var wr;
                var defaults = {
                    TaskbarHeight: $('#Taskbar').outerHeight(true),
                    addFileSelector: settings.addFileSelector || false,
                    rollback: settings.rollback || false,
                    versioning: settings.versioning,
                    Controller: settings.Controller,
                    Action: settings.Action,
                    WindowID: settings.WindowID,
                    Url: settings.WindowURL,
                    Title: ajax.pageCurrentTitle ? ajax.pageCurrentTitle : (ajax.pageTitle ? ajax.pageTitle : (settings.WindowTitle == null ? 'Window...' : settings.WindowTitle)),
                    Toolbar: ajax.toolbar,
                    Content: settings.WindowContent,
                    isSingleWindow: isSingleWindow,
                    isRootApplication: typeof ajax.isRootApplication != 'undefined' && ajax.isRootApplication != null ? ajax.isRootApplication : isRoot,
                    nopadding: nopadding,
                    enableContentScrollbar: true,
                    onBeforeClose: function(event, wm, _callback){
                        Application.onBeforeWindowClose(event, wm, _callback);
                    },
                    onAfterClose: function(event, wm, _callback){
                        Application.onAfterWindowClose(event, wm, _callback);
                    },
                    onResize: function (event, wm, ui, uiContent)
                    {
                        clearTimeout(wr);

                        var gridData = $(wm.$el).data('windowGrid');
                        if (gridData) {
                            gridData.updateDataTableSize(wm.$el, true, uiContent);
                        }


                        if (typeof resizeAce === 'function')
                        {
                            wr = setTimeout(function () {
                                resizeAce(wm);
                            }, 50);
                        }
                    },
                    onResizeStop: function (event, wm, ui, uiContent)
                    {

                        var gridData = $(wm.$el).data('windowGrid');
                        if (gridData) {
                            gridData.updateDataTableSize(wm.$el, false, uiContent);
                        }


                        Application.onWindowDragStop(wm, function () {
                            Win.redrawWindowHeight(wm.id, (wm.settings.enableContentScrollbar ? true : false));
                            if (typeof resizeAce === 'function')
                            {
                                resizeAce(wm);
                            }
                        });
                    },
                    onWindowDragStop: function(e, wm) {
                        Application.onWindowDragStop(wm);
                    },
                    onBeforeReload: function (wm, _callback)
                    {
                        Doc.unload(wm.id);
                        Win.setActive(wm.id);

                        $(wm.$el).removeData('formID');
                        $(wm.$el).removeData('formConfig');

                        Win.redrawWindowHeight(wm.id, (wm.settings.enableContentScrollbar ? true : false));

                        if (Tools.isFunction(_callback))
                        {
                            _callback();
                        }
                    },
                    onAfterCreated: function (wm, _callback, ajaxContent)
                    {
                        Desktop.windowWorkerOn = false;
                        Application.currentWindowID = wm.id;
                        Win.setActive(wm.id);

                        if (typeof settings.WindowContent === 'string' && settings.WindowContent)
                        {
                            if ($(settings.WindowContent).filter('script').length) {
                                //     console.log('Eval Scripts after window Created');
                                Tools.eval($(settings.WindowContent));
                            }
                        }
                        else if (ajaxContent)
                        {
                            if ($(ajaxContent).filter('script').length) {
                                //   console.log('Eval Scripts after window Created');
                                Tools.eval($(ajaxContent));
                            }
                        }
                        else {
                            //  console.log('No Eval Scripts for after window created found');
                        }

                        createTemplateEditor(wm.id);

                        if (typeof settings.beforeShow == 'function')
                        {
                            settings.beforeShow(wm);
                        }

                        var gridData = wm.$el.data('windowGrid');
                        if (gridData)
                        {
                            wm.settings.isGridWindow = true;
                            wm.settings.nopadding = true;
                            wm.settings.enableContentScrollbar = false;
                            wm.settings.hasGridAction = gridData.hasGridAction;
                            wm.set('minWidth', 720);
                        }

                        $('#desktop').unmask();

                        if (typeof _callback === 'function')
                        {

                            $.pagemask.hide();

                            if (gridData && (gridData.runner || gridData.processing))
                            {
                                self.sleepWindowShow(null, wm, _callback);
                            }
                            else
                            {
                                setTimeout(function () {
                                    _callback();
                                }, 50);
                            }
                        }
                    },
                    onBeforeShow: function (e, wm, _callback)
                    {
                        $('#desktop').unmask();

                        Win.setActive(wm.id);
                        Win.setDocumentVersioning(wm.id);

                        Application.currentWindowID = wm.id;
                        Application.createAppMenu(wm.settings.Controller, wm.settings.Action, wm.settings);
                        
                        var gridData = wm.$el.data('windowGrid');

                        if (gridData)
                        {
                            wm.settings.isGridWindow = true;
                            wm.settings.nopadding = true;
                            wm.settings.enableContentScrollbar = false;
                            wm.settings.hasGridAction = gridData.hasGridAction;
                            wm.settings.minWidth = 720;
                        }

                        if (wm.$el.hasClass('no-scroll'))
                        {
                            wm.settings.enableContentScrollbar = false;
                        }

                        if (wm.get('hasTinyMCE') === true)
                        {
                            wm.$el.addClass('tinyMCEwin');
                        }
                        
                        Win.Tabs.initTabs(wm.id);
                        Win.prepareWindowFormUi(wm.id);
                        
                        Desktop.windowWorkerOn = false;
                        
                        if (typeof _callback === 'function')
                        {
                            if (gridData && (gridData.runner || gridData.processing))
                            {
                                self.sleepWindowShow(null, wm, _callback);
                            }
                            else
                            {
                                setTimeout(function () {
                                    _callback();
                                }, 10);
                            }
                        }
                    },
                    onAfterShow: function (e, wm, uiContent)
                    {
                        wm.focus(e, wm.id);

                        Win.setActive(wm.id);
                        Desktop.windowWorkerOn = false;

                        // here create the grid
                        if (wm.settings.isGridWindow && wm.$el.data('windowGrid') && !wm.$el.data('windowGrid').isExcecuted != true)
                        {
                            //    console.log('isGridWindow create');
                            Desktop.windowWorkerOn = false;

                            wm.$el.data('windowGrid').createGrid(wm.$el.data('windowGrid'), function () {
                                wm.$el.removeClass('loading');
                                document.body.style.cursor = 'default';

                                Win.prepareWindow(function ()
                                {
                                    if (wm.settings.isGridWindow)
                                    {
                                        // var gridData = $(wm.$el).data('windowGrid');
                                        //  gridData.headerTableWrapper.width('');
                                        //  gridData.updateDataTableSize(wm.$el, false, uiContent);
                                    }

                                    wm.$el.removeClass('loading').unmask();


                                    Win.prepareWindowFormUi(wm.id);
                                    Desktop.Tools.rebuildTooltips();

                                    if (typeof resizeAce === 'function')
                                    {
                                        resizeAce($('#' + Win.windowID).data('WindowManager'));
                                    }

                                    if (wm.get('hasTinyMCE') === true)
                                    {
                                        setTimeout(function () {
                                            wm.updateContentHeight();

                                            if (xcallback != null && typeof xcallback == 'function')
                                            {
                                                //     Debug.log('GenerateNewWindow run callback');
                                                return xcallback(wm.$el, wm, wm.id);
                                            }
                                            else
                                            {
                                                //     Debug.log('GenerateNewWindow return');
                                                return wm.$el;
                                            }
                                        }, 50);
                                    }
                                    else
                                    {
                                        Win.redrawWindowHeight(false, (wm.settings.enableContentScrollbar ? true : false));

                                        if (xcallback != null && typeof xcallback == 'function')
                                        {
                                            return xcallback(wm.$el, wm, wm.id);
                                        }
                                        else
                                        {
                                            //   Debug.log('GenerateNewWindow return');
                                            return wm.$el;
                                        }
                                    }
                                });
                            });

                        }
                        else
                        {
                            wm.settings.isGridWindow = false;


                            //     $(wm.$el).removeClass('loading');
                            document.body.style.cursor = 'default';


                            Desktop.windowWorkerOn = false;

                            Win.prepareWindow(function ()
                            {
                                var gridData = wm.$el.data('windowGrid');

                                if (wm.settings.isGridWindow && gridData)
                                {
                                    gridData.headerTableWrapper.width('');
                                    gridData.updateDataTableSize(wm.$el, false, uiContent);
                                }

                                wm.$el.removeClass('loading').unmask();

                                Win.prepareWindowFormUi(wm.id);
                                Desktop.Tools.rebuildTooltips();

                                if (wm.get('hasTinyMCE') === true)
                                {
                                    setTimeout(function () {

                                        if (typeof resizeAce === 'function')
                                        {
                                            resizeAce($('#' + Win.windowID).data('WindowManager'));
                                        }

                                        wm.updateContentHeight();

                                        if (xcallback != null && typeof xcallback == 'function')
                                        {
                                            //    Debug.log('GenerateNewWindow run callback');
                                            var obj = wm.$el;
                                            return xcallback(wm.$el, wm, wm.id);
                                        }
                                        else
                                        {
                                            //    Debug.log('GenerateNewWindow return');
                                            return wm.$el;
                                        }
                                    }, 50);
                                }
                                else
                                {
                                    if (typeof resizeAce === 'function')
                                    {
                                        resizeAce($('#' + Win.windowID).data('WindowManager'));
                                    }

                                    Win.redrawWindowHeight(false, (wm.settings.enableContentScrollbar ? true : false));

                                    if (xcallback != null && typeof xcallback == 'function')
                                    {
                                        //    Debug.log('GenerateNewWindow run callback');
                                        var obj = wm.$el;
                                        return xcallback(wm.$el, wm, wm.id);
                                    }
                                    else
                                    {
                                        //     Debug.log('GenerateNewWindow return');
                                        return wm.$el;
                                    }
                                }

                            });


                        }


                    },
                    onFocus: function (e, wm, callback)
                    {
                        Win.setActive(wm.id);
                        Application.currentWindowID = wm.id;
                        Application.createAppMenu(wm.settings.Controller, wm.settings.Action, wm.settings);
                        
                        
                        if ( wm.$el.attr('meta') )
                        {
                            SidePanel.show();
                        }
                        else {
                            SidePanel.hide();
                        }
                        
                        
                        if (typeof setActiveCodemirror == 'function') {
                            setActiveCodemirror(wm.id);
                        }
                        
                        if (typeof callback === 'function') {
                            callback();
                        }
                    }
                };





                defaults.onResizeStart = (typeof settings.onResizeStart == 'function' ? settings.onResizeStart : defaults.onResizeStart);
                defaults.onResize = (typeof settings.onResize == 'function' ? settings.onResize : defaults.onResize);
                defaults.onResizeStop = (typeof settings.onResizeStop == 'function' ? settings.onResizeStop : defaults.onResizeStop);
                defaults.onAfterCreated = (typeof settings.onAfterCreated == 'function' ? settings.onAfterCreated : defaults.onAfterCreated);
                defaults.onBeforeShow = (typeof settings.onBeforeShow == 'function' ? settings.onBeforeShow : defaults.onBeforeShow);
                defaults.onAfterShow = (typeof settings.onAfterShow == 'function' ? settings.onAfterShow : defaults.onAfterShow);
                defaults.onFocus = (typeof settings.onFocus == 'function' ? settings.onFocus : defaults.onFocus);

                defaults.enableContentScrollbar = (typeof settings.scrollable != 'undefined' && settings.scrollable != null ? settings.scrollable : defaults.enableContentScrollbar);
                defaults.enableContentScrollbar = (typeof ajax.scrollable != 'undefined' && ajax.scrollable != null ? ajax.scrollable : defaults.enableContentScrollbar);

                if (typeof ajax.screensize != 'undefined')
                {
                    if (ajax.screensize == $(window).width() + '|' + $(window).height())
                    {
                        if (ajax.winWidth > 0)
                        {
                            defaults.Width = parseInt(ajax.winWidth);
                        }

                        if (ajax.winHeight > 0)
                        {
                            defaults.Height = parseInt(ajax.winHeight);
                        }


                        if (ajax.winLeft >= 0)
                        {
                            defaults.PositionLeft = parseInt(ajax.winLeft);
                        }

                        if (ajax.winTop > 0)
                        {
                            defaults.PositionTop = parseInt(ajax.winTop);
                        }
                    }
                }

                if (typeof settings.screensize != 'undefined')
                {
                    if (settings.screensize == $(window).width() + '|' + $(window).height())
                    {
                        if (settings.winWidth > 0)
                        {
                            defaults.Width = parseInt(settings.winWidth);
                        }

                        if (settings.winHeight > 0)
                        {
                            defaults.Height = parseInt(settings.winHeight);
                        }


                        if (settings.winLeft >= 0)
                        {
                            defaults.PositionLeft = parseInt(settings.winLeft);
                        }

                        if (settings.winTop > 0)
                        {
                            defaults.PositionTop = parseInt(settings.winTop);
                        }
                    }
                }

                if (typeof ajax.WindowResizeable != 'undefined' && ajax.WindowResizeable != true)
                {
                    defaults.Resizable = false;
                }
                else if (typeof settings.WindowResizeable != 'undefined' && settings.WindowResizeable != true)
                {
                    defaults.Resizable = false;
                }


                if (typeof ajax.WindowMinimize != 'undefined' && ajax.WindowMinimize != true)
                {
                    defaults.Minimize = false;
                }
                else if (typeof settings.WindowMinimize != 'undefined' && settings.WindowMinimize != true)
                {
                    defaults.Minimize = false;
                }


                if (typeof ajax.WindowMaximize != 'undefined' && ajax.WindowMaximize != true)
                {
                    defaults.Maximize = false;
                }
                else if (typeof settings.WindowMaximize != 'undefined' && settings.WindowMaximize != true)
                {
                    defaults.Maximize = false;
                }




                if (typeof ajax.WindowHeight != 'undefined' && ajax.WindowHeight > 0)
                {
                    defaults.Height = ajax.WindowHeight;
                }
                else if (typeof settings.WindowHeight != 'undefined' && settings.WindowHeight > 0)
                {
                    defaults.Height = settings.WindowHeight;
                }
                else if (typeof settings.Height != 'undefined' && settings.Height > 0)
                {
                    defaults.Height = settings.Height;
                }


                if (typeof ajax.WindowWidth != 'undefined' && ajax.WindowWidth > 0)
                {
                    defaults.Width = ajax.WindowWidth;
                }
                else if (typeof settings.WindowWidth != 'undefined' && settings.WindowWidth > 0)
                {
                    defaults.Width = settings.WindowWidth;
                }
                else if (typeof settings.Width != 'undefined' && settings.Width > 0)
                {
                    defaults.Width = settings.Width;
                }

                var opts = $.extend({}, settings, defaults);
                $('#desktop').prepend($('#' + settings.WindowID));
                $('#' + settings.WindowID).windowManager(opts);

            }

        },
        /**
         * Will prepare the window after created
         *
         */
        prepareWindowContent: function (windowID)
        {
            return;
            setTimeout(function () {
                Win.prepareWindow(windowID);
            }, 500);

        },
        unloadWindowContent: function (windowID)
        {
            Win.unload(windowID);
        },
        // returns the current window header
        getCurrentWindowHeader: function (windowID)
        {
            return $('#' + windowID).find('.window-header:first');
        },
        getCurrentWindowToolbar: function (windowID)
        {
            return $('#' + windowID).find('.window-header:first');
        },
        // -------------------- Internal functions -----------------------

        getOpenWindowsCount: function ()
        {
            var i = 0;
            $.each($('body').data('DcmsWindows'), function () {
                if (this.get('WindowStatus') != 'minimized' && this.get('WindowStatus') != 'closed') {
                    i += 1;
                }
            });
            return (i);
        },
        minall: function () {
            $.each($('body').data('DcmsWindows'), function () {
                if (this.get('WindowStatus') != 'minimized' && this.get('WindowStatus') != 'closed') {
                    this.ResizeWindow('minimize', false);
                }
            });
        },
        resall: function () {
            $.each($('body').data('DcmsWindows'), function () {
                if (this.get('WindowStatus') != 'window' && this.get('WindowStatus') != 'closed') {
                    // this.ResizeWindow('restore', false);
                    var self = this;
                    setTimeout(function () {
                        Dock.showApplication(self.get('Controller'));
                    }, 200);
                }
            });
        },
        transparentallon: function () {
            $.each($('body').data('DcmsWindows'), function ()
            {
                if (this.get('WindowStatus') != 'minimized' && this.get('WindowStatus') != 'closed') {
                    this.ResizeWindow('transparent-on', false);
                }
            });
        },
        transparentalloff: function () {
            $.each($('body').data('DcmsWindows'), function ()
            {
                if (this.get('WindowStatus') != 'minimized' && this.get('WindowStatus') != 'closed') {
                    this.ResizeWindow('transparent-off', false);
                }
            });
        },
        getActiveWindow: function ()
        {
            var focused = $('body').data('FocusWindow');
            if (!focused)
            {
                focused = Win.windowID;
                if (!focused)
                {
                    console.log('No windows is focused ');
                    return false;
                }
            }
            var w = $('#' + focused);
            return w;
        },
        getActiveWindowContent: function ()
        {
            var actWin = this.getActiveWindow();
            return (actWin ? actWin.data('WindowManager').BodyContent : null);
        },
        getActiveWindowToolbar: function ()
        {
            var actWin = this.getActiveWindow();
            return (actWin ? actWin.find('.window-toolbar') : null);
        },
        getActiveWindowButton: function (button)
        {
            switch (button)
            {
                case 'min':
                    button = 'win-min-btn';
                    break;

                case 'max':
                    button = 'win-max-btn';
                    break;

                case 'reg':
                    button = 'WinBtnSet.winreg';
                    break;

                case 'close':
                    button = 'win-close-btn';
                    break;

            }
            var actWin = this.getActiveWindow();
            return (actWin ? actWin.find('.window-tc .' + button) : null);
        },
        destroyActiveWindow: function ()
        {
            var actWin = this.getActiveWindow();

            if (actWin) {
                actWin.data('DcmsWindow').destroy();
            }
        },
        loadTab: function (event, opt /*url, obj, label, useOpener*/) {

            if (!opt.obj)
            {
                Debug.error('Empty object for loadTab!')
                return;
            }

            opt.url = opt.url.replace(Config.get('portalurl') + '/', '');

            var title, alt, icon = '';
            if ($(opt.obj).find('img:first').length)
            {
                icon = $(opt.obj).find('img:first').attr('src');

                if (!icon && $(opt.obj).hasClass('action-button'))
                {
                    var span = $(opt.obj).find('span:first');
                    if (span.length)
                    {
                        var img = span.css('background-image').trim();
                        img = img.replace(/.*(url\s*\(([a-z0-9_\-\.:\/"']+?)\)).*/ig, '$2');

                        if (img)
                        {
                            icon = img;
                        }
                    }
                }
            }


            if (typeof opt.obj != 'undefined' && typeof opt.obj != 'string' && (typeof opt.label == 'undefined' || opt.label == '') && icon != '')
            {
                title = $(opt.obj).find('img:first').attr('title');
                alt = $(opt.obj).find('img:first').attr('alt');

                if (typeof title != 'undefined')
                {
                    opt.label = title;
                }
                else
                {
                    if (typeof alt != 'undefined')
                    {
                        opt.label = alt;
                    }
                }
            }

            if (Tools.isString(opt.obj))
            {
                icon = opt.obj;
            }

            if (opt.label == '')
            {
                opt.label = 'Unbekannter Inhalt';
            }


            if (opt.url)
            {

                // Window exists?
                var hash = Desktop.getHash(Tools.prepareAjaxUrl(opt.url));
                var wFound = $('#' + hash);
                if (wFound.length == 1 && !opt.isSingleWindow)
                {
                    // Timeout for button click events
                    setTimeout(function () {
                        wFound.data('WindowManager').focus();
                        document.body.style.cursor = 'default';
                    }, 50);
                    return;
                }

                $('#desktop').mask('Bitte warten...');

                if ($(opt.obj).parents('.isWindowContainer').length)
                {
                    $(opt.obj).parents('.isWindowContainer').find('div:first').click();
                }

                var currentWindow = Desktop.getActiveWindow();
                var currentWindowInstance = currentWindow ? currentWindow.data('DcmsWindow') : false;
                var openerid = currentWindow ? currentWindow.attr('id') : null;

                setTimeout(function () {
                    var opts = {};
                    opts.rollback = opt.rollback || false;
                    opts.loadWithAjax = true;
                    opts.WindowToolbar = false;
                    opts.DesktopIconWidth = 36;
                    opts.DesktopIconHeight = 36;
                    opts.UseWindowIcon = false;
                    opts.Skin = Desktop.settings.Skin;

                    var appData = Tools.extractAppInfoFromUrl(opt.url);

                    opts.Controller = appData.controller;
                    opts.Action = appData.action;
                    opts.WindowTitle = opt.label;
                    opts.WindowURL = Tools.prepareAjaxUrl(opt.url);
                    opts.WindowDesktopIconFile = (icon ? icon : '');
                    opts.versioning = (opt.versioning ? opt.versioning : false);


                    opts = $.extend({}, opt, opts);


                    // reset current ajaxData
                    Desktop.ajaxData = {};

                    Desktop.getAjaxContent(opts, function (data)
                    {

                        if (!Tools.responseIsOk(data))
                        {
                            Notifier.display('error', (Tools.exists(data, 'msg') ? data.msg : 'Ajax error...'));
                        }
                        else
                        {
                            opts.rollback = data.rollback || false;
                            opts.addFileSelector = data.addFileSelector || false;

                            if (data.pageCurrentTitle)
                            {
                                opts.WindowTitle = data.pageCurrentTitle;
                            }

                            if (data.isSingleWindow)
                            {
                                if (currentWindowInstance && currentWindowInstance.get('isRootApplication'))
                                {
                                    opts.SingleWindowID = openerid;
                                }

                                opts.isSingleWindow = data.isSingleWindow;
                            }

                            if (Tools.exists(data, 'versioning') && data.versioning != '')
                            {
                                opts.versioning = data.versioning;
                            }



                            if (typeof Desktop.ajaxData.permissionerror != 'undefined' && Desktop.ajaxData.permissionerror)
                            {
                                if (typeof Desktop.ajaxData.msg != 'undefined' && Desktop.ajaxData.msg)
                                {
                                    Notifier.display('error', Desktop.ajaxData.msg);
                                }
                                return false;
                            }

                            Application.cacheCurrentApp(opts.Controller, opts.Action, data);

                            // Desktop.ajaxData = data ;



                            if (typeof Desktop.ajaxData.toolbar != 'undefined')
                            {
                                opts.WindowToolbar = Desktop.ajaxData.toolbar;
                            }




                            Desktop.GenerateNewWindow(opts, event, function (obj, objdata, id)
                            {
                                if (openerid)
                                {
                                    objdata.set('opener', openerid);
                                    obj.attr('opener', openerid);
                                }

                                obj.attr('app', opts.Controller);
                                Application.currentWindowID = id;
                                Application.createAppMenu(opts.Controller, opts.Action, opts);
                                $('#desktop').unmask();
                                obj.unmask();
                            });
                        }
                    });

                }, 50);
            }
        },
        /**
         * 
         * @returns {undefined}
         */
        changeDesktopBackground: function (temp)
        {
            if (this.basicCMSData.userdata && !temp)
            {
                $('#desktop-bg').attr('src', Config.get('backendImagePath') + 'desktop-backgrounds/' + Personal.get('desktopbackground', 'galaxy.jpg'));
            }
            if (temp)
            {
                $('#desktop-bg').attr('src', Config.get('backendImagePath') + 'desktop-backgrounds/' + temp);
            }
        }









        /*,
         
         
         
         
         
         
         
         
         storePosition: function (elementData) {
         var P = this,
         r = P.uiDialog;
         elementData.left = r.css("left");
         elementData.top = r.css("top");
         elementData.height = r.height();
         elementData.width = r.width();
         elementData.contentHeight = r.find('.ui-dialog-content').height();
         elementData.contentWidth = r.find('.ui-dialog-content').width();
         },
         
         
         addExpose: function () {
         var i = this,
         j = i.uiDialog;
         
         if (i.state != 'closed' && i.hasAnimationFinised) 
         {
         i.hasAnimationFinised = false;
         if (!i.hasClass('ui-widget-expose')) {
         i.addClass('ui-widget-expose');
         }
         if (i.state != 'expose') {
         i.expose();
         } else {
         j.animate({
         left: i.left,
         top: i.top
         }, function () {
         i.state = 'open';
         j.removeClass('ui-widget-expose');
         
         i.hasAnimationFinised = true;
         if (i.hasClass('ui-widget-expose')) {
         i.removeClass('ui-widget-expose');
         }
         
         });
         }
         }
         },
         
         expose: function (elementData) {
         var i = this,
         j = i.uiDialog;
         if (i.state == 'open') {
         i.storePosition(elementData);
         } else if (i.state == 'minimize') {
         i.minimize(elementData);
         } else if (i.state == 'fullscreen') {
         i.restore(elementData);
         } else {
         i.hasAnimationFinised = true;
         return;
         }
         j.removeClass(i.options.openClass).addClass($.ui.iMatoriaDialog.exposeClass);
         var I = $(window).height(),
         _ = $(window).width(),
         M = $(document).height(),
         E = $(document).width(),
         T = j.outerHeight(),
         t = j.outerWidth(),
         c = parseInt(j.attr('counter')), // the number of window
         b = 20;
         switch (c % 2) {
         case 1:
         j.animate({
         top: -T,
         left: (_ - t) / 2
         }, function () {
         //i.callbackExpose();
         });
         break;
         case 0:
         j.animate({
         top: (I - T) / 2,
         left: -t
         }, function () {
         //i.callbackExpose();
         });
         break;
         }
         
         
         i.state = 'expose';
         }
         */

    };

})(window, document);