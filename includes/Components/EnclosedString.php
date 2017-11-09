<?php

namespace LiteLexer\Components;
use LiteLexer\Parser;
use LiteLexer\Stream;
use LiteLexer\Tree\Branch;

/**
 * Class EnclosedString
 * @package LiteLexer\Components
 *
 * Enclose strings will captures strings like "here is a string" or 'here is a string' and will correctly respect escape
 * characters.
 */
class EnclosedString extends Component
{
	/**
	 * A list of potential string delimiters. Defaults to single and double quotes (" and ').
	 * @var string[]
	 */
	protected $_delimiters;

	/**
	 * The character used for escaping quotes (or itself). Defaults to a backslash (\).
	 * @var string
	 */
	protected $_escape_character;

	/**
	 * Whether or not to strip escape characters.
	 * @var bool
	 */
	protected $_strip = true;

	public function __construct( $delimiters=array('"', '\''), $escape_character='\\') {
		$this->_delimiters = $delimiters;
		$this->_escape_character = $escape_character;
	}

	public function parse(Parser $parser, Branch $parent_node, Stream $stream) {
		$delimiter = $stream->peek(1);
		if (!in_array($delimiter, $this->_delimiters)) {
			// no matching delimiter? done
			return false;
		}

		// take stream snapshot
		$stream->snapshot();

		// consume openining delimiter
		$stream->consume(1);

		// build string
		$string = '';
		while (true) {
			// find closing delimiter
			$add = $stream->consumeUntil($delimiter);

			// no closing delimiter
			if (false === $add) {
				// revert and abort
				$stream->revert();
				return false;
			}

			// count number of sequential escape characters
			$p = strlen($add) - 2;
			$escape_characters = 0;
			while (0 <= $p) {
				if ($add[$p] !== $this->_escape_character) {
					break;
				}
				$escape_characters++;
				$p--;
			}

			// odd number of escape characters? then this delimiter is escaped, continue capturing
			if (1 === ($escape_characters % 2)) {
				$string .= $add;
				continue;
			}

			// remove final delimiter
			$string .= substr($add, 0, -1);

			break;
		}

		// commit
		$stream->commit();

		// strip escape characters
		$value = $string;
		if ($this->_strip) {
			if ('\\' === $this->_escape_character) {
				// remove escape characters via stripslashes
				$value = stripslashes($value);
			}
			else {
				// remove escape characters via preg replace
				$value = preg_replace('/' . preg_quote($this->_escape_character, '/') . '(.)/', '\1', $value);
			}
		}

		// insert node into tree
		$this->_insertIntoTree($parser, $parent_node, $delimiter . $string . $delimiter, $value);

		return true;
	}
}
