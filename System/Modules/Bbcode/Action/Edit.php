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
 * @package      Bbcode
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit.php
 */
class Bbcode_Action_Edit extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$id                 = (int)$this->input('id');
		$bbcode[ 'bbcode' ] = $this->model->getBBcodeByID($id);


		if ( $this->_post('send') )
		{
			$error = false;
			if ( trim(HTTP::input('bbcodereplacement')) == '' )
			{
				$error = true;
			}

			if ( trim(HTTP::input('bbcodetag')) == '' )
			{
				$error = true;
			}

			if ( $error )
			{
				Library::sendJson(false, trans("Es ist ein Fehler aufgetreten! Bitte püfen Sie Ihre eingabe."));
			}

			demoadm();




			if ( $id )
			{
				$bbcodereplacement = $this->_post('bbcodereplacement');
				if ( $this->_post('params') > 1 )
				{
					$bbcodereplacement = str_replace("[param1]", "\\2", $bbcodereplacement);
					$bbcodereplacement = str_replace("[param2]", "\\3", $bbcodereplacement);
					$bbcodereplacement = str_replace("[param3]", "\\4", $bbcodereplacement);
				}
				else
				{
					$bbcodereplacement = str_replace("[param1]", "\\1", $bbcodereplacement);
				}

				$data                        = $this->_post();
				$data[ 'bbcodereplacement' ] = $bbcodereplacement;
				$data[ 'attribues' ] = $attribues;




				$this->model->save($id, $data);


				Cache::delete('bbcodes');

				Library::log("Edit the BBCode '" . $data[ 'bbcodetag' ] . "' (ID:{$id}).");
				Library::sendJson(true, sprintf(trans('BBCode `%s` wurde erfolgreich geändert'), $bbcode[ 'bbcode' ][ 'bbcodetag' ]));
				exit();
			}
			else
			{
				demoadm();

				$bbcodereplacement = $this->_post('bbcodereplacement');

				if ( $this->_post('params') > 1 )
				{
					$bbcodereplacement = str_replace("[param1]", "\\2", $bbcodereplacement);
					$bbcodereplacement = str_replace("[param2]", "\\3", $bbcodereplacement);
					$bbcodereplacement = str_replace("[param3]", "\\4", $bbcodereplacement);
				}
				else
				{
					$bbcodereplacement = str_replace("[param1]", "\\1", $bbcodereplacement);
				}

				$data                        = $this->_post();
				$data[ 'bbcodereplacement' ] = $bbcodereplacement;
				$data[ 'attribues' ] = $attribues;

				$id = $this->model->save($id, $data);

				Cache::delete('bbcodes');
				Library::log("Add a new BBCode '" . $data[ 'bbcodetag' ] . "'");
				echo Library::json(array (
				                         'success' => true,
				                         'msg'     => sprintf(trans('BBCode `%s` wurde erfolgreich hinzugefügt'), $data[ 'bbcodetag' ]),
				                         'newid'   => $id
				                   ));
				exit;
			}
		}


		Library::addNavi(trans('BBCodes'));
		Library::addNavi(sprintf(trans('BBCode %s bearbeiten'), $bbcode[ 'bbcode' ][ 'bbcodetag' ]));

		$bbcode[ 'bbcode' ][ 'bbcodereplacement' ] = str_replace("\\1", "[param1]", $bbcode[ 'bbcode' ][ 'bbcodereplacement' ]);
		$bbcode[ 'bbcode' ][ 'bbcodereplacement' ] = str_replace("\\2", "[param2]", $bbcode[ 'bbcode' ][ 'bbcodereplacement' ]);
		$bbcode[ 'bbcode' ][ 'bbcodereplacement' ] = str_replace("\\3", "[param3]", $bbcode[ 'bbcode' ][ 'bbcodereplacement' ]);
		$bbcode[ 'bbcode' ][ 'bbcodereplacement' ] = str_replace("\\4", "[param4]", $bbcode[ 'bbcode' ][ 'bbcodereplacement' ]);


		$bbcode[ 'bbcode' ]['allowedchildren'] = $bbcode[ 'bbcode' ]['allowedchildren'] ? explode('|', $bbcode[ 'bbcode' ]['allowedchildren']) : array('none');


		$bbcode['alltags'] = $this->model->getBBcodeTags();


		$this->Template->process('bbcodes/edit', $bbcode, true);
	}

}

?>