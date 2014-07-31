var deph = 1;

$(document).ready(function () {
    // empty the Help Index
    // $('#help-index').empty();

    if ($.browser.opera) {
    }


    var hidetimer;
    var loadtimer;
    var lastSubmenu = false;
    // build up the menu
    for (var help_idx in helpItems) {
        //var i = 0, childs;
        //while( childs = menuItems[i++] )
        //{
        tmenu = helpItems[help_idx];

        // create the li
        node = buildTopLevelHelpNode(tmenu, help_idx);

        // add the li to the menu
        $('#help-index').append(node);
    }
    $('#help-index label').remove();
    // remove the last separator
    //$('#help-index').find('.rootmenu-separator:last').remove();
    //$('#help-index').help();

});


function buildTopLevelHelpNode(menu, index) {
    var il = $('<div>');
    var root = $('<div>').addClass('TopLevelHelp');

    // create the link
    span = $('<span>');
    span.click(function () {
        _self = this;
        $('#help_root_' + index).slideToggle('fast', function () {
            if ($(this).is(':visible')) {
                $(_self).parent().addClass('TopLevelHelpOpen');
            }
            else {
                $(_self).parent().removeClass('TopLevelHelpOpen');
            }
        });
    });
    // append the link text to the link
    span.append(menu.label);


    if (menu.icon != '' && menu.icon != null) {
        // create the icon
        img = $('<img>').attr({
            width: 16,
            height: 16,
            alt: '',
            src: settings.base_url + menu.icon
        });

        // append the icon
        root.append(img);
    }

    // put the link in the li
    root.append(span);


    li.append(root);
    if (menu.items) {
        var ul = $('<div>').addClass('helpItems').attr('id', 'help_root_' + index).css({display: 'none'});

        // for ( var child in menu.items) {
        //	ul.append(buildChildNode(menu.items[child]));
        // }
        var i = 0, child;
        deph++;
        while (child = menu.items[i++]) {
            ul.append(buildChildHelpNode(child, index, i));
        }
        deph--;
        li.append(ul);
    }

    return li;
}

function buildChildHelpNode(menu, index, i) {


    if (menu.label == '' || menu.type == 'separator') return '';
    var root = $('<div>').addClass('ChildHelp').attr('id', 'help_' + index);
    var li = $('<div>');
    if (menu.items) {
        li.addClass('groupHelp').css({'padding-left': (deph * 6) + 'px'});
    }
    else {
        li.addClass('HelpItem').css({'padding-left': (deph * 9) + 'px'});
    }


    if (!menu.items && menu.type != 'separator' && menu.label != '') {
        if (!menu.url) {
            menu.url = '#'
        }

        href = menu.url;

        a = $('<a>').attr('href', 'javascript:void(0);');

        if (menu.icon) {
            if (menu.icon.indexOf('/') == -1) {
                icon_url = settings.base_url + menu.icon;
            } else {
                icon_url = settings.base_url + menu.icon;
            }

            img = $('<img>').attr({
                width: 16,
                height: 16,
                alt: '',
                src: icon_url
            });

        }
        else {
            img = $('<img>').attr({
                width: 16,
                height: 16,
                alt: '',
                src: settings.base_url + backendImagePath + 'spacer.gif'
            });
        }

        a.append(menu.label);

        // Seperator
        if (menu.url == '#' && !menu.click) {
            return '';
        }
        else if (menu.url != '#' && !menu.click) {


            li.append(img);
            li.append(a);
            li.bind('click', function (e) {

                $('#help-content').mask('Lade Hilfe Content "' + menu.label + '" ...');
                act = menu.controller + '|' + menu.action;

                $.get('admin.php?adm=help&get=' + act, {}, function (data) {
                    $('#help-title').html(menu.label);
                    $('#help-con').html(data.content);
                    $('#help-content').unmask();
                }, 'json');

            });
        }
    }

    if (menu.click) {
        $(li).bind('click', function (e) {
            this.blur();
            //hideMenu();	
            eval(menu.click);
            e.preventDefault();
        });
    }

    if (menu.items) {
        icon_url = settings.base_url + '' + menu.icon;

        img = $('<img>').attr({
            width: 16,
            height: 16,
            alt: '',
            'class': 'spacer',
            src: ( menu.icon ? icon_url : settings.base_url + backendImagePath + 'spacer.gif')
        });

        ie_fix = '';
        if ($.browser.msie) {
            ie_fix = ' style="float:left"';
        }

        li.append(img).append('<span' + ie_fix + '>' + menu.label + '</span>');

        li.click(function () {
            $('#help_' + index + '_' + i).slideToggle('fast');
        });


    }

    root.append(li);

    if (menu.items) {
        deph++;
        var ul = $('<div>').addClass('ChildHelpSub').attr('id', 'help_' + index + '_' + i).css({display: 'none'});
        var childs;
        for (childs in menu.items) {
            //
            //while( childs = menu.items[i++] )
            //{
            ul.append(buildChildHelpNode(menu.items[childs], index, i));
        }
        root.append(ul);
        deph--;
    }
    return root;
}


$.fn.help = function () {

    return this.each(function () {
        var self = this;

        // label the root menu
        $(this).addClass('help-root');

        // label the submenu toggles
        $(this).find('div:has(div)').addClass('ui-help-toggle');

        // label the sub menus
        $(this).children().children('div').addClass('ui-help');

        // label the sub menus
        $(this).children().children().find('div').addClass('ui-sub-help');

        // remove extra margin around separators
        $(this).find('div.separator').each(function () {
            $(this).prev().css({
                marginBottom: 0
            });
            $(this).next().css({
                marginTop: 0
            });
        });

        // add submenu icon


        // bind a click handler to the document
        $(document)
            .bind(
            'click',
            function (e) {

                if ($(e.target).parents('div.ui-help-root').length == 0) {
                    $(self).find('.action-button-selected')
                        .removeClass(
                            'action-button-selected');
                    /*
                     $(self)
                     .find(
                     'ul.ui-sub-help:visible,ul.ui-help:visible')
                     .fadeOut();
                     */
                }
            });

        // show top level menus on click
        $(this).children().children('span').bind(
            'click',

            function (e) {
                $(this).blur();
                $(self).find('div.ui-sub-help:visible,div.ui-help:visible').hide();
                $(self).find('.action-button-selected').removeClass('action-button-selected');
                $(this).addClass('action-button-selected');
                //pos = $(this).offset();
                $(this).parent().children('div').css({
                    display: 'block'
                });
                e.preventDefault();
                return false;
            });

        // show top level menus on mouseover, if a menu is already open
        $(this).children().children('span').bind(
            'click',
            function () {

                if ($(self).find('div.ui-help:visible').length != 0) {
                    $(self).find('.action-button-selected').removeClass('action-button-selected');
                    $(this).addClass('action-button-selected');
                    $(self).find('div.ui-help').hide();
                    $(this).parent().children('div').css({
                        display: 'block'
                    });
                }
            });

        // show submenus on hover
        $(this).children().find('li:has(div)').bind('click',
            function () {
                if (!$(this).parent().is('div.ui-sub-help:visible') && $(this).parent().find('div:has(div)')) {
                    $(self).find('div.ui-sub-help:visible').hide();
                }

                pos = $(this).offset();
                $(this).children('div').css({
                    paddingLeft: pos.left + $(this).parent().width() - 10,
                    top: pos.top - $(window).scrollTop() - 2,
                    display: 'block'
                });
            });

        // hide submenus on hover over other lis
        $(this).children().find('div').each(function () {
            if ($(this).children().find('div')) {
                $(this).bind('click', function () {
                    if (!$(this).children().is('div.ui-sub-help:visible')) {
                        $(this).parent().children().find('div.ui-sub-help:visible').hide();
                    }
                });
            }
        });

    });

}
