<?php

namespace Framework\DB;

use Exception;
use Framework\Data\DBStorable;
use Framework\Log\DataSourceLogger;
use mysqli;
use mysqli_result;
/**
 * Обёртка вокруг mysqli для извечения данных из базы в удобной форме.
 * 
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
class DB extends mysqli {
	
    /**
     * Режимы вставки объекта в базу
     */
    const 
        MODE_INSERT         = 0, // через "INSERT"
        MODE_INSERT_IGNORE  = 1, // через "INSERT IGNORE"
        MODE_REPLACE        = 2; // через "REPLACE"
    
    /**
     * Режим эскейпа массива
     */
    const 
        ARR_ESCAPE          = 1, // только экранирование символов
        ARR_QUOTE_ESCAPE    = 2; // экранирование символов и обведение кавычками
        
    /**
     * @var DataSourceLogger
     */
    private $dataSourceLogger;
    
	/**
	 * @var boolean Логгировать ли запросы и результаты их выполнения
	 */
	public $logging = true;
    
    /**
     * @var string Последний запрос
     */
    public $lastQuery = '';
    
    /**
     * @var boolean Выбрасывать исключение в случае ошибки выполнения запроса
     */
    public $throwOnError = false;
    
    /**
     * @param array $config
     */
    public function __construct(array $config, DataSourceLogger $dataSourceLogger) {
        parent::mysqli($config['host'], $config['user'], $config['password'], $config['name']);
        $this->set_charset('utf8');
        
        $this->dataSourceLogger = $dataSourceLogger;
    }
    
    /**
     * Экранировать каждое поле массива указанным образом
     * 
     * @param array $sql исходный массив, экранируются только значения
     * @param int $commentMode режим экранирования (см. DB::ARR_...)
     * 
     * @return array итоговый массив с экранированными значениями
     */
    public function arr2sql(array $sql, $commentMode = self::ARR_QUOTE_ESCAPE) {
        
        if ($commentMode !=self::ARR_ESCAPE && $commentMode != self::ARR_QUOTE_ESCAPE) {
            $commentMode = self::ARR_QUOTE_ESCAPE;
		}
        
        $sqlResult = array();
        
        foreach ($sql as $k => $v) {
            $val = $this->escape_string($v);
            if ($commentMode == self::ARR_QUOTE_ESCAPE) {
                $val = "'" . $val . "'";
			}
            
            $sqlResult[$k] = $val;
        }
        
        return $sqlResult;
        
    }
    
    /**
     * Удалить из указанной таблицы строки по определенным условиям
     * @param string $table имя таблицы (в формате table или schema.table)
     * @param string $where условия выборки, подставляемые в WHERE
     * @param int $limit количество удаляемых строк (если не указано - LIMIT не прописывается)
     * @return boolean <b>TRUE</b> в случае успешного выполнения запроса, <b>FALSE</b> в случае ошибки
     */
    public function delete($table, $where = '', $limit = 0) {
        return $this->query("DELETE FROM " . $this->escapeTablename($table) . ($where != '' ? ' WHERE ' . $where : "") . ($limit > 0 ? ' LIMIT ' . $limit : ''));
    }
    
    /**
     * Экранирует символы строки, добавляемой в условие LIKE (? и %)
     * @param string $string
     * @return string экранированная строка
     */
    public function escapeLikeString($string) {
        return $this->real_escape_string(str_replace(array('?', '%'), array('\?', '\%'), $string));
    }
    
    /**
     * Обводит имя таблицы (или базы/таблицы) апострофами
     * @param string $tablename имя таблицы / базы и таблицы
     * @return string экранированное имя
     */
    public function escapeTablename($tablename) {
        return "`" . str_replace(".", "`.`", $tablename) . "`";
    }
    
    /**
     * Получить все данные запроса в виде списка массивов
     * @param   string          $query      запрос
     * @param   string          $keyColumn  имя поля таблицы для получения ключей (если нужно вместо списка вернуть ассоциативный массив с ключами из определенного столбца таблицы)
     * @return  array   список / ассоциативный массив с данными запроса 
     */
    public function fetchAll($query, $keyColumn = '') {
        $r = $this->select($query);
        $result = [];
        
        for ($i = 0; $i < $r->num_rows; $i++) {
            $row = $r->fetch_assoc();
            if ($keyColumn == '') {
                $result[] = $row;
			}
            else {
                $result[$row[$keyColumn]] = $row;
			}
        }
        
        return $result;
    }
    
    /**
     * Получить результат запроса в виде одного единственного значения.
     * @param   string          $query
     * @return  mixed значение в случае успеха, <b>null</b> если запрос не вернул результатов или содержит ошибку
     */
    public function fetchOne($query) {
        $r = $this->select($query);
        if ($r->num_rows > 0) {
			$x = $r->fetch_array(MYSQLI_NUM);
            return $x[0];
		}
        return null;
    }
    
    /**
     * Получить данные запроса в виде пар ключ-значение.
     * @param string $query текст sql-запроса
     * @return array ассоциативный массив: 
     *                  ключами будут значения из первого столбца полученной таблицы, 
     *                  значениями - из второго столбца.
     */
    public function fetchPairs($query) {
        $result = $this->select($query);
        $a = null;
        
        if ($result !== false) {
            $a = [];
            for ($i = 0; $i < $result->num_rows; $i++) {
                $row = $result->fetch_array(MYSQLI_NUM);
                if (count($row) > 1) {
                    $a[$row[0]] = $row[1];
				}
                else {
                    $a[$row[0]] = $row[0];
				}
            }
        }
        
        return $a;
    }
    
    /**
     * Получить первую строку данных по запросу
     * @param   string          $query запрос
     * @return  array   ассоциативный массив с данными из первой строки таблицы или <b>NULL</b> при отсутствии данных или ошибке запроса
     */
    public function fetchRow($query) {
        $r = $this->select($query);
        if ($r->num_rows > 0) {
            return $r->fetch_assoc();
		}
        
        return null;
    }
    
    /**
     * Получить данные запроса в виде списка значений
     * @param string $query запрос
     * @return array список значений, взятых из первого столбца итоговой таблицы
     */
    public function fetchSingle($query) {
        $r = $this->select($query);
        $a = [];

        for ($i = 0; $i < $r->num_rows; $i++) {
            $row = $r->fetch_array(MYSQLI_NUM);
            $a[] = $row[0];
        }
        
        return $a;
    }
    
    /**
     * Выполнить удаление объекта из базы
     * @param   DBStorable $obj объект
     * @return  boolean см. DB::query
     */
    public function objDelete(DBStorable $obj) {
        return $this->delete($obj->getTable(), $this->getKeyWhere($obj), 1);
    }
    
    /**
     * Выполнить добавление объекта в базу
     * @param   DBStorable $obj объект
     * @param   int         $mode режим добавления (см. DB::MODE_...)
     * @return  boolean см. DB::query
     */
    public function objInsert(DBStorable $obj, $mode = self::MODE_INSERT, $delayed = false) {
        $vars = $obj->getVars();
        $set = [];
        
        foreach ($vars as $var) {
            $set['`' . $var . '`'] = $obj->serializeVar($var, $this);
		}
        
        $r = $this->insert($obj->getTable(), $set, $mode, $delayed);
        $obj->afterInsert($this, $r);
        
        return $r;
    }
    
    /**
     * Выполнить добавление объекта в базу запросом типа INSERT ... ON DUPLICATE KEY UPDATE ...
     * @param DBStorable $obj объект
     * @return boolean см. DB::query
     */
    public function objInsertUpdate(DBStorable $obj) {
        
        $vars = $obj->getVars();
        $keys = $obj->getKeyVars();
        $values = [];
        $updValues = [];
        
        foreach ($vars as $var) {
            $values["`" . $var . "`"] = $obj->serializeVar($var, $this);
            if (!in_array($var, $keys)) {
                $updValues[] = "`" . $var . "`=" . $obj->serializeVar($var, $this);
			}
        }
        $query = "INSERT INTO " . $this->escapeTablename($obj->getTable()) . "(" . implode(',', array_keys($values)) . ") VALUES(" . implode(',', $values) . ") ON DUPLICATE KEY UPDATE " . implode(', ', $updValues);
        
        return $this->query($query);
    }
    
    /**
     * Обновить данные об объекте в базе
     * @param   DBStorable   $obj   объект
     * @param   array         $fields имена обновляемых полей
     * @return  boolean см. DB::update
     */
    public function objUpdate(DBStorable $obj, array $fields) {
        $set = [];
        foreach ($fields as $var) {
            $set['`' . $var . '`'] = $obj->serializeVar($var, $this);
        }
        
        return $this->update($obj->getTable(), $set, $this->getKeyWhere($obj), 1);
    }
    
    /**
     * Произвести запрос на вставку объекта в таблицу.
     * @param   string      $table имя таблицы
     * @param   array       $set массив добавляемых данных: в полях - имена столбцов, значения - в значениях
     * @param   int         $mode режим вставки (см. DB::MODE_...)
     * @param   boolean     $delayed <b>TRUE</b> если для INSERT-запросов нужно указать свойство DELAYED
     * @return  boolean     см. DB::query , <b>FALSE</b> если передан пустой массив $set
     */
    public function insert($table, array $set, $mode = self::MODE_INSERT, $delayed = false) {
        if (count($set) > 0) {
            return $this->query($this->getInsertFunction($mode, $delayed) . $this->escapeTablename($table) . " (" . implode(', ', array_keys($set)) . ") VALUES(" . implode(', ', $set) . ")");
        }
        
        return false;
    }
    
    /**
     * Изменить у указанного объекта значения числовых полей и сохранить изменения в базу.
     * @param   DBStorable  $object объект
     * @param   array       $fields массив изменений: в ключах - имена полей, в значениях - дельты, накладываемые на значения полей
     * @return  boolean     всегда <b>TRUE</b>
     */
    public function objIncrement(DBStorable $object, array $fields) {
        
        $vars = [];
        foreach ($fields as $field => $delta) {
            if (property_exists($object, $field) && is_numeric($object->$field) && $delta != 0) {
                $object->$field += $delta;
                $vars["`" . $field . "`"] = "`" . $field . "`" . ($delta > 0 ? '+' : '') . $delta;
            }
        }
        
        if (count($vars) > 0) {
            $this->update($object->getTable(), $vars, $this->getKeyWhere($object), 1);
        }
        
        return true;
    }
    
    /**
     * Получить условия для выборки заданного объекта из базы
     * @param   DBStorable $obj объект
     * @return  string строка для вставки в WHERE
     */
    private function getKeyWhere(DBStorable $obj) {
        $keys = $obj->getKeyVars();
        
        $and = [];
        foreach ($keys as $k) {
            $and[$k] = "`" . $k . "`=" . $obj->serializeVar($k, $this);
        }
        
        return implode(' AND ', $and);
    }
    
    /**
     * Получить нужную INSERT-часть запроса
     * @param int $mode режим вставки (см. DB::MODE_...)
     * @param boolean $delayed <b>TRUE</b> для INSERT DELAYED
     * @return string строка с командой вставки
     */
    private function getInsertFunction($mode, $delayed) {
        if ($mode == self::MODE_REPLACE) {
            return "REPLACE " . ($delayed ? "DELAYED " : "") . "INTO ";
		}
        
        return "INSERT " . ($delayed ? "DELAYED " : "") . ($mode == self::MODE_INSERT_IGNORE ? "IGNORE " : "") . "INTO ";
    }
    
    /**
     * Обёртка вокруг mysqli::query с логгированием.
     * @param   string                  $query запрос
     * @param   int                     $resultmode режим выполнения запроса
     * @return  mysqli_result|boolean
     */
    public function query($query, $resultmode = MYSQLI_STORE_RESULT) {
        $ts = microtime(true);
        $r = parent::query($query, $resultmode);
        $this->lastQuery = $query;
        
        //$delta = microtime(true) - $ts;
        
		if ($this->logging) {
            $this->dataSourceLogger->add(
                    'mysql',
                    $ts, 
                    $query, 
                    ($r instanceof mysqli_result) ? $r->num_rows : $this->affected_rows, 
                    $this->errno, 
                    $this->error);
			/*$this->logs[] = array(
				'query' => $query,
				'time' => $delta,
				'error' => $this->error,
				'errno' => $this->errno,
				'rows' => ($r instanceof mysqli_result) ? $r->num_rows : $this->affected_rows
			);*/
		}
        
        if ($r === false && $this->throwOnError) {
            throw new Exception($this->error);
		}
        
        return $r;
    }
    
    /**
     * 
     * @param   string          $query
     * @return  mysqli_result
     */
    public function select($query) {
        return $this->query($query);
    }
    
    /**
     * Обновить строки в указанной таблице
     * @param string        $table имя таблицы (в формате table или schema.table)
     * @param array         $set массив обновляемых полейв формате: "поле" => "SQL-функция нового значения"
     * @param string        $where условие выборки обновляемых строк (пустая строка для обновления всех строк)
     * @param int           $limit количество обновляемых строк (все строки если не указано)
     * @return boolean
     */
    public function update($table, array $set, $where = '', $limit = 0) {
        if (count($set) > 0) {
            $setA = [];
            foreach ($set as $k => $v) {
                $setA[] = $k . " = " . $v;
            }

            return $this->query("UPDATE " . $this->escapeTablename($table) . " SET " . implode(',', $setA) . ($where != "" ? ' WHERE ' . $where : "") . ($limit > 0 ? " LIMIT " . $limit : ""));
        }
        
        return true;
    }
}
