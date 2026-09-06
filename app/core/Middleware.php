<?php
/**
 * Middleware - Utilidades de validacion y manejo de errores
 * Gestor Documental - Escuela G-733 Chorombo Bajo
 */
class Middleware
  {
    /**
 * Leer y decodificar el cuerpo JSON de la solicitud.
     * Responde 400 y termina la ejecucion si el JSON es invalido.
   */
public static function validateJson(): array
    {
      $body = file_get_contents('php://input');

    if (empty($body)) {
      return [];
    }

    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      http_response_code(400);
      echo json_encode([
                       'lang'    => APP_LANG,
                       'status'  => 'error',
                       'code'    => 400,
                       'message' => 'Cuerpo de solicitud JSON invalido: ' . json_last_error_msg(),
                       ], JSON_UNESCAPED_UNICODE);
      exit();
    }

    return $data ?? [];
    }

/**
 * Respuesta estandar de exito.
     */
public static function respond(int $code, array $payload): void
    {
      http_response_code($code);
      echo json_encode(array_merge(['lang' => APP_LANG], $payload), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

/**
 * Respuesta estandar de error.
     */
public static function error(int $code, string $message, array $extra = []): void
    {
      http_response_code($code);
      echo json_encode(array_merge([
                                   'lang'    => APP_LANG,
                                   'status'  => 'error',
                                   'code'    => $code,
                                   'message' => $message,
                                   ], $extra), JSON_UNESCAPED_UNICODE);
    }
  }
