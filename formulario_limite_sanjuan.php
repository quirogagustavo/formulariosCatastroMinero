<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>VERIFICAR LIMITE SAN JUAN</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
  <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.8.0/proj4.js"></script>
  <script src="https://unpkg.com/proj4leaflet"></script>
  <script src="https://unpkg.com/leaflet-providers"></script>
  <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>

  <link href="style.css?v=<?=time()?>" rel="stylesheet" type="text/css" /> 
  
</head>
<body class="bg-light">

  <div class="container py-4 bg-white shadow rounded-3" style="max-width: 1200px;">
    <h1 class="mb-3">VERIFICAR LIMITE SAN JUAN</h1>
    <h4 class="mb-4 text-muted">FORMULARIO DE INGRESO A BASE DE DATOS GEOGRÁFICA</h4>

    <form method="post" id="formulario" onsubmit="return prepararEnvio()">
    
    <hr class="my-4" />
    <fieldset>
    <legend class="h5">Ingresar Coordenadas Gauss Krüger Faja 2 POSGAR 2007 (EPSG:5344)</legend>
    <div class="row g-3 align-items-end">
    <div class="col-md-4">
      <label class="form-label">ESTE</label>
      <input type="number" id="x" class="form-control" required step="0.01" min="0" placeholder="0.00">
    </div>
    <div class="col-md-4">
      <label class="form-label">NORTE</label>
      <input type="number" id="y" class="form-control" required step="0.01" min="0" placeholder="0.00">
    </div>
    <div class="col-md-4">
      <div class="d-flex gap-2">
        <button type="button" onclick="agregarPunto()" class="btn btn-orange flex-fill">Agregar Punto</button>
        <button type="button" onclick="eliminarUltimoPunto(event)" class="btn btn-danger flex-fill">Eliminar Último</button>
      </div>
    </div>
    </div>
    </fieldset>


      <input type="hidden" name="puntos" id="puntos">
      
      <!-- Botones de validación de coordenadas -->
      <div class="row g-2 mt-3 mb-3">
        <div class="col-md-6">
          <button type="button" class="btn btn-info w-100" onclick="validarSecuenciaPuntos()">
            🔍 Validar Secuencia
          </button>
        </div>
        <div class="col-md-6">
          <button type="button" class="btn btn-warning w-100" onclick="corregirSecuenciaPuntos()">
            🔧 Corregir Orden
          </button>
        </div>
      </div>
      
      <ul class="mt-3" id="listaPuntos"></ul>
      <div id="map"></div>

      

    </form>
  </div>

<script>
  const map = L.map('map', {
  center: [-31.5, -68.5], // San Juan, Argentina
  zoom: 7,
  crs: L.CRS.EPSG3857
});

const googleRoadmap = L.tileLayer('http://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
  maxZoom: 20,
  attribution: '© Google'
});

const googleSatellite = L.tileLayer('http://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
  maxZoom: 20,
  attribution: '© Google'
});

const googleTerrain = L.tileLayer('http://mt1.google.com/vt/lyrs=p&x={x}&y={y}&z={z}', {
  maxZoom: 20,
  attribution: '© Google'
});

const googleHybrid = L.tileLayer('http://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
  maxZoom: 20,
  attribution: '© Google'
});

const baseUNIDE = L.tileLayer.wms("https://unide.sanjuan.gob.ar/geoserver/wms", {
  layers: "GeoserviciosUnide",
  format: "image/png",
  transparent: false,
  version: "1.1.1",
  attribution: "Mapa base: UNIDE San Juan"
});

const baseMaps = {
  "UNIDE San Juan": baseUNIDE,
  "Google Carreteras": googleRoadmap,
  "Google Satélite": googleSatellite,
  "Google Terreno": googleTerrain,
  "Google Híbrido": googleHybrid 
};


baseUNIDE.addTo(map);

let capaLimites;
const overlayMaps = {};

//MAPA LIMITE SAN JUAN
fetch("consulta_gra_limite_sanjuan.php")
  .then(res => res.json())
  .then(data => {
    capaLimites = L.geoJSON(data, {
      onEachFeature: (feature, layer) => {
        layer.bindPopup(
          `<strong>Valor1:</strong> ${feature.properties.expediente}<br>` +
          `<strong>Valor2:</strong> ${feature.properties.denominacion}<br>`
        );
      },
      style: { color: 'green', weight: 1, fillOpacity: 0.5 }
    }).addTo(map);
    overlayMaps["Límite San Juan"] = capaLimites;
    actualizarControlCapas();
  });

let controlCapas = null;
function actualizarControlCapas() {
  if (controlCapas) map.removeControl(controlCapas);
  controlCapas = L.control.layers(baseMaps, overlayMaps).addTo(map);
}
</script>
  
  <script>
    let puntos = [];
    let poligonoLayer = null;

    // Funciones de validación de secuencia de coordenadas
    function calcularAreaConSigno(coords) {
        let area = 0;
        for (let i = 0; i < coords.length; i++) {
            const j = (i + 1) % coords.length;
            area += coords[i].x * coords[j].y;
            area -= coords[j].x * coords[i].y;
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
            const { x: este, y: norte } = punto;
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

    function validarSecuenciaPuntos() {
        if (puntos.length < 3) {
            alert('Debe agregar al menos 3 puntos para validar la secuencia');
            return;
        }
        
        const coordsReordenadas = reordenarDesdePuntoNoroeste(puntos);
        
        let mensaje = '📊 ANÁLISIS DE SECUENCIA DE PUNTOS:\n\n';
        mensaje += `Puntos ingresados: ${puntos.length}\n`;
        
        const indiceNoroeste = encontrarPuntoNoroeste(puntos);
        const puntoNoroeste = puntos[indiceNoroeste];
        mensaje += `Punto noroeste: ESTE ${puntoNoroeste.x}, NORTE ${puntoNoroeste.y}\n\n`;
        
        if (esSecuenciaHoraria(coordsReordenadas)) {
            mensaje += '✅ Secuencia correcta: comienza en punto noroeste y sigue sentido horario';
        } else {
            mensaje += '⚠️ Secuencia incorrecta: debe seguir sentido horario desde el punto noroeste';
        }
        
        alert(mensaje);
    }

    function corregirSecuenciaPuntos() {
        if (puntos.length < 3) {
            alert('Debe agregar al menos 3 puntos para corregir la secuencia');
            return;
        }
        
        const coordsReordenadas = reordenarDesdePuntoNoroeste(puntos);
        
        if (!esSecuenciaHoraria(coordsReordenadas)) {
            const coordsCorregidas = invertirSecuencia(coordsReordenadas);
            puntos = coordsCorregidas;
            
            // Actualizar lista visual
            const lista = document.getElementById("listaPuntos");
            lista.innerHTML = '';
            puntos.forEach((punto, i) => {
                const li = document.createElement("li");
                li.textContent = `ESTE: ${punto.x}, NORTE: ${punto.y}`;
                lista.appendChild(li);
            });
            
            // Redibujar polígono
            dibujarPoligono();
            
            alert('✅ Secuencia corregida: los puntos ahora siguen el orden correcto (noroeste → sentido horario)');
        } else {
            alert('✅ La secuencia ya es correcta');
        }
    }    proj4.defs("EPSG:22182", "+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 +ellps=WGS84 +units=m +no_defs");
    const crs22182 = new L.Proj.CRS('EPSG:22182',
    proj4.defs('EPSG:22182'),
    {
      origin: [2200000, 0],
      resolutions: [1024, 512, 256, 128, 64, 32, 16, 8, 4, 2, 1],
    }
    );

    const fromProjection = proj4("EPSG:22182");
    const toProjection = proj4("WGS84");   

    function agregarPunto() {
      const x = parseFloat(document.getElementById("x").value);
      const y = parseFloat(document.getElementById("y").value);
      if (isNaN(x) || isNaN(y)) {
        alert("Por favor ingresa valores válidos para ESTE y NORTE");
        return;
      }

      puntos.push({x, y, z: 0});
      const li = document.createElement("li");
      li.textContent = `ESTE: ${x}, NORTE: ${y}`;
      document.getElementById("listaPuntos").appendChild(li);
      dibujarPoligono();
      document.getElementById("x").value = '0.00';
      document.getElementById("y").value = '0.00';
    }

function dibujarPoligono() {
  if (poligonoLayer) map.removeLayer(poligonoLayer);
  if (puntos.length < 3) return;

  const coords = puntos.map(p => {
    const [lon, lat] = proj4(fromProjection, toProjection, [p.x, p.y]);
    return [lon, lat];
  });

  // Crear polígono ingresado en formato GeoJSON (cierra el anillo)
  const polyUser = turf.polygon([ [...coords, coords[0]] ]);

  let dentro = false;
  let parcial = false;

  if (capaLimites) {
    capaLimites.eachLayer(layer => {
      const limite = layer.toGeoJSON();

      if (turf.booleanWithin(polyUser, limite)) {
        dentro = true;
      } else if (turf.booleanDisjoint(polyUser, limite)) {
        // no hace nada (fuera totalmente)
      } else if (turf.intersect(polyUser, limite)) {
        parcial = true;
      }
    });
  }

  // Mostrar en el mapa según el resultado
  if (dentro) {
    poligonoLayer = L.geoJSON(polyUser, { style: { color: 'blue' } }).addTo(map);
    alert("✅ El polígono está completamente dentro de San Juan.");
  } else if (parcial) {
    poligonoLayer = L.geoJSON(polyUser, { style: { color: 'orange' } }).addTo(map);
    alert("⚠️ El polígono está parcialmente dentro de San Juan.");
  } else {
    alert("❌ El polígono está fuera de los límites de San Juan.");
  }

  if (poligonoLayer) map.fitBounds(poligonoLayer.getBounds());
}

    function eliminarUltimoPunto(event) {
      event.preventDefault();
      if (puntos.length === 0) return;
      puntos.pop();
      const lista = document.getElementById("listaPuntos");
      if (lista.lastChild) lista.removeChild(lista.lastChild);
      if (puntos.length >= 3) dibujarPoligono();
      else if (poligonoLayer) {
        map.removeLayer(poligonoLayer);
        poligonoLayer = null;
      }
    }

    function prepararEnvio() { 
      if (puntos.length < 3) {
        alert("Debe agregar al menos 3 puntos para formar un polígono.");
        return false;
      }
      document.getElementById("puntos").value = JSON.stringify(puntos);
      return true;
    }
   
</script>
</body>
</html>

