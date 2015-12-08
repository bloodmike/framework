<?php

namespace Framework\Command\Console;

use Exception;
use Framework\Command\Actual\CreateCrontabCommand;
use Framework\Command\Actual\ListCommand;
use Framework\Command\Command;
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

        // добавляем в список команд фреймворковые команды
        $this->commandsInfo = [
            'framework:crontab:create' => [
                'class' => CreateCrontabCommand::class
            ],
            'list' => [
                'class' => ListCommand::class
            ],
        ];
    }

    /**
     * @return array данные о командах
     */
    public function getCommandsInfo() {
        return $this->commandsInfo;
    }

    /**
     * Установить информацию о доступных в системе консольных командах
     * 
     * @param array $commandsInfo информация о командах
     * 
     * @return $this
     */
    public function setCommandsInfo(array $commandsInfo) {
        $this->commandsInfo = array_merge($this->commandsInfo, $commandsInfo);
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

            $this->Container->set('framework.command.executor', $this); // записываем исполнитель команд в контейнер
            $Command = Command::createInstance($className, $this->Container);
            
            // получаем параметры, с которыми команда запущена
            $ArgumentsData = $ConsoleCommandManager->parseArgs($Command->getArguments());
            $Command->setArgs($ArgumentsData->getAll());
            $Command->runBefore();
            $Command->run();
            $Command->runAfter();

        } catch (Exception $Exception) {
            echo "[" . get_class($Exception) . "] " . $Exception->getMessage() . PHP_EOL;
        }
    }
}
