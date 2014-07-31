

function submitAction(FormIDName)
{
    if ( FormIDName != '' )
    {
        _SubmitError = false;
        return;
        
        
        
        var forms = $(document).find('form');
        forms.each(function() {
            if ($(this).attr('id') == FormIDName)
            {
                if ($().validationEngine)
                {
                    //alert('submit: '+ $.validationEngine.submitValidation(this, {promptPosition : "topLeft"}));
                    if ( !$(this).validationEngine('validateform', {
                        isOverflown: true, 
                        overflownDIV: "#content"
                    }) )

                    {
                        _SubmitError = true;
                        return ;
                    }
                }
            }
        });
			
			
        return;
			
			
        var FormValidator = $('body').data(FormIDName);		
        if ( typeof FormValidator != 'undefined' )
        {
            alert(typeof FormValidator);
            FormValidator.validate();			
            if(!FormValidator.isValid()) {
                _SubmitError = true;
            }
        } 
    }
}

// old validators
var allowedVlidators = 'required,min,max,url,alphanum,alpha,email,digits,number';

function loadValidationForms()
{
    var valids = allowedVlidators.split(',');
    var forms = $(document).find('form:not(.validationAdded)');
    forms.each(function() {
        var self = this;
        var addValidation = false;
			
			
        if ( typeof $(self).attr('id') == 'undefined' && typeof $(self).attr('name') != 'undefined' )
        {
            $(self).attr('id', $(self).attr('name') );
        }
			
			
			
        $(this).find('input,select,textarea').each(function() {
            if ( $(this).attr('type') != 'hidden' )
            {
                var cls = $(this).attr('class');
                if (typeof cls != 'undefined' ) {
                    cls.replace(/^req_/g, '');
                    var validators = [];
                    for (var i=0;i<valids.length;i++)
                    {
                        if ( cls.indexOf( valids[i] ) != -1 )
                        {
                            var c = valids[i];
                            if (c != 'required')
                            {
                                c = 'custom['+ c + ']';
                            }
                            validators.push(c);							
                        }
                    }

                    if (validators.length)
                    {
                        addValidation = true;
                        var addId = $(this).attr('id');
                        $(this).addClass('validate['+ validators.join(',') +']');
                        if ( !addId  )
                        {
                            $(this).attr('id', 'valiate_'+ $(this).attr('name') );
                        }
                        var cont = $('<div>').addClass('inputContainer');
                        cont.insertBefore($(this));						
                        $(this).appendTo( cont);
                    }
                }
            }
				
            if (addValidation){
				
                
                $(self).validationEngine('attach', 
                {
                    isOverflown: true, 
                    scroll: false,
                    overflownDIV: '#content'
                });
                
                $(self).addClass('validationAdded');
            }
			
        });
			

    });
}


Loader.loadCss('html/js/jquery/formValidator/css/validationEngine.jquery.css');

Loader.require(
    'html/js/jquery/formValidator/jquery.validationEngine-de.js',
    'html/js/jquery/formValidator/jquery.validationEngine.js', 
    function() {
        loadValidationForms();
    });
