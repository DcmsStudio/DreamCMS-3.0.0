<?php

/**
 * Used to scan string into a list of tokens
 */
final class Importer_Helper_XmlImportTemplateScanner
{

	/**
	 * Language keywords
	 *
	 * @var array
	 */
	private $keywords = array (
		'IF',
		'ELSEIF',
		'ELSE',
		'ENDIF',
		'FOREACH',
		'ENDFOREACH',
		'WITH',
		'ENDWITH',
		'MATH',
		'SPINTAX'
	);

	/**
	 * Parsing text
	 */
	const STATE_TEXT = 'STATE_TEXT';

	/**
	 * Parsing XPath
	 */
	const STATE_XPATH = 'STATE_XPATH';

	/**
	 * Parsing Language
	 */
	const STATE_LANG = 'STATE_LANG';

	/**
	 * Whether it is lang block start
	 *
	 * @var bool
	 */
	private $isLangBegin = false;

	/**
	 * Current parsing state
	 *
	 * @var string
	 */
	private $currentState = Importer_Helper_XmlImportTemplateScanner::STATE_TEXT;

	/**
	 * Scans template from Importer_Helper_XmlImportReaderInterface and returns the list of tokens
	 *
	 * @param Importer_Helper_XmlImportReaderInterface $input
	 * @return array
	 */
	public function scan ( Importer_Helper_XmlImportReaderInterface $input )
	{

		$results = array ();
		while ( ($ch = $input->peek()) !== false )
		{
			switch ( $this->currentState )
			{
				case Importer_Helper_XmlImportTemplateScanner::STATE_TEXT:
					if ( $ch == '[' )
					{
						$this->currentState = Importer_Helper_XmlImportTemplateScanner::STATE_LANG;
						$this->isLangBegin  = true;
						//omit [
						$input->read();
					}
					elseif ( $ch == '{' )
					{
						$this->currentState = Importer_Helper_XmlImportTemplateScanner::STATE_XPATH;
						//omit {
						$input->read();
					}
					else
					{
						$results[ ] = $this->scanText($input);
					}
					break;
				case Importer_Helper_XmlImportTemplateScanner::STATE_XPATH:
					$results = array_merge($results, $this->scanXPath($input, false));
					break;
				case Importer_Helper_XmlImportTemplateScanner::STATE_LANG:
					$ch = $input->peek();

					if ( preg_match('/\s/', $ch) )
					{
						//omit space
						$input->read();
					}
					elseif ( preg_match('/[_a-z]/i', $ch) )
					{
						$result = $this->scanName($input);
						if ( is_array($result) )
						{
							$results = array_merge($results, $result);
						}
						else
						{
							$results[ ] = $result;
						}
					}
					elseif ( preg_match('/(\d|-)/', $ch) )
					{
						$result = $this->scanNumber($input);
						if ( is_array($result) )
						{
							$results = array_merge($results, $result);
						}
						else
						{
							$results[ ] = $result;
						}
					}
					elseif ( $ch == "+" || $ch == "-" || $ch == "*" || $ch == "/" )
					{
						$this->isLangBegin = false;
						$input->read();
						$results[ ] = new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_OPERATION, $ch);
					}
					elseif ( $ch == '"' )
					{
						//omit "
						$input->read();
						$result = $this->scanString($input);
						if ( is_array($result) )
						{
							$results = array_merge($results, $result);
						}
						else
						{
							$results[ ] = $result;
						}
					}
					elseif ( $ch == '{' )
					{
						$input->read();
						$result = $this->scanXPath($input);
						if ( is_array($result) )
						{
							$results = array_merge($results, $result);
						}
						else
						{
							$results[ ] = $result;
						}
					}
					elseif ( $ch == '(' )
					{
						$this->isLangBegin = false;
						$input->read();
						$results[ ] = new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_OPEN);
					}
					elseif ( $ch == ')' )
					{
						$this->isLangBegin = false;
						$input->read();
						$results[ ] = new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_CLOSE);
					}
					elseif ( $ch == ',' )
					{
						$this->isLangBegin = false;
						$input->read();
						$results[ ] = new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_COMMA);
					}
					elseif ( $ch == ']' or $ch == "|" )
					{
						$this->isLangBegin  = false;
						$this->currentState = Importer_Helper_XmlImportTemplateScanner::STATE_TEXT;
						//omit ]
						$input->read();
					}
					else
					{
						throw new Importer_Helper_XmlImportException("Unexpected symbol '$ch'");
					}

					break;
			}
		}

		return $results;
	}

	/**
	 * Scans text
	 *
	 * @param Importer_Helper_XmlImportReaderInterface $input
	 * @return Importer_Helper_XmlImportToken
	 */
	private function scanText ( $input )
	{

		$accum = $input->read();
		while ( ($ch = $input->peek()) !== false )
		{
			if ( $ch == '{' && $accum[ strlen($accum) - 1 ] != "\\" )
			{
				$this->currentState = Importer_Helper_XmlImportTemplateScanner::STATE_XPATH;
				//omit {
				$input->read();
				break;
			}
			elseif ( $ch == '[' && $accum[ strlen($accum) - 1 ] != "\\" )
			{
				$this->currentState = Importer_Helper_XmlImportTemplateScanner::STATE_LANG;
				$this->isLangBegin  = true;
				//omit [
				$input->read();
				break;
			}
			else
			{
				$accum .= $input->read();
			}
		}
		$accum = str_replace(array (
		                           "\\[",
		                           "\\{"
		                     ), array (
		                              '[',
		                              '{'
		                        ), $accum);

		return new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_TEXT, $accum);
	}

	/**
	 * Scans XPath
	 *
	 * @param Importer_Helper_XmlImportReaderInterface $input
	 * @param bool                                     $insideLang
	 * @return Importer_Helper_XmlImportToken
	 */
	private function scanXPath ( $input, $insideLang = true )
	{

		$accum = '';
		while ( ($ch = $input->peek()) !== false )
		{
			if ( $ch == '}' && (strlen($accum) == 0 || $accum[ strlen($accum) - 1 ] != "\\") )
			{
				//skip }
				$input->read();
				$accum = str_replace("\\}", '}', $accum);
				if ( $insideLang )
				{
					if ( $this->isLangBegin )
					{
						return array (
							new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_PRINT),
							new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_XPATH, $accum)
						);
					}
					else
					{
						return new Importer_Helper_XmlImportToken(XmlImportToken::KIND_XPATH, $accum);
					}
				}
				else
				{
					$this->currentState = Importer_Helper_XmlImportTemplateScanner::STATE_TEXT;

					return array (
						new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_PRINT),
						new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_XPATH, $accum)
					);
				}
			}
			else
			{
				$accum .= $input->read();
			}
		}
		throw new Importer_Helper_XmlImportException('Unexpected end of XPath expression \'' . $accum . '\'');
	}

	/**
	 * Scans name
	 *
	 * @param Importer_Helper_XmlImportReaderInterface $input
	 * @return Importer_Helper_XmlImportToken
	 */
	private function scanName ( Importer_Helper_XmlImportReaderInterface $input )
	{

		$accum = $input->read();
		while ( preg_match('/[_a-z0-9]/i', $input->peek()) )
		{
			$accum .= $input->read();
			if ( $input->peek() === false )
			{
				throw new Importer_Helper_XmlImportException("Unexpected end of function or keyword name \"$accum\"");
			}
		}
		if ( in_array(strtoupper($accum), $this->keywords) )
		{
			return new Importer_Helper_XmlImportToken(strtoupper($accum));
		}
		else
		{
			if ( $this->isLangBegin )
			{
				$this->isLangBegin = false;

				return array (
					new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_PRINT),
					new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_FUNCTION, $accum)
				);
			}
			else
			{
				return new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_FUNCTION, $accum);
			}
		}
	}

	/**
	 * Scans string literal
	 *
	 * @param Importer_Helper_XmlImportReaderInterface $input
	 * @return Importer_Helper_XmlImportToken
	 */
	private function scanString ( Importer_Helper_XmlImportReaderInterface $input )
	{

		$accum = '';
		while ( ($ch = $input->peek()) !== false )
		{
			if ( $ch == '"' && (strlen($accum) == 0 || $accum[ strlen($accum) - 1 ] != "\\") )
			{
				//skip "
				$input->read();
				$accum = str_replace("\\\"", '"', $accum);
				if ( $this->isLangBegin )
				{
					$this->isLangBegin = false;

					return array (
						new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_PRINT),
						new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_STRING, $accum)
					);
				}
				else
				{
					return new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_STRING, $accum);
				}
			}
			else
			{
				$accum .= $input->read();
			}
		}
		throw new Importer_Helper_XmlImportException('Unexpected end of string literal "' . $accum . '"');
	}

	/**
	 * Scans number
	 *
	 * @param Importer_Helper_XmlImportReaderInterface $input
	 * @return Importer_Helper_XmlImportToken
	 */
	private function scanNumber ( Importer_Helper_XmlImportReaderInterface $input )
	{

		$isInt = true;
		$accum = $this->scanInt($input);
		if ( $input->peek() == '.' )
		{
			$isInt = false;
			$accum .= $input->read();
			$accum .= $this->scanNumberFrac($input);
		}
		if ( strtolower($input->peek()) == 'e' )
		{
			$isInt = false;
			$accum .= $input->read();
			$accum .= $this->scanInt($input);
		}
		if ( $isInt )
		{
			if ( $this->isLangBegin )
			{
				$this->isLangBegin = false;

				return array (
					new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_PRINT),
					new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_INT, (int)$accum)
				);
			}
			else
			{
				return new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_INT, intval($accum));
			}
		}
		else
		{
			if ( $this->isLangBegin )
			{
				$this->isLangBegin = false;

				return array (
					new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_PRINT),
					new Importer_Helper_XmlImportToken(Importer_Helper_XmlImportToken::KIND_FLOAT, floatval($accum))
				);
			}
			else
			{
				return new Importer_Helper_XmlImportToken(XmlImportToken::KIND_FLOAT, floatval($accum));
			}
		}
	}

	/**
	 * Scans integer number
	 *
	 * @param Importer_Helper_XmlImportReaderInterface $input
	 * @return string
	 */
	private function scanInt ( Importer_Helper_XmlImportReaderInterface $input )
	{

		if ( preg_match('/(\d|-)/', $input->peek()) )
		{
			$accum = $input->read();
			if ( $accum == '-' && !preg_match('/\d/', $input->peek()) )
			{
				throw new Importer_Helper_XmlImportException("Expected digit after a minus");
			}
			while ( preg_match('/\d/', $input->peek()) )
			{
				$accum .= $input->read();
			}

			return $accum;
		}
		else
		{
			throw new Importer_Helper_XmlImportException("digit or '-' expected in a number");
		}
	}

	/**
	 * Scans fraction part of a number
	 *
	 * @param Importer_Helper_XmlImportReaderInterface $input
	 * @return string
	 */
	private function scanNumberFrac ( Importer_Helper_XmlImportReaderInterface $input )
	{

		$accum = '';
		while ( preg_match('/\d/', $input->peek()) )
		{
			$accum .= $input->read();
		}
		if ( strlen($accum) == 0 )
		{
			throw new Importer_Helper_XmlImportException("Digits are expected after a '.'");
		}

		return $accum;
	}

}
