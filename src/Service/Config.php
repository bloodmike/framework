<?php

namespace Framework\Service;

use InvalidArgumentException;

/**
 * Объект для извлечения данных конфигурации
 *
 * @author mkoshkin
 */
class Config {
    
    /**
     * @var array массив с данными конфигурации
     */
    private $config;
    
    /**
     * @param array $config
     */
    public function __construct(array $config) {
        $this->config = $config;
    }
    
    /**
     * @param string $parameter имя параметра; для получения части параметра-массива, нужно разделять имена полей точками
     * 
     * @return bool есть ли параметр с заданным именем в конфиге
     */
    public function has($parameter) {
        $parameterParts = explode('.', $parameter);
        $pointer =& $this->config;
        foreach ($parameterParts as $namePart) {
            if (!array_key_exists($namePart, $pointer)) {
                return false;
            }
            
            $pointer =& $pointer[$namePart];
        }
        return true;
    }
    
    /**
     * @param string $parameter имя параметра; для получения части параметра-массива, нужно разделять имена полей точками
     * 
     * @return mixed значение параметра с указанным именем
     */
    public function get($parameter) {
        $parameterParts = explode('.', $parameter);
        $pointer =& $this->config;
        foreach ($parameterParts as $namePart) {
            if (!array_key_exists($namePart, $pointer)) {
                throw new InvalidArgumentException('Parameter [' . $parameter . '] is undefined');
            }
            
            $pointer =& $pointer[$namePart];
        }
        return $pointer;
    }
}
