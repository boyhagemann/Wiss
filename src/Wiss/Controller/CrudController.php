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
use Zend\Form\Form;
use Wiss\Entity\Model;
use Doctrine\ORM\EntityManager;

class CrudController extends AbstractActionController
{
    /**
     * This is the name of the model, used in this crud controller.
     *
     * @var string
     */
	protected $modelName;
	
    /**
     * The model itself
     *
     * @var Model
     */
    protected $model;
    
    /**
     * The form used for create and edit an entity
     *
     * @var Form
     */
    protected $form;
    
    /**
     *
     * @var Doctrine\ORM\EntityManager $entityManager
     */
	protected $entityManager;
    
    /**
     * The template used for the indexAction. It lists the entities
     *
     * @var string
     */
    protected $indexTemplate = 'wiss/crud/index';
	
    /**
     * The template used for the createAction. It show a form to
     * create the entity
     *
     * @var string
     */
    protected $createTemplate = 'wiss/crud/create';
    
    
    /**
     * The template used for the editAction. It show a form to
     * edit the entity properties
     *
     * @var string
     */
    protected $editTemplate = 'wiss/crud/edit';
    
    /**
     * The template used for the deleteAction. It shows a form to 
     * confirm the deletion 
     *
     * @var string
     */
    protected $deleteTemplate = 'wiss/crud/delete';
    
    /**
     * This action lists all the records for the model. It shows them in
     * a list. Each record can then be edited or deleted.
     *
     */
    public function indexAction()
	{
    	// Get the main model that holds the entity information
        $model = $this->getModel();
		$entities = $this->getEntities();
		
		// Get the main url for
		$routeName = $model->getNode()->getRoute()->getFullName();
				
		// Create the params for the view
		$params = compact('model', 'entities', 'routeName');
		
		// Create view model
		$viewModel = new ViewModel($params);
		$viewModel->setTemplate($this->indexTemplate);
		
		return $viewModel;
	}
		
	/**
	 *
	 * @return ViewModel 
	 */
	public function createAction()
	{
    	// Get the model, entity and form
        $model      = $this->getModel();
        $entity     = $this->createEntity();
        $form       = $this->getForm();
        $request    = $this->getRequest();
		
        // Check if data is posted
		if($request->isPost()) {
			
            // Set the post data to the form, in order to validate it
			$form->setData($request->getPost());
            
            // Now check if the form is valid
			if($form->isValid()) {
				
				// Save changes
                $em = $this->getEntityManager();
				$em->persist($form->getData());
				$em->flush();
				
				// Show a flash message
				$this->flashMessenger()->addMessage('Create new record succesfully');
				
				// Redirect
				$this->redirect()->toRoute('wiss/crud', array(
					'name' => $model->getSlug(),
				));
			}
		}
		
		// Create the params for the view
		$params = compact('model', 'form');
							
		// Create view model
		$viewModel = new ViewModel($params);
		$viewModel->setTemplate($this->createTemplate);
		
		return $viewModel;
	}
		
	/**
	 *
	 * @return ViewModel 
	 */
	public function editAction()
	{
        // Get the model, entity and form
        $model      = $this->getModel();
        $entity     = $this->createEntity();
        $form       = $this->getForm();
        $request    = $this->getRequest();
		
        // Check if data is posted
		if($request->isPost()) {
			
            // Set the post data to the form, in order to validate it
			$form->setData($request->getPost());
            
            // Now check if the form is valid
			if($form->isValid()) {
				
				// Save changes
                $em = $this->getEntityManager();
				$em->persist($form->getData());
				$em->flush();
				
				// Show a flash message
				$this->flashMessenger()->addMessage('Saved changes succesfully');
				
				// Redirect
				$this->redirect()->toRoute('wiss/crud', array(
					'name' => $model->getSlug(),
				));
			}
		}
		
		// Create the params for the view
		$params = compact('model', 'entity', 'form');
							
		// Create view model
		$viewModel = new ViewModel($params);
		$viewModel->setTemplate($this->editTemplate);
		
		return $viewModel;
	}
		
	/**
	 *
	 * @return ViewModel 
	 */
	public function deleteAction()
	{
        // Get the model, entity and form
        $model      = $this->getModel();
        $entity     = $this->getEntity();
        $form       = $this->getForm();
        $request    = $this->getRequest();
		
        // Check if data is posted
		if($request->isPost()) {
			
            // Set the post data to the form, in order to validate it
			$form->setData($request->getPost());
            
            // Now check if the form is valid
			if($form->isValid()) {
				
				// Save changes
                $em = $this->getEntityManager();
				$em->remove($form->getData());
				$em->flush();
				
				// Show a flash message
				$this->flashMessenger()->addMessage('Saved changes succesfully');
				
				// Redirect
				$this->redirect()->toRoute('wiss/crud', array(
					'name' => $model->getSlug(),
				));
			}
		}
		
		// Create the params for the view
		$params = compact('model', 'entity', 'form');
							
		// Create view model
		$viewModel = new ViewModel($params);
		$viewModel->setTemplate($this->deleteTemplate);
		
		return $viewModel;
	}
    
    /**
     * Get the model that holds all the information to manage an entity. It holds
     * information about the entity, form and this controller class itself.
     *
     * @return Model
     */
    public function getModel()
    {        
        if($this->model) {
            return $this->model;
        }
        
        // Get the model from the entity manager
    	$em = $this->getEntityManager();
		$repo = $em->getRepository('Wiss\Entity\Model');
		return $repo->findOneBy(array('slug' => $this->getModelName()));
    }
    
    /**
     *
     * @param Model $model
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }
    
    /**
     * Get a list of entities
     *
     * @return \Doctrine\ORM\Collection
     */
    public function getEntities()
    {        
    	// Get the entity that the model is using
        $qb = $this->getIndexQueryBuilder();
        return $qb->getQuery()->execute();
    }
    
    /**
     * This is the queryy that is used in the indexAction. It shows
     * the entites based on a query that is build here.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getIndexQueryBuilder()
    {
        $entityClass = $this->getModel()->getEntityClass();
        $repo = $this->getEntityManager()->getRepository($entityClass);
        return $repo->createQueryBuilder('i');
    }
    
    /**
     *
     * @return object A valid entity
     */
    public function getEntity()
    {      
        $model = $this->getModel();
    	$entityClass = $model->getEntityClass();
        $id = $this->getId();
        $em = $this->getEntityManager();        
		return $em->find($entityClass, $id);
    }
    
    /**
     *
     * @return object A valid entity
     */
    public function createEntity()
    {       
        $model = $this->getModel();
        $entityClass = $model->getEntityClass();
        return new $entityClass();
    }
    
    /**
     *
     * @return integer
     */
    public function getId()
    {
        return $this->params('id');
    }
    
    /**
     *
     * @return Form
     */
    public function getForm()
    {        
        if($this->form) {
            return $this->form;
        }
        
        // Build the new form
        $model = $this->getModel();
    	$class = $model->getFormClass();
        $form = new $class();
        
        // Bind the entity to the form if possible.
        // This automatically transfers data between the
        // form and the entity
        if($this->getId()) {
            $entity = $this->getEntity();
        	$form->bind($entity);
        }
        
		return new $class();
    }
    
    /**
     *
     * @return Form
     */ 
    public function setForm(Form $form)
    {
        $this->form = $form;
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
	
    /**
     *
     * @return string
     */
	public function getModelName() {
		return $this->modelName;
	}

    /**
     *
     * @param string $modelName
     */
	public function setModelName($modelName) {
		$this->modelName = $modelName;
	}


}
