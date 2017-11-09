<?php

namespace LiteLexer\Sections;
use LiteLexer\Parser;
use LiteLexer\Stream;
use LiteLexer\Tree\Branch;

/**
 * Class Sections_Ordered
 * @package LiteLexer\Sections
 *
 * This section looks for a set of blocks in a specific order. If it does not find all blocks in the specified order,
 * nothing is matched. This class can also do some branching behavior that can be achieved by a combination of
 * Sections_Single and Sections_Optional by nesting arguments in arrays.
 *
 * Example 1:
 * new Sections_Ordered('open_parentheses','whitespace','comma_separated_list','whitespace','close_parentheses')
 *
 * Example 2 with branching:
 * new Sections_Ordered('conditions',['order_by','limit'])
 * will match order "conditions order_by" or "conditions limit"
 *
 * Example 3 with branching and null:
 * new Sections_Ordered('conditions',['order_by','limit',null])
 * will match "conditions" order "conditions order_by" or "conditions limit"
 */
class Ordered extends Section
{
	/**
	 * @var string[]
	 */
	protected $_sections;

	public function __construct($sections) {
		if (is_array($sections) && 1 === func_num_args()) {
			$this->_sections = $sections;
		}
		else {
			$this->_sections = func_get_args();
		}
	}

	public function parse(Parser $parser, Branch $parent_node, Stream $stream) {
		// take a stream snapshot
		$stream->snapshot();

		// node
		$node = new Branch();

		// for each section...
		foreach ($this->_sections as $section) {
			// if the section is an array, treat it as a bunch of options (shorthand to avoid Single)
			// can also have null at the end of the list of options to make the section optional
			if (is_array($section)) {
				// consider each option...
				foreach ($section as $sec) {
					// can skip
					if (null === $sec) {
						// next section
						continue 2;
					}

					// another snapshot for this option
					$stream->snapshot();

					// try match...
					if ($parser->getBlock($sec)->parse($parser, $node, $stream)) {
						// commit
						$stream->commit();

						// next
						continue 2;
					}

					// did not match, revert snap shot
					$stream->revert();
				}

				// did not match any of the options
				$stream->revert();

				return false;
			}

			// failed to match
			if (!$parser->getBlock($section)->parse($parser, $node, $stream)) {
				// roll back to original snapshot
				$stream->revert();

				return false;
			}
		}

		// matched
		$stream->commit();

		// insert tree
		$this->_insertIntoTree($parser, $parent_node, $node);

		return true;
	}
}
