<?php

namespace Framework\Command;

/**
 * Значения переданных команде параметров
 *
 * @author mkoshkin
 */
class ArgumentsData {
    /**
     * @var array данные
     */
    private $data;
    
    /**
     * Конструктор по умолчанию
     */
    public function __construct() {
        $this->data = [];
    }
    
    /**
     * Добавить значение параметра
     * 
     * @param string $name имя параметра
     * @param mixed $value значение
     * 
     * @return $this
     */
    public function add($name, $value) {
        if (!array_key_exists($name, $this->data)) {
            $this->data[$name] = $value;
        } elseif (!is_array($this->data[$name])) {
            $this->data[$name] = [$this->data[$name], $value];
        } else {
            $this->data[$name][] = $value;
        }
        
        return $this;
    }

    /**
     * Устанавливает значение параметра, если он уже есть в списке
     *
     * @param string $name имя параметра
     * @param mixed $value устанавливаемое значение
     *
     * @return $this
     */
    public function set($name, $value) {
        if (array_key_exists($name, $this->data)) {
            $this->data[$name] = $value;
        }
        return $this;
    }

    /**
     * Переносит данные из одного поля в другое, после чего удаляет первое поле.
     * Если первого поля нет - ничего не происходит.
     * Если второго поля нет - оно создается.
     * 
     * @param string $fromName поле, из которого будут переноситься данные
     * @param string $toName поле, в которое будут переноситься данные
     * 
     * @return $this
     */
    public function merge($fromName, $toName) {
        if (array_key_exists($fromName, $this->data)) {
            
            $fromValues = $this->data[$fromName];
            if (!is_array($fromValues)) {
                $fromValues = [$fromValues];
            }
            
            foreach ($fromValues as $fromValue) {
                $this->add($toName, $fromValue);
            }
            
            unset($this->data[$fromName]);
        }
        
        return $this;
    }
    
    /**
     * @param string $name имя параметра
     * @param mixed $defaultValue значение, возвращаемое при отсутствии параметра
     * 
     * @return mixed значения указанного параметра или значение по умолчанию, если параметра нет
     */
    public function get($name, $defaultValue = null) {
        if (!array_key_exists($name, $this->data)) {
            return $defaultValue;
        }
        
        return $this->data[$name];
    }
    
    /**
     * @return array все данные по значениям
     */
    public function getAll() {
        return $this->data;
    }
    
    /**
     * @return string[] имена всех параметров со значениями
     */
    public function getNames() {
        return array_keys($this->data);
    }
    
    /**
     * Удалить все значения указанного параметра
     * 
     * @param string $name имя параметра
     * 
     * @return $this
     */
    public function remove($name) {
        if (array_key_exists($name, $this->data)) {
            unset($this->data[$name]);
        }
        
        return $this;
    }
}
