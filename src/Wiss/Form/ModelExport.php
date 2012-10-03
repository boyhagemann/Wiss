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

class ModelExport extends Form
{	
	
	/**
	 * 
	 */
    public function prepareElements()
    {                		
		$this->setHydrator(new \Zend\Stdlib\Hydrator\ClassMethods());
		$this->setAttribute('class', 'form-horizontal');
		
		// Generate form
		$this->add(array(
			'type' => 'Zend\Form\Element\Checkbox',
			'name' => 'generate_form',
			'attributes' => array(
				'label' => 'Generate form',
			),
			'options' => array(			
				'description' => 'You can generate a form based on the given elements',
			)
		));
		
		// Generate model
		$this->add(array(
			'type' => 'Zend\Form\Element\Checkbox',
			'name' => 'generate_model',
			'attributes' => array(
				'label' => 'Generate model',
			),
			'options' => array(			
				'description' => 'You can generate a model based on the given elements',
			)
		));
		
		// Generate config
		$this->add(array(
			'type' => 'Zend\Form\Element\Checkbox',
			'name' => 'generate_config',
			'attributes' => array(
				'label' => 'Generate routes and navigation',
			),
			'options' => array(			
				'hint' => 'You can export the config',
				'description' => 'You can export the config',
			)
		));
		
		// Submit
		$this->add(array(
			'type' => 'Zend\Form\Element\Submit',
			'name' => 'submit',
			'attributes' => array(
				'value' => 'Export',
				'class' => 'btn btn-primary btn-large',
			),
		));

		$inputFilter = new InputFilter();
		$inputFilter->add(new Input('generate_form'));
		$inputFilter->add(new Input('generate_model'));
		$inputFilter->add(new Input('generate_config'));
		$this->setInputFilter($inputFilter);
				
	}
	
}
