<?php

namespace Framework\TestCases\Service;

use Framework\Service\InfoChecker;
use Framework\TestCases\Mocks\Container\TestServiceA;

/**
 * @author Mikhail P. Koshkin <bloodmike.ru@gmail.com>
 */
class InfoCheckerTest extends \PHPUnit_Framework_TestCase {

    /**
     * Тестирует случай корректного вызова
     *
     * @covers \Framework\Service\InfoChecker::setService
     *
     * @dataProvider dpSetServiceOk
     *
     * @param string $case
     * @param string $serviceName
     * @param array $serviceInfo
     */
    public function testSetServiceOk($case, $serviceName, $serviceInfo) {
        $InfoChecker = new InfoChecker();
        try {
            $InfoChecker->setService($serviceName, $serviceInfo);
        } catch (\Exception $Exception) {
            $this->fail($case . ': ' . $Exception->getMessage());
        }
    }

    /**
     * Тестирует случай вызова, приводящего к ReflectionException
     *
     * @covers \Framework\Service\InfoChecker::setService
     *
     * @expectedException \ReflectionException
     */
    public function testSetServiceReflectionException() {
        $InfoChecker = new InfoChecker();
        $InfoChecker->setService('serviceA', [
            'class' => TestServiceA::class,
            'method' => 'mambaNambaFive',
            'args' => [],
        ]);
    }

    /**
     * @return array данные для теста testSetServiceOk
     */
    public function dpSetServiceOk() {
        return [
            [
                'Простой вызов с массивом параметров',
                'container',
                [
                    'class' => TestServiceA::class,
                    'args' => [
                        'site',
                        'config',
                    ],
                ],
            ],
            [
                'Простой вызов c одним параметром',
                'container',
                [
                    'class' => TestServiceA::class,
                    'args' => 'site',
                ],
            ],
        ];
    }
}