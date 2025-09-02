-- Script de diagnóstico para verificar la estructura de la base de datos
USE nattier_store;

-- Verificar si las tablas existen
SELECT 
    TABLE_NAME as 'Tabla',
    TABLE_ROWS as 'Filas',
    CREATE_TIME as 'Fecha_Creacion'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'nattier_store'
ORDER BY TABLE_NAME;

-- Verificar estructura de la tabla categoria
DESCRIBE categoria;

-- Verificar estructura de la tabla usuario  
DESCRIBE usuario;

-- Verificar estructura de la tabla producto
DESCRIBE producto;

-- Mostrar datos actuales
SELECT 'CATEGORIAS' as tabla;
SELECT * FROM categoria;

SELECT 'USUARIOS' as tabla;
SELECT id, nombre, email, rol FROM usuario;

SELECT 'PRODUCTOS (primeros 5)' as tabla;
SELECT id, nombre, precio, stock, categoria_id FROM producto LIMIT 5;
