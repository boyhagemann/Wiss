<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Wiss\Form\ElementConfig;

use Zend\Form\Form;
use Zend\Form\Element;

class Text extends Form
{	    
    public function prepareElements()
    {                
        // Label
        $label = new Element('label');
        $label->setAttributes(array(
                'type' => 'text',
                'label' => 'Label'
        ));
        $this->add($label);
    }
}
