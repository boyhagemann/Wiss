<?php

namespace Wiss\Entity;

use Wiss\Form\Mapping as Form;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 *  A test description
 * 
 * @ORM\Entity(repositoryClass="Wiss\EntityRepository\Block")
 * @ORM\Table(name="wiss_block")
 */
class Block
{
	/**
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
     * @ORM\Column
     * @Gedmo\Slug(fields={"controller", "action"}, unique=true)
	 */
	protected $slug;

	/**
	 * 
     * @ORM\Column
	 */
	protected $controller;

	/**
	 * 
     * @ORM\Column
	 */
	protected $action;

	/**
	 * 
     * @ORM\Column(nullable=true)
	 */
	protected $formClass;
    
	/**
	 * 
     * @ORM\Column(type="boolean")
	 */
	protected $available = false;    

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

	public function getController() {
		return $this->controller;
	}

	public function setController($controller) {
		$this->controller = $controller;
	}

	public function getAction() {
		return $this->action;
	}

	public function setAction($action) {
		$this->action = $action;
	}
    
    public function getFormClass() {
        return $this->formClass;
    }

    public function setFormClass($formClass) {
        $this->formClass = $formClass;
    }

    public function isAvailable() {
        return $this->available;
    }

    public function setAvailable($available) {
        $this->available = $available;
    }

    public function getSlug() {
        return $this->slug;
    }

    public function setSlug($slug) {
        $this->slug = $slug;
    }

}
