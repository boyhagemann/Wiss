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
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;
use Wiss\Entity\Model;

class ModelElement extends Form implements ServiceLocatorAwareInterface
{	
    protected $serviceLocator;

    /**
	 * 
	 * @param Model $model
	 */
    public function prepareElements()
    {                		
        $this->setHydrator(new ClassMethodsHydrator());
		$this->setAttribute('class', 'form-horizontal');
			                        
        // Get the value options from the service manager config
        $config = $this->getServiceLocator()->get('config');
        $valueOptions = $config['model-element-builders'];
			
        // Label element
        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'label',
            'options' => array(
                'label' => 'Label',
            ),
        ));
        
		// Add the select field with the available elements
		$select = new Element\Select('builder');
		$select->setValueOptions($valueOptions); 
		$select->setOptions(array(
			'label' => 'Choose an element builder'
		));
		$this->add($select);
			    		   
        
		// Submit
		$submit = new Element('submit');
		$submit->setAttributes(array(
			'type'  => 'submit',
			'value' => 'Save',
			'class' => 'btn btn-primary btn-large',
		));

		$this->add($submit);

		
		$inputFilter = $this->getInputFilter();
		$inputFilter->add(new Input('builder', array(
			'required' => true,
		)));
		$this->setInputFilter($inputFilter);
	
				
	}
	
	public function getServiceLocator() {
		return $this->serviceLocator;
	}

	public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}

}
