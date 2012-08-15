<?php

namespace Wiss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity 
 */
class Layout
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
