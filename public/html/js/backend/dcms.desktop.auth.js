Desktop.Auth = (function () {
    return {
        display: function (forceAnimate)
        {

            var self = this;

            if (forceAnimate && $('#desktop').is(':visible'))
            {
                $('#desktop,#Taskbar').animate({
                    opacity: 'hide'
                }, 1000);

                $('#loginscreen').show().animate({
                    opacity: '1.0'
                }, {
                    queue: false,
                    duration: 1000
                });

                $('#login-logo').show().animate({
                    'opacity': "1.0"
                }, 500, 'linear', function () {


                    $('#login-logo').animate({
                        'top': '25%'
                    }, 300, function () {

                       // console.log('animate: form-content');

                        $('.form-content', $('#loginscreen')).show().animate({
                            top: '47%',
                            'opacity': "1.0"
                        }, 700);

                    });
                });

            }

        },
        doAuth: function ()
        {

            $('.submit', $('#loginscreen')).removeClass('charge');
            $('form', $('#loginscreen')).find('p').remove();

            var serialized = $('form', $('#loginscreen')).serialize();
            var self = this;



            $('#login-error').remove();
            $('form', $('#loginscreen')).get(0).reset();

            $('.submit', $('#loginscreen')).addClass('charge');
            setTimeout(function () {
                $.ajax({
                    url: 'admin.php',
                    data: serialized,
                    async: false,
                    type: 'POST',
                    cache: false,
                    dataType: "json"
                }).done(function (data) {

                    if (Tools.responseIsOk(data))
                    {
                        setTimeout(function () {
                            $('.submit', $('#loginscreen')).removeClass('charge');
                            self.getDesktop();
                        }, 1000);
                    }
                    else
                    {
                        $('.submit', $('#loginscreen')).removeClass('charge');

                        if (typeof data.msg != 'undefined')
                        {

                            var Balloon = Template
                                    .setTemplate(Desktop.Templates.FormNotification)
                                    .process(
                                            {
                                                id: 'login-error',
                                                message: data.msg,
                                                title: 'Login Error'
                                            });

                            Template.reset();
                            $(Balloon).appendTo($('body'));


                            var lastFormPosition = $('#logpassword', $('#loginscreen')).offset().top + $('#logpassword', $('#loginscreen')).outerHeight();

                            $('#login-error').css({
                                'top': lastFormPosition + 15,
                                'left': $(window).width() / 2 - ($('#login-error').outerWidth(true) / 2) - 10
                            }).show();


                            $('.x-panel-header-text', $('#login-error')).addClass('error');
                        }


                        setTimeout(function ()
                        {
                            $('#logusername', $('#loginscreen')).effect('shake', {
                                distance: 10,
                                times: 3
                            }, 400);
                        }, 10);

                        setTimeout(function ()
                        {
                            $('#logpassword,#auth-submit', $('#loginscreen')).effect('shake', {
                                distance: 10,
                                times: 3
                            }, 400);
                        }, 50);
                    }
                });

            }, 100);

            return false;

        },
        getDesktop: function ()
        {

            $('.form-content', $('#loginscreen')).css({
                'opacity': "0"
            });

            $('#login-logo').css({
                'opacity': "1.0"
            });

            $('#desktop,#Taskbar').css({
                'opacity': "0"
            });

            $('#desktop,#Taskbar').hide(0);


            $('.form-content', $('#loginscreen')).css({
                opacity: '1'
            }).animate({
                'opacity': "0"
            }, 100, function () {

                $('#login-logo').animate({
                    'top': '45%'
                }, 300, function () {

                    $('#login-logo').animate({
                        'opacity': '0'
                    }, 350, function () {
                        Desktop.runAfterBoot(false);
                    });


                    // Desktop.basicCMSData = Desktop.loadBasicConfig();


                });
            });


            return true;

            var self = this;



            setTimeout(function () {



                $('#login-logo').animate({
                    'opacity': '0'
                }, 350);



                $('#desktop,#desktop-bg,#Taskbar').delay(500).css({
                    opacity: '0'
                }).show().animate({
                    'opacity': "1.0"
                }, 700, 'linear', function () {

                    $('#loginscreen').hide();
                    $('#desktop,#Taskbar').show();
                    $('body').removeClass('boot').removeClass('auth');

                    Desktop.showDesktop();

                    $('#Taskbar').hide().css({
                        position: 'absolute',
                        left: 0,
                        top: 0 - $('#Taskbar').outerHeight()
                    }).show().animate({
                        top: 0
                    }, 700);


                });

            }, 1500);
        }
    };

})(window);