# Transformación de Coordenadas POSGAR 94 → POSGAR 2007
## Implementación con Parámetros Oficiales del IGN para San Juan

---

## 📋 Resumen Ejecutivo

Este documento describe la implementación de la transformación de coordenadas POSGAR 94 a POSGAR 2007 utilizando los **parámetros oficiales del Instituto Geográfico Nacional (IGN)** para la provincia de San Juan, Argentina.

**Fecha de implementación:** 6 de noviembre de 2025  
**Commit:** 1d34eb8  
**Sistema afectado:** Formularios de Catastro Minero - Solicitud de Petición de Mensura

---

## 🎯 Objetivo

Aplicar la transformación geodésica más precisa posible entre los sistemas de referencia POSGAR 94 y POSGAR 2007, utilizando los parámetros de transformación de Helmert de 7 parámetros específicos para la provincia de San Juan, obtenidos de la Red PASMA del IGN.

---

## 📊 Parámetros de Transformación IGN

Los siguientes parámetros son **oficiales** del Instituto Geográfico Nacional para la provincia de San Juan:

| Parámetro | Símbolo | Valor | Unidad |
|-----------|---------|-------|--------|
| Traslación X | ΔX | -11.2955 | metros |
| Traslación Y | ΔY | -6.6872 | metros |
| Traslación Z | ΔZ | 3.8411 | metros |
| Rotación X | Rx | 0.2146476142 | segundos de arco |
| Rotación Y | Ry | -0.1020253503 | segundos de arco |
| Rotación Z | Rz | 0.0631241199 | segundos de arco |
| Factor de escala | μ | 0.0385966400 | ppm |

**Fuente:** Red PASMA (Posiciones Geodésicas de Alta Precisión para San Juan y Mendoza)  
**Método:** Transformación de Helmert de 7 parámetros

---

## 🔬 Diferencias con Transformación Estándar

Prueba realizada con coordenadas de ejemplo:
- **POSGAR 94:** NORTE = 6677723.79 m, ESTE = 2492370.91 m

### Resultados de Transformación a POSGAR 2007:

| Método | NORTE (2007) | ESTE (2007) | Diferencia NORTE | Diferencia ESTE |
|--------|--------------|-------------|------------------|-----------------|
| PostGIS estándar | 6677724.38 m | 2492371.13 m | - | - |
| **IGN San Juan** | **6677723.15 m** | **2492362.31 m** | **-1.23 m** | **-8.82 m** |

### Conclusión:
La transformación con parámetros IGN difiere significativamente de la transformación estándar de PostGIS:
- **1.23 metros** en dirección NORTE
- **8.82 metros** en dirección ESTE

Esta diferencia es **geodésicamente significativa** para trabajos catastrales de precisión.

---

## 🛠️ Implementación Técnica

### 1. SRID Personalizado

Se creó un nuevo SRID personalizado en la base de datos PostGIS:

- **SRID:** 922182
- **Nombre:** POSGAR 94 / Argentina 2 (IGN San Juan)
- **Tipo:** CUSTOM

**Definición PROJ4:**
```
+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 
+ellps=WGS84 
+towgs84=-11.2955,-6.6872,3.8411,0.2146476142,-0.1020253503,0.0631241199,0.0385966400 
+units=m +no_defs
```

### 2. Función PostgreSQL

**Nombre:** `transform_posgar94_to_posgar2007_ign(geometry)`

**Ubicación:** Base de datos `catastrominero`

**Descripción:**
```sql
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
```

### 3. Integración en Formularios

**Archivo modificado:** `guardar_formulario_solicitud_peticion_mensura.php`

**Línea 70 (Perímetro de Mensura):**
```php
// ANTES:
ST_Transform(ST_GeomFromText($6,22182), 5344)

// DESPUÉS:
transform_posgar94_to_posgar2007_ign(ST_GeomFromText($6,22182))
```

**Línea 115 (Pertenencias):**
```php
// ANTES:
ST_Transform(ST_GeomFromText($4,22182), 5344)

// DESPUÉS:
transform_posgar94_to_posgar2007_ign(ST_GeomFromText($4,22182))
```

---

## ✅ Ventajas de la Implementación

### 1. **Precisión Geodésica**
- Utiliza parámetros oficiales del IGN
- Específicos para la provincia de San Juan
- Mayor precisión que transformación genérica

### 2. **No Invasiva**
- **NO modifica** el SRID 22182 original de PostGIS
- **NO afecta** datos ya registrados en la base de datos
- Solo se aplica en formularios nuevos cuando el usuario selecciona POSGAR 94

### 3. **Transparente para el Usuario**
- El usuario simplemente selecciona "POSGAR 94" en el formulario
- La transformación se aplica automáticamente
- No requiere intervención manual

### 4. **Reversible**
- Si es necesario volver al método anterior, solo hay que cambiar la llamada a la función
- Los datos en la BD siempre están en POSGAR 2007 (SRID 5344)

### 5. **Auditable**
- Función de comparación disponible para verificar diferencias
- Documentación completa de parámetros utilizados

---

## 📁 Archivos del Sistema

### Scripts SQL Creados:

1. **`sql/funcion_transform_ign_sanjuan.sql`**
   - Crea el SRID personalizado 922182
   - Crea la función `transform_posgar94_to_posgar2007_ign()`
   - Crea la función de prueba `comparar_transformaciones()`
   - **ESTE ES EL ARCHIVO PRINCIPAL A EJECUTAR**

2. **`sql/transformacion_ign_sanjuan.sql`**
   - Documentación extendida de parámetros
   - Función alternativa con validaciones adicionales
   - (Opcional - solo documentación)

3. **`sql/actualizar_posgar94_ign.sql`**
   - **NO USAR** - Modifica SRID global (afectaría toda la BD)
   - Solo para referencia

### Archivos PHP Modificados:

1. **`guardar_formulario_solicitud_peticion_mensura.php`**
   - Líneas 70 y 115 modificadas
   - Usa la nueva función de transformación IGN

---

## 🔄 Proceso de Transformación

```
┌─────────────────────────────────────────────────────────────┐
│  USUARIO INGRESA COORDENADAS EN FORMULARIO                  │
│  (Selecciona: POSGAR 94)                                    │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  JavaScript: Validación y construcción de WKT               │
│  POLYGON((Y X, Y X, ...)) donde Y=ESTE, X=NORTE            │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  PHP: guardar_formulario_solicitud_peticion_mensura.php     │
│  Detecta que srid_origen = 22182 (POSGAR 94)               │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  SQL: ST_GeomFromText($wkt, 22182)                          │
│  Crea geometría con SRID 22182                             │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  SQL: transform_posgar94_to_posgar2007_ign(geom)            │
│  1. Cambia SRID a 922182 (con parámetros IGN)              │
│  2. ST_Transform(geom, 5344) aplica transformación         │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│  BASE DE DATOS: gra_cm_mensura_area_pga07                   │
│  Geometría almacenada en POSGAR 2007 (SRID 5344)           │
│  CON PRECISIÓN IGN                                          │
└─────────────────────────────────────────────────────────────┘
```

---

## 🧪 Función de Prueba

Para verificar las diferencias entre las transformaciones:

```sql
SELECT * FROM comparar_transformaciones(6677723.79, 2492370.91);
```

**Resultado esperado:**
```
      metodo       |    norte_2007     |     este_2007     | diff_norte | diff_este
-------------------+-------------------+-------------------+------------+------------
 PostGIS estándar  | 6677724.3815087965| 2492371.1283056596|     0      |     0
 IGN San Juan      | 6677723.148738802 | 2492362.3087951373| -1.2327... | -8.8195...
```

---

## 📝 Instalación y Configuración

### Paso 1: Ejecutar Script SQL

```bash
PGPASSWORD='password' psql -h host -U postgres -d catastrominero \
  -f sql/funcion_transform_ign_sanjuan.sql
```

**Resultado esperado:**
```
DELETE 0
INSERT 0 1
CREATE FUNCTION
COMMENT
CREATE FUNCTION
```

### Paso 2: Verificar Instalación

```sql
-- Verificar que existe el SRID personalizado
SELECT srid, auth_name, srtext 
FROM spatial_ref_sys 
WHERE srid = 922182;

-- Verificar que existe la función
SELECT proname, prosrc 
FROM pg_proc 
WHERE proname = 'transform_posgar94_to_posgar2007_ign';
```

### Paso 3: Probar Transformación

```sql
-- Probar con coordenadas conocidas
SELECT * FROM comparar_transformaciones(6677723.79, 2492370.91);
```

### Paso 4: Desplegar Formulario PHP

El archivo `guardar_formulario_solicitud_peticion_mensura.php` ya está actualizado en el repositorio. Solo asegurarse de que esté desplegado en el servidor web.

---

## ⚠️ Consideraciones Importantes

### 1. Alcance Geográfico
Los parámetros IGN implementados son **específicos para San Juan**. Si el sistema se extiende a otras provincias, se necesitarán parámetros diferentes.

### 2. Datos Existentes
Esta implementación **NO afecta** datos ya registrados. Todos los polígonos en la base de datos permanecen sin cambios. Solo se aplica a nuevos ingresos mediante formularios.

### 3. Sistemas de Coordenadas
- **POSGAR 94 Faja 2:** SRID 22182 (entrada del usuario)
- **POSGAR 94 IGN San Juan:** SRID 922182 (transformación intermedia)
- **POSGAR 2007 Faja 2:** SRID 5344 (almacenamiento final)

### 4. Orden de Coordenadas
PostGIS almacena en orden (ESTE, NORTE) = (Y, X), por lo que las coordenadas se invierten antes de crear el WKT.

---

## 🔍 Validación y Auditoría

### Verificar Transformación de un Polígono Específico

```sql
-- Ver expediente con su geometría original
SELECT 
    expte_siged,
    ST_AsText(geom) as geometria,
    ST_Area(geom)/10000 as superficie_ha
FROM registro_grafico.gra_cm_mensura_area_pga07
WHERE expte_siged = 'XXXX-XXXXXX-XXXX-EXP';

-- Comparar vértices
SELECT 
    ST_Y((dp).geom) as norte,
    ST_X((dp).geom) as este
FROM registro_grafico.gra_cm_mensura_area_pga07 t
JOIN LATERAL ST_DumpPoints(t.geom) AS dp ON true
WHERE t.expte_siged = 'XXXX-XXXXXX-XXXX-EXP';
```

---

## 📚 Referencias

1. **Instituto Geográfico Nacional (IGN)**
   - Red PASMA - Posiciones Geodésicas de Alta Precisión
   - Parámetros de transformación POSGAR 94 → POSGAR 2007

2. **POSGAR (POsiciones Geodésicas ARgentinas)**
   - POSGAR 94: Sistema geodésico basado en WGS84 (época 1994)
   - POSGAR 2007: Sistema geodésico basado en ITRF2005 (época 2006.632)

3. **Transformación de Helmert**
   - 7 parámetros: 3 traslaciones, 3 rotaciones, 1 factor de escala
   - Método estándar para transformaciones entre datums

4. **PostGIS**
   - Extensión espacial de PostgreSQL
   - Función ST_Transform para transformaciones de coordenadas
   - Tabla spatial_ref_sys con definiciones SRID

---

## 👥 Información de Contacto

**Desarrollador:** Gustavo Quiroga  
**Repositorio:** https://github.com/quirogagustavo/formulariosCatastroMinero  
**Commit:** 1d34eb8  
**Fecha:** 6 de noviembre de 2025

---

## 📄 Licencia y Uso

Los parámetros de transformación utilizados son **oficiales del IGN** y de dominio público para uso en aplicaciones geodésicas en Argentina.

Esta implementación está diseñada específicamente para el sistema de **Catastro Minero de la Provincia de San Juan** y debe utilizarse únicamente dentro de este contexto geográfico.

---

## 🔄 Historial de Versiones

| Versión | Fecha | Descripción | Commit |
|---------|-------|-------------|--------|
| 1.0 | 2025-11-06 | Implementación inicial con parámetros IGN San Juan | 1d34eb8 |

---

**FIN DEL DOCUMENTO**
