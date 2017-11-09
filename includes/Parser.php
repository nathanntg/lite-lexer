<?php

namespace LiteLexer;

/**
 * Class Parser
 * @package Addins\Parser
 *
 * Orchestrates the actual parsing of a string into a parse tree.
 */
class Parser
{
	/**
	 * Named blocks
	 * @var Block[]
	 */
	protected $_blocks;

	/**
     * The name of the initial block.
	 * @var string
	 */
	protected $_initial_block;

	/**
	 * @var Sections\Section
	 */
	protected $_next_block;

	/**
	 * @var Tree\Tree
	 */
	protected $_tree;

	protected $_exception;

	/**
	 * @var Exceptions\Parse[]
	 */
	protected $_potential_exceptions = [];

	protected $_throw_parse_exceptions = true;

	public function __construct() {

	}

	/** CONFIGURATION **/

	/**
	 * @param $name
	 * @param Block $block
	 */
	public function registerBlock($name , Block $block) {
		$block->setName( $name );
		$this->_blocks[ $name ] = $block;
	}

	/**
	 * @param $block_name
	 */
	public function setInitialBlock($block_name) {
		$this->_initial_block = $block_name;
	}

	/**
	 * @param bool $throw_parse_exceptions
	 */
	public function setThrowParseExceptions($throw_parse_exceptions) {
		$this->_throw_parse_exceptions = $throw_parse_exceptions;
	}

	/** RUNTIME **/
	/** - INTERNAL **/

	/**
     * @internal
	 * @param Block|string $name
	 * @return Block
	 * @throws Exceptions\Configuration
	 */
	public function getBlock($name) {
		// pass blocks directly throw
		if ($name instanceof Block) {
			return $name;
		}
		if ( !isset( $this->_blocks[ $name ] ) ) {
			throw new Exceptions\Configuration('Block "' . $name . '" does not exist in the parser configuration.');
		}
		return $this->_blocks[$name];
	}

	/**
     * @internal
	 * @param Block $block
	 */
	public function setNextBlock(Block $block ) {
		$this->_next_block = $block;
	}

    /**
     * Gets a potential exception to throw. If none exist in the queue, the fallback exception is thrown instead. The system
     * considers all potential exceptions and throws the one that occurred earliest in the parse stream.
     * @param Exceptions\Parse $fallback
     * @return Exceptions\Parse
     */
    protected function _throwPotentialException(Exceptions\Parse $fallback) {
		if (empty($this->_potential_exceptions)) {
			return $fallback;
		}

		$lowest = null;
		$lowest_key = null;
		foreach ($this->_potential_exceptions as $key => $exception) {
			// has position?
			$position = $exception->getStreamPosition();
			if (null === $position) continue;

			// first with position? or earlier position in stream?
			if (null == $lowest_key || $position < $lowest) {
				$lowest_key = $key;
				$lowest = $position;
			}
		}

		// lowest position (earliest in stream)
		if (null !== $lowest_key) {
			return $this->_potential_exceptions[$lowest_key];
		}

		// last
		return array_pop($this->_potential_exceptions);
	}

    /**
     * Once a successful match has been found, potential exceptions should be cleared out.
     * @internal
     */
    public function flushPotentialExceptions() {
		$this->_potential_exceptions = [];
	}

	/**
     * Performs the parsing of a stream.
	 * @param Stream $stream
	 * @return bool
	 * @throws Exceptions\Parse
	 */
	protected function _parse(Stream $stream ) {
		// set initial mode
		$this->setNextBlock( $this->getBlock( $this->_initial_block ) );

		// initialize tree
		$this->_tree = new Tree\Tree();
		$this->_tree->setName('root');
		if ( is_string( $this->_initial_block ) ) $this->_tree->setName( 'root' );

		// reset stream
		$stream->reset();

		// loop should only parse once
		// originally had idea to let each block set the next block, which could still be implemented
		// but same thing can be achieved using ordered list or other blocks
		while (true) {
			// parse current section
			$block = $this->_next_block;
			$this->_next_block = null; // in case none set by block

			// error parsing section?
			if (!$block->parse($this, $this->_tree, $stream)) {
				// throw error exception
				throw $this->_throwPotentialException(new Exceptions\Parse('Unable to match "' . $block . '".'));
			}

			// no next mode
			if (null === $this->_next_block) {
				// is at end of stream?
				if ($stream->isEndOfStream()) {
					// success!
					break;
				}

				// throw error exception
				throw $this->_throwPotentialException(new Exceptions\Parse('Unable to parse "' . $stream->peek($stream->getRemainingLength()) . '".'));
			}
		}

		return true;
	}

    /**
     * Used internally to manage a queue of potential exceptions, should parsing fail. Because the parse tree explores
     * different branches, the exceptions represent only potential errors and will only trigger if all branches fail.
     * The parse stream is passed to add contextual information.
     *
     * Exceptions are prioritized by their place in the parse stream. Should multiple exceptions exist in the queue,
     * the one earliest in the parse stream is thrown.
     *
     * @internal
     * @param Stream $stream
     * @param Exceptions\Parse $exception
     */
    public function addPotentialException(Stream $stream , Exceptions\Parse $exception ) {
		$exception->addStreamDetails( $stream );
		$this->_potential_exceptions[] = $exception;
	}

	/** - EXTERNAL **/

	/**
	 * @return Tree\Tree
	 */
	public function getTree() {
		return $this->_tree;
	}

	/**
	 * @param $string
	 * @param bool|null $throw_parse_exceptions If null, the default setting is used.
	 * @return bool|Tree\Tree
	 * @throws Exceptions\Parse
	 */
	public function parseString( $string , $throw_parse_exceptions=null ) {
		try {
			$this->_parse( new Stream( $string ) );
			return $this->getTree();
		}
		catch (Exceptions\Parse $e) {
			// should throw?
			if (null === $throw_parse_exceptions ? $this->_throw_parse_exceptions : $throw_parse_exceptions) {
				throw $e;
			}
			return false;
		}
	}
}
