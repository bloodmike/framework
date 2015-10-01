<?php

namespace Framework\Command\Actual;

use Framework\Command\Argument;
use Framework\Command\Command;
use Framework\Command\Console\ConsoleCommandExecutor;
use Framework\Command\CronCommand;
use Framework\Helper\ArrayHelper;

/**
 * Команда для формирования crontab-файла:
 * php index.php framework:crontab:create -p /usr/bin/php -i /www/project.com/public/index.php
 *
 * @author mkoshkin
 */
class CreateCrontabCommand extends Command {

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
        $this->setDescription('Построить crontab проекта');

        $this
            ->addArgument(
                (new Argument())
                    ->setShortName('p')
                    ->setName('php')
                    ->setDescription('полный путь к php (по умолчанию - константа PHP_BINARY)'))
            ->addArgument(
                (new Argument())
                    ->setShortName('c')
                    ->setName('config')
                    ->setDescription('путь к файлу конфигурации php, с которым следует выполнять скрипт'))
            ->addArgument(
                (new Argument())
                    ->setShortName('i')
                    ->setName('index')
                    ->setDescription('полный путь к index.php (по умолчанию - getcwd() . "/index.php")'))
            ->addArgument(
                (new Argument())
                    ->setShortName('l')
                    ->setName('log')
                    ->setDescription('путь к папке с файлами логов (если не передан - логи записываться не будут)'))
            ->addArgument(
                (new Argument())
                    ->setShortName('e')
                    ->setName('error')
                    ->setDescription('полный путь к файлу с логами ошибок (если не указан - будет использован файл из php.ini)')
            );
    }

    /**
     * @inheritdoc
     */
    public function run() {
        $phpPath = $this->context->getTrimmedString('php');
        if (!$phpPath) {
            $phpPath = PHP_BINARY;
        }

        $indexPath = $this->context->getTrimmedString('index');
        if (!$indexPath) {
            $indexPath = getcwd() . '/index.php';
        }

        $logPath = $this->context->getTrimmedString('log');

        $phpConfigFile = $this->context->getTrimmedString('config');
        if ($phpConfigFile) {
            $phpPath .= ' -c ' . $phpConfigFile;
        }
        $errorLogFile = $this->context->getTrimmedString('error');

        $commandsInfo = $this->getExecutor()->getCommandsInfo();
        foreach ($commandsInfo as $commandName => $commandData) {
            $commandClass = (string)ArrayHelper::get($commandData, 'class', '');
            if ($commandClass == __CLASS__ || !$commandClass) {
                continue;
            }
            $Command = Command::createInstance($commandClass, $this->Container);
            if ($Command instanceof CronCommand && $Command->isEnabled()) {
                $periods = $Command->getPeriods();
                foreach ($periods as $periodData) {
                    $period = ArrayHelper::get($periodData, 0, '');
                    if (!$period) {
                        continue;
                    }
                    $parameters = ArrayHelper::get($periodData, 1, '');

                    $errorLogDst = $Command->getErrorLogDst();

                    $log = '';
                    if ($logPath) {
                        $logName = $Command->getLogFilename($commandName);
                        if ($logName) {
                            $log = ' >> ' . $logPath . '/' . $logName;
                        }
                    }

                    switch ($errorLogDst) {
                        case CronCommand::ERROR_LOG_TO_NULL:
                            $log .= ' 2>/dev/null';
                            break;
                        case CronCommand::ERROR_LOG_TO_ERROUT:
                            if ($errorLogFile) {
                                $log .= ' 2>' . $errorLogFile;
                            }
                            break;
                        case CronCommand::ERROR_LOG_TO_FILE:
                            if ($log) {
                                $log .= ' 2>&1';
                            }
                        break;
                    }

                    $this->outputLn($period . ' ' . $phpPath . ' ' . $indexPath . ' ' . $commandName . ($parameters ? ' ' . $parameters : '') . $log);
                }
            }
        }
    }
}