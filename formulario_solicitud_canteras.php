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
  <title>NUEVA SOLICITUD DE CANTERAS</title>

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
    <h1 class="mb-3">NUEVA SOLICITUD DE CANTERAS</h1>
    <h4 class="mb-4 text-muted">FORMULARIO DE INGRESO A BASE DE DATOS GEOGRÁFICA</h4>

    <form method="post" action="guardar_formulario_solicitud_canteras.php" id="formulario" onsubmit="return prepararEnvio()">
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

         <div class="col-md-7">
          <label class="form-label">Nombre de la Cantera</label>
          <input type="text" name="cantera" class="form-control" required>
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

       
        <div class="col-md-7">
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
          <br><label class="form-label">Superficie declarada (ha)</label>
          <input type="number" step="0.0001" min="0" name="sup_ha" class="form-control" placeholder="0.0000" required>
        </div>

        <div class="col-12">
          <label class="form-label">Proyecto de Aprovechamiento: </label>
          <div class="form-check form-check-inline">
            <input type="radio" class="form-check-input" id="proy1" name="proyecto" value="SI">
            <label class="form-check-label" for="prog1">SI</label>
          </div>
          <div class="form-check form-check-inline">
            <input type="radio" class="form-check-input" id="proy2" name="proyecto" value="NO">
            <label class="form-check-label" for="prog2">NO</label>
          </div>
        </div>
      

      <div class="col-12">
          <label class="form-label">Situación de la Zona: </label>
          <div class="form-check form-check-inline">
            <input type="radio" class="form-check-input" id="zona1" name="zona" value="FISCAL">
            <label class="form-check-label" for="prog1">Fiscal</label>
          </div>
          <div class="form-check form-check-inline">
            <input type="radio" class="form-check-input" id="zona2" name="zona" value="PARTICULAR">
            <label class="form-check-label" for="prog2">Particular</label>
          </div>
        </div>

         <div class="col-md-6">
          <br><label class="form-label">Plazo Solicitado (años):</label>
          <input type="number" step="1" min="0" name="duracion" class="form-control" placeholder="0" required>
        </div>
      
    </div>

        

        

      <hr class="my-4" />
<fieldset>
  <!-- Selector de Sistema de Coordenadas -->
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <label class="form-label fw-bold">Sistema de Coordenadas</label>
      <select id="sistema-coordenadas" class="form-select" onchange="actualizarEtiquetasCoordenadas(); toggleMetodoTransformacionCanteras();">
        <option value="posgar2007" selected>POSGAR 2007 (EPSG:5344) - Por defecto</option>
        <option value="posgar94">POSGAR 94 (EPSG:22182) - Se transformará a POSGAR 2007</option>
      </select>
      <small class="text-muted">Seleccione el sistema en el que ingresará las coordenadas</small>
    </div>
    <div class="col-md-6" id="divMetodoTransformacionCanteras" style="display: none;">
      <label class="form-label fw-bold">Método de Transformación</label>
      <select id="metodoTransformacionCanteras" class="form-select">
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
  
  <div class="alert alert-info">
    <h6><strong>📋 NORMATIVA CATASTRAL - Secuencia de Vértices:</strong></h6>
    <ul class="mb-0">
      <li><strong>Primer vértice:</strong> Debe ser el punto más al NOROESTE (mayor Norte, menor Este en caso de empate)</li>
      <li><strong>Secuencia:</strong> Continuar en sentido HORARIO (como las manecillas del reloj)</li>
      <li><strong>Herramientas:</strong> Use los botones "🔍 Validar Secuencia" y "🔧 Corregir Orden" para verificar y corregir automáticamente</li>
    </ul>
  </div>
  
  <div class="row g-3 align-items-end">
    <div class="col-md-4">
      <label class="form-label fw-bold">X (NORTE)</label>
      <input type="number" id="x" class="form-control" required step="0.01" min="0" placeholder="0.00">
      <small class="text-muted">Debe comenzar con 6</small>
    </div>
    <div class="col-md-4">
      <label class="form-label fw-bold">Y (ESTE)</label>
      <input type="number" id="y" class="form-control" required step="0.01" min="0" placeholder="0.00">
      <small class="text-muted">Debe comenzar con 2</small>
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
      
      <!-- Tabla de puntos del área de cantera -->
      <div id="tabla-puntos-container" class="mt-3" style="display: none;">
        <h5>Puntos del Área de Cantera</h5>
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
    <div class="etiqueta">La solicitud se ubica sobre área libre de otros derechos mineros</div>
    <label class="switch">
      <input type="checkbox" id="cond3" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">La solicitud se ubica fuera de áreas de exclusión minera</div>
    <label class="switch">
      <input type="checkbox" id="cond4" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>
  </div>


      <button type="submit" id="btnEnviar" class="btn btn-success mt-4" disabled>Enviar Formulario</button>
      <button type="button" id="btnRechazo" class="btn btn-danger mt-4" disabled>Observar Expediente</button>

    </div>

    </form>
  </div>

  <script src="mapa_tipo3.js"></script>
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
    function toggleMetodoTransformacionCanteras() {
      const sistema = document.getElementById('sistema-coordenadas').value;
      const divMetodo = document.getElementById('divMetodoTransformacionCanteras');
      
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
      let x = parseFloat(ix.value);
      let y = parseFloat(iy.value);
      
      if (isNaN(x) || isNaN(y)) {
        alert("Por favor ingresa valores válidos para NORTE (X) y ESTE (Y)");
        return;
      }

      // Validar que Y (ESTE) comience con 2 (rango 2000000-2999999)
      if (y < 2000000 || y >= 3000000) {
        alert('⚠️ ERROR: La coordenada Y (ESTE) debe comenzar con 2\nRango válido: 2000000 - 2999999\nEjemplo: 2492370.69');
        iy.focus();
        return;
      }

      // Validar que X (NORTE) comience con 6 (rango 6000000-6999999)
      if (x < 6000000 || x >= 7000000) {
        alert('⚠️ ERROR: La coordenada X (NORTE) debe comenzar con 6\nRango válido: 6000000 - 6999999\nEjemplo: 6677723.20');
        ix.focus();
        return;
      }

      // Convertir si es POSGAR 94
      if (sistema === 'posgar94') {
        const metodoSeleccionado = document.getElementById('metodoTransformacionCanteras').value;
        const convertido = convertirPOSGAR94a2007(y, x, metodoSeleccionado);
        y = convertido.este07;
        x = convertido.norte07;
        alert(`✅ Coordenadas convertidas de POSGAR 94 a POSGAR 2007:\nMétodo: ${metodoSeleccionado}\n\nESTE: ${y.toFixed(2)}\nNORTE: ${x.toFixed(2)}`);
      }

      puntos.push({x, y, z: 0});
      actualizarListaPuntos();
      dibujarPoligono();
      document.getElementById("x").value = '0.00';
      document.getElementById("y").value = '0.00';
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

    function eliminarPuntoPorIndice(indice) {
      if (confirm(`¿Está seguro de eliminar el vértice ${indice + 1}?`)) {
        puntos.splice(indice, 1);
        actualizarListaPuntos();
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
        const [lon, lat] = proj4(fromProjection, toProjection, [punto.y, punto.x]);
        // Hacer zoom al punto con nivel 17
        map.setView([lat, lon], 17);
      }
    }

    function prepararEnvio() {
       if (!validarProyecto()) return false;
       if (!validarSituacion()) return false;
       
         // Validar secuencia horaria de puntos (no bloqueante)
         validarSecuenciaHoraria();
      
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
                 `¿Desea reordenar automáticamente los puntos comenzando desde el noroeste y continuar con el envío?`)) {
            reordenarDesdePuntoNoroeste(puntoNoroeste);
          }
        }
        
        // 3. Verificar orientación horaria
        const area = calcularAreaConSigno(puntos);
        if (area > 0) {
          if (confirm(`⚠️ ADVERTENCIA: Los puntos están en sentido ANTIHORARIO.\n\n` +
                 `Los vértices deben seguir el sentido HORARIO (como las manecillas del reloj).\n\n` +
                 `¿Desea invertir automáticamente el orden de los puntos y continuar con el envío?`)) {
            invertirOrdenPuntos();
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
    
    // Invertir orden de puntos
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
            
            // Añadir indicador de vértice noroeste
            const verticeLabel = index === 0 ? `<strong>V${index + 1}</strong> (NOROESTE)` : `<strong>V${index + 1}</strong>`;
            
            tr.innerHTML = `
                <td class="text-center">${verticeLabel}</td>
                <td>${punto.x.toFixed(2)}</td>
                <td>${punto.y.toFixed(2)}</td>
                <td class="text-center text-success">✅ OK</td>
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

      function validarProyecto() {
  const seleccionado = document.querySelector('input[name="proyecto"]:checked');
  if (!seleccionado) {
    alert("Debe seleccionar una opción en 'Proyecto de Aprovechamiento'.");
    return false;
  }
  return true;
  }

   function validarSituacion() {
  const seleccionado = document.querySelector('input[name="zona"]:checked');
  if (!seleccionado) {
    alert("Debe seleccionar una opción en 'Situacion de la Zona'.");
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

function verificarTodos() {
    const condiciones = [1, 2, 3, 4];
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
      window.location.href = 'observar_expediente.php?expediente=' + encodeURIComponent(expediente.nroexpediente_usado) + '&formulario=' + encodeURIComponent("SOLICITUD CANTERAS")
    };
  }
}
</script>
<script src="transformador_coordenadas.js"></script>
 
</body>
</html>
