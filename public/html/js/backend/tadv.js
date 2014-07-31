// TinyMCE Advanced jQuery sortables
var tadvSortable;

(function (jQuery) {
    tadvSortable = {
        init: function (formID, windowID) {
            var self = tadvSortable;
            var win = $('#' + windowID), tbStart;


            $('#' + formID, win ).find('ul').sortable({

                connectWith: $('#' + formID, win ).find('.dcontainer'),
                items: '> li',
                cursor: 'move',
                change: function (event, ui) {
                    self.enablesave(formID, windowID);
                },
                start: function (event, ui) {
                    if (ui && ( toolbar_id = ui.item.parent().attr('id') )) {

                    }
                },
                stop: function (event, ui) {
                    var toolbar_id;
                    if (ui && ( toolbar_id = ui.item.parent().attr('id') )) {

                        if (toolbar_id != 'tadvpalette') {

                            ui.item.find('input').remove();
                            ui.item.append('<input name="'+ toolbar_id + '[]" value="'+ ui.item.attr('id').replace('mce-', '') +'"/>');

                            //ui.item.find('input.tadv-button').attr('name', toolbar_id + '[]');
                        }

                        // $('#tadvzones').find('input.tadv-button').attr('name', toolbar_id + '[]');
                        //ui.item.find('input.tadv-button').attr('name', toolbar_id + '[]');
                    }
                },
                /*
                 activate: function( event, ui ) {
                 if ( this.id !== ui.sender.attr('id') ) {
                 $(this).parent().css({ 'border-color': '#888' }); // , 'background-color': '#fafff9'
                 }
                 },
                 deactivate: function( event, ui ) {
                 $(this).parent().css({ 'border-color': '' }); // , 'background-color': ''
                 },
                 */
                revert: 300,
                opacity: 0.8,
                // placeholder: 'tadv-placeholder',
                forcePlaceholderSize: true,
                containment: 'document'
            });
/*

            var t1 = $("#toolbar_1", win);
            var t2 = $("#toolbar_2", win);
            var t3 = $("#toolbar_3", win);
            var t4 = $("#toolbar_4", win);


            if (!t1.find('li.dum').length) {
                t1.append('<li class="dum"></li>');
            }
            if (!t2.find('li.dum').length) {
                t2.append('<li class="dum"></li>');
            }
            if (!t3.find('li.dum').length) {
                t3.append('<li class="dum"></li>');
            }
            if (!t4.find('li.dum').length) {
                t4.append('<li class="dum"></li>');
            }

            var palette = $("#tadvpalette", win);

            t1.sortable({
                forceHelperSize: true,
                forcePlaceholderSize: true,
                connectWith: [t2, t3, t4, palette],
                items: 'li',
                cancel: 'li.dum',
                stop: function (event, ui) {
                    self.update(formID, windowID);
                },
                opacity: 0.9,
                //    containment: '#contain',
                tolerance: 'pointer',
                start: function (event, ui) {
                    ui.placeholder.width(ui.item.width());
                    self.enablesave(formID, windowID);
                }
            });

            t2.sortable({
                forcePlaceholderSize: true,
                connectWith: [t1, t3, t4, palette],
                items: 'li',
                cancel: 'li.dum',
                stop: function () {
                    self.update(formID, windowID);
                },
                opacity: 0.9,
                //       containment: '#contain',
                tolerance: 'pointer',
                start: function (event, ui) {
                    ui.placeholder.width(ui.item.width());
                    self.enablesave(formID, windowID);
                }
            });

            t3.sortable({
                forceHelperSize: true,
                forcePlaceholderSize: true,
                connectWith: [t1, t2, t4, palette],
                items: 'li',
                cancel: 'li.dum',
                stop: function () {
                    self.update(formID, windowID);
                },
                opacity: 0.9,
                //        containment: '#contain',
                tolerance: 'pointer',
                start: function (event, ui) {
                    ui.placeholder.width(ui.item.width());
                    self.enablesave(formID, windowID);
                }
            });

            t4.sortable({
                forceHelperSize: true,
                forcePlaceholderSize: true,
                connectWith: [t1, t2, t3, palette],
                items: 'li',
                cancel: 'li.dum',
                stop: function () {
                    self.update(formID, windowID);
                },
                opacity: 0.9,
                //      containment: '#contain',
                tolerance: 'pointer',
                start: function (event, ui) {
                    ui.placeholder.width(ui.item.width());
                    self.enablesave(formID, windowID);
                }
            });

            palette.sortable({
                forceHelperSize: true,
                forcePlaceholderSize: true,
                connectWith: [t1, t2, t3, t4],
                items: 'li',
                cancel: 'li.dum',

                start: function (e, ui) {
                    ui.placeholder.width(ui.item.width());
                },
                stop: function () {
                    self.update(formID, windowID);
                },
                opacity: 0.9,
                //       containment: '#contain',
                tolerance: 'pointer',
                change: function (event, ui) {
                    self.enablesave(formID, windowID);
                }
            });

            this.update(formID, windowID);

            $(window).resize(function () {
                self.update(formID, windowID);
            });
            */
        },
        I: function (a) {
            return document.getElementById(a);
        },
        serialize: function (formID, windowID) {
            var tb1, tb2, tb3, tb4;
            var win = $('#' + windowID);

            tb1 = $('#toolbar_1', win).sortable('serialize', {
                expression: '([^_]+)_(.+)'
            });
            tb2 = $('#toolbar_2', win).sortable('serialize', {
                expression: '([^_]+)_(.+)'
            });
            tb3 = $('#toolbar_3', win).sortable('serialize', {
                expression: '([^_]+)_(.+)'
            })
            tb4 = $('#toolbar_4', win).sortable('serialize', {
                expression: '([^_]+)_(.+)'
            })

            $('#toolbar_1order', win).val(tb1);
            $('#toolbar_2order', win).val(tb2);
            $('#toolbar_3order', win).val(tb3);
            $('#toolbar_4order', win).val(tb4);

            if ((tb1.indexOf('wp_adv') != -1 && !tb2) ||
                (tb2.indexOf('wp_adv') != -1 && !tb3) ||
                (tb3.indexOf('wp_adv') != -1 && !tb4) ||
                tb4.indexOf('wp_adv') != -1) {
                $('#sink_err', win).css('display', 'inline');
                return false;
            }

        },
        reset: function (formID, windowID) {

            var pd = tadvSortable.I('tadvpalette');
            if (!pd || !pd.length)
                return;
            if (pd.childNodes.length > 6) {
                var last = pd.lastChild.previousSibling;
                pd.style.height = last.offsetTop + last.offsetHeight + 30 + "px";
            }
            else {
                pd.style.height = "60px";
            }
        },
        update: function (formID, windowID) {
            var t = tadvSortable, w;
            var win = $('#' + windowID);

            t.reset();
            $('#too_long', win).css('display', 'none');
            $('#sink_err', win).css('display', 'none');

            $('.container', win).each(function (index, o) {
                var kids = o.childNodes, tbwidth = o.clientWidth, W = 0;

                for (var i = 0; i < kids.length; i++) {
                    if (w = kids[i].offsetWidth)
                        W += w;
                }

                if ((W + 8) > tbwidth)
                    $('#too_long', win).css('display', 'inline');
            });

        },
        enablesave: function (formID, windowID) {
            var t = tadvSortable;
            var toolBar = Desktop.getActiveWindowToolbar();
            toolBar.find('.save').enableButton();
            Form.makeDirty(formID, windowID);
        }
    }

    $.fn.tadvSortable = function () {


    };

}(jQuery));

