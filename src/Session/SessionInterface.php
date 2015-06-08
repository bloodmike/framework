<?php

namespace Framework\Session;

/**
 * Интерфейс объектов для работы с сессией
 * 
 * @author mkoshkin
 */
interface SessionInterface {
    /**
     * Очищает данные сессии
     * 
     * @return $this
     */
    public function clear();
    
    /**
     * @param string|string[] $field имя поля / список вложенных имён полей
     * @param mixed $default значение по умолчанию, возвращаемое при отсутствии поля в сессии
     * 
     * @return mixed значение поля в сессии, если оно есть, или значение по умолчанию, если поля нет
     */
    public function get($field, $default = null);
    
    /**
     * @param string|string[] $field имя поля / список вложенных имён полей
     * 
     * @return bool есть ли указанное поле в сессии
     */
    public function has($field);
    
    /**
     * Устанавливает указанное значение в указанное поле
     * 
     * @param string|string[] $field имя поля / список вложенных имён полей 
     * @param mixed $value устанавливаемое значение
     * 
     * @return $this
     */
    public function set($field, $value);
    
    /**
     * Удаляет указанное поле из сессии
     * 
     * @param string|string[] $field имя поля / список вложенных имён полей 
     * 
     * @return $this
     */
    public function remove($field);
}
