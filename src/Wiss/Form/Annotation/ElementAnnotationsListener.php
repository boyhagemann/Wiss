<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Form
 */

namespace Wiss\Form\Annotation;

use Wiss\Form\Mapping;
use Zend\Form\Annotation\AbstractAnnotationsListener;
use Zend\EventManager\EventManagerInterface;

/**
 * Default listeners for element annotations
 *
 * Defines and attaches a set of default listeners for element annotations
 * (which are defined on object properties). These include:
 *
 * - AllowEmpty
 * - Attributes
 * - ErrorMessage
 * - Filter
 * - Flags
 * - Input
 * - Hydrator
 * - Object
 * - Required
 * - Type
 * - Validator
 *
 * See the individual annotation classes for more details. The handlers registered
 * work with the annotation values, as well as the element and input specification
 * passed in the event object.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Annotation
 */
class ElementAnnotationsListener extends AbstractAnnotationsListener
{
    /**
     * Attach listeners
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('configureElement', array($this, 'handleTextAnnotation'));
        $this->listeners[] = $events->attach('configureElement', array($this, 'handleTextareaAnnotation'));
    }

    /**
     * Determine if the element has been marked to exclude from the definition
     *
     * @param  \Zend\EventManager\EventInterface $e
     * @return bool
     */
    public function handleTextAnnotation($e)
    {
        $annotation = $e->getParam('annotation');
        if (!$annotation instanceof Mapping\Text) {
            return;
        }
		
        $elementSpec = $e->getParam('elementSpec');
        $elementSpec['spec']['attributes']['type'] = 'text';
        $elementSpec['spec']['attributes']['label'] = $annotation->getLabel();		
    }

    /**
     * Determine if the element has been marked to exclude from the definition
     *
     * @param  \Zend\EventManager\EventInterface $e
     * @return bool
     */
    public function handleTextareaAnnotation($e)
    {
        $annotation = $e->getParam('annotation');
        if (!$annotation instanceof Mapping\Textarea) {
            return;
        }
		
        $elementSpec = $e->getParam('elementSpec');
        $elementSpec['spec']['attributes']['type'] = 'textarea';
        $elementSpec['spec']['attributes']['label'] = $annotation->getLabel();		
    }

}
