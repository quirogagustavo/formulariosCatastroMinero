-- =====================================================================
-- ACTUALIZAR POSGAR 94 CON PARÁMETROS IGN PARA SAN JUAN
-- =====================================================================
-- Este script actualiza la definición de POSGAR 94 (SRID 22182)
-- para usar los parámetros oficiales del IGN para la provincia de San Juan
-- =====================================================================

-- PASO 1: Verificar la definición actual
SELECT srid, auth_name, auth_srid, srtext, proj4text
FROM spatial_ref_sys
WHERE srid = 22182;

-- PASO 2: Actualizar con parámetros IGN San Juan
-- Los parámetros +towgs84 siguen el orden: dx,dy,dz,rx,ry,rz,ds
-- Nota: Las rotaciones en PROJ4 se expresan en segundos de arco
UPDATE spatial_ref_sys 
SET proj4text = '+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 +ellps=WGS84 +towgs84=-11.2955,-6.6872,3.8411,0.2146476142,-0.1020253503,0.0631241199,0.0385966400 +units=m +no_defs'
WHERE srid = 22182;

-- PASO 3: Verificar la actualización
SELECT srid, proj4text
FROM spatial_ref_sys
WHERE srid = 22182;

-- =====================================================================
-- NOTA: También debemos actualizar POSGAR 2007
-- =====================================================================
-- Verificar POSGAR 2007 (SRID 5344)
SELECT srid, auth_name, auth_srid, proj4text
FROM spatial_ref_sys
WHERE srid = 5344;

-- Si es necesario, actualizar POSGAR 2007 Faja 2
UPDATE spatial_ref_sys 
SET proj4text = '+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 +ellps=GRS80 +units=m +no_defs'
WHERE srid = 5344;

-- =====================================================================
-- FUNCIÓN DE PRUEBA
-- =====================================================================
-- Probar la transformación con un punto conocido
DO $$
DECLARE
    punto_94 geometry;
    punto_07 geometry;
    x_94 double precision := 6677723.79;  -- NORTE en POSGAR 94
    y_94 double precision := 2492370.91;  -- ESTE en POSGAR 94
    x_07 double precision;
    y_07 double precision;
BEGIN
    -- Crear punto en POSGAR 94 (Y, X porque PostGIS usa ESTE, NORTE)
    punto_94 := ST_SetSRID(ST_MakePoint(y_94, x_94), 22182);
    
    -- Transformar a POSGAR 2007
    punto_07 := ST_Transform(punto_94, 5344);
    
    -- Extraer coordenadas
    x_07 := ST_Y(punto_07);  -- NORTE
    y_07 := ST_X(punto_07);  -- ESTE
    
    RAISE NOTICE 'POSGAR 94 - NORTE: %, ESTE: %', x_94, y_94;
    RAISE NOTICE 'POSGAR 2007 - NORTE: %, ESTE: %', x_07, y_07;
    RAISE NOTICE 'Diferencia - NORTE: % m, ESTE: % m', (x_07 - x_94), (y_07 - y_94);
END $$;

-- =====================================================================
-- RESTAURAR VALORES ORIGINALES (si es necesario)
-- =====================================================================
-- Si necesitas volver a la definición original de PostGIS:

/*
UPDATE spatial_ref_sys 
SET proj4text = '+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 +ellps=WGS84 +units=m +no_defs'
WHERE srid = 22182;
*/
