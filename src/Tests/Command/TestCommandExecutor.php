<?php

namespace Framework\Tests\Command;

use Framework\Command\Command;
use Framework\Tests\TestContainer;
use RuntimeException;

/**
 * Исполнитель команд в юнит-тестах
 *
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
class TestCommandExecutor {
    /**
     * @var TestContainer
     */
    private $TestContainer;

    /**
     * @param TestContainer $Container
     */
    public function __construct(TestContainer $Container) {
        $this->TestContainer = $Container;
    }

    /**
     * @param string $className класс вызываемой команды
     * @param array $args переменные контекста вызова
     *
     * @throws RuntimeException
     */
    public function execute($className, array $args = []) {
        $Command = Command::createInstance($className, $this->TestContainer);
        $Command->setArgs($args);
        $Command->runBefore();
        $Command->run();
        $Command->runAfter();
    }
}