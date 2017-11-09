<?php

namespace Addins\Parser;

/**
 * Class Sections_Optional
 * @package Addins\Parser
 * An easier wrapper to define an optional block.
 */
class Sections_Optional extends Sections_Section
{
	protected $_block;

	public function __construct( $block ) {
		$this->_block = $block;
	}

	public function parse(Parser $parser, Tree_Branch $parent_node, ParserStream $stream) {
		// make tree node
		$node = new Tree_Branch();

		// use component matcher
		if ($parser->getBlock($this->_block)->parse($parser, $node, $stream)) {
			// insert into tree
			$this->_insertIntoTree($parser, $parent_node, $node);
		}

		return true;
	}
}