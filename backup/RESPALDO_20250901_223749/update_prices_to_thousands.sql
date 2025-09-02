-- Script para actualizar precios a formato en miles
-- Los precios se dividirán por 1000 para un formato más amigable

USE nattier_store;

-- Actualizar todos los precios dividiéndolos por 1000
UPDATE producto SET precio = precio / 1000;

-- Verificar los nuevos precios
SELECT 
    id, 
    nombre, 
    CONCAT('$', FORMAT(precio, 0, 'es_CO')) as precio_formateado,
    precio as precio_numerico,
    stock, 
    categoria_id 
FROM producto 
ORDER BY categoria_id, precio;

-- Mostrar resumen por categoría
SELECT 
    c.nombre as categoria,
    COUNT(p.id) as total_productos,
    MIN(p.precio) as precio_minimo,
    MAX(p.precio) as precio_maximo,
    AVG(p.precio) as precio_promedio
FROM producto p
JOIN categoria c ON p.categoria_id = c.id
GROUP BY c.id, c.nombre
ORDER BY c.id;

SELECT '✅ Precios actualizados a formato en miles' as resultado;
