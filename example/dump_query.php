<?php

require('QueryParser.php');

$qp = new QueryParser();

// parse
$tree = $qp->parseString('customer.namelast=\'\' AND customer.namefirst = \'\' OR ( customer.namelast=\'\' AND customer.namefirst=\'\' )  ORDER customer.namelast DESC , customer.namelast   LIMIT 2');

// reconstruct the original string from the parse tree
$original_query = $tree->getRaw();

// remove unneeded leaves and branches
$tree->prune();

// collapse single unnamed nodes
$tree->collapseSingleUnnamedNodes();

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
							operator; raw "="; =
							value
								value_string; raw "''";
				condition_operator; raw "AND"; AND
				conditions
					condition
						condition_standard
							column; raw "customer.namefirst"; customer.namefirst
							operator; raw "="; =
							value
								value_string; raw "''";
				condition_operator; raw "OR"; OR
				conditions
					condition_set
						unnamed leaf; raw "("; (
						condition_set_values
							conditions
								condition
									condition_standard
										column; raw "customer.namelast"; customer.namelast
										operator; raw "="; =
										value
										value_string; raw "''";
							condition_operator; raw "AND"; AND
							conditions
								condition
									condition_standard
										column; raw "customer.namefirst"; customer.namefirst
										operator; raw "="; =
										value
										value_string; raw "''";
						unnamed leaf; raw ")"; )
		order_by_main
			unnamed leaf; raw "ORDER"; ORDER
			order_by_columns
				order_by_column
					column; raw "customer.namelast"; customer.namelast
					order_by_direction; raw "DESC"; DESC
				order_by_column
					column; raw "customer.namelast"; customer.namelast
		limit_main
			unnamed leaf; raw "LIMIT"; LIMIT
			limit_count; raw "2"; 2

*/
