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
      <li><strong>Secuencia:</strong> Continuar en sentido ANTIHORARIO (contrario a las manecillas del reloj)</li>
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

  <link href="style.css?v=<?=time()?>" rel="stylesheet" type="text/css" /> 
  
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
          <br><label class="form-label">Superficie declarada (ha)</label>
          <input type="number" step="0.0001" min="0" name="sup_ha" class="form-control" placeholder="0.0000" required>
        </div>

        

      <hr class="my-4" />
<fieldset>
  <!-- Selector de Sistema de Coordenadas -->
  <div class="row mb-3">
    <div class="col-md-6">
      <label class="form-label fw-bold">Sistema de Coordenadas</label>
      <select id="sistema-coordenadas" class="form-select" onchange="actualizarEtiquetasCoordenadas()">
        <option value="posgar2007" selected>POSGAR 2007 (EPSG:5344) - Por defecto</option>
        <option value="posgar94">POSGAR 94 (EPSG:22182) - Se transformará a POSGAR 2007</option>
      </select>
      <small class="text-muted">Seleccione el sistema en el que ingresará las coordenadas</small>
    </div>
  </div>
  
  <legend class="h5" id="legend-coordenadas">Ingresar Coordenadas Gauss Krüger Faja 2 POSGAR 2007 (EPSG:5344)</legend>
  <div id="info-sistema" class="alert alert-warning small mt-2" style="display: none;">
    <i class="bi bi-info-circle"></i> Las coordenadas serán transformadas automáticamente a POSGAR 2007 antes de guardar
  </div>
  
  <div class="row g-3 align-items-end">
    <div class="col-md-4">
      <label class="form-label">ESTE</label>
      <input type="number" id="x" class="form-control" required step="0.01" min="0" placeholder="0.00" onblur="validarCoordenadaEnTiempoReal()">
    </div>
    <div class="col-md-4">
      <label class="form-label">NORTE</label>
      <input type="number" id="y" class="form-control" required step="0.01" min="0" placeholder="0.00" onblur="validarCoordenadaEnTiempoReal()">
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
      <ul class="mt-3" id="listaPuntos"></ul>
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

    proj4.defs("EPSG:22182", "+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 +ellps=WGS84 +units=m +no_defs");
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

    function agregarPunto() {
      const x = parseFloat(document.getElementById("x").value);
      const y = parseFloat(document.getElementById("y").value);
      if (isNaN(x) || isNaN(y)) {
        alert("Por favor ingresa valores válidos para ESTE y NORTE");
        return;
      }

      // Validar el punto con el nuevo sistema
      validarPuntoDentroLimite(x, y, function(valido, color, estado) {
        if (valido) {
          // Punto válido - agregar a la lista con información de estado
          puntos.push({x, y, z: 0, color: color, estado: estado});
          actualizarListaPuntos();
          dibujarPoligono();
          document.getElementById("x").value = '0.00';
          document.getElementById("y").value = '0.00';
          
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
      const x = parseFloat(document.getElementById("x").value);
      const y = parseFloat(document.getElementById("y").value);
      
      // Solo validar si ambas coordenadas tienen valores válidos
      if (!isNaN(x) && !isNaN(y) && x > 0 && y > 0) {
        validarPuntoDentroLimiteSilencioso(x, y, function(valido, mensaje, color, estado) {
          mostrarEstadoValidacion(valido, mensaje, color, estado);
        });
      } else {
        ocultarEstadoValidacion();
      }
    }

    function validarPuntoDentroLimiteSilencioso(x, y, callback) {
      const formData = new FormData();
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
        const [lon, lat] = proj4(fromProjection, toProjection, [p.x, p.y]);
        return [lat, lon];
      });
      poligonoLayer = L.polygon(coords, { color: 'blue' }).addTo(map);
      map.fitBounds(poligonoLayer.getBounds());
    }

    function eliminarUltimoPunto(event) {
      event.preventDefault();
      if (puntos.length === 0) return;
      puntos.pop();
      actualizarListaPuntos();
      if (puntos.length >= 3) dibujarPoligono();
      else if (poligonoLayer) {
        map.removeLayer(poligonoLayer);
        poligonoLayer = null;
      }
    }

    function prepararEnvio() {
       if (!validarCategorias()) return false;
       if (!validarPrograma()) return false;
       
       // Validar secuencia horaria de puntos
       if (!validarSecuenciaHoraria()) {
           return false;
       }
      
      if (puntos.length < 3) {
        alert("Debe agregar al menos 3 puntos para formar un polígono.");
        return false;
      }
      document.getElementById("puntos").value = JSON.stringify(puntos);
      return true;
    }

    // Función para validar la secuencia horaria de puntos
    function validarSecuenciaHoraria() {
        if (puntos.length < 3) return true;
        
        // Para 4 puntos, verificar si están en el orden correcto: V3→V1→V4→V2
        if (puntos.length === 4) {
            if (confirm(`⚠️ POLÍGONO DE 4 PUNTOS DETECTADO:\n\n` +
                       `Para evitar la forma de "reloj de arena", el orden correcto debe ser:\n` +
                       `V3 → V1 → V4 → V2\n\n` +
                       `¿Desea aplicar automáticamente el orden correcto?`)) {
                corregirSecuenciaCompleta();
                return false;
            }
            return true;
        }
        
        // Para otros casos, usar validación original
        
        // 1. Encontrar el punto más al noroeste
        let puntoNoroeste = 0;
        for (let i = 1; i < puntos.length; i++) {
            if (puntos[i].y > puntos[puntoNoroeste].y || 
                (puntos[i].y === puntos[puntoNoroeste].y && puntos[i].x < puntos[puntoNoroeste].x)) {
                puntoNoroeste = i;
            }
        }
        
        // 2. Verificar si el primer punto es el noroeste
        if (puntoNoroeste !== 0) {
            if (confirm(`⚠️ ADVERTENCIA: El primer punto no es el más al NOROESTE.\n\nEl punto más al noroeste está en la posición ${puntoNoroeste + 1}:\n` +
                       `ESTE: ${puntos[puntoNoroeste].x}, NORTE: ${puntos[puntoNoroeste].y}\n\n` +
                       `¿Desea reordenar automáticamente los puntos comenzando desde el noroeste?`)) {
                reordenarDesdePuntoNoroeste(puntoNoroeste);
                return false;
            }
        }
        
        // 3. Verificar orientación antihoraria
        const area = calcularAreaConSigno(puntos);
        if (area < 0) {
            if (confirm(`⚠️ ADVERTENCIA: Los puntos están en sentido HORARIO.\n\n` +
                       `Los vértices deben seguir el sentido ANTIHORARIO (contrario a las manecillas del reloj).\n\n` +
                       `¿Desea invertir automáticamente el orden de los puntos?`)) {
                invertirOrdenPuntos();
                return false;
            }
        }
        
        return true;
    }
    
    // Calcular área con signo
    function calcularAreaConSigno(vertices) {
        let area = 0;
        const n = vertices.length;
        for (let i = 0; i < n; i++) {
            const j = (i + 1) % n;
            area += (vertices[j].x - vertices[i].x) * (vertices[j].y + vertices[i].y);
        }
        return area / 2;
    }
    
    // Reordenar puntos comenzando desde el punto noroeste
    function reordenarDesdePuntoNoroeste(indiceNoroeste) {
        const nuevosDesdeNoroeste = puntos.slice(indiceNoroeste).concat(puntos.slice(0, indiceNoroeste));
        puntos = nuevosDesdeNoroeste;
        actualizarListaPuntos();
        dibujarPoligono();
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
            alert(`✅ Orden de puntos invertido a sentido antihorario.\n\nPor favor revise la secuencia y vuelva a enviar.`);
        }
    }
    
    // Actualizar la lista visual de puntos
    function actualizarListaPuntos() {
        const lista = document.getElementById("listaPuntos");
        lista.innerHTML = "";
        puntos.forEach((punto, index) => {
            const li = document.createElement("li");
            li.textContent = `V${index + 1}: ESTE: ${punto.x}, NORTE: ${punto.y}`;
            
            // Aplicar color según el estado de validación
            if (punto.color) {
                li.style.color = punto.color;
                
                // Agregar información del estado
                if (punto.estado === 'zona_tolerancia') {
                    li.textContent += " ⚠️ (Fuera del límite - Zona tolerancia)";
                    li.style.fontWeight = "bold";
                } else if (punto.estado === 'dentro_limite') {
                    li.textContent += " ✅ (Dentro del límite)";
                }
            } else {
                // Fallback para puntos sin información de validación
                li.style.color = 'green';
            }
            
            if (index === 0) {
                li.style.fontWeight = "bold";
                li.textContent += " (NOROESTE)";
            }
            lista.appendChild(li);
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
        
        mensaje += `🔄 Orientación: ${area < 0 ? "❌ HORARIO (incorrecto)" : "✅ ANTIHORARIO (correcto)"}\n\n`;
        
        if (puntoNoroeste !== 0 || area < 0) {
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
            
            // 2. Verificar y corregir orientación antihoraria
            const area = calcularAreaConSigno(puntos);
            if (area < 0) {
                const primero = puntos[0];
                const resto = puntos.slice(1).reverse();
                puntos = [primero, ...resto];
                cambiosRealizados.push("✅ Invertido a sentido antihorario");
            }
        }
        
        // 3. Actualizar interfaz
        actualizarListaPuntos();
        dibujarPoligono();
        
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

