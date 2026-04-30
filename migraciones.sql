-- migraciones.sql — Nuevas tablas para mejoras v3.1
-- Ejecutar en phpMyAdmin sobre la BD sistemaacademico

USE `sistemaacademico`;

-- MEJORA 7: Auditoría
CREATE TABLE IF NOT EXISTS `auditoria` (
  `id`             INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id`     INT,
  `accion`         ENUM('CREAR','EDITAR','BORRAR','LOGIN','NOTA','UPLOAD','OTRO') DEFAULT 'OTRO',
  `tabla_afectada` VARCHAR(50),
  `registro_id`    INT,
  `detalle`        TEXT,
  `ip`             VARCHAR(45),
  `fecha`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MEJORA 8: Avisos / Anuncios
CREATE TABLE IF NOT EXISTS `avisos` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `titulo`        VARCHAR(150) NOT NULL,
  `cuerpo`        TEXT NOT NULL,
  `tipo`          ENUM('Info','Alerta','Urgente','Exito') DEFAULT 'Info',
  `activo`        BOOLEAN DEFAULT TRUE,
  `expira`        DATE DEFAULT NULL,
  `creado_por`    INT,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`creado_por`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Aviso de bienvenida de ejemplo
INSERT INTO `avisos` (`titulo`, `cuerpo`, `tipo`, `activo`, `creado_por`) VALUES
('¡Bienvenidos al curso 2025-2026!', 'El sistema académico ha sido actualizado con nuevas funcionalidades. Explora el nuevo panel y no dudes en reportar cualquier incidencia al administrador.', 'Info', 1, 1),
('Plazo de matrícula S2 abierto', 'Del 1 al 15 de abril estará abierto el plazo de matrícula para el segundo semestre. Accede a la sección de Matrículas para inscribirte.', 'Alerta', 1, 1);

-- MEJORA 9: Foto de perfil en usuarios
ALTER TABLE `usuarios` ADD COLUMN IF NOT EXISTS `foto` VARCHAR(100) DEFAULT NULL;

-- MEJORA 10: Tokens API
CREATE TABLE IF NOT EXISTS `api_tokens` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `token`      VARCHAR(64) NOT NULL UNIQUE,
  `creado`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expira`     TIMESTAMP DEFAULT (CURRENT_TIMESTAMP + INTERVAL 30 DAY),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
