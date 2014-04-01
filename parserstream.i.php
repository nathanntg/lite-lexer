<?php

namespace Addins\Parser;

/**
 * Class ParserStream
 * @package Addins\Parser
 *
 * The stream reader, which handles a sequential reading, has helpful functions for peeking ahead. In addition, it has
 * a "transaction" like system that allows conditional consumption which can be rolled back if subsequent blocks don't
 * match.
 */
class ParserStream
{
	protected $_rollback = [];
	protected $_string;
	protected $_position = 0;
	protected $_length;

	public function __construct($string) {
		$this->_string = $string;
		$this->_length = strlen($string);
	}

	/**
	 * Get internal position.
	 * @return int
	 */
	public function getPosition() {
		return $this->_position;
	}

	/**
	 * Amount of characters remaining.
	 * @return int
	 */
	public function getRemainingLength() {
		return max( $this->_length - $this->_position , 0 );
	}

	/**
	 * Is at the end of the stream?
	 * @return bool
	 */
	public function isEndOfStream() {
		return ( $this->_position >= $this->_length );
	}

	/**
	 * Read $bytes without shifting the internal pointer.
	 * @param int $bytes
	 * @return string
	 */
	public function peek($bytes) {
		return substr( $this->_string , $this->_position , $bytes );
	}

	/**
	 * Read $bytes and advance the internal pointer.
	 * @param int $bytes
	 * @return string
	 */
	public function consume($bytes) {
		$ret = substr( $this->_string , $this->_position , $bytes );
		$this->_position += $bytes;
		return $ret;
	}

	/**
	 * Read as many bytes until $character is first encountered.
	 * @param string $character
	 * @return bool|string
	 */
	public function consumeUntil($character) {
		$p = strpos( $this->_string , $character , $this->_position );
		if ( $p === false ) return false;

		$amount = $p - $this->_position;
		$ret = substr( $this->_string , $this->_position , $amount + 1 );
		$this->_position += $amount + 1;
		return $ret;
	}

	/**
	 * Peek at the last $bytes characters.
	 * @param $bytes
	 * @return string
	 */
	public function peekBack($bytes) {
		if ( $this->_position < $bytes ) {
			if ( $this->_position === 0 ) return '';
			return substr( $this->_string , 0 , $this->_position );
		}
		return substr($this->_string,$this->_position-$bytes,$bytes);
	}

	/**
	 * Takes a snapshot that can either be reverted or committed.
	 */
	public function snapshot() {
		$this->_rollback[] = $this->getPosition();
	}

	/**
	 * Reverts reads since last un-committed snapshot.
	 */
	public function revert() {
		$this->_position = array_pop( $this->_rollback );
	}

	/**
	 * Commits reads since last snapshot.
	 */
	public function commit() {
		array_pop( $this->_rollback );
	}

	/**
	 * Moves the pointer back by $bytes.
	 * @param int $bytes
	 */
	public function rewind( $bytes ) {
		$this->_position = max( $this->_position - $bytes , 0 );
	}

	/**
	 * Moves the pointer forward by $bytes.
	 * @param int $bytes
	 */
	public function skip( $bytes ) {
		$this->_position += $bytes;
	}

	/**
	 * Rewinds to the beginning and clears the rollback queue.
	 */
	public function reset() {
		$this->_position = 0;
		$this->_rollback = [];
	}
}
