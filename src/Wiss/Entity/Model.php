<?php

namespace Wiss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Wiss\EntityRepository\Model")
 * @ORM\Table(name="wiss_model")
 */
class Model
{
	/**
	 * 
	 * @ORM\ID
	 * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
	 */
	protected $id;

	/**
	 * 
     * @ORM\Column
	 */
	protected $title;
	
	/**
	 *
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=128, unique=false)
	 */
	protected $slug;
	
	/**
	 * 
     * @ORM\Column
	 */
	protected $entityClass;
	
	/**
	 * 
     * @ORM\Column(nullable=true)
	 */
	protected $formClass;
	
	/**
	 * 
     * @ORM\Column(nullable=true)
	 */
	protected $controllerClass;
	
	/**
	 *
	 * @ORM\OneToMany(targetEntity="ModelElement", mappedBy="model")
	 */
	protected $elements;	
	
	/**
	 *
	 * @ORM\ManyToOne(targetEntity="Module", inversedBy="models", fetch="EAGER")
	 */
	protected $module;	
	
	/**
	 *
	 * @ORM\ManyToOne(targetEntity="Navigation", fetch="EAGER")
	 */
	protected $node;
	
    /**
     * 
     */
    public function __construct()
    {
        $this->elements = new ArrayCollection();
    }

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getSlug() {
		return $this->slug;
	}

	public function setSlug($slug) {
		$this->slug = $slug;
	}

	public function getEntityClass() {
		return $this->entityClass;
	}

	public function setEntityClass($entityClass) {
		$this->entityClass = $entityClass;
	}

	public function getFormClass() {
		return $this->formClass;
	}

	public function setFormClass($formClass) {
		$this->formClass = $formClass;
	}

	public function getControllerClass() {
		return $this->controllerClass;
	}

	public function setControllerClass($controllerClass) {
		$this->controllerClass = $controllerClass;
	}

	public function getElements() {
		return $this->elements;
	}

	public function setElements($elements) {
		$this->elements = $elements;
	}
    
    public function getModule() {
        return $this->module;
    }

    public function setModule(Module $module) {
        $this->module = $module;
    }

	public function getNode() {
		return $this->node;
	}

	public function setNode(Navigation $node) {
		$this->node = $node;
	}
	
	/**
	 * 
	 * @param StdClass $entity
	 * @param string $separator
	 * @return string
	 */
	public function buildLabel($entity, $separator = ' - ')
	{
		$parts = array();
		foreach($this->getElements() as $element) {
			if($element->getActAsLabel()) {
				$getter = 'get' . ucfirst($element->getName());
				$parts[] = $entity->$getter();
			}
		}
		return implode($separator, $parts);
	}
}
