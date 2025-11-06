<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>NUEVA SOLICITUD DE MANIFESTACIÓN DE DESCUBRIMIENTO</title>

  
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
    <h1 class="mb-3">NUEVA SOLICITUD DE MANIFESTACIÓN DE DESCUBRIMIENTO</h1>
    <h4 class="mb-4 text-muted">FORMULARIO DE INGRESO A BASE DE DATOS GEOGRÁFICA</h4>

    <form method="post" action="guardar_formulario_solicitud_manifestacion.php" id="formulario" onsubmit="return prepararEnvio()">
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
       
        <div class="col-12">
          <label class="form-label">Nombre de manifestación de descubrimiento: </label>
          <input type="text" name="denominacion" class="form-control" required>
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
          <label class="form-label">Solicitantes: <small>-- Usar 00000000000 par ingreso extranjeros --</small></label>
          <div id="solicitantes-container"></div>
          <button type="button" class="btn btn-secondary mt-2" onclick="agregarSolicitante()">+ Agregar otro solicitante</button>
        </div>
        
        <div class="col-6">
          <label class="form-label">Tipo de Yacimiento</label>
          <select name="tipo_yacimiento" id="tipo_yacimiento" class="form-select" required onchange="mostrarDescripcionYacimiento()">
          <option value="">-- Seleccionar --</option>
          <option value="diseminado">Diseminado</option>
          <option value="vetiforme">Vetiforme</option>
          <option value="masas">Masas</option>
          <option value="manto">Manto</option>
          <option value="otros">Otros</option>
          </select>
        </div>

       
  <div class="col-12">
  <label class="form-label">Minerales Descubiertos <small>Puede seleccionar múltiples minerales con tecla control</small></label>
  <select name="minerales[]" class="form-select" multiple required>
    <!-- Opciones de minerales -->
    <option value="1">Oro</option>
    <option value="2">Plata</option>
    <option value="3">Platino</option>
    <option value="4">Mercurio</option>
    <option value="5">Cobre</option>
    <option value="6">Hierro</option>
    <option value="7">Plomo</option>
    <option value="8">Estaño</option>
    <option value="9">Zinc</option>
    <option value="10">Níquel</option>
    <option value="11">Cobalto</option>
    <option value="12">Bismuto</option>
    <option value="13">Manganeso</option>
    <option value="14">Antimonio</option>
    <option value="15">Wolfram</option>
    <option value="16">Aluminio</option>
    <option value="17">Berilio</option>
    <option value="18">Vanadio</option>
    <option value="19">Cadmio</option>
    <option value="20">Tantalio</option>
    <option value="21">Molibdeno</option>
    <option value="22">Litio</option>
    <option value="23">Potasio</option>
    <option value="24">Hulla</option>
    <option value="25">Lignito</option>
    <option value="26">Antracita</option>
    <option value="27">Hidrocarburos Sólidos</option>
    <option value="28">Arsénico</option>
    <option value="29">Cuarzo</option>
    <option value="30">Feldespato</option>
    <option value="31">Mica</option>
    <option value="32">Fluorita</option>
    <option value="33">Fosfatos Calizos</option>
    <option value="34">Azufre</option>
    <option value="35">Boratos</option>
    <option value="36">Piedras Preciosas</option>
    <option value="37">Vapores Endógenos</option>
    <option value="38">Arenas Metalíferas</option>
    <option value="39">Piedras Preciosas (en lechos de ríos)</option>
    <option value="40">Mineral en aguas corrientes (aluviones)</option>
    <option value="41">Mineral en placeres</option>
    <option value="42">Mineral en desmontes, relaves y escoriales</option>
    <option value="43">Salitres</option>
    <option value="44">Salinas</option>
    <option value="45">Turberas</option>
    <option value="46">Metales no comprendidos en 1° Categ.</option>
    <option value="47">Tierras Piritosas y Aluminosas</option>
    <option value="48">Abrasivos</option>
    <option value="49">Ocres</option>
    <option value="50">Resinas</option>
    <option value="51">Esteatitas</option>
    <option value="52">Baritina</option>
    <option value="53">Caparrosas</option>
    <option value="54">Grafito</option>
    <option value="55">Caolín</option>
    <option value="56">Sales Alcalinas o Alcalino Terrosas</option>
    <option value="57">Amianto</option>
    <option value="58">Bentonita</option>
    <option value="59">Zeolitas o Minerales Permutantes</option>
    <option value="60">Piedras Calizas</option>
    <option value="61">Calcáreas</option>
    <option value="62">Margas</option>
    <option value="63">Yeso</option>
    <option value="64">Alabastro</option>
    <option value="65">Mármoles</option>
    <option value="66">Granitos</option>
    <option value="67">Dolomita</option>
    <option value="68">Pizarras</option>
    <option value="69">Areniscas</option>
    <option value="70">Cuarcitas</option>
    <option value="71">Basaltos</option>
    <option value="72">Arenas No Metalíferas</option>
    <option value="73">Cascajo</option>
    <option value="74">Canto Rodado</option>
    <option value="75">Pedregullo</option>
    <option value="76">Grava</option>
    <option value="77">Conchilla</option>
    <option value="78">Piedra Laja</option>
    <option value="79">Ceniza Volcánica</option>
    <option value="80">Perlita</option>
    <option value="81">Piedra Pómez</option>
    <option value="82">Piedra Afilar</option>
    <option value="83">Puzzolanas</option>
    <option value="84">Pórfidos</option>
    <option value="85">Tobas</option>
    <option value="86">Tosca</option>
    <option value="87">Serpentina</option>
    <option value="88">Piedra Sapo</option>
    <option value="89">Loes</option>
    <option value="90">Arcillas Comunes</option>
    <option value="91">Uranio</option>
    <option value="92">Torio</option>
    <option value="93">Carbón</option>
    <option value="94">Sulfato de Aluminio</option>
    <option value="95">Sulfato de Sodio</option>
    <option value="96">Talco</option>
    <option value="97">Diatomita</option>
    <option value="98">Esquistos Bituminosos</option>
    <option value="99">Sulfato de Calcio</option>
    <option value="100">Sulfuro de Hierro</option>
    <option value="101">Sulfato de Magneso</option>
    <option value="102">Blenda</option>
    <option value="103">Galena</option>
    <option value="104">Tungsteno</option>
  </select>
</div>

        <div class="col-md-6">
          <label class="form-label">Superficie declarada (ha)</label>
          <input type="number" step="0.0001" min="0" name="sup_ha" class="form-control" placeholder="0.0000" required>
        </div>

        <div class="col-12">
          <label class="form-label">Aporta coordenadas de LEM: </label>
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

      

      <hr class="my-4" />
<div id="lugarExtraccion" style="display:none">   
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
  
  <legend class="h4" id="legend-coordenadas">Ingresar Coordenadas Gauss Krüger Faja 2 POSGAR 2007 (EPSG:5344)</legend>
  <div id="info-sistema" class="alert alert-warning small mt-2" style="display: none;">
    <i class="bi bi-info-circle"></i> Las coordenadas serán transformadas automáticamente a POSGAR 2007 antes de guardar
  </div>
  
  <div class="alert alert-info">
    <h6><strong>📋 NORMATIVA CATASTRAL - Secuencia de Vértices:</strong></h6>
    <ul class="mb-0">
      <li><strong>Primer vértice:</strong> Debe ser el punto más al NOROESTE (mayor Norte, menor Este en caso de empate)</li>
      <li><strong>Secuencia:</strong> Continuar en sentido HORARIO (como las manecillas del reloj)</li>
      <li><strong>Herramientas:</strong> Use los botones "🔍 Validar Secuencia" y "🔧 Corregir Orden" para verificar y corregir automáticamente</li>
    </ul>
  </div>
  
  
 <div style="margin-top:1rem;">
  <div class="row g-3 align-items-end">
    
    <legend class="h5">LUGAR DE EXTRACCIÓN MUESTRA</legend>
    <div class="col-md-4">
      <label class="form-label fw-bold">X (ESTE)</label>
      <input type="number" name="muestra_x" id="muestra_x" class="form-control" required step="0.01" min="0" placeholder="0.00">
      <small class="text-muted">Debe comenzar con 2</small>
    </div>
    <div class="col-md-4">
      <label class="form-label fw-bold">Y (NORTE)</label>
      <input type="number" name="muestra_y" id="muestra_y" class="form-control" required step="0.01" min="0" placeholder="0.00">
      <small class="text-muted">Debe comenzar con 6</small>
    </div>
    <div class="col-md-4">
      <div class="d-flex gap-2">
        <button type="button" onclick="agregarPuntoUnico()" class="btn btn-orange flex-fill">Agregar Punto</button>
        <button type="button" onclick="eliminarUltimoPuntoUnico(event)" class="btn btn-danger flex-fill">Eliminar Último</button>
      </div>
    </div>
    </div>
    <hr class="my-4" />
    </div>

    <div class="row g-3 align-items-end">
    
    <legend class="h5">AREA DE RECONOCIMIENTO</legend>
    <div class="col-md-4">
      <label class="form-label fw-bold">X (ESTE)</label>
      <input type="number" id="x" class="form-control" required step="0.01" min="0" placeholder="0.00">
      <small class="text-muted">Debe comenzar con 2</small>
    </div>
    <div class="col-md-4">
      <label class="form-label fw-bold">Y (NORTE)</label>
      <input type="number" id="y" class="form-control" required step="0.01" min="0" placeholder="0.00">
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
    </div>
  </div>
  
  <!-- Vista previa de transformación de coordenadas -->
  <div id="preview-transformacion" class="mt-3" style="display: none;"></div>
</fieldset>


      <input type="hidden" name="puntos" id="puntos">
      <ul class="mt-3" id="listaPuntos"></ul>
      <div id="map"></div>

      
  </div>
      <input type="hidden" name="nroexpediente_usado">

      <br>
      <h3>Verificación de condiciones para ingreso a la base de datos</h3>
  <div class="col-md-10">
  <div class="condicion">
    <div class="etiqueta">Ubicación de la solicitud, al menos en forma parcial, dentro de los límites de la provincia.</div>
    <label class="switch">
      <input type="checkbox" id="cond1" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">Secuencia de vértices solicitados (Inicia en vértice noroeste y la secuencia es horaria).</div>
    <label class="switch">
      <input type="checkbox" id="cond2" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">Polígono con lados Norte-Sur y Este-Oeste.</div>
    <label class="switch">
      <input type="checkbox" id="cond3" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">LEM ubicado dentro del área de la MD.</div>
    <label class="switch">
      <input type="checkbox" id="cond4" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">Ubicación de la manifestación de descubrimiento sobre área libre de otra manifestación o mina, al menos en forma parcial.</div>
    <label class="switch">
      <input type="checkbox" id="cond5" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

    <div class="condicion">
    <div class="etiqueta">Ubicación de la solicitud fuera de áreas de exclusión de actividades mineras, al menos en forma parcial (Parque Nacional San Guillermo, Parque Nacional El Leoncito, Parque Provincial Ischigualasto)</div>
    <label class="switch">
      <input type="checkbox" id="cond6" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

    <div class="condicion">
    <div class="etiqueta">Verificación de condición de descubrimiento directo.</div>
    <label class="switch">
      <input type="checkbox" id="cond7" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>
  </div>

      <button type="submit" id="btnEnviar" class="btn btn-success mt-4" disabled>Enviar Formulario</button>
      <button type="button" id="btnRechazo" class="btn btn-danger mt-4" disabled>Observar Expediente</button>

    </form>
  </div>
  
  <script src="mapa.js"></script>
  <script src="expediente.js"></script>
  <script src="solicitante.js"></script>
  <script>
    let puntos = [];
    let poligonoLayer;
    let marcadorUnico = null;


    proj4.defs("EPSG:22182", "+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 +ellps=WGS84 +units=m +no_defs");
    const crs22182 = new L.Proj.CRS('EPSG:22182',
    proj4.defs('EPSG:22182'),
    {
      origin: [2200000, 0],
      resolutions: [1024, 512, 256, 128, 64, 32, 16, 8, 4, 2, 1],
    }
    );

    const fromProjection = proj4("EPSG:22182");
    const toProjection = proj4("WGS84");   

  function agregarPuntoUnico() {
  const imuestra_x = document.getElementById("muestra_x");
  const imuestra_y = document.getElementById("muestra_y");
  const muestra_x = parseFloat(imuestra_x.value);
  const muestra_y = parseFloat(imuestra_y.value);
  
  if (isNaN(muestra_x) || isNaN(muestra_y)) {
    alert("Por favor ingresa valores válidos para ESTE y NORTE en lugar de extracción de muestra");
    return;
  }

  // Validar que X (ESTE) comience con 2 (rango 2000000-2999999)
  if (muestra_y < 2000000 || muestra_y >= 3000000) {
    alert('⚠️ ERROR: La coordenada X (ESTE) debe comenzar con 2\nRango válido: 2000000 - 2999999\nEjemplo: 2492370.69');
    imuestra_y.focus();
    return;
  }

  // Validar que Y (NORTE) comience con 6 (rango 6000000-6999999)
  if (muestra_x < 6000000 || muestra_x >= 7000000) {
    alert('⚠️ ERROR: La coordenada Y (NORTE) debe comenzar con 6\nRango válido: 6000000 - 6999999\nEjemplo: 6677723.20');
    imuestra_x.focus();
    return;
  }

  const [lon, lat] = proj4(fromProjection, toProjection, [muestra_y, muestra_x]);

  // Si ya hay un marcador anterior, eliminarlo
  if (marcadorUnico) {
    map.removeLayer(marcadorUnico);
  }

  marcadorUnico = L.marker([lat, lon]).addTo(map)
    .bindPopup(`ESTE: ${muestra_x}, NORTE: ${muestra_y}`)
    .openPopup();

  
}

function eliminarUltimoPuntoUnico(event) {
  event.preventDefault();
  if (marcadorUnico) {
    map.removeLayer(marcadorUnico);
    marcadorUnico = null;
    document.getElementById("muestra_x").value = '0.00';
    document.getElementById("muestra_y").value = '0.00';
  }
}

    function agregarPunto() {
      const ix = document.getElementById("x");
      const iy = document.getElementById("y");
      const x = parseFloat(ix.value);
      const y = parseFloat(iy.value);
      
      if (isNaN(x) || isNaN(y)) {
        alert("Por favor ingresa valores válidos para ESTE y NORTE");
        return;
      }

      // Validar que X (ESTE) comience con 2 (rango 2000000-2999999)
      if (y < 2000000 || y >= 3000000) {
        alert('⚠️ ERROR: La coordenada X (ESTE) debe comenzar con 2\nRango válido: 2000000 - 2999999\nEjemplo: 2492370.69');
        iy.focus();
        return;
      }

      // Validar que Y (NORTE) comience con 6 (rango 6000000-6999999)
      if (x < 6000000 || x >= 7000000) {
        alert('⚠️ ERROR: La coordenada Y (NORTE) debe comenzar con 6\nRango válido: 6000000 - 6999999\nEjemplo: 6677723.20');
        ix.focus();
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
        } else {
          // Punto inválido - no agregar
          alert("⚠️ ADVERTENCIA: El punto ingresado está muy alejado del límite provincial.\n\nNo se puede agregar este punto. Máximo permitido: 100km fuera del límite provincial.");
        }
      });
    }

    // Función para validar punto dentro del límite con notificación
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

    function leafletPuntoEnPoligono(punto, poligono) {
        const x = punto.lng, y = punto.lat;
        const vs = poligono.getLatLngs()[0]; // Primer anillo
        let dentro = false;

        for (let i = 0, j = vs.length - 1; i < vs.length; j = i++) {
        const xi = vs[i].lng, yi = vs[i].lat;
        const xj = vs[j].lng, yj = vs[j].lat;

        const intersecta = ((yi > y) !== (yj > y)) &&
                       (x < (xj - xi) * (y - yi) / (yj - yi + 0.00000001) + xi);
        if (intersecta) dentro = !dentro;
    }

    return dentro;
    }

    function prepararEnvio() {

        // Verificar que el marcador único esté dentro del polígono
        if (marcadorUnico && poligonoLayer) {
            const punto = marcadorUnico.getLatLng(); // lat/lng
        if (!leafletPuntoEnPoligono(punto, poligonoLayer)) {
            alert("El punto de muestra debe estar dentro del polígono de reconocimiento.");
        return false;
        }
        }

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
        
        // 1. Encontrar el punto más al noroeste (mayor Y, menor X en caso de empate)
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
                return false; // Rechazar envío para que usuario revise el reordenamiento
            }
        }
        
        // 3. Verificar orientación horaria
        const area = calcularAreaConSigno(puntos);
        if (area > 0) {
            if (confirm(`⚠️ ADVERTENCIA: Los puntos están en sentido ANTIHORARIO.\n\n` +
                       `Los vértices deben seguir el sentido HORARIO (como las manecillas del reloj).\n\n` +
                       `¿Desea invertir automáticamente el orden de los puntos?`)) {
                invertirOrdenPuntos();
                return false; // Rechazar envío para que usuario revise
            }
        }
        
        return true;
    }
    
    // Calcular área con signo (positiva = antihorario, negativa = horario)
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
        alert(`✅ Puntos reordenados. Ahora comienzan desde el vértice noroeste.\n\nPor favor revise la secuencia y vuelva a enviar.`);
    }
    
    // Invertir orden de puntos (mantener el primero, invertir el resto)
    function invertirOrdenPuntos() {
        if (puntos.length > 1) {
            const primero = puntos[0];
            const resto = puntos.slice(1).reverse();
            puntos = [primero, ...resto];
            actualizarListaPuntos();
            dibujarPoligono();
            alert(`✅ Orden de puntos invertido a sentido horario.\n\nPor favor revise la secuencia y vuelva a enviar.`);
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
    
    // Botón para validar secuencia manualmente
    function validarSecuenciaManual() {
        if (puntos.length < 3) {
            alert("Necesita al menos 3 puntos para validar la secuencia.");
            return;
        }
        
        const area = calcularAreaConSigno(puntos);
        let mensaje = "🔍 VALIDACIÓN DE SECUENCIA:\n\n";
        
        // Verificar punto noroeste
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
            mensaje += "🔧 Use el botón 'Corregir Secuencia' para solucionarlo automáticamente.";
        } else {
            mensaje += "🎉 ¡Secuencia correcta! Los puntos siguen la normativa catastral.";
        }
        
        alert(mensaje);
    }
    
    // Función para corregir automáticamente toda la secuencia
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
        
        // 4. Mostrar resultado
        if (cambiosRealizados.length > 0) {
            alert(`🔧 CORRECCIÓN AUTOMÁTICA COMPLETADA:\n\n${cambiosRealizados.join('\n')}\n\n` +
                  `Los puntos ahora siguen la normativa catastral.\n\n` +
                  `Por favor revise la nueva secuencia antes de enviar.`);
        } else {
            alert(`✅ SECUENCIA CORRECTA:\n\n` +
                  `Los puntos ya siguen la normativa catastral.\n\n` +
                  `No se requieren cambios.`);
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
    document.getElementById("muestra_x").addEventListener("input", function () {
      limitarInputDecimales(this);
    });
    document.getElementById("muestra_y").addEventListener("input", function () {
      limitarInputDecimales(this);
    });


  </script>

  <script>
  // Obtengo todos los radios con nombre 'programa'
  const radios = document.querySelectorAll('input[name="programa"]');
  const lugarExtraccion = document.getElementById('lugarExtraccion');
  const inputX = document.getElementById('muestra_x');
  const inputY = document.getElementById('muestra_y');

  radios.forEach(radio => {
    radio.addEventListener('change', () => {
      if (radio.value === 'SI' && radio.checked) {
        lugarExtraccion.style.display = 'block';
        map.invalidateSize();
        inputX.setAttribute('required', 'required');
        inputY.setAttribute('required', 'required');
        document.getElementById('btnRechazo').disabled = false;
      } else if (radio.value === 'NO' && radio.checked) {
        lugarExtraccion.style.display = 'none';
        inputX.removeAttribute('required');
        inputY.removeAttribute('required');
        document.getElementById('btnRechazo').disabled = false;
      }
    });
  });
</script>
<script>
function mostrarDescripcionYacimiento() {
  const select = document.getElementById("tipo_yacimiento");
  const descripcion = document.getElementById("descripcion_yacimiento_container");
  if (select.value === "otros") {
    descripcion.style.display = "block";
    descripcion.querySelector("input").setAttribute("required", "required");
  } else {
    descripcion.style.display = "none";
    descripcion.querySelector("input").removeAttribute("required");
  }
}

function verificarTodos() {
    const condiciones = [1, 2, 3, 4, 5, 6, 7];
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
      window.location.href = 'observar_expediente.php?expediente=' + encodeURIComponent(expediente.nroexpediente_usado) + '&formulario=' + encodeURIComponent("SOLICITUD MANIFESTACION")
    };
  }
}
</script>
<script src="transformador_coordenadas.js"></script>
</body>
</html>
</body>
</html>
