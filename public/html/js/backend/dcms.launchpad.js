var Launchpad = {
    inited: false,
    launchpad: null,
    launchpadMain: null,
    launchpadHeader: null,
    launchpadFooter: null,
    launchpad_bg: null,
    launchpadContent: null,
    defaultDockZindex: 0,
    gutterSizeW: 7,
    gutterSizeH: 5,
    isVisible: false,
    itemSize: 0,
    init: function ()
    {
        if (this.inited)
        {
            return;
        }
        var self = this;

        this.defaultDockZindex = $('#dock').css('z-Index');


        if (!$('#launchpad_action', $('#dock')).length)
        {
            var min = $('<div>').attr('id', 'launchpad_action').attr('app', 'launchpad').attr('label', 'Launchpad').addClass('dock-launchpad').addClass('appicn').addClass('static');
            var img = $('<img src="' + Application.getAppIcon('dashboard', 128) + '">').addClass('launchpadDockIcon');
            img.height(Dock.currentDockHeight - 10).width(Dock.currentDockHeight - 10);
            var dockHeight = Dock.currentDockHeight;

            min.append(img).append($('<span>'));
            min.click(function (e) {
                $(this).addClass('active');
                Launchpad.show(e);
            });

            $('#dock-inner').append(min);

            Dock.activeItems = $('#dock-inner .appicn').length;

            if (Dock.activeItems > 0)
            {
                $('.appicn img', $('#dock-inner')).css({
                    width: dockHeight - 10,
                    height: dockHeight - 10
                });
            }

            $('#dock-appmin img').css({
                height: dockHeight - 10 * Dock.minimizedItems,
                width: dockHeight - 10 * Dock.minimizedItems
            });

            if (Dock.minimizedItems > 0)
            {
                $('.appicn img', $('#dock-appmin')).css({
                    width: dockHeight - 10,
                    height: dockHeight - 10
                });
            }

            setTimeout(function () {
                Dock.updateDockPos();
                Dock.dockBubbles();
            }, 10);

        }

        this.launchpad = $('#launchpad');
        this.launchpad_bg = $('#desktop-bg');

        this.launchpadHeader = $('#launchpadHeader');
        this.launchpadMain = $('#launchpadMain');
        this.launchpadFooter = $('#launchpadFooter');
        this.launchpadContent = $('#launchpadHeader,#launchpadFooter,#launchpadMain');


        if (!this.launchpadMain.find('.launchpad-container').length)
        {
            this.launchpadMain.append($('<div class="launchpad-container"/>'));
        }


        var t;
        this.launchpadHeader.find('input').each(function () {
            $(this).unbind('keyup.launchpad').on('keyup.launchpad', function () {
                var _self = this;
                clearTimeout(t);

                t = setTimeout(function () {
                    var value = _self.value;
                    if (value) {
                        var t = value; //.replace('/', '\/').replace('*', '\*').replace('\s', ' ').replace('"', '\"');

                        self.launchpadMain.find('.LaunchItem').each(function () {
                            var str = $(this).attr('title');
                            var regex = new RegExp(t, "i");

                            if (!str.match(regex)) {
                                $(this).hide();
                            }
                            else {
                                $(this).show();
                            }
                        });
                    }
                    else {
                        self.launchpadMain.find('.LaunchItem').show();
                    }
                }, 300);


            });
        });





        this.launchpad.css({
            opacity: '0'
        }).hide();

        this.getData();
    },
    destroy: function () {
        this.launchpadMain.find('.launchpad-container').empty();
        this.inited = false;
    },
    removeItem: function (modul)
    {
        this.launchpadMain.find('#LaunchPadCase_' + modul).remove();
    },
    refresh: function ()
    {
        Launchpad.launchpad = $('#launchpad');
        Launchpad.launchpad_bg = $('#desktop-bg');
        Launchpad.launchpadHeader = $('#launchpadHeader');
        Launchpad.launchpadMain = $('#launchpadMain');
        Launchpad.launchpadFooter = $('#launchpadFooter');
        Launchpad.launchpadContent = $('#launchpadHeader,#launchpadFooter,#launchpadMain');
        Launchpad.getData();
    },
    markVisibleDesktopIcons: function ()
    {

        this.launchpadMain.find('.LaunchItem').each(function () {
            var icn = $('#DesktopIcons').find('[rel=' + $(this).attr('id').replace('LaunchPadCase_', '') + ']');
            if (icn.length)
            {
                $(this).css('opacity', '0.6');
            }
            else
            {
                $(this).css('opacity', '1');
            }
        });

    },
    show: function (event)
    {
        if (this.launchpad.is(':visible'))
        {
            return true;
        }

        var self = this;

        MissionControl.MissionControlRemove();
        Dashboard.close();

        this.updateSize();

        

        if ($('#Sidepanel').is(':visible'))
        {
            $('#Sidepanel').attr('reopen', true).hide();
        }


        if ($('#desktop-side-panel').is(':visible'))
        {

            $('#desktop-container').attr('basePos', $('#desktop-container').css('left'));
            $('#desktop-side-panel').attr('baseWidth', $('#desktop-side-panel').outerWidth());

            $('#desktop-container').css('left', '');
            $('#desktop-side-panel').hide();
        }


        if ($('#searchPopup').is(':visible')) {
            $('#searchPopup').hide();
            $('#indexer').removeClass('active');
        }


        $('#desktop .isWindowContainer:visible').addClass('hidefrom-launchpad').hide();

        $('#desktopTop_bg,#desktopTop_shadow,#Taskbar').css('display', 'none');


        $('#DesktopIcons').filter(':ui-droppable').droppable('enable');

        if ($('#gui-console').is(':visible'))
        {
            $('#gui-console').attr('reopen', '1').hide();
            $('#console', $('#Tasks-Core')).removeClass('active');
        }


        //Tools.Blur( $('#desktop-bg-container') );
        $('#desktop-bg-container').addClass('blur');


        $('#dock').show().css('z-index', '100001');

        this.launchpad_bg.css({
            'opacity': '1',
            'width': (window.innerWidth) + 'px',
            'height': (window.innerHeight) + 'px',
            'z-index': '100000',
        }).hide();

        this.launchpadMain.css({
            height: (window.innerHeight - this.launchpadHeader.outerHeight() - this.launchpadFooter.outerHeight())
        });






        this.launchpad.css({
            'z-index': '100001',
            'opacity': '1',
            'width': (window.innerWidth) + 'px',
            'height': (window.innerHeight) + 'px'
        }).hide();

        this.setContainerPos();

        this.launchpad.unbind('click').click(function (e) {
            
            if (!$(e.target).parents('#launchpadHeader').length && !$(e.target).parents('.LaunchItem').length && !$(e.target).hasClass('.LaunchItem') && !$(e.target).parents('.pane').length) {
                $('#dockBulleContent_Launchpad').remove();
                $('#dock').css('z-index', self.defaultDockZindex); // On remet le dock a son zindex original

                self.hide();
            }
        });





        $('#DesktopIcons').hide();
        self.launchpad_bg.show();
        self.launchpad.show();
        self.isVisible = true;
        self.setContainerPos();
        $('#launchpadMain').find('.launchpad-container').width($('#launchpadMain').find('.launchpad-container').width());
        this.markVisibleDesktopIcons();
        Tools.scrollBar($('#launchpadMain').find('.launchpad-container'));

        return true;



        $('#DesktopIcons').stop(true).animate({
            'opacity': '0'
        }, {
            duration: 500,
            complete: function () {
                $(this).hide();
            }
        });


        self.launchpad_bg.stop(true).animate({
            'opacity': '1'
        }, {
            duration: 500
        });

        self.launchpad.stop(true).animate({
            'opacity': '1'
        }, {
            duration: 500,
            complete: function () {
                self.isVisible = true;

                self.setContainerPos();
                $('#launchpadMain').find('.launchpad-container').width($('#launchpadMain').find('.launchpad-container').width());

                Tools.scrollBar($('#launchpadMain').find('.launchpad-container'));

            }
        });


        return true;
    },
    hide: function (speed)
    {
        if (!this.isVisible)
        {
            return;
        }

        var self = this;
        speed = speed || 300;

        $('#desktop-bg-container').removeClass('blur').css({
            opacity: 1,
            zIndex: 0
        }).children('img').css({
            opacity: 1,
            zIndex: 0
        });

        /*
         this.launchpad_bg.stop().animate({
         'opacity': '0'
         }, 500, function () {
         $(this).hide();
         });
         */


        $('#DesktopIcons').css({'opacity': '0.1'}).stop().show().animate({
            'opacity': '1'
        }, speed + 10, function () {

        });


        this.launchpad.unbind('click');
        this.launchpad.stop().animate({
            'opacity': '0'
        }, speed, function () {
            $(this).hide();



            if ($('#Sidepanel').attr('reopen') === true)
            {
                $('#Sidepanel').removeAttr('reopen').show();
            }

            if ($('#desktop-side-panel').attr('baseWidth'))
            {
                $('#desktop-container').css('left', $('#desktop-container').attr('basePos')).removeAttr('basePos');
                ;
                $('#desktop-side-panel').width($('#desktop-side-panel').attr('baseWidth')).removeAttr('baseWidth').show();
            }



            $('#desktop .hidefrom-launchpad').show();
            $('#desktopTop_bg,#desktopTop_shadow,#Taskbar').show();
            $('#desktop .DesktopIconContainer, #desktop .DesktopIconContainer-Folder').show();


            $('#DesktopIcons').droppable('enable');


            if ($('#gui-console').attr('reopen') == 1)
            {
                $('#gui-console').removeAttr('reopen').show();
                $('#console', $('#Tasks-Core')).addClass('active');
            }

            $('#dock #launchpad_action').removeClass('active');
            self.isVisible = false;
        });
    },
    testGutterItem: function ()
    {
        var appCase = $('<div>');
        appCase.attr({
            'class': 'LaunchItem'
        });
        appCase.append('<center><img class="LaunchItem_icns" src="" /><span>test</span></center>');
        this.launchpadMain.find('.launchpad-container').append(appCase);

        var itmW = this.launchpadMain.find('.LaunchItem:first').outerWidth();
        var itmH = this.launchpadMain.find('.LaunchItem:first').outerHeight();

        this.launchpadMain.find('.launchpad-container').empty();
        return {
            width: itmW,
            height: itmH
        };
    },
    getData: function ()
    {
        var self = this;
        this.launchpadMain.find('.launchpad-container').html('');
        var container = this.launchpadMain.find('.launchpad-container');

        var itemSize = this.testGutterItem();
        this.itemSize = itemSize;
        Desktop.ajaxWorkerOn = true;

        $.ajax({
            url: Tools.prepareAjaxUrl('admin.php?getLaunchPad=1'),
            type: "GET",
            async: false,
            dataType: "json",
            success: function (data)
            {
                if (Tools.responseIsOk(data) && typeof data.modules == 'object')
                {

                    var maxPerPage = (self.gutterSizeW * self.gutterSizeH);

                    if (data.modules.length > maxPerPage)
                    {
                        //console.log('add page maxPerPage:' + maxPerPage);
                    }

                    if (itemSize.width * self.gutterSizeW > self.launchpadMain.outerWidth() + 10)
                    {
                        self.gutterSizeW--;
                        //console.log('gutter width items over');
                    }

                    if (itemSize.height * self.gutterSizeH > self.launchpadMain.outerHeight() + 10)
                    {
                        self.gutterSizeH--;
                        //console.log('gutter height items over');
                    }

                    maxPerPage = (self.gutterSizeW * self.gutterSizeH);

                    //console.log('add page new maxPerPage:' + maxPerPage);
                    //console.log('gutter width items ' + self.gutterSizeW);
                    //console.log('gutter height items ' + self.gutterSizeH);


                    for (var i = 0; i < data.modules.length; i++)
                    {
                        var dat = data.modules[i];
                        var appData = Tools.extractAppInfoFromUrl(dat.url);

                        var appCase = $('<div>');
                        appCase.attr({
                            title: dat.title,
                            'class': 'LaunchItem',
                            'id': 'LaunchPadCase_' + (dat.isplugin === 'undefined' || dat.isplugin !== true ? dat.controller : dat.pluginkey)
                        });

                        var opts = {
                            WindowURL: Tools.prepareAjaxUrl(dat.url),
                            Controller: dat.controller,
                            Action: dat.action,
                            WindowTitle: dat.title,
                            isStatic: false,
                            isRootApplication: true,
                            label: dat.title,
                            rel: (dat.isplugin === 'undefined' || dat.isplugin !== true ? dat.controller : dat.pluginkey),
                            url: Tools.prepareAjaxUrl(dat.url),
                            controller: dat.controller,
                            action: dat.action,
                            WindowID: Desktop.getHash(dat.url),
                            isAddon: dat.isplugin
                        };

                        opts.loadWithAjax = true;
                        opts.allowAjaxCache = false;
                        // opts.WindowToolbar = false;
                        // opts.DesktopIconWidth= 36;
                        // opts.DesktopIconHeight= 36;
                        opts.UseWindowIcon = false;
                        // opts.Skin = Desktop.settings.Skin;
                        // opts.WindowDesktopIconFile = '';                        

                        if (typeof dat.isplugin === 'undefined' || dat.isplugin !== true)
                        {
                            opts.DesktopIconFilename = Application.getAppIcon(dat.controller, 32);
                            appCase.data('itemData', opts);
                            appCase.append('<center><img class="LaunchItem_icns" src="' + Application.getAppIcon(dat.controller, 128) + '" /><span>' + dat.title + '</span></center>');
                        }
                        else if (dat.isplugin === true)
                        {
                            opts.DesktopIconFilename = Application.getAppIcon(dat.pluginkey, 32, true);
                            appCase.data('itemData', opts);
                            appCase.append('<center><img class="LaunchItem_icns" src="' + Application.getAppIcon(dat.pluginkey, 128, true) + '" /><span>' + dat.title + '</span></center>');
                        }


                        appCase.mousedown(function () {
                            $(this).css('opacity', '0.9');
                        });

                        appCase.mouseup(function () {
                            $(this).css('opacity', '1');
                        });



                        appCase.draggable({
                            appendTo: "body",
                            zIndex: 5000,
                            helper: function (event) {
                                var iconSpan = $(this).find('span').clone();
                                var label = $(this).data('itemData').WindowTitle;
                                var icon = $(this).data('itemData').DesktopIconFilename;

                                var helper = $('<div class="fromMenu fromlaunchpad menu-drag-helper"></div>').css('z-index', 999999);
                                $(helper).data('itemData', $(this).data('itemData'));

                                return $(helper).append('<img src="' + icon + '" width="32" height="32"/>').append($('<p>').append(label)).appendTo("body");
                            },
                            revert: true,
                            scroll: false,
                            start: function (event, ui)
                            {
                                $(this).addClass('fromMenu fromlaunchpad');
                                var iconSpan = $(this).find('span').clone();
                                var label = $(this).data('itemData').WindowTitle;
                                var icon = $(this).data('itemData').DesktopIconFilename;
                                var helperTemplate = '<div class="fromMenu fromlaunchpad menu-drag-helper"></div>';
                                $(helperTemplate).data('itemData', $(this).data('itemData'))
                                        .addClass(iconSpan.attr('class'))
                                        .append('<img src="' + icon + '" width="32" height="32"/>')
                                        .append($('<p>').append(label));

                                self.hide(0);


                            },
                            stop: function ()
                            {
                                self.hide(0);
                            }
                        });



                        appCase.bind('dblclick', function (ev) {
                            if (!$(this).hasClass('downloading')) {
                                self.hide();
                                var opt = $(this).data('itemData');

                                // reset current ajaxData
                                Desktop.ajaxData = {};

                                setTimeout(function () {
                                    resizeCallback(function () {


                                        if (typeof Desktop.ajaxData.Error != 'undefined' && Desktop.ajaxData.Error == true)
                                        {

                                            Dock.ApplicationOpenError(opts.Controller);
                                            Debug.error((typeof Desktop.ajaxData.msg == 'string' ? Desktop.ajaxData.msg : 'Error'));
                                            Notifier.display('error', Desktop.ajaxData.msg);
                                        }
                                        else
                                        {
                                            if (typeof Desktop.ajaxData.sessionerror != 'undefined' && Desktop.ajaxData.sessionerror)
                                            {
                                                $('#userMenu,#Taskbar').remove();
                                                Desktop.runAfterBoot(true);
                                                return false;
                                            }

                                            if (typeof Desktop.ajaxData.toolbar != 'undefined')
                                            {
                                                opt.WindowToolbar = Desktop.ajaxData.toolbar;
                                            }


                                            var dockIcon = Dock.createDockIcon(ev, opt);
                                            setTimeout(function () {
                                                dockIcon.click();
                                            }, 300);
                                        }

                                    }, 10);

                                }, 10);



                            }
                        });

                        container.append(appCase);
                    }
                }


                Desktop.ajaxWorkerOn = false;
                self.inited = true;

                self.setContainerPos();
                Tools.scrollBar($('#launchpadMain').find('.launchpad-container'));

            }
        });

    },
    setContainerPos: function () {
        var width = $('#launchpadMain').outerWidth();
        var height = $('#launchpadMain').outerHeight();

        var space = this.itemSize * this.gutterSizeW;
        var left = (width - space) / 2;

        $('#launchpadMain').find('.launchpad-container').height('100%').width(space);
        $('#launchpadMain').find('.scroll-container')
                .width(space)
                .height(height)
                .css({marginLeft: left});
    },
    updateSize: function ()
    {
        if (!this.isVisible)
        {
            return;
        }

        $('#launchpadMain').css({
            height: (
                    window.innerHeight -
                    $('#launchpadHeader').outerHeight() -
                    $('#launchpadFooter').outerHeight())
        });

        this.setContainerPos();

        Tools.scrollBar($('#launchpadMain').find('.launchpad-container'));

        $('#desktop-bg,#launchpad').css({
            'width': (window.innerWidth) + 'px',
            'height': (window.innerHeight) + 'px'
        });

    }

};