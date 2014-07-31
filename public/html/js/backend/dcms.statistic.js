var baseChart = 'days';
var baseChartType = 'bar';
var modes = {
	base: {
		Type: 'days',
		initBar: 'bar',
		barMode: 'bar'
	},
	browsers: {
		initBar: 'barup',
		barMode: 'barup'
	},
	os: {
		initBar: 'barup',
		barMode: 'barup'
	},
	spiders: {
		initBar: 'barup',
		barMode: 'barup'
	},
	countrys: {
		initBar: 'barup',
		barMode: 'barup'
	},
	screensize: {
		initBar: 'barup',
		barMode: 'barup'
	}
};
var detailChartMode = 'barup';
var _jqPlot = false;
var jqPlotDataCache = [], BaseClone;

function setJqPlotData( key, data )
{
	jqPlotDataCache[key] = data;
}

function registerStatistic()
{

	var win = $( '#' + Win.windowID );

	if ( $.jqplot && jQuery.fn.jquery.replace( /\./g, '' ) > 190 ) {
		_jqPlot = true;
		BaseClone = win.find( '#base-chart' ).get( 0 ).innerHTML;
	}
	else {
		console.log( 'Use static statistic graphs (Jquery: ' + jQuery.fn.jquery + ' ' + jQuery.fn.jquery.replace( /\./g, '' ) + ') ' );
	}

	var hash = false, w = win;
	var v = w.find( '#show-advanced' ).find( 'option:selected' ).val();
	if ( !Desktop.isWindowSkin ) {

		hash = Win.windowID.replace( 'tab-', '' ).replace( 'content-', '' );
		w = $( '#content-tabs-' + hash );
		v = $( '#buttons-' + hash ).find( '#show-advanced' ).find( 'option:selected' ).val();
	}



	win.find( '#adv-os,#adv-browser,#adv-country,#adv-spiders,#adv-screensize,#adv-refferer' ).hide();
	win.find( '#adv-' + v ).addClass( 'st-view' ).show();

	w.find( '#tab_1' ).unbind( 'click.stat' ).bind( 'click.stat', function ( e )
	{
		var v = (hash ? $( '#buttons-' + hash ).find( '#show-advanced' ).find( 'option:selected' ).val() : w.find( '#show-advanced' ).find( 'option:selected' ).val());

		if ( !hash ) {
			win.find( '#advanced-options' ).show();
		}
		else {
			$( '#buttons-' + hash ).find( '#advanced-options' ).show();
		}

		win.find( '#tc0' ).hide();
		win.find( '#tc1').show();


		if ( !Desktop.isWindowSkin && hash ) {
			$( '#buttons-' + hash).find( '#advanced-options' ).show();
			v = $( '#buttons-' + hash ).find( '#show-advanced' ).find( 'option:selected' ).val();
		}

		Win.redrawWindowHeight( false, true );

		switch ( v ) {

			case 'country':
			default:
				getCountryChart();
				break;
			case 'browser':
				getBrowserChart();
				break;
			case 'os':
				getOSChart();
				break;
			case 'spiders':
				getRobotsChart();
				break;
			case 'screensize':
				getScreensizeChart();
				break;
		}

		if ( !v ) {
			getCountryChart();
		}
	} );

	w.find( '#tab_0' ).unbind( 'click.stat' ).bind( 'click.stat', function ( e )
	{
		win.find( '#tc0' ).show();
		win.find( '#tc1').hide();

		$( '#advanced-options', win ).hide();
		if ( !Desktop.isWindowSkin && hash ) {
			$( '#buttons-' + hash ).find( '#advanced-options' ).hide();
		}
		Win.redrawWindowHeight( false, true );
		getBaseChart();
	} );

	if ( Desktop.isWindowSkin ) {

		w.find( '#show-advanced' ).unbind( 'change.stat' );
		w.find( '#show-advanced' ).bind( 'change.stat', function ()
		{
			var v = $( this ).find( 'option:selected' ).val();

			win.find( '.st-view' ).hide( 0, function ()
			{
				$( this ).removeClass( 'st-view' );
				win.find( '#adv-' + v ).addClass( 'st-view' ).show();

				switch ( v ) {
					case 'country':
					default:
						getCountryChart();
						break;
					case 'browser':
						getBrowserChart();
						break;
					case 'os':
						getOSChart();
						break;
					case 'spiders':
						getRobotsChart();
						break;
					case 'screensize':
						getScreensizeChart();
						break;
				}
			} );
		} );

		w.find( '#stat-by-date' ).unbind( 'click.stat' ).click( function ( e )
		{

			var postvars = w.find( '#stat-by-dateForm' ).serialize();
			var atab = w.find( '.tabcontainer li.actTab' );
			var idx = $( atab ).attr( 'id' ).replace( 'tab_', '' );
			var container = win.find( '#tc' + idx ).attr( 'class' );

			container = container.replace( /^([a-zA-Z0-9_]+).*/g, '$1' );

			//  $('#tc'+idx).mask('Lade '+ atab.text().trim() +'...');
			if (typeof postvars.token == 'undefined' ) {
				postvars.token = Config.get('token');
			}
			$.post( 'admin.php', postvars + '&ajax=1&block=' + container, function ( data )
			{
				// $('#tc'+idx).unmask();

				if ( Tools.responseIsOk( data ) ) {

					win.find( '.chart-view-type' ).unbind( 'click.stat' );
					win.find( '.statdata' ).empty().append( data.html_statdata );
					win.find( '.advanced .data' ).empty().append( data.html_advanced );

					var v = $( '#show-advanced option:selected' ).val();
					win.find( '#adv-os,#adv-browser,#adv-country,#adv-spiders,#adv-screensize,#adv-refferer' ).hide();
					$( '#adv-' + v ).addClass( 'st-view' ).show();
					Win.redrawWindowHeight( false, true );

					_registerChartButtons();

					switch ( v ) {
						case 'country':
						default:
							getCountryChart();
							break;
						case 'browser':
							getBrowserChart();
							break;
						case 'os':
							getOSChart();
							break;
						case 'spiders':
							getRobotsChart();
							break;
						case 'screensize':
							getScreensizeChart();
							break;
					}

				}
				else {
					alert( 'Error Message: ' + data.msg );
				}
			}, 'json' );
		} );

	}
	else {

		$( '#buttons-' + hash ).find( '#show-advanced' ).unbind( 'change.stat' );
		$( '#buttons-' + hash ).find( '#show-advanced' ).bind( 'change.stat', function ()
		{
			var v = $( this ).find( 'option:selected' ).val();

			win.find( '.st-view' ).hide( 0, function ()
			{
				$( this ).removeClass( 'st-view' );
				win.find( '#adv-' + v ).addClass( 'st-view' ).show();

				switch ( v ) {
					case 'browser':
						getBrowserChart();
						break;
					case 'os':
						getOSChart();
						break;
					case 'spiders':
						getRobotsChart();
						break;
					case 'screensize':
						getScreensizeChart();
						break;
					case 'country':
					default:
						getCountryChart();
						break;
				}
			} );
		} );

		$( '#buttons-' + hash ).find( '#stat-by-date' ).unbind( 'click.stat' ).click( function ( e )
		{

			var postvars = $( '#buttons-' + hash ).find( '#stat-by-dateForm' ).serialize();
			var atab = w.find( '.tabcontainer li.actTab' );
			var idx = $( atab ).attr( 'id' ).replace( 'tab_', '' );
			var container = win.find( '#tc' + idx ).attr( 'class' );

			container = container.replace( /^([a-zA-Z0-9_]+).*/g, '$1' );

			//  $('#tc'+idx).mask('Lade '+ atab.text().trim() +'...');
			if (typeof postvars.token == 'undefined' ) {
				postvars.token = Config.get('token');
			}
			$.post( 'admin.php', postvars + '&ajax=1&block=' + container, function ( data )
			{
				// $('#tc'+idx).unmask();

				if ( Tools.responseIsOk( data ) ) {

					win.find( '.chart-view-type' ).unbind( 'click.stat' );
					win.find( '.statdata' ).empty().append( data.html_statdata );
					win.find( '.advanced .data' ).empty().append( data.html_advanced );

					var v = $( '#show-advanced option:selected' ).val();
					win.find( '#adv-os,#adv-browser,#adv-country,#adv-spiders,#adv-screensize,#adv-refferer').hide();
					$( '#adv-' + v ).addClass( 'st-view' ).show();
					Win.redrawWindowHeight( false, true );

					_registerChartButtons();

					switch ( v ) {
						case 'country':
						default:
							getCountryChart();
							break;
						case 'browser':
							getBrowserChart();
							break;
						case 'os':
							getOSChart();
							break;
						case 'spiders':
							getRobotsChart();
							break;
						case 'screensize':
							getScreensizeChart();
							break;
					}

				}
				else {
					alert( 'Error Message: ' + data.msg );
				}
			}, 'json' );
		} );
	}

	setTimeout( function ()
	{
	//	$( '#' + Win.windowID ).find( '#tc1' ).show();
		_registerChartButtons();
	//	$( '#' + Win.windowID ).find( '#tc1' ).hide()
	}, 350 );
}

function createMap()
{
	var win = $( '#' + Win.windowID );
	var hash = false, w = win;
	if ( !Desktop.isWindowSkin ) {
		hash = Win.windowID.replace( 'tab-', '' ).replace( 'content-', '' );
		w = $( '#content-tabs-' + hash );
	}

	var postvars = $( '#stat-by-dateForm' ).serialize();
	var width = parseInt( win.find( '#base-chart' ).innerWidth() );
	var height = parseInt( win.find( '#base-chart' ).innerHeight() );


	win.find( '#worldmap-chart' ).parent().mask('loading...');
	if (typeof postvars.token == 'undefined' ) {
		postvars.token = Config.get('token');
	}
	$.post( 'admin.php', postvars + '&action=worldmap&width=' + width + '&height=' + height, function ( data )
	{
		//   $('#base-chart').unmask();
		if ( Tools.responseIsOk( data ) ) {

			win.find( '#worldmap-chart' ).attr( 'oldheight', $( '#base-chart' ).height() );
			win.find( '#worldmap-chart' ).css( {
				height: data.height + 2,
				opacity: '0'
			} ).show().empty().append( data.worldmap )


			setTimeout( function ()
			{
				win.find( '#worldmap-chart' ).animate({opacity:1}, {duration: 300, complete: function() {
					$(this).parent().unmask();
					Win.redrawWindowHeight( false, true );
				}});

				win.find( '#worldmap-chart' ).find( '.mapPin' ).each( function ()
				{
					$( this ).hover( function ()
					{
						Piwik_Tooltip.show( $( this ).attr( 'data' ) );
					}, function ()
					{
						Piwik_Tooltip.hide();
					} );
				} );



			}, 10 );

		}
		else {
			alert( 'Error: ' + data.msg );
		}
	}, 'json' );
}

function _registerChartButtons()
{
	var win = $( '#' + Win.windowID );

	var hash = false, w = win;
	if ( !Desktop.isWindowSkin ) {
		hash = Win.windowID.replace( 'tab-', '' ).replace( 'content-', '' );
		w = $( '#content-tabs-' + hash );
	}

	if ( Desktop.isWIndowSkin ) {
		$( '#' + Win.windowID ).data( 'WindowManager' ).set( 'onResizeStop', function ()
		{
			if ( $( '#' + Win.windowID ).find( '#map' ).length ) {
				createMap();
			}
		} );
	}

	win.find( '#tc0' ).find( '.chart-view-type' ).unbind( 'click' ).bind( 'click', function (){
		if ( $( this ).attr( 'id' ) == 'worldmap' ) {
			win.find( '#base-chart' ).find( '.base-jqplot-chart:visible' ).animate({
				opacity: '0'
			}, {
				duration: 300,
				complete: function() {
					$(this ).hide().css({opacity: 1});
					createMap();
				}
			});
		}
		else {
			win.find( '#base-chart' ).css( 'height', '' );
			modes.base.Type = $( this ).attr( 'id' );

			if ( !w.find( '.base-jqplot-chart' ).length ) {
				//    $('#' + Win.windowID).find('#base-chart').empty().append(BaseClone);
			}

			win.find( '#base-chart' ).find( '.base-jqplot-chart:visible' ).animate({
				opacity: '0'
			}, {
				duration: 300,
				complete: function() {
					$(this ).hide().css({opacity: 1});
					win.find( '#base-chart' ).find( '#' + modes.base.Type + '-chart' ).show().empty();
					getBaseChart();
					setTimeout( function (){
						Win.redrawWindowHeight( false, true );
					}, 100 );
				}
			});
		}
	} );

	win.find( '.chart-mode' ).each( function (){
		var chartID = $( this ).attr( 'rel' );

		if ( chartID ) {
			switch ( chartID ) {
				case 'base-chart':
					modes.base.barMode = modes.base.initBar;
					setActiveChartType( 'base-chart', modes.base.barMode, win );
					break;
				case 'spiders-chart':
					modes.spiders.barMode = modes.spiders.initBar;
					setActiveChartType( 'spiders-chart', modes.spiders.barMode, win );
					break;

				case 'os-chart':
					modes.os.barMode = modes.os.initBar;
					setActiveChartType( 'os-chart', modes.os.barMode, win );
					break;

				case 'browsers-chart':
					modes.browsers.barMode = modes.browsers.initBar;
					setActiveChartType( 'browsers-chart', modes.browsers.barMode, win );
					break;

				case 'countrys-chart':
					modes.countrys.barMode = modes.countrys.initBar;
					setActiveChartType( 'countrys-chart', modes.countrys.barMode, win );
					break;
				case 'screensize-chart':
					modes.screensize.barMode = modes.screensize.initBar;
					setActiveChartType( 'screensize-chart', modes.screensize.barMode, win );
					break;
			}
		}

	} );

	win.find( 'span.graph-type' ).unbind( 'click.stat' ).bind( 'click.stat', function ()
	{
		var chartID = $( this ).parents( 'div:first' ).attr( 'rel' ), bartype = $( this ).attr( 'id' );

		switch ( chartID ) {
			case 'base-chart':

				modes.base.barMode = bartype;
				modes.base.initBar = bartype;
				setActiveChartType( chartID, bartype, win );
				getBaseChart();
				break;

			case 'spiders-chart':

				modes.spiders.barMode = bartype;
				setActiveChartType( chartID, bartype, win );
				getRobotsChart();
				break;

			case 'os-chart':
				modes.os.barMode = bartype;
				setActiveChartType( chartID, bartype, win );
				getOSChart();

				break;

			case 'browsers-chart':

				modes.browsers.barMode = bartype;
				setActiveChartType( chartID, bartype, win );
				getBrowserChart();
				break;
			case 'countrys-chart':

				modes.countrys.barMode = bartype;
				setActiveChartType( chartID, bartype, win );
				getCountryChart();
				break;

			case 'screensize-chart':
				modes.screensize.barMode = bartype;
				setActiveChartType( chartID, bartype, win );
				getScreensizeChart();
				break;
		}

		setTimeout( function ()
		{
			Win.redrawWindowHeight( false, true );
		}, 300 );

	} );

	getCountryChart();
	getRobotsChart();
	getOSChart();
	getBrowserChart();
	getBaseChart();


	Win.redrawWindowHeight( false, true );

}


function setActiveChartType( chartID, bartype, win ){
	win.find( 'div[rel="' + chartID +'"]' ).find( '.active-chartmode' ).removeClass( 'active-chartmode' );
	win.find( 'div[rel="' + chartID +'"]' ).find( '#'+ bartype).addClass( 'active-chartmode' );
}

function getMonthYear()
{
	var ret = '';
	if ( $( '#select-year' ).find( 'option:selected' ).val() > 0 ) {
		ret += '&year=' + $( '#select-year' ).find( 'option:selected' ).val();
	}
	if ( $( '#select-month' ).find( 'option:selected' ).val() > 0 ) {
		ret += '&month=' + $( '#select-month' ).find( 'option:selected' ).val();
	}
	return ret;
}

function getCountryChart()
{
	var mode = modes.countrys;
	if ( _jqPlot ) {
		generateJqplot( 'countrys', mode.barMode );
		return;
	}

	var width = $( '#countrys-chart' ).width() - 3;
	var height = $( '#countrys-chart' ).parents( '.st-view' ).find( 'table:first' ).height() - 8;
	if ( height > 0 ) {
		if ( height < 150 ) {
			height = 450;
		}

		if ( height > 600 ) {
			height = 600;
		}

		$( '#countrys-chart' ).css( {
			height: height
		} );
		$( '#countrys-chart' ).empty();
		//   $('#countrys-chart').parent().mask('lade Browser Chart...');

		var url = 'admin.php?adm=statistic&action=getchart&type=countrys&charttype=' + mode.barMode + '&width=' + width + '&height=' + height;
		url = url + '&_t=' + new Date().getTime() + getMonthYear();

		$( '#countrys-chart' ).append( $( "<img>", {
			src: url,
			width: '100%',
			height: '100%'
		} ).load( function ()
			{
				//         $('#countrys-chart').parent().unmask();

			} ) );
	}
	else {
		$( '#countrys-chart' ).append( 'Keine Daten vorhanden' );
	}
}

function getBrowserChart()
{
	var mode = modes.browsers;
	if ( _jqPlot ) {
		generateJqplot( 'browsers', mode.barMode );
		return;
	}

	var width = $( '#browsers-chart' ).width() - 3;
	var height = $( '#browsers-chart' ).parents( '.st-view' ).find( 'table:first' ).height() - 8;
	if ( height > 0 ) {

		if ( height < 150 ) {
			height = 450;
		}

		if ( height > 600 ) {
			height = 600;
		}

		$( '#browsers-chart' ).css( {
			height: height
		} );
		$( '#browsers-chart' ).empty();
		//     $('#browsers-chart').parent().mask('lade Browser Chart...');

		var url = 'admin.php?adm=statistic&action=getchart&type=browsers&charttype=' + mode.barMode + '&width=' + width + '&height=' + height;
		url = url + '&_t=' + new Date().getTime() + getMonthYear();

		$( '#browsers-chart' ).append( $( "<img>", {
			src: url,
			width: '100%',
			height: '100%'
		} ).load( function ()
			{
				//         $('#browsers-chart').parent().unmask();
			} ) );
	}
	else {
		$( '#browsers-chart' ).append( 'Keine Daten vorhanden' );
	}
}

function getOSChart()
{
	var mode = modes.os;
	if ( _jqPlot ) {
		generateJqplot( 'os', mode.barMode );
		return;
	}

	var width = $( '#os-chart' ).width() - 3;
	var height = $( '#os-chart' ).parents( '.st-view' ).find( 'table:first' ).height() - 8;
	if ( height > 0 ) {
		if ( height < 150 ) {
			height = 450;
		}

		if ( height > 600 ) {
			height = 600;
		}

		$( '#os-chart' ).css( {
			height: height
		} );
		$( '#os-chart' ).empty();
		//   $('#os-chart').parent().mask('lade OS Charts...');
		var url = 'admin.php?adm=statistic&action=getchart&type=os&charttype=' + mode.barMode + '&width=' + width + '&height=' + height;
		url = url + '&_t=' + new Date().getTime() + getMonthYear();

		$( '#os-chart' ).append( $( "<img>", {
			src: url,
			width: '100%',
			height: '100%'
		} ).load( function ()
			{
				//        $('#os-chart').parent().unmask();
			} ) );
	}
	else {
		$( '#os-chart' ).append( 'Keine Daten vorhanden' );
	}
}

function getRobotsChart()
{
	var mode = modes.spiders;
	if ( _jqPlot ) {
		generateJqplot( 'spiders', mode.barMode );
		return;
	}

	var width = $( '#spiders-chart' ).width() - 3;
	var height = $( '#spiders-chart' ).parents( '.st-view' ).find( 'table:first' ).height() - 8;
	if ( height > 0 ) {
		if ( height < 150 ) {
			height = 450;
		}

		if ( height > 600 ) {
			height = 600;
		}

		$( '#spiders-chart' ).css( {
			height: height
		} );
		$( '#spiders-chart' ).empty();
		//       $('#spiders-chart').parent().mask('lade Spider Charts...');
		var url = 'admin.php?adm=statistic&action=getchart&type=spiders&charttype=' + mode.barMode + '&width=' + width + '&height=' + height;
		url = url + '&_t=' + new Date().getTime() + getMonthYear();

		$( '#spiders-chart' ).append( $( "<img>", {
			src: url,
			width: '100%',
			height: '100%'
		} ).load( function ()
			{
				//         $('#spiders-chart').parent().unmask();
			} ) );
	}
	else {
		$( '#spiders-chart' ).append( 'Keine Daten vorhanden' );
	}
}

function getScreensizeChart()
{
	var mode = modes.screensize;

	if ( _jqPlot ) {
		generateJqplot( 'screensize', mode.barMode );
		return;
	}

	var width = $( '#screensize-chart' ).width() - 3;
	var height = $( '#screensize-chart' ).parents( '.st-view' ).find( 'table:first' ).height() - 8;
	if ( height > 0 ) {
		if ( height < 150 ) {
			height = 450;
		}

		if ( height > 600 ) {
			height = 600;
		}

		$( '#screensize-chart' ).css( {
			height: height
		} );
		$( '#screensize-chart' ).empty();
		//       $('#spiders-chart').parent().mask('lade Spider Charts...');
		var url = 'admin.php?adm=statistic&action=getchart&type=screensize&charttype=' + mode.barMode + '&width=' + width + '&height=' + height;
		url = url + '&_t=' + new Date().getTime() + getMonthYear();

		$( '#screensize-chart' ).append( $( "<img>", {
			src: url,
			width: '100%',
			height: '100%'
		} ).load( function ()
			{
				//         $('#spiders-chart').parent().unmask();
			} ) );
	}
	else {
		$( '#screensize-chart' ).append( 'Keine Daten vorhanden' );
	}
}

function getBaseChart()
{
	var mode = modes.base;

	if ( _jqPlot ) {
		generateJqplot( mode.Type, mode.barMode );
		return;
	}

	var width = $( '#base-chart' ).width() - 3;
	var height = 600;
	$( '#base-chart' ).css( {
		height: height + 'px'
	} );
	//   $('#base-chart').parent().mask('lade Chart...');
	//$('#base-chart').empty().append($("<img>", {src: backendImagePath  + 'loading.gif'}));

	var url = 'admin.php?adm=statistic&action=getchart&type=' + mode.Type + '&charttype=' + mode.barMode + '&width=' + width + '&height=' + height;
	url = url + '&_t=' + new Date().getTime() + getMonthYear();

	$( '#base-chart' ).empty().append( $( "<img>", {
		src: url,
		width: '100%',
		height: '100%'
	} ).load( function ()
		{
			//      $('#base-chart').parent().unmask();
		} ) );

}

/**
 * Piwik - Web Analytics
 *
 * Adapter for jqplot
 *
 * @link http://www.jqplot.com
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Constructor function
 * @param the data that would be passed to open flash chart
 */


function JQPlot( data, dataTableId )
{
	this.init( data, dataTableId );
}

JQPlot.prototype = {
	/** Generic init function */
	init: function ( data, dataTableId )
	{
		this.data = null;
		this.dataTableId = dataTableId;
		this.originalData = data;
		this.data = data.data;
		this.piedata = null;

		defaultParams = {};
		defaultParams.grid = {
			drawGridLines: false,
			background: '#fff',
			borderColor: '#ff0',
			borderWidth: 0,
			shadow: false
		};

		defaultParams.title = {
			show: false
		};

		defaultParams.axesDefaults = {
			pad: 1.0,
			lineRenderer: $.jqplot.LineRenderer,
			tickRenderer: $.jqplot.CanvasAxisTickRenderer,
			tickOptions: {
				showMark: false,
				fontSize: '11px',
				fontFamily: 'Arial',
				angle: -30
			}
		};

		// noDataIndicator option.  If no data is passed in on plot creation,
		// A div will be placed roughly in the center of the plot.  Whatever
		// text or html is given in the "indicator" option will be rendered 
		// inside the div.
		defaultParams.noDataIndicator = {
			show: true,
			// Here, an animated gif image is rendered with some loading text.
			indicator: '<span>Loading Data...</span>',
			// IMPORTANT: Have axes options set since y2axis is off by default
			// and the yaxis is on be default.  This is necessary due to the way
			// plots are constructed from data and we don't have any data
			// when using the "noDataIndicator".
			axes: {
				xaxis: {
					min: 0,
					max: 5,
					tickInterval: 1,
					showTicks: false
				},
				yaxis: {
					show: false
				},
				y2axis: {
					show: true,
					min: 0,
					max: 8,
					tickInterval: 2,
					showTicks: false
				}
			}
		};

		this.params = $.extend( true, {}, defaultParams, data.params );

		this.tooltip = data.tooltip;
		this.seriesPicker = data.seriesPicker;

		if ( typeof this.params.axes.yaxis == 'undefined' ) {
			this.params.axes.yaxis = {};
		}
		if ( typeof this.params.axes.yaxis.tickOptions == 'undefined' ) {
			this.params.yaxis.tickOptions = {
				formatString: '%d'
			};
		}
	},
	/** Generic render function */
	render: function ( type, targetDivId, lang )
	{
		// preapare the appropriate chart type
		switch ( type ) {
			case 'evolution':
				this.prepareEvolutionChart( targetDivId, lang );
				break;
			case 'bar':
				this.prepareBarChart( targetDivId, lang );
				break;
			case 'barh':
				this.prepareBarHChart( targetDivId, lang );
				break;
			case 'pie':
				this.preparePieChart( targetDivId, lang );
				break;
			default:
				return;
		}

		// handle replot
		// this has be bound before the check for an empty graph.
		// otherwise clicking on sparklines won't work anymore after an empty
		// report has been displayed.
		var self = this;
		var target = $( '#' + targetDivId )
			.on( 'replot', function ( e, data )
			{
				target.trigger( 'piwikDestroyPlot' );
				if ( target.data( 'oldHeight' ) > 0 ) {
					// handle replot after empty report
					target.height( target.data( 'oldHeight' ) );
					target.data( 'oldHeight', 0 );
					this.innerHTML = '';
				}

				(new JQPlot( data, self.dataTableId )).render( type, targetDivId, lang );
			} );

		// show loading
		target.bind( 'showLoading', function ()
		{
			var loading = $( document.createElement( 'div' ) ).addClass( 'jqplot-loading' );
			loading.css( {
				width: target.innerWidth() + 'px',
				height: target.innerHeight() + 'px',
				opacity: 0
			} );
			target.prepend( loading );
			loading.css( {opacity: .7} );
		} );

		// change series
		target.bind( 'changeColumns', function ( e, columns )
		{
			target.trigger( 'changeSeries', [columns, []] );
		} );

		target.bind( 'changeSeries', function ( e, columns, rows )
		{
			target.trigger( 'showLoading' );
			if ( typeof columns == 'string' ) {
				columns = columns.split( ',' );
			}
			if ( typeof rows == 'undefined' ) {
				rows = [];
			}
			else if ( typeof rows == 'string' ) {
				rows = rows.split( ',' );
			}
			var dataTable = dataTables[self.dataTableId];
			dataTable.param.columns = columns.join( ',' );
			dataTable.param.rows = rows.join( ',' );
			delete dataTable.param.filter_limit;
			delete dataTable.param.totalRows;
			if ( dataTable.param.filter_sort_column != 'label' ) {
				dataTable.param.filter_sort_column = columns[0];
			}
			dataTable.param.disable_generic_filters = '0';
			dataTable.reloadAjaxDataTable( false );
		} );

		// this case happens when there is no data for a line chart
		if ( this.data.length == 0 ) {
			target.addClass( 'pk-emptyGraph' );
			target.data( 'oldHeight', target.height() );
			target.css( 'height', 'auto' ).html( 'keine Daten vorhanden' );
			return;
		}

		var data = this.data;
		if ( type === 'pie' ) {
			data = this.piedata;
		}

		if ( typeof data != 'object' || data.length == 0 ) {
			data = [];
		}

		// create jqplot chart
		try {
			var plot = $.jqplot( targetDivId, data, this.params );
		} catch ( e ) {
			// this is thrown when refreshing piwik in the browser
			if ( e != "No plot target specified" ) {
				throw e;
			}
		}

		// bind tooltip
		var self = this;
		var lastTick = false;
		target.on( 'jqplotDataHighlight', function ( e, s, i, d )
		{
			if ( type == 'bar' ) {
				self.showBarChartTooltip( s, i );
			} else if ( type == 'pie' ) {
				self.showPieChartTooltip( i );
			}
			else if ( type === 'evolution' ) {

			}
		} )
			.on( 'jqplotDataUnhighlight', function ( e, s, i, d )
			{
				if ( type != 'evolution' ) {
					self.hideTooltip();
				}
			} );
		if ( type === 'evolution' ) {
			target.on( 'jqplotDataMouseOver',function ( e, s, i, d )
			{
				lastTick = i;
				self.showEvolutionChartTooltip( i );
			} ).on( 'jqplotMouseLeave', function ( e, s, i, d )
				{
					self.hideTooltip();
					$( this ).css( 'cursor', 'default' );
				} );
		}
		// handle window resize
		var plotWidth = target.outerWidth();
		var timeout = false;
		target.on( 'resizeGraph', function ()
		{
			var width = target.outerWidth();
			if ( width > 0 && Math.abs( plotWidth - width ) >= 5 ) {
				plotWidth = width;
				target.trigger( 'piwikDestroyPlot' );
				(new JQPlot( self.originalData, self.dataTableId ))
					.render( type, targetDivId, lang );
			}
		} );
		var resizeListener = function ()
		{
			if ( timeout ) {
				window.clearTimeout( timeout );
			}
			timeout = window.setTimeout( function ()
			{
				target.trigger( 'resizeGraph' );
			}, 300 );
		};

		$( '#' + Win.windowID ).on( 'resize', resizeListener );
		$( window ).on( 'resize', resizeListener );

		// export as image
		target.on( 'piwikExportAsImage', function ( e )
		{
			self.exportAsImage( target, lang );
		} );

		// manage resources
		target.on( 'piwikDestroyPlot', function ()
		{
			$( window ).off( 'resize', resizeListener );
			plot.destroy();
			for ( var i = 0; i < $.jqplot.visiblePlots.length; i++ ) {
				if ( $.jqplot.visiblePlots[i] == plot ) {
					$.jqplot.visiblePlots[i] = null;
				}
			}
			$( this ).off();
		} );


		target.animate({
			opacity: 1
		}, {
			duration: 300
		});

		if ( typeof $.jqplot.visiblePlots == 'undefined' ) {
			$.jqplot.visiblePlots = [];
			$( 'ul.nav' ).on( 'piwikSwitchPage', function ()
			{
				for ( var i = 0; i < $.jqplot.visiblePlots.length; i++ ) {
					if ( $.jqplot.visiblePlots[i] == null ) {
						continue;
					}
					$.jqplot.visiblePlots[i].destroy();
				}
				$.jqplot.visiblePlots = [];
			} );
		}

		if ( typeof plot != 'undefined' ) {
			$.jqplot.visiblePlots.push( plot );
		}
	},
	/** Export the chart as an image */
	exportAsImage: function ( container, lang )
	{
		var exportCanvas = document.createElement( 'canvas' );
		exportCanvas.width = container.width();
		exportCanvas.height = container.height();

		if ( !exportCanvas.getContext ) {
			alert( "Sorry, not supported in your browser. Please upgrade your browser :)" );
			return;
		}
		var exportCtx = exportCanvas.getContext( '2d' );

		var canvases = container.find( 'canvas' );

		for ( var i = 0; i < canvases.length; i++ ) {
			var canvas = canvases.eq( i );
			var position = canvas.position();
			var parent = canvas.parent();
			if ( parent.hasClass( 'jqplot-axis' ) ) {
				var addPosition = parent.position();
				position.left += addPosition.left;
				position.top += addPosition.top + parseInt( parent.css( 'marginTop' ), 10 );
			}
			exportCtx.drawImage( canvas[0], Math.round( position.left ), Math.round( position.top ) );
		}

		var exported = exportCanvas.toDataURL( "image/png" );

		var img = document.createElement( 'img' );
		img.src = exported;

		img = $( img ).css( {
			width: exportCanvas.width + 'px',
			height: exportCanvas.height + 'px'
		} );

		$( document.createElement( 'div' ) )
			.append( '<div style="font-size: 13px; margin-bottom: 10px;">'
				+ lang.exportText + '</div>' ).append( $( img ) )
			.dialog( {
				title: lang.exportTitle,
				modal: true,
				width: 'auto',
				position: ['center', 'center'],
				resizable: false,
				autoOpen: true,
				close: function ( event, ui )
				{
					$( this ).dialog( "destroy" ).remove();
				}
			} );
	},
	// ------------------------------------------------------------
	//  EVOLUTION CHART
	// ------------------------------------------------------------

	prepareEvolutionChart: function ( targetDivId, lang )
	{

		var tmp = this.params.axes.xaxis;

		this.setYTicks();
		//  this.addSeriesPicker(targetDivId, lang);
		options = {
			pad: 1.0,
			renderer: $.jqplot.CategoryAxisRenderer,
			tickOptions: {
				showGridline: false,
				startAngle: 35
			}

		};

		this.params.axes.xaxis = $.extend( {}, options, this.params.axes.xaxis );

		//    this.params = $.extend({}, axes, this.params);

		this.params.seriesDefaults = {
			lineWidth: 1,
			markerOptions: {
				style: "filledCircle",
				size: 6,
				shadow: false
			}
		};

		this.params.piwikTicks = {
			showTicks: true,
			showGrid: false,
			showHighlight: true
		};

		this.params.axes.xaxis.tickOptions = {
			showGridline: true,
			angle: -45
		};

		/*
		 var self = this;
		 var lastTick = false;

		 $('#' + targetDivId).on('jqplotMouseLeave', function (e, s, i, d) {
		 self.hideTooltip();
		 $(this).css('cursor', 'default');
		 }).on('jqplotPiwikTickOver', function (e, tick) {
		 lastTick = tick;
		 self.showEvolutionChartTooltip(tick);
		 if (typeof self.params.axes.xaxis.onclick != 'undefined' && typeof self.params.axes.xaxis.onclick[lastTick] == 'string') {
		 $(this).css('cursor', 'pointer');
		 }
		 });
		 $('#' + targetDivId).on('jqplotDataHighlight', function (e, s, i, d) {
		 lastTick = i;
		 self.showEvolutionChartTooltip(tick);
		 if (typeof self.params.axes.xaxis.onclick != 'undefined' && typeof self.params.axes.xaxis.onclick[lastTick] == 'string') {
		 $(this).css('cursor', 'pointer');
		 }
		 });
		 */
		this.params.legend = {
			show: false
		};
		this.params.canvasLegend = {
			show: true
		};
	},
	showEvolutionChartTooltip: function ( i )
	{

		var label;

		if ( typeof this.params.axes.xaxis.labels != 'undefined' ) {
			label = this.params.axes.xaxis.labels[i];
		} else {
			label = ''; //this.params.axes.xaxis.ticks[i];
		}

		var text = [];

		var useData = this.data;
		if ( this.data.length === 1 && typeof this.data[0] != 'undefined' ) {
			useData = this.data[0];
		}

		if ( useData && typeof useData[i] == 'number' ) {
			var value = useData[i]; //this.formatY(useData[i], d);
			var series = this.params.series[i].label;
			if ( value >= 0 ) {
				text.push( '<b>' + value + '</b> ' + series );
				this.showTooltip( label, text.join( '<br />' ) );
			}
		}

	},
	// ------------------------------------------------------------
	//  PIE CHART
	// ------------------------------------------------------------

	preparePieChart: function ( targetDivId, lang )
	{
		//  this.addSeriesPicker(targetDivId, lang);

		this.params.seriesDefaults = {
			renderer: $.jqplot.PieRenderer,
			rendererOptions: {
				shadow: true,
				showDataLabels: false,
				sliceMargin: 1,
				startAngle: 35
			}
		};

		this.params.piwikTicks = {
			showTicks: false,
			showGrid: false,
			showHighlight: false
		};

		this.params.legend = {
			show: false
		};
		this.params.pieLegend = {
			show: true
		};
		this.params.canvasLegend = {
			show: true,
			singleMetric: true
		};

		// pie charts have a different data format
		this.piedata = [];
		this.piedata[0] = [];
		if ( !(this.data[0][0] instanceof Array) ) { // check if already in different format
			for ( var i = 0; i < this.data[0].length; i++ ) {
				this.piedata[0][i] = [this.params.axes.xaxis.ticks[i], this.data[0][i]];
			}
		}
	},
	showPieChartTooltip: function ( i )
	{

		var useData = this.piedata;
		if ( this.piedata.length === 1 && typeof this.piedata[0] != 'undefined' ) {
			useData = this.piedata[0];
		}

		if ( typeof this.params.axes.xaxis.labels != 'undefined' ) {
			label = this.params.axes.xaxis.labels[i];
		} else {
			label = ''; //this.params.axes.xaxis.ticks[i];
		}

		if ( useData && typeof useData[i] == 'object' ) {

			var value = useData[i][1]; //this.formatY(useData[i], d);
			var series = this.params.series[0].label;
			var percentage = this.tooltip.percentages[0][i];
			if ( value >= 0 ) {
				var text = '<b>' + percentage + '%</b> (' + value + ' ' + series + ')';
				this.showTooltip( label, text );
			}
		}

		/*
		 var value = this.formatY(this.piedata[0][i][1], 1); // series index 1 because 0 is the label
		 var series = this.params.series[0].label;
		 var percentage = this.tooltip.percentages[0][i];

		 var label = this.piedata[0][i][0];

		 var text = '<b>' + percentage + '%</b> (' + value + ' ' + series + ')';
		 this.showTooltip(label, text);
		 */
	},
	// ------------------------------------------------------------
	//  BAR CHART
	// ------------------------------------------------------------

	prepareBarChart: function ( targetDivId, lang )
	{
		this.setYTicks();
		// this.addSeriesPicker(targetDivId, lang);

		this.params.seriesDefaults = {
			renderer: $.jqplot.BarRenderer,
			//    labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
			rendererOptions: {
				shadowOffset: 1,
				shadowDepth: 2,
				shadowAlpha: .2,
				fillToZero: true,
				varyBarColor: true,
				barMargin: this.data[0].length > 10 ? 2 : 10,
				barWidth: null
			}
		};

		this.params.piwikTicks = {
			showTicks: true,
			showGrid: false,
			showHighlight: true
		};

		this.params.axes.xaxis.renderer = $.jqplot.CategoryAxisRenderer;
		this.params.axes.xaxis.labelRenderer = $.jqplot.CanvasAxisLabelRenderer;
		this.params.axes.xaxis.tickRenderer = $.jqplot.CanvasAxisTickRenderer;
		this.params.axes.xaxis.tickOptions = {
			showGridline: false,
			angle: -45
		};

		this.params.canvasLegend = {
			show: true
		};

		this.params.seriesColors = this.seriesColors;
	},

	showBarChartTooltip: function ( s, i )
	{
		var value = this.formatY( this.data[s][i], s );
		var series = this.params.series[s].label;

		var percentage = '';
		if ( typeof this.tooltip.percentages != 'undefined' ) {
			var percentage = this.tooltip.percentages[s][i];
			percentage = ' (' + percentage + '%)';
		}

		var label = this.params.axes.xaxis.labels[i];
		var text = '<b>' + value + '</b> ' + series + percentage;
		this.showTooltip( label, text );
	},
	// ------------------------------------------------------------
	//  HELPER METHODS
	// ------------------------------------------------------------
	maxCrossDataSets: 0,
	/** Generate ticks in y direction */
	setYTicks: function ()
	{

		this.maxCrossDataSets = 0;

		// default axis
		this.setYTicksForAxis( 'yaxis', this.params.axes.yaxis );

		// other axes: y2axis, y3axis...
		/*
		 for (var i = 2; typeof this.params.axes['y' + i + 'axis'] != 'undefined'; i++) {

		 this.maxCrossDataSets = 0;
		 this.setYTicksForAxis('y' + i + 'axis', this.params.axes['y' + i + 'axis']);
		 } */
	},
	setYTicksForAxis: function ( axisName, axis )
	{
		// calculate maximum x value of all data sets
		var maxCrossDataSets = 0;

		var useData = this.data;
		if ( this.data.length === 1 && typeof this.data[0] != 'undefined' ) {
			useData = this.data[0];
		}

		for ( var i = 0; i < useData.length; i++ ) {
			if ( typeof this.params.series === 'object' && this.params.series[i] ) {
				if ( typeof this.params.series[i].yaxis == 'string' && this.params.series[i].yaxis == axisName ) {

					var _v = useData[i];
					if ( typeof useData[i] === 'object' && typeof _v != 'number' ) {
						_v = parseInt( useData[i][0][1], 10 );
					}
					if ( _v > 0 && _v > maxCrossDataSets ) {

						maxCrossDataSets = parseFloat( _v );
					}
				}
			}
		}

		// console.log('maxCrossDataSets 1:' + this.maxCrossDataSets);

		// add little padding on top
		maxCrossDataSets = Math.max( 1, maxCrossDataSets );

		// round to the nearest multiple of ten
		if ( maxCrossDataSets >= 10 ) {
			maxCrossDataSets = maxCrossDataSets + maxCrossDataSets - (maxCrossDataSets / 1.2);

			console.log( 'maxCrossDataSets 3:' + this.maxCrossDataSets );
		}

		// make sure percent axes don't go above 100%
		if ( axis.tickOptions.formatString.substring( 2, 3 ) == '%' && maxCrossDataSets > 100 ) {
			maxCrossDataSets = 100;
			//     console.log('maxCrossDataSets 4:' + maxCrossDataSets);
		}

		if ( maxCrossDataSets < 1 ) {
			maxCrossDataSets = 1;
		}

		//console.log('maxCrossDataSets 5:' + this.maxCrossDataSets);

		// calculate y-values for ticks
		var ticks = [];
		var numberOfTicks = 10;
		var tickDistance = Math.ceil( maxCrossDataSets / numberOfTicks );
		for ( var i = 0; i < numberOfTicks; i++ ) {
			ticks.push( i * tickDistance );
		}

		if ( axis.ticks === undefined ) {
			axis.ticks = {};
		}

		axis.ticks = ticks;
	},
	/** Get a formatted y values (with unit) */
	formatY: function ( value, seriesIndex )
	{
		var floatVal = parseFloat( value );
		var intVal = parseInt( value, 10 );
		if ( Math.abs( floatVal - intVal ) >= 0.002 ) {
			value = Math.round( floatVal * 100 ) / 100;
		} else if ( parseFloat( intVal ) == floatVal ) {
			value = intVal;
		} else {
			value = floatVal;
		}
		if ( typeof this.tooltip.yUnits[seriesIndex] != 'undefined' ) {
			value += this.tooltip.yUnits[seriesIndex];
		}

		return value;
	},
	/** Show the tppltip. The DOM element is created on the fly. */
	showTooltip: function ( head, text )
	{
		Piwik_Tooltip.showWithTitle( head, text );
	},
	/** Hide the tooltip */
	hideTooltip: function ()
	{
		Piwik_Tooltip.hide();
	},
	addSeriesPicker: function ( targetDivId, lang )
	{
		this.params.seriesPicker = {
			show: typeof this.seriesPicker.selectableColumns == 'object'
				|| typeof this.seriesPicker.selectableRows == 'object',
			selectableColumns: this.seriesPicker.selectableColumns,
			selectableRows: this.seriesPicker.selectableRows,
			multiSelect: this.seriesPicker.multiSelect,
			targetDivId: targetDivId,
			dataTableId: this.dataTableId,
			lang: lang
		};
	},
	/**
	 * Add an external series toggle.
	 * As opposed to addSeriesPicker, the external series toggle can only show/hide
	 * series that are already loaded.
	 * @param seriesPickerClass a subclass of JQPlotExternalSeriesToggle
	 */
	addExternalSeriesToggle: function ( seriesPickerClass, targetDivId, initiallyShowAll )
	{
		new seriesPickerClass( targetDivId, this.originalData, initiallyShowAll );

		if ( !initiallyShowAll ) {
			// initially, show only the first series
			this.data = [this.data[0]];
			this.params.series = [this.params.series[0]];
		}
	}

};

// ----------------------------------------------------------------
//  EXTERNAL SERIES TOGGLE
//  Use external dom elements and their events to show/hide series
// ----------------------------------------------------------------

function JQPlotExternalSeriesToggle( targetDivId, originalConfig, initiallyShowAll )
{
	this.init( targetDivId, originalConfig, initiallyShowAll );
}

JQPlotExternalSeriesToggle.prototype = {
	init: function ( targetDivId, originalConfig, initiallyShowAll )
	{
		this.targetDivId = targetDivId;
		this.originalConfig = originalConfig;
		this.originalData = originalConfig.data;
		this.originalSeries = originalConfig.params.series;
		this.originalAxes = originalConfig.params.axes;
		this.originalTooltipUnits = originalConfig.tooltip.yUnits;
		this.originalSeriesColors = originalConfig.params.seriesColors;
		this.initiallyShowAll = initiallyShowAll;

		this.activated = [];
		this.target = $( '#' + targetDivId );

		this.attachEvents();
	},
	// can be overridden
	attachEvents: function ()
	{
	},
	// show a single series
	showSeries: function ( i )
	{
		for ( var j = 0; j < this.activated.length; j++ ) {
			this.activated[j] = (i == j);
		}
		this.replot();
	},
	// toggle a series (make plotting multiple series possible)
	toggleSeries: function ( i )
	{
		var activatedCount = 0;
		for ( var k = 0; k < this.activated.length; k++ ) {
			if ( this.activated[k] ) {
				activatedCount++;
			}
		}
		if ( activatedCount == 1 && this.activated[i] ) {
			// prevent removing the only visible metric
			return;
		}

		this.activated[i] = !this.activated[i];
		this.replot();
	},
	replot: function ()
	{
		this.beforeReplot();

		// build new config and replot
		var usedAxes = [];
		var config = this.originalConfig;
		config.data = [];
		config.params.series = [];
		config.params.axes = {xaxis: this.originalAxes.xaxis};
		config.tooltip.yUnits = [];
		config.params.seriesColors = [];
		for ( var j = 0; j < this.activated.length; j++ ) {
			if ( !this.activated[j] ) {
				continue;
			}
			config.data.push( this.originalData[j] );
			config.tooltip.yUnits.push( this.originalTooltipUnits[j] );
			config.params.seriesColors.push( this.originalSeriesColors[j] );
			config.params.series.push( $.extend( true, {}, this.originalSeries[j] ) );
			// build array of used axes
			var axis = this.originalSeries[j].yaxis;
			if ( $.inArray( axis, usedAxes ) == -1 ) {
				usedAxes.push( axis );
			}
		}

		// build new axes config
		var replaceAxes = {};
		for ( j = 0; j < usedAxes.length; j++ ) {
			var originalAxisName = usedAxes[j];
			var newAxisName = (j == 0 ? 'yaxis' : 'y' + (j + 1) + 'axis');
			replaceAxes[originalAxisName] = newAxisName;
			config.params.axes[newAxisName] = this.originalAxes[originalAxisName];
		}

		// replace axis names in series config
		for ( j = 0; j < config.params.series.length; j++ ) {
			var series = config.params.series[j];
			series.yaxis = replaceAxes[series.yaxis];
		}

		this.target.trigger( 'replot', config );
	},
	// can be overridden
	beforeReplot: function ()
	{
	}

};

// ROW EVOLUTION SERIES TOGGLE

function RowEvolutionSeriesToggle( targetDivId, originalConfig, initiallyShowAll )
{
	this.init( targetDivId, originalConfig, initiallyShowAll );
}

RowEvolutionSeriesToggle.prototype = JQPlotExternalSeriesToggle.prototype;

RowEvolutionSeriesToggle.prototype.attachEvents = function ()
{
	var self = this;
	this.seriesPickers = this.target.closest( '.rowevolution' ).find( 'table.metrics tr' );

	this.seriesPickers.each( function ( i )
	{
		var el = $( this );
		el.click( function ( e )
		{
			if ( e.shiftKey ) {
				self.toggleSeries( i );
			} else {
				self.showSeries( i );
			}
			return false;
		} );

		if ( i == 0 || self.initiallyShowAll ) {
			// show the active series
			// if initiallyShowAll, all are active; otherwise only the first one
			self.activated.push( true );
		} else {
			// fade out the others
			el.find( 'td' ).css( 'opacity', .5 );
			self.activated.push( false );
		}

		// prevent selecting in ie & opera (they don't support doing this via css)
		if ( $.browser.msie ) {
			this.ondrag = function ()
			{
				return false;
			};
			this.onselectstart = function ()
			{
				return false;
			};
		} else if ( $.browser.opera ) {
			$( this ).attr( 'unselectable', 'on' );
		}
	} );
};

RowEvolutionSeriesToggle.prototype.beforeReplot = function ()
{
	// fade out if not activated
	for ( var i = 0; i < this.activated.length; i++ ) {
		if ( this.activated[i] ) {
			this.seriesPickers.eq( i ).find( 'td' ).css( 'opacity', 1 );
		} else {
			this.seriesPickers.eq( i ).find( 'td' ).css( 'opacity', .5 );
		}
	}
};

// ------------------------------------------------------------
//  PIWIK TICKS PLUGIN FOR JQPLOT
//  Handle ticks the piwik way...
// ------------------------------------------------------------

(function ( $ )
{

	$.jqplot.PiwikTicks = function ( options )
	{
		// canvas for the grid
		this.piwikTicksCanvas = null;
		// canvas for the highlight
		this.piwikHighlightCanvas = null;
		// renderer used to draw the marker of the highlighted point
		this.markerRenderer = new $.jqplot.MarkerRenderer( {
			shadow: false
		} );
		// the x tick the mouse is over
		this.currentXTick = false;
		// show the highlight around markers
		this.showHighlight = false;
		// show the grid
		this.showGrid = false;
		// show the ticks
		this.showTicks = true;

		$.extend( true, this, options );
	};

	$.jqplot.PiwikTicks.prototype.init = function ( target, data, opts )
	{
		// add plugin as an attribute to the plot
		var options = opts || {};
		this.plugins.piwikTicks = new $.jqplot.PiwikTicks( options.piwikTicks );

		if ( typeof $.jqplot.PiwikTicks.init.eventsBound == 'undefined' ) {
			$.jqplot.PiwikTicks.init.eventsBound = true;
			$.jqplot.eventListenerHooks.push( ['jqplotMouseMove', handleMouseMove] );
			$.jqplot.eventListenerHooks.push( ['jqplotMouseLeave', handleMouseLeave] );
		}
	};

	// draw the grid
	// called with context of plot
	$.jqplot.PiwikTicks.prototype.postDraw = function ()
	{
		var c = this.plugins.piwikTicks;

		// highligh canvas
		if ( c.showHighlight ) {
			c.piwikHighlightCanvas = new $.jqplot.GenericCanvas();

			this.eventCanvas._elem.before( c.piwikHighlightCanvas.createElement(
				this._gridPadding, 'jqplot-piwik-highlight-canvas', this._plotDimensions, this ) );
			c.piwikHighlightCanvas.setContext();
		}

		// grid canvas
		if ( c.showTicks ) {
			var dimensions = this._plotDimensions;
			dimensions.height += 6;
			c.piwikTicksCanvas = new $.jqplot.GenericCanvas();
			this.series[0].shadowCanvas._elem.before( c.piwikTicksCanvas.createElement(
				this._gridPadding, 'jqplot-piwik-ticks-canvas', dimensions, this ) );
			c.piwikTicksCanvas.setContext();

			var ctx = c.piwikTicksCanvas._ctx;

			var ticks = this.data[0];
			var totalWidth = ctx.canvas.width;
			var tickWidth = totalWidth / ticks.length;

			var xaxisLabels = this.axes.xaxis.ticks;

			for ( var i = 0; i < ticks.length; i++ ) {
				var pos = Math.round( i * tickWidth + tickWidth / 2 );
				var full = xaxisLabels[i] && xaxisLabels[i] != ' ';
				drawLine( ctx, pos, full, c.showGrid );
			}
		}
	};

	$.jqplot.preInitHooks.push( $.jqplot.PiwikTicks.init );
	$.jqplot.postDrawHooks.push( $.jqplot.PiwikTicks.postDraw );

	// draw a 1px line
	function drawLine( ctx, x, full, showGrid )
	{
		ctx.save();
		ctx.strokeStyle = '#cccccc';

		ctx.beginPath();
		ctx.lineWidth = 2;
		var top = 0;
		if ( (full && !showGrid) || !full ) {
			top = ctx.canvas.height - 5;
		}
		ctx.moveTo( x, top );
		ctx.lineTo( x, full ? ctx.canvas.height : ctx.canvas.height - 2 );
		ctx.stroke();

		// canvas renders line slightly too large
		ctx.clearRect( x, 0, x + 1, ctx.canvas.height );

		ctx.restore();
	}

	// tigger the event jqplotPiwikTickOver when the mosue enters
	// and new tick. this is used for tooltips.
	function handleMouseMove( ev, gridpos, datapos, neighbor, plot )
	{
		var c = plot.plugins.piwikTicks;

		var tick = Math.floor( datapos.xaxis + 0.5 ) - 1;
		if ( tick !== c.currentXTick ) {
			c.currentXTick = tick;
			plot.target.trigger( 'jqplotPiwikTickOver', [tick] );
			highlight( plot, tick );
		}
	}

	function handleMouseLeave( ev, gridpos, datapos, neighbor, plot )
	{
		unHighlight( plot );
		plot.plugins.piwikTicks.currentXTick = false;
	}

	// highlight a marker
	function highlight( plot, tick )
	{
		var c = plot.plugins.piwikTicks;

		if ( !c.showHighlight ) {
			return;
		}

		unHighlight( plot );

		for ( var i = 0; i < plot.series.length; i++ ) {
			var series = plot.series[i];
			var seriesMarkerRenderer = series.markerRenderer;

			c.markerRenderer.style = seriesMarkerRenderer.style;
			c.markerRenderer.size = seriesMarkerRenderer.size + 5;

			var rgba = $.jqplot.getColorComponents( seriesMarkerRenderer.color );
			var newrgb = [rgba[0], rgba[1], rgba[2]];
			var alpha = rgba[3] * .4;
			c.markerRenderer.color = 'rgba(' + newrgb[0] + ',' + newrgb[1] + ',' + newrgb[2] + ',' + alpha + ')';
			c.markerRenderer.init();

			var position = series.gridData[tick];
			c.markerRenderer.draw( position[0], position[1], c.piwikHighlightCanvas._ctx );
		}
	}

	function unHighlight( plot )
	{
		var canvas = plot.plugins.piwikTicks.piwikHighlightCanvas;
		if ( canvas !== null ) {
			var ctx = canvas._ctx;
			ctx.clearRect( 0, 0, ctx.canvas.width, ctx.canvas.height );
		}
	}

})( jQuery );

// ------------------------------------------------------------
//  LEGEND PLUGIN FOR JQPLOT
//  Render legend on canvas
// ------------------------------------------------------------

(function ( $ )
{

	$.jqplot.CanvasLegendRenderer = function ( options )
	{
		// canvas for the legend
		this.legendCanvas = null;
		// is it a legend for a single metric only (pie chart)?
		this.singleMetric = false;
		// render the legend?
		this.show = false;

		$.extend( true, this, options );
	};

	$.jqplot.CanvasLegendRenderer.init = function ( target, data, opts )
	{
		// add plugin as an attribute to the plot
		var options = opts || {};
		this.plugins.canvasLegend = new $.jqplot.CanvasLegendRenderer( options.canvasLegend );

		// add padding above the grid
		// legend will be put there
		if ( this.plugins.canvasLegend.show ) {
			options.gridPadding = {
				top: 21
			};
		}

	};

	// render the legend
	$.jqplot.CanvasLegendRenderer.postDraw = function ()
	{
		var plot = this;
		var legend = plot.plugins.canvasLegend;

		if ( !legend.show ) {
			return;
		}

		// initialize legend canvas
		var padding = {top: 0, right: this._gridPadding.right, bottom: 0, left: this._gridPadding.left};
		var dimensions = {width: this._plotDimensions.width, height: this._gridPadding.top};
		var width = this._plotDimensions.width - this._gridPadding.left - this._gridPadding.right;

		legend.legendCanvas = new $.jqplot.GenericCanvas();
		this.eventCanvas._elem.before( legend.legendCanvas.createElement(
			padding, 'jqplot-legend-canvas', dimensions, plot ) );
		legend.legendCanvas.setContext();

		var ctx = legend.legendCanvas._ctx;
		ctx.save();
		ctx.font = '10px Arial';

		// render series names
		var x = 0;
		var series = plot.legend._series;
		for ( i = 0; i < series.length; i++ ) {
			var s = series[i];
			var label;
			if ( legend.labels && legend.labels[i] ) {
				label = legend.labels[i];
			} else {
				label = s.label.toString();
			}

			ctx.fillStyle = s.color;
			if ( legend.singleMetric ) {
				ctx.fillStyle = '#666666';
			}

			ctx.fillRect( x, 10, 10, 2 );
			x += 15;

			var nextX = x + ctx.measureText( label ).width + 20;

			if ( nextX + 70 > width ) {
				ctx.fillText( "[...]", x, 15 );
				x += ctx.measureText( "[...]" ).width + 20;
				break;
			}

			ctx.fillText( label, x, 15 );
			x = nextX;
		}

		legend.width = x;

		ctx.restore();
	};

	$.jqplot.preInitHooks.push( $.jqplot.CanvasLegendRenderer.init );
	$.jqplot.postDrawHooks.push( $.jqplot.CanvasLegendRenderer.postDraw );

})( jQuery );

// ------------------------------------------------------------
//  SERIES PICKER
//  For line charts
// ------------------------------------------------------------

(function ( $ )
{

	$.jqplot.SeriesPicker = function ( options )
	{
		// dom element
		this.domElem = null;
		// render the picker?
		this.show = false;
		// the columns that can be selected
		this.selectableColumns = null;
		// the rows that can be selected
		this.selectableRows = null;
		// can multiple rows we selected?
		this.multiSelect = true;
		// css id of the target div dom element
		this.targetDivId = "";
		// the id of the current data table (index for global dataTables)
		this.dataTableId = "";
		// language strings
		this.lang = {};

		$.extend( true, this, options );
	};

	$.jqplot.SeriesPicker.init = function ( target, data, opts )
	{
		// add plugin as an attribute to the plot
		var options = opts || {};
		this.plugins.seriesPicker = new $.jqplot.SeriesPicker( options.seriesPicker );
	};

	// render the link to add series
	$.jqplot.SeriesPicker.postDraw = function ()
	{
		var plot = this;
		var picker = plot.plugins.seriesPicker;

		if ( !picker.show ) {
			return;
		}

		// initialize dom element
		picker.domElem = $( document.createElement( 'a' ) )
			.addClass( 'jqplot-seriespicker' )
			.attr( 'href', '#' ).html( '+' )
			.css( 'marginLeft', (plot._gridPadding.left + plot.plugins.canvasLegend.width - 1) + 'px' );

		picker.domElem.on( 'hide',function ()
		{
			$( this ).css( 'opacity', .55 );
		} ).trigger( 'hide' );

		plot.baseCanvas._elem.before( picker.domElem );
		/*
		 // show picker on hover
		 picker.domElem.hover(function() {
		 picker.domElem.css('opacity', 1);
		 if (!picker.domElem.hasClass('open')) {
		 picker.domElem.addClass('open');
		 showPicker(picker, plot._width);
		 }
		 }, function() {
		 // do nothing on mouseout because using this event doesn't work properly.
		 // instead, the timeout check beneath is used (checkPickerLeave()).
		 }).click(function() {
		 return false;
		 });

		 */
	};

	// show the series picker
	function showPicker( picker, plotWidth )
	{
		var pickerLink = picker.domElem;
		var pickerPopover = $( document.createElement( 'div' ) )
			.addClass( 'jqplock-seriespicker-popover' );

		var pickerState = {manipulated: false};

		// headline
		var title = picker.multiSelect ? picker.lang.metricsToPlot : picker.lang.metricToPlot;
		pickerPopover.append( $( document.createElement( 'p' ) )
			.addClass( 'headline' ).html( title ) );

		if ( picker.selectableColumns !== null ) {
			// render the selectable columns
			for ( var i = 0; i < picker.selectableColumns.length; i++ ) {
				var column = picker.selectableColumns[i];
				pickerPopover.append( createPickerPopupItem( picker, column, 'column', pickerState, pickerPopover, pickerLink ) );
			}
		}

		if ( picker.selectableRows !== null ) {
			// "records to plot" subheadline
			pickerPopover.append( $( document.createElement( 'p' ) )
				.addClass( 'headline' ).addClass( 'recordsToPlot' )
				.html( picker.lang.recordsToPlot ) );

			// render the selectable rows
			for ( var i = 0; i < picker.selectableRows.length; i++ ) {
				var row = picker.selectableRows[i];
				pickerPopover.append( createPickerPopupItem( picker, row, 'row', pickerState, pickerPopover, pickerLink ) );
			}
		}

		$( 'body' ).prepend( pickerPopover.hide() );
		var neededSpace = pickerPopover.outerWidth() + 10;

		// try to display popover to the right
		var linkOffset = pickerLink.offset();
		if ( navigator.appVersion.indexOf( "MSIE 7." ) != -1 ) {
			linkOffset.left -= 10;
		}
		var margin = (parseInt( pickerLink.css( 'marginLeft' ), 10 ) - 4);
		if ( margin + neededSpace < plotWidth
			// make sure it's not too far to the left
			|| margin - neededSpace + 60 < 0 ) {
			pickerPopover.css( 'marginLeft', (linkOffset.left - 4) + 'px' ).show();
		} else {
			// display to the left
			pickerPopover.addClass( 'alignright' )
				.css( 'marginLeft', (linkOffset.left - neededSpace + 38) + 'px' )
				.css( 'backgroundPosition', (pickerPopover.outerWidth() - 25) + 'px 4px' )
				.show();
		}
		pickerPopover.css( 'marginTop', (linkOffset.top - 5) + 'px' ).show();

		// hide and replot on mouse leave
		checkPickerLeave( pickerPopover, function ()
		{
			var replot = pickerState.manipulated;
			hidePicker( picker, pickerPopover, pickerLink, replot );
		} );
	}

	function createPickerPopupItem( picker, config, type, pickerState, pickerPopover, pickerLink )
	{
		var checkbox = $( document.createElement( 'input' ) ).addClass( 'select' )
			.attr( 'type', picker.multiSelect ? 'checkbox' : 'radio' );

		if ( config.displayed && !(!picker.multiSelect && pickerState.oneChecked) ) {
			checkbox.prop( 'checked', true );
			pickerState.oneChecked = true;
		}

		// if we are rendering a column, remember the column name
		// if it's a row, remember the string that can be used to match the row
		checkbox.data( 'name', type == 'column' ? config.column : config.matcher );

		var el = $( document.createElement( 'p' ) )
			.append( checkbox )
			.append( type == 'column' ? config.translation : config.label )
			.addClass( type == 'column' ? 'pickColumn' : 'pickRow' );

		var replot = function ()
		{
			unbindPickerLeaveCheck();
			hidePicker( picker, pickerPopover, pickerLink, true );
		};

		var checkBox = function ( box )
		{
			if ( !picker.multiSelect ) {
				pickerPopover.find( 'input.select:not(.current)' ).prop( 'checked', false );
			}
			box.prop( 'checked', true );
			replot();
		};

		el.click( function ( e )
		{
			pickerState.manipulated = true;
			var box = $( this ).find( 'input.select' );
			if ( !$( e.target ).is( 'input.select' ) ) {
				if ( box.is( ':checked' ) ) {
					box.prop( 'checked', false );
				} else {
					checkBox( box );
				}
			} else {
				if ( box.is( ':checked' ) ) {
					checkBox( box );
				}
			}
		} );

		return el;
	}

	// check whether the mouse has left the picker
	var onMouseMove;

	function checkPickerLeave( pickerPopover, onLeaveCallback )
	{
		var offset = pickerPopover.offset();
		var minX = offset.left;
		var minY = offset.top;
		var maxX = minX + pickerPopover.outerWidth();
		var maxY = minY + pickerPopover.outerHeight();
		var currentX, currentY;
		onMouseMove = function ( e )
		{
			currentX = e.pageX;
			currentY = e.pageY;
			if ( currentX < minX || currentX > maxX
				|| currentY < minY || currentY > maxY ) {
				unbindPickerLeaveCheck();
				onLeaveCallback();
			}
		};
		$( document ).mousemove( onMouseMove );
	}

	function unbindPickerLeaveCheck()
	{
		$( document ).unbind( 'mousemove', onMouseMove );
	}

	function hidePicker( picker, pickerPopover, pickerLink, replot )
	{
		// hide picker
		pickerPopover.hide();
		pickerLink.trigger( 'hide' ).removeClass( 'open' );

		// replot
		if ( replot ) {
			var columns = [];
			var rows = [];
			pickerPopover.find( 'input:checked' ).each( function ()
			{
				if ( $( this ).closest( 'p' ).hasClass( 'pickRow' ) ) {
					rows.push( $( this ).data( 'name' ) );
				} else {
					columns.push( $( this ).data( 'name' ) );
				}
			} );
			var noRowSelected = pickerPopover.find( '.pickRow' ).size() > 0
				&& pickerPopover.find( '.pickRow input:checked' ).size() == 0;
			if ( columns.length > 0 && !noRowSelected ) {

				$( '#' + picker.targetDivId ).trigger( 'changeSeries', [columns, rows] );
				// inform dashboard widget about changed parameters (to be restored on reload)
				$( '#' + picker.targetDivId ).parents( '[widgetId]' ).trigger( 'setParameters', {columns: columns, rows: rows} );
			}
		}

		pickerPopover.remove();
	}

	$.jqplot.preInitHooks.push( $.jqplot.SeriesPicker.init );
	$.jqplot.postDrawHooks.push( $.jqplot.SeriesPicker.postDraw );

})( jQuery );

// ------------------------------------------------------------
//  PIE CHART LEGEND PLUGIN FOR JQPLOT
//  Render legend inside the pie graph
// ------------------------------------------------------------

(function ( $ )
{

	$.jqplot.PieLegend = function ( options )
	{
		// canvas for the legend
		this.pieLegendCanvas = null;
		// render the legend?
		this.show = false;

		$.extend( true, this, options );
	};

	$.jqplot.PieLegend.init = function ( target, data, opts )
	{
		// add plugin as an attribute to the plot
		var options = opts || {};
		this.plugins.pieLegend = new $.jqplot.PieLegend( options.pieLegend );
	};

	// render the legend
	$.jqplot.PieLegend.postDraw = function ()
	{
		var plot = this;
		var legend = plot.plugins.pieLegend;

		if ( !legend.show ) {
			return;
		}

		var series = plot.series[0];
		var angles = series._sliceAngles;
		var radius = series._diameter / 2;
		var center = series._center;
		var colors = this.seriesColors;

		// concentric line angles
		var lineAngles = [];
		for ( var i = 0; i < angles.length; i++ ) {
			lineAngles.push( (angles[i][0] + angles[i][1]) / 2 + Math.PI / 2 );
		}

		// labels
		var labels = [];
		var data = series._plotData;
		for ( i = 0; i < data.length; i++ ) {
			labels.push( data[i][0] );
		}

		// initialize legend canvas
		legend.pieLegendCanvas = new $.jqplot.GenericCanvas();
		plot.series[0].canvas._elem.before( legend.pieLegendCanvas.createElement(
			plot._gridPadding, 'jqplot-pie-legend-canvas', plot._plotDimensions, plot ) );
		legend.pieLegendCanvas.setContext();

		var ctx = legend.pieLegendCanvas._ctx;
		ctx.save();

		ctx.font = '10px Arial';

		// render labels
		var height = legend.pieLegendCanvas._elem.height();
		var x1, x2, y1, y2, lastY2 = false, right, lastRight = false;
		for ( i = 0; i < labels.length; i++ ) {
			var label = labels[i];

			ctx.strokeStyle = colors[i % colors.length];
			ctx.lineCap = 'round';
			ctx.lineWidth = 1;

			// concentric line
			x1 = center[0] + Math.sin( lineAngles[i] ) * (radius);
			y1 = center[1] - Math.cos( lineAngles[i] ) * (radius);

			x2 = center[0] + Math.sin( lineAngles[i] ) * (radius + 7);
			y2 = center[1] - Math.cos( lineAngles[i] ) * (radius + 7);

			right = x2 > center[0];

			// move close labels
			if ( lastY2 !== false && lastRight == right && (
				(right && y2 - lastY2 < 13) ||
					(!right && lastY2 - y2 < 13)) ) {

				if ( x1 > center[0] ) {
					// move down if the label is in the right half of the graph
					y2 = lastY2 + 13;
				} else {
					// move up if in left halt
					y2 = lastY2 - 13;
				}
			}

			if ( y2 < 4 || y2 + 4 > height ) {
				continue;
			}

			ctx.beginPath();
			ctx.moveTo( x1, y1 );
			ctx.lineTo( x2, y2 );

			ctx.closePath();
			ctx.stroke();

			// horizontal line
			ctx.beginPath();
			ctx.moveTo( x2, y2 );
			if ( right ) {
				ctx.lineTo( x2 + 5, y2 );
			} else {
				ctx.lineTo( x2 - 5, y2 );
			}

			ctx.closePath();
			ctx.stroke();

			lastY2 = y2;
			lastRight = right;

			// text
			if ( right ) {
				x = x2 + 9;
			} else {
				x = x2 - 9 - ctx.measureText( label ).width;
			}

			ctx.fillStyle = '#666666';
			ctx.fillText( label, x, y2 + 3 );
		}

		ctx.restore();
	};

	$.jqplot.preInitHooks.push( $.jqplot.PieLegend.init );
	$.jqplot.postDrawHooks.push( $.jqplot.PieLegend.postDraw );

})( jQuery );

/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var Piwik_Tooltip = (function ()
{

	var domElement = false;
	var visible = false;
	var addedClass = false;
	var topOffset = 15;

	var mouseX, mouseY;

	/** Position the tooltip next to the mouse */
	var position = function ()
	{
		var tipWidth = domElement.outerWidth();
		var maxX = $( 'body' ).innerWidth() - tipWidth - 25;
		if ( mouseX < maxX ) {
			// tooltip right of mouse
			domElement.css( {
				top: (mouseY - topOffset) + "px",
				left: (mouseX + 15) + "px"
			} );
		}
		else {
			// tooltip left of mouse
			domElement.css( {
				top: (mouseY - topOffset) + "px",
				left: (mouseX - 15 - tipWidth) + "px"
			} );
		}
	};

	/** Create and initialize the tooltip */
	var initialize = function ()
	{
		if ( domElement !== false ) {
			return;
		}

		domElement = $( document.createElement( 'div' ) );
		domElement.addClass( 'piwik-tooltip' );
		$( 'body' ).prepend( domElement );
		domElement.hide();

		$( document ).mousemove( function ( e )
		{
			mouseX = e.pageX;
			mouseY = e.pageY;
			if ( visible ) {
				position();
			}
		} );
	};

	$( document ).ready( function ()
	{
		initialize();
	} );

	return {
		/** Show the tooltip with HTML content. */
		show: function ( html, addClass, maxWidth )
		{
			initialize();

			if ( visible && addedClass != addClass ) {
				domElement.removeClass( addedClass );
			} else {
				visible = true;
				position();
				domElement.show();
			}

			if ( addClass && addedClass != addClass ) {
				addedClass = addClass;
				domElement.addClass( addClass );
			}

			domElement.css( {width: 'auto'} );
			domElement.html( html );
			if ( domElement.outerWidth() > maxWidth ) {
				domElement.css( {width: maxWidth + 'px'} );
			}

			if ( domElement.outerHeight() < 25 ) {
				topOffset = 5;
			} else {
				topOffset = 15;
			}

			position();
		},
		/** Show the tooltip with title/text content. */
		showWithTitle: function ( title, text, addClass )
		{
			var html = (title ? '<span class="tip-title">' + title + '</span><br />' : '') + text;
			this.show( html, addClass );
		},
		/** Hide the tooltip */
		hide: function ()
		{
			if ( domElement !== false ) {
				domElement.hide();
			}

			if ( addedClass ) {
				domElement.removeClass( addedClass );
				addedClass = false;
			}

			visible = false;
		}

	};

})();

function generateJqplot( what, plotmode )
{
	if ( !_jqPlot ) {
		console.log( 'Error plotting!' );
		return;
	}

	if ( $( '#' + what + '-chart' ).length == 0 ) {
		console.log( 'Error plotting "' + what + '" !' );
		return;
	}

	var plotContainer = $( '#' + what + '-chart' );
	if ( typeof jqPlotDataCache[what] != 'object' ) {
		console.log( 'Error plotting "' + what + '"! Data attribute not exists.' );
		return;
	}

	var options = {}, data = {}, _mode = 'bar', tmp = jqPlotDataCache[what];
	data = $.extend( {}, tmp );
	tmp = null;

	var defaults = {};

	defaults.grid = {
		drawGridLines: false,
		background: '#F7F7F7',
		borderColor: '#f00',
		borderWidth: 0,
		shadow: false
	};

	defaults.title = {
		show: true
	};

	defaults.axesDefaults = {
		title: data.title,
		pad: 1.0,
		tickRenderer: $.jqplot.CanvasAxisTickRenderer,
		tickOptions: {
			showMark: true,
			fontSize: '11px',
			fontFamily: 'Arial',
			ticks: {}
		},
		axes: {
			xaxis: {
				tickOptions: {
					formatString: '%s',
					ticks: {}
				}
			},
			yaxis: {
				tickOptions: {
					formatString: '%s',
					angle: 0,
					ticks: {}
				}
			}
		}
	};

	var optsBar = {
		title: data.title,
		pad: 1.0,
		tickRenderer: $.jqplot.CanvasAxisTickRenderer,
		axesDefaults: {
			tickOptions: {
				angle: 0,
				ticks: {}
			}
		},
		axes: {
			ticks: {},
			xaxis: {
				tickOptions: {
					formatString: '%s',
					angle: -45,
					ticks: {}
				}
			},
			yaxis: {
				tickOptions: {
					formatString: '%s',
					angle: 0,
					ticks: {}
				}
			}
		}
	};

	var optsLine = {
		title: data.title,
		axesDefaults: {
			tickRenderer: $.jqplot.CanvasAxisTickRenderer,
			tickOptions: {
				angle: 0,
				ticks: {}
			}
		},
		axes: {
			ticks: {},
			xaxis: {
				tickOptions: {
					formatString: '%s',
					angle: -45,
					ticks: {}
				}
			},
			yaxis: {
				tickOptions: {
					formatString: '%s',
					angle: 0,
					ticks: {}
				}
			}
		},
		seriesDefaults: {
			showMarker: false,
			min: 0,
			pointLabels: {show: true}
		}
	};

	var optsPie = {
		title: data.title
	};

	optsPie.pieLegend = {
		show: true
	};
	optsPie.canvasLegend = {
		show: true,
		singleMetric: true
	};

	defaults.data = [];

	_mode = 'bar';
	options = optsBar;
	var cssClass = 'jqplot-bar';

	if ( plotmode == 'bar' ) {
		_mode = 'bar';
		options = optsBar;
		cssClass = 'jqplot-bar';
	}
	else if ( plotmode == 'barh' ) {
		_mode = 'barh';
		options = optsBar;
		cssClass = 'jqplot-bar';
	}
	else if ( plotmode == 'pie' ) {
		_mode = 'pie';
		options = optsPie;
		cssClass = 'jqplot-pie';
	}
	else if ( plotmode == 'line' ) {
		_mode = 'evolution';
		options = optsLine;
		cssClass = 'jqplot-evolution';
	}

	var opt = $.extend( {}, defaults, options, data );
	var uiqID = new Date().getTime();

	if ( !$( '#' + what + '-chart-' + _mode ).length ) {
		plotContainer.append( '<div id="' + what + '-chart-' + _mode + '" class="' + what + '-chart' + _mode + ' tmp-chart ' + cssClass + '"></div>' );
	}

	plotContainer.find( '.tmp-chart' ).hide().css( 'opacity', '0' );
	$( '#' + what + '-chart-' + _mode ).css( 'opacity', '0' ).show().empty();
	plotContainer.addClass( 'loading-chart' ).show();

	var params = opt;
	params.tooltip = data.tooltip;
	params.seriesPicker = data.seriesPicker;
	params.seriesColors = data.seriesColors;
	params.series = data.series;
	params.axes = data.axes;

	if ( params.axes.ticks === undefined ) {
		params.axes.ticks = {};
	}

//	console.log( '#' + what + '-chart-' + _mode );

	var plot = null;
	plot = new JQPlot( {
		params: params,
		data: data.data,
		tooltip: data.tooltip,
		seriesPicker: data.seriesPicker,
		seriesColors: data.seriesColors,
		series: data.series,
		axes: data.axes,
		ticks: {}
	}, what + '-chart-' + _mode, options );

	//$.jqplot(what + '-chart-' + _mode + '_' + uiqID, [data.data], options);
	setTimeout( function ()
	{
		plot.render( _mode, what + '-chart-' + _mode, 'de' );
		plotContainer.removeClass( 'loading-chart' );

		setTimeout( function ()
		{
			Win.redrawWindowHeight( false, true );
		}, 100 );
	}, 20 );

}