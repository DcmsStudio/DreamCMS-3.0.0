/**
 * QR code - a TinyMCE 4 QR code wizzard
 * qrcode/plugin.js
 *
 *
 * Plugin info: http://www.cfconsultancy.nl/
 * Author: Ceasar Feijen
 *
 * Version: 1.1.1 released 2013-10-14
 */
tinymce.PluginManager.requireLangPack('qrcode');
tinymce.PluginManager.add('qrcode', function(editor) {
	function getParams(qs) {

		// This function is anonymous, is executed immediately and
		// the return value is assigned to QueryString!
		var query_string = {};
		if (qs.match(/\?/g)) {
			qs = qs.replace(/.*\?(.*)/g, '$1');
		}
		var query = qs;

		var vars = query.split("&");
		for (var i=0;i<vars.length;i++) {
			var pair = vars[i].split("=");
			// If first entry with this name
			if (typeof query_string[pair[0]] === "undefined") {
				query_string[pair[0]] = pair[1];
				// If second entry with this name
			} else if (typeof query_string[pair[0]] === "string") {
				var arr = [ query_string[pair[0]], pair[1] ];
				query_string[pair[0]] = arr;
				// If third or later entry with this name
			} else {
				query_string[pair[0]].push(pair[1]);
			}
		}
		return query_string;
	};

    function openmanager(r) {
		var img = editor.selection.getNode(), params = [];
		if (img) {
			if (img.tagName.toLowerCase() == 'img' && img.src && img.src.match(/qrserver\.com/gi) )
			{
				params = getParams(img.src);
			}
		}
        var title="Create QRcode";
        if (typeof tinymce.settings.qrcode_title !== "undefined" && tinymce.settings.qrcode_title) {
            title=tinymce.settingsqrcode_title;
        }
        win = editor.windowManager.open({
            title: title,
            url: tinyMCE.baseURL + '/plugins/qrcode/qrcode.html' ,
            filetype: 'image',
	    	width: 650,
            height: 430,
            inline: 1,
            buttons: [{
                text: 'cancel',
                onclick: function() {
                    this.parent()
                        .parent()
                        .close();
                }
            }]
        }, params);
    }


	editor.addButton('qrcode', {
		icon: ' fa fa-qrcode',
		//image: tinyMCE.baseURL + '/plugins/qrcode/icon.png',
		tooltip: 'Insert QR Code',
		shortcut: 'Ctrl+QR',
		stateSelector: ['img[data-mce-placeholder=qrcode]'],
		onclick: openmanager
	});

	editor.addShortcut('Ctrl+QR', '', openmanager);

	editor.addMenuItem('qrcode', {
		icon:' fa fa-qrcode',
		text: 'Insert QR Code',
		shortcut: 'Ctrl+QR',
		onclick: openmanager,
		context: 'insert'
	});
});
