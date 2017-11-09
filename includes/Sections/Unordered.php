<?php

namespace LiteLexer\Sections;
use LiteLexer\Exceptions\Parse;
use LiteLexer\Parser;
use LiteLexer\Stream;
use LiteLexer\Tree\Branch;

/**
 * Class Unordered
 * @package LiteLexer\Sections
 *
 * Will match 0 or more of potential blocks in any order. By default, it must match at least one. This can be customized by
 * changing the required option.
 *
 * Example 1:
 * new Sections_Unordered('a','b','c')
 * will match "c b a c a a b" or "c" or "a" BUT NOT "" (since required by default)
 *
 * Example 2, explicit number required:
 * ( new Sections_Unordered('a','b','c') )->setRequired( 3 )
 * will match "a b c" or "c b a" or "c c c" BUT NOT "c c" or "a a a a"
 *
 * Example 3, not required:
 * ( new Sections_Unordered('a','b','c') )->setRequired( false )
 * will match "c b a c a a b" or "c" or "a" or ""
 *
 */
class Unordered extends Section
{
	/**
	 * @var string[]
	 */
	protected $_potential_blocks;

	/**
	 * @var bool|int
	 */
	protected $_required = true;

	public function __construct($potential_blocks) {
		if (is_array($potential_blocks)) {
			$this->_potential_blocks = $potential_blocks;
		}
		else {
			$this->_potential_blocks = func_get_args();
		}
	}

	/**
	 * How many blocks should be required. True means 1 or more, false means 0 or more, and an integer means require
	 * exactly that number.
	 *
	 * @param bool|int $required
	 * @return $this
	 */
	public function setRequired($required) {
		$this->_required = $required;
		return $this;
	}

	public function parse(Parser $parser, Branch $parent_node, Stream $stream) {
		// take snapshot
		$stream->snapshot();

		// count number matched
		$matched = 0;

		// initiate node
		$node = new Branch();

		// as long as we can match phrases, do it...
		while (true) {
			// look through array of sections
			foreach ($this->_potential_blocks as $block) {
				// find first matching phrase
				if ($parser->getBlock($block)->parse($parser, $node, $stream)) {
					$matched++;
					continue;
				}
			}

			// must be done
			break;
		}

		// check required count
		if ($this->_required) {
			if ( is_int( $this->_required ) ) {
				if ( $matched !== $this->_required ) {
					// add potential execption
					$parser->addPotentialException( $stream , new Parse( 'Expecting exactly ' . $this->_required . ' of ' . implode( ', ' , $this->_potential_blocks ) . ', but saw ' . $matched . '.' ) );

					// revert to snapshot
					$stream->revert();

					return false;
				}
			}
			else {
				if (0 === $matched) {
					// add potential exception
					$parser->addPotentialException( $stream , new Parse( 'Expecting at least one ' . implode( ', ' , $this->_potential_blocks ) . '.' ) );

					// revert to snapshot
					$stream->revert();

					return false;
				}
			}
		}

		// commit changes
		$stream->commit();

		// insert tree
		$this->_insertIntoTree($parser, $parent_node, $node);

		return true;
	}
}