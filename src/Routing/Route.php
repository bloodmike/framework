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
     * @var string[]
     */
    private $parametersRequirements = array();

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
     * @param string $domain
     * @param bool $https
     * 
     * @return string
     * 
     * @throws InvalidArgumentException
     */
    public function build(array $parameters, $domain = '', $https = false) {
        $url = $this->uri;
        
        foreach ($this->parameters as $parameterName) {
            if (!array_key_exists($parameterName, $parameters)) {
                throw new InvalidArgumentException('Не передан параметр ссылки [' . $parameterName . ']');
            }
            
            $url = str_replace('[' . $parameterName . ']', $parameters[$parameterName], $url);
        }

        if ($https) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }

        return ($domain != '' ? $protocol . $domain : '') . $url;
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

        if (array_key_exists('requirements', $parameters) && is_array($parameters['requirements'])) {
            $requirements = $parameters['requirements'];
            foreach ($Route->parameters as $parameterName) {
                if (array_key_exists($parameterName, $requirements)) {
                    $Route->parametersRequirements[$parameterName] = $requirements[$parameterName];
                }
            }
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
     * @param bool $https
     * 
     * @return RouteResult|null
     */
    public function match($path, $method, $subDomain, $https = false) {
        if ($this->method !== null && $method != $this->method) {
            return null;
        }

        $RouteResult = null;
        $resultTo = array();
        if ($this->matchUri($path, $resultTo)) {
            // если переданный путь совпадает с шаблоном
            $parameters = array();
            $index = 0;
            foreach ($this->parameters as $parameter) {
                $parameters[ $parameter ] = $resultTo[$index + 1];
                $index++;
            }
            
            $RouteResult = new RouteResult($this->name, $subDomain, $this->className, $this->classMethodName, $parameters, $https);
        }
        
        return $RouteResult;
    }

    /**
     * @return string регулярное выражение для проверки ссылки на соответствие маршруту
     */
    public function buildRegexp() {
        if (!$this->uri) {
            return '/.*/ui';
        }

        $replacementsFrom = array('/');
        $replacementsTo = array('\\/');

        foreach ($this->parameters as $parameterName) {
            if (array_key_exists($parameterName, $this->parametersRequirements)) {
                $replacement = $this->parametersRequirements[$parameterName];
                if ($replacement == 'n') {
                    $replacement = '[1-9][\d]*';
                }
            } else {
                $replacement = '[\da-z_]+';
            }
            $replacementsFrom[] = '[' . $parameterName . ']';
            $replacementsTo[] = '(' . $replacement . ')';
        }

        return '/^' . str_replace($replacementsFrom, $replacementsTo, $this->uri) . '$/ui';
    }

    /**
     * @param string $path
     * @param array &$resultTo
     *
     * @return bool
     */
    private function matchUri($path, array &$resultTo) {
        if (count($this->parameters) == 0) {
            return !$this->uri || $path == $this->uri;
        }

        $regexp = $this->buildRegexp();
        return preg_match($regexp, $path, $resultTo);
    }
}
