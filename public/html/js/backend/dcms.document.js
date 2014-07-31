Doc = (function () {
    return {
        runTimeoutTimer: null,
        inited: false,
        headTag: null,
        titleTag: null,
        mainContent: null,
        documentSettings: null,
        editor_onmenu: false,
        windowID: null,
        allowSidePanel: false,
        lastActiveTinyMCE: null,
        loadedDiffMirror: false,
        tinyMceConfigs: [],
        tinyMceSetup: {
            setup: function (ed) {
                var configKeyUpEvent = (typeof Config == 'object' && typeof Config.get('onTinyMCEKeyUp') === 'function' ? Config.get('onTinyMCEKeyUp') : false);
                if (typeof configKeyUpEvent === 'function')
                {
                    ed.onKeyUp.add(function (ed, e) {
                        configKeyUpEvent(ed, e);
                    });
                }
                ed.onInit.add(function (ed, e) {

                    var area = $('#' + ed.id);
                    var toolbar = $('#' + ed.id + '_external');
                    var tbpos = $('#' + ed.id).attr('toolbarpos'), editorid = ed.id, windowID = $('#' + ed.id).parents('.isWindowContainer:first').attr('id');
                    var t1, t2, t3, t4;

                    toolbar.mouseover(function () {
                        Doc.editor_onmenu = true;
                    });

                    toolbar.mouseout(function () {
                        Doc.editor_onmenu = false;
                    });


                    if (!$('#' + ed.id).hasClass('internal') || area.attr('toolbar') == 'external' || tbpos == 'external' || tbpos == 'extern')
                    {
                        //   console.log('creating external editor');

                        // $('#'+ ed.id).next().find('iframe').hide();
                        if (toolbar)
                        {
                            Doc.setTinyMceToolbar(toolbar, ed, ed.id, 'extern');
                        }

                        Doc.disableTinyMceToolbar(toolbar, e, ed.id, 'extern');

                        tinymce.dom.Event.add(ed.getWin(), 'blur', function (e) {
                            clearTimeout(t1);
                            windowID = $('#' + ed.id).parents('.isWindowContainer:first').attr('id');
                            if ($('#' + windowID).find('#' + ed.id).length) {
                                Doc.disableTinyMceToolbar(toolbar, e, ed.id, windowID, 'extern');
                                t1 = setTimeout(function () {
                                    Win.refreshContentHeight();
                                }, 10);
                            }
                        });
                        tinymce.dom.Event.add(ed.getWin(), 'focus', function (e) {
                            clearTimeout(t2);
                            windowID = $('#' + ed.id).parents('.isWindowContainer:first').attr('id');
                            if ($('#' + windowID).find('#' + ed.id).length) {
                                Doc.enableTinyMceToolbar(toolbar, e, ed.id, windowID, 'extern');
                                t2 = setTimeout(function () {
                                    Win.refreshContentHeight();
                                }, 10);
                            }

                        });

                    }

                    if ($('#' + ed.id).hasClass('internal') || $('#' + ed.id).attr('toolbar') == 'internal' || tbpos == 'internal') {
                        //  console.log('creating internal editor');

                        if (toolbar)
                        {
                            Doc.setTinyMceToolbar(toolbar, ed, ed.id, 'intern');
                        }

                        Doc.disableTinyMceToolbar(toolbar, e, ed.id, 'intern');

                        tinymce.dom.Event.add(ed.getWin(), 'blur', function (e) {
                            clearTimeout(t3);
                            windowID = $('#' + ed.id).parents('.isWindowContainer:first').attr('id');
                            if ($('#' + windowID).find('#' + ed.id).length) {
                                Doc.disableTinyMceToolbar(toolbar, e, ed.id, windowID, 'intern');
                                t3 = setTimeout(function () {
                                    Win.refreshContentHeight();
                                }, 10);
                            }
                        });
                        tinymce.dom.Event.add(ed.getWin(), 'focus', function (e) {
                            clearTimeout(t4);
                            windowID = $('#' + ed.id).parents('.isWindowContainer:first').attr('id');
                            if ($('#' + windowID).find('#' + ed.id).length) {
                                Doc.enableTinyMceToolbar(toolbar, e, ed.id, windowID, 'intern');
                                t4 = setTimeout(function () {
                                    Win.refreshContentHeight();
                                }, 10);
                            }
                        });
                    }


                });



            }
        },
        init: function ()
        {
            this.headTag = $('head');
            this.titleTag = $('title');
            this.documentSettings = $('#documentsettings-content');
            this.inited = true;
            this.windowID = Win.windowID;

            var self = this;
            $('.document-settings-toggler').attr('wid', Win.windowID);
            $('.document-settings-toggler').unbind('click.docsettings').bind('click.docsettings', function (e) {

                e.preventDefault();

                var _self = this, wid = Win.windowID;

                if (wid)
                {
                    if ($(this).hasClass('open'))
                    {
                        SidePanel.close(function () {
                            $(self.documentSettings).hide();
                            $(_self).removeClass('open');
                        });

                        if ($('#' + wid).data('formID'))
                        {
                            $('#' + wid).removeData('mo');
                        }
                    }
                    else
                    {
                        if ($('#' + wid).data('formID'))
                        {
                            $('#' + wid).data('mo', true);
                        }

                        $(self.documentSettings).show();

                        SidePanel.open(function () {
                            $(self.documentSettings).show();
                            $(_self).addClass('open');
                        });
                    }
                }
            });
        },
        lock: function (contentid, modul, action, pk, table, title, editlocation, callback)
        {
            if (modul && contentid)
            {
                $.ajax({
                    type: "POST",
                    url: 'admin.php',
                    'data': {
                        action: 'lock',
                        unlock: true,
                        modul: modul,
                        modulaction: action,
                        contentid: contentid,
                        pk: pk,
                        table: table,
                        title: title,
                        location: editlocation
                    },
                    timeout: 10000,
                    dataType: 'json',
                    cache: false,
                    async: false,
                    success: function (data)
                    {
                        if (callback) {
                            callback();
                        }
                    }
                });
            }
            else
            {
                if (callback) {
                    callback();
                }
            }
        },
        unlock: function (contentid, modul, action, pk, table, callback)
        {
            if (modul && contentid)
            {
                $.ajax({
                    type: "POST",
                    url: 'admin.php',
                    'data': {
                        action: 'unlock',
                        unlock: true,
                        modul: modul,
                        modulaction: action,
                        pk: pk,
                        table: table,
                        contentid: contentid
                    },
                    timeout: 10000,
                    dataType: 'json',
                    cache: false,
                    async: false,
                    success: function (data)
                    {
                        if (callback) {
                            callback();
                        }
                    }
                });
            }
            else
            {
                if (callback) {
                    callback();
                }
            }
        },
        unload: function (windowID)
        {

            //     Doc.Metadata.unload($('#' + windowID));

            // this.inited = false;

            if ($('.document-settings-toggler').hasClass('open'))
            {
                $('.document-settings-toggler').click();

                $('#documentmetadata' + $('#' + windowID).data('formID')).remove();

                if (!$(this.documentSettings).find('form').length)
                {
                    $('.document-settings-toggler').unbind();
                }
                else
                {
                    setTimeout(function () {

                        if ($('#' + windowID).data('formID'))
                        {
                            $('#documentmetadata' + $('#' + windowID).data('formID')).show();
                        }

                    }, 300);

                }

                $('.documentsettings-content').hide();

            }
            else
            {
                $('.document-settings-toggler').unbind();
            }


            Doc.unloadTinyMce($('#' + windowID));

            Doc.unloadAce($('#' + windowID));


            this.inited = false;
            this.windowID = null;
        },
        resetDocumentSettings: function (windowID)
        {
            if ($('#documentmetadata' + $('#' + windowID).data('formID'), $(this.documentSettings)).data('windowID') == windowID)
            {
                $('#documentmetadata' + $('#' + windowID).data('formID'), $('#documentsettings-content')).get(0).reset();



                $('#documentmetadata' + $('#' + windowID).data('formID'), $('#documentsettings-content')).find('select.inputS,input.inputR,input.inputC').each(function () {
                    if ($(this).hasClass('inputS'))
                    {
                        $(this).SelectBox('reset');
                    }
                    else if ($(this).hasClass('inputR') || $(this).hasClass('inputC'))
                    {
                        var self = $(this), name = $(this).attr('name');

                        if ($(this).attr('default') == 'on' && !$(this).prop('checked'))
                        {
                            $(this).prop('checked', true);
                            $(this).attr('checked', 'checked');
                            this.checked = true;
                        }

                        if ($(this).attr('default') == 'off' && $(this).prop('checked'))
                        {
                            $(this).prop('checked', false);
                            $(this).removeAttr('checked');
                            this.checked = false;
                        }


                        $(this).trigger('change');
                        setTimeout(function () {
                            $(self).next().triggerHandler('doReset');// trigger the Zebra_TransForm
                        }, 5);
                    }
                });
            }
        },
        diffUndo: function ()
        {
            var dv = $('#' + Win.windowID).data('dv');
            if (dv) {
                //   dv.edit.undo();
                dv.right.orig.undo();
                dv.refresh();
            }
        },
        diffRedo: function ()
        {
            var dv = $('#' + Win.windowID).data('dv');
            if (dv) {
                // dv.edit.redo();
                dv.right.orig.redo();
                dv.refresh();
            }
        },
        triggerDiffChangeTinyMCE: function (diff)
        {
            var opener = $('#' + Win.windowID).attr('opener');
            if (opener)
            {
                var id = $('#' + opener).find('.tinymce-editor:eq(0)').attr('id');
                if (id && tinyMCE)
                {
                    tinyMCE.get(id).setContent(diff.orig.getValue());
                }
            }
        },
        initDiff: function (containerID, currentvalue, targetvalue, panes)
        {
            var dv;
            panes = panes || 2;
            if (currentvalue == null || targetvalue == null)
            {
                currentvalue = currentvalue != null ? currentvalue : $('#' + containerID + '-source').get(0).innerHTML;
                targetvalue = targetvalue != null ? targetvalue : $('#' + containerID + '-target').get(0).innerHTML;
            }

            var self = this, manager = $('#' + Win.windowID).data('WindowManager');

            var target = document.getElementById(containerID);
            target.innerHTML = "";

            var srcEdit = new _dcmsSourceEditor();
            var config = srcEdit.getConfig('xml');
            config.gutters = ["CodeMirror-linenumbers"];

            if (!self.loadedDiffMirror)
            {
                Tools.loadScript('Vendor/codemirror/mode/htmlmixed/htmlmixed.js', function () {
                    Tools.loadScript('Vendor/codemirror/mode/xml/xml.js', function () {


                        self.loadedDiffMirror = true;




                        config.value = targetvalue;
                        config.origLeft = panes == 3 ? targetvalue : null;
                        config.orig = currentvalue;
                        config.onAfterChange = function (diff) {
                            self.triggerDiffChangeTinyMCE(diff);
                        };


                        var dv = new CodeMirror.MergeView(target, config);
                        /*
                         dv.edit.on("gutterClick", function(thisCM, line, gutter, clickEvent) {
                         srcEdit.codeFolding(thisCM, line, gutter, clickEvent);
                         srcEdit.codeFolding(dv.right.orig, line, gutter, clickEvent);
                         dv.refresh();
                         });
                         dv.right.orig.on("gutterClick", function(thisCM, line, gutter, clickEvent) {
                         srcEdit.codeFolding(thisCM, line, gutter, clickEvent);
                         srcEdit.codeFolding(dv.edit, line, gutter, clickEvent);
                         dv.refresh();
                         });
                         */
                        $('#' + Win.windowID).data('dv', dv);

                        $('#merge-view .CodeMirror-merge-pane:first').prepend('<div class="source-label">' + cmslang.versionTarget + '</div>');
                        $('#merge-view .CodeMirror-merge-pane:last').prepend('<div class="target-label">' + cmslang.versionSource + '</div>');



                        manager.settings.onResize = function (event, ui, wm, uiContent)
                        {
                            $('#merge-view').css({width: uiContent.width - 40, height: uiContent.height - 40});
                            if (dv)
                                dv.refresh();

                        };
                        manager.settings.onResizeStop = function (event, ui, wm, uiContent)
                        {
                            $('#merge-view').css({width: uiContent.width - 40, height: uiContent.height - 40});
                            if (dv)
                                dv.refresh();
                        };
                    });
                });
            }
            else
            {


                config.value = targetvalue;
                config.origLeft = panes == 3 ? targetvalue : null;
                config.orig = currentvalue;
                config.onAfterChange = function (diff) {
                    self.triggerDiffChangeTinyMCE(diff);
                };

                var dv = new CodeMirror.MergeView(target, config);
                $('#' + Win.windowID).data('dv', dv);
                /*
                 dv.edit.on("gutterClick", function(thisCM, line, gutter, clickEvent) {
                 srcEdit.codeFolding(thisCM, line, gutter, clickEvent);
                 srcEdit.codeFolding(dv.right.orig, line, gutter, clickEvent);
                 dv.refresh();
                 });
                 dv.right.orig.on("gutterClick", function(thisCM, line, gutter, clickEvent) {
                 srcEdit.codeFolding(thisCM, line, gutter, clickEvent);
                 srcEdit.codeFolding(dv.edit, line, gutter, clickEvent);
                 dv.refresh();
                 });*/

                $('#merge-view .CodeMirror-merge-pane:first').prepend('<div class="source-label">' + cmslang.versionTarget + '</div>');
                $('#merge-view .CodeMirror-merge-pane:last').prepend('<div class="target-label">' + cmslang.versionSource + '</div>');



                manager.settings.onResize = function (event, ui, wm, uiContent)
                {
                    $('#merge-view').css({width: uiContent.width - 40, height: uiContent.height - 40});
                    if (dv)
                        dv.refresh();
                };
                manager.settings.onResizeStop = function (event, ui, wm, uiContent)
                {
                    $('#merge-view').css({width: uiContent.width - 40, height: uiContent.height - 40});
                    if (dv)
                        dv.refresh();
                };
            }


        },
        setDocumentSettings: function (windowID)
        {

            if (!$('#document-metadata').length)
            {

                SidePanel.hide();
                $('.document-settings-toggler').hide();
                return false;
            }


            if (!this.inited)
            {
                this.init();
            }


            var self = this;
            setTimeout(function () {

                var formID = $('#' + Win.windowID).data('formID');

                if (formID)
                {
                    $('.document-settings-toggler').show();

                    if (!$('#documentmetadata' + formID, $(self.documentSettings)).length)
                    {
                        //self.documentSettings.empty();
                        $(self.documentSettings).find('form:visible').hide();

                        $('#document-metadata', $('#' + Win.windowID)).addClass('wrapped').appendTo(self.documentSettings);

                        $('.wrapped', $(self.documentSettings)).wrap($('<form/>').attr('id', 'documentmetadata' + formID).show());
                        $('#documentmetadata' + formID, $(self.documentSettings))
                                .data('windowID', Win.windowID).data('realFormID', formID).data('formConfig', $('#' + Win.windowID).data('formConfig'));

                        $('#' + Win.windowID).attr('meta', true);
                        $('.wrapped').removeClass('wrapped');
                        if (SidePanel) {
                            SidePanel.show();
                        }
                        self.allowSidePanel = true;
                    }

                    $('#document-metadata', $('#' + Win.windowID)).remove();

                    Form.registerLiveEventsForMetadata(formID);

                    $(self.documentSettings).show();
                    $('.documentsettings').show();
                }
            }, 50);

        },
        storeMetadata: function (windowID)
        {
            var formID = $('#' + windowID).data('formID');

            if (formID)
            {
                $('form:visible', $(this.documentSettings)).hide();
            }
        },
        restoreMetadata: function (windowID)
        {
            var self = this, formID = $('#' + windowID).data('formID');

            if (formID && $('#documentmetadata' + formID, $(self.documentSettings)).length)
            {

                $('.document-settings-toggler').show();
                $('#documentmetadata' + formID, $(self.documentSettings)).show();


                var open = $('#' + windowID).data('mo');

                $(this.documentSettings).show();
                if (open && !SidePanel.isVisible())
                {
                    SidePanel.open(function () {
                        $('#documentmetadata' + formID, $('#documentsettings-content')).show();
                        $('.document-settings-toggler').addClass('open');
                        $('form', $(self.documentSettings)).show();
                    });
                }

                // Win.prepareWindow( formID );
                //$('#' + windowID ).data('mo', true);
            }
            else
            {
                if (SidePanel.isVisible())
                {
                    SidePanel.close(function () {
                        $(self.documentSettings).hide();
                        $('.document-settings-toggler').removeClass('open').hide();
                        $('form:visible', $(self.documentSettings)).hide();
                    });

                }
            }
        },
        unloadAce: function (win) {
            var editors = win.find('textarea.sourceEdit');
            if (editors.length) {
                editors.each(function () {
                    if ($(this).data('ace')) {
                        $(this).data('ace').destroy();
                    }
                });
            }
        },
        loadTinyMceConfig: function (win, callback, reloadConfig)
        {
            // reset all other configs
            if (typeof window.tinymceConfig != 'undefined' && reloadConfig === true)
            {
                window.tinymceConfig = {};
            }

            if (typeof tinyMCE == 'undefined')
            {
                $.get('../Vendor/tinymce/tiny_mce_src.js', function ()
                {
                    $.get('admin.php?tinymce=getconfig', function ()
                    {
                        if (typeof callback === 'function')
                        {
                            callback(win);
                        }
                    }, 'script');
                }, 'script');
            }
            else
            {
                if (reloadConfig || (typeof window.tinymceConfig == 'undefined' || typeof window.tinymceConfig.skin == 'undefined'))
                {
                    $.get('admin.php?tinymce=getconfig', function ()
                    {
                        if (typeof callback === 'function')
                        {
                            callback(win);
                        }
                    }, 'script');
                }
                else
                {
                    if (typeof callback === 'function')
                    {
                        callback(win);
                    }
                }
            }
        },
        loadTinyMce: function (win)
        {
            var self = this;

            if (typeof win != 'undefined') {

                Win.tinyMCELoaded = false;

                //$('.table-mm-container', $(win)).jScrollPaneRemove();
                //      console.log('editors in a window');

                this.tinyMceEditors = $(win).find('.tinymce-editor').length;

                $(win).find('.tinymce-editor').each(function () {
                    if (!$(this).hasClass('loaded'))
                    {
                        var $tarea = this;
                        var tbarpos = $(this).attr('toolbarpos');
                        var tbar = $(this).attr('toolbar');
                        var area = $(this);
                        var id = $(this).attr('id');

                        if (!id)
                        {
                            id = 'editor-' + (new Date().getTime());
                            $(this).attr('id', id);
                        }
                        Debug.info('adding TinyMce ' + area.attr('name'));

                        var cfgHash = $(this).attr('name') + id + (typeof tbar === 'string' ? tbar : '') + (typeof tbarpos === 'string' ? tbarpos : '');

                        if (typeof self.tinyMceConfigs[cfgHash] == 'undefined') {
                            $.get('admin.php?tinymce=getconfig&toolbar=' + (typeof tbar === 'string' ? tbar : '') + '&toolbarpos=' + (typeof tbarpos === 'string' ? tbarpos : ''), function ()
                            {
                                if (typeof window.tinymceConfig != 'undefined') {

                                    window.tinymceConfig.height = parseInt($(area).height(), 10); //parseInt($(area).height()) / 12;

                                    $(area).attr('win', $(win).attr('id')).addClass('loaded');


                                    var baseToolbar = window.tinymceConfig.theme_advanced_toolbar_location;
                                    // window.tinymceConfig.mode = "exact";
                                    window.tinymceConfig.setup = Doc.tinyMceSetup.setup;

                                    if (tbarpos == 'internal' || tbarpos == 'intern' || area.hasClass('internal') || area.attr('toolbar') === 'internal')
                                    {
                                        $((id ? '#' + id : $tarea)).attr('toolbar', 'internal');
                                        window.tinymceConfig.theme_advanced_toolbar_location = 'external';

                                        // delete window.tinymceConfig.theme_advanced_toolbar_location;
                                    }

                                    $((id ? '#' + id : $tarea)).tinymce(window.tinymceConfig);

                                    //  self.tinyMceConfigs[cfgHash] = window.tinymceConfig;

                                    window.tinymceConfig.theme_advanced_toolbar_location = baseToolbar;
                                }

                            }, 'script');

                        }
                        else {

                            window.tinymceConfig = self.tinyMceConfigs[cfgHash];

                            window.tinymceConfig.height = parseInt($(area).height(), 10); //parseInt($(area).height()) / 12;
                            $(area).attr('win', $(win).attr('id')).addClass('loaded');

                            var baseToolbar = window.tinymceConfig.theme_advanced_toolbar_location;

                            if (tbarpos == 'internal' || tbarpos == 'intern' || area.hasClass('internal') || area.attr('toolbar') === 'internal')
                            {
                                $(area).attr('toolbar', 'internal');
                                window.tinymceConfig.theme_advanced_toolbar_location = 'external';
                                // delete window.tinymceConfig.theme_advanced_toolbar_location;
                            }

                            $((id ? '#' + id : $tarea)).tinymce(window.tinymceConfig);

                            self.tinyMceConfigs[cfgHash] = window.tinymceConfig;
                            window.tinymceConfig.theme_advanced_toolbar_location = baseToolbar;
                        }

                        if (tbarpos == 'internal' || tbarpos == 'intern' || area.hasClass('internal') || area.attr('toolbar') === 'internal')
                        {
                            area.next('span[role="application"]').addClass('intern').show();
                        }

                    }
                });

                Win.tinyMCELoaded = true;

                if ($(win).find('.mceExternalToolbar').length > 1)
                {
                    setTimeout(function() {
                        "use strict";
                        $(win).find('.mceExternalToolbar:not(.internal)').hide();
                        $(win).find('.mceExternalToolbar:first').show();
                    }, 500);

                }
            }
        },
        unloadTinyMce: function (inobject)
        {
            //       Debug.log('Unload TinyMce...');
            var self = this;

            if ($(inobject).find('.tinymce-editor').length) {
                var editors = tinymce.editors;
                $(inobject).find('.tinymce-editor').each(function () {

                    for (var i = 0; i < editors.length; ++i) {
                        if (editors[i] && editors[i].id && editors[i].id == $(this).attr('id')) {
                            //$(this).tinymce().destroy();
                            if (tinymce) {
                                tinymce.execCommand('mceToggleEditor', false, $(this).attr('id'));
                            }
                            break;
                        }
                    }
                    $(this).removeClass('loaded').show(); //.removeData();
                    $(this).next('span[role="application"]').hide();
                });
            }

            $(inobject).find('.mceExternalToolbar').hide();
            $(inobject).find('.tinyMCE-Toolbar').empty().append($('<div/>').attr('id', 'disabler').hide()).hide();
        },
        removeTinyMceToolbar: function ()
        {
            this.unloadTinyMce($('#' + Win.windowID));
        },
        loadedMcs: 0,
        setTinyMceToolbar: function (tinymceToolbar, ed, editorid, pos)
        {
            var self = this, tb, winID = Win.windowID, disabler = $('<div/>').attr('id', 'disabler');

            if (pos == 'extern') {
                if ($('#' + winID).find('.tinyMCE-Toolbar').length == 0)
                {
                    var _toolbar = $('<div/>').addClass('tinyMCE-Toolbar');
                    Win.wm.Toolbar.append(_toolbar);
                    _toolbar = null;

                    tb = Win.wm.$el.find('.tinyMCE-Toolbar');
                    tb.empty().append(disabler.hide());
                    tb.show();
                }
                else
                {
                    tb = Win.wm.$el.find('.tinyMCE-Toolbar');
                    tb.show();
                }

                if ($('#' + winID).length == 1)
                {
                    // var externalToolbar = false;
                    if (this.tinyMceEditors > 1 && Win.wm.$el.find('.mceExternalToolbar').length > 1)
                    {
                        Win.wm.$el.find('.mceExternalToolbar').hide();
                        // externalToolbar = true;
                    }

                    if (this.tinyMceEditors > 1 && $('#' + winID).find('.mceExternalToolbar').length > 1)
                    {
                        $('#' + winID).find('.mceExternalToolbar').hide();
                        // externalToolbar = true;
                    }

                    // if (externalToolbar)
                    //  {
                    tb.append($(tinymceToolbar).addClass('mceEditor dcmsSkin').show()).show();
                    tb.find('td.mceToolbar').removeClass('mceToolbar');
                    // }

                    $('#' + editorid + '_external').show();
                    $('#' + editorid).next().find('iframe:first').show();

                    setTimeout(function () {
                        Win.wm.$el.trigger('winContentChange');
                    }, 1500);
                    self.loadedMcs++;
                }

            }
            else {
                if (!$('#' + editorid).prev().is('.tinyMCE-Toolbar'))
                {
                    var _toolbar = $('<div/>').addClass('tinyMCE-Toolbar internal');
                    _toolbar.insertBefore($('#' + editorid));
                    tb = $('#' + editorid).prev();
                    tb.empty().append(disabler.hide());
                    tb.show();
                }
                else
                {
                    tb = $('#' + editorid).prev();
                    tb.show();
                }

                if ($('#' + winID).length == 1)
                {
                    // if (externalToolbar)
                    //  {
                    tb.append($(tinymceToolbar).addClass('mceEditor dcmsSkin').show()).show();
                    tb.find('td.mceToolbar').removeClass('mceToolbar');
                    // }

                    $('#' + editorid + '_external').show();
                    $('#' + editorid).next().find('iframe:first').show();

                    setTimeout(function () {
                        Win.wm.$el.trigger('winContentChange');
                    }, 1500);
                    self.loadedMcs++;
                }
            }



        },
        enableTinyMceToolbar: function (tinymceToolbar, e, editorid, windowID, pos)
        {
            var winID = $('#' + editorid).parents('.isWindowContainer').attr('id');

            if (windowID !== winID) {
                winID = windowID;
            }

            if (winID && $('#' + winID).length == 1)
            {
                if (!$('#' + editorid).prev().is('.tinyMCE-Toolbar')) {
                    var tb = $('#' + winID).find('.tinyMCE-Toolbar');

                    $('#' + winID).find('table.mceLayout').removeClass('focused');

                    if ($('#' + winID).find('.mceExternalToolbar').length > 1)
                    {
                        $('#' + winID).find('.mceExternalToolbar').hide();

                        $('#' + editorid + '_external').removeClass('disabled').show();
                    }
                    else
                    {
                        $('#' + editorid + '_external').removeClass('disabled').show();
                    }

                    if (!tb.find('.mceExternalToolbar:visible').length)
                    {
                        tb.find('.mceExternalToolbar:first').show();
                    }


                    this.lastActiveTinyMCE = editorid;

                    $('#' + editorid).css({display: ''});

                    $('#' + editorid + '_tbl').addClass('focused');

                    tb.removeClass('disabled');
                    tb.find('#disabler').hide();
                    tb.find('.mceExternalToolbar').removeClass('disabled');
                }
                else {
                    var tb = $('#' + editorid).prev();
                    $('#' + editorid).parent().find('table.mceLayout').removeClass('focused');

                    if (!tb.find('.mceExternalToolbar:visible').length)
                    {
                        tb.find('.mceExternalToolbar:first').show();
                    }

                    $('#' + editorid).css({display: ''});

                    $('#' + editorid + '_tbl').addClass('focused');

                    tb.removeClass('disabled');
                    tb.find('#disabler').hide();
                    tb.find('.mceExternalToolbar').removeClass('disabled');

                }
            }
            else {
                //  console.log('enableTinyMceToolbar winID not found');
            }

        },
        disableTinyMceToolbar: function (tinymceToolbar, e, editorid, windowID)
        {
            if (this.editor_onmenu)
            {
                return;
            }

            var winID = $('#' + editorid).parents('.isWindowContainer').attr('id');

            if (windowID !== winID) {
                winID = windowID;
            }

            if (winID && $('#' + winID).length == 1) {
                var tb = $('#' + winID).find('.tinyMCE-Toolbar');
                tb.addClass('disabled').find('#disabler').addClass('disabled').show();
                $('#' + editorid + '_tbl').removeClass('focused');

                if ($('#' + winID).find('.mceExternalToolbar').length > 1) {
                    $('#' + editorid + '_external').addClass('disabled').hide();
                }
                else {
                    $('#' + editorid + '_external').addClass('disabled').show();
                }

                tb.show();

                if ($('#' + winID).find('.mceExternalToolbar').length > 1 && !$('#' + winID).find('.mceExternalToolbar:visible').length)
                {
                    $('#' + winID).find('.mceExternalToolbar:first').show();
                }
            }
            else {
                //   console.log('disableTinyMceToolbar winID not found');
            }
        },
        doInsertRichText: function (noedit, attributes, label, tag, isStatic)
        {
            if (!$('#' + Win.windowID).find('.tinymce-editor').length && this.lastActiveTinyMCE !== null && $('#' + this.lastActiveTinyMCE).length)
            {
                $('#' + this.lastActiveTinyMCE).focus();
            }

            if ($('#' + Win.windowID).find('.tinymce-editor').length > 0)
            {
                if (tinyMCE.activeEditor != null)
                {
                    var sel = tinyMCE.activeEditor.selection.getContent();
                    var str = '<span class="dcmsCTag' + (noedit ? ' mceNonEditable' : '') + '">';

                    if (sel)
                    {
                        str += '<a href="#" ' + attributes + ' notitle="true">' + sel + '</a></span>&nbsp;';


                        //     tinyMCE.activeEditor.selection.setContent(str + '<a href="#" ' + attributes + ' notitle="true">' + sel + '</a></span> ' + ' &nbsp;');
                    }
                    else
                    {
                        str += '<a href="#" ' + attributes + '>' + label + '</a></span>&nbsp;';

                        //   tinyMCE.activeEditor.selection.setContent(str + '<a href="#" ' + attributes + '>' + label + '</a></span> ' + ' &nbsp;');
                    }



                    if (isStatic === true)
                    {
                        $.post('admin.php', {adm: 'dashboard', action: 'getstaticlink', linkcode: str}, function (data) {
                            if (Tools.responseIsOk(data))
                            {
                                tinyMCE.activeEditor.selection.setContent(data.link);
                            }
                        }, 'json');
                    }
                    else
                    {
                        tinyMCE.activeEditor.selection.setContent(str);
                    }
                }
            }
        }
    };
})(window);