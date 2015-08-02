<?php

namespace Framework\Routing;

use InvalidArgumentException;

/**
 * Маршрут.
 * Логически связывает одну ссылку / формат запроса с контроллером системы.
 *
 * @author mkoshkin
 */
class Route {
    
    /**
     * @var string 
     */
    private $name = '';
    
    /**
     * @var string[]
     */
    private $parameters = array();
    
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
     * @deprecated лучше переходить на использование domainName
     *
     * @var string
     */
    private $domain = '';

    /**
     * @var string
     */
    private $domainName = '';
    
    /**
     * @param array $parameters
     * @param bool $withDomain
     * @param string $subDomain
     * 
     * @return string
     * 
     * @throws InvalidArgumentException
     */
    public function build(array $parameters, $withDomain = false, $subDomain = '') {
        $url = $this->uri;
        
        foreach ($this->parameters as $parameterName) {
            if (!array_key_exists($parameterName, $parameters)) {
                throw new InvalidArgumentException('Не передан параметр ссылки [' . $parameterName . ']');
            }
            
            $url = str_replace('[' . $parameterName . ']', $parameters[$parameterName], $url);
        }
        
        return ($withDomain ? 'http://' . ($subDomain != '' ? $subDomain.'.' : '') . $this->domain : '') . $url;
    }


    /**
     * @param string $name
     * @param array $parameters
     * @param string[] $domains
     * 
     * @return Route
     */
    public static function createFromArray($name, array $parameters, array $domains) {
        $Route = new self();
        $Route->name = $name;
        if (array_key_exists('uri', $parameters)) {
            $Route->uri = $parameters['uri'];
        }
        
        $classParts = explode(':', $parameters['run'], 2);
        if (count($classParts) == 2 && $classParts[1] != '') {
            $Route->classMethodName = $classParts[1];
        }
        $Route->className = $classParts[0];
        if (array_key_exists('method', $parameters) && $parameters['method'] != '') {
            $Route->method = $parameters['method'];
        }
        
        $domainName = 'default';
        if (array_key_exists('domain', $parameters) && $parameters['domain'] != '') {
            $domainName = $parameters['domain'];
        }
        $Route->domainName = $domainName;
        $Route->domain = $domains[$domainName];
        
        $partsTo = array();
        preg_match_all('/\[([\da-z_]+)\]/ui', $Route->uri, $partsTo);
        foreach ($partsTo[1] as $part) {
            $Route->parameters[] = $part;
        }
        
        return $Route;
    }

    /**
     * @return string
     */
    public function getDomainName() {
        return $this->domainName;
    }
    
    /**
     * @deprecated лучше переходить на использование getDomainName
     *
     * @return string
     */
    public function getDomain() {
        return $this->domain;
    }
    
    /**
     * @param string $path
     * @param string $method
     * @param string $subDomain
     * 
     * @return Route|null
     */
    public function match($path, $method, $subDomain) {
        if ($this->method !== null && $method != $this->method) {
            return null;
        }
        if ($this->uri != '') {
            $regexp = '/^' . str_replace('/', '\\/', preg_replace('/\[([\da-z_]+)\]/ui', '([\da-z_]+)', $this->uri)) . '$/ui';
        } else {
            $regexp = '/.*/ui';
        }
        
        $routeResult = null;
        $resultTo = array();
        if (preg_match($regexp, $path, $resultTo)) {
            // если переданный путь соврадает с шаблоном
            $parameters = [];
            $index = 0;
            foreach ($this->parameters as $parameter) {
                $parameters[ $parameter ] = $resultTo[$index + 1];
                $index++;
            }
            
            $routeResult = new RouteResult($this->name, $subDomain, $this->className, $this->classMethodName, $parameters);
        }
        
        return $routeResult;
    }
}
