
Desktop.Taskbar = (function () {
    return {
        timerRunning: false,
        timerID: null,
        indexer: null,
        updateTaskbarButtons: function ()
        {
            if ($('#userMenu').is(':visible'))
            {
                this.updateByResize();
            }

            var w = $('#Taskbar').width() - $('#Start-Menu-Button').outerWidth() - $('#TaskbarPoints').outerWidth() - $('#Tasks').outerWidth() - $('#Tasks-Core').outerWidth() - $('#TaskbarShowDesktop').outerWidth() - 35;
            var buttonsVisible = $('#TaskbarButtons').find('.Taskbar-Item:visible');
            var buttonsVisibleWidth = 0;
            buttonsVisible.each(function () {
                buttonsVisibleWidth += $(this).width();
            });

            $('#TaskbarButtons').css({
                height: $('#TaskbarButtonsWrapper').height()
            });

            // reset ul scroll position top :) litte patch for menu scroll
            $('#NavItems').find('ul').css({
                top: 0
            });


            if (buttonsVisibleWidth > w)
            {
                $('#scroll-pos').show();
                $('#scroll-r').show();
                $('#scroll-l').hide();
            }
            else
            {
                $('#scroll-pos').hide();
                $('#TaskbarButtons').find('.Taskbar-Item').css({
                    left: 0
                });
            }
        },
        bindResizeEvents: function ()
        {
            var self = this;
            $(window).resize(
                    function (e) {

                        if ($('#userMenu').is(':visible'))
                        {
                            self.updateByResize();
                        }

                        var w = $('#Taskbar').width() - $('#Start-Menu-Button').outerWidth() - $('#TaskbarPoints').outerWidth() - $('#Tasks').outerWidth() - $('#Tasks-Core').outerWidth() - $('#TaskbarShowDesktop').outerWidth() - 35;
                        var buttonsVisible = $('#TaskbarButtons').find('.Taskbar-Item:visible');
                        var buttonsVisibleWidth = 0;
                        buttonsVisible.each(function () {
                            buttonsVisibleWidth += $(this).width();
                        });
                        /*
                         $('#TaskbarButtonsWrapper').css({
                         width: w, 
                         maxWidth: w
                         });
                         */
                        $('#TaskbarButtons').css({
                            height: $('#TaskbarButtonsWrapper').height()
                        });

                        // reset ul scroll position top :) litte patch for menu scroll
                        $('#NavItems').find('ul').css({
                            top: 0
                        });


                        if (buttonsVisibleWidth > w)
                        {
                            $('#scroll-pos').show();
                            $('#scroll-r').show();
                            $('#scroll-l').hide();
                        }
                        else
                        {
                            $('#scroll-pos').hide();
                            $('#TaskbarButtons').find('.Taskbar-Item').css({
                                left: 0
                            });
                        }

                    });
        },
        bindTaskbarEvents: function ()
        {
            var self = this;

            $('#Taskbar').bind('contextmenu', function (e) {
                return false;
            });


            if ($('#user-login', $('#Tasks-Core')).length)
            {
                $('#user-login', $('#Tasks-Core')).unbind('click');
                $('#user-login', $('#Tasks-Core')).click(function (e) {
                    $(this).addClass('active');
                    self.toggleUserLogin(e);
                });
            }

            this.bindResizeEvents();




            if ($('#userMenu li').length)
            {
                $('#userMenu li').unbind('mouseover mouseout');
                $('#userMenu li').bind("mouseover mouseout", function (e) {
                    if (e.type == 'mouseover') {
                        $(this).addClass('active');
                    }
                    else {
                        $(this).removeClass('active');
                    }
                });
            }



            if ($('#createbackup').length)
            {
                var clickCounter = 0;
                $('#createbackup').attr('title', 'Backup anlegen...').unbind().bind('click', function () {



                    clickCounter++;


                    if (clickCounter === 1) {

                        if ($(this).find('.backup').is(':visible')) {

                            $(this).attr('title', 'Backup abbrechen...');


                            $(this).find('.backup').hide();
                            $(this).find('img').show();
                            var btn = $(this);

                            var iframe = $('<iframe>').attr('id', 'backup-iframe');
                            iframe.css({position: 'absolute', top: -1000, left: -1000, width: 0, height: 0}).attr('src', 'admin.php?adm=backup&action=create&mode=full');
                            iframe.on('load', function () {
                                var str = $(this).contents().text();

                                $(this).remove();

                                if (str != 'error') {
                                    Notifier.info(str);
                                    btn.find('img').hide();
                                    btn.find('.backup').show();


                                } else {
                                    btn.find('img').hide();
                                    btn.find('.backup').show();
                                    Notifier.error('Backup Error');
                                }


                            });


                            setTimeout(function () {
                                $('body').append(iframe);
                            }, 50);

                        }
                    }
                    else {

                        var el = $(this);
                        $.get('admin.php?adm=backup&action=create&cancel=true', function (data) {
                            if (Tools.responseIsOk(data)) {
                                $('#backup-iframe').unbind('load').attr("src", "javascript:false;").remove();
                                el.attr('title', 'Backup anlegen...');

                                clickCounter = 0;
                                el.find('img').hide();
                                el.find('.backup').show();

                                if (data.msg) {
                                    Notifier.info(data.msg);
                                }

                            }
                        });
                    }

                });
            }



            /*
             if ($('#TaskbarShowDesktop').length)
             {
             $('#TaskbarShowDesktop').unbind()
             $('#TaskbarShowDesktop').bind("mouseover mouseout", function(e)
             {
             if (e.type == 'mouseover') {
             $(this).addClass('hover');
             Desktop.transparentallon();
             } else {
             $(this).removeClass('hover');
             Desktop.transparentalloff();
             }
             });
             
             
             $('#TaskbarShowDesktop').click(function(e)
             {
             Desktop.transparentalloff();
             
             if (Desktop.getOpenWindowsCount() > 0) {
             Desktop.minall();
             }
             else {
             Desktop.resall();
             }
             });
             
             }
             */



            if ($('#scroll-r').length)
            {
                $('#scroll-r').unbind('click').click(function (e) {
                    self.moveTaskbarItems('left')

                });
            }

            if ($('#scroll-l').length)
            {
                $('#scroll-l').unbind('click').click(function (e) {
                    self.moveTaskbarItems('')
                });
            }

            if ($('#TaskbarButtons').length)
            {
                $('#TaskbarButtons').unbind().bind('change', function (e) {
                    var TaskbarButtonsMaxWidth =
                            $('#Taskbar').width() - $('#Start-Menu-Button').outerWidth() -
                            $('#TaskbarPoints').outerWidth() - $('#Tasks').outerWidth() -
                            $('#Tasks-Core').outerWidth() - $('#TaskbarShowDesktop').outerWidth();



                    var buttons = $(this).find('.Taskbar-Item');
                    var buttonsVisible = $(this).find('.Taskbar-Item:visible');
                    var numOfButtons = buttons.length;
                    var width = 0;

                    buttons.each(function () {
                        width += $(this).width();
                    });

                    var buttonsVisibleWidth = 0;
                    buttonsVisible.each(function () {

                        buttonsVisibleWidth += $(this).width();
                    });

                    if (buttonsVisible > TaskbarButtonsMaxWidth)
                    {
                        $(this).css({
                            width: TaskbarButtonsMaxWidth - 20
                        });

                        $('#scroll-r').show();
                        $('#scroll-pos').show();

                    }
                    else
                    {
                        $('#scroll-pos').hide();
                    }
                });

            }
        },
        createTaskBar: function ()
        {
            if ($('#Taskbar').length == 0)
            {

                $('#Start-Menu,#Tasks,#Tasks-Core,#TaskbarPoints,#TaskbarButtonsWrapper,#task-cal,#userMenu,#scroll-pos').removeData().remove();
                $('#fullscreenContainer').prepend($('<div id="Taskbar" style="display: none"></div>'));

                $('#Taskbar').css({
                    top: 0 - $('#Taskbar').outerHeight()
                });

                $('#fullscreenContainer').prepend('<div id="Start-Menu"> </div>');

                $('#Taskbar').append('<div style="float: left;" id="Start-Menu-Button"><span></span></div>')
                        .append('<div id="TaskbarPoints"> </div>')
                        .append('<div title="Show Desktop" id="TaskbarShowDesktop"><span></span></div>')
                        .append('<div id="Tasks-Core"></div>')
                        .append('<div id="Tasks"></div>')
                        .append('<div id="App-Menu"></div><div id="TaskbarButtonsWrapper"><div id="TaskbarButtons"></div></div><div id="scroll-pos"><div id="scroll-r" class="ui-icon ui-icon-circle-arrow-e"></div><div id="scroll-l" class="ui-icon ui-icon-circle-arrow-w"></div></div>');

                $('#Tasks-Core').append($('<div id="content-translations" class="Taskbar-Item"><img src="' + Config.get('backendImagePath') + 'flags/' + Config.get('contenttranslationFlag') + '" width="16" height="16" /></div>'));

                if (Desktop.settings.isSeemode != true)
                {
                    $('#Tasks-Core').append($('<div id="dcmsFav" class="Taskbar-Item"><img src="' + Config.get('backendImagePath') + 'buttons/star.png" width="16" height="16" /></div>'));
                    $('#Tasks-Core').append($('<div id="createbackup" class="Taskbar-Item"><span class="backup"></span><img src="' + Config.get('backendImagePath') + 'Apple/backup-progress.gif" width="16" height="16" /></div>'));
                    $('#Tasks-Core').append($('<div id="console" class="Taskbar-Item"><span></span></div>'));
                    $('#Tasks-Core').append($('<div id="btn-missioncontroll" class="Taskbar-Item"><span></span></div>'));
                }

                $('#Tasks-Core').append($('<div id="user-login" class="Taskbar-Item"><span></span></div>'));

                this.registerContentLangButtonEvents();

                if (Desktop.settings.isSeemode)
                {
                    $('#Taskbar').find('#TaskbarShowDesktop,#dcmsFav,#console,#btn-missioncontroll').hide();
                }
                else
                {
                    this.registerBookmarkButtonEvent();

                    $('#console', $('#Tasks-Core')).click(function (e) {
                        DesktopConsole.toggle(this);
                    });

                    $('#btn-missioncontroll', $('#Tasks-Core')).click(function (e) {
                        if (MissionControl.show())
                        {
                            MissionControl.SpacesStart();
                        }
                    });
                }

                // add taskbar clock
                if (Desktop.settings.ShowTaskbarClock) {
                    this.timerRunning = false;
                    this.createTaskClock(true);
                }
            }


            $('#Taskbar').css('display', 'block').disableTextSelection();

            var w = $('#Taskbar').width() - $('#Start-Menu-Button').outerWidth() - $('#TaskbarPoints').outerWidth() - $('#Tasks').outerWidth() - $('#Tasks-Core').outerWidth() - $('#TaskbarShowDesktop').outerWidth() - 30;


            $('#TaskbarButtonsWrapper').css({
                width: w - 20,
                maxWidth: w
            });


            $('#Taskbar,#scroll-pos').hide();



            if ($('#userMenu').length == 0)
            {
                var id = 'userMenu';
                var Balloon = Template
                        .setTemplate(Desktop.Templates.Usermenu)
                        .process(
                                {
                                    id: id//,
                                            //username: Desktop.basicCMSData.userdata.username
                                });

                Template.reset();
                $(Balloon).appendTo($('body'));

                $('#userMenu').hide();
                $('#userMenu').find('.preferences').click(function (e) {
                    User.personalSettings(e);
                });

                $('#userMenu').find('.logout').click(function (e) {
                    User.logout();
                    return false;
                });
            }

            Desktop.Taskbar.bindTaskbarEvents();

            Indexer.init();


        },
        moveTaskbarItems: function (dir)
        {
            var contentx = $('#TaskbarButtons');
            var last = $('.Taskbar-Item:visible:last', contentx);
            var lastO = (last.offset().left + last.width());
            var pos;

            if (dir == 'left')
            {
                if (lastO <= $('#TaskbarButtonsWrapper').width())
                {
                    $('#scroll-r').hide();
                    $('#scroll-l').show();
                    return;
                }

                if ((contentx.position().left - 10) < 0)
                {
                    $('#scroll-l').show();
                }


                pos = contentx.position().left - 50;
            }
            else
            {
                if (lastO >= $('#TaskbarButtonsWrapper').width())
                {
                    $('#scroll-r').show();
                }

                if (contentx.position().left >= 0)
                {
                    $('#scroll-r').show();
                    $('#scroll-l').hide();
                    return;
                }


                pos = contentx.position().left + 50;
                if (lastO + pos >= $('#TaskbarButtonsWrapper').width())
                {
                    $('#scroll-r').show();
                }

                if (pos > 0)
                {
                    pos = 0;
                    $('#scroll-l').hide();
                    $('#scroll-r').show();
                }

            }


            contentx.stop(true, true).animate({
                left: pos}, 350);
        },
        /**
         *
         *
         */
        registerBookmarkButtonEvent: function ()
        {
            User.registerBookmarkButtonEvent();
        },
        /**
         *  
         */
        registerContentLangButtonEvents: function ()
        {

            var contextTimer, self = this;

            $('#content-translations').bind('click', function (ev)
            {
                var btn = $(this);
                // ev.preventDefault();
                $('#contenttrans-selector,#fav-selector').hide();
                $("#popup_overlay_remove").removeData().remove();

                if ($('#contenttrans-selector').length == 0)
                {

                    var top = $('#Taskbar').outerHeight(true);
                    var position = $('#content-translations').offset();



                    var contextDiv = $('<div>').attr('id', 'contenttrans-selector').addClass('menu').css({
                        'top': top,
                        position: 'absolute',
                        zIndex: 9999
                    });

                    var transMenu = $('<ul>').addClass('inner');


                    contextDiv.append(transMenu);
                    contextDiv.appendTo('body');

                    $.get('admin.php?getcontenttrans=1', {}, function (data) {
                        if (Tools.responseIsOk(data))
                        {
                            if (data.contentlangs)
                            {
                                for (var i in data.contentlangs)
                                {
                                    var lng = data.contentlangs[i];
                                    if (!Tools.isUndefined(lng.id))
                                    {
                                        var icon = $('<img>').attr(
                                                {
                                                    src: Config.get('backendImagePath') + 'flags/' + lng.flag,
                                                    width: 16,
                                                    height: 16,
                                                    title: lng.title
                                                });

                                        var menuLink = $('<span>').attr(
                                                {
                                                    'rel': lng.id,
                                                    'alt': lng.code
                                                }).append(icon);

                                        menuLink.append(lng.title);

                                        var menuItem = $('<li>').append(menuLink);
                                        transMenu.append(menuItem);

                                        menuLink.click(function (e)
                                        {
                                            var newIcon = $(this).find('img').attr('src');
                                            e.preventDefault();

                                            var code = $(this).attr('alt');

                                            $.get('admin.php?setcontenttranslation=' + $(this).attr('rel'), {}, function (data)
                                            {

                                                if (Desktop.responseIsOk(data))
                                                {


                                                    $('#content-translations').find('img').attr('src', newIcon);
                                                    $('#contenttrans-selector').hide();
                                                    Config.set('contentLang', code);
                                                    Desktop.refreshAllWindowsAfterChangeContentLang(
                                                            function () {
                                                                clearTimeout(contextTimer);
                                                            }
                                                    );


                                                }
                                                else
                                                {
                                                    alert(data.msg);
                                                }
                                            });
                                            return false;
                                        });
                                    }
                                }
                            }

                        }
                        else
                        {
                            jAlert(data.msg);
                        }
                    });


                    $('#contenttrans-selector').mouseleave(function ()
                    {
                        contextTimer = setTimeout(function ()
                        {
                            contextDiv.hide();
                            btn.removeClass('active');
                        }, 500);
                    });

                    $('#contenttrans-selector').mouseenter(function ()
                    {
                        clearTimeout(contextTimer);
                        contextDiv.show();
                        btn.addClass('active');
                    });

                    $('#contenttrans-selector').show(0).each(function () {
                        $(this).css({
                            left: position.left + $('#content-translations').outerWidth(true) - $(this).outerWidth(true)
                        });
                        btn.addClass('active');
                    });

                }
                else
                {

                    if (!$('#contenttrans-selector').is(':visible'))
                    {
                        var position = $('#content-translations').offset();
                        $('#contenttrans-selector').show(0).each(function () {
                            $(this).css({
                                left: position.left + $('#content-translations').outerWidth(true) - $(this).outerWidth(true)
                            });
                            btn.addClass('active');
                        });

                        $('#contenttrans-selector').mouseleave(function ()
                        {
                            contextTimer = setTimeout(function ()
                            {
                                $('#contenttrans-selector').hide();
                                btn.removeClass('active');
                            }, 500);
                        });

                        $('#contenttrans-selector').mouseenter(function ()
                        {
                            clearTimeout(contextTimer);
                            $(this).show();
                            btn.addClass('active');
                        });
                    }
                }

            });

        },
        createSearch: function ()
        {
            if ($('#spotlight').length == 0)
            {

            }
        },
        getClock: function ()
        {
            var self = this;
            var now = new Date()
            var hours = now.getHours()
            var minutes = now.getMinutes()
            var seconds = now.getSeconds()
            var timeValue = "" + ((hours > 12) ? hours - 12 : hours)

            var timeValue1 = "" + ((hours < 10) ? "0" : "") + hours;
            timeValue += ((minutes < 10) ? " : 0" : " : ") + minutes;
            timeValue1 += ((minutes < 10) ? "<span id='blinking'>:</span>0" : "<span id='blinking'>:</span>") + minutes;
            timeValue += ((seconds < 10) ? "<span id='blinking'>:</span>0" : "<span id='blinking'>:</span>") + seconds;
            // timeValue1 += ((seconds < 10) ? ":0" : ":") + seconds;
            timeValue += (hours >= 12) ? " P.M." : " A.M.";


            var day = now.getDay();
            var month = now.getMonth();
            var year = now.getFullYear();

            var dayNameFormat = dateFormat.i18n[ (typeof dateFormat.i18n['de'] != 'undefined' ? 'de' : 'en') ].dayNames[0][day];
            var monFormat = dateFormat.i18n[ (typeof dateFormat.i18n['de'] != 'undefined' ? 'de' : 'en') ].monthNames[0][month];


            return {
                dayName: dayNameFormat,
                date: now.getDate(),
                monthName: monFormat,
                'year': year,
                'time': timeValue1
            };
        },
        /**
         *  Create a Taskbar Clock
         */
        createTaskClock: function (doStop)
        {
            if (doStop)
            {
                clearTimeout(this.timerBlinking);
                clearTimeout(this.timerID);
                $('#clock', $('#Tasks')).remove();
            }

            if (!$('#clock', $('#Tasks')).length)
            {
                $('#Tasks').empty().append($('<span id="clock"></span>'));

                // add the clock calendar
                $('#fullscreenContainer').append($('<div id="task-cal"></div>'));


                $('#clock', $('#Tasks')).click(function (e)
                {
                 //   console.log('click the clock');

                    if (!$('#task-cal').hasClass('hasDatepicker'))
                    {
                        $('#task-cal').datepicker({
                            firstDay: 1,
                            showButtonPanel: true,
                            beforeShow: function () {
                                $('#ui-datepicker-div').css({
                                    width: $('#clock', $('#Tasks')).width()
                                }).hide();

                                $('#task-cal').css({
                                    position: 'absolute',
                                    zIndex: 8000
                                }).hide();

                                $('#task-cal tbody td').removeAttr('onclick');
                                $(this).find('button:first').addClass('action-button');
                                $(this).find('.ui-datepicker-close').remove();
                                setTimeout(function () {
                                    $(this).find('.ui-datepicker-current').addClass('action-button');
                                }, 50);

                            }
                        });

                        $('#task-cal tbody td').removeAttr('onclick');
                    }

                    $('#task-cal').css({
                        position: 'absolute',
                        left: $('#clock', $('#Tasks')).offset().left,
                        zIndex: 10000
                    }).show();

                    if ($('#clock', $('#Tasks')).hasClass('calOpen'))
                    {
                        $('#clock', $('#Tasks')).removeClass('calOpen');
                        $('#task-cal').animate({
                            height: 0
                        }, 300, function () {
                            $(this).hide();
                        });
                    }
                    else
                    {
                        $('#ui-datepicker-div').hide();
                        $('#clock', $('#Tasks')).addClass('calOpen');
                        $('#task-cal').css({
                            position: 'absolute',
                            top: $('#Taskbar').outerHeight(true),
                            width: $('#clock', $('#Tasks')).outerWidth(),
                            height: 0,
                            left: $('#clock', $('#Tasks')).offset().left
                        }).show().animate({
                            height: 160
                        }, 300);
                    }
                });

            }





            if (Desktop.settings.TaskbarClockSeperatorBlinking)
            {
                clearTimeout(this.timerBlinking);
            }

            var self = this, t = this.getClock();
            $('#clock', $('#Tasks')).empty().append(t.dayName + '. ' + t.date + '. ' + t.monthName + ' ' + t.year + ' &nbsp; ' + t.time);

            // do blinking the time seperator?
            if (Desktop.settings.TaskbarClockSeperatorBlinking)
            {
                this._clockSecoundBlinking();
            }

            // updete clock after 60 sec.
            this.timerID = setTimeout(function () {
                Desktop.Taskbar.createTaskClock();
            }, 60000);

            this.timerRunning = true
        },
        _clockSecoundBlinking: function ()
        {
            if ($('#blinking').length)
            {
                var self = this;

                if ($('#blinking').text() === ':')
                    $('#blinking').text(' ');
                else
                    $('#blinking').text(':');

                this.timerBlinking = setTimeout(function () {
                    self._clockSecoundBlinking();
                }, 1100);
            }
        },
        // ---------------------------- END Clock function





        toggleUserLogin: function (event)
        {
            var self = this, o = $(event.target);




            if (!o.hasClass('active'))
            {
                o.addClass('active');
            }
            else
            {
                o.removeClass('active');
            }


            var w = $('#userMenu').show().outerWidth();
            $('#userMenu').hide();


            var posRight = $('#user-login', $('#Tasks-Core')).offset().left + $('#user-login', $('#Tasks-Core')).outerWidth();
            var top = $('#Taskbar').outerHeight(true);
            var left = posRight - w;
            $('#userMenu').css(
                    {
                        zIndex: 9999,
                        position: 'absolute',
                        'top': top,
                        'left': left
                    });



            if (!$('#userMenu').is(':visible'))
            {
                $('#userMenu').fadeIn(300);
            }
            else
            {
                $('#userMenu').fadeOut(200, function () {
                });
            }
        },
        updateByResize: function ()
        {
            var w = $('#userMenu').show().outerWidth();
            $('#userMenu').hide();
            var left = $(window).width() - w - 12;

            $('#userMenu').css(
                    {
                        'top': $('#Taskbar').outerHeight(true), // + $('.notify-tray-arrow', $('#UserLogin-notify')).height(),
                        'left': left
                    });
        }




    };

})(window);