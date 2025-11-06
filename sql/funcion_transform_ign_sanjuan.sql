-- =====================================================================
-- PASO 1: CREAR SRID PERSONALIZADO CON PARÁMETROS IGN PARA SAN JUAN
-- =====================================================================
-- PARÁMETROS IGN PARA SAN JUAN (Red PASMA):
-- Fuente: Documento oficial IGN - Tabla de parámetros PASMA
-- ΔX = -11.340 m
-- ΔY = -6.686 m  
-- ΔZ = 3.836 m
-- Rx = 0.214569 arcsec
-- Ry = -0.102025 arcsec
-- Rz = 0.374988 arcsec
-- μ = 0.1211736000 ppm
-- Precisión: ±0.003m Este, ±0.003m Norte, ±0.005m Altura

-- Eliminar si existe
DELETE FROM spatial_ref_sys WHERE srid = 922182;

-- Crear SRID personalizado para POSGAR 94 con parámetros IGN
INSERT INTO spatial_ref_sys (srid, auth_name, auth_srid, srtext, proj4text)
VALUES (
    922182,
    'CUSTOM',
    922182,
    'PROJCS["POSGAR 94 / Argentina 2 (IGN San Juan)",GEOGCS["POSGAR 94",DATUM["Posiciones_Geodesicas_Argentinas_1994",SPHEROID["WGS 84",6378137,298.257223563,AUTHORITY["EPSG","7030"]],TOWGS84[-11.340,-6.686,3.836,0.214569,-0.102025,0.374988,0.1211736000],AUTHORITY["EPSG","6694"]],PRIMEM["Greenwich",0,AUTHORITY["EPSG","8901"]],UNIT["degree",0.0174532925199433,AUTHORITY["EPSG","9122"]],AUTHORITY["EPSG","4694"]],PROJECTION["Transverse_Mercator"],PARAMETER["latitude_of_origin",-90],PARAMETER["central_meridian",-69],PARAMETER["scale_factor",1],PARAMETER["false_easting",2500000],PARAMETER["false_northing",0],UNIT["metre",1,AUTHORITY["EPSG","9001"]],AUTHORITY["EPSG","22182"]]',
    '+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 +ellps=WGS84 +towgs84=-11.340,-6.686,3.836,0.214569,-0.102025,0.374988,0.1211736000 +units=m +no_defs'
);

-- =====================================================================
-- PASO 2: CREAR FUNCIÓN DE TRANSFORMACIÓN
-- =====================================================================
CREATE OR REPLACE FUNCTION transform_posgar94_to_posgar2007_ign(geom_posgar94 geometry)
RETURNS geometry AS $$
DECLARE
    geom_result geometry;
BEGIN
    -- Cambiar el SRID de la geometría al personalizado con parámetros IGN
    geom_result := ST_SetSRID(geom_posgar94, 922182);
    
    -- Transformar a POSGAR 2007 (EPSG:5344)
    geom_result := ST_Transform(geom_result, 5344);
    
    RETURN geom_result;
END;
$$ LANGUAGE plpgsql IMMUTABLE;

-- =====================================================================
-- COMENTARIOS SOBRE LA FUNCIÓN
-- =====================================================================
COMMENT ON FUNCTION transform_posgar94_to_posgar2007_ign IS 
'Transforma geometrías de POSGAR 94 (Faja 2) a POSGAR 2007 (Faja 2) 
usando los parámetros oficiales del IGN para la provincia de San Juan.
Parámetros Helmert 7: ΔX=-11.340, ΔY=-6.686, ΔZ=3.836, 
Rx=0.214569, Ry=-0.102025, Rz=0.374988, μ=0.1211736000
Precisión: ±0.003m Este, ±0.003m Norte, ±0.005m Altura';

-- =====================================================================
-- FUNCIÓN DE PRUEBA Y COMPARACIÓN
-- =====================================================================
CREATE OR REPLACE FUNCTION comparar_transformaciones(
    x_norte double precision,
    y_este double precision
)
RETURNS TABLE(
    metodo text,
    norte_2007 double precision,
    este_2007 double precision,
    diff_norte double precision,
    diff_este double precision
) AS $$
DECLARE
    punto_94 geometry;
    punto_07_postgis geometry;
    punto_07_ign geometry;
    norte_postgis double precision;
    este_postgis double precision;
    norte_ign double precision;
    este_ign double precision;
BEGIN
    -- Crear punto en POSGAR 94 (recordar: PostGIS usa Y,X = ESTE,NORTE)
    punto_94 := ST_SetSRID(ST_MakePoint(y_este, x_norte), 22182);
    
    -- Transformación estándar de PostGIS
    punto_07_postgis := ST_Transform(punto_94, 5344);
    norte_postgis := ST_Y(punto_07_postgis);
    este_postgis := ST_X(punto_07_postgis);
    
    -- Transformación con parámetros IGN
    punto_07_ign := transform_posgar94_to_posgar2007_ign(punto_94);
    norte_ign := ST_Y(punto_07_ign);
    este_ign := ST_X(punto_07_ign);
    
    -- Retornar resultados
    RETURN QUERY
    SELECT 'PostGIS estándar'::text, norte_postgis, este_postgis, 0.0::double precision, 0.0::double precision
    UNION ALL
    SELECT 'IGN San Juan'::text, norte_ign, este_ign, 
           (norte_ign - norte_postgis), (este_ign - este_postgis);
END;
$$ LANGUAGE plpgsql;

-- =====================================================================
-- PRUEBA DE LA FUNCIÓN
-- =====================================================================
-- Probar con coordenadas de ejemplo
SELECT * FROM comparar_transformaciones(6677723.79, 2492370.91);

-- =====================================================================
-- EJEMPLO DE USO EN INSERT
-- =====================================================================
/*
-- En lugar de usar ST_Transform estándar:
INSERT INTO gra_mensura_area (geom, ...) 
VALUES (ST_Transform(ST_GeomFromText('POLYGON((...))', 22182), 5344), ...);

-- Usar la función IGN:
INSERT INTO gra_mensura_area (geom, ...) 
VALUES (transform_posgar94_to_posgar2007_ign(ST_GeomFromText('POLYGON((...))', 22182)), ...);
*/
