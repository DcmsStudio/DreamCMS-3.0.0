var Auth = {
    init: function () {
        var self = this;

        $('.submit', $('#auth')).removeClass('charge');
        $('form', $('#auth')).find('p').remove();

        $('form', $('#auth')).unbind().submit(function (e) {
            e.preventDefault();
            self.execute();
        });


        $('input[type=password],input[type=text]', $('#auth')).unbind().focus(function () {
            //  $('#login-error').remove();
        });
    },
    execute: function () {
        $('.submit', $('#auth')).addClass('charge');
        $('#login-error').remove();
        var self = this, serialized = $('form', $('#auth')).serialize();

        setTimeout(function () {
            $.ajax({
                url: 'admin.php',
                data: serialized,
                async: true,
                type: 'POST',
                cache: false,
                dataType: "json"
            }).done(function (data) {

                $('.submit', $('#auth')).removeClass('charge');

                if (Tools.responseIsOk(data))
                {
                    $('body').addClass('boot').removeClass('auth');
                    // $('#dashboard-container,#auth').hide();
                    Core.run(data, true);

                }
                else {

                    if (typeof data.msg != 'undefined')
                    {
                        var Balloon = Template.setTemplate(Desktop.Templates.FormNotification).process({
                            id: 'login-error',
                            message: data.msg,
                            title: 'Login Error'
                        });

                        Template.reset();

                        $(Balloon).appendTo($('body'));

                        var lastFormPosition = $('form', $('#auth')).offset().top + $('form', $('#auth')).outerHeight(true);
                        $('#login-error').css({
                            'top': lastFormPosition + 15,
                            'left': ($(window).width() / 2) - ($('#login-error').outerWidth(true) / 2)
                        }).show();

                        $('.x-panel-header-text', $('#login-error')).addClass('error');

                        $('input[type=text]', $('#auth')).effect('shake', {
                            distance: 10,
                            times: 3
                        }, 500);

                        setTimeout(function () {
                            $('input[type=password],input.submit', $('#auth')).effect('shake', {
                                distance: 10,
                                times: 3
                            }, 450);
                        }, 50);
                    }
                }
            });
        }, 100);
    }
};