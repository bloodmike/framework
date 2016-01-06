<?php

namespace Framework\Log;

/**
 * Логгер событий в источниках данных: записывает информацию об обращениях к источникам данных в системе
 *
 * @author mkoshkin
 */
class DataSourceLogger {
    /**
     * @var int[] количество обращений к разным источникам
     */
    public $counts;

    /**
     * @var float[] суммарное время на выполнение операций
     */
    public $times;

    /**
     * @var array
     */
    private $entries;

    /**
     * @var bool хранить ли все записи подряд (true) или только суммарные характеристики (false)
     */
    private $verbose;

    /**
     * По умолчанию
     *
     * @param bool $verbose хранить ли все записи подряд (true) или только суммарные характеристики (false)
     */
    public function __construct($verbose = true) {
        $this->entries = [];
        $this->counts = [];
        $this->times = [];
    }
    
    /**
     * @param string $source
     * @param float $microtimeFrom
     * @param string $content
     * @param int $affectedRecords
     * @param int $errorCode
     * @param string $errorText
     */
    public function add($source, $microtimeFrom, $content, $affectedRecords, $errorCode, $errorText) {

        $time = microtime(true) - $microtimeFrom;
        if ($this->verbose) {
            $this->entries[] = [
                'source' => $source,
                'content' => $content,
                'time' => $time,
                'error' => $errorText,
                'errno' => $errorCode,
                'records' => $affectedRecords
            ];
        }
        if (!array_key_exists($source, $this->times)) {
            $this->times[$source] = $time;
            $this->counts[$source] = 1;
        } else {
            $this->times[$source] += $time;
            $this->counts[$source]++;
        }
    }
    
    /**
     * @return array[]
     */
    public function getLogs() {
        return $this->entries;
    }

    /**
     * @param bool $val
     *
     * @return $this
     */
    public function setVerbose($val) {
        $this->verbose = (bool)$val;
        return $this;
    }
}
