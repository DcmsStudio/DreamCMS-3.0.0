jQuery.fn.liveSearch = function (conf) {
    var config = jQuery.extend({
        url:            'index.php?cp=search&q=', 
        id:                'jquery-live-search', 
        duration:        400, 
        typeDelay:        400,
        loadingClass:    'loading', 
        onSlideUp:        function () {}, 
		onAfterResultSet: function () {}
    }, conf);
	
	var liveSearch = $('#' + config.id);
	
	
	
    // Create live-search if it doesn't exist
    if (!liveSearch.length) {
        liveSearch = $('<div id="' + config.id + '"></div>')
                        .appendTo(document.body)
                        .hide()
                        .slideUp(0);
		// Close live-search when clicking outside it
		/*
		$(document.body).click(function(event) {
			var clicked = $(event.target);

			if (!(clicked.is('#' + config.id) || clicked.parents('#' + config.id).length || clicked.is('input'))) {
				liveSearch.slideUp(config.duration, function () {
					config.onSlideUp();
				});
			}
		});
		*/
    }
	
	liveSearch.hide();
	
	

	
    return this.each(function () {
        var input = $(this).attr('autocomplete', 'off');
		
		$(this).attr('onblur', '$("#'+ config.id +'").hide()');
		
		
		
		
		
		
        var liveSearchPaddingBorderHoriz = parseInt(liveSearch.css('paddingLeft'), 10) + parseInt(liveSearch.css('paddingRight'), 10) + parseInt(liveSearch.css('borderLeftWidth'), 10) + parseInt(liveSearch.css('borderRightWidth'), 10);

        // Re calculates live search's position
        var repositionLiveSearch = function () {
            var tmpOffset = input.offset();
            var inputDim  = {
                left:     tmpOffset.left, 
                top:      tmpOffset.top, 
                width:    input.outerWidth(), 
                height:   input.outerHeight()
            };

            inputDim.topPos        = inputDim.top + inputDim.height;
            inputDim.totalWidth    = inputDim.width - liveSearchPaddingBorderHoriz;

            liveSearch.css({
                position: 'absolute', 
                left:     tmpOffset.left + 'px!important', 
                //top:      inputDim.topPos + 'px!important',
                width:    inputDim.totalWidth + 'px'
            });
        };

        // Shows live-search for this input
        var showLiveSearch = function () {
            // Always reposition the live-search every time it is shown
            // in case user has resized browser-window or zoomed in or whatever
            repositionLiveSearch();

            // We need to bind a resize-event every time live search is shown
            // so it resizes based on the correct input element
            $(window).unbind('resize', repositionLiveSearch);
            $(window).bind('resize', repositionLiveSearch);

            liveSearch.slideDown(config.duration);
        };

        // Hides live-search for this input
        var hideLiveSearch = function () {
            liveSearch.slideUp(config.duration, function () {
                config.onSlideUp();
            });
        };

		// On focus, if the live-search is empty, perform an new search
		// If not, just slide it down. Only do this if there's something in the input
        input.focus(function () {
			if (this.value !== '') {
				// Perform a new search if there are no search results
				if (liveSearch.html() == '') {
					this.lastValue = '';
					input.keyup();
				}
				// If there are search results show live search
				else {
					// HACK: In case search field changes width onfocus
					setTimeout(showLiveSearch, 1);
				}
			}
        })		
		.keyup(function () {
			// Auto update live-search onkeyup
			// Don't update live-search if it's got the same value as last time
			if (this.value != this.lastValue) {
				input.addClass(config.loadingClass);

				var q = this.value;

				// Stop previous ajax-request
				if (this.timer) {
					clearTimeout(this.timer);
				}


				// Start a new ajax-request in X ms
				this.timer = setTimeout(function () {
					$.get(config.url + q, function (data) {
					
						input.removeClass(config.loadingClass);
						
						if(responseIsOk(data)) {
							// Show live-search if results and search-term aren't empty
							if (data.result.length && q.length) {
								liveSearch.empty().append(data.result);
								showLiveSearch();
								config.onAfterResultSet();
							}
							else {
								hideLiveSearch();
							}
						}
						else
						{
							hideLiveSearch();
							alert(data.msg);
						}
					}, 'json');
				}, config.typeDelay);

				this.lastValue = this.value;
			}
		});
    });
};