Desktop.Icons = ns('Desktop.Icons');

Desktop.Icons = {
    settings: {
        WindowDesktopIcon: true
    },
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
    /**
     *  Create the Desktop Icons after load Desktop
     *  
     *  @todo add the last positions :)
     */
    selectedDesktopIcons: $([]),
    selectedDesktopIconsOffset: {
        top: 0,
        left: 0
    },
    initDesktopIcons: function ()
    {
        var self = this, desktopIcons = $("#DesktopIcons"),
                folders = Tools.exists(Desktop.basicCMSData, 'desktopfolders') ? Desktop.basicCMSData.desktopfolders : [],
                icons = Tools.exists(Desktop.basicCMSData, 'desktopicons') ? Desktop.basicCMSData.desktopicons : [];


        if (typeof icons.iconWidth != 'undefined') {
            this.iconsize.iconWidth = parseInt(icons.iconWidth, 10);
            this.iconsize.subIconWidth = (parseInt(icons.iconWidth, 10) / 2) - 4;

            delete icons.iconWidth;
            delete icons.subIconWidth;
        }

        if (typeof icons.iconLabelPos != 'undefined') {
            this.iconLabelPos = icons.iconLabelPos;

            delete icons.iconLabelPos;
        }

        if (typeof icons.iconGutterSize != 'undefined') {
            this.iconGutterSize = parseInt(icons.iconGutterSize, 10);

            delete icons.iconGutterSize;
        }

        if (typeof icons.showObjectInfo != 'undefined') {
            this.showObjectInfo = icons.showObjectInfo;

            delete icons.showObjectInfo;
        }

        if (typeof icons.iconSort != 'undefined') {
            this.iconSort = icons.iconSort;

            delete icons.iconSort;
        }

        // console.log([icons]);

        if ($("#DesktopIcons").length == 0)
        {
            $('#desktop').append($('<div id="DesktopIcons"/>'));
            desktopIcons = $("#DesktopIcons");
        }

        desktopIcons.empty();

        // console.log('create Folders : ' + folders.length);

        if (folders.length > 0)
        {
            for (var i = 0; i < folders.length; i++)
            {
                var folder = folders[i], folderID = this.folderIdNum;

                //    console.log('create Folder: ' + folder.name);
                var FolderTemplate = Template.setTemplate(Desktop.Templates.DesktopFolder).process(
                        {
                            id: folderID,
                            label: folder.name
                        });

                this.folderIdNum++;

                if (folder.top)
                {
                    $(FolderTemplate).css({
                        top: folder.top
                    })
                }

                if (folder.left)
                {
                    $(FolderTemplate).css({
                        left: folder.left
                    })
                }



                $("#DesktopIcons").append($(FolderTemplate).css({
                    top: folder.top,
                    left: folder.left
                }));


                Template.reset();



                var BalloonTemplate = Template.setTemplate(Desktop.Templates.Balloon).process(
                        {
                            id: folderID,
                            label: folder.name
                        });
                Template.reset();



                $(BalloonTemplate).hide().appendTo(desktopIcons);


                var balloonUL = $('#sub-container-' + folderID, desktopIcons).find('ul');

                $('#DesktopFolder' + folderID).data('size', 0);

                for (var x = 0; x < folder.items.length; x++)
                {
                    var itemHash = folder.items[x];

                    if (itemHash) {

                        //console.log('get Folder icon : '+ itemHash);

                        if (typeof icons[ itemHash ] != 'undefined')
                        {
                            var iconOpt = icons[ itemHash ];

                            //console.log('Folder icon : '+ itemHash);

                            iconOpt.WindowURL = iconOpt.url;
                            iconOpt.WindowTitle = iconOpt.label;
                            iconOpt.Controller = iconOpt.controller;
                            iconOpt.Action = iconOpt.action;


                            iconOpt.loadWithAjax = true;
                            iconOpt.allowAjaxCache = false;
                            iconOpt.UseWindowIcon = false;
                            iconOpt.isRootApplication = true;  // used in Dock
                            iconOpt.isStatic = false; // used in Dock

                            // reset current ajaxData
                            Desktop.ajaxData = {};
                            Desktop.ajaxData = $.extend({}, iconOpt);


                            if (typeof Desktop.ajaxData.toolbar != 'undefined')
                            {
                                iconOpt.WindowToolbar = Desktop.ajaxData.toolbar;
                            }

                            iconOpt.WindowStatus = 'closed';
                            iconOpt.WindowDesktopIconFile = iconOpt.DesktopIconFilename;

                            // create the Icon
                            this.createDesktopIcon(null, iconOpt, null);


                            var hashID = $.fn.getWindowHash(iconOpt.WindowURL);
                            iconOpt.WindowStatus = 'closed';

                            $('#DesktopIcon' + hashID).hide();
                            $('#DesktopIcon' + hashID).data('itemData', iconOpt)
                                    .attr('data-id', 'id' + this.iconIdNum)
                                    .attr('data-size', iconOpt.bytesize);

                            self.addDragDropToDesktopIcon($('#DesktopIcon' + hashID));

                            $('#DesktopIcon' + hashID).filter(":ui-droppable").droppable("disable").draggable({
                                scroll: false,
                                helper: "clone",
                                zIndex: 10000,
                                opacity: 0.9,
                                containments: $("#DesktopIcons"),
                                appendTo: $("#DesktopIcons"),
                                start: function (event, ui) {
                                    $(ui.helper).addClass('fromBalloon').css({position: 'absolute!important'});

                                    $('#DesktopIcons .DesktopIconContainer').removeClass('mouseoverclicked');
                                    $('#DesktopIcons .DesktopIconContainer').removeClass('mouseoverclickedmouseout');


                                    $(this).addClass('fromBalloon');
                                    $(this).addClass('mouseoverclicked');
                                },
                                stop: function ()
                                {
                                    $(this).addClass('mouseoverclicked');

                                    // update the icon position in Database
                                    setTimeout(function () {
                                        self.saveDesktopIconsToDatabase();
                                    }, 300);
                                }
                            }).droppable('disable');


                            var icn = $('#DesktopIcon' + hashID).find('img').attr('src');


                            // move to folder
                            var li = $('<li>');
                            balloonUL.append(li);
                            $('#DesktopIcon' + hashID).appendTo(li);

                            // show the Icon
                            $('#DesktopIcon' + hashID).show();

                            // add the image of Icon to the Folder
                            $('#DesktopFolder' + folderID).css({
                                //   position: 'absolute'
                            }).find('.folder').append($('<img src="' + icn + '" width="' + this.iconsize.subIconWidth + '" height="' + this.iconsize.subIconWidth + '"/>'));

                            var foldersize = $('#DesktopFolder' + folderID).data('size');

                            $('#DesktopFolder' + folderID)
                                    .attr('data-size', (foldersize ? foldersize + iconOpt.bytesize : iconOpt.bytesize)).attr('data-id', 'id-' + this.iconIdNum)
                                    .data('id', 'id-' + this.iconIdNum)
                                    .data('size', (foldersize ? foldersize + iconOpt.bytesize : iconOpt.bytesize))
                                    .addClass('labelpos-' + this.iconLabelPos);


                            this.iconIdNum++;
                            // remove the Icon from store                                
                            delete icons[ itemHash ];
                        }
                    }
                }


                $('#DesktopFolder' + folderID)
                        .find('.folder-label .object-info').text(folder.items.length + ' Objekt(e) / ' + Tools.formatSize($('#DesktopFolder' + folderID).data('size')));

                if (this.showObjectInfo) {
                    $('#DesktopFolder' + folderID)
                            .find('.folder-label .object-info').show();
                }
                else {
                    $('#DesktopFolder' + folderID)
                            .find('.folder-label .object-info').hide();
                }

            }
        }




        //if (icons.length > 0)
        //{

        for (x in icons)
        {
            var iconOpt = icons[ x ];
            if (iconOpt.url && typeof iconOpt.url === 'string' && iconOpt.url != '#') {
                iconOpt.WindowURL = iconOpt.url;
                iconOpt.WindowTitle = iconOpt.label;
                iconOpt.Controller = iconOpt.controller;
                iconOpt.Action = iconOpt.action;


                iconOpt.loadWithAjax = true;
                iconOpt.allowAjaxCache = false;
                iconOpt.UseWindowIcon = false;
                iconOpt.isRootApplication = true;  // used in Dock
                iconOpt.isStatic = false; // used in Dock

                // reset current ajaxData
                Desktop.ajaxData = {};
                Desktop.ajaxData = $.extend({}, iconOpt);


                if (typeof Desktop.ajaxData.toolbar != 'undefined')
                {
                    iconOpt.WindowToolbar = Desktop.ajaxData.toolbar;
                }

                iconOpt.WindowStatus = 'closed';
                iconOpt.WindowDesktopIconFile = iconOpt.DesktopIconFilename;

                // create the Icon
                this.createDesktopIcon(null, iconOpt, null);

                var exists = $('#DesktopIcon' + iconOpt.WindowID).length == 1 ? true : false;

                if (iconOpt.top && exists)
                {
                    $('#DesktopIcon' + iconOpt.WindowID).css({
                        // position: 'absolute',
                        top: iconOpt.top
                    });
                }

                if (iconOpt.left && exists)
                {

                    $('#DesktopIcon' + iconOpt.WindowID).css({
                        // position: 'absolute',
                        left: iconOpt.left
                    });
                }

                if (exists)
                {
                    delete iconOpt.top;
                    delete iconOpt.left;
                    $('#DesktopIcon' + iconOpt.WindowID)
                            .attr('data-id', 'id-' + this.iconIdNum)
                            .attr('data-size', iconOpt.bytesize)
                            .data('id', 'id-' + this.iconIdNum)
                            .data('size', iconOpt.bytesize)
                            .data('itemData', iconOpt).show();
                    this.iconIdNum++;

                    var i = $('#DesktopIcon' + iconOpt.WindowID).find('>.icon-label .object-info');

                    if (this.showObjectInfo) {
                        i.show();
                    }
                    else {
                        i.hide();
                    }

                    i.text(Tools.formatSize(iconOpt.bytesize));

                    self.addDragDropToDesktopIcon($('#DesktopIcon' + iconOpt.WindowID));
                }

            }
            // }
        }

        // $('#DesktopIcons .DesktopIconContainer,#DesktopIcons .DesktopIconContainer-Folder').width((Desktop.iconGutterSize + Desktop.iconsize.iconWidth));
        $('#DesktopIcons').find('>.DesktopIconContainer-Folder .folder-wrapper').width(this.iconsize.iconWidth).height(this.iconsize.iconWidth);
        $('#DesktopIcons').find('>.DesktopIconContainer-Folder >.folder-label,>.DesktopIconContainer .icon-label').width(this.iconGutterSize);


        // Register the Desktop Icon Events
        this.refreshDesktopIconDagnDropEvents();
        this.refreshFolderIconEvents();
        this.addDesktopIconHoverEvent();

        $('.DesktopIconContainer', desktopIcons).bind("click", function () {
            $('#DesktopIcons .DesktopIconContainer,.DesktopIconContainer-Folder').removeClass('mouseoverclicked');
            $('#DesktopIcons .DesktopIconContainer,.DesktopIconContainer-Folder').removeClass('mouseoverclickedmouseout');

            if ($(this).parents('.sub-container').length == 1)
            {
                var DesktopFolderID = $(this).parents('.sub-container').attr('id').replace('sub-container-', 'DesktopFolder');
                if (!$('#' + DesktopFolderID).hasClass('mouseoverclicked'))
                {
                    $('#' + DesktopFolderID).addClass('mouseoverclicked');
                }
            }


            if ($(this).parent().is('#DesktopIcons')) {
                desktopIcons.find('.sub-container:visible').fadeOut(200);
            }

            $(this).addClass('mouseoverclicked');
        });

        desktopIcons.unbind('click.disableselecting').bind('click.disableselecting', function (ev) {
            if (!ev.altKey) {
                if ($(this).hasClass('ui-selectable')) {
                    $(this).find('.ui-selected').removeClass('mouseoverclicked').removeClass('mouseoverclickedmouseout').removeClass('ui-selected');
                    $(this).selectable('destroy');
                }
            }
        });

        desktopIcons.unbind('mousedown.selecting').bind('mousedown.selecting', function (ev) {
            if (ev.altKey) {

                $(this).selectable({
                    filter: '>.DesktopIconContainer,>.DesktopIconContainer-Folder',
                    selecting: function (e, ui) {
                        $(ui.selecting).addClass('mouseoverclicked').addClass('mouseoverclickedmouseout');
                    },
                    unselecting: function (e, ui) {
                        $(ui.unselecting).removeClass('mouseoverclicked').removeClass('mouseoverclickedmouseout');
                    },
                    selected: function (e, ui) {
                        $(ui.selected).addClass('ui-selected').addClass('mouseoverclicked').addClass('mouseoverclickedmouseout');
                    }
                });

            }
            else {
                if ($(this).hasClass('ui-selectable')) {
                    $(this).find('.ui-selected').removeClass('mouseoverclicked').removeClass('mouseoverclickedmouseout').removeClass('ui-selected');
                    $(this).selectable('destroy');
                }
            }
        });

        $('.DesktopIconContainer-Folder', desktopIcons).bind("click.foldericon", function () {

            $('#DesktopIcons .DesktopIconContainer,.DesktopIconContainer-Folder').removeClass('mouseoverclicked');
            $('#DesktopIcons .DesktopIconContainer,.DesktopIconContainer-Folder').removeClass('mouseoverclickedmouseout');
            $(this).addClass('mouseoverclicked');
        });


        setTimeout(function () {
            $(".DesktopIconContainer:not(:visible), .DesktopIconContainer-Folder:not(:visible)", desktopIcons).fadeIn(400);
        }, 500);

    },
    /**
     * 
     * @returns {undefined}
     */
    addDesktopIconHoverEvent: function ()
    {
        var self = this;

        $("#DesktopIcons").find('.DesktopIconContainer,.DesktopIconContainer-Folder').unbind('mouseover mouseleave').bind("mouseover mouseleave", function (event) {
            if (event.type == 'mouseover')
            {
                if ($(this).hasClass('mouseoverclickedmouseout'))
                {
                    $(this).removeClass('mouseoverclickedmouseout');
                }

                $(this).addClass('mouseover');

                /**
                 *  add delete Icon
                 */
                if (!$(this).hasClass('DesktopIconContainer-Folder') && !$('#trashDesktopItem', $(this)).length)
                {
                    var $item = $(this);
                    $(this).append($('<div class="icon-remover">').attr({
                        id: 'trashDesktopItem',
                        title: 'Remove from Desktop'
                    }).click(function (e) {
                        Desktop.Trash.empty($item, e);

                        $('#trashDesktopItem', $(this)).remove();
                        $item.remove();

                        // update the database
                        setTimeout(function () {
                            self.saveDesktopIconsToDatabase();
                        }, 300);
                    }));
                }
                else
                {
                    $('#trashDesktopItem', $(this)).show();
                }
            }
            else
            {
                $('#trashDesktopItem', $(this)).hide();

                $(this).removeClass('mouseover');
                if ($(this).hasClass('mouseoverclicked'))
                {
                    $(this).addClass('mouseoverclickedmouseout');
                }
            }
        });
    },
    /**
     * 
     * @param {object} object
     * @param {object} options
     * @param {object} event optional
     * @returns {Boolean}
     */
    createDesktopIcon: function (object, options, event)
    {
        var settings = $.extend({}, this.settings, options || {});
        var img = $("img", $(object)).attr("src") ? $("img", $(object)).attr("src") : settings.WindowDesktopIconFile;

        settings.WindowURL = (settings.WindowURL !== null && settings.WindowURL !== '') ? settings.WindowURL : (object !== null ? $(object).attr("href") : '');

        if (settings.WindowURL == '')
        {
            Debug.error('Cant create the Desktop Icon. Please set the Window Url before create the Desktop Icon!');
            return;
        }

        // generate the ID from the WindowURL
        var self = this, id = $.fn.getWindowHash(settings.WindowURL);

        // do not create a desktop icon if exists the Icon or the Window
        // if the window exists then close the window and call again to create the Desktop icon
        if ($("#DesktopIcon" + id).length || $("#" + id).length)
        {
            return false;
        }



        var pluginName = options.Controller, isAddon = false;

        if (options.Controller == 'plugin' && options.WindowURL && options.WindowURL != '')
        {
            var pluginName = $.getURLParam('plugin', options.WindowURL);
            if (pluginName)
            {
                isAddon = true;
            }
            else {
                pluginName = options.Controller
            }
        }

        var icon = Application.getAppIcon(pluginName, 128, isAddon);

        settings.WindowDesktopIconFile = icon;
        settings.WindowStatus = settings.WindowStatus;
        settings.WindowTitle = (settings.WindowTitle !== null && settings.WindowTitle !== '' ? settings.WindowTitle : $(object).text());


        if ($('#DesktopIcons').length == 0)
        {
            $('#desktop').append('<div id="DesktopIcons"></div>');
        }

        if (settings.WindowDesktopIcon == true)
        {
            var DesktopIconCaption = '', temp = new Array();
            temp = settings.WindowTitle.split(' ');

            $.each(temp, function (index, value) {
                if (value.length > 17) {
                    DesktopIconCaption += value.substr(0, 17) + '... ';
                } else {
                    DesktopIconCaption += value + ' ';
                }
            });

            var tmpl = Template.setTemplate(Desktop.Templates.DesktopIcon).process({
                id: id,
                DesktopIconCaption: DesktopIconCaption,
                WindowDesktopIconFile: settings.WindowDesktopIconFile,
                DesktopIconWidth: this.iconsize.iconWidth,
                DesktopIconHeight: this.iconsize.iconWidth
            });

            Template.reset();

            $('#DesktopIcons').append(tmpl);


            // For Launchpad
            if (Tools.exists(settings, 'rel'))
            {
                if (settings.rel != '')
                {
                    $('#DesktopIcon' + id).attr('rel', settings.rel);
                }
            }
            else
            {
                if (Tools.exists(settings, 'Controller') && settings.Controller != '')
                {
                    $('#DesktopIcon' + id).attr('rel', settings.Controller);
                }
            }

            $('#DesktopIcon' + id).addClass('labelpos-' + this.iconLabelPos);



            $('#DesktopIcon' + id).data('windowData', settings);
            $('#DesktopIcon' + id).unbind('dblclick').bind('dblclick', function (e)
            {
                var self = $(this);
                // hidde folder iconcontainer ballon
                $('#DesktopIcons .sub-container:visible').hide();
                $('#DesktopIcons .DesktopIconContainer,.DesktopIconContainer-Folder').removeClass('mouseoverclicked');
                $('#DesktopIcons .DesktopIconContainer,.DesktopIconContainer-Folder').removeClass('mouseoverclickedmouseout');

                $('#desktop').mask('Bitte warten...');
                setTimeout(function () {
                    var id = self.attr('id');
                    var winData = $('#' + id.replace('DesktopIcon', '')).data('WindowManager');

                    if (winData)
                    {
                        if (winData.state == 'min') {
                            winData.ResizeWindow('restore');
                        }
                        else if (winData.state == 'default') {
                            winData.focus();
                        }
                        else if (!$("#" + winData.id).length)
                        {
                            if (settings.WindowURL) {
                                var dockIcon = self.createDockIcon(e, settings, false);

                                while (true) {
                                    if (dockIcon) {
                                        dockIcon.click();
                                        //       $('#desktop').unmask();
                                        break;
                                    }
                                }
                            }
                        }
                        else
                        {
                            if (settings.WindowURL) {
                                var dockIcon = Dock.createDockIcon(e, settings, false);
                                while (true) {
                                    if (dockIcon) {
                                        dockIcon.click();
                                        //      $('#desktop').unmask();
                                        break;
                                    }
                                }
                            }
                            else
                            {
                                var wdata = self.data('windowData');

                                Desktop.getAjaxContent(wdata, function () {
                                    Desktop.GenerateNewWindow(wdata, e);

                                });
                            }
                        }

                    }
                    else
                    {
                        var wdata = self.data('windowData');

                        if (wdata.WindowURL) {
                            var dockIcon = Dock.createDockIcon(e, wdata, false);
                            while (true) {
                                if (dockIcon) {
                                    dockIcon.click();
                                    //        $('#desktop').unmask();
                                    break;
                                }
                            }
                        }
                        else
                        {
                            var wdata = self.data('windowData');

                            Desktop.getAjaxContent(wdata, function () {
                                Desktop.GenerateNewWindow(wdata, e);
                                //    $('#desktop').unmask();
                            });
                        }
                    }
                }, 80);
            });

        }

        // return $("#" + id).hide().WindowManager(settings, event);
    },
    changeNameT: null,
    changeDesktopFolderName: function (toName, iconObject)
    {
        clearTimeout(this.changeNameT);

        var self = this;
        $(iconObject).find('.folder-label span:first').text(toName);
        this.changeNameT = setTimeout(function () {
            self.saveDesktopIconsToDatabase();
        }, 1500);

    },
    selected: $([]),
    offset: {top: 0, left: 0},
    refreshFolderIconEvents: function ()
    {
        var self = this;

        $("#DesktopIcons").find('.DesktopIconContainer-Folder').each(function ()
        {



            $(this).filter(':ui-droppable').droppable("destroy").filter(':ui-draggable').draggable("destroy");

            // bind events for folders                
            $(this).unbind('click.Folder').bind('click.Folder', function () {

                $(this).addClass('mouseoverclicked');


                var itemOffsetLeft = $(this).position().left;
                var itemOffsetTop = $(this).position().top;

                var subContainerID = 'sub-container-' + $(this).attr('id').replace('DesktopFolder', '');

                if (!$('#' + subContainerID).is(':visible'))
                {
                    var iconHeight = $(this).outerHeight();
                    var balloonHeight = $('#' + subContainerID).height();

                    $('#DesktopIcons .sub-container').fadeOut(250);

                    $('#' + subContainerID).css({
                        position: 'absolute',
                        zIndex: 5000,
                        top: itemOffsetTop - balloonHeight / 2 + (iconHeight / 2),
                        left: (itemOffsetLeft + $(this).outerWidth() + 5)
                    }).addClass('sub-container').fadeIn(350);

                    var iconObject = this;

                    $('#' + subContainerID).find('.sub-container-desc').unbind('keyup.container').bind('keyup.container', function () {
                        self.changeDesktopFolderName($(this).val(), iconObject);
                    });


                }
            }).draggable({
                scroll: false,
                helper: "original",
                containments: $('#DesktopIcons'),
                zIndex: 1000,
                start: function (event, ui)
                {

                    if ($(this).hasClass('ui-selected'))
                    {
                        self.selectedDesktopIcons = $(".ui-selected").each(function () {
                            var el = $(this);
                            el.data("offset", el.offset());
                        });

                        if (!$(this).hasClass("ui-selected"))
                            $(this).addClass("ui-selected");
                    }

                    self.selectedDesktopIconsOffset = $(this).offset();


                    $('#DesktopIcons .DesktopIconContainer').removeClass('mouseoverclicked');
                    $('#DesktopIcons .DesktopIconContainer').removeClass('mouseoverclickedmouseout');
                    $('#DesktopIcons .DesktopIconContainer-Folder').removeClass('mouseoverclicked');
                    $('#DesktopIcons .DesktopIconContainer-Folder').removeClass('mouseoverclickedmouseout');


                    var subContainerID = 'sub-container-' + $(this).attr('id').replace('DesktopFolder', '');
                    $('#' + subContainerID).hide();
                    $(this).addClass('mouseoverclicked');
                },
                drag: function (event, ui)
                {
                    var dt = ui.position.top - self.selectedDesktopIconsOffset.top, dl = ui.position.left - self.selectedDesktopIconsOffset.left;
                    // take all the elements that are selected expect $("this"), which is the element being dragged and loop through each.
                    self.selectedDesktopIcons.not(this).each(function () {
                        // create the variable for we don't need to keep calling $("this")
                        // el = current element we are on
                        // off = what position was this element at when it was selected, before drag
                        var el = $(this), off = el.data("offset");
                        el.css({top: off.top + dt, left: off.left + dl});
                    });

                },
                stop: function ()
                {
                    self.selectedDesktopIcons = $([]);
                    self.selectedDesktopIconsOffset = {
                        top: 0,
                        left: 0
                    };


                    // update the icon position in Database
                    setTimeout(function () {
                        self.saveDesktopIconsToDatabase();
                    }, 300);
                }
            }
            );



            $(this).droppable({
                activeClass: "",
                hoverClass: "drop-over-desktopicon",
                accept: ".DesktopIconContainer",
                over: function (event, ui) {
                    $(this).addClass('drop-over-desktopicon');


                    var subContainerID = 'sub-container-' + $(this).attr('id').replace('DesktopFolder', '');
                    var itemOffsetLeft = $(this).position().left;
                    var itemOffsetTop = $(this).position().top;
                    var iconHeight = $(this).outerHeight();
                    var balloonHeight = $('#' + subContainerID).show().height();



                    $('#' + subContainerID).hide().css({
                        position: 'absolute',
                        zIndex: 5000,
                        top: itemOffsetTop - balloonHeight / 2 + (iconHeight / 2),
                        left: (itemOffsetLeft + $(this).outerWidth() + 5)
                    });


                    $('#' + subContainerID).addClass('sub-container').fadeIn(350);

                },
                drop: function (event, ui) {

                    var subContainerID = 'sub-container-' + $(this).attr('id').replace('DesktopFolder', '');

                    // if balloon viewed the add item to balloon
                    if ($('#' + subContainerID).length)
                    {
                        var list = $('#' + subContainerID).find('ul:first');
                        var newwItem = $('<li>');
                        var _data = $('body').data('desktopIcon_' + $(ui.draggable).attr('id') + '_data');

                        if ($(ui.draggable).attr('style').length)
                        {
                            //$(ui.draggable).removeAttr('style');
                        }

                        newwItem.appendTo(list);



                        $(ui.draggable)
                                .droppable({
                                    disabled: true
                                })
                                .appendTo(newwItem);



                        $(this).data('size', ($(this).data('size') + parseInt($(ui.draggable).data('size'), 10)));
                        $(this).find('.folder-label .object-info').text(list.find('li').length + ' Objekte / ' + Tools.formatSize($(this).data('size')));



                        $(ui.draggable).draggable('destroy').draggable({
                            scroll: false,
                            helper: "clone",
                            containments: $("#DesktopIcons"),
                            zIndex: 10000,
                            appendTo: $('#DesktopIcons'),
                            opacity: 0.9,
                            start: function (event, ui) {
                                $(ui.helper).css({position: 'absolute!important'});
                                $(this).addClass('fromBalloon');

                                $('#DesktopIcons .DesktopIconContainer').removeClass('mouseoverclicked');
                                $('#DesktopIcons .DesktopIconContainer').removeClass('mouseoverclickedmouseout');
                                $(this).addClass('mouseoverclicked');
                            },
                            stop: function () {
                                $(this).css({position: ''});
                                $(this).removeClass('mouseoverclicked');
                            }
                        }).droppable('disable');



                        var icon = $(ui.draggable).find('img');
                        var iconClone = icon.clone(false);

                        iconClone.attr('width', self.iconsize.subIconWidth).attr('height', self.iconsize.subIconWidth);



                        $(this).find('div.folder').append(iconClone);
                        iconClone = null;

                        $('#' + subContainerID).addClass('sub-container').fadeOut(250);


                        self.refreshDesktopIconDagnDropEvents();
                        self.saveDesktopIconsToDatabase();
                    }

                },
                out: function (event, ui) {
                    // now hide the balloon
                    $(this).removeClass('drop-over-desktopicon');
                    $('#sub-container-' + $(this).attr('id').replace('DesktopFolder', '')).fadeOut(250);
                }
            }
            );






            $(this).find('.DesktopIconContainer').each(function () {
                $(this).removeClass('fromBalloon').removeClass('fromMenu');

                $(this).draggable({
                    scroll: false,
                    helper: "clone",
                    containments: $("#DesktopIcons"),
                    zIndex: 10000,
                    appendTo: $('#DesktopIcons'),
                    opacity: 0.9,
                    start: function (event, ui) {
                        $(ui.helper).css({position: 'absolute!important'});
                        if ($(this).hasClass('ui-selected'))
                        {
                            self.selectedDesktopIcons = $(".ui-selected").each(function () {
                                var el = $(this);
                                el.data("offset", el.offset());
                            });

                            if (!$(this).hasClass("ui-selected"))
                                $(this).addClass("ui-selected");
                        }

                        self.selectedDesktopIconsOffset = $(this).offset();


                        $('#DesktopIcons .DesktopIconContainer').removeClass('mouseoverclicked');
                        $('#DesktopIcons .DesktopIconContainer').removeClass('mouseoverclickedmouseout');
                        $(ui.helper).appendTo($('body'));

                        $(this).addClass('fromBalloon')
                        $(this).addClass('mouseoverclicked');
                    },
                    drag: function (event, ui)
                    {
                        var dt = ui.position.top - self.selectedDesktopIconsOffset.top, dl = ui.position.left - self.selectedDesktopIconsOffset.left;
                        // take all the elements that are selected expect $("this"), which is the element being dragged and loop through each.
                        self.selectedDesktopIcons.not(this).each(function () {
                            // create the variable for we don't need to keep calling $("this")
                            // el = current element we are on
                            // off = what position was this element at when it was selected, before drag
                            var el = $(this), off = el.data("offset");
                            el.css({top: off.top + dt, left: off.left + dl});
                        });

                    },
                    stop: function ()
                    {
                        $(this).css({position: ''});
                        // update the icon position in Database
                        setTimeout(function () {
                            self.saveDesktopIconsToDatabase();
                        }, 300);
                    }
                }
                ).droppable('disable');
            });


        });

    },
    addDragDropToDesktopIcon: function (icon)
    {
        var self = this;

        $(icon).filter(":ui-droppable").droppable("destroy").filter(":ui-draggable").draggable("destroy");
        $(icon).draggable({
            scroll: false,
            helper: "original",
            containments: $("#DesktopIcons"),
            zIndex: 10000,
            appendTo: $('#DesktopIcons'),
            opacity: 0.9,
            start: function (event, ui) {
                $(this).css({position: 'absolute!important'});
                self.selectedDesktopIconsOffset = $(this).offset();

                // disable other dragables
                if ($(this).hasClass('ui-selected'))
                {
                    self.selectedDesktopIcons = $(".ui-selected").each(function () {
                        var el = $(this);
                        el.data("offset", el.offset());
                    });

                    if (!$(this).hasClass("ui-selected"))
                        $(this).addClass("ui-selected");

                }
                else
                {
                    if ($(event.target).parents('sub-container:first').length)
                    {
                        $(this).addClass('fromBalloon');
                    }

                    $('#DesktopIcons .DesktopIconContainer').removeClass('mouseoverclicked');
                    $('#DesktopIcons .DesktopIconContainer').removeClass('mouseoverclickedmouseout');
                    $(this).addClass('mouseoverclicked');
                }


            },
            drag: function (e, ui)
            {
                var dt = ui.position.top - self.selectedDesktopIconsOffset.top, dl = ui.position.left - self.selectedDesktopIconsOffset.left;
                // take all the elements that are selected expect $("this"), which is the element being dragged and loop through each.
                self.selectedDesktopIcons.not(this).each(function () {
                    // create the variable for we don't need to keep calling $("this")
                    // el = current element we are on
                    // off = what position was this element at when it was selected, before drag
                    var el = $(this), off = el.data("offset");
                    el.css({
                        top: off.top + dt,
                        left: off.left + dl
                    });
                });
            },
            stop: function ()
            {
                $(this).css({position: ''});
                self.selectedDesktopIcons = $([]);
                self.selectedDesktopIconsOffset = {
                    top: 0,
                    left: 0
                };

                // update the icon position in Database
                setTimeout(function () {
                    self.saveDesktopIconsToDatabase();
                }, 300);
            }

        }).droppable({
            activeClass: "",
            hoverClass: "drop-over-desktopicon",
            accept: ".DesktopIconContainer,.fromBalloon",
            over: function (event, ui) {
                $(this).addClass('drop-over-desktopicon');


                var itemOffsetLeft = $(this).position().left;
                var itemOffsetTop = $(this).position().top;
                var id = $(this).attr('id').replace('DesktopIcon', '');

                var Balloon = Template
                        .setTemplate(Desktop.Templates.Balloon)
                        .process(
                                {
                                    id: id,
                                    label: 'New Folder Content'

                                });


                Template.reset();

                $('#DesktopIcons').append(Balloon);
                $('#sub-container-' + id).show();

                var iconHeight = $(this).outerHeight();
                var balloonHeight = $('#sub-container-' + id).outerHeight();


                $('#sub-container-' + id).hide().css({
                    position: 'absolute',
                    zIndex: 5000,
                    top: itemOffsetTop - (balloonHeight / 2) + (iconHeight / 2),
                    left: (itemOffsetLeft + $(this).outerWidth() + 5)
                });

                $('#sub-container-' + id).fadeIn(250);

            },
            drop: function (event, ui) {
                var icon, id = $(this).attr('id').replace('DesktopIcon', '');
                var size = 0, folderID = 'DesktopFolder' + id;


                var folderTemplate = Template
                        .setTemplate(Desktop.Templates.DesktopFolder)
                        .process(
                                {
                                    id: id,
                                    label: 'New Folder'
                                });


                Template.reset();


                var itemOffsetLeft = $(this).position().left;
                var itemOffsetTop = $(this).position().top;
                var itemStyle = $(this).attr('style');

                if ($(ui.draggable).hasClass('fromBalloon'))
                {
                    icon = $(ui.draggable).find('img');
                    $('.DesktopIconContainer-Folder .folder', $("#DesktopIcons")).find('img[src="' + icon.attr('src') + '"]').remove();
                    $(ui.draggable).removeClass('ui-draggable-dragging').removeClass('fromBalloon');
                }
                else {

                }


                $(folderTemplate).hide().appendTo($('#DesktopIcons'));

                $('#' + folderID).attr({
                    'style': itemStyle
                }).css({
                    top: itemOffsetTop,
                    left: itemOffsetLeft
                })
                        .attr('data-id', 'id-' + self.folderIdNum)
                        .attr('data-size', ($(this).data('size') ? $(this).data('size') : 0))
                        .data('id', 'id-' + self.folderIdNum)
                        .data('size', ($(this).data('size') ? $(this).data('size') : 0))
                        .addClass('labelpos-' + self.iconLabelPos);

                self.folderIdNum++;

                var folderContainer = $('#' + folderID).find('div.folder');
                var balloonUL = $('#sub-container-' + id).find('ul');
                var iconClone, li = $('<li>');

                folderContainer.parent().width(self.iconsize.iconWidth).height(self.iconsize.iconWidth);

                // add self icon to folder
                icon = $(this).find('img');

                size += $(this).data('size') || 0;




                li.appendTo(balloonUL);
                iconClone = icon.clone(false);

                iconClone.attr('width', self.iconsize.subIconWidth).attr('height', self.iconsize.subIconWidth);


                folderContainer.append(iconClone);
                iconClone = null;

                // copy self to balloon
                $(this).droppable({
                    disabled: true
                }).removeAttr('style').appendTo(li);

                $(this).draggable({
                    scroll: false,
                    helper: "clone",
                    containments: $("#DesktopIcons"),
                    zIndex: 10000,
                    appendTo: $('#DesktopIcons'),
                    opacity: 0.9,
                    start: function (event, ui) {
                        $(ui.helper).css({position: 'absolute!important'});
                        $(this).addClass('fromBalloon');

                        $('#DesktopIcons .DesktopIconContainer').removeClass('mouseoverclicked');
                        $('#DesktopIcons .DesktopIconContainer').removeClass('mouseoverclickedmouseout');
                        $(this).addClass('mouseoverclicked');
                    },
                    stop: function () {
                        $(this).css({position: ''});
                        $(this).removeClass('mouseoverclicked');
                    }
                });



                // 


                li = $('<li>');

                // add the icon to folder
                icon = $(ui.draggable).find('img');
                li.appendTo(balloonUL);
                iconClone = icon.clone(false);
                iconClone.attr('width', self.iconsize.subIconWidth).attr('height', self.iconsize.subIconWidth);
                folderContainer.append(iconClone);
                iconClone = null;

                size += $(ui.draggable).data('size') || 0;

                // add the main icon to balloon
                $(ui.draggable).droppable({
                    disabled: true
                }).removeAttr('style').appendTo(li);


                $(ui.draggable).draggable({
                    scroll: false,
                    helper: "clone",
                    containments: $("#DesktopIcons"),
                    zIndex: 10000,
                    appendTo: $('#DesktopIcons'),
                    opacity: 0.9,
                    start: function (event, ui) {
                        $(ui.helper).css({position: 'absolute!important'});
                        $(this).addClass('fromBalloon');

                        $('#DesktopIcons .DesktopIconContainer').removeClass('mouseoverclicked');
                        $('#DesktopIcons .DesktopIconContainer').removeClass('mouseoverclickedmouseout');

                        $(this).addClass('mouseoverclicked');
                    },
                    stop: function () {
                        $(this).css({position: ''});
                        $(this).removeClass('mouseoverclicked');
                    }
                });

                $(ui.draggable)


                li.appendTo(balloonUL);

                var i = $('#' + folderID).find('.folder-label .object-info');

                $(ui.draggable).find('.icon-label').show();

                if (self.showObjectInfo) {
                    i.show();
                    $(ui.draggable).find('.object-info').show();
                }
                else {
                    i.hide();
                    $(ui.draggable).find('.object-info').show();
                }




                i.text(balloonUL.find('li').length + ' Objekte / ' + Tools.formatSize(size));

                $('#' + folderID).attr('data-size', size).data('size', size).show();
                $('#sub-container-' + id).addClass('sub-container').fadeOut(250);

                self.refreshFolderIconEvents();
                self.saveDesktopIconsToDatabase();

            },
            out: function (event, ui) {
                $(this).removeClass('drop-over-desktopicon');

                var id = $(this).attr('id').replace('DesktopIcon', '');

                $('#sub-container-' + id).fadeOut(250);
                if ($('#sub-container-' + id).find('li').length == 0)
                {
                    $('#sub-container-' + id).remove();
                }
            }
        });
    },
    // refresh 
    refreshDesktopIconDagnDropEvents: function ()
    {
        var self = this;

        // drop menu item to desktop
        $("#DesktopIcons").droppable({
            accept: ".fromMenu,.fromBalloon",
            revert: true,
            drop: function (event, ui) {
                //$( this ).find( ".placeholder" ).remove();


                var icon;


                // console.log($(event.target).attr('id'));



                // move item out of a sub container
                if (($(ui.draggable).hasClass('fromBalloon') || $(ui.helper).hasClass('fromBalloon')) && $(event.target).attr('id') == 'DesktopIcons')
                {
                    icon = $(ui.draggable).find('img');
                    $(ui.draggable).removeClass('fromBalloon');
                    $(ui.draggable).droppable("enable").draggable("enable");


                    if ($(ui.draggable).attr('style'))
                    {
                        $(ui.draggable).removeAttr('style');
                    }

                    var parentLi = $(ui.draggable).parents('li:first'), size = $(ui.draggable).data('size') || 0;

                    if (Desktop.Icons.showObjectInfo) {
                        $(ui.draggable).find('.object-info').show();
                    }
                    else {
                        $(ui.draggable).find('.object-info').hide();
                    }

                    $(ui.draggable).css({top: ui.offset.top, left: ui.offset.left}).appendTo($(this));

                    $('.DesktopIconContainer-Folder .folder', $("#DesktopIcons")).find('img[src="' + icon.attr('src') + '"]').remove();

                    if (parentLi.length == 1) {


                        var i = parentLi.parents('.sub-container:first').attr('id').replace('sub-container-', '');

                        $('#DesktopFolder' + i).find('img[src="' + icon.attr('src') + '"]').remove();
                        var extras = $('#DesktopFolder' + i).find('.folder-label .object-info');
                        var newFolderSize = $('#DesktopFolder' + i).data('size') - size;
                        $('#DesktopFolder' + i).attr('data-size', newFolderSize).data('size', newFolderSize);
                        extras.text(parentLi.parent().find('li').length + ' Objekte / ' + Tools.formatSize(newFolderSize));
                        parentLi.remove();
                    }

                    $('.DesktopIconContainer-Folder', $("#DesktopIcons")).each(function () {
                        var id = $(this).attr('id');

                        $('#sub-container-' + id.replace('DesktopFolder', '')).find('ul').each(function ()
                        {
                            if (!$(this).find('li div').length)
                            {

                                $(this).parents('.sub-container:first').fadeOut(250, function () {
                                    $(this).remove();
                                });

                                $('#' + id).fadeOut(250, function () {
                                    $(this).remove();
                                });
                            }
                        });

                    });

                    self.refreshDesktopIconDagnDropEvents();
                    self.saveDesktopIconsToDatabase();
                }



                // add a new desktop icon from dragged menu
                else if ($(ui.draggable).hasClass('fromMenu') && $(event.target).attr('id') == 'DesktopIcons')
                {
                    Desktop.Taskbar.Menu.hideMenu(0);

                    var fromLaunchpad = false;
                    if ($(ui.helper).hasClass('fromlaunchpad'))
                    {
                        fromLaunchpad = true;
                    }


                    var itemData = $(ui.draggable).removeClass('fromMenu').data('itemData');
                    var currentElement = $(ui.helper).removeClass('fromMenu');
                    currentElement.removeClass('fromMenu').hide();
                    currentElement.appendTo($(this));

                    var data = $(ui.draggable).data('itemData');

                    currentElement.data('itemData', data);

                    if (!itemData)
                    {
                        //Debug.log('empty itemData ');
                    }

                    var opts = {};
                    opts.loadWithAjax = true;
                    opts.WindowToolbar = false;
                    opts.DesktopIconWidth = 36;
                    opts.DesktopIconHeight = 36;
                    opts.UseWindowIcon = false;

                    if (data.ajax)
                    {
                        opts.loadWithAjax = true;
                    }

                    // 
                    icon = '';
                    if (data.sprite_icon != false)
                    {
                        if ($('span', $(ui.helper)).hasClass('cfg'))
                        {
                            icon = self.baseURL + 'html/style/c9/img/' + 'cfgitems/' + data.sprite_icon + '.png';
                        }
                        else
                        {
                            icon = self.baseURL + 'html/style/c9/img/' + 'pulldownmenu/' + data.sprite_icon + '.png';
                        }
                    }
                    else
                    {
                        icon = $(ui.draggable).find('img').attr('src');
                    }





                    // reset current ajaxData
                    Desktop.ajaxData = {};
                    Desktop.ajaxData = $.extend({}, itemData);


                    opts.Skin = Desktop.settings.Skin;
                    opts.WindowTitle = itemData.label;
                    opts.WindowDesktopIconFile = (icon ? icon : '');

                    opts = $.extend({}, opts, itemData);

                    opts.WindowURL = Tools.prepareAjaxUrl(data.url);






                    var pluginName = opts.Controller, isAddon = 0;

                    if (opts.Controller == 'plugin' && opts.WindowURL && opts.WindowURL != '')
                    {
                        pluginName = $.getURLParam('plugin', opts.WindowURL);

                        if (pluginName)
                        {
                            isAddon = 1;
                        }
                        else {
                            pluginName = opts.Controller
                        }
                    }




                    $.get('admin.php?getModulInfo=' + pluginName + '&isAddon=' + isAddon, function (dat) {
                        if (Tools.responseIsOk(dat)) {

                            // opts.WindowTitle = dat.info.label;
                            //  opts.WindowDesktopIconFile = icon;
                            opts.WindowStatus = 'closed';

                            if (typeof dat.info.bytesize != 'undefined') {
                                opts.bytesize = dat.info.bytesize;
                            }


                            self.createDesktopIcon($(ui.draggable), opts, event);

                            var hashID = $.fn.getWindowHash(opts.WindowURL);

                            $('#DesktopIcon' + hashID).css({top: ui.offset.top, left: ui.offset.left}).hide();
                            $('#DesktopIcon' + hashID).data('itemData', data).attr('data-id', 'id-' + self.iconIdNum).data('id', 'id-' + self.iconIdNum);


                            if (!fromLaunchpad)
                            {
                                $('#DesktopIcon' + hashID).fadeIn(600);
                            }


                            if (opts.bytesize) {
                                $('#DesktopIcon' + hashID).data('size', opts.bytesize);
                            }
                            else {
                                $('#DesktopIcon' + hashID).data('size', 1024);
                            }

                            $('#DesktopIcon' + hashID).attr('data-size', $('#DesktopIcon' + hashID).data('size'));

                            var extras = $('#DesktopIcon' + hashID).find('.object-info');

                            if (self.showObjectInfo) {
                                extras.show();
                            }
                            else {
                                extras.hide();
                            }

                            extras.text(Tools.formatSize($('#DesktopIcon' + hashID).data('size')));

                            self.iconIdNum++;



                            self.saveDesktopIconsToDatabase();
                            self.refreshDesktopIconDagnDropEvents();
                            self.addDesktopIconHoverEvent();
                        }
                    });


                    /*
                     Desktop.getAjaxContent(opts, function (requestData) {
                     
                     $.pagemask.hide();
                     
                     
                     
                     if (typeof Desktop.ajaxData.toolbar != 'undefined')
                     {
                     opts.WindowToolbar = Desktop.ajaxData.toolbar;
                     }
                     
                     opts.WindowTitle = data.label;
                     opts.WindowDesktopIconFile = icon;
                     opts.WindowURL = Tools.prepareAjaxUrl(data.url);
                     opts.WindowStatus = 'closed';
                     
                     if (typeof Desktop.ajaxData.bytesize != 'undefined') {
                     opts.bytesize = Desktop.ajaxData.bytesize;
                     }
                     
                     
                     self.createDesktopIcon($(ui.draggable), opts, event);
                     
                     var hashID = $.fn.getWindowHash(opts.WindowURL);
                     
                     $('#DesktopIcon' + hashID).css({top: ui.offset.top, left: ui.offset.left}).hide();
                     $('#DesktopIcon' + hashID).data('itemData', data);
                     
                     
                     if (!fromLaunchpad)
                     {
                     $('#DesktopIcon' + hashID).fadeIn(600);
                     }
                     
                     
                     if ($('#DesktopIcon' + hashID).length) {
                     $('#DesktopIcon' + hashID).attr('data-id', this.iconIdNum).data('id', this.iconIdNum);
                     
                     if (opts.bytesize) {
                     $('#DesktopIcon' + hashID).data('size', opts.bytesize);
                     }
                     else {
                     $('#DesktopIcon' + hashID).data('size', 1024);
                     }
                     
                     $('#DesktopIcon' + hashID).attr('data-size', $('#DesktopIcon' + hashID).data('size'));
                     
                     var extras = $('#DesktopIcon' + hashID).find('.object-info');
                     
                     if (self.showObjectInfo) {
                     extras.show();
                     }
                     else {
                     extras.hide();
                     }
                     
                     extras.text(Tools.formatSize($('#DesktopIcon' + hashID).data('size')));
                     
                     this.iconIdNum++;
                     }
                     
                     
                     self.saveDesktopIconsToDatabase();
                     self.refreshDesktopIconDagnDropEvents();
                     self.addDesktopIconHoverEvent();
                     });
                     */
                }




            }
        });


        $('.DesktopIconContainer', $("#DesktopIcons")).each(function ()
        {
            if (!$(this).parents().hasClass('DesktopIconContainer-Folder') && !$(this).parents().hasClass('sub-container'))
            {
                self.addDragDropToDesktopIcon($(this));
            }
            else
            {

            }
        });

    },
    desktopIconUpdaterOn: false,
    saveDesktopIconsToDatabase: function ()
    {
        var self = this;

        if (this.desktopIconUpdaterOn)
        {
            setTimeout(function () {
                self.saveDesktopIconsToDatabase();
            }, 10);
        }
        else
        {
            this.desktopIconUpdaterOn = true;
            var allData = {};
            allData.desktopFolders = [];
            allData.desktopIcons = [];



            $('.DesktopIconContainer-Folder', $('#DesktopIcons')).each(function () {
                var folderName = $(this).find('.folder-label span').text();

                if (folderName)
                {
                    iconWidth = $(this).width();
                    var subID = $(this).attr('id').replace('DesktopFolder', 'sub-container-');
                    var iconIds = [];

                    if ($('#' + subID).length == 1)
                    {

                        $('#' + subID).find('.DesktopIconContainer').each(function () {
                            iconIds.push($(this).attr('id'));
                        });

                        allData.desktopFolders.push({
                            name: folderName,
                            left: $(this).position().left,
                            top: $(this).position().top,
                            items: iconIds
                        });

                    }
                }
            });

            $('.DesktopIconContainer', $('#DesktopIcons')).each(function () {
                var itemdata = $(this).data('itemData');
                if (itemdata)
                {
                    var item = {};

                    item.WindowID = itemdata.WindowID;
                    item.url = itemdata.url;
                    item.label = itemdata.label;
                    item.controller = itemdata.controller;
                    item.action = itemdata.action;
                    item.DesktopIconFilename = itemdata.DesktopIconFilename;

                    if ($(this).parents('.sub-container').length == 0)
                    {
                        item.left = $(this).position().left;
                        item.top = $(this).position().top;
                    }

                    allData.desktopIcons.push(item);
                }
            });

            var data = allData;
            data.adm = '';
            data.storeDesktopIcons = true;
            data.iconGutterSize = this.iconGutterSize;
            data.iconWidth = this.iconsize.iconWidth;
            data.subIconWidth = this.iconsize.subIconWidth;
            data.iconLabelPos = this.iconLabelPos;
            data.iconSort = this.iconSort;
            data.showObjectInfo = this.showObjectInfo;

            $.post(Tools.prepareAjaxUrl('admin.php'), data, function () {
                self.desktopIconUpdaterOn = false;
            }, 'json');

        }
    }
};