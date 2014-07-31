/**
 * Created by marcel on 28.03.14.
 */


var Bootstraper = {
	validInputTypes: {'text': true, 'password': true, 'date': true, 'time': true},

	init: function(container, meta, hash) {
		var self = this;

		container.find('input[type=file],input[data-toggle="file"]' ).each(function(){


			if ($(this ).attr('type') == 'file') {
				$(this).dcmsFileinput( {
					tooltip: 'auto',
					position: 'top-right'
				} );
			}
			else {
				if ( hash ) {
					var input = $(this);
					Core.Tabs.addFileSelector( hash );


					$(this).dcmsFileinput( {
						tooltip: 'auto',
						position: 'top-right',
						onClick: function()
						{

							if ( !$('#content-' + hash).get(0 ).filemanVisible ) {
								Core.Tabs.toggleFileSelectorPanel(true);
							}

							if ( $('#content-' + hash).get(0 ).filemanVisible)
							{

								// change upload and click events
								var events = container.data( 'events' );
								if ( events && typeof events.onShowFileman != 'undefined' && events.onShowFileman.length > 0 ) {
									for ( var i = 0; i < events.onShowFileman.length; ++i ) {
										if ( typeof events.onShowFileman[i] == 'function' ) {
											events.onShowFileman[i]( container, input );
										}
									}
								}
							}
						}
					} );
				}
			}
		});


		container.find('textarea,input:not([type=file],[type=hidden])' ).each(function(){
			if (typeof self.validInputTypes[$(this ).attr('type')] !== 'undefined' || this.tagName.toLowerCase() == 'textarea') {

				$(this).dcmsInputTooltip( {
					position: 'top-right'
				});

				$(this).dcmsInputTrigger();

				if ($(this ).attr('type') == 'text' && $(this ).data('toggle') === 'spin') {
					$(this).dcmsInputSpin();
				}
			}
		});
	},
	destroy: function(container) {
		var self = this;

		container.find('input[type=file]' ).each(function(){
			$(this).dcmsFileinput('destroy');
		});

		container.find('textarea,input:not([type=file],[type=hidden])' ).each(function(){
			if (typeof self.validInputTypes[$(this ).attr('type')] !== 'undefined' || this.tagName.toLowerCase() == 'textarea') {

				$(this).dcmsInputTooltip('destroy');
				$(this).dcmsInputTrigger('destroy');
				$(this).dcmsInputSpin('destroy');
			}
		});
	}
};