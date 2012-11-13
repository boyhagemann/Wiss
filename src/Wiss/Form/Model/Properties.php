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
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as EntityHydrator;

class Properties extends Form implements InputFilterProviderInterface, ServiceLocatorAwareInterface
{		
    protected $serviceLocator;
    
	/**
	 * 
	 */
    public function prepareElements()
    {           
        $sl = $this->getServiceLocator();
        $formFactory = $sl->get('Wiss\Form\Factory');
        $this->setFormFactory($formFactory); 
        
        // Get the hydrator for doctrine entities
        $em = $sl->get('doctrine.entity_manager.orm_default');                
		$this->setHydrator(new EntityHydrator($em));
        
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
            'attributes' => array(
                'label' => 'Belongs to module',
            ),
            'options' => array(
                'modelName' => 'module',
                'modelLabel' => 'name',
            )
        ));
        
        // Root        
//        $this->add(array(
//            'name' => 'node',
//            'type' => 'Wiss\Form\Element\ModelSelect',
//            'attributes' => array(
//                'label' => 'Put in navigation',
//            ),
//            'options' => array(
//                'modelName' => 'navigation',
//                'modelLabel' => 'label',
//            )
//        ));

		// Submit
		$submit = new Element('submit');
		$submit->setAttributes(array(
			'type'  => 'submit',
			'value' => 'Save',
			'class' => 'btn btn-primary btn-large',
		));

		$this->add($title);
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
