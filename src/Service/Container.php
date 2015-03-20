<?php

namespace Framework\Service;

use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;

/**
 * Контейнер сервисов и настроек
 *
 * @author mkoshkin
 */
class Container {
    /**
     * @var array массив с конфигурацией инстанса
     */
    private $config;
    
    /**
     * @var array массив с конфигурацией сервисов, доступных через контейнер
     */
    private $services;
    
    /**
     * @var array массив с объектами сервисов, лежащими в контейнере
     */
    private $instances;
    
    /**
     * @var Container
     */
    public static $inst;
    
    /**
     * @param string $environment имя окружения
     */
    public function __construct($environment) {
        $this->config = require_once('./../config/' . $environment . '.php');
        $this->services = require_once('./services.php');
        $this->instances = [];
        
        if (!is_array($this->config)) {
            throw new InvalidArgumentException('Не удалось загрузить конфигурацию');
        }
        
        if (!is_array($this->services)) {
            throw new InvalidArgumentException('Не удалось загрузить описание сервисов');
        }
        
        // :TODO: убрать
        if (self::$inst === null) {
            self::$inst = $this;
        }
    }
    
    /**
     * @param string $name имя сервиса
     * 
     * @return mixed объект указанного сервиса
     * 
     * @throws RuntimeException если сервис не найден
     */
    public function get($name) {
        
        if ($name == 'service_container') {
            return $this;
        }
        
        if (!array_key_exists($name, $this->instances)) {
            
            if (!array_key_exists($name, $this->services)) {
                throw new RuntimeException('Service [' . $name . '] not found');
            }
            
            $serviceInfo = new Info($this->services[$name]);
            
            $reflection = new ReflectionClass($serviceInfo->getClassName());
            
            $args = [];
            foreach ($serviceInfo->getArguments() as $argName) {
                if (mb_substr($argName, 0, 1) == '@') {
                    $args[] = $this->get(ltrim($argName, '@'));
                }
                else {
                    $args[] = $this->getParameter($argName);
                }
            }
            
            $this->instances[$name] = $reflection->newInstanceArgs($args);
        }
        
        return $this->instances[$name];
    }
    
    /**
     * @param string $parameterName
     * 
     * @return bool
     */
    public function hasParameter($parameterName) {
        $parameterNameParts = explode('.', $parameterName);
        $pointer =& $this->config;
        foreach ($parameterNameParts as $namePart) {
            if (!array_key_exists($namePart, $pointer)) {
                return false;
            }
            
            $pointer =& $pointer[$namePart];
        }
        return true;
    }
    
    /**
     * @param string $parameterName имя параметра, 
     *                              для указания вложенных параметров нужно разделять имена точками
     * 
     * @return mixed значение указанного параметра
     * 
     * @throws InvalidArgumentException если указанного параметра нет в конфигурации
     */
    public function getParameter($parameterName) {
        $parameterNameParts = explode('.', $parameterName);
        $pointer =& $this->config;
        foreach ($parameterNameParts as $namePart) {
            if (!array_key_exists($namePart, $pointer)) {
                throw new InvalidArgumentException('Параметр [' . $parameterName . '] не найден');
            }
            
            $pointer =& $pointer[$namePart];
        }
        return $pointer;
    }
    
    /**
     * @param string $name имя объекта
     * 
     * @return bool инициализирован ли объект
     */
    public function has($name) {
        return array_key_exists($name, $this->instances);
    }
    
    /**
     * Поместить в указанное поле переданный объект
     * 
     * @param string $name поле контейнера
     * @param mixed $value помещаемый объект
     * 
     * @return $this
     * 
     * @throws InvalidArgumentException если указанное поле уже занято другим сервисом
     */
    public function set($name, $value) {
        if (array_key_exists($name, $this->instances)) {
            throw new InvalidArgumentException('Поле [' . $name . '] занято');
        }
        $this->instances[$name] = $value;
        return $this;
    }
}