<?php
/**
 * DocumentoController
 * CRUD del Gestor Documental - Escuela G-733 Chorombo Bajo
 *
 * Rutas:
 *   GET    /api/documentos          -> index()   Listar (con filtro ?tipo= y paginacion)
 *   GET    /api/documentos/{id}     -> show()    Ver uno
 *   POST   /api/documentos          -> store()   Crear
 *   PUT    /api/documentos/{id}     -> update()  Actualizar completo
 *   PATCH  /api/documentos/{id}     -> update()  Actualizar parcial
 *   DELETE /api/documentos/{id}     -> destroy() Eliminar
 */
class DocumentoController
{
private Documento     $model;
private TipoDocumento $tipoModel;

private const CAMPOS_OBLIGATORIOS = ['titulo', 'tipo_documento_id', 'fecha'];

public function __construct()
{
$this->model     = new Documento();
$this->tipoModel = new TipoDocumento();
}

public function index(array $params = []): void
{
try {
$filtros = [
'tipo'       => $_GET['tipo'] ?? null,
'pagina'     => $_GET['pagina'] ?? 1,
'por_pagina' => $_GET['por_pagina'] ?? 10,
];

$resultado = $this->model->getAll($filtros);

Middleware::respond(200, [
'status' => 'success',
'code'   => 200,
'total'  => $resultado['meta']['total'],
'meta'   => $resultado['meta'],
'data'   => $resultado['data'],
]);
} catch (\Throwable $e) {
Middleware::error(500, 'Error al listar documentos: ' . $e->getMessage());
}
}

public function show(array $params = []): void
{
$id = $this->validarId($params);
if ($id === null) return;

try {
$documento = $this->model->getById($id);
} catch (\Throwable $e) {
Middleware::error(500, 'Error al consultar el documento: ' . $e->getMessage());
return;
}

if (!$documento) {
Middleware::error(404, "Documento con ID {$id} no encontrado.");
return;
}

Middleware::respond(200, ['status' => 'success', 'code' => 200, 'data' => $documento]);
}

public function store(array $params = []): void
{
$body = Middleware::validateJson();

$errores = $this->validarCampos($body);
if (!empty($errores)) {
Middleware::error(422, 'Datos de entrada invalidos.', ['errors' => $errores]);
return;
}

try {
$documento = $this->model->create($body);
Middleware::respond(201, [
'status'  => 'success',
'code'    => 201,
'message' => 'Documento creado exitosamente.',
'data'    => $documento,
]);
} catch (\PDOException $e) {
$this->manejarErrorBD($e);
} catch (\Throwable $e) {
Middleware::error(500, 'Error al crear el documento: ' . $e->getMessage());
}
}

public function update(array $params = []): void
  {
    $id = $this->validarId($params);
    if ($id === null) return;

  try {
    if (!$this->model->existe($id)) {
      Middleware::error(404, "Documento con ID {$id} no encontrado.");
      return;
    }
  } catch (\Throwable $e) {
    Middleware::error(500, 'Error al verificar el documento: ' . $e->getMessage());
    return;
  }

  $body   = Middleware::validateJson();
    $metodo = $_SERVER['REQUEST_METHOD'];

  if (empty($body)) {
    Middleware::error(400, 'El cuerpo de la solicitud no puede estar vacio.');
    return;
  }

  $errores = $metodo === 'PUT'
    ? $this->validarCampos($body)
    : $this->validarCamposParciales($body);

  if (!empty($errores)) {
    Middleware::error(422, 'Datos de entrada invalidos.', ['errors' => $errores]);
    return;
  }

  try {
    $documento = $this->model->update($id, $body);
    Middleware::respond(200, [
                        'status'  => 'success',
                        'code'    => 200,
                        'message' => 'Documento actualizado exitosamente.',
                        'data'    => $documento,
                        ]);
  } catch (\PDOException $e) {
    $this->manejarErrorBD($e);
  } catch (\Throwable $e) {
    Middleware::error(500, 'Error al actualizar el documento: ' . $e->getMessage());
  }
  }

public function destroy(array $params = []): void
  {
    $id = $this->validarId($params);
    if ($id === null) return;

  try {
    $eliminado = $this->model->delete($id);
  } catch (\Throwable $e) {
    Middleware::error(500, 'Error al eliminar el documento: ' . $e->getMessage());
    return;
  }

  if (!$eliminado) {
    Middleware::error(404, "Documento con ID {$id} no encontrado.");
    return;
  }

  Middleware::respond(200, [
                      'status'  => 'success',
                      'code'    => 200,
                      'message' => "Documento con ID {$id} eliminado exitosamente.",
                      ]);
  }

private function validarId(array $params): ?int
  {
    $id = isset($params['id']) ? (int) $params['id'] : 0;
    if ($id <= 0) {
      Middleware::error(400, 'ID invalido. Debe ser un numero entero positivo.');
      return null;
    }
    return $id;
  }

private function validarCampos(array $body): array
  {
    $errores = [];

  foreach (self::CAMPOS_OBLIGATORIOS as $campo) {
    $valor = $body[$campo] ?? null;
    if ($valor === null || $valor === '') {
      $errores[] = "El campo \"{$campo}\" es obligatorio.";
    }
  }

  if (!empty($errores)) {
    return $errores;
  }

  if (mb_strlen($body['titulo']) > 200) {
    $errores[] = 'El campo "titulo" no puede superar los 200 caracteres.';
  }

  if (!$this->esFechaValida($body['fecha'])) {
    $errores[] = 'El campo "fecha" debe tener formato YYYY-MM-DD.';
  }

  if (!is_numeric($body['tipo_documento_id']) || !$this->tipoModel->existe((int) $body['tipo_documento_id'])) {
    $errores[] = 'El campo "tipo_documento_id" no corresponde a un tipo documental valido. Consulte GET /api/tipos-documento.';
  }

  return $errores;
  }

private function esFechaValida(string $fecha): bool
  {
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
  }

private function validarCamposParciales(array $body): array
  {
    $errores = [];

  if (array_key_exists('titulo', $body)) {
    if ($body['titulo'] === null || $body['titulo'] === '') {
      $errores[] = 'El campo "titulo" no puede estar vacio.';
    } elseif (mb_strlen($body['titulo']) > 200) {
      $errores[] = 'El campo "titulo" no puede superar los 200 caracteres.';
    }
  }

  if (array_key_exists('fecha', $body)) {
    if (empty($body['fecha']) || !$this->esFechaValida($body['fecha'])) {
      $errores[] = 'El campo "fecha" debe tener formato YYYY-MM-DD.';
    }
  }

  if (array_key_exists('tipo_documento_id', $body)) {
    if (!is_numeric($body['tipo_documento_id']) || !$this->tipoModel->existe((int) $body['tipo_documento_id'])) {
      $errores[] = 'El campo "tipo_documento_id" no corresponde a un tipo documental valido. Consulte GET /api/tipos-documento.';
    }
  }

  return $errores;
  }

private function manejarErrorBD(\PDOException $e): void
  {
    if ($e->getCode() === '23000') {
      Middleware::error(409, 'La operacion viola una restriccion de integridad de datos (tipo de documento invalido o duplicado).');
      return;
    }
    Middleware::error(500, 'Error de base de datos: ' . $e->getMessage());
  }
}
