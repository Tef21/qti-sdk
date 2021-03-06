<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\state\Value;
use qtism\common\enums\BaseType;
use qtism\common\datatypes\QtiPair;
use \DOMDocument;

class ValueMarshallerTest extends QtiSmTestCase {

	public function testMarshallBaseType() {

		$fieldIdentifier = 'goodIdentifier';
		$baseType = BaseType::INTEGER;
		$value = 666;

		$component = new Value($value, $baseType, $fieldIdentifier);
		$component->setPartOfRecord(true); // to get the baseType written in output.
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
		$element = $marshaller->marshall($component);

		$this->assertInstanceOf('\\DOMElement', $element);
		$this->assertEquals('value', $element->nodeName);
		$this->assertEquals($fieldIdentifier, $element->getAttribute('fieldIdentifier'));
		$this->assertEquals('integer', $element->getAttribute('baseType'));
		$this->assertEquals($value . '', $element->nodeValue);
	}

	public function testMarshallBaseTypeBoolean() {

		$fieldIdentifier = 'goodIdentifier';
		$baseType = BaseType::BOOLEAN;
		$value = false;

		$component = new Value($value, $baseType, $fieldIdentifier);
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
		$element = $marshaller->marshall($component);

		$this->assertInstanceOf('\\DOMElement', $element);
		$this->assertTrue('false' === $element->nodeValue);
	}

	public function testMarshallNoBaseType() {

		$value = new QtiPair('id1', 'id2');

		$component = new Value($value);
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
		$element = $marshaller->marshall($component);

		$this->assertEquals('id1 id2', $element->nodeValue);
	}

	public function testUnmarshallNoBaseType() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">A B</value>');
		$element = $dom->documentElement;

		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
		$component = $marshaller->unmarshall($element);

		$this->assertInstanceOf('qtism\\data\\state\\Value', $component);
		$this->assertInternalType('string', $component->getValue());
		$this->assertEquals($component->getValue(), 'A B');
	}

	public function testUnmarshallNoBaseTypeButForced() {
		// Here we use the ValueMarshaller as a parametric marshaller
		// to force the Pair to be unserialized as a Pair object
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">A B</value>');
		$element = $dom->documentElement;

		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, array(BaseType::PAIR));
		$component = $marshaller->unmarshall($element);

		$this->assertInstanceOf('qtism\\data\\state\\Value', $component);
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiPair', $component->getValue());
		$this->assertEquals($component->getValue()->getFirst(), 'A');
		$this->assertEquals($component->getValue()->getSecond(), 'B');
	}

	public function testUnmarshallNoBaseTypeButForcedAndEntities() {
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">Hello &lt;b&gt;bold&lt;/b&gt;</value>');
	    $element = $dom->documentElement;

	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, array(BaseType::STRING));
	    $component = $marshaller->unmarshall($element);

	    $this->assertInstanceOf('qtism\\data\\state\\Value', $component);
	    $this->assertInternalType('string', $component->getValue());
	    $this->assertSame('Hello <b>bold</b>', $component->getValue());
	}

	public function testMarshallNoBaseTypeButForcedAndEntities() {
	    $value = "Hello <b>bold</b>";
	    $baseType = BaseType::STRING;
	    $component = new Value($value, $baseType);

	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
	    $element = $marshaller->marshall($component);

	    $this->assertSame('<value>Hello &lt;b&gt;bold&lt;/b&gt;</value>', $element->ownerDocument->saveXML($element));
	}

    public function testUnmarshallNoValue()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1"></value>');
        $element = $dom->documentElement;
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this->assertSame(-1, $component->getBaseType());
        $this->assertSame('', $component->getValue());
    }

    public function testUnmarshallStringBaseTypeWithNullValue()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" baseType="string"></value>');
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
        $this->assertSame(BaseType::STRING, $component->getBaseType());
        $this->assertSame('', $component->getValue());
    }

	public function testUnmarshallBaseType() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<value xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" baseType="pair" fieldIdentifier="fieldIdentifier1">A B</value>');
		$element = $dom->documentElement;

		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
		$component = $marshaller->unmarshall($element);

		$this->assertInstanceOf('qtism\\data\\state\\Value', $component);
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiPair', $component->getValue());
		$this->assertEquals($component->getValue()->getFirst(), 'A');
		$this->assertEquals($component->getValue()->getSecond(), 'B');
		$this->assertEquals($component->getFieldIdentifier(), 'fieldIdentifier1');
	}
}
