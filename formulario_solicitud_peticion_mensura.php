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
      <li><strong>Secuencia:</strong> Continuar en sentido HORARIO (como las manecillas del reloj)</li>
      <li><strong>Importante:</strong> Esta normativa aplica tanto para el perímetro de mensura como para las pertenencias</li>
    </ul>
  </div>

  <!-- ============================================ -->
  <!-- SECCIÓN 1: PERÍMETRO DE MENSURA (UN SOLO POLÍGONO) -->
  <!-- ============================================ -->
  <div class="card mt-4 border-success">
    <div class="card-header bg-success text-white">
      <h5 class="mb-0"><i class="bi bi-geo-alt-fill"></i> PERÍMETRO DE MENSURA</h5>
      <small>Ingrese el polígono que delimita el área total de mensura (UN SOLO POLÍGONO)</small>
    </div>
    <div class="card-body">
      
      <!-- Opción 1: Importar desde CSV -->
      <h6 class="text-success"><i class="bi bi-file-earmark-arrow-up"></i> Opción A: Importar desde archivo CSV</h6>
      <div class="col-md-8 mb-3">
        <div class="input-group">
          <input type="file" id="csvSolicitudMensura" accept=".csv" class="form-control">
          <button type="button" class="btn btn-success" onclick="importarSolicitudMensura()">
            <i class="bi bi-upload"></i> Importar Perímetro
          </button>
        </div>
        <small class="form-text text-muted">
          Formato: <code>id_mensura;id_vertice;este;norte</code><br>
          Ejemplo: <code>1;1;2457558,74;6557062,97</code>
        </small>
      </div>

      <div class="text-center my-3">
        <strong>-- O --</strong>
      </div>

      <!-- Opción 2: Ingreso manual -->
      <h6 class="text-success"><i class="bi bi-pencil-square"></i> Opción B: Ingreso manual de vértices</h6>
      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label fw-bold">X (ESTE)</label>
          <input type="number" id="y_perimetro" class="form-control" step="0.01" placeholder="2XXXXXX.XX">
          <small class="text-muted">Debe comenzar con 2</small>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-bold">Y (NORTE)</label>
          <input type="number" id="x_perimetro" class="form-control" step="0.01" placeholder="6XXXXXX.XX">
          <small class="text-muted">Debe comenzar con 6</small>
        </div>
        <div class="col-md-6">
          <div class="d-flex gap-2">
            <button type="button" onclick="agregarPuntoPerimetro()" class="btn btn-success flex-fill">
              <i class="bi bi-plus-circle"></i> Agregar Punto
            </button>
            <button type="button" onclick="eliminarUltimoPuntoPerimetro()" class="btn btn-danger flex-fill">
              <i class="bi bi-trash"></i> Eliminar Último
            </button>
          </div>
          <div class="d-flex gap-2 mt-2">
            <button type="button" onclick="finalizarPerimetroManual()" class="btn btn-primary flex-fill">
              <i class="bi bi-check-circle-fill"></i> FINALIZAR PERÍMETRO
            </button>
            <button type="button" onclick="limpiarPerimetro()" class="btn btn-secondary flex-fill">
              <i class="bi bi-x-circle"></i> Limpiar
            </button>
          </div>
        </div>
      </div>

      <!-- Tabla de puntos del perímetro -->
      <div class="row mt-3" id="tabla-perimetro-container" style="display: none;">
        <div class="col-12">
          <h6 class="text-success">Vértices del Perímetro:</h6>
          <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered">
              <thead class="table-success">
                <tr>
                  <th style="width: 15%;">Vértice</th>
                  <th style="width: 35%;">X (ESTE)</th>
                  <th style="width: 35%;">Y (NORTE)</th>
                  <th style="width: 15%;">Acciones</th>
                </tr>
              </thead>
              <tbody id="tabla-perimetro-body">
                <!-- Se llenará dinámicamente -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================ -->
  <!-- SECCIÓN 2: PERTENENCIAS (VARIOS POLÍGONOS) -->
  <!-- ============================================ -->
  <div class="card mt-4 border-primary">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0"><i class="bi bi-square-fill"></i> PERTENENCIAS</h5>
      <small>Ingrese los polígonos de las pertenencias dentro del área de mensura (PUEDEN SER VARIOS POLÍGONOS)</small>
    </div>
    <div class="card-body">
      
      <!-- Opción 1: Importar desde CSV -->
      <h6 class="text-primary"><i class="bi bi-file-earmark-arrow-up"></i> Opción A: Importar desde archivo CSV</h6>
      <div class="col-md-8 mb-3">
        <div class="input-group">
          <input type="file" id="csvFile" accept=".csv" class="form-control">
          <button type="button" class="btn btn-primary" onclick="importarDesdeCSV()">
            <i class="bi bi-upload"></i> Importar Pertenencias
          </button>
        </div>
        <small class="form-text text-muted">
          Formato: <code>id_solicitud;id_pertenencia;id_vertice;este;norte</code><br>
          Ejemplo: <code>1;P1;1;2457558,74;6557062,97</code>
        </small>
      </div>

      <div class="text-center my-3">
        <strong>-- O --</strong>
      </div>

      <!-- Opción 2: Ingreso manual -->
      <h6 class="text-primary"><i class="bi bi-pencil-square"></i> Opción B: Ingreso manual de vértices</h6>
      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label fw-bold">X (ESTE)</label>
          <input type="number" id="y_pertenencia" class="form-control" step="0.01" placeholder="2XXXXXX.XX">
          <small class="text-muted">Debe comenzar con 2</small>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-bold">Y (NORTE)</label>
          <input type="number" id="x_pertenencia" class="form-control" step="0.01" placeholder="6XXXXXX.XX">
          <small class="text-muted">Debe comenzar con 6</small>
        </div>
        <div class="col-md-6">
          <div class="d-flex gap-2">
            <button type="button" onclick="agregarPuntoPertenencia()" class="btn btn-primary flex-fill">
              <i class="bi bi-plus-circle"></i> Agregar Punto
            </button>
            <button type="button" onclick="eliminarUltimoPuntoPertenencia()" class="btn btn-danger flex-fill">
              <i class="bi bi-trash"></i> Eliminar Último
            </button>
          </div>
          <div class="d-flex gap-2 mt-2">
            <button type="button" onclick="finalizarPertenenciaManual()" class="btn btn-success flex-fill">
              <i class="bi bi-check-circle-fill"></i> FINALIZAR PERTENENCIA
            </button>
            <button type="button" onclick="limpiarPertenencia()" class="btn btn-secondary flex-fill">
              <i class="bi bi-x-circle"></i> Limpiar
            </button>
          </div>
        </div>
      </div>

      <!-- Tabla de puntos de la pertenencia actual -->
      <div class="row mt-3" id="tabla-pertenencia-container" style="display: none;">
        <div class="col-12">
          <h6 class="text-primary">Vértices de la Pertenencia Actual:</h6>
          <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered">
              <thead class="table-primary">
                <tr>
                  <th style="width: 15%;">Vértice</th>
                  <th style="width: 35%;">X (ESTE)</th>
                  <th style="width: 35%;">Y (NORTE)</th>
                  <th style="width: 15%;">Acciones</th>
                </tr>
              </thead>
              <tbody id="tabla-pertenencia-body">
                <!-- Se llenará dinámicamente -->
              </tbody>
            </table>
          </div>
        </div>
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
      <input type="hidden" name="sistema_coordenadas" id="sistema_coordenadas_hidden">
      
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
  // Para ingreso manual temporal - PERÍMETRO
  let perimetroVertices = [];
  let perimetroLayers = [];
  // Para ingreso manual temporal - PERTENENCIAS
  let pertenenciaVertices = [];
  let pertenenciaLayers = [];

proj4.defs("EPSG:22182", "+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 +ellps=WGS84 +units=m +no_defs");

// POSGAR 94 geodésico con parámetros towgs84 del IGN
proj4.defs("POSGAR94-GEO", "+proj=longlat +ellps=WGS84 +towgs84=-11.340,-6.686,3.836,0.000000214569,-0.000000102025,0.000000374988,0.0001211736 +no_defs");

// POSGAR 2007 geodésico (destino)
proj4.defs("POSGAR07-GEO", "+proj=longlat +ellps=GRS80 +towgs84=0,0,0,0,0,0,0 +no_defs");

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
      // proj4 espera [ESTE, NORTE] = [Y, X] porque p.x=NORTE, p.y=ESTE
      const [lon,lat]=proj4(fromProjection,toProjection,[p.y,p.x]);
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
      const [lon,lat] = proj4(fromProjection,toProjection,[p.y,p.x]);
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
      const [lon,lat]=proj4(fromProjection,toProjection,[p.y,p.x]); 
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
      const [lon,lat] = proj4(fromProjection,toProjection,[p.y,p.x]);
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

// ============================================
// FUNCIONES PARA PERÍMETRO DE MENSURA
// ============================================
function agregarPuntoPerimetro(){
  const ix = document.getElementById('x_perimetro');
  const iy = document.getElementById('y_perimetro');
  const x = parseFloat(ix.value);
  const y = parseFloat(iy.value);
  
  // Validaciones
  if (isNaN(x) || isNaN(y)){
    alert('Por favor ingresa valores numéricos válidos para X e Y');
    return;
  }
  
  // Validar que X (ESTE) comience con 2
  // Nota: y_perimetro contiene ESTE, x_perimetro contiene NORTE
  if (y < 2000000 || y >= 3000000){
    alert('⚠️ ERROR: La coordenada X (ESTE) debe comenzar con 2\nEjemplo: 2492370.69');
    iy.focus();
    return;
  }
  
  // Validar que Y (NORTE) comience con 6
  if (x < 6000000 || x >= 7000000){
    alert('⚠️ ERROR: La coordenada Y (NORTE) debe comenzar con 6\nEjemplo: 6677723.20');
    ix.focus();
    return;
  }
  
  // Validar que solo haya UN perímetro
  if (solicitudesMensura.length > 0){
    alert('Ya existe un perímetro de mensura. Si desea modificarlo, primero elimínelo de la lista.');
    return;
  }
  
  perimetroVertices.push({ id_v: perimetroVertices.length + 1, x, y });
  actualizarTablaPerimetro();
  dibujarTemporalPerimetro();
  ix.value = '';
  iy.value = '';
}

function eliminarUltimoPuntoPerimetro(){
  if (perimetroVertices.length === 0){
    alert('No hay puntos del perímetro para eliminar');
    return;
  }
  perimetroVertices.pop();
  actualizarTablaPerimetro();
  dibujarTemporalPerimetro();
}

function eliminarPuntoPerimetroPorIndice(indice){
  if (confirm(`¿Está seguro de eliminar el vértice ${indice + 1}?`)){
    perimetroVertices.splice(indice, 1);
    // Renumerar vértices
    perimetroVertices.forEach((v, i) => v.id_v = i + 1);
    actualizarTablaPerimetro();
    dibujarTemporalPerimetro();
  }
}

function hacerZoomPerimetro(indice){
  if (indice >= 0 && indice < perimetroVertices.length){
    const punto = perimetroVertices[indice];
    // Convertir coordenadas de POSGAR 2007 a WGS84 para el mapa
    const [lon, lat] = proj4(fromProjection, toProjection, [punto.y, punto.x]);
    // Hacer zoom al punto con nivel 17 (bastante cerca)
    map.setView([lat, lon], 17);
  }
}

function actualizarTablaPerimetro(){
  const container = document.getElementById('tabla-perimetro-container');
  const tbody = document.getElementById('tabla-perimetro-body');
  
  if (perimetroVertices.length === 0){
    container.style.display = 'none';
    tbody.innerHTML = '';
    return;
  }
  
  container.style.display = 'block';
  tbody.innerHTML = '';
  
  perimetroVertices.forEach((punto, index) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="text-center"><strong>V${punto.id_v}</strong></td>
      <td>${punto.y.toFixed(2)}</td>
      <td>${punto.x.toFixed(2)}</td>
      <td class="text-center">
        <button type="button" class="btn btn-primary btn-sm me-1" onclick="hacerZoomPerimetro(${index})" title="Hacer zoom al punto">
          🔍
        </button>
        <button type="button" class="btn btn-danger btn-sm" onclick="eliminarPuntoPerimetroPorIndice(${index})" title="Eliminar punto">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function limpiarPerimetro(){
  perimetroVertices = [];
  perimetroLayers.forEach(l=>map.removeLayer(l));
  perimetroLayers = [];
  actualizarTablaPerimetro();
}

function dibujarTemporalPerimetro(){
  // eliminar capas previas
  perimetroLayers.forEach(l=>map.removeLayer(l));
  perimetroLayers = [];
  if (perimetroVertices.length === 0) return;

  const coords = perimetroVertices.map(p=>{ const [lon,lat]=proj4(fromProjection,toProjection,[p.y,p.x]); return [lat,lon]; });

  perimetroVertices.forEach(p=>{
    const [lon,lat] = proj4(fromProjection,toProjection,[p.y,p.x]);
    const m = L.marker([lat,lon],{
      icon: L.divIcon({ className: 'vertice-label', html: p.id_v })
    }).addTo(map);
    perimetroLayers.push(m);
  });

  if (perimetroVertices.length > 2){
    const layer = L.polygon(coords, { color:'green', weight:4, fill:false }).addTo(map);
    perimetroLayers.push(layer);
  }
}

function finalizarPerimetroManual(){
  if (perimetroVertices.length < 3){
    alert('Un polígono necesita al menos 3 vértices');
    return;
  }
  
  // Validar que no exista ya un perímetro
  if (solicitudesMensura.length > 0){
    alert('Ya existe un perímetro de mensura. Si desea reemplazarlo, primero elimínelo de la lista.');
    return;
  }
  
  // generar id único
  const pol = { id_mensura: 1, vertices: perimetroVertices.slice(), sup_decl: 0, sup_graf_ha: 0 };
  solicitudesMensura.push(pol);
  document.getElementById('solicitudes_mensura').value = JSON.stringify(solicitudesMensura);
  limpiarPerimetro();
  dibujarSolicitudes();
  alert('✅ Perímetro de mensura finalizado correctamente');
  
  // Preguntar si solo hay una pertenencia
  preguntarPertenenciaUnica();
}

/**
 * Pregunta al usuario si la mensura tiene una sola pertenencia
 * Si responde afirmativamente, crea automáticamente la pertenencia con las coordenadas del perímetro
 */
function preguntarPertenenciaUnica() {
  const respuesta = confirm('¿Esta mensura tiene una sola pertenencia?\n\nSi responde SÍ, se creará automáticamente una pertenencia con las mismas coordenadas del perímetro.');
  
  if (respuesta) {
    crearPertenenciaAutomatica();
  }
}

/**
 * Crea automáticamente una pertenencia con las coordenadas del perímetro
 */
function crearPertenenciaAutomatica() {
  if (solicitudesMensura.length === 0) {
    alert('⚠️ ERROR: No existe un perímetro de mensura.');
    return;
  }
  
  // Verificar que no existan pertenencias ya creadas
  if (multipoligonos.length > 0) {
    const confirmar = confirm('⚠️ ADVERTENCIA: Ya existen pertenencias creadas.\n\n¿Desea crear una nueva pertenencia con las coordenadas del perímetro de todas formas?');
    if (!confirmar) return;
  }
  
  const perimetro = solicitudesMensura[0];
  
  // Solicitar ID de solicitud
  const id_sol = prompt('Ingrese ID de Solicitud (número):', '1');
  if (!id_sol) {
    alert('⚠️ Operación cancelada: No se ingresó ID de Solicitud.');
    return;
  }
  
  // Solicitar ID de pertenencia
  const id_pert = prompt('Ingrese ID de Pertenencia (ej: P1, P2, etc.):', 'P1');
  if (!id_pert) {
    alert('⚠️ Operación cancelada: No se ingresó ID de Pertenencia.');
    return;
  }
  
  // Crear objeto de pertenencia con las mismas coordenadas del perímetro
  const pertenencia = {
    id_sol: id_sol,
    id_p: id_pert,
    vertices: perimetro.vertices.slice(), // Copiar los vértices del perímetro
    sup_decl: 0,
    sup_graf_ha: 0
  };
  
  multipoligonos.push(pertenencia);
  document.getElementById('multipoligonos').value = JSON.stringify(multipoligonos);
  
  dibujarMultipoligonos();
  actualizarLista();
  
  alert('✅ Pertenencia ' + id_pert + ' creada automáticamente con las coordenadas del perímetro');
}

// ============================================
// FUNCIONES PARA PERTENENCIAS
// ============================================
function agregarPuntoPertenencia(){
  const ix = document.getElementById('x_pertenencia');
  const iy = document.getElementById('y_pertenencia');
  const x = parseFloat(ix.value);
  const y = parseFloat(iy.value);
  
  // Validaciones
  if (isNaN(x) || isNaN(y)){
    alert('Por favor ingresa valores numéricos válidos para X e Y');
    return;
  }
  
  // Validar que X (ESTE) comience con 2
  // Nota: y_pertenencia contiene ESTE, x_pertenencia contiene NORTE
  if (y < 2000000 || y >= 3000000){
    alert('⚠️ ERROR: La coordenada X (ESTE) debe comenzar con 2\nEjemplo: 2492370.69');
    iy.focus();
    return;
  }
  
  // Validar que Y (NORTE) comience con 6
  if (x < 6000000 || x >= 7000000){
    alert('⚠️ ERROR: La coordenada Y (NORTE) debe comenzar con 6\nEjemplo: 6677723.20');
    ix.focus();
    return;
  }
  
  pertenenciaVertices.push({ id_v: pertenenciaVertices.length + 1, x, y });
  actualizarTablaPertenencia();
  dibujarTemporalPertenencia();
  ix.value = '';
  iy.value = '';
}

function eliminarUltimoPuntoPertenencia(){
  if (pertenenciaVertices.length === 0){
    alert('No hay puntos de la pertenencia para eliminar');
    return;
  }
  pertenenciaVertices.pop();
  actualizarTablaPertenencia();
  dibujarTemporalPertenencia();
}

function eliminarPuntoPertenenciaPorIndice(indice){
  if (confirm(`¿Está seguro de eliminar el vértice ${indice + 1}?`)){
    pertenenciaVertices.splice(indice, 1);
    // Renumerar vértices
    pertenenciaVertices.forEach((v, i) => v.id_v = i + 1);
    actualizarTablaPertenencia();
    dibujarTemporalPertenencia();
  }
}

function hacerZoomPertenencia(indice){
  if (indice >= 0 && indice < pertenenciaVertices.length){
    const punto = pertenenciaVertices[indice];
    // Convertir coordenadas de POSGAR 2007 a WGS84 para el mapa
    const [lon, lat] = proj4(fromProjection, toProjection, [punto.y, punto.x]);
    // Hacer zoom al punto con nivel 17 (bastante cerca)
    map.setView([lat, lon], 17);
  }
}

function actualizarTablaPertenencia(){
  const container = document.getElementById('tabla-pertenencia-container');
  const tbody = document.getElementById('tabla-pertenencia-body');
  
  if (pertenenciaVertices.length === 0){
    container.style.display = 'none';
    tbody.innerHTML = '';
    return;
  }
  
  container.style.display = 'block';
  tbody.innerHTML = '';
  
  pertenenciaVertices.forEach((punto, index) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="text-center"><strong>V${punto.id_v}</strong></td>
      <td>${punto.y.toFixed(2)}</td>
      <td>${punto.x.toFixed(2)}</td>
      <td class="text-center">
        <button type="button" class="btn btn-primary btn-sm me-1" onclick="hacerZoomPertenencia(${index})" title="Hacer zoom al punto">
          🔍
        </button>
        <button type="button" class="btn btn-danger btn-sm" onclick="eliminarPuntoPertenenciaPorIndice(${index})" title="Eliminar punto">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function limpiarPertenencia(){
  pertenenciaVertices = [];
  pertenenciaLayers.forEach(l=>map.removeLayer(l));
  pertenenciaLayers = [];
  actualizarTablaPertenencia();
}

function dibujarTemporalPertenencia(){
  // eliminar capas previas
  pertenenciaLayers.forEach(l=>map.removeLayer(l));
  pertenenciaLayers = [];
  if (pertenenciaVertices.length === 0) return;

  const coords = pertenenciaVertices.map(p=>{ const [lon,lat]=proj4(fromProjection,toProjection,[p.y,p.x]); return [lat,lon]; });

  pertenenciaVertices.forEach(p=>{
    const [lon,lat] = proj4(fromProjection,toProjection,[p.y,p.x]);
    const m = L.marker([lat,lon],{
      icon: L.divIcon({ className: 'vertice-label', html: p.id_v })
    }).addTo(map);
    pertenenciaLayers.push(m);
  });

  if (pertenenciaVertices.length > 2){
    const layer = L.polygon(coords, { color:'blue', weight:4, fill:false, dashArray: '4,6' }).addTo(map);
    pertenenciaLayers.push(layer);
  }
}

// ============================================
// FUNCIONES DE VALIDACIÓN GEOMÉTRICA
// ============================================

/**
 * Verifica si un punto está dentro de un polígono usando el algoritmo Ray Casting
 * @param {Object} punto - {x, y}
 * @param {Array} poligono - Array de {x, y}
 * @return {boolean} true si el punto está dentro o en el borde del polígono
 */
function puntoEnPoligono(punto, poligono) {
  let x = punto.x, y = punto.y;
  let dentro = false;
  
  for (let i = 0, j = poligono.length - 1; i < poligono.length; j = i++) {
    let xi = poligono[i].x, yi = poligono[i].y;
    let xj = poligono[j].x, yj = poligono[j].y;
    
    // Verificar si el punto está en el borde del polígono (tolerancia de 0.01 metros)
    if (puntoEnSegmento(punto, poligono[i], poligono[j], 0.01)) {
      return true;
    }
    
    let intersecta = ((yi > y) != (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
    if (intersecta) dentro = !dentro;
  }
  
  return dentro;
}

/**
 * Verifica si un punto está en un segmento de línea
 */
function puntoEnSegmento(punto, p1, p2, tolerancia) {
  let d1 = distancia(punto, p1);
  let d2 = distancia(punto, p2);
  let lineaLen = distancia(p1, p2);
  
  return Math.abs(d1 + d2 - lineaLen) < tolerancia;
}

/**
 * Calcula la distancia euclidiana entre dos puntos
 */
function distancia(p1, p2) {
  return Math.sqrt(Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2));
}

/**
 * Verifica si todos los vértices de un polígono están dentro de otro polígono
 * @param {Array} poligonoInterior - Array de {x, y}
 * @param {Array} poligonoExterior - Array de {x, y}
 * @return {Object} {valido: boolean, mensaje: string}
 */
function poligonoEnPoligono(poligonoInterior, poligonoExterior) {
  // Caso especial: si los polígonos son idénticos (mensura con una sola pertenencia)
  if (poligonosIguales(poligonoInterior, poligonoExterior)) {
    return {
      valido: true,
      mensaje: 'Perímetro y pertenencia coinciden (mensura de una sola pertenencia)'
    };
  }
  
  // Verificar que todos los vértices del polígono interior estén dentro del exterior
  for (let i = 0; i < poligonoInterior.length; i++) {
    if (!puntoEnPoligono(poligonoInterior[i], poligonoExterior)) {
      return {
        valido: false,
        mensaje: `El vértice ${i + 1} de la pertenencia (X: ${poligonoInterior[i].x.toFixed(2)}, Y: ${poligonoInterior[i].y.toFixed(2)}) está FUERA del perímetro de mensura`
      };
    }
  }
  
  return {
    valido: true,
    mensaje: 'Todos los vértices de la pertenencia están dentro del perímetro'
  };
}

/**
 * Verifica si dos polígonos son idénticos (mismos vértices, puede variar el orden)
 */
function poligonosIguales(poli1, poli2) {
  if (poli1.length !== poli2.length) return false;
  
  const tolerancia = 0.01; // 1 cm de tolerancia
  
  // Verificar si cada punto de poli1 existe en poli2
  for (let p1 of poli1) {
    let encontrado = false;
    for (let p2 of poli2) {
      if (Math.abs(p1.x - p2.x) < tolerancia && Math.abs(p1.y - p2.y) < tolerancia) {
        encontrado = true;
        break;
      }
    }
    if (!encontrado) return false;
  }
  
  return true;
}

function finalizarPertenenciaManual(){
  if (pertenenciaVertices.length < 3){
    alert('Una pertenencia necesita al menos 3 vértices');
    return;
  }
  
  // VALIDACIÓN: Verificar que existe un perímetro de mensura
  if (solicitudesMensura.length === 0) {
    alert('⚠️ ERROR: Debe ingresar primero el PERÍMETRO DE MENSURA antes de agregar pertenencias.');
    return;
  }
  
  // VALIDACIÓN: Verificar que la pertenencia está dentro del perímetro
  const perimetro = solicitudesMensura[0].vertices;
  const validacion = poligonoEnPoligono(pertenenciaVertices, perimetro);
  
  if (!validacion.valido) {
    alert('❌ VALIDACIÓN FALLIDA\n\n' + validacion.mensaje + '\n\nLas pertenencias deben estar completamente dentro del perímetro de mensura.');
    return;
  }
  
  console.log('✅ Validación geométrica:', validacion.mensaje);
  
  // Solicitar ID de solicitud y ID de pertenencia
  const id_sol = prompt('Ingrese ID de Solicitud (número):', '1');
  if (!id_sol) return;
  
  const id_pert = prompt('Ingrese ID de Pertenencia (ej: P1, P2, etc.):', 'P' + (multipoligonos.length + 1));
  if (!id_pert) return;
  
  // Crear objeto de pertenencia
  const pertenencia = {
    id_sol: id_sol,
    id_p: id_pert,
    vertices: pertenenciaVertices.slice(),
    sup_decl: 0,
    sup_graf_ha: 0
  };
  
  multipoligonos.push(pertenencia);
  document.getElementById('multipoligonos').value = JSON.stringify(multipoligonos);
  
  limpiarPertenencia();
  dibujarMultipoligonos();
  actualizarLista();
  
  alert('✅ Pertenencia ' + id_pert + ' agregada correctamente');
}

function ajustarVista(){
  const allLayers = [...layersPoligonos,...layersSolicitudes];
  if(allLayers.length>0) map.fitBounds(L.featureGroup(allLayers).getBounds());
}


function actualizarLista(){
  const lista = document.getElementById("listaPuntos");
  lista.innerHTML="";

  solicitudesMensura.forEach((pol,idx)=>{
    // Para visualización: convertir a WGS84
    const coords = pol.vertices.map(p=>{ const [lon,lat]=proj4(fromProjection,toProjection,[p.y,p.x]); return [lon,lat]; });
    if(coords.length>2) coords.push(coords[0]);

    // Calcular área con coordenadas proyectadas en metros
    const areaM2 = calcularAreaProyectada(pol.vertices);
    pol.sup_graf_ha = areaM2 / 10000; // Convertir m² a hectáreas
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
    // Para visualización: convertir a WGS84
    const coords = pol.vertices.map(p=>{ 
      const [lon,lat]=proj4(fromProjection,toProjection,[p.y,p.x]); 
      return [lon,lat]; 
    });
    if(coords.length>2) coords.push(coords[0]);

    // Calcular área con coordenadas proyectadas en metros
    const areaM2 = calcularAreaProyectada(pol.vertices);
    pol.sup_graf_ha = areaM2 / 10000; // Convertir m² a hectáreas
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
    let errores = [];
    
    lines.forEach((line, lineNum) => {
      const parts = line.split(";");
      if(parts.length < 5) return; // formato: id_poligono; id_pertenencia; id_vertice; este; norte
      
      const id_sol = parts[0].trim(); 
      const id_pert = parts[1].trim();
      const id_v = parts[2].trim();
      
      // Formato CSV: columna 3=ESTE, columna 4=NORTE
      // Nuestro código: p.x=NORTE (6M), p.y=ESTE (2M)
      const este = parseFloat(parts[3].replace(",","."));
      const norte = parseFloat(parts[4].replace(",","."));
      
      if(isNaN(este)||isNaN(norte)) return;
      
      // Validar rangos POSGAR 2007 San Juan
      if (este < 2000000 || este >= 3000000) {
        errores.push(`Línea ${lineNum+1}: X (ESTE)=${este} fuera de rango (debe comenzar con 2)`);
        return;
      }
      if (norte < 6000000 || norte >= 7000000) {
        errores.push(`Línea ${lineNum+1}: Y (NORTE)=${norte} fuera de rango (debe comenzar con 6)`);
        return;
      }
      
      const x = norte; // x = NORTE
      const y = este;  // y = ESTE

      if(!tempPol[id_pert]) tempPol[id_pert] = { id_p: id_pert, id_sol: id_sol, vertices: [] };
      tempPol[id_pert].vertices.push({id_v, x, y});
    });

    if(errores.length > 0) {
      alert("⚠️ ERRORES EN CSV:\n\n" + errores.join("\n") + "\n\nLos puntos con errores fueron omitidos.");
    }

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
    let errores = [];
    
    lines.forEach((line, lineNum)=>{
      const parts=line.split(";");
      if(parts.length<4) return; // formato: id_mensura;id_vertice;este;norte
      
      const id_mensura=parts[0].trim();
      const id_v=parts[1].trim();
      
      // Formato CSV: columna 2=ESTE, columna 3=NORTE
      // Nuestro código: p.x=NORTE (6M), p.y=ESTE (2M)
      const este=parseFloat(parts[2].replace(",","."));
      const norte=parseFloat(parts[3].replace(",",".")); 
      
      if(isNaN(este)||isNaN(norte)) return;

      // Validar rangos POSGAR 2007 San Juan
      if (este < 2000000 || este >= 3000000) {
        errores.push(`Línea ${lineNum+1}: X (ESTE)=${este} fuera de rango (debe comenzar con 2)`);
        return;
      }
      if (norte < 6000000 || norte >= 7000000) {
        errores.push(`Línea ${lineNum+1}: Y (NORTE)=${norte} fuera de rango (debe comenzar con 6)`);
        return;
      }
      
      const x = norte; // x = NORTE
      const y = este;  // y = ESTE

      if(!tempPol[id_mensura]) tempPol[id_mensura] = { id_mensura, vertices: [], sup_decl: 0, sup_graf_ha: 0 };
      tempPol[id_mensura].vertices.push({id_v, x, y});
    });

    if(errores.length > 0) {
      alert("⚠️ ERRORES EN CSV:\n\n" + errores.join("\n") + "\n\nLos puntos con errores fueron omitidos.");
    }

    solicitudesMensura = Object.values(tempPol);
    
    // Validar secuencia de cada polígono importado
    solicitudesMensura.forEach(pol => {
      const resultado = validarSecuenciaPoligono(pol.vertices, `Perímetro ${pol.id_mensura}`);
      if (resultado.errores.length > 0) {
        alert(`⚠️ ADVERTENCIAS para ${resultado.nombre}:\n\n` + resultado.errores.join('\n'));
      }
    });
    
    document.getElementById("solicitudes_mensura").value = JSON.stringify(solicitudesMensura);
    dibujarSolicitudes();
    
    // Después de importar el perímetro, preguntar si es de una sola pertenencia
    if (solicitudesMensura.length > 0) {
      preguntarPertenenciaUnica();
    }
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

  // VALIDACIÓN: Superficie de pertenencias no puede exceder el perímetro
  if (!validarSuperficies()) {
    return false;
  }

  // Validar secuencia horaria de polígonos
  if (!validarSecuenciaPoligonos()) {
    return false;
  }

  // Capturar el sistema de coordenadas seleccionado
  const sistemaCoordenadas = document.getElementById("sistema-coordenadas").value;
  document.getElementById("sistema_coordenadas_hidden").value = sistemaCoordenadas;

  document.getElementById("solicitudes_mensura").value = JSON.stringify(solicitudesMensura);
  document.getElementById("multipoligonos").value = JSON.stringify(multipoligonos);
  return true;
}

// Validar que la suma de superficies de pertenencias no exceda el perímetro
function validarSuperficies() {
  // Si no hay perímetro o no hay pertenencias, no validar
  if (solicitudesMensura.length === 0 || multipoligonos.length === 0) {
    return true;
  }
  
  // Obtener superficie del perímetro (calculada)
  const superficiePerimetro = solicitudesMensura[0].sup_graf_ha || 0;
  
  // Calcular suma de superficies de pertenencias (calculadas)
  let sumaSuperificiesPertenencias = 0;
  multipoligonos.forEach(pert => {
    sumaSuperificiesPertenencias += (pert.sup_graf_ha || 0);
  });
  
  // Validar con tolerancia del 1% (errores de redondeo/cálculo)
  const tolerancia = superficiePerimetro * 0.01;
  
  if (sumaSuperificiesPertenencias > (superficiePerimetro + tolerancia)) {
    const mensaje = `❌ ERROR DE VALIDACIÓN DE SUPERFICIES\n\n` +
                   `La suma de las superficies de las PERTENENCIAS (${sumaSuperificiesPertenencias.toFixed(4)} ha) ` +
                   `EXCEDE la superficie del PERÍMETRO DE MENSURA (${superficiePerimetro.toFixed(4)} ha).\n\n` +
                   `Diferencia: ${(sumaSuperificiesPertenencias - superficiePerimetro).toFixed(4)} ha\n\n` +
                   `Esto es técnicamente imposible ya que las pertenencias deben estar contenidas ` +
                   `dentro del perímetro de mensura.\n\n` +
                   `Posibles causas:\n` +
                   `1. Error en las coordenadas del perímetro\n` +
                   `2. Error en las coordenadas de alguna pertenencia\n` +
                   `3. Pertenencias solapadas (duplicación de área)\n\n` +
                   `Por favor revise las coordenadas antes de continuar.`;
    
    alert(mensaje);
    return false;
  }
  
  // Advertencia si la suma está muy por debajo (más del 50% vacío)
  const porcentajeUtilizado = (sumaSuperificiesPertenencias / superficiePerimetro) * 100;
  if (porcentajeUtilizado < 50 && multipoligonos.length > 0) {
    const continuar = confirm(
      `⚠️ ADVERTENCIA DE SUPERFICIE\n\n` +
      `Superficie perímetro: ${superficiePerimetro.toFixed(2)} ha\n` +
      `Suma pertenencias: ${sumaSuperificiesPertenencias.toFixed(2)} ha\n` +
      `Porcentaje utilizado: ${porcentajeUtilizado.toFixed(1)}%\n\n` +
      `Hay un ${(100 - porcentajeUtilizado).toFixed(1)}% del área sin asignar a pertenencias.\n\n` +
      `¿Es esto correcto? ¿Desea continuar?`
    );
    return continuar;
  }
  
  console.log(`✅ Validación de superficies OK: ${sumaSuperificiesPertenencias.toFixed(2)} ha / ${superficiePerimetro.toFixed(2)} ha (${porcentajeUtilizado.toFixed(1)}%)`);
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
                   `3. Los vértices deben seguir orden horario\n` +
                   `4. Vuelva a importar los archivos`;
    
    if (confirm(mensaje + `\n\n¿Desea continuar de todos modos? (No recomendado)`)) {
      return true;
    }
    return false;
  }
  
  return true;
}

// Calcular área de un polígono en metros cuadrados usando fórmula de Shoelace
// vertices: array de objetos {x: NORTE, y: ESTE}
// Retorna: área en metros cuadrados (valor absoluto)
function calcularAreaProyectada(vertices) {
  if (vertices.length < 3) return 0;
  
  // Fórmula Shoelace: area = |Σ(ESTE[j] - ESTE[i]) * (NORTE[j] + NORTE[i])| / 2
  let area = 0;
  const n = vertices.length;
  for (let i = 0; i < n; i++) {
    const j = (i + 1) % n;
    area += (vertices[j].y - vertices[i].y) * (vertices[j].x + vertices[i].x);
  }
  
  return Math.abs(area / 2); // Retornar valor absoluto en m²
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
  // Recordar: p.x = NORTE (6M), p.y = ESTE (2M)
  // Fórmula Shoelace: area = Σ(ESTE[j] - ESTE[i]) * (NORTE[j] + NORTE[i]) / 2
  let area = 0;
  const n = vertices.length;
  for (let i = 0; i < n; i++) {
    const j = (i + 1) % n;
    area += (vertices[j].y - vertices[i].y) * (vertices[j].x + vertices[i].x);
  }
  area = area / 2;
  
  const errores = [];
  
  if (puntoNoroeste !== 0) {
    errores.push(`❌ El primer vértice NO es el noroeste (debería ser el vértice ${puntoNoroeste + 1})`);
  }
  
  if (area > 0) {
    errores.push(`❌ Los vértices están en sentido ANTIHORARIO (deben ir en sentido horario)`);
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

