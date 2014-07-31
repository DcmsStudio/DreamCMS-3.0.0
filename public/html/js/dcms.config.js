var Config = {
    
    cfg: {
        
        railColor : '#555',
        railOpacity : '0.2',
        railClass : 'GuiScrollRail',
        barClass : 'GuiScrollBar',
        wrapperClass : 'GuiScrollDiv',
        size : '7px',
        color: '#000',
        position : 'right',
        distance : '2px'
        
    },
    jsDebug: true,
    
    init: function( options ){
        
        if (typeof options == 'object' )
        {
            var max = options.lenght;

            for (var x in options)
            {
                this.cfg[x] = options[x];
            }
            Debug.info('Config is loaded');
        }

    },
    
    set: function(keyname, value)
    {
        
        this.cfg[keyname] = value;
        
    },
    
    get: function( keyname, _default )
    {
        if (typeof this.cfg[keyname] != 'undefined')
        {
            return this.cfg[keyname];
        }
        else
        {
            if (typeof _default != 'undefined')
            {
                return _default;                        
            }
        }
    }
    
    
};