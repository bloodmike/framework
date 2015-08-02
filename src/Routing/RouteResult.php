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
     * @var array[string]
     */
    private $parameters;
    
    /**
     * 
     * @param string $routeName
     * @param string $subDomain
     * @param string $className
     * @param string $classMethodName
     * @param array $parameters
     */
    public function __construct($routeName, $subDomain, $className, $classMethodName, array $parameters) {
        $this->routeName = $routeName;
        $this->subDomain = $subDomain;
        $this->className = $className;
        $this->classMethodName = $classMethodName;
        $this->parameters = $parameters;
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
