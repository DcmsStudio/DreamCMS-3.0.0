/**
 * DreamCMS 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP Version 5
 *
 * @package     Importer
 * @version     3.0.0 Beta
 * @category    Config
 * @copyright    2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Base.php
 */
var dblclickbuf = {
    'selected': false,
    'value': ''
};

var thewin = $('#' + Win.windowID);

function insertxpath(e, obj) {
    if (dblclickbuf.selected) {
        $(obj).val($(obj).val() + dblclickbuf.value);

        thewin.find('.xml-element[title*="/' + dblclickbuf.value.replace('{', '').replace('}', '') + '"]').removeClass('selected');
        dblclickbuf.value = '';
        dblclickbuf.selected = false;
    }
}

$.fn.xml = function (opt) {
    if (!this.length)
        return this;

    var $self = this, thewin = $('#' + Win.windowID);
    var opt = opt || {};
    var action = {};
    if ('object' == typeof opt) {
        action = opt;
    } else {
        action[opt] = true;
    }
    action = $.extend({init: !this.data('initialized')}, action);

    if (action.init) {
        this.data('initialized', true);
        // add expander
        this.find('.xml-expander').on('click', function () {
            var method;
            if ('-' == $(this).text()) {
                $(this).text('+');
                method = 'addClass';
            } else {
                $(this).text('-');
                method = 'removeClass';
            }
            // for nested representation based on div
            $(this).parent().find('> .xml-content')[method]('collapsed');
            // for nested representation based on tr
            var $tr = $(this).parent().parent().filter('tr.xml-element').next()[method]('collapsed');


            Tools.scrollBar($($self));


        });
    }


    if (action.dragable) { // drag & drop
        var _w;
        var _dbl = 0, ht;

        var $drag = $('__drag');
        $drag.length || ($drag = $('<input type="text" id="__drag" readonly="readonly" />'));

        $drag.addClass('nofocus').css({
            position: 'absolute',
            background: 'transparent',
            top: '-50px',
            left: '0!important',
            margin: '0',
            border: 'none',
            lineHeight: '1px',
            opacity: 0,
            cursor: 'pointer',
            borderRadius: '0',
            zIndex: 99
        });

		if (Desktop.isWindowSkin) { $drag.appendTo(thewin.find('.window-body:first')); }
		else {
			$drag.appendTo('body');
		}

        $drag.unbind('mousedown.xmlnode').bind('mousedown.xmlnode',function (e) {
            if (_dbl)
                return;
            var _x = e.pageX - $drag.offset().left;
            var _y = e.pageY - $drag.offset().top;
            if (_x < 4 || _y < 4 || $drag.width() - _x < 0 || $drag.height() - _y < 0) {
                return;
            }
            $drag.width($(document.body).width() - $drag.offset().left - 5).css('opacity', 1);
            $drag.select();
            _dbl = true;
            setTimeout(function () {
                _dbl = false;
            }, 400);
        }).unbind('mouseup.xmlnode').bind('mouseup.xmlnode',function (e) {

                setTimeout(function () {
                    $drag.css('opacity', 0).css('width', _w);
                    $drag.blur();
                }, 100);

            }).unbind('dblclick.xmlnode').bind('dblclick.xmlnode', function () {
                if (dblclickbuf.selected) {
                    thewin.find('.xml-element[title*="/' + dblclickbuf.value.replace('{', '').replace('}', '') + '"]').removeClass('selected');

                    if ($(this).val() == dblclickbuf.value) {
                        dblclickbuf.value = '';
                        dblclickbuf.selected = false;
                    }
                    else {
                        dblclickbuf.selected = true;
                        dblclickbuf.value = $(this).val();
                        thewin.find('.xml-element[title*="/' + $(this).val().replace('{', '').replace('}', '') + '"]').addClass('selected');
                    }
                }
                else {
                    dblclickbuf.selected = true;
                    dblclickbuf.value = $(this).val();
                    thewin.find('.xml-element[title*="/' + $(this).val().replace('{', '').replace('}', '') + '"]').addClass('selected');
                }
            });

        thewin.find('#title, textarea[name=teaser], textarea[name=content], input[name^=custom_name], textarea[name^=custom_value], input[name^=featured_image], input[name^=unique_key]')
            /*
             .unbind('dragenter.xmlhandler')
             .bind('dragenter.xmlhandler', function (e) {
             if (e.preventDefault) {
             e.preventDefault();
             }
             //$(this).trigger('focus.xmlhandler');
             return false;

             })

             .unbind('dragover.xmlhandler')
             .bind('dragover.xmlhandler', function (e) {
             if (e.preventDefault) {
             e.preventDefault();
             }
             $(this).focus();
             return false;
             })


             .unbind('dragleave.xmlhandler')
             .bind('dragleave.xmlhandler', function (e) {
             if (e.preventDefault) {
             e.preventDefault();
             }
             $(this).blur();
             return false;
             })
             .unbind('drop.xmlhandler')
             .bind('drop.xmlhandler', function (e) {


             if ($drag.val()) {
             dblclickbuf.selected = true;
             dblclickbuf.value = $drag.val();
             thewin.find('.xml-element[title*="/' + $drag.val().replace('{', '').replace('}', '') + '"]').removeClass('selected');
             }
             $(this).trigger('focus.xmlhandler');
             $(this).blur();
             //$(this).trigger('focus.xmlhandler');

             }) */
            .unbind('focus.xmlhandler')
            .bind('focus.xmlhandler', function (e) {
                insertxpath(e, this);
            });
        /*
         .bind('mouseenter.xmlhandler', function (e) {
         $(this).addClass('hoverInsertMove');
         })
         .bind('mouseleave.xmlhandler', function (e) {
         $(this).removeClass('hoverInsertMove');
         })
         .bind('mouseup.xmlhandler', function (e) {
         if ($(this).hasClass('hoverInsertMove')) {
         $(this).trigger('focus.xmlhandler');
         $(this).removeClass('hoverInsertMove');
         }
         }); */

        $(document).unbind('mousemove.xmlhandler').bind('mousemove.xmlhandler',function () {
            if (parseInt($drag.css('opacity')) != 0) {

                setTimeout(function () {
                    $drag.css('opacity', 0);
                }, 50);

                setTimeout(function () {
                    $drag.css('width', _w);
                }, 500);

            }
        }).unbind('mouseup.xmlhandler').bind('mouseup.xmlhandler', function (e) {

            });

        if ($('#content').length && window.tinymce != undefined)
            tinymce.dom.Event.add('wp-content-editor-container', 'click', function (e) {
                if (dblclickbuf.selected) {
                    tinyMCE.activeEditor.selection.setContent(dblclickbuf.value);
                    thewin.find('.xml-element[title*="' + dblclickbuf.value.replace('{', '').replace('}', '') + '"]').removeClass('selected');
                    dblclickbuf.value = '';
                    dblclickbuf.selected = false;
                }
            });

        this.find('.xml-tag.opening > .xml-tag-name, .xml-attr-name').each(function () {
            var $this = $(this);
            var xpath = '.';
            if ($this.is('.xml-attr-name'))
                xpath = '{' + ($this.parents('.xml-element:first').attr('title').replace(/^\/[^\/]+\/?/, '') || '.') + '/@' + $this.html().trim() + '}';
            else
                xpath = '{' + ($this.parent().parent().attr('title').replace(/^\/[^\/]+\/?/, '') || '.') + '}';

            $this.mouseover(function (e) {
                $drag.val(xpath).offset({left: $this.offset().left - 2, top: $this.offset().top - 2}).width(_w = $this.width() + 4).height($this.height() + 4);
            });
        }).eq(0).mouseover();
    }
    return this;
};

// selection logic
$('form.choose-elements').each(function () {
    var $form = $(this);
    $form.find('.xml').xml();
    var $input = $form.find('input[name="xpath"]');
    var $next_element = $form.find('#next_element');
    var $prev_element = $form.find('#prev_element');
    var $goto_element = $form.find('#goto_element');
    var $get_default_xpath = $form.find('#get_default_xpath');
    var $root_element = $form.find('#root_element');

    var $xml = $('.xml');
    $form.find('.xml-tag.opening').live('mousedown',function () {
        return false;
    }).live('dblclick', function () {
            if ($form.hasClass('loading'))
                return; // do nothing if selecting operation is currently under way
            $input.val($(this).parents('.xml-element').first().attr('title').replace(/\[\d+\]$/, '')).change();
        });


    var xpathChanged = function () {
        if ($input.val() == $input.data('checkedValue'))
            return;
        var xpath_elements = $input.val().split('[');
        var xpath_parts = xpath_elements[0].split('/');
        xpath_elements[0] = '';
        $input.val('/' + xpath_parts[xpath_parts.length - 1] + ((xpath_elements.length) ? xpath_elements.join('[') : ''));
        $form.addClass('loading');
        $form.find('.xml-element.selected').removeClass('selected'); // clear current selection
        // request server to return elements which correspond to xpath entered
        $input.attr('readonly', true).unbind('change', xpathChanged).data('checkedValue', $input.val());
        $xml.css({'visibility': 'hidden'});
        $xml.parents('fieldset:first').addClass('preload');
        $('.ajax-console').load('admin.php?page=pmxi-admin-import&action=evaluate', {xpath: $input.val(), show_element: $goto_element.val(), root_element: $root_element.val()}, function () {
            $input.attr('readonly', false).change(function () {
                $goto_element.val(1);
                xpathChanged();
            });
            $form.removeClass('loading');
            $xml.parents('fieldset:first').removeClass('preload');
        });
    };
    $next_element.click(function () {
        var show_element = Math.min((parseInt($goto_element.val()) + 1), parseInt($('.matches_count').html()));
        $goto_element.val(show_element).html(show_element);
        $input.data('checkedValue', '');
        xpathChanged();
    });
    $prev_element.click(function () {
        var show_element = Math.max((parseInt($goto_element.val()) - 1), 1);
        $goto_element.val(show_element).html(show_element);
        $input.data('checkedValue', '');
        xpathChanged();
    });
    $goto_element.change(function () {
        var show_element = Math.max(Math.min(parseInt($goto_element.val()), parseInt($('.matches_count').html())), 1);
        $goto_element.val(show_element);
        $input.data('checkedValue', '');
        xpathChanged();
    });
    $get_default_xpath.click(function () {
        $root_element.val($(this).attr('root'));
        $goto_element.val(1);
        $input.val($(this).attr('rel'));
        xpathChanged();
    });
    $('.change_root_element').click(function () {
        $root_element.val($(this).attr('rel'));
        $goto_element.val(1);
        $input.val('/' + $(this).attr('rel'));
        xpathChanged();
    });


    $input.change(function () {
        $goto_element.val(1);
        xpathChanged();
    }).change();

    $input.keyup(function (e) {
        if (13 == e.keyCode)
            $(this).change();
    });
});

// tag preview
$.fn.tag = function () {
    this.each(function () {
        var $tag = $(this);

        $tag.removeData();


        $tag.xml('dragable');
    });
    return this;
};
// [/xml representation dynamic]


function updateTreePos(scrollTopPos) {
    if (thewin) {
        $('#tree-repos', thewin).css({position: 'absolute', top: scrollTopPos});
    }
}

function initImporter(windowID, step, options) {
    thewin = $('#' + windowID);


    if (step == 0) {

        thewin.find('#toolbar-step1,#toolbar-step2').hide();


        // no enter in this forms
        thewin.find('input,select,textarea').not('*[type="submit"]').bind('keydown.dcmsimporter', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
            }
        });


        if ((Desktop.isWindowContainer && $('#LaunchPadCase_fileman').length == 1) || $('#main-menu' ).find('li[controller=fileman]' ).length) {


            thewin.find('#browse-files').on('click', function () {

				if (Desktop.isWindowContainer) {
                	thewin.data('WindowManager').toggleFileSelectorPanel(true);
				}
				else {
					Core.Tabs.toggleFileSelectorPanel(true);
				}

                thewin.find('#fm').registerEvents({
                    onSelectFile: function (filepath) {
                        if (filepath && filepath.match(/.*\.(zip|tar|gz|xml|csv)$/ig)) {
                            window.fileselectionPath = filepath;
                            //wm.$el.data('WindowManager').close();

                            thewin.find('#server-file-path').val(filepath);
                            thewin.find('#next-step').hide();
                            if (Desktop.isWindowContainer) { thewin.data('WindowManager').toggleFileSelectorPanel(false); }
							else {
								Core.Tabs.toggleFileSelectorPanel(false);
							}
                        }
                        else {
                            jAlert('Only *.zip, *.tar, *.gz, *.xml and *.csv Files allowed!', 'Info');
                        }
                    }
                });

            });


            /*


             var width = (thewin.width() - 50);
             var height = thewin.find('.window-body-content').height() - 40;


             thewin.addClass('use-overflow');

             var l, container = $('<div id="fm-slider" class="inline-window-slider" style="position:absolute;"></div>');
             container.height(0).width(width);


             if (thewin.width() > width) {
             l = ((thewin.width() - width) / 2);
             }
             else {
             l = 0 - ((width - thewin.width()) / 2);
             }



             container.css({
             left: l,
             top: (0 - height)
             });

             container.append($('<div style="padding:0">'));
             container.hide();

             thewin.find('.win-content').append(container);



             thewin.find('#browse-files').on('click', function () {

             var itemData = $('#LaunchPadCase_fileman').data('itemData');

             if (typeof itemData.selectFile == 'undefined') {
             itemData.selectFile = true;
             }

             itemData.url = itemData.url + '&mode=fileselector';

             var requestdata, opt = {
             WindowUrl: itemData.url,
             WindowMinimize: false,
             WindowResizeable: true,
             WindowHeight: 450,
             nopadding: true,
             Controller: 'fs',
             Action: 'index',
             app: 'browse-files',
             onAfterClose: function (e, wm, callback)
             {
             if (window.fileselectionPath) {
             thewin.find('#server-file-path').val(window.fileselectionPath);

             delete window.fileselectionPath;
             }


             if (Tools.isFunction(callback))
             {
             callback();
             delete window.fileselectionPath;
             }
             },
             onAfterCreated: function (wm, _callback, ajaxContent)
             {
             //wm.$el.attr('app', 'browse-files');
             wm.$el.addClass('select-file-browser');


             Desktop.windowWorkerOn = false;
             Application.currentWindowID = wm.id;
             Win.setActive(wm.id);



             setTimeout(function () {
             if (requestdata.maincontent)
             {
             var l = $(requestdata.maincontent).filter('script').length;
             if (l) {
             console.log('Eval Scripts after window Created');
             Tools.eval($(requestdata.maincontent));
             }
             }
             else if (ajaxContent)
             {
             if ($(ajaxContent).filter('script').length) {
             console.log('Eval Scripts after window Created');
             Tools.eval($(ajaxContent));
             }
             }
             else {
             console.log('No Eval Scripts for after window created found');
             }

             Desktop.windowWorkerOn = false;


             wm.$el.find('#fm').registerEvents({
             onSelectFile: function (filepath) {
             if (filepath && filepath.match(/.*\.(zip|tar|gz|xml|csv)$/ig)) {
             window.fileselectionPath = filepath;
             //wm.$el.data('WindowManager').close();
             wm.$el.find('#next-step').show();
             }
             else {
             jAlert('Only *.zip, *.tar, *.gz, *.xml and *.csv Files allowed!', 'Info');
             }
             }
             });

             $('#desktop').unmask();
             $.pagemask.hide();

             if (typeof _callback === 'function')
             {
             setTimeout(function () {
             _callback();
             }, 10);
             }
             }, 5);
             }
             };









             if (thewin.find('#fm-slider>div>div:first').length == 0) {



             $.ajax({
             url: itemData.url,
             type: 'GET',
             dataType: 'json',
             timeout: 30000,
             data: {},
             async: false,
             global: false,
             success: function (data)
             {
             if (Tools.responseIsOk(data))
             {
             data.applicationMenu = false;
             data.screensize = null;

             requestdata = data;

             opt.WindowToolbar = data.toolbar;
             opt.WindowTitle = data.pageCurrentTitle;
             opt.WindowContent = data.maincontent;

             if (data.onAfterOpen)
             {
             opt.onAfterOpen = data.onAfterOpen;
             }

             opt.ajaxData = data;


             thewin.find('#fm-slider').find('div:first').append(data.maincontent);



             // register cancel button
             thewin.find('#fm-slider').find('#cancel-fm').on('click', function (e) {
             thewin.find('#fm-slider').animate({
             top: 0 - thewin.find('#fm-slider').height()
             }, {
             queue: false,
             duration: 300,
             complete: function () {
             $(this).hide(); //.find('div:first').empty();
             }
             });
             });

             // now show the filemanager
             thewin.find('#fm-slider').show();

             // execute global eval
             Tools.eval(thewin.find('#fm-slider'));

             setTimeout(function () {
             thewin.find('#fm-slider').animate({
             top: 0,
             height: thewin.find('.window-body-content').height() - 40
             }, {
             queue: true,
             duration: 300,
             complete: function () {
             var con = $(this);



             thewin.data('WindowManager').set('onResize', function (event, ui, wm, sizes) {
             var l, sw = wm.$el.find('#fm-slider').width();


             if (sw > sizes.width) {
             sw = (sizes.width - 50);
             wm.$el.find('#fm-slider').width(sw);
             }
             else {
             if (sw < (sizes.width - 50)) {
             wm.$el.find('#fm-slider').width((sizes.width - 50));
             }
             }

             if (sizes.width > sw) {
             l = ((sizes.width - sw) / 2);
             }
             else {
             l = 0 - ((sw - sizes.width) / 2);
             }


             var h = wm.$el.find('.window-body-content').height() - 40;


             wm.$el.find('#fm-slider').css({'left': l, height: h});
             wm.$el.find('#fm').resizePanels(false);
             });

             thewin.data('WindowManager').set('onResizeStart', function () {
             $('#' + Win.windowID).find('#fm div.header th,#fm div.body tr:first td').attr('style', '');
             });

             thewin.data('WindowManager').set('onResizeStop', function (event, ui, wm, sizes) {
             var fm = wm.$el.find('#fm');
             var l, sw = wm.$el.find('#fm-slider').width();


             if (sw > sizes.width) {
             sw = (sizes.width - 50);
             wm.$el.find('#fm-slider').width(sw);
             }
             else {
             if (sw < (sizes.width - 50)) {
             wm.$el.find('#fm-slider').width((sizes.width - 50));
             }
             }

             if (sizes.width > sw) {
             l = ((sizes.width - sw) / 2);
             }
             else {
             l = 0 - ((sw - sizes.width) / 2);
             }


             var h = wm.$el.find('.window-body-content').height() - 40;


             wm.$el.find('#fm-slider').css({'left': l, height: h});
             setTimeout(function () {
             fm.resizePanels(function ()
             {
             fm.find('.treelistInner,.body').css({overflow: ''});
             Tools.scrollBar(fm.find('.treelistInner'));
             Tools.scrollBar(fm.find('.listview .body>:first-child'));
             Tools.scrollBar(fm.find('iconview.body'));

             setTimeout(function () {
             fm.fixTableWidth();
             }, 50);
             });
             }, 10);
             });


             // register the events
             con.find('#fm').registerEvents({
             onSelectFile: function (filepath) {
             if (filepath && filepath.match(/.*\.(zip|tar|gz|xml|csv)$/ig)) {
             window.fileselectionPath = filepath;

             thewin.find('#server-file-path').val(filepath);
             delete window.fileselectionPath;

             //wm.$el.data('WindowManager').close();
             //wm.$el.find('#next-step').show();
             thewin.find('#fm-slider').animate({
             top: 0 - thewin.find('#fm-slider').height(),
             height: 0
             }, {
             queue: false,
             duration: 300,
             complete: function () {
             $(this).hide(); //.find('div:first').empty();
             }
             });
             thewin.find('#next-step').show();
             }
             else {
             jAlert('Only *.zip, *.tar, *.gz, *.xml and *.csv Files allowed!', 'Info');
             }
             }
             });

             $(this).find('#fm').resizePanels(false);
             }
             });

             }, 100);


             // create new window
             // Tools.createPopup(data.maincontent, opt);
             }


             }
             });


             }
             else {
             thewin.find('#fm-slider').show().animate({
             top: 0,
             height: thewin.find('.window-body-content').height() - 40
             }, {
             queue: true,
             duration: 300,
             complete: function () {

             }
             });
             }



             });




             */


        }


        thewin.find('input:radio').on('change', function () {
            var val = $(this).val();
            if (!$(this).is('checked')) {

                if (val == 'upload') {
                    thewin.find('#run-step').hide();
                }
                else {
                    thewin.find('#run-step').show();
                }

                $('.view-toggle:visible').slideUp('fast');
                $('#from-' + val).slideDown('fast');

            }
            else {
                $('.view-toggle:visible').slideUp('fast');
            }
        });


        thewin.find('#run-step').on('click', function () {
            var btn = $(this);
            if (btn.hasClass('disabled')) {
                return;
            }

            var str, checkval = thewin.find('#import-upload').find('input[name="importFrom"]:checked').val();

            if (checkval) {


                var url = 'admin.php?adm=importer&action=import&do=import';
                if (checkval == 'upload') {
                    return;
                }

                if (checkval == 'url') {
                    str = thewin.find('#file-url').val();
                    url += '&mode=url&filepath=' + encodeURIComponent(str);
                }

                if (checkval == 'server') {
                    str = thewin.find('#server-file-path').val();
                    url += '&mode=file&filepath=' + encodeURIComponent(str);
                }

                $.get(url, function (data) {
                    if (Tools.responseIsOk(data)) {
                        btn.hide();
                        thewin.find('#next-step').show();
                    }
                });
            }

        });

        thewin.find('#next-step').on('click', function () {

            if ($(this).hasClass('disabled')) {
                return;
            }
			if (Desktop.isWindowSkin) {
				var windata = thewin.data('WindowManager');
				windata.mask('warten...');
				windata.set('WindowURL', 'admin.php?adm=importer&action=import&step=1').set('Url', 'admin.php?adm=importer&action=import&step=1').ReloadWindow(function (data) {
					windata.unmask();
				});
			}
			else {
				var tabContent = Core.getTabContent();
				var contentTabs = Core.getContentTabs();
				var toolbar = Core.getToolbar();

				$.ajax({
					url: 'admin.php?adm=importer&action=import&step=1',
					async: true,
					cache: false,
					global: false
				} ).done(function(data){
						if (Tools.responseIsOk(data)) {
							if (data.maincontent) {
								tabContent.empty().append(data.maincontent);
							}

							if (data.toolbar) {
								toolbar.empty().append(data.toolbar);
							}


							if ( tabContent.find( '.tabcontainer' ).length ) {

								var hash = tabContent.attr('id' ).replace('root-window-', '');

								if ( !$( '#content-tabs-' + hash ).length ) {
									var contentTabContainer = $( '<div class="content-tabs" style="display:block"></div>' ).attr( 'id', 'content-tabs-' + hash );
									contentTabContainer.appendTo( $( '#main-content-tabs' ) );
									tabContent.find( '.tabcontainer' ).appendTo( $( '#content-tabs-' + hash ) );
									$( '#main-content-tabs' ).show();
								}
								else {
									tabContent.find( '.tabcontainer' ).remove();
									$( '#main-content-tabs' ).hide();
								}
								Core.Tabs.bindContentTabEvents( $( '#content-tabs-' + hash ), hash );
							}

							$(window ).trigger('resize');

							if ( $( data.maincontent ).filter( 'script' ).length ) {

								//   console.log('Eval Scripts after window Created');
								Tools.eval( $( data.maincontent ) );
							}
						}
					});

			}
        });


        Tools.MultiUploadControl({
            control: 'upload-container',
            url: "admin.php",
            postParams: {
                adm: "importer",
                action: "upload"
            },
            file_queue_limit: 6,
            max_upload_files: 50,
            file_type_mask: '*.csv,*.xml,*.zip',
            file_type_text: "{trans('Alle Dateien')}",
            filePostParamName: 'Filedata',
            onAdd: function () {
                Win.redrawWindowHeight(false, true);
            },
            onSuccess: function (data, evaldata, file, listItem) {

                thewin.find('#run-step').hide();
                thewin.find('#next-step').show();
            }
        });


    }
    else if (step == 1) {

        thewin.find('#toolbar-step2').remove();
        thewin.find('#toolbar-step1').show();

        $('#tree-repos', thewin).css({right: 0, position: 'absolute'});

		if (Desktop.isWindowSkin) {
        	thewin.data('WindowManager').set('onScroll', updateTreePos);
			thewin.refreshScrollbars();
		}
		else {

			var hash = Core.Tabs.getActiveTabHash();
			var use = $( '#content-' + hash );
			if ( use.attr( 'fm' ) ) {
				use = use.find( '.content-wrap:first' );
			}

			if ( $( '#content-' + hash ).find( '.subwindow:visible' ).length == 1 ) {
				var sub = $( '#content-' + hash ).find( '.subwindow:visible' );
				if ( !sub.find( '.gc' ).length && !sub.hasClass( 'gc' ) ) {
					use.nanoScroller( {scrollContent: $( '#content-' + hash ), onScroll: updateTreePos} );
				}
				else {
					sub.css( {overflow: 'hidden'} );
				}
			}
			else if ( $( '#content-' + hash ).find( '.rootwindow:visible' ).length == 1 ) {
				var sub = $( '#content-' + hash ).find( '.rootwindow:visible' );
				if ( !sub.find( '.gc' ).length && !sub.hasClass( 'gc' ) ) {
					use.nanoScroller( {scrollContent: $( '#content-' + hash ), onScroll: updateTreePos} );
				}
				else {
					sub.css( {overflow: 'hidden'} );
				}
			}



			setTimeout(function() {
				$(window ).trigger('resize');
			}, 1000);


		}



		if (Desktop.isWindowSkin) {

			thewin.data('WindowManager').set('onResize', function () {
				$('#body-' + Win.windowID).find('#xml-tree-scroll').height(($('#body-' + Win.windowID).height() - 70));
				Tools.scrollBar($('#body-' + Win.windowID).find('#xml-tree-scroll .xml-tree'));
			});

			thewin.data('WindowManager').set('onResizeStop', function () {
				$('#body-' + Win.windowID).find('#xml-tree-scroll').height(($('#body-' + Win.windowID).height() - 70));
				Tools.scrollBar($('#body-' + Win.windowID).find('#xml-tree-scroll .xml-tree'));
			});
		}
		else {
			$(window ).bind('resize.importer', function() {
				if (!$('#xml-tree-scroll' ).length) {
					$(window ).unbind('resize.importer');
					return;
				}
				$('#' + Win.windowID).find('#xml-tree-scroll').height(($('#content-container').height() - 70));
				Tools.scrollBar($('#' + Win.windowID).find('#xml-tree-scroll .xml-tree'));

			});
		}


        setTimeout(function () {
            if (Desktop.isWindowSkin) {
				$('#body-' + Win.windowID).find('#xml-tree-scroll').height(($('#body-' + Win.windowID).height() - 70));
				Tools.scrollBar($('#body-' + Win.windowID).find('#xml-tree-scroll .xml-tree'));
			}
			else {
				$('#' + Win.windowID).find('#xml-tree-scroll').height(($('#content-container').height() - 70));
				Tools.scrollBar($('#' + Win.windowID).find('#xml-tree-scroll .xml-tree'));
			}

        }, 50);


        //
        thewin.find('#xml-tree-scroll .xml-tree').tag();


        // no enter in this forms
        thewin.find('input,select,textarea').not('*[type="submit"]').on('keydown', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
            }
        });


        thewin.find('#prev-record').unbind('click').on('click', function () {
            var _current = $(this).parents('.box-inner:first').find('.current-record:first');
            $(this).parents('.box-inner:first').mask('loading...');
            setTimeout(function () {
                $.get('admin.php?adm=importer&action=import&step=1&index=1', function (data) {
                    if (Tools.responseIsOk(data)) {
                        if (options.currentRecord > 1)
                            options.currentRecord -= 1;

                        _current.text(options.currentRecord);

                        _current.parents('.box-inner:first').find('.xml-tree').empty().append(data.nodes[0].node);

                        thewin.find('#xml-tree-scroll .xml-tree').tag();

                        Tools.scrollBar(_current.parents('.box-inner:first').find('.xml-tree'));
                    }

                    _current.parents('.box-inner:first').unmask();
                });
            }, 20);
        });

        thewin.find('#next-record').unbind('click').on('click', function () {
            var _current = $(this).parents('.box-inner:first').find('.current-record:first');

            $(this).parents('.box-inner:first').mask('loading...');
            setTimeout(function () {


                $.get('admin.php?adm=importer&action=import&step=1&index=2', function (data) {
                    if (Tools.responseIsOk(data)) {
                        if (options.currentRecord < options.totalrecords)
                            options.currentRecord += 1;
                        _current.text(options.currentRecord);

                        _current.parents('.box-inner:first').find('.xml-tree').empty().append(data.nodes[0].node);

                        thewin.find('#xml-tree-scroll .xml-tree').tag();


                        Tools.scrollBar(_current.parents('.box-inner:first').find('.xml-tree'));

                    }


                    _current.parents('.box-inner:first').unmask();

                });

            }, 20);
        });

		var tb = thewin;
		if (!Desktop.isWindowSkin) {
			tb = Core.getToolbar();
		}


		tb.find('#get-preview').unbind('click').on('click', function (e) {
            thewin.mask('Loading preview...');
            setTimeout(function () {

                var form = thewin.find('#import-form'), postdata = form.serialize();
                postdata.step = 'preview';


                $.post('admin.php', postdata + '&step=preview', function (data) {

                    thewin.unmask();

                    if (Tools.responseIsOk(data)) {

                        var opt = {
                            WindowUrl: 'admin.php?adm=importer&action=import&step=preview',
                            WindowMinimize: false,
                            WindowResizeable: true,
                            WindowHeight: 450,
                            nopadding: false,
                            Controller: 'importer',
                            Action: 'index',
                            app: 'importer',
                            WindowTitle: data.WindowTitle
                        };

                        Tools.createPopup(data.maincontent, opt);
                    }
                });
            }, 20);
        });


    }

}

