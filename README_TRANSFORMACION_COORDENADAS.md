# Sistema de Transformación de Coordenadas POSGAR94 → POSGAR2007

## 📋 Descripción

Este sistema permite a los usuarios ingresar coordenadas en **POSGAR 94** y transformarlas automáticamente a **POSGAR 2007** antes de guardarlas en la base de datos.

## 🎯 Objetivo

Facilitar la transición del sistema de coordenadas POSGAR 94 a POSGAR 2007, permitiendo que los usuarios trabajen con el sistema que tengan disponible, mientras que internamente el sistema siempre almacena en POSGAR 2007.

## 📂 Archivos Implementados

### 1. `transformar_coordenadas.php`
- Script backend que realiza la transformación de coordenadas
- Aplica los parámetros de transformación oficiales del IGN
- Retorna las coordenadas transformadas en formato JSON

### 2. `transformador_coordenadas.js`
- Módulo JavaScript para manejar la interfaz de transformación
- Gestiona el selector de sistema de coordenadas
- Muestra vista previa de las coordenadas transformadas
- Integra automáticamente con los formularios existentes

### 3. Formularios Modificados
Los siguientes formularios ahora incluyen el selector de sistema de coordenadas:

- `formulario_solicitud_permiso_exploracion.php`
- `formulario_solicitud_manifestacion.php`
- `formulario_solicitud_canteras.php`
- `formulario_solicitud_peticion_mensura.php`

## 🚀 Uso del Sistema

### Para el Usuario

1. **Seleccionar Sistema de Coordenadas**
   - Al inicio del formulario, encontrará un selector con dos opciones:
     - **POSGAR 2007 (Por defecto)**: Las coordenadas se guardarán tal como se ingresan
     - **POSGAR 94**: Las coordenadas serán transformadas automáticamente a POSGAR 2007

2. **Ingresar Coordenadas**
   - Ingrese las coordenadas Este y Norte en el sistema seleccionado
   - Si eligió POSGAR 94, el sistema mostrará automáticamente:
     - Las coordenadas originales (POSGAR 94)
     - Las coordenadas transformadas (POSGAR 2007)
     - Los valores de corrección aplicados (Δ Este, Δ Norte)

3. **Verificar Transformación**
   - Antes de agregar el punto, se mostrará un diálogo de confirmación
   - Revise que las coordenadas transformadas sean correctas
   - Confirme para agregar el punto

4. **Guardar**
   - Las coordenadas se guardarán en la base de datos en **POSGAR 2007**
   - Independientemente del sistema en que fueron ingresadas

### Ejemplo Práctico

**Coordenadas ingresadas en POSGAR 94:**
- Este: 2457558.74 m
- Norte: 6557062.97 m

**Coordenadas transformadas a POSGAR 2007:**
- Este: 2457558.77 m (+0.031 m)
- Norte: 6557063.12 m (+0.146 m)

## ⚙️ Parámetros de Transformación

Los parámetros utilizados son los oficiales publicados por el IGN para la región de San Juan:

- **ΔX (Este)**: +0.031 metros
- **ΔY (Norte)**: +0.146 metros

### Nota Importante
Estos parámetros son aproximados para la región de San Juan. Para mayor precisión en áreas específicas, se recomienda consultar los parámetros regionales oficiales del Instituto Geográfico Nacional (IGN).

## 🔧 Características Técnicas

### Frontend (JavaScript)
- **Validación en tiempo real**: Verifica que las coordenadas sean válidas antes de transformar
- **Vista previa interactiva**: Muestra los resultados de la transformación antes de confirmar
- **Integración transparente**: Se integra con las funciones existentes de los formularios
- **Manejo de errores**: Informa al usuario si ocurre algún problema

### Backend (PHP)
- **API RESTful**: Endpoint GET para transformar coordenadas
- **Validación de datos**: Verifica que las coordenadas sean válidas
- **Respuesta JSON**: Formato estándar para fácil integración
- **Sin modificación de BD**: No se requieren cambios en la base de datos

### Respuesta de la API

```json
{
  "success": true,
  "este_original": 2457558.74,
  "norte_original": 6557062.97,
  "este_transformado": 2457558.77,
  "norte_transformado": 6557063.12,
  "delta_este": 0.031,
  "delta_norte": 0.146,
  "sistema_origen": "POSGAR 94",
  "sistema_destino": "POSGAR 2007",
  "mensaje": "Coordenadas transformadas correctamente"
}
```

## 📊 Ventajas del Sistema

1. **Transparencia**: El usuario ve exactamente qué transformación se aplica
2. **Flexibilidad**: Permite trabajar con ambos sistemas de coordenadas
3. **Consistencia**: Todos los datos se almacenan en el mismo sistema (POSGAR 2007)
4. **Sin cambios en BD**: No requiere modificar la estructura de la base de datos
5. **Fácil auditoría**: Todas las transformaciones se pueden verificar
6. **Experiencia de usuario**: Interfaz clara y confirmación antes de guardar

## 🔄 Flujo de Trabajo

```
Usuario selecciona sistema → Ingresa coordenadas → 
Sistema transforma (si es POSGAR 94) → Muestra vista previa →
Usuario confirma → Coordenadas se guardan en POSGAR 2007
```

## 📝 Mantenimiento

### Actualizar Parámetros de Transformación
Si necesita actualizar los parámetros de transformación:

1. Edite el archivo `transformar_coordenadas.php`
2. Modifique las variables:
   ```php
   $delta_este = 0.031;    // Nuevo valor
   $delta_norte = 0.146;   // Nuevo valor
   ```
3. Guarde y pruebe con coordenadas conocidas

### Agregar a Nuevos Formularios

Para agregar el sistema de transformación a un nuevo formulario:

1. Agregue el selector de sistema antes de los campos de coordenadas:
```html
<div class="row mb-3">
  <div class="col-md-6">
    <label class="form-label fw-bold">Sistema de Coordenadas</label>
    <select id="sistema-coordenadas" class="form-select">
      <option value="posgar2007" selected>POSGAR 2007 (EPSG:5344) - Por defecto</option>
      <option value="posgar94">POSGAR 94 (EPSG:22182) - Se transformará a POSGAR 2007</option>
    </select>
  </div>
</div>
```

2. Agregue el contenedor para vista previa:
```html
<div id="preview-transformacion" class="mt-3" style="display: none;"></div>
```

3. Incluya el script antes del cierre de `</body>`:
```html
<script src="transformador_coordenadas.js"></script>
```

## 🐛 Solución de Problemas

### La transformación no funciona
- Verifique que `transformar_coordenadas.php` sea accesible
- Revise la consola del navegador para errores JavaScript
- Confirme que las coordenadas ingresadas sean válidas

### Los valores transformados parecen incorrectos
- Verifique los parámetros de transformación en `transformar_coordenadas.php`
- Confirme que esté usando los parámetros correctos para su región
- Consulte con el IGN los parámetros oficiales actualizados

## 📚 Referencias

- [Instituto Geográfico Nacional (IGN)](https://www.ign.gob.ar/)
- [POSGAR 2007 - Documentación oficial](https://www.ign.gob.ar/NuestrasActividades/Geodesia/Posgar2007)
- Sistema de Referencia: EPSG:5344 (POSGAR 2007 / Argentina 2)
- Sistema de Referencia: EPSG:22182 (POSGAR 94 / Argentina 2)

## 👥 Contacto

Para consultas sobre el sistema de transformación de coordenadas, contacte al equipo de desarrollo del Sistema de Catastro Minero.

---

**Versión**: 1.0  
**Fecha**: Octubre 2025  
**Autor**: Sistema de Catastro Minero - Provincia de San Juan
