<?php

namespace Wiss\View\Helper;
 
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
 
class PageUrl extends AbstractHelper implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
 
    /**
     * 
     * @param \Wiss\Entity\Page $page
     * @return string
     */
    public function __invoke(\Wiss\Entity\Page $page)
    {
        $name = $page->getRoute()->getFullName();
        $defaults = $page->getRoute()->getDefaults();
        $url = $this->getServiceLocator()->get('url');
        return $url($name, $defaults, array('force_canonical' => true));
    }
    
    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }
    
    public function getServiceLocator() {
        return $this->serviceLocator;
    }
}