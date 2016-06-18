<?php

namespace Framework\Command\Actual;

use Framework\Command\Command;

/**
 * Команда для проверки корректности списка сервисов
 *
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
class CheckServicesCommand extends Command {
    /**
     * @inheritdoc
     */
    public function run() {
        $commandsInfo = $this->getExecutor()->getCommandsInfo();
        foreach ($commandsInfo as $commandInfo) {

        }
    }
}