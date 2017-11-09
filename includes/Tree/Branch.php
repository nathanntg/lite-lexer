<?php

namespace LiteLexer\Tree;
use LiteLexer\Exceptions\TreeRead;

/**
 * Class Branch
 * @package LiteLexer\Tree
 *
 * A branch in the tree contains something matched by a section. There are a number of helper functions for
 * reading children easily.
 */
class Branch extends Node implements \ArrayAccess, \Countable, \IteratorAggregate
{
	/**
	 * @var Node[]
	 */
	protected $_nodes = [];

	public function addNode(Node $node) {
		$node->setParent($this);
		$this->_nodes[] = $node;
	}

	public function mergeNode(Branch $node) {
		foreach ($node->_nodes as $sub_node) {
			$sub_node->setParent($this);
			$this->_nodes[] = $sub_node;
		}
		//$node->_nodes = [];
	}

	/**
	 * Used by un-captured sections. Will discard all processed data from leaf nodes.
	 */
	public function emptyLeafNodes() {
		foreach ($this->_nodes as $node) {
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
		foreach ($this->_nodes as $node) {
			$raw .= $node->getRaw();
		}
		return $raw;
	}

	/**
	 * @return Node[]
	 */
	public function getNodes() {
		return $this->_nodes;
	}

	/**
	 * Get all nodes with a specific name.
	 * @param string $name
	 * @return Branch[]|Leaf[]
	 */
	public function getAll( $name ) {
		$ret = [];
		foreach ($this->_nodes as $node) {
			if ($node->getName() === $name) {
				$ret[] = $node;
			}
		}
		return $ret;
	}

	/**
	 * @param string|null $assert_name
	 * @return Branch|Leaf|Node
	 * @throws TreeRead
	 */
	public function getOnly($assert_name=null) {
		// check count
		if (1 !== count($this->_nodes)) {
			throw new TreeRead($this, 'does not contain a single node');
		}

		$node = $this->_nodes[0];

		// check name
		if (null !== $assert_name && $node->getName() !== $assert_name ) {
			throw new TreeRead($this, 'expecting "' . $assert_name . '" as single node');
		}

		return $node;
	}

	/**
	 * @param string $name
	 * @param bool $required
	 * @return Branch|Leaf|Node|null
	 * @throws TreeRead
	 */
	public function getFirst( $name , $required=true ) {
		foreach ($this->_nodes as $node) {
			if ($node->getName() === $name) {
				return $node;
			}
		}
		if ($required) {
			throw new TreeRead($this, 'expecting one of "' . $name . '"');
		}
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
		throw new TreeRead($this,'unable to write to tree');
	}

	public function offsetUnset($offset) {
		throw new TreeRead($this,'unable to write to tree');
	}
}
