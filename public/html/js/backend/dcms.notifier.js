var Notifier = {
    _item_count: 0,
    _winitem_count: 0,
    width: 650,
    fadeInTime: 300,
    displayTime: 6000,
    icon: 'info.png',
    isConsoleErrorOutput: false,
    timers: [],
    // if arg "inwindowID" is giving the hide the window notifier
    // or hide the basic notifier
    hide: function(inwindowID)
    {
        if (typeof inwindowID !== 'undefined')
        {
            $('.window-notifier-wrapper', $('#' + inwindowID)).hide();
        }
        else
        {
            $('#notifier-wrapper').hide();
            $('#notifier-wrapper .inner').empty();
        }
    },
    displayInWindow: function(type, message, inwindowID)
    {
        var icon = this.setIcon(type);


        if (!$('.window-notifier-wrapper', $('#' + inwindowID)).length)
        {
            $('#' + inwindowID).append($('<div class="window-notifier-wrapper"><div class="inner"></div></div>'));
            $('.window-notifier-wrapper', $('#' + inwindowID)).hide();
        }

        var self = this, item = $('<div class="item">' + message + '</div>').hide();

        $('#' + inwindowID).find('.window-notifier-wrapper').show();

        $('.window-notifier-wrapper .inner', $('#' + inwindowID)).empty().append(item);
        $('.window-notifier-wrapper .item', $('#' + inwindowID)).fadeIn(400);

        return $('.window-notifier-wrapper', $('#' + inwindowID));
    },
    info: function(message, nosound)
    {
        this.display('info', message, nosound);

    },
    warn: function(message, nosound)
    {
        if (this.isConsoleErrorOutput)
            return;

        this.display('warn', message, nosound);
    },
    error: function(message, nosound)
    {
        if (this.isConsoleErrorOutput)
            return;

        this.display('error', message, nosound);
    },
    send: function(message, nosound)
    {
        this.display('send', message, nosound);
    },
    // returns the window notifier wrapper if arg "inwindowID" is giving
    display: function(type, message, nosound)
    {
        if (this.isConsoleErrorOutput && (type == 'error' || type == 'warn'))
            return;



        clearTimeout(this.timers['itm-' + this._item_count]);

        if (typeof type != 'string')
        {
            type = 'info';
        }

        var icon = this.setIcon(type);

        if (!$('#notifier-wrapper').length)
        {
            $('#fullscreenContainer').append($('<div id="notifier-wrapper"><div class="inner"></div></div>'));

        }

        $('#notifier-wrapper').css({
            position: 'absolute',
            display: 'inline-block',
            zIndex: 99999,
            right: 10,
            height: 'auto',
            maxHeight: $(window).height() - $('#header').outerHeight() - 20,
            top: $('#Taskbar').outerHeight() + 5,
            width: 'auto'
        }).hide();



        var self = this, item = $('<div class="item" id="itm-' + this._item_count + '"></div>');
        var itemInner = $('<div class="item-inner"><span>' + message + '</span><em></em></div>')
        itemInner.appendTo(item);

        itemInner.find('em').click(function() {
            var itm = $(this).parents('div.item:first');
            clearTimeout(self.timers[itm.attr('id')]);
            itm.animate({
                opacity: 0,
                height: 0
            }, {
                duration: 300,
                queue: false,
                complete: function() {
                    $(this).hide().remove();
                    self._item_count--;

                    if (self._item_count <= 0)
                    {
                        self._item_count = 0;
                        $('#notifier-wrapper').hide();
                    }
                }});
        });



        if (icon)
        {
            itemInner.prepend($('<img src="' + Config.get('backendImagePath') + icon + '"/>'));
        }


        $('#notifier-wrapper .inner').append(item);
        $('#notifier-wrapper').show();

        item.css({
            position: 'absolute',
            zIndex: 9999,
            height: $('#itm-' + this._item_count).outerHeight(true)
        });


        this.timers['itm-' + this._item_count] = setTimeout(function() {

            item.animate({
                opacity: 0,
                height: 0
            }, {
                duration: 300,
                queue: false,
                complete: function() {
                    $(this).hide().remove();
                    self._item_count--;

                    if (self._item_count <= 0)
                    {
                        self._item_count = 0;
                        $('#notifier-wrapper').hide();
                    }
                }});

        }, self.displayTime);

        item.fadeIn(300, function() {
            if ( (type == 'info' || type == '') && !nosound ) {
                Tools.html5Audio( 'html/audio/smallbox' );
            }
        });
        this._item_count++;



        /*
         
         $(item).delay(5000).fadeOut(300, function(){             
         $(this).remove();
         
         if ($('#notifier-wrapper .inner').length === 0 )
         {
         $('#notifier-wrapper').hide();
         }
         });
         */
    },
    setIcon: function(type)
    {
        if (typeof type != 'string')
        {
            type = 'info';
        }
        type = type.toLowerCase();

        switch (type)
        {
            case 'error':
                return 'form-error.png';
                break;
            case 'info':
                return 'form-info.png';
                break;
            case 'warn':
                return 'form-not-ok.png';
                break;
            case 'send':
                return 'form-saving.png';
                break;
            default:
                return 'form-ok.png';
                break;
        }
    }


};