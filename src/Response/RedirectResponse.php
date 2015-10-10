<?php

namespace Framework\Response;

/**
 * Ответ, подразумевающий редирект на другую страницу
 *
 * @author mkoshkin
 */
class RedirectResponse implements ResponseInterface {
    
    /**
     * @var string 
     */
    private $url;
    
    /**
     * @var int
     */
    private $code;
    
    /**
     * @param string $url
     * @param int $code
     */
    public function __construct($url, $code = 301) {
        $this->url = $url;
        $this->code = $code;
    }

    /**
     * @return bool есть ли в ссылке GET-параметры (просто наличие знака ? тоже считается наличием параметров)
     */
    public function hasQuery() {
        return strpos($this->url, '?') !== false;
    }

    /**
     * Добавить указанную подстроку к ссылке справа
     *
     * @param string $urlPart строка, добавляемая к ссылке
     *
     * @return $this
     */
    public function append($urlPart) {
        $this->url .= $urlPart;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function show() {
        if (headers_sent()) {
            echo "<html><head><meta http-equiv='refresh' content='0; url=\"" . $this->url . "\"'></head><body></body></html>";
        }
        else {
            // Отключаем кеширование редиректов
            header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate"); 
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
            header('Location: ' . $this->url, true, $this->code);
        }
    }
}
