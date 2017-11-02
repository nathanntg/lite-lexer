<?php

namespace Addins\Parser;

/**
 * Class Components_String
 * @package Addins\Parser
 *
 * Matches a single string. By default, case insensitive.
 */
class Components_String extends Components_Component
{
	protected $_string;
	protected $_case_sensitive;

	public function __construct( $string , $case_sensitive=false ) {
		$this->_string = $string;
		$this->_case_sensitive = $case_sensitive;
	}

	/**
	 * @param boolean $case_sensitive
	 * @return self
	 */
	public function setCaseSensitive($case_sensitive) {
		$this->_case_sensitive = $case_sensitive;
		return $this;
	}

	public function parse(Parser $parser, Tree_Branch $parent_node, ParserStream $stream) {
		// use comparison function
		$function = ( $this->_case_sensitive ? 'strcmp' : 'strcasecmp' );

		// get string length
		$sl = strlen( $this->_string );

		// peek length and compare to string use comparison function
		$peek = $stream->peek( $sl );
		if ( $function( $peek , $this->_string ) === 0 ) {
			// advance pointer
			$stream->skip( $sl );

			// add node
			$this->_insertIntoTree($parser, $parent_node, $peek);

			// success
			return true;
		}

		// fail
		return false;
	}
}