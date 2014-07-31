/**
 DreamCMS (c)2013
 Seemode
 */









var opener, notifyTimeout;
var interval;
var authKey = null;
var authSite = null;
var interval = null, notifyTimeout;
var clickAnalyserOn = false;
var SeemodeCookie = new CookieRegistry;
var tmp = cookiePrefix;
tmp = tmp.replace(/_fe$/, '_be');
SeemodeCookie.initialize(tmp + '_registry');
var Doc = {
    lastActiveTinyMCE: null,
    loadedDiffMirror: false,
    resetDocumentSettings: function (windowID, formID)
    {

        $('#' + formID).get(0).reset();
    },
    unloadTinyMce: function (inobject)
    {
        var self = this;
        if (typeof tinyMCE != 'undefined' && $(inobject).find('.tinymce-editor').length)
        {
            if (typeof inobject != 'undefined' && jQuery.tinymce)
            {
                $(inobject).find('.tinymce-editor').each(function ()
                {
                    var idStr = $(this).attr('id');
                    $(this).tinymce().remove();
                    $(this).removeClass('loaded');
                    $(inobject).find('.tinyMCE-Toolbar').empty().append($('<div/>').attr('id', 'disabler').hide()).hide();
                    if (idStr === self.lastActiveTinyMCE) {
                        self.lastActiveTinyMCE = null;
                    }
                    $(inobject).find('#' + idStr + '_external').remove();
                });
                $(inobject).find('.tinymce-editor').each(function ()
                {
                    var idStr = $(this).attr('id');
                    $(this).removeClass('loaded');
                    if (idStr === self.lastActiveTinyMCE) {
                        self.lastActiveTinyMCE = null;
                    }
                });
                $(inobject).find('.tinyMCE-Toolbar').empty().append($('<div/>').attr('id', 'disabler').hide()).hide();
            }
        }
    },
    removeTinyMceToolbar: function ()
    {
        this.unloadTinyMce($(document));
    },
    setTinyMceToolbar: function (tinymceToolbar, ed, editorid)
    {
        var self = this, tb, disabler = $('<div/>').attr('id', 'disabler');
        $('#' + ed.id + '_external').show();
        $('#' + ed.id).next().find('iframe:first').show();
    },
    enableTinyMceToolbar: function (tinymceToolbar, e, editorid)
    {
        var disabler = $('<div>').attr('id', 'disabler');
        var root = $(tinymceToolbar).parents('.tinyMCE-Toolbar:first');
        if ($('.mceExternalToolbar', $('.tinyMCE-Toolbar', root)).length > 1)
        {
            $('#' + editorid + '_external').show();
        }
        else
        {
            $('#' + editorid + '_external').show();
        }

        this.lastActiveTinyMCE = editorid;
        $('.tinyMCE-Toolbar', root).removeClass('disabled');
        $('#disabler', $('.tinyMCE-Toolbar', root)).hide();
        $('.mceExternalToolbar', $('.tinyMCE-Toolbar', root)).removeClass('disabled');
    },
    disableTinyMceToolbar: function (tinymceToolbar, e, editorid)
    {
        if (this.editor_onmenu)
        {
            return;
        }

        var root = $(tinymceToolbar).parents('.tinyMCE-Toolbar:first');
        $('.tinyMCE-Toolbar', root).addClass('disabled');
        $('.mceExternalToolbar', $('.tinyMCE-Toolbar', root)).addClass('disabled');
        $('#disabler', $('.tinyMCE-Toolbar', root)).addClass('disabled').show();
        $('.tinyMCE-Toolbar:last').show();
    }
};
var Notifier = {
    display: function (mode, msg)
    {
        SeemodeEdit.notify(msg);
        notifyTimeout = setTimeout(function () {
            SeemodeEdit.unNotify();
        }, 3000);
    },
    info: function (msg)
    {
        SeemodeEdit.notify(msg);
        notifyTimeout = setTimeout(function () {
            SeemodeEdit.unNotify();
        }, 3000);
    },
    error: function (msg)
    {
        SeemodeEdit.notify(msg);
        notifyTimeout = setTimeout(function () {
            SeemodeEdit.unNotify();
        }, 3000);
    },
    warn: function (msg)
    {
        SeemodeEdit.notify(msg);
        notifyTimeout = setTimeout(function () {
            SeemodeEdit.unNotify();
        }, 3000);
    }
};

var Win = {
    refreshContentHeight: function () {
    }
};

var SeemodeEdit = {
    isDirty: false,
    actions: [],
    fields: [],
    panel: null,
    controlBar: null,
    panelEdit: null,
    panelEditControl: null,
    leftSide: null,
    contentSide: null,
    inited: false,
    authKey: null,
    settings: {},
    panelMinHeight: 300,
    clickAnalyserOn: false,
    init: function (opts)
    {
        if (!this.inited)
        {

            var self = this, t;
            this.inited = true;

            opts.isSeemode = true;
            opts.onTinyMceChangeHandler = function (ed)
            {
                clearTimeout(t);

                t = setTimeout(function () {
                    $('#' + ed.id + '-hidden').html(tinyMCE.activeEditor.getContent());
                    self.prepareLiveTinyMCEData($('#' + ed.id + '-hidden'), $('#' + ed.id + '-hidden').attr('contentid'), $('#' + ed.id + '-hidden').attr('modul'));
                }, 200);
            };

            opts.onTinyMCEKeyUp = function (ed, event) {

                clearTimeout(t);

                t = setTimeout(function () {
                    $('#' + ed.id + '-hidden').html(tinyMCE.activeEditor.getContent());
                    self.prepareLiveTinyMCEData($('#' + ed.id + '-hidden'), $('#' + ed.id + '-hidden').attr('contentid'), $('#' + ed.id + '-hidden').attr('modul'));
                }, 200);
            };
            this.settings = $.extend({}, opts);

            Config.init(opts);

            this.getAuthKey();


            this.buildPanel();
            this.buildBaseButton('openclose');
            this.buildBaseButton('config');
            this.buildBaseButton('spacer');
            this.buildBaseButton('clearcache');
            this.buildBaseButton('clearfullcache');
            this.buildBaseButton('clearpagecache');
            this.buildBaseButton('spacer');
            this.buildBaseButton('debug');
            this.buildBaseButton('firewall');
            this.buildBaseButton('spacer');
            this.buildBaseButton('clickanalyser');
            // this.findEditables();


            this.bindSeemodeButton();
            this.bindControlEvents();
            this.bindBaseButtonTooltip();
            window.onbeforeunload = function (e)
            {
                if (self.isDirty)
                {

                    message = cmslang.form_dirty;
                    if (typeof e == 'undefined') {
                        e = window.event;
                    }

                    if (e) {
                        e.returnValue = message;
                    }

                    return message;
                }
            };
        }


    },
    getAuthKey: function ()
    {
        var self = this, tmpAuthKey = SeemodeCookie.get('loginpermanet');

        if (tmpAuthKey !== null && tmpAuthKey !== false && tmpAuthKey != '')
        {
            self.authKey = tmpAuthKey;
            Cookie.set('loginpermanet', self.authKey);
            SeemodeCookie.set('loginpermanet', self.authKey);
        }

        if (self.authKey !== null)
        {
            return;
        }

        // check authKey
        $.ajax({
            type: "POST",
            url: systemUrl + '/index.php',
            'data': {'cp': 'main', 'getAuthKey': 1, 'ajax': 1},
            timeout: 4000,
            dataType: 'json',
            cache: false,
            async: false,
            success: function (data)
            {
                if (Tools.responseIsOk(data))
                {
                    self.authKey = data.authKey;
                    Cookie.set('loginpermanet', self.authKey);
                    SeemodeCookie.set('loginpermanet', self.authKey);
                }
                else
                {
                    Cookie.set('loginpermanet', '');
                    SeemodeCookie.set('loginpermanet', '');
                    self.authKey = null;
                    if (typeof data.msg != 'undefined')
                    {
                        console.log(data.msg);
                    }
                }
            }
        });

    },
    buildPanel: function ()
    {
        var self = this;
        this.panel = $('<div id="seemode-panel" class="seemode-panel"/>');
        this.controlBar = $('<div id="seemode-panel-control"/>');
        this.panelEdit = $('<div id="seemode-panel-edit"/>');
        this.panelEditControl = $('<div id="seemode-content-control"/>');
        this.leftSide = $('<div id="seemode-panel-left"/>');
        this.contentSide = $('<div id="seemode-panel-content"/>');
        this.panelEdit.append(this.panelEditControl).append(this.leftSide).append(this.contentSide);
        this.panel.append(this.panelEdit).append(this.controlBar);
        $('body').append(this.panel);
        $('body').css({paddingBottom: this.panel.outerHeight(true)});

        $('#seemode-panel-content,#seemode-panel-left,#seemode-panel-control,#seemode-content-control').bind('mousewheel DOMMouseScroll', function (e) {
            /*
             if ($(e.target).is('select') || $(e.target).parents('select').length || $(e.target).is('textarea') )
             {
             return;
             }
             */


            var scrollTo = null;
            if (e.type == 'mousewheel') {
                scrollTo = (e.originalEvent.wheelDelta * -1);
            }
            else if (e.type == 'DOMMouseScroll') {
                scrollTo = 40 * e.originalEvent.detail;
            }

            if (scrollTo) {
                e.preventDefault();
                $(this).scrollTop(scrollTo + $(this).scrollTop());
            }
        });



        var minHeight = this.panelMinHeight;
        this.panelEdit.resizable({
            handles: "n",
            minHeight: minHeight,
            resize: function (e, ui)
            {
                if (minHeight > ui.size.height)
                {
                    e.stopPropagation();
                    $(this).css({top: '', bottom: 0, height: minHeight});
                    var h = parseInt(self.panelEditControl.outerHeight(true), 10);
                    self.contentSide.css({height: minHeight - h});
                    self.leftSide.css({height: minHeight - h});
                    return;
                }
                else
                {

                    var h = parseInt(self.panelEditControl.outerHeight(true), 10);
                    self.contentSide.css({height: ui.size.height - h});
                    self.leftSide.css({height: ui.size.height - h});
                    $(this).css({top: '', bottom: 0, height: ui.size.height});
                }
            },
            stop: function (e, ui)
            { 
                var h = parseInt(self.panelEditControl.outerHeight(true), 10);
                $('body').css({paddingBottom: self.panel.outerHeight(true)});
                self.contentSide.css({height: ui.size.height - h});
                self.leftSide.css({height: ui.size.height - h});
            }});
    },
    getContentControls: function (params, callback) {
        var self = this;
        $.post('index.php', {getContentTrans: true}, function (data0) {
            if (Tools.responseIsOk(data0))
            {
                params += '&ajax=1&seemodePopup=1&authKey=' + self.authKey;
                params += '&settranslation=' + data0.code;

                $.post('admin.php', params, function (data) {
                    if (Tools.responseIsOk(data))
                    {
                        if (callback)
                        {
                            callback(data);
                        }
                    }
                    else
                    {
                        console.log([data]);
                        self.removeLoading();
                        
                        if (data.msg) {
                            self.notify(data.msg);
                            notifyTimeout = setTimeout(function () {
                                self.unNotify();
                            }, 3000);
                        }
                    }
                });
            }
        });
    },
    prepareLiveTinyMCEData: function (element, contentid, controller)
    {
        var value = $($.parseHTML($(element).html()));
        var tag = $('#seemode-var-' + contentid + '-' + $(element).attr('fieldname'));

        if (tag.attr('noimages') > 0)
        {
            tag.empty().append($(element).html());
            tag.find('img').each(function () {
                var parentTag = $(this).parent().get(0).tagName.toLowerCase();
                var parentText = $(this).parent().text().trim();
                if (parentTag == 'p' && parentText == '') {
                    $(this).parent().remove();
                }
                else {
                    $(this).remove();
                }
            });
        }
        else
        {
            tag.empty().append($(element).html());
        }

        if ($('.footnotes').length == 1)
        {
            this.prepareFootnotes(tag, $('.footnotes'));
        }

        if (tag.attr('allowedtags'))
        {
            var allowedtags = tag.attr('allowedtags'), allowed = allowedtags.split(','), allow = [];
            for (var i = 0; i < allowed.length; ++i)
            {
                if (allowed[i] != '')
                {
                    allow.push('<' + allowed[i] + '>');
                }
            }

            if (allow.length > 0)
            {
                tag.html(this.stripTags(tag.get(0).innerHTML, allow.join('')));
            }
        }

        if (tag.attr('length') > 0)
        {
            tag.html(this.truncateHtml(tag.get(0).innerHTML, tag.attr('length')));
        }


    },
    prepareLiveChangeData: function (e, element, contentid, controller)
    {
        var value = $($.parseHTML($(e.target).val()));
        var tag = $('#seemode-var-' + contentid + '-' + $(element).attr('name'));

        if (tag.attr('noimages') > 0)
        {
            tag.empty().append($(value)).find('img').each(function () {
                var parentTag = $(this).parent().get(0).tagName.toLowerCase();
                var parentText = $(this).parent().text().trim();
                if (parentTag == 'p' && parentText == '') {
                    $(this).parent().remove();
                }
                else {
                    $(this).remove();
                }
            });
        }
        else
        {
            tag.empty().append($(value));
        }

        if ($('.footnotes').length == 1)
        {
            this.prepareFootnotes(tag, $('.footnotes'));
        }

        if (tag.attr('allowedtags'))
        {
            var allowedtags = tag.attr('allowedtags'), allowed = allowedtags.split(','), allow = [];
            for (var i = 0; i < allowed.length; ++i)
            {
                if (allowed[i] != '')
                {
                    allow.push('<' + allowed[i] + '>');
                }
            }

            if (allow.length > 0)
            {
                tag.html(this.stripTags(tag.get(0).innerHTML, allow.join('')));
            }
        }

        if (tag.attr('length') > 0)
        {
            tag.html(this.truncateHtml(tag.get(0).innerHTML, tag.attr('length')));
        }
    },
    bindFormEditEvents: function (contentid, controller)
    {
        var self = this;
        var to;
        $('#seemode-panel-content').find('input,textarea,select').unbind('change').on('change', function (e) {
            clearTimeout(to);
            var s = this;
            to = setTimeout(function () {
                self.prepareLiveChangeData(e, s, contentid, controller);
                self.setDirty();
            }, 200);
        });

        $('#seemode-panel-content').find('input,textarea').unbind('keyup').on('keyup', function (e) {
            if (e.keyCode < 16 || e.keyCode > 40 || e.keyCode >= 93 && e.keyCode <= 111)
            {
                clearTimeout(to);
                var s = this;
                to = setTimeout(function () {
                    self.prepareLiveChangeData(e, s, contentid, controller);
                    self.setDirty();
                }, 200);

            }
        });
    },
    sendRollback: function (rollbackUrl, callback)
    {
        var postData = Tools.convertUrlToObject(rollbackUrl);
        postData.ajax = true;
        postData.transrollback = true;

        $.ajax({
            type: "POST",
            url: 'admin.php',
            'data': postData,
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
    },
    sendUnlock: function (modul, action, contentid, callback)
    {
        $.ajax({
            type: "POST",
            url: 'admin.php',
            'data': {
                action: 'unlock',
                unlock: true,
                modul: modul,
                modulaction: action,
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
    },
    sendDokumentRollback: function (close) {
        var self = this, postData = Tools.convertUrlToObject('admin.php?' + $('body').attr('rollbackUrl'));
        postData.ajax = true;
        postData.transrollback = true;
        $.post('admin.php', postData, function () {

            if (close) {
                var modul = $('body').attr('modul');
                var contentid = $('body').attr('contentid');
                var lockaction = $('body').attr('lockaction');

                if (contentid && modul)
                {
                    self.sendUnlock(modul, lockaction, contentid);
                }
            }

            self.controlBar.find('.toggle-open,.toggle-close').removeClass('active').removeClass('toggle-close').addClass('toggle-open').hide();
            self.panelEdit.hide();
            self.panelEditControl.empty();
            self.contentSide.empty();

            $('body').removeAttr('rollback').removeAttr('rollbackUrl').removeAttr('contentid').removeAttr('modul').removeAttr('lockaction');
        });
    },
    triggerFormSave: function (exit, data)
    {
        // unlock content
        if (exit && $('body').attr('rollback'))
        {
            var modul = $('body').attr('modul');
            var contentid = $('body').attr('contentid');
            var self = this, lockaction = $('body').attr('lockaction');

            if (contentid && modul)
            {
                this.sendUnlock(modul, lockaction, contentid);
            }
        }


        this.controlBar.find('.toggle-open,.toggle-close').removeClass('active').removeClass('toggle-close').addClass('toggle-open').hide();
        this.panelEdit.hide();
        this.panelEditControl.empty();
        this.contentSide.empty();

        $('body').removeAttr('rollback').removeAttr('rollbackUrl').removeAttr('contentid').removeAttr('modul').removeAttr('lockaction');
    },
    getBuildData: function (contentid, controller, callback)
    {
        var self = this;
        if (!this.actions.length)
        {
            return;
        }


        this.getContentControls('adm=' + controller + '&' + self.actions[contentid].editurl.replace('%s', contentid), function (data) {

            if (data.toolbar)
            {
                self.panelEditControl.empty().append(data.toolbar);
            }


            self.panelEdit.css({visible: 'hidden'}).show();
            var minHeight = self.panelEdit.height();
            self.panelEdit.css({visible: ''}).hide();
            if (data.maincontent)
            {
//var html = $.parseHTML(data.maincontent)

                self.contentSide.empty().append(data.maincontent);
                self.contentSide.find('.tabcontainer').remove();
                self.leftSide.empty();
                var sections = [];
                var i = 0;
                self.contentSide.find('fieldset').each(function (x) {
                    var fs = $(this);
                    if ($(this).find('legend').length >= 1)
                    {
                        var el = $('<div>').attr('id', 'field-' + i).addClass('section');
                        if (i > 0)
                        {
                            el.hide();
                        }

                        $(this).find('legend:first').each(function ()
                        {
                            sections.push($(this).text());
                        });
                        i++;
                        $(fs).wrap(el);
                    }
                });

                if (sections.length > 0)
                {
                    var contain = $('<div class="edit-sections">');
                    for (var x = 0; x < sections.length; ++x)
                    {
                        contain.append($('<div class="section-item" rel="field-' + x + '">').append(sections[x]));
                    }

                    self.leftSide.append(contain);
                    contain.find('.section-item:eq(0)').addClass('active');
                    contain.find('.section-item').click(function () {
                        $(this).parent().find('.active').removeClass('active');
                        $(this).addClass('active');
                        self.contentSide.find('.section:visible').hide();
                        self.contentSide.find('#' + $(this).attr('rel')).show();
                    });
                }
            }

            $('body').attr('contentid', contentid).attr('modul', controller);

            if (data.rollback === true)
            {

                $('body').attr('rollback', true).attr('rollbackUrl', 'adm=' + controller + '&' + self.actions[contentid].editurl.replace('%s', contentid));

                if (data.contentlockaction)
                {
                    $('body').attr('lockaction', data.contentlockaction);
                }
            }

            self.controlBar.find('.toggle-open').addClass('active').show();
            var h = parseInt(self.panelEditControl.outerHeight(true), 10);
            var height = minHeight;


            self.contentSide.find('textarea').attr('contentid', contentid).attr('modul', controller);
            self.contentSide.find('textarea').each(function () {
                if ($(this).hasClass('tinymce-editor'))
                {
                    var liveTiny = $('<div class="live-tiny-mcecontent" style="display:none"/>').attr('modul', controller).attr('contentid', contentid).attr('fieldname', $(this).attr('name')).attr('id', $(this).attr('id') + '-hidden');

                    /*
                     liveTiny.on('change', function() {
                     self.prepareLiveChangeData($(this), $(this).attr('contentid'), $(this).attr('modul'));
                     });*/


                    liveTiny.insertAfter($(this));
                }
            });

            self.contentSide.css({height: height - h});
            self.leftSide.css({height: height - h});
            self.panelEdit.css({height: height});
            if (callback)
            {
                self.initTinyMCE();
                callback();
            }

        });
    },
    initTinyMCE: function ()
    {
        if (this.contentSide.find('.tinymce-editor').length)
        {
            var self = this;
            if (typeof window.tinymceConfig == 'undefined' || typeof window.tinymceConfig.skin == 'undefined')
            {
                $.get('admin.php?tinymce=getconfig', function () {

                    self.contentSide.find('.tinymce-editor').each(function () {
                        if (!$(this).hasClass('loaded'))
                        {

                            delete window.tinymceConfig.theme_advanced_toolbar_location;
                            $(this).removeClass('external');
                            var id = $(this).attr('id');
                            $(this).attr('win', '');
                            $((id ? '#' + id : this)).tinymce(window.tinymceConfig);
                            $(this).addClass('loaded');
                        }
                    });
                }, 'script');
            }
            else
            {
                this.contentSide.find('.tinymce-editor').each(function () {
                    if (!$(this).hasClass('loaded'))
                    {
                        delete window.tinymceConfig.theme_advanced_toolbar_location;
                        $(this).removeClass('external');
                        var id = $(this).attr('id');
                        $(this).attr('win', '');
                        $((id ? '#' + id : this)).tinymce(window.tinymceConfig);
                        $(this).addClass('loaded');
                    }
                });
            }
        }
    },
    bindControlEvents: function ()
    {
        var self = this, lastContentid = 0;
        this.controlBar.find('button').unbind().on('click', function () {

            if ($(this).hasClass('edit-item'))
            {
                var contentid = $(this).attr('rel');
                var controller = $(this).attr('controller');
                var changed = false;
                if (lastContentid != contentid && self.actions[contentid] && self.actions[contentid].editurl)
                {
                    lastContentid = contentid;
                    self.getBuildData(contentid, controller);
                    changed = true;
                    self.bindFormEditEvents(lastContentid, controller);
                }

                if (!self.panelEdit.is(':visible'))
                {
                    self.panelEdit.show();
                    return false;
                }
                else
                {
                    if (!changed && self.panelEdit.is(':visible'))
                    {
                        self.panelEdit.hide();
                        return false;
                    }
                }
            }

        });
    },
    clearCache: function (type)
    {
        var url = '', msg, self = this;
        switch (type)
        {
            case 'short':
                url = 'admin.php?adm=cache&action=clear';
                msg = 'Cache wurde geleert';
                break;
            case 'full':
                url = 'admin.php?adm=cache&action=clearfull';
                msg = 'Cache vollständig wurde geleert';
                break;
            case 'pagecache':
                url = 'admin.php?adm=cache&action=clearpagecache';
                msg = 'Seitencache wurde geleert';
                break;
        }
        if (!url)
        {
            return false;
        }

        this.callAjax(url, null, function (data) {
            if (Tools.responseIsOk(data))
            {
                if (data && data.msg)
                {
                    self.notify(data.msg);
                }
                else
                {
                    self.notify(msg);
                }

                notifyTimeout = setTimeout(function () {
                    self.unNotify();
                }, 3000);
            }
        });
    },
    callAjax: function (url, postData, callback, type)
    {

        if (typeof type !== 'string')
        {
            type = 'json';
        }

        if (typeof postData == 'undefined' || postData === false || postData === null || postData === '')
        {
            url += '&ajax=1&seemodePopup=1&authKey=' + this.authKey;
            $.get(url, function (data) {

                if (typeof callback === 'function')
                {
                    callback(data);
                }
            }, type);
        }
        else
        {
            postData.seemodePopup = true;
            postData.authKey = this.authKey;

            $.post(url, postData, function (data) {

                if (typeof callback === 'function')
                {
                    callback(data);
                }
            }, type);
        }
    },
    getSettings: function (location)
    {

    },
    prepareSettings: function (data, callback)
    {
        var self = this;
        if (data.toolbar)
        {
            this.panelEditControl.empty().append(data.toolbar);
        }


        this.panelEdit.css({visible: 'hidden'}).show();
        var minHeight = this.panelEdit.height();
        this.panelEdit.css({visible: ''}).hide();
        if (data.maincontent)
        {

            this.contentSide.empty().append(data.maincontent);
            this.contentSide.find('.tabcontainer').remove();
            this.leftSide.empty();
            var contain = $('<div class="config-sections">');
            this.leftSide.append(contain);
            var ul = $('<ul>');
            this.contentSide.find('.cfg-group').each(function () {

                var label = $(this).find('>div>:first-child').text();
                var li = $('<li>').append($('<span>').append(label).append('<em/>'));
                var _sub = $('<ul>').hide();
                $(this).find('.cfg-btn').each(function () {
                    _sub.append($('<li>').append($(this)));
                });
                li.append(_sub);
                ul.append(li);
            });
            ul.find('.cfg-icon').each(function () {
                var src = $(this).find('img:first').attr('src');
                if (src)
                {
                    $(this).css({background: 'url(' + src + ')'}).find('img:first').remove();
                }

            })

            ul.find('.cfg-btn').each(function () {
                var cfgItem = $(this);
                $(this).parents('li:first').click(function () {

                    self.contentSide.mask('Laden...');
                    cfgItem.parents('ul:last').find('.cfg-btn.active').removeClass('active');
                    cfgItem.addClass('active');
                    self.callAjax(cfgItem.attr('rel'), false, function (data) {
                        if (data.toolbar)
                        {
                            self.panelEditControl.empty().append(data.toolbar);
                        }

                        self.contentSide.empty().append(data.maincontent);
                        self.contentSide.find('h3,h2,h1').remove();
                        self.contentSide.unmask();
                    });
                })
            });
            contain.append(ul);
            var firstUl = this.leftSide.find('ul:eq(0)');
            firstUl.find('>li').each(function () {
                $(this).on('click', function () {
                    if (!$(this).hasClass('open'))
                    {
                        firstUl.find('>li.open').removeClass('open').find('ul:first').slideUp(200);
                        $(this).addClass('open').find('ul:first').slideDown(200);
                    }

                });
            });
            this.contentSide.empty();
        }

        self.controlBar.find('.toggle-open').addClass('active').show();
        var h = parseInt(self.panelEditControl.outerHeight(true), 10);
        var height = minHeight;
        self.contentSide.css({height: height - h});
        self.leftSide.css({height: height - h});
        self.panelEdit.css({height: height});
        self.initTinyMCE();
        if (callback)
        {
            callback();
        }
    },
    buildBaseButton: function (type)
    {
        var btn, self = this;
        switch (type.toLowerCase())
        {
            case 'spacer':
                btn = $('<div class="base-spacer"><span></span></div>');
                break;
            case 'openclose':
                btn = $('<div class="base-btn toggle-open"><span></span></div>').attr('title', 'Dokument bearbeiten');
                btn.hide();
                btn.click(function () {
                    if (!$(this).hasClass('active')) {
                        $(this).addClass('active');
                        $('.current-edit').addClass('seemode-editing');
                        self.panelEdit.show();
                    }
                    else
                    {
                        $(this).removeClass('active');
                        $('.current-edit').removeClass('seemode-editing');
                        self.panelEdit.hide();
                    }
                });
                break;
            case 'config':
                btn = $('<div class="base-btn config"><span></span></div>').attr('title', 'Konfiguration');
                btn.click(function () {
                    var _self = this, stop = false;

                    if (self.isDirty)
                    {
                        Check = confirm(cmslang.form_dirty);
                        if (Check != true)
                        {
                            stop = true;
                        }
                    }

                    if (!stop)
                    {
                        // send Rollback only if is dirty and content editing mode
                        if ($('body').attr('rollback') && self.isDirty)
                        {
                            var postData = Tools.convertUrlToObject('admin.php?' + $('body').attr('rollbackUrl'));
                            postData.ajax = true;
                            postData.seemodePopup = true;
                            postData.transrollback = true;
                            postData.authKey = self.authKey;
                            $.post('admin.php', postData, function () {
                                $('body').removeAttr('rollback').removeAttr('rollbackUrl');
                            });
                        }

                        // Send unlock dokument??? hmmm





                        self.controlBar.find('.toggle-open,.toggle-close').removeClass('active').removeClass('toggle-close').addClass('toggle-open').hide();

                        if (self.isDirty)
                        {
                            self.removeDirty();
                        }

                        if (!$(this).hasClass('active')) {

                            if (!self.leftSide.find('.config-sections').length)
                            {
                                self.callAjax('admin.php?adm=settings', false, function (data) {
                                    $(_self).addClass('active');
                                    self.prepareSettings(data, function () {
                                        self.controlBar.find('.toggle-open').hide();
                                        self.panelEdit.show();
                                    });
                                });
                            }
                            else
                            {
                                $(_self).addClass('active');
                                self.panelEdit.show();
                            }

                        }
                        else
                        {
                            $(this).removeClass('active');
                            self.panelEdit.hide();
                        }
                    }
                });
                break;
            case 'clearcache':
                btn = $('<div class="base-btn clearcache"><span></span></div>').attr('title', 'Quick-Cache leeren');
                btn.click(function () {
                    self.clearCache('short');
                });
                break;
            case 'clearfullcache':
                btn = $('<div class="base-btn clearcache-full"><span></span></div>').attr('title', 'Ganzen Cache leeren');
                btn.click(function () {
                    self.clearCache('full');
                });
                break;
            case 'clearpagecache':
                btn = $('<div class="base-btn pagecache"><span></span></div>').attr('title', 'Seiten-Cache leeren');
                btn.click(function () {
                    self.clearCache('pagecache');
                });
                break;
            case 'debug':
                btn = $('<div class="base-btn debug"><span></span></div>');
                if (Config.get('debugger'))
                {
                    btn.attr('title', 'Debbugger aktivieren');
                }
                else
                {
                    btn.attr('title', 'Debbugger deaktivieren').addClass('off');
                }


                btn.click(function () {
                    var xself = this;



                    self.callAjax('admin.php?adm=dashboard&action=switchdebug', false, function () {
                        if (Config.get('debugger'))
                        {
                            Config.set('debugger', false);
                            $(xself).addClass('off').attr('title', 'Debbugger aktivieren');
                            self.notify('Debbugger wurde deaktiviert');
                            notifyTimeout = setTimeout(function () {
                                self.unNotify();
                            }, 3000);
                        }
                        else
                        {
                            Config.set('debugger', true);
                            $(xself).removeClass('off').attr('title', 'Debbugger deaktivieren');
                            self.notify('Debbugger wurde aktiviert');
                            notifyTimeout = setTimeout(function () {
                                self.unNotify();
                            }, 3000);
                        }
                    });
                });
                break;
            case 'firewall':
                btn = $('<div class="base-btn firewall"><span></span></div>');
                if (Config.get('firewall'))
                {
                    btn.attr('title', 'Firewall aktivieren');
                }
                else
                {
                    btn.attr('title', 'Firewall deaktivieren').addClass('off');
                }


                btn.click(function () {
                    var xself = this;
                    self.callAjax('admin.php?adm=dashboard&action=switchfirewall', false, function () {
                        if (Config.get('firewall'))
                        {
                            Config.set('firewall', false);
                            $(xself).addClass('off').attr('title', 'Firewall aktivieren');
                            self.notify('Firewall wurde deaktiviert');
                            notifyTimeout = setTimeout(function () {
                                self.unNotify();
                            }, 3000);
                        }
                        else
                        {
                            Config.set('firewall', true);
                            $(xself).removeClass('off').attr('title', 'Firewall deaktivieren');
                            self.notify('Firewall wurde aktiviert');
                            notifyTimeout = setTimeout(function () {
                                self.unNotify();
                            }, 3000);
                        }
                    });
                });
                break;
            case 'clickanalyser':
                btn = $('<div class="base-btn clickanalyser"><span></span></div>').attr('title', 'Klick-Analyse anzeigen');
                btn.click(function () {
                    if (!$(this).hasClass('active')) {
                        $(this).addClass('active');
                        self.clickAnalyserOn = true;

                        if (typeof analyseClicks != 'function')
                        {
                            $.getScript(systemUrl + '/html/js/seemode.analyseclicks.js?_' + cookiePrefix, function () {
                                analyseClicks();
                            });
                        }
                        else
                        {
                            analyseClicks();
                        }

                        $(this).attr('title', 'Klick-Analyse ausblenden');
                    }
                    else
                    {
                        self.clickAnalyserOn = false;

                        $(this).removeClass('active');
                        $(this).attr('title', 'Klick-Analyse anzeigen');
                        $(document).find('.analysePoint').remove();

                    }
                });
                break;
        }



        if (btn) {
            this.controlBar.append(btn);
        }


    },
    bindBaseButtonTooltip: function ()
    {
        var tooltip = $('#seemode-tooltip');
        if (!tooltip.length)
        {
            tooltip = $('<div id="seemode-tooltip"/>');
            $('body').append(tooltip);
        }

        var t;
        /*
         this.controlBar.on('mouseout', function() {
         tooltip.fadeOut(150);
         });
         */
        this.controlBar.find('.base-btn').each(function () {
            $(this).on('mouseover', function () {
                var btn = this;
                var offset = $(this).offset();
                clearTimeout(t);
                t = setTimeout(function () {
                    tooltip.stop(true).html($(btn).attr('title')).append('<em><em>').css({left: offset.left, top: offset.top - 30}).fadeIn(150);
                }, 100);
            }).on('mouseleave', function () {
                tooltip.stop(true).fadeOut(150);
            });
        });
    },
    buildButton: function (type, contentid, controller, offline)
    {
        return;
        switch (type.toLowerCase())
        {
            case 'edit':
                this.controlBar.append('<button type="button" rel="' + contentid + '" controller="' + controller + '" class="edit-item">Bearbeiten</button>');
                break;
            case 'delete':
                this.controlBar.append('<button type="button" rel="' + contentid + '" controller="' + controller + '" class="delete-item">Löschen</button>');
                break;
            case 'publish':
                this.controlBar.append('<button type="button" rel="' + contentid + '" controller="' + controller + '" class="publish-item">' + (offline ? 'Aktivieren' : 'Deaktivieren') + '</button>');
                break;
        }
    },
    bindSeemodeButton: function ()
    {
        var self = this, actions = [], fields = [];
        $('div.seemode-item').each(function () {

            var item = $(this);
            var contentid = item.attr('contentid'), modul = item.attr('modul');
            if (contentid > 0 && modul != '')
            {
                var container = $('<div class="seemode-content"/>');
                var itemButton = $('<div class="seemode-button"/>').append('<span>Inhalt</span>');
                var seemActions = $('<div class="seemode-actions"/>');
                container.append(itemButton);
                if (item.attr('edit'))
                {
                    seemActions.append('<span class="edit" title="Edit"></span>');
                }

                if (item.attr('publish') && item.attr('state') > 0)
                {
                    seemActions.append('<span class="publish" title="Change Publishing to Offline"></span>');
                    item.addClass('online-content');
                }
                else if (item.attr('publish') && item.attr('state') <= 0)
                {
                    seemActions.append('<span class="publish off" title="Change Publishing to Online"></span>');
                    item.addClass('offline-content');
                }

                if (item.attr('delete'))
                {
                    seemActions.append('<span class="delete" title="Edit"></span>');
                }


                actions[contentid] = {contentid: contentid, controller: modul, publishurl: (item.attr('publish') || null), editurl: (item.attr('edit') || null), deleteurl: (item.attr('delete') || null)};
                item.find('.seemode-var').each(function () {

                    var fieldName = $(this).attr('id').replace('seemode-var-', '');
                    $(this).attr('id', 'seemode-var-' + contentid + '-' + fieldName);
                    if (fieldName) {

                        if (!fields[contentid]) {
                            fields[contentid] = [];
                        }
                        if (!fields[contentid][modul]) {
                            fields[contentid][modul] = [];
                        }
                        fields[contentid][modul].push(fieldName);
                    }
                });



                seemActions.find('span').each(function () {

                    var btn = $(this);
                    if (btn.hasClass('edit')) {
                        btn.on('click', function () {

                            var stop = false;

                            if (self.isDirty)
                            {
                                Check = confirm(cmslang.form_dirty);
                                if (Check != true)
                                {
                                    stop = true;
                                }


                            }

                            if (!stop)
                            {
                                // send Rollback only if is dirty and content editing mode
                                if ($('body').attr('rollback') && self.isDirty)
                                {
                                    self.sendDokumentRollback();
                                }

                                // Send unlock dokument??? hmmm

                                self.addLoading('Hole daten...');

                                if (self.isDirty)
                                {
                                    self.removeDirty();
                                }


                                self.controlBar.find('.active').removeClass('active');


                                var btn = $(this);
                                var itm = btn.parents('.seemode-item:first');

                                $('.seemode-editing').removeClass('seemode-editing current-edit');
                                self.getBuildData(itm.attr('contentid'), itm.attr('modul'), function () {

                                    if (!self.panelEdit.is(':visible')) {
                                        $('.toggle-open', self.controlBar).removeClass('toggle-open').addClass('toggle-close').addClass('active');
                                        self.panelEdit.show();
                                    }

                                    self.bindFormEditEvents(contentid, modul);
                                    itm.addClass('seemode-editing current-edit');
                                    self.removeLoading();
                                });

                            }
                        });

                    }

                    if (btn.hasClass('delete'))
                    {
                        btn.on('click', function () {

                            var btn = $(this);
                            var itm = btn.parents('.seemode-item:first');
                            self.addLoading('löschen...');
                            $.get('admin.php?adm=' + itm.attr('modul') + '&' + itm.attr('delete').replace('%s', itm.attr('contentid')), function (data) {
                                self.removeLoading();
                                if (responseIsOk(data))
                                {
                                    self.notify('Inhalt wurde gelöscht');
                                    notifyTimeout = setTimeout(function () {
                                        self.unNotify();
                                    }, 3000);
                                    itm.stop(true).animate({height: 0, opacity: '0'}, 300, function () {
                                        $(this).remove();
                                    });
                                }
                            });
                        });
                    }

                    if (btn.hasClass('publish'))
                    {
                        btn.on('click', function () {

                            var btn = $(this);
                            var itm = btn.parents('.seemode-item:first');
                            self.addLoading('Bitte warten...');
                            if (btn.hasClass('off'))
                            {
                                btn.attr('title', 'Change Publishing to Offline');
                            }
                            else
                            {
                                btn.attr('title', 'Change Publishing to Online');
                            }


                            var params = {};
                            params['ajax'] = 1;
                            params['seemodePopup'] = 1;
                            params['authKey'] = self.authKey;
                            $.get('admin.php?adm=' + itm.attr('modul') + '&' + itm.attr('publish').replace('%s', itm.attr('contentid')), params, function (_data) {

                                self.removeLoading();
                                if (responseIsOk(_data))
                                {
                                    if (_data.msg == 1)
                                    {
                                        btn.removeClass('off');
                                        itm.addClass('online-content').removeClass('offline-content');
                                        self.notify('Inhalt wurde aktiviert');
                                        notifyTimeout = setTimeout(function () {
                                            self.unNotify();
                                        }, 3000);
                                    }
                                    else
                                    {
                                        btn.addClass('off');
                                        itm.removeClass('online-content').addClass('offline-content');
                                        self.notify('Inhalt wurde deaktiviert');
                                        notifyTimeout = setTimeout(function () {
                                            self.unNotify();
                                        }, 3000);
                                    }


                                }
                                else
                                {
                                    alert(_data.msg);
                                }
                            }, 'json');
                        });
                    }
                });
                container.append(seemActions);
                $(this).prepend(container);
            }

        });
        //  $('.seemode-button').unbind();
        // $('.seemode-button').prev().unbind();
        $('.seemode-button').click(function () {

            if (!$(this).hasClass('active'))
            {
                $(this).next().stop();
                $(this).addClass('active').next().slideToggle(200, function () {
                    var container = $(this);
                    $(this).parent().bind('mouseleave', function () {
                        $(this).unbind('mouseleave');
                        container.hide(0, function () {
                            $(this).prev().removeClass('active');
                        });
                    });
                });
            }
            else
            {
                $(this).removeClass('active').next().slideToggle(200);
                $(this).parent().unbind('mouseleave');
            }


        });
        this.actions = actions;
        this.fields = fields;
    },
    getPanelEdit: function ()
    {
        return this.panelEdit.outerHeight();
    },
    addLoading: function (message) {
        var winH = $(document).height(), winW = $(document).width();
        if (!$('#seemode-loading-page').length)
        {
            var mask = $('<div class="loding-mask" id="seemode-loading-page"></div>').css({zIndex: 99990}).hide();
            mask.appendTo($('body'));
            var maskLabel = $('<div class="loding-mask-label" id="seemode-loading-pagelabel"><span></span></div>').css({zIndex: 99991}).hide();
            maskLabel.appendTo($('body'));
            var labelHeight = 0, labelWidth = 0;
            if (message)
            {
                maskLabel.find('span').append(message);
                maskLabel.css({visible: 'hidden'}).show();
                labelHeight = maskLabel.outerHeight(true);
                labelWidth = maskLabel.outerWidth(true);
                maskLabel.hide().css({visible: ''});
            }

            var h = this.getPanelEdit();
            mask.height(parseInt(winH, 10) - h);
            mask.show();
            if (message)
            {
                maskLabel.css({left: winW / 2 - labelWidth / 2, top: h / 2 - labelHeight / 2});
                maskLabel.show();
            }
        }
        else
        {

            mask = $('#seemode-loading-page');
            maskLabel = $('#seemode-loading-pagelabel');
            maskLabel.find('span').empty().append(message);
            //   maskLabel.css({visible: 'hidden'}).show();
            labelHeight = maskLabel.outerHeight(true);
            labelWidth = maskLabel.outerWidth(true);
            //   maskLabel.hide().css({visible: ''});
            var h = this.getPanelEdit();
            mask.height(parseInt(winH, 10) - h);
            mask.show();
            if (message)
            {
                maskLabel.css({left: winW / 2 - labelWidth / 2, top: h / 2 - labelHeight / 2}).show();
            }
        }
    },
    removeLoading: function () {
        $('#seemode-loading-page,#seemode-loading-pagelabel').hide();
    },
    setDirty: function ()
    {
        if (!this.isDirty)
        {
            this.isDirty = true;
            if (!$('#seemode-dirty').length)
            {
                $('body').append($('<div id="seemode-dirty"><span>Die Seite enthält ungespeicherte Daten!</span></div>').hide());
            }
            $('#seemode-dirty').css({visible: 'hidden'}).show();
            var h = $('#seemode-dirty').outerHeight(true);
            $('#seemode-dirty').hide().css({visible: '', top: 0 - h});
            $('#seemode-dirty').show().animate({
                top: 0
            }, {
                duration: 300
            });
        }
    },
    removeDirty: function ()
    {
        this.isDirty = false;
        $('#seemode-dirty').animate({
            top: 0 - $('#seemode-dirty').outerHeight(true)
        }, {
            duration: 300,
            complete: function ()
            {
                $(this).hide();
            }
        });
    },
    notify: function (message) {
        clearTimeout(notifyTimeout);
        if (!$('#seemode_notifier').length)
        {
            $('body').append($('<div id="seemode_notifier"><div id="notifier"></div></div>').hide());
        }


        if ($('#seemode_notifier').is(':visible'))
        {
            $('#seemode_notifier').css({opacity: '1'}).hide();
        }

        $('#notifier').html(message).show();
        $('#seemode_notifier').stop(true).fadeIn(300);
    },
    unNotify: function () {
        clearTimeout(notifyTimeout);
        if ($('#seemode_notifier').is(':visible'))
        {
            $('#seemode_notifier').stop(true).show().fadeOut(900, function () {
                $('#seemode_notifier').css({opacity: '1'}).hide();
            });
        }
    },
    prepareFootnotes: function (input, footnotecontainer)
    {
        footnotecontainer.find('ul:first').empty();
        var x = 1;
        $(input).find('a').each(function () {
            var href = $(this).attr('href');
            if (href)
            {
                var label = $(this).text();
                var link = $('<a name="footnote-' + x + '" href="' + href + '" target="_blank">').append(label);
                footnotecontainer.find('ul:first').append($('<li>').append(x + '. ').append(link).append($('<small>').append('(' + href + ')')));
                var sub = $('<sup/>');
                var supLink = $('<a href="' + document.location.href + '#footnote-' + x + '">' + x + '</a> ');
                sub.append(supLink);
                sub.insertAfter($(this));
                $('<span/>').append(label).insertAfter($(this));
                $(this).remove();
                x++;
            }

        });
    },
    stripTags: function (input, allowed) {
// http://kevin.vanzonneveld.net
// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +   improved by: Luke Godfrey
// +      input by: Pul
// +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +   bugfixed by: Onno Marsman
// +      input by: Alex
// +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +      input by: Marc Palau
// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +      input by: Brett Zamir (http://brett-zamir.me)
// +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +   bugfixed by: Eric Nagel
// +      input by: Bobby Drake
// +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +   bugfixed by: Tomasz Wesolowski
// +      input by: Evertjan Garretsen
// +    revised by: Rafał Kukawski (http://blog.kukawski.pl/)
// *     example 1: strip_tags('<p>Kevin</p> <br /><b>van</b> <i>Zonneveld</i>', '<i><b>');
// *     returns 1: 'Kevin <b>van</b> <i>Zonneveld</i>'
// *     example 2: strip_tags('<p>Kevin <img src="someimage.png" onmouseover="someFunction()">van <i>Zonneveld</i></p>', '<p>');
// *     returns 2: '<p>Kevin van Zonneveld</p>'
// *     example 3: strip_tags("<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>", "<a>");
// *     returns 3: '<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>'
// *     example 4: strip_tags('1 < 5 5 > 1');
// *     returns 4: '1 < 5 5 > 1'
// *     example 5: strip_tags('1 <br/> 1');
// *     returns 5: '1  1'
// *     example 6: strip_tags('1 <br/> 1', '<br>');
// *     returns 6: '1  1'
// *     example 7: strip_tags('1 <br/> 1', '<br><br/>');
// *     returns 7: '1 <br/> 1'
        allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)

        var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
                commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
        return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
            return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
        });
    },
    /**
     * Truncate HTML string and keep tag safe.
     *
     * @method truncate
     * @param {String} string string needs to be truncated
     * @param {Number} maxLength length of truncated string
     * @param {Object} options (optional)
     * @param {Boolean} [options.keepImageTag] flag to specify if keep image tag, false by default
     * @param {Boolean|String} [options.ellipsis] omission symbol for truncated string, '...' by default
     * @return {String} truncated string
     */
    truncateHtml: function (string, maxLength, options)
    {
        var EMPTY_OBJECT = {},
                EMPTY_STRING = '',
                DEFAULT_TRUNCATE_SYMBOL = '...',
                EXCLUDE_TAGS = ['img'], // non-closed tags
                items = [], // stack for saving tags
                total = 0, // record how many characters we traced so far
                content = EMPTY_STRING, // truncated text storage
                KEY_VALUE_REGEX = '([\\w|-]+\\s*=\\s*"[^"]*"\\s*)*',
                IS_CLOSE_REGEX = '\\s*\\/?\\s*',
                CLOSE_REGEX = '\\s*\\/\\s*',
                SELF_CLOSE_REGEX = new RegExp('<\\/?\\w+\\s*' + KEY_VALUE_REGEX + CLOSE_REGEX + '>'),
                HTML_TAG_REGEX = new RegExp('<\\/?\\w+\\s*' + KEY_VALUE_REGEX + IS_CLOSE_REGEX + '>'),
                URL_REGEX = /(((ftp|https?):\/\/)[\-\w@:%_\+.~#?,&\/\/=]+)|((mailto:)?[_.\w\-]+@([\w][\w\-]+\.)+[a-zA-Z]{2,3})/g, // Simple regexp
                IMAGE_TAG_REGEX = new RegExp('<img\\s*' + KEY_VALUE_REGEX + IS_CLOSE_REGEX + '>'),
                matches = true,
                result,
                index,
                tail,
                tag,
                selfClose;
        /**
         * Remove image tag
         *
         * @private
         * @method _removeImageTag
         * @param {String} string not-yet-processed string
         * @return {String} string without image tags
         */
        function _removeImageTag (string) {
            var match = IMAGE_TAG_REGEX.exec(string),
                    index,
                    len;
            if (!match) {
                return string;
            }

            index = match.index;
            len = match[0].length;
            return string.substring(0, index) + string.substring(index + len);
        }

        /**
         * Dump all close tags and append to truncated content while reaching upperbound
         *
         * @private
         * @method _dumpCloseTag
         * @param {String[]} tags a list of tags which should be closed
         * @return {String} well-formatted html
         */
        function _dumpCloseTag (tags) {
            var html = '';
            tags.reverse().forEach(function (tag, index) {
                // dump non-excluded tags only
                if (-1 === EXCLUDE_TAGS.indexOf(tag)) {
                    html += '</' + tag + '>';
                }
            });
            return html;
        }

        /**
         * Process tag string to get pure tag name
         *
         * @private
         * @method _getTag
         * @param {String} string original html
         * @return {String} tag name
         */
        function _getTag (string) {
            var tail = string.indexOf(' ');
            // TODO:
            // we have to figure out how to handle non-well-formatted HTML case
            if (-1 === tail) {
                tail = string.indexOf('>');
                if (-1 === tail) {
                    throw new Error('HTML tag is not well-formed : ' + string);
                }
            }

            return string.substring(1, tail);
        }

        options = options || EMPTY_OBJECT;
        options.ellipsis = (undefined !== options.ellipsis) ? options.ellipsis : DEFAULT_TRUNCATE_SYMBOL;
        while (matches) {
            matches = HTML_TAG_REGEX.exec(string);
            if (!matches) {
                if (total >= maxLength) {
                    break;
                }

                matches = URL_REGEX.exec(string);
                if (!matches || matches.index >= maxLength) {
                    content += string.substring(0, maxLength - total);
                    break;
                }

                while (matches) {
                    result = matches[0];
                    index = matches.index;
                    content += string.substring(0, (index + result.length) - total);
                    string = string.substring(index + result.length);
                    matches = URL_REGEX.exec(string);
                }
                break;
            }

            result = matches[0];
            index = matches.index;
            if (total + index > maxLength) {
                // exceed given `maxLength`, dump everything to clear stack
                content += (string.substring(0, maxLength - total));
                break;
            } else {
                total += index;
                content += string.substring(0, index);
            }

            if ('/' === result[1]) {
                // move out open tag
                items.pop();
            } else {
                selfClose = SELF_CLOSE_REGEX.exec(result);
                if (!selfClose) {
                    tag = _getTag(result);
                    items.push(tag);
                }
            }

            if (selfClose) {
                content += selfClose[0];
            } else {
                content += result;
            }
            string = string.substring(index + result.length);
        }

        if (string.length > maxLength && options.ellipsis) {
            content += options.ellipsis;
        }
        content += _dumpCloseTag(items);
        if (!options.keepImageTag) {
            content = _removeImageTag(content);
        }

        return content;
    }
};
function getAuthKey () {

    var tmpAuthKey = SeemodeCookie.get('loginpermanet');
    if (tmpAuthKey !== null && tmpAuthKey !== false && tmpAuthKey != '')
    {
        authKey = tmpAuthKey;
        Cookie.set('loginpermanet', authKey);
        SeemodeCookie.set('loginpermanet', authKey);
    }

    if (authKey !== null)
    {
        return;
    }

    // check authKey
    $.post('index.php', 'cp=main&getAuthKey=1&ajax=1', function (data) {
        if (responseIsOk(data))
        {
            authKey = data.authKey;
            // authSite = data.webSite;
            Cookie.set('loginpermanet', authKey);
            SeemodeCookie.set('loginpermanet', authKey);
            var date = new Date();
            date.setTime(date.getTime() + 1209600000);
            var expires = "; expires=" + date.toGMTString();
            document.cookie = "DREAMCMS_BE_WEBSITE=" + authSite + expires + "; path=/";
        }
        else
        {
            Cookie.set('loginpermanet', '');
            SeemodeCookie.set('loginpermanet', '');
            if (typeof data.msg != 'undefined')
            {
                console.log(data.msg);
            }
        }
    }, "json");
}



function addSection (label)
{
    return $('<div>').addClass('section').append(label);
}

function addSeperator (label)
{
    return $('<div>').addClass('seperator');
}


function closeSeemodeIframe ()
{



    $('#seemode-iframe-container').animate({top: (0 - $('#seemode-iframe-container').outerHeight(true))}, 300, function () {

        $('#seemode-iframe-container').hide();
        $('#seemode-iframe').unbind('load');
        $('html,body').css({overflow: ''});
        Cookie.erase('isSeemodePopup');
        SeemodeCookie.erase('isSeemodePopup');
    });
}

var iframeInterval;
function loadIframeInterval (doc, iframWin, url, icon, name) {

    //console.log([iframWin.Desktop]);

    if (typeof iframWin.Desktop != 'undefined' && iframWin.Desktop.loaded)
    {

        clearInterval(iframeInterval);
        var posTop = $(document).scrollTop();
        /*
         if (!$('#seemode-iframe-container:visible'))
         {
         $('#seemode-iframe-container').css({top: 0, height: 0, display: 'block'}).show();
         setTimeout(function() {
         $('#seemode-iframe-container').animate({height: $('#seemode-iframe-container').parent().height()}, 300, function() {
         $('html,body').css({overflow: 'hidden'});
         });
         }, 50);
         
         }
         */

        if (typeof iframWin.openTab == 'function') {
            setTimeout(function () {
                iframWin.openTab({url: url, obj: icon, label: name});
            }, 100);
        }


    }


}




function openSeemodeWin (url, obj, name, width, height, center, resize, scroll, posleft, postop)
{
    var showx = "";
    var showy = "";
    var X, Y;
    if (typeof posleft != 'undefined' && postop > 0) {
        X = posleft
    }
    if (typeof postop != 'undefined' && postop > 0) {
        Y = postop
    }

    if (typeof scroll == 'undefined') {
        scroll = 1
    }
    if (typeof resize == 'undefined') {
        resize = 1
    }

    if ((parseInt(navigator.appVersion) >= 4) && (center))
    {
        X = (screen.width - width) / 2;
        Y = (screen.height - height) / 2;
    }

    if (X > 0)
    {
        showx = ',left=' + X;
    }

    if (Y > 0)
    {
        showy = ',top=' + Y;
    }

    if (scroll != 0) {
        scroll = 1
    }


    if (!url.match(/\?/))
    {
        url += '?seemodePopup=1';
    }
    else
    {
        url += '&seemodePopup=1';
    }

    url = url.replace(/ˆ&(amp;)/g, '');
    var span = $(obj).find('span:first');
    var icon = null;
    if (span.length)
    {
        var img = span.css('background-image');
        img = img.replace(/\s/, '');
        img = img.replace(/url\s*\(\s*(["'])/ig, '');
        img = img.replace(/(["'])\s*\)/ig, '');
        if (img)
        {
            icon = img;
        }
    }

    SeemodeCookie.set('isSeemodePopup', true);
    clearInterval(interval);
    var iframe = $('#seemode-iframe');
    if (resize == 'yes' || resize == true || resize == 'on')
    {
        iframe.parent().resizable({
            handles: 's, e, se',
            helper: "ui-resizable-helper",
            scroll: false,
            minWidth: (width / 2 >= 100 ? width / 2 : 100),
            minHeight: (height / 2 >= 100 ? height / 2 : 100),
            resize: function () {
                $(this).hide();
            },
            stop: function () {
                $(this).show();
            }
        });
    }
    else
    {
        iframe.parent().resizable('destroy');
    }

    var $_if = iframe.get(0);
    var iframWin = $_if.contentWindow ? $_if.contentWindow : $_if.document;
    var centerPos = $(document).width() / 2 - width / 2;
    if ($('#seemode-iframe-container').is(':visible') || iframWin.Desktop)
    {
        $('#seemode-iframe-container').css({left: centerPos, top: 0, width: width, height: 0}).show();
        $('#seemode-iframe-container').animate({height: height}, {duration: 300, complete: function () {
                $('html,body').css({overflow: 'hidden'});
            }
        });
        iframe.animate({height: height}, 300);
        //  iframe.parent().css({width: width, height: height});

        var doc = $_if.contentWindow ? $_if.contentWindow.document : ($_if.contentDocument ? $_if.contentDocument : $_if.document);
        iframeInterval = setInterval(function () {
            loadIframeInterval(doc, iframWin, url, icon, name);
        }, 50);
    }
    else
    {
        // iframe.parent().css({width: width, height: height});
        $('#seemode-iframe-container').css({left: centerPos, width: width, height: 0}).show();
        iframe.attr('src', 'admin.php?seemodePopup=1&authKey=' + authKey);
        iframe.bind('load', function () {

            $(this).unbind('load');
            var $_if = this;
            var iframWin = $_if.contentWindow ? $_if.contentWindow : $_if.document;
            var doc = $_if.contentWindow ? $_if.contentWindow.document : ($_if.contentDocument ? $_if.contentDocument : $_if.document);
            iframeInterval = setInterval(function () {

                loadIframeInterval(doc, iframWin, url, icon, name);
            }, 50);
        });
        $('#seemode-iframe-container').animate({height: height}, {duration: 300, complete: function () {
                $('html,body').css({overflow: 'hidden'});
            }
        });
        iframe.animate({height: height}, 300);
    }
    /*
     
     if (typeof opener == 'undefined' || opener == null)
     {
     opener = window.open('admin.php?seemodePopup=1&authKey=' + authKey, 'DreamCMS Seemode Backend', 'width=' + width + ',height=' + height + showx + showy + ',resizable=' + resize + ',scrollbars=' + scroll + ',location=no,locationbar=no,directories=no,status=no,menubar=yes,toolbar=yes');
     }
     
     if (typeof opener != 'undefined' || opener != null)
     {
     opener.focus();
     }
     
     
     interval = setInterval(function() {
     if (opener) {
     console.log([opener]);
     }
     if (opener && typeof opener.openTab == 'function' && typeof opener.Desktop != 'undefined' && opener.Desktop.loaded) {
     clearInterval(interval);
     
     
     console.log('Open url: ' + url);
     opener.openTab({url: url, obj: icon, label: name});
     opener = null;
     
     }
     
     }, 50);
     
     if (typeof opener.closed != 'undefined' && opener.closed == true) {
     opener = null;
     SeemodeCookie.erase('isSeemodePopup');
     Cookie.erase('isSeemodePopup');
     clearInterval(interval);
     } */

}

function seemNotify (message)
{
    clearTimeout(notifyTimeout);
    if ($('#seemode_notifier').is(':visible'))
    {
        $('#seemode_notifier').css({opacity: '1'}).hide();
    }

    $('#notifier').html(message).show();
    $('#seemode_notifier').stop(true).fadeIn(900); //.stop(true, true).effect('pulsate', {times: 3, easing: 'easeInOutBounce'}, 300, 	
}
function seemUnNotify () {


    clearTimeout(notifyTimeout);
    if ($('#seemode_notifier').is(':visible'))
    {
        $('#seemode_notifier').stop(true).show().fadeOut(900, function () {
            $('#seemode_notifier').css({opacity: '1'}).hide();
        });
    }
}

window.GetQueryString = function (q) {

    var path = "", query = "", hash = "", params;
    if (q.indexOf("#") > 0) {
        hash = q.substr(q.indexOf("#") + 1);
        q = q.substr(0, q.indexOf("#"));
    }
    if (q.indexOf("?") > 0) {
        path = q.substr(0, q.indexOf("?"));
        query = q.substr(q.indexOf("?") + 1);
        params = query.split('&');
    } else
        path = q;
    return (function (a) {
        if (a == "")
            return {};
        var b = {};
        for (var i = 0; i < a.length; ++i) {
            var p = a[i].split('=');
            if (p.length != 2)
                continue;
            b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
        }
        return b;
    })(query.split("&"));
};
function sendSeemodeAjax (url, param) {
    console.log(url);
    var path = url;
    var params = GetQueryString(url);
    params['ajax'] = 1;
    params['seemodePopup'] = 1;
    params['authKey'] = authKey;
    $.post(url, params, function (_data) {
        if (responseIsOk(_data))
        {
            if (typeof _data.msg == 'string')
            {
                seemNotify(_data.msg);
                notyfiyTimeout = setTimeout('seemUnNotify()', 3000);
            }
        }
        else
        {
            seemNotify(_data.msg);
            notyfiyTimeout = setTimeout('seemUnNotify()', 3000);
        }
    }, 'json');
}


function logOutConfirm () {

    Check = confirm("Wollen Sie sich wirklich abmelden?");
    if (Check != false)
    {
        if (opener != null && opener.closed != true) {

            opener.close();
        }
        sendSeemodeAjax('admin.php', 'adm=logout');
        Cookie.erase('isSeemodePopup');
        Cookie.erase('loginpermanet');
        SeemodeCookie.erase('isSeemodePopup');
        SeemodeCookie.erase('loginpermanet');
        setTimeout(function () {
            window.location.href = 'index.php?cp=auth&action=logout';
        }, 500);
    }
}

function getWebsiteInfo ()
{

    var attributes = {};
    attributes.title = $(document).find('title').html();
    var metaTags = $(document).find('meta');
    metaTags.each(function () {
        var name = $(this).attr('name');
        var content = $(this).attr('content');
        if (typeof name != 'undefined' && typeof content != 'undefined')
        {
            name = name.toLowerCase();
            if (name == 'description')
            {
                attributes.description = content;
            }
            else if (name == 'keywords')
            {
                attributes.keywords = content;
            }
            else if (name == 'robots')
            {
                if (content.toLowerCase().indexOf('noindex') == -1)
                {
                    attributes.searchable = true;
                }
                else
                {
                    attributes.searchable = false;
                }
            }
        }
    });
    var keywords = attributes.keywords;
    var keywordEntries = keywords.split(',');
    var siteInfoBubbleInnerHTML = '';
    for (var i = 0; i < keywordEntries.length; i++)
    {
        var keyword = keywordEntries[i].replace(/^\s+/, '').replace(/\s+$/, '');
        if (keyword != '') {
            keyword = keyword.toLowerCase();
            var keywordWeight = 0;
            var tags = document.getElementsByTagName('h1');
            for (var j = 0; j < tags.length; j++) {
                if (tags[j].innerHTML.toLowerCase().indexOf(keyword) != -1) {
                    keywordWeight += 2;
                }
            }
            var tags = document.getElementsByTagName('h2');
            for (var j = 0; j < tags.length; j++) {
                if (tags[j].innerHTML.toLowerCase().indexOf(keyword) != -1) {
                    keywordWeight += 2;
                }
            }
            var tags = document.getElementsByTagName('h3');
            for (var j = 0; j < tags.length; j++) {
                if (tags[j].innerHTML.toLowerCase().indexOf(keyword) != -1) {
                    keywordWeight += 2;
                }
            }
            var tags = document.getElementsByTagName('h4');
            for (var j = 0; j < tags.length; j++) {
                if (tags[j].innerHTML.toLowerCase().indexOf(keyword) != -1) {
                    keywordWeight += 2;
                }
            }
            var tags = document.getElementsByTagName('h5');
            for (var j = 0; j < tags.length; j++) {
                if (tags[j].innerHTML.toLowerCase().indexOf(keyword) != -1) {
                    keywordWeight += 2;
                }
            }
            var tags = document.getElementsByTagName('h6');
            for (var j = 0; j < tags.length; j++) {
                if (tags[j].innerHTML.toLowerCase().indexOf(keyword) != -1) {
                    keywordWeight += 2;
                }
            }
            var tags = document.getElementsByTagName('p');
            for (var j = 0; j < tags.length; j++) {
                if (tags[j].innerHTML.toLowerCase().indexOf(keyword) != -1) {
                    keywordWeight += 2;
                }
            }
            var tags = document.getElementsByTagName('p');
            for (var j = 0; j < tags.length; j++) {
                if (tags[j].innerHTML.toLowerCase().indexOf(keyword) != -1) {
                    keywordWeight += 1;
                }
            }
            var tags = document.getElementsByTagName('td');
            for (var j = 0; j < tags.length; j++) {
                if (tags[j].innerHTML.toLowerCase().indexOf(keyword) != -1) {
                    keywordWeight += 1;
                }
            }
            var tags = document.getElementsByTagName('span');
            for (var j = 0; j < tags.length; j++) {
                if (tags[j].innerHTML.toLowerCase().indexOf(keyword) != -1) {
                    keywordWeight += 1;
                }
            }
            var tags = document.getElementsByTagName('a');
            for (var j = 0; j < tags.length; j++) {
                if (tags[j].innerHTML.toLowerCase().indexOf(keyword) != -1) {
                    keywordWeight += 1;
                }
            }
            var tags = document.getElementsByTagName('li');
            for (var j = 0; j < tags.length; j++) {
                if (tags[j].innerHTML.toLowerCase().indexOf(keyword) != -1) {
                    keywordWeight += 1;
                }
            }

            // keyword = $(keyword).trim();

            if (keywordWeight == 0) {
                siteInfoBubbleInnerHTML += (i != 0 ? ', ' : '') + '<span class="nf">' + keyword + '</span>';
            }
            else {
                siteInfoBubbleInnerHTML += (i != 0 ? ', ' : '') + '<span class="bf">' + keyword + '</span>';
            }
        }
    }

    attributes.keywords_analyse = siteInfoBubbleInnerHTML;
    return attributes;
}


function getGoogleKeyword (keyword)
{
    window.open('http://www.google.de/search?q=' + keyword, 'google', 'scrollbars=yes');
}





/*
 * Generate See Mode for Frontend
 */
$(document).ready(function () {
    $('head').append('<link rel="stylesheet" href="' + cmsurl + '/html/css/seemode.css" type="text/css"/>');
    return;
    //SeemodeEdit.init();


    $('.seemode-button').unbind();
    $('.seemode-button').prev().unbind();
    $('.seemode-button').click(function () {

        var self = $(this);
        if (!$(this).hasClass('active'))
        {
            $(this).next().stop();
            $(this).addClass('active').next().slideToggle(200, function () {
                var container = $(this);
                $(this).parent().bind('mouseleave', function () {
                    $(this).unbind('mouseleave');
                    container.hide(0, function () {
                        $(this).prev().removeClass('active');
                    });
                });
            });
        }
        else
        {
            $(this).removeClass('active').next().slideToggle(200);
            $(this).parent().unbind('mouseleave');
        }


    });
    /*
     $('.seemode-actions').find('[act]').each(function() {
     $(this).unbind().click(function(e) {
     
     var action = $(this).attr('act').replace('%s', $(this).parent().attr('contentid'));
     action = action.replace(/^&(amp;)/, '');
     var url = 'admin.php?adm=' + $(this).parent().attr('controller') + '&' + action;
     $(this).parent().hide(0, function() {
     $(this).prev().removeClass('active');
     });
     if ($(this).hasClass('edit'))
     {
     openSeemodeWin(url, $(this), 'Edit', 1024, 700, true);
     }
     else if ($(this).hasClass('delete'))
     {
     sendSeemodeAjax(url);
     }
     else if ($(this).hasClass('publish'))
     {
     if ($(this).hasClass('offline'))
     {
     $(this).addClass('off');
     }
     
     var container;
     if (typeof $(this).attr('container') != 'string')
     {
     
     if ($(this).parents().find('.online-content,.offline-content').length)
     {
     container = $(this).parents().find('.online-content,.offline-content');
     }
     else
     {
     
     container = $(this).parents('article:first');
     }
     }
     else
     {
     if ($(this).attr('container') == 'parent')
     {
     if ($(this).parents().find('.online-content,.offline-content').length)
     {
     container = $(this).parents().find('.online-content,.offline-content');
     }
     else
     {
     container = $(this).parents('article:first');
     }
     }
     else
     {
     container = $(this).parents('article:first');
     }
     }
     
     
     var btn = $(this), params = {};
     params['ajax'] = 1;
     params['seemodePopup'] = 1;
     params['authKey'] = authKey;
     $.get(url, params, function(_data) {
     if (responseIsOk(_data))
     {
     if (_data.msg == 1)
     {
     btn.removeClass('off');
     container.addClass('online-content').removeClass('offline-content');
     seemNotify('Inhalt wurde aktiviert');
     notifyTimeout = setTimeout('seemUnNotify()', 3000);
     }
     else
     {
     btn.addClass('off');
     container.removeClass('online-content').addClass('offline-content');
     seemNotify('Inhalt wurde deaktiviert');
     notifyTimeout = setTimeout('seemUnNotify()', 3000);
     }
     }
     else
     {
     alert(_data.msg);
     }
     }, 'json');
     }
     
     });
     });
     
     */
    if ($('#seemode-iframe').length == 0)
    {
        $('body').append(
                $('<div>').attr('id', 'seemode-iframe-container').append(
                $('<div>').attr('id', 'seemode-iframe-placeholder').append(
                $('<iframe/>').attr('id', 'seemode-iframe').addClass('seemode-edit-iframe')
                ).append('<span class="close-seemode-iframe-container">Close</span>')
                ).hide());
        $('#seemode-iframe-container .close-seemode-iframe-container').click(function () {
            var $_if = $('#seemode-iframe');
            var iframWin = $_if.contentWindow ? $_if.contentWindow : $_if.document;
            var doc = $_if.contentWindow ? $_if.contentWindow.document : ($_if.contentDocument ? $_if.contentDocument : $_if.document);
            if ($('#seemode-iframe').contents() && $('#seemode-iframe').contents().find('.isWindowContainer').length)
            {
                $('#seemode-iframe').contents().find('.isWindowContainer .win-close-btn').trigger('click');
            }

            closeSeemodeIframe(this);
        });
    }


    if ($('#seemode_panel').length == 0)
    {

        var seemNotifier = $('<div>').attr('id', 'seemode_notifier');
        seemNotifier.append($('<span>').attr('id', 'notifier'));
        $("body").append(seemNotifier);
        var seemdiv = $('<div>').attr('id', 'seemode_panel');
        var seemode_buttons_shadow = $('<div>').attr('id', 'seemode_buttons_shadow');
        seemdiv.append(seemode_buttons_shadow);
        $("body").prepend(seemdiv);
        var seemode_buttons = $('<div>').attr('id', 'seemode_buttons');
        var seemode_dirty = $('<div>').attr('id', 'seemode_dirty');
        var seemode_notifier = $('<div>').attr({
            'id': 'seemode_notifier',
            'title': 'Seemode Verlassen'
        });
        seemdiv.mouseover(function () {
            $(seemode_buttons_shadow).stop().animate({duration: 300, width: 215 + 'px'})
        });
        seemode_buttons_shadow.mouseleave(function () {
            $(seemode_buttons_shadow).stop().animate({duration: 300, width: 25 + 'px'})
        });
        seemdiv.mouseleave(function () {
            $(seemode_buttons_shadow).stop().animate({duration: 300, width: 25 + 'px'})
        });
        var cfgBtn = $('<span>').addClass('seembtn').append($('<span>').addClass('cfg').append('Konfiguration'));
        var layoutBtn = $('<span>').addClass('seembtn').append($('<span>').addClass('layout').append('Layout Verwaltung'));
        layoutBtn.click(function (e) {
            openSeemodeWin('admin.php?adm=layouter', this, $(this).text(), 1024, 768, true);
        });
        cfgBtn.click(function (e) {
            openSeemodeWin('admin.php?adm=settings', this, $(this).text(), 1024, 768, true);
        });
        seemode_buttons.append(addSection('Allgemeines'));
        seemode_buttons.append(cfgBtn);
        seemode_buttons.append(layoutBtn);
        var backupBtn = $('<span>').addClass('seembtn').append($('<span>').addClass('backup').append('Backup anlegen'));
        backupBtn.click(function (e) {
            openSeemodeWin('admin.php?adm=backup&action=create', this, $(this).text(), 800, 600, true);
        });
        var clearCacheBtn = $('<span>').addClass('seembtn').append($('<span>').addClass('clearcache').append('Cache Leeren'));
        clearCacheBtn.click(function (e) {
            sendSeemodeAjax('admin.php?adm=cache&action=clear');
        });
        var clearCacheFullBtn = $('<span>').addClass('seembtn').append($('<span>').addClass('clearcachefull').append('Cache Komplett entleeren'));
        clearCacheFullBtn.click(function (e) {
            sendSeemodeAjax('admin.php?adm=cache&action=clearfull');
        });
        var usersBtn = $('<span>').addClass('seembtn').append($('<span>').addClass('users').append('Benutzerverwaltung'));
        usersBtn.click(function (e) {
            openSeemodeWin('admin.php?adm=user', this, $(this).text(), 1024, 768, true);
        });
        seemode_buttons.append(addSeperator());
        seemode_buttons.append(addSection('System Verwaltung'));
        seemode_buttons.append(usersBtn);
        seemode_buttons.append(clearCacheBtn);
        seemode_buttons.append(clearCacheFullBtn);
        seemode_buttons.append(backupBtn);
        seemode_buttons.append(addSeperator());
        seemode_buttons.append(addSection('Inhalte Verwaltung'));
        var newsBtn = $('<span>').addClass('seembtn').append($('<span>').addClass('news').append('Nachrichten Verwalten'));
        var staticBtn = $('<span>').addClass('seembtn').append($('<span>').addClass('static').append('Statische Seiten'));
        var assetsBtn = $('<span>').addClass('seembtn').append($('<span>').addClass('assets').append('Asset Verwaltung'));
        var mediaBtn = $('<span>').addClass('seembtn').append($('<span>').addClass('media').append('Medien Verwaltung'));
        var menuesBtn = $('<span>').addClass('seembtn').append($('<span>').addClass('menues').append('Navigations Verwaltung'));
        menuesBtn.click(function (e) {
            openSeemodeWin('admin.php?adm=menues', this, $(this).text(), 1024, 768, true);
        });
        assetsBtn.click(function (e) {
            openSeemodeWin('admin.php?adm=asset', this, $(this).text(), 1024, 768, true);
        });
        mediaBtn.click(function (e) {
            openSeemodeWin('admin.php?adm=media', this, $(this).text(), 1024, 768, true);
        });
        newsBtn.click(function (e) {
            openSeemodeWin('admin.php?adm=news', this, $(this).text(), 1024, 768, true);
        });
        staticBtn.click(function (e) {
            openSeemodeWin('admin.php?adm=page', this, $(this).text(), 1024, 768, true);
        });
        seemode_buttons.append(newsBtn);
        seemode_buttons.append(staticBtn);
        seemode_buttons.append(mediaBtn);
        seemode_buttons.append(assetsBtn);
        seemode_buttons.append(menuesBtn);
        if ($('.seemaction').length > 0)
        {

            seemode_buttons.append(addSeperator());
            seemode_buttons.append(addSection('Inhalt'));
            var publishCurContentBtn = $('<span>').addClass('seembtn').append($('<span>').addClass('publish').append('Inhalt aktivieren/deaktivieren'));
            var editCurContentBtn = $('<span>').addClass('seembtn').append($('<span>').addClass('edit').append('Inhalt bearbeiten'));
            var delCurContentBtn = $('<span>').addClass('seembtn').append($('<span>').addClass('delete').append('Inhalt löschen'));
            $('.seemaction').each(function () {
                var seemaction = $(this);
                var url = $(this).text();
                url = url.trim();
                if (url)
                {
                    if (!url.match(/^admin.php/))
                    {
                        url = 'admin.php?' + url;
                    }

                    if ($(this).hasClass('edit'))
                    {
                        editCurContentBtn.click(function (e) {
                            openSeemodeWin(url, editCurContentBtn, $(editCurContentBtn).text(), 1300, 768, true);
                        });
                        seemode_buttons.append(editCurContentBtn);
                    }
                    else if ($(this).hasClass('delete'))
                    {
                        delCurContentBtn.click(function (e) {
                            sendSeemodeAjax(url);
                        });
                        seemode_buttons.append(delCurContentBtn);
                    }
                    else if ($(this).hasClass('publish'))
                    {
                        if ($(this).hasClass('offline'))
                        {
                            publishCurContentBtn.find('span').addClass('off');
                        }



                        publishCurContentBtn.click(function (e) {

                            var btn = $(this).find('span'), params = {};
                            params['ajax'] = 1;
                            params['seemodePopup'] = 1;
                            params['authKey'] = authKey;
                            $.get(url, params, function (_data) {
                                if (responseIsOk(_data))
                                {
                                    if (_data.msg == 1)
                                    {
                                        btn.removeClass('off');
                                        $('.offline-content').addClass('online-content').removeClass('offline-content');
                                        seemNotify('Inhalt wurde aktiviert');
                                        notifyTimeout = setTimeout('seemUnNotify()', 3000);
                                    }
                                    else
                                    {
                                        btn.addClass('off');
                                        $('.online-content').removeClass('online-content').addClass('offline-content');
                                        seemNotify('Inhalt wurde deaktiviert');
                                        notifyTimeout = setTimeout('seemUnNotify()', 3000);
                                    }
                                }
                                else
                                {
                                    alert(_data.msg);
                                }
                            }, 'json');
                        });
                        seemode_buttons.append(publishCurContentBtn);
                    }
                }
            });
        }





        if (typeof logClick == 'function')
        {
            seemode_buttons.append(addSeperator());
            seemode_buttons.append(addSection('Klick-Analyse'));
            var clickAnalyserBtn = $('<span>').addClass('seembtn clickanalyserbtn').append($('<span>').addClass('clickanalyser').append('Klick-Analyse einblenden'));
            clickAnalyserBtn.click(function (e) {

                clickAnalyserOn = (clickAnalyserOn ? false : true);
                if (clickAnalyserOn) {

                    if (typeof analyseClicks != 'function')
                    {
                        $.getScript(systemUrl + '/html/js/seemode.analyseclicks.js?_' + cookiePrefix, function () {
                            analyseClicks();
                        });
                    }
                    else
                    {
                        analyseClicks();
                    }

                    $(this).children('span').addClass('on').text('Klick-Analyse ausblenden');
                }
                else
                {
                    $(this).children('span').removeClass('on').text('Klick-Analyse einblenden');
                    $(document).find('.analysePoint').remove();
                }
            });
            seemode_buttons.append(clickAnalyserBtn);
            var clickAnalyserDelBtn = $('<span>').addClass('seembtn clickanalyserbtn').append($('<span>').addClass('delete').append('Klick-Analyse leeren'));
            clickAnalyserDelBtn.click(function (e) {
                sendSeemodeAjax('admin.php?adm=clickanalyser&action=clearanalayse&url=' + base64_encode(document.location.href));
                if (clickAnalyserOn) {
                    clickAnalyserBtn.click();
                }
            });
            seemode_buttons.append(clickAnalyserDelBtn);
        }



        seemode_buttons.append(addSeperator());
        seemode_buttons.append(addSection('Sonstiges'));
        var debugSwithBtn = $('<span>').addClass('seembtn debugbtn').append($('<span>').addClass('debug').append('Debugger an-/auschalten'));
        debugSwithBtn.click(function (e) {
            sendSeemodeAjax('admin.php?adm=dashboard&action=switchdebug');
        });
        seemode_buttons.append(debugSwithBtn);
        var logoutBtn = $('<span>').addClass('seembtn logoutbtn').append($('<span>').addClass('logout').append('Abmelden'));
        logoutBtn.click(function (e) {
            logOutConfirm();
        });
        seemode_buttons.append(logoutBtn);
        seemode_buttons_shadow.append(seemode_buttons);
        $("body").prepend(seemode_dirty);
        // Fix page Layout
        seemdiv.next().css({
            'padding-top': $("#seemode_header").height() + 'px'
        });
        var attributes = getWebsiteInfo();
        var infoboxToggle = $('<div id="docinfos-toggle">');
        var infobox = $('<div>').attr('id', 'docinfos');
        var infoboxcontent = $('<div>').attr('id', 'docinfos-content');
        infoboxToggle.click(function () {
            infoboxcontent.toggle(1, function () {
                if ($(this).is(':visible'))
                {
                    $(infoboxToggle).addClass('open');
                }
                else
                {
                    $(infoboxToggle).removeClass('open');
                }
            });
        });
        infobox.append(infoboxToggle);
        infoboxcontent.append('<div><span>Titel:</span> ' + attributes.title + '</div>');
        infoboxcontent.append('<div><span class="description">Beschreibung:</span> ' + attributes.description + '</div>');
        infoboxcontent.append('<div><span>Durchsuchbar:</span> ' + attributes.searchable + '</div>');
        infoboxcontent.append('<div><span class="keywords">Keywords:</span> ' + attributes.keywords_analyse + '</div>');
        infobox.append(infoboxcontent);
        infoboxcontent.find('span.bf,span.nf').click(function () {
            getGoogleKeyword($(this).text());
        });
        $('body').append(infobox);
        $('#seemode_panel .seembtn.debugbtn').css({bottom: 54});
        $('#seemode_panel .seembtn.logoutbtn').css({bottom: 40});
        var pagemask = $('<div>').attr('id', 'seem-page-mask');
        getAuthKey();
    }
    /*
     // Find Seemode buttons
     var btns = $('body').find('.seemode_btn');
     
     btns.each(function() {
     var contentid = $(this).attr('id').replace(/[a-zA-Z_\-]+([\d]*)/g, '$1');
     var contenttype = $(this).attr('class').replace(/\s*seemode_btn\s* /g, '');
     if (contentid) $(this).addSeemodeButton(contenttype, contentid);
     });
     */

});
function seemodeRegisterForm ()
{
    $('#seemInner form').find('select,input,textarea').on('change', function (e) {
        liveSetDirty(e);
    });
    $('#seemInner form').find('input,textarea').on('keydown', function (e) {
        liveSetDirty(e);
    });
}

function seemodeResetForm ()
{
    seemode_reset();
    $('#seemInner form').get(0).reset();
}


function seemodeChangeData (contentid, mode, submode)
{

    $.get('admin.php', serialized, function (data) {

        if (responseIsOk(data)) {

            $('#seemode_dirty').html(data.msg);
            if (data.html != '')
            {
                $('#seem-' + mode + '-' + contentid).replaceWith(data.html);
            }

            $('#seemode_dirty').effect('pulsate', {
                times: 3,
                easing: 'easeInOutBounce'
            }, 500);
            setTimeout('seemode_reset()', 4000);
        }
        else
        {
            jAlert(data.msg);
        }
    }, 'json');
}





function seemodeFormSend (contentid, mode, submode)
{
    var serialized = $('#seemInner form').serialize();
    serialized += '&doSaveSeemode=1';
    serialized += '&mode=' + mode;
    $.post('admin.php', serialized, function (data) {


        if (responseIsOk(data)) {
            $('#seemode_dirty').html(data.msg);
            if (data.html != '')
            {
                $('#seem-' + mode + '-' + contentid).replaceWith(data.html);
            }

            $('#seemode_dirty').effect('pulsate', {
                times: 3,
                easing: 'easeInOutBounce'
            }, 500);
            setTimeout(' if (  $("#seemode_dirty").html() != "" ) { seemode_reset(); }', 4000);
        }
        else
        {
            jAlert(data.msg);
        }

    }, 'json');
}




function buildSeemode ()
{
    $('<div id=""/>');
}





