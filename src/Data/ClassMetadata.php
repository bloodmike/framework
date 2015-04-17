<?php

namespace Framework\Data;

use InvalidArgumentException;

/**
 * Метаданные класса: поля, ключи, типы полей
 *
 * @author Mikhail Koshkin <bloodmike.ru@gmail.com>
 */
class ClassMetadata {
    
    /**
     * @var ClassMetadata[]
     */
    private static $instances = [];
    
    /**
     * @var string имя класса
     */
    private $className;
    
    /**
     * @param DBStorable $DBStorable объект класса
     */
    private $DBStorable;
    
    /**
     * @var string[] имена всех полей класса
     */
    private $fields;
    
    /**
     * @param string $className
     * @param DBStorable $DBStorable
     */
    private function __construct($className, DBStorable $DBStorable) {
        $this->className = $className;
        $this->fields = array_keys(get_object_vars($DBStorable));
        $this->DBStorable = $DBStorable;
    }
    
    /**
     * @param string $className
     * 
     * @throws InvalidArgumentException если передан несуществующий класс или класс, не являющийся потомком DBStorable
     * 
     * @return self
     */
    public static function get($className) {
        if (!array_key_exists($className, self::$instances)) {
            if (!class_exists($className)) {
                throw new InvalidArgumentException('Class [' . $className . '] not exists');
            }
            elseif (!is_subclass_of($className, DBStorable::class)) {
                throw new InvalidArgumentException('Class [' . $className . '] is not DBStorable');
            }
            
            self::$instances[$className] = new self($className, new $className());
        }
        
        return self::$instances[$className];
    }
    
    /**
     * @return string[] имена всех полей класса
     */
    public function getFields() {
        return $this->fields;
    }
	
	/**
	 * @return string имя таблицы, в которой хранятся объекты
	 */
	public function getTable() {
		return $this->DBStorable->getTable();
	}
}
