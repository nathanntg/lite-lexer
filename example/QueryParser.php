<?php

require(__DIR__ . '/../vendor/autoload.php');

class QueryParser extends \LiteLexer\Parser
{
	public function __construct() {
		parent::__construct();

		// COLUMN
		$this->registerBlock('column', new \LiteLexer\Components\Preg('(?P<column>([a-z]+\.)*[a-z]+)'));

		// VALUE
		// possible values (no whites spaces)
		$this->registerBlock('value_string', new \LiteLexer\Components\EnclosedString());
		$this->registerBlock('value_numeric', new \LiteLexer\Components\Preg('(?P<number>(\+|-|)(\d+(\.\d+|)|\.\d+))'));
		$this->registerBlock('value_boolean', new \LiteLexer\Sections\Single(new \LiteLexer\Components\StringLiteral('true'), new \LiteLexer\Components\StringLiteral('false')));
		$this->registerBlock('value_null', new \LiteLexer\Components\StringLiteral('NULL'));
		$ds = new \LiteLexer\Sections\DelimitedSet('value', new \LiteLexer\Components\Preg('\s*,\s*'));
		$ds->setMinimumEntries(0);
		$this->registerBlock('value_set_values', $ds);
		$this->registerBlock('value_set', new \LiteLexer\Sections\Ordered(new \LiteLexer\Components\StringLiteral('('), new \LiteLexer\Components\Whitespace(), 'value_set_values', new \LiteLexer\Components\Whitespace(), new \LiteLexer\Components\StringLiteral(')')));

		// ALL possible values
		$this->registerBlock('value', new \LiteLexer\Sections\Single('value_string', 'value_numeric', 'value_boolean', 'value_null', 'value_set'));

		// add operator
		$operators = ['<=', '>=', '>', '=>', '=<', '=', '==', '<>', '!=', 'IS', 'IS NOT', 'IN', 'NOT IN', 'LIKE', 'NOT LIKE'];
		$operator_blocks = array_map(function ($op) {
			return new \LiteLexer\Components\StringLiteral($op);
		}, $operators);
		$this->registerBlock('operator', new \LiteLexer\Sections\Single($operator_blocks));

		// CONDITION
		$this->registerBlock('condition_standard', new \LiteLexer\Sections\Ordered('column', new \LiteLexer\Components\Whitespace(), 'operator', new \LiteLexer\Components\Whitespace(), 'value'));
		$this->registerBlock('condition_all', new \LiteLexer\Components\StringLiteral('1'));
		$this->registerBlock('condition', new \LiteLexer\Sections\Single('condition_standard', 'condition_all'));
		$this->registerBlock('condition_set_values', new \LiteLexer\Sections\DelimitedSet('conditions', 'condition_operator', true));
		$this->registerBlock('condition_set', new \LiteLexer\Sections\Ordered(new \LiteLexer\Components\StringLiteral('('), new \LiteLexer\Components\Whitespace(), 'condition_set_values', new \LiteLexer\Components\Whitespace(), new \LiteLexer\Components\StringLiteral(')')));
		$this->registerBlock('conditions', new \LiteLexer\Sections\Single('condition', 'condition_set'));
		$this->registerBlock('conditions_main', new \LiteLexer\Sections\Ordered(new \LiteLexer\Components\Whitespace(), new \LiteLexer\Sections\Single('condition_set_values', 'condition_set', new \LiteLexer\Components\Whitespace())));

		// add operator
		$operators = ['AND', 'OR'];
		$operator_blocks = array_map(function ($op) {
			return new \LiteLexer\Components\StringLiteral($op);
		}, $operators);
		$this->registerBlock('condition_operator', new \LiteLexer\Sections\Ordered(new \LiteLexer\Components\Whitespace(true), new \LiteLexer\Sections\Single($operator_blocks), new \LiteLexer\Components\Whitespace(true)));

		// ORDER BY
		$this->registerBlock('order_by_direction', new \LiteLexer\Sections\Single(new \LiteLexer\Components\StringLiteral('asc'), new \LiteLexer\Components\StringLiteral('desc')));
		$this->registerBlock('order_by_column', new \LiteLexer\Sections\Ordered('column', new \LiteLexer\Sections\Optional(new \LiteLexer\Sections\Ordered(new \LiteLexer\Components\Whitespace(true), 'order_by_direction'))));
		$this->registerBlock('order_by_columns', new \LiteLexer\Sections\DelimitedSet('order_by_column', new \LiteLexer\Components\Preg('\s*,\s*')));
		$this->registerBlock('order_by_main', new \LiteLexer\Sections\Ordered(new \LiteLexer\Components\Whitespace(), new \LiteLexer\Components\StringLiteral('ORDER'), new \LiteLexer\Sections\Optional(new \LiteLexer\Sections\Ordered(new \LiteLexer\Components\Whitespace(), new \LiteLexer\Components\StringLiteral('BY'))), new \LiteLexer\Components\Whitespace(), 'order_by_columns'));

		// ORDER BY
		$this->registerBlock('limit_start', new \LiteLexer\Components\Preg('\d+'));
		$this->registerBlock('limit_count', new \LiteLexer\Components\Preg('\d+'));
		$this->registerBlock('limit_main', new \LiteLexer\Sections\Ordered(new \LiteLexer\Components\Whitespace(), new \LiteLexer\Components\StringLiteral('LIMIT'), new \LiteLexer\Components\Whitespace(), new \LiteLexer\Sections\Optional(new \LiteLexer\Sections\Ordered('limit_start', new \LiteLexer\Components\Whitespace(), new \LiteLexer\Components\StringLiteral(','), new \LiteLexer\Components\Whitespace())), 'limit_count'));

		// QUERY
		$this->registerBlock('query', new \LiteLexer\Sections\Ordered('conditions_main', ['order_by_main', null], ['limit_main', null]));

		// SET INITIAL BLOCK
		$this->setInitialBlock('query');
	}
}
