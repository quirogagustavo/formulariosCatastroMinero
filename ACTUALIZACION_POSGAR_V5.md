# Actualización POSGAR Transform v5.0

## Cambios Implementados

### 1. Archivo: `posgar_transform.js`
- **Versión actualizada:** 5.0
- **Nuevas funciones:**
  - `convertirPOSGAR94a2007_IGN()` - Método oficial IGN con Helmert 7 parámetros
  - `convertirPOSGAR94a2007_GPAC()` - Método local GPAC San Juan
  - `convertirPOSGAR94a2007(este94, norte94, metodo)` - Función principal con selector

### 2. Métodos Disponibles

#### Método IGN (Por defecto)
```javascript
convertirPOSGAR94a2007(este94, norte94, 'IGN');
```
- Transformación Helmert de 7 parámetros
- Parámetros oficiales del Instituto Geográfico Nacional
- Traslación: dx=-11.340m, dy=-6.686m, dz=3.836m
- Rotación: rx=0.214569", ry=-0.102025", rz=0.374988"
- Escala: ds=0.121173600 ppm

#### Método GPAC
```javascript
convertirPOSGAR94a2007(este94, norte94, 'GPAC');
```
- Fórmula de corrección local para San Juan
- ESTE_07 = ESTE_94 + 1.6349059209 + (-2.016e-7 * NORTE_94)
- NORTE_07 = NORTE_94 + 0.0947941194 + (2.213e-7 * ESTE_94)

### 3. Formularios Actualizados

✅ **formulario_denuncia_labor_legal.php** (COMPLETO)
- Versión: 5.0
- Selector de método agregado
- Función actualizada con parámetro de método

✅ **formulario_denuncia_servidumbre.php** (Versión actualizada)
- Versión: 5.0
- Pendiente: Agregar selector de método en HTML
- Pendiente: Actualizar 4 llamadas a la función

✅ **formulario_solicitud_manifestacion.php** (Versión actualizada)
- Versión: 5.0
- Pendiente: Agregar selector de método
- Pendiente: Actualizar llamadas a la función

✅ **formulario_solicitud_permiso_exploracion.php** (Versión actualizada)
- Versión: 5.0
- Pendiente: Agregar selector de método
- Pendiente: Actualizar llamadas a la función

✅ **formulario_solicitud_canteras.php** (Versión actualizada)
- Versión: 5.0
- Pendiente: Agregar selector de método
- Pendiente: Actualizar llamadas a la función

✅ **formulario_solicitud_peticion_mensura.php** (Versión actualizada)
- Versión: 5.0
- Pendiente: Agregar selector de método
- Pendiente: Actualizar llamadas a la función

### 4. Retrocompatibilidad

El tercer parámetro `metodo` es opcional. Si no se especifica, usa 'IGN' por defecto:

```javascript
// Ambas llamadas usan método IGN
convertirPOSGAR94a2007(este94, norte94);
convertirPOSGAR94a2007(este94, norte94, 'IGN');
```

**Esto significa que todos los formularios seguirán funcionando correctamente sin cambios adicionales**, usando el método IGN oficial.

### 5. Agregar Selector en Formularios Restantes

Para agregar el selector de método en los formularios pendientes, agregar este HTML antes de los campos de coordenadas:

```html
<div class="col-md-6">
  <label class="form-label fw-bold">Método de Transformación</label>
  <select id="metodoTransformacion" class="form-select">
    <option value="IGN">IGN - Parámetros Oficiales (Helmert 7 parámetros)</option>
    <option value="GPAC">GPAC - Fórmula Local San Juan</option>
  </select>
  <small class="text-muted">IGN: Método oficial del Instituto Geográfico Nacional | GPAC: Gestión Provincial de Agrimensura</small>
</div>
```

Y actualizar las llamadas a la función:

```javascript
// ANTES:
const convertido = convertirPOSGAR94a2007(x, y);

// DESPUÉS:
const metodoSeleccionado = document.getElementById('metodoTransformacion') ? 
                           document.getElementById('metodoTransformacion').value : 'IGN';
const convertido = convertirPOSGAR94a2007(x, y, metodoSeleccionado);
```

### 6. Archivo de Prueba

**test_transform_gpac.html**
- Permite comparar ambos métodos lado a lado
- Muestra diferencias entre IGN y GPAC
- Calcula distancia entre resultados
- Interfaz visual con selector de método

### 7. Recomendaciones

1. **Por defecto usar IGN**: Es el método oficial del Instituto Geográfico Nacional
2. **Usar GPAC**: Solo si es específicamente solicitado por Agrimensura de San Juan
3. **Comparar resultados**: Usar `test_transform_gpac.html` para ver diferencias

### 8. Estado Actual

**FUNCIONANDO:**
- ✅ posgar_transform.js v5.0 con ambos métodos
- ✅ Retrocompatibilidad total (sin cambios todos usan IGN)
- ✅ formulario_denuncia_labor_legal.php con selector completo
- ✅ Todos los formularios usan versión 5.0

**OPCIONAL (Mejora futura):**
- Agregar selector de método en formularios restantes
- Actualizar llamadas para usar método seleccionado
- Agregar validación del método en servidor PHP

### 9. Próximos Pasos

Si se desea implementar el selector en todos los formularios:

1. Agregar el HTML del selector en cada formulario
2. Actualizar las llamadas a `convertirPOSGAR94a2007()` 
3. Probar conversiones con ambos métodos
4. Documentar cuál método usar según el caso

### 10. Commit Sugerido

```bash
git add formularios/posgar_transform.js
git add formularios/formulario_*.php
git add formularios/test_transform_gpac.html
git commit -m "feat: Agregar método GPAC para transformación POSGAR 94→2007

- Actualizar posgar_transform.js a v5.0
- Agregar función convertirPOSGAR94a2007_GPAC() con fórmula local
- Mantener convertirPOSGAR94a2007_IGN() como método oficial
- Actualizar todos los formularios a v5.0 (retrocompatible)
- Implementar selector de método en formulario_denuncia_labor_legal.php
- Crear test_transform_gpac.html para comparación de métodos
- Por defecto usa método IGN (oficial)"
```

---

**Fecha:** 1 de diciembre de 2025  
**Versión:** 5.0
