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
  <title>NUEVA DENUNCIA DE SERVIDUMBRE</title>

  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4leaflet/1.0.2/proj4leaflet.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/dxf-parser/dist/dxf-parser.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@turf/turf@6.5.0/turf.min.js"></script>
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
    <h1 class="mb-3">NUEVA DENUNCIA DE SERVIDUMBRE</h1>
    <h4 class="mb-4 text-muted">FORMULARIO DE INGRESO A BASE DE DATOS GEOGRÁFICA</h4>

    <form method="post" action="guardar_formulario_denuncia_servidumbre.php" id="formulario" onsubmit="return prepararEnvio()">
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
          <label class="form-label">Derechos a los que sirve:</label>
          <input type="text" name="derechos" class="form-control" required>
        </div>

        <div class="col-12">
          <label class="form-label">Solicitantes <small>-- Usar 00000000000 par ingreso extranjeros --</small></label>
          <div id="solicitantes-container"></div>
          <button type="button" class="btn btn-secondary mt-2" onclick="agregarSolicitante()">+ Agregar otro solicitante</button>
        </div>
    
         <input type="hidden" name="nroexpediente_usado"> 
         
       
  
<!-- ================== SISTEMA DE COORDENADAS ================== -->
<hr class="my-4" />
<div class="container">
  <h3>Sistema de Coordenadas</h3>
  <div class="mb-3">
    <label for="sistema-coordenadas" class="form-label">Sistema de Referencia</label>
    <select id="sistema-coordenadas" class="form-select" onchange="actualizarPlaceholdersServidumbre()">
      <option value="posgar2007" selected>POSGAR 2007 (EPSG:5344) - Recomendado</option>
      <option value="posgar94">POSGAR 94 (EPSG:22182) - Se convertirá automáticamente a POSGAR 2007 usando parámetros oficiales del IGN</option>
    </select>
    <small class="text-muted">
      Si sus coordenadas están en POSGAR 94, el sistema las convertirá automáticamente a POSGAR 2007 
      usando los parámetros oficiales del IGN (ΔX=-11.340m, ΔY=-6.686m, ΔZ=3.836m).
    </small>
  </div>
</div>

<!-- ================== CARGA MANUAL DE GEOMETRÍAS ================== -->
<hr class="my-4" />
<div class="container">
  <h3>Opción 1: Registro Manual de Coordenadas</h3>
  
  <!-- Agregar Líneas -->
  <div class="card mb-3">
    <div class="card-header bg-danger text-white">
      <h5 class="mb-0">Agregar Línea (Servidumbre Lineal)</h5>
    </div>
    <div class="card-body">
      <div class="row g-2 mb-2">
        <div class="col-md-3">
          <label class="form-label">X (ESTE) <small class="text-danger">Debe comenzar con 2</small></label>
          <input type="number" id="linea_x" class="form-control" step="0.01" placeholder="Ejemplo: 2492370.69">
        </div>
        <div class="col-md-3">
          <label class="form-label">Y (NORTE) <small class="text-danger">Debe comenzar con 6</small></label>
          <input type="number" id="linea_y" class="form-control" step="0.01" placeholder="Ejemplo: 6677723.20">
        </div>
        <div class="col-md-3">
          <label class="form-label">Ancho (m)</label>
          <input type="number" id="linea_ancho" class="form-control" step="0.01" min="0" placeholder="0.00">
        </div>
        <div class="col-md-3">
          <label class="form-label">&nbsp;</label>
          <button type="button" class="btn btn-primary w-100" onclick="agregarPuntoLinea()">+ Agregar Punto</button>
        </div>
      </div>
      <div class="mb-2">
        <button type="button" class="btn btn-secondary btn-sm" onclick="eliminarUltimoPuntoLinea()">🗑️ Eliminar último punto</button>
        <button type="button" class="btn btn-success btn-sm" onclick="finalizarLinea()">✅ Finalizar Línea</button>
      </div>
      <div id="puntos-linea-lista" class="small"></div>
    </div>
  </div>

  <!-- Agregar Polígonos -->
  <div class="card mb-3">
    <div class="card-header bg-success text-white">
      <h5 class="mb-0">Agregar Polígono (Servidumbre de Superficie)</h5>
    </div>
    <div class="card-body">
      <div class="row g-2 mb-2">
        <div class="col-md-4">
          <label class="form-label">X (ESTE) <small class="text-danger">Debe comenzar con 2</small></label>
          <input type="number" id="poligono_x" class="form-control" step="0.01" placeholder="Ejemplo: 2492370.69">
        </div>
        <div class="col-md-4">
          <label class="form-label">Y (NORTE) <small class="text-danger">Debe comenzar con 6</small></label>
          <input type="number" id="poligono_y" class="form-control" step="0.01" placeholder="Ejemplo: 6677723.20">
        </div>
        <div class="col-md-4">
          <label class="form-label">&nbsp;</label>
          <button type="button" class="btn btn-primary w-100" onclick="agregarPuntoPoligono()">+ Agregar Punto</button>
        </div>
      </div>
      <div class="mb-2">
        <button type="button" class="btn btn-secondary btn-sm" onclick="eliminarUltimoPuntoPoligono()">🗑️ Eliminar último punto</button>
        <button type="button" class="btn btn-success btn-sm" onclick="finalizarPoligono()">✅ Finalizar Polígono</button>
      </div>
      <div id="puntos-poligono-lista" class="small"></div>
    </div>
  </div>
</div>

<!-- ================== SUBIR DXF ================== -->
<hr class="my-4" />
<div class="container">
  <h3>Opción 2: Importar archivo tipo .dxf</h3>

  <div class="mb-3">
    <label for="fileInput" class="form-label">Seleccionar DXF:</label>
    <input type="file" id="fileInput" class="form-control" accept=".dxf">
  </div>

  <div id="map" style="height: 400px;" class="mb-3"></div>

  <div id="resumenDXF" class="mb-3"></div>
  
  <!-- Botones de validación de coordenadas -->
  <div class="row g-2 mb-3">
    <div class="col-md-6">
      <button type="button" class="btn btn-info w-100" onclick="analizarPoligonos()">
        🔍 Analizar Polígonos
      </button>
    </div>
    <div class="col-md-6">
      <button type="button" class="btn btn-warning w-100" onclick="corregirPoligonos()">
        🔧 Corregir Secuencia
      </button>
    </div>
  </div>
 
  <form id="formDXF" method="post" action="guardar_poligono6.php">
    <div id="tiposPorEntidad"></div>

    <!-- inputs ocultos con geometrías -->
    <input type="hidden" name="dxf_lineas" id="dxf_lineas">
    <input type="hidden" name="dxf_poligonos" id="dxf_poligonos">

    <br>
      <h3>Verificación de condiciones para ingreso a la base de datos</h3>
  <div class="col-md-6">
  <div class="condicion">
    <div class="etiqueta">La solicitud se encuentra en forma.</div>
    <label class="switch">
      <input type="checkbox" id="cond1" onchange="verificarTodos()">
      <span class="slider"></span>
    </label>
  </div>

  <div class="condicion">
    <div class="etiqueta">Geometría graficada correctamente.</div>
    <label class="switch">
      <input type="checkbox" id="cond2" onchange="verificarTodos()">
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

  <script src="mapa_tipo4.js"></script>
  <script src="expediente.js"></script>
  <script src="solicitante.js"></script>
  <script>
     function prepararEnvio() {
        return true;
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
function mostrarDescripcionObjetoServidumbre() {
  const select = document.getElementById("objeto_servidumbre");
  const descripcion = document.getElementById("objeto_servidumbre_container");
  if (select.value === "otros") {
    descripcion.style.display = "block";
    descripcion.querySelector("input").setAttribute("required", "required");
  } else {
    descripcion.style.display = "none";
    descripcion.querySelector("input").removeAttribute("required");
  }
}

function mostrarDescripcionServidumbre() {
  const select = document.getElementById("tipo_servidumbre");
  const descripcion = document.getElementById("descripcion_servidumbre_container");
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
let capaDXF = L.featureGroup().addTo(map);

let lineasGeoJSON = [];
let poligonosGeoJSON = [];
let contadorEntidades = 1;

// Variables para carga manual
let lineaActual = {
  puntos: [],
  ancho: 0
};
let poligonoActual = {
  puntos: []
};

// Definiciones de proyecciones
proj4.defs("EPSG:22182", "+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 +ellps=WGS84 +units=m +no_defs");
proj4.defs("EPSG:5344", "+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 +ellps=GRS80 +towgs84=0,0,0,0,0,0,0 +units=m +no_defs +type=crs");
proj4.defs("EPSG:922182", "+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 +ellps=WGS84 +towgs84=-11.340,-6.686,3.836,0.000000214569,-0.000000102025,0.000000374988,0.0001211736 +units=m +no_defs");

const crs22182 = new L.Proj.CRS('EPSG:22182',
  proj4.defs('EPSG:22182'),
  {
    origin: [2200000, 0],
    resolutions: [1024, 512, 256, 128, 64, 32, 16, 8, 4, 2, 1],
  }
);

const fromProjection = proj4("EPSG:22182");
const toProjection = proj4("WGS84");

// Función de conversión POSGAR 94 a POSGAR 2007
function convertirPOSGAR94aPOSGAR2007(este94, norte94) {
  // Paso 1: POSGAR 94 (con parámetros IGN) → WGS84
  const [lon, lat] = proj4('EPSG:922182', 'WGS84', [este94, norte94]);
  // Paso 2: WGS84 → POSGAR 2007
  const [este07, norte07] = proj4('WGS84', 'EPSG:5344', [lon, lat]);
  return { este: este07, norte: norte07 };
}

// Actualizar placeholders según sistema seleccionado
function actualizarPlaceholdersServidumbre() {
  const sistema = document.getElementById('sistema-coordenadas').value;
  
  const inputs = [
    { id: 'linea_x', norte: false },
    { id: 'linea_y', norte: true },
    { id: 'poligono_x', norte: false },
    { id: 'poligono_y', norte: true }
  ];
  
  inputs.forEach(input => {
    const elem = document.getElementById(input.id);
    if (sistema === 'posgar2007') {
      elem.placeholder = input.norte ? 'Ejemplo: 6677723.20' : 'Ejemplo: 2492370.69';
    } else {
      elem.placeholder = input.norte ? 'Ejemplo: 6677729.89' : 'Ejemplo: 2492382.03';
    }
  });
}

// Agregar punto a línea
function agregarPuntoLinea() {
  const sistema = document.getElementById('sistema-coordenadas').value;
  let x = parseFloat(document.getElementById('linea_x').value);
  let y = parseFloat(document.getElementById('linea_y').value);
  const ancho = parseFloat(document.getElementById('linea_ancho').value) || 0;

  if (isNaN(x) || isNaN(y)) {
    alert('Debe ingresar coordenadas válidas');
    return;
  }

  // Validación y conversión según sistema
  if (sistema === 'posgar94') {
    if (x < 2000000 || x > 3000000 || y < 6000000 || y > 7000000) {
      alert('Coordenadas fuera de rango para POSGAR 94.\nESTE: debe estar entre 2,000,000 y 3,000,000\nNORTE: debe estar entre 6,000,000 y 7,000,000\n\nEjemplos válidos:\nESTE: 2492382.03\nNORTE: 6677729.89');
      return;
    }
    const convertido = convertirPOSGAR94aPOSGAR2007(x, y);
    alert(`✅ Coordenadas convertidas de POSGAR 94 a POSGAR 2007:\n\nESTE: ${convertido.este.toFixed(2)}\nNORTE: ${convertido.norte.toFixed(2)}`);
    x = convertido.este;
    y = convertido.norte;
  } else {
    if (x < 2000000 || x > 3000000 || y < 6000000 || y > 7000000) {
      alert('Coordenadas fuera de rango.\nESTE: debe estar entre 2,000,000 y 3,000,000\nNORTE: debe estar entre 6,000,000 y 7,000,000\n\nEjemplos válidos:\nESTE: 2492370.69\nNORTE: 6677723.20');
      return;
    }
  }

  lineaActual.puntos.push([x, y]);
  lineaActual.ancho = ancho;

  // Visualizar en mapa
  const [lon, lat] = proj4('EPSG:5344', 'WGS84', [x, y]);
  L.marker([lat, lon], {
    icon: L.divIcon({
      className: 'punto-marker',
      html: `<div style="background: red; color: white; padding: 2px 5px; border-radius: 3px; font-size: 10px;">L${lineaActual.puntos.length}</div>`,
      iconAnchor: [10, 10]
    })
  }).addTo(capaDXF);

  if (lineaActual.puntos.length > 1) {
    const coords = lineaActual.puntos.map(p => {
      const [lon, lat] = proj4('EPSG:5344', 'WGS84', p);
      return [lat, lon];
    });
    L.polyline(coords, { color: 'red', weight: 3 }).addTo(capaDXF);
  }

  map.setView([lat, lon], 13);

  // Actualizar lista
  document.getElementById('puntos-linea-lista').innerHTML = 
    `<strong>Puntos agregados:</strong> ${lineaActual.puntos.length}<br>` +
    `<strong>Ancho:</strong> ${ancho.toFixed(2)} m<br>` +
    lineaActual.puntos.map((p, i) => `Punto ${i+1}: X=${p[0].toFixed(2)}, Y=${p[1].toFixed(2)}`).join('<br>');

  // Limpiar inputs
  document.getElementById('linea_x').value = '';
  document.getElementById('linea_y').value = '';
}

function eliminarUltimoPuntoLinea() {
  if (lineaActual.puntos.length === 0) {
    alert('No hay puntos para eliminar');
    return;
  }
  lineaActual.puntos.pop();
  
  // Redibujar
  capaDXF.clearLayers();
  redibujarTodasGeometrias();
  
  document.getElementById('puntos-linea-lista').innerHTML = 
    lineaActual.puntos.length > 0 ?
    `<strong>Puntos agregados:</strong> ${lineaActual.puntos.length}<br>` +
    lineaActual.puntos.map((p, i) => `Punto ${i+1}: X=${p[0].toFixed(2)}, Y=${p[1].toFixed(2)}`).join('<br>') :
    '';
}

function finalizarLinea() {
  if (lineaActual.puntos.length < 2) {
    alert('Debe agregar al menos 2 puntos para crear una línea');
    return;
  }

  // Agregar a lineasGeoJSON
  lineasGeoJSON.push({
    type: "Feature",
    geometry: { type: "LineString", coordinates: lineaActual.puntos },
    properties: { 
      id: contadorEntidades,
      ancho: lineaActual.ancho,
      origen: 'manual'
    }
  });

  // Agregar formulario de atributos
  agregarFormularioEntidad('Línea', contadorEntidades, lineaActual.ancho);

  contadorEntidades++;
  lineaActual = { puntos: [], ancho: 0 };
  document.getElementById('puntos-linea-lista').innerHTML = '';
  document.getElementById('linea_x').value = '';
  document.getElementById('linea_y').value = '';
  document.getElementById('linea_ancho').value = '';

  actualizarResumen();
  alert('✅ Línea agregada correctamente');
}

// Agregar punto a polígono
function agregarPuntoPoligono() {
  const sistema = document.getElementById('sistema-coordenadas').value;
  let x = parseFloat(document.getElementById('poligono_x').value);
  let y = parseFloat(document.getElementById('poligono_y').value);

  if (isNaN(x) || isNaN(y)) {
    alert('Debe ingresar coordenadas válidas');
    return;
  }

  // Validación y conversión según sistema
  if (sistema === 'posgar94') {
    if (x < 2000000 || x > 3000000 || y < 6000000 || y > 7000000) {
      alert('Coordenadas fuera de rango para POSGAR 94.\nESTE: debe estar entre 2,000,000 y 3,000,000\nNORTE: debe estar entre 6,000,000 y 7,000,000\n\nEjemplos válidos:\nESTE: 2492382.03\nNORTE: 6677729.89');
      return;
    }
    const convertido = convertirPOSGAR94aPOSGAR2007(x, y);
    alert(`✅ Coordenadas convertidas de POSGAR 94 a POSGAR 2007:\n\nESTE: ${convertido.este.toFixed(2)}\nNORTE: ${convertido.norte.toFixed(2)}`);
    x = convertido.este;
    y = convertido.norte;
  } else {
    if (x < 2000000 || x > 3000000 || y < 6000000 || y > 7000000) {
      alert('Coordenadas fuera de rango.\nESTE: debe estar entre 2,000,000 y 3,000,000\nNORTE: debe estar entre 6,000,000 y 7,000,000\n\nEjemplos válidos:\nESTE: 2492370.69\nNORTE: 6677723.20');
      return;
    }
  }

  poligonoActual.puntos.push([x, y]);

  // Visualizar en mapa
  const [lon, lat] = proj4('EPSG:5344', 'WGS84', [x, y]);
  L.marker([lat, lon], {
    icon: L.divIcon({
      className: 'punto-marker',
      html: `<div style="background: green; color: white; padding: 2px 5px; border-radius: 3px; font-size: 10px;">P${poligonoActual.puntos.length}</div>`,
      iconAnchor: [10, 10]
    })
  }).addTo(capaDXF);

  if (poligonoActual.puntos.length > 2) {
    const coords = poligonoActual.puntos.map(p => {
      const [lon, lat] = proj4('EPSG:5344', 'WGS84', p);
      return [lat, lon];
    });
    L.polygon(coords, { color: 'green', fillOpacity: 0.2 }).addTo(capaDXF);
  }

  map.setView([lat, lon], 13);

  // Actualizar lista
  document.getElementById('puntos-poligono-lista').innerHTML = 
    `<strong>Puntos agregados:</strong> ${poligonoActual.puntos.length}<br>` +
    poligonoActual.puntos.map((p, i) => `Punto ${i+1}: X=${p[0].toFixed(2)}, Y=${p[1].toFixed(2)}`).join('<br>');

  // Limpiar inputs
  document.getElementById('poligono_x').value = '';
  document.getElementById('poligono_y').value = '';
}

function eliminarUltimoPuntoPoligono() {
  if (poligonoActual.puntos.length === 0) {
    alert('No hay puntos para eliminar');
    return;
  }
  poligonoActual.puntos.pop();
  
  // Redibujar
  capaDXF.clearLayers();
  redibujarTodasGeometrias();
  
  document.getElementById('puntos-poligono-lista').innerHTML = 
    poligonoActual.puntos.length > 0 ?
    `<strong>Puntos agregados:</strong> ${poligonoActual.puntos.length}<br>` +
    poligonoActual.puntos.map((p, i) => `Punto ${i+1}: X=${p[0].toFixed(2)}, Y=${p[1].toFixed(2)}`).join('<br>') :
    '';
}

function finalizarPoligono() {
  if (poligonoActual.puntos.length < 3) {
    alert('Debe agregar al menos 3 puntos para crear un polígono');
    return;
  }

  // Cerrar polígono
  const puntosCerrados = [...poligonoActual.puntos, poligonoActual.puntos[0]];

  // Agregar a poligonosGeoJSON
  poligonosGeoJSON.push({
    type: "Feature",
    geometry: { type: "Polygon", coordinates: [puntosCerrados] },
    properties: { 
      id: contadorEntidades,
      origen: 'manual'
    }
  });

  // Agregar formulario de atributos
  const area = polygonAreaMeters2(poligonoActual.puntos);
  agregarFormularioEntidad('Polígono', contadorEntidades, null, area);

  contadorEntidades++;
  poligonoActual = { puntos: [] };
  document.getElementById('puntos-poligono-lista').innerHTML = '';
  document.getElementById('poligono_x').value = '';
  document.getElementById('poligono_y').value = '';

  actualizarResumen();
  alert('✅ Polígono agregado correctamente');
}

function redibujarTodasGeometrias() {
  // Redibujar líneas finalizadas
  lineasGeoJSON.forEach(linea => {
    const coords = linea.geometry.coordinates.map(p => {
      const [lon, lat] = proj4('EPSG:5344', 'WGS84', p);
      return [lat, lon];
    });
    L.polyline(coords, { color: 'red', weight: 3 }).addTo(capaDXF);
  });

  // Redibujar polígonos finalizados
  poligonosGeoJSON.forEach(polygon => {
    const coords = polygon.geometry.coordinates[0].map(p => {
      const [lon, lat] = proj4('EPSG:5344', 'WGS84', p);
      return [lat, lon];
    });
    L.polygon(coords, { color: 'green', fillOpacity: 0.2 }).addTo(capaDXF);
  });

  // Redibujar línea en progreso
  if (lineaActual.puntos.length > 0) {
    lineaActual.puntos.forEach((p, i) => {
      const [lon, lat] = proj4('EPSG:5344', 'WGS84', p);
      L.marker([lat, lon], {
        icon: L.divIcon({
          className: 'punto-marker',
          html: `<div style="background: red; color: white; padding: 2px 5px; border-radius: 3px; font-size: 10px;">L${i+1}</div>`,
          iconAnchor: [10, 10]
        })
      }).addTo(capaDXF);
    });
    if (lineaActual.puntos.length > 1) {
      const coords = lineaActual.puntos.map(p => {
        const [lon, lat] = proj4('EPSG:5344', 'WGS84', p);
        return [lat, lon];
      });
      L.polyline(coords, { color: 'red', weight: 3 }).addTo(capaDXF);
    }
  }

  // Redibujar polígono en progreso
  if (poligonoActual.puntos.length > 0) {
    poligonoActual.puntos.forEach((p, i) => {
      const [lon, lat] = proj4('EPSG:5344', 'WGS84', p);
      L.marker([lat, lon], {
        icon: L.divIcon({
          className: 'punto-marker',
          html: `<div style="background: green; color: white; padding: 2px 5px; border-radius: 3px; font-size: 10px;">P${i+1}</div>`,
          iconAnchor: [10, 10]
        })
      }).addTo(capaDXF);
    });
    if (poligonoActual.puntos.length > 2) {
      const coords = poligonoActual.puntos.map(p => {
        const [lon, lat] = proj4('EPSG:5344', 'WGS84', p);
        return [lat, lon];
      });
      L.polygon(coords, { color: 'green', fillOpacity: 0.2 }).addTo(capaDXF);
    }
  }
}

function actualizarResumen() {
  const resumen = document.getElementById("resumenDXF");
  resumen.innerHTML = `<p><strong>${lineasGeoJSON.length}</strong> líneas y <strong>${poligonosGeoJSON.length}</strong> polígonos cargados.</p>`;
  
  // Actualizar campos ocultos
  document.getElementById("dxf_lineas").value = JSON.stringify(lineasGeoJSON);
  document.getElementById("dxf_poligonos").value = JSON.stringify(poligonosGeoJSON);
}

function agregarFormularioEntidad(tipo, id, ancho = null, area = null) {
  const containerTipos = document.getElementById("tiposPorEntidad");
  const div = document.createElement('div');
  div.classList.add('mb-2','border','p-2','rounded','bg-light');

  let contenidoBase = `
    <strong>Entidad #${id}</strong> 
    <span class="badge ${tipo === 'Línea' ? 'bg-danger' : 'bg-success'}">${tipo}</span>
    <br>
    Departamento:
    <select name="departamento_entidad[]" class="form-select" required>
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

    Tipo de Servidumbre:
    <select name="tipo_servidumbre_entidad[]" class="form-select" required>
      <option value="">-- Seleccionar --</option>
      <option value="CAMINO">Camino</option>
      <option value="OCUPACIÓN">Ocupación</option>
      <option value="CONDUCTOS">Conductos</option>
      <option value="OTRO">Otro</option>
    </select>
          
    Objeto de la Servidumbre:
    <select name="objeto_servidumbre_entidad[]" class="form-select" required>
      <option value="">-- Seleccionar --</option>
      <option value="CAMINO">Camino</option>
      <option value="CAMPAMENTO">Campamento</option>
      <option value="DEPOSITO">Deposito</option>
      <option value="POZOS">Pozos</option>
      <option value="CAMPAMENTO">Campamento</option>
      <option value="COMUNICACIÓN">Comunicación</option>
      <option value="ELECTRODUCTO">Electroducto</option>
      <option value="INSTALACIONES ELÉCTRICAS">Instalaciones Eléctricas</option>
      <option value="ALMACÉN">Almacén</option>
      <option value="ACUEDUCTO">Acueducto</option>
      <option value="GASODUCTO">Gasoducto</option>
      <option value="MINERALODUCTO">Mineraloducto</option>
      <option value="SERVICIOS">Servicios</option>
      <option value="PLANTA">Planta de Beneficio</option>
      <option value="ESCOMBRERAS Y COLAS">Escombreras y Colas</option>
      <option value="AERODROMO">Aerodromo</option>
      <option value="OBRA">Obra</option>
      <option value="OTRO">Otro</option>
    </select>
  `;

  if (tipo === 'Línea') {
    const longitud = lineaLongitudMetros(lineasGeoJSON[lineasGeoJSON.length - 1].geometry.coordinates);
    contenidoBase += `
      <br><label>Ancho de la servidumbre (m)</label>
      <input type="number" step="0.01" min="0" 
          name="ancho_servidumbre_entidad[]" 
          class="form-control ancho-servidumbre" 
          value="${ancho || 0}" 
          placeholder="0.00" data-longitud="${longitud}">
      <br><strong>Longitud:</strong> ${longitud.toFixed(2)} m
      <br><strong>Superficie:</strong> <span class="superficie-linea">${((ancho || 0) * longitud / 10000).toFixed(2)}</span> ha
    `;
  }

  if (tipo === 'Polígono') {
    contenidoBase += `
      <br><strong>Superficie:</strong> ${(area/10000).toFixed(3)} ha
      <input type="hidden" name="sup_graf_ha_entidad[]" value="${(area/10000).toFixed(3)}">
    `;
  }

  div.innerHTML = contenidoBase;
  containerTipos.appendChild(div);
}   

function polygonAreaMeters2(coords) {
  let area = 0;
  for (let i = 0, j = coords.length - 1; i < coords.length; j = i++) {
    area += (coords[j][0] + coords[i][0]) * (coords[j][1] - coords[i][1]);
  }
  return Math.abs(area / 2); // en m²
}

document.getElementById("fileInput").addEventListener("change", function(e) {
  const file = e.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function(ev) {
    try {
      const parser = new DxfParser();
      const dxf = parser.parseSync(ev.target.result);

      capaDXF.clearLayers();
      lineasGeoJSON = [];
      poligonosGeoJSON = [];
      contadorEntidades = 1;

      dxf.entities.forEach(ent => {
        let layer = null;
        let coords = [];

        if (ent.type === "LWPOLYLINE" || ent.type === "POLYLINE") {
          coords = ent.vertices.map(v => [v.x, v.y]); // EPSG:22182 para enviar al backend
          
          // Convertir POSGAR 94 (22182) a POSGAR 2007 (5344)
          const coordsConvertidas = coords.map(c => {
            const convertido = convertirPOSGAR94aPOSGAR2007(c[0], c[1]);
            return [convertido.este, convertido.norte];
          });
          
          let coordsLeaflet = coordsConvertidas.map(c => {
            const [lng, lat] = proj4("EPSG:5344", "WGS84", c);
            return [lat, lng];
          });

          if ((ent.shape || ent.closed) && coordsLeaflet.length > 2) {
            const first = coordsLeaflet[0];
            const last = coordsLeaflet[coordsLeaflet.length - 1];
            if (first[0] !== last[0] || first[1] !== last[1]) coordsLeaflet.push([...first]);
            layer = L.polygon(coordsLeaflet, { color: "green", fillOpacity: 0.2 });

            poligonosGeoJSON.push({
              type: "Feature",
              geometry: { type: "Polygon", coordinates: [coordsConvertidas.concat([coordsConvertidas[0]])] },
              properties: { id: contadorEntidades, origen: 'dxf' }
            });
          } else {
            layer = L.polyline(coordsLeaflet, { color: "red" });
            lineasGeoJSON.push({
              type: "Feature",
              geometry: { type: "LineString", coordinates: coordsConvertidas },
              properties: { id: contadorEntidades, origen: 'dxf' }
            });
          }
        }

        if (layer) {
          layer.bindPopup(`Entidad #${contadorEntidades}`);
          capaDXF.addLayer(layer);

          // Agregar label
          const centroide = getCentroide(layer.toGeoJSON().geometry); // [lng, lat]
          if (centroide) {
            L.marker([centroide[1], centroide[0]], {
              icon: L.divIcon({
                className: "entidad-label",
                html: `Entidad #${contadorEntidades} (${layer instanceof L.Polygon ? "Polígono" : "Línea"})`,
                iconAnchor: [0, 6]  
              }),
              zIndexOffset: 1000
            }).addTo(capaDXF);
          }

          contadorEntidades++;
        }
      });

      if (capaDXF.getLayers().length > 0) {
        map.fitBounds(capaDXF.getBounds());
      }

      document.getElementById("dxf_lineas").value = JSON.stringify(lineasGeoJSON);
      document.getElementById("dxf_poligonos").value = JSON.stringify(poligonosGeoJSON);

      // Generar resumen
      actualizarResumen();

      // Crear formularios por entidad
      const containerTipos = document.getElementById("tiposPorEntidad");
      containerTipos.innerHTML = '';
      
      [...lineasGeoJSON, ...poligonosGeoJSON].forEach(f => {
        const tipoGeom = f.geometry.type.includes("Line") ? "Línea" : "Polígono";
        
        if (tipoGeom === "Línea") {
          const longitud = lineaLongitudMetros(f.geometry.coordinates);
          agregarFormularioEntidad(tipoGeom, f.properties.id, 0, null);
        } else {
          const coords = f.geometry.coordinates[0];
          const area = polygonAreaMeters2(coords);
          agregarFormularioEntidad(tipoGeom, f.properties.id, null, area);
        }
      });

      // Recalcular superficie al cambiar ancho
      containerTipos.addEventListener("input", function(e) {
        if (e.target.classList.contains("ancho-servidumbre")) {
          const ancho = parseFloat(e.target.value) || 0;
          const longitud = parseFloat(e.target.dataset.longitud) || 0;
          const superficie = (ancho * longitud) / 10000;
          e.target.closest('.mb-2').querySelector(".superficie-linea").textContent = superficie.toFixed(2);
        }
      });

    } catch (err) {
      alert("Error al procesar el DXF: " + err.message);
    }
  };
  reader.readAsText(file);
});

// Función para obtener centroide de geometría GeoJSON
function getCentroide(geometry) {
  if (geometry.type === "Polygon" || geometry.type === "MultiPolygon") {
    return turf.centroid(geometry).geometry.coordinates; // [lng, lat]
  }
  if (geometry.type === "LineString" || geometry.type === "MultiLineString") {
    const line = turf.lineString(geometry.coordinates);
    const length = turf.length(line);
    const midpoint = turf.along(line, length / 2);
    return midpoint.geometry.coordinates;
  }
  return null;
}

function lineaLongitudMetros(coords) {
  let total = 0;
  for (let i = 1; i < coords.length; i++) {
    const dx = coords[i][0] - coords[i-1][0];
    const dy = coords[i][1] - coords[i-1][1];
    total += Math.sqrt(dx*dx + dy*dy);
  }
  return total; // en metros
}

// Event listener para recalcular superficie cuando cambia el ancho
document.addEventListener('DOMContentLoaded', function() {
  const containerTipos = document.getElementById("tiposPorEntidad");
  if (containerTipos) {
    containerTipos.addEventListener("input", function(e) {
      if (e.target.classList.contains("ancho-servidumbre")) {
        const ancho = parseFloat(e.target.value) || 0;
        const longitud = parseFloat(e.target.dataset.longitud) || 0;
        const superficie = (ancho * longitud) / 10000;
        const superficieSpan = e.target.closest('.mb-2').querySelector(".superficie-linea");
        if (superficieSpan) {
          superficieSpan.textContent = superficie.toFixed(2);
        }
      }
    });
  }
});

// Funciones de validación de secuencia de coordenadas
function calcularAreaConSigno(coords) {
    let area = 0;
    for (let i = 0; i < coords.length; i++) {
        const j = (i + 1) % coords.length;
        area += coords[i][0] * coords[j][1];
        area -= coords[j][0] * coords[i][1];
    }
    return area / 2;
}

function esSecuenciaHoraria(coords) {
    return calcularAreaConSigno(coords) < 0;
}

function encontrarPuntoNoroeste(coords) {
    let maxNorte = -Infinity;
    let minEste = Infinity;
    let indiceNoroeste = 0;
    
    coords.forEach((punto, i) => {
        const [este, norte] = punto;
        if (norte > maxNorte || (norte === maxNorte && este < minEste)) {
            maxNorte = norte;
            minEste = este;
            indiceNoroeste = i;
        }
    });
    
    return indiceNoroeste;
}

function reordenarDesdePuntoNoroeste(coords) {
    const indiceNoroeste = encontrarPuntoNoroeste(coords);
    return [...coords.slice(indiceNoroeste), ...coords.slice(0, indiceNoroeste)];
}

function invertirSecuencia(coords) {
    return [...coords].reverse();
}

function validarSecuenciaHoraria(coords) {
    const coordsReordenadas = reordenarDesdePuntoNoroeste(coords);
    
    if (esSecuenciaHoraria(coordsReordenadas)) {
        return {
            valida: true,
            coordenadas: coordsReordenadas,
            mensaje: "Secuencia correcta: comienza en punto noroeste y sigue sentido horario"
        };
    }
    
    const coordsCorregidas = invertirSecuencia(coordsReordenadas);
    return {
        valida: false,
        coordenadas: coordsCorregidas,
        mensaje: "Secuencia corregida: reordenada desde punto noroeste en sentido horario"
    };
}

function analizarPoligonos() {
    if (poligonosGeoJSON.length === 0) {
        alert('No hay polígonos cargados para analizar');
        return;
    }
    
    let resumen = '📊 ANÁLISIS DE POLÍGONOS CARGADOS:\n\n';
    
    poligonosGeoJSON.forEach((polygon, index) => {
        const coords = polygon.geometry.coordinates[0];
        // Remover último punto si es igual al primero
        const coordsLimpias = coords[coords.length - 1][0] === coords[0][0] && 
                             coords[coords.length - 1][1] === coords[0][1] ? 
                             coords.slice(0, -1) : coords;
        
        const validacion = validarSecuenciaHoraria(coordsLimpias);
        const area = Math.abs(polygonAreaMeters2(coordsLimpias.map(c => [c[0], c[1]])));
        
        resumen += `🔸 Polígono ${index + 1}:\n`;
        resumen += `   Vértices: ${coordsLimpias.length}\n`;
        resumen += `   Área: ${area.toLocaleString('es-AR', {maximumFractionDigits: 2})} m²\n`;
        resumen += `   ${validacion.valida ? '✅' : '⚠️'} ${validacion.mensaje}\n\n`;
    });
    
    alert(resumen);
}

function corregirPoligonos() {
    if (poligonosGeoJSON.length === 0) {
        alert('No hay polígonos cargados para corregir');
        return;
    }
    
    let corregidos = 0;
    
    poligonosGeoJSON.forEach((polygon, index) => {
        const coords = polygon.geometry.coordinates[0];
        const coordsLimpias = coords[coords.length - 1][0] === coords[0][0] && 
                             coords[coords.length - 1][1] === coords[0][1] ? 
                             coords.slice(0, -1) : coords;
        
        const validacion = validarSecuenciaHoraria(coordsLimpias);
        
        if (!validacion.valida) {
            // Cerrar el polígono agregando el primer punto al final
            polygon.geometry.coordinates[0] = [...validacion.coordenadas, validacion.coordenadas[0]];
            corregidos++;
        }
    });
    
    if (corregidos > 0) {
        alert(`✅ Se corrigieron ${corregidos} polígono(s). Las coordenadas ahora siguen la secuencia correcta (noroeste → sentido horario).`);
        
        // Actualizar visualización en el mapa
        capaDXF.clearLayers();
        poligonosGeoJSON.forEach((polygon, index) => {
            const coords = polygon.geometry.coordinates[0];
            const coordsLeaflet = coords.map(c => {
                const [lng, lat] = proj4("EPSG:22182", "WGS84", c);
                return [lat, lng];
            });
            
            const layer = L.polygon(coordsLeaflet, { color: "green", fillOpacity: 0.2 });
            layer.bindPopup(`Polígono #${index + 1} (Corregido)`);
            capaDXF.addLayer(layer);
        });
    } else {
        alert('✅ Todos los polígonos ya tienen la secuencia correcta.');
    }
}

function verificarTodos() {
    const condiciones = [1, 2];
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
      window.location.href = 'observar_expediente.php?expediente=' + encodeURIComponent(expediente.nroexpediente_usado) + '&formulario=' + encodeURIComponent("DENUNCIA SERVIDUMBRE")
    };
  }
}

</script>

</body>
</html>
