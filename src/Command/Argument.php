<?php

namespace Framework\Command;

use InvalidArgumentException;

/**
 * Параметр команды.
 * Элемент данных, который содержит информацию об одном параметре конкретной команды.
 *
 * @author mkoshkin
 */
class Argument {
    /**
     * Зарезервированное имя команды для вывода справки о ней
     */
    const HELP_NAME = 'help';
    
    /**
     * @var string имя команды (полное)
     */
    private $name;
    
    /**
     * @var string короткое имя команды (1 символ)
     */
    private $shortName;
    
    /**
     * @var string описание команды
     */
    private $description;

    /**
     * @var bool должно ли у параметра быть значение
     */
    private $hasValue;

    /**
     * @param string $description описание команды
     */
    public function __construct($description = "") {
        $this->name = '';
        $this->shortName = '';
        $this->hasValue = true;
        $this->description = $description;
    }

    /**
     * @param string $name
     * @param string $shortName
     * @param string $description
     * @param bool $hasValue
     *
     * @return Argument
     */
    public static function create($name, $shortName = '', $description = '', $hasValue = true) {
        $Argument = (new self($description))->setHasValue($hasValue);
        if ($name) {
            $Argument->setName($name);
        }
        if ($shortName) {
            $Argument->setShortName($shortName);
        }
        return $Argument;
    }

    /**
     * Назначить параметру полное имя, передаваемое через --ИМЯ_ПАРАМЕТРА
     * 
     * @param string $name полное имя параметра (только a-z, A-Z, 0-9, длинее 1 символа)
     * 
     * @return $this
     * 
     * @throws InvalidArgumentException при совпадении имени команды с зарезервированным
     *                                  или при некорректном его формате (должно быть длиннее 1 символа и состоять 
     *                                  только из a-z, A-Z, 0-9)
     */
    public function setName($name) {
        if (!preg_match('/^[\da-z]{2,}$/ui', $name)) {
            throw new InvalidArgumentException('Имя параметра [' . $name . '] некорректно');
        }
        
        $this->name = $name;
        return $this;
    }

    /**
     * Назначить параметру короткое имя, передаваемое через -ИМЯ
     * 
     * @param string $shortName короткое имя параметра (1 символ: a-z, A-Z, 0-9)
     * 
     * @return $this
     * 
     * @throws InvalidArgumentException при некорректном формате имени команды
     */
    public function setShortName($shortName) {
        if (!preg_match('/^[\da-z]$/ui', $shortName)) {
            throw new InvalidArgumentException('Короткое имя параметра [' . $shortName . '] некорректно');
        }
        
        $this->shortName = $shortName;
        return $this;
    }
    
    /**
     * Установить описание параметра команды
     * 
     * @param string $description описание параметра
     * 
     * @return $this
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * Установить, требуется ли указывать значение параметра
     *
     * @param bool $val нужно ли указывать значение параметра
     *
     * @return $this
     */
    public function setHasValue($val) {
        $this->hasValue = (bool)$val;
        return $this;
    }

    /**
     * @return bool нужно ли указывать значение параметра
     */
    public function getHasValue() {
        return $this->hasValue;
    }

    /**
     * @return string имя параметра
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * @return string описание команды
     */
    public function getDescription() {
        return $this->description;
    }
    
    /**
     * @return string короткое имя параметра
     */
    public function getShortName() {
        return $this->shortName;
    }
    
    /**
     * @return string возвращает полное имя, если оно указано, или краткое в остальных случаях
     */
    public function getBestName() {
        if ($this->name != '') {
            return $this->name;
        }
        return $this->shortName;
    }
        
    /**
     * @param array $argv аргументы командной строки (глобальная переменная $argv)
     * 
     * @return ArgumentsData данные аргументов, сгруппированные по именам полей
     */
    public static function parseData(array $argv) {
        $lastArgumentName = null;
        $ArgumentsData = new ArgumentsData();
        
        for ($i = 1; $i < count($argv); $i++) {
            $arg = $argv[$i];
            $to = [];
            if (preg_match('/^\-([\da-z])$/ui', $arg, $to) || preg_match('/^\-\-([\da-z]{2,})$/ui', $arg, $to)) {
                // имя команды
                if ($lastArgumentName !== null) {
                    $ArgumentsData->add($lastArgumentName, null);
                }
                $lastArgumentName = $to[1];
            } elseif ($lastArgumentName !== null) {
                // значение
                $ArgumentsData->add($lastArgumentName, $arg);
                $lastArgumentName = null;
            }
        }
        
        if ($lastArgumentName !== null) {
            $ArgumentsData->add($lastArgumentName, null);
        }
        
        return $ArgumentsData;
    }
}
