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
	
	/**
	 *
	 * @return boolean 
	 */
	public function installAction()
	{
		$name = $this->params('name');
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
			
			// Import from config
			$em->getRepository('Wiss\Entity\Page')->import($config);
			$em->getRepository('Wiss\Entity\Navigation')->import($config);
		}
		
						
		// Show flash message
		$message = sprintf('Module %s is installed', $this->params('name'));
		$this->flashMessenger()->addMessage($message);
		
		// Redirect
		$this->redirect()->toRoute('module/export');
		
		return false;
	}
	
	/**
	 *
	 * @return boolean 
	 */
	public function exportAction()
	{		
		// Build the config
		$em = $this->getEntityManager();
		$em->getRepository('Wiss\Entity\Page')->export();
		$em->getRepository('Wiss\Entity\Navigation')->export();		
				
		$this->redirect()->toRoute('module');

		return false;
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
