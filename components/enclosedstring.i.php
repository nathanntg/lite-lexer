<?php

namespace Addins\Parser;

/**
 * Class Components_EnclosedString
 * @package Addins\Parser
 *
 * Enclose strings will captures strings like "here is a string" or 'here is a string' and will correctly respect escape
 * characters.
 */
class Components_EnclosedString extends Components_Component
{
	protected $_delimiters;
	protected $_escape_character;
	protected $_strip = true;

	public function __construct( $delimiters=['"','\''] , $escape_character='\\' ) {
		$this->_delimiters = $delimiters;
		$this->_escape_character = $escape_character;
	}

	public function parse( Parser $parser , ParserBlock $parent , Tree_Branch $parent_node , ParserStream $stream ) {
		// take stream snapshot
		$stream->snapshot();

		// get delimiter
		$delimiter = $stream->consume(1);
		if ( !in_array( $delimiter , $this->_delimiters ) ) {
			// no matching delimiter? revert
			$stream->revert();

			return false;
		}

		$string = '';
		while ( true ) {
			// get closing delimiter
			$add = $stream->consumeUntil($delimiter);

			// no closing delimiter
			if ( $add === false ) {
				// no closing delimiter? revert
				$stream->revert();

				return false;
			}

			// number of sequential escape characters
			$p = strlen( $add ) - 2;
			$escape_characters = 0;
			while ( $p >= 0 ) {
				if ( $add[ $p ] !== $this->_escape_character ) {
					break;
				}
				$escape_characters++;
				$p--;
			}

			// odd number of escape characters? then this delimiter is escaped
			if ( ( $escape_characters % 2 ) === 1 ) {
				$string .= $add;
				continue;
			}

			// remove final delimiter
			$string .= substr( $add , 0 , -1 );
			break;
		}

		// commit
		$stream->commit();

		// strip escape characters
		$value = $string;
		if ( $this->_strip ) {
			if ( $this->_escape_character === '\\' ) $value = stripslashes( $value );
			else $value = preg_replace( '/' . preg_quote( $this->_escape_character ) . '(.)/' , '\1' , $value );
		}

		// insert node into tree
		$this->_insertIntoTree( $parser , $parent , $parent_node , $delimiter . $string . $delimiter , $value );

		return true;
	}
}