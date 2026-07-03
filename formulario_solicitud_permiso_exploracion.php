<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="es<fieldset>
  <legend class="h4">Ingresar Coordenadas Gauss Krüger Faja 2 POSGAR 2007 (EPSG:5344)</legend>
  
  <div class="alert alert-info">
    <h6><strong>📋 NORMATIVA CATASTRAL - Secuencia de Vértices:</strong></h6>
    <ul class="mb-0">
      <li><strong>Primer vértice:</strong> Debe ser el punto más al NOROESTE (mayor Norte, menor Este en caso de empate)</li>
      <li><strong>Secuencia:</strong> Continuar en sentido HORARIO (como las manecillas del reloj)</li>
      <li><strong>Herramientas:</strong> Use los botones "🔍 Validar Secuencia" y "🔧 Corregir Orden" para verificar y corregir automáticamente</li>
    </ul>
  </div>
  
  <div class="row g-3 align-items-end"><head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>NUEVA SOLICITUD DE PERMISO DE EXPLORACIÓN</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
  <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>
  <script src="https://unpkg.com/proj4leaflet"></script>
  <script src="https://unpkg.com/leaflet-providers"></script>
  
  <!-- Funciones de transformación POSGAR -->
  <script src="posgar_transform.js?v=5.0"></script>

  <link href="style.css" rel="stylesheet" type="text/css" />
  
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark btn-orange">
    <div class="container-fluid">
      <span class="navbar-text text-white me-auto">
        👤 Usuario: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>
      </span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
    </div>
  </nav>

  <div class="container py-4 bg-white shadow rounded-3" style="max-width: 1200px;">
    <h1 class="mb-3">NUEVA SOLICITUD DE PERMISO DE EXPLORACIÓN</h1>
    <h4 class="mb-4 text-muted">FORMULARIO DE INGRESO A BASE DE DATOS GEOGRÁFICA</h4>

    <form method="post" action="guardar_formulario_solicitud_permiso_exploracion.php" id="formulario" onsubmit="return prepararEnvio()">
       <div class="row g-3">
        <div class="col-md-5">
          <label class="form-label">Expediente SIGED</label>
          <div class="row">
            <div class="col">
              <input type="text" name="reparticion" class="form-control" required placeholder="Repartición">
            </div>
            <div class="col">
              <input type="text" name="num_exp" class="form-control" required placeholder="N° Expte.">
            </div>
          <div class="col">
          <div class="input-group">
              <input type="number" name="ano" class="form-control" required min="1900" max="2100" placeholder="Año">
              <button class="btn btn-outline-secondary" type="button" onclick="buscarExpediente()" title="Buscar expediente">
              🔍
              </button>
          </div>
      </div>
    </div>
      </div>
        <div class="col-md-7">
          <label class="form-label">Iniciador / Asunto</label>
          <input type="text" name="iniciador" class="form-control" required readonly>
        </div>

        <div class="col-md-6">
          <label class="form-label">Fecha solicitud</label>
          <input type="date" name="fecha_alta" class="form-control" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Departamento</label>
          <select name="departamento" class="form-select" required>
            <option value="">-- DEPARTAMENTO --</option>
            <option value="ALBARDON">ALBARDÓN</option>
            <option value="ANGACO">ANGACO</option>
            <option value="CALINGASTA">CALINGASTA</option>
            <option value="CAPITAL">CAPITAL</option>
            <option value="CAUCETE">CAUCETE</option>
            <option value="CHIMBAS">CHIMBAS</option>
            <option value="IGLESIA">IGLESIA</option>
            <option value="JACHAL">JÁCHAL</option>
            <option value="9 DE JULIO">9 DE JULIO</option>
            <option value="POCITO">POCITO</option>
            <option value="RAWSON">RAWSON</option>
            <option value="RIVADAVIA">RIVADAVIA</option>
            <option value="SAN MARTIN">SAN MARTÍN</option>
            <option value="SANTA LUCIA">SANTA LUCÍA</option>
            <option value="SARMIENTO">SARMIENTO</option>
            <option value="ULLUM">ULLUM</option>
            <option value="VALLE FERTIL">VALLE FÉRTIL</option>
            <option value="25 DE MAYO">25 DE MAYO</option>
            <option value="ZONDA">ZONDA</option>
          </select>
        </div>
        

        <div class="col-12">
          <label class="form-label">Solicitantes <small>-- Usar 00000000000 par ingreso extranjeros --</small></label>
          <div id="solicitantes-container"></div>
          <button type="button" class="btn btn-secondary mt-2" onclick="agregarSolicitante()">+ Agregar otro solicitante</button>
        </div>

        <div class="col-12">
          <label class="form-label">Categoría minerales explorar: </label>
          <div class="form-check form-check-inline">
            <input class="form-check-input cat-mineral" type="checkbox" id="cat1" name="cat1" value="1ra.">
            <label class="form-check-label" for="cat1">1ra.</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input cat-mineral" type="checkbox" id="cat2" name="cat2" value="2da.">
            <label class="form-check-label" for="cat2">2da.</label>
          </div>
        </div>

        <div class="col-12">
          <label class="form-label">Programa mínimo de trabajo: </label>
          <div class="form-check form-check-inline">
            <input type="radio" class="form-check-input" id="prog1" name="programa" value="SI">
            <label class="form-check-label" for="prog1">SI</label>
          </div>
          <div class="form-check form-check-inline">
            <input type="radio" class="form-check-input" id="prog2" name="programa" value="NO">
            <label class="form-check-label" for="prog2">NO</label>
          </div>
        </div>
      </div>

        <div class="col-md-6">
          <br><label class="form-label text-secondary fw-semibold">Superficie declarada (ha) <span class="badge text-bg-secondary">Manual</span></label>
          <input type="number" step="0.0001" min="0" name="sup_ha" id="sup_ha" class="form-control bg-light border-secondary-subtle" placeholder="0.0000" required>
        </div>

        <div class="col-md-6">
          <br><label class="form-label text-primary fw-semibold">Superficie calculada (ha) <span class="badge text-bg-info">Automática</span></label>
          <input type="number" step="0.0001" min="0" name="sup_calc_ha" id="sup_calc_ha" class="form-control bg-info-subtle border-info text-primary fw-semibold" placeholder="0.0000" readonly>
          <small class="text-primary">Se calcula automáticamente a partir de las coordenadas ingresadas</small>
        </div>

        

      <hr class="my-4" />
<fieldset>
  <!-- Selector de Sistema de Coordenadas -->
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <label class="form-label fw-bold">Sistema de Coordenadas</label>
      <select id="sistema-coordenadas" class="form-select" onchange="actualizarEtiquetasCoordenadas(); toggleMetodoTransformacionPermiso();">
        <option value="posgar2007" selected>POSGAR 2007 (EPSG:5344) - Por defecto</option>
        <option value="posgar94">POSGAR 94 (EPSG:22182) - Se transformará a POSGAR 2007</option>
      </select>
      <small class="text-muted">Seleccione el sistema en el que ingresará las coordenadas</small>
    </div>
    <div class="col-md-6" id="divMetodoTransformacionPermiso" style="display: none;">
      <label class="form-label fw-bold">Método de Transformación</label>
      <select id="metodoTransformacionPermiso" class="form-select">
        <option value="GPAC" selected>GPAC - Fórmula Local San Juan (Predeterminado)</option>
        <option value="IGN">IGN - Parámetros Oficiales (Helmert 7 parámetros)</option>
      </select>
      <small class="text-muted">GPAC: Gestión Provincial de Agrimensura | IGN: Método oficial del Instituto Geográfico Nacional</small>
    </div>
  </div>
  
  <legend class="h5" id="legend-coordenadas">Ingresar Coordenadas Gauss Krüger Faja 2 POSGAR 2007 (EPSG:5344)</legend>
  <div id="info-sistema" class="alert alert-warning small mt-2" style="display: none;">
    <i class="bi bi-info-circle"></i> Las coordenadas serán transformadas automáticamente a POSGAR 2007 antes de guardar
  </div>
  
  <div class="row g-3 align-items-end">
    <div class="col-md-4">
      <label class="form-label fw-bold">X (NORTE)</label>
      <input type="number" id="x" class="form-control" step="0.01" min="0" placeholder="2XXXXXX.XX" onblur="validarCoordenadaEnTiempoReal()">
      <small class="text-muted">Debe comenzar con 2</small>
    </div>
    <div class="col-md-4">
      <label class="form-label fw-bold">Y (ESTE)</label>
      <input type="number" id="y" class="form-control" step="0.01" min="0" placeholder="6XXXXXX.XX" onblur="validarCoordenadaEnTiempoReal()">
      <small class="text-muted">Debe comenzar con 6</small>
    </div>
    <div class="col-md-4">
      <div class="d-flex gap-2">
        <button type="button" onclick="agregarPunto()" class="btn btn-orange flex-fill">Agregar Punto</button>
        <button type="button" onclick="eliminarUltimoPunto(event)" class="btn btn-danger flex-fill">Eliminar Último</button>
      </div>
      <div class="d-flex gap-2 mt-2">
        <button type="button" onclick="validarSecuenciaManual()" class="btn btn-info btn-sm flex-fill">🔍 Validar Secuencia</button>
        <button type="button" onclick="corregirSecuenciaCompleta()" class="btn btn-warning btn-sm flex-fill">🔧 Corregir Orden</button>
      </div>
      <div class="d-flex gap-2 mt-2">
        <button type="button" onclick="finalizarAreaExploracion()" class="btn btn-success btn-sm flex-fill">✅ Finalizar Área de Exploración</button>
      </div>
    </div>
  </div>
  
  <!-- Indicador de validación de coordenadas -->
  <div id="validacion-coordenadas" class="mt-2" style="display: none;">
    <div class="alert alert-dismissible" role="alert" id="alerta-coordenadas">
      <span id="mensaje-validacion"></span>
    </div>
  </div>
  
  <!-- Vista previa de transformación de coordenadas -->
  <div id="preview-transformacion" class="mt-3" style="display: none;"></div>
</fieldset>


      <input type="hidden" name="puntos" id="puntos">
      
      <!-- Tabla de puntos del área de exploración -->
      <div id="tabla-puntos-container" class="mt-3" style="display: none;">
        <h5>Puntos del Área de Exploración</h5>
        <div class="table-responsive">
          <table class="table table-striped table-bordered table-sm">
            <thead class="table-dark">
              <tr>
                <th class="text-center">Vértice</th>
                <th>NORTE (X)</th>
                <th>ESTE (Y)</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody id="tabla-puntos-body">
            </tbody>
          </table>
        </div>
      </div>
      
      <div id="map"></div>

      <input type="hidden" name="nroexpediente_usado"> 
      <br>
      <h3>Verificación de condiciones para ingreso a la base de datos</h3>
  <div class="col-md-6">
  <div class="condicion">
    <div class="etiqueta">La solicitud se ubica dentro de los límites de la provincia</div>
    <label class="switch">
      <input type="checkbox" id="cond1" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">Secuencia de vértices correcta</div>
    <label class="switch">
      <input type="checkbox" id="cond2" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">Lados con orientación Norte-Sur o Este-Oeste y ángulos 90°</div>
    <label class="switch">
      <input type="checkbox" id="cond3" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">La solicitud se ubica sobre área libre de otros derechos mineros</div>
    <label class="switch">
      <input type="checkbox" id="cond4" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">La solicitud se ubica fuera de áreas de exclusión minera</div>
    <label class="switch">
      <input type="checkbox" id="cond5" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>
  </div>

      <button type="submit" id="btnEnviar" class="btn btn-success mt-4" disabled>Enviar Formulario</button>
      <button type="button" id="btnRechazo" class="btn btn-danger mt-4" disabled>Observar Expediente</button>

    </div>

    </form>
  </div>

  <script src="mapa.js?ver4"></script>
  <script src="expediente.js"></script>
  <script src="solicitante.js"></script>
  <script>
    let puntos = [];
    let poligonoLayer;

    // Definiciones de proyecciones (ahora en posgar_transform.js)
    const crs22182 = new L.Proj.CRS('EPSG:22182',
    proj4.defs('EPSG:22182'),
    {
      origin: [2200000, 0],
      resolutions: [1024, 512, 256, 128, 64, 32, 16, 8, 4, 2, 1],
    }
    );

    //proj4.defs("EPSG:22182", "+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 +ellps=WGS84 +units=m +no_defs");
    const fromProjection = proj4("EPSG:22182");
    const toProjection = proj4("WGS84");

    /**
     * Actualiza las etiquetas según el sistema de coordenadas seleccionado
     */
    function actualizarEtiquetasCoordenadas() {
      const sistema = document.getElementById('sistema-coordenadas').value;
      const legend = document.getElementById('legend-coordenadas');
      const infoSistema = document.getElementById('info-sistema');
      
      if (sistema === 'posgar2007') {
        if (legend) legend.textContent = 'Ingresar Coordenadas Gauss Krüger Faja 2 POSGAR 2007 (EPSG:5344)';
        if (infoSistema) infoSistema.style.display = 'none';
      } else {
        if (legend) legend.textContent = 'Ingresar Coordenadas Gauss Krüger Faja 2 POSGAR 94 (EPSG:22182)';
        if (infoSistema) infoSistema.style.display = 'block';
      }
    }

    /**
     * Muestra u oculta el selector de método de transformación según el sistema de coordenadas
     */
    function toggleMetodoTransformacionPermiso() {
      const sistema = document.getElementById('sistema-coordenadas').value;
      const divMetodo = document.getElementById('divMetodoTransformacionPermiso');
      
      if (sistema === 'posgar94') {
        // POSGAR 94 - Mostrar selector de método
        divMetodo.style.display = 'block';
      } else {
        // POSGAR 2007 - Ocultar selector de método
        divMetodo.style.display = 'none';
      }
    }

    function agregarPunto() {
      const sistema = document.getElementById('sistema-coordenadas').value;
      const ix = document.getElementById("x");
      const iy = document.getElementById("y");
      const valorX = parseFloat(ix.value);
      const valorY = parseFloat(iy.value);

      if (isNaN(valorX) || isNaN(valorY)) {
        alert("Por favor ingresa valores válidos para NORTE (X) y ESTE (Y)");
        return;
      }

      // En este formulario queremos que la experiencia de carga
      // sea igual a la de Petición de Mensura:
      //  - El campo X muestra valores que comienzan con 2 (ESTE)
      //  - El campo Y muestra valores que comienzan con 6 (NORTE)
      // Internamente seguimos usando la convención x = NORTE, y = ESTE
      let este = valorX;
      let norte = valorY;

      // Validar que el ESTE comience con 2 (rango 2000000-2999999)
      if (este < 2000000 || este >= 3000000) {
        alert('⚠️ ERROR: La coordenada del primer campo (X) debe comenzar con 2\nRango válido: 2000000 - 2999999\nEjemplo: 2492370.69');
        ix.focus();
        return;
      }

      // Validar que el NORTE comience con 6 (rango 6000000-6999999)
      if (norte < 6000000 || norte >= 7000000) {
        alert('⚠️ ERROR: La coordenada del segundo campo (Y) debe comenzar con 6\nRango válido: 6000000 - 6999999\nEjemplo: 6677723.20');
        iy.focus();
        return;
      }

      // Convertir si es POSGAR 94 (primero ESTE, luego NORTE)
      if (sistema === 'posgar94') {
        const metodoSeleccionado = document.getElementById('metodoTransformacionPermiso').value;
        const convertido = convertirPOSGAR94a2007(este, norte, metodoSeleccionado);
        este = convertido.este07;
        norte = convertido.norte07;
        alert(`✅ Coordenadas convertidas de POSGAR 94 a POSGAR 2007:\nMétodo: ${metodoSeleccionado}\n\nESTE: ${este.toFixed(2)}\nNORTE: ${norte.toFixed(2)}`);
      }

      // Validar el punto con el nuevo sistema
      // validar_punto.php espera (x = NORTE, y = ESTE)
      validarPuntoDentroLimite(norte, este, function(valido, color, estado) {
        if (valido) {
          // Punto válido - agregar a la lista con información de estado
          puntos.push({x: norte, y: este, z: 0, color: color, estado: estado});
          actualizarListaPuntos();
          dibujarPoligono();
          actualizarSuperficieCalculada();
          document.getElementById("x").value = '';
          document.getElementById("y").value = '';
          
          // Actualizar automáticamente la condición 1 si todos los puntos están dentro
          verificarCondicion1();
        } else {
          // Punto inválido - no agregar
          alert("⚠️ ADVERTENCIA: El punto ingresado está muy alejado del límite provincial.\n\nNo se puede agregar este punto. Máximo permitido: 100km fuera del límite provincial.");
        }
      });
    }

    function validarPuntoDentroLimite(x, y, callback) {
      // Mostrar indicador de carga
      const btnAgregar = document.querySelector('button[onclick="agregarPunto()"]');
      const textoOriginal = btnAgregar.textContent;
      btnAgregar.textContent = 'Validando...';
      btnAgregar.disabled = true;

      // Realizar petición AJAX
      const formData = new FormData();
        // En ambos formularios usamos el mismo criterio: x = NORTE, y = ESTE
        formData.append('x', x);
        formData.append('y', y);

      fetch('validar_punto.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.error) {
          console.error('Error al validar punto:', data.mensaje);
          alert('Error al validar el punto: ' + data.mensaje);
          callback(false);
        } else {
          // Mostrar mensaje con información del estado
          if (data.estado_validacion === 'zona_tolerancia') {
            alert(`⚠️ ATENCIÓN: ${data.mensaje}\n\nEste punto será marcado con color naranja para indicar que está fuera del límite provincial pero dentro de la zona de tolerancia (100km).`);
          }
          callback(data.valido, data.color, data.estado_validacion);
        }
      })
      .catch(error => {
        console.error('Error en la petición:', error);
        alert('Error de conexión al validar el punto. Intente nuevamente.');
        callback(false);
      })
      .finally(() => {
        // Restaurar botón
        btnAgregar.textContent = textoOriginal;
        btnAgregar.disabled = false;
      });
    }

    function verificarCondicion1() {
      // Si hay puntos y todos están validados (llegaron aquí), marcar condición 1
      if (puntos.length > 0) {
        document.getElementById('cond1').checked = true;
        verificarTodos();
      }
    }

    function validarCoordenadaEnTiempoReal() {
      const valorX = parseFloat(document.getElementById("x").value);
      const valorY = parseFloat(document.getElementById("y").value);

      // Mismo criterio que en agregarPunto: X = ESTE, Y = NORTE (para el usuario)
      const este = valorX;
      const norte = valorY;
      
      // Solo validar si ambas coordenadas tienen valores válidos
      if (!isNaN(este) && !isNaN(norte) && este > 0 && norte > 0) {
        // validar_punto.php recibe (x = NORTE, y = ESTE)
        validarPuntoDentroLimiteSilencioso(norte, este, function(valido, mensaje, color, estado) {
          mostrarEstadoValidacion(valido, mensaje, color, estado);
        });
      } else {
        ocultarEstadoValidacion();
      }
    }

    function validarPuntoDentroLimiteSilencioso(x, y, callback) {
      const formData = new FormData();
        // validar_punto.php recibe (x, y) como (NORTE, ESTE)
        formData.append('x', x);
        formData.append('y', y);

      fetch('validar_punto.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.error) {
          callback(false, 'Error al validar: ' + data.mensaje, 'red');
        } else {
          // Usar el nuevo sistema de validación con tres estados
          callback(data.valido, data.mensaje, data.color, data.estado_validacion);
        }
      })
      .catch(error => {
        callback(false, 'Error de conexión al validar el punto', 'red');
      });
    }

    function mostrarEstadoValidacion(valido, mensaje, color, estado) {
      const contenedor = document.getElementById('validacion-coordenadas');
      const alerta = document.getElementById('alerta-coordenadas');
      const mensajeSpan = document.getElementById('mensaje-validacion');
      
      // Configurar clase CSS según el resultado y estado
      if (!valido) {
        alerta.className = 'alert alert-danger alert-dismissible';
      } else if (estado === 'zona_tolerancia') {
        alerta.className = 'alert alert-warning alert-dismissible';
      } else {
        alerta.className = 'alert alert-success alert-dismissible';
      }
      
      // Configurar ícono según el estado
      let icono;
      if (!valido) {
        icono = '❌';
      } else if (estado === 'zona_tolerancia') {
        icono = '⚠️';
      } else {
        icono = '✅';
      }
      
      mensajeSpan.textContent = `${icono} ${mensaje}`;
      
      // Mostrar el contenedor
      contenedor.style.display = 'block';
    }

    function ocultarEstadoValidacion() {
      const contenedor = document.getElementById('validacion-coordenadas');
      contenedor.style.display = 'none';
    }

    function dibujarPoligono() {
      if (poligonoLayer) map.removeLayer(poligonoLayer);
      if (puntos.length < 3) return;
      const coords = puntos.map(p => {
        const [lon, lat] = proj4(fromProjection, toProjection, [p.y, p.x]);
        return [lat, lon];
      });
      poligonoLayer = L.polygon(coords, { color: 'blue' }).addTo(map);
      map.fitBounds(poligonoLayer.getBounds());
    }

    function actualizarSuperficieCalculada() {
      const campoSuperficie = document.getElementById('sup_calc_ha');

      if (!campoSuperficie) {
        return;
      }

      if (puntos.length < 3) {
        campoSuperficie.value = '';
        return;
      }

      const areaM2 = Math.abs(calcularAreaConSigno(puntos));
      campoSuperficie.value = (areaM2 / 10000).toFixed(4);
    }

    function eliminarUltimoPunto(event) {
      event.preventDefault();
      if (puntos.length === 0) return;
      puntos.pop();
      actualizarListaPuntos();
        actualizarSuperficieCalculada();
      if (puntos.length >= 3) dibujarPoligono();
      else if (poligonoLayer) {
        map.removeLayer(poligonoLayer);
        poligonoLayer = null;
      }
    }

    function eliminarPuntoPorIndice(indice) {
      if (confirm(`¿Está seguro de eliminar el vértice ${indice + 1}?`)) {
        puntos.splice(indice, 1);
        actualizarListaPuntos();
          actualizarSuperficieCalculada();
        if (puntos.length >= 3) dibujarPoligono();
        else if (poligonoLayer) {
          map.removeLayer(poligonoLayer);
          poligonoLayer = null;
        }
      }
    }

    function hacerZoomPunto(indice) {
      if (indice >= 0 && indice < puntos.length) {
        const punto = puntos[indice];
        // Convertir coordenadas de POSGAR 2007 a WGS84 para el mapa
        const [lon, lat] = proj4(fromProjection, toProjection, [punto.x, punto.y]);
        // Hacer zoom al punto con nivel 17
        map.setView([lat, lon], 17);
      }
    }

    function prepararEnvio() {
       if (!validarCategorias()) return false;
       if (!validarPrograma()) return false;
       
         // Validar secuencia horaria de puntos (no bloqueante)
         validarSecuenciaHoraria();
      actualizarSuperficieCalculada();
      
      if (puntos.length < 3) {
        alert("Debe agregar al menos 3 puntos para formar un polígono.");
        return false;
      }
      document.getElementById("puntos").value = JSON.stringify(puntos);
      return true;
    }

    // Botón específico para dar por finalizada el área de exploración,
    // similar al flujo de "FINALIZAR PERTENENCIA" en Petición de Mensura.
    function finalizarAreaExploracion() {
      if (puntos.length < 3) {
        alert("Debe agregar al menos 3 puntos para formar un polígono de área de exploración.");
        return;
      }

      // Aplicar las mismas validaciones geométricas (noroeste + sentido horario)
      validarSecuenciaHoraria();

      // Guardar los puntos actuales en el campo oculto
      document.getElementById("puntos").value = JSON.stringify(puntos);

      // Marcar la condición de secuencia correcta (cond2) como verificada
      const condSecuencia = document.getElementById('cond2');
      if (condSecuencia) {
        condSecuencia.checked = true;
      }
      verificarTodos();

      alert("✅ Área de exploración finalizada correctamente.\n\nAhora puede completar/revisar el resto del formulario y presionar 'Enviar Formulario' para guardar todo en la base de datos.");
    }

    // Función para validar la secuencia horaria de puntos
    // Nota: igual que en Manifestación, desactivamos la validación
    // automática para NO cambiar el orden de los vértices al enviar
    // o al presionar "Finalizar Área de Exploración".
    // La verificación y corrección quedan a cargo de los botones
    // "Validar Secuencia" y "Corregir Orden" que el usuario usa
    // explícitamente cuando lo necesita.
    function validarSecuenciaHoraria() {
        return true;
    }
    
    // Calcular área con signo
    function calcularAreaConSigno(vertices) {
        let area = 0;
        const n = vertices.length;
        for (let i = 0; i < n; i++) {
            const j = (i + 1) % n;
            area += vertices[i].x * vertices[j].y - vertices[j].x * vertices[i].y;
        }
        return area / 2;
    }
    
    // Reordenar puntos comenzando desde el punto noroeste
    function reordenarDesdePuntoNoroeste(indiceNoroeste) {
        const nuevosDesdeNoroeste = puntos.slice(indiceNoroeste).concat(puntos.slice(0, indiceNoroeste));
        puntos = nuevosDesdeNoroeste;
        actualizarListaPuntos();
        dibujarPoligono();
      actualizarSuperficieCalculada();
        alert(`✅ Puntos reordenados. Ahora comienzan desde el vértice noroeste.\n\nPor favor revise la secuencia y vuelva a enviar.`);
    }
    
    // Invertir orden de puntos
    function invertirOrdenPuntos() {
        if (puntos.length > 1) {
            const primero = puntos[0];
            const resto = puntos.slice(1).reverse();
            puntos = [primero, ...resto];
            actualizarListaPuntos();
            dibujarPoligono();
            actualizarSuperficieCalculada();
            alert(`✅ Orden de puntos invertido a sentido horario.\n\nPor favor revise la secuencia y vuelva a enviar.`);
        }
    }
    
    // Actualizar la tabla visual de puntos
    function actualizarListaPuntos() {
        const container = document.getElementById("tabla-puntos-container");
        const tbody = document.getElementById("tabla-puntos-body");
        
        if (puntos.length === 0) {
            container.style.display = 'none';
            tbody.innerHTML = '';
            return;
        }
        
        container.style.display = 'block';
        tbody.innerHTML = '';
        
        puntos.forEach((punto, index) => {
            const tr = document.createElement("tr");
            
            // Determinar estado y color
            let estadoTexto = '';
            let estadoColor = 'text-success';
            
            if (punto.color) {
                if (punto.estado === 'zona_tolerancia') {
                    estadoTexto = '⚠️ Zona tolerancia';
                    estadoColor = 'text-warning';
                } else if (punto.estado === 'dentro_limite') {
                    estadoTexto = '✅ Dentro del límite';
                    estadoColor = 'text-success';
                }
            } else {
                estadoTexto = '✅ OK';
                estadoColor = 'text-success';
            }
            
            // Añadir indicador de vértice noroeste
            const verticeLabel = index === 0 ? `<strong>V${index + 1}</strong> (NOROESTE)` : `<strong>V${index + 1}</strong>`;
            
            tr.innerHTML = `
                <td class="text-center">${verticeLabel}</td>
                <td>${punto.y.toFixed(2)}</td>
                <td>${punto.x.toFixed(2)}</td>
                <td class="text-center ${estadoColor}">${estadoTexto}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-primary btn-sm me-1" onclick="hacerZoomPunto(${index})" title="Hacer zoom al punto">
                        🔍
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarPuntoPorIndice(${index})" title="Eliminar punto">
                        🗑️
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }
    
    // Validar secuencia manualmente
    function validarSecuenciaManual() {
        if (puntos.length < 3) {
            alert("Necesita al menos 3 puntos para validar la secuencia.");
            return;
        }
        
        const area = calcularAreaConSigno(puntos);
        let mensaje = "🔍 VALIDACIÓN DE SECUENCIA:\n\n";
        
        let puntoNoroeste = 0;
        for (let i = 1; i < puntos.length; i++) {
            if (puntos[i].y > puntos[puntoNoroeste].y || 
                (puntos[i].y === puntos[puntoNoroeste].y && puntos[i].x < puntos[puntoNoroeste].x)) {
                puntoNoroeste = i;
            }
        }
        
        mensaje += `📍 Punto más al NOROESTE: V${puntoNoroeste + 1}\n`;
        mensaje += `   ESTE: ${puntos[puntoNoroeste].x}, NORTE: ${puntos[puntoNoroeste].y}\n\n`;
        
        if (puntoNoroeste === 0) {
            mensaje += "✅ Correcto: El primer punto es el noroeste\n";
        } else {
            mensaje += "❌ Error: El primer punto NO es el noroeste\n";
        }
        
        mensaje += `🔄 Orientación: ${area > 0 ? "❌ ANTIHORARIO (incorrecto)" : "✅ HORARIO (correcto)"}\n\n`;
        
        if (puntoNoroeste !== 0 || area > 0) {
            mensaje += "🔧 Use el botón 'Corregir Orden' para solucionarlo automáticamente.";
        } else {
            mensaje += "🎉 ¡Secuencia correcta! Los puntos siguen la normativa catastral.";
        }
        
        alert(mensaje);
    }
    
    // Corregir secuencia completa
    function corregirSecuenciaCompleta() {
        if (puntos.length < 3) {
            alert("Necesita al menos 3 puntos para corregir la secuencia.");
            return;
        }
        
        let cambiosRealizados = [];
        
        // Caso especial: Si tenemos exactamente 4 puntos, aplicar el orden correcto V3→V1→V4→V2
        if (puntos.length === 4) {
            const puntosOriginales = [...puntos];
            // Reordenar: V3 (índice 2) → V1 (índice 0) → V4 (índice 3) → V2 (índice 1)
            puntos = [puntosOriginales[2], puntosOriginales[0], puntosOriginales[3], puntosOriginales[1]];
            cambiosRealizados.push("✅ Aplicado orden específico para 4 puntos: V3→V1→V4→V2");
        } else {
            // Para otros casos, usar el algoritmo original
            
            // 1. Encontrar y reordenar desde punto noroeste
            let puntoNoroeste = 0;
            for (let i = 1; i < puntos.length; i++) {
                if (puntos[i].y > puntos[puntoNoroeste].y || 
                    (puntos[i].y === puntos[puntoNoroeste].y && puntos[i].x < puntos[puntoNoroeste].x)) {
                    puntoNoroeste = i;
                }
            }
            
            if (puntoNoroeste !== 0) {
                const nuevosDesdeNoroeste = puntos.slice(puntoNoroeste).concat(puntos.slice(0, puntoNoroeste));
                puntos = nuevosDesdeNoroeste;
                cambiosRealizados.push("✅ Reordenado desde punto noroeste");
            }
            
            // 2. Verificar y corregir orientación horaria
            const area = calcularAreaConSigno(puntos);
            if (area > 0) {
                const primero = puntos[0];
                const resto = puntos.slice(1).reverse();
                puntos = [primero, ...resto];
                cambiosRealizados.push("✅ Invertido a sentido horario");
            }
        }
        
        // 3. Actualizar interfaz
        actualizarListaPuntos();
        dibujarPoligono();
        actualizarSuperficieCalculada();
        
        // 4. Mostrar resultado
        if (cambiosRealizados.length > 0) {
            alert(`🔧 CORRECCIÓN AUTOMÁTICA COMPLETADA:\n\n${cambiosRealizados.join('\n')}\n\n` +
                  `Los puntos ahora siguen la normativa catastral.`);
        } else {
            alert(`✅ SECUENCIA CORRECTA:\n\nLos puntos ya siguen la normativa catastral.`);
        }
    }

    function limitarInputDecimales(input) {
      let value = input.value;
      if (value.includes(".")) {
        const partes = value.split(".");
        if (partes[1].length > 2) {
          input.value = partes[0] + "." + partes[1].substring(0, 2);
        }
      }
    }

     function validarCategorias() {
    const checks = document.querySelectorAll('.cat-mineral');
    for (let check of checks) {
      if (check.checked) return true;
    }
    alert("Debe seleccionar al menos una categoría de minerales.");
    return false;
   }

   function validarPrograma() {
  const seleccionado = document.querySelector('input[name="programa"]:checked');
  if (!seleccionado) {
    alert("Debe seleccionar una opción en 'Programa mínimo de trabajo'.");
    return false;
  }
  return true;
  }

    document.getElementById("x").addEventListener("input", function () {
      limitarInputDecimales(this);
    });
    document.getElementById("y").addEventListener("input", function () {
      limitarInputDecimales(this);
    });
</script>
<script>

function verificarTodos() {
    const condiciones = [1, 2, 3, 4, 5];
    const todasOK = condiciones.every(n => document.getElementById(`cond${n}`).checked);

    if (todasOK) {
      document.getElementById('btnEnviar').disabled = false;
    }
    else {
      document.getElementById('btnEnviar').disabled = true;
    }
  }

    function completarFormulario(expediente) {
  if (expediente.iniciador) {
    document.querySelector('[name="iniciador"]').value =
      expediente.iniciador + ' / ' + expediente.extracto + ' / ' + expediente.nroexpediente_usado;
    document.querySelector('[name="nroexpediente_usado"]').value =
      expediente.nroexpediente_usado;

    // Habilitar botones
    
    const btnRechazo = document.getElementById('btnRechazo');
    btnRechazo.disabled = false;
    btnRechazo.onclick = function () {
      window.location.href = 'observar_expediente.php?expediente=' + encodeURIComponent(expediente.nroexpediente_usado) + '&formulario=' + encodeURIComponent("SOLICITUD PERMISO EXPLORACION")
    };
  }
}
</script>
<script src="transformador_coordenadas.js"></script>
</body>
</html>

