<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use qtism\data\state\DefaultValue;
use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use qtism\data\state\TemplateDeclaration;
use \DOMDocument;

class TemplateDeclarationMarshallerTest extends QtiSmTestCase {

	public function testMarshall21() {
	    
	    $values = new ValueCollection(array(new Value('tplx', BaseType::IDENTIFIER)));
	    $defaultValue = new DefaultValue($values);
	    $templateDeclaration = new TemplateDeclaration('tpl1', BaseType::IDENTIFIER, Cardinality::SINGLE, $defaultValue);
	    $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($templateDeclaration)->marshall($templateDeclaration);
	    
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    $this->assertEquals('<templateDeclaration identifier="tpl1" cardinality="single" baseType="identifier"><defaultValue><value>tplx</value></defaultValue></templateDeclaration>', $dom->saveXML($element));
	}
	
	public function testUnmarshall21() {
	    $element = $this->createDOMElement('
	        <templateDeclaration identifier="tpl1" cardinality="single" baseType="identifier"><defaultValue><value>tplx</value></defaultValue></templateDeclaration>
	    ');
	    
	    $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
	    $this->assertInstanceOf('qtism\\data\\state\\TemplateDeclaration', $component);
	    $this->assertEquals('tpl1', $component->getIdentifier());
	    $this->assertEquals(Cardinality::SINGLE, $component->getCardinality());
	    $this->assertEquals(BaseType::IDENTIFIER, $component->getBaseType());
	    
	    $default = $component->getDefaultValue();
	    $this->assertInstanceOf('qtism\\data\\state\\DefaultValue', $default);
	    $values = $default->getValues();
	    $this->assertEquals(1, count($values));
	    $this->assertEquals('tplx', $values[0]->getValue());
	}
	
	public function testMarshall20() {
	    // Make sure that paramVariable and mathVariable appear in output even
	    // if their value is false. In QTI 2.0, these attributes are required.
	    
	    $values = new ValueCollection(array(new Value('tplx', BaseType::IDENTIFIER)));
	    $defaultValue = new DefaultValue($values);
	    $templateDeclaration = new TemplateDeclaration('tpl1', BaseType::IDENTIFIER, Cardinality::SINGLE, $defaultValue);
	    $element = $this->getMarshallerFactory('2.0.0')->createMarshaller($templateDeclaration)->marshall($templateDeclaration);
	     
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    $this->assertEquals('<templateDeclaration identifier="tpl1" cardinality="single" baseType="identifier" paramVariable="false" mathVariable="false"><defaultValue><value>tplx</value></defaultValue></templateDeclaration>', $dom->saveXML($element));
	}
	
	public function testUnmarshall20NoParamVariable() {
	    $element = $this->createDOMElement('
	        <templateDeclaration identifier="tpl1" cardinality="single" baseType="identifier"><defaultValue><value>tplx</value></defaultValue></templateDeclaration>
	    ');
	    
	    $expectedMsg = "The mandatory attribute 'paramVariable' is missing from element 'templateDeclaration'.";
	    $this->setExpectedException('\\qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException', $expectedMsg);
	    $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
	}
	
	public function testUnmarshall20NoMathVariable() {
	    $element = $this->createDOMElement('
	        <templateDeclaration identifier="tpl1" cardinality="single" baseType="identifier" paramVariable="true"><defaultValue><value>tplx</value></defaultValue></templateDeclaration>
	    ');
	     
	    $expectedMsg = "The mandatory attribute 'mathVariable' is missing from element 'templateDeclaration'.";
	    $this->setExpectedException('\\qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException', $expectedMsg);
	    $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
	}
}