<?php

namespace Addins\Parser;

use LiteLexer\Stream;

class StreamTest extends \PHPUnit\Framework\TestCase
{
	public function testConsumeUntil() {
		$string = 'here is a string"" with many" " characters';
		$parts = explode('"', $string);
		array_pop($parts);
		$parts[] = false;

		// make stream
		$stream = new Stream($string);

		foreach ($parts as $part) {
			if (is_string($part)) $part .= '"';
			$this->assertEquals($part, $stream->consumeUntil('"'));
		}
	}

	public function testPeek() {
		$stream = new Stream('here is a string');
		$this->assertEquals('here', $stream->peek(4));
		$this->assertEquals('here', $stream->peek(4));

		// check consume
		$this->assertEquals('here ', $stream->consume(5));

		// check peek
		$this->assertEquals('is', $stream->peek(2));
		$this->assertEquals('is a string', $stream->peek(100));

		// check past end
		$this->assertEquals('is a string', $stream->consume(100));
		$this->assertEquals('', $stream->consume(5));
		$this->assertEquals('', $stream->peek(5));
	}

	/**
	 * @depends testPeek
	 */
	public function testSnapshot() {
		$stream = new Stream('here is a string');

		$stream->snapshot();

		// check consume
		$this->assertEquals('here ', $stream->consume(5));
		$this->assertEquals('is', $stream->peek(2));

		// should go back
		$stream->revert();

		// check consume
		$this->assertEquals('here ', $stream->consume(5));
		$this->assertEquals('is', $stream->peek(2));

		$stream->snapshot();

		$this->assertEquals('is ', $stream->consume(3));
		$this->assertEquals('a string', $stream->peek(8));

		$stream->commit();

		$this->assertEquals('a string', $stream->peek(8));
	}
}
