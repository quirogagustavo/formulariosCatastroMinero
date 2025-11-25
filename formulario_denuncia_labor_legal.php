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
  <title>NUEVA DENUNCIA DE LABOR LEGAL</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>
  <script src="https://unpkg.com/proj4leaflet"></script>
  <script src="https://unpkg.com/leaflet-providers"></script>

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
    <h1 class="mb-3">NUEVA DENUNCIA DE LABOR LEGAL</h1>
    <h4 class="mb-4 text-muted">FORMULARIO DE INGRESO A BASE DE DATOS GEOGRÁFICA</h4>

    <form method="post" action="guardar_formulario_denuncia_labor_legal.php" id="formulario" onsubmit="return prepararEnvio()">
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
          <label class="form-label">Nombre de manifestación de descubrimiento</label>
          <input type="text" name="denominacion" class="form-control" required readonly>
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
          <label class="form-label">Solicitantes: <small>-- Usar 00 par ingreso extranjeros --</label>
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

        <div class="col-6" id="descripcion_yacimiento_container" style="display: none;">
          <label class="form-label">Describa el tipo de yacimiento</label>
          <input type="text" name="descripcion_yacimiento" class="form-control" placeholder="Describa aquí...">
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

<div class="col-6">
          <label class="form-label">Tipo Labor Ejecutada</label>
          <select name="tipo_labor" id="tipo_labor" class="form-select" required onchange="mostrarDescripcionLabor()">
          <option value="">-- Seleccionar --</option>
          <option value="Pozo">Pozo</option>
          <option value="Galería">Galería</option>
          <option value="Trinchera">Trinchera</option>
          <option value="Perforación">Perforación</option>
          <option value="Destape">Destape</option>
          <option value="Otros">Otros</option>
          </select>
        </div>

        <div class="col-6" id="descripcion_labor_container" style="display: none;">
          <label class="form-label">Describa el tipo de labor ejecutada</label>
          <input type="text" name="descripcion_labor" class="form-control" placeholder="Describa aquí...">
        </div>
           

      <hr class="my-4" />
<fieldset>
  <legend class="h4">Ingresar Coordenadas Gauss Krüger Faja 2</legend>
  
  <div class="alert alert-info">
    <h6><strong>📋 NORMATIVA CATASTRAL - Ubicación de Labor Legal:</strong></h6>
    <ul class="mb-0">
      <li><strong>Ubicación:</strong> Coordenadas exactas donde se ejecutó la labor legal</li>
      <li><strong>Precisión:</strong> Utilizar coordenadas Gauss Krüger Faja 2</li>
      <li><strong>Sistema destino:</strong> POSGAR 2007 (EPSG:5344)</li>
      <li><strong>Validación:</strong> La labor debe estar dentro de los límites de la manifestación</li>
    </ul>
  </div>
  
  <!-- Selector de Sistema de Coordenadas -->
  <div class="row g-3 mb-3">
    <div class="col-md-12">
      <label class="form-label fw-bold">Sistema de Coordenadas de Entrada</label>
      <select id="sistemaCoordenadasLabor" class="form-select" onchange="actualizarPlaceholdersLabor()">
        <option value="5344">POSGAR 2007 (EPSG:5344) - Recomendado</option>
        <option value="22182">POSGAR 94 (EPSG:22182) - Se convertirá automáticamente</option>
      </select>
      <small class="text-muted">Si sus coordenadas están en POSGAR 94, se convertirán automáticamente a POSGAR 2007 usando los parámetros oficiales del IGN.</small>
    </div>
  </div>
  
 <div id="lugarExtraccion" style="display:block; margin-top:1rem;">
  <div class="row g-3 align-items-end">
    
    <legend class="h5">INGRESO DE COORDENADAS LA LABOR LEGAL</legend>
    <div class="col-md-4">
      <label class="form-label">X (ESTE) <small class="text-danger">Debe comenzar con 2</small></label>
      <input type="number" name="muestra_x" id="muestra_x" class="form-control" required step="0.01" placeholder="Ejemplo: 2492370.69">
    </div>
    <div class="col-md-4">
      <label class="form-label">Y (NORTE) <small class="text-danger">Debe comenzar con 6</small></label>
      <input type="number" name="muestra_y" id="muestra_y" class="form-control" required step="0.01" placeholder="Ejemplo: 6677723.20">
    </div>
    <div class="col-md-4">
      <div class="d-flex gap-2">
        <button type="button" onclick="agregarPuntoUnico()" class="btn btn-orange flex-fill">Agregar Punto</button>
        <button type="button" onclick="eliminarUltimoPuntoUnico(event)" class="btn btn-danger flex-fill">Eliminar Último</button>
      </div>
    </div>
  </div>
  
  <!-- Información del punto agregado en POSGAR 2007 -->
  <div id="infoPuntoAgregado" class="alert alert-success mt-3" style="display: none;">
    <h6 class="fw-bold">📍 Punto de Labor Legal (POSGAR 2007)</h6>
    <div class="row">
      <div class="col-md-6">
        <strong>X (ESTE):</strong> <span id="puntoEste">-</span>
      </div>
      <div class="col-md-6">
        <strong>Y (NORTE):</strong> <span id="puntoNorte">-</span>
      </div>
    </div>
  </div>
       
   
</fieldset>


      <input type="hidden" name="puntos" id="puntos">
      <ul class="mt-3" id="listaPuntos"></ul>
      <div id="map"></div>

      <input type="hidden" name="nroexpediente_usado"> 

      <br>
      <h3>Verificación de condiciones para ingreso a la base de datos</h3>
  <div class="col-md-6">
  <div class="condicion">
    <div class="etiqueta">Ubicación de la Labor Legal dentro de los límits de la manifestación.</div>
    <label class="switch">
      <input type="checkbox" id="cond1" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">Presentación de Croquis</div>
    <label class="switch">
      <input type="checkbox" id="cond2" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">Presentación de Comentarios Descriptivos</div>
    <label class="switch">
      <input type="checkbox" id="cond3" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>


  </div>


      <div class="row mt-4">
        <div class="col-auto">
          <button type="submit" id="btnEnviar" class="btn btn-success" disabled>Enviar Formulario</button>
        </div>
        <div class="col-auto">
          <button type="button" id="btnRechazo" class="btn btn-danger" disabled>Observar Expediente</button>
        </div>
      </div>
    </form>
</div>

  <script src="mapa.js?ver2"></script>
  <script src="expediente_tipo2.js"></script>
  <script src="solicitante.js"></script>
  <script>
    let puntos = [];
    let poligonoLayer;
    let marcadorUnico = null;

    // Definiciones de sistemas de coordenadas
    // POSGAR 94 (EPSG:22182)
    proj4.defs("EPSG:22182", "+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 +ellps=WGS84 +units=m +no_defs");
    
    // POSGAR 2007 (EPSG:5344) - Faja 2
    proj4.defs("EPSG:5344", "+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 +ellps=GRS80 +towgs84=0,0,0,0,0,0,0 +units=m +no_defs +type=crs");
    
    // POSGAR 94 (EPSG:22182) - Faja 2
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
    
    /**
     * Actualiza los placeholders de los campos según el sistema de coordenadas seleccionado
     */
    function actualizarPlaceholdersLabor() {
      const sistema = document.getElementById('sistemaCoordenadasLabor').value;
      const inputX = document.getElementById('muestra_x');
      const inputY = document.getElementById('muestra_y');
      
      if (sistema === '5344') {
        // POSGAR 2007
        inputX.placeholder = 'Ejemplo: 2492370.69';
        inputY.placeholder = 'Ejemplo: 6677723.20';
      } else {
        // POSGAR 94
        inputX.placeholder = 'Ejemplo: 2492382.03';
        inputY.placeholder = 'Ejemplo: 6677729.89';
      }
    }

    function agregarPuntoUnico() {
      let muestra_x = parseFloat(document.getElementById("muestra_x").value);
      let muestra_y = parseFloat(document.getElementById("muestra_y").value);
      
      if (isNaN(muestra_x) || isNaN(muestra_y)) {
        alert("Por favor ingresa valores válidos para X (ESTE) e Y (NORTE)");
        return;
      }
      
      const sistema = document.getElementById('sistemaCoordenadasLabor').value;
      
      // Validar rangos según el sistema de coordenadas
      if (sistema === '5344') {
        // POSGAR 2007: ESTE debe comenzar con 2, NORTE con 6
        if (muestra_x < 2000000 || muestra_x >= 3000000) {
          alert('⚠️ ERROR: La coordenada X (ESTE) debe comenzar con 2\nEjemplo: 2492370.69');
          document.getElementById("muestra_x").focus();
          return;
        }
        if (muestra_y < 6000000 || muestra_y >= 7000000) {
          alert('⚠️ ERROR: La coordenada Y (NORTE) debe comenzar con 6\nEjemplo: 6677723.20');
          document.getElementById("muestra_y").focus();
          return;
        }
      } else {
        // POSGAR 94: rangos similares pero se convertirán
        if (muestra_x < 2000000 || muestra_x >= 3000000) {
          alert('⚠️ ERROR: La coordenada X (ESTE) debe comenzar con 2\nEjemplo: 2492382.03');
          document.getElementById("muestra_x").focus();
          return;
        }
        if (muestra_y < 6000000 || muestra_y >= 7000000) {
          alert('⚠️ ERROR: La coordenada Y (NORTE) debe comenzar con 6\nEjemplo: 6677729.89');
          document.getElementById("muestra_y").focus();
          return;
        }
        
        // Convertir de POSGAR 94 a POSGAR 2007
        // 1. POSGAR 94 -> WGS84
        const [lon, lat] = proj4('EPSG:22182', 'WGS84', [muestra_x, muestra_y]);
        
        // 2. WGS84 -> POSGAR 2007
        const [este07, norte07] = proj4('WGS84', 'EPSG:5344', [lon, lat]);
        
        console.log(`Conversión POSGAR 94 -> 2007: (${muestra_x}, ${muestra_y}) -> (${este07.toFixed(2)}, ${norte07.toFixed(2)})`);
        
        // Actualizar valores a POSGAR 2007
        muestra_x = este07;
        muestra_y = norte07;
        
        // IMPORTANTE: Actualizar los campos del formulario con las coordenadas convertidas
        document.getElementById("muestra_x").value = muestra_x.toFixed(2);
        document.getElementById("muestra_y").value = muestra_y.toFixed(2);
        
        // Mostrar mensaje informativo
        alert(`✅ Coordenadas convertidas de POSGAR 94 a POSGAR 2007:\n\n` +
              `ESTE: ${muestra_x.toFixed(2)}\n` +
              `NORTE: ${muestra_y.toFixed(2)}\n\n` +
              `Las coordenadas se guardarán en POSGAR 2007.`);
      }
      
      // Usar POSGAR 2007 para visualización
      const [lon, lat] = proj4('EPSG:5344', 'WGS84', [muestra_x, muestra_y]);

      // Si ya hay un marcador anterior, eliminarlo
      if (marcadorUnico) {
        map.removeLayer(marcadorUnico);
      }

      marcadorUnico = L.marker([lat, lon]).addTo(map)
        .bindPopup(`ESTE: ${muestra_x.toFixed(2)}, NORTE: ${muestra_y.toFixed(2)}`)
        .openPopup();
      
      map.setView([lat, lon], 13);
      
      // Mostrar información del punto en POSGAR 2007
      document.getElementById('infoPuntoAgregado').style.display = 'block';
      document.getElementById('puntoEste').textContent = muestra_x.toFixed(2);
      document.getElementById('puntoNorte').textContent = muestra_y.toFixed(2);
    }

function eliminarUltimoPuntoUnico(event) {
  event.preventDefault();
  if (marcadorUnico) {
    map.removeLayer(marcadorUnico);
    marcadorUnico = null;
    document.getElementById("muestra_x").value = '';
    document.getElementById("muestra_y").value = '';
    
    // Ocultar información del punto
    document.getElementById('infoPuntoAgregado').style.display = 'none';
    document.getElementById('puntoEste').textContent = '-';
    document.getElementById('puntoNorte').textContent = '-';
  }
}

    function agregarPunto() {
      const x = parseFloat(document.getElementById("x").value);
      const y = parseFloat(document.getElementById("y").value);
      if (isNaN(x) || isNaN(y)) {
        alert("Por favor ingresa valores válidos para ESTE y NORTE");
        return;
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

        // Verificar que el marcador único esté dentro del polígono (si hay polígono)
        if (marcadorUnico && poligonoLayer) {
            const punto = marcadorUnico.getLatLng(); // lat/lng
        if (!leafletPuntoEnPoligono(punto, poligonoLayer)) {
            alert("El punto de labor legal debe estar dentro del polígono de reconocimiento.");
        return false;
        }
        }

        // Validar secuencia horaria de puntos (si hay polígono)
        if (puntos.length >= 3 && !validarSecuenciaHoraria()) {
            return false;
        }
      
      if (puntos.length > 0 && puntos.length < 3) {
        alert("Debe agregar al menos 3 puntos para formar un polígono o ningún punto para solo labor legal.");
        return false;
      }
      document.getElementById("puntos").value = JSON.stringify(puntos);
      return true;
    }

    // Función para validar la secuencia horaria de puntos (si aplica)
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
                       `¿Desea reordenar automáticamente los puntos comenzando desde el noroeste?`)) {
                reordenarDesdePuntoNoroeste(puntoNoroeste);
                return false;
            }
        }
        
        // 3. Verificar orientación horaria
        const area = calcularAreaConSigno(puntos);
        if (area > 0) {
            if (confirm(`⚠️ ADVERTENCIA: Los puntos están en sentido ANTIHORARIO.\n\n` +
                       `Los vértices deben seguir el sentido de las manecillas del reloj.\n\n` +
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
            alert(`✅ Orden de puntos invertido a sentido horario.\n\nPor favor revise la secuencia y vuelva a enviar.`);
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
        inputX.setAttribute('required', 'required');
        inputY.setAttribute('required', 'required');
      } else if (radio.value === 'NO' && radio.checked) {
        lugarExtraccion.style.display = 'none';
        inputX.removeAttribute('required');
        inputY.removeAttribute('required');
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

function mostrarDescripcionLabor() {
  const select = document.getElementById("tipo_labor");
  const descripcion = document.getElementById("descripcion_labor_container");
  if (select.value === "otros") {
    descripcion.style.display = "block";
    descripcion.querySelector("input").setAttribute("required", "required");
  } else {
    descripcion.style.display = "none";
    descripcion.querySelector("input").removeAttribute("required");
  }
}
</script>
<script>
function verificarTodos() {
    const condiciones = [1, 2, 3];
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

       
    const btnRechazo = document.getElementById('btnRechazo');
    btnRechazo.disabled = false;
    btnRechazo.onclick = function () {
      window.location.href = 'observar_expediente.php?expediente=' + encodeURIComponent(expediente.nroexpediente_usado) + '&formulario=' + encodeURIComponent("DENUNCIA LABOR LEGAL")
    };
  }
}
</script>

</body>
</html>
