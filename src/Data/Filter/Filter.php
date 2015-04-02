<?php

namespace Framework\Data\Filter;

use Framework\DB\DB;
use Framework\Service\Container;
use WM\ClassHelper;

/**
 * Фильтр объектов.
 * Подклассы реализуют логику формирования условий для sql-запросов выборки данных из базы.
 * 
 * @abstract
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
abstract class Filter {
    
    /**
     * Префикс к кратким названиям таблиц в JOIN'ах
     * @var string
     */
    public $tablePrefix = "";
    
    /**
     * @var DB
     */
    protected $db;
    
    public function __construct() {
        $this->db = ClassHelper::getDB(Container::$inst);
    }
    
    /**
     * Получить массив JOIN'ов запроса
     * @param   string          $tableShort краткое название главной таблицы
     * @return  array,\string
     */
    public function getJoinsArr($tableShort) {
        return array();
    }
    
    /**
     * Получить строку со всеми JOIN'ами запроса
     * @param string $tableShort краткое название главной таблицы
     * @return string строка с JOIN'ами
     */
    public final function getJoins($tableShort) {
        $j = $this->getJoinsArr($tableShort);
        return (count($j) > 0) ? " " . implode(' ', $j) : '';
    }
    
    /**
     * Получить массив всех условий выборки
     * @param   string          $tableShort краткое название главной таблицы
     * @return  array,\string массив условий для WHERE запроса
     */
    public function getWhereArr($tableShort) {
        return array();
    }
    
    /**
     * Получить строку WHERE запроса
     * @param   string $tableShort краткое название главной таблицы
     * @return  string строка с WHERE-частью запроса
     */
    public final function getWhere($tableShort) {
        $w = $this->getWhereArr($tableShort);
        return (count($w) > 0) ? " WHERE " . implode(' AND ', $w) : "";
    }
    
    /**
     * 
     * @param string $tableSmall
     * @return string
     */
    public final function getTableSmall($tableSmall) {
        return $this->tablePrefix . $tableSmall;
    }
    
}
