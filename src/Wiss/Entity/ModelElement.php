<?php

namespace Wiss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="wiss_model_element")
 */
class ModelElement
{
	/**
	 * 
	 * @ORM\ID
	 * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
	 */
	protected $id;

	/**
	 * 
     * @ORM\Column
	 */
	protected $name;

	/**
	 * 
     * @ORM\Column
	 */
	protected $label;

	/**
	 * 
     * @ORM\Column
	 */
	protected $builderClass;
	
	/**
	 *
     * @ORM\Column(type="array")
	 */
	protected $configuration;
	
	/**
	 *
     * @ORM\ManyToOne(targetEntity="Model", inversedBy="elements")
	 */
	protected $model;
	
	/**
	 *
     * @ORM\Column(type="boolean")
	 */
	protected $actAsLabel;
	
	public function getId() 
	{
		return $this->id;
	}

	public function getName() 
	{
		return $this->name;
	}

	public function setName($name) 
	{
		$this->name = $name;
	}
	
	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
	}

	public function getBuilderClass() {
		return $this->builderClass;
	}

	public function setBuilderClass($builderClass) {
		$this->builderClass = $builderClass;
	}
	
	public function getConfiguration() 
	{
		return $this->configuration;
	}

	public function setConfiguration($configuration) 
	{
		$this->configuration = $configuration;
	}
	
	public function getModel() {
		return $this->model;
	}

	public function setModel(Model $model) {
		$this->model = $model;
	}

	public function getActAsLabel() {
		return $this->actAsLabel;
	}

	public function setActAsLabel($actAsLabel) {
		$this->actAsLabel = $actAsLabel;
	}


}
