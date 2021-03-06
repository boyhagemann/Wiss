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
use Wiss\Entity\Model;


class ModelExport extends Form
{	
	protected $model;
	
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
				'class' => 'checkbox',
				'value' => 1,
				'label' => 'Generate form?',
				'checked' => 'checked',
			),
		));
		
		// Form class
		$this->add(array(
			'type' => 'Zend\Form\Element\Text',
			'name' => 'form_class',
			'attributes' => array(
				'label' => 'Form classname',
			),
		));
		
		// Form path
		$this->add(array(
			'type' => 'Zend\Form\Element\Text',
			'name' => 'form_path',
			'attributes' => array(
				'label' => 'Path to form',
				'class' => 'span6',
			),
		));
		
		
		// Generate controller
		$this->add(array(
			'type' => 'Zend\Form\Element\Checkbox',
			'name' => 'generate_controller',
			'attributes' => array(
				'class' => 'checkbox',
				'value' => 1,
				'label' => 'Generate controller?',
				'checked' => 'checked',
			),
		));
		
		// Controller class
		$this->add(array(
			'type' => 'Zend\Form\Element\Text',
			'name' => 'controller_class',
			'attributes' => array(
				'label' => 'Controller classname',
			),
		));
		
		// Controller path
		$this->add(array(
			'type' => 'Zend\Form\Element\Text',
			'name' => 'controller_path',
			'attributes' => array(
				'label' => 'Path to controller',
				'class' => 'span6',
			),
		));
		
		
		// Generate model
		$this->add(array(
			'type' => 'Zend\Form\Element\Checkbox',
			'name' => 'generate_entity',
			'attributes' => array(
				'class' => 'checkbox',
				'value' => 1,
				'label' => 'Generate entity?',
				'checked' => 'checked',
			),
		));
		
		// Entity class
		$this->add(array(
			'type' => 'Zend\Form\Element\Text',
			'name' => 'entity_class',
			'attributes' => array(
				'label' => 'Entity classname',
			),
		));
		
		// Entity path
		$this->add(array(
			'type' => 'Zend\Form\Element\Text',
			'name' => 'entity_path',
			'attributes' => array(
				'label' => 'Path to entity',
				'class' => 'span6',
			),
		));
		
		// Generate config
		$this->add(array(
			'type' => 'Zend\Form\Element\Checkbox',
			'name' => 'generate_config',
			'attributes' => array(
				'class' => 'checkbox',
				'value' => 1,
				'label' => 'Generate routes and navigation?',
				'checked' => 'checked',
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
		$inputFilter->add(new Input('generate_form'));
		$inputFilter->add(new Input('form_class'));
		$inputFilter->add(new Input('form_path'));
		$inputFilter->add(new Input('generate_controller'));
		$inputFilter->add(new Input('controller_class'));
		$inputFilter->add(new Input('controller_path'));
		$inputFilter->add(new Input('generate_entity'));
		$inputFilter->add(new Input('entity_class'));
		$inputFilter->add(new Input('entity_path'));
		$inputFilter->add(new Input('generate_config'));
		$this->setInputFilter($inputFilter);
				
	}
	
	/**
	 * 
	 * @param \Wiss\Entity\Model $model
	 */
	public function setModel(Model $model)
	{
		$this->model = $model;
		
		// Preset the classes en paths, based on the model
		$this->setData(array(
			'controller_class' => $this->buildClassName('Application\Controller\\'),
			'controller_path' => $this->buildControllerPath(),
			'form_class' => $this->buildClassName('Application\Form\\'),
			'form_path' => $this->buildFormPath(),
			'entity_class' => $this->buildClassName('Application\Entity\\'),
			'entity_path' => $this->buildEntityPath(),
		));
		
		// Uncheck the generation of the model, this model
		// is already generated
		$this->get('generate_entity')->setAttribute('checked', null);
		
	}
	
	/**
	 * 
	 * @return Model
	 */
	public function getModel()
	{
		return $this->model;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function buildControllerPath()
	{
        $folder = 'module/Application/src/Application/Controller';
        $filename = sprintf('%s/%sController.php', $folder, $this->buildClassName());
		return $filename;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function buildFormPath()
	{
        $folder = 'module/Application/src/Application/Form';
        $filename = sprintf('%s/%s.php', $folder, $this->buildClassName());
		return $filename;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function buildEntityPath()
	{
        $folder = 'module/Application/src/Application/Entity';
        $filename = sprintf('%s/%s.php', $folder, $this->buildClassName());
		return $filename;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function buildClassName($folderSeparator = '')
	{
		$entityClass = $this->getModel()->getEntityClass();
        $className = substr($entityClass, strrpos($entityClass, '\\') + 1);
		return $folderSeparator . $className;
	}
}
