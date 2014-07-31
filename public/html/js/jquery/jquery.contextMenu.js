// jQuery Context Menu Plugin
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
//
// More info: http://abeautifulsite.net/2008/09/jquery-context-menu-plugin/
//
// Terms of Use
//
// This plugin is dual-licensed under the GNU General Public License
//   and the MIT License and is copyright A Beautiful Site, LLC.
//
if (jQuery)
    (function () {

        function _createContextMenu (e, el, o, srcElement, callback)
        {
            // Get this context menu
            var menu = $('#' + o.menu);
            var offset = $(el).offset();


            if ($(el).hasClass('disabled') || $(menu).hasClass('disabled'))
                return false;




            // Hide bindings
            setTimeout(function () { // Delay for Mozilla
                $(document).bind('click.contextmenu', function () {

                    $(document).unbind('click.contextmenu').unbind('keypress.contextmenu');
                    $('#' + o.menu).hide();
                    return false;
                });
            }, 10);

            // $(menu).find('LI.disabled').removeClass('disabled');

            if (o.disable != undefined)
            {
                var d = o.disable.split(',');
                for (var i = 0; i < d.length; i++) {
                    $(menu).find('A[href="' + d[i] + '"]').parent().addClass('disabled');
                }
            }
            else
                // Detect mouse position
                var d = {}, x, y;
            if (self.innerHeight) {
                d.pageYOffset = self.pageYOffset;
                d.pageXOffset = self.pageXOffset;
                d.innerHeight = self.innerHeight;
                d.innerWidth = self.innerWidth;
            } else if (document.documentElement &&
                    document.documentElement.clientHeight) {
                d.pageYOffset = document.documentElement.scrollTop;
                d.pageXOffset = document.documentElement.scrollLeft;
                d.innerHeight = document.documentElement.clientHeight;
                d.innerWidth = document.documentElement.clientWidth;
            } else if (document.body) {
                d.pageYOffset = document.body.scrollTop;
                d.pageXOffset = document.body.scrollLeft;
                d.innerHeight = document.body.clientHeight;
                d.innerWidth = document.body.clientWidth;
            }

            (e.pageX) ? x = e.pageX : x = e.clientX + d.scrollLeft;
            (e.pageY) ? y = e.pageY : x = e.clientY + d.scrollTop;
            /*
             var h = $(menu).height();
             var mtop = offset.top;
             
             if ((mtop - h) > y)
             {
             var y = y - h;
             }
             else if ((mtop + h) < y)
             {
             var y = y - h;
             }
             */

            var menuWidth = $(menu).width();
            var menuHeight = $(menu).height();

            if (x + menuWidth > $(document).width())
            {
                x = x - menuWidth;
            }

            if (y + menuHeight > $(document).height())
            {
                y = y - menuHeight;
            }




            $(menu).css({top: y - 5, left: x - 5}).fadeIn(o.inSpeed, function () {


                $(this).find('li').removeClass('firstitem lastitem')
                $(this).find('li:visible:first').addClass('firstitem');
                $(this).find('li:visible:last').addClass('lastitem');
            });

            // Hover events
            $(menu).find('A').mouseover(function () {
                $(menu).find('LI.hover').removeClass('hover');
                $(this).parent().addClass('hover');
            }).mouseout(function () {
                $(menu).find('LI.hover').removeClass('hover');
            });

            // Keyboard
            $(document).bind('keypress.contextmenu', function (e) {

                switch (e.keyCode) {
                    case 38: // up
                        if ($(menu).find('LI.hover').size() == 0) {
                            $(menu).find('LI:last').addClass('hover');
                        } else {
                            $(menu).find('LI.hover').removeClass('hover').prevAll('LI:not(.disabled)').eq(0).addClass('hover');
                            if ($(menu).find('LI.hover').size() == 0)
                                $(menu).find('LI:last').addClass('hover');
                        }
                        break;
                    case 40: // down
                        if ($(menu).find('LI.hover').size() == 0) {
                            $(menu).find('LI:first').addClass('hover');
                        } else {
                            $(menu).find('LI.hover').removeClass('hover').nextAll('LI:not(.disabled)').eq(0).addClass('hover');
                            if ($(menu).find('LI.hover').size() == 0)
                                $(menu).find('LI:first').addClass('hover');
                        }
                        break;
                    case 13: // enter
                        $(menu).find('LI.hover A').trigger('click.contextitem');
                        break;
                    case 27: // esc
                        $(document).trigger('click.contextmenu');
                        break
                }
            });

            // When items are selected
            $('#' + o.menu).find('A').unbind('click.contextitem');
            $('#' + o.menu).find('LI:not(.disabled) A').bind('click.contextitem', function (e) {
                $(document).unbind('click.contextmenu').unbind('keypress.contextmenu');
                $(menu).hide();

                // Callback
                if (callback)
                {
                    callback($(this).attr('href').substr(1), $(srcElement), {x: x - offset.left, y: y - offset.top, docX: x, docY: y}, e);
                }
                
                return false;
            });
        }


        $.extend($.fn, {
            contextMenu: function (o, callback)
            {
                // Defaults
                if (o.menu == undefined)
                    return false;
                if (o.inSpeed == undefined)
                    o.inSpeed = 150;
                if (o.outSpeed == undefined)
                    o.outSpeed = 75;
                // 0 needs to be -1 for expected results (no fade)
                if (o.inSpeed == 0)
                    o.inSpeed = -1;
                if (o.outSpeed == 0)
                    o.outSpeed = -1;



                $(document).bind("contextmenu", function () {
                    // return false;
                });


                // Loop each context menu
                $(this).each(function () {
                    var el = $(this);


                    if ($('#' + o.menu).parents('#maincontent:first').length)
                    {
                        $('#' + o.menu).appendTo('body');
                    }

                    // Add contextMenu class
                    $('#' + o.menu).addClass('dcmscontextmenu');

                    // Simulate a true right click

                    $(el).bind('mousedown.contextmenu', function (e) {
                        var evt = e;
                        e.stopPropagation();
                        //e.stopPropagation();
                        //$(this).mouseup( function(e) {
                        //e.stopPropagation();
                        var srcElement = $(this);
                        //$(this).unbind('mouseup');
                        if (e.which === 3)
                        {
                            // Hide context menus that may be showing
                            $('#' + o.menu).hide();
                            if (typeof o.onBeforeShow === 'function')
                            {
                                o.onBeforeShow(e, $('#' + o.menu), function ()
                                {
                                    _createContextMenu(e, el, o, srcElement, callback);
                                });
                            }
                            else
                            {
                                _createContextMenu(e, el, o, srcElement, callback);
                            }

                        }
                        else
                        {
                            if ($('#' + o.menu).is(':visible'))
                            {
                                if (!$(e.target).hasClass('dcmscontextmenu') && $(e.target).parents('.dcmscontextmenu').length == 0)
                                {
                                    $(document).unbind('click.contextmenu').unbind('keypress.contextmenu');
                                    $('#' + o.menu).hide();
                                }
                            }
                        }
                        //});
                    });



                    // Disable text selection
                    if ($.browser.mozilla) {
                        $('#' + o.menu).each(function () {
                            $(this).css({'MozUserSelect': 'none'});
                        });
                    } else if ($.browser.msie) {
                        $('#' + o.menu).each(function () {
                            $(this).bind('selectstart.disableTextSelect', function () {
                                return false;
                            });
                        });
                    } else {
                        $('#' + o.menu).each(function () {
                            $(this).bind('mousedown.disableTextSelect', function () {
                                return false;
                            });
                        });
                    }
                    // Disable browser context menu (requires both selectors to work in IE/Safari + FF/Chrome)
                    $(el).add($('UL.dcmscontextmenu')).bind('contextmenu', function () {
                        return false;
                    });

                });



                return $(this);
            },
            hideContextItems: function (o)
            {
                if (o == undefined) {
                    $(this).find('LI').hide().removeClass('first').removeClass('last');
                    return($(this));
                }

                $(this).each(function () {
                    if (o != undefined) {
                        var d = o.split(',');
                        for (var i = 0; i < d.length; i++)
                        {
                            $(this).find('A[href="' + d[i] + '"]').parent().hide().removeClass('first').removeClass('last');
                        }
                    }
                });

                $(this).find('li:visible:first').addClass('first');
                $(this).find('li:visible:last').addClass('last');



                return($(this));
            },
            showContextItems: function (o)
            {
                if (o == undefined) {
                    $(this).find('LI').show().removeClass('first').removeClass('last');

                    $(this).find('li:visible:first').addClass('first');
                    $(this).find('li:visible:last').addClass('last');

                    return($(this));
                }


                $(this).find('li.first').removeClass('first');
                $(this).find('li.last').removeClass('last');


                $(this).each(function () {
                    if (o != undefined) {
                        var d = o.split(',');
                        for (var i = 0; i < d.length; i++)
                        {
                            $(this).find('A[href="' + d[i] + '"]').parent().show().removeClass('first').removeClass('last');
                        }
                    }
                });


                $(this).find('li:visible:first').addClass('first');
                $(this).find('li:visible:last').addClass('last');



                return($(this));
            },
            // Disable context menu items on the fly
            disableContextMenuItems: function (o) {
                if (o == undefined) {
                    // Disable all
                    $(this).find('LI').addClass('disabled');
                    return($(this));
                }
                $(this).each(function () {
                    if (o != undefined) {
                        var d = o.split(',');
                        for (var i = 0; i < d.length; i++)
                        {
                            $(this).find('A[href="' + d[i] + '"]').parent().addClass('disabled');

                        }
                    }
                });
                return($(this));
            },
            // Enable context menu items on the fly
            enableContextMenuItems: function (o) {
                if (o == undefined) {
                    // Enable all
                    $(this).find('LI.disabled').removeClass('disabled');
                    return($(this));
                }
                $(this).each(function () {
                    if (o != undefined) {
                        var d = o.split(',');
                        for (var i = 0; i < d.length; i++) {
                            $(this).find('A[href="' + d[i] + '"]').parent().removeClass('disabled');

                        }
                    }
                });
                return($(this));
            },
            // Disable context menu(s)
            disableContextMenu: function () {
                $(this).each(function () {
                    $(this).addClass('disabled');
                });
                return($(this));
            },
            // Enable context menu(s)
            enableContextMenu: function () {
                $(this).each(function () {
                    $(this).removeClass('disabled');
                });
                return($(this));
            },
            // Destroy context menu(s)
            destroyContextMenu: function () {
                // Destroy specified context menus
                $(this).each(function () {
                    // Disable action
                    $(this).unbind('mousedown.disableTextSelect').unbind('mouseup.contextmenu');
                });




                return($(this));
            }

        });
    })(jQuery);