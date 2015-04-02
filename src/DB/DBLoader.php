<?php

namespace Framework\DB;

use Framework\Data\DBObjectId;
use Framework\Data\DBStorable;

/**
 * Класс DBLoader
 *
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
class DBLoader {
    
    /**
     * @var DB
     */
    private $db;
    
    /**
     * @param DB $db
     */
    public function __construct(DB $db) {
        $this->db = $db;
    }
	
    /**
     * 
     * @param DBObjectId $obj
     * @param int $id
     * 
     * @return boolean
     */
    public function loadById(DBObjectId $obj, $id) {
        if ($this->db !== null && $id > 0) {
            $row = $this->db->fetchRow("SELECT * FROM `" . $obj->getTable() . "` WHERE id=" . $id . " LIMIT 1");
            if ($row !== null) {
                $obj->fetch($row);
                return true;
            }
        }
        return false;
    }
    
    /**
     * @param DBObjectId $object
     * @param int $id
     * 
     * @return DBObjectId
     */
    public function loadAndReturn(DBObjectId $object, $id) {
        if ($this->db !== null && $id > 0) {
            $row = $this->db->fetchRow("SELECT * FROM `" . $object->getTable() . "` WHERE id=" . $id . " LIMIT 1");
            if ($row !== null) {
                return $object->fetch($row);
            }
        }
        
        return null;
    }
    
    /**
     * 
     * @param DBStorable $obj
     * @param array $keys
     * @return boolean
     */
    public function loadByKeys(DBStorable $obj, array $keys) {
        if ($this->db !== null && count($keys) > 0) {
            $a = array();
            foreach ($keys as $k => $v) {
                $a[] = "`$k`='" . $this->db->escape_string($v) . "'";
            }
            
            $data = $this->db->fetchRow("SELECT * FROM `" . $obj->getTable() . "` WHERE " . implode(' AND ', $a) . " LIMIT 1");
            if ($data !== null) {
                $obj->fetch($data);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 
     * @param DBStorable $obj
     * @param array $keys
     * @return DBStorable|null
     */
    public function loadAndReturnByKeys(DBStorable $obj, array $keys) {
        if ($this->db !== null && count($keys) > 0) {
            $a = array();
            foreach ($keys as $k => $v) {
                $a[] = "`$k`='" . $this->db->escape_string($v) . "'";
            }
            
            $data = $this->db->fetchRow("SELECT * FROM `" . $obj->getTable() . "` WHERE " . implode(' AND ', $a) . " LIMIT 1");
            if ($data !== null) {
                return $obj->fetch($data);
            }
        }
        
        return null;
    }
    
	/**
	 * TODO: переместить в более подходящий класс
	 * @param DBStorable $object
	 * @param array $sourceRow
	 * @param string $classPrefix
	 * @return DBStorable
	 */
	public function fetchWithPrefix(DBStorable $object, array &$sourceRow, $classPrefix = '') {
		$vars = array_keys(get_object_vars($object));
		
		foreach ($vars as $varName) {
			if (array_key_exists($classPrefix . $varName, $sourceRow)) {
				$object->fetchKey($varName, $sourceRow[$classPrefix . $varName]);
			}
		}
		
		return $object;
	}
}
