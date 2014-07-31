/**
 DreamCMS (c)2011
 Seemode Click Analyser
 */
var isActiveClickAnalyzer = false, urlAnalyzer = base64_encode(document.location.href);


function analyseClicks() {

    var analyzerURL = "admin.php?adm=clickanalyser&action=analyse&ajax=1&url=" + urlAnalyzer + '&seemodePopup=1&setpage=' + authSite;

    $.get(analyzerURL, {}, function(data) {
        if (responseIsOk(data))
        {
            if (typeof data.clicks != 'undefined')
            {


                for (var i = 0; i < data.clicks.length; i++) {
                    var dat = data.clicks[i];

                    var clickPoint = document.createElement('div');
                    clickPoint.innerHTML = '&#160;';
                    clickPoint.className = 'analysePoint';
                    clickPoint.style.zIndex = 999900 + i;


                    x = (dat.x > 0 ? dat.x : 0);
                    y = (dat.y > 0 ? dat.y : 0);

                    clickPoint.style.width = '10px';
                    clickPoint.style.height = '10px';
                    clickPoint.style.left = (x - 5) + 'px';
                    clickPoint.style.top = (y - 5) + 'px';

                    document.getElementsByTagName("body")[0].insertBefore(clickPoint, document.body.firstChild);
                }
                isActiveClickAnalyzer = true;
            }
        }
        else
        {
            alert(data.msg);
        }
    });


}