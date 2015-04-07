<?php

namespace Framework\Routing;

use Framework\Service\Config;
use Framework\Service\Container;
use InvalidArgumentException;

/**
 * Объект для определения домена
 *
 * @author mkoshkin
 */
class DomainSelector {
    /**
     * Поле для хранения объекта с текущим доменом
     */
    const CONTAINER_CURRENT_DOMAIN = 'current_domain';
    
    /**
     * @var Config
     */
    private $Config;
    
    /**
     * @var Container
     */
    private $Container;
    
    /**
     * @param Config $Config
     * @param Container $Container
     */
    public function __construct(Config $Config, Container $Container) {
        $this->Config = $Config;
        $this->Container = $Container;
    }
    
    /**
     * @param string $fullDomainString полный домен
     * 
     * @return Domain объект с данными домена
     */
    public function createDomain($fullDomainString) {
        $to = [];
        preg_match('/(?:([a-z\d\-_]+)\.)?([a-z\d\-]+\.[a-z]{2,5})$/', $fullDomainString, $to);
        
        $domains = $this->Config->get('domains');
        foreach ($domains as $domainName => $domain) {
            if ($domain == $to[2]) {
                return new Domain($domainName, $domain, $to[1]);
            }
        }
        // если домен не найден - возвращаем домен по умолчанию
        $defaultDomain = $this->Config->get('default_domain');
        return new Domain('', $domains[$defaultDomain], $to[1]);
    }
    
    /**
     * @param string $domainName проектное имя домена
     * @param string $subdomain поддомен, если требуется
     * 
     * @throws InvalidArgumentException если в проекте нет указанного домена
     * 
     * @return string строка домена
     */
    public function getDomainString($domainName, $subdomain = '') {
        $domains = $this->Config->get('domains');
        if (!array_key_exists($domainName, $domains)) {
            throw new InvalidArgumentException('Domain name [' . $domainName . '] undefined');
        }
        
        return ($subdomain != '' ? $subdomain . '.' : '') . $domains[$domainName];
    }
    
    /**
     * Записывает текущий домен в контейнер
     * 
     * @param Domain $Domain текущий домен
     * 
     * @return Domain переданный домен
     */
    public function setCurrentDomain(Domain $Domain) {
        $this->Container->set(self::CONTAINER_CURRENT_DOMAIN, $Domain);
        return $Domain;
    }
    
    /**
     * Получить из контейнера текущий домен
     * 
     * @return Domain|null
     */
    public function getCurrentDomain() {
        if ($this->Container->has(self::CONTAINER_CURRENT_DOMAIN)) {
            return $this->Container->get(self::CONTAINER_CURRENT_DOMAIN);
        }
        return null;
    }
}
