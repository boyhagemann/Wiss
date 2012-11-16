<?php

namespace Wiss\EntityRepository;

use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Gedmo\Sluggable\Util\Urlizer;
use Wiss\Form\ModelExport as ExportForm;

use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * 
 */
class Model extends \Doctrine\ORM\EntityRepository
{		
    /**
     *
     * @param array $data 
     * @return Model
     */
    public function createFromArray(Array $data)
	{
        $em = $this->getEntityManager();
        // Create a new model
        $model = new \Wiss\Entity\Model;
        $model->setTitle($data['title']);
        $model->setEntityClass($data['entity_class']);
		
        // Save this new model
        $em->persist($model);
        $em->flush();

        return $model;
    }
    
    /**
     * 
     * @param \Wiss\Entity\Model $model
     */
    public function generate(\Wiss\Entity\Model $model)
    {
        $this->generateController($model);
        $this->generateEntity($model);
        $this->generateForm($model);
        $this->generateRoutes($model);
        $this->generateNavigation($model);
    }
    
    /**
     *
     * @param \Wiss\Entity\Model $model 
     */
    public function generateRoutes(\Wiss\Entity\Model $model) 
	{
        // Build the config, starting from router.routes
        $config['router']['routes']['wiss']['child_routes']['model']['child_routes'] = array(
            $model->getSlug() => array(
                'type' => 'Literal',
                'may_terminate' => true,
                'options' => array(
                    'layout' => 'cms',
                    'route' => '/' . $model->getSlug(),
                    'defaults' => array(
                        '__NAMESPACE__' => '',
                        'controller' => $model->getControllerClass(),
                        'action' => 'index',
                        'slug' => $model->getSlug(),
                    ),
                ),
                'child_routes' => array(
                    'create' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'layout' => 'cms',
                            'route' => '/create',
                            'defaults' => array(
                                'action' => 'create',
                            ),
                        )
                    ),
                    'edit' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'layout' => 'cms',
                            'route' => '/edit/:id',
                            'defaults' => array(
                                'action' => 'edit',
                            ),
                            'constraints' => array(
                                'id' => '[0-9]+',
                            ),
                        )
                    ),
                    'delete' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'layout' => 'cms',
                            'route' => '/delete/:id',
                            'defaults' => array(
                                'action' => 'delete',
                            ),
                            'constraints' => array(
                                'id' => '[0-9]+',
                            ),
                        )
                    )
                )
            )
        );

        // Import the config thru the Page entity repository
        $em = $this->getEntityManager();
        $repo = $em->getRepository('Wiss\Entity\Route');
        $repo->import($config);		
		$repo->export();	
    }
	
    /**
     *
     * @param \Wiss\Entity\Model $model
     */
    public function generateNavigation(\Wiss\Entity\Model $model) 
	{
        $baseRoute = 'wiss/model/' . $model->getSlug();
        
        // Build the config, starting from navigation
        $config['navigation'] = array(
            $model->getSlug() => array(
                'label' => $model->getTitle(),
                'route' => $baseRoute,
				'pages' => array(
					'records' => array(
						'label' => 'Records',
						'route' => $baseRoute,
                        'params' => array(
                            'slug' => $model->getSlug(),
                        ),
					),
					'properties' => array(
						'label' => 'Properties',
						'route' => 'wiss/model/properties',
                        'params' => array(
                            'slug' => $model->getSlug(),
                        ),
					),
					'elements' => array(
						'label' => 'Elements',
						'route' => 'wiss/model/elements',
                        'params' => array(
                            'slug' => $model->getSlug(),
                        ),
					),
				)
            )
        );

        // Import the config thru the Navigation entity repository
        $em = $this->getEntityManager();
        $repo = $em->getRepository('Wiss\Entity\Navigation');
        $repo->import($config);
		$repo->export();
		
		// Bind the navigation node to the model
		if(!$model->getNode()) {
			$node = $repo->findOneBy(array('name' => $model->getSlug()));
			$model->setNode($node);
			$em->persist($model);
			$em->flush();
		}
    }
	
	

    /**
     *
     * @param \Wiss\Entity\Model $model
     */
    public function generateController(\Wiss\Entity\Model $model) 
	{		
        $class = $model->getControllerClass();
        $module = $model->getModule()->getName();
        $filename = $this->buildControllerPath($module, $class);
        $namespace = substr($class, 0, strrpos($class, '\\'));

        // Create the folder if it does not exist
        if(!file_exists(dirname($filename))) {
            @mkdir(dirname($filename), 0777, true);
        }
        
        $fileData = array(
            'filename' => $filename,
            'namespace' => $namespace,
            'uses' => array(
                array('Wiss\Controller\CrudController', 'EntityController'),
            ),
            'class' => array(
                'name' => $class . 'Controller',
                'extendedclass' => 'EntityController',
                'properties' => array(
                    array('modelName', $model->getSlug(), PropertyGenerator::FLAG_PROTECTED),
                )
            ),
        );

        // Write the data to disk
        $generator = FileGenerator::fromArray($fileData);
        $generator->write();
    }
    
    /**
     * 
     * @param \Wiss\Entity\Model $model
     */
    public function generateEntity(\Wiss\Entity\Model $model)
    {
        $filter = new \Zend\Filter\Word\CamelCaseToUnderscore();
        $filter2 = new \Zend\Filter\Word\DashToUnderscore();
        
        $class = $model->getEntityClass();
        $module = $model->getModule()->getName();
        $filename = $this->buildEntityPath($module, $class);
                        
        // Create the folder if it does not exist
        if(!file_exists(dirname($filename))) {
            @mkdir(dirname($filename), 0777, true);
        }
        		
		// Start a new metadata class
		$info = new ClassMetadata($class);	
                
        // Build the right table name
        $tableName = strtolower($filter->filter($model->getModule()->getName()));
        $tableName .= '_' . $filter2->filter($model->getSlug());
        $info->setTableName($tableName);

		// Start a builder to add data to the metadata object
		$builder = new ClassMetadataBuilder($info);
		
		// Add the primary key
		$builder->createField('id', 'integer')->isPrimaryKey()
										   ->generatedValue()
										   ->build();
		
		// Add the model elements
		foreach($model->getElements() as $element) {
            
            // Only continue if the element exists
            if(!$element instanceof \Wiss\Entity\ModelElement) {
                continue;
            }
            
            // Add the element to the builder
			$builder->addField($element->getName(), 'string');
		}
        
        // Set the right folder for the entity
        $folder = sprintf('module/%s/src', $model->getModule()->getName());

		// Build the entity with the generator
		$generator = new EntityGenerator();
		$generator->setUpdateEntityIfExists(true);	// only update if class already exists
		$generator->setRegenerateEntityIfExists(true);	// this will overwrite the existing classes
		$generator->setGenerateStubMethods(true);
		$generator->setGenerateAnnotations(true);
		$generator->generate(array($info), $folder);
		
		// Export to the database       
		$classes[] = $info;
        $tool = new SchemaTool($this->getEntityManager());
		try {
			$tool->dropSchema($classes); // @todo Will remove all previous records, make it optional !!!
			$tool->createSchema($classes);
		}
		catch(\Exception $e) {
			print $e; exit;
		}
    }
		
    /**
     * 
     * @param \Wiss\Entity\Model $model
     */
    public function generateForm(\Wiss\Entity\Model $model) 
	{
        $class = $model->getFormClass();
        $module = $model->getModule()->getName();
        $namespace = substr($class, 0, strrpos($class, '\\'));
        $filename = $this->buildFormPath($module, $class);

        // Create the body for in the __construct method
        $body = sprintf('parent::__construct(\'%s\');', $class) . PHP_EOL . PHP_EOL;
        $body .= '$this->setHydrator(new ClassMethodsHydrator());' . PHP_EOL;
        $body .= '$this->setAttribute(\'class\', \'form-horizontal\');' . PHP_EOL . PHP_EOL;

        // Add the elements 
        foreach ($model->getElements() as $element) {
            
            // Only continue if the element exists
            if(!$element instanceof \Wiss\Entity\ModelElement) {
                continue;
            }
            
			$name = $element->getName();
			
            /** @todo Implement form element building */
        }

        // Create the submit method
        $body .= '// submit' . PHP_EOL;
        $body .= '$this->add(array(' . PHP_EOL;
        $body .= sprintf('  \'name\' => \'%s\',', 'submit') . PHP_EOL;
        $body .= sprintf('  \'type\' => \'%s\',', 'Zend\Form\Element\Submit') . PHP_EOL;
        $body .= '	\'attributes\' => array(' . PHP_EOL;
        $body .= sprintf('    \'value\' => \'%s\',', 'Save') . PHP_EOL;
        $body .= sprintf('    \'class\' => \'%s\',', 'btn btn-primary') . PHP_EOL;
        $body .= ')));' . PHP_EOL . PHP_EOL;


        // Create the folder if it does not exist
        if(!file_exists(dirname($filename))) {
            @mkdir(dirname($filename), 0777, true);
        }
					
        // Build the file holding the php class
        $fileData = array(
            'filename' => $filename,
            'namespace' => $namespace,
            'uses' => array(
                array('Zend\Form\Form'),
                array('Zend\StdLib\Hydrator\ClassMethods', 'ClassMethodsHydrator'),
            ),
            'class' => array(
                'name' => $class,
                'extendedclass' => 'Form',
                'methods' => array(
                    array(
                        'name' => '__construct',
                        'parameters' => array(),
                        'flags' => null,
                        'body' => $body,
                    )
                )
            ),
        );

        // Generate the file and save it to disk
        $generator = FileGenerator::fromArray($fileData);
        $generator->write();
    }
	
	/**
	 * 
	 * @param string $class
	 * @return string
	 */
	public function buildTitleFromClass($class)
	{
        // Get the title based on the class
        $title = explode('\\', $class);
        $title = end($title);
		return $title;
	}
	
	/**
	 * 
	 * @param string $entityClass
	 * @return Wiss\Entity\Model
	 */
	public function findOneByEntityClass($entityClass)
	{		
        // Find the model with this class
        return $this->findOneBy(array(
            'entityClass' => $entityClass,
		));
	}
	
	/**
	 * 
	 * @param string $slug
	 * @return Wiss\Entity\Model
	 */
	public function findOneBySlug($slug)
	{		
        // Find the model with this class
        return $this->findOneBy(array(
            'slug' => $slug
		));
	}
	
    /**
     * 
     * @param string $module
     * @param string $class
     * @return string
     */
    public function buildControllerPath($module, $class)
    {
        $file = str_replace('\\', '/', $class);
        $path = sprintf('module/%s/src/%sController.php', $module, $file);
		return $path;
    }
	
    /**
     * 
     * @param string $module
     * @param string $class
     * @return string
     */
    public function buildFormPath($module, $class)
    {
        $file = str_replace('\\', '/', $class);
        $path = sprintf('module/%s/src/%s.php', $module, $file);
		return $path;
    }
	
    /**
     * 
     * @param string $module
     * @param string $class
     * @return string
     */
    public function buildEntityPath($module, $class)
    {
        $file = str_replace('\\', '/', $class);
        $path = sprintf('module/%s/src/%s.php', $module, $file);
		return $path;
    }
}
