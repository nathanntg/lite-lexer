<?php

namespace Addins\Parser;

/**
 * Class Tree_Leaf
 * @package Addins\Parser
 *
 * A leaf in the tree contains something matched by a component.
 */
class Tree_Leaf extends Tree_Node
{
	protected $_raw;
	protected $_processed;

	public function __construct( $raw , $processed=null ) {
		$this->_raw = $raw;
		$this->_processed = $processed;
	}

	/**
	 * Get raw string that was matched.
	 * @param string $raw
	 */
	public function setRaw($raw) {
		$this->_raw = $raw;
	}

	/**
	 * @return string
	 */
	public function getRaw() {
		return $this->_raw;
	}

	/**
	 * Set processed version of raw string. null if the raw string was discarded (capture set to false for
	 * the component or parent sections).
	 * @param mixed $processed
	 */
	public function setProcessed($processed) {
		$this->_processed = $processed;
	}

	/**
	 * Get processed version of raw string. null if the raw string was discarded (capture set to false for
	 * the component or parent sections).
	 * @return mixed
	 */
	public function getProcessed() {
		return $this->_processed;
	}

	/**
	 * Discard value (use for sections where capture is set to false).
	 */
	public function emptyLeafNodes() {
		$this->_processed = null;
	}

	/**
	 * Print debugging entry.
	 * @param string $prefix
	 */
	public function debug($prefix='') {
		echo $prefix , ( $this->_name ? $this->_name : 'unnamed leaf' ) , '; raw "' , $this->getRaw() , '"; ' , ( $this->_processed === null ? 'DROPPED' : $this->_processed ) , PHP_EOL;
	}

	/**
	 * Was value discarded.
	 * @return bool
	 */
	public function isEmpty() {
		return ( $this->_processed === null );
	}

	public function prune() {

	}
}