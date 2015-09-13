<?php

namespace Framework\Routing;

use Framework\Service\Container;
use InvalidArgumentException;
use RuntimeException;

/**
 * Роутер
 * Связывает ссылки с контроллерами
 *
 * @author mkoshkin
 */
class Router {
    /**
     * @var Route[] список поддерживаемых маршрутов
     */
    private $Routes = null;

    /**
     * @var Route[][] сгруппированный по доменам список маршрутов
     */
    private $GrouppedRoutes = null;

    /**
     * @var string[]
     */
    private $domains;

    /**
     * @var string[]
     */
    private $plainDomains;

    /**
     * @var Container
     */
    private $Container;
    
    /**
     * @var string
     */
    private $projectRootPrefix;

    /**
     * @var bool
     */
    private $https;

    /**
     * @param string[]  $domains
     * @param Container $Container
     */
    public function __construct(array $domains, Container $Container) {
        $this->Routes = array();
        $this->GrouppedRoutes = array();
        $this->https = false;
        $this->domains = $domains;
        $this->plainDomains = array();
        foreach ($this->domains as $domainName => $domain) {
            if (substr($domain, 0, 2) != '*.') {
                $this->plainDomains[$domainName] = $domain;
            }
        }

        $this->Container = $Container;
        $this->projectRootPrefix = '';
        if ($Container->hasParameter('root_prefix')) {
            $this->projectRootPrefix = $Container->getParameter('root_prefix');
        }
    }

    /**
     * @param bool $https
     *
     * @return $this
     */
    public function setHttps($https) {
        $this->https = $https;
        return $this;
    }

    /**
     * @param array $routesConfig
     * 
     * @return $this
     */
    public function setRoutes(array $routesConfig) {
        $this->Routes = array();
        $this->GrouppedRoutes = array();

        foreach ($routesConfig as $routeName => $routeData) {
            if (!is_array($routeData)) {
                continue;
            }
            if ($this->projectRootPrefix != ''){
                $routeData['uri'] = $this->projectRootPrefix . (array_key_exists('uri', $routeData) ? $routeData['uri'] : '');
            }

            $Route = Route::createFromArray($routeName, $routeData, $this->domains);
            $domainName = $Route->getDomainName();

            if (!array_key_exists($domainName, $this->GrouppedRoutes)) {
                $this->GrouppedRoutes[$domainName] = array();
            }

            $this->Routes[$routeName] = $Route;
            $this->GrouppedRoutes[$domainName][] = $Route;
        }
        
        return $this;
    }

    /**
     * @param string $domain строка домена
     *
     * @return string[] пара [имя домена, поддомен]
     *
     * @throws RuntimeException если указанный домен не совпадает ни с одним доменом системы
     */
    public function getDomainName($domain) {
        foreach ($this->domains as $domainName => $domainString) {
            if (substr($domainString, 0, 2) == '*.') {
                $rootDomainString = substr($domainString, 2);

                if (substr($domain, -strlen($rootDomainString) - 1) == '.' . $rootDomainString) {
                    return array($domainName, substr($domain, 0, -strlen($rootDomainString) - 1));
                }

            } elseif ($domain == $domainString) {
                return array($domainName, '');
            }
        }

        throw new RuntimeException('Домен [' . $domain . '] не поддерживается');
    }

    /**
     * @param string $url запрашиваемая ссылка
     * @param string $domain домен, на который поступил запрос
     * @param string $requestMethod метод запроса: GET, POST, ...
     * 
     * @return RouteResult обработанный маршрут
     * 
     * @throws RuntimeException
     */
    public function execute($url, $domain, $requestMethod) {
        list($domainName, $subDomain) = $this->getDomainName($domain);
        $this->Container->set('current_domain_name', $domainName);
        $this->Container->set('current_subdomain', $subDomain);

        if (array_key_exists($domainName, $this->GrouppedRoutes)) {
            foreach ($this->GrouppedRoutes[$domainName] as $Route) {
                $RouteResult = $Route->match($url, $requestMethod, $subDomain, $this->https);
                if ($RouteResult !== null) {
                    return $RouteResult;
                }
            }
        }
        
        throw new RuntimeException('Контроллер не найден');
    }
    
    /**
     * @param string    $routeName
     * @param array     $parameters
     * @param bool      $withDomain
     * @param string    $subDomain
     * 
     * @return string
     * 
     * @throws InvalidArgumentException
     */
    public function generate($routeName, array $parameters = array(), $withDomain = false, $subDomain = '') {
        if (!array_key_exists($routeName, $this->Routes)) {
            throw new InvalidArgumentException('Не найдена ссылка [' . $routeName . ']');
        }
        
        $currentDomain = $this->getFullCurrentDomain();

        $Route = $this->Routes[$routeName];
        $newDomain = $this->getFullDomain($Route->getDomainName(), $subDomain);

        return $Route->build($parameters, ($withDomain || $newDomain != $currentDomain) ? $newDomain : '', $this->https);
    }

    /**
     * @return string полный текущий домен
     *
     * @throws \RuntimeException если текущий домен/поддомен не выбраны
     */
    public function getFullCurrentDomain() {
        return $this->getFullDomain(
            $this->Container->get('current_domain_name'),
            $this->Container->get('current_subdomain')
        );
    }

    /**
     * Формирует домен по указанному имени и поддомену
     *
     * @param string $domainName имя домена
     * @param string $subDomain поддомен (если требуется и если поддерживается указанным доменом)
     *
     * @return string полный домен
     *
     * @throws \RuntimeException если домен с указанным именем не поддерживается
     */
    public function getFullDomain($domainName, $subDomain = '') {
        if (!array_key_exists($domainName, $this->domains)) {
            throw new \RuntimeException('Домен [' . $domainName . '] не поддерживается');
        }

        $subDomain .= ($subDomain != '') ? '.' : '';

        return str_replace('*.', $subDomain, $this->domains[$domainName]);
    }
}
