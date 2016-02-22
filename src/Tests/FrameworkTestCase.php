<?php

namespace Framework\Tests;

use Framework\Service\Container;
use Framework\Tests\Command\TestCommandExecutor;
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
    private $Container;

    /**
     * @var TestCommandExecutor
     */
    private $TestCommandExecutor;

    /**
     * @return Container
     */
    protected function getContainer() {
        if ($this->Container === null) {
            $this->Container = Container::$inst;//new Container('test');
        }
        
        return $this->Container;
    }

    /**
     * @return TestCommandExecutor
     */
    protected function getCommandExecutor() {
        if (!$this->TestCommandExecutor) {
            $this->TestCommandExecutor = new TestCommandExecutor($this->getContainer());
        }
        return $this->TestCommandExecutor;
    }
}
