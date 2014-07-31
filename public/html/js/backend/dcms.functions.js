

function convertMS (s) {
    var d, h, m, s;
    // s = Math.floor(ms / 1000);
    m = Math.floor(s / 60);
    s = s % 60;
    h = Math.floor(m / 60);
    m = m % 60;
    d = Math.floor(h / 24);
    h = h % 24;
    return {day: d, hour: h, min: m, sec: s};
}
;

/**
 * Will translate a string
 * use sprintf for extra params giving
 * 
 * @todo for the next version 
 * 
 * @returns string
 */
function trans ()
{
    // first arg is the string

    // all other args as sprintf params ;)
}


function refreshAfterModulPublishingChange() {

	if (Desktop.isWindowSkin) {
		Launchpad.refresh();
	}
	else {
		delete top.menuItems;
		$('#main-menu ul:first').empty().append('<li>reload ...</li>');

		$.ajax({
			type: "GET",
			url: Tools.prepareAjaxUrl('admin.php?action=menu'),
			success: function () {
				DesktopMenu.coreMenuCache = top.menuItems;
				DesktopMenu.createCoreMenu();
				//top.menuItems = null;
			},
			dataType: "script",
			cache: false
		});
	}
}



function updateZebraForTreeGrid (obj, listItemType, zebraRow) {

    listItemType = listItemType || 'li';
    zebraRow = zebraRow || 'row';

	obj.find('table' ).addClass('table table-striped table-hover');

    obj.find(listItemType).each(function (index) {
        $(this).removeClass('zebra');
        if (index % 2) {
            $(this).addClass('zebra');
        }

    });
    
    var rows = obj.find(zebraRow);

    rows.each(function () {
        var l;
        if ((l = $(this).parents('ul,ol').length) > 1) {
            $(this).css({
                paddingLeft: (l - 1) * 20
            });
        }
        else {
            $(this).css({
                paddingLeft: ''
            });
        }
    });

    rows.hover(function () {
        $(this).parents('.hover').removeClass('hover');
        $(this).addClass('hover');
    }, function () {
        $(this).removeClass('hover');
    });

}

