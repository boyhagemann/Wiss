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
use Doctrine\ORM\EntityManager;

class ModuleController extends AbstractActionController
{
	protected $entityManager;
	
    /**
     * Lists the installed modules
     *
     * @return array
     */
    public function indexAction()
    {
    	$modules = $this->getEntityManager()->getRepository('Wiss\Entity\Module')->findAll();	
		return compact('modules');
    }
	
    /**
     * Lists the uninstalled modules
     *
     * @return array
     */
	public function uninstalledAction()
	{        
		$config     = $this->getServiceLocator()->get('applicationconfig');
		$paths      = $config['module_listener_options']['module_paths'];
		$installed  = $this->getInstalledModulesAsArray();
		$modules    = array();
		
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
		$name   = $this->params('name');
		$em     = $this->getEntityManager();
		$module = $em->getRepository('Wiss\Entity\Module')->findOneBy(array('name' => $name));
		
		if(!$module) {
			$module = new \Wiss\Entity\Module;
			$module->setName($this->params('name'));
			$em->persist($module);
			$em->flush();
		}				
		
        // Get the Module object from the Module.php file in the module
		$moduleManager = $this->getServiceLocator()->get('modulemanager');
		$zfModule = $moduleManager->getModule($module->getName());
		
		// Import the route and navigation config
		$this->forward()->dispatch(__CLASS__, array(
			'action' => 'import',
		));
								
		// Show flash message
		$message = sprintf('Module %s is installed', $this->params('name'));
		$this->flashMessenger()->addMessage($message);
		
		// Export the route and navigation config
		$this->forward()->dispatch(__CLASS__, array(
			'action' => 'export',
		));

		// Redirect
		$this->redirect()->toRoute('wiss/module');
		
		return false;
	}
	
	public function importAction()
	{
		$config = $this->getServiceLocator()->get('config');
		$em     = $this->getEntityManager();
		
		$em->getRepository('Wiss\Entity\Route')->import($config);
		$em->getRepository('Wiss\Entity\Navigation')->import($config);
		
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
		$em->getRepository('Wiss\Entity\Route')->export();
		$em->getRepository('Wiss\Entity\Navigation')->export();		

		return false;
	}
	
	/**
	 *
	 * @return array 
	 */
	public function getInstalledModulesAsArray()
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
	 * @param EntityManager $entityManager 
	 */
	public function setEntityManager(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	/**
	 *
	 * @return EntityManager 
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}
}
