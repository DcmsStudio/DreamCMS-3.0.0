
if (typeof initUserGroupPerms === 'undefined')
{


    function initUserGroupPerms ()
    {

        var w = $('#' + Win.windowID);
        w.find('[rel]').each(function () {

            var require = $(this).attr('rel'), wi = (Desktop.isWindowSkin ? $(this).parents('.isWindowContainer:first') : $('#' + Win.windowID));

            if (require && $(this).parents('.box-inner:first').length == 1 && $(this).parents('.box-inner:first').attr('class'))
            {
                var self = $(this);
                var keyname = $(this).parents('.box-inner:first').attr('class').replace(/.*(perm-([a-z0-9_]*)).*/ig, '$2');
                var idName = keyname + '-' + require;
                if (keyname && require)
                {
                    var selected = wi.find('#' + idName); //$('#' + Win.windowID).find('input[name="' + keyname + Tools.escapeJqueryRegex('[' + require + ']') + '"]');

                    if (selected.length > 1)
                    {
                        jAlert('Document contains more as one element of: ' + require);
                        return;
                    }
                    else if (selected.length == 1)
                    {

                        if ($('#' + idName + '_container').length == 0)
                        {
                            var div = $('<div/>').attr('id', idName + '_container').addClass('form-sub-container');
                            div.insertAfter($(selected).parent());


                            $(selected).on('change', function () {
                                var el = $(this);
                                setTimeout(function () {
                                    if (el.is(':checked'))
                                    {
                                        $('#' + idName + '_container').show();
                                    }
                                    else
                                    {
                                        $('#' + idName + '_container').hide();
                                    }

                                    Win.refreshWindowScrollbars(wi.attr('id'));
                                }, 10);
                                //if (GUI) GUI.updateScrollSize();
                            });


                            selected.css({marginTop: '5px'});
                            wi.find('#' + idName).parent().next('div').css({'marin-bottom': '20px', 'width': 'auto', 'display': 'block'});
                        }

                        $(this).appendTo($('#' + idName + '_container'));

                        if (selected.prop('checked'))
                        {
                            $('#' + idName + '_container').show();
                        }
                        else
                        {
                            $('#' + idName + '_container').hide();
                        }

                    }
                }
            }

        });


        w.find('.box-inner label').css({'cursor': 'pointer'});
       // w.find('.box-inner div.form-sub-container').css({marginTop: '3px', marginLeft: '10px', marinBottom: '20px', 'paddingLeft': '10px', 'border-left': '1px solid #c0c0c0'});
        w.find('div.form-sub-container :first-child fieldset').css({marginLeft: '20px', marinBottom: '10px', 'clear': 'both'});
        w.find('label').css({clear: 'both', display: 'block'}).each(function () {
            var _for = $(this).attr('for');

            if (_for)
            {
                $(this).insertBefore($('#' + _for + '_container'));
            }
        });



        //$('<br/>').insertAfter($('#' + Win.windowID).find('label'));
    }

}


if (typeof initUserPerms === 'undefined')
{

    function initUserPerms (isSpecialUserPerms)
    {

        var w = $('#' + Win.windowID);



		w.find('[rel]').each(function () {
			var keyname = '';
			var require = $(this).attr('rel'), wi = (Desktop.isWindowSkin ? $(this).parents('.isWindowContainer:first') : $('#' + Win.windowID));

			if (require) {
				keyname = $(this).parents('[perm]');
				if (keyname && keyname.length == 1) {

					keyname = keyname.attr('perm');
					require = require.replace(/\d*?$/g, '');
					var idName = keyname + '-' + require ;
					var fieldset = wi.find('fieldset[rel='+require +']');

					if ($('#' + idName + '_container').length == 0)
					{
						var div = $('<div/>').attr('id', idName + '_container').addClass('subitems-container');
						if ( $('#' + idName +'0').parents('fieldset:first' ).length ) {
							div.insertAfter( $('#' + idName +'0').parents('fieldset:first' ) );
						}
						else {
							div.insertAfter( $('#' + idName +'0') );
						}
					}


					if ( fieldset.length ) {
						fieldset.appendTo($('#' + idName + '_container'));
					}


					$('#' + idName +'1,#'+idName +'0,#'+idName +'2').change(function () {

						var el = $(this);
						setTimeout(function () {

							if (el.is(':checked') && el.val() > 0)
							{
								$('#' + idName + '_container').show();
							}
							else
							{
								$('#' + idName + '_container').hide();
							}
						}, 10);
					});


					if ( $('#' + idName +'1').is(':checked') )
					{
						$('#' + idName + '_container').show();
					}
					else
					{
						$('#' + idName + '_container').hide();
					}

				}


			}
		});







/*
        w.find('input[use]').each(function () {


            var keyname = '';
            var require = $(this).attr('use'), wi = (Desktop.isWindowSkin ? $(this).parents('.isWindowContainer:first') : $('#' + Win.windowID));

            if (require && $(this).parents('.box-inner:first').length == 1 && $(this).parents('.box-inner:first').attr('class'))
            {

                if ($(this).parents('.box-inner:first').length == 0)
                {
                    Debug.log('Undefined Perm key was found');
                }
                else
                {
                    keyname = $(this).parents('.box-inner:first').attr('perm');
                }


				require = require.replace(/\d*?$/g, '');
                var idName = keyname + '-' + require;
                var selected = wi.find('#' + idName);


                if (selected.length > 1)
                {
                    jAlert('Document contains more as one element of: ' + require);
                    return;
                }
                else if (selected.length == 1)
                {

                    if ($('#' + idName + '_container').length == 0)
                    {
                        var div = $('<div/>').attr('id', idName + '_container').addClass('subitems-container');
                        div.insertAfter($(selected).parent());

                        $(selected).change(function () {

                            var el = $(this);
                            setTimeout(function () {

                                if (el.is(':checked'))
                                {
                                    $('#' + idName + '_container').show();
                                }
                                else
                                {
                                    $('#' + idName + '_container').hide();
                                }
                            }, 10);
                        });

                        selected.css({marginTop: '5px'});
                        selected.parent().next('div').css({'marin-bottom': '20px', 'width': 'auto', 'display': 'block'});
                    }

                    $(this).appendTo($('#' + idName + '_container'));


                    if ($('#' + idName).is(':checked'))
                    {
                        $('#' + idName + '_container').show();
                    }
                    else
                    {
                        $('#' + idName + '_container').hide();
                    }
                }

            }
			else if (require && $(this).parents('.panel-body:first').length == 1 ) {

				keyname = $(this).parents('.panel-body:first').attr('perm');
				require = require.replace(/\d*?$/g, '');

				var idName = keyname + '-' + require;
				var selected = wi.find('#' + idName);


				if (selected.length > 1)
				{
					jAlert('Document contains more as one element of: ' + require);
					return;
				}
				else if (selected.length == 1 )
				{

					if ($('#' + idName + '_container').length == 0)
					{
						var div = $('<div/>').attr('id', idName + '_container').addClass('subitems-container');
						div.insertAfter($(selected).parent());

						selected.css({marginTop: '5px'});
						selected.parent().next('div').css({'marin-bottom': '20px', 'width': 'auto', 'display': 'block'});
					}

					if (!selected.find('[use=' + $(this ).attr('use') +']').length) {
						selected.appendTo($('#' + idName + '_container'));
					}
					else {
						$(this).change(function () {

							var el = $(this);
							setTimeout(function () {

								if (el.is(':checked') && el.val() > 0)
								{
									$('#' + idName + '_container').show();
								}
								else
								{
									$('#' + idName + '_container').hide();
								}
							}, 10);
						});
					}




					if ( $(this).is(':checked') && $(this).val() > 0 )
					{
						$('#' + idName + '_container').show();
					}
					else
					{
						$('#' + idName + '_container').hide();
					}
				}

			}

        });
*/


        $('div.subitems-container', w).css({marginLeft: '10px', marinBottom: '20px', 'paddingLeft': '10px', 'border-left': '1px solid #c0c0c0'});
     //   w.find('div.subitems-container:first-child' ).find('div.subitems-container').css({marginLeft: '10px', marinBottom: '10px', 'clear': 'both'});

		if (!isSpecialUserPerms) {
			w.find('label[for]' ).css({clear: 'both', display: 'block'});
		}
		else {
			w.find('label[for]').css({clear: 'none', display: 'inline-block', marginRight: 20});
		}

		w.find('label[for]').each(function () {
			var _for = $(this).attr('for');

			if (_for)
			{
				_for = _for.replace(/\d+?$/g, '');
				$(this ).parents('fieldset:first').insertBefore($('#' + _for + '_container'));
			}
		});


/*
        if (!isSpecialUserPerms) {

            w.find('label[for]').css({clear: 'both', display: 'block'}).each(function () {
                var _for = $(this).attr('for');
                if (_for)
                {
                    $(this).insertBefore($('#' + _for + '_container'));
                }
            });

			w.find('label' ).not('[rel]').css({ display: ''});
        }
        else {
            w.find('label[for]').css({clear: 'none', display: 'inline-block', marginRight: 20}).each(function () {
                var _for = $(this).attr('for');
                if (_for)
                {
                    if ($(this).next().is('label')) {
                        $(this).next().insertBefore($('#' + _for + '_container'));
                    }
                    else {
                        $(this).insertBefore($('#' + _for + '_container'));
                    }
                }
            });


			w.find('label' ).not('[rel]').css({ display: ''});
        }
        */
    }

}