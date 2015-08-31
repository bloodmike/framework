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
     * Режим проверки с увеличением значения
     */
    const MODE_INCREMENT = 0;

    /**
     * Режим проверки без увеличения значения
     */
    const MODE_CHECK = 1;

    /**
     * Режим очистки счетчиков
     */
    const MODE_CLEAR = 2;

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
     * @param int $uid уникальный ID (например, пользователя)
     * @param int $mode режим работы (см. константы FloodHelper::MODE_...)
     * @param int $increment число, на которое нужно увеличить счетчик
     *
     * @return bool <b>TRUE</b> если все лимиты соблюдены, <b>FALSE</b> если хоть один не соблюден
     */
    public function checkUidFlood($action, $limits, $uid, $mode = self::MODE_CHECK, $increment = 1) {
        return $this->checkFloodInternal($action, $limits, 'flood_u_' . $uid, $mode, $increment);
    }

    /**
     * @param string $action название действия
     * @param string $limits строка с описанием ограничений
     * @param string|null $ip IP-адрес исполнителя действия (null - текущий IP пользователя)
     * @param int $mode режим работы (см. константы FloodHelper::MODE_...)
     * @param int $increment число, на которое нужно увеличить счетчик
     *
     * @return bool <b>TRUE</b> если все лимиты соблюдены, <b>FALSE</b> если хоть один не соблюден
     */
    public function checkIpFlood($action, $limits, $ip = null, $mode = self::MODE_CHECK, $increment = 1) {
        if ($ip === null) {
            $ip = $this->getRealIp();
        }
        return $this->checkFloodInternal($action, $limits, 'flood_ip_' . ip2long($ip), $mode, $increment);
    }

    /**
     * @param string $action название действия
     * @param string $limits лимиты
     * @param int $mode режим работы (см. константы FloodHelper::MODE_...)
     * @param int $increment число, на которое нужно увеличить счетчик
     *
     * @return bool
     */
    public function checkFlood($action, $limits, $mode = self::MODE_CHECK, $increment = 1) {
        return $this->checkFloodInternal($action, $limits, 'flood_p_', $mode, $increment);
    }

    /**
     * @param string $action
     * @param string $limits
     * @param string $keyPrefix
     * @param int $mode
     * @param int $increment
     *
     * @return bool
     */
    protected function checkFloodInternal($action, $limits, $keyPrefix, $mode, $increment) {
        $result = true;
        $limitsArray = explode(',', $limits);
        foreach ($limitsArray as $limitPair) {
            $limitParts = $this->extractLimitParts($limitPair);

            $key = $keyPrefix . '_' . $action . '_' . implode('_', $limitParts);
            if ($mode == self::MODE_CLEAR) {
                $this->MemcacheObj->delete($key);
            } elseif ($mode == self::MODE_CHECK) {
                if (!$this->processMCSelect($key, $limitParts)) {
                    $result = false;
                }
            } elseif ($mode == self::MODE_INCREMENT) {
                if (!$this->processMCUpdate($key, $limitParts, $increment)) {
                    $result = false;
                }
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
     * @param $key
     * @param $limitParts
     *
     * @return bool
     */
    private function processMCSelect($key, $limitParts) {
        $value = $this->MemcacheObj->get($key);
        return ($value === false || $value <= $limitParts[0]);
    }

        /**
     * @param string $key ключ MC, где хранятся данные
     * @param int[] $limitParts пара чисел - ограничение по количеству действий на отрезок времени
     * @param int $increment число, на которое нужно увеличить счетчик
     *
     * @return bool пройдена ли проверка ограничения успешно
     */
    private function processMCUpdate($key, $limitParts, $increment) {

        if ($this->MemcacheObj->add($key, $increment, 0, $limitParts[1])) {
            if (!$limitParts[0]) {
                return false;
            }
        } elseif ($limitParts[0] > 1) {
            $value = $this->MemcacheObj->increment($key, $increment);
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
