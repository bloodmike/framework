<?php

namespace Framework\Command;

use Framework\Context;
use Framework\Service\Container;
use InvalidArgumentException;
use RuntimeException;

/**
 * Консольная команда
 *
 * @author mkoshkin
 */
abstract class Command {
    /**
     * @var Container контейнер зависимостей
     */
    protected $Container;
    
    /**
     * @var array массив переданных с командой параметров
     */
    protected $args;
    
    /**
     * @var Argument[] параметры, которые может принимать команда
     */
    private $Arguments;
    
    /**
     * @var string описание команды
     */
    private $description;
    
    /**
     * @var bool[] хэшмэп с именами / краткими именами параметров
     */
    private $namesMap;
    
    /**
     * @var Context контекст с параметрами команды
     */
    protected $context;
    
    /**
     * @param Container $Container контейнер зависимостей
     * @param array $args аргументы
     */
    public final function __construct(Container $Container, array $args) {
        $this->Container = $Container;
        $this->args = $args;
        $this->context = new Context($this->args);
        $this->description = '';
        $this->namesMap = [];
        $this->configure();
    }
    
    /**
     * Установить значения переданных аргументов
     * 
     * @param array $args массив с передаваемыми команде аргументами
     * 
     * @return $this
     */
    public function setArgs(array $args) {
        $this->args = $args;
        $this->context = new Context($this->args);
        return $this;
    }
    
    /**
     * Сохранение настроек команды
     */
    public function configure() {
        
    }
    
    /**
     * Добавить в команду аргумент
     * 
     * @param Argument $Argument новый аргумент
     * 
     * @return $this
     * 
     * @throws InvalidArgumentException если имя/краткое имя совпадает с именем другого аргумента этой команды
     */
    protected function addArgument(Argument $Argument) {
        $name = $Argument->getName();
        if ($name != '' && array_key_exists($name, $this->namesMap)) {
            throw new InvalidArgumentException('Имя параметра [' . $name . '] уже занято для этой команды');
        }
        
        $shortName = $Argument->getShortName();
        if ($shortName != '' && array_key_exists($shortName, $this->namesMap)) {
            throw new InvalidArgumentException('Имя параметра [' . $shortName . '] уже занято для этой команды');
        }
        
        $this->Arguments[] = $Argument;
        return $this;
    }
    
    /**
     * Установить описание команды
     * 
     * @param string $description описание команды
     * 
     * @return $this
     */
    protected function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Argument[] список параметров команды
     */
    public function getArguments() {
        return $this->Arguments;
    }

    /**
     * Исполняемая часть команды
     */
    abstract public function run();
    
    /**
     * Вывод строки в консоль
     * 
     * @param string $string строка для вывода
     * 
     * @return $this
     */
    protected final function output($string) {
        echo $string;
        return $this;
    }
    
    /**
     * Вывод строки в консоль с добавлением PHP_EOL в конец строки
     * 
     * @param string $string строка для вывода
     * 
     * @return $this
     */
    protected final function outputLn($string) {
        echo $string . PHP_EOL;
        return $this;
    }
    
    /**
     * @param string $className класс команды
     * @param Container $Container контейнер зависимостей
     * 
     * @return Command объект указанного класса
     * 
     * @throws RuntimeException если переданный класс не является подклассом консольной команды
     */
    public static function createInstance($className, Container $Container) {
        if (!is_subclass_of($className, self::class)) {
            throw new \RuntimeException('Класс [' . $className . '] не является командой');
        }
        
        return new $className($Container, []);
    }
}
