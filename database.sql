-- Gestor Documental - Escuela Basica G-733 Chorombo Bajo
-- Comuna de Maria Pinto
-- Desarrollo Backend - IF201IINF - Evaluacion U3 (CRUD)

CREATE DATABASE IF NOT EXISTS chorombo_gestor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE chorombo_gestor;

CREATE TABLE IF NOT EXISTS tipos_documento (
      id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
      codigo      VARCHAR(40)     NOT NULL UNIQUE,
      nombre      VARCHAR(100)    NOT NULL,
      descripcion VARCHAR(255)    NULL,
      PRIMARY KEY (id)
  ) ENGINE=InnoDB;

INSERT INTO tipos_documento (codigo, nombre, descripcion) VALUES
    ('memo', 'Memo', 'Comunicacion interna breve entre el equipo directivo.'),
    ('oficio', 'Oficio', 'Comunicacion formal dirigida a organismos externos.'),
    ('citacion_apoderado', 'Citacion de Apoderado', 'Convocatoria formal a un apoderado o apoderada.'),
    ('acuerdo_apoderado', 'Acuerdo de Apoderados', 'Acta de acuerdos tomados con el centro de padres.'),
    ('reunion_comunal', 'Reunion Comunal', 'Documentos de reuniones con la Corporacion Municipal.'),
    ('permiso_administrativo', 'Permiso Administrativo', 'Solicitudes y resoluciones de permisos del personal.');

CREATE TABLE IF NOT EXISTS documentos (
      id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
      titulo              VARCHAR(200)    NOT NULL,
      tipo_documento_id   INT UNSIGNED    NOT NULL,
      fecha               DATE            NOT NULL,
      descripcion         TEXT            NULL,
      archivo_referencia  VARCHAR(500)    NULL,
      creado_en           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
      actualizado_en      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      CONSTRAINT fk_doc_tipo FOREIGN KEY (tipo_documento_id) REFERENCES tipos_documento (id) ON DELETE RESTRICT,
      INDEX idx_tipo (tipo_documento_id),
      INDEX idx_fecha (fecha)
  ) ENGINE=InnoDB;

INSERT INTO documentos (titulo, tipo_documento_id, fecha, descripcion, archivo_referencia) VALUES
    ('Memo N 12 - Suspension de clases por mantencion electrica', (SELECT id FROM tipos_documento WHERE codigo='memo'), '2026-08-10', 'Aviso interno al equipo docente sobre corte programado de luz.', 'memo_012_2026.pdf'),
    ('Oficio N 45 - Solicitud de material didactico a la Corporacion', (SELECT id FROM tipos_documento WHERE codigo='oficio'), '2026-08-05', 'Solicitud formal de recursos para el segundo semestre.', 'oficio_045_2026.pdf'),
    ('Citacion apoderado curso 4to Basico B', (SELECT id FROM tipos_documento WHERE codigo='citacion_apoderado'), '2026-08-20', 'Citacion por atraso reiterado del estudiante.', 'citacion_4basicoB_ago2026.pdf'),
    ('Acuerdo Centro de Padres - Actividad aniversario', (SELECT id FROM tipos_documento WHERE codigo='acuerdo_apoderado'), '2026-07-30', 'Acuerdos tomados en asamblea de apoderados para la celebracion de aniversario.', 'acuerdo_aniversario_2026.pdf'),
    ('Acta reunion comunal directores Maria Pinto', (SELECT id FROM tipos_documento WHERE codigo='reunion_comunal'), '2026-08-01', 'Acta de la reunion mensual de directores de la comuna.', 'acta_reunion_comunal_ago2026.pdf'),
    ('Permiso administrativo - Docente Ana Lopez', (SELECT id FROM tipos_documento WHERE codigo='permiso_administrativo'), '2026-08-15', 'Solicitud de permiso administrativo por tramite personal.', 'permiso_alopez_ago2026.pdf');
