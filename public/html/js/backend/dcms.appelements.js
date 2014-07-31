var connectWithSortable = new Array();
var connectWith = new Array();
var fieldediticon = '<img src="images/field-edit.png" width="16" height="16" alt=""/>';

function registerApplicationOptions()
{
    var _window = $('#' + Win.windowID);
    var id = $('.tab:first').attr('id');
    if (id) {
        id = id.replace('tab-', '');
    }

    _window.find('#app-elements').find('.tab-content').hide();
    _window.find('.tab:first').addClass('active');
    _window.find('#tab-content-' + id).show();


    var accheight = _window.find("#field-elements").outerHeight();
    var handleheight = _window.find("#field-elements h3").outerHeight();
    var handles = _window.find('#field-elements').find('h3').length;


    _window.find("#field-elements").accordion({
        heightStyle: "fill",
        animate: false,
        header: "h3",
        icons: {"header": "", "activeHeader": ""}
    });

    /*
     
     
     $("#field-elements h3:first").addClass("active");
     $("#field-elements div:not(:first)").hide();
     $("#field-elements h3:first").next('div').show();
     $("#field-elements h3").click(function() {
     if ($(this).hasClass('active'))
     {
     return false;
     }
     
     $(this).next("div").slideToggle(400).siblings("div:visible").slideUp(400);
     $(this).toggleClass("active");
     $(this).siblings("h3").removeClass("active");
     });
     
     */

    _window.find('span.tmpName').css({
        'z-index': 1000
    });

    getConnectWith();
    setSortables();

    _window.find('#app-elements').find('ul.app-tabs li').each(function() {
        if (!$(this).hasClass('add-tab'))
        {
            var addThis = $('<span class="icon"><span class="rename-tab" title="' + cmslang.app_rename_tab + '"></span><span class="delete-tab" title="' + cmslang.app_delete_tab + '"></span></span>');
            $(this).append(addThis);


            var id = $(this).attr('id');
            id = id.replace('tab-', '');


            // patch empty tab contents
            if (_window.find('#tab-content-' + id).length == 0 || _window.find('#tab-content-' + id).length == 'undefined')
            {

                _window.find('#app-elements').append($('<div class="tab-content">' + "\r\n" + '<ul class="sortable ui-helper-reset">' + "\r\n\r\n" + '</ul>' + "\r\n" + '</div>').attr('id', 'tab-content-' + id).show());
            }

            // Standart tab darf nicht gelöscht werden
            if ($(this).hasClass('core-tab'))
            {
                $(this).find('.delete-tab').remove();
            }


            // Add TAB edit / remove action
            registerTabActions(id);

        }
    });



    _window.find('.add-tab').click(function() {
        jPrompt(cmslang.app_addtab_prompt, '', cmslang.app_addtab_prompttitle, function(label) {

            if (label) {
                $.get('admin.php?adm=app&action=addtab&appid=' + $('#appid').val() + '&label=' + label, {}, function(data) {

                    if (Tools.responseIsOk(data)) {
                        _window.find("#app-elements").tabs("destroy");
                        _window.find('#app-elements ul:first').dcmsAddTab(data.msg, {
                            tabcontent_container: '#app-elements'
                        });

                        var id = _window.find('#app-elements ul:first li.tab:not(.add-tab):last').attr('id');
                        id = id.replace('tab-', '');


                        // Add tab edit
                        _window.find('#tab-' + id).find('.icon').append($('<span>').addClass('rename-tab'));
                        _window.find('#tab-' + id).find('.icon').append($('<span>').addClass('delete-tab'));
                        registerTabActions(id);

                        // Clean connectWith array
                        connectWith = new Array();
                        connectWithSortable = new Array();
                        getConnectWith();
                        setSortables();

                        /*
                         $( "#app-elements" ).tabs({
                         selected: id
                         });
                         */

                        Notifier.display('info', data.msg);
                    }
                    else {
                        jAlert(data.msg);
                    }
                }, 'json');

            }
        });

    });



    // Register FIELD edit / remove action
    var added = _window.find('#added-fields').find('.element-holder');
    added.each(function() {

        $(this).find('.edit-btn').css('cursor', 'pointer').attr('title', cmslang.app_edit_field).click(function() {
            loadFieldData($(this).parent());
        });

        $(this).find('.remove-btn').css('cursor', 'pointer').click(function() {
            deleteField($(this).parent());
        });
    });

    _window.find('#added-fields').disableSelection();
    _window.find('#field-elements').disableSelection();


}



function moveToTab(totab, fromLable, fieldid, elementType)
{
    // update db tabs
    if (totab && totab != fromLable && fromLable != '' && fieldid)
    {
        var ismove = true;
        //alert(fieldid +' ' + fromLable +' ' +totab +' ' +elementType);
        //alert('admin.php?adm=application&action=edit&manage=elements&appid='+ $('#appid').val() +'&itemtype='+elementType+'&do=movetotab&totab='+ tabLabel +'&formtab='+ formLabel +'&fieldid='+ fieldid);
        setTimeout(function() {
            $.post('admin.php',
                    {
                        'adm': 'app',
                        'action': 'edit',
                        '_type': 'app',
                        'manage': 'elements',
                        'appid': $('#' + Win.windowID).find('#appid').val(),
                        'itemtype': elementType,
                        'do': 'movetotab',
                        'totab': totab,
                        'formtab': fromLable,
                        'fieldid': fieldid
                    }, function(data) {
                if (Tools.responseIsOk(data)) {
                    Notifier.display('info', data.msg);
                }
                else {
                    jAlert(data.msg);
                }
            }, 'json');
        }, 150);

    }
}

function updateFieldOrders(serialized, moveToLable, elementType)
{
    $.get('admin.php?adm=app&action=edit&_type=app&manage=elements&appid=' + $('#appid').val() + '&itemtype=' + elementType + '&do=reorder&' + serialized + '&fromlabel=' + moveToLable, {}, function(data) {

        if (Tools.responseIsOk(data)) {
            Notifier.display('info', data.msg);
        }
        else {
            jAlert(data.msg);
        }
    }, 'json');
}




function getConnectWith()
{
    $('#' + Win.windowID).find('#app-elements ul:first').find('.tab').each(function() {
        if (!$(this).hasClass('add-tab'))
        {
            var id = $(this).attr('id');
            id = id.replace('tab-', '');
            if (id > 0)
            {
                connectWith.push('#tab-content-' + id);
                connectWithSortable.push('#tab-content-' + id + ' ul');
            }
        }
    });
}

function setSortables()
{
    var moveid = '';
    var fromLable = '';
    var moveToLable = '';
    var selectedItems = null;
    var ismove = false;

    var $tabs = $('#' + Win.windowID).find("#app-elements").tabs({
        panelTemplate: '<div class="tab-content"></div>',
        selected: 0
    });

    var currentTab = $('#' + Win.windowID).find('#app-tabs').find('li:first:not(.add-tab)');

    if (currentTab.length == 1)
    {
        var id = currentTab.attr('id');

        if (id)
        {
            currentTab.addClass('active');
            $('#' + Win.windowID).find('#app-elements').find('.tab-content').hide();

            id = id.replace('tab-', '');
            $('#' + Win.windowID).find('#tab-content-' + id).show();
        }
    }



    $('#' + Win.windowID).find('#app-tabs').find('li:not(.add-tab)').each(function() {
        // Register TAB click action
        $(this).click(function(e) {
            e.preventDefault();

            var id = $(this).attr('id');
            id = id.replace('tab-', '');

            $(this).parent().find('.tab').removeClass('active');
            $('#' + Win.windowID).find('#app-elements').find('.tab-content').hide();

            $('#' + Win.windowID).find('#tab-' + id).addClass('active');
            $('#' + Win.windowID).find('#tab-content-' + id).show();
        });

    });







    var isInternalAppElement = false;
    var $items = $(connectWithSortable.join(',')).find('li');
    if ($('#helper-element').length == 0)
    {
        $('body').append($('<ul id="helper-element">'));
    }

    //alert(connectWithSortable);
    $($items).on('click', function(e) {
        if (e.altKey) {
            $(this).toggleClass('grouped');
        }
    });

    $(connectWithSortable.join(','), $tabs).sortable({
        connectWith: connectWith.join(','),
        forceHelperSize: true,
        forcePlaceholderSize: true,
        placeholder: 'ui-sortable-placeholder',
        distance: 10,
        //revert: true,
        helper: 'clone',
        tolerance: 'pointer',
        handler: '.element-holder',
        cancel: 'div.box div.box-inner fieldset',
        start: function(event, ui) {
            //moveid = $(ui.item).attr('id');
            // alert('ID: '+ moveid);
            //var id = $(ui.helper).parents(".tab-content").attr('id').replace('tab-content-', '');			
            //fromLable = trim( $('#tab-'+id).find('a').text() );
            ismove = false;

            if ($(this).parent().hasClass('app-intern'))
            {
                isInternalAppElement = true;
            }
        },
        stop: function(event, ui) {

            if ($(this).hasClass('drag'))
            {
                return;
            }


            var $item = $(this);
            var $list = $($item.find("span").attr("rel")).parents().find(".sortable:first");
            var serialized = '';
            var id = $('#' + Win.windowID).find(".tab-content:visible").attr('id').replace('tab-content-', '');
            var fromLable = trim($('#' + Win.windowID).find('#tab-' + id).find('span').text());


            var items = $('#' + Win.windowID).find(".tab-content").find('.element-holder');
            items.each(function() {
                var id = $(this).attr('id');
                id = id.replace('field-', 'field[]=');
                serialized += (serialized ? '&' : '') + id;
            });
            //alert(serialized);

            //var fieldid = $(this).find('div.element-holder').attr('id').replace('field-', '');


            if (serialized != '')
            {
                var activetab = $('#' + Win.windowID).find('#app-tabs li.active');
                var tabid = activetab.attr('id').replace('tab-', '');

                var elementType = $('#' + Win.windowID).find('#app-elements').attr('class').replace('el-', '');
                elementType = elementType.replace(/^([^\s]*).*/g, '$1');

                var totab = $(activetab).find('span').text();

                setTimeout(function() {
                    updateFieldOrders(serialized, totab, elementType)
                }, 250);
            }
        }

    });



    $('#' + Win.windowID).find('#field-elements ul.elements li').draggable({
        helper: 'clone',
        revert: 'invalid',
        opacity: 0.7,
        zIndex: 9000,
        stack: ".tab-content",
        connectToSortable: connectWithSortable.join(','),
        stop: function(event, ui) {
            $('.tab-label').unbind('mouseover');

        },
        start: function(event, ui) {
    
            $(connectWithSortable.join(','), $tabs).sortable('disabled');

            $(this).addClass('drag');
            if ($(this).parent().hasClass('app-intern'))
            {
                isInternalAppElement = true;
            }

            var id = $('#' + Win.windowID).find(".tab-content:visible").attr('id').replace('tab-content-', '');
            fromLable = trim($('#' + Win.windowID).find('#tab-' + id).find('a').text());
            ismove = false;
            // $(connectWithSortable.join(',')).sortable({disabled: true});
            $(ui.helper).css({
                width: $('#' + Win.windowID).find('#field-elements').width() + 'px'
            });
        }
    });

    /*
     $(connectWithSortable.join(',')).selectable({
     filter: 'li',
     distance: 20,
     start: function(event, ui) { 
     $(connectWithSortable.join(',')).sortable({ disabled: true });
     },
     stop: function() {
     var list = $(this);
     selectedItems = new Array;
     
     $( ".ui-selected", this ).each(function() {
     selectedItems.push( $(this).find('div.element-holder:first').attr('id').replace('field-') );
     
     var cabbage = this.id + ',';
     var index = $(list).children("li").index( this );
     item = $(list).index( index );
     alert( $(item).find('div.element-holder:first').attr('id') );
     
     
     alert(index + 1);
     //selectedItems.push( $( "#selectable li" ).find('div.element-holder:first').attr('id').replace('field-').index( this ) );
     });
     $(connectWithSortable.join(',')).sortable({ enable: true, disabled: false });
     alert( selectedItems.join(',') );
     }
     });
     
     */

    /**
     * When item is dropped from the Add <Stuff>
     */
    var $tab_items = $('#' + Win.windowID).find('#app-tabs li:not(.add-tab),#app-elements ul.sortable').droppable({
        hoverClass: "ui-sortable-placeholder",
        tolerance: 'pointer',
        
        drop: function(event, ui) {

            var id = $('#' + Win.windowID).find(".tab-content:visible").attr('id').replace('tab-content-', '');
            var toLabel = trim($('#' + Win.windowID).find('#tab-' + id).find('span').text());


            var $item = $(this);
            var $list = $($item.find("span").attr("rel")).find(".sortable");
            if (!$($list).length)
            {
                return;
            }


            var toId = $($list).parents('div.tab-content').attr('id').replace('tab-content-', '');
            var totab = trim($('#' + Win.windowID).find('#tab-' + toId).find('span').text());

            ui.draggable.show();
            $(ui.draggable).hide('slow', function() {

                // Change active tab
                //$('#app-tabs li.active').removeClass('active');
                $('#' + Win.windowID).find('#app-tabs').find('li#tab-' + toId).trigger('click');
                $('#' + Win.windowID).find('#app-tabs').find('li#tab-' + toId).addClass('active');

                var holder = $(this).find('div.element-holder');
                if (holder.length)
                {
                    var fieldid = $(this).find('div.element-holder').attr('id').replace('field-', '');
                }

                // Move this element to active Tab
                if (totab != toLabel && toLabel != '' && fieldid)
                {
                    ismove = true;

                    $(ui.draggable).appendTo($('#' + Win.windowID).find('#tab-content-' + toId + ' ul:first')).show('fast', function() {

                        // $(ui.helper).remove();

                        var elementType = $('#' + Win.windowID).find('#app-elements').attr('class').replace('el-', '');
                        elementType = elementType.replace(/^([^\s]*).*/g, '$1');




                        // update db tabs
                        moveToTab(totab, toLabel, fieldid, elementType);
                        /*
                         var serialized;
                         
                         var items = $(".tab-content").find('.element-holder');
                         items.each(function() {
                         var id = $(this).attr('id');
                         id = id.replace('field-', 'field[]=');
                         serialized += (serialized ? '&' : '') + id;
                         });
                         
                         if (serialized)
                         {
                         updateFieldOrders(serialized, totab, elementType);
                         }
                         */

                        event.preventDefault();





                        //setTimeout(function(){ $(connectWithSortable.join(',')).sortable({enable: true, disabled: false}); }, 300);

                    });



                }
                else
                {
                    // add the new element to active Tab
                    addBoxElement($(ui.helper), isInternalAppElement);
                    isInternalAppElement = false;
                    $(connectWithSortable.join(',')).sortable({enable: true, disabled: false});
                }

            });

            $(connectWithSortable.join(','), $tabs).sortable('enable');

            //
        }
    });

    // $('.tab-content').get(0).className = 'tab-content';
    $tabs.removeClass('ui-tabs-nav');
    $tabs.removeClass('ui-helper-reset');
    $tabs.removeClass('ui-widget-header');
    $('#' + Win.windowID).find('#app-elements').removeClass('ui-tabs').removeClass('ui-tabs-nav');

    $('#' + Win.windowID).find('#app-elements .ui-widget-header').removeClass('ui-widget-header');
    $('#' + Win.windowID).find('#app-elements .ui-corner-top').removeClass('ui-corner-top');
    $('#' + Win.windowID).find('#app-elements .ui-corner-all').removeClass('ui-corner-all');

    $('#' + Win.windowID).find('#app-elements').removeClass('ui-widget').removeClass('ui-widget-content').removeClass('ui-corner-all');



}


function registerTabActions(tabid)
{
    var elementType = $('#' + Win.windowID).find('#app-elements').attr('class').replace('el-', '');
    elementType = elementType.replace(/^([^\s]*).*/g, '$1');


    $('#' + Win.windowID).find('#tab-' + tabid + ' .rename-tab').click(function(e) {
        if (typeof e != 'undefined') {
            e.preventDefault();
        }

        var original = $('#' + Win.windowID).find('#tab-' + tabid + ' a').text();

        jPrompt(cmslang.app_renametab_prompt, original, cmslang.app_renametab_prompttitle, function(label) {

            if (label && label != original) {
                $.post('admin.php', {
                    'adm': 'app',
                    'action': 'renametab',
                    'manage': 'elements',
                    'appid': $('#' + Win.windowID).find('#appid').val(),
                    'itemtype': elementType,
                    //    'fieldid': fieldid,
                    oldlabel: original,
                    'label': label
                }, function(data) {

                    if (Tools.responseIsOk(data))
                    {
                        $('#' + Win.windowID).find('#tab-' + tabid + ' span:first').html(data.label)
                        Notifier.display('info', cmslang.app_renametab_notify);
                    }
                    else {
                        jAlert(data.msg);
                    }
                }, 'json');

            }
        });

    });

    $('#' + Win.windowID).find('#tab-' + tabid + ' .delete-tab').click(function(e) {


        // Standart Tab
        var defaultTab = $('#' + Win.windowID).find('#app-elements li.tab:first');
        var id = defaultTab.attr('id');
        id = id.replace('tab-', '');

        var defaultTabContent = $('#' + Win.windowID).find('#tab-content-' + id);
        var label = $('#tab-' + tabid + ' span').text();

        if (typeof e != 'undefined') {
            e.preventDefault();
        }



        jConfirm(cmslang.app_deletetab_confirm.replace('%s', label), cmslang.app_deletetab_confirmtitle, function(ev) {
            if (ev) {

                $.post('admin.php',
                        {
                            'adm': 'app',
                            'action': 'deletetab',
                            'appid': $('#appid').val(),
                            'itemtype': elementType,
                            'label': label

                        }, function(data) {

                    if (Tools.responseIsOk(data))
                    {
                        $('#' + Win.windowID).find('#tab-' + tabid).remove();
                        defaultTabContent.append($('#' + Win.windowID).find('#tab-content-' + tabid).html());
                        $('#' + Win.windowID).find('#tab-content-' + tabid).remove();

                        // Clean connectWith array
                        connectWith = new Array();
                        connectWithSortable = new Array();
                        getConnectWith();
                        setSortables();

                        Notifier.display('info', cmslang.app_deletetab_notify.replace('%s', data.msg));
                    }
                    else {
                        jAlert(data.msg);
                    }
                }, 'json');

            }
        });

    });
}



function createElementForm()
{


}





function addBoxElement(ui, isInternalAppElement)
{

    if ($(ui).attr('rel') == undefined || $(ui).attr('rel') == '')
    {
        return;
    }

    var elementType = $('#' + Win.windowID).find('#app-elements').attr('class').replace('el-', '');
    elementType = elementType.replace(/^([^\s]*).*/g, '$1');

    var activetab = $('#' + Win.windowID).find('#app-tabs li.active');
    var tabid = activetab.attr('id').replace('tab-', '');

    var save = $('<button class="action-button save-btn"><img src="' + Config.get('backendImagePath') + 'buttons/save.png" width="16" height="16" alt=""/> <span>' + cmslang.app_save_field + '</span></button>').attr('title', cmslang.app_save_field);
    var remove = $('<img src="' + Config.get('backendImagePath') + 'field-remove.png" width="16" height="16" alt="" title="' + cmslang.app_delete_field + '" class="remove-btn" style="float:right"/>');
    var edit = $('<img src="' + Config.get('backendImagePath') + 'field-edit.png" width="16" height="16" alt="" class="edit-btn" style="float:right"/>').css('cursor', 'pointer').attr('title', cmslang.app_edit_field);

    var keyname = $(ui).attr('rel').replace('field', '');
    var title = $(ui).text();
    var newfield = $('<li>').addClass('appel');



    var holder = $('<div>').addClass('element-holder').attr({
        'id': 'field-999999',
        'rel': keyname
    });

    var tmpName = cmslang.app_new_field;
    if (isInternalAppElement)
    {
        tmpName = title;
    }





    holder.append($('<span class="' + keyname + '"></span>'));
    holder.append($('<span class="tmpName">' + tmpName + '</span>'));
    holder.append(' (' + keyname + ')');
    holder.append(remove);
    holder.append(edit);

    newfield.append(holder);

    $('#' + Win.windowID).find('#tab-content-' + tabid + ' ul:first').append(newfield);



    var boxtitle = $('<h2>').append('<span>' + cmslang.app_new_fieldnamed.replace('%s', title) + '</span> (' + keyname + ')');
    var boxinner = $('<div>').addClass('box-inner').css({
        'min-height': '100px'
    });
    boxtitle.append(save);
    // boxtitle.append( remove);
    // boxtitle.append( edit  );


    var box = $('<div>').attr({
        'id': 'form-999999',
        'rel': $(ui).attr('rel')
    }).addClass('box').append(boxtitle).css({
        'margin-bottom': '5px'
    });

    var toolbar = $('<div>').addClass('toolbar2');
    toolbar.append(save);
    //box.append(toolbar);



    var form = $('<form action="admin.php" name="fieldForm-999999" id="fieldForm-999999" method="post"></form>');
    form.append(toolbar);
    edit.click(function() {
        var id = $(this).parent().attr('id').replace('field-', '');
        $('#' + Win.windowID).find('#form-' + id).modal();
    });


    var hidden = '<input type="hidden" name="adm" value="app"/>'
            + '<input type="hidden" name="action" value="edit"/>'
            + '<input type="hidden" name="_type" value="app"/>'
            + '<input type="hidden" name="manage" value="elements"/>'
            + '<input type="hidden" name="do" value="savefield"/>'
            + '<input type="hidden" name="fieldid" id="field-id-999999" value="0"/>'
            + '<input type="hidden" name="itemtype" value="' + elementType + '"/>'
            + '<input type="hidden" name="fieldtype" value="' + keyname + '"/>'
            + '<input type="hidden" name="appid" value="' + $('#appid').val() + '"/>'
            + '<input type="hidden" name="appkey" value="' + $('#app-key').val() + '"/>';




    remove.css('cursor', 'pointer').click(function() {
        deleteField($(this).parent());
    });


    var tmp = ' <fieldset>'
            + '     <legend>' + cmslang.app_fieldname + '</legend>'
            // +'     {info:fields|name}'
            + '     <input type="text" name="fieldname" id="field-name-999999" size="60" maxLength="250" value="" class="required"/>'
            + ' </fieldset>'
            + ' '
            + ' <fieldset>'
            + '     <legend>' + cmslang.app_fielddescription + '</legend>'
            //   +'     {info:fields|description}'
            + '     <textarea name="description" id="field-description-999999" rows="3"></textarea>'
            + ' </fieldset>';

    boxinner.append(tmp);


    form.html(hidden);
    form.append(boxinner);
    box.append(form);
    box.hide();

    $('#' + Win.windowID).find('#tab-content-' + tabid).prepend(box);

    $('#' + Win.windowID).find('#field-forms').append(box);

    var tabLabel = $('#' + Win.windowID).find('#tab-' + tabid + ' span').text();

    // alert('admin.php?adm=application&action=edit&manage=elements&appid='+ $('#appid').val() +'&element='+ elementType +'&do=editfield&fieldtype='+ keyname  +'&fieldid=0&tab='+tabLabel);return;




    boxinner.mask(cmslang.mask_getdata);

    $.post('admin.php', 'adm=app&action=edit&_type=app&manage=elements&appid=' + $('#appid').val() + '&element=' + elementType + '&do=editfield&fieldtype=' + keyname + '&fieldid=0&tab=' + tabLabel, function(data) {
        boxinner.unmask();

        if (Tools.responseIsOk(data))
        {
            holder.attr('id', 'field-' + data.fieldid);
            boxinner.append(data.fieldhtml);
            boxtitle.attr({
                id: 'field_' + data.fieldid,
                'rel': $(ui).attr('rel')
            });

            $(box).attr('id', 'form-' + data.fieldid);

            $('#' + Win.windowID).find('#field-id-999999').attr('id', "field-id-" + data.fieldid).val(data.fieldid);
            $('#' + Win.windowID).find('#fieldForm-999999').attr({
                'id': "fieldForm-" + data.fieldid,
                'name': "fieldForm-" + data.fieldid
            });

            $('#' + Win.windowID).find('#field-name-999999').attr('id', "field-name-" + data.fieldid).val(data.fieldname);
            $('#' + Win.windowID).find('#field-description-999999').attr('id', "field-description-" + data.fieldid).val(data.description);
            $('#' + Win.windowID).find('#field-forms').find('#fieldForm-999999').attr('name', "fieldForm-" + data.fieldid);
            $('#' + Win.windowID).find('#field-forms').find('#fieldForm-999999').attr('id', "fieldForm-" + data.fieldid);

            save.show();


            registerSaveBtn(data.fieldid, Win.wm);

            /*
             set_dirty();
             setFormStatusOk(cmslang.app_new_fieldnotify.replace('%s', title));
             rebuildTooltips();
             */

            Notifier.display('info', cmslang.app_new_fieldnotify.replace('%s', title));



            // Clean connectWith array
            connectWith = new Array();
            connectWithSortable = new Array();
            getConnectWith();
            setSortables();
            $(connectWithSortable.join(',')).sortable('enable');
        }
        else {

            jAlert(data.msg);
        }
    }, 'json');
}



function loadFieldData(obj)
{
    var fieldtype = $(obj).attr('rel').replace('field', '');
    var fieldid = $(obj).attr('id').replace('field-', '');
    var boxinner = $('#' + Win.windowID).find('#form-' + fieldid).find('.box-inner');
    var save_btn = $('#' + Win.windowID).find('#form-' + fieldid).find('.save-btn');
    var box = $('#' + Win.windowID).find('#form-' + fieldid);

    var activetab = $('#' + Win.windowID).find('#app-elements .tab-content:visible').attr('id').replace('tab-content-', '');
    label = $('#' + Win.windowID).find('#tab-' + activetab).find('a').text();


    if (box.length === 0)
    {
        //alert('Invalid application request!');
    }

    if ($(boxinner).find('fieldset').length != 0)
    {
        //$(box).modal();

        Tools.createPopup($(box).html(), {
            title: 'Formular-Feld Einstellungen...',
            onAfterShow: function(event, _wm, callback) {
                _wm.$el.find('field-type-edit-toolbar').show();
                registerSaveBtn(fieldid, _wm);

                if (Tools.isFunction(callback))
                {
                    callback();
                }
            },
            onBeforeClose: function(event, _wm, callback) {

            }
        });



        return false;
    }

    // box.appendTo( $('body') );

    var elementType = $('#' + Win.windowID).find('#app-elements').attr('class').replace('el-', '');
    elementType = elementType.replace(/^([^\s]*).*/g, '$1');






    // ($(box).hasClass('iscore') ? ' disabled" readonly="readonly' : '')




    $('#' + Win.windowID).find('#fieldtype-' + fieldid).val(fieldtype);

    // alert('admin.php?adm=application&action=edit&manage=elements&appid='+ $('#appid').val() +'&element='+ elementType +'&do=editfield&fieldtype='+ fieldtype +'&fieldid='+fieldid);

    $.get('admin.php?adm=app&action=edit&_type=app&manage=elements&tablabel=' + escape(label) + '&appid=' + $('#' + Win.windowID).find('#appid').val() + '&element=' + elementType + '&do=editfield&fieldtype=' + fieldtype + '&fieldid=' + fieldid, {}, function(data) {

        if (Tools.responseIsOk(data)) {


            var tmp =
                    '<fieldset>' + '<input type="hidden" name="tab_label" value="' + label + '"/>'
                    + '     <legend>' + cmslang.app_fieldname + '</legend>'
                    // +'     {info:fields|name}'
                    + '     <input type="text" name="fieldname" id="field-name-' + fieldid + '" size="60" maxLength="250" value="' + data.fieldname + '" class="required"/>'
                    + ' </fieldset>'
                    + ' '
                    + ' <fieldset>'
                    + '     <legend>' + cmslang.app_fielddescription + '</legend>'
                    //   +'     {info:fields|description}'
                    + '     <textarea name="description" id="field-description-' + fieldid + '" rows="3">' + data.description + '</textarea>'
                    + ' </fieldset>';


            Tools.createPopup(data.fieldhtml, {
                title: 'Formular-Feld Einstellungen...',
                WindowToolbar: data.toolbar,
                onBeforeShow: function(event, wm, _callback) {

                    wm.$el.find('#field-type-edit-toolbar').show();

                    wm.focus();
                    Win.setActive(wm.id);
                    Desktop.Tools.rebuildTooltips();


                    if (wm.get('hasTinyMCE') === true)
                    {
                        $(wm.$el).addClass('tinyMCEwin');
                    }

                    registerSaveBtn(fieldid, wm);

                    if (Tools.isFunction(_callback))
                    {
                        setTimeout(function() {
                            _callback();
                        }, 100);
                    }
                }
            });



        }
        else {

            jAlert(data.msg);
        }
    }, 'json');
}

function registerSaveBtn(fieldid, _wm)
{
    var box = $(_wm.$el).find('#form-' + fieldid);
    var boxinner = box.find('.box-inner');

    $(_wm.$el).find('.save-btn').unbind('click');
    $(_wm.$el).find('.save-btn').click(function() {
        boxinner.mask(cmslang.mask_saving);

        var post = $('#fieldForm-' + fieldid).serialize() + '&ajax=1';
        // alert( "ID:"+fieldid +' '+post );
        // return;
        $.post('admin.php', post, function(data)
        {
            boxinner.unmask();

            if (Tools.responseIsOk(data))
            {
                $('#field-' + fieldid).find('.tmpName').text($('#field-name-' + fieldid).val());

                disableSaveElement(fieldid, true);
                Notifier.display('info', data.msg);

            }
            else {
                // alert(data.msg);return;
                var errstr = '';
                if (typeof data.formerrors != 'undefined')
                {
                    for (var field in data.formerrors) {
                        if (typeof data.formerrors[field] != 'undefined')
                        {
                            for (var x in data.formerrors[field]) {
                                errstr += (errstr != '' ? '<br>' : '') + data.formerrors[field][x];
                            }
                        }
                        else
                        {
                            errstr += (errstr != '' ? '<br>' : '') + data.formerrors[field];
                        }
                    }
                }


                jAlert((typeof data.msg != 'undefined' ? data.msg : '') + ' ' + errstr);
            }

        }, 'json');

    });
}

function deleteField(obj)
{
    var fieldtype = $(obj).attr('rel').replace('field', '');
    var fieldid = $(obj).attr('id').replace('field-', '');

    var elementType = $('#' + Win.windowID).find('#app-elements').attr('class').replace('el-', '');
    elementType = elementType.replace(/^([^\s]*).*/g, '$1');

    jConfirm(cmslang.app_delete_field_confirm, cmslang.alert, function(r) {
        if (r) {

            $('#' + Win.windowID).find('#form-' + fieldid).mask(cmslang.app_delete_mask);

            $.get('admin.php?adm=app&action=edit&_type=app&manage=elements&appid=' + $('#appid').val() + '&itemtype=' + elementType + '&do=delete&fieldtype=' + fieldtype + '&fieldid=' + fieldid, {}, function(data) {
                if (Tools.responseIsOk(data))
                {
                    $('#' + Win.windowID).find('#field-' + fieldid).parent().remove();
                    $('#' + Win.windowID).find('#form-' + fieldid).remove();

                    //    getConnectWith();
                    //     setSortables();

                    Notifier.display('info', cmslang.app_delete_notify);
                }
                else
                {
                    $('#' + Win.windowID).find('#form-' + fieldid).unmask();
                    jAlert(data.msg);
                }

            });
        }
    });
}


function disableSaveElement(id, truefalse)
{
    if (truefalse) {
        $('#' + Win.windowID).find('#form-' + id).find('.save-btn').addClass('disabled');
    }
    else
    {
        $('#' + Win.windowID).find('#form-' + id).find('.save-btn').removeClass('disabled');
    }
}