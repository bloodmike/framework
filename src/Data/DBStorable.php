<?php

namespace Framework\Data;

use Framework\DB\DB;

/**
 * Базовый класс для объектов базы
 *
 * @abstract
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
abstract class DBStorable {
    
    /**
     * 
     * @param mixed $data источник данных для заполнения объекта.
     */
    public function __construct($data = null) {
        if (is_array($data)) {
            $this->fetch($data);
        }
    }
    
    /**
     * Команда обновления объекта после его добавления в базу.
     * @param DB $db 
     * @param bool $dmlResult результат выполнения запроса на вставку
     */
    public function afterInsert(DB $db, $dmlResult) {
        
    }
    
    /**
     * Должна возвращать имя таблицы с данными объекта
     * @return string имя таблицы
     */
    abstract public function getTable();
    
    /**
     * Получить список ключевых полей
     * @return array список имен ключевых полей таблицы
     */
    abstract public function getKeyVars();
    
    /**
     * Получить поля объекта
     * @return array  список с именами полей объекта
     */
    public function getVars() {
        return array_keys(get_class_vars(get_called_class()));
    }
    
    /**
     * Заполнить объект данными из массива
     * @param array $data ассоциативный массив с данными
     * @return $this
     */
    public function fetch(array $data) {
        if (count($data) > 0) {
            foreach ($data as $k => $v) {
                if (property_exists($this, $k)){
                    $this->fetchKey($k, $v);
                }
            }
        }
        return $this;
    }
    
    /**
     * Заполнить указанное поле объекта переданными данными.
     * @param string $key поле объекта
     * @param string $value данные в виде строки, полученной при вызове serializeVar
     */
    public function fetchKey($key, $value) {
        if (is_bool($this->$key)) {
            $this->$key = ((int)$value == 1);
		}
		elseif (is_array($this->$key)) {
			$this->$key = json_decode($value, true);
			if (!is_array($this->$key)) {
				$this->$key = array();
			}
		}
        else {
            $this->$key = $value;
        }
    }
    
    /**
     * Сериализовать значение поля объекта для сохранения в базу
     * @param String $name имя поля
     * @param DB $db
     * @return String строка с сериализованными данными, должна правильно восстанавливаться методом fetchKey
     */
    public function serializeVar($name, DB $db) {
        if (is_array($this->$name)) {
            return "'" . $db->real_escape_string(json_encode($this->$name, JSON_UNESCAPED_UNICODE)) . "'";
        }
        elseif (is_bool($this->$name)) {
            return $this->$name ? 1 : 0;
        }
        elseif ($this->$name === null) {
            return 'NULL';
        }
        return "'" . $db->real_escape_string((string)$this->$name) . "'";
    }
}