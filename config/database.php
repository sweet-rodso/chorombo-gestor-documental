<?php
/**
 * Conexion a base de datos - PDO/MySQL
 * Gestor Documental - Escuela G-733 Chorombo Bajo
 */

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'chorombo_gestor');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_LANG', 'esp');
define('APP_NAME', 'Gestor Documental Chorombo Bajo');

/**
 * Obtener una conexion PDO singleton.
   * Lanza PDOException si la conexion falla (capturada en index.php).
   */
function getDbConnection(): PDO
  {
        static $pdo = null;

    if ($pdo === null) {
              $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
              $options = [
                            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES   => false,
                        ];
              $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
  }
