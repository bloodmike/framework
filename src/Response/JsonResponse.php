<?php

namespace Framework\Response;

use JsonSerializable;

/**
 * Description of JsonResponse
 *
 * @author mkoshkin
 */
class JsonResponse implements ResponseInterface {
    
    /**
     * @var JsonSerializable 
     */
    protected $data;
    
    /**
     * @param mixed $data
     * @param int $code
     */
    public function __construct($data) {
        $this->data = $data;
    }
    
    /**
     * @inheritdoc
     */
    public function show() {
        echo json_encode($this->data);
    }
}
