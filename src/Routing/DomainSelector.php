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
        $FullDomainString = new DomainString($fullDomainString);
        
        $domains = $this->Config->get('domains');
        foreach ($domains as $domainName => $domainString) {
            $DomainString = new DomainString($domainString);
            $subDomain = $DomainString->getSubDomain($FullDomainString);
            
            if (is_string($subDomain)) {
                return new Domain($domainName, $domainString, $subDomain);
            }
        }
        // TODO: бросать исключение если домен не найден
        // если домен не найден - возвращаем домен по умолчанию
        $defaultDomain = $this->Config->get('default_domain');
        return new Domain('', $domains[$defaultDomain], '');
    }
    
    /**
     * @return Domain домен по умолчанию
     */
    public function createDefaultDomain() {
        $defaultDomainName = $this->Config->get('default_domain');
        $domains = $this->Config->get('domains');
        return new Domain($defaultDomainName, $domains[$defaultDomainName], '');
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
