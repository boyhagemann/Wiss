<?php

namespace Wiss\Entity;

use Wiss\Annotation\Overview;
use Wiss\Form\Mapping as Form;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 *  A test description
 * 
 * @ORM\Entity 
 * @Overview(titleField="title")
 */
class Block
{
	/**
	 * @Annotation\Exclude()
	 * @ORM\ID
	 * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
	 */
	protected $id;

	/**
	 * 
	 * @Form\Text({"label"="Title"})
     * @ORM\Column
	 */
	protected $title;

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


}
