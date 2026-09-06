<?php
/**
 * Modelo TipoDocumento
 * Catalogo de tipos documentales (memo, oficio, citacion_apoderado, etc.)
 * Conexion real a MySQL via PDO.
 */
class TipoDocumento
  {
    private PDO $db;

public function __construct()
    {
      $this->db = getDbConnection();
    }

/**
 * Listar todos los tipos de documento disponibles.
     * GET /api/tipos-documento
   */
public function getAll(): array
    {
      $stmt = $this->db->query('SELECT id, codigo, nombre, descripcion FROM tipos_documento ORDER BY nombre ASC');
      return $stmt->fetchAll();
    }

/**
 * Obtener un tipo de documento por su ID.
     */
public function getById(int $id): ?array
    {
      $stmt = $this->db->prepare('SELECT id, codigo, nombre, descripcion FROM tipos_documento WHERE id = ? LIMIT 1');
      $stmt->execute([$id]);
      $row = $stmt->fetch();
      return $row ?: null;
    }

/**
 * Obtener un tipo de documento por su codigo (ej. "memo").
   */
public function getByCodigo(string $codigo): ?array
    {
      $stmt = $this->db->prepare('SELECT id, codigo, nombre, descripcion FROM tipos_documento WHERE codigo = ? LIMIT 1');
      $stmt->execute([$codigo]);
      $row = $stmt->fetch();
      return $row ?: null;
    }

/**
 * Verificar si un ID de tipo de documento existe.
     */
public function existe(int $id): bool
    {
      $stmt = $this->db->prepare('SELECT COUNT(*) FROM tipos_documento WHERE id = ?');
      $stmt->execute([$id]);
      return (int) $stmt->fetchColumn() > 0;
    }
  }
