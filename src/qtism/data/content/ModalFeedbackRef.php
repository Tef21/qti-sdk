<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013-2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content;

use qtism\data\ShowHide;
use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\common\utils\Format;
use \InvalidArgumentException;

/**
 * An extension of QTI that represents a reference to an external QTI modalFeedback component. 
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ModalFeedbackRef extends QtiComponent
{
    /**
     * The identifier on which it is decided to show or hide the feedback.
     * 
     * @var string
     * @qtism-bean-property
     */
    private $outcomeIdentifier;
    
    /**
     * Whether to show or hide the feedback if the identifier found in the variable
     * referenced in the outcomeIdentifier attribute matches the one referenced
     * by the identifier attribute.
     * 
     * @var integer
     * @qtism-bean-property
     */
    private $showHide;
    
    /**
     * The identifier to match to show or hide the feedback.
     *
     * @var string
     * @qtism-bean-property
     */
    private $identifier;
    
    /**
     * The title of the feedback
     * 
     * @var string
     * @qtism-bean-property
     */
    private $title;

    /**
     * The URI referencing the file containing the definition of the external modalFeedback content.
     *
     * @var string
     * @qtism-bean-property
     */
    private $href;

    /**
     * Create a new ModalFeedbackRef object.
     *
     * @param string $identifier A QTI identifier.
     * @param string $href A URI locating the external modalFeedback content definition.
     * @throws \InvalidArgumentException If any argument is invalid.
     */
    public function __construct($outcomeIdentifier, $showHide, $identifier, $href, $title = '')
    {
        $this->setOutcomeIdentifier($outcomeIdentifier);
        $this->setShowHide($showHide);
        $this->setIdentifier($identifier);
        $this->setHref($href);
        $this->setTitle($title);
    }

    /**
     * Set the identifier that has to be matched to show or hide the feedack content.
     *
     * @param string $identifier A QTI identifier.
     * @throws \InvalidArgumentException If $identifier is not a valid QTI identifier.
     */
    public function setIdentifier($identifier)
    {
        if (Format::isIdentifier($identifier, false) === true) {
            $this->identifier = $identifier;
        } else {
            $msg = "The 'identifier' argument must be a valid QTI identifier, '" . $identifier . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the identifier that has to be matched to show or hide the feedback content.
     *
     * @return string A QTI identifier.
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
   
    /**
     * Set the identifier of the outcome variable that will be used has a lookup
     * to know wheter or not the content of the modalFeedback has to be shown.
     *
     * @param string $outcomeIdentifier A QTI identifier.
     * @throws \InvalidArgumentException If $outcomeIdentifier is not a valid QTI identifier.
     */
    public function setOutcomeIdentifier($outcomeIdentifier)
    {
        if (Format::isIdentifier($outcomeIdentifier, false) === true) {
            $this->outcomeIdentifier = $outcomeIdentifier;
        } else {
            $msg = "The 'outcomeIdentifier' argument must be a valid QTI identifier, '" . $outcomeIdentifier . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }
    
    /**
     * Get the identifier of the outcome variable that will be used has a lookup
     * to know wheter or not the content of the modalFeedback has to be shown.
     *
     * @return string A QTI identifier.
     */
    public function getOutcomeIdentifier()
    {
        return $this->identifier;
    }
    
    /**
     * Set whether the feedback content must be shown or hidden when the identifier matches.
     * 
     * @param integer $showHide A value from the ShowHide enumeration.
     * @throws InvalidArgumentException If $showHide is not a value from the ShowHide enumeration.
     */
    public function setShowHide($showHide)
    {
        if (in_array($showHide, ShowHide::asArray()) === true) {
            $this->showHide = $showHide;
        } else {
            $msg = "The 'showHide' argument must be a value from the ShowHide enumeration, '" . gettype($showHide) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }
    
    /**
     * Get whether the feedback content must be shown or hidden when the identifier matches.
     * 
     * @return integer A value from the ShowHide enumeration.
     */
    public function getShowHide()
    {
        return $this->showHide;
    }

    /**
     * Set the URI locating the external modal definition.
     *
     * @param string $href A URI.
     * @throws \InvalidArgumentException If $href is not a valid URI.
     */
    public function setHref($href)
    {
        if (Format::isUri($href) === true) {
            $this->href = $href;
        } else {
            $msg = "The 'href' argument must be a valid URI, '" . $href . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the URI locating the external rubrickBlock definition.
     *
     * @return string A URI.
     */
    public function getHref()
    {
        return $this->href;
    }
    
    /**
     * Set the title of the feedback.
     * 
     * @param string $title
     * @throws InvalidArgumentException If $title is not a string.
     */
    public function setTitle($title)
    {
        if (is_string($title) === true) {
            $this->title = $title;
        } else {
            $msg = "The 'title' argument must be a string, '" . gettype($title) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }
    
    /**
     * Get the title of the feedback.
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @see \qtism\data\QtiComponent::getComponents()
     */
    public function getComponents()
    {
        return new QtiComponentCollection();
    }
}