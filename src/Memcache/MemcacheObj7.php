<?php

namespace Framework\Memcache;

use Framework\Log\DataSourceLogger;
use Memcache;
use RuntimeException;

/**
 * Надстройка над Memcache для подключения класса к фреймворку в версии php >= 7.0
 *
 * @author mkoshkin
 */
class MemcacheObj7 {
    /**
     * @var DataSourceLogger
     */
    private DataSourceLogger $dataSourceLogger;

    /**
     * @var ?Memcache
     */
    private ?Memcache $memcache = null;

    /**
     * @var array
     */
    private array $config;

    private function getMemcache(): Memcache {
        if (!$this->memcache) {
            $host = $this->config['host'];
            $port = 11211;
            if (array_key_exists('port', $this->config)) {
                $port = $this->config['port'];
            }
            $this->memcache = new Memcache();
            if (!$this->memcache->connect($host, $port)) {
                throw new RuntimeException('Не удалось подключиться к memcached');
            }
        }
        return $this->memcache;
    }

    /**
     * @param array $config
     * @param DataSourceLogger $dataSourceLogger
     */
    public function __construct(array $config, DataSourceLogger $dataSourceLogger) {
        $this->config = $config;
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
        $result = $this->getMemcache()->increment($key, $value);

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
        $result = $this->getMemcache()->decrement($key, $value);

        $this->dataSourceLogger->add(
            'memcache',
            $microtimeFrom,
            'Decrement [' . $key . '] on [' . $value . ']',
            $result, 0, '');

        return $result;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $flag
     * @param int $expire
     *
     * @return bool
     */
    public function add($key, $value, $flag = 0, $expire = 0) {
        $microtimeFrom = microtime(true);
        $result = $this->getMemcache()->add($key, $value, $flag, $expire);
        $this->dataSourceLogger->add(
            'memcache',
            $microtimeFrom,
            'Add to [' . $key . '] value [' . $value . ']' . (($expire > 0) ? ' for [' . $expire . '] seconds' : ''),
            $result, 0, '');

        return $result;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $flag
     * @param int $expire
     *
     * @return bool
     */
    public function set($key, $value, $flag = 0, $expire = 0) {
        $microtimeFrom = microtime(true);
        $result = $this->getMemcache()->set($key, $value, $flag, $expire);
        $this->dataSourceLogger->add(
            'memcache',
            $microtimeFrom,
            'Set [' . $key . '] to [' . $value . ']' . (($expire > 0) ? ' for [' . $expire . '] seconds' : ''),
            $result, 0, '');

        return $result;
    }

    /**
     * @param string|string[] $key
     * @param mixed $param1
     * @param mixed $param2
     *
     * @return mixed|array
     */
    public function get($key, &$param1 = null, &$param2 = null) {
        $microtimeFrom = microtime(true);
        $result = $this->getMemcache()->get($key, $param1, $param2);
        $this->dataSourceLogger->add(
            'memcache',
            $microtimeFrom,
            'Get [' . (is_array($key) ? implode(', ', $key) : $key) . ']',
            is_array($result) ? count($result) : 0, 0, '');

        return $result;
    }
}
