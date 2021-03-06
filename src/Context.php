<?php

namespace Framework;

/**
 * Контекст запроса<br/>
 * Класс-обёртка для удобного обращения к параметрам запроса.
 *
 * @author mike
 */
class Context {
    
    /**
     * Контейнер параметров запроса
     * @var array ссылка на массив параметров запроса
     */
    private $data = null;
    
    /**
     * Сформировать объект контекста со ссылкой на массив параметров запроса.
     * @param array &$data параметры запроса
     */
    public function __construct(array &$data) {
        $this->data = $data;
    }
    
    /**
     * Получить массив параметров из указанного поля.
     *
     * @param string $field поле параметров, из которого нужно получить массив
     * @param bool $scalarAsValue использовать ли одиночное значение в качестве единственного элемента массива
     *
     * @return array возвращает массив параметров или пустой массив в случае, когда указанного поля нет в списке или в поле содержится не массив
     */
    public function getArray($field, $scalarAsValue = false) {
        if (array_key_exists($field, $this->data)) {
            if (is_array($this->data[$field])) {
                return $this->data[$field];
            } elseif ($scalarAsValue) {
                return array($this->data[$field]);
            }
        }
        
        return array();
    }
    
    /**
     * Получить из указанного поля массив уникальных положительных целых чисел.
     *
     * @param string $field имя поля
     * @param bool $scalarAsValue использовать ли одиночное значение в качестве единственного элемента массива
     *
     * @return array возвращает массив уникальных положительных чисел из параметров запроса или пустой массив, если поле отсутствует или в нём содержится не массив
     */
    public function getUnsignedIntUniqueArray($field, $scalarAsValue = false) {
        $arr = $this->getArray($field, $scalarAsValue);
        $keys = array();
        if (count($arr) > 0) {
            foreach ($arr as $id) {
                $id = (int)$id;
                if ($id > 0) {
                    $keys[$id] = true;
                }
            }
        }
        
        return array_keys($keys);
    }
    
    /**
     * 
     * @param string $field
     * @return boolean
     */
    public function getBoolean($field) {
        if (array_key_exists($field, $this->data)) {
            return (bool)$this->data[$field];
        }
        
        return false;
    }
    
    /**
     * Получить дробное числовое значение из параметров запроса.
     * 
     * @param   string $field имя поля
     * 
     * @return  float дробное число из параметров или 0 если поля нет в списке параметров
     */
    public function getFloat($field) {
        if (array_key_exists($field, $this->data)) {
            return (float)$this->data[$field];
        }
        
        return 0;
    }
    
    
    /**
     * Получить целочисленное значение из параметров запроса.
     *
     * @param   string $field имя поля
     * @param   int|null $min если число - минимально допустимое значение
     * @param   int|null $max если число - максимально допустимое значение
     *
     * @return  int целое число из параметров или 0 если поля нет в списке параметров
     */
    public function getInt($field, $min = null, $max = null) {
        if (array_key_exists($field, $this->data)) {
            $v = (int)$this->data[$field];
        } else {
            $v = 0;
        }

        if ($min !== null) {
            $v = max($min, $v);
        }

        if ($max !== null) {
            $v = min($max, $v);
        }
        return $v;
    }
    
    /**
     * 
     * @param   string $field
     * @return  string
     */
    public function getString($field) {
        if (array_key_exists($field, $this->data)) {
            return (string)$this->data[$field];
        }
        
        return '';
    }

    /**
     * @param string $field
     * @param string $pattern
     *
     * @return string строковое значение параметра, если оно соответствует переданному регулярному выражению,
     *                  или пустую строку - если не соответствует
     */
    public function getMatchingString($field, $pattern) {
        if (array_key_exists($field, $this->data)) {
            $string = (string)$this->data[$field];
            if ($pattern) {
                if (preg_match($pattern, $string)) {
                    return $string;
                }
            } else {
                return $string;
            }
        }

        return '';
    }

    /**
     * @param string $field
     * @param string $characterMask
     * 
     * @return string
     */
    public function getTrimmedString($field, $characterMask = " \t\n\r\0\x0B") {
        return trim($this->getString($field), $characterMask);
    }
    
    /**
     * Проверить, что среди параметров присутствует указанное поле.
     * @param string $field имя поля
     * @return boolean <b>TRUE</b> если поле есть среди параметров, <b>FALSE</b> в противном случае
     */
    public function hasField($field) {
        return array_key_exists($field, $this->data);
    }
    
    /**
     * 
     * @param   string  $field
     * @return  boolean
     * @deprecated since version 1 дублирующий метод
     */
    public function exists($field) {
        return array_key_exists($field, $this->data);
    }

    /**
     * @return bool
     */
    public function isEmpty() {
        return !count($this->data);
    }
}
