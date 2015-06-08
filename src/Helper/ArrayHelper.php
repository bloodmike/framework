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
	
	/**
	 * @param array $from массив
	 * @param string|string[] $field поле/список вложенных полей
	 * 
	 * @return array копия переданного массива без указанного элемента
	 */
	public function withoutField(array $from, $field) {
		if (!is_array($field)) {
			$field = [$field];
		}
		
		$copy = $from;
		$pointer =& $copy;
		$index = 0;
		
		foreach ($field as $fieldName) {
			if (!array_key_exists($fieldName, $pointer)) {
				// если элемент массива не найден - останавливаем
				break;
			} elseif ($index == count($field) - 1) {
				// если это последний элемент вложенного поля - удаляем
				unset($pointer[$fieldName]);
				break;
			}
			
			$pointer =& $pointer[$fieldName];
			$index++;
		}
		
		return $copy;
	}
}
