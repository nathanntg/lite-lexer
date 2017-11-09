<?php

require(__DIR__ . '/../example/QueryParser.php');

class QueryParserTest extends \PHPUnit\Framework\TestCase
{
	public function testParseValueBoolean() {
		$qp = new QueryParser();
		$qp->setInitialBlock('value_boolean');
		$qp->setThrowParseExceptions(false);

		$tree = $qp->parseString('false');
		$this->assertEquals('false', $tree->getOnly('value_boolean')->getOnly()->getProcessed());

		$this->assertInstanceOf('LiteLexer\Tree\Tree', $tree = $qp->parseString('false'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $tree = $qp->parseString('FALSE'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $tree = $qp->parseString('TRUE'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $tree = $qp->parseString('tRuE'));

		$this->assertEquals(false, $qp->parseString('1234'));
		$this->assertEquals(false, $qp->parseString('TRUE123'));
		$this->assertEquals(false, $qp->parseString('afalse'));
		$this->assertEquals(false, $qp->parseString('120yhwn4h05a8h340a38y4aio3u4z;p934rius-z03i40-=az'));
	}

	public function testParseValueNumber() {
		$qp = new QueryParser();
		$qp->setInitialBlock('value_numeric');
		$qp->setThrowParseExceptions(false);

		$tree = $qp->parseString('132');
		$this->assertEquals('132', $tree->getOnly('value_numeric')->getProcessed());

		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('132'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('.5'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('-.5'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('+456123.123'));

		$this->assertEquals(false, $qp->parseString('+1.'));
		$this->assertEquals(false, $qp->parseString('.'));
		$this->assertEquals(false, $qp->parseString('TRUE123'));
		$this->assertEquals(false, $qp->parseString('afalse'));
		$this->assertEquals(false, $qp->parseString('120yhwn4h05a8h340a38y4aio3u4z;p934rius-z03i40-=az'));
	}

	public function testParseValueString() {
		$qp = new QueryParser();
		$qp->setInitialBlock('value_string');
		$qp->setThrowParseExceptions(false);

		$tree = $qp->parseString('"i am a \' hea\\\\\\"vily escaped string"');
		$this->assertEquals('i am a \' hea\\"vily escaped string', $tree->getOnly('value_string')->getProcessed());

		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('"hello world"'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('""'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('\'\''));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('"i am a \' hea\\\\\\"vily escaped string"'));
		$this->assertEquals(false, $qp->parseString('"string"suffix'));
		$this->assertEquals(false, $qp->parseString('prefix"string"'));
		$this->assertEquals(false, $qp->parseString('\'\'""'));
		$this->assertEquals(false, $qp->parseString('raw'));
		$this->assertEquals(false, $qp->parseString('false'));
		$this->assertEquals(false, $qp->parseString('52'));
		$this->assertEquals(false, $qp->parseString('"i am a \' hea\\\\"vily escaped string"'));
		$this->assertEquals(false, $qp->parseString('"120yhwn4h05a8h340a\"38y4aio3u4z;p934rius-z03i40-=az'));
	}

	public function testParseValueSet() {
		$qp = new QueryParser();
		$qp->setInitialBlock('value_set');
		$qp->setThrowParseExceptions(false);

		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('(true,true)'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('( true,      12498.1234  ,-5, "nathan")'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('("nathan is, cool"             )'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('()'));
		$this->assertEquals(false, $qp->parseString('(hello,bye'));
		$this->assertEquals(false, $qp->parseString('()hello,bye'));
		$this->assertEquals(false, $qp->parseString('hello,bye'));
		$this->assertEquals(false, $qp->parseString('("huh)'));
		$this->assertEquals(false, $qp->parseString('14'));
	}

	/**
	 * @depends testParseValueBoolean
	 */
	public function testParseValue() {
		$qp = new QueryParser();
		$qp->setInitialBlock('value');
		$qp->setThrowParseExceptions(false);

		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('false'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('(true,true)'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('( true,      12498.1234  ,-5, "nathan")'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('("nathan is, cool"             )'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('()'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('132'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('.5'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('-.5'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('+456123.123'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('"hello world"'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('""'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('\'\''));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('"i am a \' hea\\\\\\"vily escaped string"'));
	}

	/**
	 * @depends testParseValueBoolean
	 */
	public function testParseColumn() {
		$qp = new QueryParser();
		$qp->setInitialBlock('column');
		$qp->setThrowParseExceptions(false);

		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('customer.namelast'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('namelast'));
	}

	/**
	 * @depends testParseColumn
	 * @depends testParseValueBoolean
	 */
	public function testParseCondition() {
		$qp = new QueryParser();
		$qp->setInitialBlock('condition');
		$qp->setThrowParseExceptions(false);

		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('customer.namelast = \'\''));
	}

	/**
	 */
	public function testParseConditionOperator() {
		$qp = new QueryParser();
		$qp->setInitialBlock('condition_operator');
		$qp->setThrowParseExceptions(false);

		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString(' OR '));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString(' and '));
		$this->assertEquals(false, $qp->parseString('OR'));
		$this->assertEquals(false, $qp->parseString('AND'));
		$this->assertEquals(false, $qp->parseString('oreaos and bananas'));
	}

	/**
	 * @depends testParseCondition
	 * @depends testParseConditionOperator
	 */
	public function testParseConditionsMain() {
		$qp = new QueryParser();
		$qp->setInitialBlock('conditions_main');
		$qp->setThrowParseExceptions(false);

		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('customer.namelast = \'\' AND customer.namefirst = \'\''));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('customer.namelast=\'\' AND customer.namefirst=\'\''));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('( customer.namelast=\'\' AND customer.namefirst=\'\' )'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('customer.namelast=\'\' AND customer.namefirst=\'\' OR ( customer.namelast=\'\' AND customer.namefirst=\'\' )'));
		$this->assertEquals(false, $qp->parseString('customer.namelast=\'\' AND customer.namefirst=\'\' ( customer.namelast=\'\' AND customer.namefirst=\'\' )'));
	}

	/**
	 * @depends testParseColumn
	 */
	public function testParseOrderBy() {
		$qp = new QueryParser();
		$qp->setInitialBlock('order_by_main');
		$qp->setThrowParseExceptions(false);

		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('ORDER BY customer'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('ORDER customer.namelast, customer.namelast'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString(' ORDER customer.namelast,customer.namelast'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString(' ORDER       customer.namelast     ,      customer.namelast'));
	}

	public function testParseLimit() {
		$qp = new QueryParser();
		$qp->setInitialBlock('limit_main');
		$qp->setThrowParseExceptions(false);

		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('LIMIT 5'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('LIMIT 0, 5'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('LIMIT 0,5'));
		$this->assertInstanceOf('LiteLexer\Tree\Tree', $qp->parseString('        LIMIT 0   ,   5'));
	}

	/**
	 * @depends testParseConditionsMain
	 */
	public function testTreeRaw() {
		$qp = new QueryParser();
		$qp->setThrowParseExceptions(false);

		$queries = [
			'customer.namelast=\'\' AND customer.namefirst=\'\' OR ( customer.namelast=\'\' AND customer.namefirst=\'\' )  ORDER customer.namelast DESC , customer.namelast   LIMIT 2',
			'customer.namelast = \'\' AND customer.namefirst = \'\' AND 1',
			'customer.namelast=\'\' AND customer.namefirst=\'\' OR ( customer.namelast=\'\' AND customer.namefirst=\'\' )',
			'1'
		];

		foreach ($queries as $i => $query) {
			// was successfully parsed?
			$this->assertInstanceOf('LiteLexer\Tree\Tree', $tree = $qp->parseString($query));

			// can reconstruct original?
			$this->assertEquals($query, $tree->getRaw());

			if ($i === 0) {
				$this->assertEquals('2', $tree->getFirst('query')->getFirst('limit_main')->getFirst('limit_count')->getRaw());
			}
		}
	}
}
