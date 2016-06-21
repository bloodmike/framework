<?php

namespace Framework\Service;

use InvalidArgumentException;

/**
 * Информация о сервисе в контейнере
 *
 * @author mkoshkin
 */
class Info {
    /**
     * Имя вызываемого при создании объекта класса
     */
    const FIELD_CLASS = 'class';
    
    /**
     * Имена параметров, передаваемых объекту при создании
     */
    const FIELD_ARGS = 'args';
    
	/**
	 * Поле с классом-генератором сервиса
	 */
	const FIELD_GENERATOR_CLASS = 'generator_class';
	
    /**
     * Если создание объекта производится через вызов одного из методов класса - имя этого метода
     */
    const FIELD_METHOD = 'method';
    
    /**
     * @var string 
     */
    private $className;
    
	/**
	 * @var string
	 */
	private $generatorClassName;
	
    /**
     * @var string
     */
    private $method;
    
    /**
     * @var string[]
     */
    private $arguments;
    
    /**
     * @param array $info
     *
     * @throws InvalidArgumentException
     */
    public function __construct($info) {
        if (!is_array($info)) {
            throw new InvalidArgumentException('Argument passed to ' . __CLASS__ . ' is not array');
        }
        
        if (!array_key_exists(self::FIELD_CLASS, $info) || !is_string($info[self::FIELD_CLASS])) {
            throw new InvalidArgumentException('Property [' . self::FIELD_CLASS . '] badly defined');
        }
        
        $this->className = $info[self::FIELD_CLASS];
        
        if (!array_key_exists(self::FIELD_ARGS, $info)) {
            throw new InvalidArgumentException('Property [' . self::FIELD_ARGS . '] not defined');
        }
        $this->arguments = array();
        if (!is_array($info[self::FIELD_ARGS])) {
            $this->arguments = [$info[self::FIELD_ARGS]];
        } else {
            $this->arguments = $info[self::FIELD_ARGS];
        }
        
		$this->generatorClassName = '';
		if (array_key_exists(self::FIELD_GENERATOR_CLASS, $info)) {
			$this->generatorClassName = $info[self::FIELD_GENERATOR_CLASS];
		}
		
        $this->method = '';
        if (array_key_exists(self::FIELD_METHOD, $info)) {
            $this->method = $info[self::FIELD_METHOD];
        }
    }
    
    /**
     * @return string
     */
    public function getClassName() {
        return $this->className;
    }
    
	/**
	 * @return string
	 */
	public function getGeneratorClassName() {
		return $this->generatorClassName;
	}
	
    /**
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }
    
    /**
     * @return string[]
     */
    public function getArguments() {
        return $this->arguments;
    }
}
