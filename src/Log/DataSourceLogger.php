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
    private $counts;

    /**
     * @var float[] суммарное время на выполнение операций
     */
    private $times;

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
        $this->verbose = $verbose;
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

    /**
     * @param string|null $source название источника событий (null - все источники)
     *
     * @return int количество зафиксированных событий
     */
    public function getCount($source = null) {
        if ($source === null) {
            return array_sum($this->counts);
        }
        if (array_key_exists($source, $this->counts)) {
            return $this->counts[$source];
        }
        return 0;
    }

    /**
     * @param string|null $source название источника событий (null - все источники)
     *
     * @return float суммарное время выполнения зафиксированных событий
     */
    public function getTime($source = null) {
        if ($source === null) {
            return array_sum($this->times);
        }
        if (array_key_exists($source, $this->times)) {
            return $this->times[$source];
        }
        return 0;
    }

    /**
     * @param string $source название источника событий
     *
     * @return float среднее время выполнения зафиксированных событий (0 - если событий не было)
     */
    public function getAvgTime($source) {
        if (array_key_exists($source, $this->counts)) {
            return $this->times[$source] / $this->counts[$source];
        }
        return 0;
    }
}
