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
			
            if ($index++ == count($field) - 1) {
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
     * @param array& $to
     * @param string|string[] $field
     * @param int|float $value
     *
     * @return int|float
     */
	public static function increment(array &$to, $field, $value = 1) {
        if (is_array($field)) {
            $i = 0;
            $n = count($field);
            if ($n) {
                $pointer =& $to;
                foreach ($field as $fieldName) {
                    if ($i++ < $n - 1) {
                        if (!array_key_exists($fieldName, $pointer) || !is_array($pointer[$fieldName])) {
                            $pointer[$fieldName] = [];
                        }
                        $pointer =& $pointer[$fieldName];
                    } else {
                        return ArrayHelper::increment($pointer, $fieldName, $value);
                    }
                }
            }
            return 0;
        } else {
            if (array_key_exists($field, $to)) {
                $to[$field] += $value;
            } else {
                $to[$field] = $value;
            }
        }
        return $to[$field];
    }

	/**
	 * @param array& $to
	 * @param string|string[] $field
	 * @param string $string
	 *
	 * @return string
	 */
	public static function append(array &$to, $field, $string) {
		if (is_array($field)) {
			$i = 0;
			$n = count($field);
			if ($n) {
				$pointer =& $to;
				foreach ($field as $fieldName) {
					if ($i++ < $n - 1) {
						if (!array_key_exists($fieldName, $pointer) || !is_array($pointer[$fieldName])) {
							$pointer[$fieldName] = [];
						}
						$pointer =& $pointer[$fieldName];
					} else {
						return ArrayHelper::append($pointer, $fieldName, $string);
					}
				}
			}
			return 0;
		} else {
			if (array_key_exists($field, $to)) {
				$to[$field] .= $string;
			} else {
				$to[$field] = $string;
			}
		}
		return $to[$field];
	}

	/**
	 * @param array& $to
	 * @param string|string[] $field
	 * @param mixed $element
	 *
	 * @return string
	 */
	public static function push(array &$to, $field, $element) {
		if (is_array($field)) {
			$i = 0;
			$n = count($field);
			if ($n) {
				$pointer =& $to;
				foreach ($field as $fieldName) {
					if ($i++ < $n - 1) {
						if (!array_key_exists($fieldName, $pointer) || !is_array($pointer[$fieldName])) {
							$pointer[$fieldName] = [];
						}
						$pointer =& $pointer[$fieldName];
					} else {
						return ArrayHelper::push($pointer, $fieldName, $element);
					}
				}
			}
			return 0;
		} else {
			if (array_key_exists($field, $to)) {
				if (!is_array($to[$field])) {
                    $to[$field] = [];
                }
                $to[$field][] = $element;
			} else {
				$to[$field] = [$element];
			}
		}
		return $to[$field];
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
				} elseif (in_array($value, $pointer[$fieldName])) {
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
	 * Добавляет в массив переданные переменные, если они не пустые.
	 * Для строк - непустые строки, для чисел - отличные от нуля значения, null и false не добавляются;
	 * объекты добавляются в любом случае, а массивы - только если они непустые.
	 *
	 * @param array &$to массив, куда надо добавлять значения
	 * @param $_ один и более добавляемых параметров
	 */
	public static function addNotEmpty(array &$to, $_) {
		$n = func_num_args();
		for ($i = 1; $i < $n; $i++) {
			$arg = func_get_arg($i);
			if (is_string($arg) || is_int($arg) || is_float($arg)) {
				if ($arg) {
					$to[] = $arg;
				}
			} elseif ($arg === true || is_object($arg) || (is_array($arg) && count($arg))) {
				$to[] = $arg;
			}
		}
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
	
	/**
	 * @param array $params параметры url-запроса
	 * 
	 * @return string строка с параметрами без ключей: a[]=x&a[]=y вместо a[0]=x&a[1]=y
	 */
	public static function httpBuildQueryWithoutKeys(array $params) {
		return preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', http_build_query($params));
	}

	/**
	 * @param string $query url-запрос
	 * @param bool $before нужно ли проверять и выставлять знак вопроса в начале строки
	 * @param bool $after нужно ли проверять и выставлять знак & в конце строки
	 *
	 * @return string строка url-запроса с проставленным слева "?" и справа "&"
	 */
	public static function httpQuerySurround($query, $before = true, $after = true) {
		if ($before) {
			$query = ltrim($query, '?');
			if ($query || $after) {
				$query = '?' . $query;
			}
		}
		if ($after) {
			$query = rtrim($query, '&');
			if ($query != '?' || $query == '' && !$before) {
				$query .= '&';
			}
		}
		return $query;
	}

	/**
	 * @param mixed $mixed
	 *
	 * @return array
	 */
	public static function toArray($mixed) {
		if (is_object($mixed)) {
			return get_object_vars($mixed);
		} elseif (!is_array($mixed)) {
			return (array)$mixed;
		}
		return $mixed;
	}
}
