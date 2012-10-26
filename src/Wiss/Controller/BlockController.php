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

class BlockController extends AbstractActionController
{
	protected $entityManager;
		
    /**
     * 
     */
    public function indexAction()
    {								
		$blocks = $this->getEntityManager()->getRepository('Wiss\Entity\Block')->findBy(array(
            'available' => true
        ));
		
		return compact('blocks');
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
