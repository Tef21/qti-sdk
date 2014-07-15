<?php

use qtism\common\datatypes\Float;
use qtism\common\datatypes\Identifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\State;
use qtism\runtime\common\ResponseVariable;
use qtism\common\datatypes\Duration;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\AssessmentItemSessionException;
use qtism\data\TimeLimits;
use qtism\data\ItemSessionControl;
use \DateTime;
use \DateTimeZone;

require_once (dirname(__FILE__) . '/../../../QtiSmAssessmentItemTestCase.php');

class AssessmentItemSessionTimingTest extends QtiSmAssessmentItemTestCase {
    
    public function testBeginItemSession() {
        $session = self::instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new Duration('PT30S'));
        
        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginItemSession();
        
        // The session duration must remain the PT0S, because we are not interacting yet.
        $this->assertTrue($session['duration']->equals(new Duration('PT0S')));
        
        // The time reference must remain the same as the one provided at item session beginning time.
        $this->assertEquals('2014-07-14@13:00:00@GMT+0000', $session->getTimeReference()->format('Y-m-d@H:i:s@T'));
    }
    
    /**
     * @depends testBeginItemSession
     */
    public function testBeginFirstAttempt() {
        $session = self::instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new Duration('PT30S'));
        
        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginItemSession();
        
        // The candidate spent 3 seconds to begin his first attempt.
        $session->setTime(new DateTime('2014-07-14T13:00:03+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // Remember that time-tracking is not enabled in the INITIAL state.
        $this->assertTrue($session['duration']->equals(new Duration('PT0S')));
        $this->assertEquals('2014-07-14@13:00:03@GMT+0000', $session->getTimeReference()->format('Y-m-d@H:i:s@T'));
    }
    
    /**
     * @depends testBeginFirstAttempt
     */
    public function testEndAttempt() {
        $session = self::instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new Duration('PT30S'));
        
        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginItemSession();
        
        // The candidate spent 2 seconds to begin his first attempt.
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spent 15 seconds to end his first attempt.
        $session->setTime(new DateTime('2014-07-14T13:00:17+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceB')))));
        
        // Duration should be 15S.
        $this->assertTrue($session['duration']->equals(new Duration('PT15S')));
        
        // Only 1 attempt, so that the session must be closed.
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getState());
    }
    
    /**
     * @depends testEndAttempt
     */
    public function testEndItemSession() {
        $session = self::instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new Duration('PT30S'));
        
        // Give infinite attempts.
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setMaxAttempts(0);
        $session->setItemSessionControl($itemSessionControl);
        
        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginItemSession();
        
        // The candidate spent 2 seconds to begin his first attempt.
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spent 15 seconds to end his first attempt.
        $session->setTime(new DateTime('2014-07-14T13:00:17+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceB')))));
        
        // Infinite number of attempts, so the session is not closed.
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getState());
        
        // Close the session. The duration remains the same.
        $session->endItemSession();
        $this->assertTrue($session['duration']->equals(new Duration('PT15S')));
    }
    
    /**
     * @depends testBeginFirstAttempt
     */
    public function testEndItemSessionBrutal() {
        // -- We will close the session during an attempt.
        $session = self::instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new Duration('PT30S'));
    
        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginItemSession();
    
        // The candidate spent 2 seconds to begin his first attempt.
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();

        // Close the session while the candidate spent 10 seconds on the attempt, but without ending it.
        $session->setTime(new DateTime('2014-07-14T13:00:12+00:00', new DateTimeZone('UTC')));
        $session->endItemSession();
        $this->assertTrue($session['duration']->equals(new Duration('PT10S')));
    }
    
    /**
     * @depends testBeginFirstAttempt
     */
    public function testGetStateTimeOverflow() {
        // -- We test if after a setTime that overflows maxTime,
        //    the session is indeed closed.
        $session = self::instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new Duration('PT30S'));
        $session->setTimeLimits($timeLimits);
        
        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginItemSession();
        
        // The candidate spent 2 seconds to begin his first attempt.
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spent 60 seconds on the attempt.
        $session->setTime(new DateTime('2014-07-14T13:01:02+00:00', new DateTimeZone('UTC')));
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getState());
    }
    
    /**
     * @depends testBeginFirstAttempt
     */
    public function testEndAttemptTimeOverflowNoLateSubmission() {
        
        $session = self::instantiateBasicAssessmentItemSession();
        $timeLimits = new TimeLimits(null, new Duration('PT30S'));
        $session->setTimeLimits($timeLimits);
        
        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginItemSession();
        
        // The candidate spent 2 seconds to begin his first attempt.
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spent 60 seconds on the attempt.
        $session->setTime(new DateTime('2014-07-14T13:01:02+00:00', new DateTimeZone('UTC')));
        // Extra check: make sure that duration cannot be longer than maxTime.
        $this->assertTrue($session['duration']->equals(new Duration('PT30S')));
        
        $this->setExpectedException(
            'qtism\\runtime\\tests\\AssessmentItemSessionException',
            "The maximum time to be spent on the item session has been reached.",
            AssessmentItemSessionException::DURATION_OVERFLOW
        );
        
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceB')))));
    }
    
    /**
     * @depends testEndAttemptTimeOverflowNoLateSubmission
     */
    public function testEndAttemptTimeOverflowWithLateSubmission() {
    
        $session = self::instantiateBasicAssessmentItemSession();
        
        $timeLimits = new TimeLimits(null, new Duration('PT30S'));
        $timeLimits->setAllowLateSubmission(true);
        $session->setTimeLimits($timeLimits);
    
        // The session is time-tracked and begins 2014-07-14 at 1 PM.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginItemSession();
    
        // The candidate spent 2 seconds to begin his first attempt.
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
    
        // The candidate spent 60 seconds on the attempt.
        $session->setTime(new DateTime('2014-07-14T13:01:02+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceB')))));
        
        // The attempt is taken into account because allowLateSubmission = true.
        $this->assertEquals(1, $session['numAttempts']->getValue());
        
        // The session is closed.
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getState());
    }
    
    public function testEvolutionBasicTimeLimitsUnderflowOverflow() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
    
        // Give more than one attempt.
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setMaxAttempts(2);
        $itemSession->setItemSessionControl($itemSessionControl);
    
        // No late submission allowed.
        $timeLimits = new TimeLimits(new Duration('PT1S'), new Duration('PT2S'));
        $itemSession->setTimeLimits($timeLimits);
        
        $itemSession->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $itemSession->beginItemSession();
    
        // End the attempt before minTime of 1 second.
        $this->assertEquals(2, $itemSession->getRemainingAttempts());
        $itemSession->beginAttempt();
        $this->assertEquals(1, $itemSession->getRemainingAttempts());
    
        try {
            $itemSession->endAttempt();
            // An exception MUST be thrown.
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::DURATION_UNDERFLOW, $e->getCode());
        }
    
        // Check that numAttempts is taken into account &
        // that the session is correctly suspended, waiting for
        // the next attempt.
        $this->assertEquals(1, $itemSession['numAttempts']->getValue());
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $itemSession->getState());
    
        // Try again by waiting too much to respect max time at endAttempt time.
        $itemSession->beginAttempt();
        $this->assertEquals(0, $itemSession->getRemainingAttempts());
        $itemSession->setTime(new DateTime('2014-07-14T13:00:03+00:00', new DateTimeZone('UTC')));
    
        try {
            $itemSession->endAttempt();
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::DURATION_OVERFLOW, $e->getCode());
        }
    
        $this->assertEquals(2, $itemSession['numAttempts']->getValue());
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $itemSession['SCORE']);
        $this->assertEquals(0.0, $itemSession['SCORE']->getValue());
    }
    
    public function testAcceptableLatency() {
        $itemSession = self::instantiateBasicAssessmentItemSession(new Duration('PT1S'));
    
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setMaxAttempts(3);
        $itemSession->setItemSessionControl($itemSessionControl);
    
        $timeLimits = new TimeLimits(new Duration('PT1S'), new Duration('PT2S'));
        $itemSession->setTimeLimits($timeLimits);
    
        $itemSession->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $itemSession->beginItemSession();
    
        // Sleep 1 second to respect minTime and stay in the acceptable latency time.
        $itemSession->beginAttempt();
        $itemSession->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $itemSession->endAttempt();
    
        // Sleep 1 more second to achieve the attempt outside the time frame.
        $itemSession->beginAttempt();
        $itemSession->setTime(new DateTime('2014-07-14T13:00:03+00:00', new DateTimeZone('UTC')));
    
        try {
            $itemSession->endAttempt();
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::DURATION_OVERFLOW, $e->getCode());
            $this->assertEquals('PT3S', $itemSession['duration']->__toString());
            $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
            $this->assertEquals(0, $itemSession->getRemainingAttempts());
        }
    }
    
    public function testEvolutionBasicMultipleAttempts() {
    
        $count = 5;
        $attempts = array(new Identifier('ChoiceA'), new Identifier('ChoiceB'), new Identifier('ChoiceC'), new Identifier('ChoiceD'), new Identifier('ChoiceE'));
        $expected = array(new Float(0.0), new Float(1.0), new Float(0.0), new Float(0.0), new Float(0.0));
    
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $itemSessionControl = new ItemSessionControl();
        $itemSessionControl->setMaxAttempts($count);
        $itemSession->setItemSessionControl($itemSessionControl);
        
        $itemSession->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $itemSession->beginItemSession();
    
        for ($i = 0; $i < $count; $i++) {
            // Here, manual set up of responses.
            $this->assertTrue($itemSession->isAttemptable());
            $itemSession->beginAttempt();
    
            // simulate some time... 1 second to answer the item.
            $t = $i + 1;
            $itemSession->setTime(new DateTime("2014-07-14T13:00:0${t}+00:00", new DateTimeZone('UTC')));
    
            $itemSession['RESPONSE'] = $attempts[$i];
            $itemSession->endAttempt();
            $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $itemSession['SCORE']);
            $this->assertTrue($expected[$i]->equals($itemSession['SCORE']));
            $this->assertEquals($t, $itemSession['numAttempts']->getValue());
        }
    
        // The total duration should have taken 5 seconds.
        $this->assertEquals(5, $itemSession['duration']->getSeconds(true));
    
        // one more and we get an exception... :)
        try {
            $this->assertFalse($itemSession->isAttemptable());
            $itemSession->beginAttempt();
            $this->assertTrue(false);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::STATE_VIOLATION, $e->getCode());
        }
    }
    
    public function testAllowLateSubmissionNonAdaptive() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
    
        $timeLimits = new TimeLimits(null, new Duration('PT1S'), true);
        $itemSession->setTimeLimits($timeLimits);
    
        $itemSession->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $itemSession->beginItemSession();
    
        $itemSession->beginAttempt();
        $itemSession['RESPONSE'] = new Identifier('ChoiceB');
    
        // No exception because late submission is allowed.
        $itemSession->setTime(new DateTime('2014-07-14T13:00:05+00:00', new DateTimeZone('UTC')));
        $itemSession->endAttempt();
        $this->assertEquals(1.0, $itemSession['SCORE']->getValue());
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
    }
    
    public function testDurationBrutalSessionClosing() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        
        $itemSession->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $itemSession->beginItemSession();
        $this->assertEquals($itemSession['duration']->__toString(), 'PT0S');
    
        $this->assertTrue($itemSession->isAttemptable());
        $itemSession->beginAttempt();
        
        $itemSession->setTime(new DateTime('2014-07-14T13:00:01+00:00', new DateTimeZone('UTC')));
    
        // End session while attempting (brutal x))
        $itemSession->endItemSession();
        $this->assertEquals($itemSession['duration']->__toString(), 'PT1S');
    }
    
    public function testRemainingTimeOne() {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $this->assertFalse($itemSession->getRemainingTime());
        $timeLimits = new TimeLimits();
        $timeLimits->setMaxTime(new Duration('PT3S'));
        $itemSession->setTimeLimits($timeLimits);
        
        $itemSession->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $itemSession->beginItemSession();
        $this->assertEquals(1, $itemSession->getRemainingAttempts());
        $this->assertTrue($itemSession->getRemainingTime()->equals(new Duration('PT3S')));
    
        $itemSession->beginAttempt();
        $itemSession->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $this->assertTrue($itemSession->getRemainingTime()->equals(new Duration('PT1S')));

        $itemSession->setTime(new DateTime('2014-07-14T13:00:03+00:00', new DateTimeZone('UTC')));
        $this->assertTrue($itemSession->getRemainingTime()->equals(new Duration('PT0S')));
        
        try {
            $itemSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceB')))));
            // Must be rejected, no more time remaining!!!
            $this->assertFalse(true);
        }
        catch (AssessmentItemSessionException $e) {
            $this->assertEquals(AssessmentItemSessionException::DURATION_OVERFLOW, $e->getCode());
            $this->assertTrue($itemSession->getRemainingTime()->equals(new Duration('PT0S')));
        }
    }
    
    public function testRemainingTimeTwo() {
        // by default, there is no max time limit.
        $itemSession = self::instantiateBasicAdaptiveAssessmentItem();
        $this->assertFalse($itemSession->getRemainingTime());
         
        $itemSession->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $itemSession->beginItemSession();
        
        $itemSession->beginAttempt();
        $this->assertFalse($itemSession->getRemainingTime());
        $itemSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')))));
        $this->assertEquals('incomplete', $itemSession['completionStatus']->getValue());
         
        $this->assertFalse($itemSession->getRemainingTime());
        $itemSession->beginAttempt();
        $itemSession->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceB')))));
        $this->assertEquals('completed', $itemSession['completionStatus']->getValue());
        $this->assertFalse($itemSession->getRemainingTime());
    }
}