<?php

namespace Addins\Parser;

/**
 * Class Components_Component
 * @package Addins\Parser
 *
 * A component represents something to actually match in the string. Components add Tree_Leaf entries to the parse tree.
 */
abstract class Components_Component extends ParserBlock
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

	protected function _insertIntoTree( Parser $parser , ParserBlock $parent , Tree_Branch $parent_node , $raw_string=null , $pre_processed=null ) {
		// filter
		if ( $this->_capture === false ) $value = null; // null values are used to keep a raw tree that can be full recombined
		elseif ( $this->_cb_process ) $value = call_user_func( $this->_cb_process , ( $pre_processed === null ? $raw_string : $pre_processed ) , $raw_string );
		else $value = ( $pre_processed === null ? $raw_string : $pre_processed );

		// add components as leaf
		$leaf = new Tree_Leaf( $raw_string , $value );
		$leaf->setName( $this->_name );
		$parent_node->addNode( $leaf );
	}
}