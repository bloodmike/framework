<?php

namespace Framework\Helper;

/**
 * Хэлпер работы с массивами
 *
 * @author Mikhail Koshkin <bloodmike.ru@gmail.com>
 */
abstract class ArrayHelper {
	/**
	 * @param array $from массив данных
	 * @param string[]|string $field поле/список вложенных полей
	 * @param mixed $default значение по умолчанию, выдаваемое при отсутствии поля
	 * 
	 * @return mixed значение из указанных полей массива или значение по умолчанию
	 */
	public static function get(array $from, $field, $default = null) {
		if (!is_array($field)) {
			$field = [$field];
		}
		
		$pointer =& $from;
		
		foreach ($field as $fieldName) {
			if (!is_array($pointer) || !array_key_exists($fieldName, $pointer)) {
				return $default;
			}
			
			$pointer =& $pointer[$fieldName];
		}
		
		return $pointer;
	}
}
