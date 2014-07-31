
/**
 * This function opens up a window for editing a specific block item
 */


function EditBlock(id, type) {
    var id = parseInt(id);

    if (id < 1) {
        return;
    }

    var tbHeight = '300';
    var tbWidth = '600';

    if (type == 'htmlBlock') {
        tbHeight = '200'
    } else if (type == 'customImage') {
        tbHeight = '230'
        tbWidth = '350';
    }

    // create the window
    var win = $.fn.window.create({
        uri: 'remote.php?section=layout&action=editBlock&blockid=' + id + '&type=' + type,
        title: 'Edit a Block',
        width: tbWidth,
        autoOpen: true
    });

    // window buttons
    var submitBtn = $('<button type="submit" style="font-weight: bold;">Save &amp; Add</button>');
    var cancelBtn = $('<button type="button" style="float: left;">Cancel</button>');

    // set button events
    submitBtn.bind('click', submitBlock);
    cancelBtn.bind('click', function() {
        win.close();
    });

    // set the buttons and open it
    win.buttons(submitBtn.add(cancelBtn));
}
function DeleteBlock(deleteObj) {

    var id = SiteLayout.RemoveCopyName($(deleteObj).parents('.layoutListsMenusItem').attr('id'));
    var sectionId = $(deleteObj).parents('.layoutListsAccordian').attr('id');

    var blockName = $(deleteObj).parent().text();
    blockName = blockName.replace(/^\s+|\s+$/g, '');

    if (id.substr(0, 4) == 'list') {
        if (sectionId == 'layoutListsMenus') {
            if (confirm('The \'%s\' menu/list will be removed from all web pages and deleted. Are you sure you want to continue? Click OK to confirm.'.replace('%s', blockName))) {
                SiteLayout.HasMadeChanges = true;
                $.getJSON('remote.php?section=layout&action=deletelist&listid=' + id, function(json) {
                    if (json.success) {
                        $('#outerLayoutsContainer .' + id).remove();
                        $('#MainMessage').successMessage('The block \'%s\' has been successfully removed from all web pages and deleted.'.replace('%s', blockName));
                    } else {
                        $('#MainMessage').errorMessage('Unable to delete the \'%s\' block.'.replace('%s', blockName));
                    }

                    SiteLayout.RefreshOverlays();
                });
            }
        } else {
            if (confirm("Are you sure you want to remove this list? Remember, you can re-add it later from the 'Menus & Lists' section on the left.\r\n\r\nClick OK to confirm this list should be removed.".replace('%s', blockName))) {
                $(deleteObj).parents('.layoutListsMenusItem').remove();
            }
        }
    } else {
        if (sectionId == 'layoutListsSaved') {
            //	block is being deleted from the saved lists
            if (confirm("The \'%s\' block will be removed from all web pages and deleted. Are you sure you want to continue? Click OK to confirm".replace('%s', blockName))) {
                SiteLayout.HasMadeChanges = true;
                $.getJSON('remote.php?section=layout&action=deleteblock&blockid=' + id, function(json) {
                    if (json.success) {
                        $('#outerLayoutsContainer .' + id).remove();
                        $('#MainMessage').successMessage('The block \'%s\' has been successfully removed from all web pages and deleted.'.replace('%s', blockName));
                    } else {
                        $('#MainMessage').errorMessage('Unable to delete the \'%s\' block.'.replace('%s', blockName));
                    }

                    SiteLayout.RefreshOverlays();
                });
            }
        } else {
            //	block is being deleted from the layout
            if (confirm("Are you sure you want to remove this block? Remember, you can re-add it later from the 'Saved Blocks' section on the left.\r\n\r\nClick OK to confirm this block should be removed.".replace('%s', blockName))) {
                $(deleteObj).parents('.layoutListsMenusItem').remove();
            }
        }
    }
}

var dropped = false;
var draggable_sibling;
var droppeds = 0;
var Layouter = {
    layoutContainer: null,
    // col3 is middle
    // col2 is right
    // col1 is left
    columnorder2: {
        0: ['left', 'middle'],
        1: ['middle', 'left'],
        2: ['left', 'middle'],
        3: ['middle', 'left']
    },
    columnorder3: {
        0: ['left', 'middle', 'right'],
        1: ['right', 'middle', 'left'],
        2: ['left', 'right', 'middle'],
        3: ['middle', 'right', 'left'],
        4: ['right', 'left', 'middle'],
        5: ['middle', 'left', 'right']
    },
    dropelements: {
        dp_5050: '<div class="subcolumns itemBox cbox" id="xxx1">\n\t<div class="c50l" id="xxx2">xxx0</div>\n\t<div class="c50r" id="xxx3">xxx0</div>\n</div>',
        dp_3366: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c33l" id="xxx2">xxx0</div><div class="c66r" id="xxx3">xxx0</div></div>',
        dp_6633: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c66l" id="xxx2">xxx0</div><div class="c33r" id="xxx3">xxx0</div></div>',
        dp_3862: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c38l" id="xxx2">xxx0</div><div class="c62r" id="xxx3">xxx0</div></div>',
        dp_6238: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c62l" id="xxx2">xxx0</div><div class="c38r" id="xxx3">xxx0</div></div>',
        dp_2575: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c25l" id="xxx2">xxx0</div><div class="c75r" id="xxx3">xxx0</div></div>',
        dp_7525: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c75l" id="xxx2">xxx0</div><div class="c25r" id="xxx3">xxx0</div></div>',
        dp_3333: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c33l" id="xxx2">xxx0</div><div class="c33l" id="xxx3">xxx0</div><div class="c33r" id="xxx4">xxx0</div></div>',
        dp_4425: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c25l" id="xxx2">xxx0</div><div class="c25l" id="xxx3">xxx0</div><div class="c25l" id="xxx4">xxx0</div><div class="c25r" id="xxx5">xxx0</div></div>',
        dp_1221: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c25l" id="xxx2">xxx0</div><div class="c50l" id="xxx3">xxx0</div><div class="c25r" id="xxx4">xxx0</div></div>',
        dp_1122: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c25l" id="xxx2">xxx0</div><div class="c25l" id="xxx3">xxx0</div><div class="c50r" id="xxx4">xxx0</div></div>',
        dp_2211: '<div class="subcolumns itemBox cbox" id="xxx1"><div class="c50l" id="xxx2">xxx0</div><div class="c25l" id="xxx3">xxx0</div><div class="c25r" id="xxx4">xxx0</div></div>',
        dp_subc: '<div class="subc" id="xxx1"><!-- Insert your subtemplate content here--></div>',
        dp_subcl: '<div class="subcl" id="xxx1"><!-- Insert your subtemplate content here--></div>',
        dp_subcr: '<div class="subcr" id="xxx1"><!-- Insert your subtemplate content here--></div>'
    },
    generatedCssCode: '',
    /**
     *  config
     */
    savedItems: '',
    savedList: '',
    templateColumns: '',
    nextDynID: null,
    selectedLayouts: '',
    savedSubColLayout: '',
    defaults: {
        doctype: 0,
        div_page: true,
        div_header: true,
        div_nav: true,
        div_teaser: false,
        div_teaser_position: '',
        div_footer: true,
        div_topnav: 0,
        template: 3,
        layout_align: 2,
        content_columns: 2,
        content_2col_order: 0,
        content_3col_order: 0,
        left_width: "25%",
        center_width: "50%",
        right_width: "25%",
        margin_left: "25%",
        margin_right: "25%",
        margin_left_ie: "25%",
        margin_right_ie: "25%",
        layout_width_unit: "%",
        left_unit: "%",
        center_unit: "%",
        right_unit: "%",
        lunit_equal: false,
        cunit_equal: false,
        layout_width: "auto",
        layout_minwidth: "740px",
        layout_maxwidth: "90em",
        dynamic_id: 1,
        user_id: 0,
        menu_template: 0,
        gfxborder: 0,
        column_divider: 0,
        ie_minmax: 1
    },
    settings: {},
    draggable_elements: {
        connectToSortable: '#layoutsContainer div.mainBox,.layoutBoxCustomTop div.mainBox,.layoutBoxCustomBottom div.mainBox,.sortableSubCols,.subsort',
        zIndex: 999999,
        helper: 'clone',
        revert: 'invalid',
        forcePlaceholderSize: true,
        forceHelperSize: true,
        opacity: 0.7,
        start: function(event, ui) {
            Layouter.disableSortableContents();
            $('.ui-sortable').sortable({
                disabled: true
            });
        },
        stop: function(event, ui) {
            //alert( 'draggable_elements stop: ' + $(ui.helper).parent().html() );
            $('.ui-sortable').sortable({
                disabled: false
            });
            Layouter.enableSortableContents();
        }

    },
    disableSortableContainers: function() {
        $('#layoutsContainer div.mainBox,.layoutBoxCustomTop div.mainBox,.layoutBoxCustomBottom div.mainBox,.sortableSubCols,.subsort,.subtemplate,.contentcolumn').sortable({
            disables: true
        });
    },
    enableSortableContainers: function() {
        $('#layoutsContainer div.mainBox,.layoutBoxCustomTop div.mainBox,.layoutBoxCustomBottom div.mainBox,.sortableSubCols,.subsort,.subtemplate,.contentcolumn').sortable({
            disables: false
        });
    },
    disableSortableContents: function() {
        $('.cbox').sortable({
            disables: true
        });
    },
    enableSortableContents: function() {
        $('.cbox').sortable({
            disables: false
        });
    },
    droppables: {
        connectWith: "#layoutsContainer div.mainBox,.layoutBoxCustomTop div.mainBox,.layoutBoxCustomBottom div.mainBox,.sortableSubCols .allowSubCols",
        accept: '.cbox,.nosortables,.dropaccept',
        tolerance: 'pointer',
        greedy: true,
        addClasses: true,
        placeholder: "ui-sortable-placeholder",
        cancel: '.contentPlaceholder',
        forcePlaceholderSize: true,
        forceHelperSize: true,
        scroll: false,
        over: function(event, ui) {
            dropToList = $(this).parents().find('.dropzonehover:first');
        },
        drop: function(event, ui) {
            //   $('#layoutsContainer img.dropaccept').remove()
            // $(ui.item).appendTo( $('.dropzoneactive') );

            alert('Drop To List: ' + $(dropToList).html());

            //alert('drop');
            //$('.dropzoneactive').removeClass('dropzoneactive');
        },
        out: function(event, ui) {
            $('.dropzoneactive').removeClass('dropzoneactive');
            Layouter.updateDBSubCols($(ui.item).parents('.mainBox:first'));
        }
    },
    columsDraggableOptions: {
        connectToSortable: '#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom,.sortableSubCols .allowSubCols',
        zIndex: 99990,
        helper: 'clone',
        revert: 'invalid',
        opacity: 0.5,
        scroll: false,
        placeholder: "ui-sortable-placeholder",
        cancel: '.contentPlaceholder',
        forcePlaceholderSize: true,
        forceHelperSize: true,
        delay: 500,
        cursorAt: {
            top: 0,
            left: 0
        },
        start: function(event, ui)
        {
            $(ui.item).addClass('drag');

        },
        stop: function(event, ui) {
            // $(ui.helper).removeClass('drag');
            // Layouter.insertSubCols(event, ui);

            //alert('drag stop');

            //$(ui.item).replaceWith($('<span>Test</span>'));

            Layouter.updateDBSubCols($(ui.item).parents('.mainBox:first'));
        }
    },
    columsSortableOptions: {
        connectWith: "#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom,.sortableSubCols .allowSubCols,.subcolumns .allowSubCols, .subsort",
        placeholder: "ui-sortable-placeholder",
        //accept: '.itemBox:not(.dum),.nosortables,.subcolumns .allowSubCols,div.mainBox.allowSubCols',
        items: ".contentcolumn, li.subtemplate,.itemBox",
        helper: 'clone',
        cursor: 'move',
        zIndex: 9999,
        // forceHelperSize: true,
        forcePlaceholderSize: true,
        dropOnEmpty: false,
        distance: 10,
        cancel: '.contentPlaceholder',
        revert: true,
        tolerance: 'pointer',
        scroll: false,
        handler: '.layoutmenu',
        cursorAt: {
            top: 0,
            left: 0
        },
        delay: 600,
        start: function(event, ui)
        {
            if ($(ui.item).hasClass('dum') || $(ui.item).hasClass('layoutmenu')) {
                return false;
            }
            $('.dropaccept').droppable({
                disabled: false
            });
        },
        stop: function(event, ui) {



            if ($(ui.item).hasClass('dum') || $(ui.item).hasClass('layoutmenu'))
            {
                alert('columsSortableOptions stop return');
                return false;
            }

            // alert('columsSortableOptions stop');

            Layouter.insertSubCols(event, ui);

            $('.dropaccept').droppable({
                disabled: false
            });
        }
    },
    fieldSortableOptions: {
        connectWith: ".allowSubCols:not(.layoutBox)",
        placeholder: "ui-sortable-placeholder",
        cursor: 'move',
        accept: '.itemBox:not(.dum),.nosortables',
        items: ".itemBox:not(.dum),li.m",
        zIndex: 9999,
        // forceHelperSize: true,
        forcePlaceholderSize: true,
        dropOnEmpty: false,
        distance: 20,
        cancel: '.contentPlaceholder',
        revert: false,
        helper: 'clone',
        tolerance: 'pointer',
        scroll: false,
        delay: 500,
        cursorAt: {
            top: 0,
            left: 0
        },
        start: function(event, ui)
        {
            if ($(ui.item).hasClass('dum') || $(ui.item).hasClass('subtemplate') || $(ui.item).hasClass('contentcolumn') || $(ui.item).hasClass('drag')) {
                return false;
            }

            currentList = $(this).parents();

            if ($(this).parent().hasClass('mod') || $(ui.item).parent().hasClass('mod'))
            {
                currentList = $(ui.item).parent();
            }
            else
            {
                currentList = $(currentList).parents('.mainBox:first');
            }

            if (!$(ui.item).hasClass('.subcolumns'))
            {
                $('.subcolumns').sortable({
                    disabled: true
                });
            }


        },
        stop: function(event, ui) {


            if ($(ui.item).hasClass('dum') || $(ui.item).hasClass('subtemplate') || $(ui.item).hasClass('contentcolumn') || $(ui.item).hasClass('drag'))
            {
                return false;
            }


            if ($(ui.item).get(0).tagName == 'LI') {
                //alert('fieldSortableOptions stop');
                // is out of UL element
                if (!$(ui.item).parent().hasClass('mod'))
                {
                    var inner = $(ui.item).get(0).innerHTML;
                    var itemClass = $(ui.item).attr('class');
                    var item = $('<div>').attr('id', $(ui.item).attr('id')).insertAfter($(ui.item));

                    if (typeof itemClass != 'undefined')
                    {
                        item.addClass(itemClass);
                    }

                    item.addClass('cbox').addClass('itemBox').removeClass('fromModulList').removeClass('tmpElement');
                    fromList.append($(ui.item).removeClass('tmpElement'));


                    //$(item).removeClass('cbox');


                    $(item).append($('<div>').addClass('contentbox-menu').css({
                        height: '22px'
                    }).html(inner));

                    $(item).append($('<div>').addClass('contentbox-content').text('Insert Content here...'));
                    Layouter.addBlockToSection($(item).find('.contentbox-menu'));

                    //       alert('fieldSortableOptions mod stop');
                }


                if ($(ui.item).parents('ul:first').find('tmpElement').length > 0)
                {
                    $(ui.item).parents('ul:first').find('tmpElement').remove();
                }
                //alert($(ui.item).parent().html() );


                $(ui.item).removeClass('fromModulList').removeClass('tmpElement');

                Layouter.initSortables();

                $('.tmpElement').removeClass('tmpElement');

                return false;
            }


            $(ui.item).removeClass('fromModulList').removeClass('tmpElement');





            $('.dropaccept').droppable({
                disabled: true
            });


            //Layouter.updateDBSubCols( $( ui.item ).parents('.mainBox:first') );
            Layouter.initSortables();

            $('.subcolumns').sortable({
                disabled: false
            });

            $('.tmpElement').removeClass('tmpElement');
            Layouter.updateDBSubCols($(ui.item).parents('.mainBox:first'));

        },
        helper: function(event, element) {
            if ($(element).hasClass('dum') || $(element).hasClass('subtemplate') || $(element).hasClass('contentcolumn') || $(element).hasClass('drag')) {
                return false;
            }
            //element.clone().appendTo('body');
            fromList = $(element).parent();

            return element.addClass('tmpElement').clone().appendTo($('body'));
        },
                beforeStop: function(event, ui) {
            //alert('fieldSortableOptions beforeStop');


            if ($(ui.item).hasClass('dum') || $(ui.item).hasClass('subtemplate') || $(ui.item).hasClass('contentcolumn') || $(ui.item).hasClass('drag')) {
                return false;
            }

            //alert('fieldSortableOptions beforeStop');

            // Layouter.insertSubCols(event, ui);



            $(ui.item).parent().append(ui.helper);

            $('.tmpElement').insertAfter($('<span>').addClass('elementPos'));
            toList = $(ui.item).parent();
        }


    },
    initSortables: function() {
        if ($('.subsort').find('.itemBox').length == 0) {
            $('.subsort').empty().append($('<div>').addClass('itemBox').addClass('dum'));
        }

        $('.layoutModules ul,.allowSubCols:not(.mainBox)').sortable(Layouter.fieldSortableOptions).disableSelection();
        $('#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom,.sortableSubCols .allowSubCols').sortable(Layouter.columsSortableOptions).disableSelection();
        //$( 'div.mainBox:not(.boxLocked),.subcolumns,.subtemplate,.contentcolumn').draggable( Layouter.columsDraggableOptions ).disableSelection();


        var builderDrag = this.columsDraggableOptions;
        builderDrag.handle = '';
        $('#builder .contentelement').draggable(this.columsDraggableOptions);
        $('#column-layouts li.subtemplate').draggable(this.columsDraggableOptions);
    },
    updateLayoutSize: function(cols) {
        return;
        var innerwidth = $('.layoutBox-inner').innerWidth();
        var boxlwidth = $('.leftColumns').width();
        var boxrwidth = $('.rightColumns').width();



        boxlwidth = ($('.leftColumns').is(':visible') ? parseInt(boxlwidth) + 15 : 0);
        boxrwidth = ($('.rightColumns').is(':visible') ? parseInt(boxrwidth) + 15 : 0);
        $('.middleColumns').css({
            'width': (innerwidth - boxlwidth - boxrwidth)
        });
    },
    prepareCurrentLayout: function()
    {
        var menu = '<div id="xxx9" class="layoutmenu"> <span class="more">mehr...</span><div class="submenu"></div></div>';

        if (typeof Layouter.savedList == 'object')
        {
            var inner = $('#layoutsContainer div.contentPlaceholder').html();
            var contentPlaceholder = false;

            for (var i in Layouter.savedList)
            {
                var data = Layouter.savedList[i];

                if ($(data.item).hasClass('contentPlaceholder'))
                {
                    contentPlaceholder = true;
                    $('#' + data.block + ' div.contentPlaceholder').remove();
                }


                var item = $(data.item).removeClass('itemBox');
                item.addClass('itemBox');

                if (typeof $(item).find('.sortableSubCols').length == 0)
                {
                    //   Layouter.addButtonsToItem( item, data );
                }
                else
                {
                    //    $(item).prepend(menu);
                }

                $('#' + data.block).append(item);
                // var item = $('#'+ data.block +' div:last');
            }


            if (contentPlaceholder && typeof inner != 'undefined' && inner != '')
            {
                $('div.contentPlaceholder').empty().append(inner);
            }
        }


        $('.itemBox').each(function() {
            var _self = this;


            if (!$(_self).hasClass('contentPlaceholder') && $(_self).find('.sortableSubCols').length > 0)
            {

                if ($(this).hasClass('subcolumns'))
                {
                    $(this).removeClass('itemBox');
                }

                $(_self).find('.allowSubCols').each(function() {
                    if ($(this).find('.dum').length == 0) {
                        $(this).append($('<div>').addClass('itemBox').addClass('dum'));
                    }
                });

                // root id
                var id = $(this).attr('id').replace('dyn_id', '');
                tmp = menu.replace('xxx9', 'menu' + id);
                $(this).prepend($(tmp));



                // create buttons to column container
                Layouter.createAddButton('#menu' + id);
                Layouter.createRemoveButton('#menu' + id);
                Layouter.createEmptyButton('#menu' + id);

                if ($('#menu' + id).find('.submenu div:first').length)
                {
                    $('#menu' + id).find('.more').click(function() {
                        $('.layoutmenu .submenu:visible').hide();
                        $(this).parent().find('.submenu:first').slideToggle(50, function() {

                            $(this).unbind('mouseleave').bind('mouseleave', function() {
                                $(this).hide();
                            });

                        });
                    });
                }
                else
                {
                    // error is empty menu
                    var parentObj = $('#menu' + id).parent();

                    if (parentObj.attr('id'))
                    {
                        Debug.log('The container "' + parentObj.attr('id') + '" is not set! It will remove.');
                    }
                    $('#menu' + id).parent().remove();

                }

            }
            else if (!$(this).hasClass('contentPlaceholder'))
            {
                if ($(this).hasClass('subcolumns'))
                {
                    $(this).removeClass('itemBox');
                }


                Layouter.addButtonsToItem($(this), data);
            }


        });


        if (Layouter.nextDynID > 1)
        {
            Layouter.settings.dynamic_id = parseInt(Layouter.nextDynID);
        }






        if (typeof Layouter.savedItems == 'object')
        {

            for (var blockname in Layouter.savedItems)
            {
                var items = Layouter.savedItems[blockname].split(',');

                for (var y in items)
                {
                    var itemidname = items[y];

                    if (!itemidname)
                    {
                        continue;
                    }

                    var element = $('#' + itemidname);

                    if (typeof element !== 'undefined')
                    {
                        if (!element.hasClass('contentPlaceholder'))
                        {
                            var type = '';
                            if (element.attr('id'))
                            {
                                type = element.attr('id').replace(/_\d*$/g, '');
                            }

                            var title = element.find('span:first').clone();
                            title.addClass(type);
                            $(element).removeClass(type);
                            $(element).empty().addClass('m').append(
                                    $('<div>').addClass('contentbox-menu').css({
                                height: '22px'
                            }).append(title));

                            $(element).append($('<div>').addClass('contentbox-content').text('Insert Content here...'));



                            Layouter.addButtonsToItem($('.contentbox-menu:first', element));
                            //blockHTML.replace('[' + itemidname + ']', element);
                        }
                    }
                }
            }
        }


        $('.cbox').addClass('itemBox');




        // $('#layoutsContainer div.mainBox:not(.boxLocked)').droppable(this.droppables);

        /*
         if ( typeof Layouter.savedSubColLayout == 'object' )
         {
         for( var i in Layouter.savedSubColLayout )
         {
         var data = Layouter.savedSubColLayout[i];
         var matchID = data.block.replace('subcolcontainer', '');
         
         $('.'+ data.block).append();
         }
         } */
    },
    setupLayouterGui: function() {





        $('#builder .tcontainer').hide();
        $('#builder').addClass('isbuilderMoved').unbind().draggable({
            scroll: false,
            zIndex: 99999,
            handle: '#ui_content>h3:first'
        }).show();


        // setup tabs
        $('#builder .jquery_tabs a').click(function(e) {
            var self = this;
            e.preventDefault();


            if ($(self).parent().hasClass('layout'))
            {
                // Show layout menu
                //$('.itemBox .layoutmenu').show();
                //$('.layoutBoxCustomBottom, .layoutBoxBottom, .layoutBoxCustomTop, .layoutBoxTop, .middleColumns,.rightColumns,.leftColumns').removeClass('transparentborder');
                //$('.subcolumns').find('.sortableSubCols .cbox:not(.subcolumns)').hide();

                $('#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom,.sortableSubCols .allowSubCols').sortable({
                    disabled: false
                });


                $('.layoutModules ul,.allowSubCols:not(.mainBox)').sortable({
                    disabled: true
                });

            }
            else
            {
                // hide layout menu
                //$('.itemBox .layoutmenu').hide();
                //$('.layoutBoxCustomBottom, .layoutBoxBottom, .layoutBoxCustomTop, .layoutBoxTop, .middleColumns,.rightColumns,.leftColumns').addClass('transparentborder');
                //$('.subcolumns').find('.sortableSubCols .cbox:not(.subcolumns)').show();

                $('#layoutBoxTop,#layoutBoxCustomTop,#layoutBoxLeft,#layoutBoxRight,#layoutBoxMiddle,#layoutBoxCustomBottom,#layoutBoxBottom,.sortableSubCols .allowSubCols').sortable({
                    disabled: true
                });

                $('.layoutModules ul,.allowSubCols:not(.mainBox)').sortable({
                    disabled: false
                });
            }

            $(self).parent().parent().find('li.current').removeClass('current');
            $(self).parent().addClass('current');

            var tabC = $(self).parent().attr('id').replace('tab', '');
            $('#builder .tcontainer').hide();
            $('#builder #t_' + tabC).show();

            return false;
        });

        $('#builder h3 .switch_pin').click(function(e) {
            var self = this;
            e.preventDefault();

            if ($(self).find('img').attr('src').match(/pin-out\./)) {
                $(self).find('img').attr('src', $(self).find('img').attr('src').replace('pin-out', 'pin-in'));
                $('#builder').css({
                    position: 'fixed',
                    zIndex: 99999
                });
            }
            else
            {
                $(self).find('img').attr('src', $(self).find('img').attr('src').replace('pin-in', 'pin-out'));
                $('#builder').css({
                    position: 'absolute',
                    zIndex: 99999
                });
            }
            return false;
        });

        if ($('.jquery_tabs li.current').length) {
            var id = $('.jquery_tabs li.current').attr('id');
            $('#builder #t_' + id.replace('tab', '')).show();
        }


        $('#layout-accordion').accordion({
            autoHeight: false,
            heightStyle: "fill"
        });

        /*
         $('#builder .contentcolumn').draggable(this.draggable_elements);
         $('#builder .contentcolumn').removeClass('dropaccept');
         $('#builder .contentcolumn').addClass('protected');
         */
        $('#builder').find('input,select,textarea').addClass('nodirty');
    },
    tinit: null,
    initLayouter: function(initData) {
        var self = this;
        if (!$('#' + Win.windowID).data('DcmsWindow'))
        {
            this.tinit = setTimeout(function() {
                self.initLayouter(initData);
            }, 50);
        }
        else
        {
            clearTimeout(this.tinit);
            if ($('#' + Win.windowID).data('DcmsWindow').get('layouterInited'))
            {
                return;
            }




            console.log('Init layouter');
            this.layoutContainer = $('#layoutsContainer');

            $('.layout-builder[windowid=' + Win.windowID + ']').remove();
            $('#builder').addClass('layout-builder').attr('windowid', Win.windowID);
            $('#builder').appendTo($('#desktop'));


            $('#' + Win.windowID).data('DcmsWindow').set('layouterInited', true);
            $('#' + Win.windowID).data('DcmsWindow').set('onBeforeClose', function(winObj, winId) {
                $('.layout-builder').each(function() {
                    if ($(this).attr('windowid') == winId)
                    {
                        $(this).remove();
                    }
                });
            });


            $('body .isbuilderMoved').remove();
            var self = this;
            this.settings = $.extend({}, this.defaults),
                    $('link.layouterCss', $('head')).remove();
            $('head').append('<link rel="stylesheet" class="layouterCss" href="public/html/css/subcols.css" type="text/css"/>');
            $('head').append('<link rel="stylesheet" class="layouterCss" href="public/html/css/subcolsIEHacks.css" type="text/css"/>');

            var path = Config.get('backendImagePath');
            path = path.replace('/img/', '/css/');


            path = path.replace(Config.get('portalurl') + '/', '');



            // alert('PATH:'+path+'layoutbuilder.css');
            $('head').append('<link rel="stylesheet" class="layouterCss" href="public/' + path + 'dcms.layouter.css" type="text/css"/>');
            $('head').append('<link rel="stylesheet" class="layouterCss" href="public/' + path + 'layoutbuilder.css" type="text/css"/>');

            this.selectedLayouts = initData.selectedLayouts;
            this.templateColumns = initData.templateColumns;
            this.savedSubColLayout = initData.savedSubColLayout;
            // prepare data cache
            this.savedList = initData.savedList;
            this.nextDynID = (parseInt(initData.nextDynID) > 0 ? parseInt(initData.nextDynID) : 1);
            this.savedItems = initData.savedItems;

            this.prepareCurrentLayout();
            this.setupLayouterGui();
            this.initSortables();


            $('.cancel').hide().click(function() {
                document.location.href = 'admin.php?adm=layouter&reload=1&skinid={$request.skinid}' + (isSeemodePopup ? '&seemodePopup=1' : '');
            });

            $('.layout-header,.layout-footer,.layout-static,.layoutBoxTop,.layoutBoxBottom,.layoutBoxCustomTop,.layoutBoxCustomBottom').hide();

            this.updateLayoutSize();

            if ($('#layout-header').is(':checked')) {
                $('.layout-header,.layoutBoxTop').show(0,
                        function() {
                            if ($(this).is(':visible')) {
                                $(this).css('display', 'inline');
                            }
                        });
            }
            if ($('#layout-footer').is(':checked')) {
                $('.layout-footer,.layoutBoxBottom').show(0, function() {
                    if ($(this).is(':visible')) {
                        $(this).css('display', 'inline');
                    }
                });
            }
            if ($('#layout-customheader').is(':checked')) {
                $('.layoutBoxCustomBottom').show(0, function() {
                    if ($(this).is(':visible')) {
                        $(this).css('display', 'inline');
                    }
                });
            }
            if ($('#layout-customfooter').is(':checked')) {
                $('.layoutBoxCustomTop').show(0, function() {
                    if ($(this).is(':visible')) {
                        $(this).css('display', 'inline');
                    }
                });
            }
            if ($('#layout-static').is(':checked')) {
                $('.layout-static').show();
            }


            self.guiChangedCols(0);
            $('.col-left,.col-right').hide();




            var order_3_iu_id = $('#order_3').attr('sb');
            var order_2_iu_id = $('#order_2').attr('sb');

            $('input[name=cols]:checked').each(function() {
                var value = $(this).val();
                value = value.replace('cols', '');
                value = value.split('-');

                if (value[0] == 2)
                {
                    self.guiChangedCols(2);
                    $('.col-left,.col-right,.rightColumns,.leftColumns').hide();
                    if (value[2] === 'left') {
                        $('.col-left,.leftColumns,.middleColumns').show();
                        self.updateLayoutSize('left');
                    }
                    else if (value[2] === 'right')
                    {
                        $('.col-right,.rightColumns,.middleColumns').show();
                        self.updateLayoutSize('right');
                    }

                    $('#sbHolder_' + order_3_iu_id).hide();
                    $('#sbHolder_' + order_2_iu_id).show();
                }
                else if (value[0] == 3) {
                    $('#sbHolder_' + order_2_iu_id).hide();
                    $('#sbHolder_' + order_3_iu_id).show();


                    $('.col-left,.col-right,.rightColumns,.leftColumns,.middleColumns').show();
                    self.guiChangedCols(3);
                    self.updateLayoutSize(3);
                }
                else if (value[0] == 0) {
                    $('#sbHolder_' + order_2_iu_id).hide();
                    $('#sbHolder_' + order_3_iu_id).hide();
                    $('.col-left,.col-right,.middleColumns,.rightColumns,.leftColumns').hide();
                    $('.middleColumns').show();
                    self.guiChangedCols(0);
                    self.updateLayoutSize(0);

                }

                $(this).parent().find('span:first').addClass('active');
            });




            $('input[name=cols]').change(function() {
                $('.cols .active').removeClass('active');
                $(this).parent().find('span:first').addClass('active');

                var value = $(this).val();
                value = value.replace('cols', '');
                value = value.split('-');

                if (value[0] == 2) {
                    $('#sbHolder_' + order_3_iu_id).hide();
                    $('#sbHolder_' + order_2_iu_id).show();


                    self.guiChangedCols(2);
                    $('.col-left,.col-right,.rightColumns,.leftColumns').hide();

                    if (value[2] === 'left')
                    {
                        $('.col-left,.leftColumns,.middleColumns').show();
                        self.updateLayoutSize('left');
                    }
                    else if (value[2] === 'right')
                    {
                        $('.col-right,.rightColumns,.middleColumns').show();
                        self.updateLayoutSize('right');
                    }
                }
                else if (value[0] == 3) {
                    $('#sbHolder_' + order_2_iu_id).hide();
                    $('#sbHolder_' + order_3_iu_id).show();

                    $('.col-left,.col-right,.rightColumns,.leftColumns,.middleColumns').show();
                    self.guiChangedCols(3);
                    self.updateLayoutSize(3);
                }
                else if (value[0] == 0) {
                    $('#sbHolder_' + order_2_iu_id).hide();
                    $('#sbHolder_' + order_3_iu_id).hide();
                    $('.col-left,.col-right,.rightColumns,.leftColumns').hide();
                    $('.middleColumns').show();
                    self.guiChangedCols(0);
                    self.updateLayoutSize(0);
                }

            });


            /**
             * setup layout boxes
             */
            $('.layoutBoxTop,.layoutBoxBottom,.layoutBoxBottom,.layoutBoxCustomBottom,.layoutBoxCustomTop,.layout-static').hide();
            $('#layout-header,#layout-footer,#layout-static,#layout-customheader,#layout-customfooter').each(function() {
                var el = $(this).attr('id');

                var show = false;
                if ($(this).is(':checked') || $(this).get(0).checked) {
                    $('.' + el).show();
                    show = true;
                }
                else
                {
                    $('.' + el).hide();
                }

                self.setChangedCols(el, show);

            }).change(function(e) {
                e.preventDefault();
                var el = $(this).attr('id');

                var show = false;
                if ($(this).is(':checked')) {
                    $('.' + el).show();
                    show = true;
                }
                else
                {
                    $('.' + el).hide();
                }

                self.setChangedCols(el, show);
            });


            $('#tab_1').click(function() {
                setTimeout(function() {
                    self.updateLayoutSize()
                }, 20);
            });

            $('#sidebar-tree').resize(function() {
                self.updateLayoutSize()
            });

            // lock/unlock buttons
            $('.middleColumns .lockbtn .lockbtn-label,.rightColumns .lockbtn .lockbtn-label,.leftColumns .lockbtn .lockbtn-label').click(function() {

                if ($(this).parents('.lockbtn:first').hasClass('locked'))
                {
                    $(this).parents('.lockbtn:first').removeClass('locked');
                    $(this).parents('.lockbtn:first').parent().removeClass('lockedMask');
                    $(this).parents('.lockbtn:first').parent().find('.connectedSortable:first-child').removeClass('boxLocked');
                    $(this).parents('.lockbtn:first').parent().find('.ui-sortable').sortable('enable');

                    var box = $(this).parent().parent().children().find('div.layoutBox');
                    $(this).parents('.lockbtn:first').parent().find('.layoutBox-lock-mask').remove();


                    box.removeClass('boxLocked').sortable('enable');
                    $(this).parent().parent().children().find('a').css({
                        cursor: 'pointer'
                    });
                }
                else
                {
                    $(this).parents('.lockbtn:first').addClass('locked');
                    $(this).parents('.lockbtn:first').parent().find('.connectedSortable:first-child').addClass('boxLocked');
                    $(this).parents('.lockbtn:first').parent().find('.ui-sortable').sortable('disable');
                    $(this).parents('.lockbtn:first').parent().addClass('lockedMask');
                    $(this).parents('.lockbtn:first').parent().append($('<div>').addClass('layoutBox-lock-mask'));


                    $(this).parent().parent().children().find('div.layoutBox').append($('<div>').addClass('layoutBox-lock-mask'));
                    $(this).parent().parent().children().find('div.layoutBox').addClass('boxLocked').sortable('disable');
                    $(this).parent().parent().children().find('a').css({
                        cursor: 'not-allowed'
                    });
                }
            });


            // lock/unlock buttons
            $('.layoutBoxTop .lockbtn .lockbtn-label,.layoutBoxBottom .lockbtn .lockbtn-label,.layoutBoxCustomBottom .lockbtn .lockbtn-label,.layoutBoxCustomTop .lockbtn .lockbtn-label').click(function() {
                if ($(this).parent().hasClass('locked'))
                {
                    $(this).parent().removeClass('locked');
                    $(this).parent().children().removeClass('locked');
                    //    $(this).parent().children('.section-opts').enable();

                    $(this).parent().parent().removeClass('lockedMask').find('.layoutBox-lock-mask').remove();
                    $(this).parent().parent().find('.connectedSortable:first-child').removeClass('boxLocked');
                    $(this).parent().parent().find('.ui-sortable').sortable('enable');


                    $(this).parent().parent().children().find('div.layoutBox').removeClass('boxLocked').sortable('enable');

                    $(this).parent().parent().children().find('a').css({
                        cursor: 'pointer'
                    });
                }
                else
                {
                    $(this).parent().addClass('locked'); // .lockbtn
                    $(this).parent().children().addClass('locked');
                    // $(this).parent().children('.section-opts').disabled();


                    $(this).parent().parent().find('.connectedSortable:first-child').addClass('boxLocked');
                    $(this).parents().find('.ui-sortable:first-child').sortable('disable');
                    $(this).parent().parent().addClass('lockedMask');
                    $(this).parent().parent().append($('<div>').addClass('layoutBox-lock-mask'));



                    $(this).parent().parent().children().find('div.layoutBox').addClass('boxLocked').sortable('disable');
                    $(this).parent().parent().children().find('a').css({
                        cursor: 'not-allowed'
                    });

                }

            });

            $('.section-opts').hide();


            self.addSubcolButtons();
            self.initSortables();










            // empty box buttons
            $('.section-opts span').click(function(e) {
                //self.clearSections($(this).parent().parent().prev().children('ul').attr('id'));
            });
        }
    },
    // change column states
    setChangedCols: function(el, show) {
        if (el == 'layout-header' && show) {
            $('.layoutBoxTop').show();
        }
        else if (el == 'layout-header' && !show) {
            $('.layoutBoxTop').hide();
        }
        else if (el == 'layout-footer' && show) {
            $('.layoutBoxBottom').show();
        }
        else if (el == 'layout-footer' && !show) {
            $('.layoutBoxBottom').hide();
        }
        else if (el == 'layout-customfooter' && show) {
            $('.layoutBoxCustomBottom').show();
        }
        else if (el == 'layout-customfooter' && !show) {
            $('.layoutBoxCustomBottom').hide();
        }
        else if (el == 'layout-customheader' && show) {
            $('.layoutBoxCustomTop').show();
        }
        else if (el == 'layout-customheader' && !show) {
            $('.layoutBoxCustomTop').hide();
        }
    },
    // Update Gui Tool column orders
    guiChangedCols: function(numcols) {

        $('#order_3,#order_2').hide();
        switch (numcols)
        {
            case 2:
                $('#order_3').prev().prev().hide();
                $('.col-orders,#order_2').show();
                $('#order_2').prev().prev().show();

                $('.col-orders>th.secondary>.right').hide();
                $('.col-orders>td.center>span').hide();
                $('.col-orders>td.center>select').show();
                $('.col-orders>td.center>input').show();
                $('.col-orders>td.right').hide();

                break;
            case 3:
                $('.col-orders>th.secondary>.right').show();
                $('.col-orders>td.right').show();

                $('.col-orders>td.center>select').hide();
                $('.col-orders>td.center>input').hide();
                $('.col-orders>td.center>span').show();

                $('#order_2').prev().prev().hide();
                $('.col-orders,#order_3').show();
                $('#order_3').prev().prev().show();
                break;
            case 0:
                $('.col-orders,#order_3,#order_2').hide();
                $('.col-orders>td.center>select').hide();
                $('.col-orders>td.center>input').hide();

                $('#order_2,#order_3').hide();
                $('#order_2,#order_3').prev().prev().hide();
                break;
        }

        this.guiSetEventColOrder(numcols);
    },
    // set gui column events
    guiSetEventColOrder: function(numcols) {

        // $('#order_3,#order_2').unbind('change');
        var self = this;
        switch (numcols)
        {
            case 0:
                self.initColOrder(0);
                break;

            case 2:
                self.initColOrder(2);

                $('#order_2').change(function(e) {
                    e.preventDefault();
                    setTimeout(function() {
                        self.initColOrder(2);
                    }, 300);
                });
                break;
            case 3:
                self.initColOrder(3);

                $('#order_3').change(function(e) {
                    e.preventDefault();
                    setTimeout(function() {
                        self.initColOrder(3);
                    }, 300);
                });
                break;
        }
    },
    initColOrder: function(numcols) {
        var self = this;
        margin_unit = 'px';
        margin_left = 5;
        margin_right = 5;

        left_width = '180px';
        right_width = '180px';
        center_width = '';

        left_unit = 'px';
        right_unit = 'px';
        center_unit = '';



        switch (numcols)
        {
            case 0:
                $('#col1,#col2').css({
                    'float': 'left',
                    'width': left_width,
                    'margin': '0',
                    'margin-right': margin_right + margin_unit
                });

                $("#col3").css({
                    'width': 'auto',
                    'margin': '0'
                });
                break;

            case 2:

                var orderEl = $('#order_2');
                var content_order = orderEl.get(0).selectedIndex;
                var order = self.columnorder2[content_order];
                self.updateColOrderLabels(2, content_order);
                self.generateCssCode(2, content_order);

                switch (content_order) {
                    case 0:
                        $('#col1,#col2').css({
                            'float': 'left',
                            'width': left_width,
                            'margin': '0',
                            'margin-right': margin_right + margin_unit
                        });
                        $("#col3").css({
                            'width': 'auto',
                            'margin': '0'
                        });

                        break;
                    case 1:
                        $('#col1,#col2').css({
                            'float': 'right',
                            'width': left_width,
                            'margin': '0',
                            'margin-left': margin_left + margin_unit
                        });
                        $("#col3").css({
                            'width': 'auto',
                            'margin': '0'

                        });
                        break
                }

                break;
            case 3:
                var content_order = $('#order_3').get(0).selectedIndex;
                var order = self.columnorder3[content_order];

                self.updateColOrderLabels(3, content_order);
                self.generateCssCode(3, content_order);

                switch (content_order) {
                    case 0:
                        var a = parseInt(str_replace(left_unit, '', left_width)) + margin_left;
                        var b = parseInt(str_replace(right_unit, '', right_width)) + margin_right;
                        $("#col1").css({
                            "float": "left",
                            "width": left_width,
                            "margin": "0",
                            'margin-right': margin_right + margin_unit
                        });
                        $("#col2").css({
                            "float": "right",
                            "width": right_width,
                            "margin": "0",
                            'margin-left': margin_left + margin_unit

                        });
                        $("#col3").css({
                            "width": "auto",
                            "margin": "0"

                                    //"margin": "0 " + b + right_unit + " 0 " + a + left_unit
                        });

                        break;
                    case 1:
                        var c = parseInt(str_replace(right_unit, '', right_width));
                        var d = parseInt(str_replace(left_unit, '', left_width));

                        $("#col1").css({
                            "float": "right",
                            "width": right_width,
                            "margin": "0",
                            'margin-left': margin_left + margin_unit

                        });
                        $("#col2").css({
                            "float": "left",
                            "width": left_width,
                            "margin": "0",
                            'margin-right': margin_right + margin_unit
                        });
                        $("#col3").css({
                            "width": "auto",
                            "margin": "0"


                                    //"margin": "0 " + c + right_unit + " 0 " + d + left_unit
                        });

                        break;
                    case 2:
                        var f = parseInt(str_replace(left_unit, '', left_width));
                        var g = parseInt(str_replace(right_unit, '', right_width));
                        $("#col1").css({
                            "float": "left",
                            "width": left_width,
                            "margin": "0",
                            'margin-right': margin_right + margin_unit

                        });
                        $("#col2").css({
                            "float": "left",
                            "width": right_width,
                            "margin": "0",
                            'margin-right': margin_right + margin_unit
                        });
                        $("#col3").css({
                            "width": "auto",
                            "margin": "0"
                        });

                        break;
                    case 3:
                        var h = parseInt(str_replace(right_unit, '', right_width));
                        var g = parseInt(str_replace(left_unit, '', left_width));
                        $("#col1").css({
                            "float": "right",
                            "width": right_width,
                            "margin": "0"
                        });
                        $("#col2").css({
                            "float": "right",
                            "width": left_width,
                            "margin": "0",
                            'margin-right': margin_right + margin_unit,
                            'margin-left': margin_left + margin_unit
                        });
                        $("#col3").css({
                            "width": "auto",
                            "margin": "0"
                        });

                        break;
                    case 4:
                        var f = parseInt(str_replace(left_unit, '', left_width));
                        var g = parseInt(str_replace(center_unit, '', center_width));
                        $("#col1").css({
                            "float": "left",
                            "width": left_width,
                            "margin": "0 0 0 " + (parseInt(str_replace(right_unit, '', right_width)) + parseInt(str_replace(left_unit, '', left_width))) + right_unit
                        });
                        $("#col2").css({
                            "float": "left",
                            'left': 0,
                            "width": right_width,
                            "margin": "0"
                        });
                        $("#col3").css({
                            "width": "auto",
                            "margin": "0 0 0 " + String(f + g) + center_unit
                        });

                        break;
                    case 5:
                        var h = parseInt(str_replace(right_unit, '', right_width));
                        var g = parseInt(str_replace(center_unit, '', center_width));
                        $("#col1").css({
                            "float": "right",
                            "width": left_width,
                            "margin": "0 " + right_width + " 0 -" + String(h + g) + center_unit
                        });
                        $("#col2").css({
                            "float": "right",
                            "width": right_width,
                            "margin": "0"
                        });
                        $("#col3").css({
                            "width": "auto",
                            "margin": "0 " + String(h + g) + center_unit + " 0 0"
                        });

                        break
                }
                break;
        }
    },
    /*
     update column order labels
     */
    updateColOrderLabels: function(numcols, order) {

        var leftLabel = $('.col-orders label[for=left_width]');
        var centerLabel = $('.col-orders label[for=center_width]');
        var rightLabel = $('.col-orders label[for=right_width]');


        switch (numcols)
        {
            case 2:
                switch (order)
                {
                    case 0:
                        leftLabel.text('Left');
                        rightLabel.text('');
                        centerLabel.text('Content');

                        $('#center_width').hide();
                        $('#center_width').next().show();

                        $('#left_width,#right_width').show();
                        $('#left_width,#right_width').next().hide();

                        break;

                    case 1:
                        leftLabel.text('Content');
                        rightLabel.text('');
                        centerLabel.text('Left');

                        $('#center_width').show();
                        $('#center_width').next().hide();

                        $('#left_width,#right_width').hide();
                        $('#left_width,#right_width').next().show();

                        break;
                }
                break;

            case 3:
                switch (order)
                {
                    case 0:
                        leftLabel.text('Left');
                        rightLabel.text('Right');
                        centerLabel.text('Content');


                        $('.col-orders td.right').find('#right_width,.unit').show();
                        $('#right_width').next().hide();

                        $('.col-orders td.left').find('#left_width,.unit').show();
                        $('#left_width').next().hide();

                        $('.col-orders td.center').find('#center_width,.unit').hide();
                        $('#center_width').next().show();

                        break;

                    case 1:
                        leftLabel.text('Right');
                        rightLabel.text('Left');
                        centerLabel.text('Content');

                        $('.col-orders td.right').find('#right_width,.unit').show();
                        $('#right_width').next().hide();

                        $('.col-orders td.left').find('#left_width,.unit').show();
                        $('#left_width').next().hide();

                        $('.col-orders td.center').find('#center_width,.unit').hide();
                        $('#center_width').next().show();

                        break;

                    case 2:
                        leftLabel.text('Left');
                        rightLabel.text('Content');
                        centerLabel.text('Right');

                        $('.col-orders td.right').find('#right_width,.unit').hide();
                        $('#right_width').next().show();

                        $('.col-orders td.left').find('#left_width,.unit').show();
                        $('#left_width').next().hide();

                        $('.col-orders td.center').find('#center_width,.unit').show();
                        $('#center_width').next().hide();
                        break;

                    case 3:
                        leftLabel.text('Content');
                        rightLabel.text('Left');
                        centerLabel.text('Right');

                        $('.col-orders td.right').find('#right_width,.unit').show();
                        $('#right_width').next().hide();

                        $('.col-orders td.left').find('#left_width,.unit').hide();
                        $('#left_width').next().show();

                        $('.col-orders td.center').find('#center_width,.unit').show();
                        $('#center_width').next().hide();

                        break;
                }
                break;
        }
    },
    generateCssCode: function(numcols, content_order) {

        margin_unit = 'px';
        margin_left = 5;
        margin_right = 5;

        left_width = '180px';
        right_width = '180px';
        center_width = '';

        left_unit = 'px';
        right_unit = 'px';
        center_unit = '';

        var s = '';
        switch (numcols)
        {
            case 2:
                switch (content_order) {
                    case 0:
                        s += "#col1,#col2 { float:left; width:" + left_width + "; margin: 0; margin-right:" + margin_right + margin_unit + ";}\n";
                        s += "#col3 { width: auto; margin: 0; }\n";
                        break;
                    case 1:
                        s += "#col1,#col2 { float:left; width:" + left_width + "; margin: 0; margin-left:" + margin_left + margin_unit + ";}\n";
                        s += "#col3 { width: auto; margin: 0; }\n";
                        break
                }
                break;
            case 3:

                switch (content_order) {
                    case 0:
                        s += "#col1 { float: left; width:" + left_width + ";margin: 0;margin-right" + margin_right + margin_unit + ";}\n";
                        s += "#col2 { float: right; width:" + right_width + ";margin: 0;margin-left:" + margin_left + margin_unit + ";}\n";
                        s += "#col3 { width: auto; margin: 0; }\n";

                        break;
                    case 1:
                        s += "#col2 { float: left; width:" + left_width + ";margin: 0;margin-right" + margin_right + margin_unit + ";}\n";
                        s += "#col1 { float: right; width:" + right_width + ";margin: 0;margin-left:" + margin_left + margin_unit + ";}\n";
                        s += "#col3 { width: auto; margin: 0; }\n";

                        break;
                    case 2:
                        s += "#col1 { float: left; width:" + left_width + ";margin: 0;margin-right" + margin_right + margin_unit + ";}\n";
                        s += "#col2 { float: left; width:" + right_width + ";margin: 0;margin-right:" + margin_right + margin_unit + ";}\n";
                        s += "#col3 { width: auto; margin: 0; }\n";

                        break;
                    case 3:
                        s += "#col1 { float: right; width:" + right_width + ";margin: 0;}\n";
                        s += "#col2 { float: right; width:" + left_width + ";margin: 0;margin-right:" + margin_right + margin_unit + ";margin-left:" + margin_left + margin_unit + "; }\n";
                        s += "#col3 { width: auto; margin: 0; }\n";
                        break;
                }
                break;
        }

        this.generatedCssCode = s;
        return s;
    },
    /*
     INSERT SUBCOLUMNS
     */
    insertSubCols: function(event, b) {
        // drag = $(b.draggable)[0]; // the droped element
        var layoutID = $(b.item).attr('layout');

        // alert(''+$(b.item).attr('layout') );


        switch (layoutID) {
            case 'dp_5050':
            case 'dp_3366':
            case 'dp_6633':
            case 'dp_3862':
            case 'dp_6238':
            case 'dp_2575':
            case 'dp_7525':
            case 'dp_3333':
            case 'dp_4425':
            case 'dp_1221':
            case 'dp_1122':
            case 'dp_2211':


                var placeholder = $('.ui-sortable-placeholder');

                //$(this).find( "div.dummy:first" ).remove();
                $(this).droppable("destroy");

                if (layoutID == 'dp_4425') {
                    var c = 5;
                } else if (layoutID == 'dp_3333' || layoutID == 'dp_1221' || layoutID == 'dp_1122' || layoutID == 'dp_2211') {
                    var c = 4;
                } else {
                    var c = 3;
                }


                // Template
                var template = Layouter.dropelements[layoutID];


                var list = $('<div>').addClass('nosortables'); //.addClass('dropaccept');


                var rand = Math.floor(Math.random() * 10001);
                var newId = 'subcols';
                // make sure we have a unique ID number
                if ($('#' + newId + '_' + rand).length)
                {
                    while ($('#' + newId + '_' + rand).exists()) {
                        rand = Math.floor(Math.random() * 10001);
                    }
                }
                newId = newId + '_' + rand;
                list.addClass(newId).attr('id', 'subcolcontainer_' + rand);


                var cidx = Layouter.settings.dynamic_id;


                // sortable 
                var currentID = 'dynid' + String(Layouter.settings.dynamic_id);

                /**
                 *  Content container
                 */
                var ul = '<div class="sortableSubCols equalize" title="Subtemplate Container: #' + 'dyn_id' + String(Layouter.settings.dynamic_id) + '"><div class="allowSubCols connectedSortable dropaccept subsort" style="min-height:30px"></div></div>';

                /**
                 *  Content Container Menu
                 */
                var menu = '<div id="menu' + String(Layouter.settings.dynamic_id) + '" class="layoutmenu"><span class="more">mehr...</span><div class="submenu"></div></div>';

                template = template.replace(/xxx0/g, ul);

                // prepare columns
                for (var i = 1; i <= c; i++) {
                    template = template.replace('xxx' + String(i), 'dyn_id' + String(Layouter.settings.dynamic_id + (i - 1)));
                }

                var items = $(template);
                items.prepend($(menu));
                items.find('.allowSubCols').append($('<div class="itemBox dum"/>'));

                list.attr('id', currentID).append(items);
                list = Layouter.formatXml($(list).html());

                $(b.item).replaceWith($(list));



                // add buttons to content container menu
                Layouter.createAddButton('#menu' + String(cidx));
                Layouter.createRemoveButton('#menu' + String(cidx));
                Layouter.createEmptyButton('#menu' + String(cidx));



                $('#menu' + String(cidx)).find('.more').click(function() {
                    $('.layoutmenu .submenu:visible').hide();
                    $(this).parent().find('.submenu:first').slideToggle(50, function() {
                        $(this).unbind('mouseleave').bind('mouseleave', function() {
                            $(this).hide();
                        });

                    });
                });


                $(list).find('.subcolumns').addClass('equalize').addClass('itemBox').addClass('cbox');


                // 
                $(currentID).droppable('destroy');


                // update dynamic id
                Layouter.settings.dynamic_id = Layouter.settings.dynamic_id + c;

                break;
        }


    },
    createAddButton: function(elementid, blockid) {
        var button = $('<span>').attr('title', 'Create/Close Dropzone').addClass('addsubcols');

        button.click(function() {
            if (!$(this).parent().parent().hasClass('dropprotect'))
            {
                $(this).parents().find('.dropaccept').removeClass('dropzone').addClass('dropprotect').droppable({
                    disabled: true
                });
                $(this).parents().find('.ui-sortable').sortable({
                    disabled: true
                });

                $(this).addClass('addsubcols-cancel');

                $(this).parent().parent().addClass('dropaccept').addClass('dropzone');
                $(this).parent().parent().droppable({
                    disabled: false
                }).sortable({
                    disabled: false
                });
                $('#builder .contentelement').draggable({
                    disabled: false
                });
                $('#builder .subtemplate').draggable({
                    disabled: false
                });
            }
            else
            {
                $(this).removeClass('addsubcols-cancel');
                $(this).parents().find('.dropprotect').removeClass('dropprotect').removeClass('dropzone').droppable({
                    disabled: true
                });
                $(this).parents().find('.ui-sortable-disabled').sortable({
                    disabled: false
                });
                //Layouter.initSortables();

                // 
                $('#builder .contentelement').draggable({
                    disabled: true
                });
                $('#builder .subtemplate').draggable({
                    disabled: true
                });
            }
        });

        $(elementid).prepend($(button));
    },
    // Remove the current Subcols
    createRemoveButton: function(elementid) {
        var _self = this;
        var button = $('<span>').attr('title', 'Remove Container').addClass('removecontainer').append('Remove Container');
        button.click(function() {
            var self = this;
            jConfirm('Möchtest du diesen Container wirklich löschen?', 'Bestätigung...', function(r) {
                if (r) {
                    $(elementid).parent().remove();
                    _self.updateDBSubCols($(self).parents('.mainBox:first'));
                }
            });
        });

        $(elementid).find('.submenu').append($('<div>').append(button));
    },
    createEmptyButton: function(elementid) {
        var _self = this;
        var button = $('<span>').attr('title', 'Empty Contents').addClass('emptycontent').append('Empty Contents');
        button.click(function() {
            var self = this;
            jConfirm('Möchtest du diesen Block-Inhalt wirklich löschen?', 'Bestätigung...', function(r) {
                if (r) {

                    if ($(self).parent().parent().hasClass('dropaccept'))
                    {
                        $(self).parent().parent().droppable({
                            disabled: true
                        });
                    }

                    var removedIds = [];
                    $(self).parent().parent().removeClass('addsubcols-cancel');
                    $(self).parent().parent().find('.cbox').each(function() {

                        if (!$(this).hasClass('dum') && !$(this).hasClass('subcolumns'))
                        {
                            removedIds.push($(this).attr('id'));
                            // $(this).remove();
                        }

                    });

                    // remove from database
                    if (removedIds.length > 0)
                    {
                        for (var x in removedIds) {
                            Layouter.removeItem($('#' + removedIds[x]).find('.delete-btn'), false);
                            $('#' + removedIds[x]).remove();
                        }

                        _self.updateDBSubCols($(self).parents('.mainBox:first'));
                    }

                }
            });

        });

        $(elementid).find('.submenu').append($('<div>').append(button));
    },
    /**
     *  Insert all Buttons to subcols
     */
    addSubcolButtons: function(obj) {

        var self = this;
        $('#layoutsContainer .add-subcols').remove();
        $('#layoutsContainer .allowSubCols').unbind('mouseover');
        $('#layoutsContainer .scol').unbind('mouseover');

        $('#layoutsContainer .allowSubCols').each(function() {
            if ($(this).parent().hasClass('layoutBox-container'))
            {
                var after = $(this).parent();
                var addcol = $('<span>').addClass('addcol').attr('title', 'Spalten hinzufügen');
                var list = $(after).find('ul:first');

                addcol.hover(function() {
                    list.addClass('insertCols');
                },
                        function() {
                            list.removeClass('insertCols');
                        }).click(function() {
                    list.addClass('currentSelectedList');
                    self.buildSubCols(list);
                });

                var removecol = $('<span>').attr('title', 'Spalte löschen (rechts nach links)').show().addClass('removecol');

                var div = $('<div>').addClass('add-subcols');
                div.append(addcol);
                div.append(removecol);


                div.insertAfter(after);
            }
        });

        if ($('#layoutsContainer .allowSubCols ul:first').length)
        {
            $('#layoutsContainer .allowSubCols .add-subcols:first-child .removecol').show();
        }


        $('.scol').each(function() {
            var _self = this;

            var addcol = $('<span>').addClass('addcol').attr('title', 'Spalten hinzufügen');
            var removecol = $('<span>').attr('title', 'Spalte löschen (rechts nach links)').show().addClass('removecol');
            var list = $(_self).find('ul:first');

            addcol.hover(function() {
                list.addClass('insertCols');
            }, function() {
                list.removeClass('insertCols');
            }).click(function() {
                list.addClass('currentSelectedList');
                self.buildSubCols(list);
            });

            var div = $('<div>').addClass('add-subcols');
            div.append(addcol);
            div.append(removecol);
            $(this).append(div);
        });
    },
    addBlockToSection: function(obj) {

        var self = this, newId = $(obj).parent().attr('id');
        if (newId.match(/_\d+$/))
        {
            return;
        }

        var rand = Math.floor(Math.random() * 10001);

        // make sure we have a unique ID number
        if ($('#' + newId + '_' + rand).length)
        {
            while ($('#' + newId + '_' + rand).exists()) {
                rand = Math.floor(Math.random() * 10001);
            }
        }

        newId = newId + '_' + rand;
        var blockObj = $(obj).parents('.mainBox:first');

        var cols = $('.cols input:checked').val();
        var block = blockObj.attr('id');
        var layoutid = $('#layout-id').val();


        if (typeof block == 'undefined')
        {
            alert('Invalid Block! Could not instert this Block!' + "\n" + $(obj).parent().parent().html());
            return;
        }

        // set the new id
        $(obj).parent().attr('id', newId);
        //Layouter.addButtonsToItem(obj, data);

        // return;

        $.get('admin.php?adm=layouter&action=addblock&ajax=1&layoutid=' + layoutid + '&current=' + newId + '&cols=' + cols + '&block=' + block, {}, function(data) {
            if (Tools.responseIsOk(data))
            {
                // set the new id
                $(obj).parent().attr('blockid', data.blockid);
                self.addButtonsToItem(obj, data);


                // Update the database
                self.updateDBSubCols(blockObj);
            }
            else
            {
                alert(data.msg);
            }
        }, 'json');


    },
    moveItem: function(obj, currentpos) {
        var self = this, Id = obj.attr('id');

        var cols = $('.cols input:checked').val();
        var blockname = obj.parent().attr('id');
        var layoutid = $('#layout-id').val();

        if (obj.parents('.mainBox:first').hasClass('boxLocked'))
        {
            return false;
        }

        var O = obj;

        if (!obj.parents('.mainBox:first').hasClass('boxLocked'))
        {
            O = obj.parents('.mainBox:first');
            blockname = $(O).attr('id');
        }


        if ($('#' + currentpos).length && !$('#' + currentpos).hasClass('boxLocked'))
        {
            currentpos = $('#' + currentpos).parents('ul.layoutBox:first');
        }




        var neworder = [];
        $(O).find('li').each(function() {

            if (typeof $(this).attr('id') != 'undefined')
            {
                neworder.push($(this).attr('id'));
            }
        });
        var newdata = neworder.join(',');


        $.post('admin.php', {
            adm: 'layouter',
            action: 'moveblock',
            layoutid: layoutid,
            neworder: newdata,
            moved: Id,
            cols: cols,
            block: blockname,
            current: currentpos
        }, function(data) {
            if (Tools.responseIsOk(data))
            {
                self.updateDBSubCols(O);
            }
            else
            {
                //alert(data.msg);
            }
        }, 'json');
    },
    getBlockId: function(obj)
    {
        if ($(obj).attr('id'))
        {
            return $(obj).attr('id');
        }
        else
        {
            return $(obj).parents('.itemBox:first').attr('id');
        }
    },
    /**
     * Content buttons
     *
     */
    addButtonsToItem: function(obj, data) {
        var _self = this, blockid = $(obj).parent().attr('blockid');


        if (typeof data == 'object' && Tools.exists(data, 'blockid'))
        {
            blockid = data.blockid;
        }



        var _edit = $('<span>').addClass('edit-btn').attr('title', 'Inhalt bearbeiten');
        _edit.attr('blockid', blockid);

        var _delete = $('<span>').addClass('delete-btn').attr('title', 'Element entfernen');
        _delete.attr('blockid', blockid);

        var id = this.getBlockId(obj);
        var addEdit = false;
        if (typeof id != 'undefined')
        {
            if (id.match(/^modul_/))
            {
                addEdit = true;
            }
        }

        addEdit = true;

        $(_delete).click(function(e) {
            var self = this;


            if ($(self).parents('.mainBox:first').hasClass('boxLocked'))
            {
                return false;
            }



            jConfirm('Möchtest du diesen Inhalts Container wirklich löschen?', 'Bestätigung...', function(r) {
                if (r) {
                    _self.removeItem(obj);
                    //$(self).parent().remove();
                }
            });
            return false;
        });

        $(obj).append(_delete);

        if (addEdit)
        {
            $(obj).append(_edit);
            $(_edit).click(function(e) {
                _self.editBlock($(this));
                return false;
            });
        }


    },
    clearSections: function(ulcol) {
        if ($('#' + ulcol).find('li:not(.contentPlaceholder)').length == 0) {
            return false;
        }
        var _self = this;

        jConfirm('Möchtest du wirklich alle Inhalts Container löschen?<p><strong>Es gehen alle Daten der Blöcke dabei verloren!!!</strong></p>', 'Bestätigung...', function(r) {
            if (r) {
                $('#' + ulcol).parent().mask('Entferne Inhalte...');
                $('#' + ulcol).removeClass('boxLocked').prev().removeClass('boxLocked');
                var block = ulcol;
                var layoutid = $('#layout-id').val();
                var cols = $('.cols input:checked').val();
                var items = $('#' + ulcol + ' li:not(.contentPlaceholder)');

                items.each(function() {
                    var Id = _self.getBlockId(this);
                    var item = this;
                    $.get('admin.php?adm=layouter&action=removeblock&layoutid=' + layoutid + '&contentbox=' + Id + '&cols=' + cols + '&layoutblock=' + block, {}, function(data) {
                        if (Tools.responseIsOk(data))
                        {
                            $(item).remove();

                        }
                        else
                        {
                            alert(data.msg);
                        }
                    }, 'json');
                });

                // _self.updateDBSubCols( $('#'+ ulcol) );
                $('#' + ulcol).parent().unmask();
            }
        });

        return false;
    },
    removeItem: function(o, updateDBLayout) {

        var self = this, obj = $(o).parents('.cbox:first');
        var Id = this.getBlockId(obj);
        var cols = $('.cols input:checked').val();
        var block = obj.parents('.mainBox:first').attr('id');
        var layoutid = $('#layout-id').val();

        if (obj.parent().parents('.mainBox:first').hasClass('boxLocked'))
        {
            return false;
        }

        obj.parent().parents('.mainBox:first').mask('Entferne Inhalte...');

        $.get('admin.php?adm=layouter&action=removeblock&layoutid=' + layoutid + '&contentbox=' + Id + '&cols=' + cols + '&layoutblock=' + block, {}, function(data) {

            obj.parent().parents('.mainBox:first').unmask();
            if (Tools.responseIsOk(data))
            {
                obj.remove();

                // @todo remove this
                //if (typeof updateDBLayout == 'undefined' || updateDBLayout === true) {
                self.updateDBSubCols(obj.parents('.mainBox:first'));
                //}
            }
            else
            {
                //alert(data.msg);
            }
        }, 'json');


    },
    editBlock: function(obj)
    {
        var self = this, blockID = $(obj).attr('blockid');
        var Id = this.getBlockId(obj);
        var cols = $('.cols input:checked').val();
        var block = obj.parents('div.mainBox:first').attr('id');
        var layoutid = $('#layout-id').val();

        if ($(block).parents('.mainBox:first').hasClass('boxLocked'))
        {
            return;
        }


        data = this.prepareSubColHTML(obj.parents('div.mainBox:first'));
        var html = data[1];
        /*
         console.log(html+"\n\n"+  data[2] +"\n\nOrdered: "+data[0]);
         return;
         
         */



        $.get('admin.php?adm=layouter&action=editblock&layoutid=' + layoutid + '&contentbox=' + Id + '&layoutblock=' + block + '&blockid=' + blockID, {}, function(data) {
            if (Tools.responseIsOk(data))
            {

                self.buildBlockForm(data, obj);
            }
            else
            {
                alert(data.msg);
            }
        }, 'json');

    },
    buildBlockForm: function(data, obj)
    {
        Tools.createPopup(data.form, {
            title: data.formlabel,
            resizeable: true,
            WindowToolbar: data.toolbar
        });

    },
    // Create new sub cols
    buildSubCols: function(obj)
    {
        var self = this, block = obj.attr('id');

        $('#subcol-form .cancel-cols').click(function(e) {
            e.preventDefault();

            $('#subcol-form-container').hide();

            $.pagemask.hide();


            return false;
        });


        $('#subcol-form .save-cols').click(function(e) {
            e.preventDefault();
            $('#subcol-form-container').hide();
            self.saveSubCols(obj);
            $.pagemask.hide();
            return false;
        });

        $('#subcol-form-container').show().modal();
        $('#subcol-form-container').find('span.close').remove();

    },
    saveSubCols: function(obj)
    {
        var self = this, block = $(obj);
        var li = $('<li>').addClass('nosortables');
        var rand = Math.floor(Math.random() * 10001);

        newId = 'subcols';
        // make sure we have a unique ID number
        if ($('#' + newId + '_' + rand).length)
        {
            while ($('#' + newId + '_' + rand).exists()) {
                rand = Math.floor(Math.random() * 10001);
            }
        }
        newId = newId + '_' + rand;

        li.addClass(newId).attr('id', 'subcolcontainer_' + rand);

        var divcontainer = $('<div>').addClass('subcolumns').addClass('equalize').attr('id', newId);
        var numcols = parseInt($('#subcol-form select[name="cols"] option:selected').val());
        if (numcols < 1)
        {
            $('#subcol-form').get(0).reset();
            $('#maincontent .currentSelectedList').removeClass('currentSelectedList');
            return false;
        }

        colclass = '';

        if (numcols == 2)
        {
            colclass = '50';
        }
        else if (numcols == 3)
        {
            colclass = '33';
        }
        else if (numcols == 4)
        {
            colclass = '25';
        }


        li.append(divcontainer);

        for (var i = 1; i <= numcols; i++)
        {
            var ul = $('<ul>').addClass('sortableSubCols').addClass('allowSubCols').css('min-height', '45px');
            ul.sortable(self.fieldSortableOptions).disableSelection();

            var cls = 'c' + ((colclass && (numcols != i)) ? colclass + 'l' : colclass + 'r');
            var colDiv = $('<div>').addClass(cls).addClass('scol');
            colDiv.append(ul);

            divcontainer.append(colDiv);
        }

        $('.currentSelectedList').append(li);

        self.updateDBSubCols($('.currentSelectedList'));


        $('#subcol-form').get(0).reset();
        $('#maincontent .currentSelectedList').removeClass('currentSelectedList');
        self.addSubcolButtons();
        self.initSortables();
    },
    formatXml: function(xml) {
        var reg = /(>)(<)(\/*)/g;
        var wsexp = / *(.*) +\n/g;
        var contexp = /(<.+>)(.+\n)/g;
        xml = xml.replace(reg, '$1\n$2$3').replace(wsexp, '$1\n').replace(contexp, '$1\n$2').replace(/\n{1,}/g, '\n').replace(/    /g, '');
        var pad = 0;
        var formatted = '';
        var lines = xml.split('\n');
        var indent = 0;
        var lastType = 'other';

        // 4 types of tags - single, closing, opening, other (text, doctype, comment) - 4*4 = 16 transitions 
        var transitions = {
            'single->single': 0,
            'single->closing': -1,
            'single->opening': 0,
            'single->other': 0,
            'closing->single': 0,
            'closing->closing': -1,
            'closing->opening': 0,
            'closing->other': 0,
            'opening->single': 1,
            'opening->closing': 0,
            'opening->opening': 1,
            'opening->other': 1,
            'other->single': 0,
            'other->closing': -1,
            'other->opening': 0,
            'other->other': 0
        };

        for (var i = 0; i < lines.length; i++) {
            var ln = lines[i];
            var single = Boolean(ln.match(/<.+\/>/)); // is this line a single tag? ex. <br />
            var closing = Boolean(ln.match(/<\/.+>/)); // is this a closing tag? ex. </a>
            var opening = Boolean(ln.match(/<[^!].*>/)); // is this even a tag (that's not <!something>)
            var type = single ? 'single' : closing ? 'closing' : opening ? 'opening' : 'other';
            var fromTo = lastType + '->' + type;
            lastType = type;
            var padding = '';

            indent += transitions[fromTo];
            for (var j = 0; j < indent; j++) {
                padding += '    ';
            }

            formatted += padding + ln + '\n';
        }

        return formatted;
    },
    prepareSubColHTML: function(currentBlockObj)
    {

        var html = '';
        var $clone = $(currentBlockObj).clone(false, false);


        var subColHTML = [];

        $($clone).find('.layoutmenu').remove();
        $($clone).find('.subcolumns').each(function() {

            $(this).removeClass('ui-sortable-disabled').removeAttr('aria-disabled');
            $(this).removeClass('ui-droppable ui-droppable-disabled ui-state-disabled').removeClass('ui-sortable');



            var id = $(this).attr('id');


            subColHTML.push('[cols:subcols' + id + ']' + $(this).html() + '[/colsend:subcols' + id + ']');

            //$(this).replaceWith( '[subcols'+ id +']'  );



            str = '[START:subcols' + id + ']' + "\n";
            $('<p>').append(str).insertBefore($(this));

            str = '[/END:subcols' + id + ']' + "\n";
            $('<p>').append(str).insertAfter($(this));

        });

        $($clone).find('.cbox').each(function() {
            var id = $(this).attr('id');

            if ($(this).hasClass('dum')) {
                $(this).remove();
            }
            else
            {

                if (!$(this).hasClass('subcolumns')) {

                    $(this).removeClass('ui-droppable ui-droppable-disabled ui-state-disabled').removeClass('ui-sortable').removeClass('ui-sortable-disabled').removeAttr('aria-disabled');

                    $(this).replaceWith('[' + id + ']' + "\n");
                }
            }
        });


        $($clone).find('.dum').remove();

        $($clone).find('.subsort').each(function() {
            //$(this).removeAttr('class');
            $(this).removeAttr('style').removeAttr('aria-disabled');

            $(this).removeClass('ui-droppable ui-droppable-disabled ui-state-disabled').removeClass('ui-sortable');
        });


        $($clone).find('.equalize').each(function() {
            $(this).removeAttr('title');
            $(this).removeAttr('style');
            //$(this).find('div:first').replaceWith($(this).find('div:first').html() )
        });

        $($clone).find('p').each(function() {
            $(this).replaceWith($(this).text());
        });















        var newhtml = $($clone).html();
        var founds = newhtml.match(/\[([a-zA-Z0-9_\-:]*)\]/g);
        var newOrderedIds = [];
        for (i in founds) {
            var str = founds[i];
            str = str.replace('[', '');
            str = str.replace(']', '');
            str = str.replace('START:', '');
            newOrderedIds.push(str);

        }
        var strOrdered = newOrderedIds.join(',');


        html = this.formatXml($($clone).html());
        var data = [];
        data.push(strOrdered);
        data.push(html);
        data.push(this.formatXml(subColHTML.join("\n")));

        return data;


        alert(strOrdered);
        alert(html);
    },
    /**
     * Will save all sub cols. 
     * @returns {undefined}
     */
    saveColsLayout: function(callback)
    {
        var self = this;

        $('body').css('cursor', 'wait');
        
        
        var elements = $('#'+ Win.windowID).find('.mainBox');

        elements.each(function() {
            var parentVisible = $(this).parent().is(':visible');
            var selfVisible = $(this).is(':visible');
            if (parentVisible && selfVisible )
            {
                self.updateDBSubCols($(this));
            }
        });

        $('body').css('cursor', '');
        if (typeof callback == 'function')
        {
            callback();
        }
    },
    updateDBSubCols: function(currentBlockObj)
    {
        var block = $(currentBlockObj).attr('id');
        var cols = $('.cols input:checked').val();
        var layoutid = $('#layout-id').val();


        var neworder = [];
        $(currentBlockObj).find('.cbox').each(function() {
            if (typeof $(this).attr('id') != 'undefined' && !$(this).hasClass('subcolumns'))
            {
                neworder.push($(this).attr('id'));
            }
        });


        var newdata = neworder.join(',');

        if (newdata == '')
        {
            return;
        }


        data = this.prepareSubColHTML($(currentBlockObj));



        newdata = data[0];
        var html = data[1];

        //alert('Layout:'+layoutid +' block:'+block+' cols:'+cols+' html:'+html);

        //return;
        $.post('admin.php', {
            adm: 'layouter',
            action: 'savesubcols',
            layoutid: layoutid,
            cols: cols,
            layoutblock: block,
            neworder: newdata,
            //   subneworders: subneworders,
            htmldata: html
        }, function(data) {
            if (Tools.responseIsOk(data))
            {

            }
            else
            {
                // alert(data.msg);
            }
        }, 'json');


        return;

        var subneworders = [];
        $(currentBlockObj).find('.subsort').find('.cbox:not(.subcolumns)').each(function() {
            subneworders.push($(this).attr('id'));
        });
        var subneworders = subneworders.join(',');

        var html = '';
        /*
         $(currentBlockObj).find('.subcolumns').each(function(){
         var htmlClone = $(this).parent().clone(false);		
         $(htmlClone).find('.add-subcols').remove();			
         html += $(htmlClone).html();			
         
         });
         
         alert(html);
         */

        var O = currentBlockObj;

        if (!$(currentBlockObj).hasClass('mainBox'))
        {
            O = $(currentBlockObj).parents('.mainBox:first');
        }



        var $htmlClone = $(O).clone(false, false);

        $($htmlClone).find('.add-subcols').remove();
        $($htmlClone).find('span').remove();
        $($htmlClone).find('div.subsort:first-child').each(function() {
            var id = $(this).attr('id');

            if (typeof id != 'undefined')
            {

                if ($(this).hasClass('contentPlaceholder'))
                {
                    $(this).empty();
                }

                if ($(this).hasClass('nosortables'))
                {
                    if ($(this).parent().hasClass('mainBox'))
                    {
                        subid = id.replace('subcolcontainer', '');

                        // read subcontent
                        var container = $(this).children(':first').clone();



                        $(container).find('div').each(function() {
                            var _id = $(this).attr('id');
                            if (!$(this).children('div').length)
                            {
                                $(this).replaceWith('[' + _id + ']' + "\n");
                            }
                            else
                            {
                                '[' + _id + ']' + "\n".insertAfter(this);
                            }
                        });

                        // find text
                        var containerContent = container.text();
                        var containerHTML = container.html();
                        //containerHTML = containerHTML.replace(containerContent, ''); // clear Text

                        $(this).html("\n" + '[START:subcols' + subid + ']' + "\n" + containerHTML + "\n" + '[/END:subcols' + subid + ']' + "\n");
                    }
                }
                else
                {
                    if (!$(this).children('div').length)
                    {
                        $(this).replaceWith('[' + id + ']' + "\n");
                    }
                    else
                    {
                        '[' + id + ']' + "\n".insertAfter(this);
                    }
                }

            }
        });



        // html += $($htmlClone).html();


        var subcols = [];
        $('.subcolumns').each(function() {


            subcols.push($(this).html());
        });
        html = '';
        html = subcols.join('#SUB#');


        // alert(html);
        //    html = Layouter.formatXml(html);

        alert('Layout:' + layoutid + ' block:' + block + ' cols:' + cols + ' html:' + html);

        return;
        $.post('admin.php', {
            adm: 'layouter',
            action: 'savesubcols',
            layoutid: layoutid,
            cols: cols,
            block: block,
            neworder: newdata,
            subneworders: subneworders,
            htmldata: html
        }, function(data) {
            if (Tools.responseIsOk(data))
            {

            }
            else
            {
                alert(data.msg);
            }
        }, 'json');
    }


};


