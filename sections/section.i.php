<?php

namespace Addins\Parser;

/**
 * Class Sections_Section
 * @package Addins\Parser
 * Sections represent sets of components and will add a Tree_Branch to the parse tree.
 */
abstract class Sections_Section extends ParserBlock
{
    /**
     * Whether or not to create a new node for this section in the parse tree. Defaults to true if the section
     * has a name defined.
     *
     * @var bool
     */
    protected $_new_node;

    /**
     * @param bool $new_node
     */
    public function setNewNode($new_node) {
        $this->_new_node = $new_node;
    }

	protected function _insertIntoTree(Parser $parser, Tree_Branch $parent_node, Tree_Branch $self=null) {
		if ( $self === null ) $self = new Tree_Branch();

		// filter
		if ( $this->_capture === false ) {
			// leaf nodes are still retained, but processed values are stripped
			// this allows full parse tree to recompose original message
			// tree utilities can cleave empty nodes
			$self->emptyLeafNodes();
		}

		// don't do new node for components
		$new_node = ( isset( $this->_new_node ) ? $this->_new_node : isset( $this->_name ) );

		// name node
		if ( $this->_name ) $self->setName( $this->_name );

		// add or merge node accordingly
		if ( $new_node ) $parent_node->addNode( $self );
		else $parent_node->mergeNode( $self );
	}
}
