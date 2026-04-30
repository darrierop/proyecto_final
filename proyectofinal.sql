-- ============================================================
-- BASE DE DATOS: sistemaacademico  v3.0
-- Sistema de Gestión Académica — Datos de muestra abundantes
-- ============================================================
-- ACCESO:  admin/admin123  |  profesor1-4/profesor123  |  alumno1-10/alumno123
-- ============================================================

CREATE DATABASE IF NOT EXISTS `sistemaacademico`
  DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;
USE `sistemaacademico`;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. ROLES
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `roles` VALUES
(1,'Administrador','Control total del sistema'),
(2,'Profesor','Gestiona cursos y calificaciones'),
(3,'Alumno','Consulta notas y matrículas');

-- 2. USUARIOS
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `nombre_completo` VARCHAR(100),
  `rol_id` INT NOT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `telefono` VARCHAR(20),
  `direccion` VARCHAR(200),
  FOREIGN KEY (`rol_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `usuarios` (`id`,`usuario`,`password`,`email`,`nombre_completo`,`rol_id`,`telefono`,`direccion`) VALUES
(1,'admin',   '$2y$10$P.r6/mrindWGs/77/qqusuMQRGjYsgZk3wsh7Gxof0hVRLIrNt.ru','admin@academia.es',        'Administrador del Sistema', 1,'600000001','C/ Administración 1'),
(2,'profesor1','$2y$10$D1Gh.5b..FLPeNxcu7et3OY.D3osfoeqQWa4VZW4KWFlKOuI57uda','javier.gomez@academia.es', 'Javier Gómez Martínez',     2,'611111111','Av. Universidad 22'),
(3,'profesor2','$2y$10$D1Gh.5b..FLPeNxcu7et3OY.D3osfoeqQWa4VZW4KWFlKOuI57uda','lucia.sanz@academia.es',   'Lucía Sanz Fernández',      2,'622222222','C/ Ciencias 8'),
(4,'profesor3','$2y$10$D1Gh.5b..FLPeNxcu7et3OY.D3osfoeqQWa4VZW4KWFlKOuI57uda','miguel.torres@academia.es','Miguel Torres Blanco',      2,'633333333','Pl. Mayor 3'),
(5,'profesor4','$2y$10$D1Gh.5b..FLPeNxcu7et3OY.D3osfoeqQWa4VZW4KWFlKOuI57uda','elena.morales@academia.es','Elena Morales Jiménez',     2,'644444444','C/ Letras 15'),
(6, 'alumno1','$2y$10$S/bn6oT6qPix5EV16i46uug.O9tukc2i.sjNyqYqXnhIlJy.JCqa2','andres.castro@mail.com',   'Andrés Castro López',       3,'655000001','C/ Palmera 4'),
(7, 'alumno2','$2y$10$S/bn6oT6qPix5EV16i46uug.O9tukc2i.sjNyqYqXnhIlJy.JCqa2','sofia.vega@mail.com',      'Sofía Vega Ruiz',           3,'655000002','Av. Olmos 9'),
(8, 'alumno3','$2y$10$S/bn6oT6qPix5EV16i46uug.O9tukc2i.sjNyqYqXnhIlJy.JCqa2','raul.lopez@mail.com',      'Raúl López García',         3,'655000003','C/ Roble 7'),
(9, 'alumno4','$2y$10$S/bn6oT6qPix5EV16i46uug.O9tukc2i.sjNyqYqXnhIlJy.JCqa2','carmen.diaz@mail.com',     'Carmen Díaz Martín',        3,'655000004','Pl. Flores 2'),
(10,'alumno5','$2y$10$S/bn6oT6qPix5EV16i46uug.O9tukc2i.sjNyqYqXnhIlJy.JCqa2','pablo.ruiz@mail.com',      'Pablo Ruiz Sánchez',        3,'655000005','C/ Pino 11'),
(11,'alumno6','$2y$10$S/bn6oT6qPix5EV16i46uug.O9tukc2i.sjNyqYqXnhIlJy.JCqa2','laura.navarro@mail.com',   'Laura Navarro Pérez',       3,'655000006','Av. Cedro 5'),
(12,'alumno7','$2y$10$S/bn6oT6qPix5EV16i46uug.O9tukc2i.sjNyqYqXnhIlJy.JCqa2','diego.moreno@mail.com',    'Diego Moreno Serrano',      3,'655000007','C/ Castaño 3'),
(13,'alumno8','$2y$10$S/bn6oT6qPix5EV16i46uug.O9tukc2i.sjNyqYqXnhIlJy.JCqa2','ana.jimenez@mail.com',     'Ana Jiménez Torres',        3,'655000008','Pl. Encina 6'),
(14,'alumno9','$2y$10$S/bn6oT6qPix5EV16i46uug.O9tukc2i.sjNyqYqXnhIlJy.JCqa2','marcos.gil@mail.com',      'Marcos Gil Fernández',      3,'655000009','C/ Abeto 18'),
(15,'alumno10','$2y$10$S/bn6oT6qPix5EV16i46uug.O9tukc2i.sjNyqYqXnhIlJy.JCqa2','elena.santos@mail.com',   'Elena Santos Romero',       3,'655000010','Av. Sauces 22');

-- 3. AULAS
DROP TABLE IF EXISTS `aulas`;
CREATE TABLE `aulas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(60) NOT NULL,
  `capacidad` INT DEFAULT 30,
  `tipo` ENUM('Teoría','Laboratorio','Taller','Seminario','Informática') DEFAULT 'Teoría',
  `planta` TINYINT DEFAULT 1,
  `edificio` VARCHAR(50) DEFAULT 'Principal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `aulas` VALUES
(1,'Aula 101',35,'Teoría',1,'Principal'),(2,'Aula 102',35,'Teoría',1,'Principal'),
(3,'Aula 201',30,'Teoría',2,'Principal'),(4,'Aula 202',30,'Teoría',2,'Principal'),
(5,'Aula 203',25,'Seminario',2,'Principal'),(6,'Laboratorio A',20,'Laboratorio',1,'Ciencias'),
(7,'Laboratorio B',20,'Laboratorio',1,'Ciencias'),(8,'Laboratorio C',18,'Laboratorio',2,'Ciencias'),
(9,'Taller de Redes',15,'Taller',1,'Tecnología'),(10,'Taller Eléctrico',15,'Taller',1,'Tecnología'),
(11,'Aula Informática 1',24,'Informática',1,'Tecnología'),(12,'Aula Informática 2',24,'Informática',2,'Tecnología'),
(13,'Salón de Actos',120,'Teoría',0,'Principal'),(14,'Sala de Reuniones',20,'Seminario',3,'Principal');

-- 4. DEPARTAMENTOS
DROP TABLE IF EXISTS `departamentos`;
CREATE TABLE `departamentos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `codigo` VARCHAR(10) NOT NULL UNIQUE,
  `jefe_id` INT,
  FOREIGN KEY (`jefe_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `departamentos` VALUES
(1,'Matemáticas e Informática','DEP-MAT',2),
(2,'Idiomas y Humanidades','DEP-ING',3),
(3,'Tecnología y Redes','DEP-TEC',4),
(4,'Ciencias Aplicadas','DEP-CIE',5);

-- 5. ASIGNATURAS
DROP TABLE IF EXISTS `asignaturas`;
CREATE TABLE `asignaturas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(20) UNIQUE,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT,
  `creditos` INT DEFAULT 6,
  `departamento_id` INT,
  `nivel` ENUM('Básico','Intermedio','Avanzado') DEFAULT 'Básico',
  `horas_semanales` TINYINT DEFAULT 4,
  FOREIGN KEY (`departamento_id`) REFERENCES `departamentos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `asignaturas` VALUES
(1,'MAT101','Álgebra Lineal','Vectores, matrices y sistemas lineales',6,1,'Básico',4),
(2,'MAT201','Cálculo Diferencial','Límites, derivadas y aplicaciones',6,1,'Intermedio',4),
(3,'INF102','Bases de Datos','Diseño y SQL con MySQL y PostgreSQL',6,1,'Intermedio',4),
(4,'INF201','Programación Orientada a Objetos','OOP en Java: clases, herencia y polimorfismo',6,1,'Intermedio',4),
(5,'INF301','Inteligencia Artificial','ML, búsqueda y procesamiento del lenguaje',6,1,'Avanzado',4),
(6,'PRG101','Fundamentos de Programación','Introducción a la programación con Python',5,1,'Básico',3),
(7,'PRG301','Programación Web','PHP, HTML5, CSS3 y JavaScript moderno',6,1,'Intermedio',4),
(8,'ING201','Inglés Técnico I','Comunicación técnica en inglés B1-B2',4,2,'Básico',3),
(9,'ING301','Inglés Técnico II','Redacción, presentaciones y negociación C1',4,2,'Intermedio',3),
(10,'HUM101','Ética Profesional','Ética TIC, propiedad intelectual y privacidad',3,2,'Básico',2),
(11,'RED201','Redes y Sistemas','TCP/IP, administración y seguridad básica',5,3,'Básico',4),
(12,'RED301','Ciberseguridad','Criptografía, vulnerabilidades y ethical hacking',5,3,'Avanzado',3),
(13,'RED401','Cloud Computing','AWS, Azure, Docker y Kubernetes',5,3,'Avanzado',3),
(14,'CIE101','Física Aplicada','Mecánica, electromagnetismo y óptica',5,4,'Básico',4),
(15,'CIE201','Estadística y Probabilidad','Inferencia estadística y análisis con R',5,4,'Intermedio',3);

-- 6. CURSOS
DROP TABLE IF EXISTS `cursos`;
CREATE TABLE `cursos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `asignatura_id` INT NOT NULL,
  `profesor_id` INT,
  `nombre_grupo` VARCHAR(50),
  `semestre` VARCHAR(20),
  `anio` YEAR DEFAULT 2026,
  `estado` ENUM('Activo','Finalizado','Pendiente') DEFAULT 'Activo',
  `max_alumnos` INT DEFAULT 30,
  FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas`(`id`),
  FOREIGN KEY (`profesor_id`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `cursos` (`id`,`asignatura_id`,`profesor_id`,`nombre_grupo`,`semestre`,`anio`,`estado`,`max_alumnos`) VALUES
(1, 1,2,'Grupo A','2025-2026 S1',2026,'Activo',30),(2, 1,2,'Grupo B','2025-2026 S1',2026,'Activo',28),
(3, 3,2,'Grupo A','2025-2026 S1',2026,'Activo',25),(4, 7,3,'Grupo A','2025-2026 S1',2026,'Activo',25),
(5,11,2,'Grupo B','2025-2026 S1',2026,'Activo',20),(6, 8,3,'Grupo A','2025-2026 S1',2026,'Activo',30),
(7, 6,4,'Grupo A','2025-2026 S1',2026,'Activo',30),(8, 4,4,'Grupo B','2025-2026 S1',2026,'Activo',25),
(9,14,5,'Grupo A','2025-2026 S1',2026,'Activo',30),(10,2,2,'Grupo A','2025-2026 S2',2026,'Pendiente',30),
(11,5,4,'Grupo A','2025-2026 S2',2026,'Pendiente',20),(12,12,4,'Grupo A','2025-2026 S2',2026,'Pendiente',18),
(13,13,3,'Grupo A','2025-2026 S2',2026,'Pendiente',20),(14,9,3,'Grupo A','2025-2026 S2',2026,'Pendiente',30),
(15,15,5,'Grupo A','2025-2026 S2',2026,'Pendiente',25),
(16,1,2,'Grupo A','2024-2025 S1',2025,'Finalizado',30),(17,3,2,'Grupo A','2024-2025 S1',2025,'Finalizado',25),
(18,8,3,'Grupo A','2024-2025 S1',2025,'Finalizado',30),(19,6,4,'Grupo A','2024-2025 S1',2025,'Finalizado',30),
(20,14,5,'Grupo A','2024-2025 S1',2025,'Finalizado',30);

-- 7. HORARIOS
DROP TABLE IF EXISTS `horarios`;
CREATE TABLE `horarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `curso_id` INT NOT NULL,
  `aula_id` INT,
  `dia_semana` ENUM('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'),
  `hora_inicio` TIME NOT NULL,
  `hora_fin` TIME NOT NULL,
  FOREIGN KEY (`curso_id`) REFERENCES `cursos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`aula_id`) REFERENCES `aulas`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `horarios` VALUES
(1,1,1,'Lunes','08:00','10:00'),(2,1,1,'Miércoles','08:00','10:00'),
(3,2,2,'Martes','16:00','18:00'),(4,2,2,'Jueves','16:00','18:00'),
(5,3,11,'Martes','10:00','12:00'),(6,3,11,'Jueves','10:00','12:00'),
(7,4,11,'Lunes','10:00','12:00'),(8,4,11,'Viernes','10:00','12:00'),
(9,5,9,'Miércoles','10:00','12:00'),(10,5,9,'Viernes','08:00','10:00'),
(11,6,3,'Lunes','12:00','14:00'),(12,6,3,'Miércoles','12:00','14:00'),
(13,7,12,'Martes','08:00','10:00'),(14,7,12,'Jueves','08:00','10:00'),
(15,8,11,'Miércoles','15:00','17:00'),(16,8,11,'Viernes','15:00','17:00'),
(17,9,6,'Lunes','15:00','17:00'),(18,9,6,'Jueves','15:00','17:00');

-- 8. MATRÍCULAS
DROP TABLE IF EXISTS `matriculas`;
CREATE TABLE `matriculas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `alumno_id` INT NOT NULL,
  `curso_id` INT NOT NULL,
  `fecha_matricula` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `nota_final` DECIMAL(4,2) DEFAULT NULL,
  `estado` ENUM('Activa','Baja','Suspendida') DEFAULT 'Activa',
  UNIQUE KEY `unica` (`alumno_id`,`curso_id`),
  FOREIGN KEY (`alumno_id`) REFERENCES `usuarios`(`id`),
  FOREIGN KEY (`curso_id`) REFERENCES `cursos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `matriculas` (`id`,`alumno_id`,`curso_id`,`fecha_matricula`,`nota_final`,`estado`) VALUES
(1,6,1,'2026-02-01 09:00',8.50,'Activa'),(2,6,3,'2026-02-01 09:05',7.20,'Activa'),
(3,6,6,'2026-02-01 09:10',NULL,'Activa'),(4,6,7,'2026-02-01 09:15',9.10,'Activa'),
(5,6,9,'2026-02-01 09:20',NULL,'Activa'),(6,7,1,'2026-02-02 10:00',9.00,'Activa'),
(7,7,6,'2026-02-02 10:05',6.80,'Activa'),(8,7,4,'2026-02-02 10:10',NULL,'Activa'),
(9,7,7,'2026-02-02 10:15',8.75,'Activa'),(10,7,8,'2026-02-02 10:20',NULL,'Activa'),
(11,8,2,'2026-02-03 11:00',7.50,'Activa'),(12,8,4,'2026-02-03 11:05',8.00,'Activa'),
(13,8,5,'2026-02-03 11:10',NULL,'Activa'),(14,8,8,'2026-02-03 11:15',6.40,'Activa'),
(15,8,9,'2026-02-03 11:20',NULL,'Activa'),(16,9,1,'2026-02-04 09:00',6.00,'Activa'),
(17,9,3,'2026-02-04 09:05',5.80,'Activa'),(18,9,6,'2026-02-04 09:10',NULL,'Activa'),
(19,9,9,'2026-02-04 09:15',7.30,'Activa'),(20,10,2,'2026-02-05 10:00',4.50,'Activa'),
(21,10,5,'2026-02-05 10:05',NULL,'Activa'),(22,10,7,'2026-02-05 10:10',8.20,'Activa'),
(23,10,8,'2026-02-05 10:15',NULL,'Activa'),(24,11,1,'2026-02-06 09:00',9.50,'Activa'),
(25,11,4,'2026-02-06 09:05',NULL,'Activa'),(26,11,6,'2026-02-06 09:10',8.00,'Activa'),
(27,11,7,'2026-02-06 09:15',9.20,'Activa'),(28,11,9,'2026-02-06 09:20',NULL,'Activa'),
(29,12,2,'2026-02-07 10:00',5.50,'Activa'),(30,12,3,'2026-02-07 10:05',7.80,'Activa'),
(31,12,5,'2026-02-07 10:10',NULL,'Activa'),(32,12,8,'2026-02-07 10:15',6.90,'Activa'),
(33,13,1,'2026-02-08 09:00',7.00,'Activa'),(34,13,4,'2026-02-08 09:05',NULL,'Activa'),
(35,13,6,'2026-02-08 09:10',6.50,'Activa'),(36,13,9,'2026-02-08 09:15',8.40,'Activa'),
(37,14,2,'2026-02-09 10:00',3.80,'Activa'),(38,14,5,'2026-02-09 10:05',5.20,'Activa'),
(39,14,7,'2026-02-09 10:10',NULL,'Activa'),(40,14,8,'2026-02-09 10:15',7.10,'Activa'),
(41,15,1,'2026-02-10 09:00',8.80,'Activa'),(42,15,3,'2026-02-10 09:05',9.10,'Activa'),
(43,15,6,'2026-02-10 09:10',NULL,'Activa'),(44,15,7,'2026-02-10 09:15',8.60,'Activa'),
(45,15,9,'2026-02-10 09:20',NULL,'Activa'),
-- Año anterior
(46,6,16,'2025-09-10 09:00',7.80,'Activa'),(47,7,16,'2025-09-10 09:00',8.50,'Activa'),
(48,8,16,'2025-09-10 09:00',6.20,'Activa'),(49,6,17,'2025-09-10 09:00',6.90,'Activa'),
(50,7,18,'2025-09-10 09:00',7.40,'Activa'),(51,8,19,'2025-09-10 09:00',8.10,'Activa'),
(52,9,20,'2025-09-10 09:00',5.60,'Activa');

-- 9. CALIFICACIONES
DROP TABLE IF EXISTS `calificaciones`;
CREATE TABLE `calificaciones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `matricula_id` INT NOT NULL,
  `nombre_actividad` VARCHAR(100) NOT NULL,
  `tipo` ENUM('Examen','Práctica','Trabajo','Proyecto','Participación','Laboratorio') DEFAULT 'Examen',
  `nota` DECIMAL(4,2) NOT NULL,
  `peso` DECIMAL(5,2) DEFAULT 30.00,
  `fecha` DATE,
  `observaciones` VARCHAR(200),
  FOREIGN KEY (`matricula_id`) REFERENCES `matriculas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `calificaciones` (`id`,`matricula_id`,`nombre_actividad`,`tipo`,`nota`,`peso`,`fecha`,`observaciones`) VALUES
(1,1,'Examen Parcial T1','Examen',8.00,30.00,'2026-02-15','Matrices y sistemas'),
(2,1,'Práctica Matlab','Práctica',9.00,20.00,'2026-02-20','Entregada a tiempo'),
(3,1,'Trabajo Grupal','Trabajo',8.50,20.00,'2026-03-01','Presentación excelente'),
(4,1,'Examen Final','Examen',8.20,30.00,'2026-03-20','Buen dominio del temario'),
(5,2,'Examen Parcial','Examen',7.00,35.00,'2026-02-15',NULL),
(6,2,'Práctica SQL','Práctica',8.00,25.00,'2026-02-22','Consultas complejas resueltas'),
(7,2,'Proyecto Final BD','Proyecto',7.50,25.00,'2026-03-01','Normalización correcta'),
(8,2,'Examen Final','Examen',6.50,15.00,'2026-03-22',NULL),
(9,4,'Práctica 1 Listas','Práctica',9.50,20.00,'2026-02-14','Código limpio y eficiente'),
(10,4,'Práctica 2 Funciones','Práctica',9.00,20.00,'2026-02-28','Documentación perfecta'),
(11,4,'Proyecto Scraper','Proyecto',9.00,30.00,'2026-03-15','Proyecto destacado'),
(12,4,'Examen Final','Examen',9.20,30.00,'2026-03-21',NULL),
(13,6,'Examen Parcial T1','Examen',9.50,30.00,'2026-02-15','Sobresaliente'),
(14,6,'Práctica Matlab','Práctica',8.50,20.00,'2026-02-20',NULL),
(15,6,'Trabajo Grupal','Trabajo',9.00,20.00,'2026-03-01',NULL),
(16,6,'Examen Final','Examen',9.00,30.00,'2026-03-20',NULL),
(17,7,'Listening Test 1','Examen',7.00,25.00,'2026-02-12',NULL),
(18,7,'Writing Essay','Trabajo',6.50,25.00,'2026-02-26','Vocabulario técnico mejorable'),
(19,7,'Speaking Test','Examen',7.00,25.00,'2026-03-10',NULL),
(20,7,'Examen Final','Examen',6.80,25.00,'2026-03-22',NULL),
(21,9,'Práctica 1 Listas','Práctica',9.00,20.00,'2026-02-14',NULL),
(22,9,'Práctica 2 Funciones','Práctica',8.50,20.00,'2026-02-28',NULL),
(23,9,'Proyecto Scraper','Proyecto',8.75,30.00,'2026-03-15',NULL),
(24,9,'Examen Final','Examen',8.75,30.00,'2026-03-21',NULL),
(25,11,'Examen Parcial T1','Examen',7.00,30.00,'2026-02-15',NULL),
(26,11,'Práctica Matlab','Práctica',8.50,20.00,'2026-02-20',NULL),
(27,11,'Trabajo Grupal','Trabajo',7.00,20.00,'2026-03-01',NULL),
(28,11,'Examen Final','Examen',7.50,30.00,'2026-03-20',NULL),
(29,12,'Maquetación HTML/CSS','Práctica',8.50,20.00,'2026-02-18',NULL),
(30,12,'API REST con PHP','Proyecto',8.00,30.00,'2026-03-05',NULL),
(31,12,'Examen Parcial','Examen',7.50,20.00,'2026-02-25',NULL),
(32,12,'Proyecto Final Web','Proyecto',8.20,30.00,'2026-03-18',NULL),
(33,14,'Práctica Herencia','Práctica',6.50,25.00,'2026-02-20',NULL),
(34,14,'Examen Parcial','Examen',6.00,25.00,'2026-02-27',NULL),
(35,14,'Proyecto POO','Proyecto',6.80,25.00,'2026-03-14',NULL),
(36,14,'Examen Final','Examen',6.30,25.00,'2026-03-21',NULL),
(37,16,'Examen Parcial T1','Examen',5.50,30.00,'2026-02-15',NULL),
(38,16,'Práctica Matlab','Práctica',7.00,20.00,'2026-02-20',NULL),
(39,16,'Trabajo Grupal','Trabajo',6.50,20.00,'2026-03-01',NULL),
(40,16,'Examen Final','Examen',5.80,30.00,'2026-03-20',NULL),
(41,19,'Práctica Lab 1','Laboratorio',7.00,20.00,'2026-02-19',NULL),
(42,19,'Examen Parcial','Examen',7.50,30.00,'2026-02-26',NULL),
(43,19,'Práctica Lab 2','Laboratorio',7.50,20.00,'2026-03-12',NULL),
(44,19,'Examen Final','Examen',7.20,30.00,'2026-03-21',NULL),
(45,20,'Examen Parcial T1','Examen',4.00,30.00,'2026-02-15','Necesita refuerzo'),
(46,20,'Práctica Matlab','Práctica',5.50,20.00,'2026-02-20',NULL),
(47,20,'Trabajo Grupal','Trabajo',5.00,20.00,'2026-03-01',NULL),
(48,20,'Examen Final','Examen',4.20,30.00,'2026-03-20','No superó el mínimo'),
(49,22,'Práctica 1 Listas','Práctica',8.00,20.00,'2026-02-14',NULL),
(50,22,'Práctica 2 Funciones','Práctica',8.50,20.00,'2026-02-28',NULL),
(51,22,'Proyecto Scraper','Proyecto',8.00,30.00,'2026-03-15',NULL),
(52,22,'Examen Final','Examen',8.30,30.00,'2026-03-21',NULL),
(53,24,'Examen Parcial T1','Examen',9.80,30.00,'2026-02-15','Matrícula de honor'),
(54,24,'Práctica Matlab','Práctica',9.50,20.00,'2026-02-20',NULL),
(55,24,'Trabajo Grupal','Trabajo',9.00,20.00,'2026-03-01',NULL),
(56,24,'Examen Final','Examen',9.50,30.00,'2026-03-20',NULL),
(57,27,'Práctica 1 Listas','Práctica',9.50,20.00,'2026-02-14',NULL),
(58,27,'Práctica 2 Funciones','Práctica',9.00,20.00,'2026-02-28',NULL),
(59,27,'Proyecto Scraper','Proyecto',9.50,30.00,'2026-03-15','Proyecto destacado del curso'),
(60,27,'Examen Final','Examen',9.00,30.00,'2026-03-21',NULL),
(61,46,'Examen Parcial','Examen',7.50,40.00,'2025-11-15',NULL),
(62,46,'Examen Final','Examen',8.00,60.00,'2026-01-20',NULL),
(63,47,'Examen Parcial','Examen',8.00,40.00,'2025-11-15',NULL),
(64,47,'Examen Final','Examen',9.00,60.00,'2026-01-20',NULL),
(65,48,'Examen Parcial','Examen',6.00,40.00,'2025-11-15',NULL),
(66,48,'Examen Final','Examen',6.50,60.00,'2026-01-20',NULL);

-- 10. MENSAJES
DROP TABLE IF EXISTS `mensajes`;
CREATE TABLE `mensajes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `remitente_id` INT NOT NULL,
  `destinatario_id` INT NOT NULL,
  `asunto` VARCHAR(150),
  `cuerpo` TEXT NOT NULL,
  `leido` BOOLEAN DEFAULT FALSE,
  `fecha_envio` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`remitente_id`) REFERENCES `usuarios`(`id`),
  FOREIGN KEY (`destinatario_id`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `mensajes` VALUES
(1,1,2,'Bienvenida al sistema','Hola Javier, bienvenido. Revisa tus cursos asignados para 2025-2026 S1.',FALSE,'2026-02-01 08:00'),
(2,1,3,'Bienvenida al sistema','Hola Lucía, bienvenida. Tienes 3 cursos este semestre. Cualquier duda escríbeme.',FALSE,'2026-02-01 08:05'),
(3,1,4,'Bienvenida al sistema','Hola Miguel, bienvenido. Estás asignado a POO y Python este semestre.',FALSE,'2026-02-01 08:10'),
(4,1,5,'Bienvenida al sistema','Hola Elena, bienvenida. Impartirás Física Aplicada y Estadística el próximo semestre.',FALSE,'2026-02-01 08:15'),
(5,1,6,'Matrícula confirmada','Andrés, tu matrícula para S1 ha sido procesada. Estás inscrito en 5 asignaturas.',TRUE,'2026-02-01 09:30'),
(6,1,7,'Matrícula confirmada','Sofía, tu matrícula ha sido procesada. Estás inscrita en 5 asignaturas.',TRUE,'2026-02-02 09:30'),
(7,1,8,'Matrícula confirmada','Raúl, tu matrícula ha sido confirmada. 5 asignaturas este semestre.',FALSE,'2026-02-03 09:30'),
(8,2,6,'Primer examen de Álgebra','Andrés, el parcial es el 15/02 a las 8:00 en Aula 101. Repasa vectores y matrices.',FALSE,'2026-02-10 10:00'),
(9,2,7,'Primer examen de Álgebra','Sofía, recuerda el parcial el día 15. El contenido abarca hasta el tema 4.',FALSE,'2026-02-10 10:05'),
(10,3,7,'Nota trabajo de Inglés','Sofía, tu nota del Writing Essay es 6.50. El vocabulario técnico era mejorable. Pasa por tutorías.',FALSE,'2026-02-27 12:00'),
(11,3,6,'Horario de tutoría','Andrés, los martes 13:00-14:00 tengo tutoría si necesitas ayuda con programación web.',FALSE,'2026-03-01 09:00'),
(12,4,8,'Entrega proyecto POO','Raúl, el proyecto de POO se entrega el 14 de marzo a las 23:59.',FALSE,'2026-03-08 11:00'),
(13,6,2,'Consulta sobre Álgebra','Buenos días Javier, no entiendo la diagonalización de matrices. ¿Podría explicarlo en tutoría?',FALSE,'2026-02-18 17:30'),
(14,2,6,'Re: Consulta sobre Álgebra','Claro Andrés. La diagonalización requiere primero los valores propios. Ven el lunes a las 11:00.',TRUE,'2026-02-18 19:00'),
(15,7,3,'Pregunta examen Inglés','Lucía, ¿el examen final incluye escritura de informes técnicos o solo lectura y escucha?',FALSE,'2026-03-05 16:00'),
(16,3,7,'Re: Examen final Inglés','Sofía, habrá comprensión lectora (40%), escucha (30%) y redacción de email formal (30%).',FALSE,'2026-03-05 17:30'),
(17,1,2,'Reunión de coordinación','Javier, reunión de coordinación el jueves a las 12:00 en Sala de Reuniones. Confirma asistencia.',FALSE,'2026-03-10 08:00'),
(18,2,1,'Re: Reunión coordinación','Confirmada mi asistencia. ¿Tienes el orden del día disponible?',TRUE,'2026-03-10 09:15'),
(19,5,9,'Resultado práctica Física','Carmen, tu nota de la primera práctica es 7.0. Buen trabajo con el equipamiento.',FALSE,'2026-02-20 14:00'),
(20,1,15,'Felicitación por rendimiento','Elena, quería felicitarte por tu excelente rendimiento este semestre. Sigue así.',FALSE,'2026-03-15 10:00');

-- 11. EVENTOS
DROP TABLE IF EXISTS `eventos`;
CREATE TABLE `eventos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(150) NOT NULL,
  `descripcion` TEXT,
  `fecha_inicio` DATETIME NOT NULL,
  `fecha_fin` DATETIME,
  `tipo` ENUM('Examen','Entrega','Festivo','Reunión','Evento','Otro') DEFAULT 'Otro',
  `curso_id` INT,
  `creado_por` INT,
  FOREIGN KEY (`curso_id`) REFERENCES `cursos`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`creado_por`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `eventos` VALUES
(1,'Examen Parcial Álgebra A','Temas 1-4','2026-02-15 08:00','2026-02-15 10:00','Examen',1,2),
(2,'Examen Parcial Álgebra B','Temas 1-4','2026-02-15 16:00','2026-02-15 18:00','Examen',2,2),
(3,'Entrega Proyecto BD','Entrega del proyecto final','2026-03-01 23:59',NULL,'Entrega',3,2),
(4,'Examen Parcial Inglés','Listening + Reading','2026-02-12 12:00','2026-02-12 14:00','Examen',6,3),
(5,'Práctica Lab. Física 1','Primera práctica laboratorio','2026-02-19 15:00','2026-02-19 17:00','Otro',9,5),
(6,'Reunión coordinación docente','Reunión semestral profesores','2026-03-13 12:00','2026-03-13 14:00','Reunión',NULL,1),
(7,'Semana Cultural del Centro','Actividades culturales abiertas','2026-03-22 09:00','2026-03-26 18:00','Evento',NULL,1),
(8,'Día de San Isidro','Festivo — sin clases','2026-05-15 00:00','2026-05-15 23:59','Festivo',NULL,1),
(9,'Entrega Proyecto POO','Proyecto final de POO','2026-03-14 23:59',NULL,'Entrega',8,4),
(10,'Examen Final Álgebra A','Examen final','2026-03-20 08:00','2026-03-20 10:00','Examen',1,2),
(11,'Examen Final Bases de Datos','Examen final BD','2026-03-22 10:00','2026-03-22 12:00','Examen',3,2),
(12,'Entrega Proyecto Web','Proyecto final web','2026-03-18 23:59',NULL,'Entrega',4,3),
(13,'Examen Final Python','Examen final Python','2026-03-21 08:00','2026-03-21 10:00','Examen',7,4),
(14,'Examen Final Física','Examen final Física','2026-03-21 15:00','2026-03-21 17:00','Examen',9,5),
(15,'Apertura Matrícula S2','Plazo de matrícula para S2','2026-04-01 09:00','2026-04-15 23:59','Otro',NULL,1);

-- 12. TUTORÍAS
DROP TABLE IF EXISTS `tutorias`;
CREATE TABLE `tutorias` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `profesor_id` INT NOT NULL,
  `alumno_id` INT NOT NULL,
  `fecha` DATETIME NOT NULL,
  `duracion` SMALLINT DEFAULT 30,
  `motivo` VARCHAR(200),
  `notas` TEXT,
  `estado` ENUM('Pendiente','Realizada','Cancelada') DEFAULT 'Pendiente',
  FOREIGN KEY (`profesor_id`) REFERENCES `usuarios`(`id`),
  FOREIGN KEY (`alumno_id`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `tutorias` VALUES
(1,2,6,'2026-02-19 11:00',30,'Dudas diagonalización','Explicados valores y vectores propios. Alumno comprende bien.','Realizada'),
(2,3,7,'2026-03-06 13:00',30,'Estructura examen Inglés','Repaso de writing y técnicas de listening.','Realizada'),
(3,4,8,'2026-03-09 10:00',45,'Revisión proyecto POO','Errores en herencia múltiple — corregidos.','Realizada'),
(4,2,9,'2026-02-25 11:00',30,'Repaso examen parcial','Ejercicios de sistemas de ecuaciones.','Realizada'),
(5,5,9,'2026-02-21 15:00',30,'Práctica laboratorio 1','Revisión de mediciones y errores.','Realizada'),
(6,2,6,'2026-03-18 11:00',30,'Consulta examen final',NULL,'Pendiente'),
(7,3,7,'2026-03-19 13:00',30,'Preparación examen Inglés',NULL,'Pendiente'),
(8,4,10,'2026-03-17 10:00',45,'Refuerzo Python recursividad','Alumno tiene dificultades con recursividad.','Realizada'),
(9,2,14,'2026-03-12 11:00',60,'Revisión errores — examen suspendido','Plan de refuerzo: vectores y valores propios.','Realizada'),
(10,5,10,'2026-02-24 16:00',30,'Fundamentos mecánica','Ejercicios de dinámica y estática.','Realizada');

-- 13. ASISTENCIA
DROP TABLE IF EXISTS `asistencia`;
CREATE TABLE `asistencia` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `matricula_id` INT NOT NULL,
  `fecha` DATE NOT NULL,
  `tipo` ENUM('Presente','Ausente','Retraso','Justificado') DEFAULT 'Presente',
  `observacion` VARCHAR(200),
  FOREIGN KEY (`matricula_id`) REFERENCES `matriculas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `asistencia` VALUES
(1,1,'2026-02-03','Presente',NULL),(2,1,'2026-02-05','Presente',NULL),(3,1,'2026-02-10','Presente',NULL),
(4,1,'2026-02-12','Retraso','Llegó 10 min tarde'),(5,1,'2026-02-17','Presente',NULL),
(6,1,'2026-02-19','Presente',NULL),(7,1,'2026-02-24','Ausente','Sin justificar'),
(8,1,'2026-02-26','Presente',NULL),(9,1,'2026-03-03','Presente',NULL),(10,1,'2026-03-05','Presente',NULL),
(11,6,'2026-02-04','Presente',NULL),(12,6,'2026-02-06','Presente',NULL),
(13,6,'2026-02-11','Justificado','Justificado médico'),(14,6,'2026-02-13','Presente',NULL),
(15,6,'2026-02-18','Presente',NULL),(16,6,'2026-02-20','Presente',NULL),
(17,11,'2026-02-04','Presente',NULL),(18,11,'2026-02-06','Retraso','Bus con retraso'),
(19,11,'2026-02-11','Presente',NULL),(20,11,'2026-02-13','Presente',NULL),
(21,20,'2026-02-04','Ausente',NULL),(22,20,'2026-02-06','Ausente',NULL),
(23,20,'2026-02-11','Presente',NULL),(24,20,'2026-02-13','Ausente',NULL),
(25,20,'2026-02-18','Presente',NULL),(26,20,'2026-02-20','Presente',NULL);

-- 14. MATERIAL DIDÁCTICO
DROP TABLE IF EXISTS `materiales`;
CREATE TABLE `materiales` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `curso_id` INT NOT NULL,
  `titulo` VARCHAR(150) NOT NULL,
  `descripcion` TEXT,
  `tipo` ENUM('Apuntes','Ejercicios','Presentación','Enlace','Examen_Modelo','Vídeo') DEFAULT 'Apuntes',
  `url` VARCHAR(300),
  `fecha_subida` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `subido_por` INT,
  FOREIGN KEY (`curso_id`) REFERENCES `cursos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subido_por`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `materiales` VALUES
(1,1,'Tema 1 — Vectores y matrices','Apuntes completos con ejemplos resueltos','Apuntes','https://academia.es/files/alg_t1.pdf','2026-02-03 09:00',2),
(2,1,'Ejercicios Tema 1','30 ejercicios de matrices y vectores','Ejercicios','https://academia.es/files/alg_ej1.pdf','2026-02-03 09:05',2),
(3,1,'Tema 2 — Sistemas lineales','Gauss-Jordan y regla de Cramer','Apuntes','https://academia.es/files/alg_t2.pdf','2026-02-10 09:00',2),
(4,1,'Examen modelo 2024-25','Examen del año pasado con soluciones','Examen_Modelo','https://academia.es/files/alg_ex24.pdf','2026-03-10 10:00',2),
(5,3,'Apuntes SQL DDL y DML','CREATE, ALTER, INSERT, UPDATE, DELETE','Apuntes','https://academia.es/files/bd_sql.pdf','2026-02-04 10:00',2),
(6,3,'Ejercicios JOIN y subconsultas','Práctica avanzada de SELECT multitabla','Ejercicios','https://academia.es/files/bd_joins.pdf','2026-02-11 10:00',2),
(7,3,'Slides Normalización','Presentación 1FN, 2FN y 3FN','Presentación','https://academia.es/files/bd_norm.pdf','2026-02-18 10:00',2),
(8,4,'Introducción a Laravel','Documentación Laravel 10','Apuntes','https://laravel.com/docs','2026-02-05 11:00',3),
(9,4,'Plantilla Proyecto Final','Estructura base del proyecto de evaluación','Apuntes','https://academia.es/files/web_tmpl.zip','2026-02-05 11:05',3),
(10,6,'Unit 1 — Technical Vocabulary','Vocabulario técnico informático en inglés','Apuntes','https://academia.es/files/ing_u1.pdf','2026-02-03 12:00',3),
(11,6,'Listening Practice Podcast','Enlace a podcast de tecnología','Enlace','https://techinpodcast.com','2026-02-10 12:00',3),
(12,7,'Slides Python — Tipos de datos','Presentación de la primera clase','Presentación','https://academia.es/files/py_s1.pdf','2026-02-04 08:00',4),
(13,7,'Ejercicios listas y diccionarios','Práctica 1 con soluciones','Ejercicios','https://academia.es/files/py_e1.pdf','2026-02-11 08:00',4),
(14,7,'Tutorial JupyterLab','Vídeo instalación y uso de Jupyter','Vídeo','https://youtube.com/jupyter-tutorial','2026-02-04 08:30',4),
(15,9,'Apuntes Mecánica Clásica','Leyes de Newton y aplicaciones','Apuntes','https://academia.es/files/fis_mec.pdf','2026-02-03 15:00',5),
(16,9,'Guion Práctica 1 Péndulo','Procedimiento y análisis de resultados','Ejercicios','https://academia.es/files/fis_p1.pdf','2026-02-17 15:00',5),
(17,8,'Patrones de Diseño GoF','Resumen de los 23 patrones con ejemplos Java','Apuntes','https://academia.es/files/poo_gof.pdf','2026-02-06 10:00',4),
(18,5,'Guía Cisco Packet Tracer','Tutorial simulación de redes','Enlace','https://netacad.com/packet-tracer','2026-02-05 10:00',2);

SET FOREIGN_KEY_CHECKS = 1;
