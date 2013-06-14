<?php

namespace Addins\Parser;

/**
 * Class ParserException
 * @package Addins\Parser
 *
 * Indicative of an error matching any data. This is often a best guess.
 */
class ParserException extends \Exception
{
	protected $_stream_position;
	protected $_stream_next_bytes;

	public function addStreamDetails( ParserStream $stream ) {
		$this->_stream_position = $stream->getPosition();
		$this->_stream_next_bytes = $stream->peek( 32 );

		$this->message .= ' at position ' . $this->_stream_position . ' beginning "' . $this->_stream_next_bytes . '"';
	}

	/**
	 * @return string|null
	 */
	public function getStreamNextBytes() {
		return $this->_stream_next_bytes;
	}

	/**
	 * @return int|null
	 */
	public function getStreamPosition() {
		return $this->_stream_position;
	}
}