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
     * @return string путь к папке с логами крон-скриптов
     */
    protected function getLogPath() {
        return $this->context->getTrimmedString('log');
    }

    /**
     * @return string путь к исполняемому файлу php
     */
    protected function getPhpBinary() {
        $phpPath = $this->context->getTrimmedString('php');
        if (!$phpPath) {
            $phpPath = PHP_BINARY;
        }
        return $phpPath;
    }

    /**
     * @return string путь к исполняемому файлу index.php
     */
    protected function getIndexPhpFile() {
        $indexPath = $this->context->getTrimmedString('index');
        if (!$indexPath) {
            $indexPath = getcwd() . '/index.php';
        }
        return $indexPath;
    }

    /**
     * @return string путь к логу для ошибок скриптов, направляющих свои ошибки в общий файл
     */
    protected function getErrorLogFile() {
        return $this->context->getTrimmedString('error');
    }

    /**
     * @return string путь к файлу с конфигурацией php
     */
    protected function getPhpConfigFile() {
        return $this->context->getTrimmedString('config');
    }

    /**
     * @inheritdoc
     */
    public function run() {
        $phpPath = $this->getPhpBinary();
        $indexPath = $this->getIndexPhpFile();
        $phpConfigFile = $this->getPhpConfigFile();
        if ($phpConfigFile) {
            $phpPath .= ' -c ' . $phpConfigFile;
        }

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

                    $log = $this->getCommandLogAppendix($Command->getLogFilename($commandName), $Command->getErrorLogDst());
                    $this->outputLn($period . ' ' . $phpPath . ' ' . $indexPath . ' ' . $commandName . ($parameters ? ' ' . $parameters : '') . $log);
                }
            }
        }
    }

    /**
     * @param string $logFileName имя файла логов команды
     * @param int $errorLogDestination направление для логов ошибок команды (см CronCommand::ERROR_LOG_TO_...)
     *
     * @return string строка с направлениями потоков вывода в нужные файлы
     */
    protected function getCommandLogAppendix($logFileName, $errorLogDestination) {
        $log = '';

        $logPath = $this->getLogPath();
        if ($logPath && $logFileName) {
            $log = ' >> ' . $logPath . '/' . $logFileName;
        }

        switch ($errorLogDestination) {
            case CronCommand::ERROR_LOG_TO_NULL:
                $log .= ' 2>/dev/null';
                break;
            case CronCommand::ERROR_LOG_TO_ERROUT:
                $errorLogFile = $this->getErrorLogFile();
                if ($errorLogFile) {
                    $log .= ' 2>>' . $errorLogFile;
                }
                break;
            case CronCommand::ERROR_LOG_TO_FILE:
                if ($log) {
                    $log .= ' 2>&1';
                }
                break;
        }

        return $log;
    }
}