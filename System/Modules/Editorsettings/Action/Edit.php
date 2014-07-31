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
 * @package      Editorsettings
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit.php
 */
class Editorsettings_Action_Edit extends Controller_Abstract
{

    /**
     * @var array
     */
    private $options = array(
        'advlink1'    => 0,
        'advimage'    => 1,
        'editorstyle' => 0,
        'hideclasses' => 0,
        'contextmenu' => 0,
        'no_autop'    => 0
    );

    /**
     * @var array
     */
    private $plugins = array(
        'style',
        'emotions',
        'print',
        'searchreplace',
        'xhtmlxtras',
        'advimage'
    );

    /**
     * @var array
     */
    private $toolbars = array(
        'toolbar_1' => array(
            'undo',
            'redo',
            'separator8',
            'bold',
            'italic',
            'strikethrough',
            'underline',
            'separator1',
            'bullist',
            'numlist',
            'outdent',
            'indent',
            'separator2',
            'justifyleft',
            'justifycenter',
            'justifyright',
            'separator3',
            'link',
            'unlink',
            'separator4',
            'image',
            'styleprops',
            'separator12',
            'spellchecker',
            'search',
            'separator6',

            'fullscreen',
            'separator7',
            'pdw'
        ),
        'toolbar_2' => array(
            'styleselect',
            'formatselect',
            'fontsizeselect',
            'pastetext',
            'pasteword',
            'removeformat',
            'separator9',
            'charmap',
            'separator10',
            'forecolor',
            'backcolor',
            'separator11',
            'sup',
            'sub',
            'media',
        ),
        'toolbar_3' => array(),
        'toolbar_4' => array()
    );

    /**
     * @var array
     */
    private $btns1 = array(
        'bold',
        'italic',
        'strikethrough',
        'underline',
        'separator',
        'bullist',
        'numlist',
        'outdent',
        'indent',
        'separator',
        'justifyleft',
        'justifycenter',
        'justifyright',
        'separator',
        'link',
        'unlink',
        'separator',
        'image',
        'styleprops',
        'separator',
        'separator',
        'spellchecker',
        'search',
        'separator',
        'fullscreen',
        'separator',
        'pdw'
    );

    /**
     * @var array
     */
    private $btns2 = array(
        'fontsizeselect',
        'formatselect',
        'pastetext',
        'pasteword',
        'removeformat',
        'separator',
        'charmap',
        'print',
        'separator',
        'forecolor',
        'backcolor',
        'emotions',
        'separator',
        'sup',
        'sub',
        'media',
        'separator',
        'undo',
        'redo',
        'attribs'
    );

    /**
     * @var array
     */
    private $btns3 = array();

    /**
     * @var array
     */
    private $btns4 = array();

    /**
     * @var array
     */
    private $basic = array(
        'bold',
        'italic',
        'strikethrough',
        'underline',
        'bullist',
        'numlist',
        'outdent',
        'indent',
        'justifyleft',
        'justifycenter',
        'justifyright',
        'justifyfull',
        'cut',
        'copy',
        'paste',
        'link',
        'unlink',
        'image',
        'search',
        'replace',
        'fontselect',
        'fontsizeselect',
        'fullscreen',
        'styleselect',
        'formatselect',
        'forecolor',
        'backcolor',
        'pastetext',
        'pasteword',
        'removeformat',
        'cleanup',
        'spellchecker',
        'charmap',
        'print',
        'undo',
        'redo',
        'tablecontrols',
        'cite',
        'ins',
        'del',
        'abbr',
        'acronym',
        'attribs',
        'layer',
        'advhr',
        'code',
        'visualchars',
        'nonbreaking',
        'sub',
        'sup',
        'visualaid',
        'insertdate',
        'inserttime',
        'anchor',
        //         'styleprops',
        'emotions',
        'media',
        'blockquote',
        'separator',
        'preview',
        '|'
    );





    private $all_plugins = array(
        'advlist',
        'atd',
        'autolink',
        'anchor',
        'autoresize',
        'autosave',
        'code',
        'contextmenu',
        'directionality',
        'emoticons',
        'importcss',
        'insertdatetime',
        'nonbreaking',
        'print',
        'searchreplace',
        'table',
        'visualblocks',
        'visualchars',
        'link',
        'compat3x',
        'contextmenu',
        'noneditable',
        'lists',
        'bbcode',
        'layer',
        'tabfocus',
        'legacyoutput',
        'wordcount',
        'example',
        'example_dependency'
    );

    private $default_settings = array(
        'toolbar_1' => 'undo,redo,bold,italic,blockquote,bullist,numlist,alignleft,aligncenter,alignright,link,unlink,table,fullscreen',
        'toolbar_2' => 'formatselect,alignjustify,strikethrough,outdent,indent,pastetext,removeformat,charmap,emoticons,forecolor',
        'toolbar_3' => '',
        'toolbar_4' => '',
    );


    private $buttons = array(
        // Core
        'bold'           => 'Bold',
        'italic'         => 'Italic',
        'underline'      => 'Underline',
        'strikethrough'  => 'Strikethrough',
        'alignleft'      => 'Align Left',
        'aligncenter'    => 'Align Center',
        'alignright'     => 'Align Right',
        'alignjustify'   => 'Justify',
        'styleselect'    => '<!--styleselect-->',
        'formatselect'   => '<!--formatselect-->',
        'fontselect'     => '<!--fontselect-->',
        'fontsizeselect' => '<!--fontsizeselect-->',
        'cut'            => 'Cut',
        'copy'           => 'Copy',
        'paste'          => 'Paste',
        'bullist'        => 'Bullet List',
        'numlist'        => 'Numbered List',
        'outdent'        => 'Outdent',
        'indent'         => 'Indent',
        'blockquote'     => 'Quote',
        'undo'           => 'Undo',
        'redo'           => 'Redo',
        'removeformat'   => 'Remove Formatting',
        'subscript'      => 'Subscript',
        'superscript'    => 'Superscript',

        // From plugins
        'hr'             => 'Horizontal Rule',
        'link'           => 'Link',
        'unlink'         => 'Remove Link',
        'image'          => 'Edit Image',
        'charmap'        => 'Character Map',
        'pastetext'      => 'Paste as Text',
        'print'          => 'Print',
        'anchor'         => 'Insert Anchor',
        'searchreplace'  => 'Search/Replace',
        'visualblocks'   => 'Visual Blocks',
        //	'visualchars' => 'Hidden Chars',
        'code'           => 'HTML code',
        'fullscreen'     => 'Full Screen',
        'insertdatetime' => 'Insert Date/Time',
        'media'          => 'Insert Media',
        'nonbreaking'    => 'Non-Break Space',
        'table'          => 'Table',
        'ltr'            => 'Left to Right',
        'rtl'            => 'Right to Left',
        'emoticons'      => 'Emoticons',
        'forecolor'      => 'Text Color',
        'backcolor'      => 'Text Background',
        'spellchecker'   => 'Spellcheck',

        // dcms
        'googlemaps'     => 'Google Map',
        'contentgrid'    => 'Content Grid',
        'contenttabs'    => 'Content Tabs',
        'loremipsum'=> 'Lorem ipsum',
        'qrcode'    => 'QR-Code'
    );


    public function execute()
    {

        if ( $this->isFrontend() )
        {
            return;
        }

        $this->_doEdit();
    }

    private function getTinyMcePlugins()
    {

        $dirs     = array();
        $iterator = new DirectoryIterator( VENDOR_PATH . 'tinymce4/plugins' );
        foreach ( $iterator as $fileinfo )
        {
            if ( !$fileinfo->isDot() && $fileinfo->isDir() )
            {
                $dirs[ ] = $fileinfo->getFilename();
            }
        }

        return $dirs;
    }

    private function _doEdit()
    {

        $groupid         = (int)HTTP::input( 'groupid' );
        $data[ 'group' ] = $this->db->query( 'SELECT groupid, title, dashboard, editorsettings FROM %tp%users_groups WHERE groupid = ?', $groupid )->fetch();

        $appPlugins = $this->getTinyMcePlugins();


        $tadv_toolbars = null;
        if ( !empty( $data[ 'group' ][ 'editorsettings' ] ) )
        {
            $tadv_toolbars = unserialize( $data[ 'group' ][ 'editorsettings' ] );
        }


        if ( !is_array( $tadv_toolbars ) )
        {
            // default toolbars
            $tadv_toolbars = $this->default_settings;
        }
        else
        {
            $tadv_toolbars[ 'toolbar_1' ] = isset( $tadv_toolbars[ 'toolbar_1' ] ) ? $tadv_toolbars[ 'toolbar_1' ] : array();
            $tadv_toolbars[ 'toolbar_2' ] = isset( $tadv_toolbars[ 'toolbar_2' ] ) ? $tadv_toolbars[ 'toolbar_2' ] : array();
            $tadv_toolbars[ 'toolbar_3' ] = isset( $tadv_toolbars[ 'toolbar_3' ] ) ? $tadv_toolbars[ 'toolbar_3' ] : array();
            $tadv_toolbars[ 'toolbar_4' ] = isset( $tadv_toolbars[ 'toolbar_4' ] ) ? $tadv_toolbars[ 'toolbar_4' ] : array();
        }


        $hidden_row = 0;
        $i          = 0;
        foreach ( $tadv_toolbars as $toolbar )
        {
            $l = $t = false;
            $i++;

            if ( empty( $toolbar ) )
            {
                $btns[ "toolbar_$i" ] = array();
                continue;
            }

            foreach ( $toolbar as $k => $v )
            {
                if ( strpos( $v, 'separator' ) !== false )
                {
                    $toolbar[ $k ] = 'separator';
                }

                if ( 'layer' == $v )
                {
                    $l = $k;
                }

                if ( 'tablecontrols' == $v )
                {
                    $t = $k;
                }

                if ( empty( $v ) )
                {
                    unset( $toolbar[ $k ] );
                }
            }

            if ( $l !== false )
            {
                array_splice( $toolbar, $l, 1, array(
                    'insertlayer',
                    'moveforward',
                    'movebackward',
                    'absolute'
                ) );
            }

            if ( $t !== false )
            {
                array_splice( $toolbar, $t + 1, 0, 'delete_table,' );
            }

            $btns[ "toolbar_$i" ] = $toolbar;
        }

        extract( $btns );

        $allbtns = array_merge( $toolbar_1, $toolbar_2, $toolbar_3, $toolbar_4 );

        if ( in_array( 'template', $allbtns ) )
        {
            $plugins[ ] = 'template';
        }


        if ( in_array( 'media', $allbtns ) )
        {
            $plugins[ ] = 'media';
        }
        if ( in_array( 'advhr', $allbtns ) )
        {
            $plugins[ ] = 'advhr';
        }
        if ( in_array( 'insertlayer', $allbtns ) )
        {
            $plugins[ ] = 'layer';
        }
        if ( in_array( 'visualchars', $allbtns ) )
        {
            $plugins[ ] = 'visualchars';
        }

        if ( in_array( 'imgmap', $allbtns ) )
        {
            $plugins[ ] = 'imgmap';
        }

        if ( in_array( 'googlemaps', $allbtns ) )
        {
            $plugins[ ] = 'googlemaps';
        }

        if ( in_array( 'nonbreaking', $allbtns ) )
        {
            $plugins[ ] = 'nonbreaking';
        }
        if ( in_array( 'styleprops', $allbtns ) )
        {
            $plugins[ ] = 'style';
        }
        if ( in_array( 'emotions', $allbtns ) )
        {
            $plugins[ ] = 'emotions';
        }

        if ( in_array( 'insertdate', $allbtns ) || in_array( 'inserttime', $allbtns ) )
        {
            $plugins[ ] = 'insertdatetime';
        }


        if ( in_array( 'tablecontrols', $allbtns ) )
        {
            $plugins[ ] = 'table';
        }
        if ( in_array( 'print', $allbtns ) )
        {
            $plugins[ ] = 'print';
        }
        if ( in_array( 'iespell', $allbtns ) )
        {
            $plugins[ ] = 'iespell';
        }
        if ( in_array( 'search', $allbtns ) || in_array( 'replace', $allbtns ) )
        {
            $plugins[ ] = 'searchreplace';
        }

        if ( in_array( 'cite', $allbtns ) || in_array( 'ins', $allbtns ) || in_array( 'del', $allbtns ) || in_array( 'abbr', $allbtns ) || in_array( 'acronym', $allbtns ) || in_array( 'attribs', $allbtns ) )
        {
            $plugins[ ] = 'xhtmlxtras';
        }

        if ( $tadv_options[ 'advlink1' ] == '1' )
        {
            $plugins[ ] = 'advlink';
        }
        if ( $tadv_options[ 'advlist' ] == '1' )
        {
            $plugins[ ] = 'advlist';
        }
        if ( $tadv_options[ 'advimage' ] == '1' )
        {
            $plugins[ ] = 'advimage';
        }
        if ( $tadv_options[ 'contextmenu' ] == '1' )
        {
            $plugins[ ] = 'contextmenu';
        }



        foreach ( $appPlugins as $name )
        {
            if ( isset( $this->buttons[ $name ] ) || in_array($name, $this->all_plugins ) )
            {
                continue;
            }

            $this->buttons[ $name ] = $name;
        }


        #$buttons['MCFileManager'] = 'insertimage';
        #$buttons['MCImageManager'] = 'insertfile';


        $buttons = $this->buttons;


        $tadv_allbtns    = array_keys( $buttons );
        $tadv_allbtns[ ] = 'separator';
        $tadv_allbtns[ ] = '|';

        for ( $i = 1; $i < 21; $i++ )
        {
            $buttons[ "separator$i" ] = "separator$i";
        }


        $html = <<<EOF

<div class="wrap" id="contain">
<div id="tadvzones">

EOF;


        for ($x = 1; $x <= 4; $x++) {

            $html .= <<<E
     <div class="tadvdropzone tadvitem toolbar">
        <ul style="position: relative;" id="toolbar_{$x}" class="dcontainer">
E;

            if (isset($tadv_toolbars[ 'toolbar_' . $x ]) && is_array($tadv_toolbars[ 'toolbar_' . $x ]) ) {


                $tb = array();

                foreach ( $tadv_toolbars[ 'toolbar_' . $x ] as $k )
                {
                    if ( ($label = $this->getButtonLabel($k)) && strpos( $k, 'separator' ) === false ) {
                        $tb[$k] = $label;
                    }
                    else {
                        $tb[$k] = $k;
                    }
                }


                foreach ( $tb as $btn => $label )
                {

                    if ( strpos( $label, '<!' ) === 0 )
                    {
                        $label = '';
                    }

                    if ( strpos( $btn, 'separator' ) !== false )
                    {
                        $html .= <<<E
        <li class="separator" id="pre_{$btn}"><div class="tadvitem"> </div><input type="hidden" name="toolbar_{$x}[]" value="{$btn}"/></li>
E;
                    }
                    else
                    {
                        $html .= <<<E
                <li class="tadvmodule" id="mce-{$btn}">
                    <div class="tadvitem">
                        <i class="mce-ico mce-i-{$btn}" title="$label"></i>
                        <span class="descr">$label</span>
                        <input type="hidden" name="toolbar_{$x}[]" value="{$btn}"/>
                    </div>
                </li>
E;
                    }
                }

                $buttons = array_diff( $buttons, $tb );
            }

            $html .= "</ul></div>";
        }









        $availeble = trans( 'verfügbare Buttons und Controlls' );


        $html .= <<<E
	</ul>
    </div>

	</div>

	<div id="tadvWarnmsg">&nbsp;
        <span id="too_long" style="display:none;">
            Adding too many buttons will make the toolbar too long and will not display correctly in TinyMCE!
        </span>
	</div>

<fieldset>
    <legend>{$availeble}</legend>
	<div id="tadvpalettediv">
	<ul class="tadvitem dcontainer" style="position: relative;" id="tadvpalette">
E;
        if ( is_array( $buttons ) )
        {
            foreach ( $buttons as $btn => $label )
            {

                if ( strpos( $label, '<!' ) === 0 )
                {
                    $label = '';
                }

                if ( strpos( $btn, 'separator' ) !== false )
                {
                    $html .= <<<E
                    <li class="separator" id="pre_{$btn}"><div class="tadvitem"> </div></li>
E;
                }
                else
                {
                    $html .= <<<E
                <li class="tadvmodule" id="mce-{$btn}">
                    <div class="tadvitem">
                        <i class="mce-ico mce-i-{$btn}" title="$label"></i>
                        <span class="descr">$label</span>
                    </div>
                </li>
E;
                }
            }
        }
        $html .= <<<E
	</ul>
	</div>


</fieldset>
</div>
E;


        $data[ 'toolbars' ] = $html;

        Library::addNavi( sprintf( trans( 'Inhalts Editor für Benutzergruppe `%s`' ), $data[ 'group' ][ 'title' ] ) );

        $this->Template->addScript( BACKEND_JS_URL . 'tadv' );
        // $this->Template->addScript( Settings::get('portalurl') . '/Vendor/tinymce/themes/advanced/skins/dcms/ui.css', true );
        $this->Template->addScript( Settings::get( 'portalurl' ) . '/Vendor/tinymce4/skins/dcms/skin.min.css', true );
        $this->Template->process( 'settings/editors', $data, true );
    }


    private function getButtonLabel($name ) {
        if ( isset($this->buttons[$name]) ) {
            return $this->buttons[$name];
        }
        return false;
    }

}
