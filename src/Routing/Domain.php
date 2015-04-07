<?php

namespace Framework\Routing;

/**
 * Объект домена.
 * Содержит данные о домене/поддомене и о том, к какому проектному домену относится объект
 *
 * @author mkoshkin
 */
class Domain {
    
    /**
     * @var string имя домена в проекте, если пустая строка - проектный домен не определен
     */
    private $domainName;
    
    /**
     * @var string домен
     */
    private $domain;
    
    /**
     * @var string поддомен
     */
    private $subdomain;
    
    /**
     * @param string $domainName имя домена в проекте
     * @param string $domain домен
     * @param string $subdomain поддомен
     */
    public function __construct($domainName, $domain, $subdomain) {
        $this->domainName = $domainName;
        $this->domain = $domain;
        $this->subdomain = $subdomain;
    }
    
    /**
     * Домен
     * 
     * @return string
     */
    public function getDomain() {
        return $this->domain;
    }
    
    /**
     * Имя проектного домена
     * 
     * @return string
     */
    public function getDomainName() {
        return $this->domainName;
    }
    
    /**
     * Полное доменное имя (с поддоменом)
     * 
     * @return string
     */
    public function getFullDomain() {
        return ($this->subdomain != '' ? $this->subdomain . '.' : '') . $this->domain;
    }
    
    /**
     * Поддомен
     * 
     * @return string
     */
    public function getSubdomain() {
        return $this->subdomain;
    }
}
