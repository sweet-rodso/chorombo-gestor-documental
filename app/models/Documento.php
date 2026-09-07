<?php
/**
 * Modelo Documento
 * Entidad principal del Gestor Documental. CRUD completo sobre MySQL via PDO
 * con prepared statements, transacciones y paginacion.
 */
class Documento
  {
    private PDO $db;

public function __construct()
    {
      $this->db = getDbConnection();
    }

/**
 * Consulta base con JOIN a tipos_documento, para no repetir la union
     * en cada metodo (principio DRY / optimizacion de consultas).
     */
private function baseSelect(): string
    {
      return "SELECT d.id, d.titulo, d.fecha, d.descripcion, d.archivo_referencia,
                         d.creado_en, d.actualizado_en,
                                            t.id AS tipo_documento_id, t.codigo AS tipo_codigo, t.nombre AS tipo_nombre
                                                        FROM documentos d
                                                                    INNER JOIN tipos_documento t ON t.id = d.tipo_documento_id";
    }

/**
 * Listar documentos con filtros opcionales y paginacion.
     * GET /api/documentos?tipo=memo&pagina=1&por_pagina=10
     *
     * @param array $filtros ['tipo' => codigo?, 'pagina' => int, 'por_pagina' => int]
     */
public function getAll(array $filtros = []): array
    {
      $pagina    = max(1, (int) ($filtros['pagina'] ?? 1));
      $porPagina = min(50, max(1, (int) ($filtros['por_pagina'] ?? 10)));
      $offset    = ($pagina - 1) * $porPagina;

    $where  = '';
      $params = [];

    if (!empty($filtros['tipo'])) {
      $where = ' WHERE t.codigo = :tipo';
      $params[':tipo'] = $filtros['tipo'];
    }

    $sqlCount = "SELECT COUNT(*) FROM documentos d INNER JOIN tipos_documento t ON t.id = d.tipo_documento_id" . $where;
      $stmtCount = $this->db->prepare($sqlCount);
      $stmtCount->execute($params);
      $total = (int) $stmtCount->fetchColumn();

    $sql = $this->baseSelect() . $where . ' ORDER BY d.fecha DESC, d.id DESC LIMIT :limit OFFSET :offset';
      $stmt = $this->db->prepare($sql);
      foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
      }
      $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
      $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
      $stmt->execute();

    return [
      'data' => $this->formatRows($stmt->fetchAll()),
      'meta' => [
      'total'      => $total,
      'pagina'     => $pagina,
      'por_pagina' => $porPagina,
      'paginas'    => (int) ceil($total / $porPagina),
      ],
      ];
    }

/**
 * Obtener un documento por su ID.
     * GET /api/documentos/{id}
   */
public function getById(int $id): ?array
    {
      $stmt = $this->db->prepare($this->baseSelect() . ' WHERE d.id = :id LIMIT 1');
      $stmt->execute([':id' => $id]);
      $row = $stmt->fetch();
      return $row ? $this->formatRow($row) : null;
    }

/**
 * Crear un nuevo documento.
     * POST /api/documentos
     */
public function create(array $data): array
    {
      $this->db->beginTransaction();
      try {
        $stmt = $this->db->prepare(
          'INSERT INTO documentos (titulo, tipo_documento_id, fecha, descripcion, archivo_referencia)
                       VALUES (:titulo, :tipo_documento_id, :fecha, :descripcion, :archivo_referencia)'
          );
        $stmt->execute([
                       ':titulo'             => $data['titulo'],
                       ':tipo_documento_id'  => $data['tipo_documento_id'],
                       ':fecha'              => $data['fecha'],
                       ':descripcion'        => $data['descripcion'] ?? null,
                       ':archivo_referencia' => $data['archivo_referencia'] ?? null,
                       ]);
        $id = (int) $this->db->lastInsertId();
        $this->db->commit();
        return $this->getById($id);
      } catch (\Throwable $e) {
        $this->db->rollBack();
        throw $e;
      }
    }

/**
 * Actualizar un documento existente (soporta actualizacion parcial para PATCH).
   * PUT/PATCH /api/documentos/{id}
   */
public function update(int $id, array $data): ?array
    {
      $campos = [];
      $params = [':id' => $id];

    foreach (['titulo', 'tipo_documento_id', 'fecha', 'descripcion', 'archivo_referencia'] as $campo) {
      if (array_key_exists($campo, $data)) {
        $campos[] = "{$campo} = :{$campo}";
        $params[":{$campo}"] = $data[$campo];
      }
    }

    if (empty($campos)) {
      return $this->getById($id);
    }

    $this->db->beginTransaction();
      try {
        $sql  = 'UPDATE documentos SET ' . implode(', ', $campos) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $this->db->commit();
        return $this->getById($id);
      } catch (\Throwable $e) {
        $this->db->rollBack();
        throw $e;
      }
    }

/**
 * Eliminar un documento por ID.
     * DELETE /api/documentos/{id}
 */
public function delete(int $id): bool
    {
      $this->db->beginTransaction();
      try {
        $stmt = $this->db->prepare('DELETE FROM documentos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $affected = $stmt->rowCount();
        $this->db->commit();
        return $affected > 0;
      } catch (\Throwable $e) {
        $this->db->rollBack();
        throw $e;
      }
    }

/**
 * Verificar si un documento existe.
     */
public function existe(int $id): bool
    {
      $stmt = $this->db->prepare('SELECT COUNT(*) FROM documentos WHERE id = ?');
      $stmt->execute([$id]);
      return (int) $stmt->fetchColumn() > 0;
    }

private function formatRows(array $rows): array
    {
      return array_map([$this, 'formatRow'], $rows);
    }

private function formatRow(array $row): array
    {
      return [
        'id'                 => (int) $row['id'],
        'titulo'             => $row['titulo'],
        'fecha'              => $row['fecha'],
        'descripcion'        => $row['descripcion'],
        'archivo_referencia' => $row['archivo_referencia'],
        'tipo_documento'     => [
        'id'     => (int) $row['tipo_documento_id'],
        'codigo' => $row['tipo_codigo'],
        'nombre' => $row['tipo_nombre'],
        ],
        'creado_en'      => $row['creado_en'],
        'actualizado_en' => $row['actualizado_en'],
        ];
    }
  }
