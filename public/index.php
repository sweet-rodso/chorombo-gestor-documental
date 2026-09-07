<?php
/**
 * Front Controller - Gestor Documental
 * Escuela Basica G-733 Chorombo Bajo | Comuna de Maria Pinto
 * Desarrollo Backend - IF201IINF - Evaluacion U3 (CRUD)
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
http_response_code(200);
exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Middleware.php';

require_once __DIR__ . '/../app/models/TipoDocumento.php';
require_once __DIR__ . '/../app/models/Documento.php';

require_once __DIR__ . '/../app/controllers/TipoDocumentoController.php';
require_once __DIR__ . '/../app/controllers/DocumentoController.php';

$router = new Router();

$router->get('/api/tipos-documento', 'TipoDocumentoController@index');

$router->get('/api/documentos', 'DocumentoController@index');
$router->get('/api/documentos/{id}', 'DocumentoController@show');
$router->post('/api/documentos', 'DocumentoController@store');
$router->put('/api/documentos/{id}', 'DocumentoController@update');
$router->patch('/api/documentos/{id}', 'DocumentoController@update');
$router->delete('/api/documentos/{id}', 'DocumentoController@destroy');

$router->dispatch();
