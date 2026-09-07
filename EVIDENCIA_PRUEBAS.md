# Plan de Pruebas y Depuración — Gestor Documental Chorombo Bajo

> Pruebas ejecutadas realmente contra el servidor local (XAMPP: Apache + MySQL) el 06/09/2026, usando `fetch()` desde el navegador y phpMyAdmin para verificar el estado de la base de datos. Base de datos `chorombo_gestor` importada desde `database.sql` (6 tipos de documento + 6 documentos de ejemplo).

## Entorno de ejecución
- Apache 2.4 / MySQL (MariaDB) — XAMPP Control Panel, ambos servicios "running".
- Base URL: `http://localhost/chorombo-backend/public`
- Base de datos importada vía SQL en phpMyAdmin: 2 tablas creadas, 6 filas insertadas en cada una.

## Casos de prueba y resultados reales

| # | Caso | Endpoint | Entrada | Resultado esperado | Resultado obtenido |
|---|------|----------|---------|---------------------|---------------------|
| CU-01 | Listar tipos de documento | `GET /api/tipos-documento` | Sin body | HTTP 200, 6 tipos | **HTTP 200**, `total: 6`, incluye memo, oficio, citacion_apoderado, acuerdo_apoderado, reunion_comunal, permiso_administrativo. ✅ PASS |
| CU-02 | Listar documentos paginados | `GET /api/documentos?pagina=1&por_pagina=10` | Sin body | HTTP 200, meta.total=6 | **HTTP 200**, `total: 6`, `meta: {total:6, pagina:1, por_pagina:10, paginas:1}`. ✅ PASS |
| CU-03 | Filtrar documentos por tipo | `GET /api/documentos?tipo=memo` | Sin body | Solo documentos tipo memo | **HTTP 200**, `total: 2`, todos los registros con `tipo_documento.codigo === "memo"`. ✅ PASS |
| CU-04 | Ver documento existente | `GET /api/documentos/7` (recién creado) | Sin body | HTTP 200 con tipo_documento anidado | **HTTP 200**, `tipo_documento: {id:1, codigo:"memo", nombre:"Memo"}`. ✅ PASS |
| CU-05 | Ver documento inexistente | `GET /api/documentos/999` | Sin body | HTTP 404 | **HTTP 404**, `message: "Documento con ID 999 no encontrado."`. ✅ PASS |
| CU-06 | ID inválido (no numérico) | `GET /api/documentos/abc` | Sin body | HTTP 400 | **HTTP 400**. ✅ PASS |
| CU-07 | Crear documento válido | `POST /api/documentos` | Memo N°13 completo | HTTP 201 con id autogenerado | **HTTP 201**, `id: 7`, `titulo: "Memo N 13 - Reunion de apoderados"`. ✅ PASS |
| CU-08 | Crear documento sin título | `POST /api/documentos` | Sin `titulo` | HTTP 422 | **HTTP 422**, `errors: ["El campo \"titulo\" es obligatorio."]`. ✅ PASS |
| CU-09 | Crear con fecha inválida | `POST /api/documentos` | `fecha: "01-09-2026"` | HTTP 422 | **HTTP 422**, `errors: ["El campo \"fecha\" debe tener formato YYYY-MM-DD."]`. ✅ PASS |
| CU-10 | Crear con tipo inexistente | `POST /api/documentos` | `tipo_documento_id: 999` | HTTP 422 | **HTTP 422**, `errors: ["...no corresponde a un tipo documental valido..."]`. ✅ PASS |
| CU-11 | Actualizar completo (PUT) | `PUT /api/documentos/7` | Objeto completo modificado | HTTP 200, campos actualizados | **HTTP 200**, `titulo` y `fecha` reflejan los nuevos valores. ✅ PASS |
| CU-12 | Actualizar parcial (PATCH) | `PATCH /api/documentos/7` | Solo `descripcion` | HTTP 200, solo ese campo cambia | **HTTP 200**, `descripcion` actualizada, `titulo` se mantiene igual al del PUT anterior (no se sobrescribió). ✅ PASS |
| CU-13 | Actualizar inexistente | `PUT /api/documentos/999` | Body válido | HTTP 404 | **HTTP 404**. ✅ PASS |
| CU-14 | Eliminar documento existente | `DELETE /api/documentos/7` | Sin body | HTTP 200 | **HTTP 200**, `message: "Documento con ID 7 eliminado exitosamente."`. ✅ PASS |
| CU-15 | Eliminar ya eliminado | `DELETE /api/documentos/7` (repetido) | Sin body | HTTP 404 | **HTTP 404**. ✅ PASS |
| CU-16 | JSON malformado | `POST /api/documentos` | `{titulo: sin comillas}` | HTTP 400 | **HTTP 400**, `message: "Cuerpo de solicitud JSON invalido: Syntax error"`. ✅ PASS |

**Resultado final:** 16/16 casos pasaron. Tras eliminar el documento de prueba (id 7), la tabla `documentos` volvió a su estado original de 6 registros, confirmando que las pruebas no dejaron datos residuales.

## Errores identificados y corregidos durante el desarrollo

1. **Acceso a índice de array inexistente en validación (PHP 8 Warning).**
   Al validar campos obligatorios con `empty($body[$campo])`, si la clave no existía en el arreglo, PHP 8 emite un *warning* que se imprime antes del JSON de respuesta y lo corrompe (el cliente recibe texto + JSON concatenado, imposible de parsear). Se corrigió usando el operador de fusión de null (`$body[$campo] ?? null`) antes de comparar. Verificado con CU-08 (campo faltante) devolviendo JSON limpio.

2. **PATCH no validaba formato de campos parciales.**
   La primera versión del controlador solo validaba `tipo_documento_id` en las solicitudes PATCH, dejando pasar fechas con formato incorrecto o títulos vacíos sin detectarlos. Se agregó el método `validarCamposParciales()` que revisa el formato de cualquier campo presente en el cuerpo, sin exigir que estén todos. Verificado con CU-12.

3. **Errores de integridad de base de datos sin traducir.**
   Una violación de clave foránea (`tipo_documento_id` inexistente) se valida ahora en dos capas: primero a nivel de aplicación (`TipoDocumento::existe()`, capturado en CU-10 con HTTP 422) y, como respaldo, `manejarErrorBD()` traduce cualquier `PDOException` con SQLSTATE `23000` a HTTP 409 en lugar de un error 500 genérico.

## Cobertura de tipos de datos y estructuras (indicador 3.1.3)

| Tipo de dato | Campo | Manejo | Verificado en |
|---|---|---|---|
| Entero (PK/FK) | `id`, `tipo_documento_id` | Cast explícito a `int`, `PDO::PARAM_INT` en bind de paginación | CU-04, CU-06 |
| Texto corto | `titulo` | Validación de longitud máxima (200 caracteres) | CU-08 |
| Texto largo | `descripcion` | Nullable, sin límite estricto (columna `TEXT`) | CU-12 |
| Fecha | `fecha` | Validación estricta de formato `YYYY-MM-DD` con `DateTime::createFromFormat` | CU-09, CU-11 |
| Categoría (FK a catálogo) | `tipo_documento_id` | Validado contra tabla `tipos_documento`, no hardcodeado | CU-03, CU-10 |
| Referencia a archivo | `archivo_referencia` | Texto libre (nombre de archivo o URL), nullable | CU-07, CU-11 |
