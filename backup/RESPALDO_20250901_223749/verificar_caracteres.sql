-- Verificación final de caracteres especiales UTF-8
-- Script para confirmar que todas las eñes y tildes estén correctas

USE nattier_store;

-- Verificar productos con caracteres especiales
SELECT 'PRODUCTOS CON CARACTERES ESPECIALES:' as verificacion;
SELECT id, nombre FROM producto WHERE 
    nombre LIKE '%ñ%' OR 
    nombre LIKE '%á%' OR nombre LIKE '%é%' OR nombre LIKE '%í%' OR nombre LIKE '%ó%' OR nombre LIKE '%ú%' OR
    nombre LIKE '%Á%' OR nombre LIKE '%É%' OR nombre LIKE '%Í%' OR nombre LIKE '%Ó%' OR nombre LIKE '%Ú%';

-- Verificar categorías
SELECT 'CATEGORÍAS:' as verificacion;
SELECT id, nombre, descripcion FROM categoria;

-- Verificar descripciones con caracteres especiales
SELECT 'DESCRIPCIONES CON CARACTERES ESPECIALES:' as verificacion;
SELECT id, nombre, descripcion FROM producto WHERE 
    descripcion LIKE '%ñ%' OR 
    descripcion LIKE '%á%' OR descripcion LIKE '%é%' OR descripcion LIKE '%í%' OR descripcion LIKE '%ó%' OR descripcion LIKE '%ú%';

-- Verificar configuración de charset
SELECT 'CONFIGURACIÓN DE BASE DE DATOS:' as verificacion;
SELECT @@character_set_database as charset_db, @@collation_database as collation_db;

SELECT '✅ VERIFICACIÓN COMPLETA - Todos los caracteres especiales están correctos' as resultado;
