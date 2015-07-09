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
     * @param array $from массив данных
	 * @param string[]|string $field поле/список вложенных полей
     * 
     * @return bool есть ли указанное поле в массиве
     */
    public static function has(array $from, $field) {
        if (!is_array($field)) {
			$field = [$field];
		}
		
		$pointer =& $from;
		
		foreach ($field as $fieldName) {
			if (!is_array($pointer) || !array_key_exists($fieldName, $pointer)) {
				return false;
			}
			
			$pointer =& $pointer[$fieldName];
		}
		
        return true;
    }
	
    /**
     * Установить значение в указанное поле переданного массива.
     * Для вложенных полей массивы создаются автоматически, затирая существующие вложенные поля - не-массивы.
     * 
     * @param array& $to массив данных
	 * @param string[]|string $field поле/список вложенных полей
     * @param mixed $value
     */
    public static function set(array& $to, $field, $value) {
        if (!is_array($field)) {
			$field = [$field];
		}
		
		$pointer =& $to;
		$index = 0;
        
		foreach ($field as $fieldName) {
			if (!is_array($pointer) || !array_key_exists($fieldName, $pointer)) {
				$pointer[$fieldName] = [];
			}
			
            if ($index == count($field) - 1) {
                $pointer[$fieldName] = $value;
            } else {
                $pointer =& $pointer[$fieldName];
            }
		}
    }
    
    /**
     * Удаляет из переданного массива указанное поле
     * 
     * @param array& $from массив данных
     * @param string[]|string $field поле/список вложенных полей
     * 
     * @return bool удалено ли поле; если поля не было в массиве - возвращается false
     */
    public static function remove(array& $from, $field) {
        if (!is_array($field)) {
			$field = [$field];
		}
        
		$pointer =& $from;
		$index = 0;
        
		foreach ($field as $fieldName) {
            if (!is_array($pointer) || !array_key_exists($fieldName, $pointer)) {
				return false;
			}
            
			if ($index == count($field) - 1) {
                unset($pointer[$fieldName]);
            } else {
                $pointer =& $pointer[$fieldName];
            }
            
            $index++;
		}
        
        return true;
    }
    
	/**
	 * @param array $from массив
	 * @param string|string[] $field поле/список вложенных полей
	 * @param mixed $value значение элемента, который требуется удалить (null - если нужно удалить элемент полностью)
	 * 
	 * @return array копия переданного массива без указанного элемента
	 */
	public static function withoutField(array $from, $field, $value = null) {
		if (!is_array($field)) {
			$field = [$field];
		}
		
		$copy = $from;
		$pointer =& $copy;
		$index = 0;
		
		foreach ($field as $fieldName) {
			if (!is_array($pointer) || !array_key_exists($fieldName, $pointer)) {
				// если элемент массива не найден - останавливаем
				break;
			} elseif ($index == count($field) - 1) {
				// если это последний элемент вложенного поля - удаляем
				if ($value === null) {
					unset($pointer[$fieldName]); // если не передано конкретное значение - удаляем весь элемент
				} elseif (in_array($value, $pointer)) {
					self::removeValue($pointer[$fieldName], $value);
				}
				break;
			}
			
			$pointer =& $pointer[$fieldName];
			$index++;
		}
		
		return $copy;
	}

	/**
	 * Удаляет из переданного массива все элементы с указанным значением
	 *
	 * @param array &$array массив
	 * @param mixed $value удаляемое значение
	 */
	public static function removeValue(array &$array, $value) {
		do {
			$key = array_search($value, $array);
			if ($key !== false) {
				unset($array[$key]);
			}
		} while ($key !== false);
	}
}
