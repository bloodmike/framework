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
     * @var TestContainer
     */
    private $Container;

    /**
     * @var TestCommandExecutor
     */
    private $TestCommandExecutor;

    /**
     * @return TestContainer
     */
    protected function getContainer() {
        if ($this->Container === null) {
            $this->Container = $this->initContainer();
        }
        
        return $this->Container;
    }

    /**
     * Создает тестовый контейнер. Выполняется один раз при первом запросе контейнера.
     *
     * @return TestContainer
     */
    protected function initContainer() {
        return new TestContainer(Container::$inst);
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
