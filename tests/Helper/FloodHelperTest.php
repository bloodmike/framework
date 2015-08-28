<?php

namespace Helper;

use Framework\Helper\FloodHelper;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Framework\Memcache\MemcacheObj;

/**
 * Тесты хэлпера проверки повторяющихся действий
 *
 * @author mkoshkin
 *
 * @covers \Framework\Helper\FloodHelper
 */
class FloodHelperTest extends PHPUnit_Framework_TestCase {

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createMemcacheObjMock() {
        return $this->getMockBuilder(MemcacheObj::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @covers \Framework\Helper\FloodHelper::checkFlood
     */
    public function testCheckFloodOk() {

        $MemcacheObjMock = $this->createMemcacheObjMock();
        $MemcacheObjMock
            ->expects($this->at(0))
            ->method('add')
            ->withAnyParameters()
            ->willReturn(true);

        $MemcacheObjMock
            ->expects($this->at(1))
            ->method('add')
            ->withAnyParameters()
            ->willReturn(false);

        $MemcacheObjMock
            ->expects($this->at(2))
            ->method('increment')
            ->withAnyParameters()
            ->willReturn(2);

        $FloodHelper = new FloodHelper($MemcacheObjMock);
        $this->assertTrue($FloodHelper->checkFlood('ttt', '1/60,2/3600'));
    }

    /**
     * @covers \Framework\Helper\FloodHelper::checkFlood
     */
    public function testCheckFloodFail() {

        $MemcacheObjMock = $this->createMemcacheObjMock();
        $MemcacheObjMock
            ->expects($this->at(0))
            ->method('add')
            ->withAnyParameters()
            ->willReturn(false);

        $MemcacheObjMock
            ->expects($this->at(1))
            ->method('increment')
            ->withAnyParameters()
            ->willReturn(4);

        $FloodHelper = new FloodHelper($MemcacheObjMock);
        $this->assertFalse($FloodHelper->checkFlood('ttt', '2/60'));
    }
}