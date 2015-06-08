<?php

namespace Framework\Session;

use Framework\Helper\ArrayHelper;

/**
 * Обёртка вокруг сессии
 *
 * @author mkoshkin
 */
class Session implements SessionInterface {
    /**
     * @var array массив данных сессии
     */
    private $Session;
    
    public function __construct() {
        session_start();
        $this->Session =& $_SESSION;
    }
    
    public function clear() {
        $this->Session = [];
    }

    public function get($field, $default = null) {
        return ArrayHelper::get($this->Session, $field, $default);
    }

    public function has($field) {
        return ArrayHelper::has($this->Session, $field);
    }

    public function remove($field) {
        return ArrayHelper::remove($this->Session, $field);
    }

    public function set($field, $value) {
        
    }
}
