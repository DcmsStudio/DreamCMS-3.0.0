;
(function ($)
{
    var selectBoxIds = [];

    $.fn.SelectBox = function (options)
    {
        var opts = options;

        return this.each(function ()
        {
            var isMultiLined = false, width = false, isMultiSelect = false;

            if ($(this).attr('size') > 1)
            {
                isMultiLined = parseInt($(this).attr('size'), 10);
                // return this;
            }


            if ($(this).attr('multiple'))
            {
                if (!isMultiLined)
                {
                    isMultiLined = 4;
                }
                isMultiSelect = true;
            }
            else {
                width = $(this).outerWidth(true);
            }

            //  Debug.log('select: ' + opts);




            if (opts == 'destroy')
            {
                var listId = $(this).attr('sb');
                if (listId)
                {
                    $(this).removeAttr('sb').removeClass('inputS');
                    $('.select-box[listid="' + listId + '"]').remove();
                    $('#' + listId).remove();

                    delete selectBoxIds[listId];

                    if (selectBoxIds.length == 0)
                    {
                        $(document).unbind('mouseup.cbk');
                        $(document ).unbind('click.singleselect');
                    }
                }

                return this;
            }

			var isTransformed = $(this).hasClass('inputS');


            if (opts == 'reset')
            {
                var self = this, listId = $(this).attr('sb');


                if (listId )
                {

                    setTimeout(function () {
                        $('#' + listId).find('.selected').removeClass('selected');

                        if ($(self).attr('default') === '_none_')
                        {
                            $(self).next().children('.select-box-label').html($(self).find('option:eq(0)').text());
                            $('#' + listId).find('[rel="_none_"]').addClass('selected');


                            $(self).find('option:selected').each(function () {
                                $(this).removeAttr('selected').prop('selected', false);
                            });

                            $(self).find('option:eq(0)').attr('selected', 'selected').prop('selected', true);
                        }
                        else
                        {
                            var defaults = $(self).attr('default');
                            var def = defaults.split(';;');


                            $(self).find('option:selected').each(function () {
                                $(this).removeAttr('selected').prop('selected', false);
                            });
                            
                            var label = $(self).next().children('.select-box-label');

                            for (var i = 0; i < def.length; ++i) {

                                var opt = $(self).find('option[value="' + Tools.escapeJqueryRegex(def[i]) + '"]');

                                if (opt.length == 1) {
                                    label.html(opt.text());
                                    $('#' + listId).find('[rel="' + Tools.escapeJqueryRegex(def[i]) + '"]').addClass('selected');


                                    opt.attr('selected', 'selected').prop('selected', true);

                                }


                            }
                            /*
                             $(self).filter('option:selected').each(function () {
                             
                             label.html($(this).text());
                             $('#' + listId).find('[rel="' + Tools.escapeJqueryRegex($(this).val()) + '"]').addClass('selected');
                             });
                             */
                        }

                    }, 50);
                }

                return this;
            }

            if (opts == 'resetDefault')
            {
                var label = $(this).next().find('.select-box-label');
                if (label.length)
                {
                    label.parent().attr('default', $(this).find('option:eq(' + this.selectedIndex + ')').val());
                }

                return this;
            }

            // scroll to
            if (typeof opts == 'number' && isTransformed)
            {
                var listId = $(this).attr('sb');
                return this;
            }
            else if (typeof opts == 'object' && isTransformed)
            {
                var listId = $(this).attr('sb');
                return this;
            }

            var nameClean = $(this).attr('name');

            if (typeof nameClean == 'undefined')
            {

                nameClean = 'sel-' + new Date().getTime();
                $(this).attr('name', nameClean);
            }

            nameClean = nameClean.replace(/([^a-zA-Z0-9_\-]*)/, '');

            var windowID = opts;
            if ((isTransformed && $('#' + $(this).attr('sb')).length))
            {
                //  Debug.log('Skip prepare select: ' + nameClean);
                return this;
            }


            if ($('#' + $(this).attr('sb')).length == 1)
            {
                if ($(this).next().hasClass('select-box'))
                {
                    var sb = $(this).attr('sb');
                    $(this).next().remove();
                    $('#' + sb).remove();
                }
            }


            // Cache the number of options
            var $this = $(this), hash = 's-' + new Date().getTime();


            // Hides the select element
            $this.addClass('inputS');
            $this.attr('sb', hash).on('change', function () {
                if ($(this).is(':disabled'))
                {
                    $(this).next().addClass('disabled');
                }
                else
                {
                    $(this).next().removeClass('disabled');
                }
            });

            var defaultVal = '';
            if (isMultiLined || isMultiSelect)
            {
                // list shadow
                var $list = $('<div />', {
                    'class': 'select-multilined-box',
                    boxname: nameClean,
                    win: windowID,
                    id: hash
                });

                var $listShadow = $('<div class="select-box-opts-container-inner scroll-content" />'); //  scrollabel
                var $listShadow2 = $('<div style="height:100%;display:block; overflow:hidden" />'); //  scrollabel
                // Insert an unordered list
                var $listOptions = $('<ul />', {
                    'class': 'select-box-options',
                    id: 'inner-' + hash
                });
                $listShadow.append($listOptions).appendTo($list);

                $list.insertAfter($this);
                var length = 0, idx = 0;

                var childs = $this.children(), childlength = childs.length;


                // Insert a list item into the unordered list for each select option
                // $this.children().each(function (x) {
                for (var x = 0; x< childlength; ++x) {

                    var node = childs[x];

                    if ($(node).text()) {

                        var isOptGroup = $(node).is('optgroup');
                        var isSelected = $(node).is(':selected');
                        length++;

                        if (isOptGroup)
                        {
                            var $li = $('<li>' ).addClass('optgroup');
                            $li.append('<em>' + $(node).attr('label') + '</em>');
                            $li.appendTo($listOptions);


							$(node ).attr('idx', idx );
							idx++;

                            $(node).find('option').each(function (y) {

                                var isSelected = $(this).is(':selected');
                                var li = $('<li />', {
                                    text: $(this).text(),
                                    rel: ($(this).val() ? $(this).val() : '_none_'),
									'idx': idx
                                }).addClass('sub');

								$(this ).attr('idx', idx );
								idx++;

                                if (isSelected && !isMultiSelect) {
                                    defaultVal = $(this).val();
                                    li.addClass('selected');
                                }
                                else if (isSelected && isMultiSelect) {
                                    defaultVal += (defaultVal ? ';;' : '') + $(this).val();
                                    li.addClass('selected');
                                }
                                li.appendTo($listOptions);
                            });
                        }
                        else
                        {
							$(node ).attr('idx',idx );

                            var li = $('<li />', {
                                text: $(node).text(),
                                rel: $(node).val(),
								idx: idx
                            });
							idx++;
                            li.prepend('<span></span>');
                            if (isSelected && !isMultiSelect) {
                                defaultVal = $(node).val();
                                li.addClass('selected');
                            }
                            else if (isSelected && isMultiSelect) {
                                defaultVal += (defaultVal ? ';;' : '') + $(node).val();
                                li.addClass('selected');
                            }
                            li.appendTo($listOptions);
                        }

                    }
                //});
                }


                if (isMultiSelect)
                {
                    $listOptions.addClass('isMultiSelect');
                }

                //$listOptions.height(22 * length);

                var height = isMultiLined * 22;
                $(this).attr('default', defaultVal ? defaultVal : '_none_');
                $list.height(height);
                // Cache the list items
                var $listItems = $listOptions.children('li:not(.optgroup)');

                // Hides the unordered list when a list item is clicked and updates the styled div to show the selected list item
                // Updates the select element to have the value of the equivalent option
                $listItems.click(function (e) {
					$( e.target ).parents('.select-multilined-box' ).addClass('focus').trigger('focus');

                    e.stopPropagation();




                    // skip optgroup
                    if ($(this).hasClass('optgroup'))
                    {
                        return;
                    }

					var index = (typeof $(this).attr('idx') != 'undefined' ? $(this).attr('idx') : $(this ).index());

                    if (!$listOptions.hasClass('isMultiSelect'))
                    {
                        $list.find('.selected').removeClass('selected');
                        $(this).addClass('selected');
                        $this.val($(this).attr('rel'));

                        $this.find('option').removeAttr('selected').prop('selected', false);
                        $this.find('[idx='+index+']').attr('selected', true).prop('selected', true);
                    }
                    else
                    {
                        $(this).toggleClass('selected');

                        if ($(this).hasClass('selected'))
                        {
                        //    $this.find('option[value="' + $(this).attr('rel') + '"]').attr('selected', false).prop('selected', false);
                            $this.find('option[idx='+index+']' ).attr('selected', true).prop('selected', true);
                        }
                        else
                        {
                         //   $this.find('option[value="' + $(this).attr('rel') + '"]').removeAttr('selected').prop('selected', false);
                            $this.find('option[idx='+index+']').removeAttr('selected').prop('selected', false).removeAttr('selected');
                        }
                    }


                    $this.trigger('change');
                });


                //$('#' + hash).nanoScroller({scrollContent: $('#' + hash).find('.select-box-opts-container-inner')});

                Tools.scrollBar($('#' + hash).find('div.select-box-opts-container-inner'), $listOptions.find('.selected'));

                setTimeout(function () {
                    $(window).trigger('resizescrollbar');
                }, 1000);

				$(document ).unbind('click.multiselect');
				$(document ).bind('click.multiselect', function(e) {
					if ( !$( e.target ).parents('div.select-box-opts-container-inner').length ) {
						$('div.select-multilined-box').removeClass('focus').trigger('blur');
					}
					else if ( $( e.target ).parents('div.select-box-opts-container-inner').length ) {
						$( e.target ).parents('div.select-multilined-box').addClass('focus').trigger('focus');
					}
				});


                return;
            }





            // Wrap the select element in a div
            var $sel = $('<div class="select-box"></div>').attr('listid', hash);
            selectBoxIds[hash] = true;

            if (width > 0) {
                // $sel.width(width + 30);
            }
            $sel.insertAfter($this);


            // Insert a styled div to sit over the top of the hidden select element

            $sel.append('<div class="select-box-label"></div>');
            $sel.append('<div class="select-box-arrow"><span></span></div>');

            // Cache the styled div
            var $styledSelect = $sel.find('div.select-box-label');
            $styledSelect.attr('list', hash);

            // Show the first select option in the styled div
            $styledSelect.text($this.children('option').eq(0).text());

            // list shadow
            var $list = $('<div />', {
                'class': 'select-box-opts-container',
                boxname: nameClean,
                win: windowID,
                id: hash
            });

            var $listShadow = $('<div class="select-box-opts-container-inner"/>');

            // Insert an unordered list
            var $listOptions = $('<ul />', {
                'class': 'select-box-options',
                id: 'inner-' + hash
            });

            $listShadow.append($listOptions).appendTo($list);

            if ($('#' +Config.get('fullscreenContainerId') ).length ) {
                $list.appendTo($('#'+Config.get('fullscreenContainerId')));
            }
            else {
                $list.appendTo($('body'));
            }

            if (!isMultiSelect) {

               // var cloned = $sel.clone(false);
               // $('body').append(cloned);
                //var span = $sel.find('.select-box-label');
                var tmpWidth = 0, idx = 0, selectedSet = false, children = $this.children(), childlength = children.length, selectedText = false;


                for ( var i = 0; i< childlength; ++i) {

                    var node = $(children[i]);


              //  children.each(function (i) {

					var w, text = node.text().trim();

					if (text) {
						var isSelected = node.is(':selected');
						var isOptGroup = node.is('optgroup');

						if (isOptGroup)
						{
                            node.attr('idx', i);
                            node.find('option').each(function () {

								var selected = $(this).is(':selected');

								if (selected && !selectedSet) {
									defaultVal = $(this).val();
									//$styledSelect.text( text );
									selectedText = text;
									selectedSet = true;
								}

								//$styledSelect.html($(this ).text());

								if ( (w = parseInt($styledSelect.width())) > tmpWidth ) {
									tmpWidth = w;
								}
							});

							$styledSelect.html('<em>' + node.attr('label') + '</em>');

							if ( (w = parseInt($styledSelect.width())) > tmpWidth ) {
								tmpWidth = w;
							}
						}
						else {


							if (isSelected && !selectedSet) {
								defaultVal = node.val();
								//$styledSelect.text( text );
								selectedText = text;
								selectedSet = true;
							}

							$styledSelect.html(text);

							if ( (w = parseInt($styledSelect.width())) > tmpWidth ) {
								tmpWidth = w;
							}
						}
					}


                    if (i + 1 >= childlength) {
                        if (tmpWidth > 0) {
                            $sel.width(tmpWidth+24);
                        }

						if (selectedText) {
							$styledSelect.text(selectedText);
						}
                       // cloned.remove();
                    }
                //});
                }
            }




            $(this).attr('default', defaultVal ? defaultVal : '_none_');
            $this.attr('default', defaultVal ? defaultVal : '_none_');

            // Cache the list items
            var idx = 0, $listItems = $listOptions.children('li:not(.optgroup)');
			var $childs = $this.children(), childlength = $childs.length;

            // Show the unordered list when the styled div is clicked (also hides it if the div is clicked again)
            $sel.click(function (e) {
				$( e.target ).parents('div.select-box').addClass('focus').trigger('focus');
                e.stopPropagation();

                if ($(this).hasClass('disabled'))
                {
                    return;
                }


				if (!$listOptions.find('li' ).length && childlength ) {
					//$sel.mask('warten...');

					// Insert a list item into the unordered list for each select option
					//$childs.each(function (x) {

                    for (var x = 0; x<childlength; ++x) {
                        var node = $($childs[x]);
						var isOptGroup = node.is('optgroup');
						var isSelected = node.is(':selected');
						var text = $($childs[x]).text().trim();
						if (text) {

							if (isOptGroup)
							{
								var $li = $('<li>').addClass('optgroup');
								$li.append('<em>' + node.attr('label') + '</em>');
								$li.appendTo($listOptions);
                                node.attr('idx',idx );
								idx++;

                                node.find('option').each(function (y) {

									$(this ).attr('idx',idx );
									var isSelected = $(this).is(':selected');
									var li = $('<li />', {
										text: $(this).text().trim(),
										rel: ($(this).val() ? $(this).val() : '_none_'),
										'idx': idx
									}).addClass('sub');


									li.prepend('<span></span>');

									if (isSelected) {
										//$styledSelect.text($(this).text());
										li.addClass('selected');
									}

									li.appendTo($listOptions);

									idx++;
								});
							}
							else
							{
                                node.attr('idx',idx );
								var li = $('<li />', {
									text: text,
									rel: node.val(),
									idx: idx
								});
								li.prepend('<span></span>');

								if (isSelected) {
									//$styledSelect.text($(this).text());
									li.addClass('selected');
								}

								li.appendTo($listOptions);
								idx++;
							}
						}
					//});
                    }

                    $listItems = $listOptions.children('li:not(.optgroup)');


					//$sel.unmask();


					// Hides the unordered list when a list item is clicked and updates the styled div to show the selected list item
					// Updates the select element to have the value of the equivalent option
					$listItems.click(function (e) {
						e.stopPropagation();

						// skip optgroup
						if ($(this).hasClass('optgroup'))
						{
							return;
						}

						var index = (typeof $(this).attr('idx') != 'undefined' ? $(this).attr('idx') : $(this ).index());

						$list.find('.selected').removeClass('selected');
						$(this).addClass('selected');

						$styledSelect.text($(this).text()).removeClass('active');
						$this.val($(this).attr('rel'));

						$this.find('option').removeAttr('selected').prop('selected', false);

						$this.find('[idx='+index+']').prop('selected', true).attr('selected', 'selected').get(0).selected = true;
						$this.trigger('change');

						$list.fadeOut(150, function () {
							$(this).hide().children(':first').height('').width('').find('ul:first').css({paddingRight: ''});

						});
						/* alert($this.val()); Uncomment this for demonstration! */
					});

				}










                $('.select-box.active').each(function () {
                    $(this).removeClass('active');
                    $('#' + $(this).attr('listid')).children(':first').css({height: '', width: ''});
                    $('#' + $(this).attr('listid')).hide().find('ul:first').css({paddingRight: ''});
                });

                $(this).addClass('active');

                var listid = $(this).attr('listid');
                var list = $('#' + listid);

                if (list.length == 0)
                {
                    // Debug.log('Selectbox Option Container not exists! ID:' + listid);
                }

                list.css({visibility: ''}).show();


                var $offset = $sel.offset(), left = parseInt($offset.left) + 10, top = parseInt($offset.top);
                var viewportHeight = parseInt($(window).height(), 10), selectHeight = parseInt($(this).outerHeight());
                var listHeight = parseInt(list.find('ul:first').outerHeight(true)), listWidth;

                if (list.attr('w') === undefined)
                {
                    list.attr('w', parseInt(list.width()));
                }

                listWidth = list.attr('w');

                // set max height
                var setHeight = listHeight;

                if (setHeight > 350)
                {
                    setHeight = 350;
                }

                if ((top + selectHeight + setHeight) > viewportHeight)
                {
                    if (setHeight === 350)
                    {
                        //   setHeight = (top < 350 && top > 100 ? top - 22 : setHeight - 10 - ( (top + selectHeight + setHeight) - viewportHeight) );
                    }

                    top = top + selectHeight - setHeight;
                }


                // set max width
                if (left + list.width() > $(window).width())
                {
                    var listWidth = list.width();
                    var l = $sel.offset().left;
                    left = (left - listWidth + $sel.outerWidth(true)) - 3;
                }


                var scrollTo = (list.find('.selected').length ? parseInt(list.find('.selected').position().top) : 0);
                list.children(':first').css({height: setHeight /*, width: listWidth*/});
                list.css({height: setHeight, left: left, top: top, visibility: '', display: 'none'}).toggle(0, function () {

                    // update scrollbar and scroll to selected item

                    $(this).find('.select-box-opts-container-inner').addClass('scroll-content');
                    $(this).nanoScroller({
                        scrollTo: scrollTo
                    });

                    setTimeout(function () {
                        $(window).trigger('resizescrollbar');
                    }, 100);

                    // Tools.scrollBar($(this).find('.select-box-opts-container-inner ul:first'), scrollTo);
                    // Tools.scrollBar($(this).find('.select-box-opts-container-inner ul:first'), scrollTo);
                });
            });

			$(document ).unbind('click.singleselect').bind('click.singleselect', function(e) {

				if ( !$( e.target ).parents('div.select-box').length )
				{
					$('div.select-box').removeClass('focus').trigger('blur');
				}

                if (!$(e.target).parents('div.select-box-opts-container').length ) {
                    $('div.select-box-opts-container').hide();
                }


				if ( $( e.target ).parents('div.select-box').length ) {
					$( e.target ).parents('div.select-box').addClass('focus').trigger('focus');
				}
			});

            // Hides the unordered list when clicking outside of it
            $(document).unbind('mouseup.cbk').bind('mouseup.cbk', function (e) {

                if (!$(e.target).parents('.pane').length && $(e.target).parents('.select-box-opts-container').length == 0 && $(e.target).parents('.select-box').length == 0)
                {

                    for (var hash in selectBoxIds) {
                        var box = $('div[listid='+ hash +']');


                        if (box && box.length && box.hasClass('active') ) {
                            box.removeClass('active').removeClass('focus').trigger('blur').children().removeClass('active');
                            $('#' + hash).fadeOut(150, function () {

                                $(this).hide().children(':first').height('').width('').find('ul:first').css({paddingRight: ''});
                            });
                        }
                    }

                    /*
                    $('div.select-box.active').each(function () {
                        var id = $(this).attr('listid');
                        $(this).removeClass('active').removeClass('focus').trigger('blur').children().removeClass('active');
                        $('#' + id).fadeOut(150, function () {

                            $(this).hide().children(':first').height('').width('').find('ul:first').css({paddingRight: ''});
                        });
                    });
                    */
                }
            });

        });
    };

})(jQuery);