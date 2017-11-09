<?php

namespace LiteLexer\Exceptions;
use LiteLexer\Tree\Branch;
use LiteLexer\Tree\Leaf;
use LiteLexer\Tree\Node;

/**
 * Class TreeRead
 * @package LiteLexer\Exceptions
 *
 * TreeRead are thrown if you try to read from a non-existent node. This is often indicative that your tree
 * interpreter does not match the output of parser.
 */
class TreeRead extends Exception
{
	protected function _describeNode(Node $node, $children=false) {
		$name = $node->getName();
		if (empty($name)) {
			$name = 'unnamed node';
		}

		// add identifier
		$name .= ($node instanceof Leaf ? ' [leaf]' : ' [branch]');

		// describe children as well (non-recusive, just helps provide a bit more context)
		if ($children && $node instanceof Branch) {
			$name .= ' with children ' . implode(', ', array_map([$this, '_describeNode'], $node->getNodes()));
		}

		return $name;
	}

	public function __construct(Node $node, $message, $code=0, \Exception $previous=null) {
		parent::__construct($message . ' at node ' . $this->_describeNode($node, true), $code, $previous);
	}
}
