# Gestor Documental — Escuela Básica G-733 Chorombo Bajo

Backend REST (PHP 8 + MySQL/PDO) para la gestión documental del equipo directivo de la Escuela Básica G-733 Chorombo Bajo, comuna de María Pinto. Desarrollado para la Evaluación Sumativa U3 — Desarrollo Backend (IF201IINF), Instituto Profesional San Sebastián.

Autor: Rodrigo Alexis Soto Cifuentes

## 1. Descripción del proyecto

Permite crear, consultar, actualizar y eliminar (CRUD) registros documentales: memos, oficios, citaciones de apoderados, acuerdos de apoderados, documentos de reuniones comunales y permisos administrativos. Queda preparado para ser consumido por la aplicación Frontend de la asignatura Desarrollo Frontend (ambas evaluaciones representan las dos capas del mismo proyecto).

## 2. Instalación (XAMPP)

1. Copiar la carpeta `chorombo-backend` dentro de `C:\xampp\htdocs\`.
2. Iniciar **Apache** y **MySQL** desde el Panel de Control de XAMPP.
3. Abrir phpMyAdmin (`http://localhost/phpmyadmin`) e importar el archivo `database.sql` (crea la base `chorombo_gestor`, sus tablas y datos de ejemplo).
4. Verificar la conexión en `config/database.php` (por defecto: host `127.0.0.1`, usuario `root`, sin contraseña — configuración estándar de XAMPP).
5. Probar en el navegador: `http://localhost/chorombo-backend/public/api/tipos-documento`.

## 3. Estructura del proyecto

```
chorombo-backend/
├── app/
│   ├── controllers/
│   │   ├── DocumentoController.php
│   │   └── TipoDocumentoController.php
│   ├── core/
│   │   ├── Router.php
│   │   └── Middleware.php
│   └── models/
│       ├── Documento.php
│       └── TipoDocumento.php
├── config/
│   └── database.php
├── public/
│   ├── index.php       (front controller)
│   └── .htaccess       (reescritura de URLs)
├── database.sql
├── openapi.yaml         (documentación Swagger/OpenAPI)
├── chorombo_postman.json (colección Postman)
├── EVIDENCIA_PRUEBAS.md  (plan y evidencia de pruebas)
└── README.md
```

## 4. Entidad principal: Documento

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT (PK, auto) | Identificador del registro |
| `titulo` | VARCHAR(200) | Título o nombre del documento |
| `tipo_documento_id` | INT (FK) | Referencia a `tipos_documento` |
| `fecha` | DATE | Fecha del documento |
| `descripcion` | TEXT | Descripción o referencia |
| `archivo_referencia` | VARCHAR(500) | Nombre de archivo almacenado o URL de referencia |
| `creado_en` / `actualizado_en` | DATETIME | Timestamps automáticos |

Tipos documentales (tabla catálogo `tipos_documento`): `memo`, `oficio`, `citacion_apoderado`, `acuerdo_apoderado`, `reunion_comunal`, `permiso_administrativo`.

## 5. Endpoints

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/documentos` | Listar documentos (filtros `?tipo=` y paginación `?pagina=&por_pagina=`) |
| GET | `/api/documentos/{id}` | Ver un documento |
| POST | `/api/documentos` | Crear documento |
| PUT | `/api/documentos/{id}` | Actualizar documento completo |
| PATCH | `/api/documentos/{id}` | Actualizar documento parcial |
| DELETE | `/api/documentos/{id}` | Eliminar documento |
| GET | `/api/tipos-documento` | Listar tipos de documento disponibles |

Documentación completa de request/response y códigos de estado: ver `openapi.yaml` (importable en [Swagger Editor](https://editor.swagger.io) o en Postman).

## 6. Pruebas

Importar `chorombo_postman.json` en Postman. Ver `EVIDENCIA_PRUEBAS.md` para el plan de casos de prueba, errores detectados durante el desarrollo y su corrección.

## 7. Diseño y decisiones técnicas

- **Patrón MVC** con enrutador propio (Router.php), consistente con el resto del proyecto de la asignatura.
- **PDO con prepared statements** para todas las operaciones, previniendo inyección SQL.
- **Transacciones** en create/update/delete para garantizar consistencia.
- **Paginación** en el listado (`LIMIT`/`OFFSET`) para evitar cargar la tabla completa — optimización de rendimiento (indicador 3.1.5).
- **Validación exhaustiva**: campos obligatorios, formato de fecha, longitud de texto, existencia de tipo documental, y traducción de errores de integridad de BD (SQLSTATE 23000) a HTTP 409 en lugar de errores genéricos 500.
- **CORS habilitado** para permitir el consumo desde el Frontend en otro origen.

## 8. Uso de Inteligencia Artificial

Este proyecto fue desarrollado con apoyo de **Claude** (Anthropic) como asistencia en la generación de la estructura del código PHP/PDO, la documentación OpenAPI y este README. El diseño de la entidad, las decisiones de arquitectura y la validación del cumplimiento de los requisitos del caso son responsabilidad del estudiante.
