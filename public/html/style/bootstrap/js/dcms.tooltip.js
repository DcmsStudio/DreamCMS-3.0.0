
var ToolTip = {
    toolTipTemplate: '<div id="{id}" class=" x-panel notify-form-panel">'
            + '<div class="x-panel-header"><span class="x-panel-header-text">{title}</span></div>'
            + '<div class="notify-tray-header-line"></div>'
            + '<div class="x-panel-body">{message}</div>'
            + '<div class="notify-tray-arrow" style="top: -16px;"></div>'
            + '</div>',
    toolTip: {},
    buildTips: function () {
        var t, tips = $('body').find('.tip');
        var tipIndex = 0;
        var self = this;

        $('body').unbind('click.tiphide');

        if (!$('#tip2').length)
        {
            var Balloon = Template.setTemplate(this.toolTipTemplate).process({
                id: 'tip2',
                message: '',
                title: ''
            });

            Template.reset();
            $(Balloon).remove('.x-panel-header,.notify-tray-header-line');
            $(Balloon).appendTo($('body'));
            $('#tip2').hide();
        }

        tips.each(function ()
        {
            var alt = $(this).attr('alt');
            
            if (alt && typeof alt === 'string' && alt != '') {
                $(this).hover(function () {
                    var xself = this;
                    clearTimeout(t);

                    t = setTimeout(function () {
                        $('#tip2').hide();
                        var alt = $(xself).attr('alt');
                        $('.x-panel-body', $('#tip2')).append(alt);

                        var iconPos = $(xself).offset();
                        var leftpos = iconPos.left;

                        if (leftpos < 80)
                        {
                            leftpos = iconPos.left;
                        }
                        $('#tip2').css({
                            zIndex: 99999,
                            left: (leftpos) + 'px',
                            top: (iconPos.top + 23) + 'px',
                            'position': 'absolute',
                            width: 'auto'
                        }).fadeIn(200, function () {
                            $(this).show();
                        });

                    }, 500);
                }, function () {
                    clearTimeout(t);
                    $('#tip2').hide();
                });
            }
        });



        if (tipIndex)
        {
            $('body').unbind('click.tiphide').bind('click.tiphide', function (ev)
            {
                if ($('#tip2:visible').length && !$(ev.target).hasClass('infoicon') && $(ev.target).attr('id') != 'tip2')
                {
                    $('#tip2').fadeOut(300);
                }
                //
            });
        }

    },
    rebuildTooltips: function ()
    {
        var t, tips = $('body').find('.infoicon');
        var tipIndex = 0;
        var self = this;

        $('body').unbind('click.tooltiphide');


        if (!$('#tip').length)
        {
            var Balloon = Template.setTemplate(this.toolTipTemplate).process({
                id: 'tip',
                message: '',
                title: ''
            });

            Template.reset();

            $(Balloon).appendTo($('body'));
            $('#tip').hide();
        }

        tips.each(function ()
        {
            var alt = $(this).attr('alt');

            $(this).attr('id', 'tip' + tipIndex + '_' + $(this).attr('alt').replace('|', '_'));

            $(this).unbind('click.tooltip').bind('click.tooltip', function (e)
            {
                clearTimeout(t);
                $('#tip').hide();
                self.toolTip.alt = alt;
                self.toolTip.obj = $(this);
                self.showTip();

                var iconPos = $(this).offset();
                var leftpos = iconPos.left;

                if (leftpos < 80)
                {
                    leftpos = iconPos.left;
                }
                $('#tip').css(
                        {
                            zIndex: 99999,
                            left: (leftpos) + 'px',
                            top: (iconPos.top + 23) + 'px',
                            'position': 'absolute',
                            width: 300
                        }).fadeIn(200, function ()
                {
                    $(this).show();
                });
            }).hover(function () {
                var xself = this;
                clearTimeout(t);

                t = setTimeout(function () {
                    $('#tip').hide();
                    self.toolTip.alt = $(xself).attr('alt');
                    self.toolTip.obj = $(xself);
                    self.showTip();

                    var iconPos = $(xself).offset();
                    var leftpos = iconPos.left;

                    if (leftpos < 80)
                    {
                        leftpos = iconPos.left;
                    }
                    $('#tip').css({
                        zIndex: 99999,
                        left: (leftpos) + 'px',
                        top: (iconPos.top + 23) + 'px',
                        'position': 'absolute',
                        width: 300
                    }).fadeIn(200, function () {
                        $(this).show();
                    });

                }, 500);
            }, function () {
                clearTimeout(t);
                $('#tip').hide();
            });



            tipIndex++;
        });



        if (tipIndex)
        {
            $('body').unbind('click.tooltiphide').bind('click.tooltiphide', function (ev)
            {
                if ($('#tip:visible').length && !$(ev.target).hasClass('infoicon') && $(ev.target).attr('id') != 'tip')
                {
                    $('#tip').fadeOut(300);
                }
                //
            });
        }
    },
    showTip: function ()
    {
        if (this.toolTip.alt == undefined || this.toolTip.alt == '')
        {
            return;
        }

        var isAlt = this.toolTip.alt;
        isAlt = isAlt.replace('|', '_');

        $('.x-panel-body', $('#tip')).empty();
        $('.x-panel-header-text', $('#tip')).empty();

        this.toolTip.obj.attr('src', Config.get('loadingImgSmall'));

        var post = 'adm=tooltip&ajax=1&tip=' + Utf8.encode(this.toolTip.alt);
        var url = 'admin.php?adm=tooltip&ajax=1&tip=' + Utf8.encode(this.toolTip.alt);
        var self = this;

        $.get(url, {}, function (data)
        {
            $('.x-panel-header-text', $('#tip')).append(data.title);
            $('.x-panel-body', $('#tip')).append(data.content);
            self.toolTip.obj.attr('src', Config.get('backendImagePath') + 'info.png')
        }, 'json');
    }
};