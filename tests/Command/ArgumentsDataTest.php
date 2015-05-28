<?php

namespace Framework\TestCases\Command;

use Framework\Command\ArgumentsData;
use PHPUnit_Framework_TestCase;

/**
 * Тесты объектов со значениями параметров
 *
 * @covers \Framework\Command\ArgumentsData
 * 
 * @author mkoshkin
 */
class ArgumentsDataTest extends PHPUnit_Framework_TestCase {
    
    /**
     * Проверяет выставление / добавление значений
     * 
     * @covers \Framework\Command\ArgumentsData::add
     * @covers \Framework\Command\ArgumentsData::get
     * @covers \Framework\Command\ArgumentsData::getAll
     * @covers \Framework\Command\ArgumentsData::getNames
     */
    public function testSetterGetter() {
        $ArgumentsData = (new ArgumentsData())
                ->add('one', 'alfa')
                ->add('two', 'beta')
                ->add('two', 'gamma')
                ->add('omega', null);
        
        $this->assertEquals('alfa', $ArgumentsData->get('one'));
        $this->assertEquals(['beta', 'gamma'], $ArgumentsData->get('two'));
        $this->assertNull($ArgumentsData->get('three'));
        $this->assertEquals('', $ArgumentsData->get('three', ''));
        
        $this->assertEquals([
            'one'   => 'alfa',
            'two'   => ['beta', 'gamma'],
            'omega' => null
        ], $ArgumentsData->getAll());
        
        $this->assertEquals(['one', 'two', 'omega'], $ArgumentsData->getNames());
    }
    
    /**
     * Тестирует перенос данных из поля в поле
     * 
     * @covers \Framework\Command\ArgumentsData::merge
     */
    public function testMerge() {
        $ArgumentsData = (new ArgumentsData())
                ->add('one', 'alfa')
                ->add('two', 'beta')
                ->add('three', null)
                ->add('four', '1')
                ->add('four', '2');
        
        $ArgumentsData->merge('none', 'one');
        $this->assertEquals('alfa', $ArgumentsData->get('one'));
        
        $ArgumentsData->merge('one', 'two');
        $this->assertNull($ArgumentsData->get('one'));
        $this->assertEquals(['beta', 'alfa'], $ArgumentsData->get('two'));
        
        $ArgumentsData->merge('two', 'three');
        $this->assertEquals([null, 'beta', 'alfa'], $ArgumentsData->get('three'));
        
        $ArgumentsData->merge('three', 'four');
        $this->assertEquals(['1', '2', null, 'beta', 'alfa'], $ArgumentsData->get('four'));
        
        $ArgumentsData->merge('four', 'five');
        $this->assertEquals(['1', '2', null, 'beta', 'alfa'], $ArgumentsData->get('five'));
    }
    
    /**
     * Тестирует удаление данных из поля
     * 
     * @covers \Framework\Command\ArgumentsData::remove
     */
    public function testRemove() {
        $ArgumentsData = (new ArgumentsData())
                ->add('one', 'alfa')
                ->add('four', '1')
                ->add('four', '2');
        
        $ArgumentsData->remove('one');
        $this->assertNull($ArgumentsData->get('one'));
        
        $ArgumentsData->remove('four');
        $this->assertNull($ArgumentsData->get('four'));
    }
}
