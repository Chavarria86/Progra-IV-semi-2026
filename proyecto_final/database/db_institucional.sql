-- =============================================
-- BASE DE DATOS: db_institucional
-- Sistema de Gestión de Pasantías - Génesis Profesional
-- =============================================

CREATE DATABASE IF NOT EXISTS db_institucional;
USE db_institucional;

-- Tabla principal de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  correo VARCHAR(150) UNIQUE NOT NULL,
  contrasena VARCHAR(255) NOT NULL,
  rol ENUM('pasante','supervisor','vice_decano') NOT NULL,
  portafolio_url VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Datos extendidos de pasantes
CREATE TABLE IF NOT EXISTS pasantes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  area ENUM('desarrollo','diseño','infraestructura','seguridad') DEFAULT 'desarrollo',
  tipo_pasantia ENUM('graduacion','horas_sociales','practicas') DEFAULT 'practicas',
  estado ENUM('aprobado','en_proceso','rechazado') DEFAULT 'en_proceso',
  fase_actual ENUM('F1','F2','F3') DEFAULT 'F1',
  supervisor_id INT DEFAULT NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (supervisor_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- CV de los pasantes
CREATE TABLE IF NOT EXISTS cv (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pasante_id INT NOT NULL,
  archivo_url VARCHAR(255),
  contenido_json TEXT,
  estado ENUM('subido','validado','rechazado') DEFAULT 'subido',
  observaciones TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (pasante_id) REFERENCES pasantes(id) ON DELETE CASCADE
);

-- Informes mensuales y finales
CREATE TABLE IF NOT EXISTS informes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pasante_id INT NOT NULL,
  tipo ENUM('mensual','final') NOT NULL,
  archivo_url VARCHAR(255),
  estado ENUM('en_espera','aprobado','no_aprobado') DEFAULT 'en_espera',
  observaciones TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pasante_id) REFERENCES pasantes(id) ON DELETE CASCADE
);

-- Vacantes de empresas
CREATE TABLE IF NOT EXISTS vacantes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  empresa VARCHAR(150) NOT NULL,
  area ENUM('desarrollo','diseño','infraestructura','seguridad') NOT NULL,
  descripcion TEXT,
  estado ENUM('activa','cerrada') DEFAULT 'activa',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Postulaciones de pasantes a vacantes
CREATE TABLE IF NOT EXISTS postulaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pasante_id INT NOT NULL,
  vacante_id INT NOT NULL,
  estado ENUM('pendiente','aceptada','rechazada') DEFAULT 'pendiente',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pasante_id) REFERENCES pasantes(id) ON DELETE CASCADE,
  FOREIGN KEY (vacante_id) REFERENCES vacantes(id) ON DELETE CASCADE
);

-- Recomendaciones de supervisores a pasantes
CREATE TABLE IF NOT EXISTS recomendaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pasante_id INT NOT NULL,
  supervisor_id INT NOT NULL,
  contenido TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pasante_id) REFERENCES pasantes(id) ON DELETE CASCADE,
  FOREIGN KEY (supervisor_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Notificaciones del sistema
CREATE TABLE IF NOT EXISTS notificaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  mensaje TEXT NOT NULL,
  leida BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- =============================================
-- DATOS DE PRUEBA (SEED)
-- =============================================

-- Contraseña para todos los usuarios de prueba: "password123"
-- Hash bcrypt de "password123": $2b$10$YourHashHere (se generará en el seeder de Node.js)

-- Vice Decano
INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol) VALUES
('Carlos', 'Martínez', 'carlos.martinez@ugb.edu.sv', '$2b$10$defaulthash', 'vice_decano');

-- Supervisores
INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol) VALUES
('Ana', 'López', 'ana.lopez@ugb.edu.sv', '$2b$10$defaulthash', 'supervisor'),
('Roberto', 'García', 'roberto.garcia@ugb.edu.sv', '$2b$10$defaulthash', 'supervisor');

-- Pasantes
INSERT INTO usuarios (nombre, apellido, correo, contrasena, rol) VALUES
('José', 'Hernández', 'usss000001@ugb.edu.sv', '$2b$10$defaulthash', 'pasante'),
('María', 'Flores', 'usss000002@ugb.edu.sv', '$2b$10$defaulthash', 'pasante'),
('Pedro', 'Ramírez', 'usss000003@ugb.edu.sv', '$2b$10$defaulthash', 'pasante');

-- Datos de pasantes
INSERT INTO pasantes (usuario_id, area, tipo_pasantia, estado, fase_actual, supervisor_id) VALUES
(4, 'desarrollo', 'practicas', 'en_proceso', 'F1', 2),
(5, 'diseño', 'graduacion', 'en_proceso', 'F1', 2),
(6, 'infraestructura', 'horas_sociales', 'en_proceso', 'F1', 3);

-- Vacantes de ejemplo
INSERT INTO vacantes (empresa, area, descripcion, estado) VALUES
('TechCorp', 'desarrollo', 'Desarrollador Frontend Jr. - React/Vue', 'activa'),
('DesignStudio', 'diseño', 'Diseñador UI/UX para aplicaciones móviles', 'activa'),
('CloudNet', 'infraestructura', 'Administrador de servidores Linux', 'activa'),
('SecureTech', 'seguridad', 'Analista de seguridad informática Jr.', 'activa'),
('DataFlow', 'desarrollo', 'Desarrollador Backend - Node.js/Python', 'activa');
