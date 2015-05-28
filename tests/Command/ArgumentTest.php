<?php

namespace Framework\TestCases\Command;

use Framework\Command\Argument;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

/**
 * Тесты методов параметра команды
 * 
 * @covers \Framework\Command\Argument
 *
 * @author mkoshkin
 */
class ArgumentTest extends PHPUnit_Framework_TestCase {
    
    /**
     * Тестирует попытку назначить некорректное имя параметру
     * 
     * @covers \Framework\Command\Argument::setName
     * 
     * @dataProvider dpTestSetBadName
     * 
     * @param string $name имя параметра
     */
    public function testSetNameBad($name) {
        try {
            $Argument = new Argument();
            $Argument->setName($name);
            $this->fail('Удалось назначить некорректное имя параметру');
        } catch (InvalidArgumentException $InvalidArgumentException) {

        }
    }
    
    /**
     * @return array данные для теста testSetBadName
     */
    public function dpTestSetBadName() {
        return [
            [''],
            ['a'],
            ['--zyid'],
            ['-123mogo'],
            [Argument::HELP_NAME],
        ];
    }
    
    /**
     * Тестирует выставление аргументу корректного имени
     * 
     * @covers \Framework\Command\Argument::setName
     * @covers \Framework\Command\Argument::getName
     */
    public function testSetNameOk() {
        $Argument = new Argument();
        $Argument->setName('ab');
        $Argument->setName('01');
        $Argument->setName('delta2');
        $this->assertEquals('delta2', $Argument->getName());
    }
    
    /**
     * Тестирует попытку назначить некорректное короткое имя параметру
     * 
     * @covers \Framework\Command\Argument::setShortName
     * 
     * @dataProvider dpSetShortNameBad
     * 
     * @param string $shortName некорректное короткое имя параметра
     */
    public function testSetShortNameBad($shortName) {
        try {
            $Argument = new Argument();
            $Argument->setShortName($shortName);
            $this->fail('Удалось назначить некорректное короткое имя параметру');
        } catch (InvalidArgumentException $InvalidArgumentException) {

        }
    }
    
    /**
     * @return array данные для теста testSetShortNameBad
     */
    public function dpSetShortNameBad() {
        return [
            [''],
            ['*'],
            ['-a'],
            ['aa'],
            ['90a'],
        ];
    }
    
    /**
     * Тестирует назначение короткого имени параметру
     * 
     * @covers \Framework\Command\Argument::setShortName
     * @covers \Framework\Command\Argument::getShortName
     */
    public function testSetShortNameOk() {
        $Argument = new Argument();
        $Argument->setShortName('0');
        $Argument->setShortName('A');
        $Argument->setShortName('c');
        $this->assertEquals('c', $Argument->getShortName());
    }
    
    
    /**
     * Тестирует назначение описания параметру
     * 
     * @covers \Framework\Command\Argument::setDescription
     * @covers \Framework\Command\Argument::getDescription
     */
    public function testSetDescription() {
        $Argument = new Argument('Description');
        $this->assertEquals('Description', $Argument->getDescription());
        
        $Argument->setDescription('Myth');
        $this->assertEquals('Myth', $Argument->getDescription());
    }
    
    /**
     * Тестирует парсинг данных из $argv
     * 
     * @covers \Framework\Command\Argument::parseData
     * 
     * @dataProvider dpParseData
     * 
     * @param array $args - данные $argv
     * @param array $expected - ожидаемый результат вызова
     */
    public function testParseData(array $args, array $expected) {
        $this->assertEquals($expected, Argument::parseData($args)->getAll());
    }
    
    /**
     * @return array источник данных для теста testParseData
     */
    public function dpParseData() {
        return [
            [
                ["index.php"],
                []
            ],
            [
                ["index.php", "-a"],
                [
                    "a" => null
                ]
            ],
            [
                ["test.php", "-a", "18", "-a", "--bro", "--gamma", "x", "-2", "alfa"],
                [
                    "a"     => ["18", null],
                    "bro"   => null,
                    "gamma" => "x",
                    "2"     => "alfa"
                ]
            ],
        ];
    }
    
    /**
     * Тестирует получение "лучшего" имени параметра
     * 
     * @covers \Framework\Command\Argument::getBestName
     */
    public function testGetBestName() {
        $Argument = new Argument();
        $this->assertEquals("", $Argument->getBestName());
        $this->assertEquals("s", $Argument->setShortName("s")->getBestName());
        $this->assertEquals("long", $Argument->setName("long")->getBestName());
    }
}
