<?php

namespace Framework;

use Framework\Response\JsonResponse;
use Framework\Response\RedirectResponse;
use Framework\Response\Response;
use Framework\Routing\Router;
use Framework\Service\Container;
use JsonSerializable;
use Framework\Context;

/**
 * Основной класс контроллера
 *
 * @author mkoshkin
 */
class Controller {
    /**
     * @var Context контекст вызова страницы (POST или GET данные)
     */
    protected $context;

    /**
     * @var Context ссылочный контекст (всегда содержит данные из GET)
     */
    protected $urlContext;
    
    /**
     * @var string имя маршрута, полученного роутером по текущей ссылке
     */
    protected $routeName;
    
    /**
     * @var Router роутер
     */
    protected $router;

    /**
     * @var Container контейнер сервисов
     */
    protected $container;


    /**
     * @param Context $context контекст вызова
     * @param Router $router роутер
     * @param string $routeName имя маршрута, полученного по ссылке из роутера
     * @param Container $container контейнер сервисов
     */
    public final function __construct(Context $context, Router $router, $routeName, Container $container) {
        $this->context = $context;
        $this->urlContext = new Context($_GET);
        $this->router = $router;
        $this->routeName = $routeName;
        $this->container = $container;
    }
    
    /**
     * @param JsonSerializable $data сериализуемые в json данные
     * 
     * @return JsonResponse отдать json-ответ с переданными данными
     */
    public function json($data) {
        return new JsonResponse($data);
    }
    
    /**
     * 
     * @param string $url ссылка, по которой нужно перенаправить пользователя
     * @param int $code код ответа (301 по умолчанию)
     * 
     * @return RedirectResponse отдать редирект на указанную страницу
     */
    public function redirect($url, $code = 301) {
        return new RedirectResponse($url, $code);
    }
    
    /**
     * @param string $data текстовые данные
     * @param int $code код ответа (200 по умолчанию)
     * 
     * @return Response отдать указанные текстовые данные
     */
    public function text($data = '', $code = 200) {
        return new Response($data, $code);
    }
    
    /**
     * Действие после определения метода контроллера и до вызова этого метода
     * 
     * @return Response|null если вернуть не null, метод контроллера не будет вызван
     */
    public function beforeMethodCall() {
        return null;
    }
    
    /**
     * @param string $className
     * @param Context $context
     * @param Router $router
     * @param string $routeName
     * @param Container $container
     * 
     * @return Controller
     */
    public static function createOne($className, Context $context, Router $router, $routeName, Container $container) {
        return new $className($context, $router, $routeName, $container);
    }
}
