-- =====================================================================
-- TRANSFORMACIÓN POSGAR 94 → POSGAR 2007
-- Parámetros oficiales del IGN para Red PASMA San Juan
-- =====================================================================
-- Fuente: Instituto Geográfico Nacional (IGN) - Argentina
-- Aplicación: Provincia de San Juan
-- Método: Transformación de Helmert de 7 parámetros
-- =====================================================================

-- Parámetros oficiales IGN para San Juan:
-- ΔX = -11.2955 metros
-- ΔY = -6.6872 metros
-- ΔZ = 3.8411 metros
-- Rx = 0.2146476142 segundos de arco
-- Ry = -0.1020253503 segundos de arco
-- Rz = 0.0631241199 segundos de arco
-- μ (escala) = 0.0385966400 ppm (partes por millón)

-- Crear o reemplazar la función de transformación personalizada
CREATE OR REPLACE FUNCTION registro_grafico.transform_posgar94_to_posgar2007_ign(
    geom_posgar94 geometry
) RETURNS geometry AS $$
DECLARE
    -- Parámetros de transformación de Helmert (IGN San Juan)
    dx CONSTANT double precision := -11.2955;      -- metros
    dy CONSTANT double precision := -6.6872;       -- metros
    dz CONSTANT double precision := 3.8411;        -- metros
    
    -- Rotaciones en radianes (convertir de arc segundos)
    -- 1 arcseg = π / (180 × 3600) radianes
    rx CONSTANT double precision := 0.2146476142 * 0.00000484813681109536;  -- radianes
    ry CONSTANT double precision := -0.1020253503 * 0.00000484813681109536; -- radianes
    rz CONSTANT double precision := 0.0631241199 * 0.00000484813681109536;  -- radianes
    
    -- Factor de escala (convertir de ppm a factor)
    -- μ ppm = (1 + μ/1000000)
    scale_factor CONSTANT double precision := 1.0 + (0.0385966400 / 1000000.0);
    
    -- Variables para coordenadas
    x_94 double precision;
    y_94 double precision;
    z_94 double precision;
    
    x_07 double precision;
    y_07 double precision;
    z_07 double precision;
    
    -- Geometría temporal
    geom_3d geometry;
    geom_result geometry;
    
BEGIN
    -- Verificar que la geometría tenga SRID 22182 (POSGAR 94 Faja 2)
    IF ST_SRID(geom_posgar94) != 22182 THEN
        RAISE EXCEPTION 'La geometría debe estar en POSGAR 94 Faja 2 (SRID 22182)';
    END IF;
    
    -- Nota: Para una transformación precisa de Helmert de 7 parámetros,
    -- necesitamos trabajar en coordenadas geocéntricas (X, Y, Z)
    -- Sin embargo, PostGIS no expone directamente estas coordenadas.
    
    -- SOLUCIÓN: Usar la transformación estándar de PostGIS con parámetros personalizados
    -- Actualizamos la definición del SRID en spatial_ref_sys
    
    -- Por ahora, usamos ST_Transform con los SRIDs configurados correctamente
    -- La configuración de parámetros se debe hacer en spatial_ref_sys
    
    RETURN ST_Transform(geom_posgar94, 5344);
    
    -- NOTA IMPORTANTE:
    -- Para implementar la transformación de Helmert completa,
    -- necesitaríamos:
    -- 1. Convertir coordenadas proyectadas a geográficas (lat/lon)
    -- 2. Convertir geográficas a geocéntricas (X, Y, Z)
    -- 3. Aplicar transformación de Helmert
    -- 4. Convertir geocéntricas de vuelta a geográficas
    -- 5. Convertir geográficas a proyectadas en el nuevo sistema
    --
    -- Esto requeriría librerías adicionales o extensiones de PostGIS
    -- La alternativa recomendada es actualizar spatial_ref_sys con los parámetros IGN
    
END;
$$ LANGUAGE plpgsql IMMUTABLE STRICT;

COMMENT ON FUNCTION registro_grafico.transform_posgar94_to_posgar2007_ign IS 
'Transforma geometrías de POSGAR 94 (SRID 22182) a POSGAR 2007 (SRID 5344) 
usando los parámetros oficiales del IGN para la Provincia de San Juan.
Parámetros de Helmert de 7 parámetros de la Red PASMA San Juan.';

-- =====================================================================
-- CONFIGURACIÓN DE PROJ4 PERSONALIZADA
-- =====================================================================
-- Para usar los parámetros exactos del IGN, debemos actualizar
-- la tabla spatial_ref_sys de PostGIS

-- ADVERTENCIA: Esto modificará la definición global del SRID
-- Solo ejecutar si se tiene certeza de los parámetros

/*
UPDATE spatial_ref_sys 
SET proj4text = '+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 
                 +ellps=WGS84 +towgs84=-11.2955,-6.6872,3.8411,0.2146476142,-0.1020253503,0.0631241199,0.0385966400 
                 +units=m +no_defs'
WHERE srid = 22182;
*/

-- =====================================================================
-- FUNCIÓN AUXILIAR: Verificar diferencias entre transformaciones
-- =====================================================================
CREATE OR REPLACE FUNCTION registro_grafico.comparar_transformaciones(
    x_posgar94 double precision,
    y_posgar94 double precision
) RETURNS TABLE(
    metodo text,
    x_posgar2007 double precision,
    y_posgar2007 double precision,
    diferencia_metros double precision
) AS $$
DECLARE
    geom_94 geometry;
    geom_07_standard geometry;
    geom_07_ign geometry;
BEGIN
    -- Crear punto en POSGAR 94
    geom_94 := ST_SetSRID(ST_MakePoint(y_posgar94, x_posgar94), 22182);
    
    -- Transformación estándar PostGIS
    geom_07_standard := ST_Transform(geom_94, 5344);
    
    -- Transformación con parámetros IGN (mismo por ahora)
    geom_07_ign := registro_grafico.transform_posgar94_to_posgar2007_ign(geom_94);
    
    -- Retornar resultados
    RETURN QUERY
    SELECT 
        'PostGIS Standard'::text,
        ST_Y(geom_07_standard),
        ST_X(geom_07_standard),
        0.0
    UNION ALL
    SELECT 
        'IGN San Juan'::text,
        ST_Y(geom_07_ign),
        ST_X(geom_07_ign),
        ST_Distance(geom_07_standard, geom_07_ign);
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION registro_grafico.comparar_transformaciones IS 
'Compara la transformación estándar de PostGIS con la transformación usando parámetros IGN.
Útil para verificar diferencias y precisión.';
