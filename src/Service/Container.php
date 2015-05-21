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
	 * @var array хэшмэп с именами сервисов, которые установлены в контейнер через Container::set
	 */
	private $customServiceNamesMap;
	
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
		$this->customServices = array();
        
        if (!is_array($this->services)) {
            throw new InvalidArgumentException('Не удалось загрузить описание сервисов');
        }
        
        // :TODO: убрать
        if (self::$inst === null) {
            self::$inst = $this;
        }
    }
    
    /**
     * Очищает контейнер от всех объектов
     */
    public function clear() {
        $this->instances = array();
    }
	
	/**
	 * Сохранить имя сервиса, установленного в контейнер вручную
	 * 
	 * @param string $name имя сервиса
	 */
	private function saveCustomService($name) {
		$this->customServiceNamesMap[$name] = true;
	}
	
	/**
	 * @param string $name имя сервиса
	 * 
	 * @return bool существует ли в конфигурации сервис с указанным именем
	 */
	private function serviceExists($name) {
		return array_key_exists($name, $this->services);
	}
	
	/**
	 * @param string $name имя сервиса
	 * 
	 * @return Info объект с описанием сервиса
	 * 
	 * @throws RuntimeException если сервиса нет в конфигурации
	 */
	private function createServiceInfo($name) {
		if (!$this->serviceExists($name)) {
			throw new RuntimeException('Service [' . $name . '] not found');
		}

		return new Info($this->services[$name]);
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
        } elseif ($name == 'service_config') {
            return $this->Config;
        }
        
        if (!array_key_exists($name, $this->instances)) {
            $ServiceInfo = $this->createServiceInfo($name);
            
            $args = [];
            foreach ($ServiceInfo->getArguments() as $argName) {
                if (mb_substr($argName, 0, 1) == '@') {
                    $args[] = $this->get(ltrim($argName, '@'));
                } elseif (mb_substr($argName, 0, 1) == '\\') {
					// имя класса начинается со слэша
					$args[] = $argName;
				} else {
                    $args[] = $this->getParameter($argName);
                }
            }
            
            $className = $ServiceInfo->getClassName();
			$generatorClassName = $ServiceInfo->getGeneratorClassName();
            $method = $ServiceInfo->getMethod();
            
            if ($method == '') {
                $reflection = new ReflectionClass($className);
                $this->instances[$name] = $reflection->newInstanceArgs($args);
            } else {
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
     * @param string $parameterName имя параметра
     * 
     * @return bool существует ли указанный параметр конфигурации
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
     * Поместить в указанное поле переданный объект.
	 * Если в поле должен храниться какой-либо сервис - команда Container::clear очистит его от установленного значения.
     * 
     * @param string $name поле контейнера
     * @param mixed $value помещаемый объект
     * 
     * @return $this
     */
    public function set($name, $value) {
		if ($this->serviceExists($name)) {
			$this->saveCustomService($name);
		}
		
        $this->instances[$name] = $value;
        return $this;
    }
}
