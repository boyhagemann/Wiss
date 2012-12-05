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
        $methods = $fileScanner->getClass(get_class($controller))->getMethods();
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
                                        
                    // Return a new block entity, ready to be saved
                    $block = new \Wiss\Entity\Block;
                    $block->setTitle($annotation->getTitle());
                    $block->setController(get_class($controller));
                    $block->setAction($action);
                    $blocks[] = $block;
                }
            }
        
        }
        
        return $blocks;
    }
}
