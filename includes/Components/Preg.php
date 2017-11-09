<?php

namespace LiteLexer\Components;
use LiteLexer\Exceptions\Configuration;
use LiteLexer\Parser;
use LiteLexer\Stream;
use LiteLexer\Tree\Branch;

/**
 * Class Preg
 * @package LiteLexer\Components
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
class Preg extends Component
{
	protected $_preg;
	protected $_max_length = 1024;

	public function __construct($preg, $modifiers='i', $delimiters='/') {
		$this->_preg = sprintf('%1$s^%2$s%1$s%3$s', $delimiters, $preg, $modifiers);
	}

	/**
	 * @param int $max_length
	 * @return $this
	 */
	public function setMaxLength($max_length) {
		$this->_max_length = $max_length;
		return $this;
	}

	public function parse(Parser $parser, Branch $parent_node, Stream $stream) {
		$peek = $stream->peek($this->_max_length);

		// run match
		if (preg_match($this->_preg, $peek, $match)) {
			// check for empty match
			if ('' === $match[0]) {
				throw new Configuration('Parser configuration led to matching 0 length string. Can result in an infinite loop.');
			}

			// success
			$stream->skip(strlen($match[0]));

			// add node
			$this->_insertIntoTree($parser, $parent_node, $match[0]);

			return true;
		}

		// fail
		return false;
	}
}
