<?php

namespace LiteLexer\Components;
use LiteLexer\Block;
use LiteLexer\Parser;
use LiteLexer\Tree\Branch;
use LiteLexer\Tree\Leaf;

/**
 * Class Components_Component
 * @package Addins\Parser
 *
 * A component represents something to actually match in the string. Components add Tree_Leaf entries to the parse tree.
 */
abstract class Component extends Block
{
	protected $_cb_process;

	/**
	 * A post-processor that converts the raw matched string into a processed value. Allows more processing to happen mid-parse
	 * The same outcome can be achieved while reading the parse tree, of course.
	 * @param callable $cb_process
	 * @return $this
	 */
	public function setCallbackProcess($cb_process) {
		$this->_cb_process = $cb_process;
		return $this;
	}

	protected function _insertIntoTree(Parser $parser, Branch $parent_node, $raw_string=null, $pre_processed=null) {
		// what to capture
		if (false === $this->_capture) {
			// null values are used to keep a raw tree that can be full recombined
			$value = null;
		}
		elseif (isset($this->_cb_process)) {
			$value = call_user_func($this->_cb_process, (null === $pre_processed ? $raw_string : $pre_processed), $raw_string);
		}
		else {
			$value = (null === $pre_processed ? $raw_string : $pre_processed);
		}

		// add components as leaf
		$leaf = new Leaf($raw_string, $value);
		$leaf->setName($this->_name);
		$parent_node->addNode($leaf);
	}
}
