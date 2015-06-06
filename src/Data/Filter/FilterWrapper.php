<?php

namespace Framework\Data\Filter;

use Framework\DB\DB;

/**
 * Обёртка для фильтра объектов.
 * Подклассы реализуют выборку объектов различных типов из базы,
 * условия выборки задаются в переданном объекте-фильтре одного из подклассов Filter
 *
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
class FilterWrapper {
    
    /**
     * Фильтр, используемый для получения JOIN и WHERE частей запросов на выборку
     * @var Filter
     */
    protected $filter = null;
    
    /**
     * Имя главной таблицы
     * @var string
     */
    private $tableFull = '';
    
    /**
     * Сокращение имени главной таблицы
     * @var string
     */
    private $tableShort = '';
    
    /**
     * Список сортировок, попадающих в ORDER BY
     * @var array
     */
    protected $orderBy = array();
    
    /**
     * Кешировать ли запрос (дописывается SQL_NO_CACHE)
     * @var boolean
     */
    private $sqlCaching = true;
    
    /**
     * @var DB
     */
    private $db;
    
    /**
     * 
     * @param Filter    $filter
     * @param string    $tableFull
     * @param string    $tableShort
     */
    public function __construct(Filter $filter, $tableFull, $tableShort) {
        $this->filter = $filter;
        $this->tableShort = (string)$tableShort;
        $this->tableFull = (string)$tableFull;
        
        $this->db = $filter->getDB();
    }
    
    /**
     * Добавить в выборку сортировку по указанному столбцу
     * @param string    $column      имя столбца, требуется экранировать
     * @param string    $tableShort краткое имя таблицы в запросе, если передана пустая строка - используется главная таблица
     * @param boolean   $asc         сортировать по возрастанию или по убыванию (<b>TRUE</b> и <b>FALSE</b> соответственно)
     * @return static       изменённый объект
     */
    public function addOrderBy($column, $tableShort = '', $asc = true) {
        
        if ($tableShort == "" || $tableShort == $this->tableShort) {
            $ts = $this->tableShort;
        }
        else {
            $ts = $this->filter->getTableSmall($tableShort);
        }
        
        $this->orderBy[] = $ts . '.' . $column . ($asc ? ' ASC' : ' DESC');
        return $this;
    }
    
    /**
     * Добавить сортировку по произвольному параметру
     * @param string $rawColumn произвольный параметр сортировки (вставляется в запрос AS-IS)
     * @return FilterWrapper текущий объект
     */
    public function addOrderByRaw($rawColumn) {
        $this->orderBy[] = $rawColumn;
        return $this;
    }
    
    /**
     * Очистить список сортировок.
     * @return static
     */
    public function clearOrderBy() {
        $this->orderBy = array();
        return $this;
    }
    
    /**
     * Получить количество строк в таблице, удовлетворяющих условиям.
     * @return int количество найденных строк
     */
    public function getCount() {
        return (int)$this->db->fetchOne("SELECT " . (!$this->sqlCaching ? 'SQL_NO_CACHE ' : '') . " COUNT(*) FROM " . $this->tableFull . " " . $this->tableShort . $this->filter->getJoins($this->tableShort) . $this->filter->getWhere($this->tableShort));
    }
    
    /**
     * Получить краткое название главной таблицы с данными
	 * @final
     * @return string имя таблицы
     */
    final public function getTableShort() {
        return $this->tableShort;
    }
    
    /**
     * Получить массив данных по запросу с заданными условиями выборки и сортировки.
     * @param int $length размер выборки (0 - выбрать всё)
     * @param int $offset сколько строк пропустить (0 - выбирать с самого начала)
	 * @param string $keyColumn см. \DB::fetchAll
     * @param array массив данных из таблицы по заданным условиям.
     */
    public function fetchAll($length = 0, $offset = 0, $keyColumn = '') {
		return $this->fetchColumns($this->tableShort . ".*", $length, $offset, $keyColumn);
    }
    
	/**
	 * 
	 * @param string $select
	 * @param int $length
	 * @param int $offset
	 * @param string $keyColumn
	 * @return array
	 */
	public function fetchColumns($select, $length = 0, $offset = 0, $keyColumn = '') {
		return $this->db->fetchAll("SELECT " . (!$this->sqlCaching ? 'SQL_NO_CACHE ' : '') . $select . " FROM " . $this->tableFull . " " . $this->tableShort . $this->filter->getJoins($this->tableShort) . $this->filter->getWhere($this->tableShort) . (count($this->orderBy) > 0 ? " ORDER BY " . implode(', ', $this->orderBy) : "") . ($length > 0 ? ' LIMIT ' . $offset . "," . $length : ''), $keyColumn);
	}
		
	
    /**
     * Получить список значений поля по заданным условиям выборки.
     * @param string $columnName имя столбца с нужными данными
     * @param string $tableName имя таблицы (по умолчанию - главная таблица)
     * @param int $length размер выборки (0 - выбрать всё)
     * @param int $offset сколько строк пропустить (0 - выбирать с самого начала)
     * @return array см. DB::fetchSingle
     */
    public function fetchSingle($columnName, $tableName = "", $length = 0, $offset = 0) {
        $ts = ($tableName != "" ? $tableName : $this->tableShort) . "." . $columnName;
        return $this->db->fetchSingle("SELECT " . (!$this->sqlCaching ? 'SQL_NO_CACHE ' : '') . $ts . " FROM " . $this->tableFull . " " . $this->tableShort . $this->filter->getJoins($this->tableShort) . $this->filter->getWhere($this->tableShort) . (count($this->orderBy) > 0 ? " ORDER BY " . implode(', ', $this->orderBy) : "") . ($length > 0 ? ' LIMIT ' . $offset . "," . $length : ''));
    }
    
    /**
     * Установить заданный фильтр.
     * @param Filter $filter фильтр
     * @return FilterWrapper текущий объект
     */
    public function setFilter(Filter $filter) {
        $this->filter = $filter;
        return $this;
    }
    
    /**
     * Включить / выключить кэширование запросов (работает только отключение).
     * @param boolean $val <b>FALSE</b> если требуется дописывать SQL_NO_CACHE
     * @return FilterWrapper текущий объект
     */
    public function setSqlCaching($val) {
        $this->sqlCaching = (bool)$val;
        return $this;
    }
}
