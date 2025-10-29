/**
 * Módulo para manejar la transformación de coordenadas POSGAR94 a POSGAR2007
 * Autor: Sistema de Catastro Minero
 * Fecha: Octubre 2025
 */

// Variable global para almacenar el sistema de coordenadas seleccionado
let sistemaCoordenadasActual = 'posgar2007';
let coordenadasTransformadas = null;

/**
 * Inicializa el selector de sistema de coordenadas
 */
function inicializarSelectorCoordenadas() {
  const selector = document.getElementById('sistema-coordenadas');
  if (selector) {
    selector.addEventListener('change', function() {
      sistemaCoordenadasActual = this.value;
      actualizarEtiquetasCoordenadas();
      
      // Limpiar campos si hay datos
      const inputX = document.getElementById('x');
      const inputY = document.getElementById('y');
      
      if (inputX && inputY && (inputX.value || inputY.value)) {
        if (confirm('¿Desea limpiar las coordenadas actuales al cambiar de sistema?')) {
          inputX.value = '';
          inputY.value = '';
          limpiarVistaPrevia();
        }
      }
    });
  }
}

/**
 * Actualiza las etiquetas según el sistema seleccionado
 */
function actualizarEtiquetasCoordenadas() {
  const legend = document.getElementById('legend-coordenadas');
  const infoSistema = document.getElementById('info-sistema');
  
  if (legend) {
    if (sistemaCoordenadasActual === 'posgar94') {
      legend.innerHTML = 'Ingresar Coordenadas Gauss Krüger Faja 2 POSGAR 94 (EPSG:22182) - Serán transformadas a POSGAR 2007';
      if (infoSistema) {
        infoSistema.innerHTML = '<i class="bi bi-info-circle"></i> Las coordenadas serán transformadas automáticamente a POSGAR 2007 antes de guardar';
        infoSistema.className = 'alert alert-warning small mt-2';
        infoSistema.style.display = 'block';
      }
    } else {
      legend.innerHTML = 'Ingresar Coordenadas Gauss Krüger Faja 2 POSGAR 2007 (EPSG:5344)';
      if (infoSistema) {
        infoSistema.style.display = 'none';
      }
    }
  }
}

/**
 * Transforma coordenadas de POSGAR94 a POSGAR2007
 */
async function transformarCoordenadas(este, norte) {
  if (sistemaCoordenadasActual === 'posgar2007') {
    return {
      success: true,
      este_transformado: este,
      norte_transformado: norte,
      delta_este: 0,
      delta_norte: 0
    };
  }
  
  try {
    const response = await fetch(`transformar_coordenadas.php?este=${este}&norte=${norte}&sistema=${sistemaCoordenadasActual}`);
    const data = await response.json();
    
    if (data.success) {
      coordenadasTransformadas = data;
      return data;
    } else {
      throw new Error(data.error || 'Error en la transformación');
    }
  } catch (error) {
    console.error('Error transformando coordenadas:', error);
    alert('Error al transformar coordenadas: ' + error.message);
    return null;
  }
}

/**
 * Muestra la vista previa de las coordenadas transformadas
 */
function mostrarVistaPrevia(data) {
  const preview = document.getElementById('preview-transformacion');
  if (!preview) return;
  
  if (sistemaCoordenadasActual === 'posgar94' && data) {
    preview.innerHTML = `
      <div class="card border-info">
        <div class="card-header bg-info text-white">
          <strong>📐 Transformación de Coordenadas</strong>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <h6 class="text-primary">POSGAR 94 (Ingresado)</h6>
              <p class="mb-1"><strong>Este:</strong> ${data.este_original.toFixed(2)} m</p>
              <p class="mb-0"><strong>Norte:</strong> ${data.norte_original.toFixed(2)} m</p>
            </div>
            <div class="col-md-6">
              <h6 class="text-success">POSGAR 2007 (Transformado)</h6>
              <p class="mb-1"><strong>Este:</strong> ${data.este_transformado.toFixed(2)} m</p>
              <p class="mb-0"><strong>Norte:</strong> ${data.norte_transformado.toFixed(2)} m</p>
            </div>
          </div>
          <hr>
          <div class="small text-muted">
            <p class="mb-1"><strong>Corrección aplicada:</strong></p>
            <p class="mb-0">ΔEste: ${data.delta_este.toFixed(3)} m | ΔNorte: ${data.delta_norte.toFixed(3)} m</p>
          </div>
          <div class="alert alert-info small mb-0 mt-2">
            <i class="bi bi-check-circle"></i> Las coordenadas transformadas a POSGAR 2007 serán las que se guarden en la base de datos.
          </div>
        </div>
      </div>
    `;
    preview.style.display = 'block';
  } else {
    preview.style.display = 'none';
  }
}

/**
 * Limpia la vista previa
 */
function limpiarVistaPrevia() {
  const preview = document.getElementById('preview-transformacion');
  if (preview) {
    preview.style.display = 'none';
    preview.innerHTML = '';
  }
  coordenadasTransformadas = null;
}

/**
 * Override de la función agregarPunto original para incluir transformación
 */
const agregarPuntoOriginal = typeof agregarPunto !== 'undefined' ? agregarPunto : null;

async function agregarPuntoConTransformacion() {
  const inputX = document.getElementById('x');
  const inputY = document.getElementById('y');
  
  if (!inputX || !inputY) {
    alert('No se encontraron los campos de coordenadas');
    return;
  }
  
  const x = parseFloat(inputX.value);
  const y = parseFloat(inputY.value);
  
  if (isNaN(x) || isNaN(y) || x <= 0 || y <= 0) {
    alert("Por favor ingresa valores válidos para ESTE y NORTE");
    return;
  }
  
  // Transformar coordenadas si es necesario
  const resultado = await transformarCoordenadas(x, y);
  
  if (!resultado) {
    return; // Error en transformación
  }
  
  // Mostrar vista previa
  mostrarVistaPrevia(resultado);
  
  // Si requiere confirmación en POSGAR94, solicitar al usuario
  if (sistemaCoordenadasActual === 'posgar94') {
    const confirmar = confirm(
      `Se transformarán las coordenadas:\n\n` +
      `POSGAR 94 (ingresado):\n` +
      `Este: ${resultado.este_original.toFixed(2)} m\n` +
      `Norte: ${resultado.norte_original.toFixed(2)} m\n\n` +
      `POSGAR 2007 (transformado):\n` +
      `Este: ${resultado.este_transformado.toFixed(2)} m\n` +
      `Norte: ${resultado.norte_transformado.toFixed(2)} m\n\n` +
      `¿Desea agregar este punto?`
    );
    
    if (!confirmar) {
      limpiarVistaPrevia();
      return;
    }
  }
  
  // Usar las coordenadas transformadas para agregar el punto
  const esteTransformado = resultado.este_transformado;
  const norteTransformado = resultado.norte_transformado;
  
  // Temporalmente cambiar los valores de los inputs para que agregarPunto use las coordenadas transformadas
  const valorOriginalX = inputX.value;
  const valorOriginalY = inputY.value;
  
  inputX.value = esteTransformado;
  inputY.value = norteTransformado;
  
  // Llamar a la función original de agregarPunto
  if (agregarPuntoOriginal) {
    await agregarPuntoOriginal();
  } else if (typeof agregarPunto !== 'undefined') {
    await agregarPunto();
  }
  
  // Limpiar los campos
  inputX.value = '';
  inputY.value = '';
  limpiarVistaPrevia();
}

/**
 * Inicializar cuando el DOM esté listo
 */
document.addEventListener('DOMContentLoaded', function() {
  inicializarSelectorCoordenadas();
  
  // Reemplazar la función agregarPunto si existe
  if (typeof agregarPunto !== 'undefined') {
    window.agregarPuntoOriginal = agregarPunto;
    window.agregarPunto = agregarPuntoConTransformacion;
  }
});
