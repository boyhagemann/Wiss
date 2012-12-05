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
                    
                    // Only actions can be blocks
                    if(!preg_match('/([a-zA-Z0-9_]+)Action/', $method->getName())) {
                        continue;
                    }
                    
                    // Get the action from the method name
                    $action = substr($method->getName(), 0, -6);
                    
                    // Get the controller from the class name
                    $controller = substr($class->getName(), 0, -10); // strip the word 'Controller'
                    
                    if($annotation->getController()) {
                        $blockController = $annotation->getController();
                    }
                    else {
                        $blockController = $controller;
                    }
                    
                    $block = $em->getRepository('Wiss\Entity\Block')->findOneBy(array(
                        'controller' => $blockController,
                        'action' => $annotation->getAction(),
                    ));
                    
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
                                        
                    $content[] = $contentEntity;
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
        $content = array();
        foreach($controllerLoader->getCanonicalNames() as $name) {
            $controller = $controllerLoader->get($name);
            $content += $this->scanController($controller);
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
