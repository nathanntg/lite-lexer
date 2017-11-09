<?php

namespace LiteLexer\Sections;
use LiteLexer\Block;
use LiteLexer\Parser;
use LiteLexer\Tree\Branch;

/**
 * Class Section
 * @package LiteLexer\Sections
 * Sections represent sets of components and will add a Tree_Branch to the parse tree.
 */
abstract class Section extends Block
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

	protected function _insertIntoTree(Parser $parser, Branch $parent_node, Branch $self=null) {
    	// create self
		if (null === $self) $self = new Branch();

		// filter
		if (false === $this->_capture) {
			// leaf nodes are still retained, but processed values are stripped
			// this allows full parse tree to recompose original message
			// tree utilities can cleave empty nodes
			$self->emptyLeafNodes();
		}

		// name node
		if ($this->_name) {
			$self->setName($this->_name);
		}

		// don't do new node for components
		$new_node = (isset($this->_new_node) ? $this->_new_node : isset($this->_name));

		// add or merge node accordingly
		if ($new_node) {
			$parent_node->addNode($self);
		}
		else {
			$parent_node->mergeNode($self);
		}
	}
}
