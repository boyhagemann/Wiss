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

class IndexController extends AbstractActionController
{
	protected $entityManager;
		
    /**
     * 
     */
    public function installAction()
    {
		$form = new \Wiss\Form\Install();				
		$form->setAttribute('action', $this->url()->fromRoute('wiss/install'));
				
		if($this->getRequest()->isPost()) {
			$form->setData($this->getRequest()->getPost());
			
			if($form->isValid()) {
							
				$config = array(
					'doctrine' => array(
						'connection' => array(
							'orm_default' => array(
								'params' => $form->getData()
							),
						),
					)
				);
				
				// Write the config to disk in the config autoload folder
				$file = 'config/autoload/connection.global.php';	
				$writer = new \Zend\Config\Writer\PhpArray();
				$writer->toFile($file, $config);
				
				// Do the actual install
				$this->install();	
				
				// Update the config with the application installed
				$file = 'module/Application/config/module.config.php';	
				$config = \Zend\Config\Factory::fromFile($file);
				$config['application']['installed'] = true;
				$writer = new \Zend\Config\Writer\PhpArray();
				$writer->toFile($file, $config);
				
				// Redirect 
				$this->redirect()->toRoute('wiss/module/default', array(
					'action' => 'uninstalled'
				));
			}
		}
					
		
		return compact('form');
    }
	
	/**
	 *
	 * @return boolean 
	 */
	public function redirectToInstallAction()
	{
		// Redirect 
		$this->redirect()->toRoute('wiss/install');		
		
		return false;
	}
	
	/**
	 * 
	 */
	public function install()
	{
		$em = $this->getEntityManager();
        $classes = array(
          $em->getClassMetadata('Wiss\Entity\Page'),
          $em->getClassMetadata('Wiss\Entity\Route'),
          $em->getClassMetadata('Wiss\Entity\Layout'),
          $em->getClassMetadata('Wiss\Entity\Zone'),
          $em->getClassMetadata('Wiss\Entity\Content'),
          $em->getClassMetadata('Wiss\Entity\Block'),
          $em->getClassMetadata('Wiss\Entity\Module'),
          $em->getClassMetadata('Wiss\Entity\Model'),
          $em->getClassMetadata('Wiss\Entity\Navigation'),
        );
        
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
        
        try {
            $tool->dropSchema($classes);
        }
        catch(Exception $e) {
            print $e->getMessage();
        }
        $tool->createSchema($classes);
			
		
		// Insert layout
		$layout = new \Wiss\Entity\Layout;
		$layout->setTitle('default');		
		$layout->setPath('layout/layout');
		$em->persist($layout);
		
		// Insert layout
		$layout2 = new \Wiss\Entity\Layout;
		$layout2->setTitle('cms');	
		$layout2->setPath('wiss/layout/layout');
		$em->persist($layout2);
		
		// Insert zone
		$zone = new \Wiss\Entity\Zone;
		$zone->setTitle('Main content');
		$zone->setName('content');
		$zone->setLayout($layout);
		$em->persist($zone);
		
		// Insert zone
		$zone2 = new \Wiss\Entity\Zone;
		$zone2->setTitle('Sidebar content');
		$zone2->setName('sidebar');
		$zone2->setLayout($layout);
		$em->persist($zone2);
		
		$layout->setMainZone($zone);
		$em->persist($layout);
				
		
		// Insert navigation
		$navigation = new \Wiss\Entity\Navigation;
		$navigation->setLabel('Default');
		$em->persist($navigation);
		
		// Insert navigation
		$navigation2 = new \Wiss\Entity\Navigation;
		$navigation2->setLabel('Cms');
		$em->persist($navigation2);
		
		// Insert module
		$module = new \Wiss\Entity\Module;
		$module->setName('Application');
		$em->persist($module);
		
		// Insert module
		$module2 = new \Wiss\Entity\Module;
		$module2->setName('Wiss');
		$em->persist($module2);
				
		$em->flush();
	}
	
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
