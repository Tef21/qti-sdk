<?php

namespace qtism\runtime\tests;

use qtism\data\AssessmentTest;
use \RuntimeException;

/**
 * The AbstractAssessmentTestSessionFactory class is a bed for instantiating
 * various implementations of AssessmentTestSession.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AbstractAssessmentTestSessionFactory {
    
    /**
     * The Route object to be used to instantiate an AssessmentTestSession object.
     * 
     * @var Route
     */
    private $route;
    
    /**
     * The AssessmentTest object to be used to instantiate an AssessmentTestSession object.
     * 
     * @var AssessmentTest
     */
    private $assessmentTest;
    
    public function __construct(AssessmentTest $assessmentTest) {
        $this->setAssessmentTest($assessmentTest);
    }
    
    /**
     * Set the Route object to be used to instantiate an AssessmentTestSession object.
     * 
     * @param Route $route A Route object.
     */
    public function setRoute(Route $route = null) {
        $this->route = $route;
    }
    
    /**
     * Get the Route object to be used to instantiate An AssessmentTestSession object.
     * 
     * @return Route A Route object.
     */
    public function getRoute() {
        return $this->route;
    }
    
    /**
     * Set the AssessmentTest object to be used to instantiate an AssessmentTestSession object.
     * 
     * @param AssessmentTest $assessmentTest An AssessmentTest object.
     */
    public function setAssessmentTest(AssessmentTest $assessmentTest) {
        $this->assessmentTest = $assessmentTest;
    }
    
    /**
     * Get the AssessmentTest object to be used to instantiate an AssessmentTestSession object.
     * 
     * @return AssessmentTest An AssessmentTest object.
     */
    public function getAssessmentTest() {
        return $this->assessmentTest;
    }
    
    /**
     * Create a new AssessmentTestSession object with the content
     * of the factory.
     * 
     * @return AssessmentTestSession An AssessmentTestSession object.
     * @throws RuntimeException If no Route has been provided to the factory yet.
     */
    public function createAssessmentTestSession() {
        if (is_null($this->getRoute() === true)) {
            $msg = "No Route has been set in the factory. The AssessmentTestSession cannot be instantiated without it.";
            throw new RuntimeException($msg);
        }
    }
}