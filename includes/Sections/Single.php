<?php

namespace LiteLexer\Sections;
use LiteLexer\Exceptions\Parse;
use LiteLexer\Parser;
use LiteLexer\Stream;
use LiteLexer\Tree\Branch;

/**
 * Class Single
 * @package LiteLexer\Sections
 *
 * Will match just one of the potential blocks specified. By default, it must match something, but required can be
 * turned off.
 */
class Single extends Section
{
	/**
	 * @var string[]
	 */
	protected $_potential_blocks;

	protected $_required = true;

	public function __construct($potential_blocks) {
		if (is_array($potential_blocks)) {
			$this->_potential_blocks = $potential_blocks;
		}
		else {
			$this->_potential_blocks = func_get_args();
		}
	}

	/**
	 * @param bool $required
	 * @return $this
	 */
	public function setRequired($required) {
		$this->_required = $required;
		return $this;
	}

	public function parse(Parser $parser, Branch $parent_node, Stream $stream) {
		// new node
		$node = new Branch();

		// look through array of sections
		foreach ($this->_potential_blocks as $block) {
			// find first matching phrase
			if ($parser->getBlock($block)->parse($parser, $node, $stream)) {
				// insert tree
				$this->_insertIntoTree($parser, $parent_node, $node);

				return true;
			}
		}

		// required
		if ($this->_required) {
			// add potential exception
			$parser->addPotentialException($stream, new Parse(sprintf('Expection one of %s .', implode(', ', $this->_potential_blocks))));

			return false;
		}

		return true;
	}
}
