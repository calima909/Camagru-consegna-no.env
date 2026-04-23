<?php

class Router
{
    public function dispatch()
    {
        $url = $this->parseUrl();

        $controllerName = !empty($url[0])
            ? ucfirst($url[0]) . 'Controller'
            : 'HomeController';

        $method = $url[1] ?? 'index';

        $params = array_slice($url, 2);

        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            http_response_code(404);
            exit('Controller not found');
        }

        require_once $controllerFile;

        $controller = new $controllerName;

        if (!method_exists($controller, $method)) {
            http_response_code(404);
            exit('Method not found');
        }

        call_user_func_array([$controller, $method], $params);
    }

    private function parseUrl(): array
    {
        if (isset($_GET['url'])) {
            return explode('/', trim($_GET['url'], '/'));
        }
        return [];
    }
}
