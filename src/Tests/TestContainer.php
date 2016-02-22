<?php

namespace Framework\Tests;

use Framework\Service\Container;
use Framework\Service\Info;
use \InvalidArgumentException;
use \RuntimeException;

/**
 * Контейнер зависимостей для unit-тестов.
 * Обладает рядом дополнительных возможностей для удобства написания тестов.
 *
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
class TestContainer extends Container {
    /**
     * @var Container
     */
    private $Container;

    /**
     * @param Container $Container
     */
    public function __construct(Container $Container) {
        $this->Container = $Container;
        parent::__construct('', $Container->Config->getData(), $Container->services);
    }

    /**
     * @param object $Mock мок класса из контейнера
     *
     * @return $this
     *
     * @throws InvalidArgumentException если передан не объект или объект без родительского класса
     * @throws RuntimeException если передан мок класса, описания которого нет в контейнере зависимостей,
     */
    public function setMock($Mock) {
        if (!is_object($Mock)) {
            throw new InvalidArgumentException('Object must be given');
        }
        $parentClass = get_parent_class($Mock);
        if ($parentClass === false) {
            throw new InvalidArgumentException('Unable get parent class of mock');
        }

        $parentClass = '\\' . ltrim($parentClass, '\\');
        foreach ($this->services as $serviceName => $serviceData) {
            $Info = new Info($serviceData);
            if ($Info->getClassName() == $parentClass) {
                return $this->set($serviceName, $Mock);
            }
        }

        throw new RuntimeException('Unable get service name for mock');
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function set($name, $value) {
        return $this->Container->set($name, $value);
    }

    /**
     * @param string $name
     * 
     * @return mixed
     */
    public function get($name) {
        return $this->Container->get($name);
    }

    /**
     * @inheritdoc
     */
    public function clear() {
        $this->Container->clear();
    }
}