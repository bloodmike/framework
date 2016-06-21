<?php

namespace Framework\Service;

/**
 * Класс для проверки данных сервиса из описания
 *
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
class InfoChecker {
    /**
     *
     */
    const ERROR_IN_INFO = 0;

    /**
     *
     */
    const ERROR_CLASS_NOT_FOUND = 1;

    /**
     *
     */
    const ERROR_GENERATOR_NOT_FOUND = 2;

    /**
     *
     */
    const ERROR_METHOD_ISNT_STATIC = 3;

    /**
     *
     */
    const ERROR_METHOD_ISNT_PUBLIC = 4;

    /**
     *
     */
    const ERROR_CONSTRUCT_ISNT_PUBLIC = 5;

    /**
     *
     */
    const ERROR_CLASS_IS_ABSTRACT = 6;

    /**
     *
     */
    const ERROR_NOT_ENOUGH_PARAMETERS = 7;

    /**
     *
     */
    const ERROR_TOO_MANY_PARAMETERS = 8;

    /**
     * @var Info|null проверяемая информация о сервисе
     */
    private $Info;

    /**
     * @var string имя сервиса
     */
    private $serviceName;

    /**
     * По умолчанию
     */
    public function __construct() {
        $this->Info = null;
        $this->serviceName = '';
    }

    /**
     * Проверяет корректность описания сервиса
     *
     * @param string $serviceName
     * @param array $serviceInfo
     *
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     *
     * @return $this
     */
    public function setService($serviceName, $serviceInfo) {
        $this->serviceName = $serviceName;
        $this->Info = new Info($serviceInfo);
        $className = $this->Info->getClassName();
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Class [' . $className . '] not found', self::ERROR_CLASS_NOT_FOUND);
        }
        $generatorClassName = $this->Info->getGeneratorClassName();
        if ($generatorClassName) {
            if (!class_exists($generatorClassName)) {
                throw new \InvalidArgumentException('Generator class [' . $className . '] not found', self::ERROR_GENERATOR_NOT_FOUND);
            }
        }
        $targetClass = $generatorClassName ? $generatorClassName : $className;
        $method = $this->Info->getMethod();
        if ($method) {
            $ReflectionMethod = new \ReflectionMethod($targetClass, $method);
            if (!$ReflectionMethod->isStatic()) {
                throw new \InvalidArgumentException('Method ' . $targetClass . '::' . $method . ' is not static', self::ERROR_METHOD_ISNT_STATIC);
            }
            if (!$ReflectionMethod->isPublic()) {
                throw new \InvalidArgumentException('Method ' . $targetClass . '::' . $method . ' is not public', self::ERROR_METHOD_ISNT_PUBLIC);
            }
        } else {
            $ReflectionMethod = new \ReflectionMethod($targetClass, '__construct');
            if (!$ReflectionMethod->isPublic()) {
                throw new \InvalidArgumentException($targetClass . '::__construct is not public', self::ERROR_CONSTRUCT_ISNT_PUBLIC);
            }
            $ReflectionClass = $ReflectionMethod->getDeclaringClass();
            if ($ReflectionClass->isAbstract()) {
                throw new \InvalidArgumentException('Class ' . $targetClass . ' is abstract', self::ERROR_CLASS_IS_ABSTRACT);
            }
        }

        $arguments = $this->Info->getArguments();
        $requiredParametersCount = $ReflectionMethod->getNumberOfRequiredParameters();
        if (count($arguments) < $requiredParametersCount) {
            throw new \InvalidArgumentException('Method ' . $targetClass . '::' . $ReflectionMethod->getName() . ' requires at least ' . $requiredParametersCount . ' ' . ($requiredParametersCount > 1 ? 'parameters' : 'parameter') . ', ' . count($arguments) . ' passed', self::ERROR_NOT_ENOUGH_PARAMETERS);
        }
        $parametersCount = $ReflectionMethod->getNumberOfParameters();
        if (count($arguments) > $parametersCount) {
            throw new \InvalidArgumentException('Method ' . $targetClass . '::' . $ReflectionMethod->getName() . ' requires at most ' . $parametersCount . ' ' . ($parametersCount > 1 ? 'parameters' : 'parameter') . ', ' . count($arguments) . ' passed', self::ERROR_TOO_MANY_PARAMETERS);
        }

        return $this;
    }
}