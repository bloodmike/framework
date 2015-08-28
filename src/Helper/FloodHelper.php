<?php

namespace Framework\Helper;

use Framework\Memcache\MemcacheObj;

/**
 * Хэлпер для проверки повторяющихся действий с промежуточным хранением в MC
 *
 * @author mkoshkin
 */
class FloodHelper {
    /**
     * @var MemcacheObj
     */
    private $MemcacheObj;

    /**
     * @param MemcacheObj $MemcacheObj
     */
    public function __construct(MemcacheObj $MemcacheObj) {
        $this->MemcacheObj = $MemcacheObj;
    }

    /**
     * @param string $action название действия
     * @param string $limits строка с описанием лимитов
     * @param int $userId ID пользователя
     * @param bool $clear если нужно просто очистить лимиты, следует передать <b>TRUE</b>
     *
     * @return bool <b>TRUE</b> если все лимиты соблюдены, <b>FALSE</b> если хоть один не соблюден
     */
    public function checkUserFlood($action, $limits, $userId, $clear = false) {
        return $this->checkFloodInternal($action, $limits, 'flood_u_' . $userId, $clear);
    }

    /**
     * @param string $action название действия
     * @param string $limits строка с описанием ограничений
     * @param string|null $ip IP-адрес исполнителя действия (null - текущий IP пользователя)
     * @param bool $clear если нужно просто очистить лимиты, следует передать <b>TRUE</b>
     *
     * @return bool <b>TRUE</b> если все лимиты соблюдены, <b>FALSE</b> если хоть один не соблюден
     */
    public function checkIpFlood($action, $limits, $ip = null, $clear = false) {
        if ($ip === null) {
            $ip = $this->getRealIp();
        }
        return $this->checkFloodInternal($action, $limits, 'flood_ip_' . ip2long($ip), $clear);
    }

    /**
     * @param string $action название действия
     * @param string $limits лимиты
     * @param bool $clear для очистки лимитов следует передать <b>TRUE</b>
     *
     * @return bool
     */
    public function checkFlood($action, $limits, $clear = false) {
        return $this->checkFloodInternal($action, $limits, 'flood_p_', $clear);
    }

    /**
     * @param string $action
     * @param string $limits
     * @param string $key_prefix
     * @param bool $clear
     *
     * @return bool
     */
    private function checkFloodInternal($action, $limits, $key_prefix, $clear) {
        $result = true;
        $limitsArray = explode(',', $limits);
        foreach ($limitsArray as $limitPair) {
            $limitParts = $this->extractLimitParts($limitPair);

            $key = $key_prefix . '_' . $action . '_' . implode('_', $limitParts);
            if ($clear) {
                $this->MemcacheObj->delete($key);
            } elseif (!$this->processMC($key, $limitParts)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param string $limitPair строка с описанием ограничения
     *
     * @return int[] массив из двух чисел: допустимое количество действий, ограничение по времени (в секундах)
     */
    private function extractLimitParts($limitPair) {
        $limitParts = explode('/', $limitPair);
        if (!$limitParts[0]) {
            $limitParts[0] = 1;
        }
        if (count($limitParts) == 1) {
            $limitParts[] = 0;
        }
        return $limitParts;
    }

    /**
     * @param string $key ключ MC, где хранятся данные
     * @param int[] $limitParts пара чисел - ограничение по количеству действий на отрезок времени
     *
     * @return bool пройдена ли проверка ограничения успешно
     */
    private function processMC($key, $limitParts) {
        if ($this->MemcacheObj->add($key, 1, 0, $limitParts[1])) {
            if (!$limitParts[0]) {
                return false;
            }
        } elseif ($limitParts[0] > 1) {
            $value = $this->MemcacheObj->increment($key, 1);
            if ($value !== false && $value > $limitParts[0]) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * @deprecated
     *
     * @return string IP-адрес, с которого пришел запрос
     */
    private function getRealIp() {
        $ip = (string) ArrayHelper::get($_SERVER, 'HTTP_X_REAL_IP', '');
        if ($ip == '') {
            $ip = (string) ArrayHelper::get($_SERVER, 'HTTP_X_FORWARDED_FOR', '');
        }
        if ($ip == '') {
            $ip = (string) ArrayHelper::get($_SERVER, 'SERVER_ADDR', '');
        }
        return $ip;
    }
}
