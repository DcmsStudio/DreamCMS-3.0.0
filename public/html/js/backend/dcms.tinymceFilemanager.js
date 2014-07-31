/**
 * Created by marcel on 16.04.14.
 */


function getWin() {
	return(!window.frameElement && window.dialogArguments) || opener || parent || top;
}

var w = getWin();
var editor = w.tinymce ? w.tinymce.EditorManager.activeEditor : null;
var wm = w.tinymce ? editor.windowManager : null;
var params = w.tinymce ? wm.getParams() : {};
var trans = w.tinymce ? w.tinymce.util.I18n : null;


function getSelectedFile() {
	return $( 'body' ).find( '#fm' ).getSelectedFile();
}




function initFM () {
	$( 'body' ).addClass( 'no-padding' );
	$( 'body' ).find( '#fm' ).parent().addClass( 'no-padding' );
	$( 'body' ).find( '#fm' ).parent().parent().addClass( 'no-scroll' ); //.jScrollPaneRemove();

	//  var hash =  Win.windowID.replace('content-', '').replace('tab-', '');

	$( 'body' ).attr( 'isfileman', true );
	$( 'body' ).find( '#fm' ).Filemanager( {
		connectorUrl: 'admin.php?adm=fileman',
		mode: 'tinymce',
		dirSep: '/',
		toolbarContainer: $( '#fm-toolbar' ),
		externalScrollbarContainer: '.pane',
		isInlineFileman: true,
		selectFile: params.selectfile || false,
		scrollTo: function ( container, toObject )
		{
			if ( container === 'tree' ) {
				Tools.scrollBar( $( 'body' ).find( '#fm .treelistInner' ), toObject );
			}
		},
		externalScrollbarDestroy: function ()
		{

		},
		externalScrollbarCreate: function ()
		{
			var _win = $( 'body' );

			_win.find( '#fm .treelistInner,#fm .body' ).css( {overflow: ''} );
			Tools.scrollBar( _win.find( '#fm .treelistInner' ) );
			Tools.scrollBar( _win.find( '#fm .listview .body>:first-child' ) );
			Tools.scrollBar( _win.find( '#fm .iconview.body' ) );

			setTimeout( function ()
			{
				//if ($('#fm .foldercontentInner .body', _win).hasClass('jspScrollable'))
				// {
				_win.find( '#fm' ).fixTableWidth();
				// }
			}, 80 );

		},
		onResizeStart: function ()
		{
			var _win = $( 'body' );

			_win.find( '#fm .treelistInner,#fm .body' ).css( {width: '', overflow: ''} );

		},
		onResizeStop: function ()
		{
			var win = $( 'body' );

			win.find( '#fm' ).resizePanels( function ()
			{
				var win = $( 'body' );
				win.find( '#fm .treelistInner,#fm .body' ).css( {overflow: ''} );
				Tools.scrollBar( win.find( '#fm .treelistInner' ) );
				Tools.scrollBar( win.find( '#fm .listview .body>:first-child' ) );
				Tools.scrollBar( win.find( '#fm .iconview.body' ) );

				setTimeout( function ()
				{
					//   if ($('#fm .foldercontentInner .body', win).hasClass('jspScrollable'))
					//   {
					win.find( '#fm' ).fixTableWidth();
					//    }
				}, 80 );

			} );
		},
		onBeforeLoad: function ()
		{

		},
		onAfterLoad: function ()
		{
			//var win = $('#'+ Win.windowID);
			// $('#fm .treelistInner,#fm .foldercontentInner .body', $('#'+ Win.windowID) ).jScrollPaneRemove();
			var win = $( 'body' );
			win.find( '#fm .treelistInner,#fm .body', $( '#' + Win.windowID ) ).css( {overflow: ''} );
			Tools.scrollBar( win.find( '#fm .treelistInner' ) );
			Tools.scrollBar( win.find( '#fm .listview .body>:first-child' ) );
			Tools.scrollBar( win.find( '#fm .iconview.body' ) );

			setTimeout( function ()
			{
				win.find( '#fm' ).resizePanels( function ()
				{
					win.find( '#fm .treelistInner,#fm .body' ).css( {overflow: ''} );
					Tools.scrollBar( win.find( '#fm .treelistInner' ) );
					Tools.scrollBar( win.find( '#fm .listview .body>:first-child' ) );
					Tools.scrollBar( win.find( '#fm .iconview.body' ) );

					setTimeout( function ()
					{
						win.find( '#fm' ).fixTableWidth();
					}, 150 );
				} );

			}, 400 );
		}
	} );

	$( 'body' ).find( '#fm' ).registerEvents({
		onSelectFile: function(path, width, height) {

			var mcewindow = wm.windows[0];
			if (!mcewindow || !mcewindow._id) {
				console.error('Invalid TinyMCE');
				return;
			}

			if ( editor.filebrowserParent.find( '#src').length ) {
				editor.filebrowserParent.find( '#src').value( path );
				editor.filebrowserParent.find( '#width').value( width );
				editor.filebrowserParent.find( '#height').value( height );
				editor.filebrowser.close();
				delete editor.filebrowser;
				delete editor.filebrowserParent;
				return;
			}

			alert('TinyMCE Input field not found!');
		}
	});



}