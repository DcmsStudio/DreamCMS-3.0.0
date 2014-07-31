(function(jQuery) {
    $.fn.dcmsSerialize = function()
    {
        var rCRLF = /\r?\n/g;
        
        function serialize(form)
        {
            return jQuery.param(form.find('input,select,textarea').map(function(i, elem) {
                if (elem.name && !jQuery(this).is(":disabled"))
                {
                    var type = this.type;
                    var val = null;
                    if (type == 'radio' || type == 'checkbox')
                    {
                        val = this.checked ? jQuery(this).val() : null;
                    }
                    else
                    {
                        val = jQuery(this).val();
                    }

                    if (val != null)
                    {
                        var subcontainer = jQuery(this).parents('.form-sub-container:first');
                        if (subcontainer.length)
                        {
                            // add only post fields if the container is visible :)
                            if (subcontainer.is(':visible'))
                            {
                                return jQuery.isArray(val) ?
                                        jQuery.map(val, function(_val) {
                                    return {name: elem.name, value: _val.replace(rCRLF, "\r\n")};
                                }) : {name: elem.name, value: val.replace(rCRLF, "\r\n")};
                            }
                        }
                        else
                        {
                            return jQuery.isArray(val) ?
                                    jQuery.map(val, function(_val) {
                                return {name: elem.name, value: _val.replace(rCRLF, "\r\n")};
                            }) : {name: elem.name, value: val.replace(rCRLF, "\r\n")};
                        }
                    }

                    return null;
                }
            }));
        }


        return serialize($(this));
    }

})(jQuery);