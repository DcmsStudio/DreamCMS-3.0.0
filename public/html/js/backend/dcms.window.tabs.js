var formTabIndex = 1;
ns('Win');
Win = (function () {
    return {
        windowID: null,
        maskedWindows: [],
        openerWindow: null,
        _hasTinyMCE: false,
        wm: false,
        setActive: function (winID)
        {
            if (typeof winID == 'string')
            {
                this.windowID = winID.replace('#', '');
            }
            else if (typeof winID == 'object')
            {
                this.windowID = $(winID).attr('id');
            }

            var wm = $('#' + this.windowID).data('WindowManager');

            if (wm)
            {
                Application.setActiveUrl(wm.get('Url'));
                this.wm = wm;
                wm = null;
            }
            else
            {
                this.wm = false;
            }


            DesktopConsole.resetZindex();
            Desktop.Taskbar.updateTaskbarButtons();

            if ($('#' + this.windowID).find('.tinymce-editor').length > 0)
            {
                if (this.wm) {
                    this.wm.set('hasTinyMCE', true);
                }

                this._hasTinyMCE = true;
                // this.wm.settings.enableContentScrollbar = false;
            }
            else
            {
                if (this.wm)
                {
                    this.wm.set('hasTinyMCE', false);
                }
            }


        },
        hasSidePanel: function ()
        {
            return $('#document-metadata').length > 0 ? true : false;
        },
        setWindowPanelContent: function (side, content)
        {
            if (this.wm && typeof this.wm.id === 'string')
            {
                var panel;

                switch (side)
                {
                    case 'right':
                        panel = this.wm.WindowPanelRight;
                        break;
                    case 'left':
                        panel = this.wm.WindowPanelRight;
                        break;
                }

                panel.find('.window-panel-content:first').empty().append(content);
                Tools.scrollBar(panel.find('.window-panel-content:first'));
            }
        },
        toggleWindowPanel: function (side)
        {
            if (this.wm && typeof this.wm.id === 'string')
            {
                this.wm.toggleWindowPane(side);
            }
        },
        close: function (windowID)
        {
            if (this.wm && this.wm.id == windowID)
            {
                this.wm.close();
                return;
            }

            if (typeof windowID == 'undefined')
            {
                windowID = this.windowID;
            }
            else if (typeof windowID == 'object')
            {
                windowID = $(windowID).attr('id');
            }
            else if (typeof winID == 'string')
            {
                windowID = windowID.replace('#', '');
            }

            $('#' + windowID).data('WindowManager').close()
        },
        getWindowTitle: function (windowID)
        {
            if (this.wm && this.wm.id == windowID)
            {
                return this.wm.getTitle();
            }

            if (typeof windowID == 'undefined')
            {
                windowID = this.windowID;
            }
            else if (typeof windowID == 'object')
            {
                windowID = $(windowID).attr('id');
            }
            else if (typeof winID == 'string')
            {
                windowID = windowID.replace('#', '');
            }

            return $('#' + windowID).data('WindowManager').getTitle();
        },
        refreshOpenerWindow: function (id, callback)
        {
            if (id != null && typeof id != 'undefined')
            {
                var currentWin = this.windowID;

                this.focusOpenerWindow(id);

                var self = this, gridData = $('#' + id).data('windowGrid');
                if (typeof gridData == 'object')
                {
                    gridData.refresh(callback);
                }
                else
                {
                    if ($('#' + id).data('WindowManager'))
                    {
                        $('#' + id).data('WindowManager').ReloadWindow(function () {
                            self.focusOpenerWindow(currentWin);

                            if (callback) {
                                callback();
                            }
                        });

                        return;
                    }
                }

                this.focusOpenerWindow(currentWin);
            }
        },
        focusOpenerWindow: function (id)
        {
            if (id)
            {
                // focus the opener window
                Application.currentWindowID = id;
                $('#' + id).trigger('click');
            }
        },
        refreshContentHeight: function (winID) {
            if (!winID)
            {
                winID = this.windowID;
            }
            var windata = $('#' + winID).data('WindowManager');
            if ($('#' + winID).length && windata)
            {
                windata.updateContentHeight();

                var oldTriggerState = windata.stopWinContentChangeTrigger;
                windata.stopWinContentChangeTrigger = false;
                $('#' + winID).trigger('winContentChange');
                windata.stopWinContentChangeTrigger = oldTriggerState;

            }

        },
        redrawWindowHeight: function (winID, refreshScroll)
        {
            if (!winID)
            {
                winID = this.windowID;
            }


            var windata = $('#' + winID).data('WindowManager');

            if ($('#' + winID).length && windata)
            {
                windata.updateContentHeight();
                $('#' + winID).trigger('winContentChange');

                if (refreshScroll === true && !Win.hasTinyMCE())
                {
                    var oldTriggerState = windata.stopWinContentChangeTrigger;
                    windata.stopWinContentChangeTrigger = false;

                    $('#' + winID).trigger('winContentChange');

                    windata.stopWinContentChangeTrigger = oldTriggerState;

                    return;

                }
            }

        },
        refreshScrollbars: function (winID) {
            if (!winID)
            {
                winID = this.windowID;
            }
            var windata = $('#' + winID).data('WindowManager');

            if (windata)
            {
                var oldTriggerState = windata.stopWinContentChangeTrigger;
                $('#' + winID).trigger('winContentChange');
                windata.stopWinContentChangeTrigger = oldTriggerState;
            }
        },
        hasTinyMCE: function ()
        {
            if ($('.tinymce-editor', $('#' + this.windowID)).length > 0)
            {
                return true;
            }

            return false;
        },
        tickInterval: null,
        isPrepareWindow: false,
        setDocumentVersioning: function (winID)
        {
            $('#' + winID).find('#VersioningForm').each(function () {
                var winObj = $('#' + winID);
                winObj.find('#changeVersion').disableButton();
                $(this).find('select').unbind();

                var currentVersion = $(this).find('select').val();

                $(this).find('select').on('change', function (e) {
                    $(this).parents('div.isWindowContainer').find('#changeVersion').enableButton();
                });




                $(this).find('#changeVersion').unbind().on('click', function (e) {

                    var params = $(this).parents('form:first').serialize();

                    $.post('admin.php', params, function (data) {
                        if (Tools.responseIsOk(data))
                        {
                            if (winObj && winObj.data('WindowManager'))
                            {
                                winObj.data('WindowManager').ReloadWindow();
                            }
                        }
                        else
                        {
                            if (data.msg)
                            {
                                Notifier.display('error', data.msg);
                            }
                        }
                    });
                });


                $(this).find('#diffVersion').unbind().on('click', function (e) {
                    var selectedVersion = $(this).parents('form:first').find('select').val();
                    var modul = $(this).parents('form:first').find('input[name="adm"]').val();
                    var id = $(this).parents('form:first').find('input[name="id"]').val();
                    if (id)
                    {
                        $.post('admin.php', {adm: 'dashboard', action: 'diff', modul: modul, id: id, sourceversion: currentVersion, targetversion: selectedVersion}, function (data) {
                            if (Tools.responseIsOk(data))
                            {
                                Tools.createPopup(data.maincontent, {
                                    WindowTitle: data.pageCurrentTitle ? data.pageCurrentTitle : 'Merge',
                                    WindowMaximize: true,
                                    WindowMinimize: true,
                                    WindowResizeable: true,
                                    WindowToolbar: data.toolbar,
                                    app: modul
                                });
                            }
                            else
                            {
                                if (data.msg)
                                {
                                    Notifier.display('error', data.msg);
                                }
                            }
                        });
                    }

                });

            });
        },
        prepareWindow: function (callback)
        {
            var containTinyMce = false, self = this;


            if (!this.wm)
            {
                // Debug.log('skip prepareWindow');
                this.tickInterval = setTimeout(function () {
                    self.prepareWindow(callback, true);
                }, 5);
            }
            else
            {

                clearTimeout(this.tickTimeout);

                this.isPrepareWindow = true;

                var winID = this.windowID;

                // console.log('prepareWindow initTabs');
                // Win.Tabs.initTabs(winID);

                $('#' + winID).find('fieldset').each(function () {

                    if ( $(this).find('img.infoicon').length )
                    {
                        $(this).find('legend').each(function () {
                           var legend = $(this);
                           if (legend.next().is('img.infoicon') || legend.prev().is('img.infoicon')) {
                               legend.addClass('left-20');
                           } 
                        });
                    }
                    
                });


                Doc.init(winID);

                // console.log('prepareWindow redrawWindowHeight');
                // this.redrawWindowHeight(this.windowID, false);

                // console.log('prepareWindow setDocumentSettings');
                Doc.setDocumentSettings(winID);

                // console.log('prepareWindow rebuildTooltips');
                Desktop.Tools.rebuildTooltips(winID);

                if ($().tabby)
                {
                    $('textarea', $('#' + winID)).tabby();
                }

                $('a.doTab', $('#' + winID)).each(function () {

                    $(this).unbind('click').bind('click', function (e)
                    {
                        var href = $(this).attr('href');
                        var rel = $(this).attr('rel');
                        var url = '';

                        if (typeof href != 'undefined' && href.match(/(^admin\.php|\/admin\.php\?)/))
                        {
                            url = href;
                        }
                        if (url == '' && typeof rel != 'undefined' && rel.match(/(^admin\.php|\/admin\.php\?)/))
                        {
                            url = rel;
                        }

                        e.preventDefault();

                        var l = $(this).text().trim();
                        if (l != '')
                        {
                            var label = l;
                        }

                        if (url != '')
                        {
                            Desktop.loadTab(e, {url: url, obj: $(this), label: label, useOpener: true});
                        }

                        return false;
                    });


                });


                this.tinyMCELoaded = false;

                // init TinyMCE if exist
                if (this._hasTinyMCE)
                {
                    Doc.loadTinyMce($('#' + winID));
                }
                else
                {
                    this.tinyMCELoaded = true;
                }

                //    $('.table-mm-container', $('#' + self.windowID)).addClass('prepared');

                self.runAfterTinyMCELoad(callback, winID);
            }
        },
        runAfterTinyMCELoad: function (callback, winID)
        {
            var self = this;

            if (!this.tinyMCELoaded)
            {
                this.tickInterval = setTimeout(function () {
                    self.runAfterTinyMCELoad(callback, winID);
                }, 20);
            }
            else
            {
                clearTimeout(this.tickInterval);
                this.tinyMCELoaded = true;

                // console.log('prepareWindow redrawWindowHeight last');
                $('#' + winID).unmask();

                if ($('#' + winID).data('WindowManager'))
                {
                    $('#' + winID).data('WindowManager').set('complete', true);
                }

                this.isPrepareWindow = false;

                if (typeof callback == 'function')
                {
                    callback();
                }

            }
        },
        refreshWindowScrollbars: function (windowID) {

            if (typeof windowID == 'undefined')
            {
                windowID = this.windowID;
            }
            else if (typeof windowID == 'object')
            {
                windowID = $(windowID).attr('id');
            }

            if ($('#' + windowID).data('WindowManager'))
            {
                var windata = $('#' + windowID).data('WindowManager');

                var oldTriggerState = windata.stopWinContentChangeTrigger;
                windata.stopWinContentChangeTrigger = false;
                windata.settings.enableContentScrollbar = true;

                windata.enableWindowScrollbar();

                $('#' + windowID).trigger('winContentChange');
                //  console.log('trigger winContentChange');

                windata.stopWinContentChangeTrigger = oldTriggerState;
            }


        },
        /**
         * Prepare all form elements
         * @param windowID
         */
        prepareWindowFormUi: function (windowID)
        {
            if (typeof windowID == 'undefined')
            {
                windowID = (this.wm ? this.wm.id : this.windowID);
            }
            else if (typeof windowID == 'object')
            {
                windowID = $(windowID).attr('id');
            }


            // prepare window toolbar buttons to apple style
            var i = 0;
            $('#toolbar-' + windowID).find('button').each(function () {
                if ($(this).parents('.tablegrid-searchbar').length == 0)
                {
                    var text = $(this).text().trim();
                    if (text)
                    {
                        $(this).attr('title', text);
                        ++i;
                    }
                }
            });

            // Prepare Checkboxes & Prepare Radio
            $('#' + windowID).find('input[type="checkbox"]:not(.inputC),input[type="radio"]:not(.inputR)').each(function () {
                var t = $(this).attr('type');
                if (t === 'checkbox')
                {
                    $(this).attr('default', (($(this).is(':checked') || $(this).get(0).checked == true) ? 'on' : 'off'));
                    $.Zebra_TransForm($(this));
                    $(this).addClass('inputC');
                }
                else if (t === 'radio')
                {
                    $(this).attr('default', (($(this).is(':checked') || $(this).get(0).checked == true) ? 'on' : 'off'));
                    $.Zebra_TransForm($(this));
                    $(this).addClass('inputR');
                }
            });

            $('#' + windowID).find('select:not(.inputS)').SelectBox(windowID);


            /**
             * set tabindex
             */

            $('#' + windowID).find('input,textarea,select.inputS').each(function () {
                if ($(this).is('input')) {
                    if ($(this).attr('type')) {
                        if ($(this).attr('type').match(/(radio|checkbox)/i)) {
                            if ($(this).hasClass('inputC') || $(this).hasClass('inputR')) {
                                $(this).prev().prev().attr('tabindex', formTabIndex++);
                            }
                        }
                        else {
                            if (!$(this).attr('type').match(/hidden/))
                            {
                                $(this).attr('tabindex', formTabIndex++);
                            }
                        }
                    }
                }
                else if ($(this).is('textarea')) {
                    $(this).attr('tabindex', formTabIndex++);
                }
                else if ($(this).is('select')) {
                    $(this).attr('tabindex', formTabIndex++);
                }
            });

        },
        updateFormUiDefaults: function (formID, windowID)
        {
            $('#' + windowID).find('#' + formID).find('.inputS,.inputR,.inputC').each(function () {

                if ($(this).hasClass('inputS'))
                {
                    $(this).attr('default', this.selectedIndex);
                }
                else if ($(this).hasClass('inputR') || $(this).hasClass('inputC'))
                {
                    $(this).attr('default', 'off');

                    if ($(this).is(':checked') || $(this).prop('checked'))
                    {
                        $(this).attr('default', 'on');
                    }
                }

            });
        },
        resetWindowFormUi: function (windowID, formID)
        {

            $('#' + formID, $('#' + windowID)).find('select.inputS,input.inputR,input.inputC').each(function () {
                if ($(this).hasClass('inputS'))
                {
                    // console.log('reset SelectBox ');
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
        },
        removeWindowFormUi: function (windowID)
        {
            $('#' + windowID + ',#Sidepanel').find('select').each(function () {
                var sb = $(this).attr('sb');

                if (sb)
                {
                    $(this).SelectBox('destroy');
                    $(this).removeClass('inputS').removeAttr('sb');
                    $('#' + sb).remove();
                }


            });


        },
        unload: function (windowid)
        {
            // unload form
            if ($('#' + windowid).data('formID'))
            {
                //    $('#' + id).find('#' + $('#' + id).data('formID')).removeData('formConfig');
                //    $('#' + id).removeData('formConfig');
                //    $('#' + id).removeData('formID');
            }

            Doc.unload(windowid);
            // this.windowID = null;
        },
        mask: function (msg, delay)
        {
            var w = Desktop.getActiveWindow();
            if (!w)
            {
                return false;
            }


            this.maskedWindows[ $(w).attr('id') ] = true;

            $(w).mask(msg, delay);
        },
        unmask: function ()
        {
            var w = Desktop.getActiveWindow();
            if (!w)
            {
                return false;
            }

            if (typeof this.maskedWindows[ $(w).attr('id') ] != 'undefined')
            {
                delete this.maskedWindows[ $(w).attr('id') ];

                $(w).unmask();
            }
        }

    }

})(window);

Win.Tabs = (function () {
    return {
        initTabs: function (windowID)
        {

            if (!$('#' + windowID).find('.tabcontainer').length)
            {
                Debug.log('No tab container found');
                return;
            }


            //   console.log('Creating Tabs');
            var contain = $('#' + windowID).find('.window-toolbar:first');


            if (!contain.length)
            {
                Debug.log('window-toolbar not found to create tabs');
                return;
            }

            if (!contain.next().hasClass('tabcontainer'))
            {
                // console.log($('#' + windowID).find('.tabcontainer:first').html());
                // $('#' + Win.windowID).find('.tabcontainer:first').insertBefore($('#' + Win.windowID).find('.window-body-content:first'));
                $('#' + windowID).find('.tabcontainer:first').insertAfter(contain);

                // Debug.log('tabcontainer move to window-toolbar:first');


            }
            else
            {
                // Debug.log('Removing existing Tabs');
                $('#' + windowID).find('.sub-content .tabcontainer').remove();
            }

            var tabs = $('.tabcontainer', $('#' + windowID)).find('li');


            $('#' + windowID + ' .tab-content').hide();

            var actTab = null;
            tabs.each(function (i)
            {
                if ($(this).hasClass('actTab'))
                {
                    actTab = i;
                }

                $('#' + windowID).find('#tc' + i).addClass('tab-content');
            });


            if (actTab === null)
            {
                $(tabs).removeClass('actTab');
                tabs.eq(0).addClass('actTab');

                if ($('#' + windowID).find('#tc0').hasClass('use-nopadding')) {
                    $('#' + windowID).addClass('no-padding');
                }


                $('#' + windowID).find('#tc0').addClass('tab-content').show();
            }
            else
            {
                $(tabs).removeClass('actTab');
                tabs.eq(actTab).addClass('actTab');

                if ($('#' + windowID).find('#tc' + actTab).hasClass('use-nopadding')) {
                    $('#' + windowID).addClass('no-padding');
                }

                $('#' + windowID).find('#tc' + actTab).addClass('tab-content').show();
            }

            tabs.each(function (i)
            {
                if ($(this).hasClass('actTab'))
                {
                    var tab = $('#' + windowID).find('#tc' + i);
                    if ($('#' + windowID).find('.tinyMCE-Toolbar').length == 1)
                    {
                        if ($('#tc' + i, $('#' + windowID)).find('.tinymce-editor').length >= 1)
                        {
                            $('.tinyMCE-Toolbar #disabler', $('#' + windowID)).removeClass('forceDisable');
                        }
                        else
                        {
                            $('.tinyMCE-Toolbar #disabler', $('#' + windowID)).addClass('forceDisable');
                        }
                    }

                    var useNoPadding = false;
                    if (tab.hasClass('use-nopadding')) {
                        $('#' + windowID).addClass('no-padding');
                        useNoPadding = true;
                    }
                    else {
                        $('#' + windowID).removeClass('no-padding');
                    }

                    tab.show();
                    $('#' + windowID).trigger('winContentChange');


                    if (useNoPadding) {
                        setTimeout(function () {
                            if (tab.find('.sourceEdit').length == 1) {
                                var ace = tab.find('.sourceEdit').data('ace');

                                if (ace && typeof resizeAce === 'function') {
                                    resizeAce($('#' + windowID).data('WindowManager'));
                                }
                            }
                        }, 10);
                    }


                }
                else
                {
                    $('.tinyMCE-Toolbar #disabler', $('#' + windowID)).addClass('forceDisable');
                    $('#' + windowID).find('#tc' + i).hide();
                }

                $(this).unbind('click.tab').on('click.tab', function () {

                    var tabID = $(this).attr('id');

                    var w = $('#' + windowID);

                    if (tabID)
                    {
                        tabID = tabID.replace('tab_', '');
                        var tabContent = w.find('#tc' + tabID);

                        if (!tabContent.is(':visible'))
                        {
                            $(this).parent().find('.actTab:first').removeClass('actTab').addClass('defTab');
                            $(this).removeClass('defTab').addClass('actTab');

                            var useNoPadding = false;
                            if (tabContent.hasClass('use-nopadding')) {
                                w.addClass('no-padding');
                                useNoPadding = true;
                            }
                            else {
                                w.removeClass('no-padding');
                            }


                            w.find('.tab-content:visible').hide();
                            tabContent.show();
                            var wtoolbar = w.find('.tinyMCE-Toolbar');
                            if (wtoolbar.length == 1)
                            {
                                if (tabContent.find('.tinymce-editor').length >= 1)
                                {
                                    wtoolbar.removeClass('forceDisable');
                                    wtoolbar.find('#disabler').removeClass('forceDisable');
                                }
                                else
                                {
                                    wtoolbar.addClass('forceDisable');
                                    wtoolbar.find('#disabler').addClass('forceDisable');
                                }
                            }

                            w.trigger('winContentChange');

                            if (useNoPadding) {
                                setTimeout(function () {
                                    if (tabContent.find('.sourceEdit').length == 1) {
                                        var ace = tabContent.find('.sourceEdit').data('ace');

                                        if (ace && typeof resizeAce === 'function') {
                                            resizeAce(w.data('WindowManager'));
                                        }
                                    }
                                }, 10);
                            }

                        }
                        else {
                            if (tabContent.hasClass('use-nopadding')) {
                                w.addClass('no-padding');
                            }
                            else {
                                w.removeClass('no-padding');
                            }
                        }

                    }
                });

            });



            $('#' + windowID).trigger('winContentChange');
            //   $('#' + windowID).find('.tabHeader ul.tabbedMenu').taboverflow();
        }
    };

})(window);

Win.ContentTabs = (function () {
    return {
        negateIfRTL: ('undefined' != typeof isRtl && isRtl) ? -1 : 1,
        initTabs: function (ulObject)
        {
            var fixed = null, objectString = '';
            if (typeof object === 'string')
            {
                if (ulObject.substr(0, 1) === '#' || ulObject.substr(0, 1) === '.')
                {
                    objectString = ulObject;
                }
                else
                {
                    objectString = '#' + ulObject;
                }

                fixed = $(objectString);
            }
            else
            {
                fixed = $(ulObject);
            }


            var ulID = fixed.children(':first').attr('id');
            var fluid = fixed.children('.tabs'),
                    active = fluid.children('.activetab'),
                    tabs = fluid.children('.tab'),
                    tabsWidth = 0,
                    fixedRight, fixedLeft,
                    arrowLeft, arrowRight, resizeTimer, css = {},
                    marginFluid = 'margin-left',
                    marginFixed = 'margin-right',
                    msPerPx = 2, tabContents = $('.tab-content');

            // Find the width of all tabs
            tabs.each(function () {
                tabsWidth = tabsWidth + parseInt($(this).outerWidth(true));
            });

            Win.ContentTabs.refreshTabs = function (savePosition)
            {
                if (!fixed.length)
                {
                    return;
                }



                var fixedWidth = fixed.width(),
                        margin = 0, css = {};
                fixedLeft = fixed.offset().left;
                fixedRight = fixedLeft + fixedWidth;

                if (!savePosition)
                    active.makeTabVisible();

                // Prevent space from building up next to the last tab if there's more to show
                if (tabs.last().isTabVisible()) {
                    margin = fixed.width() - tabsWidth;
                    margin = margin > 0 ? 0 : margin;
                    css[marginFluid] = margin + 'px';
                    fluid.animate(css, 100, "linear");
                }

                // Show the arrows only when necessary
                if (fixedWidth > tabsWidth)
                    arrowLeft.add(arrowRight).hide();
                else
                    arrowLeft.add(arrowRight).show();
            };

            Win.ContentTabs.registerTabEvents = function ()
            {
                if (!tabs.length)
                {
                    return;
                }

                var act = null;
                tabs.each(function (index, obj)
                {
                    if (act == null && $(this).hasClass('activetab'))
                    {
                        act = index;
                    }
                });

                if (act == null)
                {
                    tabs.removeClass('activetab');
                    tabs.eq(0).addClass('activetab');

                    active = tabs.eq(0);
                    tabContents.hide();
                    tabContents.eq(0).show();
                }
                else
                {
                    active = tabs.eq(act);
                    tabContents.hide();
                    tabContents.eq(act).show();
                }


                tabs.each(function ()
                {
                    var contentId = $(this).attr('tab');
                    if (contentId)
                    {
                        $(this).unbind('click.tab').bind('click.tab', function (e) {

                            e.preventDefault();
                            if (!$('#' + ulID + '-' + contentId).is(':visible'))
                            {
                                active.removeClass('activetab');
                                active = $(this);
                                $(tabContents).hide();
                                $('#' + ulID + '-' + contentId).show();
                                $(this).addClass('activetab');
                                Win.redrawWindowHeight(Win.windowID, true);
                                Win.refreshWindowScrollbars();
                            }

                        });
                    }

                });

                Win.refreshWindowScrollbars();
            };




            $.fn.extend({
                makeTabVisible: function () {
                    var t = $(this).eq(0), left, right, css = {}, shift = 0;

                    if (!t.length)
                        return this;

                    left = t.offset().left;
                    right = left + t.outerWidth();

                    if (right > fixedRight)
                        shift = fixedRight - right;
                    else if (left < fixedLeft)
                        shift = fixedLeft - left;

                    if (!shift)
                        return this;

                    css[marginFluid] = "+=" + Win.ContentTabs.negateIfRTL * shift + 'px';
                    fluid.animate(css, Math.abs(shift) * msPerPx, "linear");
                    return this;
                },
                isTabVisible: function () {
                    var t = $(this).eq(0),
                            left = t.offset().left,
                            right = left + t.outerWidth();
                    return (right <= fixedRight && left >= fixedLeft) ? true : false;
                }
            });



            // Set up fixed margin for overflow, unset padding
            css['padding'] = 0;
            css[marginFixed] = (-1 * tabsWidth) + 'px';
            fluid.css(css);

            if (!fixed.parent().hasClass('nav-tabs-nav'))
            {
                // Build tab navigation
                arrowLeft = $('<div class="nav-tabs-arrow nav-tabs-arrow-left"><a>&laquo;</a></div>');
                arrowRight = $('<div class="nav-tabs-arrow nav-tabs-arrow-right"><a>&raquo;</a></div>');

                // Attach to the document
                fixed.wrap('<div class="nav-tabs-nav"/>').parent().prepend(arrowLeft).append(arrowRight);
            }
            else
            {
                arrowLeft = fixed.parent().find('.nav-tabs-arrow-left:first');
                arrowRight = fixed.parent().find('.nav-tabs-arrow-right:first');
            }


            Win.ContentTabs.refreshTabs();
            Win.ContentTabs.registerTabEvents();

            // Make sure the tabs reset on resize
            $('#' + Win.windowID).resize(function () {
                if (resizeTimer)
                    clearTimeout(resizeTimer);
                resizeTimer = setTimeout(Win.ContentTabs.refreshTabs, 200);
            });

            // Build arrow functions
            $.each([{
                    arrow: arrowLeft,
                    next: "next",
                    last: "first",
                    operator: "+="
                }, {
                    arrow: arrowRight,
                    next: "prev",
                    last: "last",
                    operator: "-="
                }], function () {
                var that = this;
                this.arrow.unbind('mousedown').mousedown(function () {
                    var marginFluidVal = Math.abs(parseInt(fluid.css(marginFluid))),
                            shift = marginFluidVal,
                            css = {};

                    if ("-=" == that.operator)
                        shift = Math.abs(tabsWidth - fixed.width()) - marginFluidVal;

                    if (shift <= 0)
                        return;

                    css[marginFluid] = that.operator + shift + 'px';
                    fluid.animate(css, shift * msPerPx, "linear");
                }).unbind('mouseup').mouseup(function () {
                    var tab, next;
                    fluid.stop(false);
                    tab = tabs[that.last]();
                    while ((next = tab[that.next]()) && next.length && !next.isTabVisible()) {
                        tab = next;
                    }
                    tab.makeTabVisible();
                });
            });
        }


    };
})(window);
