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
    private $routes = null;
    
    /**
     * @var string[]
     */
    private $domains;
    
    /**
     * @var Container
     */
    private $container;
    
    /**
     * @var string
     */
    private $projectRootPrefix;
    
    /**
     * @param string[]  $domains
     * @param Container $container
     */
    public function __construct(array $domains, Container $container) {
        $this->routes = array();
        $this->domains = $domains;
        $this->container = $container;
        $this->projectRootPrefix = '';
        if ($container->hasParameter('root_prefix')) {
            $this->projectRootPrefix = $container->getParameter('root_prefix');
        }
    }
    
    /**
     * @param array $routesConfig
     * 
     * @return $this
     */
    public function setRoutes(array $routesConfig) {
        foreach ($routesConfig as $routeName => $routeData) {
            if (!is_array($routeData)) {
                continue;
            }
            if ($this->projectRootPrefix != ''){
                $routeData['uri'] = $this->projectRootPrefix . (array_key_exists('uri', $routeData) ? '' : $routeData['uri']);
            }
            
            $this->routes[$routeName] = Route::createFromArray($routeName, $routeData, $this->domains);
        }
        
        return $this;
    }
    
    /**
     * @param string $url
     * @param string $domain
     * @param string $requestMethod
     * 
     * @return RouteResult
     * 
     * @throws RuntimeException
     */
    public function execute($url, $domain, $requestMethod) {
        $domainsReverseMap = array_flip($this->domains);
        if (!array_key_exists($domain, $domainsReverseMap)) {
            throw new RuntimeException('Домен [' . $domain . '] не поддерживается');
        }
        $this->container->set('current_domain_name', $domainsReverseMap[$domain]);
        
        foreach ($this->routes as $route) {
            $routeResult = $route->match($url, $domain, $requestMethod);
            if ($routeResult !== null) {
                return $routeResult;
            }
        }
        
        throw new RuntimeException('Контроллер не найден');
    }
    
    /**
     * @param string    $routeName
     * @param array     $parameters
     * @param bool      $withDomain
     * 
     * @return string
     * 
     * @throws InvalidArgumentException
     */
    public function generate($routeName, array $parameters = array(), $withDomain = false) {
        if (!array_key_exists($routeName, $this->routes)) {
            throw new InvalidArgumentException('Не найдена ссылка [' . $routeName . ']');
        }
        
        $currentDomain = '';
        if ($this->container->has('current_domain_name')) {
            $currentDomain = $this->domains[$this->container->get('current_domain_name')];
        }
        
        $route = $this->routes[$routeName];
        return $route->build($parameters, ($withDomain || $route->getDomain() != $currentDomain));
    }
}
