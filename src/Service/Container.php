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
     * @var Config массив с конфигурацией инстанса
     */
    private $Config;
    
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
	 * @param string $path путь к файлам конфигурации
     */
    public function __construct($environment, $path = './../config/') {
        $this->Config = new Config(require_once($path . $environment . '.php'));
        $this->services = require_once('./services.php');
        $this->instances = array();
        
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
        else
        if ($name == 'service_config') {
            return $this->Config;
        }
        
        if (!array_key_exists($name, $this->instances)) {
            
            if (!array_key_exists($name, $this->services)) {
                throw new RuntimeException('Service [' . $name . '] not found');
            }
            
            $ServiceInfo = new Info($this->services[$name]);
            
            $args = [];
            foreach ($ServiceInfo->getArguments() as $argName) {
                if (mb_substr($argName, 0, 1) == '@') {
                    $args[] = $this->get(ltrim($argName, '@'));
                }
				elseif (mb_substr($argName, 0, 1) == '\\') {
					// имя класса начинается со слэша
					$args[] = $argName;
				}
                else {
                    $args[] = $this->getParameter($argName);
                }
            }
            
            $className = $ServiceInfo->getClassName();
			$generatorClassName = $ServiceInfo->getGeneratorClassName();
            $method = $ServiceInfo->getMethod();
            
            if ($method == '') {
                $reflection = new ReflectionClass($className);
                $this->instances[$name] = $reflection->newInstanceArgs($args);
            }
            else {
                $this->instances[$name] = call_user_func_array(
					[
						($generatorClassName != '' ? $generatorClassName : $className), 
						$method
					], 
					$args);
            }
        }
        
        return $this->instances[$name];
    }
    
    /**
     * @param string $parameterName
     * 
     * @return bool
     */
    public function hasParameter($parameterName) {
        return $this->Config->has($parameterName);
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
        return $this->Config->get($parameterName);
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
        $this->instances[$name] = $value;
        return $this;
    }
}
