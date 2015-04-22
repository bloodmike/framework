<?php

namespace Framework\TestCases\Routing;

use Framework\Routing\DomainString;
use PHPUnit_Framework_TestCase;

/**
 * Тесты класса доменной строки
 *
 * @covers \Framework\Routing\DomainString
 * 
 * @author mkoshkin
 */
class DomainStringTest extends PHPUnit_Framework_TestCase {
    
    
    /**
     * Тестирует неудачные попытки получить поддомен
     * 
     * @covers \Framework\Routing\DomainString::getSubDomain
     */
    public function testGetSubDomainNone() {
        $MainDomain = new DomainString('main.domain.de');
        
        $this->assertNull($MainDomain->getSubDomain(new DomainString('domain.de')));
        $this->assertNull($MainDomain->getSubDomain(new DomainString('domainer.de')));
        $this->assertNull($MainDomain->getSubDomain(new DomainString('maind.domain.de')));
    }
    
    /**
     * Тестирует удачные попытки получить поддомен
     * 
     * @covers \Framework\Routing\DomainString::getSubDomain
     */
    public function testGetSubDomainSuccess() {
        $MainDomain = new DomainString('main.domain.com');
        
        $this->assertEquals('', $MainDomain->getSubDomain($MainDomain));
        $this->assertEquals('www', $MainDomain->getSubDomain(new DomainString('www.main.domain.com')));
        $this->assertEquals('ale.www', $MainDomain->getSubDomain(new DomainString('ale.www.main.domain.com')));
    }
}
