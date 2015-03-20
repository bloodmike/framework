<?php

namespace Framework\Buffer;

/**
 * Элемент буфера
 * Объекты с таким интерфейсом могут быть помещены в буфер Buffer.
 * 
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
interface BufferElementInterface {
    
    /**
     * Получить хэш-ключ объекта
     * @return string хэш-ключ объекта
     */
    public function getHashKey();
    
    /**
     * @return string
     */
    public function getTable();
}
