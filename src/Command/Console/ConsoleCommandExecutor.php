<?php

namespace Framework\Command\Console;

use Exception;
use Framework\Command\Command;
use Framework\Command\Console\ConsoleCommandManager;
use Framework\Service\Container;
use RuntimeException;

/**
 * Исполнитель консольных команд.
 * Отвечает за выбор, параметризацию и запуск команд через консоль.
 *
 * @author mkoshkin
 */
class ConsoleCommandExecutor {
    /**
     * @var Container $Container контейнер зависимостей 
     */
    private $Container;
    
    /**
     * @var array данные о командах
     */
    private $commandsInfo;
    
    /**
     * @param Container $Container контейнер зависимостей
     */
    public function __construct(Container $Container) {
        $this->Container = $Container;
    }
    
    /**
     * Установить информацию о доступных в системе консольных командах
     * 
     * @param array $commandsInfo информация о командах
     * 
     * @return $this
     */
    public function setCommandsInfo(array $commandsInfo) {
        $this->commandsInfo = $commandsInfo;
        return $this;
    }
    
    /**
     * Выполнить команду из командной строки.
     * 
     * @param array $argv данные командной строки $argv
     */
    public function execute(array $argv) {
        try {
            $ConsoleCommandManager = new ConsoleCommandManager($argv);
            
            // получаем объект команды
            $commandName = $ConsoleCommandManager->getCommandName();
            if (!array_key_exists($commandName, $this->commandsInfo)) {
                throw new RuntimeException('Команда [' . $commandName . '] не найдена');
            }
            
            $className = $this->commandsInfo[$commandName]['class'];
            $Command = Command::createInstance($className, $this->Container);
            
            // получаем параметры, с которыми команда запущена
            $ArgumentsData = $ConsoleCommandManager->parseArgs($Command->getArguments());
            $Command->setArgs($ArgumentsData->getAll());
            $Command->run();
        } catch (Exception $Exception) {
            echo "[" . get_class($Exception) . "] " . $Exception->getMessage() . PHP_EOL;
        }
    }
}
