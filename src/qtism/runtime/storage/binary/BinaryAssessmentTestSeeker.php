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
 *
 */

namespace qtism\runtime\storage\binary;

use qtism\data\AssessmentTest;
use qtism\runtime\storage\common\AssessmentTestSeeker;

/**
 * A implementation of AssessmentTestSeeker dedicated to binary data.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class BinaryAssessmentTestSeeker extends AssessmentTestSeeker
{
    /**
     * Create a new BinaryAssessmentTestSeeker object.
     *
     * @param \qtism\data\AssessmentTest $test
     */
    public function __construct(AssessmentTest $test)
    {
        $classes = array(
            'assessmentItemRef', 
            'assessmentSection',
            'testPart',
            'outcomeDeclaration',
            'responseDeclaration',
            'templateDeclaration',
            'branchRule',
            'preCondition',
            'itemSessionControl'
        );
        
        parent::__construct($test, $classes);
    }
}
