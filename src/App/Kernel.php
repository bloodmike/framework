<?php

namespace Framework\App;

/**
 * Ядро системы
 *
 * @author mkoshkin
 */
class Kernel {
    
    /**
     * @var Kernel 
     */
    public static $instance;
    
    /**
     * @var Module[]
     */
    private $Modules;
    
    /**
     * 
     * @param Module[] $Modules
     */
    public function __construct(array $Modules) {
        if (self::$instance !== null) {
            die("Kernel cann't be created multiple times");
        }
        
        self::$instance = $this;
        $this->Modules = $Modules;
    }
}
