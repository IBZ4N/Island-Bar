CREATE DATABASE IslandBar;
USE IslandBar;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(150) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    categoria_id INT,
    FOREIGN KEY (categoria_id) REFERENCES categoria(id)
);

CREATE TABLE imagenes_productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    url_imagen VARCHAR(300) NOT NULL,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

DELIMITER //
CREATE TRIGGER imagen_por_defecto
AFTER INSERT ON productos
FOR EACH ROW
BEGIN
    INSERT INTO imagenes_productos (producto_id, url_imagen)
    VALUES (NEW.id, 'palmera_neon_blanca.png');
END //
DELIMITER ;

INSERT INTO categoria (nombre)
VALUES
('Drinks'),
('Cocktails'),
('Starters'),
('Main Dishes');

INSERT INTO productos (nombre, descripcion, precio, categoria_id)
VALUES
('Viking Craft Beer', 'Strong craft beer with Nordic style.', 12000, 1),
('Frosted Lager Beer', 'Cold lager served in a frosted glass.', 10000, 1),
('Ice Thunder Cocktail', 'Blue vodka cocktail with citrus notes.', 18000, 2),
('Northern Lights Cocktail', 'Fruity mix with white rum and mint.', 20000, 2),
('Snowy Nachos', 'Nachos with cheese, pico de gallo and meat.', 15000, 3),
('Glazed Wings', 'Wings coated in smoky BBQ sauce.', 17000, 3),
('Polar Burger', 'Double beef burger with cheddar.', 25000, 4),
('Island Ribs', 'Slow-cooked BBQ ribs.', 32000, 4),
('Arctic White Pasta', 'Creamy pasta with chicken and parmesan.', 23000, 4);

INSERT INTO usuarios (nombre_completo, correo, nombre_usuario, contrasena)
VALUES
('Administrador Island Bar', 'admin@islandbar.com', 'admin', 'admin123'),
('Ibzan Administrador', 'ibzan@islandbar.com', 'Ibzan', '12345');

UPDATE imagenes_productos SET url_imagen = 'viking_beer.jpg' WHERE producto_id = 1;
UPDATE imagenes_productos SET url_imagen = 'frosted_lager.jpg' WHERE producto_id = 2;
UPDATE imagenes_productos SET url_imagen = 'ice_thunder.jpg' WHERE producto_id = 3;
UPDATE imagenes_productos SET url_imagen = 'northern_lights.jpg' WHERE producto_id = 4;
UPDATE imagenes_productos SET url_imagen = 'snowy_nachos.jpg' WHERE producto_id = 5;
UPDATE imagenes_productos SET url_imagen = 'glazed_wings.jpg' WHERE producto_id = 6;
UPDATE imagenes_productos SET url_imagen = 'polar_burger.jpg' WHERE producto_id = 7;
UPDATE imagenes_productos SET url_imagen = 'island_ribs.jpg' WHERE producto_id = 8;
UPDATE imagenes_productos SET url_imagen = 'arctic_pasta.jpg' WHERE producto_id = 9;
