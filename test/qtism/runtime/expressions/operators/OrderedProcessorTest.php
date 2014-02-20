<?php

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\expressions\operators\OrderedProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\common\enums\BaseType;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\common\MultipleContainer;
use qtism\common\datatypes\Duration;
use qtism\common\datatypes\Point;

class OrderedProcessorTest extends QtiSmTestCase {
	
	public function testNull() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$processor = new OrderedProcessor($expression, $operands);
		
		$operands[] = null;
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$operands = new OperandsCollection(array(new OrderedContainer(BaseType::FLOAT)));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
		
		$operands = new OperandsCollection(array(null, 25, new OrderedContainer(BaseType::INTEGER)));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $result);
		$this->assertEquals(1, count($result));
		$this->assertEquals(BaseType::INTEGER, $result->getBaseType());
		$this->assertEquals(25, $result[0]);
		
		$operands = new OperandsCollection(array(null, 25, new OrderedContainer(BaseType::INTEGER, array(26))));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $result);
		$this->assertEquals(2, count($result));
		$this->assertEquals(BaseType::INTEGER, $result->getBaseType());
		$this->assertEquals(25, $result[0]);
		$this->assertEquals(26, $result[1]);
		
		$operands = new OperandsCollection(array(new OrderedContainer(BaseType::INTEGER), 25, new OrderedContainer(BaseType::INTEGER, array(26))));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $result);
		$this->assertEquals(2, count($result));
		$this->assertEquals(BaseType::INTEGER, $result->getBaseType());
		$this->assertEquals(25, $result[0]);
		$this->assertEquals(26, $result[1]);
		
		$operands = new OperandsCollection();
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertSame(null, $result);
	}
	
	public function testScalar() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 'String1';
		$operands[] = 'String2';
		$operands[] = 'String3';
		$processor = new OrderedProcessor($expression, $operands);
		
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $result);
		$this->assertEquals(3, count($result));
		$this->assertEquals('String1', $result[0]);
		$this->assertEquals('String2', $result[1]);
		$this->assertEquals('String3', $result[2]);
		
		$operands = new OperandsCollection(array('String!'));
		$processor->setOperands($operands);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $result);
		$this->assertEquals(1, count($result));
	}
	
	public function testContainer() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(1, 2), new Point(2, 3)));
		$operands[] = new OrderedContainer(BaseType::POINT);
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(3, 4)));
		$processor = new OrderedProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $result);
		$this->assertEquals(3, count($result));
		$this->assertTrue($result[0]->equals(new Point(1, 2)));
		$this->assertTrue($result[1]->equals(new Point(2, 3)));
		$this->assertTrue($result[2]->equals(new Point(3, 4)));
	}
	
	public function testMixed() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new Point(1, 2);
		$operands[] = null;
		$operands[] = new OrderedContainer(BaseType::POINT, array(new Point(3, 4)));
		$processor = new OrderedProcessor($expression, $operands);
		$result = $processor->process();
		$this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $result);
		$this->assertEquals(2, count($result));
		$this->assertTrue($result[0]->equals(new Point(1, 2)));
		$this->assertTrue($result[1]->equals(new Point(3, 4)));
	}
	
	public function testJuggling() {
	    $expression = $this->createFakeExpression();
	    $operands = new OperandsCollection();
	    $operands[] = new OrderedContainer(BaseType::IDENTIFIER, array('identifier1', 'identifier2'));
	    $operands[] = 'identifier3';
	    $operands[] = new OrderedContainer(BaseType::STRING, array('string1', 'string2'));
	    $operands[] = null;
	    $processor = new OrderedProcessor($expression, $operands);
	    $result = $processor->process();
	    $this->assertInstanceOf('qtism\\runtime\\common\\OrderedContainer', $result);
	    $this->assertEquals(5, count($result));
	    $this->assertTrue($result->equals(new OrderedContainer(BaseType::IDENTIFIER, array('identifier1', 'identifier2', 'identifier3', 'string1', 'string2'))));
	}
	
	public function testWrongBaseType() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = new Point(1, 2);
		$operands[] = new Duration('P2D');
		$operands[] = null;
		$operands[] = 10;
		$processor = new OrderedProcessor($expression, $operands);
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function testWrongCardinality() {
		$expression = $this->createFakeExpression();
		$operands = new OperandsCollection();
		$operands[] = 10;
		$operands[] = null;
		$operands[] = new OrderedContainer(BaseType::INTEGER, array(10));
		$processor = new OrderedProcessor($expression, $operands);
		$result = $processor->process();
		
		$operands[] = new RecordContainer();
		$this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
		$result = $processor->process();
	}
	
	public function createFakeExpression() {
		return $this->createComponentFromXml('
			<ordered>
				<baseValue baseType="boolean">false</baseValue>
				<baseValue baseType="boolean">true</baseValue>
				<ordered>
					<baseValue baseType="boolean">false</baseValue>
				</ordered>
			</ordered>
		');
	}
}