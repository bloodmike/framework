<?php

namespace Framework\Response;

/**
 *
 * @author mkoshkin
 */
interface ResponseInterface {
    /**
     * Поле кук, определяющее разрешение на вывод отладки
     */
    const DEBUG_KEY = 'dev';

    /**
     * Значение поля в куки, нужное для вывода отладки
     */
    const DEBUG_VALUE = 'ghbdtnvbif!';
    
    /**
     * Должен возвращать
     */
    public function show();
}
