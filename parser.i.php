<?php

namespace Addins\Parser;

class Parser
{
	/**
	 * Named blocks
	 * @var ParserBlock[]
	 */
	protected $_blocks;

	/**
	 * @var string
	 */
	protected $_initial_block;

	/**
	 * @var Sections_Section
	 */
	protected $_next_block;

	/**
	 * @var Tree_Tree
	 */
	protected $_tree;

	protected $_exception;

	/**
	 * @var ParserException[]
	 */
	protected $_potential_exceptions = [];

	protected $_throw_parse_exceptions = true;

	public function __construct() {

	}

	/** CONFIGURATION **/

	public function registerBlock( $name , ParserBlock $block ) {
		$block->setName( $name );
		$this->_blocks[ $name ] = $block;
	}

	public function setInitialBlock( $block_name ) {
		$this->_initial_block = $block_name;
	}

	/**
	 * @param boolean $throw_parse_exceptions
	 */
	public function setThrowParseExceptions( $throw_parse_exceptions ) {
		$this->_throw_parse_exceptions = $throw_parse_exceptions;
	}

	/** RUNTIME **/
	/** - INTERNAL **/

	/**
	 * @param ParserBlock|string $name
	 * @return ParserBlock
	 * @throws ParserConfigurationException
	 */
	public function getBlock( $name ) {
		if ( $name instanceof ParserBlock ) return $name;
		if ( !isset( $this->_blocks[ $name ] ) ) throw new ParserConfigurationException('Block "' . $name . '" does not exist in the parser configuration.');
		return $this->_blocks[ $name ];
	}

	/**
	 * @param ParserBlock $block
	 */
	public function setNextBlock( ParserBlock $block ) {
		$this->_next_block = $block;
	}

	protected function _throwPotentialException( ParserException $fallback ) {
		if ( !$this->_potential_exceptions ) return $fallback;

		$lowest = null;
		$lowest_key = null;
		foreach ( $this->_potential_exceptions as $key => $exception ) {
			// has position?
			$position = $exception->getStreamPosition();
			if ( $position === null ) continue;

			// first with position?
			if ( $lowest_key === null ) {
				$lowest_key = $key;
				$lowest = $position;
			}

			// earlier?
			if ( $position < $lowest ) {
				$lowest_key = $key;
				$lowest = $position;
			}
		}

		// lowest position (earliest in stream)
		if ( $lowest_key !== null ) return $this->_potential_exceptions[ $lowest_key ];

		// last
		return array_pop( $this->_potential_exceptions );
	}

	public function flushPotentialExceptions() {
		$this->_potential_exceptions = [];
	}

	/**
	 * @param ParserStream $stream
	 * @return bool
	 * @throws ParserException
	 */
	protected function _parse( ParserStream $stream ) {
		// set initial mode
		$this->setNextBlock( $this->getBlock( $this->_initial_block ) );

		// initialize tree
		$this->_tree = new Tree_Tree();
		if ( is_string( $this->_initial_block ) ) $this->_tree->setName( 'root' );

		// reset stream
		$stream->reset();

		// loop should only parse once
		// originally had idea to let each block set the next block, which could still be implemented
		// but same thing can be achieved using ordered list or other blocks
		while ( true ) {
			// parse current section
			$block = $this->_next_block;
			$this->_next_block = null; // in case none set by block

			// error parsing section?
			if ( !$block->parse( $this , null , $this->_tree , $stream ) ) {
				// throw error exception
				throw $this->_throwPotentialException( new ParserException( 'Unable to match "' . $block . '".' ) );
			}

			// no next mode
			if ( $this->_next_block === null ) {
				// is at end of stream?
				if ( $stream->isEndOfStream() ) {
					// success!
					break;
				}

				// throw error exception
				throw $this->_throwPotentialException( new ParserException('Unable to parse "' . $stream->peek($stream->getRemainingLength()) . '".') );
			}
		}

		return true;
	}

	public function addPotentialException( ParserStream $stream , ParserException $exception ) {
		$exception->addStreamDetails( $stream );
		$this->_potential_exceptions[] = $exception;
	}

	/** - EXTERNAL **/

	/**
	 * @return Tree_Tree
	 */
	public function getTree() {
		return $this->_tree;
	}

	/**
	 * @param $string
	 * @param bool|null $throw_parse_exceptions If null, the default setting is used.
	 * @return bool|Tree_Tree
	 * @throws ParserException
	 */
	public function parseString( $string , $throw_parse_exceptions=null ) {
		try {
			$this->_parse( new ParserStream( $string ) );
			return $this->getTree();
		}
		catch ( ParserException $e ) {
			if ( $throw_parse_exceptions === null ? $this->_throw_parse_exceptions : $throw_parse_exceptions ) throw $e;
			return false;
		}
	}

}