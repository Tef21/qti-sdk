<?php
namespace qtismtest\common\collections;

use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use qtism\common\collections\Container;
use qtism\common\collections\StringCollection;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiDuration;
use qtism\common\enums\Cardinality;

class ContainerTest extends QtiSmTestCase {

	/**
	 * A Container object reset at each test.
	 * 
	 * @var Container
	 */
	private $container;
	
	/**
	 * Get the Container object.
	 * 
	 * @return Container A Container object.
	 */
	protected function getContainer() {
		return $this->container;
	}
	
	public function setUp() {
		parent::setUp();
		$this->container = new Container();
	}
	
	public function tearDown() {
	    parent::tearDown();
	    unset($this->container);
	}
	
	/**
	 * @dataProvider validValueProvider
	 */
	public function testAddValid($value) {
		// Try to test any QTI runtime model compliant data
		// for addition in the container.
		$container = $this->getContainer();
		$container[] = $value;
		
		$this->assertTrue($container->contains($value));
	}
	
	/**
	 * @dataProvider invalidValueProvider
	 */
	public function testAddInvalid($value) {
		$container = $this->getContainer();
		
		$this->setExpectedException('\\InvalidArgumentException');
		$container[] = $value;
	}
	
	public function testIsNull() {
		$container = $this->getContainer();
		
		$this->assertTrue($container->isNull());
		
		$container[] = new QtiInteger(1);
		$this->assertFalse($container->isNull());
	}
	
	/**
	 * @dataProvider validValueCollectionProvider
	 */
	public function testCreateFromDataModelValid(ValueCollection $valueCollection) {
		$container = Container::createFromDataModel($valueCollection);
		$this->assertInstanceOf('qtism\\common\\collections\\Container', $container);
	}
	
	/**
	 * @dataProvider validEqualsPrimitiveProvider
	 */
	public function testEqualsPrimitiveValid($a, $b) {
		$this->assertTrue($a->equals($b));
	}
	
	/**
	 * @dataProvider invalidEqualsPrimitiveProvider
	 */
	public function testEqualsPrimitiveInvalid($a, $b) {
		$this->assertFalse($a->equals($b));
	}
	
	/**
	 * @dataProvider occurencesProvider
	 */
	public function testOccurences($container, $lookup, $expected) {
		$this->assertEquals($expected, $container->occurences($lookup));
	}
	
	public function validValueProvider() {
		return array(
			array(new QtiInteger(25)),
			array(new QtiFloat(25.3)),
			array(new QtiInteger(0)),
			array(new QtiString('')),
			array(new QtiString('super')),
			array(new QtiBoolean(true)),
			array(new QtiBoolean(false)),
			array(new QtiDuration('P1D')),
			array(new QtiPoint(20, 20)),
			array(new QtiPair('A', 'B')),
			array(new QtiDirectedPair('C', 'D')),
			array(null)
		);
	}

	public function invalidValueProvider() {
		return array(
			array(new \DateTime()),
			array(array())	
		);
	}
	
	public function validEqualsPrimitiveProvider() {
		return array(
			array(new Container(array(new QtiBoolean(true), new QtiBoolean(false))), new Container(array(new QtiBoolean(false), new QtiBoolean(true)))),
			array(new Container(array(new QtiInteger(14), new QtiInteger(13))), new Container(array(new QtiInteger(13), new QtiInteger(14)))),
			array(new Container(array(null)), new Container(array(null))),
			array(new Container(array(new QtiInteger(0))), new Container(array(new QtiInteger(0)))),
			array(new Container(array(new QtiString('string'))), new Container(array(new QtiString('string')))),
			array(new Container(array(new QtiFloat(14.5))), new Container(array(new QtiFloat(14.5)))),
			array(new Container(array(new QtiString('string1'), new QtiString('string2'))), new Container(array(new QtiString('string1'), new QtiString('string2')))),
			array(new Container(), new Container()),
		);
	}
	
	public function invalidEqualsPrimitiveProvider() {
		return array(
			array(new Container(array(new QtiInteger(14))), new Container(array(new QtiInteger(13)))),
			array(new Container(array(new QtiInteger(14))), new Container(array(new QtiString('string')))),
			array(new Container(array(null)), new Container(array(new QtiInteger(0)))),
			array(new Container(), new Container(array(new QtiInteger(13)))),
			array(new Container(array(new QtiBoolean(true))), new QtiBoolean(true)),
		);
	}
	
	public function occurencesProvider() {
		return array(
			array(new Container(array(new QtiInteger(15))), new QtiInteger(15), 1),
			array(new Container(array(new QtiFloat(14.3))), new QtiFloat(14.3), 1),
			array(new Container(array(new QtiBoolean(true))), new QtiBoolean(true), 1),
			array(new Container(array(new QtiBoolean(false))), new QtiBoolean(false), 1),
			array(new Container(array(new QtiString('string'))), new QtiString('string'), 1),
			array(new Container(array(new QtiInteger(0))), new QtiInteger(0), 1),
			array(new Container(array(null)), null, 1),
			array(new Container(array(new QtiInteger(15), new QtiString('string'), new QtiInteger(15))), new QtiInteger(15), 2),
			array(new Container(array(new QtiFloat(14.3), new QtiInteger(143), new QtiFloat(14.3))), new QtiFloat(14.3),  2),
			array(new Container(array(new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(false))), new QtiBoolean(false), 2),
			array(new Container(array(new QtiString('string'), new QtiInteger(2), new QtiString('str'), new QtiString('string'), new QtiString('string'))), new QtiString('string'), 3),
			array(new Container(array(new QtiString('null'), null)), null, 1),
			array(new Container(array(new QtiInteger(14), new QtiInteger(15), new QtiInteger(16))), true, 0),
			array(new Container(array(new QtiString('string'), new QtiInteger(1), new QtiBoolean(true), new QtiFloat(14.3), new QtiPoint(20, 20), new QtiPoint(20, 21))), new QtiPoint(20, 20), 1),
		    array(new Container(array(null)), new QtiInteger(1), 0)
		);
	}
	
	public function validValueCollectionProvider() {
		$returnValue = array();
		
		$valueCollection = new ValueCollection();
		$returnValue[] = array($valueCollection);
		
		$valueCollection = new ValueCollection();
		$valueCollection[] = new Value(15, BaseType::INTEGER);
		$valueCollection[] = new Value('string', BaseType::STRING);
		$valueCollection[] = new Value(true, BaseType::BOOLEAN);
		$returnValue[] = array($valueCollection);
		
		return $returnValue;
	}
	
	public function testClone() {
		$container = $this->getContainer();
		$container[] = new QtiPoint(10, 20);
		$container[] = new QtiDuration('P2D'); // 2 days.
		$container[] = new QtiPair('A', 'B');
		$container[] = new QtiDirectedPair('C', 'D');
		$container[] = new QtiInteger(20);
		$container[] = new QtiFloat(20.1);
		$container[] = new QtiBoolean(true);
		$container[] = new QtiString('String!');
		
		$clone = clone $container;
		$this->assertFalse($clone === $container);
		$this->assertFalse($clone[0] === $container[0]);
		$this->assertFalse($clone[1] === $container[1]);
		$this->assertFalse($clone[2] === $container[2]);
		$this->assertFalse($clone[3] === $container[3]);
		$this->assertFalse($clone[4] === $container[4]);
		$this->assertFalse($clone[5] === $container[5]);
		$this->assertFalse($clone[6] === $container[6]);
		$this->assertFalse($clone[7] === $container[7]);
	}
	
	public function testContains() {
		$pair = new QtiPair('A', 'B');
		$container = $this->getContainer();
		$container[] = $pair;
		$this->assertTrue($container->contains(new QtiPair('A', 'B')));
	}
	
	public function testContains2() {
	    $identifier = new QtiIdentifier('test');
	    $container = $this->getContainer();
	    $container[] = $identifier;
	    $this->assertTrue($container->contains(new QtiIdentifier('test')));
	}
	
	/**
	 * @dataProvider toStringProvider
	 * 
	 * @param Container $container
	 * @param string $expected The expected result of a __toString() call.
	 */
	public function testToString(Container $container, $expected) {
		$this->assertEquals($expected, $container->__toString());
	}
	
	public function toStringProvider() {
		$returnValue = array();
		
		$returnValue[] = array(new Container(), '[]');
		$returnValue[] = array(new Container(array(new QtiInteger(10))), '[10]');
		$returnValue[] = array(new Container(array(new QtiBoolean(true), new QtiBoolean(false))), '[true; false]');
		$returnValue[] = array(new Container(array(new QtiDuration('P2DT2S'), new QtiPoint(10, 15), new QtiPair('A', 'B'), new QtiDirectedPair('C', 'D'), new QtiString('String!'))), '[P2DT2S; 10 15; A B; C D; \'String!\']');
		
		return $returnValue;
	}
	
	/**
	 * @dataProvider invalidDatatypeProvider
	 * 
	 * @param mixed $value
	 * @param string $expectedMsg
	 */
	public function testInvalidDatatype($value, $expectedMsg) {
	     $this->setExpectedException('\\InvalidArgumentException', $expectedMsg);
	     $container = new Container(array($value));
	}
	
	public function invalidDatatypeProvider() {
	    $msg = "Cannot insert a non QTI Scalar Datatype into a QTI Container. The following Datatypes are accepted ";
        $msg .= "null, QTI Identifier, QTI Boolean, QTI Integer, QTI Float, QTI String, QTI Point, QTI Pair, QTI DirectedPair, ";
        $msg .= "QTI Duration, QTI File, QTI Uri, QTI IntOrIdentifier. ";
	    
	    return array(
	        array(10, $msg . "'php:integer' given."),
	        array(12.2, $msg . "'php:double' given."),
	        array('str', $msg . "'php:string' given."),
	        array(true, $msg . "'php:boolean' given."),
	        array(array(), $msg . "'php:array' given."),
	        array(new Container(), $msg . "'qtism\\common\\collections\\Container' given.")
	    );
	}
	
	public function testAlwaysMultipleCardinality() {
	    $container = new Container();
	    $this->assertEquals(Cardinality::MULTIPLE, $container->getCardinality());
	}
    
    public function testDetach() {
        $object = new QtiBoolean(true);
        $container = new Container(array($object));
        
        $this->assertCount(1, $container);
        
        $container->detach($object);
        
        $this->assertCount(0, $container);
    }
    
    public function testDetachNotFound() {
        $this->setExpectedException(
            '\\UnexpectedValueException',
            "The object you want to detach could not be found in the collection."
        );
        
        $object = new QtiBoolean(true);
        $container = new Container(array($object));
        $container->detach(new QtiBoolean(false));
    }
    
    public function testDetachNotObject() {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "You can only detach 'objects' into an AbstractCollection, 'NULL' given."
        );
        $container = new Container();
        $container->detach(null);
    }
    
    public function testReplaceNotFound() {
        $this->setExpectedException(
            '\\UnexpectedValueException',
            "The object you want to replace could not be found."
        );
        
        $object = new QtiBoolean(true);
        $container = new Container(array($object));
        $container->replace(new QtiBoolean(false), new QtiBoolean(false));
    }
    
    public function testReplaceToReplaceNotObject() {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "You can only replace 'objects' into an AbstractCollection, 'NULL' given."
        );
        
        $object = new QtiBoolean(true);
        $container = new Container(array($object));
        $container->replace(null, new QtiBoolean(false));
    }
    
    public function testReplaceReplacementNotObject() {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "You can only replace 'objects' into an AbstractCollection, 'NULL' given."
        );
        
        $object = new QtiBoolean(true);
        $container = new Container(array($object));
        $container->replace(new QtiBoolean(false), null);
    }
    
    public function testMergeNotCompliantTypes() {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Only collections with compliant types can be merged ('qtism\common\collections\Container' vs 'qtism\common\collections\StringCollection')."
        );
        
        $container1 = new Container();
        $container2 = new StringCollection();
        $container1->merge($container2);
    }
    
    public function testDiffNotCompliantTypes() {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Difference may apply only on two collections of the same type."
        );
        
        $container1 = new Container();
        $container2 = new StringCollection();
        $container1->diff($container2);
    }
    
    public function testIntersectNotCompliantTypes() {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Intersection may apply only on two collections of the same type."
        );
        
        $container1 = new Container();
        $container2 = new StringCollection();
        $container1->intersect($container2);
    }
}
