/**
 * Created by marcel on 17.05.14.
 */


/**
 *      no longer used!
 */
var TinyCallback = {
    cleanXHTML: function (f, d) {
        var e = "";
        var b = d.match(/<a[^>]*>/gi);
        if (b != null) {
            for (var c = 0; c < b.length; c++) {
                e = b[c].replace(/target="_blank"/gi, 'onclick="window.open(this.href); return false;"');
                d = d.replace(b[c], e)
            }
        }
        return d.replace(/<br>/, "<br/>")
    },
    cleanHTML: function (b, a) {
        a = a.replace(/<br\s*\/?>/i, "<br/>");
        a = a.replace(/^\s*/ig, "");
        a = a.replace(/\s*$/ig, "");
        return a;
    },
    handleEventCallback: function (e) {
        if (typeof Config.get('TinyMCE_HandleEventCallback') === 'function') {
            clearTimeout(tinymceT);
            tinymceT = setTimeout(function () {
                Config.get('TinyMCE_HandleEventCallback')(e);
            }, 250);
        }
    },
    nodeChangeHandler: function (editor_id, node, undo_index, undo_levels, visual_aid, any_selection) {

    },
    onChangeHandler: function (inst) {
        /*
         if (inst.isDirty())
         {
         Form.setDirty(false, $('#' + inst.editorId).parents('form:first'));
         }
         else {
         Form.resetDirty($('#' + inst.editorId).parents('form:first'));
         }
         */
        var triggerFunction = Config.get('onTinyMceChangeHandler', false);
        if (typeof triggerFunction === 'function') {
            triggerFunction(inst);
        }
    }
};

// the new form with autosave function
var Form = {
    reg: [],
    defaults: {
        autosave: 0
    },
    registerForm: function (fid, options) {
        if (!Win.windowID) {
            return;
        }

        var hash = fid + '-' + Win.windowID.replace('content-', '');
        if ($('#' + fid).hasClass('form-registred') ) {

            return;
        }

        var opt = $.extend({}, this.defaults, options);
        var form_Instance = new FormInstance(fid, opt, Win.windowID);

        form_Instance.init();
        this.reg[hash] = form_Instance;
    },

    bindChangeEvents: function(fid, winID) {
        var hash = fid + '-' + winID.replace('content-', '');
        if (typeof this.reg[hash] == 'undefined') {
            return;
        }

        this.reg[hash].bindChangeEvents();
        this.reg[hash].registerAutosave(true);

    },

    registerAutosave: function (fid, winid) {
        var hash = fid + '-' + winid.replace('content-', '');
        if (typeof this.reg[hash] == 'undefined') {
            return;
        }
        this.reg[hash].registerAutosave();
    },

    save: function (event, exit, isdraft, fid, winid) {
        var hash = fid + '-' + winid.replace('content-', '');
        if (typeof this.reg[hash] == 'undefined') {
            return;
        }

        this.reg[hash].save(event, exit, isdraft, fid, winid);
    },

    saveCallBack: function(event, data, exit, isdraft, fid, winid, postData) {
        var hash = fid + '-' + winid.replace('content-', '');
        if (typeof this.reg[hash] == 'undefined') {
            return;
        }

        this.reg[hash].formSaveCallBack(event, exit, isdraft, fid, winid, postData);
    },

    resetDirty: function(formobj)
    {
        if (typeof formobj == 'object') {
            fid = formobj.attr('id');
            winid = formobj.data('formConfig').windowID;
            if (fid && winid) {
                var hash = fid + '-' + winid.replace('content-', '');
                if (typeof this.reg[hash] == 'undefined') {
                    return;
                }

                this.reg[hash].resetDirty();
            }
        }

    },
    makeDirty: function (fid, winID)
    {

        var hash = fid + '-' + winID.replace('content-', '');
        if (typeof this.reg[hash] == 'undefined') {
            if ($('#' + fid, $('#' + winID)).length == 1) {
                if (!$('#' + fid, $('#' + winID)).data('formConfig')) {
                    $('#' + fid, $('#' + winID)).data('formConfig', {
                        isDirty: false
                    });
                }

                var cfg = $('#' + fid, $('#' + winID)).data('formConfig');
                cfg = $.extend(cfg, {
                    isDirty: true
                });

                if (!Config.get('isSeemode')) {
                    if (Desktop.isWindowSkin) {
                        $('#' + winID).data('formConfig', cfg);
                        $('#' + winID).find('.win-title').find('sup').remove();
                        $('#' + winID).find('.win-title').addClass('dirty').append(' <sup>*</sup>');
                    }
                    else {
                        Core.setDirty(true);
                    }
                }
                else {
                    SeemodeEdit.setDirty();
                }
            }
        }
        else {
            this.reg[hash].makeDirty();
        }
    },
    makeReset: function (fid, winID) {

        var hash = fid + '-' + winID.replace('content-', '');
        if (typeof this.reg[hash] == 'undefined') {
            if ($('#' + fid, $('#' + winID)).length == 1) {
                if (!$('#' + fid, $('#' + winID)).data('formConfig')) {
                    $('#' + fid, $('#' + winID)).data('formConfig', {
                        isDirty: false
                    });
                }

                var cfg = $('#' + fid, $('#' + winID)).data('formConfig');
                cfg = $.extend(cfg, {
                    isDirty: false
                });


                if (!Config.get('isSeemode')) {
                    if (Desktop.isWindowSkin) {
                        $('#' + winID).data('formConfig', cfg);
                        $('#' + winID).find('.win-title').find('sup').remove();
                        $('#' + winID).find('.win-title').removeClass('dirty');
                    }
                    else {
                        Core.resetDirty(true);
                    }
                }
                else {
                    SeemodeEdit.removeDirty();
                }

            }
        }
        else {
            this.reg[hash].makeReset();
        }
    },


    setDirty: function (event, form, winid) {
        this.liveSetDirty(event, form, form.attr('id'), winid);
    },

    liveSetDirty: function (e, form, fid, winid) {

        if (!fid && typeof form == 'object') {
            fid = form.attr('id');
        }

        var hash = fid + '-' + winid.replace('content-', '');
        if (typeof this.reg[hash] == 'undefined') {
            return;
        }

        this.reg[hash].liveSetDirty(e);
    },

    setContentLockAction: function (action, fid, winid) {
        var hash = fid + '-' + winid.replace('content-', '');

        if (typeof this.reg[hash] == 'undefined') {
            return;
        }

        this.reg[hash].setConfig('contentlockaction', action);

    },

    destroy: function (formObj) {
        var cfg = formObj.data('formConfig');
        if (cfg) {
            if (!cfg.windowID) {
                return;
            }

            var hash = cfg._formid + '-' + cfg.windowID.replace('content-', '');
            if (typeof this.reg[hash] == 'undefined') {
                return;
            }

            this.reg[hash].destroy();

            delete this.reg[hash];

            formObj.removeClass('form-registred');
        }
    },

};

//
function updateTextareaFields(formobj) {
    if (typeof tinymce != 'undefined' && typeof formobj == 'object') {
        for (var i = 0; i < tinymce.editors.length; i++) {
            if (typeof tinymce.editors[i] != 'undefined') {
                if (tinymce.editors[i].id) {
                    if ($('#' + tinymce.editors[i].id.replace('inline-', ''), formobj).length == 1) {
                        var val = tinymce.editors[i].getContent();
                        val = val.replace(/<p>\s*<\/p>/g, '');

                        $('#' + tinymce.editors[i].id.replace('inline-', ''), formobj).val(val);
                    }
                }
            }
        }
    }
}

//
function FormInstance(form_ID, options, window_id) {
    "use strict";

    var allowedButtonClasses = ['save', 'save_exit', 'cancel', 'reset', 'draft', 'run', 'run_exit'];
    var windowID = window_id, formID = form_ID, config = {}, defaults = {
            url: 'admin.php',
            exiturl: '',
            contentTable: '',
            useContentTags: false,
            focus_first: false,
            isDirty: false,
            autosave: false, // use numbers for save delay
            onAfterSubmit: false, // external event
            onBeforeSerialize: false,
            onBeforeSubmit: false, // external event
            runAfterSubmit: false, // external event
            // prepare other form data
            onBeforeSend: false, // external event
            onReset: false, //
            contentIdentifierID: 'content-id', // record ID
            identifierType: '',
            baseField: '',
            formid: null,
            rebuildIdentifier: function (type) {
                this.rebuildPageIdentifier(config.baseField, type, $('#' + config.contentIdentifierID).val());
            },
            error: function (data) {
                if (data.msg !== '') {
                    this.setFormStatus(data.msg);
                }
                else {
                    this.setFormStatus(cmslang.formsave_error);
                }
            }
        },
        $_window, $_form, self = this;


    config = $.extend({}, defaults, options);

    $_form = $('#' + formID);

    if (config.isPopup) {
        $_window = $('#' + formID).parents('.popup:first');
    }
    else {
        $_window = $('#' + windowID);
    }

    this.setConfig = function (k, v) {
        config[k] = v;

        $($_window).data('formConfig', config);
        $($_form).data('formConfig', config);
    };

    this.init = function () {


        if (Config.get('isSeemode')) {
            //$_window.attr('id', 'seemode-win');
        }

        config.url = config.url.replace(Config.get('portalurl') + '/', '');
        config.url = Config.get('portalurl') + '/' + config.url;
        config._formid = formID;
        config.windowID = windowID;

        // set the form config
        $_window.data('formConfig', config);
        $_window.data('formID', formID);

        //
        $('#' + formID).data('windowID', windowID);
        $('#' + formID).data('formConfig', config);


        // add dirty event
        this.bindChangeEvents();

        if (config.useContentTags) {
            this.registerContentTags();
        }


        this.bindButtons();

        if ( !Desktop.isWindowSkin && !Config.get('isSeemode') ) {
            Core.resetDirty(true);
        }

        // set the form config
        $_window.data('formConfig', config);
        $_window.data('formID', formID);

        //
        $('#' + formID).data('windowID', windowID);
        $('#' + formID).data('formConfig', config);

        $('#' + formID).addClass('form-registred');
    };

    this.destroy = function () {
        var cfg = $_form.data('formConfig');

        if (cfg) {
            if (cfg.autoSaveInstance) {
                cfg.autoSaveInstance.destroy();
            }

            Core.removeShortcutHelp('Alt+R', true);
            Core.removeShortcutHelp('Alt+S', true);
            Core.removeShortcutHelp('Ctrl+Alt+E', true);
            Core.removeShortcutHelp('Alt+C', true);

            $_form.unbind();
        }

        if (formID) {
            $(document).unbind('keydown.form' + formID);
            $('input.content-tags', $_form).unbind();
        }
    };

    this.bindChangeEvents = function() {
        // add dirty event
        $_form.find('input,select,textarea').each(function () {
            var el = $(this);
            if (( (el.is('input') && el.attr('type') != 'checkbox' && el.attr('type') != 'radio') || el.is('textarea')) && !el.hasClass('nodirty')) {
                el.unbind('keyup.form').bind('keyup.form', function (e) {

                    // restart autosave delay
                    if (config.autoSaveInstance) {
                        config.autoSaveInstance.restart();
                    }

                    self.validation($(this));
                    self.liveSetDirty(e, $_form, formID, windowID);
                });
            }
            else if ((el.is('select') || (el.is('input') && el.attr('type') == 'checkbox' || el.attr('type') == 'radio')) && /*!$(this).parents('#document-metadata').length && */ !el.hasClass('nodirty')) {
                el.unbind('change.form').bind('change.form', function (e) {
                    //e.preventDefault();
                    // self.validation($(this));
                    self.liveSetDirty(e, $_form, formID, windowID);
                });
            }
        });
    };

    this.registerAutosave = function (reset) {
        if (config.autosave > 0) {
            if (typeof config.contentIdentifierID == 'string' && config.contentIdentifierID != '') {

                if (reset && config.autoSaveInstance) {
                    config.autoSaveInstance.destroy();
                }

                var autosave = new Autosave(formID, windowID, {
                    delay: config.autosave,
                    idfieldname: config.contentIdentifierID,
                    postid: $('#' + windowID).find('#' + config.contentIdentifierID).val()
                });

                config.autoSaveInstance = autosave;

                $_form.data('formConfig', config);
                $_window.data('formConfig', config);


                autosave.start();

            }
            else {
                Debug.log('The Form "' + formID + '" could not use autosave!');
            }
        }
    };


    this.bindButtons = function () {

        var toolbar = (Config.get('isSeemode') ? $('#seemode-header') : Core.getToolbar());

        if (typeof config.useToolbar == 'object') {
            toolbar = config.useToolbar;
        }

        if (toolbar && toolbar.find('button').length > 0) {

            var fid = formID, winID = windowID;


            toolbar.find('button').each(function () {
                if (!$(this).parents('#VersioningForm').length) {

                    var buttonClassName = $(this).attr('class');
                    if (buttonClassName) {
                        $(this).unbind('click.form');
                        buttonClassName = buttonClassName.replace(/.*\s?(reset|run|run_exit|save|save_exit|cancel|draft)\s.*/g, '$1');
                        //console.log('Button buttonClassName:' + buttonClassName);

                        var rel = $(this).attr('rel');
                        //console.log('Button rel:' + rel);

                        if (rel == formID) {
                            // set the button data first
                            $(this).data('formID', fid).data('windowID', winID);

                            var button = $(this);

                            // now register the events
                            switch (buttonClassName) {
                                case 'reset':


                                    button.unbind('click.form').bind('click.form', function (e) {
                                        e.preventDefault();

                                        // self.formID = $( this ).data( 'formID' );
                                        // self.windowID = $( this ).data( 'windowID' );
                                        self.resetForm(e);
                                    });

                                    Core.addShortcutHelp('Alt+R', 'Reset Dokument', true);
                                    $(document).bind('keydown.form' + fid, function (e) {
                                        var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
                                        if (e.altKey && char == 'r') {
                                            button.trigger('click.form');
                                        }
                                    });

                                    break;

                                case 'run':

                                    Core.addShortcutHelp('Alt+S', 'Execute', true);
                                    $(document).bind('keydown.form' + fid, function (e) {
                                        var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
                                        if (e.altKey && char == 's') {
                                            button.trigger('click.form');
                                        }
                                    });


                                    button.unbind('click.form').bind('click.form', function (e) {
                                        e.preventDefault();

                                        // self.formID = $( this ).data( 'formID' );
                                        // self.windowID = $( this ).data( 'windowID' );


                                        if (config.autoSaveInstance) {
                                            config.autoSaveInstance.stop();
                                        }


                                        self.save(e, false, false, fid, winID);
                                    });


                                    break;
                                case 'run_exit':

                                    Core.addShortcutHelp('Ctrl+Alt+E', 'Run and Exit the Document', true);
                                    $(document).bind('keydown.form' + fid, function (e) {
                                        var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
                                        if (e.altKey && e.ctrlKey && char == 'e') {
                                            button.trigger('click.form');
                                        }
                                    });

                                    button.unbind('click.form').bind('click.form', function (e) {
                                        e.preventDefault();

                                        //self.formID = $( this ).data( 'formID' );
                                        //self.windowID = $( this ).data( 'windowID' );
                                        if (config.autoSaveInstance) {
                                            config.autoSaveInstance.stop();
                                        }

                                        if (!$(this).parents('.switch-content-window').length) {

                                            self.save(e, true, false, fid, winID);
                                        }
                                        else {
                                            self.save(e, false, false, fid, winID);
                                            if (!Config.get('isSeemode')) {
                                                var _self = this;
                                                setTimeout(function () {
                                                    $(_self).parents('.switch-content-window').data('WindowManager').switchSingleContent(e, 'main');
                                                }, 250);
                                            }
                                        }
                                    });
                                    break;

                                case 'save':

                                    Core.addShortcutHelp('Alt+S', 'Save the Document', true);

                                    $(document).bind('keydown.form' + fid, function (e) {
                                        var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
                                        if (e.altKey && char == 's') {
                                            button.trigger('click.form');
                                        }
                                    });

                                    button.unbind('click.form').bind('click.form', function (e) {
                                        e.preventDefault();
                                        if (config.autoSaveInstance) {
                                            config.autoSaveInstance.stop();
                                        }
                                        //self.formID = $( this ).data( 'formID' );
                                        //self.windowID = $( this ).data( 'windowID' );
                                        self.save(e, false, false, fid, winID);
                                    });

                                    break;

                                case 'save_exit':

                                    Core.addShortcutHelp('Ctrl+Alt+E', 'Save and Exit the Document', true);

                                    $(document).bind('keydown.form' + fid, function (e) {
                                        var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
                                        if (e.altKey && e.ctrlKey && char == 'e') {
                                            button.trigger('click.form');
                                        }
                                    });

                                    $(this).unbind('click.form').bind('click.form', function (e) {
                                        e.preventDefault();

                                        //self.formID = $( this ).data( 'formID' );
                                        //self.windowID = $( this ).data( 'windowID' );
                                        if (config.autoSaveInstance) {
                                            config.autoSaveInstance.stop();
                                        }
                                        if (!$(this).parents('.switch-content-window').length) {

                                            self.save(e, true, false, fid, winID);
                                        }
                                        else {
                                            self.save(e, false, false, fid, winID);

                                            if (!Config.get('isSeemode')) {
                                                var _self = this;
                                                setTimeout(function () {
                                                    $(_self).parents('.switch-content-window').data('WindowManager').switchSingleContent(e, 'main');
                                                }, 250);
                                            }
                                        }
                                    });

                                    break;

                                case 'cancel':


                                    Core.addShortcutHelp('Alt+C', 'Cancel the Document', true);

                                    $(document).bind('keydown.form' + fid, function (e) {
                                        var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
                                        if (e.altKey && char == 'c') {
                                            button.trigger('click.form');
                                        }
                                    });


                                    if (!Config.get('isSeemode')) {
                                        button.unbind('click.form').bind('click.form', function (e) {
                                            e.preventDefault();
                                            if (config.autoSaveInstance) {
                                                config.autoSaveInstance.stop();
                                            }
                                            //  self.formID = $( this ).data( 'formID' );
                                            //   self.windowID = $( this ).data( 'windowID' );

                                            if (!$(this).parents('.switch-content-window').length) {
                                                if ($('#' + winID).data('WindowManager')) {
                                                    // Doc.unload(self.windowID);
                                                    $('#' + winID).data('WindowManager').set('isForceClose', true);
                                                    $('#' + winID).data('WindowManager').close();
                                                    //Desktop.getActiveWindowButton('close').trigger('click');
                                                }
                                                else {
                                                    Core.Tabs.closeActiveTab();
                                                }
                                            }
                                            else {
                                                $(this).parents('.switch-content-window').data('WindowManager').switchSingleContent(e, 'main');
                                            }

                                        });
                                    }
                                    else {
                                        button.unbind('click.form').bind('click.form', function (e) {
                                            e.preventDefault();

                                            SeemodeEdit.sendDokumentRollback(true);
                                        });
                                    }
                                    break;

                                case 'draft':

                                    Core.addShortcutHelp('Alt+D', 'Save the Document as Draft', true);

                                    $(document).bind('keydown.form' + fid, function (e) {
                                        var char = String.fromCharCode(e.keyCode).toLocaleLowerCase();
                                        if (e.altKey && char == 'd') {
                                            button.trigger('click.form');
                                        }
                                    });

                                    button.unbind('click.form').bind('click.form', function (e) {
                                        e.preventDefault();
                                        if (config.autoSaveInstance) {
                                            config.autoSaveInstance.stop();
                                        }
                                        //self.formID = $( this ).data( 'formID' );
                                        //self.windowID = $( this ).data( 'windowID' );

                                        self.save(e, true, true, fid, winID);
                                    });
                                    break;
                            }
                        }
                        else {
                            // console.log('Skip Form Registry button! ID:' + formID);
                        }

                    }
                }

            });
        }
        else {
            console.log('Toolbar is not visible. Used for Form Registry!!!');
        }
    };


    this.resetForm = function (event, btn) {
        // stop reset click if not dirty
        if ((typeof config.isDirty != 'undefined' && !config.isDirty)) {
            return;
        }

        $_form.get(0).reset();

        if (Tools.isFunction(config.onReset)) {
            config.onReset(formID, config);
        }

        Doc.resetDocumentSettings(windowID, formID);

        if (!Config.get('isSeemode')) {
            Win.resetWindowFormUi(windowID, formID);
        }
        else {
            $_form.get(0).reset();
        }

        this.resetDirty($_form, btn);

        // restart autosave delay
        if (config.autoSaveInstance) {
            config.autoSaveInstance.restart();
        }
    };

    this.updateSelectDefaultAttr = function (formID, windowID) {
        if (!Config.get('isSeemode')) {
            Win.updateFormUiDefaults(formID, windowID);
        }
    };


    this.save = function (event, exit, isdraft, _formID, _windowID) {

        var xself = this, autosave = false,
            cfg,
            $form = $_form,
            $win = $_window,
            fid = formID, wid = windowID;


        if (_windowID && _windowID != windowID) {
            $win = $('#' + _windowID);
            wid = _windowID;
        }
        if (_formID && _formID != formID) {
            $form = $('#' + _formID);
            fid = _formID;
        }

        var cfg = $form.data('formConfig'), error = false;

        if (typeof cfg != 'object') {
            console.log('invalid form config!');
            return false;
        }

        updateTextareaFields($form);

        if (event != 'autosave') {
            $form.find('input,select,textarea').each(function () {
                if (!error && /*$( this ).parents().is( ':visible' ).length &&*/ $(this).is(':visible')) {
                    if (xself.validation($(this))) {
                        console.log('Form error for field: ' + $(this).attr('name'));
                        error = true;
                    }
                }
            });

            if (error) {
                return false;
            }

            $win.mask(cmslang.save_notify);
        }
        else {
            autosave = true;

            $form.find('input,select,textarea').each(function () {
                if (!error && /*$( this ).parents().is( ':visible' ).length &&*/ $(this).is(':visible')) {
                    if (xself.validation($(this), true)) {
                        error = true;
                    }
                }
            });

            if (error) {
                return false;
            }


            var tb = Core.getToolbar();
            if (tb && tb.length) {
                $('button[rel=' + fid + ']', tb).removeClass('autosave');
            }
            else {
                $('button[rel=' + fid + ']', $win).addClass('autosave');
            }
        }

        this.form = {};
        var docID = null, self = this, stop = false, savevalid;

        setTimeout(function () {
            if (cfg.contentIdentifierID && $win.find('#' + cfg.contentIdentifierID).val() > 0) {
                docID = $win.find('#' + cfg.contentIdentifierID).val();
            }

            $form.focus();
            $form.get(0).focus();


            //  console.log('action save. formID:' + formID + ' windowID:' + windowID);
            // this.config = $(this.form).data('formConfig');

            /**
             * prepare Date before serialize the form
             */
            if (typeof cfg.onBeforeSerialize === 'function') {
                cfg.onBeforeSerialize($form, cfg, $win);
            }

            var postData = $form.serialize(); // $form.dcmsSerialize(); // this.getFormPostData($form); //$form.serialize();

            if (typeof postData.token == 'undefined') {
                postData.token = Config.get('token');
            }

            if (typeof cfg.onBeforeSend === 'function') {
                postData = cfg.onBeforeSend(postData);
            }

            // prepare data to post the form
            postData += '&ajax=1&exit=' + (exit ? 1 : 0);

            if (typeof cfg.contentlockaction === 'string') {
                postData += '&unlockaction=' + cfg.contentlockaction;
            }

            if ($('#documentmetadata' + fid).length) {
                postData += '&' + $('#documentmetadata' + fid).serialize();
            }

            if (typeof isdraft != 'undefined' && isdraft == true) {
                postData += '&savedraft=1';
            }

            if (typeof cfg.onBeforeSubmit === 'function') {
                stop = cfg.onBeforeSubmit(postData);
            }

            if (stop) {
                return false;
            }

            if (typeof cfg.save === 'function') {
                cfg.save(event, exit, isdraft, fid, wid, postData);
                return false;
            }


            // update statusbar to saving
            if (!Desktop.isWindowSkin) {

                if (!autosave) {
                    Core.setSaving(true);
                }

                setTimeout(function () {
                    $.ajax({
                        type: "POST",
                        url: 'admin.php',
                        'data': postData,
                        timeout: 10000,
                        dataType: 'json',
                        cache: false,
                        async: false,
                        success: function (data) {
                            if (xself.formSaveCallBack(event, data, exit, isdraft, fid, wid, postData)) {
                                if (typeof cfg.autoSaveInstance == 'object') {
                                    cfg.autoSaveInstance.saveCallback(true);
                                }
                            }
                            return false;
                        }
                    });

                }, 10);

            }
            else {

                $.ajax({
                    type: "POST",
                    url: 'admin.php',
                    'data': postData,
                    timeout: 10000,
                    dataType: 'json',
                    cache: false,
                    async: false,
                    success: function (data) {
                        if (xself.formSaveCallBack(event, data, exit, isdraft, fid, wid, postData)) {
                            if (typeof cfg.autoSaveInstance == 'object') {
                                cfg.autoSaveInstance.saveCallback(true);
                            }
                        }
                        return false;
                    }
                });
            }
        }, 10);

    };


    this.formSaveCallBack = function (event, data, exit, isdraft, fid, wid, postData) {


        var docID = null, self = this, cfg = config;
        if (cfg.contentIdentifierID && $_window.find('#' + cfg.contentIdentifierID).val() > 0) {
            docID = $_window.find('#' + cfg.contentIdentifierID).val();
        }

        $_form.focus();
        $_form.get(0).focus();


        if (Tools.responseIsOk(data))
        {

            // update versioning
            if (typeof data.versionselection != 'undefined' && data.versionselection != '' && !exit) {
                var versioning;

                if (typeof Core != 'undefined' && typeof Core.getToolbar == 'function') {
                    var tb = Core.getToolbar();
                    if (tb) {
                        versioning = tb.find('#setVersion');
                        if (!versioning) {
                            versioning = [];
                        }
                    }
                }
                else {
                    if ($_window.data('WindowManager')) {
                        versioning = $_window.find('#setVersion');
                    }
                }

                if ( versioning ) {
                    if (versioning.length == 1) {
                        var rebuild = false;
                        if (versioning.hasClass('inputS')) {
                            versioning.SelectBox('destroy');
                            rebuild = true;
                        }

                        versioning.empty().append(data.versionselection);

                        if (rebuild) versioning.SelectBox();
                    }
                }
            }

            this.resetDirty($_form);
            if (docID == null && data.newid > 0 && typeof cfg.contentIdentifierID == 'string' && cfg.contentIdentifierID != '' && !exit) {
                $_window.find('#' + cfg.contentIdentifierID).val(data.newid);
                docID = data.newid;
            }

            // update content tree item
            var treeItem = $('#tree-node-' + docID).filter('[modul=' + $_form.find('input[name=adm]').val() + ']');

            if (treeItem.length == 1) {
                var d = treeItem.data('nodeData');
                var hash = windowID.replace('tab-', '').replace('content-', '');

                if ($('#meta-' + hash).length && $('#meta-' + hash).find('#meta-published').length) {

                    var sel = parseInt($('#meta-published', $('#meta-' + hash)).find(':selected').val());

                    if (sel == 1 || sel == 2) {
                        treeItem.removeClass('tree-node-unpublished').addClass('tree-node-published');
                        if (d && typeof d.published != 'undefined') {
                            d.published = 1;
                        }
                    }
                    else {
                        treeItem.addClass('tree-node-unpublished').removeClass('tree-node-published');
                        if (d && typeof d.published != 'undefined') {
                            d.published = 0;
                        }
                    }

                }
                else {
                    if ($_form.find('[name=published]').length) {

                        if ($_form.find('[name=published]').is('select')) {
                            var s = null;
                            var sel = parseInt($(this).find(':selected').val());

                            if (sel === 1 || sel === 2) {
                                treeItem.removeClass('tree-node-unpublished').addClass('tree-node-published');
                                if (d && typeof d.published != 'undefined') {
                                    d.published = 1;
                                }

                                s = true;
                            }
                            else {
                                if (s === null) {
                                    treeItem.addClass('tree-node-unpublished').removeClass('tree-node-published');
                                    if (d && typeof d.published != 'undefined') {
                                        d.published = 0;
                                    }
                                }
                            }
                        }
                        else {
                            var s = null;
                            $(this).each(function () {
                                var sel = $(this).is(':selected');
                                if (sel === 1 || sel === 2) {
                                    s = true;
                                    treeItem.removeClass('tree-node-unpublished').addClass('tree-node-published');
                                    if (d && typeof d.published != 'undefined') {
                                        d.published = 1;
                                    }
                                }
                                else {
                                    if (s === null) {
                                        treeItem.addClass('tree-node-unpublished').removeClass('tree-node-published');
                                        if (d && typeof d.published != 'undefined') {
                                            d.published = 0;
                                        }
                                    }
                                }
                            });
                        }
                    }
                }

                if (exit === true) {
                    treeItem.removeClass('locked');
                    var src = treeItem.find('.tree-node-icon:first').attr('src');
                    if (src) {
                        treeItem.find('.tree-node-icon:first').attr('src', src.replace('-locked', ''));
                    }

                    if (d && typeof d.locked != 'undefined') {
                        d.locked = 0;
                    }
                }

                if (d) {
                    treeItem.data('nodeData', d);
                }
            }

            // patch
            this.updateSelectDefaultAttr($_form);

            // restart autosave if exists
            if (!exit && config.autoSaveInstance) {
                config.autoSaveInstance.restart();
            }

            if (event == 'autosave')
            {
                return true;
            }

            $_window.unmask();

            // refresh the opener window
            var openerID = $_window.attr('opener');
            // console.log( 'openerID: ' + openerID + ' docID:' + docID );
            if (exit === true && !Config.get('isSeemode')) {

                // Doc.unload();

                // remove rollback attribute from window.
                // this stop the ajax rollback call in dcms.application.js at onBeforeWindowClose()
                if (exit) {
                    //$_win.removeAttr('rollback');
                }

                var w = $_window.data('WindowManager');

                Notifier.display(1, (typeof data.msg != 'undefined' ? data.msg : 'Formular wurde erfolgreich gespeichert'));

                if (w) {
                    $_window.removeData('formConfig');
                    w.close(event, $_window.data('WindowManager'), function () {

                        // highlight the edited content :)
                        if (!cfg.forceNoRefresh && typeof openerID == 'string' && docID != null && parseInt(docID) > 0) {
                            Win.refreshOpenerWindow(openerID, function () {
                                setTimeout(function () {

                                    if (typeof cfg.onAfterSubmit === 'function') {
                                        cfg.onAfterSubmit(exit, data, $form);
                                    }

                                    var tr = $('#' + openerID).find('#data-' + docID);
                                    if (tr.length == 1) {
                                        var bgColor, color;
                                        if (tr.is('tr')) {
                                            bgColor = tr.find('td:first').css('backgroundColor');
                                            color = tr.find('td:first').css('color');
                                        }
                                        else {
                                            bgColor = tr.css('backgroundColor');
                                            color = tr.css('color');
                                        }

                                        bgColor = bgColor || '#ffffff';
                                        color = color || '#333333';

                                        tr.animate({
                                            backgroundColor: '#FF235D',
                                            color: "#fff"
                                        }, 150, function () {
                                            $(this).animate({
                                                backgroundColor: bgColor,
                                                color: color
                                            }, 150, function () {
                                                $(this).animate({
                                                    backgroundColor: '#FF235D',
                                                    color: "#fff"
                                                }, 150, function () {
                                                    $(this).animate({
                                                        backgroundColor: bgColor,
                                                        color: color
                                                    }, 150, function () {
                                                        $(this).animate({
                                                            backgroundColor: '#FF235D',
                                                            color: "#fff"
                                                        }, 150, function () {

                                                            $(this).animate({
                                                                backgroundColor: bgColor,
                                                                color: color
                                                            }, 150, function () {
                                                                $(this).css({
                                                                    backgroundColor: '',
                                                                    color: ''
                                                                });
                                                            });
                                                        });
                                                    });
                                                });
                                            });
                                        });
                                    }
                                }, 150);
                            });
                        }
                    });
                }
                else {
                    /**
                     * Only for none Window Skin
                     */

                    Core.closeTab(function () {

                        // highlight the edited content :)
                        if (!cfg.forceNoRefresh && typeof openerID == 'string' && docID != null && parseInt(docID) > 0) {
                            Win.refreshOpenerWindow(openerID, function () {
                                setTimeout(function () {

                                    if (typeof cfg.onAfterSubmit === 'function') {
                                        cfg.onAfterSubmit(exit, data, $_form);
                                    }

                                    var hash;
                                    if (openerID.match(/^tab-/)) {
                                        hash = openerID.replace('tab-', '');
                                    }

                                    if (openerID.match(/^content-/)) {
                                        hash = openerID.replace('content-', '');
                                    }

                                    if (openerID.match(/^meta-/)) {
                                        hash = openerID.replace('meta-', '');
                                    }

                                    if (hash) {

                                        var tr = $('#content-' + hash).find('#data-' + docID);
                                        if (tr.length == 1) {
                                            var bgColor, color;
                                            if (tr.is('tr')) {
                                                bgColor = tr.find('td:first').css('backgroundColor');
                                                color = tr.find('td:first').css('color');
                                            }
                                            else {
                                                bgColor = tr.css('backgroundColor');
                                                color = tr.css('color');
                                            }

                                            bgColor = bgColor || '#ffffff';
                                            color = color || '#333333';

                                            tr.animate({
                                                backgroundColor: '#FF235D',
                                                color: "#fff"
                                            }, 150, function () {
                                                $(this).animate({
                                                    backgroundColor: bgColor,
                                                    color: color
                                                }, 150, function () {
                                                    $(this).animate({
                                                        backgroundColor: '#FF235D',
                                                        color: "#fff"
                                                    }, 150, function () {
                                                        $(this).animate({
                                                            backgroundColor: bgColor,
                                                            color: color
                                                        }, 150, function () {
                                                            $(this).animate({
                                                                backgroundColor: '#FF235D',
                                                                color: "#fff"
                                                            }, 150, function () {

                                                                $(this).animate({
                                                                    backgroundColor: bgColor,
                                                                    color: color
                                                                }, 150, function () {
                                                                    $(this).css({
                                                                        backgroundColor: '',
                                                                        color: ''
                                                                    });
                                                                });
                                                            });
                                                        });
                                                    });
                                                });
                                            });
                                        }

                                    }
                                }, 200);
                            });
                        }
                        else {
                            if (typeof cfg.onAfterSubmit === 'function') {
                                cfg.onAfterSubmit(exit, data, $_form);
                            }
                        }
                    }, (typeof data.unlock_content != 'undefined' && data.unlock_content == true ? true : false), cfg.contentlockaction);
                }

            }
            else if (exit === true && Config.get('isSeemode')) {
                SeemodeEdit.triggerFormSave(exit, data);
                Notifier.display(1, (typeof data.msg != 'undefined' ? data.msg : 'Formular wurde erfolgreich gespeichert'));
            }
            else if (exit !== true && !Config.get('isSeemode')) {
                Notifier.display(1, (typeof data.msg != 'undefined' ? data.msg : 'Formular wurde erfolgreich gespeichert'));

                // highlight the edited content :)
                if (Desktop.isWindowSkin && !cfg.forceNoRefresh && typeof openerID == 'string' && docID != null && parseInt(docID) > 0) {

                    Win.refreshOpenerWindow(openerID, function () {
                        setTimeout(function () {

                            if (typeof cfg.onAfterSubmit === 'function') {
                                cfg.onAfterSubmit(exit, data, $_form);
                            }

                            var tr = $('#' + openerID).find('#data-' + docID);
                            // console.log( [tr] );
                            if (tr.length == 1) {
                                var bgColor, color;
                                if (tr.is('tr')) {
                                    bgColor = tr.find('td:first').css('backgroundColor');
                                    color = tr.find('td:first').css('color');
                                }
                                else {
                                    bgColor = tr.css('backgroundColor');
                                    color = tr.css('color');
                                }

                                tr.animate({
                                    backgroundColor: '#FF235D',
                                    color: "#fff"
                                }, 150, function () {
                                    $(this).animate({
                                        backgroundColor: bgColor,
                                        color: color
                                    }, 150, function () {
                                        $(this).animate({
                                            backgroundColor: '#FF235D',
                                            color: "#fff"
                                        }, 150, function () {
                                            $(this).animate({
                                                backgroundColor: bgColor,
                                                color: color
                                            }, 150, function () {
                                                $(this).animate({
                                                    backgroundColor: '#FF235D',
                                                    color: "#fff"
                                                }, 150, function () {

                                                    $(this).animate({
                                                        backgroundColor: bgColor,
                                                        color: color
                                                    }, 150, function () {
                                                        $(this).css({
                                                            backgroundColor: '', color: ''
                                                        });
                                                    });
                                                });
                                            });
                                        });
                                    });
                                });

                            }
                            else {

                            }
                        }, 150);
                    });
                }
                else {
                    if (typeof cfg.onAfterSubmit === 'function') {
                        cfg.onAfterSubmit(exit, data, $_form);
                    }
                }


                return true;
            }
            else if (exit !== true && Config.get('isSeemode')) {
                Notifier.display(1, (typeof data.msg != 'undefined' ? data.msg : 'Formular wurde erfolgreich gespeichert'));
            }
        }
        else {
            if (event == 'autosave') {

                var tb = Core.getToolbar();

                if (tb && tb.length) {
                    $('button[rel=' + fid + ']', tb).removeClass('autosave');
                }
                else {
                    $('button[rel=' + fid + ']').removeClass('autosave');
                }

                console.log('Autosave Error:' + (typeof data.msg != 'undefined' ? data.msg : 'Es ist ein Fehler aufgetreten'));

                return false;
            }

            Notifier.display('error', (typeof data.msg != 'undefined' ? data.msg : 'Es ist ein Fehler aufgetreten'));
        }

        $_window.unmask();
    };


    this.makeDirty = function () {

        var cfg = config;

        cfg = $.extend(cfg, {
            isDirty: true
        });

        if (!Config.get('isSeemode'))
        {
            config = cfg;
            $_window.data('formConfig', cfg);
            $_form.data('formConfig', cfg);

            if (Desktop.isWindowSkin) {
                $('#' + winID).find('.win-title').find('sup').remove();
                $('#' + winID).find('.win-title').addClass('dirty').append(' <sup>*</sup>');
            }
            else {
                Core.setDirty(true);
            }
        }
        else {
            SeemodeEdit.setDirty();
        }
    };


    this.makeReset = function () {
        var cfg = config;

        cfg = $.extend(cfg, {
            isDirty: false
        });

        if (!Config.get('isSeemode')) {
            config = cfg;
            $_window.data('formConfig', cfg);
            $_form.data('formConfig', cfg);

            if (Desktop.isWindowSkin) {
                $('#' + winID).find('.win-title').find('sup').remove();
                $('#' + winID).find('.win-title').removeClass('dirty');
            }
            else {
                Core.resetDirty(true);
            }
        }
        else {
            SeemodeEdit.removeDirty();
        }
    };

    this.resetDirty = function (form, btn) {

        config.isDirty = false;
        $_form.data('formConfig', config);
        $_window.data('formConfig', config);

        if (!Config.get('isSeemode'))
        {
            // restart autosave delay
            if (config.autoSaveInstance) {
                config.autoSaveInstance.restart();
            }

            if (Desktop.isWindowSkin) {
                $('#' + winID).find('.win-title').removeClass('dirty').find('sup').remove();
            }
            else {
                Core.resetDirty(true, btn);
            }

        }
        else {

            // restart autosave delay
            if (config.autoSaveInstance) {
                config.autoSaveInstance.restart();
            }
            SeemodeEdit.removeDirty();
        }
    };

    this.setDirty = function (event, form, fid, winid) {
        this.liveSetDirty(event);
    };


    this.liveSetDirty = function (event) {
        if (config) {
            config = $.extend(config, {
                isDirty: true
            });

            // restart autosave delay
            if (config.autoSaveInstance) {
                config.autoSaveInstance.restart();
            }

            if (Tools.isString(config._formid)) {
                $_form.data('formConfig', config);
            }

            if (!Config.get('isSeemode')) {
                if (Desktop.isWindowSkin) {
                    $_window.data('formConfig', config);
                    $_window.find('.win-title').find('sup').remove();
                    $_window.find('.win-title').addClass('dirty').append(' <sup>*</sup>');
                }
                else {
                    Core.setDirty(true);
                }
            }
            else {
                 if (typeof SeemodeEdit != 'undefined') {
                     SeemodeEdit.setDirty();
                 }
            }
        }

    };
    this.isDirty = function (fid, winID) {
        var cfg = null;

        if (!winID) {
            winID = $('#' + fid).data('windowID');
            cfg = $('#' + fid, $('#' + winID)).data('formConfig');
        }
        else {
            cfg = $('#' + fid, $('#' + winID)).data('formConfig');
        }

        if (typeof cfg == 'undefined' || typeof cfg.isDirty == 'undefined') {
            console.log('Undefined Form configuration for Form.isDirty.');
            return null;
        }

        if (cfg.isDirty) {
            return true;
        }

        return false;

    };

    /**
     *  set button state
     */
    this.enableButton = function (btn) {
        $(btn).attr('disabled', false);
        $(btn).removeAttr('disabled').removeClass('disabled');
    };

    this.disableButton = function (btn) {
        $(btn).attr('disabled', true).addClass('disabled');
    };


    this.validateForm = function (event) {
        console.log('validateForm')
    };


    this.registerContentTags = function () {

        if (!$('div.content-tag', $('#' + formID)).length) {
            $('.tag-table', $('#' + formID)).hide();
        }

        var self = this, fields = $('input.content-tags', $('#' + formID));
        var fo = $('#' + formID);

        fields.each(function () {

            $(this).attr('formid', formID);

            var hiddenField = $(this).css('float', 'left').prev();
            var divResult, addTag;

            if ($(this).next().hasClass('addtag-btn')) {
                addTag = $(this).next();
                divResult = $('#live-tag-result');

                addTag.unbind();
            }
            else {
                var addTag = $('<span>').css({
                    'cursor': 'pointer',
                    'float': 'left'
                }).addClass('addtag-btn');

                divResult = $('#live-tag-result');
                if (!divResult.length) {
                    divResult = $('<div id="live-tag-result">').addClass('live-tag-result');

                    if ($('#fullscreenContainer').length) {
                        $('#fullscreenContainer').append(divResult);
                    }
                    else {
                        $('body').append(divResult);
                    }

                }
                $(this).addClass('live-search');
                //      divResult = $('<div>').addClass('live-tag-result');
                addTag.insertAfter($(this));
                //      divResult.insertAfter(addTag);
            }

            var timeout = null, self = this;
            var inputfield = $(this);
            var currentValue = '';

            $(this).unbind('blur.tagform').bind('blur.tagform', function (e) {
                setTimeout(function () {
                    inputfield.removeClass('tag-loading');
                    clearTimeout(timeout);
                    $(divResult).hide();
                }, 300);
            });

            // live search tags
            $(this).unbind('keyup.tagform').bind('keyup.tagform', function (e) {
                inputfield.removeClass('tag-loading');
                clearTimeout(timeout);
                $(divResult).hide();
                var val = $(this).val();
                var _self = this;
                if (val.length >= 3 && e.keyCode != 27) {
                    currentValue = hiddenField.val().trim();
                    currentValue = currentValue.replace(/^0([,]?)$/g, '');
                    inputfield.addClass('tag-loading');

                    timeout = setTimeout(function () {
                        var params = {};
                        params.adm = 'tags';
                        params.action = 'search';
                        params.q = val;
                        params.table = $(_self).attr('data-table');
                        params.skip = currentValue;
                        params.ajax = 1;
                        if (typeof params.token == 'undefined') {
                            params.token = Config.get('token');
                        }
                        $.post('admin.php', params, function (data) {
                            if (Tools.responseIsOk(data)) {
                                divResult.empty();

                                for (var i in data.tags) {
                                    if (data.tags[i].tag) {
                                        var divTag = $('<div>').addClass('content-tag');
                                        divTag.append($('<span>').append(data.tags[i].tag));
                                        divTag.append($('<span>'));

                                        // insert add tag
                                        divTag.attr('rel', data.tags[i].id).css('cursor', 'pointer').click(function () {

                                            var v = hiddenField.val().trim();
                                            v = v.replace(/^0([,]?)$/g, '');

                                            hiddenField.val((v != '' ? v + ',' + $(this).attr('rel') : $(this).attr('rel')));

                                            $(this).find('span:last').addClass('delete-tag').attr('title', 'Diesen Tag entfernen').click(function (ev) {
                                                // ev.preventDefault();
                                                self.updateTagsIdField($(this).parent());
                                            });

                                            $(this).appendTo($('.tag-table', fo));

                                            $('.tag-table', fo).show();
                                            $(divResult).empty().hide();

                                        });

                                        $(divResult).append(divTag);

                                    }
                                }

                                if (data.tags.length > 0) {
                                    var offset = inputfield.offset();
                                    $(divResult).css({visible: 'hidden', zIndex: 8000}).show();

                                    var height = $(divResult).outerHeight(true);

                                    if (offset.top + inputfield.outerHeight(true) + height <= $(document).height()) {
                                        $(divResult).css({top: offset.top + inputfield.outerHeight(true), left: offset.left, visible: ''});
                                    }
                                    else {
                                        $(divResult).css({top: offset.top - height, left: offset.left, visible: ''});
                                    }
                                }

                                inputfield.removeClass('tag-loading');
                            }
                            else {
                                alert(data.msg);
                            }
                        }, 'json');

                    }, 300);
                }
            });

            // click add tag button
            addTag.unbind('click.tagform').on('click.tagform', function (e) {
                var val = $(self).val().trim();

                e.preventDefault();

                if (val.length >= 3) {
                    var params = {};
                    params.adm = 'tags';
                    params.action = 'add';
                    params.tag = val;
                    params.table = $(self).attr('data-table');
                    params.send = 1;
                    params.ajax = 1;
                    if (typeof params.token == 'undefined') {
                        params.token = Config.get('token');
                    }
                    $.post('admin.php', params, function (data) {
                        if (Tools.responseIsOk(data)) {

                            hiddenField.val((currentValue ? hiddenField.val() + ',' + data.newid : data.newid));

                            var div = $('<div>').attr('rel', data.newid).addClass('content-tag');
                            var removeLink = $('<span>').css('cursor', 'pointer').addClass('delete-tag').attr('title', 'Diesen Tag entfernen');
                            removeLink.click(function (ev) {
                                // ev.preventDefault();
                                self.updateTagsIdField($(this).parent());
                            });
                            div.append($('<span>').append(val));
                            div.append(removeLink);
                            div.appendTo($('.tag-table', fo));

                            $('.tag-table', fo).show();
                        }
                        else {
                            jAlert(data.msg);
                        }
                    }, 'json');
                }
            });

        });

        // Register Tag delete Buttons
        var tags = $('.tag-table .delete-tag', fo);
        tags.each(function () {
            $(this).attr('title', 'Diesen Tag entfernen').unbind('click.tagrem').on('click.tagrem', function () {
                self.updateTagsIdField($(this).parent());
            });
        });
    };

    function tagRemoveEvent(obj, inForm) {
        $(obj).unbind('click.tagremove').on('click.tagremove', function () {
            jConfirm('Möchtest du diesen Tag wirklich löschen?', 'Bestätigung...', function (r) {
                if (r) {
                    var id = $(obj).attr('rel');
                    var hiddenField = $(obj).parents('.contenttags:first').find('input.content-tags');
                    hiddenField = hiddenField.prev();

                    var params = {};
                    params.adm = 'tags';
                    params.action = 'delete';
                    //	params.table = $(_self ).attr('data-table');
                    params.id = id;
                    params.ajax = 1;
                    if (typeof params.token == 'undefined') {
                        params.token = Config.get('token');
                    }
                    $.post('admin.php', params, function (deldata) {
                        if (Tools.responseIsOk(deldata)) {
                            var hiddenField = $(obj).parents('.contenttags:first').find('input.content-tags');
                            hiddenField = hiddenField.prev();

                            var currentValue = hiddenField.val().trim();
                            var splited = currentValue.split(',');
                            var tmp = '';

                            for (var i = 0; i < splited.length; i++) {
                                if (splited[i] != id) {
                                    tmp = (tmp != '' ? tmp + ',' + splited[i] : splited[i]);
                                }
                            }

                            hiddenField.val(tmp);
                            $(obj).parent().remove();

                            if ($(obj).parents('div.tag-table:first').find('div').length == 0) {
                                $(obj).parents('div.tag-table:first').hide();
                            }
                        }
                        else {
                            alert(deldata.msg);
                        }
                    }, 'json');

                }
            });
        });
    };

    this.updateTagsIdField = function (obj) {
        var id = $(obj).attr('rel');
        if (!id) {
            Debug.error('ID to delete the tag not set.');
            return;
        }

        var hiddenField = $(obj).parents('.contenttags:first').find('input.content-tags');
        hiddenField = hiddenField.prev();

        var currentValue = hiddenField.val().trim();
        var splited = currentValue.split(',');
        var tmp = '';

        for (var i = 0; i < splited.length; i++) {
            if (splited[i] && splited[i] != id) {
                tmp = (tmp != '' ? tmp + ',' + splited[i] : splited[i]);
            }
        }

        hiddenField.val(tmp);
        $(obj).remove();

        if ($(obj).parents('div.tag-table:first').find('div').length == 0) {
            $(obj).parents('div.tag-table:first').hide();
        }
    };


    function doValidate(mode, value) {
        switch (mode) {
            case 'mail':
                return value.match(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
                break;

            case 'url':
                return value.match(/^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i);
                break;

            case 'alphanum':
                return value.match(/^[0-9a-zA-ZäüöÄÜÖß]+$/);
                break;

            case 'alpha':
                return value.match(/^[a-zA-ZäüöÄÜÖß]+$/);
                break;

            case 'number':
                return value.match(/^[\-\+]?(([0-9]+)([\.,]([0-9]+))?|([\.,]([0-9]+))?)$/);
                break;

            case 'integer':
                return value.match(/^[\-\+]?\d+$/);
                break;
        }
    };


    this.validation = function (jqElement, returnOnly) {

        var classNames = jqElement.attr('class'), formData = config, error = false;
        if (formData && classNames) {
            if (classNames.match(/require/i)) {

                var value = jqElement.val(), isTextarea = jqElement.is('textarea');
                var fixName = jqElement.attr('name').replace('[', '-').replace(']', '-');

                if (value && value.length) {

                    var message = false;

                    if (classNames.match(/(e)mail/i) && !doValidate('mail', value)) {
                        message = cmslang.validation_invalid_email;
                    }
                    else if (classNames.match(/integer/i) && !doValidate('integer', value)) {
                        message = cmslang.validation_invalid_integer;
                    }
                    else if (classNames.match(/number/i) && !doValidate('number', value)) {
                        message = cmslang.validation_invalid_number;
                    }
                    else if (classNames.match(/alpha/i) && !doValidate('alpha', value)) {
                        message = cmslang.validation_invalid_alpha;
                    }
                    else if (classNames.match(/alphanum/i) && !doValidate('alphanum', value)) {
                        message = cmslang.validation_invalid_alphanum;
                    }
                    else if (classNames.match(/url/i) && !doValidate('url', value)) {
                        message = cmslang.validation_invalid_url;
                    }
                }
                else {
                    message = cmslang.validation_invalid_input;
                }

                if (message) {
                    error = true;

                    if (!returnOnly) {
                        jqElement.addClass('error');
                        var position = jqElement.position(), offset = jqElement.offset();

                        if ($('#' + fixName + '_' + formID).length) {
                            var after = jqElement.next();
                            if (jqElement.parent().hasClass('input-tooltip')) {
                                after = jqElement.parent().next();
                            }
                            after.show().find('.content').html(message);
                        }
                        else {

                            var after = jqElement;
                            if (jqElement.parent().hasClass('input-tooltip')) {
                                after = jqElement.parent();
                            }

                            var errorContainer = $('<div id="' + fixName + '_' + formID + '" rel="validate_' + formID + '" class="validation"><span class="content"></span></div>');
                            errorContainer.insertAfter(after);
                            errorContainer.find('.content').html(message).show();
                        }
                    }
                    else {
                        return true;
                    }
                }
                else {
                    jqElement.removeClass('error');
                    $('#' + fixName + '_' + formID).hide();
                }
            }
        }

        return error;
    };
}