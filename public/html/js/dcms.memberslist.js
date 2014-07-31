function loadList(mode) {

	$('#ajax-list').mask('laden...');
	$.post(systemUrl +'/index.php', {ajax: true, cp: 'members', viewmode: mode, page: page}, function(data) {
		$('#ajax-list').unmask();
		if (responseIsOk(data))
		{
			$('#ajax-list').empty().append(data.content);
		}
		else
		{
			alert(data.msg);
		}	
	}, "json");

}




$(document).ready(function() {
	$('#view-list').click(function(e) {
		e.preventDefault();
		
		mode = 'list';
		loadList(mode);
		
	});

	$('#view-photo').click(function(e) {
		e.preventDefault();
		mode = 'photo';
		loadList(mode);
	});

	$('#view-advancedlist').click(function(e) {
		e.preventDefault();
		
		mode = 'advancedlist';
		loadList(mode);
	});
});