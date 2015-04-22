<?php

namespace Framework\Routing;

/**
 * Строка домена.
 * Содержит логику для разделения домена на части и проверок, что один домен является поддоменом другого
 *
 * @author mkoshkin
 */
class DomainString {
    
    /**
     * @var string[] части домена (в обратном порядке)
     */
    private $domainParts;
    
    /**
     * @param string $domain строка домена
     */
    public function __construct($domain) {
        $this->domainParts = array_reverse(explode('.', $domain));
    }
    
    /**
     * @return string домен в виде строки
     */
    public function __toString() {
        return implode('.', array_reverse($this->domainParts));
    }
    
    /**
     * @param DomainString $DomainString домен
     * 
     * @return string|null если переданный домен совпадает с текущим - возвращается пустая строка,
     *                      если переданный домен является поддоменом текущего - возвращается строка с частями поддомена,
     *                      в остальных случаях возвращается null
     */
    public function getSubDomain(DomainString $DomainString) {
        if (count($this->domainParts) > count($DomainString->domainParts)) {
            return null;
        }
        
        for ($i = 0; $i < count($this->domainParts); $i++) {
            if ($this->domainParts[$i] != $DomainString->domainParts[$i]) {
                return null;
            }
        }
        
        return implode('.', array_reverse(array_slice($DomainString->domainParts, $i)));
    }
}
