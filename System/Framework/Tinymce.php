<?php

/**
 * DreamCMS 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP Version 5
 *
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Tinymce.php
 */
class Tinymce
{

	protected static $_tinyMCECoreButtons = 'newdocument fullpage bold italic underline strikethrough alignleft aligncenter alignright alignjustify styleselect formatselect fontselect fontsizeselect cut copy paste searchreplace bullist numlist outdent indent blockquote undo redo link unlink anchor image media code insertdatetime preview forecolor backcolor table hr removeformat subscript superscript charmap emoticons fullscreen ltr rtl spellchecker visualchars visualblocks nonbreaking template pagebreak restoredraft ';






	/**
	 * @var int
	 */
	protected static $areaIndex = 0;

	public static function getConfig ()
	{

		$sessionid  = session_id();
		$default    = 'en';
		$toolbar    = HTTP::input('toolbar');
		$toolbarpos = HTTP::input('toolbarpos');

		switch ( $toolbarpos )
		{
			case 'intern':
			case 'internal':
				$toolbarpos = 'internal';
				break;
			case 'external':
			case 'extern':
			default:
				$toolbarpos = 'external';
				break;
		}

		$editorurl = Settings::get('portalurl') . '/';
		$toolbar   = ( !empty( $toolbar ) ? $toolbar : 'Default' );


		$language = Locales::getShortLocale();
		if ( !$language )
		{
			$language = $default;
		}


		$extraTemplateCss  = self::getContentCss();
		$extraTemplatesStr = self::getContentTemplates();

		$extraTemplatesStr = implode(',', json_encode($extraTemplatesStr));

		list( $plugins, $toolbar_output, $_dummybtns ) = self::getTinyMceToolbars();

		$lang = CONTENT_TRANS;

		$u    = Settings::get('portalurl') . '/asset/js';
		$u2   = Settings::get('portalurl') . '/';
		$_sid = $sessionid;


		$cfg = <<<EOF
    // 1
if (typeof _filebrowser != 'function'){
  function _filebrowser(field_name, url, type, win) {    
    fileBrowserURL = "{$u2}Vendor/tinymce/pdw_file_browser/index.php?editor=tinymce&sid={$sessionid}&filter=" + type;
    tinyMCE.activeEditor.windowManager.open({
        title: "PDW File Browser",
        url: fileBrowserURL,
        width: 950,
        height: 650,
        inline: 0,
        maximizable: 1,
        close_previous: 0,
        toolbar: 'yes',
        menubar: 'yes', location: 'yes'
      },{
        window : win,
        input : field_name
      }
    );    
  }
}

window.tinymceConfig = {
        file_browser_callback : "_filebrowser",
        script_url : '{$path}/Vendor/tinymce/tiny_mce.js',
        // mode: "textareas",
        mode: "exact",
        theme: "advanced",
        // PDW Toggle Toolbars settings
        pdw_toggle_on : '1',
        pdw_toggle_toolbars : "2,3,4",
        /* disable the gecko spellcheck since AtD provides one */
        gecko_spellcheck: false,        
        /* the URL to the button image to display */
        atd_button_url              : "{$u2}Vendor/tinymce/plugins/AtD/atdbuttontr.gif",

        /* the URL of your proxy file */
        atd_rpc_url                 : "{$u2}Vendor/tinymce/plugins/AtD/server/proxy.php?lang=%s&url=",

        /* set your API key */
        atd_rpc_id                  : "dashnine",

        /* edit this file to customize how AtD shows errors */
        atd_css_url                 : "{$u2}Vendor/tinymce/plugins/AtD/css/content.css",

        /* this list contains the categories of errors we want to show */
        atd_show_types              : "Bias Language,Cliches,Complex Expression,Diacritical Marks,Double Negatives,Hidden Verbs,Jargon Language,Passive voice,Phrases to Avoid,Redundant Expression",

        /* strings this plugin should ignore */
        atd_ignore_strings          : "AtD,rsmudge",

        /* enable "Ignore Always" menu item, uses cookies by default. Set atd_ignore_rpc_url to a URL AtD should send ignore requests to. */
        atd_ignore_enable           : "false",

        /* add the AtD button to the first row of the advanced theme */
        // theme_advanced_buttons1_add : "AtD",
        
        
        
        inlinepopups_skin: 'dcms',
        //plugins: "save,pagelink,safari,dcmsmedia,dcmsphp,dcmspageindex,spellchecker,pagebreak,style,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,autoresize",
        plugins: "{$plugins}",

        
        content_css: 'html/css/tinymce.css,html/css/subcols.css,html/css/subcols_extended.css{$extraTemplateCss}',
        template_templates : [
            {$extraTemplatesStr}
        ],
        //skin: 'default',
        skin: 'dcms',
        //skin_variant : "silver",
        language : "{$language}",

        entity_encoding : "raw",
	disk_cache : false,

        fix_list_elements : true,
        fix_table_elements : true,

        convert_urls:false,
        relative_urls:true,
        remove_script_host:true,
        
        
        remove_linebreaks : false,
        /*force_br_newlines : true,
        remove_linebreaks : false,
        forced_root_block : 'p',
        force_br_newlines : false,
        force_p_newlines : true,
        */

        event_elements : "a,div,h1,h2,h3,h4,h5,h6,img,p,span",
      //  extended_valid_elements : "q[cite|class|title]",
        tabfocus_elements : ":prev,:next",
        forced_root_block : 'p',

        entities : "160,nbsp,60,lt,62,gt,173,shy",
        cleanup_on_startup : false,
        convert_fonts_to_spans: true,
        save_enablewhendirty : true,
        save_on_tinymce_forms : true,
        advimage_update_dimensions_onchange : true,
        spellchecker_languages : "+Deutsch=de,Englisch=en,Spanisch=es,Französisch=fr,Griechisch=el,Polnisch=pl",

        theme_advanced_blockformats : "div,p,address,pre,h1,h2,h3,h4,h5,h6",
        theme_advanced_font_sizes : "8px,9px,10px,11px,12px,13px,14px,15px,16px,17px,18px,19px,20px,21px,22px,23px,24px",
        save_callback : "TinyCallback.cleanXHTML",
        init_instance_callback : "TinyCallback.getScrollOffset",
        

        object_resizing : true,

        theme_advanced_toolbar_location : "external",
        //theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        theme_advanced_resize_horizontal : false,
        // file_browser_callback : "TinyMCE_Filebrowser",
        
        /*
        valid_elements : "@[id|class|style|title|dir<ltr?rtl|lang|xml::lang|onclick|ondblclick|"
        + "onmousedown|onmouseup|onmouseover|onmousemove|onmouseout|onkeypress|"
        + "onkeydown|onkeyup],a[rel|rev|charset|hreflang|tabindex|accesskey|type|"
        + "name|href|target|title|class|onfocus|onblur],pre[class|id|title|style],strong/b,em/i,strike,u,"
        + "#p,-ol[type|compact],-ul[type|compact],-li,br,img[longdesc|usemap|"
        + "src|border|alt=|title|hspace|vspace|width|height|align],-sub,-sup,"
        + "-blockquote,-table[border=0|cellspacing|cellpadding|width|frame|rules|"
        + "height|align|summary|bgcolor|background|bordercolor],-tr[rowspan|width|"
        + "height|align|valign|bgcolor|background|bordercolor],tbody,thead,tfoot,"
        + "#td[colspan|rowspan|width|height|align|valign|bgcolor|background|bordercolor"
        + "|scope],#th[colspan|rowspan|width|height|align|valign|scope],caption,-div,"
        + "span,address,hr[size|noshade],-font[face"
        + "|size|color],dd,dl,dt,cite,abbr,acronym,del[datetime|cite],ins[datetime|cite],"
        + "object[classid|width|height|codebase|*],param[name|value|_value],embed[type|width"
        + "|height|src|*],script[src|type],map[name],area[shape|coords|href|alt|target],bdo,"
        + "button,col[align|char|charoff|span|valign|width],colgroup[align|char|charoff|span|"
        + "valign|width],dfn,fieldset,form[action|accept|accept-charset|enctype|method],"
        + "input[accept|alt|checked|disabled|maxlength|name|readonly|size|src|type|value],"
        + "kbd,label[for],legend,noscript,optgroup[label|disabled],option[disabled|label|selected|value],"
        + "q[cite],samp,select[disabled|multiple|name|size],small,"
        + "textarea[cols|rows|disabled|name|readonly],tt,var,big,div[*],a[*],pre[class|id|title|style],p[*]",
        */
        extended_valid_elements : "div[*],a[*],pre[*],p[*],h1[*],h2[*],h3[*],h4[*],h5[*],h6[*],php[*]",
        

        custom_tag_ns_prefix : "",
        custom_tags : "pageindex,php",
       // use_native_selects: true,
        apply_source_formatting : true,
        // height : "400",
		{$toolbar_output}
        savecallback : false,
        submit_patch : true,
        onchange_callback : "TinyCallback.onChangeHandler",
    //    handle_event_callback: 'TinyCallback.handleEventCallback',
     //   handle_node_change_callback: 'TinyCallback.nodeChangeHandler',
        visualblocks_default_state: true,
        end_container_on_empty_block: true,
                
        // Schema is HTML5 instead of default HTML4
        schema: "html5",

        // End container block element when pressing enter inside an empty block
        end_container_on_empty_block: true,
                
        style_formats: [
            {title: 'Headers'},
                {title: 'h1', block: 'h1'},
                {title: 'h2', block: 'h2'},
                {title: 'h3', block: 'h3'},
                {title: 'h4', block: 'h4'},
                {title: 'h5', block: 'h5'},
                {title: 'h6', block: 'h6'},

            {title: 'Blocks' },
                {title: 'p', block: 'p'},
                {title: 'div', block: 'div'},
                {title: 'pre', block: 'pre'},
                
            {title : 'Source Code', block : 'code', classes : 'prettyprint', exact: true},
                
            {title: 'Containers'},
                {title: 'section', block: 'section', wrapper: true, merge_siblings: false},
                {title: 'article', block: 'article', wrapper: true, merge_siblings: false},
                {title: 'blockquote', block: 'blockquote', wrapper: true},
                {title: 'hgroup', block: 'hgroup', wrapper: true},
                {title: 'aside', block: 'aside', wrapper: true},
                {title: 'figure', block: 'figure', wrapper: true}
            
        ],
                
                
        setup : function(ed) {

            ed.addCommand('mceDirtySet', function() {
                ed.mceDirtySetRuntimeTimer = window.setTimeout(function() {
                    Form.setDirty(false, $('#' + ed.id).parents('form:first'));
                }, 600);
            });


            ed.onKeyUp.add(function(ed, e) {
                if (ed.mceDirtySetRuntimeTimer) { // Wenn ein Timeout existiert, dieses zurücksetzen
                    window.clearTimeout(ed.mceDirtySetRuntimeTimer);
                }
                ed.execCommand('mceDirtySet');
            });

            var configKeyUpEvent = (typeof Config == 'object' && typeof Config.get('onTinyMCEKeyUp') === 'function' ? Config.get('onTinyMCEKeyUp') : false );
            if (typeof configKeyUpEvent === 'function')
            {
                ed.onKeyUp.add(function(ed, e) {
                        configKeyUpEvent(ed, e);
                });
            }
            
            ed.onInit.add(function(ed, e) { 
                
                var doc = ed.getDoc();
                var area = $('#'+ ed.id);
                var toolbar = $('#'+ ed.id + '_external');
                var tbpos = $('#'+ ed.id).attr('toolbarpos'), editorid = ed.id, windowID = $('#'+ ed.id).parents('.isWindowContainer:first').attr('id');

                if ( !$('#'+ ed.id).hasClass('internal') || area.attr('toolbar') == 'external' || tbpos == 'external' || tbpos == 'extern' )
                {

                    // $('#'+ ed.id).next().find('iframe').hide();
                    if( toolbar )
                    {
                        Doc.setTinyMceToolbar(toolbar, ed, ed.id, 'extern');
                    }

                    Doc.disableTinyMceToolbar(toolbar, e, ed.id, 'extern');

                    tinymce.dom.Event.add(ed.getWin(), 'blur', function(e) { Doc.disableTinyMceToolbar(toolbar, e, ed.id, windowID, 'extern'); setTimeout(function(){Win.refreshContentHeight();}, 1); } );
                    tinymce.dom.Event.add(ed.getWin(), 'focus', function(e) {  Doc.enableTinyMceToolbar(toolbar, e, ed.id, windowID, 'extern'); setTimeout(function(){Win.refreshContentHeight();}, 1); } );

                    toolbar.mouseover(function(){
                        Doc.editor_onmenu=true;
                    });

                    toolbar.mouseout(function(){
                        Doc.editor_onmenu=false;
                    }); 
                }
                    
                if ($('#'+ ed.id).hasClass('internal') || $('#'+ ed.id).attr('toolbar') == 'internal' || tbpos == 'internal' ) {

                    if( toolbar )
                    {
                        Doc.setTinyMceToolbar(toolbar, ed, ed.id, 'intern');
                    }

                    Doc.disableTinyMceToolbar(toolbar, e, ed.id, 'intern');

                    tinymce.dom.Event.add(ed.getWin(), 'blur', function(e) { Doc.disableTinyMceToolbar(toolbar, e, ed.id, windowID, 'intern'); setTimeout(function(){Win.refreshContentHeight();}, 1); } );
                    tinymce.dom.Event.add(ed.getWin(), 'focus', function(e) {  Doc.enableTinyMceToolbar(toolbar, e, ed.id, windowID, 'intern'); setTimeout(function(){Win.refreshContentHeight();}, 1); } );

                    toolbar.mouseover(function(){
                        Doc.editor_onmenu=true;
                    });

                    toolbar.mouseout(function(){
                        Doc.editor_onmenu=false;
                    }); 
                }
    
                ed.tinyMCEPopup.onInit.add(function(ed) {
                    Win.prepareWindowFormUi(ed, true);
                });
            });            

        },
        
}
EOF;

		header('Content-Type: application/javascript');
		header("Expires: Mon, 20 Jul 1995 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		echo $cfg;
		exit;
	}

    /**
     * @return string
     */
    public static function getContentCss ()
	{

		$extraTemplateCss = '';

		if ( is_file(PUBLIC_PATH . 'html/css/tinymce4/templates.css') )
		{
			// $extraTemplateCss = ',public/html/css/subcols.css';
			$extraTemplateCss = ',html/css/contentgrid.css';
			$extraTemplateCss .= ',html/css/tinymce4/templates.css';
		}

		return $extraTemplateCss;
	}

    /**
     * @return array
     */
    public static function getContentTemplates ()
	{

		$files = Library::getFiles(VENDOR_PATH . 'tinymce/templates/', false);
		foreach ( $files as $r )
		{
			$ext = strtolower(Library::getExtension($r[ 'filename' ]));
			if ( $ext == 'html' || $ext == 'htm' )
			{
				$extraTemplates[ ] = array (
					'title'   => preg_replace('#(_|\-)#', ' ', preg_replace('#\.[a-z]*$#', '', $r[ 'filename' ])),
					'src'     => 'Vendor/tinymce/templates/' . $r[ 'filename' ],
					'content' => file_get_contents(VENDOR_PATH . 'tinymce/templates/' . $r[ 'filename' ])
				);
				//'{title : "' . preg_replace('#(_|\-)#', ' ', preg_replace('#\.[a-z]*$#', '', $r[ 'filename' ])) . '", src : "Vendor/tinymce/templates/' . $r[ 'filename' ] . '"}';
			}
		}

		return $extraTemplates;

	}

    /**
     * @param null $toolbars
     * @return array
     */
    public static function getTinyMceToolbars ( $toolbars = null )
	{

		$tadv_toolbars = ( is_array($toolbars) ? $toolbars : User::getEditorSettings() );

		if ( !is_array($tadv_toolbars) )
		{
			include( DATA_PATH . 'system/defaultConfig_tinymceEditor.php' );

			// default toolbars
			$tadv_toolbars = $GLOBALS[ 'default_tinymce_toolbar' ];
		}
		else
		{
			$tadv_toolbars[ 'toolbar_1' ] = count($tadv_toolbars[ 'toolbar_1' ]) ? (array)$tadv_toolbars[ 'toolbar_1' ] : array ();
			$tadv_toolbars[ 'toolbar_2' ] = count($tadv_toolbars[ 'toolbar_2' ]) ? (array)$tadv_toolbars[ 'toolbar_2' ] : array ();
			$tadv_toolbars[ 'toolbar_3' ] = count($tadv_toolbars[ 'toolbar_3' ]) ? (array)$tadv_toolbars[ 'toolbar_3' ] : array ();
			$tadv_toolbars[ 'toolbar_4' ] = count($tadv_toolbars[ 'toolbar_4' ]) ? (array)$tadv_toolbars[ 'toolbar_4' ] : array ();
		}



		$_btns = array ();

		if ( TINYMCE_VERSION == 4 )
		{
			$dirs = self::getAllTinyMCEPlugins();

			$tmp = array ();
			foreach ( $dirs as $r )
			{
				$tmp[ ] = $r[ 'dirname' ];
			}

			$cores       = explode(' ', self::$_tinyMCECoreButtons);
			$convertBtns = array ();

			foreach ( $tadv_toolbars as &$buttons )
			{
				if ( empty( $buttons ) )
				{
					continue;
				}
				else
				{
					foreach ( $buttons as $k => &$v )
					{
						if ( stripos($v, 'separator') === false )
						{
							if ( $v == 'search' || $v == 'replace' )
							{
								if ( isset( $convertBtns[ 'searchreplace' ] ) )
								{
									unset( $buttons[ $k ] );
								}
								else
								{
									$v                              = 'searchreplace';
									$convertBtns[ 'searchreplace' ] = 'searchreplace';
								}
							}

							if ( $v == 'removeformat' || $v == 'cleanup' )
							{
								if ( isset( $convertBtns[ 'removeformat' ] ) )
								{
									unset( $buttons[ $k ] );
								}
								else
								{
									$v                             = 'removeformat';
									$convertBtns[ 'removeformat' ] = 'removeformat';
								}
							}

							if ( $v == 'inserttime' || $v == 'insertdate' )
							{
								if ( isset( $convertBtns[ 'insertdatetime' ] ) )
								{
									unset( $buttons[ $k ] );
								}
								else
								{
									$v                               = 'insertdatetime';
									$convertBtns[ 'insertdatetime' ] = 'inserttime';
								}
							}

							if ( $v == 'justifyfull' )
							{
								$v = 'alignjustify';
							}

							if ( $v == 'justifyleft' )
							{
								$v = 'alignleft';
							}
							if ( $v == 'justifyright' )
							{
								$v = 'alignright';
							}
							if ( $v == 'justifycenter' )
							{
								$v = 'aligncenter';
							}
							if ( $v == 'sub' )
							{
								$v = 'subscript';
							}
							if ( $v == 'sup' )
							{
								$v = 'superscript';
							}
							if ( $v == 'mcegooglemaps' )
							{
								$v = 'googlemaps';
							}
						}
					}
				}
			}


			$i = 0;

			#print_r($tadv_toolbars);exit;

			foreach ( $tadv_toolbars as $key => &$buttons )
			{
				$i++;
				if ( empty( $buttons ) )
				{
                    $tadv_toolbars[ $key ] = array ();
					continue;
				}


				if ( !isset( $btns[ "toolbar_$i" ] ) )
				{
					$_btns[ "toolbar_$i" ] = array ();
				}

				foreach ( $buttons as $k => &$v )
				{
					if ( stripos($v, 'separator') !== false )
					{
						$buttons[ (int)$k ] = '|';
					}
					else
					{
						if ( empty( $v ) )
						{
							unset( $buttons[ $k ] );
						}
						else
						{
							if ( !in_array($v, $tmp) && !in_array($v, $cores) )
							{
								unset( $buttons[ $k ] );
							}
						}
					}
				}

				//$_btns[ "toolbar_$i" ] = $buttons;
			}



			foreach ( $tadv_toolbars as $i => &$row )
			{
				$c = count($row);

				if ( isset( $row[ $c - 1 ] ) && isset($row[ $idx - 1 ]) && $row[ $c - 1 ] == '|' )
				{
					unset( $row[ $c - 1 ] );
				}


				foreach ( $row as $idx => $r )
				{
					if ( ( $r == '|' && isset($row[ $idx - 1 ]) && $row[ $idx - 1 ] == '|' ) )
					{
						unset( $row[ $idx ] );
					}
				}
			}

			$btns = $tadv_toolbars;


			$toolbar_output = ( count($tadv_toolbars[ 'toolbar_1' ]) ? "\n" . 'theme_advanced_buttons1 : "' . implode(',', $tadv_toolbars[ 'toolbar_1' ]) . '",' : '' );

			if ( count($tadv_toolbars[ 'toolbar_2' ]) )
			{
				$toolbar_output .= "\n" . 'theme_advanced_buttons2 : "' . implode(',', $tadv_toolbars[ 'toolbar_2' ]) . '",';
			}

			if ( count($tadv_toolbars[ 'toolbar_3' ]) )
			{
				$toolbar_output .= "\n" . 'theme_advanced_buttons3 : "' . implode(',', $tadv_toolbars[ 'toolbar_3' ]) . '",';
			}

			if ( count($tadv_toolbars[ 'toolbar_4' ]) )
			{
				$toolbar_output .= "\n" . 'theme_advanced_buttons4 : "' . implode(',', $tadv_toolbars[ 'toolbar_4' ]) . '",';
			}

		}
		else
		{


			$list       = false;
			$image      = false;
			$link       = false;
			$hidden_row = 0;
			$i          = 0;
			foreach ( $tadv_toolbars as $toolbar )
			{
				$l = $t = false;
				$i++;

				if ( empty( $toolbar ) )
				{
					$btns[ "toolbar_$i" ] = array ();
					continue;
				}


				if ( $i == 1 )
				{
					$toolbar[ ] = 'visualchars';
					$toolbar[ ] = 'visualblocks';

					$toolbar[ ] = '|';
					$toolbar[ ] = 'pdw_toggle';
				}

				foreach ( $toolbar as $k => $v )
				{
					if ( strpos($v, 'separator') !== false )
					{
						$toolbar[ $k ] = '|';
					}

					if ( 'layer' == $v )
					{
						$l = $k;
					}

					if ( 'image' == $v )
					{
						$image = true;
					}
					if ( 'lists' == $v )
					{
						$list = true;
					}
					if ( 'link' == $v )
					{
						$link = true;
					}


					if ( 'tablecontrols' == $v )
					{
						$t = 'delete_table,' . $k;
					}

					if ( 'iespell' == $v )
					{
						$t = 'spellchecker';
					}

					if ( 'spellchecker' == $v )
					{
						$toolbar[ $k ] = 'AtD';
					}

					if ( empty( $v ) )
					{
						unset( $toolbar[ $k ] );
					}
				}

				if ( $l !== false )
				{
					array_splice($toolbar, $l, 1, array (
					                                    'insertlayer',
					                                    'moveforward',
					                                    'movebackward',
					                                    'absolute'
					                              ));
				}

				if ( 'tablecontrols' == $v )
				{
					// array_splice( $toolbar, $t + 1, 0, 'delete_table,'  );
				}

				$btns[ "toolbar_$i" ] = $toolbar;
			}

			$toolbar_output = ( count($btns[ 'toolbar_1' ]) ? "\n" . 'theme_advanced_buttons1 : "' . implode(',', $btns[ 'toolbar_1' ]) . '",' : '' );

			if ( count($btns[ 'toolbar_2' ]) )
			{
				$toolbar_output .= ( count($btns[ 'toolbar_2' ]) ? "\n" . 'theme_advanced_buttons2 : "' . implode(',', $btns[ 'toolbar_2' ]) . '",' : 'theme_advanced_buttons2 : "|"' );
			}
			else
			{
				$toolbar_output .= "\n" . 'theme_advanced_buttons2 : "",';
			}

			if ( count($btns[ 'toolbar_3' ]) )
			{
				$toolbar_output .= ( count($btns[ 'toolbar_3' ]) ? "\n" . 'theme_advanced_buttons3 : "' . implode(',', $btns[ 'toolbar_3' ]) . '",' : 'theme_advanced_buttons3 : "|",' );
			}
			else
			{
				$toolbar_output .= "\n" . 'theme_advanced_buttons3 : "",';
			}


			if ( count($btns[ 'toolbar_4' ]) )
			{
				$toolbar_output .= ( count($btns[ 'toolbar_4' ]) ? "\n" . 'theme_advanced_buttons4 : "' . implode(',', $btns[ 'toolbar_4' ]) . '",' : '' );
			}

		}


		$_allbtns = array_merge($btns['toolbar_1'], $btns['toolbar_2'], $btns['toolbar_3'], $btns['toolbar_4'] );


		$allbtns = array_unique($_allbtns);
		$plugins = self::getTinyMcePlugins($allbtns, $btns);


		return array ( $plugins, $toolbar_output, $btns );
	}

	/**
	 *
	 * @return array
	 */
	public static function getAllTinyMCEPlugins ()
	{

		static $dirs;

		if ( !is_array($dirs) )
		{
			$dirs = array ();

			if ( TINYMCE_VERSION == 4 )
			{
				$dirs = Library::getDirs(VENDOR_PATH . 'tinymce4/plugins/');
			}
			elseif ( TINYMCE_VERSION < 4 )
			{
				$dirs = Library::getDirs(VENDOR_PATH . 'tinymce/plugins/');
			}
		}

		return $dirs;
	}

    /**
     * @param $allbtns
     * @param $dcmstb
     * @return array
     */
    public static function getTinyMce4Plugins ( $allbtns, $dcmstb )
	{

		$dirs  = self::getAllTinyMCEPlugins();
		$cores = explode(' ', self::$_tinyMCECoreButtons);


		$plugins[ ] = 'noneditable';
		$plugins[ ] = 'nonbreaking';
		$plugins[ ] = 'visualblocks';
		$plugins[ ] = 'visualchars';
		$plugins[ ] = 'wordcount';
		$plugins[ ] = 'contextmenu';
		$plugins[ ] = 'directionality';

		if ( in_array('mcegooglemaps', $allbtns) )
		{
			$plugins[ ] = 'googlemaps';
		}

		if ( in_array('bullist', $allbtns) || in_array('numlist', $allbtns) )
		{
			$plugins[ ] = 'advlist';
		}

		//
		if ( in_array('spellchecker', $allbtns) || in_array('iespell', $cores) )
		{
			$plugins[ ] = 'atd';
		}


		foreach ( $dirs as $r )
		{
			if ( $r[ 'dirname' ] == 'fullpage' || $r[ 'dirname' ] == 'spellchecker' || $r[ 'dirname' ] == 'iespell' )
			{
				continue;
			}

			if ( in_array($r[ 'dirname' ], $allbtns) || in_array($r[ 'dirname' ], $cores) )
			{
				$plugins[ ] = $r[ 'dirname' ];
			}
		}

		return array_unique($plugins);
	}

    /**
     * @param $allbtns
     * @param $dcmstb
     * @return array|string
     */
    public static function getTinyMcePlugins ( $allbtns, $dcmstb )
	{

		if ( TINYMCE_VERSION == 4 )
		{
			return self::getTinyMce4Plugins($allbtns, $dcmstb);
		}


		$plugins[ ] = 'pdw';
		$plugins[ ] = 'save';
		$plugins[ ] = 'safari';
		$plugins[ ] = 'noneditable';
		$plugins[ ] = 'visualblocks';
		$plugins[ ] = 'visualchars';
		//   $plugins[] = 'styleselect';


		if ( in_array('w3cvalidate', $allbtns) )
		{
			$plugins[ ] = 'w3cvalidate';
		}

		if ( in_array('youtube', $allbtns) )
		{
			$plugins[ ] = 'youtube';
		}

		if ( in_array('youtubeIframe', $allbtns) )
		{
			$plugins[ ] = 'youtubeIframe';
		}

		if ( in_array('loremipsum', $allbtns) )
		{
			$plugins[ ] = 'loremipsum';
		}

		if ( in_array('tagwrap', $allbtns) )
		{
			$plugins[ ] = 'tagwrap';
		}

		if ( in_array('dcmsmedia', $allbtns) )
		{
			$plugins[ ] = 'dcmsmedia';
		}
		if ( in_array('dcmspageindex', $allbtns) )
		{
			$plugins[ ] = 'dcmspageindex';
		}
		if ( in_array('dcmsphp', $allbtns) )
		{
			$plugins[ ] = 'dcmsphp';
		}

		if ( in_array('contentgrid', $allbtns) )
		{
			$plugins[ ] = 'contentgrid';
		}
		if ( in_array('contenttabs', $allbtns) )
		{
			$plugins[ ] = 'contenttabs';
		}


		if ( in_array('template', $allbtns) )
		{
			$plugins[ ] = 'template';
		}

		if ( in_array('imgmap', $allbtns) )
		{
			$plugins[ ] = 'imgmap';
		}
		if ( in_array('mcegooglemaps', $allbtns) )
		{
			$plugins[ ] = 'mcegooglemaps';
		}
		if ( in_array('media', $allbtns) )
		{
			$plugins[ ] = 'media';
		}
		if ( in_array('advhr', $allbtns) )
		{
			$plugins[ ] = 'advhr';
		}
		/*
		  if ( in_array( 'insertlayer', $allbtns ) )
		  {
		  $plugins[] = 'layer';
		  }
		 */

		$plugins[ ] = 'layer';

		if ( in_array('visualchars', $allbtns) )
		{
			$plugins[ ] = 'visualchars';
		}
		if ( in_array('nonbreaking', $allbtns) )
		{
			$plugins[ ] = 'nonbreaking';
		}
		if ( in_array('styleprops', $allbtns) )
		{
			$plugins[ ] = 'style';
		}
		if ( in_array('emotions', $allbtns) )
		{
			$plugins[ ] = 'emotions';
		}
		if ( in_array('insertdate', $allbtns) || in_array('inserttime', $allbtns) )
		{
			$plugins[ ] = 'insertdatetime';
		}

		if ( in_array('fullscreen', $allbtns) )
		{
			$plugins[ ] = 'fullscreen';
		}


		if ( in_array('tablecontrols', $allbtns) )
		{
			$plugins[ ] = 'table';
		}
		if ( in_array('print', $allbtns) )
		{
			$plugins[ ] = 'print';
		}
		if ( in_array('preview', $allbtns) )
		{
			$plugins[ ] = 'preview';
		}

		$useSpellChecker = false;

		if ( in_array('iespell', $allbtns) )
		{
			$plugins[ ] = 'iespell';
			$plugins[ ] = 'spellchecker';

			$useSpellChecker = true;
		}
		if ( in_array('spellchecker', $allbtns) && !in_array('spellchecker', $plugins) )
		{
			$plugins[ ] = 'spellchecker';

			$useSpellChecker = true;
		}


		if ( $useSpellChecker )
		{
			$plugins[ ] = 'AtD';
		}


		if ( in_array('pagebreak', $allbtns) )
		{
			$plugins[ ] = 'pagebreak';
		}

		if ( in_array('search', $allbtns) || in_array('replace', $allbtns) )
		{
			$plugins[ ] = 'searchreplace';
		}

		if ( in_array('cite', $allbtns) || in_array('ins', $allbtns) || in_array('del', $allbtns) || in_array('abbr', $allbtns) || in_array('acronym', $allbtns) || in_array('attribs', $allbtns) )
		{
			$plugins[ ] = 'xhtmlxtras';
		}

		if ( $link )
		{
			$plugins[ ] = 'advlink';
		}
		if ( $list )
		{
			$plugins[ ] = 'advlist';
		}
		if ( $image )
		{
			$plugins[ ] = 'advimage';
		}

		$plugins[ ] = 'contextmenu';


		#$plugins[] = 'autoresize';
		$plugins[ ] = 'inlinepopups';

		#$plugins[] = 'fancybox';


		#$plugins[] = 'noneditable';
		return implode(',', $plugins);

	}

    /**
     * @param        $_content
     * @param        $fieldName
     * @param string $width
     * @param int $height
     * @param int $col
     * @param int $rows
     * @param string $toolbar
     * @param string $toolbarPos
     * @param string $extraClasses
     * @return string
     */
	public static function getTextarea ( $_content, $fieldName, $width = '100%', $height = 300, $col = 60, $rows = 3, $toolbar = 'Default', $toolbarPos = 'external', $extraClasses = '' )
	{

		self::$areaIndex++;

		$idName = $fieldName . '-' . self::$areaIndex . '-' . time();

		$value = htmlspecialchars($_content);


		return <<<EOF
<textarea name="{$fieldName}" cols="{$col}" rows="{$rows}" class="tinymce-editor {$toolbarPos} {$extraClasses}" toolbarpos="{$toolbarPos}" toolbar="{$toolbar}" id="tinymce-{$idName}" style="width:{$width};height: {$height}px">{$value}</textarea>
EOF;
	}

}
