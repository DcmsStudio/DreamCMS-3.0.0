/**
 * Created by marcel on 29.04.14.
 */

setTimeout( function ()
{
	Tools.scrollBar( $( '#' + Win.windowID ).find( '.subwindow:visible' ).find( '#template-groups' ) );
}, 100 );

var windowObj = $( '#' + Win.windowID ).find( '.subwindow:visible' );

if ( typeof buildTemplateResult == 'undefined' ) {

	var renameTemplate = "Template umbenennen";
	var deleteTemplate = "Template löschen";

	function buildTemplateResult( container, data, isSearch )
	{
		container.empty();
		for ( var i = 0; i < data.templates.length; ++i ) {

			var dat = data.templates[i];
			var row = $( '<div />' ).addClass( 'row' );
			row.append( $( '<span />' ).append( dat.templatename ) ).data( 'id', dat.id ).attr( 'title', title );

			if ( isSearch ) {
				row.append( $( '<em />' ).append( ' (' + dat.group_name + ')' ) );
			}

			var rightSide = $( '<div />' ).addClass( 'right-side' );
			var title = "Template `%s` bearbeiten".replace( '%s', dat.templatename );
			var editImage = $( '<img src="' + Config.get( 'backendImagePath' ) + 'edit.png" width="16" height="16" title="' + title + '" ="" />' );
			var renameImage = $( '<img src="' + Config.get( 'backendImagePath' ) + 'document-rename.png" width="16" height="16" title="' + renameTemplate + '" ="" />' );
			var deleteImage = $( '<img src="' + Config.get( 'backendImagePath' ) + 'delete.png" width="16" height="16" title="' + deleteTemplate + '" ="" />' );
			var editlink = $( '<a />' ).css( {'cursor': 'pointer', marginLeft: '10px'} ).attr( 'title', title ).data( 'id', dat.id );
			editlink.append( editImage );
			editlink.click( function ()
			{
				openTab( {url: 'admin.php?adm=skins&amp;action=edittemplate&amp;id=' + $( this ).data( 'id' ) + '&amp;skinid=364', obj: this, label: $( this ).attr( 'title' ), isSingleWindow: false} );
			} );
			var dellink = $( '<a />' ).css( {'cursor': 'pointer', marginLeft: '10px'} ).data( 'id', dat.id ).data( 'groupname', dat.group_name );
			dellink.append( deleteImage );
			dellink.click( function ()
			{
				var s = $( this );
				jConfirm( "Wollen Sie das Template wirklich löschen?.", "Achtung...",
					function ( res )
					{
						if ( res ) {
							var url = 'admin.php?adm=skins&amp;action=deltemplate&amp;id=' + $( s ).data( 'id' );
							$.get( url + '&amp;ajax=1', function ( data )
							{
								if ( Tools.responseIsOk( data ) ) {

									var groupRow = $( '#' + Win.windowID ).find( '#template-groups' ).find( 'div[rel="' + $( s ).data( 'groupname' ) + '"]' );
									if ( groupRow &amp;&amp; groupRow.length ) {
										var totalTemplates = groupRow.find( 'span.total' ).attr( 'total' );
										if ( totalTemplates > 0 ) {
											totalTemplates--;
											groupRow.find( 'span' ).attr( 'total', totalTemplates );
										}

										groupRow.find( 'span.total' ).html( "(%s Templates)".replace( '%s', totalTemplates ) );
									}

									$( s ).parents( 'div.row:first' ).slideUp( 400, function ()
									{
										$( this ).remove();
									} );
								}
								else {
									jAlert( data.msg );
								}
							}, 'json' );
						}
					} );
			} );
			var renamelink = $( '<a />' ).css( {'cursor': 'pointer', marginLeft: '10px'} ).data( 'id', dat.id ).data( 'name', dat.templatename );
			renamelink.append( renameImage );
			renamelink.click( function ()
			{
				var s = $( this );
				jPrompt( "Template Bezeichnung:", $( this ).data( 'name' ), "Template Umbenennen...",
					function ( res )
					{
						if ( res ) {
							var url = 'admin.php?adm=skins&amp;action=renametemplate&amp;id=' + $( s ).data( 'id' ) + '&amp;newname=' + res;
							$.get( url + '&amp;ajax=1', function ( data )
							{
								if ( Tools.responseIsOk( data ) ) {
									jAlert( data.msg );
									$( s ).parents( 'div.row:first' ).find( 'span' ).text( res );
								}
								else {
									jAlert( data.msg );
								}
							}, 'json' );
						}
					} );
			} );
			rightSide.append( editlink );
			rightSide.append( renamelink );
			rightSide.append( dellink );

			row.on( 'dblclick', function ( e )
			{
				openTab( {url: 'admin.php?adm=skins&amp;action=edittemplate&amp;id=' + $( this ).data( 'id' ) + '&amp;skinid=364', obj: this, label: $( this ).attr( 'title' ), isSingleWindow: false} );
			} );

			row.append( rightSide );
			container.append( row );
		}
	}
}

windowObj.find( '#template-groups' ).find( 'div' ).each( function ()
{
	var btnContainer = $( '<div class="inline-buttons"></div>' );
	var editImage = $( '<span><img src="' + Config.get( 'backendImagePath' ) + 'edit.png" width="16" height="16" ="" /></span>' ).attr( 'title', "Template erstellen" );
	var deleteImage = $( '<span><img src="' + Config.get( 'backendImagePath' ) + 'delete.png" width="16" height="16" ="" /></span>' ).attr( 'title', "Template Gruppe löschen" );
	//   var editlink1 = $('<button type="button" />').attr('title', "Template erstellen");

	btnContainer.append( editImage ).append( deleteImage );

	editImage.click( function ()
	{
		var href = 'admin.php?adm=skins&amp;action=edittemplate&amp;id=&amp;skinid=364&amp;group=' + $( this ).parent().parents( 'div:first' ).attr( 'rel' );

		jPrompt( "Template Bezeichnung:", '', "Template erstellen...",
			function ( res )
			{
				if ( res ) {
					if ( !res.match( /^([a-z0-9_\-]+)$/i ) ) {
						jAlert( "Bitte nur folgende Zeichen verwenden: (A-Za-z0-9_ und Bindestrich)" );
					}
					else if ( !res.match( /^([a-z0-9])/i ) ) {
						jAlert( "Templates müssen mit (A-Za-z0-9) beginnen" );
					}
					else {
						href += '&amp;templatename=' + res;
						openTab( {url: href, obj: false, label: "Template erstellen", isSingleWindow: 0} );
					}
				}
			} );

	} );

	deleteImage.click( function ()
	{
		var groupName = $( this ).parent().parents( 'div:first' ).attr( 'rel' );

		// root group not allowed for delete
		if ( !groupName || groupName.toLowerCase() === 'root' ) {
			return;
		}
		else {
			var groupRow = $( this ).parent().parents( 'div:first' );
			jConfirm(
				"Sind Sie sicher, das die Template-Gruppe ´%s´ gelöscht werden soll?<br/>Achtung: Es werden alle dazugehörigen Templates unwiederbringlich gelöscht!"
					.replace( '%s', groupName ), "Template-Gruppe löschen...",
				function ( res )
				{
					if ( res ) {
						$.post( 'admin.php', {
							adm: 'skins',
							action: 'delgroup',
							group: groupName,
							skinid: '364',
							token: Config.get( 'token' )
						}, function ( data )
						{
							if ( Tools.responseIsOk( data ) ) {
								Notifier.info( data.msg );

								if ( $( '#' + Win.windowID ).find( '.subwindow:visible' ).data( 'currentGroup' ) == groupName ) {
									$( '#result-list', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).find( 'div:first' ).empty();
								}

								groupRow.slideUp( 400, function ()
								{
									$( this ).remove();
								} );
							}
							else {
								Notifier.error( data &amp;&amp; data.msg ? data.msg : "Unknown error" );
							}
						}, 'json' );
					}
				} );
		} // end else

	} );

	btnContainer.appendTo( $( this ).find( 'small' ) );

	$( this ).bind( 'dblclick.tpl', function ()
	{
		$( '#result-list', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).mask( 'Hole Templates...' );
		var rel = $( this ).attr( 'rel' );
		$( '#template-groups', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).find( 'div.active' ).removeClass( 'active' );
		$( this ).addClass( 'active' );
		$.post( 'admin.php', {
			adm: 'skins',
			action: 'templates',
			id: '364',
			tplgroup: rel,
			token: Config.get( 'token' )
		}, function ( data )
		{
			if ( Tools.responseIsOk( data ) ) {

				$( '#result-list', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).unmask();

				$( '#' + Win.windowID ).find( '.subwindow:visible' ).data( 'currentGroup', rel );

				buildTemplateResult( $( '#result-list', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).find( 'div:first' ), data );

				Tools.scrollBar( $( '#' + Win.windowID ).find( '.subwindow:visible' ).find( '#result-list' ).find( 'div:first' ) );

				setTimeout( function () { $( window ).trigger( 'resize' ); }, 500 );
			}
		}, 'json' );
	} );
} );

windowObj.find( '#search-template .search-string span' ).unbind( 'click.tpl' ).on( 'click.tpl', function ()
{
	$( '#qstr', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).val( '' );
	$( this ).hide();
	$( '#result-list', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).show();
	$( '#search-result-list', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).hide();
} );

windowObj.find( '#search-btn' ).unbind( 'click.tpl' ).on( 'click.tpl', function ()
{
	var q = $( '#qstr', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).val();
	if ( q.length ) {
		$( '#search-template .search-string span' ).show();
		$( '#result-list', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).mask( 'Suche Templates...' );

		$.post( 'admin.php', {
			adm: 'skins',
			action: 'search',
			skinid: '364',
			q: q,
			token: Config.get( 'token' )
		}, function ( data )
		{
			if ( Tools.responseIsOk( data ) ) {

				if ( data.results.length == 0 ) {
					jAlert( 'Es wurden keine Templates mit dem Suchmuster `%s` gefunden.'.replace( '%s', q ) );
				}
				else {
					data.templates = data.results;

					$( '#result-list', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).hide();
					$( '#result-list', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).unmask();

					$( '#search-result-list', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).show();
					$( '#search-result-list', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).find( 'div:first' ).empty();

					buildTemplateResult( $( '#' + Win.windowID ).find( '.subwindow:visible' ).find( '#search-result-list' ).find( 'div:first' ), data, true );
					Tools.scrollBar( $( '#' + Win.windowID ).find( '.subwindow:visible' ).find( '#search-result-list' ).find( 'div:first' ) );
				}
			}
		}, 'json' );
	}
	else {
		$( '#' + Win.windowID ).find( '.subwindow:visible' ).find( '#search-template .search-string span' ).trigger( 'click' );
	}
} );

windowObj.find( 'form' ).unbind( 'keyup.tpl keypress.tpl' ).bind( "keyup.tpl keypress.tpl", function ( e )
{
	var code = e.keyCode || e.which;
	if ( code == 13 ) {
		$( '#search-btn', $( '#' + Win.windowID ).find( '.subwindow:visible' ) ).trigger( 'click' );

		e.preventDefault();
		return false;
	}
} );

$( '#buttons-' + Win.windowID.replace( 'tab-', '' ).replace( 'content-', '' ) ).find( '.subbuttons:visible #regenerate-templates' ).unbind( 'click.tpl' ).on( 'click.tpl', function ( e )
{
	$( '#' + Win.windowID ).find( '.subwindow:visible' ).mask( "Regeneriere Templates..." );

	e.preventDefault();

	setTimeout( function ()
	{

		$.post( 'admin.php', {
			adm: 'skins',
			action: 'regenerate',
			skinid: '364',
			token: Config.get( 'token' )
		}, function ( data )
		{
			$( '#' + Win.windowID ).find( '.subwindow:visible' ).unmask();

			if ( Tools.responseIsOk( data ) ) {
				Notifier.info( data.msg );
			}
			else {
				Notifier.error( data &amp;&amp; data.msg ? data.msg : "Unknown error" );
			}
		}, 'json' );

	}, 100 );
} );

Core.addEvent( 'onResize', function ( tabContent )
{
	$( '#result-list,#search-result-list', tabContent ).height( $( '#content-container-inner' ).outerHeight() - $( 'div.template-groups-container', tabContent ).outerHeight( true ) - 50 );
} );

setTimeout( function ()
{
	$( window ).trigger( 'resize' );
}, 500 );
