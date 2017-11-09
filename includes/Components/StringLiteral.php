<?php

namespace LiteLexer\Components;
use LiteLexer\Parser;
use LiteLexer\Stream;
use LiteLexer\Tree\Branch;

/**
 * Class StringLiteral
 * @package LiteLexer\Components
 *
 * Matches a single, literal string. By default, case insensitive.
 */
class StringLiteral extends Component
{
	/**
	 * @var string
	 */
	protected $_string;

	/**
	 * @var bool
	 */
	protected $_case_sensitive;

	public function __construct($string, $case_sensitive=false) {
		$this->_string = $string;
		$this->_case_sensitive = $case_sensitive;
	}

	/**
	 * @param bool $case_sensitive
	 * @return $this
	 */
	public function setCaseSensitive($case_sensitive) {
		$this->_case_sensitive = $case_sensitive;
		return $this;
	}

	public function parse(Parser $parser, Branch $parent_node, Stream $stream) {
		// use comparison function
		$function = ($this->_case_sensitive ? 'strcmp' : 'strcasecmp');

		// get string length
		$sl = strlen($this->_string);

		// peek length and compare to string use comparison function
		$peek = $stream->peek( $sl );
		if (0 === $function($peek, $this->_string)) {
			// advance pointer
			$stream->skip($sl);

			// add node
			$this->_insertIntoTree($parser, $parent_node, $peek);

			// success
			return true;
		}

		// fail
		return false;
	}
}
