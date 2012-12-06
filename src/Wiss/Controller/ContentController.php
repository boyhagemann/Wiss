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
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManager;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as EntityHydrator;

class ContentController extends AbstractActionController
{
    /**
     *
     * @var array 
     */
    protected $zoneViewModels = array();
    
    /**
     *
     * @var EntityManager
     */
	protected $entityManager;
			
    /**            
     * This action dispatches multiple other controller actions into
     * so called zones.
     *
     */
	public function routeAction()
	{			
        $em = $this->getEntityManager();
        
        // Get the route this found in the original routeMatch
		$route = $em->getRepository('Wiss\Entity\Route')->findOneBy(array(
            'fullName' => $this->params('route')
        ));		
        
        // Get the page that belongs to the found route
		$page = $route->getPage();
        
        // Set the right layout
        $this->layout($page->getLayout()->getPath());
			
        // Set the flash messages first, before dispatching any othe
        // controller actions. Otherwise the flashMessages are already shown
        // in the layout. This is because the layout is rendered after all
        // the actions are dispatched.
        $this->layout()->setVariable('flashMessages', $this->flashMessenger()->getMessages());
                        
        // Walk each zone and process the blocks
		foreach($page->getContent() as $content) {
			            
            // Get the block from this content part
            $block = $content->getBlock();

            // Inject all defaults that are set in the content block.
            // Each content block can have its own unique parameters to
            // control a block. This means a new controller action is 
            // dispatched for each block with custom parameters.
            $defaults = array(
                'controller' => $block->getController(),
                'action' => $block->getAction(),
            );
            $defaults += (array) $content->getDefaults();
            $defaults += $route->getDefaults();
            
            // Alter the current controller's routeMatch		
            $routeMatch	= $this->getEvent()->getRouteMatch();
            foreach($defaults as $key => $value) {
                $routeMatch->setParam($key, $value);
            }

            // Dispatch the new routeMatch. The routeMatch has the
            // right action set, so the only thing that remains is
            // to point to the right controller.
            $view = $this->forward()->dispatch($block->getController());
            
            // Only add the content to the view if a ViewModel is returned
            // If false is returned, it means that no view has to be 
            // rendered.
            if(!$view instanceof ViewModel) {
                continue;
            }
            
            // Return early if this is an ajax call
            if($view instanceof JsonModel) {
                return $view;
            }
            
            // Check if this viewModel is the last one to render in the chain.
            // A viewModel can have the 'setTerminate' to true. No other
            // view has to be rendered
            if($view->terminate()) {
                continue;
            }
            
            // The dispatched block can return a viewmodel. We want to 
            // capture this model to a unique key in our zone view model. 
            $view->setCaptureTo($content->getId());
            
            // Add the view to a zone view
            $zoneName = $content->getZone()->getName();
            $this->getZoneViewModel($zoneName)->addChild($view);
        }
		
        // If all blocks are added to the zones, add the zones
        // to the layout. We want to expose the zone viewmodel to a variable
        // key in the layout view script.
        foreach($this->zoneViewModels as $zoneName => $viewModel) {           
            $this->layout()->addChild($viewModel, $zoneName);
        }
        
        // No need to render the current action, all blocks are now
        // rendered directly to the layout.
		return false;
	}
    
    /**
     * 
     * @return array
     */
    public function propertiesAction()
    {        
        // Get the page content block
        $em = $this->getEntityManager();
        $content = $em->getRepository('Wiss\Entity\Content')->find($this->params('id'));
        
        // Get the form
        $form = $this->getForm();
        $form->bind($content);
        
        // Check if data is posted
        if($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            
            if($form->isValid()) {
                
                $em->persist($form->getData());
                $em->flush();
                
                // Show a flash message
                $this->flashMessenger()->addMessage('The block properties are updated!');
                
                //Redirect
                $this->redirect()->toRoute('wiss/page/content', array(
                    'id' => $content->getPage()->getId()
                ));
            }
        }
        
        return compact('content', 'form');
    }
    
    /**
     * 
     * @return array
     */
    public function configurationAction()
    {        
        // Get the page content block
        $em = $this->getEntityManager();
        $content = $em->getRepository('Wiss\Entity\Content')->find($this->params('id'));
        
        // Get the form for the configuration
        $form = $this->getBlockConfigurationForm($content);
        
        // Set the defaults based on the current configuration of the content block
        $form->setData($content->getDefaults());
        
        // Check if data is posted
        if($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            
            if($form->isValid()) {
                
                // Set the defaults from the form in the page content block
                $content->setDefaults($form->getData());
                $em->persist($content);
                $em->flush();
                
                // Show a flash message
                $this->flashMessenger()->addMessage('The block configuration is saved succesfully');
                
                //Redirect
                $this->redirect()->toRoute('wiss/page/content', array(
                    'id' => $content->getPage()->getId()
                ));
            }
        }
        
        return compact('content', 'form');
    }
    
    /**
     * 
     */
    public function deleteAction()
    {
        // Get the page content block
        $em = $this->getEntityManager();
        $content = $em->getRepository('Wiss\Entity\Content')->find($this->params('id'));
        $pageId = $content->getPage()->getId();
        
        $em->remove($content);
        $em->flush();

        // Show a flash message
        $this->flashMessenger()->addMessage('The block is removed from the page');

        //Redirect
        $this->redirect()->toRoute('wiss/page/content', array(
            'id' => $pageId
        ));
        
        return false;
    }
    
    /**
     * 
     * @return \Wiss\Form\Content
     */
    public function getForm()
    {        
        $sl = $this->getServiceLocator();
        $em = $this->getEntityManager();
        $hydrator = new EntityHydrator($this->getEntityManager());
        
        $form = $sl->get('Wiss\Form\Content'); 
		$form->setAttribute('class', 'form-horizontal');
		$form->setHydrator($hydrator);   
        $form->prepareElements();                
                
        return $form;
    }
    
    /**
     * 
     * @param \Wiss\Entity\Content $content
     * @return \Zend\Form\Form
     */
    public function getBlockConfigurationForm(\Wiss\Entity\Content $content)
    {        
        $sl = $this->getServiceLocator();
        $em = $this->getEntityManager();
        $hydrator = new EntityHydrator($this->getEntityManager());
        
        $formClass = $content->getBlock()->getFormClass();
        $form = $sl->get($formClass); 
		$form->setAttribute('class', 'form-horizontal');
		$form->setHydrator($hydrator);   
        $form->prepareElements();                
                
        return $form;
    }
    
    /**
     * 
     * @param string $name
     * @return \Zend\View\Model\ViewModel
     */
    public function getZoneViewModel($name)
    {
        // Check if the zone viewmodel already exists
        if(key_exists($name, $this->zoneViewModels)) {
            return $this->zoneViewModels[$name];
        }
        
        // Create a new viewmodel if it does not exist yet.
        $view = new ViewModel();
        
        // Give the viewmodel a special template. In this template the user
        // can alter the surrounding html to their needs
        $view->setTemplate('wiss/content/zone');
        
        // We want to pass the current zone name as a variable in the view,
        // just for styling purposes
        $view->setVariable('zone', $name);
        
        // Add the viewmodel to this controllers registry
        $this->zoneViewModels[$name] = $view;
        
        // Return the zone viewmodel
        return $view;
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
