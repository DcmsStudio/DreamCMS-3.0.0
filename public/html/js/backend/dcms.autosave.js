/* 
 * DreamCMS 3.0
 * 
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE Version 2
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-2.0.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@dcms-studio.de so we can send you a copy immediately.
 * 
 * PHP Version 5.3.6
 * @copyright	Copyright (c) 2008-2013 Marcel Domke (http://www.dcms-studio.de)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 */

function Autosave(formID, winID, opt) {

    var opts = $.extend({}, opt), formID = formID, winID = winID, delay = opts.delay || 10000, postIDFieldname = opts.idfieldname || false, initialCompareString = '', objects;

    // Autosave in localStorage
    var intervalTimer, compareString, lastCompareString = null, lastSaveTime = 0, formobject, post_id, notifierTimeout, notifierHideDelay = 5000, autoHide = false;
    var tb;

    post_id = opts.postid || 0;
    formobject = $('#' + formID);
    // objects = formobject.find('input:not(.nodirty),select:not(.nodirty),textarea:not(.nodirty)');





    /**
     * Save post data for the current post
     *
     * Runs on a 15 sec. interval, saves when there are differences in the post title or content.
     * When the optional data is provided, updates the last saved post data.
     *
     * $param data optional Object The post data for saving, minimum 'post_title' and 'content'
     * @return bool
     */
    function save() {

        if (!formobject.data('formConfig')) {
            return false;
        }

        if ( window.event ) {

        }


        updateTextareaFields(formobject);

        compareString = getCompareString();

        // If the content, title and excerpt did not change since the last save, don't save again
        if (compareString === lastCompareString) {
            return false;
        }

        Form.save('autosave', false, true, formID, winID);
    }

    function removeSpaces(string) {
        return string.toString().replace(/[\x20\t\r\n\f]+/g, '');
    }

    // Strip whitespace and compare two strings
    function compare(str1, str2) {
        return ( removeSpaces(str1 || '') === removeSpaces(str2 || '') );
    }

    // Concatenate title, content and excerpt. Used to track changes when auto-saving.
    function getCompareString() {
        // var String = '';
        return jQuery.param(formobject.serialize());

        /*
        objects.each(function () {
            var fieldname = $(this).attr('name');
            var fieldtype = $(this).attr('type');
            if (postIDFieldname !== fieldname && fieldname !== 'token' && fieldname !== '_fsend') {
                var val = $(this).val();
                String += (!val && fieldtype == 'checkbox' || fieldtype == 'radio' ? '0' : (val ? val : ''));

            }
        });

        return String;
        */
    }

    this.saveCallback = function (valid) {
        if (valid) {
            clearTimeout(notifierTimeout);

            lastSaveTime = ( new Date() ).getTime();

            // read new id if saved
            if (!post_id) {
                if (postIDFieldname) {
                    post_id = $('#' + winID).find(postIDFieldname).val();
                }
            }

            lastCompareString = compareString;

            compareString = null;

            var a = new Date(lastSaveTime);
            var hour = a.getHours();
            var min = a.getMinutes();
            var sec = a.getSeconds();

            if (hour < 10) {
                hour = '0' + hour;
            }
            if (min < 10) {
                min = '0' + min;
            }
            if (sec < 10) {
                sec = '0' + sec;
            }

            tb.find('.state-global .state-msg').hide();
            var autoSaveMsg = tb.find('div.autosave-msg');

            if (!autoSaveMsg.length) {
                autoSaveMsg = $('<div class="autosave-msg"><span>Entwurf wurde um ' + hour + ':' + min + ':' + sec + ' gespeichert</span></div>').css({
                    display: 'inline-block'
                });
                tb.append(autoSaveMsg);
            }
            else {
                autoSaveMsg.html('<span>Entwurf wurde um ' + hour + ':' + min + ':' + sec + ' gespeichert</span>').delay(300).css({
                    display: 'inline-block'
                });
            }



            if (autoSaveMsg.is(':visible')) {
                if (autoHide) {
                    notifierTimeout = setTimeout(function () {
                        tb.find('.autosave-msg').fadeOut(500);
                    }, notifierHideDelay);
                }
            }
            else {
                autoSaveMsg.delay(300).fadeIn(300, function () {
                    if (autoHide) {
                        notifierTimeout = setTimeout(function () {
                            tb.find('.autosave-msg').fadeOut(500);
                        }, notifierHideDelay);
                    }
                });
            }
        }
    }


    this.destroy = function () {
        this.stop();
        lastSaveTime = 0;
        initialCompareString = lastCompareString = null;
    }

    this.stop = function()
    {
        clearInterval(intervalTimer);
        tb.find('.state-global .state-msg').show();
        tb.find('div.autosave-msg').remove();
    }

    this.removeMessage = function(){
        tb.find('div.autosave-msg').remove();
    }

    this.restart = function() {
        clearInterval(intervalTimer);

        // objects = formobject.find('input:not(.nodirty),select:not(.nodirty),textarea:not(.nodirty)');

        // Save every
        intervalTimer = setInterval(save, delay);
    };

    this.start = function () {
        tb = Core.getActiveStatusbar();
        if (!tb.length) {
            tb = $('#content-container');
        }


        // init compare
        initialCompareString = getCompareString();

        // set first compare
        lastCompareString = initialCompareString;

        // Save every
        intervalTimer = setInterval(save, delay);
    }

};