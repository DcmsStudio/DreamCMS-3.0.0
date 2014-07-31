
function dcmsTracker()
{

    if (typeof jQuery == 'undefined') {
        alert('Track Error...');
    }

    //

    var screenSize = screen.width + ";" + screen.height;


    // @todo track screen colors, flash support ...
    $.post('index.php', {
        
        cp: 'tracker',
        screenXY: screenSize,
        ua: navigator.userAgent,
        HTTP_REFERER: location.referrer,
		token: token
    }, function(data) {
        if (typeof data == 'object' && typeof data.success == 'boolean' && data.success == true) {
			if (data.csrfToken === 'string') { token = data.csrfToken; }
            return;
        }
        else
        {
            alert('Track Error:' + data.msg);
        }
    }, 'json');
}
