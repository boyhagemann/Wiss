<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Wiss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PageController extends AbstractActionController
{
	protected $entityManager;
	
	/**
	 *
	 * @return array 
	 */
    public function indexAction()
    {
		$pages = $this->getEntityManager()->getRepository('Wiss\Entity\Page')->findAll();
				
		return compact('pages');
    }
		
	/**
	 *
	 * @return array 
	 */
	public function propertiesAction()
	{
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Page');
		$page = $repo->find($this->params('id'));
					
		return compact('page');
	}
		
	/**
	 *
	 * @return array 
	 */
	public function contentAction()
	{
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Page');
		$page = $repo->find($this->params('id'));
		
		$zones = $page->getLayout()->getZones();
		$used = array();
		foreach($zones as $zone) {
			$used[$zone->getId()] = array();
		}
		
		$pageContent = $page->getContent();
		foreach($pageContent as $content) {
			$zoneId = $content->getZone()->getId();
			$used[$zoneId][] = $content;
		}
		
		return compact('page', 'zones', 'used');
	}
			
	/**
	 *
	 * @param \Doctrine\ORM\EntityManager $entityManager 
	 */
	public function setEntityManager(\Doctrine\ORM\EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	/**
	 *
	 * @return type 
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}
}
