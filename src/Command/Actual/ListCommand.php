<?php

namespace Framework\Command\Actual;

use Framework\Command\Argument;
use Framework\Command\Command;
use Framework\Command\Console\ConsoleCommandExecutor;
use Framework\Helper\ArrayHelper;

/**
 * Команда для вывода списка доступных команд с описанием
 *
 * @author mkoshkin
 */
class ListCommand extends Command {
    /**
     * @return ConsoleCommandExecutor
     */
    protected function getExecutor() {
        return $this->Container->get('framework.command.executor');
    }

    /**
     * @inheritdoc
     */
    public function configure() {
         $this
             ->setDescription('Вывод списка доступных команд')
             ->addArgument(Argument::create('quiet', 'q', 'Выводить только названия команд', false));
    }

    /**
     * @inheritdoc
     */
    public function run() {
        $commandsInfo = $this->getExecutor()->getCommandsInfo();
        $quiet = $this->context->getBoolean('quiet');
        $names = array_keys($commandsInfo);
        sort($names, SORT_STRING);
        reset($names);
        if ($quiet) {
            $this->outputLn(implode(PHP_EOL, $names));
        } else {
            foreach ($names as $name) {
                $commandClass = ArrayHelper::get($commandsInfo[$name], 'class', '');
                if (!$commandClass || !class_exists($commandClass)) {
                    continue;
                }
                $this->outputLn($name);
                $Command = Command::createInstance($commandClass, $this->Container);
                $this->outputLn("\t\t".$Command->getDescription());
            }
        }
    }
}