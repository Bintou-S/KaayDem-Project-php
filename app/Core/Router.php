<?php
declare(strict_types=1);
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $action): void
    {
        $this->routes['GET'][$path] = $action;
    }

    public function post(string $path, string $action): void
    {
        $this->routes['POST'][$path] = $action;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Strip base path /kaaydem75
        $base = '/kaaydem75';
        if (str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        if ($uri === '' || $uri === false) $uri = '/';

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route => $action) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                [$controllerName, $methodName] = explode('@', $action);
                $class = "App\\Controllers\\{$controllerName}";

                if (!class_exists($class)) {
                    $this->abort(500, "Controller {$class} introuvable.");
                    return;
                }

                $controller = new $class();

                if (!method_exists($controller, $methodName)) {
                    $this->abort(500, "Méthode {$methodName} introuvable.");
                    return;
                }

                call_user_func_array([$controller, $methodName], $matches);
                return;
            }
        }

        $this->abort(404);
    }

    private function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        if ($code === 404) {
            $controller = new \App\Controllers\AuthController();
            // Render a basic 404
            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
            <title>404 - Page introuvable</title>
            <link rel="stylesheet" href="/kaaydem75/public/css/style.css">
            </head><body>
            <nav class="navbar"><a class="navbar-brand" href="/kaaydem75/">🚐 Kaay Dem !</a></nav>
            <div class="container" style="text-align:center;padding:4rem 0">
            <h1 style="font-size:5rem;color:#1a7a4a">404</h1>
            <p>Page introuvable — Accès interdit</p>
            <a href="/kaaydem75/" class="btn btn-primary" style="margin-top:1rem">Retour à l\'accueil</a>
            </div></body></html>';
        } else {
            echo "Erreur {$code} : {$message}";
        }
    }
}
