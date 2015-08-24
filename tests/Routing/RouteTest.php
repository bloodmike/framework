<?php

namespace Framework\TestCases\Routing;

use Framework\Routing\Route;
use Framework\Routing\RouteResult;

/**
 * @author mkoshkin
 */
class RouteTest extends \PHPUnit_Framework_TestCase {

    /**
     *
     * @dataProvider dpTestCreate
     *
     * @covers \Framework\Routing\Route::match
     *
     * @param array $parameters
     * @param string $method
     * @param string $uri
     * @param bool $expectedResult
     * @param array $expectedParameters
     */
    public function testMatch(array $parameters, $method, $uri, $expectedResult, $expectedParameters = []) {
        $Route = Route::createFromArray('my_route', $parameters, array('default' => 'test.test'));
        $RouteResult = $Route->match($uri, $method, '');
        if (!$expectedResult) {
            $this->assertNull($RouteResult);
            return;
        }

        $this->assertInstanceOf(RouteResult::class, $RouteResult);
        $this->assertEquals($expectedParameters, $RouteResult->getParameters());
    }

    /**
     * @return array данные для теста testMatch
     */
    public function dpTestCreate() {
        return [
            [
                [
                    'uri' => '/',
                    'method' => 'GET',
                    'run' => '\Class::method',
                ],
                'GET',
                '/',
                true,
                []
            ],
            [
                [
                    'uri' => '/test/',
                    'method' => 'GET',
                    'run' => '\Class::method',
                ],
                'GET',
                '/test/',
                true,
                []
            ],
            [
                [
                    'uri' => '/test/',
                    'method' => 'GET',
                    'run' => '\Class::method',
                ],
                'GET',
                '/testers/',
                false,
            ],
            [
                [
                    'uri' => '/test/[d]/',
                    'method' => 'GET',
                    'run' => '\Class::method',
                    'requirements' => [
                        'd' => 'n'
                    ]
                ],
                'GET',
                '/test/123/',
                true,
                [
                    'd' => '123'
                ]
            ],
            [
                [
                    'uri' => '/test/[d]/',
                    'method' => 'GET',
                    'run' => '\Class::method',
                    'requirements' => [
                        'd' => 'n'
                    ]
                ],
                'GET',
                '/test/abc3/',
                false,
            ],
            [
                [
                    'uri' => '/test/[d]/[h]/',
                    'method' => 'POST',
                    'run' => '\Class::method',
                    'requirements' => [
                        'h' => 'abc|def',
                        'd' => 'n'
                    ]
                ],
                'POST',
                '/test/8/def/',
                true,
                [
                    'd' => '8',
                    'h' => 'def'
                ]
            ],
        ];
    }
}
