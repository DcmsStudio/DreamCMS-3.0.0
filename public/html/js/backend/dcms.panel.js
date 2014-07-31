var SidePanel = (function() {
    return {
        inited: false,
        init: function()
        {
            if (this.inited)
            {
                return;
            }

            this.inited = true;
            this.hide();
        },
        hide: function()
        {
            $('#Sidepanel').hide();
            return this;
        },
        show: function()
        {
            $('#Sidepanel').show();
            return this;
        },
        empty: function()
        {
            $('#documentsettings-content').empty();
            return this;
        },
        isVisible: function()
        {
            return $('#Sidepanel').is(':visible');
        },
        open: function(callback)
        {
            if ( parseInt( $('#Sidepanel').css('left'), 0) != 0)
            {
                $('#Sidepanel').show().animate({
                    left: '0'
                }, 200, function() {

                    if (typeof callback == 'function')
                    {
                        callback();
                    }
                });
            }
            else
            {
                if (typeof callback == 'function')
                {
                    callback();
                }
            }



        },
        close: function(callback)
        {
            if ( parseInt( $('#Sidepanel').css('left'), 0) == 0)
            {
                $('#Sidepanel').show().animate({
                    left: 16 - ($('#Sidepanel').width())
                }, 200, function() {
                    if (typeof callback == 'function')
                    {
                        callback();
                    }
                });
            }
            else
            {
                if (typeof callback == 'function')
                {
                    callback();
                }
            }
        }



    };
})(window);