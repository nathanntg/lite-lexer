<?php

namespace Addins\Parser;

/**
 * Class ParserBlock
 * @package Addins\Parser
 *
 * The building block of the parsing engine. Each ParserBlock can match a specific string (components) or a set of other
 * blocks (sections). These are then translated into branches and leaves in the tree.
 */
abstract class ParserBlock
{
	protected $_name;

	// whether or not this should be added to the Tree at all
	protected $_capture;

	abstract public function parse( Parser $parser , ParserBlock $parent , Tree_Branch $parent_node , ParserStream $stream );

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->_name = $name;
	}

	/**
	 * @param bool $capture
	 */
	public function setCapture($capture) {
		$this->_capture = $capture;
	}

	public function __toString() {
		if ( isset( $this->_name ) ) return $this->_name;
		return 'unnamed block [' . get_class($this) . ']';
	}

	abstract protected function _insertIntoTree( Parser $parser , ParserBlock $parent , Tree_Branch $parent_node );
}