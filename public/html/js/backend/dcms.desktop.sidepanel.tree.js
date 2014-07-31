ns('Desktop.Sidepanel.Tree');


Desktop.Sidepanel.Tree = {
    treeSelectMode: 'default',
    currentNode: null,
    tfpm: false,
    level: 0,
    copyItem: 0,
    moveItem: 0,
    //
    el: null,
    inited: false,
    init: function (el)
    {
        if (this.inited)
        {
            return;
        }

        this.el = $('#documents-content');
        this.el.empty();
        this.inited = true;
    },
    addContextMenu: function ()
    {
        if ($('#document-tree-context').length == 0)
        {

        }
    },
    reload: function ()
    {
        this.build();
    },
    build: function ()
    {
        this.rebuildTree();
        return;

        var self = this;
        this.el.empty();
        $.get('admin.php?adm=dashboard&action=tree&_' + new Date().getTime(), function () {
            if (treeData && openData)
            {
                self.buildTree(treeData, openData);
            }
        }, 'script');
    },
    getData: function (params, callback)
    {
        if (typeof params == 'undefined')
        {
            params = {};
        }

        params.adm = 'dashboard';
        params.action = 'contenttree';

        $.post('admin.php?ajax=1', params, function (data) {

            if (typeof callback == 'function')
            {
                callback(data);
            }
            else
            {
                return data;
            }

        }, 'json');
    },
    getBaseImage: function ()
    {
        return $('<img/>').attr({
            alt: '',
            width: 16,
            height: 16
        });
    },
    getIndent: function (level)
    {
        return $('<div/>').css({
            width: (10 * level)
        }).addClass('tree-indenter');
    },
    getToggler: function () {
        return $('<span/>').addClass('toggle-icon plus');
    },
    getNodeIcon: function (icon, nodedata)
    {
        if (typeof icon == 'undefined' || icon == null) {
            icon = Config.get('backendImagePath', '') + 'tree/page.png';
        }

        var locked = false;
        if (typeof nodedata.locked != 'undefined' && nodedata.locked > 0)
        {
            locked = true;
            icon = Config.get('backendImagePath', '') + 'tree/page-locked.png';
        }




        if (typeof nodedata == 'object')
        {
            if (nodedata.published == 1)
            {
                icon = Config.get('backendImagePath', '') + 'tree/page.png';
                if (locked)
                {
                    icon = Config.get('backendImagePath', '') + 'tree/page-locked.png';
                }
            }

            if (nodedata.published < 1)
            {
                icon = Config.get('backendImagePath', '') + 'tree/page.png';
                if (locked)
                {
                    icon = Config.get('backendImagePath', '') + 'tree/page-locked.png';
                }
            }

            if (typeof nodedata.draft != 'undefined' && nodedata.draft > 0)
            {
                icon = Config.get('backendImagePath', '') + 'tree/document-clock.png';
                if (locked)
                {
                    icon = Config.get('backendImagePath', '') + 'tree/document-locked.png';
                }
            }

            if (typeof nodedata.isindexpage != 'undefined' && nodedata.isindexpage == 1)
            {
                icon = Config.get('backendImagePath', '') + 'tree/page-index.png';
                if (locked)
                {
                    icon = Config.get('backendImagePath', '') + 'tree/page-index-locked.png';
                }
            }

            if (typeof nodedata.is_folder != 'undefined' && nodedata.is_folder == 1)
            {
                icon = Config.get('backendImagePath', '') + 'tree/folder-closed.png';
                if (locked)
                {
                    icon = Config.get('backendImagePath', '') + 'tree/folder-closed-locked.png';
                }
            }

        }


        return this.getBaseImage().attr({
            src: icon
        }).addClass('tree-node-icon');
    },
    getEmptyNode: function (container, level)
    {
        //var level = (container.prev().children('img:first').width() / 16) + 2;

        var div = $('<div>').addClass('tree-node tree-node-empty');
        div.append(this.getIndent(level));
        div.append(this.getNodeIcon(Config.get('backendImagePath', '') + 'tree/page.png', false).css({
            opacity: .5
        }));
        div.append(cmslang.empty_node);

        return div;
    },
    updateIndenter: function (node, container)
    {
        var self = this, tonode = $(container).prev();
        if (!tonode.hasClass('isFolder'))
        {
            return;
        }


        var treeActions = $(node).data('nodeActions');
        var level = parseInt(tonode.attr('level'));
        var itemNewLevel = level;

        var toCatid = (typeof $(tonode).attr('catid') != 'undefined' && $(tonode).attr('catid') != null ? parseInt($(tonode).attr('catid')) : 0);
        var nodeCatid = parseInt($(node).attr('catid'));
        $(node).removeAttr('style');

        // move only if level is changed
        if (typeof treeActions.moveitem === 'string' && nodeCatid != toCatid && !$(node).hasClass('isFolder'))
        {
            var newIndent = this.getIndent(itemNewLevel + 2);
            $(node).find('.tree-indenter:first').replaceWith(newIndent);

            var url = treeActions.moveitem;
            url = url.replace('{itemid}', $(node).attr('contentid'));
            url = url.replace('{catid}', toCatid);

            $.post('admin.php', url, function (data) {

                $(self.el).find('.isFolder').each(function () {
                    if ($(this).hasClass('can-drop')) {
                        $(this).droppable('enable');
                    }
                });

                if (Tools.responseIsOk(data))
                {
                    $(node).attr('catid', toCatid).attr('level', itemNewLevel + 1);

                }
            }, 'json');
        }
        else if (typeof treeActions.movecat === 'string' && nodeCatid != toCatid && $(node).hasClass('isFolder'))
        {
            var newIndent = this.getIndent(itemNewLevel + 1);
            $(node).find('.tree-indenter:first').replaceWith(newIndent);

            var url = treeActions.movecat;
            url = url.replace('{itemid}', nodeCatid);
            url = url.replace('{catid}', toCatid);

            $.post('admin.php', url, function (data) {

                $(self.el).find('.isFolder').each(function () {
                    if ($(this).hasClass('can-drop')) {
                        $(this).droppable('enable');
                    }
                });

                if (Tools.responseIsOk(data))
                {
                    $(node).attr('catid', toCatid).attr('level', itemNewLevel + 1);
                    if (data.msg) {
                        Notifier.info(data.msg);
                    }
                }
                else {
                    jAlert((data && data.msg ? data.msg : 'Sorry there was an error'), 'Error...');
                }
            }, 'json');
        }
        else
        {
            //$(node).attr('level', itemNewLevel );

            $(this.el).find('.isFolder').each(function () {
                if ($(this).hasClass('can-drop')) {
                    $(this).droppable('enable');
                }
            });
        }
    },
    bindDraggable: function ()
    {
        var self = this;

        $(this.el).find('.tree-node').filter('ui-draggable').draggable('destroy');
        $(this.el).find('.tree-node').filter('ui-droppable').droppable('destroy');

        $(this.el).find('.tree-node').each(function () {
            if (!$(this).hasClass('isFolder'))
            {
                var modul = $(this).attr('modul');
                var allowMove = $(this).data('nodeActions');

                if (allowMove && typeof allowMove.moveitem === 'string')
                {
                    $(this).draggable({
                        handle: ".tree-node-icon",
                        opacity: 1,
                        cursor: "move",
                        helper: function () {
                            var h = $(this).clone(false, false);
                            h.css({width: $(this).width()});


                            return h;
                        },
                        revert: 'invalid',
                        appendTo: "body",
                        zIndex: 99999,
                        scroll: false,
                        start: function ()
                        {
                            var _modul = $(this).attr('modul');



                            $(self.el).find('div.tree-node:not([modul="' + _modul + '"])').each(function () {

                                if ($(this).hasClass('ui-droppable')) {
                                    $(this).droppable('disable');
                                }
                                if ($(this).hasClass('ui-draggable')) {
                                    $(this).draggable('disable');
                                }

                            });


                        },
                        stop: function ()
                        {
                            var _modul = $(this).attr('modul');
                            $(self.el).find('div.tree-node:not([modul="' + _modul + '"])').each(function () {

                                if ($(this).hasClass('ui-droppable')) {
                                    $(this).droppable('enable');
                                }
                                if ($(this).hasClass('ui-draggable')) {
                                    $(this).draggable('enable');
                                }

                            });
                        }
                    });
                }
            }
            else if ($(this).hasClass('isFolder'))
            {
                var allowMove = $(this).data('nodeActions');

                if (allowMove && typeof allowMove.moveitem === 'string' || $(this).attr('level') == 0)
                {
                    $(this).addClass('can-drop').droppable({
                        hoverClass: "drop-hover",
                        over: function () {
                            if ($(this).hasClass('ui-droppable-disabled') && $('body').hasClass('dragging')) {
                                $('body').css('cursor', 'not-allowed');
                            }
                        },
                        out: function () {

                            $('body').css('cursor', '');

                        },
                        drop: function (event, ui) {
                            if (event.originalEvent.type == 'mouseup')
                            {
                                var $to = $(this).next();
                                var $item = $(ui.draggable);


                                if ($item.attr('id') === $(this).attr('id')) {
                                    jAlert('Kann leider nicht in sich selbst verschoben werden', 'Error...');
                                }
                                else {
                                    $item.fadeOut(10, function () {

                                        if ($item.hasClass('isFolder'))
                                        {
                                            if ($to.find('>div.isFolder:last').length) {
                                                $item.insertAfter($to.find('>.isFolder:last-child'));
                                            }
                                            else {
                                                $to.prepend($item);
                                            }
                                        }
                                        else {
                                            $item.appendTo($to);
                                        }

                                        $to.find('.tree-node-empty').remove();
                                        $item.fadeIn(10, function () {
                                            self.updateIndenter($item, $to);
                                        });
                                    });
                                }
                            }
                        }
                    });
                }

                if (allowMove && typeof allowMove.movecat === 'string') { 
                    $(this).draggable({
                        handle: ".tree-node-icon",
                        opacity: 1,
                        cursor: "move",
                        helper: function () {
                            var h = $(this).clone(false, false);
                            h.css({width: $(this).width()});
                            return h;
                        },
                        revert: 'invalid',
                        appendTo: "body",
                        zIndex: 99999,
                        scroll: false,
                        start: function ()
                        {
                            $('body').addClass('dragging');
                            var _modul = $(this).attr('modul');
                            $(self.el).find('div.tree-node:not([modul="' + _modul + '"])').each(function () {

                                if ($(this).hasClass('ui-droppable')) {
                                    $(this).droppable('disable');
                                }
                                if ($(this).hasClass('ui-draggable')) {
                                    $(this).draggable('disable');
                                }

                            });
                        },
                        stop: function ()
                        {
                            $('body').removeClass('dragging');
                            var _modul = $(this).attr('modul');
                            $(self.el).find('div.tree-node:not([modul="' + _modul + '"])').each(function () {

                                if ($(this).hasClass('ui-droppable')) {
                                    $(this).droppable('enable');
                                }
                                if ($(this).hasClass('ui-draggable')) {
                                    $(this).draggable('enable');
                                }
                            });
                        }
                    });
                }

            }
        });


    },
    unbindDraggable: function ()
    {

    },
    rebuildTree: function (froceReload)
    {
        var force = {};
        if (typeof froceReload != 'undefined' && froceReload)
        {
            force.refresh = true;
        }

        var fpm, _self = this;



        if (!$(this.el).find('#nodes').length)
        {
            $(this.el).append('<div id="nodes"/>');
        }

        var el = $(this.el).find('#nodes');
        el.empty();
        $(el).mask('laden...');

        this.getData(force, function (data) {
            if (Tools.responseIsOk(data))
            {

                this.copyItem = 0;
                this.moveItem = 0;

                var root = $('<div>');
                root.append($('<a>'));

                el.empty();
                el.append($('<div>').attr('id', 'tree-node-1')).css({
                    marginTop: -5,
                    marginBottom: 10,
                    height: 'auto'
                });
                el.append($('<div>').addClass('nodes-container'));

                var container = $('#tree-node-1').next('.nodes-container');
                var last_node = {}, first_node = null;
                var i, _x = 1;
                for (i in data.modules)
                {
                    var dat = data.modules[i];

                    container.append(_self.buildNode(dat, _x));

                    if (first_node == null)
                    {
                        first_node = dat;
                    }


                    if (dat.is_folder == 1) {
                        container.append($('<div>').addClass('nodes-container').css({
                            display: 'none'
                        }));
                    }

                    var subs = $(container).find('.nodes-container:last');
                    var root = $(container).find('.nodes-container:last');

                    if (typeof dat.categories != 'undefined')
                    {
                        startlevel = (container.prev().children('span:first').width() / 16) + 1;
                        var lastsubnode = subs;

                        for (var x in dat.categories) {
                            _x++;

                            //dat.categories[x].level = (container.prev().children('span:first').width()/16) + 1;
                            if (typeof last_node.level != 'undefined' && last_node.level > 1 && dat.categories[x].level > last_node.level)
                            {
                                //	subs.find('.plus:last').removeClass('plus').addClass('minus');
                                //subs.append(_self.buildNode(dat.categories[x], _x));
                                //subs.find('.nodes-container:last').prev().find('.plus').removeClass('plus').addClass('minus');
                            }
                            else
                            {
                                //subs.parents('.nodes-container:first').find('.plus:last').removeClass('plus').addClass('minus');
                                //subs = subs.parents('.nodes-container:first');
                            }


                            subs.append(_self.buildNode(dat.categories[x], _x));

                            if (dat.categories[x].is_folder == 1) {
                                var subnode = $('<div>').addClass('nodes-container').css({
                                    display: 'none'
                                });
                                subs.append(subnode);


                                if (typeof dat.categories[x].items != 'undefined' && dat.categories[x].items.length > 0)
                                {
                                    subs.find('.nodes-container:last').prev().find('.plus').removeClass('plus').addClass('minus');

                                    for (var y in dat.categories[x].items) {
                                        var item = dat.categories[x].items[y];
                                        item.level = last_node.level + 1;
                                        item.level = (subs.prev().children('span:first').width() / 16) + 1

                                        subnode.append(_self.buildNode(item));
                                    }

                                    subs.children('.nodes-container:last').show();
                                }

                            }

                            subs.show();
                            last_node = dat.categories[x];
                        }
                    }

                    _x++;
                }



                if (last_node.id)
                {
                    var ancestors = $('#tree-node-' + last_node.id).parents('.nodes-container');
                    ancestors.each(function () {
                        var pm_icon = $(this).prev().children('a').find('span:first');
                        if (pm_icon.length == 1) {
                            pm_icon.removeClass('plus').addClass('minus');
                            pm_icon.next().attr({
                                src: pm_icon.next().attr('src').replace('folder-closed', 'folder-open')
                            });
                        }
                    });
                }


                $('#tree-node-' + first_node.id).addClass('tree-node-active');
                $(el).unmask();


                _self.contextMenu();
                _self.bindDraggable();
            }
            else
            {
                $(_self.el).unmask();
                _self.bindDraggable();

                if (typeof data.controlleractionperm != 'undefined' && !data.controlleractionperm || (typeof data.controllerperm != 'undefined' && !data.controllerperm))
                {
                    $(_self.el).empty();
                    var parent = $(_self.el).parent();
                    $(parent).prev().hide();
                    $(parent).hide();
                }
                else
                {
                    alert(data.msg);
                }
            }

        });
    },
    buildTree: function (data, openTo) {


        $(this.el).mask('laden...');

        this.copyItem = 0;
        this.moveItem = 0;
        var self = this;

        var root = $('<div>');
        root.append($('<a>'));
        this.el.empty();
        this.el.append($('<div>').attr('id', 'tree-node-1')).css({
            paddingTop: 5
        });
        this.el.append($('<div>').addClass('nodes-container'));

        var i, container = $('#tree-node-1').next('.nodes-container');
        for (i in data) {

            container.append(this.buildNode(data[i]));

            if (data[i].is_folder == 1) {
                container.append($('<div>').addClass('nodes-container').css({
                    display: 'none'
                }));
            }
        }

        i = null;



        var last_node = {};
        for (i in openTo) {
            container = $('#tree-node-' + openTo[i][0].parent).next('.nodes-container');
            for (var x in openTo[i])
            {
                container.append(this.buildNode(openTo[i][x]));

                if (openTo[i][x].is_folder == 1) {
                    container.append($('<div>').addClass('nodes-container').css({
                        display: 'none'
                    }));
                }
                container.show();
            }

            last_node = openTo[i][x];
        }

        if (last_node.id) {
            var ancestors = $('#tree-node-' + last_node.id).parents('.nodes-container');
            ancestors.each(function () {
                var pm_icon = $(this).prev().children('a').find('i:first');
                if (pm_icon.length == 1) {
                    pm_icon.attr({
                        src: pm_icon.attr('src').replace('plus', 'minus')
                    });
                    pm_icon.next().attr({
                        src: pm_icon.next().attr('src').replace('folder-closed', 'folder-open')
                    });
                }
            });
        }
        /*
         if (typeof openTree != 'undefined' && openTree)
         {
         $('#tree-node-' + openTree).addClass('tree-node-active');
         setTimeout(function() {
         $('#tree-node-' + openTree).find('a:first').click();
         }, 10);
         }
         */

        $(this.el).find('.toggle-icon').css({
            opacity: 0
        });

        $(this.el).bind('mouseenter', function () {
            if (self.tfpm !== false) {
                window.clearTimeout(self.tfpm);
            }
            self.tfpm = false;
            $(this.el).find('.toggle-icon').fadeTo('fast', 1)
        });

        $(this.el).bind('mouseleave', function () {
            self.tfpm = window.setTimeout(function () {
                $(this.el).find('.toggle-icon').fadeTo('slow', 0)
            }, 1500)
        });

        $(this.el).unmask();


        $('.tree-node span').draggable({
            connectToSortable: "#assigned-items",
            forceHelperSize: true,
            forcePlaceholderSize: true,
            placeholder: 'ui-state-highlight',
            distance: 10,
            revert: true,
            handle: 'span',
            tolerance: 'pointer',
            helper: 'clone'
        });


        var tt;
        $('#page-tree-menu').unbind('mousenter').bind('mousenter', function () {
            clearTimeout(tt);
        });

        $('#page-tree-menu').unbind('mouseleave').bind('mouseleave', function () {
            var s = this;
            tt = setTimeout(function () {
                $(s).hide();
            }, 300);
        });
        this.bindDraggable();

    },
    buildNode: function (node, idx)
    {
        var self = this;


        if (!node.name)
        {
            return;
        }

        if (typeof node.appid == 'undefined' || node.appid == 0)
        {
            var div = $('<div>').attr({
                id: "tree-node-" + node.id,
                'modul': node.module,
                'modulid': node.mid,
                'rel': node.id,
                'level': parseInt(node.level)
            }).addClass('tree-node');
        }
        else
        {
            var div = $('<div>').attr({
                id: "tree-node-" + node.id,
                'modul': node.module,
                'modulid': node.mid,
                'rel': node.appid,
                'appid': node.appid,
                'level': parseInt(node.level)
            }).addClass('tree-node');
        }


        if (typeof node.locked != 'undefined' && node.locked > 0)
        {
            div.addClass('locked');
        }

        if (typeof node.isindexpage != 'undefined' && node.isindexpage > 0)
        {
            div.addClass('tree-node-indexpage');
        }


        if (node.published < 1)
        {
            div.addClass('tree-node-unpublished');
        }

        if (typeof node.draft != 'undefined' && node.draft > 0)
        {
            div.addClass('tree-node-draft');
        }

        var icon, level = parseInt(node.level);
        var nappid = node.appid;
        var napptype = node.apptype;
        var ncontentid = node.contentid;
        var ncatid = node.catid;

        if (napptype)
        {
            div.attr({
                'apptype': napptype
            });
        }

        if (ncontentid > 0)
        {
            div.attr({
                'contentid': ncontentid
            });
        }

        if (ncatid > 0)
        {
            div.attr({
                'catid': ncatid
            });
        }

        if (node.pk)
        {
            div.attr({
                'pk': node.pk
            });
        }
        if (node.table)
        {
            div.attr({
                'table': node.table
            });
        }

        if (node.extraClass) {
            div.addClass(node.extraClass);
        }




        //if(typeof node.is_folder != 'undefined' && node.is_folder != 1 )
        //{

        //}


        if (typeof node.is_folder != 'undefined' && node.is_folder == 1)
        {


            div.append(this.getIndent(level));
            var toggler = $('<a>').attr({
                href: '#'
            }).attr('id', 'm' + node.module + '_a' + parseInt(node.appid) + '_' + node.id);


            toggler.bind('click.pagestree', function (e)
            {
                e.preventDefault();

                if ($(e.target).parents('.tree-node:first')) {

                    Cookie.set('active_pages_context', 'm:' + node.module + '_a:' + parseInt(node.appid) + '_' + node.id);
                    self.selectNode($(e.target).parents('.tree-node:first'));
                    self.nodeToggle(this);

                }
            });

            toggler.append(this.getToggler());



            div.css('cursor', 'pointer').bind('click.pagestree', function (e) {
                if ($(e.target).parents('.tree-node:first')) {
                    self.selectNode(this);
                }

                // $('#m' + node.module + '_a' + node.appid + '_' + node.id).click(e);
            });





            if (typeof node.appicon != 'undefined' && node.appicon != '')
            {
                icon = node.appicon;
            }
            else
            {
                icon = node.icon ? Config.get('backendImagePath', '') + node.icon : false;
            }

            if (!icon || icon == 'undefinded') {
                if (node.type == 'site') {
                    icon = Config.get('backendImagePath', '') + 'tree/site.png';

                    if (node.id == webSite)
                    {
                        icon = Config.get('backendImagePath', '') + 'tree/site-editing.png';
                    }

                } else {
                    icon = Config.get('backendImagePath', '') + 'tree/folder-closed.png';
                }
            }

            var newIcon = this.getNodeIcon(icon, node);
            if (node.type == 'site') {
                newIcon.addClass('node-site');
            }

            toggler.append(newIcon);

            div.append(toggler);
            div.addClass('isFolder');

        }
        else
        {
            div.append(this.getIndent((level + 1), 'page'));
            div.append(this.getNodeIcon(null, node));
        }

        var span = $('<span>').addClass('node-label').append(node.name);
        div.append(span);
        if (node.lngerr == 1)
        {
            span.addClass('locerr');
        }

        div.bind('click.pagestree', function (e) {

            self.selectNode($(this));

        });


        if (typeof node.is_folder != 'undefined' && node.is_folder == 1)
        {
            var nodeReload = $('<span/>').addClass('node-reload');
            div.append(nodeReload);
            nodeReload.click(function (e) {
                if ($(e.target).parents('.tree-node:first').length) {
                    self.selectNode($(e.target).parents('.tree-node:first'));
                    self.reloadNode(e);
                }
            });
        }

        if (typeof node.actions != 'undefined')
        {
            div.data('nodeActions', node.actions);
        }

        div.data('nodeData', node);

        return div;
    },
    /**
     * 
     * @returns {undefined}
     */
    reloadNode: function ()
    {
        var self = this, selected = this.getSelectedNode();
        var rel = selected.attr('rel');
        var modul = selected.attr('modul');
        var modulid = selected.attr('modulid');
        var catid = selected.attr('catid');
        var level = selected.attr('level');

        var id = selected.attr('id').replace('tree-node-', '');
        var oid = id;



        var nodeIcon = selected.find('.tree-node-icon:first');
        var nodeToggleIcon = selected.find('.toggle-icon:first');

        selected.find('span.node-label').attr({
            oldContent: selected.find('span.node-label').html()
        }).html('laden...');

        nodeToggleIcon.removeClass('plus').addClass('minus');
        selected.addClass('tree-node-loading');

        nodeIcon.attr({
            src: nodeIcon.attr('src').replace('folder-closed', 'folder-open')
        });

        nodeIcon.attr({
            oldIcon: nodeIcon.attr('src'),
            src: Config.get('backendImagePath', '') + 'loading.gif'
        });


        var container = selected.next('.nodes-container');
        container.css({opacity: "0.5"});


        $.getJSON('admin.php?adm=dashboard&action=contenttree&modul=' + modul + '&modulid=' + modulid + '&catid=' + catid + '&level=' + level, {}, function (_data)
        {
            if (Tools.responseIsOk(_data))
            {
                container.empty();

                //container = $('#tree-node-' + oid).next('.nodes-container'); // !! select the correct container to put the nodes in :)
                var modules = _data.modules;
                if (typeof _data.modules != 'undefined')
                {
                    for (var i in modules)
                    {
                        var dat = modules[i];

                        if (typeof dat.categories != 'undefined')
                        {
                            var x;
                            for (x in dat.categories)
                            {
                                container.append(self.buildNode(dat.categories[x]));

                                if (dat.categories[x].is_folder == 1)
                                {
                                    container.append($('<div>').addClass('nodes-container').css({
                                        display: 'none'
                                    }));
                                }

                            }
                        }

                        if (typeof dat.items != 'undefined')
                        {
                            if (dat.items.length > 0)
                            {
                                var y;
                                for (y in dat.items)
                                {
                                    container.append(self.buildNode(dat.items[y]));

                                }
                            }
                            else
                            {
                                if (catid)
                                {
                                    container.append(self.getEmptyNode(container, level));
                                }
                            }
                        }
                    }


                    self.bindDraggable();

                    if (!container.find('.tree-node-icon:first'))
                    {
                        container.append(self.getEmptyNode(container, level));
                    }

                    setTimeout(function () {
                        container.show();
                        container.css({opacity: "1"});



                        setTimeout(function () {
                            Desktop.Sidepanel.enableScrollbar();
                            selected.removeClass('toggling');
                        }, 200);
                    }, 80);

                }
                else
                {
                    self.bindDraggable();

                    container.append(self.getEmptyNode(container, level));
                    container.show();
                    container.css({opacity: "1"});
                    Desktop.Sidepanel.updateScrollbar();
                    selected.removeClass('toggling');
                }
            }
            else
            {
                displayErrorDialog(data);
                selected.removeClass('toggling');
                container.css({opacity: "1"});
                Desktop.Sidepanel.updateScrollbar();
            }

            nodeIcon.attr({
                src: nodeIcon.attr('oldIcon')
            });

            selected.find('span.node-label').html(selected.find('span.node-label').attr('oldContent'));
            selected.removeClass('tree-node-loading');


        });




    },
    /**
     * 
     * @param object el
     * @returns {unresolved}
     */
    nodeToggle: function (el)
    {

        var self = this, selected = this.getSelectedNode();


        var element = $(el);

        if (selected.hasClass('toggling'))
        {
            return;
        }

        selected.addClass('toggling');

        var rel = selected.attr('rel');
        var modul = selected.attr('modul');
        var modulid = selected.attr('modulid');
        var catid = selected.attr('catid');
        var level = selected.attr('level');

        var nodeIcon = selected.find('.tree-node-icon:first');
        var nodeToggleIcon = selected.find('.toggle-icon:first');
        var container = selected.next('.nodes-container');

        if (container.is(':visible'))
        {
            Desktop.Sidepanel.disableScrollbar();

            container.hide().empty();

            container.hide('blind', {
                direction: 'vertical',
                easing: 'easeOutQuad'
            }, 'fast', function () {
                selected.removeClass('toggling');
            });





            nodeToggleIcon.removeClass('minus').addClass('plus');
			nodeIcon.removeClass('folder-open').addClass('folder-closed');


            Desktop.Sidepanel.enableScrollbar();


        }
        else
        {
            container.hide().empty();

            if (container.is(':empty'))
            {
                //Desktop.Sidepanel.disableScrollbar();

                selected.find('span.node-label').attr({
                    oldContent: selected.find('span.node-label').html()
                }).html('laden...');


                nodeToggleIcon.removeClass('plus').addClass('minus');
                selected.addClass('tree-node-loading');

                nodeIcon.attr({
                    src: nodeIcon.attr('src').replace('folder-closed', 'folder-open')
                });

				nodeIcon.removeClass('folder-closed').addClass('folder-open');

                nodeIcon.attr({
                    oldIcon: nodeIcon.attr('src'),
                    src: Config.get('backendImagePath', '') + 'loading.gif'
                });



                $.getJSON('admin.php?adm=dashboard&action=contenttree&modul=' + modul + '&modulid=' + modulid + '&catid=' + catid + '&level=' + level, {}, function (_data)
                {
                    if (Tools.responseIsOk(_data)) {
                        // container = $('#tree-node-' + oid).next('.nodes-container'); // !! select the correct container to put the nodes in :)
                        var modules = _data.modules;

                        if (typeof _data.modules != 'undefined')
                        {
                            var length = modules.length;
                            var current = 0;
                            for (var i in modules)
                            {
                                var dat = modules[i];

                                if (typeof dat.categories != 'undefined')
                                {
                                    var x;
                                    for (x in dat.categories)
                                    {
                                        container.append(self.buildNode(dat.categories[x]));

                                        if (dat.categories[x].is_folder == 1)
                                        {
                                            container.append($('<div>').addClass('nodes-container').css({
                                                display: 'none'
                                            }));
                                        }

                                    }
                                }

                                if (typeof dat.items != 'undefined')
                                {
                                    if (dat.items.length > 0)
                                    {
                                        var y;
                                        for (y in dat.items)
                                        {
                                            container.append(self.buildNode(dat.items[y]));

                                        }
                                    }
                                    else
                                    {
                                        if (catid)
                                        {
                                            // container.append(self.getEmptyNode(container, level+1));
                                        }
                                    }
                                }

                                current++;

                            }

                            if (!container.find('.tree-node-icon:first'))
                            {
                                //  container.append(self.getEmptyNode(container, level));
                            }

                            setTimeout(function () {
                                container.show();

                                setTimeout(function () {



                                    Desktop.Sidepanel.enableScrollbar();
                                    selected.removeClass('toggling');

                                    nodeIcon.attr({
                                        src: nodeIcon.attr('oldIcon')
                                    });


                                    selected.find('span.node-label').html(selected.find('span.node-label').attr('oldContent'));
                                    selected.removeClass('tree-node-loading');


                                    self.bindDraggable();

                                }, 200);
                            }, 80);

                        }
                        else
                        {
                            container.append(self.getEmptyNode(container, level + 1));
                            container.show();
                            Desktop.Sidepanel.updateScrollbar();
                            selected.removeClass('toggling');

                            nodeIcon.attr({
                                src: nodeIcon.attr('oldIcon')
                            });


                            selected.find('span.node-label').html(selected.find('span.node-label').attr('oldContent'));
                            selected.removeClass('tree-node-loading');

                            self.bindDraggable();

                        }




                    }
                    else
                    {
                        displayErrorDialog(data);
                        selected.removeClass('toggling');
                        Desktop.Sidepanel.updateScrollbar();

                        nodeIcon.attr({
                            src: nodeIcon.attr('oldIcon')
                        });


                        selected.find('span.node-label').html(selected.find('span.node-label').attr('oldContent'));
                        selected.removeClass('tree-node-loading');

                    }




                });
            }
            else
            {
                nodeToggleIcon.removeClass('plus').addClass('minus');
                nodeIcon.attr({
                    src: nodeIcon.attr('src').replace('folder-closed', 'folder-open')
                });


                selected.find('span.node-label').html(selected.find('span.node-label').attr('oldContent'));
                selected.removeClass('tree-node-loading');


                container.show('blind', {
                    direction: 'vertical',
                    easing: 'easeOutBounce'
                }, 'fast', function () {
                    $(this).prev().find('.toggling').removeClass('toggling');

                    Desktop.Sidepanel.updateScrollbar();
                });
            }

        }
    },
    getPageData: function (id) {

        $.get('admin.php?adm=menues&action=details&ajax=1&id=' + id, {}, function (data) {
            if (Tools.responseIsOk(data)) {
                Tools.createPopup(data.details, {
                    title: 'Men&uuml;punkt Details...'
                });
            }
            else
            {
                jAlert(data.msg);
            }

        });

    },
    prepareActionLoaction: function (location, catid, contentid)
    {
        location = location.replace('{catid}', parseInt(catid));
        return location.replace('{contentid}', parseInt(contentid));
    },
    /**
     * EVENTS
     */
    getSelectedNode: function ()
    {
        return $(this.el).find('.tree-node-active');
    },
    selectNode: function (el)
    {
        $(this.el).find('.tree-node-active').removeClass('tree-node-active');
        $(el).addClass('tree-node-active');

        return;

        if (treeSelectMode == 'default') {
            self.el.find('.tree-node-active').removeClass('tree-node-active');
            $(el).addClass('tree-node-active');
            //Cookie.set('active_tree_node', $(el).attr('id').replace('tree-node-', ''));

            $.getJSON('admin.php?adm=sidebar&setnode=' + $(el).attr('id').replace('tree-node-', ''), {}, function (data) {
                if (Tools.responseIsOk(data)) {

                }
            });
        } else {
            id = $(el).attr('id').replace('tree-node-', '');
            treeSelectMode(id);
        }
    },
    setupTreeMenu: function () {
        var self = this;

        // remove extra margin around separators
        $('#page-tree-menu').find('li.separator').each(function () {
            $(this).prev().css({
                marginBottom: 0
            });
            $(this).next().css({
                marginTop: 0
            });
        });

        $('#page-tree-menu a').attr('href', 'javascript:void(0)').bind('click', self.handleMenu);
    },
    contextMenu: function ()
    {
        var self = this, ul = $('#sidebar-context');
        if (ul.length == 0)
        {
            $('<ul id="sidebar-context"/>').hide().appendTo($('body'));
            ul = $('#sidebar-context');
            ul.addClass('contextmenu');


            var tpl = '<li><a href="#mod-add-cat">' + cmslang.context_add_cat + '</a></li>\n\
<li><a href="#mod-add-item">' + cmslang.context_add_item + '</a></li>\n\
<li><a href="#mod-publish">' + cmslang.context_modul_publish + '</a></li>\n\
<li><a href="#mod-edit">' + cmslang.context_modul_edit + '</a></li>\n\
<li class="separator"></li>\n\
<li><a href="#edit-cat">' + cmslang.context_edit_cat + '</a></li>\n\
<li><a href="#edit-item">' + cmslang.context_edit_item + '</a></li>\n\
<li><a href="#add-cat">' + cmslang.context_add_cat + '</a></li>\n\
<li><a href="#add-item">' + cmslang.context_add_item + '</a></li>\n\n\
<li><a href="#item-publish">' + cmslang.context_publish + '</a></li>\n\n\
<li><a href="#item-setindex"> As Index Page </a></li>\n\n\
<li class="separator"></li>\n\n\
<li><a href="#insert-dynamic-link-content">Insert Dynamic Link to this Document</a></li>\n\
<li><a href="#insert-static-link-content">Insert Static Link to this Document</a></li>\n\
<li class="separator"></li>\n\
<li><a href="#unlock">Document Unlock</a></li>\n\
<li><a href="#lock">Document Lock</a></li>';
            $(tpl).appendTo(ul);

            $('body').append(ul);
        }



        var el = $(this.el).find('#nodes');
        el.destroyContextMenu();
        el.contextMenu({
            menu: 'sidebar-context',
            onBeforeShow: function (e, menuObj, callbackContext)
            {
                $('ul.contextmenu').hide();

                var selNode = $(e.target).parents('div.tree-node:first');
                self.selectNode(selNode);


                $('#sidebar-context').hideContextItems('#item-setindex,#edit-cat,#edit-item,#add-cat,#item-publish,#insert-dynamic-link-content,#insert-static-link-content,#mod-add-cat,#mod-add-item,#mod-edit,#mod-publish');

                if (selNode.data('nodeActions'))
                {
                    var actions = selNode.data('nodeActions');

                    var show = [];
                    for (var x in actions)
                    {
                        show.push('#' + x);
                    }

                    $('#sidebar-context').showContextItems(show.join(','));
                }


                if (selNode.hasClass('locked'))
                {
                    $('#sidebar-context').hideContextItems('#lock');
                    $('#sidebar-context').showContextItems('#unlock');
                }
                else
                {
                    $('#sidebar-context').hideContextItems('#unlock');
                    $('#sidebar-context').showContextItems('#lock');
                }

                if (parseInt(selNode.attr('contentid')) > 0)
                {
                    $('#sidebar-context').showContextItems('#insert-dynamic-link-content,#insert-static-link-content');
                }

                callbackContext();
            }
        },
        function (action, _el, pos, e) {

            e.preventDefault();

            var selNode = $(_el).find('div.tree-node-active:first');
            var nodeData = selNode.data('nodeData');
            if (typeof nodeData != 'undefined') {

            }





            if (action === 'insert-dynamic-link-content')
            {

                var nodeLabel = selNode.find('.node-label').text(), ncontentid = selNode.attr('contentid');
                var ncatid = selNode.attr('catid');
                var nappid = selNode.attr('appid');
                var napptype = selNode.attr('apptype');
                var nmodul = selNode.attr('modul');

                var idofContent = 0;
                if (ncontentid)
                {
                    idofContent = ncontentid;
                }
                else if (ncatid)
                {
                    idofContent = ncatid;
                    if (typeof napptype != 'undefined')
                    {
                        napptype += 'cat';
                    }
                    else
                    {
                        nmodul += 'cat';
                    }
                }

                var tag, str;
                if (typeof napptype != 'undefined' && napptype != '')
                {
                    tag = '{' + napptype + ':' + idofContent + '}';
                    str = 'isapp="true" modul="' + napptype + '" contentid="' + idofContent + '"';
                    Doc.doInsertRichText(true, str, nodeLabel, tag);
                }
                else
                {
                    tag = '{' + nmodul + ':' + idofContent + '}';
                    str = 'modul="' + nmodul + '" contentid="' + idofContent + '"';
                    Doc.doInsertRichText(true, str, nodeLabel, tag);
                }
            }
            else if (action === 'insert-static-link-content')
            {
                var nodeLabel = selNode.find('.node-label').text(), ncontentid = selNode.attr('contentid');
                var ncatid = selNode.attr('catid');
                var nappid = selNode.attr('appid');
                var napptype = selNode.attr('apptype');
                var nmodul = selNode.attr('modul');

                var idofContent = 0;
                if (ncontentid)
                {
                    idofContent = ncontentid;
                }
                else if (ncatid)
                {
                    idofContent = ncatid;
                    if (typeof napptype != 'undefined')
                    {
                        napptype += 'cat';
                    }
                    else
                    {
                        nmodul += 'cat';
                    }
                }

                var tag, str;
                if (typeof napptype != 'undefined' && napptype != '')
                {
                    tag = '{' + napptype + ':' + idofContent + '}';
                    str = 'isapp="true" modul="' + napptype + '" contentid="' + idofContent + '" static="true"';
                    Doc.doInsertRichText(true, str, nodeLabel, tag);
                }
                else
                {
                    tag = '{' + nmodul + ':' + idofContent + '}';
                    str = 'modul="' + nmodul + '" contentid="' + idofContent + '" static="true"';
                    Doc.doInsertRichText(true, str, nodeLabel, tag, true);
                }
            }
            else if (action === 'unlock' || action === 'lock')
            {
                if (nodeData.actions === null) {
                    console.log('No node data (actions) for ' + action);
                    return;
                }



                var title = selNode.find('.node-label').text();
                var ncontentid = selNode.attr('contentid');
                var ncatid = selNode.attr('catid');
                var nmodul = selNode.attr('modul');
                var idofContent = 0, isContent = false;


                var table, pk;
                if (typeof nodeData.actions != 'undefined' && typeof nodeData.actions.lockunlock_data != 'undefined') {
                    if (typeof nodeData.actions.lockunlock_data.table != 'undefined') {
                        table = nodeData.actions.lockunlock_data.table;
                    }

                    if (typeof nodeData.actions.lockunlock_data.pk != 'undefined') {
                        pk = nodeData.actions.lockunlock_data.pk;
                    }
                }



                if (selNode.attr('contentid') > 0)
                {
                    idofContent = ncontentid;
                    isContent = true;
                }
                else if (selNode.attr('catid'))
                {
                    idofContent = ncatid;
                }

                var lockaction = '', editlocation = '';
                if (selNode.data('nodeActions'))
                {
                    var actions = selNode.data('nodeActions');
                    for (var x in actions)
                    {
                        if (x != 'lockunlock_data') {
                            if (action == x)
                            {
                                lockaction = actions[x];
                            }

                            if (x == 'edit-cat' && ncatid && !isContent)
                            {
                                editlocation = actions[x].replace('{catid}', ncatid);
                            }

                            if (x == 'edit-item' && ncontentid && isContent)
                            {
                                editlocation = actions[x].replace('{contentid}', ncontentid);
                            }
                        }
                    }
                }



                if (idofContent && nmodul)
                {

                    var nodeIcon = $('img.tree-node-icon:first', selNode);


                    if (action === 'unlock')
                    {

                        nodeIcon.attr({
                            oldIcon: nodeIcon.attr('src'),
                            src: Config.get('backendImagePath', '') + 'loading.gif'
                        });


                        Doc.unlock(idofContent, nmodul, lockaction, pk, table, function () {
                            nodeIcon.attr('src', nodeIcon.attr('oldIcon').replace('-locked', ''));
                            selNode.removeClass('locked');
                        });
                    }
                    else if (action === 'lock')
                    {
                        nodeIcon.attr({
                            oldIcon: nodeIcon.attr('src'),
                            src: Config.get('backendImagePath', '') + 'loading.gif'
                        });


                        Doc.lock(idofContent, nmodul, lockaction, pk, table, title, editlocation, function () {
                            nodeIcon.attr('src', nodeIcon.attr('oldIcon').replace('.', '-locked.'));
                            selNode.addClass('locked');
                        });
                    }
                }
            }
            else
            {
                if (selNode.data('nodeActions'))
                {

                    var nodeIcon = $('img.tree-node-icon:first', selNode);

                    var actions = selNode.data('nodeActions');
                    for (var x in actions)
                    {
                        if (x != 'lockunlock_data' && action == x)
                        {
                            var url = 'admin.php?' + self.prepareActionLoaction(actions[x], selNode.attr('catid'), selNode.attr('contentid'));
                            var nodeLabel = selNode.find('span.node-label:first').text();

                            if (action != 'item-publish' && action != 'insert-richtext' && action != 'mod-publish' && action != 'item-setindex')
                            {
                                openTab({url: url, obj: selNode, label: (nodeLabel ? nodeLabel : action), isTreeNode: true});
                            }
                            else if (action == 'insert-richtext')
                            {

                                // Doc.doInsertRichText(true, (nodeLabel ? nodeLabel : action));
                            }
                            else if (action == 'item-publish')
                            {
                                nodeIcon.attr({
                                    oldIcon: nodeIcon.attr('src'),
                                    src: Config.get('backendImagePath', '') + 'loading.gif'
                                });


                                self.callAjax(url, selNode, nodeIcon);
                            }
                            else if (action == 'item-setindex')
                            {
                                nodeIcon.attr({
                                    oldIcon: nodeIcon.attr('src'),
                                    src: Config.get('backendImagePath', '') + 'loading.gif'
                                });

                                var indexPage = nodeIcon.parents('.nodes-container:first').find('.tree-node-indexpage');

                                $.get(url, function (_data) {
                                    if (Tools.responseIsOk(_data))
                                    {
                                        if (indexPage.length = 1)
                                        {
                                            var ondat = indexPage.data('nodeData');
                                            ondat.isindexpage = 0;
                                            indexPage.data('nodeData', ondat);

                                            indexPage.removeClass('tree-node-indexpage');

                                            var nicon = self.getNodeIcon(null, ondat);
                                            indexPage.find('.tree-node-icon:first').replaceWith(nicon);
                                        }

                                        var ndat = selNode.data('nodeData');
                                        if (ndat)
                                        {
                                            ndat.isindexpage = 1;
                                            selNode.data('nodeData', ondat);

                                            var newicon = self.getNodeIcon(null, ndat);
                                            nodeIcon.replaceWith(newicon);
                                            selNode.addClass('tree-node-indexpage');
                                        }

                                    }
                                });

                            }
                            else if (action == 'mod-publish')
                            {
                                nodeIcon.attr({
                                    oldIcon: nodeIcon.attr('src'),
                                    src: Config.get('backendImagePath', '') + 'loading.gif'
                                });

                                self.callAjax(url, selNode, nodeIcon);
                            }
                        }
                    }
                }
            }
        });
    },
    callAjax: function (url, selNode, nodeIcon)
    {
        $.get(url, {}, function (data) {

            nodeIcon.attr('src', nodeIcon.attr('oldIcon'));

            if (Tools.responseIsOk(data)) {
                if (selNode.hasClass('unpub') || selNode.hasClass('tree-node-unpublished'))
                {
                    selNode.removeClass('tree-node-unpublished').addClass('tree-node-published');
                    selNode.removeClass('unpub');
                }
                else
                {
                    selNode.addClass('unpub');
                    selNode.removeClass('tree-node-published').addClass('tree-node-unpublished');
                }
            }
            else
            {
                jAlert(data.msg);
            }
        }, 'json');
    },
    menu: function (e, el)
    {
        this.currentNode = $(el);

        var mleft = e.pageX - 5;
        var mtop = e.pageY - 20; // - $(window).scrollTop();

        if (($('#sidebar-context').outerHeight() + mtop) >= $(window).height())
        {
            mtop = $(window).height() - $('#tree-menu').outerHeight() - 2;
        }


        if ($(el).hasClass('tree-node-site'))
        {
            $('#site-active,#site-edit,#site-delete,#site-page-add,#site-separator,#item-publish', '#page-tree-menu').show();
            $('#page-view,#page-edit,#page-delete,#page-add,#page-separator,#page-copy,#page-move,#past-item-copy,#past-item-move,#page-publish', '#page-tree-menu').hide();

            // copy and move
            if (this.copyItem > 0)
            {
                $('#page-copy', '#page-tree-menu').hide();
                $('#past-item', '#page-tree-menu').show();
            }
            else if (this.moveItem > 0)
            {
                $('#page-move', '#page-tree-menu').hide();
                $('#past-item', '#page-tree-menu').show();
            }


            if ($(el).attr('level') > 0)
            {
                $('#mod-publish').find('div:first').hide();
            }
            else
            {
                $('#mod-publish').find('div:first').show();
            }
        }
        else
        {
            $('#site-active,#site-edit,#site-delete,#site-page-add,#site-separator,#past-item-copy,#past-item-move,#page-publish,#item-publish', '#page-tree-menu').hide();
            $('#page-view,#page-edit,#page-delete,#page-add,#page-separator,#page-copy,#page-move,#page-publish,#item-publish', '#page-tree-menu').show();

            $('#page-publish div').show();

            // publish and unpublish
            if ($(el).parents('.tree-node:first').hasClass('tree-node-unpublished'))
            {
                $('#page-publish').find('div:last').hide();
                $('#item-publish').find('div:first').hide();
            }
            else if (!$(el).parents('.tree-node:first').hasClass('tree-node-unpublished'))
            {
                $('#page-publish').find('div:first').hide();
                $('#item-publish').find('div:first').hide();
            }

            if (!$(el).parents('.tree-node:first').hasClass('tree-node-indexpage'))
            {
                $('#item-setindex').find('div:first').show();
            }
            else
            {
                $('#item-setindex').find('div:first').hide();
            }


            if ($(el).attr('level') > 0)
            {
                $('#mod-publish').find('div:first').hide();
            }
            else
            {
                $('#mod-publish').find('div:first').show();
            }


            // copy and move
            if (this.copyItem > 0)
            {
                $('#page-copy', '#page-tree-menu').hide();
                $('#past-item-copy', '#page-tree-menu').show();
            }
            else if (this.moveItem > 0)
            {
                $('#page-move', '#page-tree-menu').hide();
                $('#past-item-move', '#page-tree-menu').show();
            }
        }

        $('#sidebar-context').css({
            top: mtop,
            left: mleft
        }).show();

        e.preventDefault();
        return false;
    },
    handleMenu: function (e)
    {
        var el = this.currentNode;
        var id = el.attr('id').replace('tree-node-', ''); // Menuitem ID
        var icon = $(e.target).find('img:first').attr('src'); // Icon from contextmenu
        var label = trim(el.text()); // Menuitem label

        var action = $(e.target).parents('li:first').attr('id');

        switch (action)
        {
            case 'site-active' :
                icon = Config.get('backendImagePath', '') + 'tree/site-editing.png';
                var defaulticon = Config.get('backendImagePath', '') + 'tree/site.png';
                var nodeImg = $(currentNode).find('.tree-node-icon:first').attr('src');
                $('#side-tree-content .node-site').attr('src', defaulticon);
                $(currentNode).find('.node-site:first').attr('src', Config.get('backendImagePath', '') + 'loading.gif');

                $.get('admin.php?action=index&setpage=' + id, {}, function (data) {
                    if (Tools.responseIsOk(data)) {

                        $(currentNode).parent().find('.node-site').attr('src', icon);
                        webSite = id;
                        setFormStatusOk(data.msg);
                        menuObj.getMenu();
                        $('#copyright>span:last').html(sprintf('aktuelle Website `%s` unter Domain `%s` wird bearbeitet', data.pagetitle, data.domainname));
                        setTimeout(unnotify, 5000);
                    } else {
                        $(currentNode).find('tree-node-icon').attr('src', nodeImg);
                        jAlert(data.msg);
                    }
                });

                $('#page-tree-menu').hide();
                break;


            case 'page-view' :
                this.getPageData(id);
                break;


            case 'page-edit' :
                href = 'admin.php?adm=menues&action=edit&id=' + id;
                openTab({url: href, obj: icon, label: label});
                $('#page-tree-menu').hide();
                break;



            case 'site-page-add' :
            case 'page-add' :
                if (id == 9999999) {
                    id = 0;
                }
                href = 'admin.php?adm=menues&action=add&parent=' + id;
                openTab({url: href, obj: icon, label: label});
                $('#page-tree-menu').hide();
                break;
            case 'site-edit' :
                if (id == 9999999)
                {
                    id = 0;
                }
                href = 'admin.php?adm=menues&action=edit&id=' + id;
                openTab({url: href, obj: icon, label: label});
                $('#page-tree-menu').hide();
                break;

            case 'page-move' :
                this.moveItem = id;
                this.copyItem = 0;
                $('#page-tree-menu').hide();
                break;

            case 'page-copy' :
                this.copyItem = id;
                this.moveItem = 0;
                $('#page-tree-menu').hide();
                break;

            case 'page-publish' :

                var published = ($(currentNode).hasClass('tree-node-unpublished') ? 1 : 0);
                var nodeImg = $(currentNode).find('.tree-node-icon:first').attr('src');

                $(currentNode).find('.tree-node-icon:first').attr('src', Config.get('backendImagePath', '') + 'loading.gif');


                $.get('admin.php?adm=menues&action=publish&id=' + id + '&published=' + published, {}, function (data) {

                    $(currentNode).find('.tree-node-icon:first').attr('src', nodeImg);
                    if (Tools.responseIsOk(data)) {

                        if ($(currentNode).hasClass('tree-node-unpublished'))
                        {
                            $(currentNode).removeClass('tree-node-unpublished');
                        }
                        else
                        {
                            $(currentNode).addClass('tree-node-unpublished');
                        }

                    } else {
                        jAlert(data.msg);
                    }
                });


                $('#page-tree-menu').hide();
                break;


            case 'past-item-move':
            case 'past-item-copy':

                if (this.copyItem > 0)
                {
                    $.get('admin.php?adm=menues&action=copyitem&toid=' + id + '&itemid=' + this.copyItem, {}, function (data) {
                        if (Tools.responseIsOk(data)) {
                        } else {
                            jAlert(data.msg);
                        }
                    });
                }
                else if (this.moveItem > 0)
                {
                    $.get('admin.php?adm=menues&action=moveitem&toid=' + id + '&itemid=' + this.moveItem, {}, function (data) {
                        if (Tools.responseIsOk(data)) {
                            jAlert(data.msg);

                        } else {
                            jAlert(data.msg);
                        }
                    });

                }

                this.copyItem = 0;
                this.moveItem = 0;
                $('#page-tree-menu').hide();
                return;
                break;


            case 'page-delete' :
                var name = $('#tree-node-' + id + ' span').html();
                jConfirm(cmslang.confirm_delete_page.replace('%s', name), cmslang.confirmation_required, function (ok) {
                    if (ok) {
                        $.pagemask.show(cmslang.loading);
                        $.post(settings.base_url + 'content/delete/' + id, {}, function (data) {
                            if (Tools.responseIsOk(data)) {
                                document.location.href = settings.base_url + 'content';
                            } else {
                                displayErrorDialog(data);
                            }
                        });
                    }
                });
                break;


            default :
                jAlert('Tree action `' + action + '` not implemented yet', cmslang.alert_header);
        }
        e.preventDefault();
        return false;
    }





};