/**
 *  DreamCMS Autocompleter
 */
;
(function($) {

    $.fn.dcmsAutocomplete = function(options) {


        options = options || {};

        options.postparams = options.postparams || {};
        options.minlength = (options.minlength > 0 ? options.minlength : 3);


        var delay = 240;

        return this.each(function() {

            var timeouter, container, scrollable, ul, input = $(this);
            var uiq = 'c-' + Date.now();


            input.attr('autocomplete', 'off').attr('uiq', uiq).addClass('autocomplete');
            input.on('blur', function() {

                var rel = $(this).attr('uiq');
                setTimeout(function() {
                    if ($('#' + rel).is(':visible'))
                    {
                        clearTimeout(timeouter);
                        $('#' + rel).hide().remove();
                    }
                }, 200);
            });


            input.on('keydown', function(e) {
                var rel = $(this).attr('uiq');
                switch (e.keyCode)
                {
                    case 38: // up
                        if ($('#' + rel).find('li.active').prev().is('li'))
                        {
                            $('#' + rel).find('li.active').removeClass('active').prev().addClass('active');
                            Tools.scrollBar($('#' + rel).find('ul'), $('#' + rel).find('.active'));
                        }
                        return false;
                        break;
                    case 40: // down
                        if ($('#' + rel).find('li.active').next().is('li'))
                        {
                            $('#' + rel).find('li.active').removeClass('active').next().addClass('active');



                            Tools.scrollBar($('#' + rel).find('ul'), $('#' + rel).find('.active'));

                        }
                        return false;
                        break;
                    case 13: // return
                        e.preventDefault();
                        $('#' + rel).find('li.active').trigger('click');
                        return false;
                        break;
                }

            });

            input.on('keyup', function(e) {

                switch (e.keyCode)
                {
                    case 38: // up
                    case 40: // down
                    case 13: // return
                        return false;
                        break;
                }

                clearTimeout(timeouter);

                var value = $(this).val();
                var rel = $(this).attr('uiq');

                timeouter = setTimeout(function() {

                    clearTimeout(timeouter);
                    $('#' + rel).remove();

                    if ($('#' + rel).length != 1)
                    {
                        if (typeof options.container == 'object')
                        {
                            container = options.container;
                            container.hide().attr('id', rel);
                            if (container.find('ul').length == 0)
                            {
                                container.append($('<div>').append($('<div>').append('<ul/>')));
                            }

							container = $('#' + rel);
                        }
                        else if (typeof options.container == 'string')
                        {
                            container = $('#' + options.container);
                            container.hide().attr('id', rel);
                            if (container.find('ul').length == 0)
                            {
                                container.append($('<div>').append($('<div>').append('<ul/>')));
                            }

							container = $('#' + rel);
                        }
                        else
                        {
                            container = $('<div>' ).attr('id', rel);
                            container.hide();
                            container.append($('<div>').append($('<div>').append('<ul/>')));
                            $('body').append(container);

							container = $('#' + rel);
                        }
                    }
                    else
                    {
                        container = $('#' + rel);
                    }

                    container.addClass('autocompleter');
                    container.css({maxWidth: input.width()}).attr('id', rel);


                    scrollable = container.find('div:last');
                    ul = container.find('ul');

                    Tools.removeScrollBar(ul);

                    container.css('height', '');
                    ul.removeAttr('class').removeAttr('style');
                    scrollable.removeAttr('class').removeAttr('style');

                    input.addClass('execute-autocomplete');

                    if (value.length >= options.minlength)
                    {


                        options.postparams.q = value;
						if (typeof options.postparams.token == 'undefined' ) {
							options.postparams.token = Config.get('token');
						}

                        $.post(options.url, options.postparams, function(data) {
                            if (Tools.responseIsOk(data))
                            {
                                if (data.items && data.items.length)
                                {
                                    scrollable.css({height: '100%'});
                                    ul.css('height', '').empty();


                                    ul.css('height', (data.items.length > 20 ? 20 * 20 : data.items.length * 20));
                                    var ulHeight = parseInt(ul.outerHeight(true), 10), maxHeight = parseInt(scrollable.outerHeight(true), 10);

                                 //   console.log('maxHeight ' + maxHeight + ' ulHeight: ' + ulHeight);






                                    if (maxHeight > 0)
                                    {
                                        if (ulHeight > 0 && ulHeight < maxHeight)
                                        {
                                            scrollable.css({height: ulHeight});
                                        }
                                        else if (ulHeight > maxHeight)
                                        {
                                            scrollable.css({height: maxHeight});
                                        }
                                        else
                                        {
                                            scrollable.css({height: (data.items.length > 6 ? 20 * 6 : data.items.length * 20)});
                                        }
                                    }
                                    else
                                    {
                                        scrollable.css({height: (data.items.length > 6 ? 20 * 6 : data.items.length * 20)});
                                    }



                                    for (var x = 0; x < data.items.length; ++x)
                                    {
                                        ul.append($('<li>').attr('rel', data.items[x].id).append(data.items[x].label));
                                    }


                                    if (typeof options.onClick == 'function')
                                    {
                                        ul.find('li').bind('click', function(e) {
                                            e.preventDefault();
                                            options.onClick(e);
                                            $('#' + rel).hide().remove();
                                        }).on('mouseover', function() {
                                            ul.find('li.active').removeClass('active');
                                            $(this).addClass('active');
                                        });
                                    }
                                    else
                                    {
                                        ul.find('li').bind('click', function(e) {
                                            e.preventDefault();
                                            $('#' + rel).hide().remove();
                                        }).on('mouseover', function() {
                                            ul.find('li.active').removeClass('active');
                                            $(this).addClass('active');
                                        });
                                    }


                                    ul.find('li:eq(0)').addClass('active'); // select the first item




                                    var offset = input.offset();
                                    container.css({height: scrollable.height(), left: offset.left, top: offset.top + input.outerHeight(true)});

                                    setTimeout(function() {
                                        input.removeClass('execute-autocomplete');
                                    }, 300);

                                    container.show();
                                    Tools.scrollBar(ul);

                                }
                                else
                                {
                                    setTimeout(function() {
                                        input.removeClass('execute-autocomplete');
                                    }, 300);
                                    $('#' + rel).hide().remove();
                                }
                            }
                            else
                            {
                                setTimeout(function() {
                                    input.removeClass('execute-autocomplete');
                                }, 300);
                                $('#' + rel).hide().remove();
                            }
                        });


                    }
                    else
                    {
                        setTimeout(function() {
                            input.removeClass('execute-autocomplete');
                        }, 300);
                        $('#' + rel).hide().remove();
                    }

                }, delay);
            });


        });
    };

})(jQuery);