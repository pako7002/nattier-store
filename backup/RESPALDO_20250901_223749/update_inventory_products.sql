-- Script para actualizar productos según Inventario.txt
-- Actualizando el catálogo a productos de moda de playa

USE nattier_store;

-- Actualizar productos con información del inventario real
UPDATE producto SET 
    nombre = 'Set Verano Top y Short Cachetero',
    descripcion = 'Hermoso set de verano con top y short cachetero, perfecto para la playa',
    precio = 90000.00
WHERE id = 1;

UPDATE producto SET 
    nombre = 'Set Love Top y Short Flecos',
    descripcion = 'Conjunto encantador con top y short con detalles de flecos',
    precio = 90000.00
WHERE id = 2;

UPDATE producto SET 
    nombre = 'Set Dúo Incandescente Blanco',
    descripcion = 'Elegante set de dos piezas en color blanco incandescente',
    precio = 120000.00
WHERE id = 3;

UPDATE producto SET 
    nombre = 'Bikini Conchas Beige',
    descripcion = 'Bikini con diseño de conchas en tono beige natural',
    precio = 87000.00
WHERE id = 4;

UPDATE producto SET 
    nombre = 'Set Baby Tono Blanco',
    descripcion = 'Delicado set en tonos blancos suaves, estilo baby',
    precio = 125000.00
WHERE id = 5;

UPDATE producto SET 
    nombre = 'Salida de Playa Flecos',
    descripcion = 'Elegante salida de playa con detalles de flecos',
    precio = 80000.00
WHERE id = 6;

UPDATE producto SET 
    nombre = 'Bikini Fiesta Strapless Lycra',
    descripcion = 'Bikini strapless en lycra, ideal para fiestas de playa',
    precio = 120000.00
WHERE id = 7;

UPDATE producto SET 
    nombre = 'Set Tres Piezas Bikini y Salida',
    descripcion = 'Conjunto completo de tres piezas: bikini y salida de playa',
    precio = 170000.00
WHERE id = 8;

UPDATE producto SET 
    nombre = 'Set Terracota Top, Bikini y Falda',
    descripcion = 'Conjunto completo en tono terracota con top, bikini y falda',
    precio = 190000.00
WHERE id = 9;

UPDATE producto SET 
    nombre = 'Set Flores en Trío Beige',
    descripcion = 'Hermoso conjunto de tres piezas con estampado floral en beige',
    precio = 170000.00
WHERE id = 10;

UPDATE producto SET 
    nombre = 'Bikini Imperial Macramé y Lycra',
    descripcion = 'Bikini de lujo con detalles de macramé y lycra de alta calidad',
    precio = 118000.00
WHERE id = 11;

UPDATE producto SET 
    nombre = 'Vestido Crochet Marfil',
    descripcion = 'Elegante vestido tejido en crochet color marfil',
    precio = 100000.00
WHERE id = 12;

UPDATE producto SET 
    nombre = 'Outfit Playa 2 Piezas Vestido y Pava',
    descripcion = 'Conjunto de playa de dos piezas con vestido y sombrero pava',
    precio = 130000.00
WHERE id = 13;

UPDATE producto SET 
    nombre = 'Outfit Elegance Tres Piezas Incluye Bolso',
    descripcion = 'Conjunto elegante de tres piezas que incluye bolso de mano',
    precio = 150000.00
WHERE id = 14;

UPDATE producto SET 
    nombre = 'Trío Bikini Bordados en Lycra',
    descripcion = 'Conjunto de tres piezas de bikini con bordados en lycra',
    precio = 90000.00
WHERE id = 15;

UPDATE producto SET 
    nombre = 'Bolso Riñonera Tres Tonos',
    descripcion = 'Práctica riñonera en combinación de tres tonos',
    precio = 35000.00
WHERE id = 16;

UPDATE producto SET 
    nombre = 'Adorno Pared Beige',
    descripcion = 'Decorativo adorno para pared en tono beige',
    precio = 25000.00
WHERE id = 17;

-- Verificar la actualización
SELECT id, nombre, precio FROM producto ORDER BY id;

SELECT 'Productos actualizados exitosamente según inventario' as mensaje;
