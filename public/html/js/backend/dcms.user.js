var User = (function () {
    return {
        registerBookmarkButtonEvent: function ()
        {
            $('#dcmsFav').attr('title', cmslang.yourbookmarks);

            var contextTimer, self = this;

            $('#dcmsFav').unbind('click').bind('click', function (ev)
            {
                var btn = $(this);

                ev.preventDefault();
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

                    divContainer.appendTo('body');

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
                        if (!$('.isWindowContainer').length)
                        {
                            $('#fav-selector').hide();
                            return false;
                        }

                        var pageTitle = '';
                        var instance = Desktop.getActiveWindow();
                        var windata = $(instance).data('WindowManager');

                        if (!windata)
                        {
                            $('#fav-selector').hide();
                            return false;
                        }
                        else
                        {
                            var loc = windata.get('WindowURL');
                        }


                        var locate = loc;

                        if (locate != '')
                        {
                            locate = locate.replace(/&$/, '');
                        }

                        var pageCurrentTitle = windata.get('WindowTitle');
                        var currentPageIcon = windata.get('WindowDesktopIconFile');
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

                                        fav.url = fav.url.replace(/.*(admin\.php.*)/g, '$1');

                                        var icon = $('<img>').attr(
                                                {
                                                    src: (fav.icon != '' ? fav.icon : Config.get('backendImagePath') + 'bookmark.png'),
                                                    width: 16,
                                                    height: 16,
                                                    title: cmslang.removebookmark,
                                                    id: 'favid-' + fav.id
                                                }).css('cursor', 'pointer');

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
                                                'id': id,
												token: Config.get('token')
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
                            left: position.left + $('#dcmsFav').outerWidth(true) - $('#fav-selector').outerWidth(true)
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
                                left: position.left + $('#dcmsFav').outerWidth(true) - $('#fav-selector').outerWidth(true)
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

            });
        },
        logout: function ()
        {
            $.post('admin.php', {
                adm: 'auth',
                action: 'logout',
                ajax: 1,
				token: Config.get('token')
            }, function (data)
            {
                if (Tools.responseIsOk(data))
                {
                    Cookie.erase();

                    setTimeout(function ()
                    {
                        delete(Desktop.basicCMSData.userdata);
                        Launchpad.destroy();

                        document.location.href = document.location.href;

                    }, 300);
                }
                else
                {
                    console.log('logout error');
                }
            }, 'json');
        },
        personalSettings: function (e)
        {
            var opts = {
                WindowURL: Tools.prepareAjaxUrl('admin.php?adm=personal'),
                Controller: 'personal',
                controller: 'personal',
                Action: '',
                action: '',
                isStatic: false,
                isRootApplication: true
            };

            opts.url = Tools.prepareAjaxUrl(opts.WindowURL);
            opts.WindowID = Desktop.getHash(opts.WindowURL);
            if (!$('#' + opts.WindowID).length)
            {
                Desktop.getAjaxContent(opts, function (data)
                {
                    if (Tools.responseIsOk(data))
                    {
                        opts.WindowTitle = data.applicationTitle;
                        opts.label = data.applicationTitle;
                        opts.title = data.applicationTitle;
                        opts.loadWithAjax = true;
                        opts.allowAjaxCache = false;
                        opts.WindowToolbar = false;
                        opts.DesktopIconWidth = 36;
                        opts.DesktopIconHeight = 36;
                        opts.UseWindowIcon = false;
                        opts.Skin = Desktop.settings.Skin;
                        opts.WindowDesktopIconFile = '';

                        Desktop.ajaxWorkerOn = false;

                        Dock.createDockIcon(e, opts);
                        setTimeout(function () {
                            $('.dock-' + opts.Controller, $('#dock-inner')).click();
                        }, 150);
                    }
                    else
                    {
                        $.jAlert(Tools.exists(data, 'msg') ? data.msg : 'Personal Settings Error', 'Error', function () {
                            Debug.error(Tools.exists(data, 'msg') ? data.msg : 'Personal Settings Error', 'Error');
                        });
                    }
                });

            }
            else
            {
                $('#' + opts.WindowID).click();
            }
        }


    }
})(window);