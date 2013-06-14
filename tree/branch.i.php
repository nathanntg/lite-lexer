<?php

namespace Addins\Parser;

/**
 * Class Tree_Branch
 * @package Addins\Parser
 *
 * A branch in the tree contains something matched by a section. There are a number of helper functions for
 * reading children easily.
 */
class Tree_Branch extends Tree_Node implements \ArrayAccess, \Countable, \IteratorAggregate
{
	/**
	 * @var Tree_Node[]
	 */
	protected $_nodes = [];

	public function addNode( Tree_Node $node ) {
		$node->setParent( $this );
		$this->_nodes[] = $node;
	}

	public function mergeNode( Tree_Branch $node ) {
		foreach ( $node->_nodes as $sub_node ) {
			$sub_node->setParent( $this );
			$this->_nodes[] = $sub_node;
		}
		//$node->_nodes = [];
	}

	/**
	 * Used by un-captured sections. Will discard all processed data from leaf nodes.
	 */
	public function emptyLeafNodes() {
		foreach ( $this->_nodes as $node ) {
			$node->emptyLeafNodes();
		}
	}

	/**
	 * Combine the raw responses of all children. Note that this can reconstruct the whole original string, as long
	 * as prune or other tree modification functions have not been called.
	 * @return string
	 */
	public function getRaw() {
		$raw = '';
		foreach ( $this->_nodes as $node ) $raw .= $node->getRaw();
		return $raw;
	}

	/**
	 * @return Tree_Node[]
	 */
	public function getNodes() {
		return $this->_nodes;
	}

	/**
	 * Get all nodes with a specific name.
	 * @param string $name
	 * @return Tree_Branch[]|Tree_Leaf[]
	 */
	public function getAll( $name ) {
		$ret = [];
		foreach ( $this->_nodes as $node ) {
			if ( $node->getName() === $name ) $ret[] = $node;
		}
		return $ret;
	}

	/**
	 * @param string|null $assert_name
	 * @return Tree_Branch|Tree_Leaf
	 * @throws ParserTreeException
	 */
	public function getOnly($assert_name=null) {
		// check count
		if ( count( $this->_nodes ) !== 1 ) throw new ParserTreeException( $this , 'does not contain a single node' );

		$node = $this->_nodes[ 0 ];

		// check name
		if ( $assert_name !== null && $node->getName() !== $assert_name ) throw new ParserTreeException( $this , 'expecting "' . $assert_name . '" as single node' );

		return $node;
	}

	/**
	 * @param string $name
	 * @param bool $required
	 * @return Tree_Branch|Tree_Leaf|null
	 * @throws ParserTreeException
	 */
	public function getFirst( $name , $required=true ) {
		foreach ( $this->_nodes as $node ) {
			if ( $node->getName() === $name ) return $node;
		}
		if ( $required ) throw new ParserTreeException( $this , 'expecting one of "' . $name . '"' );
		return null;
	}

	/**
	 * Print debugging copy of the tree for easy readability.
	 * @param string $prefix
	 */
	public function debug($prefix='') {
		echo $prefix , ( $this->_name ? $this->_name : 'unnamed branch' ) , PHP_EOL;
		$prefix .= "\t";
		foreach ( $this->_nodes as $node ) {
			$node->debug( $prefix );
		}
	}

	/**
	 * Contains any nodes.
	 * @return bool
	 */
	public function isEmpty() {
		return ( count( $this->_nodes ) === 0 );
	}

	/**
	 * Remove empty children.
	 */
	public function prune() {
		$new_nodes = [];

		foreach ( $this->_nodes as $node ) {
			// prune sub nodes
			$node->prune();

			// add to node list
			if ( !$node->isEmpty() ) $new_nodes[] = $node;
		}

		$this->_nodes = $new_nodes;
	}

	/**
	 * Number of children nodes.
	 * @return int
	 */
	public function count() {
		return count( $this->_nodes );
	}

	public function getIterator() {
		return new \ArrayIterator( $this->_nodes );
	}

	public function offsetExists($offset) {
		return  isset( $this->_nodes[ $offset ] );
	}

	public function offsetGet($offset) {
		if ( isset( $this->_nodes[ $offset ] ) ) {
			return $this->_nodes[ $offset ];
		}

		return null;
	}

	public function offsetSet($offset,$val) {
		throw new ParserTreeException($this,'unable to write to tree');
	}

	public function offsetUnset($offset) {
		throw new ParserTreeException($this,'unable to write to tree');
	}
}