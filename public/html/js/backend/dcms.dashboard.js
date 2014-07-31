var Dashboard = {
    inited: false,
    dashboardOn: false,
    activeWidgetCache: null,
    init: function()
    {
        if (this.inited)
        {
            return;
        }
        this.bindDashboardShortcuts();
        this.inited = true;
    },
    bindDashboardShortcuts: function()
    {
        var self = Dashboard;
        
        $(document).on('keydown.dashboard', function(e) {
            var meta = e.ctrlKey || e.metaKey;

            if (e.keyCode == 123 && meta) {
                if (self.dashboardOn)
                {
                    self.close();
                }
                else
                {
                    self.show();
                }
            }

        });
    },
    updateSize: function()
    {
        $('#dashboard').height(window.innerHeight).width(window.innerWidth);
        $('#dashboard-main').height(window.innerHeight - $('#dashboard-fo').outerHeight(true));
    },
    closeMissionControl: function()
    {
        Launchpad.hide();

        if (MissionControl.missionControlActived)
        {
            MissionControl.MissionControlRemove();


            $('#desktop').find('.isWindowContainer:visible').attr('reopen', 1).hide();




            return true;

            $('#desktop-bg').css({
                position: 'absolute',
                top: '',
                left: '',
                width: window.innerWidth,
                height: window.innerHeight
            }).show();

            $('#desktop').unbind('click.missioncontrol').css({
                top: '',
                left: '',
                width: window.innerWidth,
                height: window.innerHeight
            }).hide();

            //return;

            $('#missioncontrol-bg').hide();


            $('.MissioncontrolIcns, .MissioncontrolIcnsText, .MissionControlCache').remove();
            $('#spacesMiniContainer, .spacesCreateNewDesktopOverZone').hide();


            var i, $elements = $('.isWindowContainer');


            if ($elements.length > 0)
            {
                $elements.scale(1).css({
                    'box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18), 0 18px 50px rgba(0, 0, 0, 0.2)',
                    '-moz-box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18), 0 18px 50px rgba(0, 0, 0, 0.2)',
                    '-webkit-box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18), 0 18px 50x rgba(0, 0, 0, 0.2)'
                });
            }


            $('.isWindowContainer').each(function() {
                var self = this;
                var top = $(this).attr('oldTop');
                var left = $(this).attr('oldLeft');


                if ($(this).parent().hasClass('MissionControl_GroupWindow') && $('.tinymce-editor', $(this)).length == 0) {
                    var parentID = $(this).parent().attr('id');
                 //   console.log('id to unwrap without editors: ' + parentID);

                    $(this).unwrap();
                }


                $(this).scale(1).css({
                    position: 'absolute',
                    top: (top) + 'px',
                    left: (left) + 'px',
                    'margin-top': '0px',
                    'margin-left': '0px',
                    'box-shadow': '0 0 0 0px rgba(0, 0, 0, 0), 0 0px 0px rgba(0, 0, 0, 0)',
                    '-moz-box-shadow': '0 0 0 0px rgba(0, 0, 0, 0), 0 0px 0px rgba(0, 0, 0, 0)',
                    '-webkit-box-shadow': '0 0 0 0px rgba(0, 0, 0, 0), 0 0px 0px rgba(0, 0, 0, 0)'
                }).removeAttr('oldStyle').removeAttr('oldTop').removeAttr('oldLeft').removeAttr('oldWidth').removeAttr('oldHeight').removeAttr('oldZIndex');

                if ($(this).parent().hasClass('MissionControl_GroupWindow') && $('.tinymce-editor', $(this)).length > 0) {
                    if ($(this).data('DcmsWindow')) {
                        $(this).data('DcmsWindow').FocusWindow();
                    }

                    $(this).unwrap();
                }

                $('.tinymce-editor', $(this)).each(function() {

                 //   console.log('id editors: ' + $(this).attr('name'));
                    var e = this;
                    Doc.loadTinyMceConfig(
                            $(self), function() {
                        $(e).removeClass('loaded');
                        Doc.loadTinyMce($(self));
                     //   console.log('id editors: ' + $(e).attr('name') + ' loaded');
                    });
                });

            });


            MissionControl.MissionControlWindowInfo = [];
            MissionControl.missionControlActived = false;
            return true;
        }
    },
    close: function()
    {
        if (!this.dashboardOn)
        {
            return false;
        }


        $('#dashboard').hide();


        if ($('#desktop-side-panel').attr('reopen') == 1)
        {
            $('#desktop-container').css('left', $('#desktop-container').attr('basePos')).removeAttr('basePos');
            $('#desktop-side-panel').width($('#desktop-side-panel').attr('baseWidth')).removeAttr('baseWidth').show();
            $('#desktop-side-panel').removeAttr('baseWidth').removeAttr('reopen');
        }

        if ($('#Sidepanel').attr('reopen') == 1)
        {
            $('#Sidepanel').show();
            $('#Sidepanel').removeAttr('reopen');
        }

        if ($('#gui-console').attr('reopen') == 1)
        {
            $('#gui-console').removeAttr('reopen').show();
        }

        $('#desktop-bg-container').removeClass('dashboard-bg');

        $('#desktop-bg').css({
            position: 'absolute',
            top: '',
            left: '',
            width: window.innerWidth,
            height: window.innerHeight
        }).show();

        $('#desktop').css({
            position: 'absolute',
            'top': '',
            'left': '',
            width: window.innerWidth,
            height: window.innerHeight,
        }).show();


        $('#dock,#Taskbar,#DesktopIcons,.isWindowContainer[reopen]').fadeIn(300, function()
        {
            $('.isWindowContainer').removeAttr('reopen');

            if ($('#gui-console').attr('reopen'))
            {
                setTimeout(function() {
                    $('#gui-console').removeAttr('reopen').fadeIn(300);
                    $('#console', $('#Tasks-Core')).addClass('active');
                }, 10);
            }
        });


        $('#dashboard-fo').find('span').unbind();
        this.dashboardOn = false;
    },
    show: function()
    {
        if (this.dashboardOn)
        {
            return false;
        }
        var self = this;

        // close MissionControl if exists
        this.closeMissionControl();

        this.updateSize();



        $(window).resize(function() {
            $('#dashboard-main').css({
                height: $('#dashboard').height() - $('#dashboard-fo').outerHeight(true)
            });
        });

        if ($('#Sidepanel').is(':visible'))
        {
            $('#Sidepanel').attr('reopen', '1').hide();
        }

        if ($('#desktop-side-panel').is(':visible'))
        {
            $('#desktop-container').attr('basePos', $('#desktop-container').css('left'));
            $('#desktop-side-panel').attr('baseWidth', $('#desktop-side-panel').outerWidth());
            $('#desktop-container').css('left', '');
            $('#desktop-side-panel').attr('reopen', '1').hide();
        }

        if ($('#gui-console').is(':visible'))
        {
            $('#gui-console').attr('reopen', '1').hide();
            $('#console', $('#Tasks-Core')).removeClass('active');
        }
        
        
        

        $('#Taskbar,#dock,#DesktopIcons').hide();

        $('#desktop-bg').css({
            position: 'absolute',
            top: 0,
            left: 0,
            width: window.innerWidth,
            height: window.innerHeight
        });

        $('#desktop-bg-container').addClass('dashboard-bg');
        $('#desktop').hide();
        
        $('#desktop').find('.isWindowContainer:visible').hide().attr('reopen', '1');
        
        
        //Tools.spinner($('#dashboard'));
        $('#dashboard').css({
            top: 0,
            left: 0,
            width: window.innerWidth,
            height: window.innerHeight,
            position: 'absolute'
        }).show();



        $('#dashboard-fo').find('span').unbind();
        $('#dashboard-fo').find('span').click(function(e) {

            if ($(this).hasClass('close'))
            {
                self.close();
            }
            else if ($(this).hasClass('add'))
            {
                if (!$(this).hasClass('active')) {
                    $(this).addClass('active');
                    $('#widgets').hide();
                    $('#add-widgets').show();
                }
                else
                {
                    $(this).removeClass('active');
                    $('#add-widgets').hide();
                    $('#widgets').show();
                }
            }
            else if ($(this).hasClass('min') && !$('#dashboard-fo .add').hasClass('active'))
            {
                if (!$(this).hasClass('active')) {
                    $(this).addClass('active');
                    $('#add-widgets').hide();
                    $('#widgets').show();

                    $('#widgets').addClass('remove-widgets');
                }
                else
                {
                    $(this).removeClass('active');
                    $('#widgets').removeClass('remove-widgets');
                }
            }
        });

        this.dashboardOn = true;


        if (this.activeWidgetCache === null)
        {
            this.loadWidgets(true, function(data) {

                self.createWidgets(data);
                self.initDashboardWidgets();
            });
        }
        else
        {
            this.initDashboardWidgets();
        }
    },
    loadWidgets: function(activated, callback)
    {
        if (activated == true)
        {
            $.ajax({
                url: 'admin.php?adm=widgets',
                type: 'GET',
                dataType: 'json',
                timeout: 30000,
                data: {},
                global: false,
                success: function(data)
                {
                    if (Tools.responseIsOk(data))
                    {
                        callback(data);
                    }
                    else
                    {
                        jAlert(data.msg);
                    }
                }
            });
        }
        else
        {
            callback();
        }
    },
    refreshWidget: function(e)
    {
        if ($(e.target).parents('.widget-container:first').length == 1)
        {
            this.reloadWidget($(e.target).parents('.widget-container:first'));
        }
    },
    reloadWidget: function(widget)
    {
        var self = this, id = widget.attr('id').replace('widget-', '');
        var key = widget.attr('wgt');
        $.ajax({
            url: 'admin.php?adm=widgets&action=refresh&id=' + id + '&widget=' + key,
            type: 'GET',
            dataType: 'json',
            timeout: 10000,
            global: false,
            success: function(data)
            {
                if (Tools.responseIsOk(data))
                {
                    var content = $.parseHTML(data.output);
                    $('#widget-' + id).find('#widget-con').empty().append(content);
                    self.evalCode(data.output);
                }
            }
        });
    },
    createWidgets: function(data)
    {
        var self = this;

        if (data && data.widgets && data.widgets.length)
        {
            self.activeWidgetCache = data.widgets;

            for (var x = 0; x < data.widgets.length; ++x)
            {
                var dat = data.widgets[x];

                var widgetContainer = $('<div class="widget-container"/>');

                widgetContainer.attr('id', 'widget-' + dat.id).attr('wgt', dat.key);

                widgetContainer.css({
                    left: dat.left,
                    top: dat.top
                }).hide();



                var widgetContainerInner = $('<div class="widget-container-inner" data-direction="right"/>');

                widgetContainer
                        .append($('<div class="widget-drag-handler"/>'))
                        .append(widgetContainerInner);

                var content = $.parseHTML(dat.content_html);

                var con = $('<div class="widget-content"/>');
                con.append('<div class="widget-name"><span>' + dat.name + '</span></div>');
                con.append($('<div id="widget-con"></div>').append(content));
                con.append('<div class="refresh-btn"></div>');
                widgetContainerInner.append(con);

                var settings = false;

                if (dat.configurable)
                {
                    con.append('<div class="settings-btn">i</div>');

                    settings = $.parseHTML(dat.settings_html);

                    var set = $('<div class="widget-settings"/>');
                    set.append('<div class="widget-name"><span>' + dat.name + '</span></div>');
                    set.append($('<div id="widget-set"></div>').append(settings));
                    set.append('<div class="back-widget-btn"></div>');
                    widgetContainerInner.append(set);
                }

                widgetContainerInner.append('<div class="remove-widget-btn"></div>');
                widgetContainer.appendTo($('#widgets'));

                self.evalCode(dat.content_html);

                if (settings)
                {
                    self.evalCode(dat.settings_html);
                }
            }

            //$('#dashboard').removeClass('loader');
            $('#widgets .widget-container').fadeIn(400);
        }
    },
    evalCode: function(code)
    {
        // console.log([code]);
        $(code).filter('script').each(function() {
            //     console.log('script: ' + (this.text || this.textContent || this.innerHTML || ''));
            $.globalEval(this.text || this.textContent || this.innerHTML || '');
        });
    },
    initDashboardWidgets: function()
    {
        var self = this;


        if (this.dashboardOn)
        {
            //Tools.spinner($('#dashboard'), false);

            // Register all config forms
            $('#widgets').find('.widget-settings form').each(function() {


                if ($(this).find('button.widget-save-button').length == 1)
                {
                    var id = $(this).parents('.widget-container').attr('id').replace('widget-', '');
                    var key = $(this).parents('.widget-container').attr('wgt');


                    var $form = $(this);
                    $(this).find('button.widget-save-button').click(function() {
                        self.saveWidgetConfig($form, id, key);
                    });
                }


            });




            $('#widgets').filter(':ui-draggable').draggable('destroy');
            
            
            $('#widgets').find('.widget-container').draggable({
                //handle: '.widget-drag-handler',
                cancel: 'input[type=text],input[type=radio],input[type=checkbox],input[type=password],textarea',
                stack: '.widget-container',
                stop: function(e, ui)
                {

                    self.saveWidgetPosition($(this));

                }
            });

            $('#widgets').find('.widget-container-inner').unbind();
            $('#widgets').find('.widget-container-inner').on('mouseover', function() {
                $(this).find('.settings-btn,.refresh-btn').stop().animate({opacity: '1'}, 350);
            }).on('mouseleave', function() {
                $(this).find('.settings-btn,.refresh-btn').stop().animate({opacity: '0'}, 350);
            });

            // Click on settings button
            $('#widgets').find('.widget-container-inner .settings-btn').on('click', function() {
                $this = $(this).parents('.widget-container-inner');
                direction($this);
            });


            // reload widget
            $('#widgets').find('.widget-container-inner .refresh-btn').unbind();
            $('#widgets').find('.widget-container-inner .refresh-btn').on('click', function() {
                self.reloadWidget($(this).parents('.widget-container:first'));
            });

            // Click on close Settings
            $('#widgets').find('.widget-container-inner .back-widget-btn').unbind();
            $('#widgets').find('.widget-container-inner .back-widget-btn').on('click', function() {
                $this = $(this).parents('.widget-container-inner');
                direction($this);
            });



            function direction($this) {

                // fix for IE
                if (getInternetExplorerVersion() != -1) {
                    $this.find('div').fadeToggle();
                    return;
                }

                if ($this.data('direction') === 'right') {
                    $this.toggleClass('flipping-right');

                } else if ($this.data('direction') === 'left') {

                    $this.toggleClass('flipping-left');

                } else if ($this.data('direction') === 'top') {

                    $this.toggleClass('flipping-top');

                } else if ($this.data('direction') === 'bottom') {

                    $this.toggleClass('flipping-bottom');

                }

            }

            function getInternetExplorerVersion() {
                var rv = -1; // Return value assumes failure.
                if (navigator.appName == 'Microsoft Internet Explorer')
                {
                    var ua = navigator.userAgent;
                    var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
                    if (re.exec(ua) != null)
                        rv = parseFloat(RegExp.$1);
                }
                return rv;
            }
        }
    },
    saveWidgetConfig: function(form, id, key)
    {
        var self = this, ser = form.serialize();
        ser += '&id=' + id + '&name=' + key + '&adm=widgets&action=save';

        $.post('admin.php', ser, function(data) {
            if (Tools.responseIsOk(data))
            {
                // go back to Front
                form.parents('.widget-settings:first').find('.back-widget-btn').trigger('click');
                self.refreshWidget({target: form}); // emulate event target
            }
            else
            {

            }
        }, 'json');
    },
    saveWidgetPosition: function(widget)
    {
        var top = $(widget).position().top, left = $(widget).position().left;
        var widgetID = $(widget).attr('id').replace('widget-', '');


        $.ajax({
            url: 'admin.php',
            type: 'POST',
            dataType: 'json',
            timeout: 800,
            data: {
                adm: 'widgets',
                action: 'order',
                id: widgetID,
                top: top,
                left: left
            },
            success: function(data)
            {
                if (!Tools.responseIsOk(data))
                {
                    jAlert(data.msg);
                }
            }
        });
    }






};








var dashboardOn = false;

function destroyMissionControll()
{
    if (!missionControlActived)
        return false

    $('#desktop').unbind('click.missioncontrol').css({
        'top': '0',
        'left': '0',
        'width': '100%',
        'height': '100%'
    }).hide();

    $('#missioncontrol-bg').hide();


    $('body > .MissioncontrolIcns, body > .MissioncontrolIcnsText, body > .MissionControlCache').remove();
    $('#spacesMiniContainer, .spacesCreateNewDesktopOverZone').hide();


    var i, $elements = $('.isWindowContainer');


    if ($elements.length > 0)
    {
        $elements.scale(1).css({
            'box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18), 0 18px 50px rgba(0, 0, 0, 0.2)',
            '-moz-box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18), 0 18px 50px rgba(0, 0, 0, 0.2)',
            '-webkit-box-shadow': '0 0 0 1px rgba(0, 0, 0, 0.18), 0 18px 50x rgba(0, 0, 0, 0.2)'
        });
    }


    $('.isWindowContainer').each(function() {
        var self = this;
        var top = $(this).attr('oldTop');
        var left = $(this).attr('oldLeft');


        if ($(this).parent().hasClass('MissionControl_GroupWindow') && $('.tinymce-editor', $(this)).length == 0) {
            var parentID = $(this).parent().attr('id');
           // console.log('id to unwrap without editors: ' + parentID);

            $(this).unwrap();
        }


        $(this).css({
            position: 'absolute',
            top: (top) + 'px',
            left: (left) + 'px',
            'margin-top': '0px',
            'margin-left': '0px',
            'box-shadow': '0 0 0 0px rgba(0, 0, 0, 0), 0 0px 0px rgba(0, 0, 0, 0)',
            '-moz-box-shadow': '0 0 0 0px rgba(0, 0, 0, 0), 0 0px 0px rgba(0, 0, 0, 0)',
            '-webkit-box-shadow': '0 0 0 0px rgba(0, 0, 0, 0), 0 0px 0px rgba(0, 0, 0, 0)'
        }).removeAttr('oldStyle').removeAttr('oldTop').removeAttr('oldLeft').removeAttr('oldWidth').removeAttr('oldHeight').removeAttr('oldZIndex');





        if ($(this).parent().hasClass('MissionControl_GroupWindow') && $('.tinymce-editor', $(this)).length > 0) {
            if ($(this).data('DcmsWindow')) {
                $(this).data('DcmsWindow').FocusWindow();
            }

            $(this).unwrap();
        }




        $('.tinymce-editor', $(this)).each(function() {

         //   console.log('id editors: ' + $(this).attr('name'));
            var e = this;
            Doc.loadTinyMceConfig(
                    $(self), function() {
                $(e).removeClass('loaded');
                Doc.loadTinyMce($(self));
               // console.log('id editors: ' + $(e).attr('name') + ' loaded');
            });
        });

    });


    MissionControlWindowInfo = [];
    missionControlActived = false;
    return true;
}

function removeDaskboard()
{
    if (!dashboardOn)
        return false

    $('#dashboard').hide();
    $('#Taskbar,#DesktopIcons,#dock').show();

    if ($('#desktop-side-panel').attr('baseWidth') !== null)
    {
        $('#desktop-container').css('left', $('#desktop-container').attr('basePos')).removeAttr('basePos');
        ;
        $('#desktop-side-panel').width($('#desktop-side-panel').attr('baseWidth')).removeAttr('baseWidth').show();
    }

    if ($('#Sidepanel').attr('reopen') == 1)
    {
        $('#Sidepanel').removeAttr('reopen').show();
    }

    if ($('#gui-console').attr('reopen') == 1)
    {
        $('#gui-console').removeAttr('reopen').show();
    }

    $('#desktop-bg-container').removeClass('dashboard-bg');
    $('#desktop-bg').css({
        position: 'absolute',
        top: '0px',
        left: '0px',
        width: window.innerWidth,
        height: window.innerHeight,
        'box-shadow': '0 0 0 rgba(0,0,0,0)',
        '-moz-box-shadow': '0 0 0 rgba(0,0,0,0)',
        '-webkit-box-shadow': '0 0 0 rgba(0,0,0,0)'
    }).show();

    $('#desktop').css({
        position: 'absolute',
        'top': '0',
        'left': '0',
        'width': '100%',
        'height': '100%'
    }).show();


    $('#dock,#Taskbar,#DesktopIcons').fadeIn(300, function() {
        if ($('#gui-console').attr('reopen'))
        {
            setTimeout(function() {
                $('#gui-console').removeAttr('reopen').fadeIn(300);
                $('#console', $('#Tasks-Core')).addClass('active');
            }, 10);
        }
    });


    $('#dashboard-fo').find('span').unbind();


    dashboardOn = false;
}


function DashboardStart()
{
    if (dashboardOn)
        return true;

    destroyMissionControll();

    $('#desktop-bg').css({
        position: 'absolute',
        top: '0px',
        left: '0px',
        width: window.innerWidth,
        height: window.innerHeight
    });

    $('#desktop-bg-container').addClass('dashboard-bg');

    $('#dashboard').css({
        'top': '0',
        'left': '0',
        'width': '100%',
        'height': '100%',
        position: 'absolute',
        zIndex: 10
    }).show();




    $('#dashboard-main').css({
        height: $('#dashboard').height() - $('#dashboard-fo').outerHeight(true)
    });

    $(window).resize(function() {
        $('#dashboard-main').css({
            height: $('#dashboard').height() - $('#dashboard-fo').outerHeight(true)
        });
    });



    $('#dashboard-fo').find('span').click(function(e) {

        if ($(this).hasClass('close'))
        {
            removeDaskboard();
        }
        else if ($(this).hasClass('add'))
        {
            if (!$(this).hasClass('active')) {
                $(this).addClass('active');
                $('#widgets').hide();
                $('#add-widgets').show();
            }
            else
            {
                $(this).removeClass('active');
                $('#add-widgets').hide();
                $('#widgets').show();
            }
        }
        else if ($(this).hasClass('min') && !$('#dashboard-fo .add').hasClass('active'))
        {
            if (!$(this).hasClass('active')) {
                $(this).addClass('active');
                $('#add-widgets').hide();
                $('#widgets').show();

                $('#widgets').addClass('remove-widgets');
            }
            else
            {
                $(this).removeClass('active');
                $('#widgets').removeClass('remove-widgets');
            }
        }
    });

    dashboardOn = true;
    initDashboardWidgets();
}



function initDashboardWidgets()
{
    if (dashboardOn)
    {

        $('#widgets').filter(':ui-draggable').draggable('destroy');
        $('#widgets').find('.widget-container').draggable({
            handle: '.widget-drag-handler'
        });

        $('#widgets').find('.widget-container-inner .settings-btn').unbind();
        $('#widgets').find('.widget-container-inner .settings-btn').on('mouseover', function() {
            $(this).animate({opacity: '1'}, 350);
        }).on('mouseleave', function() {
            $(this).animate({opacity: '0'}, 350);
        });

        // Click on settings button
        $('#widgets').find('.widget-container-inner .settings-btn').on('click', function() {
            $this = $(this).parents('.widget-container-inner');
            direction($this);
        });

        // Click on close Settings
        $('#widgets').find('.widget-container-inner .back-widget-btn').unbind();
        $('#widgets').find('.widget-container-inner .back-widget-btn').on('click', function() {
            $this = $(this).parents('.widget-container-inner');
            direction($this);
        });


        function direction($this) {

            // fix for IE
            if (getInternetExplorerVersion() != -1) {
                $this.find('div').fadeToggle();
                return;
            }

            if ($this.data('direction') === 'right') {
                $this.toggleClass('flipping-right');

            } else if ($this.data('direction') === 'left') {

                $this.toggleClass('flipping-left');

            } else if ($this.data('direction') === 'top') {

                $this.toggleClass('flipping-top');

            } else if ($this.data('direction') === 'bottom') {

                $this.toggleClass('flipping-bottom');

            }

        }

        function getInternetExplorerVersion() {
            var rv = -1; // Return value assumes failure.
            if (navigator.appName == 'Microsoft Internet Explorer')
            {
                var ua = navigator.userAgent;
                var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
                if (re.exec(ua) != null)
                    rv = parseFloat(RegExp.$1);
            }
            return rv;
        }
    }
}





