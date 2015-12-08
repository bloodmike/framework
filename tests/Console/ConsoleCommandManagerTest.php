<?php

namespace Framework\TestCases\Command;

use Framework\Command\Argument;
use Framework\Command\Console\ConsoleCommandManager;
use PHPUnit_Framework_TestCase;

/**
 * Набор тестов менеджера консольных команд
 *
 * @covers \Framework\Console\ConsoleCommandManager
 *
 * @author mkoshkin
 */
class ConsoleCommandManagerTest extends PHPUnit_Framework_TestCase {

    /**
     * Тестирует парсинг переданных через консоль аргументов
     *
     * @covers \Framework\Console\ConsoleCommandManager::parseArgs
     *
     * @dataProvider dpParseArgs
     *
     * @param array $argv
     * @param array $Arguments
     * @param array $expectedData
     */
    public function testParseArgs(array $argv, array $Arguments, array $expectedData) {
        array_unshift($argv, 'index.php');
        $ConsoleCommandManager = new ConsoleCommandManager($argv);
        $ArgumentsData = $ConsoleCommandManager->parseArgs($Arguments);
        $this->assertEquals($expectedData, $ArgumentsData->getAll());
    }

    /**
     * @return array данные для тест testParseArgs
     */
    public function dpParseArgs() {
        return [
            [
                [],
                [],
                [],
            ],
            [
                ['--help', '2'],
                [
                    Argument::create('help', '', '', false),
                ],
                [
                    'help' => 1,
                ],
            ],
            [
                ['--help', '-l', '-t'],
                [
                    Argument::create('help', '', '', false),
                    Argument::create('lol', 'l', '', true),
                    Argument::create('top', '', '', true),
                ],
                [
                    'help' => 1,
                    'lol' => null,
                ],
            ],
            [
                ['--help', '-l', 'happy', '-t', '-l', 'mamma'],
                [
                    Argument::create('help', '', '', false),
                    Argument::create('lol', 'l', '', true),
                    Argument::create('top', 't', '', true),
                ],
                [
                    'help' => 1,
                    'lol' => ['happy', 'mamma'],
                    'top' => null,
                ],
            ],
        ];
    }
}