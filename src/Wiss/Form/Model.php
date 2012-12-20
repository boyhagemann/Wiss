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
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class Model extends Form implements InputFilterProviderInterface
{		    
	/**
	 * 
	 */
    public function prepareElements()
    {                   		
		// Title
        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'title',
            'attributes' => array(
                'label' => 'Name of the model'
            )
        ));
        
        // Module    
        $this->add(array(
            'name' => 'module',
            'type' => 'DoctrineORMModule\Form\Element\EntitySelect',
            'attributes' => array(
                'label' => 'Belongs to module',
            ),
            'options' => array(
                'target_class' => 'Wiss\Entity\Module',
                'property' => 'title',
            )
        ));    
        
        // Root    
//        $this->add(array(
//            'name' => 'node',
//            'type' => 'DoctrineORMModule\Form\Element\EntitySelect',
//            'attributes' => array(
//                'label' => 'Put in navigation',
//            ),
//            'options' => array(
//                'target_class' => 'Wiss\Entity\Navigation',
//                'property' => 'label',
//            )
//        ));    
        
		// Submit
        $this->add(array(
            'type' => 'Zend\Form\Element\Submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Save',
                'class' => 'btn btn-primary btn-large',
            )
        ));
	}

    /**
     * 
     * @return array
     */
	public function getInputFilterSpecification()
    {
        return array(
            
        );
    }
	
}
