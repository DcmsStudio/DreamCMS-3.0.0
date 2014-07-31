var is_dirty = false;
var tinyEls = [];
var tinymceConfig;
var menuObj;
var toggleon = 0;
var org_classname = '';
var exiturl = '';
var exitpopup = false;
var fancy_pagemask = true;
var forcemask = true;
var ajaxOpen = false;
var menu_clicked = false;
var metaadvancedTimer = 0;
var xeditor = null;
if (typeof requestString == 'undefined')
{
    var requestString = '';
}
var gridcols;
var listViewTbl;
var _listViewTbl = '';
var ajax_loading_imgurl = 'html/style/default/img/loading.gif';
var ajax_loading_content = '<img src="' + ajax_loading_imgurl + '" class="float:left" /> ' + cmslang.loading;
var isSeemodePopup = false;
var pagestree = null;
var toolbarTabsCls = null;


/**
 *
classes:

var MyCompany = MyCompany || {};
MyCompany.MyApplication = {};
MyCompany.MyApplication.Model = {

    test: function(){
        alert ....
    }


};

or something like the following:

var MyCompany = MyCompany || {
    MyApplication: {
        Model: {

            test: function(){
                alert ....
            }

        }
    }        
};



useing:

var model = namespace('MyCompany.MyApplication.Model');


 *
 */
function namespace(namespaceString) {
    var parts = namespaceString.split('.'),
    parent = window,
    currentPart = '';    
        
    for(var i = 0, length = parts.length; i < length; i++) {
        currentPart = parts[i];
        parent[currentPart] = parent[currentPart] || {};
        parent = parent[currentPart];
    }
    
    return parent;
}






function onCalSelect(calendar, date) {
    var input_field = document.getElementById("date");
    input_field.value = date;
    if (calendar.dateClicked) {
        calendar.callCloseHandler(); // this calls "onClose" (see above)
    }
}

function onCalClose(calendar) {
    // calendar.hide();
    calendar.destroy();
}




function dbg()
{
    if (typeof console != 'undefined' && typeof console.info != 'undefined')
    {
        for (var i = 0; i < arguments.length; i++)
        {
            console.info(arguments[i]);
        }
    }
}

function resetAjaxSetup()
{
    $.ajaxSetup(
    {
        dataType: "json",
        cache: false,
        async: false,
        timeout: 36000
    });
}

function responseIsOk(data)
{
    if (data == null || (typeof data == 'object' && typeof data.success == 'boolean' && data.success == false))
    {
        if (data != null && typeof data == 'object' && data.sessionerror != 'undefined' && typeof data.sessionerror != null )
        {
            if ( data.sessionerror == true)
            {
                //document.location.href = cmsurl + 'admin.php';
				
                return false;
            }
        }
        
        
        if (data != null && typeof data == 'object' && data._log != 'undefined' && typeof data._log != null )
        {
            Debug.warn(data._log);
        }
        
        
        return false;
    }
    else
    {
        return true;
    }
}




var Base = {
    host: null,
    hostPath: null,
    StartTimer: 0,
    execCount: 0,
    loaderObj: null,
    _GUI: null,
    
    
    // call in loader.js
    // this is the absolute first call
    init: function ( _loaderObj )
    {
        this.loaderObj = _loaderObj;
        
        this.StartTimer = new Date().getTime();
        this.data = {
            done: {},
            load_events: [],
            load_timer: null,
            load_done: false,
            load_init: null
        };
        
        this.head = document.getElementsByTagName('head')[0] || document.documentElement;
        Tools.browserDetect();

        var sHref = location.href.split("#")[0].split("?")[0];
        this.host = location.hostname && sHref.replace(/(\/\/[^\/]*)\/.*$/, "$1");
        this.hostPath = sHref.replace(/\/[^\/]*$/, "") + "/";
        
        
        $(document).ajaxError(function (e, xhr, settings, exception)
        {
            alert('Error Message: ' + exception + '\n\nerror in: ' + settings.url + ' \n' + 'error:\n' + xhr.responseText);
        });
        
    },
    
    setGui: function(_guiObj)
    {
        this._GUI = _guiObj;
    //  alert('GUI > '+ _guiObj);
    },
    loadGui: function()
    {
        //  alert('GUI > '+ GUI);
        this._GUI.init();        
    },
    getInitTimer: function()
    {        
        return this.StartTimer - new Date().getTime();
    },
    
    uniqueHtmlIds: 0,
    setUniqueHtmlId: function (oHtml)
    {
        var id;
        oHtml.setAttribute("id", id = "q" + this.uniqueHtmlIds++);
        return id;
    },
    getUniqueId: function ()
    {
        return this.uniqueHtmlIds++;
    },  


    /**
     * returns the Loading image as &lt;img/&gt;
     */
    getLoading: function()
    {
        
        
    },


    getVersion: function(useFromGui)
    {
        $.get('admin.php?action=checkversion', {}, function(data) {
            if(responseIsOk(data))
            {
                if (useFromGui) {
                    $('#system-notifier').html(data.output);
                }
                else {
                    return data.output;
                }
            }
            else
            {
                alert(data.msg);
            } 
        });
    },
    
    
    
    
    getWinHeight: function()
    {
        if (Tools.isOpera)
        {
            return document.documentElement["clientHeight"];
        }
        else if (Tools.isSafari)
        {
            return document.documentElement["clientHeight"];
        }
        else
        {
            return $(window).height();
        }   
    },
    getWinWidth: function()
    {
        if (Tools.isOpera)
        {
            return document.documentElement["clientWidth"];
        }
        else if (Tools.isSafari)
        {
            return document.documentElement["clientWidth"];
        }
        else
        {
            return $(window).width();
        }
    },

    help: function()
    {
        var act = toolbarTabsCls.getHelpKeys();
        
        if ( act === null)
        {
            return;
        }

        $("#popup_overlay_remove").remove();
	
	
        $.pagemask.show();
        $.get('admin.php?adm=help&get=' + act, {}, function(data) {
            $.pagemask.hide();
            if(responseIsOk(data)) {
			
                createPopup(data.content,
                {
                    title: 'DreamCMS Hilfe...',
                    width: (Math.floor($(window).width()) - 160),
                    height: (Math.floor($(window).height()) - 100)
                });
            }
            else
            {
                alert(data.msg);
            }
        }, "json");
    }
};