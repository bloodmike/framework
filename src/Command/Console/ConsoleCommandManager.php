<?php

namespace Framework\Command\Console;

use Framework\Command\Argument;
use Framework\Command\ArgumentsData;
use RuntimeException;

/**
 * Парсер параметров из командной строки.
 *
 * @author mkoshkin
 */
class ConsoleCommandManager {
    
    /**
     * @var array параметры командной строки
     */
    private $argv;
    
    /**
     * @param array $argv параметры командной строки
     */
    public function __construct(array $argv) {
        $this->argv = $argv;
    }
    
    /**
     * @return string имя вызываемой команды
     * 
     * @return RuntimeException если имя команды не передано
     */
    public function getCommandName() {
        if (!array_key_exists(1, $this->argv) || $this->argv[1] == '') {
            throw new RuntimeException('Имя команды не передано');
        }
        
        return $this->argv[1];
    }
    
    /**
     * @param Argument[] $Arguments параметры команды
     * 
     * @return ArgumentsData данные о значениях параметров, полученных из командной строки;
     *                      все параметры, несоответствующие переданным, из результатов убраны
     */
    public function parseArgs(array $Arguments) {
        $ArgumentsData = Argument::parseData($this->argv);
        $bestNames = [];
        foreach ($Arguments as $Argument) {
            if ($Argument->getShortName() != '' && $Argument->getName() != '') {
                $ArgumentsData->merge(
                        $Argument->getShortName(), 
                        $Argument->getName());
                
                $bestNames[$Argument->getBestName()] = true;
            }
        }
        
        $removeNames = [];
        foreach ($ArgumentsData->getNames() as $name) {
            if (!array_key_exists($name, $bestNames)) {
                $removeNames[] = $name;
            }
        }
        
        foreach ($removeNames as $name) {
            $ArgumentsData->remove($name);
        }
        
        return $ArgumentsData;
    }
}
