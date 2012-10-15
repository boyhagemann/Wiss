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
    public function prepareElements(Model $model)
    {                		
		$this->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods());
		$this->setAttribute('class', 'form-horizontal');
			
						
		$elements = new Fieldset('elements');
		
		$inputFilter = $this->getInputFilter();
		$inputFilter->add(new Input('elements', array(
			'required' => true,
		)));
                        
        // Get the value options from the service manager config
        $config = $this->getServiceLocator()->get('config');
        $valueOptions = $config['element-config-forms'];
			
        $mapping = $this->getFieldMapping($model->getEntityClass());
		foreach($mapping as $field) {
						
//            \Zend\Debug\Debug::dump($field); exit;
            
			$fieldset = new Fieldset($field['fieldName']);
			$fieldset->setOptions(array(
				'legend' => $field['fieldName'],
			));
                        
			// Add the select field with the available elements
			$select = new Element\Select('type');
			$select->setLabel($field['fieldName']);
			$select->setValueOptions(array('' => 'No element assigned yet...') + $valueOptions); 
			$select->setAttributes(array(
				'class' => 'form-class',
			));
			$fieldset->add($select);
			
			// Add the trigger button to show the modal window
			$button = new Element\Button('trigger');
			$button->setOptions(array(
				'label' => 'Configure',
			));
            
            // Build the element config url
            $plugins = $this->getServiceLocator()->get('controller-plugin-manager');
            $url = $plugins->get('url')->fromRoute('wiss/model/element-config');
            
			$button->setAttributes(array(
				'class' => 'element-config-trigger btn',
				'data-target' => "#myModal",
				'data-remote' => $url,
			));
			$fieldset->add($button);
			
			// Store the result of the modal window form in a hidden element
			$configuration = new Element\Hidden('configuration');
			$configuration->setAttributes(array(
				'class' => 'element-config',
			));
			$fieldset->add($configuration);
                        
                        
			// Add the hidden config element
			$config = new Element\Hidden('button');
			$fieldset->add($config);
			
			$elements->add($fieldset);		
		}
		
		$this->add($elements);
		
                
        
		// Submit
		$submit = new Element('submit');
		$submit->setAttributes(array(
			'type'  => 'submit',
			'value' => 'Save',
			'class' => 'btn btn-primary btn-large',
		));

		$this->add($submit);

		$this->setInputFilter($inputFilter);
	
				
	}
	
	/**
	 *
	 * @param string $class
	 * @return array 
	 */
	public function getFieldMapping($class)
	{
            $em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
            $meta = $em->getClassMetadata($class);
            return $meta->fieldMappings;
	}
	
	/**
	 *
	 * @param string $class 
	 */
	public function setTitleFieldOptions($class)
	{
		$options = array('' => 'Choose a field name...');
		foreach($this->getFieldMapping($class) as $field) {
			$options[$field['fieldName']] = $field['fieldName'];
		}
		$this->get('title_field')->setAttribute('options', $options);		
	}

	public function getServiceLocator() {
		return $this->serviceLocator;
	}

	public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}

}
