<?php

namespace Framework\Response;

/**
 * Description of Response
 *
 * @author mkoshkin
 */
class Response implements ResponseInterface {
    
    /**
     * @var string
     */
    private $data;
    
    /**
     * @var int
     */
    private $code;
    
    /**
     * @param string $data
     * @param int $code
     */
    public function __construct($data = '', $code = 200) {
        $this->data = $data;
        $this->code = $code;
    }
    
    /**
     * @inheritdoc
     */
    public function show() {
        http_response_code($this->code);
        echo $this->data;
    }
}
