<?php

namespace Wiss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Wiss\Form\Mapping as Form;
use Wiss\Annotation\Overview;
use Zend\Form\Annotation;

/**
 * @ORM\Entity
 * @Overview(titleField="title")
 */
class Layout
{
	/**
	 * 
     * @Annotation\Exclude()
	 * @ORM\ID
	 * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
	 */
	protected $id;

	/**
     * @Annotation\Attributes({"type":"text"})
     * @Annotation\Options({"label":"Testlabel"})
	 * @Form\Text()
     * @ORM\Column
	 */
	protected $title;

	/**
	 * 
     * @ORM\Column
	 */
	protected $path;	

	/**
	 * @ORM\OneToMany(targetEntity="Zone", mappedBy="layout")
	 */
	protected $zones;

	/**
	 * @ORM\ManyToOne(targetEntity="Zone")
	 */
	protected $mainZone;

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
	
	public function getPath() {
		return $this->path;
	}

	public function setPath($path) {
		$this->path = $path;
	}
	
	public function getZones() {
		return $this->zones;
	}

	public function setZones($zones) {
		$this->zones = $zones;
	}
	
	public function getMainZone() {
		return $this->mainZone;
	}

	public function setMainZone(Zone $mainZone) {
		$this->mainZone = $mainZone;
	}



}
