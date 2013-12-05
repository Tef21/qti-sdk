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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 * @subpackage
 *
 */

namespace qtism\runtime\rendering\markup\xhtml;

use qtism\runtime\rendering\AbstractRenderingContext;
use qtism\data\QtiComponent;
use qtism\runtime\rendering\AbstractRenderer;
use \DOMDocumentFragment;

/**
 * Base class of all XHTML renderers.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class AbstractXhtmlRenderer extends AbstractRenderer {
    
    /**
     * A tag name to be used instead of the 
     * QTI class name for rendering.
     * 
     * @var string
     */
    private $replacementTagName = '';
    
    /**
     * A set of additional CSS classes to be added
     * to the rendered element.
     *
     * @var array
     */
    private $additionalClasses = array();
    
    /**
     * Create a new XhtmlAbstractRenderer object.
     *
     * @param AbstractRenderingContext An optional rendering context to be used e.g. when outside of a rendering engine.
     */
    public function __construct(AbstractRenderingContext $renderingContext = null) {
        parent::__construct($renderingContext);
    }
    
    /**
     * Render a QtiComponent into a DOMDocumentFragment that will be registered
     * in the current rendering context.
     */
    public function render(QtiComponent $component) {
        $doc = $this->getRenderingContext()->getDocument();
        $fragment = $doc->createDocumentFragment();
        
        $this->renderingImplementation($fragment, $component);
        $this->getRenderingContext()->storeRendering($component, $fragment);
        return $fragment;
    }
    
    protected function renderingImplementation(DOMDocumentFragment $fragment, QtiComponent $component) {
        
        
        $this->appendElement($fragment, $component);
        $this->appendChildren($fragment, $component);
        $this->appendAttributes($fragment, $component);
        
        if ($this->hasAdditionalClasses() === true) {
            $classes = implode("\x20", $this->getAdditionalClasses());
            $currentClasses = $fragment->firstChild->getAttribute('class');
            $glue = ($currentClasses !== '') ? "\x20" : "";
            $fragment->firstChild->setAttribute('class', $currentClasses . $glue . $classes);
        }
        
        // Reset additional classes for next rendering.
        $this->setAdditionalClasses(array());
    }
    
    /**
     * Append a new DOMElement to the currently rendered $fragment which is suitable
     * to $component.
     * 
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     */
    protected function appendElement(DOMDocumentFragment $fragment, QtiComponent $component) {
        $tagName = ($this->hasReplacementTagName() === true) ? $this->getReplacementTagName() : $component->getQtiClassName();
        $fragment->appendChild($this->getRenderingContext()->getDocument()->createElement($tagName));
    }
    
    /**
     * Append the children renderings of $components to the currently rendered $fragment.
     * 
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     */
    protected function appendChildren(DOMDocumentFragment $fragment, QtiComponent $component) {
        $element = $fragment->firstChild;
        foreach ($this->getRenderingContext()->getChildrenRenderings($component) as $childrenRendering) {
            $element->appendChild($childrenRendering->firstChild);
        }
    }
    
    /**
     * Append the necessary attributes of $component to the currently rendered $fragment.
     * 
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     */
    abstract protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component);
    
    /**
     * Set the replacement tag name.
     * 
     * @param string $replacementTagName
     */
    protected function setReplacementTagName($replacementTagName) {
        $this->replacementTagName = $replacementTagName;
    }
    
    /**
     * Get the replacement tag name.
     * 
     * @return string
     */
    protected function getReplacementTagName() {
        return $this->replacementTagName;
    }
    
    /**
     * Whether a replacement tag name is defined.
     * 
     * @return boolean
     */
    protected function hasReplacementTagName() {
        return $this->getReplacementTagName() !== '';
    }
    
    /**
     * The renderer will by default render the QTI Component into its markup equivalent, using
     * the QTI class name returned by the component as the rendered element name.
     * 
     * Calling this method will make the renderer use $tagName as the element node name to be
     * used at rendering time.
     * 
     * @param string $tagName A tagname e.g. 'div'.
     */
    public function transform($tagName) {
        $this->setReplacementTagName($tagName);
    }
    
    /**
     * Set the array of additional CSS classes.
     *
     * @param array $additionalClasses
     */
    protected function setAdditionalClasses(array $additionalClasses) {
        $this->additionalClasses = $additionalClasses;
    }
    
    /**
     * Get the array of additional CSS classes.
     *
     * @return array
     */
    protected function getAdditionalClasses() {
        return $this->additionalClasses;
    }
    
    /**
     * Whether additional CSS classes are defined for rendering.
     *
     * @return boolean
     */
    protected function hasAdditionalClasses() {
        return count($this->getAdditionalClasses()) > 0;
    }
    
    /**
     * Add an additional CSS class to be rendered.
     *
     * @param string $additionalClass A CSS class.
     */
    public function additionalClass($additionalClass) {
        $additionalClasses = $this->getAdditionalClasses();
        $additionalClasses[] = $additionalClass;
        $this->setAdditionalClasses(array_unique($additionalClasses));
    }
}