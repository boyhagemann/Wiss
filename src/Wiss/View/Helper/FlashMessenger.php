<?php
// ./module/Application/src/Application/View/Helper/AbsoluteUrl.php
namespace Wiss\View\Helper;
 
use Zend\View\Helper\AbstractHelper;
use Zend\Mvc\Controller\Plugin\FlashMessenger as Messenger;
 
class FlashMessenger extends AbstractHelper
{
    protected $flashMessenger;
 
    public function __construct(Messenger $flashMessenger)
    {
        $this->flashMessenger = $flashMessenger;
    }
 
    public function __invoke()
    {
        return $this->flashMessenger;
    }
}