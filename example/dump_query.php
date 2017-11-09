<?php

require('QueryParser.php');

$qp = new QueryParser();

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

