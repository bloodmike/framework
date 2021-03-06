<?php

namespace Framework\Command\Console;

use Exception;
use Framework\Command\Actual\CreateCrontabCommand;
use Framework\Command\Actual\CheckServicesCommand;
use Framework\Command\Actual\ListCommand;
use Framework\Command\Argument;
use Framework\Command\Command;
use Framework\Service\Container;

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
            'framework:crontab:create' => [ // команда для сборки crontab'а проекта
                'class' => CreateCrontabCommand::class
            ],
            'framework:services:check' => [ // команда для проверки валидности списка сервисов
                'class' => CheckServicesCommand::class,
            ],
            'list' => [ // команда для вывода списка команд
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
            if (!$commandName) {
                $commandName = 'list';
            } elseif (!array_key_exists($commandName, $this->commandsInfo)) {
                $matchingCommands = [];
                foreach (array_keys($this->commandsInfo) as $commandInfoName) {
                    if (strpos($commandInfoName, $commandName) === 0) {
                        $matchingCommands[] = PHP_EOL . $commandInfoName;
                    }
                }
                $postfix = "";
                if (count($matchingCommands)) {
                    $postfix = ", возможно, вы имели в виду:" . implode('', $matchingCommands);
                }
                echo 'Команда [' . $commandName . '] не найдена' . $postfix . PHP_EOL;
                return;
            }

            $className = $this->commandsInfo[$commandName]['class'];

            $this->Container->set('framework.command.executor', $this); // записываем исполнитель команд в контейнер
            $Command = Command::createInstance($className, $this->Container);

            // получаем параметры, с которыми команда запущена
            $Arguments = $Command->getArguments();
            $ArgumentsData = $ConsoleCommandManager->parseArgs($Arguments);
            $Command->setArgs($ArgumentsData->getAll());
            if ($Command->getContext()->getBoolean(Argument::HELP_NAME)) {
                $description = $Command->getDescription();
                $s = PHP_EOL;
                if ($description) {
                    $s .= $description . PHP_EOL;
                }
                $argsStr = '';
                foreach ($Arguments as $Argument) {
                    $longName = $Argument->getName();
                    if ($longName == Argument::HELP_NAME) {
                        continue;
                    }
                    $longName = $longName ? '--' . $longName : '';
                    $shortName = $Argument->getShortName();
                    $shortName = $shortName ? '-' . $shortName : '';
                    $hasValue = $Argument->getHasValue();
                    $argumentDescription = $Argument->getDescription();
                    $argsStr .= "\t".$longName . ($longName && $shortName ? '/' : '') . $shortName;
                    if ($hasValue) {
                        $argsStr .= "\t" . 'VALUE';
                    } else {
                        $argsStr .= "\t";
                    }

                    if ($argumentDescription) {
                        $argsStr .= "\t" . $argumentDescription;
                    }
                    $argsStr .= PHP_EOL;
                }
                if ($argsStr) {
                    $s .= PHP_EOL . 'Параметры:' . PHP_EOL;
                }
                echo $s, $argsStr, PHP_EOL;
            } else {
                $Command->runBefore();
                $Command->run();
                $Command->runAfter();
            }

        } catch (Exception $Exception) {
            echo "[" . get_class($Exception) . "] " . $Exception->getMessage() . PHP_EOL;
        }
    }
}
