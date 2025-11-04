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
  <title>NUEVA SOLICITUD DE PETICIÓN DE MENSURA</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>
  <script src="https://unpkg.com/proj4leaflet"></script>
  <script src="https://unpkg.com/leaflet-providers"></script>
  <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>


  <link href="style.css" rel="stylesheet" type="text/css" /> 
  <style>
            /* Texto de polígonos (solicitudes) */
        .poligono-label {
        color: green;
        font-weight: bold;
        font-size: 16px;
        text-align: center;
        white-space: nowrap;
        }

        /* Texto de pertenencias */
        .pertenencia-label {
        color: blue;
        font-weight: bold;
        font-size: 14px;
        text-align: center;
        white-space: nowrap;
        }

        /* Texto de vértices */
        .vertice-label {
        color: black;
        font-weight: normal;
        font-size: 11px;
        text-align: center;
        white-space: nowrap;
        }
  </style>

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
    <h1 class="mb-3">NUEVA SOLICITUD DE PETICIÓN DE MENSURA</h1>
    <h4 class="mb-4 text-muted">FORMULARIO DE INGRESO A BASE DE DATOS GEOGRÁFICA</h4>

    <form method="post" action="guardar_formulario_solicitud_peticion_mensura.php" id="formulario" onsubmit="return prepararEnvio()">
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
          <label class="form-label">Nombre de la Mina</label>
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
          <label class="form-label">Solicitantes <small>-- Usar 00000000000 par ingreso extranjeros --</small></label>
          <div id="solicitantes-container"></div>
          <button type="button" class="btn btn-secondary mt-2" onclick="agregarSolicitante()">+ Agregar otro solicitante</button>
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
        <div class="col-md-12">
          <label class="form-label">Área total de mensura solicitada (ha)</label>
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
  
  <legend class="h4" id="legend-coordenadas">Ingresar Coordenadas Gauss Krüger Faja 2 POSGAR 2007 (EPSG:5344)</legend>
  <div id="info-sistema" class="alert alert-warning small mt-2" style="display: none;">
    <i class="bi bi-info-circle"></i> Las coordenadas serán transformadas automáticamente a POSGAR 2007 antes de guardar
  </div>
  
  <div class="alert alert-info">
    <h6><strong>📋 NORMATIVA CATASTRAL - Secuencia de Vértices:</strong></h6>
    <ul class="mb-0">
      <li><strong>Primer vértice:</strong> Debe ser el punto más al NOROESTE (mayor Norte, menor Este en caso de empate)</li>
      <li><strong>Secuencia:</strong> Continuar en sentido ANTIHORARIO (contrario a las manecillas del reloj)</li>
      <li><strong>Importante:</strong> Esta normativa aplica tanto para el perímetro de mensura como para las pertenencias</li>
    </ul>
  </div>
  
  <br>
   <!-- CSV Solicitud Mensura -->
   <h6>Ingreso de coordenadas de los vértices del perímetro de la solicitud de mensura (archivo csv)</h6>   
   <div class="col-6 mt-3">
        <div class="input-group">
          
          <input type="file" id="csvSolicitudMensura" accept=".csv" class="form-control">
          <button type="button" class="btn btn-outline-success" onclick="importarSolicitudMensura()">Importar Perímetro</button>
        </div>
        <small class="form-text text-muted">El archivo csv no debe tener encabezados y la siguiente estructura: identificador de polígono; identificador de vértice; coordenada este; coordenada norte por ejemplo: <br>1;1;2457558,74;6557062,97<br>1;4;2459358,71;6557062,98<br>1;8;2459358,71;6556662,98</small>
      </div>

  <!-- CSV Pertenencias -->
  <br>
  <h6>Ingreso de coordenadas de los vértices de pertenencias (archivo csv)</h6>    
  <div class="col-6 mt-3">
        <div class="input-group">
          
          <input type="file" id="csvFile" accept=".csv" class="form-control">
          <button type="button" class="btn btn-outline-primary" onclick="importarDesdeCSV()">Importar Pertenencias</button>
        </div>
        <small class="form-text text-muted">El archivo csv no debe tener encabezados y la siguiente estructura: identificador de polígono; identificador de pertenencia; identificador de vértice; coordenada este; coordenada norte) por ejemplo: <br>1;1;1;2457558,74;6557062,97<br>1;1;2;2458158,71;6557062,97<br>1;1;6;2458158,71;6556662,97</small>
      </div>

      <!-- Ingreso manual de coordenadas (opcional) -->
      <div class="row g-3 align-items-end mt-3">
        <div class="col-md-4">
          <label class="form-label">ESTE</label>
          <input type="number" id="x_manual" class="form-control" step="0.01" min="0" placeholder="0.00">
        </div>
        <div class="col-md-4">
          <label class="form-label">NORTE</label>
          <input type="number" id="y_manual" class="form-control" step="0.01" min="0" placeholder="0.00">
        </div>
        <div class="col-md-4">
          <div class="d-flex gap-2">
            <button type="button" onclick="agregarPuntoManual()" class="btn btn-orange flex-fill">Agregar Punto</button>
            <button type="button" onclick="eliminarUltimoPuntoManual()" class="btn btn-danger flex-fill">Eliminar Último</button>
          </div>
          <div class="d-flex gap-2 mt-2">
            <button type="button" onclick="finalizarPoligonoManual()" class="btn btn-primary btn-sm flex-fill">Finalizar Polígono</button>
            <button type="button" onclick="limpiarPoligonoManual()" class="btn btn-secondary btn-sm flex-fill">Limpiar</button>
          </div>
        </div>
      </div>

      <div class="col-12 mt-3">
        <div class="d-flex gap-2">
          <button type="button" onclick="analizarPoligonosImportados()" class="btn btn-info">
            🔍 Analizar Secuencia de Polígonos
          </button>
          <small class="align-self-center text-muted">Verificar que los polígonos importados cumplan con la normativa catastral</small>
        </div>
      </div>

      
      <input type="hidden" name="solicitudes_mensura" id="solicitudes_mensura">
      <input type="hidden" name="multipoligonos" id="multipoligonos">
      
      <!-- Vista previa de transformación de coordenadas -->
      <div id="preview-transformacion" class="mt-3" style="display: none;"></div>
      
      <div id="map"></div>

      <ul class="mt-3" id="listaPuntos"></ul>   

      <input type="hidden" name="nroexpediente_usado">

      <br>
      <h3>Verificación de condiciones para ingreso a la base de datos</h3>
  <div class="col-md-10">
  <div class="condicion">
    <div class="etiqueta">Labor legal ubicada dentro de los límites del área a mensurar.</div>
    <label class="switch">
      <input type="checkbox" id="cond1" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">Pertenencias solicitadas ubicadas dentro del área de la manifestación de descubrimiento.</div>
    <label class="switch">
      <input type="checkbox" id="cond2" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">El área de las pertenencias es acorde al tipo de yacimiento y mineral.</div>
    <label class="switch">
      <input type="checkbox" id="cond3" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">Secuencia de vértices pertenencias correcta.</div>
    <label class="switch">
      <input type="checkbox" id="cond4" onchange="verificarTodos()">
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

  <script src="mapa_tipo2.js?ver2"></script>
    <script src="expediente_tipo2.js"></script>
    <script src="solicitante.js"></script>
  <script>
  let multipoligonos = [];
  let solicitudesMensura = [];
  let layersPoligonos = [];
  let layersSolicitudes = [];
  // Para ingreso manual temporal
  let manualVertices = [];
  let manualLayers = [];

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

// Dibujar pertinencias con id_vertice
// Dibujar multipolígonos con id_vertice
function dibujarMultipoligonos(){
  layersPoligonos.forEach(l=>map.removeLayer(l));
  layersPoligonos=[];

  multipoligonos.forEach(pol=>{
    if(pol.vertices.length<3) return;
    const coords = pol.vertices.map(p=>{
      const [lon,lat]=proj4(fromProjection,toProjection,[p.x,p.y]);
      return [lat,lon];
    });

    const layer = L.polygon(coords, {
        color: 'blue',     
        weight: 1,         
        fill: true,        
        dashArray: '4,6'   
      }).addTo(map);
    layersPoligonos.push(layer);

    const latCentroid = coords.reduce((sum,c)=>sum+c[0],0)/coords.length;
    const lonCentroid = coords.reduce((sum,c)=>sum+c[1],0)/coords.length;
   
   
    L.marker([latCentroid,lonCentroid],{
    icon: L.divIcon({
    className: 'pertenencia-label',
    html: `Pertenencia ${pol.id_p}`
    })
    }).addTo(map);

    pol.vertices.forEach(p=>{
      const [lon,lat] = proj4(fromProjection,toProjection,[p.x,p.y]);
      L.marker([lat,lon],{
    icon: L.divIcon({
        className: 'vertice-label',
        html: `${p.id_v}`,
        iconAnchor: [-5, -5] 
    })
    }).addTo(map);
    });
  });
  ajustarVista(); 
  actualizarLista();
}

// Dibujar solicitudes con id_vertice
function dibujarSolicitudes(){
  layersSolicitudes.forEach(l=>map.removeLayer(l));
  layersSolicitudes=[];

  solicitudesMensura.forEach(pol=>{
    if(!pol.vertices || pol.vertices.length<3) return;

    const coords = pol.vertices.map(p=>{ 
      const [lon,lat]=proj4(fromProjection,toProjection,[p.x,p.y]); 
      return [lat,lon]; 
    });

    const layer = L.polygon(coords, { color:'green', weight:5, fill:false }).addTo(map);
    layersSolicitudes.push(layer);

    const latCentroid = coords.reduce((s,c)=>s+c[0],0)/coords.length;
    const lonCentroid = coords.reduce((s,c)=>s+c[1],0)/coords.length;
    L.marker([latCentroid,lonCentroid],{
    icon: L.divIcon({
        className: 'poligono-label',
        html: `Polígono ${pol.id_mensura}`
    })
    }).addTo(map);

    
    pol.vertices.forEach(p=>{
      const [lon,lat] = proj4(fromProjection,toProjection,[p.x,p.y]);
      L.marker([lat,lon],{
        icon: L.divIcon({
        className: 'vertice-label',
        html: `${p.id_v}`
        })
        }).addTo(map);
        });
      });

  ajustarVista(); 
  actualizarLista();
}

// --- Funciones para ingreso manual de coordenadas/polígonos ---
function agregarPuntoManual(){
  const ix = document.getElementById('x_manual');
  const iy = document.getElementById('y_manual');
  const x = parseFloat(ix.value);
  const y = parseFloat(iy.value);
  if (isNaN(x) || isNaN(y) || x <= 0 || y <= 0){
    alert('Por favor ingresa valores válidos para ESTE y NORTE');
    return;
  }
  manualVertices.push({ id_v: manualVertices.length + 1, x, y });
  dibujarTemporalManual();
  ix.value = '';
  iy.value = '';
}

function eliminarUltimoPuntoManual(){
  if (manualVertices.length === 0){
    alert('No hay puntos manuales para eliminar');
    return;
  }
  manualVertices.pop();
  dibujarTemporalManual();
}

function limpiarPoligonoManual(){
  manualVertices = [];
  manualLayers.forEach(l=>map.removeLayer(l));
  manualLayers = [];
}

function dibujarTemporalManual(){
  // eliminar capas previas
  manualLayers.forEach(l=>map.removeLayer(l));
  manualLayers = [];
  if (manualVertices.length === 0) return;

  const coords = manualVertices.map(p=>{ const [lon,lat]=proj4(fromProjection,toProjection,[p.x,p.y]); return [lat,lon]; });

  manualVertices.forEach(p=>{
    const [lon,lat] = proj4(fromProjection,toProjection,[p.x,p.y]);
    const m = L.marker([lat,lon],{
      icon: L.divIcon({ className: 'vertice-label', html: p.id_v })
    }).addTo(map);
    manualLayers.push(m);
  });

  if (manualVertices.length > 2){
    const layer = L.polygon(coords, { color:'green', weight:4, fill:false }).addTo(map);
    manualLayers.push(layer);
  }
}

function finalizarPoligonoManual(){
  if (manualVertices.length < 3){
    alert('Un polígono necesita al menos 3 vértices');
    return;
  }
  // generar id único
  let maxId = 0;
  solicitudesMensura.forEach(p=>{ if (p.id_mensura && Number(p.id_mensura) > maxId) maxId = Number(p.id_mensura); });
  const newId = maxId + 1;
  const pol = { id_mensura: newId, vertices: manualVertices.slice(), sup_decl: 0, sup_graf_ha: 0 };
  solicitudesMensura.push(pol);
  document.getElementById('solicitudes_mensura').value = JSON.stringify(solicitudesMensura);
  limpiarPoligonoManual();
  dibujarSolicitudes();
}


function ajustarVista(){
  const allLayers = [...layersPoligonos,...layersSolicitudes];
  if(allLayers.length>0) map.fitBounds(L.featureGroup(allLayers).getBounds());
}


function actualizarLista(){
  const lista = document.getElementById("listaPuntos");
  lista.innerHTML="";

  solicitudesMensura.forEach((pol,idx)=>{
    const coords = pol.vertices.map(p=>{ const [lon,lat]=proj4(fromProjection,toProjection,[p.x,p.y]); return [lon,lat]; });
    if(coords.length>2) coords.push(coords[0]);

    pol.sup_graf_ha = turf.area({type:"Polygon", coordinates:[coords]}) / 10000;
    const areaHa = (isFinite(pol.sup_graf_ha) ? pol.sup_graf_ha : 0).toFixed(2);

    const li = document.createElement("li");
    li.className="list-group-item list-group-item-success";
    li.innerHTML = `
      <div class="d-flex gap-3 align-items-center" style="background-color: #d8e6db; padding: 5px;">
        <div><strong>Polígono ${pol.id_mensura}</strong></div>
        <div>Superficie calculada: ${areaHa} ha</div>
        <div class="d-flex align-items-center gap-2" style="width: 100%; max-width: 350px;">
        <span class="flex-shrink-0">Superficie declarada:</span> 
        <input type="number" step="0.01" class="form-control form-control-sm flex-grow-1" 
            value="${pol.sup_decl ?? ''}"
            onchange="solicitudesMensura[${idx}].sup_decl = parseFloat(this.value)||0"> ha
        </div>
        </div>`;
    lista.appendChild(li);
  });

  multipoligonos.forEach((pol,idx)=>{
    const coords = pol.vertices.map(p=>{ 
      const [lon,lat]=proj4(fromProjection,toProjection,[p.x,p.y]); 
      return [lon,lat]; 
    });
    if(coords.length>2) coords.push(coords[0]);

    pol.sup_graf_ha = turf.area({type:"Polygon", coordinates:[coords]}) / 10000;
    const areaHa = (isFinite(pol.sup_graf_ha) ? pol.sup_graf_ha : 0).toFixed(2);
    
    const li = document.createElement("li");
    li.className="list-group-item list-group-item-primary";
    li.innerHTML = `
      <li class="list-group-item list-group-item-primary" data-index="${idx}">
  <div class="d-flex gap-3 align-items-center" style="background-color: #c6d1f5ff; padding: 5px;">
    <div><strong>Pertenencia ${pol.id_p}</strong></div>
    <div>Polígono: ${pol.id_sol}</div>
    <div>Superficie calculada: <b>${areaHa} ha</b></div>
        <div class="d-flex align-items-center gap-2" style="max-width: 350px; width: 100%;">
        <span class="flex-shrink-0">Superficie declarada:</span>
        <input type="number" step="0.01" class="form-control form-control-sm sup-decl-input flex-grow-1" value="${pol.sup_decl ?? ''}"> ha
    </div>
    </div>
</li>`;
    lista.appendChild(li);
  });
}

// Importar CSV Pertinencias
function importarDesdeCSV(){
  const input = document.getElementById("csvFile");
  const file = input.files[0];
  if(!file){ alert("Seleccione un archivo CSV"); return; }

  const reader = new FileReader();
  reader.onload = e => {
    const lines = e.target.result.split(/\r?\n/).filter(l=>l.trim()!=="");
    let tempPol = {};
    lines.forEach(line => {
      const parts = line.split(";");
      if(parts.length < 5) return; // ahora tenemos id_poligono; id_pertenencia; id_vertice; este; norte
      const id_sol = parts[0].trim(); 
      const id_pert = parts[1].trim();
      const id_v = parts[2].trim();
      const x = parseFloat(parts[3].replace(",","."));
      const y = parseFloat(parts[4].replace(",","."));
      if(isNaN(x)||isNaN(y)) return;

          if(!tempPol[id_pert]) tempPol[id_pert] = { id_p: id_pert, id_sol: id_sol, vertices: [] };
      tempPol[id_pert].vertices.push({id_v, x, y});
    });

    multipoligonos = Object.values(tempPol);

    document.getElementById("multipoligonos").value = JSON.stringify(multipoligonos);
    dibujarMultipoligonos();
  };
  reader.readAsText(file,"UTF-8");
}

// Importar CSV Solicitud Mensura
function importarSolicitudMensura(){
  const input=document.getElementById("csvSolicitudMensura");
  const file=input.files[0];
  if(!file){alert("Seleccione un archivo CSV"); return;}
  const reader=new FileReader();
  reader.onload=e=>{
    const lines=e.target.result.split(/\r?\n/).filter(l=>l.trim()!=="");
    const tempPol={};
    lines.forEach(line=>{
      const parts=line.split(";");
      if(parts.length<4) return;
      const id_mensura=parts[0].trim(), id_v=parts[1].trim();
      const x=parseFloat(parts[2].replace(",",".")), y=parseFloat(parts[3].replace(",",".")); 
      if(isNaN(x)||isNaN(y)) return;

      if(!tempPol[id_mensura]) tempPol[id_mensura] = { id_mensura, vertices: [], sup_decl: 0, sup_graf_ha: 0 };
      tempPol[id_mensura].vertices.push({id_v, x, y});
    });

    solicitudesMensura = Object.values(tempPol);
    document.getElementById("solicitudes_mensura").value = JSON.stringify(solicitudesMensura);
    dibujarSolicitudes();
  };
  reader.readAsText(file,"UTF-8");
}

function prepararEnvio(){

  solicitudesMensura.forEach((pol, idx) => {
    pol.sup_decl = parseFloat(pol.sup_decl) || 0;
  });

  const pertenenciaInputs = document.querySelectorAll('#listaPuntos .sup-decl-input');
  pertenenciaInputs.forEach(input => {
      const listItem = input.closest('li');
      const idx = listItem.getAttribute('data-index');
      if (multipoligonos[idx]) {
          multipoligonos[idx].sup_decl = parseFloat(input.value) || 0;
      }
  });

  // Validar secuencia horaria de polígonos
  if (!validarSecuenciaPoligonos()) {
    return false;
  }

  document.getElementById("solicitudes_mensura").value = JSON.stringify(solicitudesMensura);
  document.getElementById("multipoligonos").value = JSON.stringify(multipoligonos);
  return true;
}

// Validar secuencia horaria de todos los polígonos
function validarSecuenciaPoligonos() {
  let erroresEncontrados = [];
  
  // Validar solicitudes de mensura
  solicitudesMensura.forEach((pol, idx) => {
    if (pol.vertices && pol.vertices.length >= 3) {
      const resultado = validarSecuenciaPoligono(pol.vertices, `Polígono de Mensura ${pol.id_mensura}`);
      if (!resultado.valido) {
        erroresEncontrados.push(resultado.mensaje);
      }
    }
  });
  
  // Validar pertenencias
  multipoligonos.forEach((pol, idx) => {
    if (pol.vertices && pol.vertices.length >= 3) {
      const resultado = validarSecuenciaPoligono(pol.vertices, `Pertenencia ${pol.id_p}`);
      if (!resultado.valido) {
        erroresEncontrados.push(resultado.mensaje);
      }
    }
  });
  
  if (erroresEncontrados.length > 0) {
    const mensaje = `⚠️ ERRORES DE SECUENCIA DETECTADOS:\n\n${erroresEncontrados.join('\n\n')}\n\n` +
                   `Para corregir:\n` +
                   `1. Edite los archivos CSV\n` +
                   `2. Asegúrese de que cada polígono comience desde el vértice noroeste\n` +
                   `3. Los vértices deben seguir orden antihorario\n` +
                   `4. Vuelva a importar los archivos`;
    
    if (confirm(mensaje + `\n\n¿Desea continuar de todos modos? (No recomendado)`)) {
      return true;
    }
    return false;
  }
  
  return true;
}

// Validar secuencia de un polígono individual
function validarSecuenciaPoligono(vertices, nombre) {
  if (vertices.length < 3) {
    return { valido: true, mensaje: "" };
  }
  
  // Encontrar punto noroeste
  let puntoNoroeste = 0;
  for (let i = 1; i < vertices.length; i++) {
    if (vertices[i].y > vertices[puntoNoroeste].y || 
        (vertices[i].y === vertices[puntoNoroeste].y && vertices[i].x < vertices[puntoNoroeste].x)) {
      puntoNoroeste = i;
    }
  }
  
  // Calcular área con signo
  let area = 0;
  const n = vertices.length;
  for (let i = 0; i < n; i++) {
    const j = (i + 1) % n;
    area += (vertices[j].x - vertices[i].x) * (vertices[j].y + vertices[i].y);
  }
  area = area / 2;
  
  const errores = [];
  
  if (puntoNoroeste !== 0) {
    errores.push(`❌ El primer vértice NO es el noroeste (debería ser el vértice ${puntoNoroeste + 1})`);
  }
  
  if (area < 0) {
    errores.push(`❌ Los vértices están en sentido HORARIO (deben ir en sentido antihorario)`);
  }
  
  if (errores.length > 0) {
    return {
      valido: false,
      mensaje: `🔸 ${nombre}:\n${errores.join('\n')}`
    };
  }
  
  return { valido: true, mensaje: "" };
}

// Función para analizar polígonos importados
function analizarPoligonosImportados() {
  if (solicitudesMensura.length === 0 && multipoligonos.length === 0) {
    alert("No hay polígonos importados para analizar.");
    return;
  }
  
  let informe = "📊 ANÁLISIS DE POLÍGONOS IMPORTADOS:\n\n";
  
  // Analizar solicitudes de mensura
  if (solicitudesMensura.length > 0) {
    informe += "🔹 POLÍGONOS DE MENSURA:\n";
    solicitudesMensura.forEach((pol, idx) => {
      if (pol.vertices && pol.vertices.length >= 3) {
        const resultado = validarSecuenciaPoligono(pol.vertices, `Polígono ${pol.id_mensura}`);
        informe += `  • ${resultado.valido ? '✅' : '❌'} Polígono ${pol.id_mensura} (${pol.vertices.length} vértices)\n`;
        if (!resultado.valido) {
          informe += `    ${resultado.mensaje.replace('🔸 Polígono ' + pol.id_mensura + ':\n', '').replace('❌ ', '    - ')}\n`;
        }
      }
    });
    informe += "\n";
  }
  
  // Analizar pertenencias
  if (multipoligonos.length > 0) {
    informe += "🔹 PERTENENCIAS:\n";
    multipoligonos.forEach((pol, idx) => {
      if (pol.vertices && pol.vertices.length >= 3) {
        const resultado = validarSecuenciaPoligono(pol.vertices, `Pertenencia ${pol.id_p}`);
        informe += `  • ${resultado.valido ? '✅' : '❌'} Pertenencia ${pol.id_p} (${pol.vertices.length} vértices)\n`;
        if (!resultado.valido) {
          informe += `    ${resultado.mensaje.replace('🔸 Pertenencia ' + pol.id_p + ':\n', '').replace('❌ ', '    - ')}\n`;
        }
      }
    });
  }
  
  alert(informe);
}
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
      window.location.href = 'observar_expediente.php?expediente=' + encodeURIComponent(expediente.nroexpediente_usado) + '&formulario=' + encodeURIComponent("SOLICITUD PETICION MENSURA")
    };
  }
}
</script>
<script src="transformador_coordenadas.js"></script>
</body>
</html>

