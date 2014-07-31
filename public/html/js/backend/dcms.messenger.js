

var Messenger = function () {

    this.current_folder = 1;
    this.current_message = 0;
    this.maxMessages = 50;

    this.settings = {
        cancreatefolders: false,
        maxfolders: 0,
        maxpms: 0
    };


    this.init = function (opts) {

        var self = this;

        this.settings = $.extend({}, this.settings, opts);
        this.win = $('#' + Win.windowID);
        this.windowID = Win.windowID;
        this.win.data('messenger', this);
        this.bindEvents();

        var total = parseInt($('#count-all').text().trim(), 10);

        if (total > 0)
        {
            var maxMessages = parseInt($('#count-max').text().trim(), 10);
            var sum = Math.round((total / maxMessages) * 100);
            $('#count-all-percent').html(sum.toString());
            this.drawGraph(sum);
        }
        else {
            $('#count-all-percent').html('0');
            this.drawGraph('0');
        }

        if (Desktop.isWindowSkin) {
            $('#' + Win.windowID).data('WindowManager').set('onResizeStop', function (event, ui, wm, uiContent) {
                $('#message-view,#message-compose-container,#messenger-left,#message-table').height(uiContent.height);
                if (wm.settings.isGridWindow)
                {
                    var gridData = $(wm.$el).data('windowGrid');
                    gridData.updateDataTableSize(wm.$el, false, uiContent);
                }
                self.updateScrollbars();
            });

            $('#' + Win.windowID).data('WindowManager').set('onResize', function (event, ui, wm, uiContent) {
                $('#message-view,#message-compose-container,#messenger-left,#message-table').height(uiContent.height);

                if (wm.settings.isGridWindow)
                {
                    var gridData = $(wm.$el).data('windowGrid');
                    gridData.updateDataTableSize(wm.$el, true, uiContent);
                }

                self.updateScrollbars();
            });
        }
        else {
            $(window).bind('resize.messenger', function () {
                $('#message-view,#message-compose-container,#messenger-left,#message-table').height($('#content-container-inner').height());

                if ($('#' + Win.windowID).data('windowGrid'))
                {
                    var gridData = $('#' + Win.windowID).data('windowGrid');
                    gridData.updateDataTableSize($('#' + Win.windowID));
                }

                self.updateScrollbars();
            });
        }

        setTimeout(function () {
            $('#message-view,#message-compose-container,#messenger-left,#message-table').height($('#body-content-' + Win.windowID).height());

            self.updateScrollbars();
        }, 300);

    };

    this.updateScrollbars = function () {
        Tools.scrollBar($('#messenger-left div:first'));
        Tools.scrollBar($('#message-compose-container div:first'));
        Tools.scrollBar($('#message-view div:first'));
    };


    this.resize = function () {
        if (Desktop.isWindowSkin) {
            $('#message-view,#message-compose-container,#messenger-left,#message-table').height($('#body-content-' + Win.windowID).height());
        }
        else {
            $('#message-view,#message-compose-container,#messenger-left,#message-table').height($('#content-container-inner').height());
        }
        this.updateScrollbars();
    };


    this.updateFolderCouters = function (data) {
        if (Tools.responseIsOk(data)) {

            $('#folder-panel').find('tr').each(function () {
                $(this).find('td:last').text('0');
            });


            var sum = 0;
            for (var x in data.folders) {
                var f = data.folders[x];
                if (f.counter !== null) {
                    if ( $('#count-' + x).length ) $('#count-' + x).html(parseInt(f.counter, 0));
                    else if ($('#count-' + f.id).length) $('#count-' + f.id).html(parseInt(f.counter, 0));
                }
            }

            var total = (typeof data.totalmessages != 'undefined' && data.totalmessages !== null ? data.totalmessages : 0);

            $('#count-all').html(total.toString());

            if (total > 0)
            {
                var maxMessages = parseInt($('#count-max').text().trim(), 0);
                sum = Math.round((total / maxMessages) * 100);
                $('#count-all-percent').html(sum.toString());
                this.drawGraph(sum);
            }
            else {
                $('#count-all-percent').html('0');
                this.drawGraph('0');
            }
        }
    };



    this.bindEvents = function () {
        var self = this;

        $('#set-read').unbind('click').on('click', function (e) {
            e.preventDefault();
            self.markRead(1);

            return false;
        });

        $('#set-unread').unbind('click').on('click', function (e) {
            e.preventDefault();
            self.markRead(0);

            return false;
        });

        $('#option-panel a.move-to-link').unbind('click').on('click', function (e) {
            e.preventDefault();
            folder = $(this).attr('rel').replace('folder-', '');
            self.moveTo(folder);

            return false;
        });


        $('#folder-panel a').unbind('click').on('click', function (e) {
            e.preventDefault();

            // what's the current folder?
            self.current_folder = $(this).attr('rel').replace('folder-', '');

            // show the message list
            $('#message-view').hide();
            $('#message-compose-container').hide();
            $('#message-table').show();

            self.setActiveFolder(self.current_folder);
            self.getFolder();

            return false;
        });

        $('#option-panel a.move-to-link').unbind('click').on('click', function (e) {
            e.preventDefault();
            folder = $(this).attr('rel').replace('folder-', '');
            self.moveTo(folder);

            return false;
        });

        $('#compose-button').unbind('click').on('click', function (e) {
            e.preventDefault();
            self.win.mask('laden...');
            $.get('admin.php?adm=messenger&action=write&ajax=1', {}, function (data) {
                if (Tools.responseIsOk(data)) {

                    self.win.unmask();
                    self.activateCompose(true);

                    $('#message-compose-container div:first').empty().append(data.maincontent);

                    self.bindComposerButtons();


                } else {
                    self.win.unmask();
                    jAlert(data.msg);
                }
            }, 'json');

        });

        $('#empty-trash-button').unbind('click').on('click', function (e) {
            e.preventDefault();


            jConfirm(cmslang.empty_trash_folder, cmslang.alert, function (res) {
                if (res) {
                    self.win.mask(cmslang.emptytrash);
                    var url = 'admin.php?adm=messenger&ajax=1&action=empty';
                    $.get(url, {}, function (data) {
                        if (Tools.responseIsOk(data)) {
                            self.win.unmask();


                            self.updateFolderCouters(data);


                            setTimeout(function () {

                                $.get('admin.php?adm=messenger&action=getnew', function (d) {
                                    if (Tools.responseIsOk(d)) {
                                        if (Desktop.Sidepanel && typeof Desktop.Sidepanel.updateMessenger === 'function') {
                                            Desktop.Sidepanel.updateMessenger(d);
                                        }
                                    }
                                });
                                self.getFolder();
                            }, 100);


                        } else {
                            self.win.unmask();
                            jAlert(data.msg);
                        }
                    });
                }
            });
        });

        if (this.settings.cancreatefolders) {

            $('#create-folder-button').unbind('click').on('click', function (e) {
                var name;
                e.preventDefault();



                if ($('#folder-panel tr').length - 3 >= self.settings.maxfolders) {


                    jAlert('The maximum of your folders is ' + self.settings.maxfolders + '. Sorry can´t create a new Folder.');

                    return;
                }

                var url = Config.get('backendImagePath');


                jPrompt(cmslang.create_folder, '', cmslang.create_foldertitle, function (name) {
                    if (name) {
                        var parms = {};
                        parms.adm = 'messenger';
                        parms.action = 'createfolder';
                        parms.name = name;
						parms.token = Config.get('token');


                        $.post('admin.php', parms, function (data) {
                            if (Tools.responseIsOk(data)) {
                                // ToolbarTabs.reloadActiveTab();


                                var tr = $('<tr>').attr('id', 'folder-' + data.newid);
                                tr.append('<td><a href="#" rel="folder-' + data.newid + '"><img src="' + url + 'spacer.gif" width="16" height="16" alt="" /><img src="' + url + 'folder.png" width="16" height="16" alt="" /><span>' + name + '</span></a></td><td class="folder-count" id="count-' + data.newid + '">0</td>');


                                $('#folder-panel table').append(tr);




                                tr = $('<tr>');
                                tr.append('<td><img src="' + url + 'messenger/user.png" width="16" height="16" alt="" /> <a class="move-to-link" rel="folder-' + data.newid + '" href="#">verschieben nach: <span>' + name + '</span></a></td>');

                                $('#option-panel table').append(tr);

                                self.bindEvents();

                            } else {
                                jAlert(data.msg);
                            }
                        }, 'json');
                    }
                });
            });



            $('#delete-folder-button').unbind('click').on('click', function (e) {
                e.preventDefault();

                if (self.current_folder > 3) {

                    jConfirm(cmslang.delete_messenger_folder.replace('%s', $('#folder-' + self.current_folder).find('td:first').text().trim()), cmslang.alert, function (res)
                    {
                        if (res) {
                            self.win.mask(cmslang.mask_pleasewait);
                            $.get('admin.php?adm=messenger&action=deletefolder&folder=' + self.current_folder, {}, function (data) {
                                self.win.unmask();

                                if (Tools.responseIsOk(data)) {

                                    $('#folder-' + self.current_folder).remove();



                                    self.updateFolderCouters(data);


                                    setTimeout(function () {
                                        self.current_folder = 1;
                                        self.getFolder();
                                    }, 100);

                                } else {
                                    jAlert(data.msg);
                                }
                            });
                        }
                    });
                }
            });

            $('#rename-folder-button').unbind('click').on('click', function (e) {
                e.preventDefault();

                if (self.current_folder > 3) {
                    var str = $('#folder-' + self.current_folder).find('span').text();

                    jPrompt(cmslang.create_folder, str, cmslang.rename_foldertitle, function (name) {
                        if (name) {
                            self.win.mask(cmslang.mask_saving);
                            $.get('admin.php?adm=messenger&action=renamefolder&folder=' + self.current_folder + '&newname=' + name, {}, function (data) {
                                if (Tools.responseIsOk(data)) {

                                    self.win.unmask();
                                    $('#option-panel').find('a[rel="folder-' + self.current_folder + '"]').find('span').html(name);
                                    $('#folder-' + self.current_folder).find('span').html(name);
                                } else {

                                    self.win.unmask();
                                    jAlert(data.msg);
                                }
                            });
                        }
                    });

                }
            });



        }
        else {
            $('#create-folder-button,#delete-folder-button,#rename-folder-button').hide();
        }


        if (Cookie.get('wg-read-messenger', false) > 0)
        {

            setTimeout(function () {
                $('#message-table,#message-compose-container').hide();

                $('#message-view').show();
                $('#message-view div:first').empty();
                $('#message-container').unmask();
                $('#message-container').mask(cmslang.loadmessage);

                var current_message = Cookie.get('wg-read-messenger');

                $.get('admin.php?adm=messenger&ajax=1&action=view&id=' + current_message, {}, function (data) {
                    if (Tools.responseIsOk(data)) {

                        self.current_message = current_message;

                        $('#message-container').unmask();
                        $('#message-view div:first').append(data.maincontent);
                        self.bindMessageButtons();
                        Tools.scrollBar($('#message-view div:first'));
                    } else {
                        $('#message-container').unmask();
                        jAlert(data.msg);
                        $('#message-table').show();
                    }
                }, 'json');

                Cookie.set('wg-read-messenger', null);

            }, 500);

            return;
        }





        /*
         if (Cookie.get('wg-compose'))
         {
         self.win.mask('laden...');
         
         $.get('admin.php?adm=messenger&action=write&ajax=1', {}, function (data) {
         if (Tools.responseIsOk(data)) {
         self.win.unmask();
         self.activateCompose(true);
         $('#message-compose-container div:first').html(data.maincontent);
         self.bindButtons();
         
         
         } else {
         self.win.unmask();
         jAlert(data.msg);
         }
         }, 'json');
         
         Cookie.set('wg-compose', true);
         }
         */

    };

    this.readMessage = function (id) {
        var self = this;
        if (id) {
            this.current_message = id;

            $('#message-table').hide();
            $('#message-compose-container').hide();
            $('#message-view div:first').empty();
            $('#message-view').show();
            $('#message-container').unmask();
            $('#message-container').mask(cmslang.loadmessage);

            $.get('admin.php?adm=messenger&ajax=1&action=view&id=' + id, {}, function (data) {
                if (Tools.responseIsOk(data)) {

                    $('#message-container').unmask();
                    $('#message-view div:first').append(data.maincontent);
                    self.bindMessageButtons();
                    Tools.scrollBar($('#message-view div:first'));
                } else {
                    $('#message-container').unmask();
                    jAlert(data.msg);
                    $('#message-table').show();
                }
            }, 'json');
        }
    };

    this.bindRowAction = function ()
    {
        var self = this, trs = $('#message-table tbody').find('tr');
        trs.each(function ()
        {
            $(this).attr('title', cmslang.doubleclick_to_read_messeage).css({'cursor': 'pointer'}).unbind('dblclick').bind('dblclick', function (e)
            {
                var id = parseInt($(this).attr('id').replace('data-', ''), 0);

                if (id) {
                    self.readMessage(id);
                }

                e.preventDefault();
                return false;
            });
        });


        this.drawGraph(Math.round($('#count-all-percent').text()));

        setTimeout(function () {
            self.bindEvents();
        }, 800);
    };


    this.getSelection = function () {

        var grid = this.win.data('windowGrid');
        if (grid) {
            return grid.getSelected();
        }

        return [];
    };

    this.getFolder = function () {

        var self = this;
        this.activateCompose(false);

        this.setActiveFolder(this.current_folder);

        if (this.current_folder == 3) {
            $('#empty-trash-button').enableButton();
        } else {
            $('#empty-trash-button').disableButton();
        }

        this.setActiveFolder(this.current_folder);

        var grid = this.win.data('windowGrid'), url = 'admin.php?adm=messenger&folder=' + this.current_folder;
        if (grid) {
            grid.griddataurl = Tools.prepareAjaxUrl(url);
            grid.getData();
        }
        /*
         loadGridData(gridTable, {folder: this.current_folder}, function (data) {
         
         }); */
    };

    this.getMessengerParams = function (params)
    {
        this.current_message = false;
        params.folder = this.current_folder;
        return params;
    };

    this.activateCompose = function (active, backtoReadMessage)
    {
        if (active === true)
        {
            $('#messengerfolder').hide();
            $('#message-view').hide();
            $('#message-table').hide();

            $('#messengercompose').show();
            $('#message-compose-container').show();

            if (!Desktop.isWindowSkin)
            {
                $('#' + this.win.attr('id').replace('content-', 'statusbar-')).empty();
            }

            this.updateScrollbars();
        }
        else
        {
            if (backtoReadMessage) {
                $('#messengerfolder').show();
                $('#message-view').show();
                $('#message-compose-container').hide();
                $('#messengercompose').hide();
            }
            else {
                $('#messengercompose').hide();
                $('#message-compose-container').hide();
                $('#messengerfolder').show();
                $('#message-table').show();
            }

            if (!Desktop.isWindowSkin)
            {
                $('#' + this.win.attr('id').replace('content-', 'statusbar-')).empty();
            }

            this.updateScrollbars();
        }
    };

    this.drawGraph = function (percent) {
        var el = $('#count-percent-graph');
        var div = $('<div></div>').attr('title', percent + '% used');
        el.empty().append(div);
        // var url = Config.get('backendImagePath');


        if (percent == 0) {
            //el.append($('<img>').attr({width: 2, height: 8, alt: '', src: url + 'bar/bar-unused-left.gif'}));
            //el.append($('<img>').attr({width: 180, height: 8, alt: '', src: url + 'bar/bar-unused.gif'}));
            //el.append($('<img>').attr({width: 2, height: 8, alt: '', src: url + 'bar/bar-unused-right.gif'}));
            div.append($('<span>').width(0))
        } else if (percent >= 100) {
            //el.append($('<img>').attr({width: 2, height: 8, alt: '', src: url + 'bar/bar-used-left.gif'}));
            //el.append($('<img>').attr({width: 180, height: 8, alt: '', src: url + 'bar/bar-used.gif'}));
            //el.append($('<img>').attr({width: 2, height: 8, alt: '', src: url + 'bar/bar-used-right.gif'}));
            div.append($('<span>').width(180))
        } else {
            //el.append($('<img>').attr({width: 2, height: 8, alt: '', src: url + 'bar/bar-used-left.gif'}));
            //el.append($('<img>').attr({width: Math.round((percent / 100) * 190), height: 8, alt: '', src: url + 'bar/bar-used.gif'}));
            //el.append($('<img>').attr({width: Math.round(((100 - percent) / 100) * 190), height: 8, alt: '', src: url + 'bar/bar-unused.gif'}));
            //el.append($('<img>').attr({width: 2, height: 8, alt: '', src: url + 'bar/bar-unused-right.gif'}));
            div.append($('<span>').width(Math.round((percent / 100) * 180)))
        }



    };

    this.markRead = function (read) {
        var self = this, params = {};
        params.read = read;
        var selection = this.getSelection($('#message-table'));
        if (selection.length > 0) {

            params.selection = selection.join(',');
            $.get('admin.php?adm=messenger&action=mark&ajax=1', $.param(params), function (data) {
                if (Tools.responseIsOk(data)) {
                    // show the message list
                    $('#message-compose-container').hide();
                    $('#message-view').hide();
                    $('#message-table').show();
                    self.getFolder();
                } else {
                    jAlert(data.msg);
                }
            }, 'json');
        }
    };
    this.moveTo = function (folder) {
        var self = this, params = {};
        params.folder = folder;
        var selection = this.getSelection($('#message-table'));
        params.selection = selection.join(',');
        if (params.selection.length > 0) {

            $.get('admin.php?adm=messenger&action=move&ajax=1', $.param(params), function (data) {
                if (Tools.responseIsOk(data)) {

                    // show the message list
                    self.getFolder();
                    self.updateFolderCouters(data);


                } else {
                    jAlert(data.msg);
                }
            }, 'json');
        }
    };

    this.setActiveFolder = function (folder) {
        var url = Config.get('backendImagePath');

        // update the current folder arrow thing
        $('#folder-panel').find('img.current-folder').attr('src', url + 'spacer.gif').removeClass('current-folder');
        var row = $('#folder-' + folder);
        row.find('td:first img:first').attr('src', url + 'messenger/right.png').addClass('current-folder');
    };



    this.bindComposerButtons = function () {
        var self = this;


        setTimeout(function () {
            $('#compose-cancel-button').unbind().bind('click', function (e) {

                e.preventDefault();

                var formID = self.win.data('formID');
                if (formID)
                {
                    Form.resetDirty( $('#' + formID) );
                    Form.destroy( $('#' + formID) );
                    self.win.removeData('formID');

                }

                if (!Desktop.isWindowSkin)
                {
                    $('#' + self.windowID.replace('content-', 'statusbar-')).empty();
                }

                // show the message list
                self.activateCompose(false, false);
                self.bindEvents();
                e.preventDefault();
                return false;
            });

            $('#compose-save-button').unbind().bind('click', function (e) {
                e.preventDefault();

                var formID = self.win.data('formID');

                self.win.data('formConfig').onAfterSubmit = function (exit, data)
                {
                    self.updateFolderCouters(data);

                    if (formID)
                    {
                        Form.resetDirty( $('#' + formID) );
                        Form.destroy( $('#' + formID) );

                    }

                    if (!Desktop.isWindowSkin)
                    {
                        $('#' + self.windowID.replace('content-', 'statusbar-')).empty();
                    }


                    self.activateCompose(false, false);

                    setTimeout(function () {
                        self.win.removeData('formID');
                        self.getFolder();
                    }, 1000);

                };

                // show the message list
                Form.save(e, false, false, formID, self.windowID );

                return false;
            });
        }, 200);
    };

    this.bindMessageButtons = function () {

        var self = this;

        $('#compose-cancel-button').unbind().on('click', function (e) {
            e.preventDefault();

            // show the message list
            self.activateCompose(false);
            var formID = self.win.data('formID');
            if (formID)
            {
                Form.resetDirty( $('#' + formID) );
                Form.destroy( $('#' + formID) );
                self.win.removeData('formID');

            }

            return false;
        });



        $('#rcpt-accept').unbind().on('click', function (e) {
            e.preventDefault();
            var cell = $(this).parents('td:first');
            cell.empty().append(Tools.getLoadingImage());
            $.get('admin.php?adm=messenger&action=receipt&id=' + self.current_message, {}, function (data) {
                if (Tools.responseIsOk(data)) {
                    self.updateFolderCouters(data);
                    cell.empty().html(data.msg);

                } else {
                    jAlert(data.msg);
                }
            }, 'json');

            return false;
        });

        $('#rcpt-decline').unbind().on('click', function (e) {
            e.preventDefault();
            $(this).parents('tr:first').hide();
            return false;
        });

        $('#reply-button').unbind().on('click', function (e) {

            $('#compose-cancel-button').unbind().on('click', function (e2) {
                // show the message list

                self.activateCompose(false);
                $('#message-view').show();
                $('#message-table').hide();
                e2.preventDefault();
                return false;
            });

            $('#message-container').mask('laden...');
            $.get('admin.php?adm=messenger&action=write&type=reply&ajax=1&id=' + self.current_message, {}, function (data) {
                if (Tools.responseIsOk(data)) {

                    $('#message-container').unmask();
                    $('#message-compose-container div:first').empty().append(data.maincontent);
                    self.activateCompose(true);
                    self.bindComposerButtons();

                } else {
                    $('#message-container').unmask();
                    jAlert(data.msg);
                }
            }, 'json');

            e.preventDefault();
            return false;

        });

        $('#replyall-button').unbind().on('click', function (e) {
            $('#compose-cancel-button').unbind().on('click', function (e2) {
                // show the message list

                self.activateCompose(false);
                $('#message-view').show();
                $('#message-table').hide();
                e2.preventDefault();
                return false;
            });

            $('#message-container').mask('laden...');
            $.get('admin.php?adm=messenger&action=write&type=replyall&ajax=1&id=' + self.current_message, {}, function (data) {
                if (Tools.responseIsOk(data)) {

                    $('#message-container').unmask();

                    $('#message-compose-container div:first').empty().append(data.maincontent);
                    self.activateCompose(true);

                    self.bindComposerButtons();

                } else {
                    $('#message-container').unmask();
                    jAlert(data.msg);
                }
            }, 'json');
            e.preventDefault();
            return false;
        });

        $('#forward-button').unbind().on('click', function (e) {

            $('#compose-cancel-button').unbind().on('click', function (e2) {
                // show the message list

                self.activateCompose(false);
                $('#message-view').show();
                $('#message-table').hide();
                e2.preventDefault();
                return false;
            });

            $('#message-container').mask('laden...');
            $.get('admin.php?adm=messenger&action=write&type=forward&ajax=1&id=' + self.current_message, {}, function (data) {
                if (Tools.responseIsOk(data)) {

                    $('#message-container').unmask();


                    $('#message-compose-container div:first').empty().append(data.maincontent);
                    self.activateCompose(true);
                    self.bindComposerButtons();

                } else {
                    $('#message-container').unmask();
                    jAlert(data.msg);
                }
            }, 'json');
            e.preventDefault();
            return false;
        });


        // cancel message read
        $('#cancel-button').unbind().on('click', function (e) {

            var formID = self.win.data('formID');
            if (formID)
            {
                Form.resetDirty( $('#' + formID) );
                Form.destroy( $('#' + formID) );
                self.win.removeData('formID');

            }


            $('#message-view div:first').empty();
            $('#message-compose-container,#message-view').hide();
            $('#message-table').show();
            self.getFolder();
            e.preventDefault();
            return false;
        });

        $('#delete-button').unbind().on('click', function (e) {
            var params = {};
            params.folder = 3;
            if (self.current_message) {
                params.selection = self.current_message;
                $.get('admin.php?adm=messenger&action=move&ajax=1', params, function (data) {
                    if (Tools.responseIsOk(data)) {

                        $('#message-view div:first').empty();
                        $('#message-view').hide();
                        $('#message-table').show();

                        self.updateFolderCouters(data);
                        // show the message list
                        setTimeout(function () {
                            self.getFolder();
                        }, 50);
                    } else {
                        jAlert(data.msg);
                    }
                });
            }

            e.preventDefault();
            return false;

        });
    };


    this.checkMessageSelection = function (selection) {
        this.messengerCheckSelection(selection);
    };

    this.messengerCheckSelection = function (sel)
    {
        var self = this, selection = (sel === true ? 0 : sel.length);

        if (this.current_folder == 2)
        {
            $('.move-to-link').each(function () {
                if ($(this).attr('rel') != 'folder-3') {
                    $(this).parents('tr:first').hide();
                }
            });
        }
        else
        {
            $('.move-to-link').each(function () {
                $(this).parents('tr:first').show();
            });
        }

        if (this.current_folder > 3) {
            $('#delete-folder-button').enableButton();
            $('#rename-folder-button').enableButton();
        } else {
            $('#delete-folder-button').disableButton();
            $('#rename-folder-button').disableButton();
        }

        if (selection == 0 && $('#option-panel').is(':visible')) {
            $('#option-panel').fadeOut(300, function () {
                self.updateScrollbars();
            });
        }

        if (selection >= 1 && !$('#option-panel').is(':visible')) {
            $('#option-panel').fadeIn(300, function () {
                self.updateScrollbars();
            });
        }
    };

    return this;
};