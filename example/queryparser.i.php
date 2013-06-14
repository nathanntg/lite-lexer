<?php

namespace Addins\Parser;

class ExampleQueryParser extends Parser
{
	public function __construct() {
		parent::__construct();

		// COLUMN
		$this->registerBlock( 'column' , new Components_Preg( '(?P<column>([a-z]+\.)*[a-z]+)' ) );

		// VALUE
		// possible values (no whites spaces)
		$this->registerBlock( 'value_string' , new Components_EnclosedString() );
		$this->registerBlock( 'value_numeric' , new Components_Preg( '(?P<number>(\+|-|)(\d+(\.\d+|)|\.\d+))' ) );
		$this->registerBlock( 'value_boolean' , new Sections_Single( new Components_String( 'true' ) , new Components_String( 'false' ) ) );
		$this->registerBlock( 'value_null' , new Components_String( 'NULL' ) );
		$ds = new Sections_DelimitedSet( 'value' , new Components_Preg('\s*,\s*' ) );
		$ds->setMinimumEntries(0);
		$this->registerBlock( 'value_set_values' , $ds );
		$this->registerBlock( 'value_set' , new Sections_Ordered( new Components_String( '(' ) , new Components_Whitespace() , 'value_set_values' , new Components_Whitespace() , new Components_String( ')' ) ) );

		// ALL possible values
		$this->registerBlock( 'value' , new Sections_Single( 'value_string' , 'value_numeric' , 'value_boolean' , 'value_null' , 'value_set' ) );

		// add operator
		$operators = [ '<=' , '>=' , '>' , '=>' , '=<' , '=' , '==' , '<>' , '!=' , 'IS' , 'IS NOT' , 'IN' , 'NOT IN'  , 'LIKE' , 'NOT LIKE' ];
		$operator_blocks = array_map( function( $op ) { return new Components_String( $op ); } , $operators );
		$this->registerBlock( 'operator' , new Sections_Single( $operator_blocks ) );

		// CONDITION
		$this->registerBlock( 'condition_standard' , new Sections_Ordered( 'column' , new Components_Whitespace() , 'operator' ,  new Components_Whitespace() , 'value' ) );
		$this->registerBlock( 'condition_all' , new Components_String( '1' ) );
		$this->registerBlock( 'condition' , new Sections_Single( 'condition_standard' , 'condition_all' ) );
		$this->registerBlock( 'condition_set_values' , new Sections_DelimitedSet( 'conditions' , 'condition_operator' , true ) );
		$this->registerBlock( 'condition_set' , new Sections_Ordered( new Components_String( '(' ) , new Components_Whitespace() , 'condition_set_values' , new Components_Whitespace() , new Components_String( ')' ) ) );
		$this->registerBlock( 'conditions' , new Sections_Single( 'condition' , 'condition_set' )  );
		$this->registerBlock( 'conditions_main' , new Sections_Ordered( new Components_Whitespace() , new Sections_Single( 'condition_set_values' , 'condition_set'  , new Components_Whitespace() ) ) );

		// add operator
		$operators = [ 'AND' , 'OR' ];
		$operator_blocks = array_map( function( $op ) { return new Components_String( $op ); } , $operators );
		$this->registerBlock( 'condition_operator' , new Sections_Ordered( new Components_Whitespace(true) , new Sections_Single( $operator_blocks ) , new Components_Whitespace(true) ) );

		// ORDER BY
		$this->registerBlock( 'order_by_direction' , new Sections_Single( new Components_String( 'asc' ) , new Components_String( 'desc' ) ) );
		$this->registerBlock( 'order_by_column' , new Sections_Ordered( 'column' , new Sections_Optional( new Sections_Ordered( new Components_Whitespace( true ) , 'order_by_direction' )  ) ) );
		$this->registerBlock( 'order_by_columns' , new Sections_DelimitedSet('order_by_column',new Components_Preg('\s*,\s*' )) );
		$this->registerBlock( 'order_by_main' , new Sections_Ordered( new Components_Whitespace() , new Components_String( 'ORDER' ) , new Sections_Optional( new Sections_Ordered( new Components_Whitespace() , new Components_String( 'BY' ) ) ) , new Components_Whitespace() , 'order_by_columns' ) );

		// ORDER BY
		$this->registerBlock( 'limit_start' , new Components_Preg( '\d+') );
		$this->registerBlock( 'limit_count' , new Components_Preg( '\d+') );
		$this->registerBlock( 'limit_main' , new Sections_Ordered( new Components_Whitespace() , new Components_String( 'LIMIT' ) , new Components_Whitespace() , new Sections_Optional( new Sections_Ordered( 'limit_start' , new Components_Whitespace() , new Components_String(',') , new Components_Whitespace() ) ) , 'limit_count' ) );

		// QUERY
		$this->registerBlock( 'query' , new Sections_Ordered( 'conditions_main' , [ 'order_by_main' , null ] , [ 'limit_main' , null ] ) );

		// SET INITIAL BLOCK
		$this->setInitialBlock( 'query' );
	}
}

$qp = new ExampleQueryParser();

// parse
$tree = $qp->parseString('customer.namelast=\'\' AND customer.namefirst = \'\' OR ( customer.namelast=\'\' AND customer.namefirst=\'\' )  ORDER customer.namelast DESC , customer.namelast   LIMIT 2');

// reconstruct the original string from the parse tree
$original_query = $tree->getRaw();

// remove unneeded leaves and branches
$tree->prune();

// print debugging tree
$tree->debug();

/*

PRODUCES:
root
	query
		conditions_main
			condition_set_values
				conditions
					condition
						condition_standard
							column; raw "customer.namelast"; customer.namelast
							operator
								unnamed leaf; raw "="; =
							value
								value_string; raw "''";
				condition_operator
					unnamed leaf; raw "AND"; AND
				conditions
					condition
						condition_standard
							column; raw "customer.namefirst"; customer.namefirst
							operator
								unnamed leaf; raw "="; =
							value
								value_string; raw "''";
				condition_operator
					unnamed leaf; raw "OR"; OR
				conditions
					condition_set
						unnamed leaf; raw "("; (
						condition_set_values
							conditions
								condition
									condition_standard
										column; raw "customer.namelast"; customer.namelast
										operator
											unnamed leaf; raw "="; =
										value
											value_string; raw "''";
							condition_operator
								unnamed leaf; raw "AND"; AND
							conditions
								condition
									condition_standard
										column; raw "customer.namefirst"; customer.namefirst
										operator
											unnamed leaf; raw "="; =
										value
											value_string; raw "''";
						unnamed leaf; raw ")"; )
		order_by_main
			unnamed leaf; raw "ORDER"; ORDER
			order_by_columns
				order_by_column
					column; raw "customer.namelast"; customer.namelast
					order_by_direction
						unnamed leaf; raw "DESC"; DESC
				order_by_column
					column; raw "customer.namelast"; customer.namelast
		limit_main
			unnamed leaf; raw "LIMIT"; LIMIT
			limit_count; raw "2"; 2

*/

