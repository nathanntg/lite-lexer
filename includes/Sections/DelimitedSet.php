<?php

namespace LiteLexer\Sections;
use LiteLexer\Exceptions\Parse;
use LiteLexer\Parser;
use LiteLexer\Stream;
use LiteLexer\Tree\Branch;

/**
 * Class Sections_DelimitedSet
 * @package Addins\Parser
 *
 * Matches strings in the format "entry [delimiter entry]+". This is makes matching comma separated lists way easier.
 * For example, to match a comma separated list, the delimiter could be a Component_String(',') or Component_Preg('\s*,\s*).
 * Delimiters are discarded by default. If the delimiters contain data (e.g., if they are operators), then you can
 * set store_delimiters to true. Use minimum_entries and maximum_entries to define ranges for number of entries matched.
 * By default, one entry is required.
 */
class DelimitedSet extends Section
{
	protected $_entry;
	protected $_delimiter;
	protected $_store_delimiters;
	protected $_minimum_entries = 1;
	protected $_maximum_entries;

	public function __construct($entry, $delimiter, $store_delimiters=false ) {
		$this->_entry = $entry;
		$this->_delimiter = $delimiter;
		$this->_store_delimiters = $store_delimiters;
	}

	/**
	 * Whether or not delimiters should be stored. By default, they are discarded.
	 * @param boolean $store_delimiters
	 * @return self
	 */
	public function setStoreDelimiters($store_delimiters) {
		$this->_store_delimiters = $store_delimiters;
		return $this;
	}

	/**
	 * The minimum number of entries to match. By default 1. Use 0 to allow empty lists.
	 * @param int $minimum_entries
	 * @return self
	 */
	public function setMinimumEntries($minimum_entries) {
		$this->_minimum_entries = $minimum_entries;
		return $this;
	}

	/**
	 * The maximum number of entries to match. By default, no maximum.
	 * @param int $maximum_entries
	 * @return self
	 */
	public function setMaximumEntries($maximum_entries) {
		$this->_maximum_entries = $maximum_entries;
		return $this;
	}

	public function parse(Parser $parser, Branch $parent_node, Stream $stream) {
		// snapshot
		$stream->snapshot();

		// make node
		$node = new Branch();

		$matched = 0;

		// get blocks
		$block_entry = $parser->getBlock($this->_entry);
		$block_delimiter = $parser->getBlock($this->_delimiter);
		if (!$this->_store_delimiters) {
			// TODO: could cause bug if delimiter is used elsewhere and should be captured
			$block_delimiter->setCapture(false);
		}

		while (true) {
			// get delimiter for second and subsequent entries
			if (0 < $matched) {
				// check for delimiter
				if (!$block_delimiter->parse($parser, $node, $stream)) {
					// no delimiter? end of delimited set
					break;
				}
			}

			// get entry
			if (!$block_entry->parse($parser, $node, $stream)) {
				// no entries is an acceptable value
				if (0 === $matched) break;

				// add potential exception
				$parser->addPotentialException($stream, new Parse('Expecting delimited set entry.'));

				// revert
				$stream->revert();

				return false;
			}

			// increment match counter
			$matched++;
		}

		// check minimum range
		if (isset($this->_minimum_entries) && $matched < $this->_minimum_entries) {
			// add potential exception
			$parser->addPotentialException($stream, new Parse(sprintf('Expecting at least %d entries in the delimited set; found %d.', $this->_minimum_entries, $matched)));

			// stream revert
			$stream->revert();

			return false;
		}

		// check maximum range
		if (isset($this->_maximum_entries) && $matched > $this->_maximum_entries) {
			// add potential exception
			$parser->addPotentialException($stream, new Parse(sprintf('Expecting at most %d entries in the delimited set; found %d.', $this->_maximum_entries, $matched)));

			// stream revert
			$stream->revert();

			return false;
		}

		// success
		$stream->commit();

		// insert nodes
		$this->_insertIntoTree($parser, $parent_node, $node);

		return true;
	}
}
