<?php

namespace LiteLexer\Components;
use LiteLexer\Exceptions\Parse;
use LiteLexer\Parser;
use LiteLexer\Stream;
use LiteLexer\Tree\Branch;

/**
 * Class Whitespace
 * @package LiteLexer\Components
 * Will match 0 or more whitespace characters (same as PHP trim). By default, the characters are not captured. Turning this
 * to mandatory will cause it to only match if there is at least 1 whitespace character.
 */
class Whitespace extends Component
{
	/**
	 * If there must be one or more spaces for this component to match.
	 * @var bool
	 */
	protected $_mandatory;

	public function __construct($mandatory=false) {
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

	public function parse(Parser $parser, Branch $parent_node, Stream $stream) {
		$raw = '';

		while (true) {
			// end of stream
			if ($stream->isEndOfStream()) break;

			// get next character
			$next = $stream->consume(1);

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
		if ($this->_mandatory && empty($raw)) {
			// add potential error
			$parser->addPotentialException($stream, new Parse('Expected space.'));

			return false;
		}

		// add node
		$this->_insertIntoTree($parser, $parent_node, $raw);

		return true;
	}

	// TODO: compare performance... probably faster for whitespace heavy parse strings
	public function parseAlternative(Parser $parser, Branch $parent_node, Stream $stream) {
		$raw = '';
		$chunk_size = 8;

		while (true) {
			if ($stream->isEndOfStream()) break;

			// read chunk
			$chunk = $stream->consume($chunk_size);

			// trim white space
			$new_chunk = ltrim($chunk);

			// any characters left? rewind by remaining length
			if ($new_chunk) {
				// rewind
				$stream->rewind(strlen($new_chunk));

				// append any whitespaces
				$to_append = $chunk_size - strlen($new_chunk);
				if ($to_append) {
					$raw .= substr($chunk, 0, $to_append);
				}

				break;
			}

			// track raw for node
			$raw .= $chunk;
		}

		// check mandatory
		if ($this->_mandatory && empty($raw)) {
			// add potential error
			$parser->addPotentialException($stream, new Parse('Expected space.'));

			return false;
		}

		// add node
		$this->_insertIntoTree($parser, $parent_node, $raw);

		return true;
	}
}
