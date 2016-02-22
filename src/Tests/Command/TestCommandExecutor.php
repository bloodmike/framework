<?php

namespace Framework\Tests\Command;

use Framework\Command\Command;
use Framework\Service\Container;
use RuntimeException;

/**
 * Исполнитель команд в юнит-тестах
 *
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
class TestCommandExecutor {
    /**
     * @var Container
     */
    private $Container;

    /**
     * @param Container $Container
     */
    public function __construct(Container $Container) {
        $this->Container = $Container;
    }

    /**
     * @param string $className класс вызываемой команды
     * @param array $args переменные контекста вызова
     *
     * @throws RuntimeException
     */
    public function execute($className, array $args = []) {
        $Command = Command::createInstance($className, $this->Container);
        $Command->setArgs($args);
        $Command->runBefore();
        $Command->run();
        $Command->runAfter();
    }
}