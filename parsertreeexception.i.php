<?php

namespace Addins\Parser;

/**
 * Class ParserTreeException
 * @package Addins\Parser
 *
 * ParserTreeExceptions are thrown if you try to read from a non-existent node. This is often indicative that your tree
 * interpreter does not match the output of parser.
 */
class ParserTreeException extends \Exception
{
	protected function _describeNode( Tree_Node $node , $children=false ) {
		$name = $node->getName();
		if ( !$name ) $name = 'unnamed node';
		$name .= ( $node instanceof Tree_Leaf ? ' [leaf]' : ' [branch]' );

		if ( $children && $node instanceof Tree_Branch ) {
			$name .= ' with children ' . implode( ', ' , array_map( [ $this , '_describeNode' ] , $node->getNodes() ) );
		}

		return $name;
	}

	public function __construct( Tree_Node $node , $message , $code=0 , \Exception $previous=null ) {
		parent::__construct( $message . ' at node ' . $this->_describeNode( $node , true ) , $code , $previous );
	}
}