var Template = {
    
    template: null,
    data: null,
    
    varRegex: new RegExp('\{([^\{\}]*)\}' , 'g'),
    
    setTemplate: function(template)
    {
        this.template = new String(template);
        ;
        return this;
    },
    
    
    reset: function()
    {
        this.template = this.data = null;
        return this;
    },
    
    
    process: function(data)
    {
        if (this.template===null)
        {
            return false;
        }
        
        
        
        this.data = {};
        this.data = data;
        var self = this;
        // this.template = this.template.replace(/[\r\n\t]*/g, '');
        
        return this.template.replace(this.varRegex, function(match, s){ 
            return self.prepare(s); 
        });
    },
    
    prepare: function( variableName )
    {
        var _dataType = typeof this.data[ variableName ];
        if ( _dataType != 'undefined' && _dataType != null )
        {
            _dataType = _dataType.toString();
            
            if (_dataType === 'string')
            {
                return this.data[ variableName ];
            }
            else if (_dataType === 'number')
            {
                return parseInt( this.data[ variableName ] );
            }
            else if ( _dataType === 'boolean')
            {
                return new Boolean( this.data[ variableName ] );
            }
            else if ( _dataType === 'object')
            {
                return this.data[ variableName ];
            }
        }

        return '';
    }
    
    
};
