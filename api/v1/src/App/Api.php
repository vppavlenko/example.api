<?php

namespace nvs\api\v1\App;

use nvs\api\v1\Config\Config;
use nvs\api\v1\Middlewares\Auth;
use nvs\api\v1\Middlewares\Json;
use nvs\api\v1\Services\Iblock;
use nvs\api\v1\Services\SmsNotification;
use nvs\api\v1\Services\User;
use nvs\api\v1\Services\UserConfirmConstraints;
use nvs\api\v1\Services\UserDataConstraints;
use nvs\api\v1\Services\UserEmailConstraints;
use nvs\api\v1\Services\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

class Api
{
    private $routes;
    private $config;
    private $app;

    public function __construct($routes, $config, $silexApp)
    {
        $this->routes = $routes;
        $this->config = $config;
        $this->app = $silexApp;

        $this->initSilex();

        $this->registerConfig();
        $this->registerServices();
        $this->registerMiddlewares();
        $this->registerRoutes();
    }

    private function initSilex(): void
    {
        $this->app['debug'] = true;
    }

    private function registerConfig(): void
    {
        $this->app['config'] = $this->app->share(function () {
            return new Config($this->config);
        });
    }

    private function registerServices(): void
    {
        $this->app['services.user.constraints'] = $this->app->share(function () {
            return new UserDataConstraints;
        });

        $this->app['services.confirm.constraints'] = $this->app->share(function () {
            return new UserConfirmConstraints;
        });

        $this->app['services.email.constraints'] = $this->app->share(function () {
            return new UserEmailConstraints;
        });

        $this->app['services.user'] = $this->app->share(function () {
            return new User(new Iblock);
        });

        $this->app['services.validator'] = $this->app->share(function () {
            return new Validator(Validation::createValidator());
        });

        $this->app['services.sms'] = $this->app->share(function () {
            return new SmsNotification(new Iblock);
        });
    }

    private function registerMiddlewares(): void
    {
        $this->app['middleware.json'] = $this->app->share(function () {
            return new Json;
        });

        $this->app['middleware.auth'] = $this->app->share(function () {
            return new Auth;
        });
    }

    private function registerRoutes(): void
    {
        foreach ($this->routes as $route => $routeParams) {

            $method = strtolower($routeParams['method']);
            $objRoute = $this->app->{$method}($route, $routeParams['controller'] . '::' . $routeParams['action']);

            foreach ($routeParams['middleWare'] as $middleWare) {
                $objRoute->before(function (Request $request) use ($middleWare) {
                    return $this->app['middleware.' . $middleWare]->handle($request, $this->app);
                });
            }
        }
    }

    public function run(): void
    {
        $this->app->run();
    }
}
