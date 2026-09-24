
CREATE DATABASE IF NOT EXISTS shmebulock
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE shmebulock;


CREATE TABLE usuario (
    id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario  VARCHAR(50)  NOT NULL,
    correo          VARCHAR(100) NOT NULL UNIQUE,
    contrasena      VARCHAR(255) NOT NULL,           
    edad            INT,
    estilo          VARCHAR(50),                     
    perfil          VARCHAR(50),                     
    rol             ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente', 
    fecha_registro  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categoria (
    id_categoria    INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(50) NOT NULL,            -- ej: "Ropa - Deportiva", "Accesorio - Cadenas"
    tipo            ENUM('ropa', 'accesorio') NOT NULL
) ENGINE=InnoDB;

-- Tabla: region

CREATE TABLE region (
    id_region           INT AUTO_INCREMENT PRIMARY KEY,
    nombre              VARCHAR(50) NOT NULL,
    moneda              VARCHAR(10) NOT NULL,        
    recargo_importado   DECIMAL(5,2) NOT NULL DEFAULT 0.00  
) ENGINE=InnoDB;

-- Tabla: producto

CREATE TABLE producto (
    id_producto     INT AUTO_INCREMENT PRIMARY KEY,
    titulo          VARCHAR(100) NOT NULL,
    descripcion     TEXT,
    precio          DECIMAL(10,2) NOT NULL,
    stock           INT NOT NULL DEFAULT 0,
    imagen          VARCHAR(255),                    -- ruta del archivo, ej: img/producto123.jpg
    id_categoria    INT NOT NULL,
    id_region_origen INT,                            -- [AGREGADO] de que region es originario, para calcular recargo por importacion
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria),
    FOREIGN KEY (id_region_origen) REFERENCES region(id_region)
) ENGINE=InnoDB;

-- Tabla: compra

CREATE TABLE compra (
    id_compra       INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario      INT NOT NULL,
    id_region       INT,                             
    fecha           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total           DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_region) REFERENCES region(id_region)
) ENGINE=InnoDB;

-- Detalle: que productos y cuantos, dentro de cada compra
CREATE TABLE compra_detalle (
    id_detalle      INT AUTO_INCREMENT PRIMARY KEY,
    id_compra       INT NOT NULL,
    id_producto     INT NOT NULL,
    cantidad        INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,           
    FOREIGN KEY (id_compra) REFERENCES compra(id_compra) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
) ENGINE=InnoDB;

-- Tabla: carrito

CREATE TABLE carrito (
    id_usuario      INT NOT NULL,
    id_producto     INT NOT NULL,
    cantidad        INT NOT NULL DEFAULT 1,
    PRIMARY KEY (id_usuario, id_producto),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla: favoritos

CREATE TABLE favoritos (
    id_usuario      INT NOT NULL,
    id_producto     INT NOT NULL,
    fecha_agregado  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario, id_producto),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Datos de ejemplo minimos para poder probar la conexion

INSERT INTO region (nombre, moneda, recargo_importado) VALUES
('Argentina', 'ARS', 0.00),
('Estados Unidos', 'USD', 15.00);

INSERT INTO categoria (nombre, tipo) VALUES
('Ropa - Streetwear', 'ropa'),
('Ropa - Y2K', 'ropa'),
('Accesorio - Cadenas', 'accesorio'),
('Accesorio - Anillos', 'accesorio');


INSERT INTO usuario (nombre_usuario, correo, contrasena, edad, estilo, perfil, rol) VALUES
('admin_demo', 'admin@shmebulock.com', 'CAMBIAR_POR_HASH_REAL', 20, 'Alt', 'publico', 'admin');
