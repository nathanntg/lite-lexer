<?php

namespace Addins\Parser;

/**
 * Class Sections_Single
 * @package Addins\Parser
 * Will match just one of the potential blocks specified. By default, it must match something, but required can be turned off.
 */
class Sections_Single extends Sections_Section
{
	/**
	 * @var ParserBlock
	 */
	protected $_potential_blocks;

	protected $_required = true;

	public function __construct( $potential_blocks ) {
		if ( is_array( $potential_blocks ) ) {
			$this->_potential_blocks = $potential_blocks;
		}
		else {
			$this->_potential_blocks = func_get_args();
		}
	}

	/**
	 * @param bool $required
	 * @return self
	 */
	public function setRequired($required) {
		$this->_required = $required;
		return $this;
	}

	public function parse(Parser $parser, Tree_Branch $parent_node, ParserStream $stream) {
		$node = new Tree_Branch();

		// look through array of sections
		foreach ( $this->_potential_blocks as $block ) {
			// accept string sections
			if ( is_string( $block ) ) $block = $parser->getBlock( $block );

			// find first matching phrase
			if ($block->parse($parser, $node, $stream)) {
				// insert tree
				$this->_insertIntoTree($parser, $parent_node, $node);

				return true;
			}
		}

		// required
		if ( $this->_required ) {
			// add potential execption
			$parser->addPotentialException( $stream , new ParserException( 'Expecting one of ' . implode( ', ' , $this->_potential_blocks ) . '.' ) );

			return false;
		}

		return true;
	}
}