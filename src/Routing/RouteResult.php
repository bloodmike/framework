<?php

namespace Framework\Routing;

/**
 * Description of RouteResult
 *
 * @author mkoshkin
 */
class RouteResult {

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var string
     */
    private $subDomain;
    
    /**
     * @var string
     */
    private $className;
    
    /**
     * @var string 
     */
    private $classMethodName;
    
    /**
     * @var string[]
     */
    private $parameters;

    /**
     * @var bool
     */
    private $https;
    
    /**
     * 
     * @param string $routeName
     * @param string $subDomain
     * @param string $className
     * @param string $classMethodName
     * @param array $parameters
     * @param bool $https
     */
    public function __construct($routeName, $subDomain, $className, $classMethodName, array $parameters, $https) {
        $this->routeName = $routeName;
        $this->subDomain = $subDomain;
        $this->className = $className;
        $this->classMethodName = $classMethodName;
        $this->parameters = $parameters;
        $this->https = $https;
    }
    
    /**
     * @return string
     */
    public function getClassName() {
        return $this->className;
    }
    
    /**
     * @return string
     */
    public function getClassMethodName() {
        return $this->classMethodName;
    }

    /**
     * @return bool
     */
    public function getHttps() {
        return $this->https;
    }

    /**
     * @return array[string]
     */
    public function getParameters() {
        return $this->parameters;
    }
    
    /**
     * @return string
     */
    public function getRouteName() {
        return $this->routeName;
    }

    /**
     * @return string
     */
    public function getSubDomain() {
        return $this->subDomain;
    }
}
