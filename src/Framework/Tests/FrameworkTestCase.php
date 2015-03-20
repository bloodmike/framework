<?php

namespace Framework\Tests;

use Framework\Service\Container;
use PHPUnit_Framework_TestCase;

/**
 * Description of FrameworkTestCase
 *
 * @author mkoshkin
 */
class FrameworkTestCase extends PHPUnit_Framework_TestCase {
    /**
     * @var Container
     */
    private $container;
    
    /**
     * @return Container
     */
    protected function getContainer() {
        if ($this->container === null) {
            $this->container = Container::$inst;//new Container('test');
        }
        
        return $this->container;
    }
}
