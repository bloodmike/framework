<?php

namespace Helper;

use Framework\Helper\ArrayHelper;
use PHPUnit_Framework_TestCase;

/**
 * Тесты хэлпера работы с массивами
 *
 * @author mkoshkin
 *
 * @covers \Framework\Helper\ArrayHelper
 */
class ArrayHelperTest extends PHPUnit_Framework_TestCase {
    /**
     * @covers \Framework\Helper\ArrayHelper::increment
     *
     * @dataProvider dpIncrement
     *
     * @param string $case
     * @param array $original
     * @param string|string[] $field
     * @param int|float $value
     * @param int|float $expectedResult
     * @param array $expectedArray
     */
    public function testIncrement($case, $original, $field, $value, $expectedResult, $expectedArray) {
        $this->assertEquals($expectedResult, ArrayHelper::increment($original, $field, $value), $case . ' (результат)');
        $this->assertEquals($expectedArray, $original, $case . ' (измененный массив)');
    }

    /**
     * @return array источник данных для теста testIncrement
     */
    public function dpIncrement() {
        return [
            [
                'Простой случай без поля',
                [
                    'a' => 't',
                ],
                'b',
                2,
                2,
                [
                    'a' => 't',
                    'b' => 2,
                ]
            ],
            [
                'Простой случай с полем',
                [
                    'a' => 't',
                    'c' => 8,
                ],
                'c',
                3,
                11,
                [
                    'a' => 't',
                    'c' => 11,
                ]
            ],
            [
                'Сложная вложенность без инициализации последнего элемента',
                [
                    'a' => 'X',
                    'c' => [
                        25 => 1,
                    ],
                ],
                ['c', 25],
                1,
                2,
                [
                    'a' => 'X',
                    'c' => [
                        25 => 2,
                    ],
                ]
            ],
            [
                'Сложная вложенность с инициализацией последнего элемента',
                [
                    'a' => 'X',
                    'c' => [
                        25 => [
                            't' => 0,
                        ],
                    ],
                ],
                ['c', 25, 'z'],
                5,
                5,
                [
                    'a' => 'X',
                    'c' => [
                        25 => [
                            't' => 0,
                            'z' => 5,
                        ],
                    ],
                ]
            ],
            [
                'Сложная вложенность с инициализацией промежуточных элементов',
                [
                    'a' => 'X',
                    'd' => [],
                ],
                ['d', 0, 'x'],
                1,
                1,
                [
                    'a' => 'X',
                    'd' => [
                        0 => [
                            'x' => 1,
                        ],
                    ],
                ]
            ],
            [
                'Сложная вложенность с переопределением промежуточных элементов',
                [
                    'a' => 'X',
                    'd' => [],
                ],
                ['a', 12, 13],
                3,
                3,
                [
                    'a' => [
                        12 => [
                            13 => 3,
                        ],
                    ],
                    'd' => [],
                ]
            ],
        ];
    }
}