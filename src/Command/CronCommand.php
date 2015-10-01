<?php

namespace Framework\Command;

/**
 * Команда, выполняемая через crontab.
 * Помимо параметров запуска должна возвращать периоды, в которые ее нужно выполнять на сервере.
 *
 * @author mkoshkin
 */
abstract class CronCommand extends Command {

    /**
     * Отправлять ошибки в /dev/null
     */
    const ERROR_LOG_TO_NULL = 0;

    /**
     * Отправлять ошибки по настройкам php или в указанный файл ошибок
     */
    const ERROR_LOG_TO_ERROUT = 1;

    /**
     * Отправлять ошибки в общий файл логов скрипта (если файл не указан - будут оправлены в стандартный поток ошибок)
     */
    const ERROR_LOG_TO_FILE = 2;

    /**
     * @return array периоды выполнения команды и параметры выполнения:
     *                  [
     *                      ['10,40 * * * *', '-l 12 --delay 500'],
     *                      ['20,50 * * * *'],                          // без параметров
     *                      ['0,30 * * * *', '-l 100 --delay 1000'],
     *                      ['@reboot'], // на перезагрузке
     *                  ]
     */
    abstract public function getPeriods();

    /**
     * @return bool нужно ли добавлять команду в crontab
     */
    public function isEnabled() {
        return true;
    }

    /**
     * @param string $name имя команды
     *
     * @return string если возвращается непустая строка - лог будет записываться в файл с указанным именем,
     *                  если возвращается пустая строка - лог записываться не будет.
     */
    public function getLogFilename($name) {
        return str_replace(':', '_', $name) . '.log';
    }

    /**
     * @return int направление, куда отправлять логи ошибок
     */
    public function getErrorLogDst() {
        return self::ERROR_LOG_TO_ERROUT;
    }
}