<?php

namespace Framework\Data\Storage;

use Framework\Data\ClassMetadata;
use Framework\DB\DB;

/**
 * Хранилище объектов
 *
 * @author Mikhail Koshkin <bloodmike.ru@gmail.com>
 */
class DBStorage {
	
	/**
	 * @var string имя класса, объекты которого извлекаются хранилищем
	 */
	private $className;

	/**
	 * @var ClassMetadata метаданные класса
	 */
	private $ClassMetadata;

	/**
	 * @var DB
	 */
	protected $db;
	
	private function __construct($className, DB $db) {
		$this->className = $className;
		$this->db = $db;
	}
	
	/**
	 * @param string $className
	 * @param DB $db
	 * 
	 * @return self
	 */
	public static function createInstance($className, DB $db) {
		return new static($className, $db);
	}
	
	/**
	 * @return DB коннектор базы данных
	 */
	public function getDB() {
		return $this->db;
	}


	/**
	 * @return string имя класса, объекты которого обрабатываются хранилищем
	 */
	protected function getClassName() {
		return $this->className;
	}
	
	/**
	 * @return ClassMetadata метаданные класса
	 */
	protected function getClassMetadata() {
		if ($this->ClassMetadata === null) {
			$this->ClassMetadata = ClassMetadata::get($this->className);
		}
		
		return $this->ClassMetadata;
	}
	
	/**
	 * @param int $id
	 * 
	 * @return DBObjectId|null
	 */
	public function load($id) {
		if ($id > 0) {
			$row = $this->db->fetchRow('SELECT * FROM ' . $this->getClassMetadata()->getTable() . ' WHERE id=' . $id . ' LIMIT 1');
			if ($row !== null) {
				$className = $this->className;
				return new $className($row);
			}
		}
		return null;
	}
	
	/**
	 * @param array $properties
	 * 
	 * @return DBStorable|null
	 */
	public function loadOneBy(array $properties) {
		if (count($properties) > 0) {
			$a = array();
            foreach ($properties as $k => $v) {
                $a[] = "`$k`='" . $this->db->real_escape_string($v) . "'";
            }
            
            $data = $this->db->fetchRow("SELECT * FROM `" . $this->getClassMetadata()->getTable() . "` WHERE " . implode(' AND ', $a) . " LIMIT 1");
            if ($data !== null) {
				$className = $this->className;
                return new $className($data);
            }
		}
		return null;
	}
	
    /**
     * @param string $query sql-запрос
     * @param string $keyColumn столбец уникальных значений, по которым в массиве будут разложены объекты
     * 
     * @throws InvalidArgumentException если передан несуществующий класс
     * 
     * @return DBStorable[] массив объектов, собранных из данных запроса
     */
    public function loadFromQuery($query, $keyColumn = '') {
        $array = $this->db->fetchAll($query, $keyColumn);
		$className = $this->className;
        foreach ($array as $key => $row) {
            $array[$key] = new $className($row);
        }
        
        return $array;
    }
}
