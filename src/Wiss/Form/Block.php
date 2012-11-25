<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Wiss\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\Form;

class Block extends Form implements InputFilterProviderInterface
{		
	/**
	 * 
	 */
    public function prepareElements()
    {       
        // Title
        $this->add(array(
            'name' => 'title',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'label' => 'Title',
            )
        ));		
        
        // Controller
        $this->add(array(
            'name' => 'controller',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'label' => 'Controller',
            )
        ));		
        
        // Action
        $this->add(array(
            'name' => 'action',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'label' => 'Action',
            )
        ));		
        
        // Form class
        $this->add(array(
            'name' => 'form_class',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'label' => 'Form class',
            )
        ));		
        
        // Available
        $this->add(array(
            'name' => 'available',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'value' => 1,
                'label' => 'Is available as page content?',
            )
        ));		
        
        // Submit
        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Save',
                'class' => 'btn btn-primary btn-large',
            ),
        ));		
	
    }
    
    /**
     * 
     * @return array
     */
	public function getInputFilterSpecification()
    {
        return array(
            'title' => array(
                'required' => true
            )
        );
    }
    
}
