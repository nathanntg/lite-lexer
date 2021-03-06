lite-lexer
==========

A light-weight PHP-based string lexer. By defining parser blocks, a simple syntax can be defined.
The parser will then build a tree representing the parsed string. This can then be interpreted 
for other uses.

I created this for a simple query language to read custom data from a model system, but it 
can easily be adapted to other syntaxes. It has relatively low overhead, and based on my
testing performed fast (enough so for my application).

Installation
------------

This package is available via composer and can be added to a project using the command:

```bash
$ composer require nathanntg/lite-lexer
```

Usage
-----

The recommended usage is to subclass `LiteLexer\Parser` to define your own syntax blocks 
(see the example implementation). Then you can run your parser as follows:

```php
$parser = new MyParser();

// parse string
$tree = $parser->parseString($string_to_parse);

// optional: remove unneeded leaves and branches
$tree->prune();

// print debugging tree
$tree->debug();
```

Example Implementation
----------------------

The example folder contains a QueryParser class that implements the parser needed
to read a basic query language. Specifically, given an input string like the following:

```
customer.namelast='' AND customer.namefirst = '' OR ( customer.namelast='' AND customer.namefirst='' )  ORDER customer.namelast DESC , customer.namelast   LIMIT 2
```

It will produce the following parse tree:

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

Testing
-------

You can run the PHPUnit tests using the following command (assuming installed via
composer, and includes the developer requirements):

```bash
$ vendor/bin/phpunit tests
```
