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
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class Properties extends Form implements InputFilterProviderInterface, ServiceLocatorAwareInterface
{		
    protected $serviceLocator;
    
	/**
	 * 
	 */
    public function prepareElements()
    {                		
		$this->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods());
		$this->setAttribute('class', 'form-horizontal');
		
		// Title
		$title = new Element('title');
		$title->setAttributes(array(
			'type' => 'text',
			'label' => 'Name of the model'
		));
        
        // Module        
        $this->add(array(
            'name' => 'module',
            'type' => 'Wiss\Form\Element\ModelSelect',
            'options' => array(
                'model' => 'module',
            )
        ));

		// Submit
		$submit = new Element('submit');
		$submit->setAttributes(array(
			'type'  => 'submit',
			'value' => 'Save',
			'class' => 'btn btn-primary btn-large',
		));

		$this->add($title);
		$this->add($module);
		$this->add($submit);

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
    
	public function getServiceLocator() {
		return $this->serviceLocator;
	}

	public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}
	
}
