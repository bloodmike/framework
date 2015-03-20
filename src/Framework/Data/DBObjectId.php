<?php

namespace Framework\Data;

use Framework\DB\DB;
use Framework\Buffer\BufferElementInterface;
use Framework\Data\DBStorable;

/**
 * Базовый класс для объектов базы с id
 *
 * @abstract
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
abstract class DBObjectId extends DBStorable implements BufferElementInterface {
    
    /**
     * Идентификатор объекта
     * @var int
     */
    public $id = null;
    
    /**
     * После вставки объекта в случае успеха и при отсутствии у объекта id
     * производится его получение из авто-инкрементного счётчика таблицы
     * @param DB $db
     * @param boolean $dmlResult результат выполнения запроса
     */
    public function afterInsert(DB $db, $dmlResult) {
        if ($dmlResult === true && $this->id === null) {
            $this->id = $db->insert_id;
		}
    }
    
    /**
     * 
     * @return int
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * 
     * @return array
     */
    public function getKeyVars() {
        return array("id");
    }
    
    /**
     * 
     * @return array
     */
    public function getVars() {
        $fields = get_class_vars(get_called_class());
        unset($fields['id']);
        return array_keys($fields);
    }
    
    /**
     * 
     * @return array
     */
    public function getHashKey() {
        return $this->id;
    }
    
    /**
     * Извлечь идентификаторы переданных объектов и вернуть из списком
     * @static
     * @param   array $list список объектов DBObjectId
     * @return  array список идентификаторов 
     */
    public static function extractIds(array $list) {
        $ids = array();
        foreach ($list as $obj) {
            if ($obj instanceof DBObjectId) {
                $ids[] = $obj->getId();
            }
        }
        
        return $ids;
    }
}
