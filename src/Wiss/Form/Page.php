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

class Page extends Form implements InputFilterProviderInterface
{		
	/**
	 * 
	 */
    public function __construct()
    {                		
        parent::__construct('route');
        
        // Title
        $this->add(array(
            'name' => 'title',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'label' => 'Title',
            )
        ));
		
        // Route
        $this->add(array(
            'name' => 'route',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'label' => 'Route',
            )
        ));
		
        // Layout
        $this->add(array(
            'name' => 'layout',
            'type' => 'DoctrineORMModule\Form\Element\EntitySelect',
            'attributes' => array(
                'label' => 'Layout',
            ),
            'options' => array(
                'target_class' => 'Wiss\Entity\Layout',
                'property' => 'title',
            )
        ));
			
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
            ),
            'route' => array(
                'required' => true
            ),
            'layout' => array(
                'required' => true
            ),
        );
    }
    
}
