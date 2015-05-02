<?php

namespace Framework\App;

/**
 * Модуль системы.
 * Содержит данные о сервисах системы, которые следует передавать в контейнер 
 * для возможности дальнейшей загрузки этих сервисов.
 *
 * @author mkoshkin
 */
abstract class Module {
    
    /**
     * @return array описание сервисов системы
     */
    abstract public function getServices();
}
