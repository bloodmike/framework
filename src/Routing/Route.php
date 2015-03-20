<?php

namespace Framework\Routing;

use InvalidArgumentException;

/**
 * Description of Route
 *
 * @author mkoshkin
 */
class Route {
    
    /**
     * @var string 
     */
    private $name = '';
    
    /**
     * @var array[string]
     */
    private $parameters = [];
    
    /**
     * @var string 
     */
    private $uri = '';
    
    /**
     * @var string|null 
     */
    private $method = null;
    
    /**
     * @var string
     */
    private $className = '';
    
    /**
     * @var string
     */
    private $classMethodName = 'work';
    
    /**
     * @var string
     */
    private $domain = '';
    
    /**
     * @param array $parameters
     * @param bool $withDomain
     * 
     * @return string
     * 
     * @throws InvalidArgumentException
     */
    public function build(array $parameters, $withDomain = false) {
        $url = $this->uri;
        
        foreach ($this->parameters as $parameterName) {
            if (!array_key_exists($parameterName, $parameters)) {
                throw new InvalidArgumentException('Не передан параметр ссылки [' . $parameterName . ']');
            }
            
            $url = str_replace('[' . $parameterName . ']', $parameters[$parameterName], $url);
        }
        
        return ($withDomain ? 'http://' . $this->domain : '') . $url;
    }


    /**
     * @param string $name
     * @param array $parameters
     * @param string[] $domains
     * 
     * @return Route
     */
    public static function createFromArray($name, array $parameters, array $domains) {
        $route = new self();
        $route->name = $name;
        if (array_key_exists('uri', $parameters)) {
            $route->uri = $parameters['uri'];
        }
        
        $classParts = explode(':', $parameters['run'], 2);
        if (count($classParts) == 2 && $classParts[1] != '') {
            $route->classMethodName = $classParts[1];
        }
        $route->className = $classParts[0];
        if (array_key_exists('method', $parameters) && $parameters['method'] != '') {
            $route->method = $parameters['method'];
        }
        
        $domainName = 'default';
        if (array_key_exists('domain', $parameters) && $parameters['domain'] != '') {
            $domainName = $parameters['domain'];
        }
        $route->domain = $domains[$domainName];
        
        $partsTo = [];
        preg_match_all('/\[([\da-z_]+)\]/ui', $route->uri, $partsTo);
        foreach ($partsTo[1] as $part) {
            $route->parameters[] = $part;
        }
        
        return $route;
    }
    
    /**
     * @return string
     */
    public function getDomain() {
        return $this->domain;
    }
    
    /**
     * @param string $path
     * @param string $domain
     * @param string $method
     * 
     * @return Route|null
     */
    public function match($path, $domain, $method) {
        if (($this->method !== null && $method != $this->method) || $domain != $this->domain) {
            return null;
        }
        if ($this->uri != '') {
            $regexp = '/^' . str_replace('/', '\\/', preg_replace('/\[([\da-z_]+)\]/ui', '([\da-z_]+)', $this->uri)) . '$/ui';
        }
        else {
            $regexp = '/.*/ui';
        }
        
        $routeResult = null;
        $resultTo = [];
        if (preg_match($regexp, $path, $resultTo)) {
            // если переданный путь соврадает с шаблоном
            $parameters = [];
            $index = 0;
            foreach ($this->parameters as $parameter) {
                $parameters[ $parameter ] = $resultTo[$index + 1];
                $index++;
            }
            
            $routeResult = new RouteResult($this->name, $this->className, $this->classMethodName, $parameters);
        }
        
        return $routeResult;
    }
}
