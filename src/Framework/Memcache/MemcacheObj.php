<?php

namespace Framework\Memcache;

use Framework\Log\DataSourceLogger;
use Memcache;
use RuntimeException;

/**
 * Надстройка над Memache для подключения класса к фреймворку
 *
 * @author mkoshkin
 */
class MemcacheObj extends Memcache {
    
    /**
     * @var DataSourceLogger 
     */
    private $dataSourceLogger;
    
    /**
     * @param array $config
     */
    public function __construct($config, DataSourceLogger $dataSourceLogger) {
        $host = $config['host'];
        $port = 11211;
        if (array_key_exists('port', $config)) {
            $port = $config['port'];
        }
        
        if (!$this->connect($host, $port)) {
            throw new RuntimeException('Не удалось подключиться к memcached');
        }
        
        $this->dataSourceLogger = $dataSourceLogger;
    }
    
    /**
     * 
     * @param string $key
     * @param int $value
     * 
     * @return int|bool
     */
    public function increment($key, $value = 1) {
        $microtimeFrom = microtime(true);
        $result = parent::increment($key, $value);
        
        $this->dataSourceLogger->add(
                'memcache', 
                $microtimeFrom, 
                'Increment [' . $key . '] on [' . $value . ']', 
                $result, 0, '');
        
        return $result;
    }
    
    /**
     * @param string $key
     * @param int $value
     * 
     * @return int|bool
     */
    public function decrement($key, $value = 1) {
        $microtimeFrom = microtime(true);
        $result = parent::decrement($key, $value);
        
        $this->dataSourceLogger->add(
                'memcache', 
                $microtimeFrom, 
                'Decrement [' . $key . '] on [' . $value . ']', 
                $result, 0, '');
        
        return $result;
    }
    
    /**
     * 
     * @param string $key
     * @param mixed $value
     * @param int $flag
     * @param int $expire
     * 
     * @return bool
     */
    public function set($key, $value, $flag = 0, $expire = 0) {
        $microtimeFrom = microtime(true);
        $result = parent::set($key, $value, $flag, $expire);
        $this->dataSourceLogger->add(
                'memcache', 
                $microtimeFrom, 
                'Set [' . $key . '] to [' . $value . ']' . (($expire > 0) ? ' for [' . $expire . '] seconds' : ''), 
                $result, 0, '');
        
        return $result;
    }
    
    /**
     * @param string|string[] $key
     * 
     * @return mixed|array
     */
    public function get($key) {
        $microtimeFrom = microtime(true);
        $result = parent::get($key);
        $this->dataSourceLogger->add(
                'memcache', 
                $microtimeFrom, 
                'Get [' . (is_array($key) ? implode(', ', $key) : $key) . ']', 
                count($result), 0, '');
        
        return $result;
    }
}
