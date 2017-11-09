<?php

namespace Addins\Parser;

/**
 * Class Components_Whitespace
 * @package Addins\Parser
 * Will match 0 or more whitespace characters (same as PHP trim). By default, the characters are not captured. Turning this
 * to mandatory will cause it to only match if there is at least 1 whitespace character.
 */
class Components_Whitespace extends Components_Component
{
	protected $_mandatory;

	public function __construct( $mandatory=false ) {
		$this->_mandatory = $mandatory;
		$this->_capture = false;
	}

	/**
	 * Set whether at least one whitespace character is required to match.
	 * @param boolean $mandatory
	 * @return self
	 */
	public function setMandatory($mandatory) {
		$this->_mandatory = $mandatory;
		return $this;
	}

	public function parse(Parser $parser, Tree_Branch $parent_node, ParserStream $stream) {
		$raw = '';

		while ( true ) {
			// end of stream
			if ( $stream->isEndOfStream()) break;

			// get next character
			$next = $stream->consume( 1 );

			// is whitespace character?
			switch ( ord( $next ) ) {
				case 0: // \0
				case 9: // \t
				case 10: // \n
				case 11: // \x0B vertical tab
				case 13: // \r
				case 32: // " " space
					$raw .= $next;

					// break switch
					break;
				default:
					// rewind
					$stream->rewind(1);

					// end white loop
					break 2;
			}
		}

		// check mandatory
		if ( $this->_mandatory && strlen( $raw ) === 0 ) {
			// add potential error
			$parser->addPotentialException( $stream , new ParserException( 'Expected space.' ) );

			return false;
		}

		// add node
		$this->_insertIntoTree($parser, $parent_node, $raw);

		return true;
	}

	// TODO: compare performance... probably faster for whitespace heavy parse strings
	public function parseAlternative(Parser $parser, Tree_Branch $parent_node, ParserStream $stream) {
		$raw = '';
		$chunk_size = 8;

		while ( true ) {
			if ( $stream->isEndOfStream()) break;

			// read chunk
			$chunk = $stream->consume( $chunk_size );

			// trim white space
			$new_chunk = ltrim( $chunk );

			// any characters left? rewind by remaining length
			if ( $new_chunk ) {
				$stream->rewind( strlen( $new_chunk ) );
				break;
			}

			// track raw for node
			$raw .= substr( $chunk , 0 , $chunk_size - strlen( $new_chunk ) );
		}

		// check mandatory
		if ( $this->_mandatory && strlen( $raw ) === 0 ) {
			// add potential error
			$parser->addPotentialException( $stream , new ParserException( 'Expected space.' ) );

			return false;
		}

		// add node
		$this->_insertIntoTree($parser, $parent_node, $raw);

		return true;
	}
}