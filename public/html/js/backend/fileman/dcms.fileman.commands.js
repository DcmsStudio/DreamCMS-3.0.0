(function ($) {
    Fileman.prototype.Commands = function (fm) {
        var self = this;
        /**
         * Fileman instance
         *
         * @type Fileman
         */
        this.fm = fm;
        this.lock = false;
        this.cmd = {};
        this.buttons = {};


        if ($('.fileman-contextmenu').length)
        {
            $('.fileman-contextmenu').remove();
        }



        this.menu = $('<div class="fileman-contextmenu" />').appendTo(document.body).hide();

        $(document).unbind('keypress.fm').unbind('keydown.fm').bind($.browser.mozilla || $.browser.opera ? 'keypress.fm' : 'keydown.fm', function (e) {
            var meta = e.ctrlKey || e.metaKey;



            if ($('#' + self.fm.id).parents('.isWindowContainer').attr('id') != Win.windowID)
            {
                return;
            }




            if (self.lock || !self.fm || $(e.originalEvent.target).get(0).tagName.toLowerCase() == 'input' || $(e.originalEvent.target).get(0).tagName.toLowerCase() == 'textarea') {
                return;
            }

            switch (e.keyCode) {
                /* arrows left/up. with Ctrl - exec "back", w/o - move selection */
                case 37:
                case 38:
                    e.stopPropagation();
                    e.preventDefault();
                    if (e.keyCode == 37 && meta) {
                        self.ui.execIfAllowed('back');
                    } else {
                        moveSelection(false, !e.shiftKey);
                    }
                    break;
                    /* arrows right/down. with Ctrl - exec "open", w/o - move selection */
                case 39:
                case 40:
                    e.stopPropagation();
                    e.preventDefault();
                    if (meta) {
                        self.ui.execIfAllowed('open');
                    } else {
                        moveSelection(true, !e.shiftKey);
                    }
                    break;
                    /* Space - QuickLook */
                case 32:
                    e.preventDefault();
                    e.stopPropagation();

                    self.fm.quickview.toggle();
                    if (self.fm.quickview.visible)
                    {
                        self.fm.quickview.update();
                    }


                    break;
                    /* Esc */
                case 27:
                    self.fm.quickview.hide();
                    break;
            }
        });

        $(document).bind('keydown.fm', function (e) {
            var meta = e.ctrlKey || e.metaKey;

            if ($('#' + self.fm.id).parents('.isWindowContainer').attr('id') != Win.windowID)
            {
                return;
            }

            if (self.lock || !self.fm || $(e.originalEvent.target).get(0).tagName.toLowerCase() == 'input' || $(e.originalEvent.target).get(0).tagName.toLowerCase() == 'textarea') {
                return;
            }
            switch (e.keyCode) {
                /* Meta+Backspace - delete */
                case 8:
                    if (meta && self.ui.isCmdAllowed('rm')) {
                        e.preventDefault();
                        self.ui.exec('rm');
                    }
                    break;
                    /* Enter - exec "select" command if enabled, otherwise exec "open" */
                case 13:
                    if (self.ui.isCmdAllowed('select')) {
                        //return self.ui.exec('select');
                    }

                    self.ui.execIfAllowed('open');

                    break;
                    /* Delete */
                case 46:
                    self.ui.execIfAllowed('rm');
                    break;
                    /* Ctrl+A */
                case 65:
                    if (meta) {
                        e.preventDefault();
                        self.fm.selectAll();
                    }
                    break;
                    /* Ctrl+C */
                case 67:
                    meta && self.ui.execIfAllowed('copy');
                    break;
                    /* Ctrl+Q - get info */
                case 86:
                    if (meta) {
                        e.preventDefault();
                        //  console.log('quickview');
                        self.fm.quickview.setMode(true);
                        self.fm.quickview.show();
                        self.fm.quickview.update();
                    }
                    break;

                    /* Ctrl+I - get info */
                case 73:
                    if (meta) {
                        e.preventDefault();
                        self.fm.quickview.setMode();
                        self.fm.quickview.show();
                        self.fm.quickview.update();
                    }
                    break;
                    /* Ctrl+N - new folder */
                case 78:
                    if (meta) {
                        e.preventDefault();
                        self.ui.execIfAllowed('mkdir');
                    }
                    break;
                    /* Ctrl+U - upload files */
                case 85:

                    if (meta) {
                        e.preventDefault();
                        self.ui.execIfAllowed('upload');
                    }
                    break;
                    /* Ctrl+V */
                case 86:
                    meta && self.ui.execIfAllowed('paste');
                    break;
                    /* Ctrl+X */
                case 88:
                    meta && self.ui.execIfAllowed('cut');
                    break;

                case 113:
                    self.ui.execIfAllowed('rename');
                    break;

            }

        });


        this.exec = function ()
        {
            if (arguments.length === 0)
                return;
            var args = [];
            Array.prototype.push.apply(args, arguments);
            var cmd = args.shift();

            if (this.cmd[cmd])
            {
                if (cmd != 'open' && !this.cmd[cmd].isAllowed()) {
                    return this.fm.layout.error('Command not allowed');
                }



                if (!this.fm.locked) {

                    args.unshift(this.fm);

                    this.fm.quickview.hide();
                    $('.el-finder-info').remove();
                    this.cmd[cmd].exec(args);
                    this.update();
                }
            }
        }

        this.cmdName = function (cmd) {

            if (this.cmd[cmd] && this.cmd[cmd].name) {
                return cmd == 'archive' && this.fm.opts.archives.length == 1
                        ? this.fm.i18n('Create') + ' ' + this.fm.mime2kind(this.fm.opts.archives[0]).toLowerCase()
                        : this.fm.i18n(this.cmd[cmd].name);
            }
            return cmd;
        }

        this.isCmdAllowed = function (cmd)
        {
            return self.cmd[cmd] && self.cmd[cmd].isAllowed();
        }

        this.execIfAllowed = function () {

            if (arguments.length === 0)
                return;
            var args = [];
            Array.prototype.push.apply(args, arguments);
            var cmd = args.shift();
            args.unshift(this.fm);
            this.isCmdAllowed(cmd) && this.exec(args);
        }

        this.includeInCm = function (cmd, t) {

            return this.isCmdAllowed(cmd) && this.cmd[cmd].cm(t);
        }

        this.update = function () {
            for (var i in this.buttons) {
                this.buttons[i].toggleClass('disabled', !this.cmd[i].isAllowed());
            }
        }

        this.init = function ()
        {

        }



        /**
         *  Init the Filemanager Toolbar
         */
        this.initCommants = function (disabled)
        {
            var i, j, n, c = false, zindex = 2, z, t = this.fm.toolbar;

            /* disable select command if there is no callback for it */
            if (!this.fm.opts.editorCallback) {
                disabled.push('select');
            }

            /* disable archive command if no archivers enabled */
            if (!this.fm.opts.archives.length && $.inArray('archive', disabled) == -1) {
                disabled.push('archive');
            }

            for (i in this.commands)
            {
                if ($.inArray(i, disabled) == -1) {
                    this.commands[i].prototype = this.command.prototype;
                    this.cmd[i] = new this.commands[i](this.fm);
                }
            }

            for (i = 0; i < t.length; i++)
            {
                if (c) {
                    this.fm.layout.toolbar.append('<li class="delim" />');
                }

                c = false;
                var totalInGroup = t[i].length;

                for (j = 0; j < totalInGroup; j++) {
                    n = t[i][j];
                    if (this.cmd[n])
                    {
                        c = true;

                        this.buttons[n] = $('<li class="' + n + (j == totalInGroup - 1 ? ' groupEnd' : (j == 0 ? ' groupStart' : '')) + '" title="' + this.cmdName(n) + '" name="' + n + '" />')
                                .append('<span></span>')
                                .appendTo(this.fm.layout.toolbar)
                                .click(function (e) {
                                    e.stopPropagation();
                                })
                                .bind('click', (function (ui) {
                                    return function () {
                                        !$(this).hasClass('disabled') && !$(this).hasClass('dropButton') && ui.exec($(this).attr('name'));
                                        // !$(this).hasClass('disabled') && $(this).hasClass('dropButton') && ui.execDropButton($(this).attr('name'));
                                    }
                                })(this)
                                        ).hover(
                                function () {
                                    !$(this).hasClass('disabled') && $(this).addClass('el-finder-tb-hover')
                                },
                                function () {
                                    $(this).removeClass('el-finder-tb-hover')
                                }
                        );


                        if (typeof this.cmd[n].getDropDownButton == 'function')
                        {
                            var dropDown = this.cmd[n].getDropDownButton();

                            this.buttons[n].append($('<span/>').addClass('dropArrow')).addClass('dropButton').bind('click', (function (ui) {
                                return function () {
                                    if (!$(this).hasClass('disabled'))
                                    {
                                        var ti = null, offset = $(this).position();

                                        $('ul[rel=' + $(this).attr('name') + ']').addClass('dropButton-Content').unbind('mouseenter').unbind('mouseleave').css({
                                            top: offset.top + $(this).outerHeight(),
                                            left: offset.left,
                                            position: 'absolute',
                                            zIndex: 9999
                                        }).show()
                                                .bind('mouseenter', function () {

                                                    clearTimeout(ti);

                                                })
                                                .bind('mouseleave', function () {
                                                    var _self = this;
                                                    ti = setTimeout(function () {
                                                        $(_self).hide();
                                                    }, 200);

                                                });
                                    }
                                }
                            })(this));



                            this.fm.layout.toolbar.parent().append(dropDown);
                        }

                    }
                }
            }


            var lastDelim = this.fm.layout.toolbar.find('li.delim:last');
            if (!lastDelim.next().hasClass('groupStart'))
            {
                lastDelim.remove();
            }

            this.fm.layout.toolbar.find('li.delim').each(function ()
            {
                if ($(this).next().hasClass('groupEnd') && $(this).prev().hasClass('groupEnd'))
                {
                    $(this).next().removeClass('groupEnd').addClass('groupStartEnd');
                }
            })


            this.update();

        };





        /**
         * Move selection in current dir
         *
         * @param Boolean move forward?
         * @param Boolean clear current selection?
         **/
        function moveSelection (forward, reset) {
            var p, _p, cur;

            if (!$('[hash]', self.fm.layout.foldercontentContainer).length) {
                return;
            }

            if (self.fm.selected.length == 0) {
                p = $('[hash]:' + (forward ? 'first' : 'last'), self.fm.layout.foldercontentContainer);
                self.fm.select(p);
            } else if (reset) {
                p = $('.ui-selected:' + (forward ? 'last' : 'first'), self.fm.layout.foldercontentContainer);
                _p = p[forward ? 'next' : 'prev']('[hash]');
                if (_p.length) {
                    p = _p;
                }
                self.fm.select(p, true);
            } else {
                if (self.pointer) {
                    cur = $('[hash="' + self.pointer + '"].ui-selected', self.fm.layout.foldercontentContainer);
                }
                if (!cur || !cur.length) {
                    cur = $('.ui-selected:' + (forward ? 'last' : 'first'), self.fm.layout.foldercontentContainer);
                }
                p = cur[forward ? 'next' : 'prev']('[key]');

                if (!p.length) {
                    p = cur;
                } else {
                    if (!p.hasClass('ui-selected')) {
                        self.fm.select(p);
                    } else {
                        if (!cur.hasClass('ui-selected')) {
                            self.fm.unselect(p);
                        } else {
                            _p = cur[forward ? 'prev' : 'next']('[hash]')
                            if (!_p.length || !_p.hasClass('ui-selected')) {
                                self.fm.unselect(cur);
                            }
                            else {
                                while ((_p = forward ? p.next('[hash]') : p.prev('[hash]')) && p.hasClass('ui-selected')) {
                                    p = _p;
                                }
                                self.fm.select(p);
                            }
                        }
                    }
                }
            }
            self.pointer = p.attr('hash');
            self.fm.checkSelectedPos(forward);
        }




    }




    /**
     * @class elFinder user Interface Command.
     * @author dio dio@std42.ru
     **/
    Fileman.prototype.Commands.prototype.command = function (fm) {
    }

    /**
     * Return true if command can be applied now
     * @return Boolean
     **/
    Fileman.prototype.Commands.prototype.command.prototype.isAllowed = function () {
        return true;
    }

    /**
     * Return true if command can be included in contextmenu of required type
     * @param String contextmenu type (cwd|group|file)
     * @return Boolean
     **/
    Fileman.prototype.Commands.prototype.command.prototype.cm = function (t) {
        return false;
    }

    /**
     * Return not empty array if command required submenu in contextmenu
     * @return Array
     **/
    Fileman.prototype.Commands.prototype.command.prototype.argc = function (t) {
        return [];
    }





    Fileman.prototype.Commands.prototype.commands = {
        /**
         * @class Go into previous folder
         * @param Object elFinder
         **/
        back: function (fm) {
            var self = this;
            this.name = 'Back';
            this.fm = fm;

            this.exec = function () {
                if (this.fm.history.length) {
                    var phash = this.fm.history.pop();





                    this.fm.ajax({
                        cmd: 'open',
                        pathHash: phash,
                        target: phash
                    }, function (data) {
                        self.fm.reload(data);
                    });
                }
            }

            this.isAllowed = function () {
                return this.fm.history.length > 0 ? true : false;
            }

        },
        /**
         * @class. Open/close quickLook window
         * @param Object  elFinder
         **/
        quicklook: function (fm) {
            var self = this;
            this.name = 'Preview with Quick Look';
            this.fm = fm;

            this.exec = function () {
                self.fm.quickview.toggle();
            }

            this.isAllowed = function () {
                return this.fm.selected.length == 1;
            }

            this.cm = function () {
                return true;
            }
        },
        /**
         * @class Display files/folders info in dialog window
         * @param Object  elFinder
         **/
        info: function (fm) {
            var self = this;
            this.name = 'Get info';
            this.fm = fm;

            /**
             * Open dialog windows for each selected file/folder or for current folder
             **/
            this.exec = function () {
                var sel = this.fm.getSelected();
                var f, s, cnt = this.fm.selected.length, w = $(window).width(), h = $(window).height();

                this.fm.lockShortcuts(true);

                if (!cnt) {
                    /** nothing selected - show cwd info **/
                    info(self.fm.cwd);
                } else {
                    /** show info for each selected obj **/
                    $.each(sel, function () {
                        info(this);
                    });
                }

                function info (f) {
                    var p = ['50%', '50%'], x, y, d,
                            tb = '<table cellspacing="0" width="100%"><tr><td width="40%">' + self.fm.i18n('Name') + '</td><td width="60%">' + f.name + '</td></tr><tr><td>' + self.fm.i18n('Kind') + '</td><td>' + self.fm.mime2kind(f.link ? 'symlink' : f.mime) + '</td></tr><tr><td>' + self.fm.i18n('Size') + '</td><td>' + self.fm.formatSize(f.size) + '</td></tr><tr><td>' + self.fm.i18n('Modified') + '</td><td>' + self.fm.formatDate(f.date) + '</td></tr><tr><td>' + self.fm.i18n('Permissions') + '</td><td>' + self.fm.formatPermissions(f.read, f.write, f.rm) + '</td></tr>';

                    if (f.link) {
                        tb += '<tr><td>' + self.fm.i18n('Link to') + '</td><td>' + f.linkTo + '</td></tr>';
                    }
                    if (f.dim) {
                        tb += '<tr><td>' + self.fm.i18n('Dimensions') + '</td><td>' + f.dim + ' px.</td></tr>';
                    }
                    if (f.url) {
                        tb += '<tr><td>' + self.fm.i18n('URL') + '</td><td><a href="' + f.url + '" target="_blank">' + f.url + '</a></td></tr>';
                    }

                    if (cnt > 1) {
                        d = $('.fileman-dialog-info:last');
                        if (!d.length) {
                            x = Math.round(((w - 350) / 2) - (cnt * 10));
                            y = Math.round(((h - 300) / 2) - (cnt * 10));
                            p = [x > 20 ? x : 20, y > 20 ? y : 20];
                        } else {
                            x = d.offset().left + 10;
                            y = d.offset().top + 10;
                            p = [x < w - 350 ? x : 20, y < h - 300 ? y : 20];
                        }
                    }


                    jDialog($('<div class="fileman-dialog-info" />').append(tb + '</table>'), self.fm.i18n(f.mime == 'directory' ? 'Folder info' : 'File info'), function ()
                    {
                        if (--cnt <= 0) {
                            self.fm.lockShortcuts();
                        }
                    }, function (popup) {
                        popup.css({
                            left: p[0], top: p[1]
                        });
                    });

                }
            }

            this.cm = function (t) {
                return true;
            }
        },
        open: function (fm)
        {
            var self = this;
            this.name = 'Open';
            this.fm = fm;

            this.exec = function () {
                self.fm.lockShortcuts(true);
                var a = arguments;
                var s = this.fm.getSelected(0);

                // link to original
                s.hash = s.hash.replace(/^link_/, '');

                var args = {
                    action: 'open',
                    type: (s && s.mime == 'directory' ? 'dir' : 'file'),
                    pathHash: (s && s.hash ? s.hash : null),
                    target: this.fm.cwd.hash,
                    tree: true
                };


                this.fm.ajax(args, function (data)
                {
                    self.fm.reload(false); // reload directory only if is not a file (the selected)
                    self.fm.lockShortcuts();
                });
            }

            this.cm = function (t) {
                return t == 'cwd';
            }
        },
        /**
         * @class Reload current directory and navigation panel
         * @param Object elFinder
         **/
        reload: function (fm) {
            var self = this;
            this.name = 'Reload';
            this.fm = fm;

            this.exec = function ()
            {
                self.fm.lockShortcuts(true);
                var a = arguments;
                var args = {
                    action: 'open',
                    type: 'dir',
                    pathHash: this.fm.cwd.hash,
                    target: this.fm.cwd.hash,
                    tree: true
                };


                this.fm.ajax(args, function (data) {
                    self.fm.reload(false);
                    self.fm.lockShortcuts();
                });
            }

            this.cm = function (t) {
                return t == 'cwd';
            }
        },
        /**
         * @class Copy file/folder to "clipboard"
         * @param Object elFinder
         **/
        copy: function (fm) {
            this.name = 'Copy';
            this.fm = fm;

            this.exec = function () {
                this.fm.setBuffer(this.fm.selected);
            }

            this.isAllowed = function () {
                if (this.fm.selected.length) {
                    var s = this.fm.getSelected(), l = s.length;
                    while (l--) {
                        if (s[l].read) {
                            return true;
                        }
                    }
                }
                return false;
            }

            this.cm = function (t) {
                return t != 'cwd';
            }
        },
        /**
         * @class Cut file/folder to "clipboard"
         * @param Object elFinder
         **/
        cut: function (fm) {
            this.name = 'Cut';
            this.fm = fm;

            this.exec = function () {
                this.fm.setBuffer(this.fm.selected, 1);
            }

            this.isAllowed = function () {
                if (this.fm.selected.length) {
                    var s = this.fm.getSelected(), l = s.length;
                    while (l--) {
                        if (s[l].read && s[l].rm) {
                            return true;
                        }
                    }
                }
                return false;
            }

            this.cm = function (t) {
                return t != 'cwd';
            }
        },
        /**
         * @class Paste file/folder from "clipboard"
         * @param Object elFinder
         **/
        paste: function (fm) {
            var self = this;
            this.name = 'Paste';
            this.fm = fm;

            this.exec = function () {
                var i, d, f, r, msg = '';

                if (!this.fm.buffer.dst) {
                    this.fm.buffer.dst = this.fm.cwd.hash;
                }

                d = this.fm.layout.treeContainer.find('[hash="' + this.fm.buffer.dst + '"]');

                if (!d.length || d.hasClass('noaccess') || d.hasClass('readonly')) {
                    return this.fm.layout.error('Access denied');
                }

                if (this.fm.buffer.src == this.fm.buffer.dst) {
                    return this.fm.layout.error('Unable to copy into itself');
                }



                var o = {
                    action: 'paste',
                    current: this.fm.cwd.hash,
                    src: this.fm.buffer.src,
                    dst: this.fm.buffer.dst,
                    cut: this.fm.buffer.cut
                };
				var bufferFiles = this.fm.buffer.files;
                var selectAfterDelete = this.fm.buffer.dst;

                if (selectAfterDelete)
                {
                    self.fm.layout.doTreeUpdateHash = true;
                    self.fm.selected = [];
                    self.fm.selected.push(selectAfterDelete);
                }



                if (this.fm.jquery > 132) {
                    o.targets = this.fm.buffer.files;
                } else {
                    o['targets[]'] = this.fm.buffer.files;
                }

				if ( this.fm.buffer.files.length ) {
					this.fm.ajax(o, function (data)
					{
						for (var i = 0;i<bufferFiles.length;++i) {
							if (self.fm.opts.view == 'list') {
								$('tr[hash='+ bufferFiles[i] +']', self.fm.layout.foldercontentContainer ).remove();
							}
							else {
								$('div[hash='+ bufferFiles[i] +']', self.fm.layout.foldercontentContainer ).remove();
							}
						}

						self.fm.lock( );
						//data.dircontent && self.fm.prepareData(true);


					}, {
						force: true
					});
				}
            }

            this.isAllowed = function () {
                return this.fm.buffer.files;
            }

            this.cm = function (t) {
                return t == 'cwd';
            }
        },
        coverflow: function (fm) {
            var self = this;
            this.name = 'View Coverflow';
            this.fm = fm;

            this.exec = function () {
                self.fm.lockShortcuts(true);

                if (!this.fm.opts.coverflow)
                    this.fm.opts.coverflow = true;
                else
                    this.fm.opts.coverflow = false;

                // this.fm.opts.view = 'coverflow';
                this.fm.layout.foldercontentContainer.addClass('el-finder-disabled');
                this.fm.prepareData();
                this.fm.layout.foldercontentContainer.removeClass('el-finder-disabled');
                self.fm.lockShortcuts();
            }

            this.isAllowed = function () {
                return true; //this.fm.opts.view != 'coverflow';
            }

            this.cm = function (t) {
                return t == 'cwd';
            }
        },
        /**
         * @class Switch elFinder into icon view
         * @param Object elFinder
         **/
        icons: function (fm) {
            var self = this;
            this.name = 'View as icons';
            this.fm = fm;

            this.exec = function () {
                self.fm.lockShortcuts(true);
                this.fm.opts.view = 'icons';
                this.fm.layout.foldercontentContainer.addClass('el-finder-disabled');
                this.fm.prepareData();
                this.fm.layout.foldercontentContainer.removeClass('el-finder-disabled');
                self.fm.lockShortcuts();


            }

            this.isAllowed = function () {
                return this.fm.opts.view != 'icons';
            }

            this.cm = function (t) {
                return t == 'cwd';
            }
        },
        /**
         * @class Switch elFinder into list view
         * @param Object elFinder
         **/
        list: function (fm) {
            var self = this;
            this.name = 'View as list';
            this.fm = fm;

            this.exec = function () {
                self.fm.lockShortcuts(true);
                this.fm.opts.view = 'list';

                this.fm.layout.foldercontentContainer.addClass('el-finder-disabled');
                this.fm.prepareData();
                this.fm.layout.foldercontentContainer.removeClass('el-finder-disabled');
                self.fm.lockShortcuts();


            }

            this.isAllowed = function () {
                return this.fm.opts.view != 'list';
            }

            this.cm = function (t) {
                return t == 'cwd';
            }
        },
        /**
         * @class Create archive
         * @param Object elFinder
         **/
        archive: function (fm) {
            var self = this;
            this.name = 'Create archive';
            this.fm = fm;


            this.getDropDownButton = function ()
            {

                var ul = $('<ul rel="archive">').hide();

                for (var i = 0; i < self.fm.opts.archives.length; i++) {
                    ul.append($('<li>').attr('mode', self.fm.opts.archives[i]).append(self.fm.mime2kind(self.fm.opts.archives[i])));
                }


                ul.find('li').each(function () {
                    $(this).click(function () {
                        self.fm.lockShortcuts(true);
                        self.exec($(this).attr('mode'));
                    });
                });

                return ul;
            }

            this.exec = function (t)
            {

                var o = {
                    action: 'archive',
                    current: self.fm.cwd.hash,
                    type: $.inArray(t, this.fm.opts.archives) != -1 ? t : this.fm.opts.archives[0],
                    name: self.fm.i18n('Archive')
                };
                if (this.fm.jquery > 132) {
                    o.targets = self.fm.selected;
                } else {
                    o['targets[]'] = self.fm.selected;
                }

                this.fm.ajax(o, function (data) {

                    self.fm.reload(data);
                    self.fm.lockShortcuts();
                });
            }

            this.isAllowed = function () {
                if (this.fm.cwd.write && this.fm.selected.length) {
                    var s = this.fm.getSelected(), l = s.length;
                    while (l--) {
                        if (s[l].read) {
                            return true;
                        }
                    }
                }
                return false;
            }

            this.cm = function (t) {
                return t != 'cwd';
            }

            this.argc = function () {
                var i, v = [];
                for (i = 0; i < self.fm.opts.archives.length; i++) {
                    v.push({
                        'class': 'archive',
                        'argc': self.fm.params.archives[i],
                        'text': self.fm.view.mime2kind(self.fm.params.archives[i])
                    });
                }
                ;
                return v;
            }
        },
        /**
         * @class Extract files from archive
         * @param Object elFinder
         **/
        extract: function (fm) {
            var self = this;
            this.name = 'Uncompress archive';
            this.fm = fm;

            this.exec = function () {
                self.fm.lockShortcuts(true);
                this.fm.ajax({
                    action: 'extract',
                    current: this.fm.cwd.hash,
                    target: this.fm.getSelected(0).hash
                }, function (data) {
                    self.fm.reload(data);
                    self.fm.lockShortcuts();
                })
            }

            this.isAllowed = function () {
                var extract = this.fm.opts.extract,
                        cnt = extract && extract.length;
                return this.fm.cwd.write && this.fm.selected.length == 1 && this.fm.getSelected(0).read && cnt && $.inArray(this.fm.getSelected(0).mime, extract) != -1;
            }

            this.cm = function (t) {
                return t == 'file';
            }
        },
        /**
         * @class Resize image
         * @param Object  elFinder
         **/
        resize: function (fm) {
            var self = this;
            this.name = 'Resize image';
            this.fm = fm;

            this.exec = function () {

                Notifier.display('warn', 'Resize image is currently not implemented');
                return;

                var sel = this.fm.selected;
                //var s = this.fm.getSelected();

                var s = this.fm.dircontent[sel[0]];


                if (s[0] && s[0].write && s[0].dim) {
                    var size = s[0].dim.split('x'),
                            w = parseInt(size[0]),
                            h = parseInt(size[1]), rel = w / h
                    iw = $('<input type="text" size="9" value="' + w + '" name="width"/>'),
                            ih = $('<input type="text" size="9" value="' + h + '" name="height"/>'),
                            f = $('<form/>').append(iw).append(' x ').append(ih).append(' px');
                    iw.add(ih).bind('change', calc);
                    self.fm.lockShortcuts(true);
                    var d = $('<div/>').append($('<div/>').text(self.fm.i18n('Dimensions') + ':')).append(f).dialog({
                        title: self.fm.i18n('Resize image'),
                        dialogClass: 'el-finder-dialog',
                        width: 230,
                        modal: true,
                        close: function () {
                            self.fm.lockShortcuts();
                        },
                        buttons: {
                            Cancel: function () {
                                $(this).dialog('close');
                            },
                            Ok: function () {
                                var _w = parseInt(iw.val()) || 0,
                                        _h = parseInt(ih.val()) || 0;
                                if (_w > 0 && _w != w && _h > 0 && _h != h) {
                                    self.fm.ajax({
                                        cmd: 'resize',
                                        current: self.fm.cwd.hash,
                                        target: s[0].hash,
                                        width: _w,
                                        height: _h
                                    },
                                    function (data) {
                                        self.fm.reload(data);
                                        self.fm.lockShortcuts();
                                    });
                                }
                                $(this).dialog('close');
                            }
                        }
                    });
                }

                function calc () {
                    var _w = parseInt(iw.val()) || 0,
                            _h = parseInt(ih.val()) || 0;

                    if (_w <= 0 || _h <= 0) {
                        _w = w;
                        _h = h;
                    } else if (this == iw.get(0)) {
                        _h = parseInt(_w / rel);
                    } else {
                        _w = parseInt(_h * rel);
                    }
                    iw.val(_w);
                    ih.val(_h);
                }

            }

            this.isAllowed = function () {
                var sel = this.fm.selected;


                if (sel.length == 0 || !sel[0])
                    return false;
                if (this.fm.dircontent[sel[0]] == null)
                {
                    return false;
                }

                return sel.length == 1 && this.fm.dircontent[sel[0]].write && this.fm.dircontent[sel[0]].read && this.fm.dircontent[sel[0]].resize;
            }

            this.cm = function (t) {
                return t == 'file';
            }
        },
        /**
         * @class Remove files/folders
         * @param Object elFinder
         **/
        rm: function (fm) {
            var self = this;
            this.name = 'Remove';
            this.fm = fm;



            this.sendRequest = function (ids, dirs)
            {
                var o = {
                    action: 'delete',
                    current: self.fm.cwd.hash
                };


                var _ids = [], gotoroot = false;
                var selectAfterDelete = self.fm.cwd.hash;


                for (var i = 0; i < ids.length; i++)
                {
                    _ids.push(ids[i].hash);

                    if (ids[i].hash == self.fm.cwd.hash)
                    {
                        gotoroot = fm.root(ids[i]);
                    }

                    if (!gotoroot && ids[i].hasOwnProperty('phash'))
                    {
                        selectAfterDelete = ids[i].phash;
                    }

                }

                if (self.fm.jquery > 132) {
                    o.targets = _ids;
                } else {
                    o['targets[]'] = _ids;
                }

                if (dirs.length > 0 && dirs[0].hasOwnProperty('phash'))
                {
                    selectAfterDelete = dirs[0].phash;
                }


                self.fm.ajax(o, function (data) {

                    self.fm.lockShortcuts(true);
                    self.fm.cacheRemove(ids);


                    if (selectAfterDelete)
                    {
                        self.fm.layout.doTreeUpdateHash = true;
                        self.fm.selected = [];
                        self.fm.selected.push(selectAfterDelete);
                    }


                    gotoroot && self.fm.exec('open', gotoroot);
                    !gotoroot && data.tree && self.fm.reload(true);

                    self.fm.lockShortcuts();

                }, {
                    force: true
                });
            }





            this.exec = function ()
            {
                var i, dirs = [], ids = [], s = this.fm.getSelected();

                for (var i = 0; i < s.length; i++) {
                    if (!s[i].rm) {
                        return this.fm.layout.error(s[i].name + ': ' + this.fm.i18n('Access denied'));
                    }
                    ids.push(s[i]);

                    if (s[i].mime == 'directory') {
                        dirs.push(s[i]);
                    }
                }
                ;

                if (ids.length)
                {
                    this.fm.lockShortcuts(true);



                    jConfirm(this.fm.i18n('Are you sure you want to remove files?<br /> This cannot be undone!'), this.fm.i18n('Confirmation required'), function (ok) {
                        if (ok)
                        {
                            self.sendRequest(ids, dirs);
                        }
                        else
                        {
                            self.fm.lockShortcuts();
                        }
                    });


                }
            }

            this.isAllowed = function (f) {
                if (this.fm.selected.length) {
                    var s = this.fm.getSelected(), l = s.length;

                    for (var i in s) {
                        if (s[i].rm) {
                            return true;
                        }
                    }
                }
                return false;
            }

            this.cm = function (t) {
                return t != 'cwd';
            }
        },
        /**
         * @class Create new folder
         * @param Object elFinder
         **/
        mkdir: function (fm) {
            var self = this;
            this.name = 'New folder';
            this.fm = fm;

            this.exec = function () {
                self.fm.unselectAll();
                var n = this.fm.uniqueName('untitled folder'),
                        $input = $('<input type="text"/>').val(n),
                        prev = this.fm.layout.foldercontentContainer.find('.directory:last'),
                        /**
                         *  dummy data
                         */
                        f = {
                            name: n,
                            hash: '',
                            mime: 'directory',
                            read: true,
                            write: false,
                            date: '',
                            size: 0
                        },
                el = this.fm.opts.view == 'list' ? $(this.fm.layout.renderRow(f, 0, '')).children('td').eq(1).empty().append($input).end().end()
                        : $(this.fm.layout.renderIcon(f, 0, '')).children('label').empty().append($input).end()
                el.addClass('directory ui-selected');

                if (prev.length) {
                    el.insertAfter(prev);
                }
                else if (this.fm.opts.view == 'list') {

                    var lastDir = this.fm.layout.foldercontentContainer.find('tbody .cwd-icon-directory:last');
                    if (lastDir.length == 1)
                    {
                        var odd = lastDir.parents('tr:first').hasClass('odd') ? '' : 'odd';
                        el.insertAfter(lastDir.parents('tr:first'));
                        el.addClass(odd);
                    }
                    else
                    {
                        if (this.fm.layout.foldercontentContainer.find('tbody tr').length == 1)
                        {
                            var odd = this.fm.layout.foldercontentContainer.find('tbody tr').eq(0).hasClass('odd') ? '' : 'odd';
                            el.insertBefore(this.fm.layout.foldercontentContainer.find('tbody tr').eq(0));
                            el.addClass(odd);
                        }
                        else
                            el.appendTo(this.fm.layout.foldercontentContainer.find('tbody'));
                    }

                }
                else {


                    var lastDir = this.fm.layout.foldercontentContainer.find('.cwd-icon-directory:last');
                    if (lastDir.length == 1)
                    {
                        el.insertAfter(lastDir.parents('tr:first'));
                    }
                    else
                    {
                        el.insertBefore(this.fm.layout.foldercontentContainer.find('tbody tr').eq(0))
                    }



                    el.prependTo(this.fm.layout.foldercontentContainer)
                }


                self.fm.checkSelectedPos();

                $input.select().focus();

                $input.click(
                        function (e) {
                            e.stopPropagation();

                        }
                ).bind('change blur',
                        function (e) {
                            mkdir(e);
                        }
                ).keydown(
                        function (e) {
                            e.stopPropagation();
                            if (e.keyCode == 27) {
                                el.remove();
                                self.fm.lockShortcuts();
                            } else if (e.keyCode == 13) {
                                mkdir(e);
                            }
                        }
                );

                self.fm.lockShortcuts(true);

                function mkdir (e) {
                    if (!self.fm.locked)
                    {
                        var err, name = $input.val();
                        if (!self.fm.isValidName(name)) {
                            err = 'Invalid name';
                        } else if (self.fm.fileExists(name)) {
                            err = 'File or folder with the same name already exists';
                        }
                        if (err) {
                            self.fm.layout.error(err);
                            self.fm.lockShortcuts(true);
                            el.addClass('ui-selected');
                            return $input.select().focus();
                        }

                        self.fm.ajax({
                            action: 'mkdir',
                            current: self.fm.cwd.hash,
                            name: name
                        },
                        function (data)
                        {
                            if (data.error) {
                                el.addClass('ui-selected');
                                return $input.select().focus();
                            }

                            var d = data;

                            if (d.hasOwnProperty('select'))
                            {
                                self.fm.setSelected(d.select[0]);
                                self.fm.layout.doTreeUpdateHash = true;
                            }

                            self.fm.lockShortcuts(false);
                            self.fm.reload(true);

                            if (d.hasOwnProperty('select'))
                            {
                                self.fm.setSelected(d.select[0]);
                                self.fm.layout.doTreeUpdateHash = true;
                                self.fm.layout.updateTreeSelection();
                            }
                        },
                                {
                                    force: true
                                });
                    }
                }
            }

            this.isAllowed = function () {
                return this.fm.cwd.write;
            }

            this.cm = function (t) {
                return t == 'cwd';
            }
        },
        /**
         * @class Create new text file
         * @param Object elFinder
         **/
        mkfile: function (fm) {
            var self = this;
            this.name = 'New text file';
            this.fm = fm;

            this.exec = function () {
                self.fm.unselectAll();
                var n = this.fm.uniqueName('untitled file', '.txt'),
                        $input = $('<input type="text"/>').val(n),
                        f = {
                            name: n,
                            hash: '',
                            mime: 'text/plain',
                            read: true,
                            write: true,
                            date: '',
                            size: 0
                        },
                el = this.fm.opts.view == 'list' ? $(this.fm.layout.renderRow(f, 0, '')).children('td').eq(1).empty().append($input).end().end()
                        : $(this.fm.layout.renderIcon(f, 0, '')).children('label').empty().append($input).end()

                el.addClass('text ui-selected').appendTo(this.fm.opts.view == 'list' ? this.fm.layout.foldercontentContainer.find('tbody') : this.fm.layout.foldercontentContainer);

                $input.select().focus();

                $input.bind('change blur', function (e) {
                    mkfile(e);
                });

                $input.click(function (e) {
                    e.stopPropagation();
                });

                $input.keydown(function (e) {
                    e.stopPropagation();
                    if (e.keyCode == 27) {
                        el.remove();
                        self.fm.lockShortcuts();
                    } else if (e.keyCode == 13) {
                        mkfile(e);
                    }
                });

                self.fm.lockShortcuts(true);

                function mkfile (e) {
                    if (!self.fm.locked) {
                        var err, name = $input.val();
                        if (!self.fm.isValidName(name)) {
                            err = 'Invalid name';
                        } else if (self.fm.fileExists(name)) {
                            err = 'File or folder with the same name already exists';
                        }
                        if (err) {
                            self.fm.layout.error(err);
                            self.fm.lockShortcuts(true);
                            el.addClass('ui-selected');

                            return $input.select().focus();
                        }
                        self.fm.ajax({
                            action: 'mkfile',
                            current: self.fm.cwd.hash,
                            name: name
                        }, function (data) {
                            if (data.error) {
                                el.addClass('ui-selected');
                                return $input.select().focus();
                            }
                            self.fm.reload(false);
                        }, {
                            force: true
                        });
                    }
                }

            }

            this.isAllowed = function (f) {
                return this.fm.cwd.write;
            }

            this.cm = function (t) {
                return t == 'cwd';
            }
        },
        /**
         * @class Upload files
         * @param Object elFinder
         **/
        upload: function (fm) {
            var self = this;
            this.name = 'Upload files';
            this.fm = fm;

            this.exec = function () {


                this.fm.layout.toggleUpload();


                if (this.fm.layout.isUploadMode) {
                    
                    
                    this.fm.layout.toolbar.find('.upload').addClass('disabled');

                    var path = self.fm.layout.getCwdPath();
					self.fm.layout.uploadPath.empty().append('Upload Path: ' + (path == '' ? '/' : '/' + path));

					var uploadContainer;

					if (!self.fm.opts.treePanel) {
						uploadContainer = self.fm.el.find('#upload-drop');
					}
					else {
						uploadContainer = self.fm.opts.treePanel.find('#upload-drop');
					}

					Tools.MultiUploadControl({
						refresh: false,
						url: self.fm.opts.connectorUrl,
						control: uploadContainer,
						postParams: {
							adm: "fileman",
							action: "upload",
							current: self.fm.cwd.hash
						},
						file_queue_limit: 3,
						max_upload_files: 30,
						file_type_mask: '*.*',
						file_type_text: 'Alle Dateien',
						filePostParamName: 'upload',
						onAdd: function ()
						{

						},
						onUploadStart: function ()
						{
							self.fm.lock(true);
						},
						onComplite: function (data, evaldata, file)
						{
							self.fm.lock();

							self.fm.data = data;
							self.fm.reload(false);
						},
						onSuccess: function (data, evaldata, file, listItem) {
							setTimeout(function () {
								listItem.fadeOut(300, function () {
									$(this).remove();
								});
							}, 1000);
						}
					});


					/*
                    new Tools.MultiUploadControl({
                        refresh: false,
                        url: this.fm.opts.connectorUrl,
                        control: $('#upload-drop', $(this.fm.layout.upload) ),
                        postParams: {
                            adm: "fileman",
                            action: "upload",
                            current: this.fm.cwd.hash
                        },
                        file_queue_limit: 3,
                        max_upload_files: 30,
                        file_type_mask: '*.*',
                        file_type_text: 'Alle Dateien',
                        filePostParamName: 'upload',
                        onAdd: function ()
                        {

                        },
                        onUploadStart: function ()
                        {
                            self.fm.lock(true);
                        },
                        onComplite: function (data, evaldata, file)
                        {
                            self.fm.lock();
                            self.fm.data = data;
                            self.fm.reload(false);

                        },
                        onSuccess: function (data, evaldata, file, listItem) {
                            setTimeout(function () {
                                listItem.fadeOut(300, function () {
                                    $(this).remove();
                                });
                            }, 1000);
                        }
                    });
                    */
                }
                else {
                    this.fm.layout.toolbar.find('.upload').removeClass('disabled');
                }


                return;





                var id = 'el-finder-io-' + (new Date().getTime()),
                        e = $('<div class="ui-state-error ui-corner-all"><span class="ui-icon ui-icon-alert"/><div/></div>'),
                        m = this.fm.opts.uplMaxSize ? '<p>' + this.fm.i18n('Maximum allowed files size') + ': ' + this.fm.opts.uplMaxSize + '</p>' : '',
                        b = $('<p class="el-finder-add-field"><span class="ui-state-default ui-corner-all"><em class="ui-icon ui-icon-circle-plus"/></span>' + this.fm.i18n('Add field') + '</p>')
                        .click(function () {
                            $(this).before('<p><input type="file" name="upload[]"/></p>');
                        }),
                        f = '<form method="post" enctype="multipart/form-data" action="' + self.fm.options.url + '" target="' + id + '"><input type="hidden" name="cmd" value="upload" /><input type="hidden" name="current" value="' + self.fm.cwd.hash + '" />',
                        d = $('<div/>'),
                        i = 3;

                while (i--) {
                    f += '<p><input type="file" name="upload[]"/></p>';
                }

                // Rails csrf meta tag (for XSS protection), see #256
                var rails_csrf_token = $('meta[name=csrf-token]').attr('content');
                var rails_csrf_param = $('meta[name=csrf-param]').attr('content');
                if (rails_csrf_param != null && rails_csrf_token != null) {
                    f += '<input name="' + rails_csrf_param + '" value="' + rails_csrf_token + '" type="hidden" />';
                }

                f = $(f + '</form>');

                d.append(f.append(e.hide()).prepend(m).append(b)).dialog({
                    dialogClass: 'fileman-dialog',
                    title: self.fm.i18n('Upload files'),
                    modal: true,
                    resizable: false,
                    close: function () {
                        self.fm.lockShortcuts();
                    },
                    buttons: {
                        Cancel: function () {
                            $(this).dialog('close');
                        },
                        Ok: function () {
                            if (!$(':file[value]', f).length) {
                                return error(self.fm.i18n('Select at least one file to upload'));
                            }
                            setTimeout(function () {
                                self.fm.lock();
                                if ($.browser.safari) {
                                    $.ajax({
                                        url: self.fm.options.url,
                                        data: {
                                            cmd: 'ping'
                                        },
                                        error: submit,
                                        success: submit
                                    });
                                } else {
                                    submit();
                                }
                            });
                            $(this).dialog('close');
                        }
                    }
                });

                self.fm.lockShortcuts(true);

                function error (err) {
                    e.show().find('div').empty().text(err);
                }

                function submit () {
                    var $io = $('<iframe name="' + id + '" name="' + id + '" src="about:blank"/>'),
                            io = $io[0],
                            cnt = 50,
                            doc, html, data;

                    $io.css({
                        position: 'absolute',
                        top: '-1000px',
                        left: '-1000px'
                    })
                            .appendTo('body').bind('load', function () {
                        $io.unbind('load');
                        result();
                    });

                    self.fm.lock(true);
                    f.submit();

                    function result () {
                        try {
                            doc = io.contentWindow ? io.contentWindow.document : io.contentDocument ? io.contentDocument : io.document;
                            /* opera */
                            if (doc.body == null || doc.body.innerHTML == '') {
                                if (--cnt) {
                                    return setTimeout(result, 100);
                                } else {
                                    complite();
                                    return self.fm.view.error('Unable to access iframe DOM after 50 tries');
                                }
                            }
                            /* get server response */
                            html = $(doc.body).html();
                            if (self.fm.jquery >= 141) {
                                data = $.parseJSON(html);
                            } else if (/^[\],:{}\s]*$/.test(html.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, "@")
                                    .replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, "]")
                                    .replace(/(?:^|:|,)(?:\s*\[)+/g, ""))) {
                                /* get from jQuery 1.4 */
                                data = window.JSON && window.JSON.parse ? window.JSON.parse(html) : (new Function("return " + html))();
                            } else {
                                data = {
                                    error: 'Unable to parse server response'
                                };
                            }

                        } catch (e) {
                            data = {
                                error: 'Unable to parse server response'
                            };
                        }
                        complite();
                        data.error && self.fm.view.error(data.error, data.errorData);
                        data.cwd && self.fm.reload(data);
                        data.tmb && self.fm.tmb();
                    }

                    function complite () {
                        self.fm.lock();
                    }

                }

            };

            this.isAllowed = function () {

                return this.fm.cwd.write;
            };

            this.cm = function (t) {
                return t == 'cwd';
            };
        },
        /**
         * @class Make file/folder copy
         * @param Object elFinder
         **/
        duplicate: function (fm) {
            var self = this;
            this.name = 'Duplicate';
            this.fm = fm;

            this.exec = function () {
                this.fm.ajax({
                    cmd: 'duplicate',
                    current: this.fm.cwd.hash,
                    target: this.fm.selected[0]
                },
                function (data) {
                    self.fm.reload(data);
                });
            }

            this.isAllowed = function () {
                return this.fm.cwd.write && this.fm.selected.length == 1 && this.fm.getSelected()[0].read;
            }

            this.cm = function (t) {
                return t == 'file';
            }
        },
        /**
         * @class Edit text file
         * @param Object elFinder
         **/
        edit: function (fm) {
            var self = this;
            this.name = 'Edit text file';
            this.fm = fm;

            this.exec = function () {
                var f = this.fm.getSelected(0);

                this.fm.lockShortcuts(true);
                this.fm.ajax({
                    action: 'read',
                    current: this.fm.cwd.hash,
                    target: f.hash
                }, function (data) {


                    if (data.error)
                    {
                        self.fm.lockShortcuts();
                        self.fm.lock(false);
                        Notifier.display('error', data.error);
                        return true;
                    }

                    self.fm.lock(false);
                    self.fm.lockShortcuts(true);


                    var ta = $('<textarea/>').val(data.content || '').keydown(function (e) {
                        e.stopPropagation();
                        if (e.keyCode == 9) {
                            e.preventDefault();
                            if ($.browser.msie) {
                                var r = document.selection.createRange();
                                r.text = "\t" + r.text;
                                this.focus();
                            }
                            else {
                                var before = this.value.substr(0, this.selectionStart),
                                        after = this.value.substr(this.selectionEnd);
                                this.value = before + "\t" + after;
                                this.setSelectionRange(before.length + 1, before.length + 1);
                            }
                        }
                    });


                    var baseVal = ta.val();


                    jDialog($('<div/>').append(ta), self.fm.i18n(self.name), function (ok)
                    {
                        if (ok)
                        {
                            var c = ta.val();
                            if (baseVal != c)
                            {
                                $.ajax({
                                    url: self.fm.opts.connectorUrl,
                                    method: 'POST',
                                    data: {
                                        adm: 'fileman',
                                        action: 'edit',
                                        current: self.fm.cwd.hash,
                                        target: f.hash,
                                        content: c
                                    },
                                    dataType: 'json',
                                    async: true,
                                    cache: false,
                                    error: function ()
                                    {
                                        self.fm.ajaxLoading = false;
                                    },
                                    success: function (data)
                                    {
                                        if (data.error)
                                        {
                                            self.fm.lockShortcuts();
                                            self.fm.lock(false);
                                            Notifier.display('error', data.error);
                                            return true;
                                        }


                                        self.fm.lockShortcuts();

                                        if (data.target) {
                                            // self.fm.cdc[data.target.hash] = data.target;
                                            self.fm.layout.updateFile(data.target);
                                            self.fm.selectById(data.target.hash);
                                        }
                                    }
                                });
                            }

                            self.fm.lockShortcuts();
                        }

                    });



                    /*
                     
                     $('<div/>').append(ta)
                     .dialog({
                     dialogClass: 'el-finder-dialog',
                     title: self.fm.i18n(self.name),
                     modal: true,
                     width: 500,
                     close: function() {
                     self.fm.lockShortcuts();
                     },
                     buttons: {
                     Cancel: function() {
                     $(this).dialog('close');
                     },
                     Ok: function() {
                     var c = ta.val();
                     $(this).dialog('close');
                     self.fm.ajax({
                     cmd: 'edit',
                     current: self.fm.cwd.hash,
                     target: f.hash,
                     content: c
                     }, function(data) {
                     if (data.target) {
                     self.fm.cdc[data.target.hash] = data.target;
                     self.fm.view.updateFile(data.target);
                     self.fm.selectById(data.target.hash);
                     
                     }
                     }, {
                     type: 'POST'
                     });
                     }
                     }
                     });
                     
                     */


                });
            }

            this.isAllowed = function () {
                if (self.fm.selected.length == 1) {
                    var f = this.fm.getSelected(0);
                    return f.write && f.read && (f.mime.indexOf('text') == 0 || f.mime == 'application/x-empty' || f.mime == 'application/xml');
                }
            }

            this.cm = function (t) {
                return t == 'file';
            }
        },
        /**
         * @class Rename file/folder
         * @param Object elFinder
         **/
        rename: function (fm) {
            var self = this;
            this.name = 'Rename';
            this.fm = fm;

            this.exec = function () {
                var s = this.fm.getSelected(), el, c, input, f, n;

                if (s.length == 1) {


					f = s[0];
					if (this.fm.opts.view == 'list') {
						el = this.fm.layout.foldercontentContainer.find('tr[hash="' + f.hash + '"]');
					}
					if (this.fm.opts.view == 'icons') {
						el = this.fm.layout.foldercontentContainer.find('div[hash="' + f.hash + '"]');
					}

                    c = this.fm.opts.view == 'icons' ? el.find('label:first') : el.find('td').eq(1);
                    n = c.html();


                    var $input = $('<input type="text" />')
                            .val(f.name)
                            .appendTo(c.empty())
                            .bind('change blur', rename)
                            .keydown(function (e) {
                                e.stopPropagation();
                                if (e.keyCode == 27) {
                                    restore();
                                } else if (e.keyCode == 13) {
                                    if (f.name == $(this).val()) {
                                        restore();
                                    } else {
                                        $(this).trigger('change');
                                    }
                                }
                            }).click(function (e) {
                        e.stopPropagation();

                    }).select().focus();

                    this.fm.lockShortcuts(true);
                }

                function restore () {
                    c.html(n);
                    self.fm.lockShortcuts();
                }

                function rename () {

                    if (!self.fm.locked) {
                        var err, name = $input.val();
                        if (f.name == $input.val()) {
                            return restore();
                        }

                        if (!self.fm.isValidName(name)) {
                            err = 'Invalid name';
                        } else if (self.fm.fileExists(name)) {
                            err = 'File or folder with the same name already exists';
                        }

                        if (err) {
                            self.fm.layout.error(err);
                            el.addClass('ui-selected');
                            self.fm.lockShortcuts(true);
                            return $input.select().focus();
                        }

                        self.fm.ajax({
                            action: 'rename',
                            current: self.fm.cwd.hash,
                            target: f.hash,
                            name: name
                        }, function (data) {
                            if (data.error) {
                                restore();
                            } else {
                                f.mime == 'directory' && self.fm.removePlace(f.hash) && self.fm.addPlace(data.target);
                                self.fm.reload(data);
                            }
                        }, {
                            force: true
                        });
                    }
                }
            }

            /**
             * Return true if only one file selected and has write perms and current dir has write perms
             * @return Boolean
             */
            this.isAllowed = function () {
                return this.fm.cwd.write && this.fm.getSelected(0).write;
            }

            this.cm = function (t) {
                return t == 'file';
            }
        }
    }
})(jQuery, window);