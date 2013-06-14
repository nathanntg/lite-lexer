<?php

namespace Addins\Parser;

abstract class Tree_Node
{
	/**
	 * @var self|null
	 */
	protected $_parent;

	/**
	 * @var string
	 */
	protected $_name;

	public function __construct() {
	}

	/**
	 * @param self $parent
	 */
	public function setParent( self $parent) {
		$this->_parent = $parent;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->_name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * @return self|null
	 */
	public function getParent() {
		return $this->_parent;
	}

	abstract public function emptyLeafNodes();
	abstract public function getRaw();
	abstract public function debug($prefix='');
	abstract public function prune();
	abstract public function isEmpty();
}