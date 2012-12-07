<?php

namespace Wiss\EntityRepository;

use Doctrine\ORM\Mapping as ORM;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Annotation\Parser\DoctrineAnnotationParser;

/**
 * 
 */
class Content extends \Doctrine\ORM\EntityRepository
{		
    /**
     * Get the page content already sorted by content position
     * 
     * @param \Wiss\Entity\Page $page
     * @param mixed $isGlobal
     * @return array
     */
    public function findByPage(\Wiss\Entity\Page $page, $isGlobal = true)
    {
        $qb = $this->createQueryBuilder('c');
        $qb ->where('c.page = :page')
            ->orWhere('c.global = :global')
            ->setParameter('page', $page->getId())
            ->setParameter('global', (bool) $isGlobal);
        $result = $qb->getQuery()->getResult();
        
        $sort = array();
        foreach($result as $content) {
            $sort[$content->getPosition()][] = $content;
        }
        
        // Reverse base on the position key
        ksort($sort);
        
        $sortedContent = array();
        foreach($sort as $items) {
            $sortedContent = array_merge($sortedContent, $items);
        }
        
        return $sortedContent;        
    }
    
    /**
     * 
     * @param \Zend\Mvc\Controller\AbstractActionController $controller
     * @return array
     */
    public function scanController(\Zend\Mvc\Controller\AbstractActionController $controller)
    {
        $em = $this->getEntityManager();
        
        $content = array();
        $reflection = new \ReflectionObject($controller);
        
        // Setup a parser for the annotation
        $parser = new DoctrineAnnotationParser();
        $parser->registerAnnotation('Wiss\Annotation\Content');
        
        // Get the annotation manager
        $annotationManager = new AnnotationManager();
        $annotationManager->attach($parser);
        
        $fileScanner = new \Zend\Code\Scanner\FileScanner($reflection->getFileName());        
        $class = $fileScanner->getClass(get_class($controller));
        $methods = $class->getMethods();
        foreach($methods as $method) {
            $annotationScanner = $method->getAnnotations($annotationManager);
            
            if($annotationScanner) {
                foreach($annotationScanner as $annotation) {
                    
                    // Check if the annotation is a valid block
                    if(!$annotation instanceof \Wiss\Annotation\Content) {
                        continue;
                    }
                    
                        
                    // Only actions can use content
                    if(!preg_match('/([a-zA-Z0-9_]+)Action/', $method->getName())) {
                        continue;
                    }
                    
                    // Get the action from the method name
                    $action = substr($method->getName(), 0, -6);                    
                    if($annotation->getAction()) {
                        $contentAction = $annotation->getAction();
                    }
                    else {
                        $contentAction = $action;
                    }
                    
                    // Get the controller from the class name
                    $controller = substr($class->getName(), 0, -10); // strip the word 'Controller'                    
                    if($annotation->getController()) {
                        $contentController = $annotation->getController();
                    }
                    else {
                        $contentController = $controller;
                    }
                    
                    // Find the block that must be used as content
                    $block = $em->getRepository('Wiss\Entity\Block')->findOneBy(array(
                        'controller' => $contentController,
                        'action' => $contentAction,
                    ));
                                        
                    // Find the page for the current controller and action
                    $qb = $this->createQueryBuilder('c');
                    $qb->join('c.block', 'b')
                       ->where('b.controller = :controller')
                       ->andWhere('b.action = :action')
                       ->setParameter('controller', $controller)
                       ->setParameter('action', $action);
                    $currentContent = $qb->getQuery()->getOneOrNullResult();
                                        
                    $page = $currentContent->getPage();                    
                                            
                    $zone = $em->getRepository('Wiss\Entity\Zone')->findOneBy(array(
                        'name' => $annotation->getZone(),
                        'layout' => $page->getLayout()->getId(),
                    ));
                    
                    // Return a new block entity, ready to be saved
                    $contentEntity = new \Wiss\Entity\Content;
                    $contentEntity->setBlock($block);
                    $contentEntity->setTitle($block->getTitle());
                    $contentEntity->setZone($zone);
                    $contentEntity->setPage($page);
                    $contentEntity->setPosition(0);
                    $contentEntity->setDefaults(array());
                    $contentEntity->setGlobal($annotation->isGlobal());
                                    
                    $key = $contentEntity->getKey();
                    $content[$key] = $contentEntity;
                }
            }
        
        }
        
        return $content;
    }
    
    /**
     * 
     * @param \Zend\Mvc\Controller\ControllerManager $controllerLoader
     * @return array
     */
    public function scanControllers(\Zend\Mvc\Controller\ControllerManager $controllerLoader) 
    {
        $controllers = array();
        foreach($controllerLoader->getCanonicalNames() as $key => $name) {
            $controllers[$name] = $name;
        }
                
        $content = array();
        foreach($controllers as $name) {
            $controller = $controllerLoader->get($name);
            $content = array_merge($content, $this->scanController($controller));
        }
        
        return $content;
    }
    
    /**
     * 
     * @param array $content
     */
    public function saveContent(Array $content)
    {
        foreach($content as $contentItem) {
            $this->saveContentItem($contentItem);
        }
    }
    
    /**
     * 
     * @param \Wiss\Entity\Content $content
     */
    public function saveContentItem(\Wiss\Entity\Content $content) 
    {        
        $em = $this->getEntityManager(); 
        $em->persist($content);
        $em->flush();
    }
}
