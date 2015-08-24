<?php

namespace Framework\Time;

/**
 * Класс для работы со временем.
 * Содержит фунционал, предназначенный для упорядочивания и тестирования методов, завязанных на время.
 *
 * @author Mikhail Koshkin <bloodmike.ru@gmail.com>
 */
abstract class Time {
	/**
	 * Сутки
	 */
	const TIME_DAY = 86400;
	
	/**
	 * Час
	 */
	const TIME_HOUR = 3600;

	/**
	 * Минута
	 */
	const TIME_MINUTE = 60;

	/**
	 * Неделя
	 */
	const TIME_WEEK = 604800;
	
	/**
	 * @var int|null "замороженное" время в миллисекундах или null, если время не заморожено
	 */
	private static $time = null;
	
	/**
	 * @param int $round если требуется округлять полученное время, нужно передать количество секунд на округление
	 *
	 * @return int unix-время
	 */
	public static function get($round = 0) {
		if (self::$time !== null) {
			$time = floor(self::$time / 1000);
		} else {
			$time = time();
		}

		if ($round > 1) {
			$time = $time - ($time % $round);
		}
		return $time;
	}
	
	/**
	 * "Заморозить" время. После заморозки все вызовы get возвращают одно и то же время
	 * 
	 * @param int|null $time установить конкретное unix-время в секундах (null - взять текущее)
	 * 
	 * @return int сохраненное unix-время
	 */
	public static function freeze($time = null) {
		if ($time === null) {
			self::$time = floor(microtime(true) * 1000);
		} else {
			self::$time = $time * 1000;
		}
		
		return self::$time;
	}
	
	/**
	 * "Разморозить" время. После разморозки вызовы get начинают возвращать актуальное время
	 * 
	 * @return int текущее unix-время
	 */
	public static function unfreeze() {
		self::$time = null;
		return time();
	}
}
