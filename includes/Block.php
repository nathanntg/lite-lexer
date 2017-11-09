<?php

namespace LiteLexer;

/**
 * Class Block
 * @package LiteLexer
 *
 * The building block of the parsing engine. Each ParserBlock can match a specific string (components) or a set of other
 * blocks (sections). These are then translated into branches and leaves in the tree.
 */
abstract class Block
{
    /**
     * The name for the parse block. Names are used within the parse tree and for debugging purposes.
     * @var string|null
     */
    protected $_name;

    /**
     * Whether or not parsed values should be added to the Tree at all.
     * @var bool
     */
    protected $_capture;

	abstract public function parse(Parser $parser, Tree\Branch $parent_node, Stream $stream);

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
		if (isset($this->_name)) return $this->_name;
		return 'unnamed block [' . get_class($this) . ']';
	}

	abstract protected function _insertIntoTree(Parser $parser, Tree\Branch $parent_node);
}
