/**
 DreamCMS (c)2011
 DreamCMS ClickLogger v1.0
 */

var urlLogger = document.location.href;
var timeLoad = new Date();
var screenSize = screen.width + ";" + screen.height;
var clickTimeout, yT = [];

if (jQuery)
{
    $(document).ready(function () {
        analyseClicks = true;
        $(document).unbind('mousedown.clicklogger');
        $(document).bind('mousedown.clicklogger', function (e) {
			if ($(e.target).is('input') || $(e.target).is('select') || $(e.target).is('textarea') || $(e.target).is('label') || $(e.target).parents('input,textarea,select,label').length || $(e.target).parents('.wysibb').length )
			{
				return true;
			}
            logClick(e);
            return true;
        });
/*
        $(document).bind('mousemove', function (e) {
            trackMouse(e);
        });
 */
        $(window).bind('beforeunload', function () {
            var x = 0;
			// little blocking
            while (x < 90000) {
                if (!x) {
                    unloadMouseTracker();
                    console.log('s');
                }
                ++x;
            }
        });
    });
}/*
 else
 {
 if (document.addEventListener) {
 analyseClicks = true;
 document.addEventListener("mousedown", logClick, false);
 document.addEventListener("mousemove", trackMouse, false);
 document.addEventListener("beforunload", unloadMouseTracker, false);
 }
 else {
 if (document.attachEvent) {
 analyseClicks = true;
 document.attachEvent("onmousedown", logClick);
 document.attachEvent("onmousemove", logClick);
 document.attachEvent("onbeforunload", unloadMouseTracker);
 }
 }
 }
 */

function trackMouse (e) {
    var positionX = -1;
    var positionY = -1;

    if (e.pageX) {
        positionX = e.pageX;
        positionY = e.pageY;
    }
    else if (e.clientX) {
        positionX = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
        positionY = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
    }

    yT.push([positionX, positionY]);
}

function unloadMouseTracker () {

    jQuery.ajax({
        async: true,
        url: 'index.php',
        type: "POST",
        global: false,
        data: {
            cp: 'clickanalyser',
            action: 'logclick',
            mt: yT,
            screen: screenSize,
            url: urlLogger,
			token: token
        },
        error: function () {

        },
        success: function (data) {
			if (data && typeof data.csrfToken === 'string') { token = data.csrfToken; }
        }

    });

}

function logClick (event)
{
    clearTimeout(clickTimeout);
    clickTimeout = setTimeout(function () {

        if (authKey !== null || typeof analyseClicks != 'undefined' || (typeof clickAnalyserOn != 'undefined' && clickAnalyserOn)) {
            // return;
        }

        if ($(event.target).is('input') || $(event.target).is('select') || $(event.target).is('textarea') || $(event.target).is('label') || $(event.target).parents('input,textarea,select,label').length || $(event.target).parents('.wysibb').length)
        {
			clearTimeout(clickTimeout);
            return;
        }

        var positionX = -1;
        var positionY = -1;

        if (event.pageX) {
            positionX = event.pageX;
            positionY = event.pageY;
        }
        else if (event.clientX) {
            positionX = event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            positionY = event.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }

        if (positionX > -1) {
            var timeClick = new Date();
            var timeRet = parseInt((timeClick.getTime() - timeLoad.getTime()) / 1000);
            if (!jQuery)
            {
                var logURL = "index.php?cp=clickanalyser&action=logclick&x=" + positionX + '&y=' + positionY + '&t=' + timeRet + '&screen=' + screenSize + "&url=" + base64_encode(urlLogger);
                var requestObject;
                if (window.XMLHttpRequest) {
                    requestObject = new XMLHttpRequest();
                }
                else if (window.ActiveXObject) {
                    requestObject = new ActiveXObject("Microsoft.XMLHTTP");
                }
                requestObject.open("GET", logURL, true);
                requestObject.send(null);
            }
            else
            {
                $.post('index.php', {
                    cp: 'clickanalyser',
                    action: 'logclick',
                    x: positionX,
                    y: positionY,
                    t: timeRet,
					token: token,
                    screen: screenSize,
                    url: base64_encode(urlLogger)
                }, function (data) {
					if (data && typeof data.csrfToken === 'string') { token = data.csrfToken; }
                });
            }
        }

    }, 1000);
}