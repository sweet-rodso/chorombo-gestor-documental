<?php
/**
 * TipoDocumentoController
 * Expone el catalogo de tipos documentales de la escuela.
 *
 * Rutas:
 *   GET /api/tipos-documento -> index()
 */
class TipoDocumentoController
{
private TipoDocumento $model;

public function __construct()
{
$this->model = new TipoDocumento();
}

public function index(array $params = []): void
{
try {
$tipos = $this->model->getAll();
Middleware::respond(200, [
'status' => 'success',
'code'   => 200,
'total'  => count($tipos),
'data'   => $tipos,
]);
} catch (\Throwable $e) {
Middleware::error(500, 'Error al consultar tipos de documento: ' . $e->getMessage());
}
}
}
