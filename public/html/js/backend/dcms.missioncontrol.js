var MissionControl = {
    inited: false,
    missionControlActived: false,
    spacesFirstLaunch: false,
    missionControlZoomActived: false,
    missionControlKeyBoardShortcut: false,
    MissionControlWindowInfo: [],
    init: function ()
    {
        if (this.inited)
        {
            return;
        }
        this.bindDashboardShortcuts();
        this.inited = true;
    },
    bindDashboardShortcuts: function ()
    {
        var self = MissionControl;

        $(document).on('keydown.missioncontrol', function (e) {
            var meta = e.ctrlKey || e.metaKey;

            if (e.keyCode == 122 && meta)
            {

                if (Dashboard.dashboardOn)
                {
                    Dashboard.close();
                }

                if (self.missionControlActived)
                {
                    self.MissionControlRemove();
                }
                else
                {
                    if (self.show())
                    {
                        self.SpacesStart();
                    }
                }
            }

        });
    },
    MissionControlRemove: function ()
    {
        if (!this.missionControlActived)
        {
            return false;
        }

        var xself = this;

        $('.MissioncontrolIcns,.MissioncontrolIcnsText,.MissionControlCache').remove();

        $('#desktop').unbind('click.missioncontrol');
        $('#desktop-bg').css({position: 'absolute'});
        $('#desktop-bg,#desktop').css({
            left: 0,
            top: 0,
            width: window.innerWidth,
            height: window.innerHeight
        });




        if ($('#desktop-side-panel').attr('reopen') == 1)
        {
            $('#desktop-container').css('left', $('#desktop-container').attr('basePos')).removeAttr('basePos');
            $('#desktop-side-panel').width($('#desktop-side-panel').attr('baseWidth')).removeAttr('baseWidth').show();
            $('#desktop-side-panel').removeAttr('baseWidth').removeAttr('reopen');
        }

        if ($('#Sidepanel').attr('reopen') == 1)
        {
            $('#Sidepanel').removeAttr('reopen').show();
        }

        if ($('#gui-console').attr('reopen') == 1)
        {
            $('#gui-console').removeAttr('reopen').show();
        }







        var spaceWindows = $('#desktop').find('.isWindowContainer[activespace="' + Desktop.currentSpace + '"]:visible'), total = spaceWindows.length;


        spaceWindows.each(function (i) {
            var self = this;
            var top = $(this).attr('oldTop');
            var left = $(this).attr('oldLeft');

            if ($(this).parent().hasClass('MissionControl_GroupWindow') && $('.tinymce-editor', $(this)).length == 0) {
                var parentID = $(this).parent().attr('id');
                Debug.log('id to unwrap without editors: ' + parentID);

                $(this).unwrap();
            }

            console.log('reset position : ' + $(this).attr('id'));

            $(this).css({
                position: 'absolute',
                top: top,
                zIndex: $(this).attr('oldZIndex'),
                left: left,
                'margin-top': '',
                'margin-left': '',
                'box-shadow': '',
                '-moz-box-shadow': '',
                '-moz-transform': '',
                '-webkit-transform': '',
                '-webkit-box-shadow': '',
                'transform': ''
            }).removeAttr('oldStyle').
                    removeAttr('oldTop').
                    removeAttr('oldLeft').
                    removeAttr('oldWidth').
                    removeAttr('oldHeight').
                    removeAttr('oldZIndex').addClass('mustFade').scale(1);

            if ($(this).parent().hasClass('MissionControl_GroupWindow') && $('.tinymce-editor', $(this)).length > 0) {

                Debug.log('id to unwrap with editors: ' + $(this).parent().attr('id'));

                $(this).unwrap().scale(1);

                $('.tinymce-editor', $(this)).each(function () {

                    Debug.log('id editors: ' + $(this).attr('name'));

                    var e = this;

                    Doc.loadTinyMceConfig($(self), function () {
                        $(e).removeClass('loaded');
                        Doc.loadTinyMce($(self));
                        console.log('id editors: ' + $(e).attr('name') + ' loaded');
                    });
                });
            }



            // enable resizeable if exists
            if ($(this).hasClass('ui-resizable')) {
                $(this).resizable('enable');
            }


            $(this).hide();
        });

        Tools.sleep(2);

        var args = arguments;


        $('#missioncontrol-bg,#spacesMiniContainer,.spacesCreateNewDesktopOverZone').stop().fadeOut(100, function () {
            $(this).hide();
        });

        $('#Taskbar,#DesktopIcons,#dock,#desktop .mustFade').stop().fadeIn(350, function () {
            if ($(this).hasClass('mustFade')) {
                $(this).removeClass('mustFade');
            }

            // Now activate the clicked window

            if (typeof args[0] != 'undefined')
            {
                var $activateWin = $('#' + args[0]);

                if ($activateWin.length === 1)
                {
                    setTimeout(function () {
                        Debug.log('focus uniqueid in ' + $activateWin.attr('uniqueid'));

                        $activateWin.trigger('click');

                        if ($activateWin.data('WindowManager'))
                        {

                            setTimeout(function () {
                                $activateWin.data('WindowManager').focus();
                                $activateWin.addClass('active');
                            }, 100);
                        }
                    }, 100);
                }
            }


            xself.MissionControlWindowInfo = [];
            xself.missionControlActived = false;
        });
        return true;

    },
    show: function ()
    {
        if (this.missionControlActived)
        {
            return false;
        }


        var xself = this, MCstartTime = new Date().getTime();
        var elapsedTime = 0, windowZIndex = 3000;



        this.MissionControlWindowInfo = [];

        if ($('#Sidepanel').is(':visible'))
        {
            $('#Sidepanel').attr('reopen', '1').hide();
        }


        if ($('#searchPopup').is(':visible')) {
            $('#searchPopup').hide();
            $('#indexer').removeClass('active');
        }

        if ($('#desktop-side-panel').is(':visible'))
        {

            $('#desktop-container').attr('basePos', $('#desktop-container').css('left'));
            $('#desktop-side-panel').attr('baseWidth', $('#desktop-side-panel').outerWidth());
            $('#desktop-container').css('left', '');
            $('#desktop-side-panel').attr('reopen', '1').hide();
        }

        if ($('#gui-console').is(':visible'))
        {
            $('#gui-console').attr('reopen', '1').hide();
            $('#console', $('#Tasks-Core')).removeClass('active');
        }

        $('#missioncontrol-bg').show();
        $('#Taskbar,#dock,#DesktopIcons').hide();

        $('#desktop,#desktop-bg').css({
            top: 0,
            left: 0
        });


        Tools.sleep(10);


        var MissionControlMainTop = ((window.innerHeight * 9) / 100) + 40; //(parseInt($('#desktop').outerHeight()) / 2) - (((window.innerHeight * 69.3) / 100) / 2);

        $('#desktop-bg').css({
            'position': 'absolute',
            'top': (parseInt($('#desktop-bg').height()) / 2) - (((window.innerHeight * 69.3) / 100) / 2) + 'px',
            'left': (parseInt($('#desktop-bg').width()) / 2) - (((window.innerWidth * 69.3) / 100) / 2) + 'px',
            'width': (window.innerWidth * 69.3) / 100 + 'px',
            'height': (window.innerHeight * 69.3) / 100 + 'px',
            'box-shadow': '0px 12px 30px rgba(0,0,0,.5)',
            '-moz-box-shadow': '0px 12px 30px rgba(0,0,0,.5)',
            '-webkit-box-shadow': '0px 12px 30px rgba(0,0,0,.5)'
        });


        Tools.sleep(10);

        $('.tinymce-editor').each(function () {
            console.log('id editors: ' + $(this).attr('name') + ' remove');
            $(this).tinymce().remove();
        });


        Tools.sleep(50);


        this.missionControlActived = true;


        var GroupWindowArray = [];
        var GroupWinList = [];
        var winWidth = [];


        var windows = $('#desktop').find('.isWindowContainer[activespace=' + Desktop.currentSpace + ']:visible');

        windows.each(function (i) {
            // positions
            var idName = $(this).attr('id');
            var top = parseInt($(this).css('top'));
            var left = parseInt($(this).css('left'));

            xself.MissionControlWindowInfo.push([idName, i, [top, left]]);

            GroupWindowArray.push([idName, i]);
            GroupWinList.push($(this));

            $(this).attr({
                'oldTop': parseInt($(this).css('top')),
                'oldLeft': parseInt($(this).css('left')),
                'oldWidth': parseInt($(this).css('width')),
                'oldHeight': parseInt($(this).css('height')),
                'oldZIndex': $(this).css('z-index')
            }).css({
                'position': 'relative',
                'top': 0,
                'left': 0,
                'box-shadow': '0px 0px 5px rgba(0,0,0,.6)',
                '-moz-box-shadow': '0px 0px 5px rgba(0,0,0,.6)',
                '-webkit-box-shadow': '0px 0px 5px rgba(0,0,0,.6)'
            });
            $(this).scale(1);
            // windows.eq(i).hide()

            if ($(this).hasClass('ui-resizable'))
            {
                $(this).resizable('disable');
            }
        });
        /*
         for (var i = 0; i < windows.length; i++) {
         
         var actWin = windows.eq(i);
         
         // positions
         var idName = $(actWin).attr('id');
         var top = parseInt($(actWin).css('top'));
         var left = parseInt($(actWin).css('left'));
         
         this.MissionControlWindowInfo.push([idName, i, [top, left]]);
         
         GroupWindowArray.push([idName, i]);
         GroupWinList.push($(actWin));
         
         actWin.attr({
         'oldTop': parseInt($(actWin).css('top')),
         'oldLeft': parseInt($(actWin).css('left')),
         'oldWidth': parseInt($(actWin).css('width')),
         'oldHeight': parseInt($(actWin).css('height')),
         'oldZIndex': $(actWin).css('z-index')
         }).css({
         'position': 'relative',
         'top': 0,
         'left': 0,
         'box-shadow': '0px 0px 5px rgba(0,0,0,.6)',
         '-moz-box-shadow': '0px 0px 5px rgba(0,0,0,.6)',
         '-webkit-box-shadow': '0px 0px 5px rgba(0,0,0,.6)'
         });
         actWin.scale(1);
         // windows.eq(i).hide()
         
         if ($(actWin).hasClass('ui-resizable'))
         {
         $(actWin).resizable('disable');
         }
         } */

        //   Tools.sleep(10);


        for (var i = 0; i < GroupWindowArray.length; i++) {
            $(GroupWinList).each(function ()
            {
                var idName = $(this).attr('id');

                var id = undefined;

                if (idName.substring(0, 1) == 'w' || (idName !== undefined && idName !== null)) {
                    id = idName;
                }

                if (id === GroupWindowArray[i][0]) {
                    if ($('#missioncontrol_' + GroupWindowArray[i][0]).length == 0) {
                        $('#' + GroupWindowArray[i][0] + '[activespace=' + Desktop.currentSpace + ']').wrapAll('<div class="MissionControl_GroupWindow" id="missioncontrol_' + GroupWindowArray[i][0] + '"></div>');
                    }
                }
            });
        }

        Tools.sleep(10);


        // var MissionControl_GroupWindows = $('.MissionControl_GroupWindow');



        var margeTopArray = [], gw = $('.MissionControl_GroupWindow');


        for (var i = 0; i < gw.length; i++) {

            //$('.MissionControl_GroupWindow').eq(i).css('height', (parseInt($('.MissionControl_GroupWindow').eq(i).css('height')) + 70)+'px')
            var mleft = 0;
            var mGroupWindow = gw.eq(i);
            var groupChildWins = $(mGroupWindow).children('div.isWindowContainer');
            var mx = $(mGroupWindow).children('div.isWindowContainer').length;


            for (var j = 0; j < mx; j++) {
                if (groupChildWins.length > 3) {

                    switch (j) {
                        case groupChildWins.length:
                            groupChildWins.eq(j).css({
                                'margin-left': 45,
                                'margin-top': -(parseInt(groupChildWins.eq(j - 1).height()) - 15) + 'px'
                            });
                            margeTopArray.push([i, [j, 15]]);
                            break;
                        case groupChildWins.length - 1:
                            groupChildWins.eq(j).css({
                                'margin-left': 30,
                                'margin-top': -(parseInt(groupChildWins.eq(j - 1).height()) - 15) + 'px'
                            });
                            margeTopArray.push([i, [j, 15]]);
                            break;
                        case groupChildWins.length - 2:
                            groupChildWins.eq(j).css({
                                'margin-left': 15,
                                'margin-top': -(parseInt(groupChildWins.eq(j - 1).height()) - 15) + 'px'
                            });
                            margeTopArray.push([i, [j, 15]]);
                            break;
                        default:
                            if (j > 0) {
                                groupChildWins.eq(j).css({
                                    'margin-left': mleft,
                                    'margin-top': -(parseInt(groupChildWins.eq(j - 1).height()) - 4) + 'px'
                                });
                                margeTopArray.push([i, [j, 4]]);

                            }
                            else {
                                groupChildWins.eq(j).css({
                                    'margin-left': 0,
                                    'margin-top': 0
                                });
                            }
                            break;
                    }

                }
                else {
                    if (j > 0) {
                        groupChildWins.eq(j).css({
                            'margin-left': mleft + 'px',
                            'margin-top': -(parseInt(groupChildWins.eq(j - 1).height()) - 15) + 'px'
                        });
                        margeTopArray.push([i, [j, 15]]);
                    }
                    else {
                        groupChildWins.eq(j).css({
                            'margin-left': 0,
                            'margin-top': 0
                        });
                    }
                    mleft += 15;
                }

            }


            $(mGroupWindow)
                    .height((parseInt(groupChildWins.eq(0).height())) + 90)
                    .width(groupChildWins.eq(0).width())
                    .addClass('initialWidth_' + (parseInt(groupChildWins.eq(0).width())))
                    .addClass('initialHeight_' + (parseInt(groupChildWins.eq(0).height())));
        }



        var PourcentageScale = 100;
        var Scale = 1.00;

        var desktopW = parseInt($('#desktop').width());
        var desktopH = parseInt($('#desktop').height());


        $('#desktop').css({
            'position': 'absolute',
            'top': ((window.innerHeight * 9) / 100) + 40, //MissionControlMainTop + 10,
            'left': 0,
            'width': window.innerWidth,
            'height': window.innerHeight - ((window.innerHeight * 9) / 100) + 30 // (window.innerHeight - MissionControlMainTop - 90)
        }).each(function () {
            var xx = 0;
            var SECURELOOP = 0, loop = $(this).children('.MissionControl_GroupWindow').length;

            while (xx < loop) {
                SECURELOOP++;
                if (SECURELOOP >= 100) {
                    console.error('SCR LP');
                    break;
                }
                var wi = parseInt($(this).children('.MissionControl_GroupWindow').eq(xx).css('width'));
                var he = parseInt($(this).children('.MissionControl_GroupWindow').eq(xx).css('height'));
                var el = $(this).children('.MissionControl_GroupWindow').eq(xx);

                //=============================
                var pleft = el.offset().left;
                var ptop = el.offset().top;
                var elWidthPlusOffsetLeft = wi + pleft;
                var elHeightPlusOffsetTop = he + ptop;
                //=============================

                if (elWidthPlusOffsetLeft > desktopW || elHeightPlusOffsetTop > desktopH) {
                    Scale -= 0.10;
                    PourcentageScale -= 10;
                    for (var j = 0; j < loop; j++) {
                        var classes = $(this).children('.MissionControl_GroupWindow').eq(j).attr('class');
                        var classesSplit = classes.split(' ');
                        var initialWidth = 1, initialHeight = 1;


                        for (var k = 0; k < classesSplit.length; k++)
                        {
                            //var clW = classesSplit[k].substring(0, 13);

                            if (classesSplit[k].substring(0, 13) == 'initialWidth_') {
                                initialWidth = parseInt(classesSplit[k].substring(13, classesSplit[k].length));
                            }

                            if (classesSplit[k].substring(0, 14) == 'initialHeight_') {
                                initialHeight = parseInt(classesSplit[k].substring(14, classesSplit[k].length));
                            }
                        }


                        var wii = parseInt($(this).children('.MissionControl_GroupWindow').eq(j).css('width'));
                        var hee = parseInt($(this).children('.MissionControl_GroupWindow').eq(j).css('height'));

                        var _w = ((initialWidth * PourcentageScale) / 100);
                        var _h = ((initialHeight * PourcentageScale) / 100);

                        $(this).children('.MissionControl_GroupWindow').eq(j).css({
                            'width': _w,
                            'height': _h
                        });
                    }



                    if (xx == 0)
                        xx = 0;
                    else
                        xx--;
                }
                else {
                    xx++;
                }
            }



            //Tools.sleep(20);

            for (var i = 0; i < loop; i++) {
                //var MissionControl_GroupWindows = $('.MissionControl_GroupWindow');
                var group = $('.MissionControl_GroupWindow').eq(i);
                //======== Group Margintop add ========
                /*var margtop = 0;
                 for(var j=0;j<margeTopArray.length;j++) {
                 //margtop += margeTopArray[i][1][0];
                 //console.log(margeTopArray[i][j][1])
                 //margtop += margeTopArray[i][j][1];
                 }
                 console.log(margeTopArray);
                 margtop = (margtop*PourcentageScale)/100;
                 $('.MissionControl_GroupWindow').eq(i).css('height', (parseInt($('.MissionControl_GroupWindow').eq(i).css('height')) + margtop)+'px');
                 */


                //======== Window Code Position Scale ========
                var add2 = 0, cwin = group.children('.isWindowContainer');
                for (var j = 0; j < cwin.length; j++) {

                    var length = cwin.length;
                    var win_ = cwin.eq(j);
                    var width = parseInt(win_.width());
                    var height = parseInt(win_.height());
                    var marginleft = parseInt(win_.css('margin-left'));
                    var margintop = parseInt(win_.css('margin-top'));
                    var winTop = (((height * PourcentageScale) / 100) - height) / 2;
                    var winLeft = (((width * PourcentageScale) / 100) - width) / 2;
                    var winMarLeft = (marginleft * PourcentageScale) / 100;
                    var winMarTop = (margintop * PourcentageScale) / 100;
                    var cTop = 0, cLeft = 0;



                    windowZIndex++;
                    win_.css({
                        'display': 'block',
                        'top': winTop + 'px',
                        'left': winLeft + 'px',
                        'margin-left': winMarLeft + 'px',
                        'z-index': windowZIndex
                    }).scale(Scale);

                    var zoomScale = false;
                    if (xself.missionControlZoomActived)
                    {
                        if (Scale < 0.8999999)
                        {
                            zoomScale = 0.89999990;
                        }
                    }



                    if (length > 4) {
                        var add = 4 * (length - 3);
                        switch (j) {
                            case length - 1:
                                cTop = (group.offset().top + margintop) + height + 30 + add2 - 12;
                                cLeft = group.offset().left + winMarLeft;
                                break;
                            case length - 2:
                                cTop = (group.offset().top + margintop) + height + 15 + add2 - 12;
                                cLeft = group.offset().left + winMarLeft;
                                break;
                            case length - 3:
                                cTop = (group.offset().top + margintop) + height + add2;
                                cLeft = group.offset().left + winMarLeft;
                                break;
                            default:
                                if (j == 0) {
                                    cTop = margintop + group.offset().top;
                                    cLeft = group.offset().left;
                                }
                                else {

                                    cTop = (group.offset().top + margintop) + height + add2;
                                    cLeft = group.offset().left;
                                    add2 += 4;
                                }
                                break;
                        }
                    }
                    else {
                        if (length == 4) {
                            switch (j) {
                                case 0:
                                    cTop = margintop + group.offset().top;
                                    cLeft = group.offset().left;
                                    break;
                                case 1:
                                    cTop = (group.offset().top + margintop) + height;
                                    cLeft = group.offset().left + winMarLeft;
                                    break;
                                case 2:
                                    cTop = (group.offset().top + margintop) + height + 4;
                                    cLeft = group.offset().left + winMarLeft;
                                    break;
                                case 3:
                                    cTop = (group.offset().top + margintop) + height + 15 + 4;
                                    cLeft = group.offset().left + winMarLeft;
                                    break;
                            }
                        }
                        else {
                            switch (j) {
                                case 0:
                                    cTop = margintop + group.offset().top;
                                    cLeft = group.offset().left;
                                    break;
                                case 1:
                                    cTop = (group.offset().top + margintop) + height;
                                    cLeft = group.offset().left + winMarLeft;
                                    break;
                                case 2:
                                    cTop = (group.offset().top + margintop) + height + 15;
                                    cLeft = group.offset().left + winMarLeft;
                                    break;
                            }
                        }
                    }




                    var cache = $('<div>').css({
                        'z-index': (windowZIndex + j + 10),
                        'top': cTop + 'px',
                        'left': cLeft + 'px',
                        'width': ((width * PourcentageScale) / 100) + 'px',
                        'height': ((height * PourcentageScale) / 100) + 'px'
                    }).attr({
                        'class': 'MissionControlCache',
                        'attachedGroup': '' + i,
                        'attachedWindow': '' + j
                    }).hide();

                    cache.bind('mouseover', function () {

                        if (xself.missionControlZoomActived == false) {
                            $('.MissionControlCache').removeClass('MissionControlCacheHover');
                            $(this).addClass('MissionControlCacheHover');
                        }
                        else
                        {
                            $('.MissionControlCache').removeClass('MissionControlCacheHover');
                        }

                        $t = $(this);
                        var keypressed = false;

                        if (!xself.missionControlKeyBoardShortcut)
                        {
                            xself.missionControlKeyBoardShortcut = true;

                            $('body').keydown(function (eventKeyBoard)
                            {
                                if (self.missionControlActived && !keypressed) {
                                    keypressed = true;
                                    var touche = window.eventKeyBoard ? eventKeyBoard.keyCode : eventKeyBoard.which;

                                    if (touche == 32) {
                                        window.scrollBy(0, 0);
                                        self.missionControlZoomActived = true;
                                        self.missionControlKeyBoardShortcut = true;
                                        var MCGroup = parseInt($t.attr('attachedGroup')), MCWindow = parseInt($t.attr('attachedWindow'));
                                        $currentWindow = $('.MissionControl_GroupWindow').eq(MCGroup).children('.isWindowContainer').eq(MCWindow);

                                        $('#MissionControlIcns_' + $currentWindow.attr('id') + ', #MissioncontrolIcnsText_' + $currentWindow.attr('id')).hide();


                                        $('.MissionControl_GroupWindow').eq(MCGroup).children('.isWindowContainer').hide();
                                        var CWTop = 0 - 20; // 20 étant le margin d'en haut

                                        $currentWindow.attr({
                                            'oldStyle': Base64.encode($currentWindow.attr('style'))
                                        }).css({
                                            'position': 'absolute',
                                            'z-index': $currentWindow.css('z-index') + 10000,
                                            'top': CWTop + 'px',
                                            'left': '0px',
                                            'margin-top': '0px',
                                            'margin-left': '0px',
                                            'visibility': 'visible'
                                        });

                                        if (parseInt($currentWindow.css('width')) > window.innerWidth || parseInt($currentWindow.css('height')) > (window.innerHeight - 80)) {
                                            FinalTop = (((window.innerHeight / 2) - (((parseInt($currentWindow.css('height')) * (PourcentageScale + 10)) / 100) / 2)) - $('.MissionControl_GroupWindow').eq(MCGroup).offset().top);
                                            FinalLeft = ((window.innerWidth / 2) - (((parseInt($currentWindow.css('width')) * (PourcentageScale + 10)) / 100) / 2));
                                            Scale2 = PourcentageScale + 0.10;
                                        }
                                        else {
                                            FinalTop = (((window.innerHeight / 2) - (((parseInt($currentWindow.css('height')) * 100) / 100) / 2)) - $('.MissionControl_GroupWindow').eq(MCGroup).offset().top);
                                            FinalLeft = ((window.innerWidth / 2) - (((parseInt($currentWindow.css('width')) * 100) / 100) / 2));
                                            Scale2 = '1';
                                        }

                                        $currentWindow.animate({
                                            'visibility': 'visible',
                                            'top': FinalTop + 'px',
                                            'left': FinalLeft + 'px',
                                            'scale': Scale2
                                        }, 250);
                                    }
                                }
                            });



                            $('body').keyup(function ()
                            {
                                if (xself.missionControlActived && keypressed)
                                {
                                    xself.missionControlKeyBoardShortcut = true;
                                    xself.missionControlZoomActived = false;
                                    var MCGroup = parseInt($t.attr('attachedGroup')), MCWindow = parseInt($t.attr('attachedWindow'));
                                    $currentWindow = $('.MissionControl_GroupWindow').eq(MCGroup).children('.windows').eq(MCWindow);
                                    $('#MissionControlIcns_' + $currentWindow.attr('id') + ', #MissioncontrolIcnsText_' + $currentWindow.attr('id')).show()
                                    $currentWindow.css('visibility', 'visible').attr('style', Base64.decode($currentWindow.attr('oldStyle')));
                                    $('.MissionControl_GroupWindow').eq(MCGroup).children('.isWindowContainer').css('visibility', 'visible');
                                    keypressed = false;
                                }
                            });
                        }
                    })

                    cache.bind('mouseleave', function () {
                        $(this).removeClass('MissionControlCacheHover');
                    });


                    cache.click(function (e) {
                        var MCGroup = parseInt($(this).attr('attachedGroup')), MCWindow = parseInt($(this).attr('attachedWindow'));
                        var idx, CurrentWindowUniqueID = $('.MissionControl_GroupWindow').eq(MCGroup).children('.isWindowContainer').eq(MCWindow).attr('id');
                        xself.MissionControlRemove(CurrentWindowUniqueID, idx);
                        return false;
                    });

                    $('#fullscreenContainer').append(cache);
                }



                //======== Icns App Code Group ========
                var windowID = $('.MissionControl_GroupWindow').eq(i).attr('id').replace('missioncontrol_', '');
                var controller = $('#' + windowID).attr('app');

                //var APPPath = WhatPathForThisApp(app);
                var icnsTop = parseInt($('.MissionControl_GroupWindow').eq(i).css('height')) + $('.MissionControl_GroupWindow').eq(i).offset().top;
                var icnsLeft = (parseInt($('.MissionControl_GroupWindow').eq(i).css('width')) + $('.MissionControl_GroupWindow').eq(i).offset().left) - (parseInt($('.MissionControl_GroupWindow').eq(i).css('width')) / 2);

                var icnsT = ((icnsTop - (((128 * (PourcentageScale - 10)) / 100)))) - 30;
                var icnsL = (icnsLeft - (((128 * (PourcentageScale - 10)) / 100) / 2));

                var icnsSrc = $('#' + windowID).find('.window-titlebar .title img').attr('src');


                var dat = $('#' + windowID).data('WindowManager');
                if (dat.settings.isAddon)
                {
                    if (dat.settings.URL) {
                        controller = dat.settings.URL.replace(/.*plugin=([a-zA-Z0-9_]+)/ig, '$1');
                    }
                }


                if (controller)
                {
                    var icsSpan = $('<span/>').attr({
                        'id': 'MissionControlIcns_' + windowID,
                        'class': 'MissioncontrolIcns'
                    }).css({
                        'text-align': 'center',
                        'width': ((128 * (PourcentageScale - 10)) / 100) + 'px',
                        'height': ((128 * (PourcentageScale - 10)) / 100) + 'px',
                        'position': 'absolute',
                        'top': icnsT,
                        'left': icnsL,
                        'z-index': 99999
                    }).hide();


                    var icns = $('<img/>').attr({
                        'src': Application.getAppIcon(controller, 128, (typeof dat.settings.isAddon != 'undefined' ? dat.settings.isAddon : false))
                    }).css({
                        'width': ((128 * (PourcentageScale - 10)) / 100) + 'px',
                        'height': ((128 * (PourcentageScale - 10)) / 100) + 'px',
                        'position': 'relative',
                        'z-index': 99999
                    });


                    icsSpan.append(icns);



                    $('#fullscreenContainer').append(icsSpan);
                }

                var appTitle = $('#' + windowID).find('.win-title').text();
                var icnsText = $('<span/>').attr('class', 'MissioncontrolIcnsText').html(appTitle);


                var textWidth = xself.getTextWidth(appTitle);


                var top = parseInt(icnsT) + ((128 * (PourcentageScale - 10)) / 100);
                var left = parseInt(icnsLeft) - (textWidth / 2);

                icnsText.css({
                    'display': 'block',
                    'position': 'absolute',
                    'top': top,
                    'left': left,
                    'z-index': 99999
                }).attr('id', 'MissioncontrolIcnsText_' + windowID).hide();


                $('#fullscreenContainer').append(icnsText);

                // var ClientWidth = icnsText.get(0).clientWidth / 2;
                // icnsText.css('left', (parseInt(icnsText.css('left')) - ClientWidth))


            } // end for(var i=0;i<loop;i++) {



            $('#missionControlLoad').css('display', 'none').remove();
        });


        Tools.sleep(10);

        $('#fullscreenContainer .MissionControlCache,#fullscreenContainer .MissioncontrolIcnsText,#fullscreenContainer .MissioncontrolIcns').fadeIn(400);

        $('#desktop').unbind('click.missioncontrol').bind('click.missioncontrol', function (e) {
            if (xself.missionControlActived)
                xself.MissionControlRemove();
        });


        elapsedTime = new Date().getTime() - MCstartTime;
        console.info('Time to create Mission Control: ' + elapsedTime + 'ms');


        return true;

    },
    getTextWidth: function (text)
    {
        var txtObj = $('<span id="rem-text" class="MissioncontrolIcnsText"/>').html(text);
        $('body').append(txtObj);
        var width = txtObj.width();
        txtObj.remove();
        return width;
    },
    SpacesStart: function ()
    {
        var xself = this;

        if (!this.spacesFirstLaunch)
        {
            this.spacesFirstLaunch = true;


            var spacesMaximumDesktop = 4;
            var spacesLength = $('#spacesMiniContainer').children('center').children('.spacesMiniSpace').length - 1;
            var SpaceMiniContainer = $('#spacesMiniContainer');
            $('#spacesMiniContainer').css({
                'width': window.innerWidth,
                'height': ((window.innerHeight * 9) / 100) + 30,
                'display': 'block'
            });

            // ======== Create New Desktop Code ========
            var attrDesktopBackground = $('#desktop-bg').attr('src'), spaces_plusTop = ((((window.innerHeight * 9) / 100) / 2) - (23 / 2)), spaces_plusLeft = ((((window.innerWidth * 10) / 100) / 4) - (24 / 2));
            var createNewDesktopOverZone = $('<div></div>').attr('class', 'spacesCreateNewDesktopOverZone').css({
                'height': ((window.innerHeight * 9) / 100) + 30
            });
            var spacesCreateNewDesktopButtonBlackZone = $('<div></div>').attr('class', 'spacesCreateNewDesktopButtonBlackZone').css({
                'width': (((window.innerWidth * 10) / 100) / 2) + 'px',
                'height': (((window.innerHeight * 9) / 100) + 30) + 'px',
                'display': 'none'
            });
            var createNewDesktopButton = $('<div></div>').attr('class', 'spacesCreateNewDesktopButton').css({
                'width': (((window.innerWidth * 10) / 100) / 2) + 'px',
                'height': ((window.innerHeight * 9) / 100) + 'px',
                'right': '-' + ((window.innerWidth * 10) / 100) + 50 + 'px'
            }).html('<img src="' + attrDesktopBackground + '" alt="" style="position:absolute;top:0px;left:0px;width:' + ((window.innerWidth * 10) / 100) + 'px;height:' + ((window.innerHeight * 9) / 100) + 'px;"/>' +
                    '<span style="position:absolute;top:' + spaces_plusTop + 'px;left:' + spaces_plusLeft + 'px;z-index:2;" class="add-desktop" />');


            Tools.sleep(5);

            createNewDesktopOverZone.hover(function () {
                createNewDesktopButton.animate({
                    'right': '0px'
                }, 200, 'easeOutQuad');
            }, function () {
                createNewDesktopButton.animate({
                    'right': '-' + ((window.innerHeight * 9) / 100) + 50 + 'px'
                }, 200, 'easeOutQuad');
                spacesCreateNewDesktopButtonBlackZone.css('display', 'none'); // Au cas ou
            }).append(createNewDesktopButton).append(spacesCreateNewDesktopButtonBlackZone);

            createNewDesktopOverZone.mousedown(function () {
                spacesCreateNewDesktopButtonBlackZone.css('display', 'block');
            });

            createNewDesktopOverZone.mouseup(function () {
                spacesCreateNewDesktopButtonBlackZone.css('display', 'none');
            });

            createNewDesktopButton.click(function () {
                if (spacesLength < spacesMaximumDesktop && Desktop.spacesActualLength < Desktop.maxSpaces) {
                    Desktop.spacesActualLength++;
                    var newSpaces = $('<div></div>').attr('class', 'spacesMiniSpace').css({
                        'width': '0px',
                        'height': ((window.innerHeight * 9) / 100) + 30 + 'px',
                        'display': 'inline-block'
                    });

                    SpaceMiniContainer.children('center').append(newSpaces);

                    newSpaces.animate({
                        'width': ((window.innerWidth * 10) / 100) + 4 + 'px'
                    }, 500, function () {
                        var offsetLeft = $(this).offset().left;
                        var left = window.innerWidth + ((window.innerHeight * 9) / 100) + 50;
                        var volatileElement = $('<div/>').html('<img src="' + attrDesktopBackground + '" alt="" style="position:absolute;top:0px;left:0px;width:' + ((window.innerWidth * 10) / 100) + 'px;height:' + ((window.innerHeight * 9) / 100) + 'px;"/>').css({
                            'position': 'absolute',
                            'left': left + 'px',
                            'top': '10px',
                            'width': ((window.innerWidth * 10) / 100) + 'px',
                            'height': ((window.innerHeight * 9) / 100) + 'px',
                            'box-shadow': '0px 10px 35px rgba(0,0,0,.6), 0px 0px 5px rgba(0,0,0,.2)',
                            '-moz-box-shadow': '0px 10px 35px rgba(0,0,0,.6), 0px 0px 5px rgba(0,0,0,.2)',
                            '-webkit-box-shadow': '0px 10px 35px rgba(0,0,0,.6), 0px 0px 5px rgba(0,0,0,.2)'
                        }).animate({
                            'left': offsetLeft + 'px'
                        }, 500, function () {

                            var interval;

                            newSpaces.animate({
                                'width': '0px'
                            }, 500).remove();


                            var miniSpaceContainer = $('<div></div>').attr({
                                'class': 'spacesMiniSpace',
                                'spaceNumber': '' + Desktop.spacesActualLength + ''
                            }).addClass('spacesMiniDesktop').css({
                                'width': ((window.innerWidth * 10) / 100) + 4 + 'px',
                                'height': ((window.innerHeight * 9) / 100) + 30 + 'px',
                                'display': 'inline-block'
                            });

                            miniSpaceContainer.on('mouseover', function () {
                                var th = $(this);
                                interval = setTimeout(function () {
                                    var offleft = th.offset().left, offtop = th.offset().top, t = th;
                                    var close = $('<div />').attr('class', 'spacesClose').css({
                                        'top': (offtop - 10) + 'px',
                                        'left': (offleft - 12.5) + 'px',
                                        'display': 'block',
                                        'opacity': '0'
                                    }).html('<span class="spaces_closebox"></span>');

                                    if (th.children('.spacesClose').length == 0)
                                        th.append(close);
                                    close.animate({
                                        'opacity': '1'
                                    }, 500);


                                    close.mouseup(function () {
                                        xself.SpacesDestroySpace(t);
                                    });

                                }, 500);
                            });

                            miniSpaceContainer.on('mouseleave', function () {
                                clearTimeout(interval);
                                $(this).children('.spacesClose').animate({
                                    'opacity': '0'
                                }, 500).remove();
                            });

                            // ======== Change Space ========
                            miniSpaceContainer.click(function () {
                                xself.SpacesChangeSpace($(this));
                            }).mousedown(function () {
                                $(this).css('opacity', '0.60');
                            }).mouseup(function () {
                                $(this).css('opacity', '1');
                            });


                            var spacesMiniWinContainer = $('<div></div>').css({
                                //'border': '2px solid transparent',
                                'width': ((window.innerWidth * 10) / 100) + 'px',
                                'height': ((window.innerHeight * 9) / 100) + 'px'
                            }).attr('class', 'spacesMiniWin').append('<img class="spacesMiniWinWallpaper" style="width:' + ((window.innerWidth * 10) / 100) + 'px;height:' + ((window.innerHeight * 9) / 100) + 'px;" src="' + $('#desktop-bg').attr('src') + '" onmousedown="if (event.preventDefault) event.preventDefault()"/>');

                            // SPAN
                            var spaceMiniScaceSpanContainer = $('<span></span>').css({
                                'margin-top': ((window.innerHeight * 9) / 100) + 12 + 'px',
                                'width': ((window.innerWidth * 10) / 100) + 'px'
                            }).attr('class', 'spaceMiniSpanTitle').html('Container');


                            miniSpaceContainer.append(spacesMiniWinContainer).append(spaceMiniScaceSpanContainer)
                            SpaceMiniContainer.children('center').append(miniSpaceContainer)

                            volatileElement.remove();

                            SpaceMiniContainer.disableSelection();

                            xself.SpacesActualizeDesktopName();
                            xself.SpacesActualizeMiniWinSize();

                        });
                        $('#fullscreenContainer').append(volatileElement);

                    });

                }
            });

            SpaceMiniContainer.append(createNewDesktopOverZone);

            Tools.sleep(10);


            // ============================================================


            // ======== Dashboard MiniSpace ========
            var miniSpaceDashBoard = $('<div></div>').attr('class', 'spacesMiniSpace').css({
                'width': ((window.innerWidth * 10) / 100) + 4 + 'px',
                'height': ((window.innerHeight * 9) / 100) + 30 + 'px',
                'display': 'inline-block'
            }).addClass('mini-Space-DashBoard');

            var spacesMiniWinDashBoard = $('<div></div>').css({
                'border': '2px solid transparent',
                'width': ((window.innerWidth * 10) / 100) + 'px',
                'height': ((window.innerHeight * 9) / 100) + 'px'
            }).attr('class', 'spacesMiniWin').mousedown(function (event) {
                if (event.preventDefault)
                    event.preventDefault();


                Dashboard.show();
                //DashboardStart();
            });



            // SPAN
            var spaceMiniScaceSpanDashBoard = $('<span></span>').css({
                'margin-top': ((window.innerHeight * 9) / 100) + 12 + 'px',
                'width': ((window.innerWidth * 10) / 100) + 'px'
            }).attr('class', 'spaceMiniSpanTitle').html('Dashboard');


            miniSpaceDashBoard.append(spacesMiniWinDashBoard).append(spaceMiniScaceSpanDashBoard);
            SpaceMiniContainer.children('center').append(miniSpaceDashBoard);
            // ============================================================


            // ======== 1 MiniSpace ========
            var miniSpaceContainer1 = $('<div></div>').attr({
                'class': 'spacesMiniSpace',
                'spaceNumber': '1'
            }).addClass('spacesMiniDesktop').css({
                'width': ((window.innerWidth * 10) / 100) + 4 + 'px',
                'height': ((window.innerHeight * 9) / 100) + 30 + 'px',
                'display': 'inline-block'
            });



            Tools.sleep(10);

            var spacesMiniWinContainer1 = $('<div></div>').css({
                'border': '2px solid #efefef',
                'width': ((window.innerWidth * 10) / 100) + 'px',
                'height': ((window.innerHeight * 9) / 100) + 'px'
            }).attr('class', 'spacesMiniWin').append('<img class="spacesMiniWinWallpaper" style="width:' + ((window.innerWidth * 10) / 100) + 'px;height:' + ((window.innerHeight * 9) / 100) + 'px;" src="' + $('#desktop-bg').attr('src') + '" onmousedown="if (event.preventDefault) event.preventDefault()"/>');

            Tools.sleep(10);


            for (var i = 0; i < $('.windows').length; i++) {
                var top = $('.windows').eq(i).attr('oldTop');
                var left = $('.windows').eq(i).attr('oldLeft');
                var width = $('.windows').eq(i).attr('oldWidth');
                var height = $('.windows').eq(i).attr('oldHeight');
                var zindex = $('.windows').eq(i).attr('oldZIndex');
                var wmini = ((window.innerWidth * 10) / 100);
                var hmini = ((window.innerHeight * 9) / 100);

                var w_width = (wmini * (width / window.innerWidth));
                var w_height = (hmini * (height / window.innerHeight));
                var w_top = (hmini * (top / window.innerHeight));
                var w_left = (wmini * (left / window.innerWidth));

                var w = $('<div></div>').css({
                    'top': w_top + 'px',
                    'left': w_left + 'px',
                    'width': w_width + 'px',
                    'height': w_height + 'px',
                    'z-index': zindex,
                    'position': 'absolute',
                    'background': '#ededed',
                    'box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18)',
                    '-moz-box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18)',
                    '-webkit-box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18)'
                });

                spacesMiniWinContainer1.append(w);
            }


            // SPAN
            var spaceMiniScaceSpanContainer1 = $('<span></span>').css({
                'margin-top': ((window.innerHeight * 9) / 100) + 12 + 'px',
                'width': ((window.innerWidth * 10) / 100) + 'px'
            }).attr('class', 'spaceMiniSpanTitle').html('Schreibtisch');

            miniSpaceContainer1.append(spacesMiniWinContainer1).append(spaceMiniScaceSpanContainer1);

            // ======== Change Space ========
            miniSpaceContainer1.click(function () {
                xself.SpacesChangeSpace($(this))
            }).mousedown(function () {
                $(this).css('opacity', '0.60');
            }).mouseup(function () {
                $(this).css('opacity', '1');
            });

            SpaceMiniContainer.children('center').append(miniSpaceContainer1);
            // ============================================================
        }
        else {

            $('#spacesMiniContainer, .spacesCreateNewDesktopOverZone').css('display', 'block');
            var attrDesktopBackground = $('img#desktop-bg').attr('src');

            $('#spacesMiniContainer').find('.spacesMiniWinWallpaper').attr('src', attrDesktopBackground);


            $('.spacesMiniSpace').css({
                'width': ((window.innerWidth * 10) / 100) + 4 + 'px',
                'height': ((window.innerHeight * 9) / 100) + 30 + 'px'
            });
            $('.spacesMiniWin').css({
                'width': ((window.innerWidth * 10) / 100) + 'px',
                'height': ((window.innerHeight * 9) / 100) + 'px'
            }).children('img.spacesMiniWinWallpaper').css({
                'width': ((window.innerWidth * 10) / 100) + 'px',
                'height': ((window.innerHeight * 9) / 100) + 'px'
            });
            $('.spaceMiniSpanTitle').css({
                'top': ((window.innerHeight * 9) / 100) + 20 + 'px',
                'width': ((window.innerWidth * 10) / 100) + 'px'
            });
            // ============================================================
            xself.SpacesActualizeMiniWinSize();
        }


        $('.tinymce-editor', $('#desktop .isWindowContainer[activespace=' + Desktop.currentSpace + ']:visible')).each(function () {

            console.log('activate in SpacesActualizeSpaceLocation editor: ' + $(this).attr('name'));
            var e = this;
            Doc.loadTinyMceConfig(
                    $('.isWindowContainer[activespace=' + Desktop.currentSpace + ']'), function () {
                $(e).removeClass('loaded');
                Doc.loadTinyMce($('.isWindowContainer[activespace=' + Desktop.currentSpace + ']:visible'));
                console.log('id editors: ' + $(e).attr('name') + ' loaded');
            });
        });



    },
    SpacesActualizeMiniWinSize: function () {
        console.log('start SpacesActualizeMiniWinSize');

        var $miniDesktops = $('.spacesMiniDesktop');

        for (var i = 0; i < $miniDesktops.length; i++)
        {
            $miniDesktops.eq(i).children('.spacesMiniWin').children('div').remove();

            var windows = $('.isWindowContainer[activespace=' + (i + 1) + ']');


            for (var j = 0; j < windows.length; j++) {

                var _w = windows.eq(j);


                if (_w.attr('oldTop')) {
                    var top = _w.attr('oldTop');
                    var left = _w.attr('oldLeft');
                    var width = _w.attr('oldWidth');
                    var height = _w.attr('oldHeight');
                    var zindex = _w.attr('oldZIndex');
                }
                else {
                    var top = parseInt(_w.css('top'));
                    var left = parseInt(_w.css('left'));
                    var width = parseInt(_w.css('width'));
                    var height = parseInt(_w.css('height'));
                    var zindex = _w.css('z-index');
                }

                var wmini = ((window.innerWidth * 10) / 100);
                var hmini = ((window.innerHeight * 9) / 100);
                var w_width = (wmini * (width / window.innerWidth));
                var w_height = (hmini * (height / window.innerHeight));
                var w_top = (hmini * (top / window.innerHeight));
                var w_left = (wmini * (left / window.innerWidth));

                var w = $('<div></div>').css({
                    'top': w_top + 'px',
                    'left': w_left + 'px',
                    'width': w_width + 'px',
                    'height': w_height + 'px',
                    'z-index': zindex,
                    'position': 'absolute',
                    'background': '#ededed',
                    'box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18)',
                    '-moz-box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18)',
                    '-webkit-box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18)'
                });

                $miniDesktops.eq(i).children('.spacesMiniWin').append(w);

            }
        }
    },
    SpacesActualizeDesktopName: function () {


        var $mini = $('.spacesMiniSpace');

        if ($mini.length > 2) {
            for (var i = 1; i < $mini.length; i++) {
                $mini.eq(i).children('.spaceMiniSpanTitle').html('Schreibtisch ' + i);
            }
        }
        else {
            $mini.eq(1).children('.spaceMiniSpanTitle').html('Schreibtisch')
        }
    },
    SpacesChangeSpace: function (t) {
        $space = t;
        var xself = this, spaceNumber = $space.attr('spacenumber');

        if (Desktop.currentSpace != spaceNumber) {

            var spaceTop = $space.offset().top;
            var spaceLeft = $space.offset().left;
            var wallWidth = parseInt($space.children('.spacesMiniWin').children('img.spacesMiniWinWallpaper').width(), 10);
            var wallHeight = parseInt($space.children('.spacesMiniWin').children('img.spacesMiniWinWallpaper').height(), 10);


            wallWidth = $('#desktop-bg').width();
            wallHeight = $('#desktop-bg').height();
            spaceTop = $('#desktop-bg').offset().top;
            spaceLeft = $('#desktop-bg').offset().left;

            var wallSrc = $space.children('.spacesMiniWin').children('img.spacesMiniWinWallpaper').attr('src');
            var imgVolatile = $('<img />').css({
                'position': 'absolute',
                'top': spaceTop,
                'left': spaceLeft,
                'width': wallWidth,
                'height': wallHeight,
                'z-index': '1000001'
            }).attr('src', wallSrc);

            $('#desktop-container').append(imgVolatile);

            imgVolatile.animate({
                'top': 0,
                'left': 0,
                'width': window.innerWidth,
                'height': window.innerHeight
            }, {
                duration: 350,
                complete: function () {


                    xself.MissionControlRemove();
                    $(this).fadeOut(600, function () {
                        $(this).remove();
                    });
                    Desktop.currentSpace = spaceNumber;

                    $('.isWindowContainer,#TaskbarButtons .Taskbar-Item').hide();


                    var id = $('.isWindowContainer[activespace=' + spaceNumber + ']').attr('id');
                    $('.isWindowContainer[activespace=' + spaceNumber + '],#Taskbar' + id).show();

                    $('.spacesMiniSpace').children('.spacesMiniWin').css('border', 'none');
                    $space.children('.spacesMiniWin').css('border', '2px solid #efefef');

                    var zindex = [];
                    for (var i = 0; i < $('.isWindowContainer[activespace=' + spaceNumber + ']').length; i++) {
                        zindex.push(parseInt($('.isWindowContainer[activespace=' + spaceNumber + ']').eq(i).css('z-index')));
                    }

                    zindex.sort();
                    zindex.reverse();

                    var windows = $('.isWindowContainer[activespace=' + spaceNumber + ']');

                    for (var i = 0; i < windows.length; i++)
                    {
                        if (windows.eq(i).css('z-index') == zindex[0]) {
                            windows.eq(i).css('z-index', zindex[0] + 1);
                        }
                    }
                }
            });

        }
    },
    SpacesActualizeSpaceLocation: function () {
        $('.isWindowContainer').hide();
        $('.isWindowContainer[activespace=' + Desktop.currentSpace + ']').show();
    },
    SpacesChangeSpaceTo: function (w) {

        $win = w;
        var spaceAllocated = $win.attr('activespace');

        if (spaceAllocated != Desktop.currentSpace)
        {
            this.MissionControlRemove();

            $('.isWindowContainer').hide();
            $('.isWindowContainer[activespace=' + spaceAllocated + ']').hide();

            $('.spacesMiniSpace').children('.spacesMiniWin').css('border', 'none');
            $('.spacesMiniSpace[spaceNumber=' + spaceAllocated + ']').css('border', '2px solid #efefef');
        }
    },
    SpacesDestroySpace: function (t) {

        $space = t;
        var spaceNumber = $space.attr('spacenumber');
        if (spaceNumber > 1) {
            var windowsInSpace = $('.isWindowContainer[activespace=' + spaceNumber + ']');
            for (var i = 0; i < windowsInSpace.length; i++) {
                console.log(windowsInSpace.eq(i).attr('activespace', '1'));
            }
        }

        $space.remove();

        Desktop.spacesActualLength--;
        Desktop.currentSpace = 1;

        $('.spacesMiniSpace').children('.spacesMiniWin').css('border', 'none');
        $('.spacesMiniSpace[spaceNumber=1]').children('.spacesMiniWin').css('border', '2px solid #efefef');

        this.SpacesActualizeSpaceLocation();
        this.MissionControlRemove();
    },
    // disable image dragging
    disableDragging: function (e) {
        e.preventDefault();
    }

};


// register onLoad event with anonymous function
window.onload = function (e) {
    var evt = e || window.event, // define event (cross browser)
            imgs, // images collection
            i;                      // used in local loop
    // if preventDefault exists, then define onmousedown event handlers
    if (evt.preventDefault) {
        // collect all images on the page
        imgs = document.getElementsByTagName('img');
        // loop through fetched images
        for (i = 0; i < imgs.length; i++) {
            // and define onmousedown event handler
            imgs[i].onmousedown = MissionControl.disableDragging;
        }
    }
};



var missionControlActived = false, spacesFirstLaunch = false, missionControlZoomActived = false, missionControlKeyBoardShortcut = false;
var MissionControlWindowInfo = [];

function MissionControlRemove () {
    if (!missionControlActived)
        return false

    $('.MissioncontrolIcns,.MissioncontrolIcnsText,.MissionControlCache').remove();
    $('#missioncontrol-bg,#spacesMiniContainer,.spacesCreateNewDesktopOverZone').hide();

    $('#desktop-bg').css({
        position: 'absolute',
        top: '0px',
        left: '0px',
        width: window.innerWidth,
        height: window.innerHeight
    }).show();

    $('#desktop').unbind('click.missioncontrol').css({
        'top': '0',
        'left': '0',
        'width': '100%',
        'height': '100%'
    }).show();


    $('#Taskbar,#DesktopIcons,#dock').show();

    if ($('#desktop-side-panel').attr('baseWidth') !== null)
    {
        $('#desktop-container').css('left', $('#desktop-container').attr('basePos')).removeAttr('basePos');
        ;
        $('#desktop-side-panel').width($('#desktop-side-panel').attr('baseWidth')).removeAttr('baseWidth').show();
    }

    if ($('#Sidepanel').attr('reopen') == 1)
    {
        $('#Sidepanel').removeAttr('reopen').show();
    }

    if ($('#gui-console').attr('reopen') == 1)
    {
        $('#gui-console').removeAttr('reopen').show();
    }

    Tools.sleep(10);


    var spaceWindows = $('#desktop').find('.isWindowContainer[activespace="' + Desktop.currentSpace + '"]:visible'), total = spaceWindows.length;


    spaceWindows.each(function (i) {
        var self = this;
        var top = $(this).attr('oldTop');
        var left = $(this).attr('oldLeft');

        if ($(this).parent().hasClass('MissionControl_GroupWindow') && $('.tinymce-editor', $(this)).length == 0) {
            var parentID = $(this).parent().attr('id');
            Debug.log('id to unwrap without editors: ' + parentID);

            $(this).unwrap();
        }

        console.log('reset position : ' + $(this).attr('id'));

        $(this).scale(1).css({
            position: 'absolute',
            top: top,
            left: left,
            'margin-top': '',
            'margin-left': '',
            'box-shadow': '',
            '-moz-box-shadow': '',
            '-webkit-box-shadow': ''
        }).
                removeAttr('oldStyle').
                removeAttr('oldTop').
                removeAttr('oldLeft').
                removeAttr('oldWidth').
                removeAttr('oldHeight').
                removeAttr('oldZIndex');

        if ($(this).parent().hasClass('MissionControl_GroupWindow') && $('.tinymce-editor', $(this)).length > 0) {

            Debug.log('id to unwrap with editors: ' + $(this).parent().attr('id'));

            $(this).unwrap();

            $('.tinymce-editor', $(this)).each(function () {

                Debug.log('id editors: ' + $(this).attr('name'));

                var e = this;

                Doc.loadTinyMceConfig(
                        $(self), function () {


                    $(e).removeClass('loaded');
                    Doc.loadTinyMce($(self));
                    console.log('id editors: ' + $(e).attr('name') + ' loaded');
                }
                );
            });
        }



        // enable resizeable if exists
        if ($(this).hasClass('ui-resizable')) {
            $(this).resizable('enable');
        }

        if (i >= total - 1)
        {
            // Now activate the clicked window
            var args = arguments;
            if (typeof args[0] != 'undefined')
            {
                var activateWin = $('.isWindowContainer[uniqueid="' + args[0] + '"]')

                if (activateWin.length === 1)
                {
                    setTimeout(function () {
                        Debug.log('focus uniqueid in ' + activateWin.attr('uniqueid'));
                        if (activateWin.data('WindowManager'))
                        {
                            activateWin.click();
                            setTimeout(function () {
                                activateWin.data('WindowManager').focus();
                                activateWin.addClass('active');
                            }, 100);
                        }
                    }, 10);
                }
            }

        }
    });

    Tools.sleep(50);

    MissionControlWindowInfo = [];
    missionControlActived = false;
    return true;

}




function MissionControlStart () {
    if (missionControlActived)
    {
        return false;
    }

    var MCstartTime = new Date().getTime();
    var elapsedTime = 0, windowZIndex = 3000;
    MissionControlWindowInfo = [];

    if ($('#Sidepanel').is(':visible'))
    {
        $('#Sidepanel').attr('reopen', '1').hide();
    }



    if ($('#desktop-side-panel').is(':visible'))
    {
        $('#desktop-container').attr('basePos', $('#desktop-container').css('left'));
        $('#desktop-side-panel').attr('baseWidth', $('#desktop-side-panel').outerWidth());
        $('#desktop-container').css('left', '');
        $('#desktop-side-panel').hide();
    }



    if ($('#gui-console').is(':visible'))
    {
        $('#gui-console').attr('reopen', '1').hide();
        $('#console', $('#Tasks-Core')).removeClass('active');
    }

    $('#missioncontrol-bg').show();

    $('#Taskbar,#dock,#DesktopIcons').hide();

    $('#desktop,#desktop-bg').css({
        top: 0,
        left: 0
    });


    Tools.sleep(10);


    var MissionControlMainTop = (parseInt($('#desktop').height()) / 2) - (((window.innerHeight * 69.3) / 100) / 2);
    $('#desktop-bg').css({
        'position': 'absolute',
        'top': (parseInt($('#desktop-bg').height()) / 2) - (((window.innerHeight * 69.3) / 100) / 2) + 'px',
        'left': (parseInt($('#desktop-bg').width()) / 2) - (((window.innerWidth * 69.3) / 100) / 2) + 'px',
        'width': (window.innerWidth * 69.3) / 100 + 'px',
        'height': (window.innerHeight * 69.3) / 100 + 'px',
        'box-shadow': '0px 12px 30px rgba(0,0,0,.5)',
        '-moz-box-shadow': '0px 12px 30px rgba(0,0,0,.5)',
        '-webkit-box-shadow': '0px 12px 30px rgba(0,0,0,.5)'
    });


    Tools.sleep(10);

    $('.tinymce-editor').each(function () {
        console.log('id editors: ' + $(this).attr('name') + ' remove');
        $(this).tinymce().remove();
    });


    Tools.sleep(50);


    missionControlActived = true;


    var GroupWindowArray = [];
    var GroupWinList = [];
    var winWidth = [];


    var windows = $('#desktop').find('.isWindowContainer[activespace=' + Desktop.currentSpace + ']:visible');


    for (var i = 0; i < windows.length; i++) {

        var actWin = windows.eq(i);

        // positions
        var idName = $(actWin).attr('id');
        var top = parseInt($(actWin).css('top'));
        var left = parseInt($(actWin).css('left'));

        MissionControlWindowInfo.push([idName, i, [top, left]]);

        GroupWindowArray.push([idName, i]);
        GroupWinList.push($(actWin));

        windows.eq(i).attr({
            'oldTop': parseInt($(actWin).css('top')),
            'oldLeft': parseInt($(actWin).css('left')),
            'oldWidth': parseInt($(actWin).css('width')),
            'oldHeight': parseInt($(actWin).css('height')),
            'oldZIndex': $(actWin).css('z-index')
        }).css({
            'position': 'relative',
            'top': 0,
            'left': 0,
            'box-shadow': '0px 0px 5px rgba(0,0,0,.6)',
            '-moz-box-shadow': '0px 0px 5px rgba(0,0,0,.6)',
            '-webkit-box-shadow': '0px 0px 5px rgba(0,0,0,.6)'
        }).scale(1);


        if ($(actWin).hasClass('ui-resizable'))
        {
            $(actWin).resizable('disable');
        }
    }

    Tools.sleep(10);


    for (var i = 0; i < GroupWindowArray.length; i++) {
        $(GroupWinList).each(function ()
        {
            var idName = $(this).attr('id');

            var id = undefined;

            if (idName.substring(0, 1) == 'w') {
                id = idName;
            }

            if (id == GroupWindowArray[i][0]) {
                if ($('#missioncontrol_' + GroupWindowArray[i][0]).length == 0) {
                    $('#' + GroupWindowArray[i][0] + '[activespace=' + Desktop.currentSpace + ']').wrap('<div class="MissionControl_GroupWindow" id="missioncontrol_' + GroupWindowArray[i][0] + '"></div>');
                }
            }
        });
    }

    Tools.sleep(10);



    var margeTopArray = [];
    for (var i = 0; i < $('.MissionControl_GroupWindow').length; i++) {
        var mleft = 0;
        //$('.MissionControl_GroupWindow').eq(i).css('height', (parseInt($('.MissionControl_GroupWindow').eq(i).css('height')) + 70)+'px')

        var mGroupWindow = $('.MissionControl_GroupWindow').eq(i);
        var mx = $(mGroupWindow).children('div.isWindowContainer').length;


        for (var j = 0; j < mx; j++) {
            if ($(mGroupWindow).children('div.isWindowContainer').length > 3) {

                switch (j) {
                    case $(mGroupWindow).children('div.isWindowContainer').length:
                        $(mGroupWindow).children('div.isWindowContainer').eq(j).css({
                            'margin-left': 45,
                            'margin-top': -(parseInt($(mGroupWindow).children('div.isWindowContainer').eq(j - 1).css('height')) - 15) + 'px'
                        });
                        margeTopArray.push([i, [j, 15]]);
                        break;
                    case $(mGroupWindow).children('div.isWindowContainer').length - 1:
                        $(mGroupWindow).children('div.isWindowContainer').eq(j).css({
                            'margin-left': 30,
                            'margin-top': -(parseInt($(mGroupWindow).children('div.isWindowContainer').eq(j - 1).css('height')) - 15) + 'px'
                        });
                        margeTopArray.push([i, [j, 15]]);
                        break;
                    case $(mGroupWindow).children('div.windows').length - 2:
                        $(mGroupWindow).children('div.windows').eq(j).css({
                            'margin-left': 15,
                            'margin-top': -(parseInt($(mGroupWindow).children('div.isWindowContainer').eq(j - 1).css('height')) - 15) + 'px'
                        });
                        margeTopArray.push([i, [j, 15]]);
                        break;
                    default:
                        if (j > 0) {
                            $(mGroupWindow).children('div.isWindowContainer').eq(j).css({
                                'margin-left': mleft,
                                'margin-top': -(parseInt($(mGroupWindow).children('div.isWindowContainer').eq(j - 1).css('height')) - 4) + 'px'
                            });
                            margeTopArray.push([i, [j, 4]]);

                        }
                        else {
                            $(mGroupWindow).children('div.isWindowContainer').eq(j).css({
                                'margin-left': 0,
                                'margin-top': 0
                            });
                        }
                        break;
                }

            }
            else {
                if (j > 0) {
                    $(mGroupWindow).children('div.isWindowContainer').eq(j).css({
                        'margin-left': mleft + 'px',
                        'margin-top': -(parseInt($(mGroupWindow).children('div.isWindowContainer').eq(j - 1).css('height')) - 15) + 'px'
                    });
                    margeTopArray.push([i, [j, 15]]);
                }
                else {
                    $(mGroupWindow).children('div.isWindowContainer').eq(j).css({
                        'margin-left': 0,
                        'margin-top': 0
                    });
                }
                mleft += 15;
            }

        }


        $(mGroupWindow).css('height', (parseInt($(mGroupWindow).css('height'))) + 90)
                .addClass('initialWidth_' + (parseInt($(mGroupWindow).css('width'))))
                .addClass('initialHeight_' + (parseInt($(mGroupWindow).css('height'))));
    }



    var PourcentageScale = 100;
    var Scale = 1.00;

    var desktopW = parseInt($('#desktop').width());
    var desktopH = parseInt($('#desktop').height());

    $('#desktop').css({
        'position': 'absolute',
        'top': MissionControlMainTop + 10,
        'left': 0,
        'width': window.innerWidth,
        'height': (window.innerHeight - MissionControlMainTop - 90)
    }).each(function () {
        var xx = 0;
        var SECURELOOP = 0, loop = $(this).children('.MissionControl_GroupWindow').length;

        while (xx < loop) {
            SECURELOOP++;
            if (SECURELOOP >= 100) {
                console.error('SCR LP');
                break;
            }
            var wi = parseInt($(this).children('.MissionControl_GroupWindow').eq(xx).css('width'));
            var he = parseInt($(this).children('.MissionControl_GroupWindow').eq(xx).css('height'));
            var el = $(this).children('.MissionControl_GroupWindow').eq(xx);

            //=============================
            var pleft = el.offset().left;
            var ptop = el.offset().top;
            var elWidthPlusOffsetLeft = wi + pleft;
            var elHeightPlusOffsetTop = he + ptop;
            //=============================

            if (elWidthPlusOffsetLeft > desktopW || elHeightPlusOffsetTop > desktopH) {
                Scale -= 0.10;
                PourcentageScale -= 10;
                for (var j = 0; j < loop; j++) {
                    var classes = $(this).children('.MissionControl_GroupWindow').eq(j).attr('class');
                    var classesSplit = classes.split(' ');
                    var initialWidth, initialHeight;


                    for (var k = 0; k < classesSplit.length; k++)
                    {
                        var clW = classesSplit[k].substring(0, 13);

                        if (classesSplit[k].substring(0, 13) == 'initialWidth_') {
                            initialWidth = parseInt(classesSplit[k].substring(13, classesSplit[k].length));
                        }

                        if (classesSplit[k].substring(0, 14) == 'initialHeight_') {
                            initialHeight = parseInt(classesSplit[k].substring(14, classesSplit[k].length));
                        }
                    }


                    var wii = parseInt($(this).children('.MissionControl_GroupWindow').eq(j).css('width'));
                    var hee = parseInt($(this).children('.MissionControl_GroupWindow').eq(j).css('height'));
                    var _w = ((initialWidth * PourcentageScale) / 100);
                    var _h = ((initialHeight * PourcentageScale) / 100);



                    $(this).children('.MissionControl_GroupWindow').eq(j).css({
                        'width': _w,
                        'height': _h
                    });
                }



                if (xx == 0)
                    xx = 0;
                else
                    xx--;
            }
            else {
                xx++;
            }
        }



        Tools.sleep(20);

        for (var i = 0; i < loop; i++) {
            var group = $('.MissionControl_GroupWindow').eq(i);
            //======== Group Margintop add ========
            /*var margtop = 0;
             for(var j=0;j<margeTopArray.length;j++) {
             //margtop += margeTopArray[i][1][0];
             //console.log(margeTopArray[i][j][1])
             //margtop += margeTopArray[i][j][1];
             }
             console.log(margeTopArray);
             margtop = (margtop*PourcentageScale)/100;
             $('.MissionControl_GroupWindow').eq(i).css('height', (parseInt($('.MissionControl_GroupWindow').eq(i).css('height')) + margtop)+'px');
             */


            //======== Window Code Position Scale ========
            var add2 = 0;
            for (var j = 0; j < group.children('.isWindowContainer').length; j++) {

                var length = group.children('.isWindowContainer').length;
                var win_ = group.children('.isWindowContainer').eq(j);
                var width = parseInt(win_.outerWidth());
                var height = parseInt(win_.outerHeight());
                var marginleft = parseInt(win_.css('margin-left'));
                var margintop = parseInt(win_.css('margin-top'));
                var winTop = (((height * PourcentageScale) / 100) - height) / 2;
                var winLeft = (((width * PourcentageScale) / 100) - width) / 2;
                var winMarLeft = (marginleft * PourcentageScale) / 100;
                var winMarTop = (margintop * PourcentageScale) / 100;
                var cTop = 0, cLeft = 0;



                windowZIndex++;
                win_.css({
                    'display': 'block',
                    'top': winTop,
                    'left': winLeft,
                    'margin-left': winMarLeft,
                    'z-index': windowZIndex
                }).scale(Scale);

                var zoomScale = false;
                if (missionControlZoomActived)
                {
                    if (Scale < 0.8999999)
                    {
                        zoomScale = 0.89999990;
                    }
                }



                if (length > 4) {
                    var add = 4 * (length - 3);
                    switch (j) {
                        case length - 1:
                            cTop = (group.offset().top + margintop) + height + 30 + add2 - 12;
                            cLeft = group.offset().left + winMarLeft;
                            break;
                        case length - 2:
                            cTop = (group.offset().top + margintop) + height + 15 + add2 - 12;
                            cLeft = group.offset().left + winMarLeft;
                            break;
                        case length - 3:
                            cTop = (group.offset().top + margintop) + height + add2;
                            cLeft = group.offset().left + winMarLeft;
                            break;
                        default:
                            if (j == 0) {
                                cTop = margintop + group.offset().top;
                                cLeft = group.offset().left;
                            }
                            else {

                                cTop = (group.offset().top + margintop) + height + add2;
                                cLeft = group.offset().left;
                                add2 += 4;
                            }
                            break;
                    }
                }
                else {
                    if (length == 4) {
                        switch (j) {
                            case 0:
                                cTop = margintop + group.offset().top;
                                cLeft = group.offset().left;
                                break;
                            case 1:
                                cTop = (group.offset().top + margintop) + height;
                                cLeft = group.offset().left + winMarLeft;
                                break;
                            case 2:
                                cTop = (group.offset().top + margintop) + height + 4;
                                cLeft = group.offset().left + winMarLeft;
                                break;
                            case 3:
                                cTop = (group.offset().top + margintop) + height + 15 + 4;
                                cLeft = group.offset().left + winMarLeft;
                                break;
                        }
                    }
                    else {
                        switch (j) {
                            case 0:
                                cTop = margintop + group.offset().top;
                                cLeft = group.offset().left;
                                break;
                            case 1:
                                cTop = (group.offset().top + margintop) + height;
                                cLeft = group.offset().left + winMarLeft;
                                break;
                            case 2:
                                cTop = (group.offset().top + margintop) + height + 15;
                                cLeft = group.offset().left + winMarLeft;
                                break;
                        }
                    }
                }
                var cache = $('<div>').css({
                    'z-index': (windowZIndex + j + 10),
                    'top': cTop,
                    'left': cLeft,
                    'width': ((width * PourcentageScale) / 100),
                    'height': ((height * PourcentageScale) / 100)
                }).attr({
                    'class': 'MissionControlCache',
                    'attachedGroup': '' + i,
                    'attachedWindow': '' + j
                }).hover(function () {

                    if (missionControlZoomActived == false) {
                        $('.MissionControlCache').removeClass('MissionControlCacheHover');
                        $(this).addClass('MissionControlCacheHover');
                    }
                    else
                    {
                        $('.MissionControlCache').removeClass('MissionControlCacheHover');
                    }

                    $t = $(this);
                    var keypressed = false;

                    if (!missionControlKeyBoardShortcut) {
                        missionControlKeyBoardShortcut = true;
                        $('body').keydown(function (eventKeyBoard) {
                            if (missionControlActived && !keypressed) {
                                keypressed = true;
                                var touche = window.eventKeyBoard ? eventKeyBoard.keyCode : eventKeyBoard.which;

                                if (touche == 32) {
                                    window.scrollBy(0, 0);
                                    missionControlZoomActived = true;
                                    missionControlKeyBoardShortcut = true;
                                    var MCGroup = parseInt($t.attr('attachedGroup')), MCWindow = parseInt($t.attr('attachedWindow'));
                                    $currentWindow = $('.MissionControl_GroupWindow').eq(MCGroup).children('.isWindowContainer').eq(MCWindow);

                                    $('#MissionControlIcns_' + $currentWindow.attr('id') + ', #MissioncontrolIcnsText_' + $currentWindow.attr('id')).hide();


                                    $('.MissionControl_GroupWindow').eq(MCGroup).children('.isWindowContainer').hide();
                                    var CWTop = 0 - 20; // 20 étant le margin d'en haut

                                    $currentWindow.attr({
                                        'oldStyle': Base64.encode($currentWindow.attr('style'))
                                    }).css({
                                        'position': 'absolute',
                                        'z-index': $currentWindow.css('z-index') + 10000,
                                        'top': CWTop + 'px',
                                        'left': '0px',
                                        'margin-top': '0px',
                                        'margin-left': '0px',
                                        'visibility': 'visible'
                                    });

                                    if (parseInt($currentWindow.css('width')) > window.innerWidth || parseInt($currentWindow.css('height')) > (window.innerHeight - 80)) {
                                        FinalTop = (((window.innerHeight / 2) - (((parseInt($currentWindow.css('height')) * (PourcentageScale + 10)) / 100) / 2)) - $('.MissionControl_GroupWindow').eq(MCGroup).offset().top);
                                        FinalLeft = ((window.innerWidth / 2) - (((parseInt($currentWindow.css('width')) * (PourcentageScale + 10)) / 100) / 2));
                                        Scale2 = PourcentageScale + 0.10;
                                    }
                                    else {
                                        FinalTop = (((window.innerHeight / 2) - (((parseInt($currentWindow.css('height')) * 100) / 100) / 2)) - $('.MissionControl_GroupWindow').eq(MCGroup).offset().top);
                                        FinalLeft = ((window.innerWidth / 2) - (((parseInt($currentWindow.css('width')) * 100) / 100) / 2));
                                        Scale2 = '1';
                                    }

                                    $currentWindow.animate({
                                        'visibility': 'visible',
                                        'top': FinalTop + 'px',
                                        'left': FinalLeft + 'px',
                                        'scale': Scale2
                                    }, 250);
                                }
                            }
                        });



                        $('body').keyup(function () {
                            if (missionControlActived && keypressed) {
                                missionControlKeyBoardShortcut = true;
                                missionControlZoomActived = false;
                                var MCGroup = parseInt($t.attr('attachedGroup')), MCWindow = parseInt($t.attr('attachedWindow'));
                                $currentWindow = $('.MissionControl_GroupWindow').eq(MCGroup).children('.windows').eq(MCWindow);
                                $('#MissionControlIcns_' + $currentWindow.attr('id') + ', #MissioncontrolIcnsText_' + $currentWindow.attr('id')).show()
                                $currentWindow.css('visibility', 'visible').attr('style', Base64.decode($currentWindow.attr('oldStyle')));
                                $('.MissionControl_GroupWindow').eq(MCGroup).children('.isWindowContainer').css('visibility', 'visible');
                                keypressed = false;

                            }
                        });
                    }
                }, function () {
                    $(this).removeClass('MissionControlCacheHover');
                }).click(function (e) {
                    var MCGroup = parseInt($(this).attr('attachedGroup')), MCWindow = parseInt($(this).attr('attachedWindow'));
                    var idx, CurrentWindowUniqueID = $('.MissionControl_GroupWindow').eq(MCGroup).children('.isWindowContainer').eq(MCWindow).attr('uniqueid');
                    MissionControlRemove(CurrentWindowUniqueID, idx);
                    return false;
                });

                $('#fullscreenContainer').append(cache);
            }



            //======== Icns App Code Group ========
            var app = $('.MissionControl_GroupWindow').eq(i).attr('id').substring(15, $('.MissionControl_GroupWindow').eq(i).attr('id').length);
            var controller = $('#' + app).attr('app');

            //var APPPath = WhatPathForThisApp(app);
            var icnsTop = parseInt($('.MissionControl_GroupWindow').eq(i).css('height')) + $('.MissionControl_GroupWindow').eq(i).offset().top;
            var icnsLeft = (parseInt($('.MissionControl_GroupWindow').eq(i).css('width')) + $('.MissionControl_GroupWindow').eq(i).offset().left) - (parseInt($('.MissionControl_GroupWindow').eq(i).css('width')) / 2);

            var icnsT = ((icnsTop - (((128 * (PourcentageScale - 10)) / 100)))) - 40;
            var icnsL = (icnsLeft - (((128 * (PourcentageScale - 10)) / 100)));

            var icnsSrc = $('#' + app).find('.window-titlebar .title img').attr('src');


            if (controller)
            {
                var icsSpan = $('<span/>').attr({
                    'id': 'MissionControlIcns_' + app,
                    'class': 'MissioncontrolIcns'
                }).css({
                    'text-align': 'center',
                    'width': ((128 * (PourcentageScale - 10)) / 100) + 'px',
                    'height': ((128 * (PourcentageScale - 10)) / 100) + 'px',
                    'position': 'absolute',
                    'top': icnsT,
                    'left': icnsL,
                    'z-index': 99999
                });


                var icns = $('<img/>').attr({
                    'src': Application.getAppIcon(controller, 128)
                }).css({
                    'width': ((128 * (PourcentageScale - 10)) / 100) + 'px',
                    'height': ((128 * (PourcentageScale - 10)) / 100) + 'px',
                    'position': 'absolute',
                    'z-index': 99999
                });
                icsSpan.append(icns);



                $('#fullscreenContainer').append(icsSpan);
            }

            var appTitle = $('#' + app).find('.window-titlebar .title').text();
            var icnsText = $('<span></span>').html(appTitle).attr('class', 'MissioncontrolIcnsText');
            var top = parseInt(icnsT) + ((128 * (PourcentageScale - 10)) / 100) - 20;
            var left = parseInt(icnsL) + (((128 * (PourcentageScale - 10)) / 100));

            icnsText.css({
                'display': 'block',
                'position': 'absolute',
                'top': top,
                'left': left,
                'z-index': 99999
            }).attr('id', 'MissioncontrolIcnsText_' + app);


            $('#fullscreenContainer').append(icnsText);

            var ClientWidth = icnsText.get(0).clientWidth / 2;
            icnsText.css('left', (parseInt(icnsText.css('left')) - ClientWidth))


        } // end for(var i=0;i<loop;i++) {



        $('#missionControlLoad').css('display', 'none').remove();
    });


    Tools.sleep(10);

    $('#desktop').unbind('click.missioncontrol').bind('click.missioncontrol', function (e) {

        if (missionControlActived)
            MissionControlRemove();
    });


    elapsedTime = new Date().getTime() - MCstartTime;
    console.info('Time to create Mission Control: ' + elapsedTime + 'ms');
    return true;
}

function SpacesStart ()
{
    if (!spacesFirstLaunch)
    {
        spacesFirstLaunch = true;
        var spacesMaximumDesktop = 4;
        var spacesLength = $('#spacesMiniContainer').children('center').children('.spacesMiniSpace').length - 1;
        var SpaceMiniContainer = $('#spacesMiniContainer');
        $('#spacesMiniContainer').css({
            'width': window.innerWidth,
            'height': ((window.innerHeight * 9) / 100) + 30,
            'display': 'block'
        });

        // ======== Create New Desktop Code ========
        var attrDesktopBackground = $('#desktop-bg').attr('src'), spaces_plusTop = ((((window.innerHeight * 9) / 100) / 2) - (23 / 2)), spaces_plusLeft = ((((window.innerWidth * 10) / 100) / 4) - (24 / 2));
        var createNewDesktopOverZone = $('<div></div>').attr('class', 'spacesCreateNewDesktopOverZone').css({
            'height': ((window.innerHeight * 9) / 100) + 30
        });
        var spacesCreateNewDesktopButtonBlackZone = $('<div></div>').attr('class', 'spacesCreateNewDesktopButtonBlackZone').css({
            'width': (((window.innerWidth * 10) / 100) / 2) + 'px',
            'height': (((window.innerHeight * 9) / 100) + 30) + 'px',
            'display': 'none'
        });
        var createNewDesktopButton = $('<div></div>').attr('class', 'spacesCreateNewDesktopButton').css({
            'width': (((window.innerWidth * 10) / 100) / 2) + 'px',
            'height': ((window.innerHeight * 9) / 100) + 'px',
            'right': '-' + ((window.innerWidth * 10) / 100) + 50 + 'px'
        }).html('<img src="' + attrDesktopBackground + '" alt="" style="position:absolute;top:0px;left:0px;width:' + ((window.innerWidth * 10) / 100) + 'px;height:' + ((window.innerHeight * 9) / 100) + 'px;"/>' +
                '<span style="position:absolute;top:' + spaces_plusTop + 'px;left:' + spaces_plusLeft + 'px;z-index:2;" class="add-desktop" />');


        Tools.sleep(5);

        createNewDesktopOverZone.hover(function () {
            createNewDesktopButton.animate({
                'right': '0px'
            }, 200, 'easeOutQuad');
        }, function () {
            createNewDesktopButton.animate({
                'right': '-' + ((window.innerHeight * 9) / 100) + 50 + 'px'
            }, 200, 'easeOutQuad');
            spacesCreateNewDesktopButtonBlackZone.css('display', 'none'); // Au cas ou
        }).append(createNewDesktopButton).append(spacesCreateNewDesktopButtonBlackZone)
        createNewDesktopOverZone.mousedown(function () {
            spacesCreateNewDesktopButtonBlackZone.css('display', 'block');
        });
        createNewDesktopOverZone.mouseup(function () {
            spacesCreateNewDesktopButtonBlackZone.css('display', 'none');
        });

        createNewDesktopButton.click(function () {
            if (spacesLength < spacesMaximumDesktop && Desktop.spacesActualLength < Desktop.maxSpaces) {
                Desktop.spacesActualLength++;
                var newSpaces = $('<div></div>').attr('class', 'spacesMiniSpace').css({
                    'width': '0px',
                    'height': ((window.innerHeight * 9) / 100) + 30 + 'px',
                    'display': 'inline-block'
                });
                SpaceMiniContainer.children('center').append(newSpaces);
                newSpaces.animate({
                    'width': ((window.innerWidth * 10) / 100) + 4 + 'px'
                }, 500, function () {
                    var offsetLeft = $(this).offset().left;
                    var left = window.innerWidth + ((window.innerHeight * 9) / 100) + 50;
                    var volatileElement = $('<div/>').html('<img src="' + attrDesktopBackground + '" alt="" style="position:absolute;top:0px;left:0px;width:' + ((window.innerWidth * 10) / 100) + 'px;height:' + ((window.innerHeight * 9) / 100) + 'px;"/>').css({
                        'position': 'absolute',
                        'left': left + 'px',
                        'top': '10px',
                        'width': ((window.innerWidth * 10) / 100) + 'px',
                        'height': ((window.innerHeight * 9) / 100) + 'px',
                        'box-shadow': '0px 10px 35px rgba(0,0,0,.6), 0px 0px 5px rgba(0,0,0,.2)',
                        '-moz-box-shadow': '0px 10px 35px rgba(0,0,0,.6), 0px 0px 5px rgba(0,0,0,.2)',
                        '-webkit-box-shadow': '0px 10px 35px rgba(0,0,0,.6), 0px 0px 5px rgba(0,0,0,.2)'
                    }).animate({
                        'left': offsetLeft + 'px'
                    }, 500, function () {
                        var interval;
                        newSpaces.animate({
                            'width': '0px'
                        }, 500).remove();


                        var miniSpaceContainer = $('<div></div>').attr({
                            'class': 'spacesMiniSpace',
                            'spaceNumber': '' + Desktop.spacesActualLength + ''
                        }).addClass('spacesMiniDesktop').css({
                            'width': ((window.innerWidth * 10) / 100) + 4 + 'px',
                            'height': ((window.innerHeight * 9) / 100) + 'px',
                            'display': 'inline-block'
                        });

                        miniSpaceContainer.on('mouseover', function () {
                            var th = $(this);
                            interval = setTimeout(function () {
                                var offleft = th.offset().left, offtop = th.offset().top, t = th;
                                var close = $('<div />').attr('class', 'spacesClose').css({
                                    'top': (offtop - 10) + 'px',
                                    'left': (offleft - 12.5) + 'px',
                                    'display': 'block',
                                    'opacity': '0'
                                }).html('<span class="spaces_closebox"></span>');

                                if (th.children('.spacesClose').length == 0)
                                    th.append(close);
                                close.animate({
                                    'opacity': '1'
                                }, 500);


                                close.mouseup(function () {
                                    SpacesDestroySpace(t);
                                });

                            }, 500);
                        });

                        miniSpaceContainer.on('mouseleave', function () {
                            clearTimeout(interval);
                            $(this).children('.spacesClose').animate({
                                'opacity': '0'
                            }, 500).remove();
                        });

                        // ======== Change Space ========
                        miniSpaceContainer.click(function () {
                            SpacesChangeSpace($(this))
                        }).mousedown(function () {
                            $(this).css('opacity', '0.60');
                        }).mouseup(function () {
                            $(this).css('opacity', '1');
                        });


                        var spacesMiniWinContainer = $('<div></div>').css({
                            //'border': '2px solid transparent',
                            'width': ((window.innerWidth * 10) / 100) + 'px',
                            'height': ((window.innerHeight * 9) / 100) + 'px'
                        }).attr('class', 'spacesMiniWin').append('<img class="spacesMiniWinWallpaper" style="width:' + ((window.innerWidth * 10) / 100) + 'px;height:' + ((window.innerHeight * 9) / 100) + 'px;" src="' + $('#desktop-bg').attr('src') + '" onmousedown="if (event.preventDefault) event.preventDefault()"/>');

                        // SPAN
                        var spaceMiniScaceSpanContainer = $('<span></span>').css({
                            'margin-top': ((window.innerHeight * 9) / 100) + 'px',
                            'width': ((window.innerWidth * 10) / 100) + 'px'
                        }).attr('class', 'spaceMiniSpanTitle').html('Container');
                        miniSpaceContainer.append(spacesMiniWinContainer).append(spaceMiniScaceSpanContainer);
                        SpaceMiniContainer.children('center').append(miniSpaceContainer);

                        volatileElement.remove();

                        SpaceMiniContainer.disableSelection();
                        SpacesActualizeDesktopName();
                        SpacesActualizeMiniWinSize();

                    });
                    $('#fullscreenContainer').append(volatileElement);

                });

            }
        });

        SpaceMiniContainer.append(createNewDesktopOverZone);

        Tools.sleep(10);


        // ============================================================


        // ======== Dashboard MiniSpace ========
        var miniSpaceDashBoard = $('<div></div>').attr('class', 'spacesMiniSpace').css({
            'width': ((window.innerWidth * 10) / 100) + 4 + 'px',
            'height': ((window.innerHeight * 9) / 100) + 30 + 'px',
            'display': 'inline-block'
        }).addClass('mini-Space-DashBoard');

        var spacesMiniWinDashBoard = $('<div></div>').css({
            'border': '2px solid transparent',
            'width': ((window.innerWidth * 10) / 100) + 'px',
            'height': ((window.innerHeight * 9) / 100) + 'px'
        }).attr('class', 'spacesMiniWin').mousedown(function (event) {
            if (event.preventDefault)
                event.preventDefault();


            Dashboard.show();
            //DashboardStart();
        });



        // SPAN
        var spaceMiniScaceSpanDashBoard = $('<span></span>').css({
            'margin-top': ((window.innerHeight * 9) / 100) + 12 + 'px',
            'width': ((window.innerWidth * 10) / 100) + 'px'
        }).attr('class', 'spaceMiniSpanTitle').html('Dashboard');


        miniSpaceDashBoard.append(spacesMiniWinDashBoard).append(spaceMiniScaceSpanDashBoard);
        SpaceMiniContainer.children('center').append(miniSpaceDashBoard);
        // ============================================================


        // ======== 1 MiniSpace ========
        var miniSpaceContainer1 = $('<div></div>').attr({
            'class': 'spacesMiniSpace',
            'spaceNumber': '1'
        }).addClass('spacesMiniDesktop').css({
            'width': ((window.innerWidth * 10) / 100) + 4 + 'px',
            'height': ((window.innerHeight * 9) / 100) + 30 + 'px',
            'display': 'inline-block'
        });



        Tools.sleep(10);

        var spacesMiniWinContainer1 = $('<div></div>').css({
            'border': '2px solid #efefef',
            'width': ((window.innerWidth * 10) / 100) + 'px',
            'height': ((window.innerHeight * 9) / 100) + 'px'
        }).attr('class', 'spacesMiniWin').append('<img class="spacesMiniWinWallpaper" style="width:' + ((window.innerWidth * 10) / 100) + 'px;height:' + ((window.innerHeight * 9) / 100) + 'px;" src="' + $('#desktop-bg').attr('src') + '" onmousedown="if (event.preventDefault) event.preventDefault()"/>');

        Tools.sleep(10);


        for (var i = 0; i < $('.windows').length; i++) {
            var top = $('.windows').eq(i).attr('oldTop');
            var left = $('.windows').eq(i).attr('oldLeft');
            var width = $('.windows').eq(i).attr('oldWidth');
            var height = $('.windows').eq(i).attr('oldHeight');
            var zindex = $('.windows').eq(i).attr('oldZIndex');
            var wmini = ((window.innerWidth * 10) / 100);
            var hmini = ((window.innerHeight * 9) / 100);

            var w_width = (wmini * (width / window.innerWidth));
            var w_height = (hmini * (height / window.innerHeight));
            var w_top = (hmini * (top / window.innerHeight));
            var w_left = (wmini * (left / window.innerWidth));

            var w = $('<div></div>').css({
                'top': w_top + 'px',
                'left': w_left + 'px',
                'width': w_width + 'px',
                'height': w_height + 'px',
                'z-index': zindex,
                'position': 'absolute',
                'background': '#ededed',
                'box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18)',
                '-moz-box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18)',
                '-webkit-box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18)'
            })
            spacesMiniWinContainer1.append(w);
        }


        // SPAN
        var spaceMiniScaceSpanContainer1 = $('<span></span>').css({
            'margin-top': ((window.innerHeight * 9) / 100) + 12 + 'px',
            'width': ((window.innerWidth * 10) / 100) + 'px'
        }).attr('class', 'spaceMiniSpanTitle').html('Schreibtisch');
        miniSpaceContainer1.append(spacesMiniWinContainer1).append(spaceMiniScaceSpanContainer1);
        // ======== Change Space ========
        miniSpaceContainer1.click(function () {
            SpacesChangeSpace($(this))
        }).mousedown(function () {
            $(this).css('opacity', '0.60');
        }).mouseup(function () {
            $(this).css('opacity', '1');
        });

        SpaceMiniContainer.children('center').append(miniSpaceContainer1);
        // ============================================================
    }
    else {
        var attrDesktopBackground = $('#desktop-bg').attr('src');

        $('#spaceMiniContainer .spacesMiniWinWallpaper').attr('src', attrDesktopBackground);

        $('#spacesMiniContainer, .spacesCreateNewDesktopOverZone').css('display', 'block');

        $('.spacesMiniSpace').css({
            'width': ((window.innerWidth * 10) / 100) + 4 + 'px',
            'height': ((window.innerHeight * 9) / 100) + 30 + 'px'
        });
        $('.spacesMiniWin').css({
            'width': ((window.innerWidth * 10) / 100) + 'px',
            'height': ((window.innerHeight * 9) / 100) + 'px'
        }).children('img.spacesMiniWinWallpaper').css({
            'width': ((window.innerWidth * 10) / 100) + 'px',
            'height': ((window.innerHeight * 9) / 100) + 'px'
        });
        $('.spaceMiniSpanTitle').css({
            'top': ((window.innerHeight * 9) / 100) + 20 + 'px',
            'width': ((window.innerWidth * 10) / 100) + 'px'
        });
        // ============================================================
        SpacesActualizeMiniWinSize();
    }


    $('.tinymce-editor', $('#desktop .isWindowContainer[activespace=' + Desktop.currentSpace + ']:visible')).each(function () {

        console.log('activate in SpacesActualizeSpaceLocation editor: ' + $(this).attr('name'));
        var e = this;
        Doc.loadTinyMceConfig(
                $('.isWindowContainer[activespace=' + Desktop.currentSpace + ']'), function () {
            $(e).removeClass('loaded');
            Doc.loadTinyMce($('.isWindowContainer[activespace=' + Desktop.currentSpace + ']:visible'));
            console.log('id editors: ' + $(e).attr('name') + ' loaded');
        });
    });
}

function SpacesActualizeMiniWinSize () {
    console.log('start SpacesActualizeMiniWinSize')

    for (var i = 0; i < $('.spacesMiniDesktop').length; i++) {
        $('.spacesMiniDesktop').eq(i).children('.spacesMiniWin').children('div').remove();

        var windows = $('.isWindowContainer[activespace=' + (i + 1) + ']');


        for (var j = 0; j < windows.length; j++) {

            var _w = windows.eq(j);


            if (_w.attr('oldTop')) {
                var top = _w.attr('oldTop');
                var left = _w.attr('oldLeft');
                var width = _w.attr('oldWidth');
                var height = _w.attr('oldHeight');
                var zindex = _w.attr('oldZIndex');
            }
            else {
                var top = parseInt(_w.css('top'));
                var left = parseInt(_w.css('left'));
                var width = parseInt(_w.css('width'));
                var height = parseInt(_w.css('height'));
                var zindex = _w.css('z-index');
            }

            var wmini = ((window.innerWidth * 10) / 100);
            var hmini = ((window.innerHeight * 9) / 100);
            var w_width = (wmini * (width / window.innerWidth));
            var w_height = (hmini * (height / window.innerHeight));
            var w_top = (hmini * (top / window.innerHeight));
            var w_left = (wmini * (left / window.innerWidth));
            var w = $('<div></div>').css({
                'top': w_top + 'px',
                'left': w_left + 'px',
                'width': w_width + 'px',
                'height': w_height + 'px',
                'z-index': zindex,
                'position': 'absolute',
                'background': '#ededed',
                'box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18)',
                '-moz-box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18)',
                '-webkit-box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18)'
            });
            $('.spacesMiniDesktop').eq(i).children('.spacesMiniWin').append(w);

        }
    }
}

function SpacesActualizeDesktopName () {

    if ($('.spacesMiniSpace').length > 2) {
        for (var i = 1; i < $('.spacesMiniSpace').length; i++) {
            $('.spacesMiniSpace').eq(i).children('.spaceMiniSpanTitle').html('Schreibtisch ' + i);
        }
    }
    else {
        $('.spacesMiniSpace').eq(1).children('.spaceMiniSpanTitle').html('Schreibtisch')
    }
}

function SpacesChangeSpace (t) {
    $space = t;
    var spaceNumber = $space.attr('spacenumber');
    if (Desktop.currentSpace != spaceNumber) {

        var spaceTop = $space.offset().top;
        var spaceLeft = $space.offset().left;
        var wallWidth = parseInt($space.children('.spacesMiniWin').children('img.spacesMiniWinWallpaper').css('width'));
        var wallHeight = parseInt($space.children('.spacesMiniWin').children('img.spacesMiniWinWallpaper').css('height'));
        var wallSrc = $space.children('.spacesMiniWin').children('img.spacesMiniWinWallpaper').attr('src');
        var imgVolatile = $('<img />').css({
            'position': 'absolute',
            'top': spaceTop + 'px',
            'left': spaceLeft + 'px',
            'width': wallWidth + 'px',
            'height': wallHeight + 'px',
            'z-index': '1000001'
        }).attr('src', wallSrc);

        $('#fullscreenContainer').append(imgVolatile);

        imgVolatile.animate({
            'top': '0px',
            'left': '0px',
            'width': window.innerWidth + 'px',
            'height': window.innerHeight + 'px'
        }, 250, function () {
            $(this).remove();
            MissionControlRemove();
            Desktop.currentSpace = spaceNumber;

            $('.isWindowContainer,#TaskbarButtons .Taskbar-Item').hide();


            var id = $('.isWindowContainer[activespace=' + spaceNumber + ']').attr('id');
            $('.isWindowContainer[activespace=' + spaceNumber + '],#Taskbar' + id).show();

            $('.spacesMiniSpace').children('.spacesMiniWin').css('border', 'none');
            $space.children('.spacesMiniWin').css('border', '2px solid #efefef');

            var zindex = [];
            for (var i = 0; i < $('.isWindowContainer[activespace=' + spaceNumber + ']').length; i++) {
                zindex.push(parseInt($('.isWindowContainer[activespace=' + spaceNumber + ']').eq(i).css('z-index')));
            }

            zindex.sort();
            zindex.reverse();

            var windows = $('.isWindowContainer[activespace=' + spaceNumber + ']');
            for (var i = 0; i < windows.length; i++)
            {
                if (windows.eq(i).css('z-index') == zindex[0]) {
                    windows.eq(i).css('z-index', zindex[0] + 1);
                }
            }
        });
    }
}

function SpacesActualizeSpaceLocation () {
    $('.isWindowContainer').hide();
    $('.isWindowContainer[activespace=' + Desktop.currentSpace + ']').show();
}

function SpacesChangeSpaceTo (w) {

    $win = w;
    var spaceAllocated = $win.attr('activespace');
    if (spaceAllocated != Desktop.currentSpace) {
        MissionControlRemove();

        $('.isWindowContainer').hide();

        $('.isWindowContainer[activespace=' + spaceAllocated + ']').hide();

        $('.spacesMiniSpace').children('.spacesMiniWin').css('border', 'none');
        $('.spacesMiniSpace[spaceNumber=' + spaceAllocated + ']').css('border', '2px solid #efefef');
    }
}

function SpacesDestroySpace (t) {

    $space = t;
    var spaceNumber = $space.attr('spacenumber');
    if (spaceNumber > 1) {
        for (var i = 0; i < $('.isWindowContainer[activespace=' + spaceNumber + ']').length; i++) {
            console.log($('.isWindowContainer[activespace=' + spaceNumber + ']').eq(i).attr('activespace', '1'));
        }
    }
    $space.remove();

    Desktop.spacesActualLength--;
    Desktop.currentSpace = 1;
    $('.spacesMiniSpace').children('.spacesMiniWin').css('border', 'none');
    $('.spacesMiniSpace[spaceNumber=1]').children('.spacesMiniWin').css('border', '2px solid #efefef');

    SpacesActualizeSpaceLocation();
    MissionControlRemove();
}

// register onLoad event with anonymous function
window.onload = function (e) {
    var evt = e || window.event, // define event (cross browser)
            imgs, // images collection
            i;                      // used in local loop
    // if preventDefault exists, then define onmousedown event handlers
    if (evt.preventDefault) {
        // collect all images on the page
        imgs = document.getElementsByTagName('img');
        // loop through fetched images
        for (i = 0; i < imgs.length; i++) {
            // and define onmousedown event handler
            imgs[i].onmousedown = disableDragging;
        }
    }
};

// disable image dragging
function disableDragging (e) {
    e.preventDefault();
}