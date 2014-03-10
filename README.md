lite-lexer
==============

A light-weight PHP-based string lexer. By defining parser blocks, a simple syntax can be created.
The parser will then build a tree representing the syntax. This can then be interpreted for other
uses.

I created this for a simple query language to read custom data from a model system, but it 
can easily be adapted to other simple syntaxes. It has relatively low overhead, and based on my
testing performed fast (enough so for my application).

The example file shows a basic query language syntax definition, that can
take this input string:

```
customer.namelast='' AND customer.namefirst = '' OR ( customer.namelast='' AND customer.namefirst='' )  ORDER customer.namelast DESC , customer.namelast   LIMIT 2
```

And produce the following parse tree:

```
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
```
