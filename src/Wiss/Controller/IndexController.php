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
					
				// Update the config with the application uninstalled
				$this->writeToApplicationConfig(array(
					'application' => array(
						'installed' => false,
						'use_zones' => false,
					)
				));
                
                // Remove old generated configs
                @unlink('config/autoload/connection.global.php');
                @unlink('config/autoload/navigation.global.php');
                @unlink('config/autoload/routes.global.php');
                
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
				
				// Update the config with the application installed
				$this->writeToApplicationConfig(array(
					'application' => array(
						'installed' => true,
						'use_zones' => false,
					)
				));
		
				// Redirect to the actual install
				$this->redirect()->toRoute('wiss/install-models');
			}
		}
					
		
		return compact('form');
    }
	
	/**
	 * 
	 */
	public function installModelsAction()
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
          $em->getClassMetadata('Wiss\Entity\ModelElement'),
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
			
        // Insert module
        $module = new \Wiss\Entity\Module;
        $module->setName('Wiss');
        $module->setTitle('Wiss');
        $module->setLocked(true);
        $em->persist($module);
			
        // Insert module
        $module2 = new \Wiss\Entity\Module;
        $module2->setName('Application');
        $module2->setTitle('Application');
        $em->persist($module2);
        
        // Insert model module
        $model = new \Wiss\Entity\Model;
        $model->setTitle('Module');
        $model->setControllerClass('Wiss\Controller\Module');
        $model->setEntityClass('Wiss\Entity\Module');
        $model->setFormClass('Wiss\Form\Module');
        $model->setModule($module);
        $em->persist($model);        
        
        // Insert model navigation
        $model = new \Wiss\Entity\Model;
        $model->setTitle('Navigation');
        $model->setControllerClass('Wiss\Controller\Navigation');
        $model->setEntityClass('Wiss\Entity\Navigation');
        $model->setFormClass('Wiss\Form\Navigation');
        $model->setModule($module);
        $em->persist($model);         
		
		// Insert layout
		$layout = new \Wiss\Entity\Layout;
		$layout->setTitle('default');		
		$layout->setPath('layout/layout');
		$em->persist($layout);
		
		// Insert layout
		$layout2 = new \Wiss\Entity\Layout;
		$layout2->setTitle('wiss');	
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
						
		// Insert zone
		$zone3 = new \Wiss\Entity\Zone;
		$zone3->setTitle('Main content');
		$zone3->setName('content');
		$zone3->setLayout($layout2);
		$em->persist($zone3);
        
		// Insert zone
		$zone4 = new \Wiss\Entity\Zone;
		$zone4->setTitle('Sidebar content');
		$zone4->setName('sidebar');
		$zone4->setLayout($layout2);
		$em->persist($zone4);
        
		$layout2->setMainZone($zone3);
		$em->persist($layout2);
        
		
		// Insert navigation
		$navigation = new \Wiss\Entity\Navigation;
		$navigation->setLabel('Default');
		$em->persist($navigation);
		
		// Insert navigation
		$navigation2 = new \Wiss\Entity\Navigation;
		$navigation2->setLabel('Cms');
		$em->persist($navigation2);
				
		// Insert model page
		$model = new \Wiss\Entity\Model;
		$model->setTitle('Page');
		$model->setEntityClass('Wiss\Entity\Page');
		$model->setControllerClass('Wiss\Controller\PageController');
		$model->setFormClass('Wiss\Form\Page');
        $model->setModule($module);
		$em->persist($model);
        
        
		$em->flush();
		
		// Import and export the route and navigation config		
		$config = $this->getServiceLocator()->get('config');						
		$em->getRepository('Wiss\Entity\Route')->import($config);
		$em->getRepository('Wiss\Entity\Navigation')->import($config);
		$em->getRepository('Wiss\Entity\Route')->export();
		$em->getRepository('Wiss\Entity\Navigation')->export();		

                
        // Generate the file and folders for the application module
        $em->getRepository('Wiss\Entity\Module')->generate($module2);
                             
        
        $em->flush();
        
        
        // For the scanning of annotation, just scan the controller that are
        // registered with the service locator.
        $controllerLoader = $this->getServiceLocator()->get('controllerloader');
        
        // Scan and install the block annotations
        $blockRepo = $em->getRepository('Wiss\Entity\Block');
        $blocks = $blockRepo->scanControllers($controllerLoader);
        $blockRepo->saveBlocks($blocks);
        
        // Scan and install the content annotations
        $contentRepo = $em->getRepository('Wiss\Entity\Content');
        $content = $contentRepo->scanControllers($controllerLoader);
        $contentRepo->saveContent($content);
        
		// Update the config with the models installed
		$this->writeToApplicationConfig(array(
			'application' => array(
				'installed' => true,
				'use_zones' => true,
			)
		));        
        
        // Show a flash message
        $this->flashMessenger()->addMessage('The cms is installed sucessfully');
				
		// Redirect 
		$this->redirect()->toRoute('wiss/module');
		
		return false;
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
	
	/**
	 * 
	 * @param array $config
	 */
	public function writeToApplicationConfig(Array $config)
	{		
		$file = 'module/Application/config/module.config.php';	
		$original = \Zend\Config\Factory::fromFile($file);
		$configToWrite = $config + $original;
		$writer = new \Zend\Config\Writer\PhpArray();
		$writer->toFile($file, $configToWrite);
	}
}
