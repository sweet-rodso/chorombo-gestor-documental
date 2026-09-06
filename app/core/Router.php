<?php
/**
 * Router - Enrutador REST de la API del Gestor Documental
 * Mapea metodos HTTP + URI a controladores y acciones
 */
class Router
  {
    private array $routes = [];

public function get(string $path, string $handler): void
    {
      $this->routes[] = ['method' => 'GET', 'path' => $path, 'handler' => $handler];
    }

public function post(string $path, string $handler): void
    {
      $this->routes[] = ['method' => 'POST', 'path' => $path, 'handler' => $handler];
    }

public function put(string $path, string $handler): void
    {
      $this->routes[] = ['method' => 'PUT', 'path' => $path, 'handler' => $handler];
    }

public function patch(string $path, string $handler): void
    {
      $this->routes[] = ['method' => 'PATCH', 'path' => $path, 'handler' => $handler];
    }

public function delete(string $path, string $handler): void
    {
      $this->routes[] = ['method' => 'DELETE', 'path' => $path, 'handler' => $handler];
    }

/**
 * Despachar la solicitud entrante
     */
public function dispatch(): void
    {
      $method = $_SERVER['REQUEST_METHOD'];
      $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
      if ($basePath !== '' && strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
      }

    $uri = rtrim($uri, '/');
      if (empty($uri)) {
        $uri = '/';
      }

    foreach ($this->routes as $route) {
      if ($route['method'] !== $method) {
        continue;
      }

      $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route['path']);
      $pattern = '#^' . $pattern . '$#';

      if (preg_match($pattern, $uri, $matches)) {
        $params = [];
        foreach ($matches as $key => $value) {
          if (!is_int($key)) {
            $params[$key] = $value;
          }
        }
        $this->callHandler($route['handler'], $params);
        return;
      }
    }

    $this->notFound();
    }

private function callHandler(string $handler, array $params): void
    {
      [$controllerName, $action] = explode('@', $handler);

    if (!class_exists($controllerName)) {
      $this->serverError("Controlador '{$controllerName}' no encontrado.");
      return;
    }

    try {
      $controller = new $controllerName();
    } catch (\Throwable $e) {
      $this->serverError('No fue posible conectar con la base de datos: ' . $e->getMessage());
      return;
    }

    if (!method_exists($controller, $action)) {
      $this->serverError("Accion '{$action}' no existe en '{$controllerName}'.");
      return;
    }

    $controller->$action($params);
    }

private function notFound(): void
    {
      http_response_code(404);
      echo json_encode([
                       'lang'    => defined('APP_LANG') ? APP_LANG : 'esp',
                       'status'  => 'error',
                       'code'    => 404,
                       'message' => 'Ruta no encontrada.',
                       ], JSON_UNESCAPED_UNICODE);
    }

private function serverError(string $msg): void
    {
      http_response_code(500);
      echo json_encode([
                       'lang'    => defined('APP_LANG') ? APP_LANG : 'esp',
                       'status'  => 'error',
                       'code'    => 500,
                       'message' => $msg,
                       ], JSON_UNESCAPED_UNICODE);
    }
  }
