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
 * @package     DreamCMS
 * @version     3.0.0 Beta
 * @category    Template Engine
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Likethis.php
 *
 */
class Compiler_Tag_Likethis extends Compiler_Tag_Abstract
{

    /**
     * @var array
     */
    private $_allowedNetworks = array(
        'fb',
        'facebook',
        'twitter' );

    /**
     * @var string
     */
    private $_startCode = '';


    /**
     *
     */
    public function configure()
    {
        $this->tag->setAttributeConfig(
                array(
                    'likebutton'  => array(
	                    Compiler_Attribute::OPTIONAL,
                        Compiler_Attribute::BOOL ),
                    'sharebutton' => array(
	                    Compiler_Attribute::OPTIONAL,
	                    Compiler_Attribute::BOOL ),
                    'showcount'   => array(
	                    Compiler_Attribute::OPTIONAL,
                        Compiler_Attribute::BOOL ),
                    'networks'    => array(
	                    Compiler_Attribute::REQUIRED,
                        Compiler_Attribute::STRING ),
                    'enable'      => array(
	                    Compiler_Attribute::OPTIONAL,
                        Compiler_Attribute::EXPRESSION,
                        null )
                )
        );
    }

    public function process()
    {
        $likebutton = $this->getAttributeValue( 'likebutton' );
        $sharebutton = $this->getAttributeValue( 'sharebutton' );
        $showcount = $this->getAttributeValue( 'showcount' );

	    if (!isset($showcount) ) {
		    $showcount = true;
	    }

        $networks = $this->getAttributeValue( 'networks' );
        $enable = $this->getAttributeValue( 'enable' );

        $net = explode( ',', substr( $networks[ 0 ], 1, -1 ) );
        $this->startCode = '<div class="socialShare"><span>' . Compiler_Abstract::PHP_OPEN . 'echo trans(\'Beitrag Weiterempfehlen\')' . Compiler_Abstract::PHP_CLOSE . '</span>';


        foreach ( $net as $str )
        {
            $str = trim( $str );

            if ( empty( $str ) )
            {
                continue;
            }


            switch ( $str )
            {
                case 'fb':
                case 'facebook':

                    $this->startCode .= '<div class="facebook">';

                    if ( $sharebutton )
                    {
                        $this->startCode .= Compiler_Abstract::PHP_OPEN . ' $_url = Api::getShareUrl("facebook"); if ($_url !== false) { ' . Compiler_Abstract::PHP_CLOSE . '
                            <a href="' . Compiler_Abstract::PHP_OPEN . ' echo $_url; ' .
	                        Compiler_Abstract::PHP_CLOSE . '" rel="nofollow" class="share facebook">
                                    <span class="sharetext">' . Compiler_Abstract::PHP_OPEN . ' echo trans(\'Teilen\'); ' .
	                        Compiler_Abstract::PHP_CLOSE . '</span>' . Compiler_Abstract::PHP_OPEN . ' } $_url = null; ' .
	                        Compiler_Abstract::PHP_CLOSE;
                    }

                    if ( $likebutton )
                    {
                        $this->startCode .= '<div class="like facebook" style="float:left; padding:2px 7px 0 0; width:460px">
<iframe src="http://www.facebook.de/plugins/like.php?href=' . Compiler_Abstract::PHP_OPEN . 'echo urlencode( Api::currentLocation() );' . Compiler_Abstract::PHP_CLOSE . '&amp;layout=standard&amp;show_faces=true&amp;width=450&amp;action=like&amp;font=arial&amp;colorscheme=light&amp;height=26" scrolling="no" frameborder="0" style="border:none;overflow:hidden;width:450px;height:30px;" allowTransparency="true"></iframe>
</div>';
                    }

                    if ( $showcount )
                    {
                        $this->startCode .= '<span class="sharecount"><span>[Sharecount name="facebook" cache="true"]</span></span>';
                    }


                    if ( $sharebutton )
                    {
                        $this->startCode .= '</a>';
                    }

                    $this->startCode .= '</div>';

                    break;

                case 'tweet':
                case 'twitter':
                    $this->startCode .= '<div class="twitter">';

                    if ( $sharebutton )
                    {

                        $this->startCode .= Compiler_Abstract::PHP_OPEN . ' $_url = Api::getShareUrl("twitter"); if ($_url !== false) { ' . Compiler_Abstract::PHP_CLOSE . '
                            <a href="' . Compiler_Abstract::PHP_OPEN . ' echo $_url; ' .
	                        Compiler_Abstract::PHP_CLOSE . '" rel="nofollow" class="share twitter">
                                    <span class="sharetext">' . Compiler_Abstract::PHP_OPEN . ' echo trans(\'Twittern\'); ' .
	                        Compiler_Abstract::PHP_CLOSE . '</span>' . Compiler_Abstract::PHP_OPEN . ' } $_url = null; ' .
	                        Compiler_Abstract::PHP_CLOSE;
                    }

                    if ( $showcount )
                    {
                        $this->startCode .= '<span class="sharecount"><span>[Sharecount name="twitter" cache="true"]</span></span>';
                    }


                    if ( $sharebutton )
                    {
                        $this->startCode .= '</a>';
                    }

                    $this->startCode .= '</div>';
                    break;


                case 'gplus':
                case 'plusone':
                case 'googleplus':
                    $this->startCode .= '<div class="gplus">';

                    if ( $sharebutton )
                    {
                        $this->startCode .= Compiler_Abstract::PHP_OPEN . ' $_url = Api::getShareUrl("plusone"); if ($_url !== false) { ' . Compiler_Abstract::PHP_CLOSE . '
                            <a href="' . Compiler_Abstract::PHP_OPEN . ' echo $_url; ' .
	                        Compiler_Abstract::PHP_CLOSE . '" rel="nofollow" class="share plusone">
                                    <span class="sharetext"></span>' . Compiler_Abstract::PHP_OPEN . ' } $_url = null; ' .
	                        Compiler_Abstract::PHP_CLOSE;
                    }

                    if ( $showcount )
                    {
                        $this->startCode .= '<span class="sharecount"><span>[Sharecount name="googleplus" cache="true"]</span></span>';
                    }

                    if ( $sharebutton )
                    {
                        $this->startCode .= '</a>';
                    }

                    $this->startCode .= '</div>';
                    break;


                default:
                    $this->startCode .= '';
                    break;
            }
        }


        $this->startCode .= '</div>';





        if ( $enable[ 0 ] )
        {


            $this->set( 'nophp', false );
            $code = '
if (' . $enable[ 0 ] . ') { ?>
    ' . $this->startCode . '
' . Compiler_Abstract::PHP_OPEN . '}';
        }
        else
        {
            $this->set( 'nophp', true );
            $code = $this->startCode;
        }


        $this->setStartTag( $code );
    }

}
