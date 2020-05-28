<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */
namespace qtism\common\datatypes;

use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use \DateInterval;
use \DateTimeZone;
use \DateTime;
use \Exception;
use \InvalidArgumentException;

/**
 * Implementation of the QTI duration datatype.
 *
 * The duration datatype enables you to express time duration as specified
 * by ISO8601.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class QtiDuration implements QtiDatatype
{
    /**
     * Internally, the Duration class always
     * uses the UTC reference time zone.
     *
     * @var string
     */
    const TIMEZONE = 'UTC';

    private $refDate;

    /**
     *
     * @var \DateInterval
     */
    private $interval;

    /**
     * Create a new instance of Duration.
     *
     * The $intervalSpec argument is a duration specification:
     *
     * The format begins with <b>P</b> for "period". Each year of the period is represented
     * by an integer value followed by the period symbol. If the duration contains timing elements,
     * this portion of the specification is prefixed by letter <b>T</b>.
     *
     * * Y -> Years
     * * M -> Months
     * * D -> Days
     * * W -> Week. It will be converted to days. Then, you cannot combine it with D.
     * * H -> Hours
     * * M -> Minutes
     * * S -> Seconds
     *
     * Here are examples: 2 days will be <b>P2D</b>, 2 seconds will be <b>P2TS</b>,
     * 6 years and 5 minutes will be <b>P6YT5M</b>.
     *
     * Please note that this datatype does not support negative durations.
     *
     * @param string $intervalSpec A duration as in ISO8601.
     * @throws \InvalidArgumentException If $intervalSpec is not a valid ISO8601 duration.
     */
    public function __construct($intervalSpec)
    {
        if (gettype($intervalSpec) === 'string' && $intervalSpec !== '') {
            try {
                $tz = new DateTimeZone(self::TIMEZONE);
                $d1 = new DateTime('now', $tz);
                $d2 = clone $d1;
                $d2->add(new DateInterval($intervalSpec));
                $interval = $d2->diff($d1);
                $interval->invert = ($interval->invert === 1) ? 0 : 1;
                $this->interval = $interval;
                $this->refDate = new DateTime('@0', $tz);
            } catch (Exception $e) {
                $msg = "The specified interval specification cannot be processed as an ISO8601 duration.";
                throw new InvalidArgumentException($msg, 0, $e);
            }
        } else {
            $msg = "The intervalSpec argument must be a non-empty string.";
            throw new InvalidArgumentException($msg);
        }

    }

    /**
     * Create a Duration object from a DateInterval object.
     *
     * @param \DateInterval $interval
     * @return \qtism\common\datatypes\QtiDuration
     */
    public static function createFromDateInterval(DateInterval $interval)
    {
        $duration = new QtiDuration('PT0S');
        $duration->setInterval($interval);

        return $duration;
    }

    /**
     * Get the PHP DateInterval object corresponding to the duration.
     *
     * @return \DateInterval A DateInterval PHP object.
     */
    protected function getInterval()
    {
        return $this->interval;
    }

    /**
     * Set the PHP DateInterval object corresponding to the duration.
     *
     * @param \DateInterval $interval A DateInterval PHP object.
     */
    protected function setInterval(DateInterval $interval)
    {
        $this->interval = $interval;
    }

    /**
     * Get the number of years.
     *
     * @return int
     */
    public function getYears()
    {
        return $this->getInterval()->y;
    }

    /**
     * Get the number of months.
     *
     * @return int
     */
    public function getMonths()
    {
        return $this->getInterval()->m;
    }

    /**
     * Get the number of days.
     *
     * @param boolean $total Whether the number of days must be the total of days or simply an offset (default).
     * @return int
     */
    public function getDays($total = false)
    {
        return ($total == true) ? $this->getInterval()->days : $this->getInterval()->d;
    }

    /**
     * Get the number of hours.
     *
     * @return int
     */
    public function getHours()
    {
        return $this->getInterval()->h;
    }

    /**
     * Get the number of minutes.
     *
     * @return int
     */
    public function getMinutes()
    {
        return $this->getInterval()->i;
    }

    /**
     * Get the number of seconds.
     *
     * @param boolean $total Whether to get the total amount of seconds, as a single integer, that represents the complete duration.
     * @return int The value of the total duration in seconds.
     */
    public function getSeconds($total = false)
    {
        if ($total === false) {
            return $this->getInterval()->s;
        }

        $sYears = 31536000 * $this->getYears();
        $sMonths = 30 * 24 * 3600 * $this->getMonths();
        $sDays = 24 * 3600 * $this->getDays();
        $sHours = 3600 * $this->getHours();
        $sMinutes = 60 * $this->getMinutes();
        $sSeconds = $this->getSeconds();

        return $sYears + $sMonths + $sDays + $sHours + $sMinutes + $sSeconds;
    }

    /**
     * QtiDuration to string
     *
     * Returns a string representation of the QtiDuration object, as per ISO8601.
     *
     * @return string
     */
    public function __toString()
    {
        $string = 'P';

        if ($this->interval->y > 0) {
            $string .= $this->interval->y . 'Y';
        }

        if ($this->interval->m > 0) {
            $string .= $this->interval->m . 'M';
        }

        if ($this->interval->d > 0) {
            $string .= $this->interval->d . 'D';
        }

        if ($this->interval->h > 0 || $this->interval->i > 0 || $this->interval->s > 0) {
            $string .= 'T';

            if ($this->interval->h > 0) {
                $string .= $this->interval->h . 'H';
            }

            if ($this->interval->i > 0) {
                $string .= $this->interval->i . 'M';
            }

            if ($this->getSeconds() > 0) {
                $string .= $this->interval->s . 'S';
            }
        }

        if ($string === 'P') {
            // Special case, the duration is 'nothing'.
            return 'PT0S';
        }

        return $string;
    }

    /**
     * Get the encapsulated value from the Non-Scalar object, represented as a string.
     * This is what we can find in result report variables for example.
     *
     * @return string
     */
    public function getValue()
    {
        return (string) $this;
    }

    /**
     * Whether a given $obj is equal to this Duration.
     *
     * @param mixed $obj A given value.
     * @return boolean Whether the equality is established.
     */
    public function equals($obj)
    {
        return (gettype($obj) === 'object' &&
                $obj instanceof self &&
                '' . $obj === '' . $this);
    }

    /**
     * Whether the duration described by this Duration object is shorter
     * than the one described by $duration.
     *
     * @param \qtism\common\datatypes\QtiDuration $duration A Duration object to compare with this one.
     * @return boolean
     */
    public function shorterThan(QtiDuration $duration)
    {
        return $this->getSeconds(true) < $duration->getSeconds(true);
    }

    /**
     * Whether the duration described by this Duration object is longer than or
     * equal to the one described by $duration.
     *
     * @param \qtism\common\datatypes\QtiDuration $duration A Duration object to compare with this one.
     * @return boolean
     */
    public function longerThanOrEquals(QtiDuration $duration)
    {
        return $this->getSeconds(true) >= $duration->getSeconds(true);
    }

    /**
     * Add a duration to this one.
     *
     * For instance, PT1S + PT1S = PT2S.
     *
     * @param \qtism\common\datatypes\QtiDuration|\DateInterval $duration A QtiDuration or DateInterval object.
     */
    public function add($duration)
    {
        $d1 = $this->refDate;
        $d2 = clone $d1;

        if ($duration instanceof QtiDuration) {
            $toAdd = $duration;
        } elseif ($duration instanceof DateInterval) {
            $toAdd = self::createFromDateInterval($duration);
        } else {
            return;
        }

        $d2->add(new DateInterval($this->__toString()));
        $d2->add(new DateInterval($toAdd->__toString()));

        $interval = $d2->diff($d1);
        $this->interval = $interval;
    }

    /**
     * Subtract a duration to this one. If $duration is greater than or equal to
     * the current duration, a duration of 0 seconds is returned.
     *
     * For instance P2S - P1S = P1S
     *
     * @param QtiDuration $duration
     */
    public function sub(QtiDuration $duration)
    {
        if ($duration->longerThanOrEquals($this) === true) {
            $this->setInterval(new DateInterval('PT0S'));
        } else {
            $refStrDate = '@0';
            $tz = new DateTimeZone(self::TIMEZONE);
            $d1 = new DateTime($refStrDate, $tz);
            $d2 = new DateTime($refStrDate, $tz);

            $d1->add(new DateInterval($this->__toString()));
            $d2->add(new DateInterval($duration->__toString()));

            $interval = $d1->diff($d2);
            $this->setInterval($interval);
        }
    }

    public function __clone()
    {
        // ... :'( ... https://bugs.php.net/bug.php?id=50559
        $tz = new DateTimeZone(self::TIMEZONE);
        
        // - This section is critical. Creating
        // a new DateTime object as 'now' for $d2 is extremely
        // dangerous. If the current PHP process goes to sleep
        // right after $d1 instanciation, $d2 could be created
        // n seconds later, where n is the time spent by the PHP
        // process in sleep mode.
        $d1 = new DateTime('now', $tz);
        $d2 = clone $d1;
        // - End of the critical section.
        
        $d2->add(new DateInterval($this->__toString()));
        $interval = $d2->diff($d1);
        $interval->invert = ($interval->invert === 1) ? 0 : 1;
        $this->setInterval($interval);
    }

    /**
     * Whether or not the duration is negative e.g. -PT20S = -20 seconds.
     *
     * @return boolean
     */
    public function isNegative()
    {
        return $this->interval->invert === 0;
    }

    /**
     * Get the baseType of the value. This method systematically returns
     * the BaseType::DURATION value.
     *
     * @return integer A value from the BaseType enumeration.
     */
    public function getBaseType()
    {
        return BaseType::DURATION;
    }

    /**
     * Get the cardinality of the value. This method systematically returns
     * the Cardinality::SINGLE value.
     *
     * @return integer A value from the Cardinality enumeration.
     */
    public function getCardinality()
    {
        return Cardinality::SINGLE;
    }
}
