<?php
/**
 * {@link aCssParserPlugin Parser plugin} for preserve parsing expression() declaration values.
 *
 * This plugin return no {@link aCssToken CssToken} but ensures that php declaration values will get parsed
 * properly.
 *
 * @package		CssMin/Parser/Plugins
 * @link
 * @author		Stephen Walker <stephen@itwebexperts.com>
 * @copyright	2012 Stephen Walker <stephen@itwebexperts.com>
 * @version		3.0.1
 */
class CssPhpParserPlugin extends aCssParserPlugin
{
	/**
	 * Count of left braces.
	 *
	 * @var integer
	 */
	private $leftBraces = 0;
	/**
	 * Count of right braces.
	 *
	 * @var integer
	 */
	private $rightBraces = 0;
	/**
	 * Implements {@link aCssParserPlugin::getTriggerChars()}.
	 *
	 * @return array
	 */
	public function getTriggerChars()
	{
		return array("<", "(", ")", ">", "?");
	}
	/**
	 * Implements {@link aCssParserPlugin::getTriggerStates()}.
	 *
	 * @return array
	 */
	public function getTriggerStates()
	{
		return false;
	}
	/**
	 * Implements {@link aCssParserPlugin::parse()}.
	 *
	 * @param integer $index Current index
	 * @param string $char Current char
	 * @param string $previousChar Previous char
	 * @return mixed TRUE will break the processing; FALSE continue with the next plugin; integer set a new index and break the processing
	 */
	public function parse($index, $char, $previousChar, $state)
	{
		// Start of expression
		if ($char === "<" && strtolower(substr($this->parser->getSource(), $index + 1, 4)) === "?php" && $state !== "T_PHP")
		{
			$this->parser->pushState("T_PHP");
			$this->parser->setExclusive(__CLASS__);
			$this->restoreBuffer = $this->parser->getAndClearBuffer();
		}
		// Count left braces
		elseif ($char === "(" && $state === "T_PHP")
		{
			$this->leftBraces++;
		}
		// Count right braces
		elseif ($char === ")" && $state === "T_PHP")
		{
			$this->rightBraces++;
		}
		// Possible end of expression; if left and right braces are equal the expressen ends
		elseif ($char === ">" && $state === "T_PHP" && $this->leftBraces === $this->rightBraces)
		{
			$this->leftBraces = $this->rightBraces = 0;
			$this->parser->popState();
			$this->parser->unsetExclusive();
			$this->parser->appendToken(new CssPhpFunctionToken('<' . $this->parser->getAndClearBuffer()));
			$this->parser->setBuffer($this->restoreBuffer);
		}
		else
		{
			return false;
		}
		return true;
	}
}
?>