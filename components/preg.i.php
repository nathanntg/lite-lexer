<?php

namespace Addins\Parser;

/**
 * Class Components_Preg
 * @package Addins\Parser
 *
 * Matches a PHP (Perl) Regular Expression. Note that for optimization, this is configured with a maximum length (set to
 * 1024 by default). If the regular expression will always be very short, this maximum length can be shortened for a performance
 * boost. Or if you need longer, it can be increased.
 *
 * The constructor automatically adds a ^ and encloses the expression in delimiters, because matchers are always looking
 * at the beginning of a string.
 *
 * You can not use backward assertions.
 */
class Components_Preg extends Components_Component
{
	protected $_preg;
	protected $_max_length = 1024;

	public function __construct( $preg , $modifiers='i' , $delimiters='/' ) {
		$this->_preg = $delimiters . '^' . $preg . $delimiters . $modifiers;
	}

	/**
	 * @param int $max_length
	 * @return self
	 */
	public function setMaxLength($max_length) {
		$this->_max_length = $max_length;
		return $this;
	}

	public function parse( Parser $parser , ParserBlock $parent , Tree_Branch $parent_node , ParserStream $stream ) {
		$peek = $stream->peek( $this->_max_length );

		// run match
		if ( preg_match( $this->_preg , $peek , $match ) ) {
			// check for empty match
			if ( $match[ 0 ] === '' ) {
				throw new ParserConfigurationException('Parser configuration led to matching 0 length string. Can result in an infinite loop.' );
			}

			// success
			$stream->skip( strlen( $match[ 0 ] ) );

			// add node
			$this->_insertIntoTree( $parser , $parent , $parent_node , $match[ 0 ] );

			return true;
		}

		// fail
		return false;
	}
}