<?php

namespace Framework\Log;

/**
 * Description of DataSourceLogger
 *
 * @author mkoshkin
 */
class DataSourceLogger {
    
    /**
     * @var array
     */
    private $entries;
    
    /**
     * 
     */
    public function __construct() {
        $this->entries = [];
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
        $this->entries[] = [
            'source' => $source,
            'content' => $content,
            'time' => microtime(true) - $microtimeFrom,
            'error' => $errorText,
            'errno' => $errorCode,
            'records' => $affectedRecords
        ];
    }
    
    /**
     * @return array[]
     */
    public function getLogs() {
        return $this->entries;
    }
}
