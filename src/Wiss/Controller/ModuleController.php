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

class ModuleController extends AbstractActionController
{
	protected $entityManager;
	
    public function indexAction()
    {
		$modules = $this->getEntityManager()->getRepository('Wiss\Entity\Module')->findAll();		
		return compact('modules');
    }
	
	public function uninstalledAction()
	{
		$config = $this->getServiceLocator()->get('applicationconfig');
		$paths = $config['module_listener_options']['module_paths'];
		$installed = $this->getInstalledModules();
		$modules = array();
		
		foreach($paths as $path) {
			$iterator = new \DirectoryIterator($path);
			foreach($iterator as $folder) {
				
				// Filter out only real folders
				if(!$folder->isDir() || $folder->isDot()) {
					continue;
				}
				
				// Filter out only ZF2 modules
				if(!file_exists($folder->getPathname() . '/Module.php')) {
					continue;
				}
				
				// Check if module is installed
				if(in_array($folder->getFilename(), $installed)) {
					continue;
				}
				
				// Module does not exist yet
				$modules[] = $folder->getFilename();
			}
		}
				
        return compact('modules');
	}
	
	public function installAction()
	{
		$this->install($this->params('name'));
						
		// Show flash message
		$message = sprintf('Module %s is installed', $this->params('name'));
		$this->flashMessenger()->addMessage($message);
		
		// Redirect
		$this->redirect()->toRoute('module/export');
		
		return false;
	}
	
	public function exportAction()
	{		
		// Build the config
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Navigation');
		$repo->exportToConfig();		
		
		// Build the config
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Page');
		$repo->exportRoutes();
				
		$this->redirect()->toRoute('module');

		return false;
	}
	
	public function install($name)
	{
		$em = $this->getEntityManager();
		$module = $em->getRepository('Wiss\Entity\Module')->findOneBy(array('name' => $name));
		
		if(!$module) {
			$module = new \Wiss\Entity\Module;
			$module->setName($this->params('name'));
			$em->persist($module);
			$em->flush();
		}				
		
		$moduleManager = $this->getServiceLocator()->get('modulemanager');
		$zfModule = $moduleManager->getModule($module->getName());
		
		// Get the module config
		$config = array();
		if(method_exists($zfModule, 'getConfig')) {
			$config = $zfModule->getConfig();
			$this->installRoutes($config);
			$this->installNavigation($config);
		}
	}
	
	/**
	 * 
	 * @param array $config
	 */
	public function installNavigation(Array $config)
	{
		if(!isset($config['navigation'])) {
			return;
		}
		
		$navigation = $config['navigation'];
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Navigation');
//		
		// Build the pages from the routes
		foreach($navigation as $name => $navigationData) {
			$repo->createNavigationFromArray($name, $navigationData, null, true);
		}
				
		// Save entities
		$this->getEntityManager()->flush();
	}
	
	/**
	 * 
	 * @param array $config
	 */
	public function installRoutes(Array $config)
	{
		if(!isset($config['router']['routes'])) {
			return;
		}
		
		$routes = $config['router']['routes'];
		$repo = $this->getEntityManager()->getRepository('Wiss\Entity\Page');
		
		// Build the pages from the routes
		foreach($routes as $name => $routeData) {
			$repo->createPageFromRoute($name, $routeData);
		}
				
		// Save entities
		$this->getEntityManager()->flush();
	}
	
	/**
	 *
	 * @return array 
	 */
	public function getInstalledModules()
	{
		$modules = $this->getEntityManager()->getRepository('Wiss\Entity\Module')->findAll();
		$installed = array();
		foreach($modules as $module) {
			$installed[] = $module->getName();
		}
		
		return $installed;
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
