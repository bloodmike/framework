<?php

namespace Framework\Service;

use InvalidArgumentException;

/**
 * Description of Info
 *
 * @author mkoshkin
 */
class Info {
    /**
     * 
     */
    const FIELD_CLASS = 'class';
    
    /**
     * 
     */
    const FIELD_ARGS = 'args';
    
    /**
     * @var string
     */
    private $className;
    
    /**
     * @var string[]
     */
    private $arguments;
    
    /**
     * @param array $info
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
        }
        else {
            $this->arguments = $info[self::FIELD_ARGS];
        }
    }
    
    /**
     * @return string
     */
    public function getClassName() {
        return $this->className;
    }
    
    /**
     * @return string[]
     */
    public function getArguments() {
        return $this->arguments;
    }
}
