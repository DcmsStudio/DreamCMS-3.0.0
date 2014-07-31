/**
 * DreamCMS Core script
 * 
 */

var DCMS = {
    
    /**
	* Returns get parameters.
	*
	* If the desired param does not exist, null will be returned
	*
	* @example value = DCMS.getURLParam("paramName");
	*/
    getURLParam: function(strParamName){
        var strReturn = "";
        var strHref = window.location.href;
        var bFound=false;

        var cmpstring = strParamName + "=";
        var cmplen = cmpstring.length;

        if ( strHref.indexOf("?") > -1 ){
            var strQueryString = strHref.substr(strHref.indexOf("?")+1);
            var aQueryString = strQueryString.split("&");
            for ( var iParam = 0; iParam < aQueryString.length; iParam++ ){
                if (aQueryString[iParam].substr(0,cmplen)==cmpstring){
                    var aParam = aQueryString[iParam].split("=");
                    strReturn = aParam[1];
                    bFound=true;
                    break;
                }

            }
        }
        if (bFound==false) return null;
        return strReturn;
    },
    
    hasPagemask: function()
    {
        if ($('#popup_overlay').length)
        {
            return true;
        }
        
        return false;
    },
    
    maskPage: function(label)
    {        
        if (this.hasPagemask() && (typeof label == 'undefined' || label == false) )
        {
            $("#popup_overlay,#popup_overlay_msg,#popup_overlay_remove").remove();
            return;
        }
        else if (this.hasPagemask() && typeof label == 'string' )
        {
            if (!$('#popup_overlay_msg').length)
            {
                $('div:first' , $('#popup_overlay_msg')).empty().append(label);
            }
            else
            {
                var maskMsgDiv = $('<div class="loadmask-msg" id="popup_overlay_msg" style="display:none;"></div>');
                maskMsgDiv.append('<div>' + label + '</div>');
                $('body').append(maskMsgDiv);
            
                maskMsgDiv.css({
                    zIndex: 999999,
                    width: maskMsgDiv.width(),
                    height: maskMsgDiv.height(),
                    position: 'fixed',
                    left: '50%',
                    top : '40%',
                    marginLeft: 0-Math.floor(maskMsgDiv.outerWidth()/2)
                });
                maskMsgDiv.show();
            }
            
            return;
        }
        
        
        
        $("body").append('<div id="popup_overlay"></div>');
        $("#popup_overlay").css({
            position: 'fixed',
            zIndex: 999998,
            top: '0',
            left: '0',
            width: '100%',
            height: '100%',
            background: '#555555',
            opacity: .4
        });
        
        
        if(typeof label == "string") {
            var maskMsgDiv = $('<div class="loadmask-msg" id="popup_overlay_msg" style="display:none;"></div>');
            maskMsgDiv.append('<div>' + label + '</div>');
            $('body').append(maskMsgDiv);
            
            maskMsgDiv.css({
                zIndex: 999999,
                width: maskMsgDiv.width(),
                height: maskMsgDiv.height(),
                position: 'fixed',
                left: '50%',
                top : '50%',
                marginLeft: 0-Math.floor(maskMsgDiv.outerWidth()/2),
                marginTop: 0-Math.floor(maskMsgDiv.outerHeight()/2)
            });
            maskMsgDiv.show();
        }        
    },
    

    mask: function(el, msg)
    {

        if($(el).hasClass('masked') )
        {
            
            this.unmask(el);
        }

        if($(el).css("position") == "static") {
            $(el).addClass("masked-relative");
        }

        $(el).addClass("masked");
        
        var maskDiv = $('<div class="loadmask"></div>');
        var maskHeight = $(el).height() - parseInt($(el).css("padding-top"));
        var maskWidth = $(el).width() + parseInt($(el).css("padding-left")) + parseInt($(el).css("padding-right"));
        maskDiv.height( maskHeight );
        maskDiv.width( maskWidth );
        
        $(el).append(maskDiv);
        
        
        if(typeof msg == "string") {
            var maskMsgDiv = $('<div class="loadmask-msg" style="display:none;"></div>');
            maskMsgDiv.append('<div>' + msg + '</div>');
            

            //calculate center position
            maskMsgDiv.css("top",  Math.round($(el).height() / 2 - (maskHeight - parseInt(maskMsgDiv.css("padding-top")) - parseInt(maskMsgDiv.css("padding-bottom"))) / 2)+"px" );
            maskMsgDiv.css("left", Math.round($(el).width() / 2 - (maskWidth - parseInt(maskMsgDiv.css("padding-left")) - parseInt(maskMsgDiv.css("padding-right"))) / 2)+"px");
            
            $(el).append(maskMsgDiv);
            maskMsgDiv.show();
        }
    },
    
    unmask: function(el)
    {
        $(el).find(".loadmask-msg,.loadmask").remove();
        $(el).removeClass("masked").removeClass("masked-relative");
        
    }
    
    
};