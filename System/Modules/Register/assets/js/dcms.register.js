$(document).ready(function () {

    var div = $('<div id="form-message" class="validation"></div>');
    div.insertBefore($('#register-form'));
    div.hide();


    $('#register-form').registerFormFE({
        exit_url: systemUrl,
        save: function (exit) {
            var self = this;
            $('#form-message').hide();
            var post = $('#register-form').serialize();

            $.post('register/index', post, function (data) {
                if (responseIsOk(data)) {

                    var button = $('<button/>')
                        .addClass('button')
                        .append('Weiter...');
                    $('#form-message')
                        .empty();

                    var span = $('<div/>')
                        .append(data.msg);
                    $('#register-form')
                        .hide();

                    $('#form-message')
                        .addClass('success')
                        .append(span)
                        .show();

                    $('#form-message')
                        .addClass('success')
                        .append($('<div/>')
                            .append(button));

                    button.click(function () {
                        document.location.href = systemUrl
                    });
                } else {
                    //$('#form-message').hide();
                    self.error(data);
                }
            }, "json");
        }
    });
});