<?php

namespace Wiss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Wiss\Form\Mapping as Form;

/**
 * @ORM\Entity
 * @ORM\Table(name="wiss_layout")
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
     * @ORM\Column
	 */
	protected $title;

	/**
     * @ORM\Column
     * @Gedmo\Slug(fields={"title"})
	 */
	protected $slug;

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

    public function getSlug() {
        return $this->slug;
    }

    public function setSlug($slug) {
        $this->slug = $slug;
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
