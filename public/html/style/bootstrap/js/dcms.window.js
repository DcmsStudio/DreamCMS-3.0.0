/**
 * Created by marcel on 30.05.14.
 */

var Win = {
    hash: null,
    windowID: null,
    maskedWindows: {},
    setActive: function (hash) {

        this.windowID = 'content-' + hash;
        this.hash = hash;

    },
    refreshWindowScrollbars: function () {
        this.refreshScrollbars();
    },
    refreshScrollbars: function () {
        $(window).trigger('resize');
    },
    refreshContentHeight: function () {
        Core.updateViewPort();
    },
    refreshTinyMCE: function () {
        $('#main-content').find();
    },
    prepareWindowFormUi: function () {
        if (!this.windowID) {
            return;
        }





        // Prepare Checkboxes & Prepare Radio
        /*
        $('#' + this.windowID + ',#meta-' + hash + ',#buttons-' + hash).find('input[type="checkbox"]:not(.inputC),input[type="radio"]:not(.inputR)').each(function () {
            var t = $(this).attr('type');
            if (t === 'checkbox') {
                $(this).attr('default', (($(this).is(':checked') || $(this).get(0).checked == true) ? 'on' : 'off'));
                $.Zebra_TransForm($(this));
                $(this).addClass('inputC');
            }
            else if (t === 'radio') {
                $(this).attr('default', (($(this).is(':checked') || $(this).get(0).checked == true) ? 'on' : 'off'));
                $.Zebra_TransForm($(this));
                $(this).addClass('inputR');
            }
        });
        */
        if(!Modernizr.pointerevents) {
            var hash = this.windowID.replace('content-', '').replace('tab-', '');
            $('#' + this.windowID + ',#meta-' + hash + ',#buttons-' + hash).find('select:not(.inputS)').SelectBox();
        }
        else {
            $('#' + this.windowID + ',#meta-' + hash + ',#main-content-buttons').find('select').each(function(){
                "use strict";

                if (!$(this).attr('size') && !$(this).parent().is('div.select-wrap') ) {
                    $(this).wrap( $('<div class="select-wrap"></div>') );
                }
                if ($(this).attr('size') > 0 && !$(this).parent().is('div.select-wrap-multi') ) {
                    $(this).wrap( $('<div class="select-wrap-multi"></div>') );
                }
            });
        }
    },
    resetWindowFormUi: function (windowID, formID) {


        if(!Modernizr.pointerevents) {
            var hash = windowID.replace('content-', '').replace('tab-', '');
            $('#' + formID + ',#meta-' + hash + ',#buttons-' + hash, $('#' + windowID)).find('select.inputS').each(function () {
                "use strict";
                $(this).SelectBox('reset');
            });
        }


        /*
        $('#' + formID + ',#meta-' + hash + ',#buttons-' + hash, $('#' + windowID)).find('select.inputS,input.inputR,input.inputC').each(function () {
            if ($(this).hasClass('inputS')) {
                // console.log('reset SelectBox ');
                $(this).SelectBox('reset');
            }
            else if ($(this).hasClass('inputR') || $(this).hasClass('inputC')) {
                var self = $(this), name = $(this).attr('name');

                if ($(this).attr('default') == 'on' && (!$(this).prop('checked') || !this.checked)) {
                    $(this).prop('checked', true);
                    $(this).attr('checked', 'checked');
                    this.checked = true;
                }

                if ($(this).attr('default') == 'off' && ($(this).prop('checked') || this.checked)) {
                    $(this).prop('checked', false);
                    $(this).removeAttr('checked');
                    this.checked = false;
                }

                $(this).trigger('change');

                setTimeout(function () {
                    self.next().triggerHandler('doReset');// trigger the Zebra_TransForm
                });
            }
        });
        */
    },
    removeWindowFormUi: function (windowID) {
        if(!Modernizr.pointerevents) {
            var hash = windowID.replace('content-', '').replace('tab-', '');
            $('#' + windowID + ',#meta-' + hash + ',#buttons-' + hash).find('select').each(function () {
                var sb = $(this).attr('sb');

                if (sb) {
                    $(this).SelectBox('destroy');
                    $(this).removeClass('inputS').removeAttr('sb');
                    $('#' + sb).remove();
                }
            });
        }

    },
    redrawWindowHeight: function (win) {
        Core.updateViewPort();
    },
    updateFormUiDefaults: function (formID, windowID) {

    },
    refreshOpenerWindow: function (tabid, callback) {
        var hash;
        if (typeof tabid == 'string') {

            if (tabid.match(/^tab-/)) {
                hash = tabid.replace('tab-', '');
            }

            if (tabid.match(/^content-/)) {
                hash = tabid.replace('content-', '');
            }

            if (tabid.match(/^meta-/)) {
                hash = tabid.replace('meta-', '');
            }
        }

        if (hash) {
            if ($('#content-' + hash).data('windowGrid')) {
                $('#content-' + hash).data('windowGrid').refresh(callback);
            }
            else {
                Core.Tabs.refreshTab(hash, callback);
            }
        }
    },
    unload: function (windowid) {
        Doc.unload(windowid);
    },
    mask: function (msg, delay) {
        var w = this.windowID;
        if (!w || w == null) {
            return false;
        }

        this.maskedWindows[ w ] = true;

        $('#' + w).mask(msg, delay);
    },
    unmask: function () {
        var w = this.windowID;
        if (!w || w == null) {
            return false;
        }

        if (typeof this.maskedWindows[ w] != 'undefined') {
            delete this.maskedWindows[ w ];
            $('#' + w).unmask();
        }
    }
};

ns('Win.ContentTabs');

Win.ContentTabs = {
    initTabs: function (windowID) {
        var hash = Win.windowID.replace('tab-', '').replace('content-', '');
        var tabContainer = $('#content-tabs-' + hash);

        Core.Tabs.bindContentTabEvents(tabContainer, hash);
    }
};