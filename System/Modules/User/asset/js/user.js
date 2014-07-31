/**
 * Created by marcel on 06.03.14.
 */


function userblog(opt) {

	var opts = $.extend({}, opt);

	this.init = function() {
		if (document.location.href.match(/(\/|&)(add|edit)\/?/ig)) {
			this.bindEditForm();
		}
	};

	this.bindEditForm = function() {

		if (typeof opts.formid == 'undefined')
		{
			console.log('Invalid formid for userblog edit!');
			return;
		}
		$('#'+ opts.formid ).find('textarea.bbcode-textarea' ).each(function() {

			$(this).comment({
				resize_maxheight: 600,
				autoresize: false,
				traceTextarea: false,
				buttons: 'undo,redo,|,img,link,video,map,smilebox,|,quote,code,offtop,table,removeFormat,-,bold,italic,underline,strike,sup,sub,|,justifyleft,justifycenter,justifyright,|,bullist,numlist,|,fontcolor,fontsize,fontfamily',
			});
		});


		$('#blog-edit-form').find('#send').click(function(){
			$('#blog-edit-form').find('textarea.bbcode-textarea' ).each(function() {
				$(this).sync();
			});
			$('#blog-edit-form' ).submit();
		});

	};





}
