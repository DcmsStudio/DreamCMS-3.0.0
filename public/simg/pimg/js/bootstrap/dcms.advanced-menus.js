/**
 * DreamCMS 3.0
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * 
 * PHP Version 5
 *
 * @package     Importer
 * @version     3.0.0 Beta
 * @category    Config
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Base.php
 */

var Tasks = {
    timerBlinking: 0,
    timerRunning: false,
    init: function () {

        var container = $('#menu-extras');
        container.empty();
        this.createTaskClock((this.timerRunning ? true : false));
        container.append($('<span id="content-translations" class="Taskbar-Item" title="' + cmslang.current_content_lang + '"><img src="' + Config.get('backendImagePath') + 'flags/' + Config.get('contenttranslationFlag') + '" width="16" height="16" /></span>'));
        if (Config.get('isSeemode') != true)
        {
            container.append($('<span id="dcmsFav" class="Taskbar-Item"><i class="fa fa-star"></i></span>'));
            container.append($('<span id="createbackup" title="' + cmslang.create_backup + '" class="Taskbar-Item"><i class="backup fa fa-refresh"></i><i class="runbackup fa fa-refresh fa-spin"></i></span>'));
            container.append($('<span id="console" title="' + cmslang.switch_console + '" class="Taskbar-Item"><i class="fa fa-bars"></i></span>'));
            container.append($('<span id="user-login" class="Taskbar-Item"><i class="fa fa-user"></i></span>'));



            container.append('<span id="switch-fullscreen" class="Taskbar-Item fullscreen"><i class="fa fa-expand"></span></span>');
        }


        Indexer.init();
        this.registerContentLangButtonEvents();
        this.registerBookmarkButtonEvent();
        $('#console').click(function () {
            if (!$('#gui-console').is(':visible')) {
                $(this).addClass('active');
                $('#gui-console').show();
            }
            else {
                $(this).removeClass('active');
                $('#gui-console').hide();
            }


            Core.updateViewPort();
            $(window).trigger('resize');
        });

        var clickCounter = 0;

        $('#createbackup').bind('click', function () {
            clickCounter++;
            if (clickCounter === 1) {

                if ($(this).find('.backup').is(':visible')) {

                    $(this).attr('title', 'Backup abbrechen...');
                    $(this).find('.backup').hide();
                    $(this).find('.runbackup').css({display: 'inline-block'});
                    var btn = $(this);
                    var iframe = $('<iframe>').attr('id', 'backup-iframe');
                    iframe.css({position: 'absolute', top: -1000, left: -1000, width: 0, height: 0}).attr('src', 'admin.php?adm=backup&action=create&mode=full');
                    iframe.on('load', function () {
                        var str = $(this).contents().text();
                        //  $(this).remove();
                        if (str && str != 'error') {
                            $(this).remove();
                            clickCounter = 0;
                            Notifier.info(str);
                            btn.find('.runbackup').hide();
                            btn.find('.backup').css({display: 'inline-block'});

                        } else {
                            clickCounter = 0;
                            btn.find('.runbackup').hide();
                            btn.find('.backup').css({display: 'inline-block'});
                            Notifier.error('Backup Error');

                        }
                    });

                    $('body').append(iframe);

                }
            }
            else {

                var el = $(this);
                $.get('admin.php?adm=backup&action=create&cancel=true', function (data) {
                    if (Tools.responseIsOk(data)) {
                        $('#backup-iframe').unbind('load').attr("src", "javascript:false;").remove();
                        el.attr('title', 'Backup anlegen...');
                        clickCounter = 0;
                        el.find('.runbackup').hide();
                        el.find('.backup').css({display: 'inline-block'});
                        if (data.msg) {
                            Notifier.info(data.msg);
                        }

                    }
                });
            }
        });


		$('html' ).attr('id', 'fsc');



        $('#switch-fullscreen').bind('click', function () {
            var _element = document.getElementById("fsc");

            if (_element.mozRequestFullScreen) {

                if (!document.mozFullScreen) {
                    $(this).find('i').removeClass('fa-expand').addClass('fa-compress');
                    _element.mozRequestFullScreen();
                } else {
                    $(this).find('i').removeClass('fa-compress').addClass('fa-expand');
                    document.mozCancelFullScreen();
                }

            } else if (_element.webkitRequestFullScreen) {

                if (!document.webkitIsFullScreen) {
                    $(this).find('i').removeClass('fa-expand').addClass('fa-compress');
					_element.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);

                } else {
                    $(this).find('i').removeClass('fa-compress').addClass('fa-expand');
                    document.webkitCancelFullScreen();
                }
            }
			else if (_element.requestFullScreen) {
				if (!document.fullScreen) {
					$(this).find('i').removeClass('fa-expand').addClass('fa-compress');
					_element.requestFullscreen();
				} else {
					$(this).find('i').removeClass('fa-compress').addClass('fa-expand');
					document.exitFullScreen();
				}
			}
        });

    },
    /**
     *
     *
     */

    registerBookmarkButtonEvent: function ()
    {
        $('#dcmsFav').attr('title', cmslang.yourbookmarks);
        var contextTimer, self = this;
        $('#dcmsFav').unbind('click').bind('click', function (ev)
        {
            var btn = $(this);
            $('#contenttrans-selector,#fav-selector').hide();
            $("#popup_overlay_remove").removeData().remove();
            if ($('#fav-selector').length == 0)
            {
                var top = $('#Taskbar').outerHeight(true);
                var position = $('#dcmsFav').offset();
                var divContainer = $('<div>').attr('id', 'fav-selector').css(
                        {
                            'top': top,
                            position: 'absolute',
                            zIndex: 9999
                        }).addClass('menu');
                
                
                
                if ($('#fullscreenContainer').length) {
                    divContainer.appendTo($('#fullscreenContainer'));
                }
                else {
                    divContainer.appendTo('body');
                }
                
                var favMenu = $('<ul>').addClass('inner');
                favMenu.appendTo(divContainer);
                var icon = $('<img>').attr(
                        {
                            src: Config.get('backendImagePath') + 'bookmark-add.png',
                            width: 16,
                            height: 16
                        });
                var menuLink = $('<span>').append(icon).append(cmslang.addbookmark);
                var menuItem = $('<li>').append(menuLink);
                menuLink.click(function (e)
                {
                    e.preventDefault();
                    if (!$('#main-tabs li.active').length)
                    {
                        $('#fav-selector').hide();
                        return false;
                    }

                    var pageTitle = '';
                    var windata = $('#main-tabs li.active').data('itemData');
                    if (!windata)
                    {
                        $('#fav-selector').hide();
                        return false;
                    }
                    else
                    {
                        var loc = windata.url;
                    }


                    var locate = loc;
                    if (locate != '')
                    {
                        locate = locate.replace(/&$/, '');
                    }

                    var pageCurrentTitle = windata.label;
                    var currentPageIcon = windata.icon;
                    pageCurrentTitle = (pageCurrentTitle != '' ? pageCurrentTitle : '');
                    currentPageIcon = (currentPageIcon != '' ? escape(currentPageIcon) : '');
                    $.get('admin.php?adm=bookmark&action=add&url=' + escape((locate != '' ? locate : document.location.href)) + '&title=' + pageCurrentTitle + '&icon=' + currentPageIcon, {
                    }, function (data)
                    {
                        $('#dcmsFav').removeClass('active');
                        if (!Tools.responseIsOk(data))
                        {
                            alert((data.msg != '' ? data.msg : data.error));
                        }
                        else
                        {

                            $('#fav-selector').removeData().remove();
                            $('#fav-selector').hide();
                            clearTimeout(contextTimer);
                        }
                    }, 'json');
                });
                favMenu.append(menuItem);
                var sep = $('<li>').addClass('separator').append('<div class="menu-separator"></div>');
                favMenu.append(sep);
                $.get('admin.php?adm=bookmark', {
                }, function (data)
                {
                    if (Tools.responseIsOk(data))
                    {
                        if (data.favorites)
                        {
                            var idx = 0;
                            for (var i in data.favorites)
                            {
                                var fav = data.favorites[i];
                                if (fav.url && fav.title && fav.title != '')
                                {
                                    var icon;
                                    fav.url = fav.url.replace(/.*(admin\.php.*)/g, '$1');
                                    if (fav.icon && typeof fav.icon != 'undefined' && fav.icon != 'undefined') {
                                        icon = $('<img>').attr(
                                                {
                                                    src: (fav.icon != '' ? fav.icon : Config.get('backendImagePath') + 'bookmark.png'),
                                                    width: 16,
                                                    height: 16,
                                                    title: cmslang.removebookmark,
                                                    id: 'favid-' + fav.id
                                                }).css('cursor', 'pointer');
                                    }
                                    else {
                                        icon = $('<img>').attr(
                                                {
                                                    src: Config.get('backendImagePath') + 'spacer.gif',
                                                    width: 16,
                                                    height: 16,
                                                    title: cmslang.removebookmark,
                                                    id: 'favid-' + fav.id
                                                }).css('cursor', 'pointer');
                                    }

                                    icon.hover(function ()
                                    {
                                        $(this).attr('osrc', $(this).attr('src'));
                                        $(this).attr('src', Config.get('backendImagePath') + 'delete.gif');
                                    }, function ()
                                    {
                                        $(this).attr('src', $(this).attr('osrc'));
                                    });
                                    icon.click(function (e)
                                    {
                                        var id = $(this).attr('id');
                                        id = id.replace('favid-', '');
                                        var listitem = $(this).parents('li:first');
                                        $.post('admin.php', {
                                            adm: 'bookmark',
                                            action: 'delete',
                                            'id': id
                                        }, function (data)
                                        {
                                            if (Tools.responseIsOk(data))
                                            {
                                                listitem.remove();
                                            }
                                        });
                                        e.preventDefault();
                                        return false;
                                    });
                                    var menuLink = $('<span>').attr('url', fav.url).append(icon);
                                    if (fav.title != '')
                                    {
                                        menuLink.append(fav.title);
                                    }
                                    else
                                    {
                                        menuLink.append(fav.url);
                                    }


                                    var menuItem = $('<li>').append(menuLink);
                                    favMenu.append(menuItem);
                                    menuLink.click(function (e)
                                    {
                                        var url = $(this).attr('url');
                                        Desktop.loadTab(e, {url: url, obj: $(this), label: $(this).text()});
                                        //document.location.href = url;
                                        e.preventDefault();
                                        divContainer.hide();
                                        return false;
                                    });
                                }

                                idx++;
                            }
                        }


                    }
                    else
                    {
                        alert(data.msg);
                    }
                });
                btn.addClass('active');
                divContainer.show();
                divContainer.mouseleave(function ()
                {
                    contextTimer = setTimeout(function ()
                    {
                        btn.removeClass('active');
                        divContainer.hide();
                    }, 500);
                });
                divContainer.mouseenter(function ()
                {
                    btn.addClass('active');
                    divContainer.show();
                    clearTimeout(contextTimer);
                });
                setTimeout(function () {
                    $('#fav-selector').css({
                        left: position.left + $('#dcmsFav').outerWidth(true) - $('#fav-selector').outerWidth(true) + 1
                    });
                }, 20);
            }
            else
            {

                if (!$('#fav-selector').is(':visible'))
                {

                    btn.addClass('active');
                    var position = $('#dcmsFav').offset();
                    $('#fav-selector').show();
                    setTimeout(function () {
                        $('#fav-selector').css({
                            left: position.left + $('#dcmsFav').outerWidth(true) - $('#fav-selector').outerWidth(true) + 1
                        });
                    }, 20);
                    $('#fav-selector').mouseleave(function ()
                    {
                        contextTimer = setTimeout(function () {
                            $('#fav-selector').hide();
                            btn.removeClass('active');
                        }, 500);
                    });
                    $('#fav-selector').mouseenter(function ()
                    {
                        $(this).show();
                        btn.addClass('active');
                        clearTimeout(contextTimer);
                    });
                }
            }

            ev.preventDefault();
        });
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

                var top = $('#menu-extras').outerHeight(true);
                var position = $('#content-translations').offset();
                var contextDiv = $('<div>').attr('id', 'contenttrans-selector').addClass('menu').css({
                    'top': top,
                    position: 'absolute',
                    zIndex: 9999
                });
                var transMenu = $('<ul>').addClass('inner');
                contextDiv.append(transMenu);
                
                
                if ($('#fullscreenContainer').length) {
                    contextDiv.appendTo($('#fullscreenContainer'));
                }
                else {
                    contextDiv.appendTo('body');
                }
                
                
                
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
                                    menuItem.click(function (e)
                                    {

                                        var newIcon = $(this).find('img').attr('src');
                                        e.preventDefault();
                                        var code = $(this).find('span').attr('alt');
                                        $.get('admin.php?setcontenttranslation=' + $(this).find('span').attr('rel'), {}, function (data)
                                        {
                                            if (Tools.responseIsOk(data))
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
                var position = $('#content-translations').offset();
                $('#contenttrans-selector').show(0).each(function () {
                    $(this).css({
                        left: position.left + $('#content-translations').outerWidth(true) - $(this).width() + 1
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
                            left: position.left + $('#content-translations').outerWidth(true) - $(this).width() + 1
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
     * Create Taskbar Clock
     * @param {bool} doStop
     * @returns {undefined}
     */
    createTaskClock: function (doStop)
    {
        if (doStop) {
            clearTimeout(this.timerBlinking);
            $('#clock', $('#menu-extras')).remove();
        }

        if (!$('#clock', $('#menu-extras')).length)
        {
            $('#menu-extras').empty().append($('<span id="clock"></span>'));
            // add the clock calendar
            
            if ($('#fullscreenContainer').length) {
                $('#fullscreenContainer').append($('<div id="task-cal"></div>'));
            }
            else {
                $('body').append($('<div id="task-cal"></div>'));
            }
            

            $('#clock', $('#menu-extras')).click(function (e)
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
                    top: $('#clock', $('#menu-extras')).offset().top + $('#clock', $('#menu-extras')).height,
                    left: $('#clock', $('#menu-extras')).offset().left + $('#clock', $('#menu-extras')).outerWidth(true) - $('#task-cal').outerWidth(true) + 1,
                    zIndex: 10000
                }).show();

                if ($('#clock', $('#menu-extras')).hasClass('calOpen'))
                {
                    $('#clock', $('#menu-extras')).removeClass('calOpen');
                    $('#task-cal').hide();
                }
                else
                {
                    $('#ui-datepicker-div').hide();
                    $('#clock', $('#menu-extras')).addClass('calOpen');
                    $('#task-cal').css({
                        position: 'absolute',
                        top: $('#menu-extras').outerHeight(true),
                        left: $('#clock', $('#menu-extras')).offset().left + $('#clock', $('#menu-extras')).outerWidth(true) - $('#task-cal').outerWidth(true) + 1
                    }).show();
                    var t;
                    $('#task-cal').unbind().bind('mouseenter', function () {
                        clearTimeout(t);
                    });
                    $('#task-cal').unbind().bind('mouseleave', function () {
                        var self = this;
                        t = setTimeout(function () {
                            $('#clock', $('#menu-extras')).removeClass('calOpen');
                            $(self).hide();
                        }, 300);
                    });
                }
            });
        }





        if (!Config.get('TaskbarClockSeperatorBlinking'))
        {
            clearTimeout(this.timerBlinking);
        }

        var self = this, t = this.getClock();
        $('#clock', $('#menu-extras')).empty().append(t.dayName + '. ' + t.date + '. ' + t.monthName + ' ' + t.year + ' &nbsp; ' + t.time);
        // do blinking the time seperator?
        if (Config.get('TaskbarClockSeperatorBlinking'))
        {
            clearTimeout(this.timerBlinking);
            this._clockSecoundBlinking();
        }

        // updete clock after 60 sec.
        this.timerID = setTimeout(function () {
            self.createTaskClock();
        }, 60000);
        this.timerRunning = true;
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
    }
};