CREATE DATABASE IF NOT EXISTS prestamos_equipos
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE prestamos_equipos;

CREATE TABLE usuarios (
  id_usuario     INT AUTO_INCREMENT PRIMARY KEY,
  nombre         VARCHAR(100)  NOT NULL,
  email          VARCHAR(150)  NOT NULL UNIQUE,
  cedula         VARCHAR(20)   UNIQUE,
  password_hash  VARCHAR(255)  NOT NULL,
  tipo           ENUM('admin','docente','estudiante') NOT NULL DEFAULT 'estudiante',
  carrera        VARCHAR(100),
  estado         ENUM('activo','sancionado') NOT NULL DEFAULT 'activo',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categorias (
  id_categoria      INT AUTO_INCREMENT PRIMARY KEY,
  nombre            VARCHAR(80) NOT NULL,
  descripcion       TEXT,
  dias_max_prestamo INT NOT NULL DEFAULT 3
) ENGINE=InnoDB;

CREATE TABLE equipos (
  id_equipo          INT AUTO_INCREMENT PRIMARY KEY,
  id_categoria       INT NOT NULL,
  codigo_inventario  VARCHAR(50) NOT NULL UNIQUE,
  nombre             VARCHAR(120) NOT NULL,
  marca              VARCHAR(80),
  modelo             VARCHAR(80),
  estado             ENUM('disponible','prestado','mantenimiento','baja') NOT NULL DEFAULT 'disponible',
  condicion          ENUM('bueno','regular','dañado') NOT NULL DEFAULT 'bueno',
  descripcion        TEXT,
  foto_url           VARCHAR(255),
  created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE prestamos (
  id_prestamo                INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario                 INT NOT NULL,
  id_equipo                  INT NOT NULL,
  id_admin_aprueba           INT,
  fecha_solicitud            DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_aprobacion           DATETIME,
  fecha_prestamo             DATETIME,
  fecha_devolucion_esperada  DATE NOT NULL,
  fecha_devolucion_real      DATETIME,
  estado                     ENUM('pendiente','aprobado','activo','devuelto','rechazado','vencido') NOT NULL DEFAULT 'pendiente',
  motivo_solicitud           TEXT,
  observaciones_devolucion   TEXT,
  FOREIGN KEY (id_usuario)       REFERENCES usuarios(id_usuario) ON UPDATE CASCADE,
  FOREIGN KEY (id_equipo)        REFERENCES equipos(id_equipo)   ON UPDATE CASCADE,
  FOREIGN KEY (id_admin_aprueba) REFERENCES usuarios(id_usuario) ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE sanciones (
  id_sancion   INT AUTO_INCREMENT PRIMARY KEY,
  id_prestamo  INT NOT NULL,
  id_usuario   INT NOT NULL,
  motivo       ENUM('retraso','daño','perdida') NOT NULL,
  dias_sancion INT NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin    DATE NOT NULL,
  estado       ENUM('activa','cumplida') NOT NULL DEFAULT 'activa',
  descripcion  TEXT,
  FOREIGN KEY (id_prestamo) REFERENCES prestamos(id_prestamo) ON UPDATE CASCADE,
  FOREIGN KEY (id_usuario)  REFERENCES usuarios(id_usuario)   ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE mantenimientos (
  id_mantenimiento  INT AUTO_INCREMENT PRIMARY KEY,
  id_equipo         INT NOT NULL,
  id_admin          INT NOT NULL,
  tipo              ENUM('preventivo','correctivo') NOT NULL,
  descripcion       TEXT NOT NULL,
  fecha_inicio      DATE NOT NULL,
  fecha_fin         DATE,
  costo             DECIMAL(8,2) DEFAULT 0.00,
  FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo)   ON UPDATE CASCADE,
  FOREIGN KEY (id_admin)  REFERENCES usuarios(id_usuario) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Datos semilla para poder probar el login
INSERT INTO categorias (nombre, descripcion, dias_max_prestamo) VALUES
  ('Laptop', 'Computadoras portátiles', 3),
  ('Proyector', 'Proyectores de vídeo', 2);

INSERT INTO usuarios (nombre, email, password_hash, tipo) VALUES
  ('Administrador', 'admin@universidad.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
  --Email: admin@universidad.edu
  --Password: password