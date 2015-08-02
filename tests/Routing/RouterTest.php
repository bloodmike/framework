<?php

namespace Framework\TestCases\Routing;


use Framework\Routing\Router;
use Framework\Service\Container;

class RouterTest extends \PHPUnit_Framework_TestCase {

    /**
     * @return Router
     */
    private function createRouter() {
        $ContainerMock = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();

        return new Router([
            'default'   => 'test.com',
            'admin'     => 'adm.test.com',
            'my'        => '*.test.ru',
            'vi'        => 'test.ru',
        ], $ContainerMock);
    }

    /**
     * @covers \Framework\Routing\Router::getDomainName
     */
    public function testGetDomainName() {
        $Router = $this->createRouter();

        $this->assertEquals(['default', ''], $Router->getDomainName("test.com"));
        $this->assertEquals(['admin', ''], $Router->getDomainName("adm.test.com"));
        try {
            $Router->getDomainName('gamma.abs');
            $this->fail();
        } catch (\RuntimeException $RuntimeException) {

        }

        $this->assertEquals(['my', 'tt'], $Router->getDomainName('tt.test.ru'));
        $this->assertEquals(['my', 'abc.def'], $Router->getDomainName('abc.def.test.ru'));
        $this->assertEquals(['vi', ''], $Router->getDomainName('test.ru'));
    }


    public function testExecute() {
        $Router = $this->createRouter();

        $Router->setRoutes([
            'a' => [
                'domain' => 'vi',
                'uri' => '/',
                'run' => 'A:root',
            ],
            'b' => [
                'domain' => 'my',
                'uri' => '/',
                'run' => 'B:root',
            ],
            'c' => [
                'uri' => '/hi/',
                'run' => 'C:hi',
            ],
        ]);

        $this->assertEquals('a', $Router->execute('/', 'test.ru', 'GET')->getRouteName());

        $this->assertEquals('b', $Router->execute('/', 'alpha.test.ru', 'GET')->getRouteName());

        $this->assertEquals('c', $Router->execute('/hi/', 'test.com', 'GET')->getRouteName());

        try {
            $Router->execute('/hi/', 'gamma.test.com', 'GET');
            $this->fail();
        } catch (\RuntimeException $RuntimeException) {

        }
    }
}