function loadList(mode) {
    $('#ajax-list').mask('laden...');
    var page = document.location.href.replace(/.*\/(\d+?)\/.*/g, '$1');
    $.post('index.php', {ajax: true, cp: 'members', viewmode: mode, page: page}, function (data) {
        $('#ajax-list').unmask();
        if (responseIsOk(data)) {
            $('#ajax-list').empty().html(data.content);
        }
        else {
            alert(data.msg);
        }
    }, "json");

}


$(document).ready(function () {

    $('#view-list').attr('href', 'javascript:void(0)').click(function (e) {

        //e.preventDefault();
        mode = 'list';
        loadList(mode);
        return false;
    });

    $('#view-photo').attr('href', 'javascript:void(0)').click(function (e) {
        //e.preventDefault();
        mode = 'photo';
        loadList(mode);
        return false;
    });

    $('#view-advancedlist').attr('href', 'javascript:void(0)').click(function (e) {
        //e.preventDefault();

        mode = 'advancedlist';
        loadList(mode);
        return false;
    });
});