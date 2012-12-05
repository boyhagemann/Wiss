<?php

namespace Wiss\EntityRepository;

use Doctrine\ORM\Mapping as ORM;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Annotation\Parser\DoctrineAnnotationParser;

/**
 * 
 */
class Block extends \Doctrine\ORM\EntityRepository
{		
    /**
     * 
     * @param \Zend\Mvc\Controller\AbstractActionController $controller
     * @return array
     */
    public function scanController(\Zend\Mvc\Controller\AbstractActionController $controller)
    {
        $blocks = array();
        $reflection = new \ReflectionObject($controller);
        
        // Setup a parser for the annotation
        $parser = new DoctrineAnnotationParser();
        $parser->registerAnnotation('Wiss\Annotation\Block');
        
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
                    if(!$annotation instanceof \Wiss\Annotation\Block) {
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
                    
                    // Return a new block entity, ready to be saved
                    $block = new \Wiss\Entity\Block;
                    $block->setTitle($annotation->getTitle());
                    $block->setController($controller);
                    $block->setAction($action);
                    $block->setAvailable(true);
                    $block->setSlug($this->createSlug($block));
                    
                    if($annotation->getForm()) {
                        $block->setFormClass($annotation->getForm());
                    }
                    
                    $blocks[$block->getSlug()] = $block;
                }
            }
        
        }
        
        return $blocks;
    }
    
    /**
     * 
     * @param \Zend\Mvc\Controller\ControllerManager $controllerLoader
     * @return array
     */
    public function scanControllers(\Zend\Mvc\Controller\ControllerManager $controllerLoader) 
    {
        $blocks = array();
        foreach($controllerLoader->getCanonicalNames() as $name) {
            $controller = $controllerLoader->get($name);
            $blocks += $this->scanController($controller);
        }
        
        return $blocks;
    }
    
    /**
     * 
     * @param array $blocks
     */
    public function saveBlocks(Array $blocks)
    {
        foreach($blocks as $slug => $block) {
            $this->saveBlock($slug, $block);
        }
    }
    
    /**
     * 
     * @param string $slug
     * @param \Wiss\Entity\Block $block
     */
    public function saveBlock($slug, \Wiss\Entity\Block $block) 
    {        
        $em = $this->getEntityManager();        
        $existingBlock = $this->findOneBy(array(
            'slug' => $slug, 
        ));

        if($existingBlock) {
            $existingBlock->setAvailable(true);
            $em->persist($existingBlock);
            $em->flush();
        }
        else {
            $em->persist($block);
            $em->flush();
        }
    }
    
    /**
     * 
     * @param \Wiss\Entity\Block $block
     * @return string
     */
    public function createSlug(\Wiss\Entity\Block $block)
    {
        $filter = new \Zend\Filter\Word\SeparatorToDash('\\');
        $key = strtolower($filter->filter($block->getController() . '-' . $block->getAction()));
        return $key;
    }
}
