<?php

namespace Framework\Command;

use Framework\Service\Container;

/**
 * Консольная команда
 *
 * @author mkoshkin
 */
abstract class Command {
    /**
     * @var Container
     */
    protected $Container;
    
    /**
     * @var array массив переданных с командой параметров
     */
    protected $args;
    
    /**
     * 
     */
    public final function __construct(Container $Container, array $args) {
        $this->Container = $Container;
        $this->args = $args;
    }
    
    /**
     * Сохранение настроек команды
     */
    public function configure() {
        
    }
    
    /**
     * Исполняемая часть команды
     */
    abstract public function run();
}
