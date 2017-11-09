<?php

namespace LiteLexer\Sections;
use LiteLexer\Parser;
use LiteLexer\Stream;
use LiteLexer\Tree\Branch;

/**
 * Class Optional
 * @package LiteLexer\Sections
 *
 * An easier wrapper to define an optional block.
 */
class Optional extends Section
{
	protected $_block;

	public function __construct($block) {
		$this->_block = $block;
	}

	public function parse(Parser $parser, Branch $parent_node, Stream $stream) {
		// make tree node
		$node = new Branch();

		// use component matcher
		if ($parser->getBlock($this->_block)->parse($parser, $node, $stream)) {
			// insert into tree
			$this->_insertIntoTree($parser, $parent_node, $node);
		}

		return true;
	}
}
