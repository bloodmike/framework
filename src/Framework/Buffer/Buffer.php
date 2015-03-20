<?php

namespace Framework\Buffer;

/**
 * Буфер объектов
 * Реализует логику сохранения и извлечения объектов по их хэш-ключу.
 * 
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
class Buffer {
    
    /**
     * 
     * @var array,BufferElement
     */
    private $map = array();
    
    /**
     * 
     * @param BufferElementInterface $element
     */
    public function add(BufferElementInterface $element) {
        $this->map[$element->getHashKey()] = $element;
    }
    
    /**
     * 
     * @param   string  $hashKey
     * @return  boolean
     */
    public function contains($hashKey) {
        return array_key_exists((string)$hashKey, $this->map);
    }
    
    /**
     * 
     * @param string $hashKey
     */
    public function drop($hashKey) {
        if ($this->contains($hashKey)) {
            unset($this->map[(string)$hashKey]);
        }
    }
    
    /**
     * 
     * @param   string  $hashKey
     * @return  null|BufferElementInterface
     */
    public function getByHash($hashKey) {
        if ($this->contains($hashKey)) {
            return $this->map[$hashKey];
        }
        
        return null;
    }
    
    /**
     * 
     * @param string $hashKey
     */
    public function setEmpty($hashKey) {
        $this->map[(string)$hashKey] = null;
    }
}
