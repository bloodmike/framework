<?php

namespace Framework\Command\Actual;

use Framework\Command\Argument;
use Framework\Command\Command;
use Framework\Service\InfoChecker;

/**
 * Команда для проверки корректности списка сервисов
 *
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
class CheckServicesCommand extends Command {
    /**
     * @var bool проблем с конфигурацией сервисов не обнаружено
     */
    protected $isOK = true;

    /**
     * @inheritdoc
     */
    public function configure() {
        $this->addArgument(Argument::create('config', 'c', 'check configuration data', false));
    }

    /**
     * @inheritdoc
     */
    public function run() {
        $servicesInfo = $this->Container->getServicesInfo();

        $InfoChecker = new InfoChecker();
        foreach ($servicesInfo as $serviceName => $serviceInfo) {
            try {
                $InfoChecker->setService($serviceName, $serviceInfo);
            } catch (\Exception $Exception) {
                $this->outputLn('Problem with [' . $serviceName . ']: ' . $Exception->getMessage());
                $this->isOK = false;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function runAfter() {
        if (!$this->isOK) {
            throw new \RuntimeException('One or more errors found in services definition');
        }
    }
}