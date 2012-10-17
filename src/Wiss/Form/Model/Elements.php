<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Wiss\Form\Model;

use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Wiss\Entity\Model;

class Elements extends Form implements ServiceLocatorAwareInterface
{	
    protected $serviceLocator;

    /**
	 * 
	 * @param Model $model
	 */
    public function prepareElements()
    {                		
		$this->setAttribute('class', 'form-horizontal');
			                        
        // Get the value options from the service manager config
        $config = $this->getServiceLocator()->get('config');
        $valueOptions = $config['element-config-forms'];
			      
		// Add the select field with the available elements
		$select = new Element\Select('element-config-class');
		$select->setValueOptions($valueOptions); 
		$select->setOptions(array(
			'label' => 'Choose an element'
		));
		$select->setAttributes(array(
			'class' => 'form-class',
		));
		$this->add($select);
			    		   
        
		// Submit
		$submit = new Element('submit');
		$submit->setAttributes(array(
			'type'  => 'submit',
			'value' => 'Add new element',
			'class' => 'btn btn-primary btn-large',
		));

		$this->add($submit);

		
		$inputFilter = $this->getInputFilter();
		$inputFilter->add(new Input('element-config-class', array(
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
