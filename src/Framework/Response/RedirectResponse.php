<?php

namespace Framework\Response;

/**
 * Description of RedirectResponse
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
