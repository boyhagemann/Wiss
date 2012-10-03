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
			'type' => 'Zend\Form\Element\MultiCheckbox',
			'name' => 'generate',
			'attributes' => array(
				'class' => 'checkbox',
			),
			'options' => array(
				'value_options' => array(
					'form' => 'Generate a form based on the given elements',
					'controller' => 'Generate a controller to manage the model',
					'model' => 'Generate a new model',
					'config' => 'Generate routes and navigation',
				),
			),
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
		$inputFilter->add(new Input('generate'));
		$this->setInputFilter($inputFilter);
				
	}
	
}
