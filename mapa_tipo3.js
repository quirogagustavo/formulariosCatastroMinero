const map = L.map('map', {
  center: [-31.5, -68.5], // San Juan, Argentina
  zoom: 7,
  crs: L.CRS.EPSG3857
});

// =========================
// MAPAS BASE
// =========================

// Google Maps Carreteras
const googleRoadmap = L.tileLayer('http://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
  maxZoom: 20,
  attribution: '© Google'
});

// Google Maps Satélite
const googleSatellite = L.tileLayer('http://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
  maxZoom: 20,
  attribution: '© Google'
});

// Google Maps Terreno
const googleTerrain = L.tileLayer('http://mt1.google.com/vt/lyrs=p&x={x}&y={y}&z={z}', {
  maxZoom: 20,
  attribution: '© Google'
});

// Google Maps Híbrido (Satélite + Labels)
const googleHybrid = L.tileLayer('http://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
  maxZoom: 20,
  attribution: '© Google'
});

// UNIDE San Juan (probamos si soporta EPSG:3857)
const baseUNIDE = L.tileLayer.wms("https://unide.sanjuan.gob.ar/geoserver/wms", {
  layers: "GeoserviciosUnide",
  format: "image/png",
  transparent: false,
  version: "1.1.1",
  attribution: "Mapa base: UNIDE San Juan"
});

// Diccionario de bases
const baseMaps = {
  "UNIDE San Juan": baseUNIDE,
  "Google Carreteras": googleRoadmap,
  "Google Satélite": googleSatellite,
  "Google Terreno": googleTerrain,
  "Google Híbrido": googleHybrid 
};

// Agregamos Google Satélite por defecto
baseUNIDE.addTo(map);

// =========================
// CAPAS VECTORIALES
// =========================
let capaManifestaciones, capaPermisos, capaMinas, capaAreas, capaSolicitudes;
const overlayMaps = {};

// Manifestaciones
fetch("consulta_gra_manifestaciones.php")
  .then(res => res.json())
  .then(data => {
    capaManifestaciones = L.geoJSON(data, {
      onEachFeature: (feature, layer) => {
        layer.bindPopup(
          `<strong>Expte:</strong> ${feature.properties.expediente}<br>` +
          `<strong>Denominación:</strong> ${feature.properties.denominacion}<br>`
        );
      },
      style: { color: 'green', weight: 0.5, fillOpacity: 0.2 }
    }).addTo(map);
    overlayMaps["Manifestaciones"] = capaManifestaciones;
    actualizarControlCapas();
  });

// Permisos de Exploración
fetch("consulta_gra_permisos_exploracion.php")
  .then(res => res.json())
  .then(data => {
    capaPermisos = L.geoJSON(data, {
      onEachFeature: (feature, layer) => {
        layer.bindPopup(`<strong>Expte:</strong> ${feature.properties.expediente}<br>`);
      },
      style: { color: 'red', weight: 0.5, fillOpacity: 0.2 }
    }).addTo(map);
    overlayMaps["Permisos Exploración"] = capaPermisos;
    actualizarControlCapas();
  });

// Minas
fetch("consulta_gra_minas.php")
  .then(res => res.json())
  .then(data => {
    capaMinas = L.geoJSON(data, {
      onEachFeature: (feature, layer) => {
        layer.bindPopup(
          `<strong>Expte:</strong> ${feature.properties.expediente}<br>` +
          `<strong>Denominación:</strong> ${feature.properties.denominacion}<br>`
        );
      },
      style: { color: 'orange', weight: 0.5, fillOpacity: 0.2 }
    }).addTo(map);
    overlayMaps["Minas"] = capaMinas;
    actualizarControlCapas();
  });

  // Areas Protegidas
fetch("consulta_gra_areas_protegidas.php")
  .then(res => res.json())
  .then(data => {
    capaAreas = L.geoJSON(data, {
      onEachFeature: (feature, layer) => {
        layer.bindPopup(
          `<strong>Expte:</strong> ${feature.properties.expediente}<br>` +
          `<strong>Denominación:</strong> ${feature.properties.denominacion}<br>`
        );
      },
      style: { color: 'MediumSpringGreen', weight: 0.5, fillOpacity: 0.5 }
    }).addTo(map);
    overlayMaps["Áreas Protegidas"] = capaAreas;
    actualizarControlCapas();
  });

    // Cargar Canteras
  fetch("consulta_gra_mensura_canteras.php")
    .then(res => res.json())
    .then(data => {
      capaMensuraCanteras = L.geoJSON(data, {
        onEachFeature: function (feature, layer) {
          layer.bindPopup(
            `<strong>Expte:</strong> ${feature.properties.expediente}<br>` +
            `<strong>Denominación:</strong> ${feature.properties.denominacion}<br>` 
          );
        },
        style: {
          color: 'purple',
          weight: 0.1,
          fillOpacity: 0.3
        }
      }).addTo(map);
      overlayMaps["Canteras"] = capaMensuraCanteras;
      actualizarControlCapas();
    }); 

      fetch("consulta_gra_solicitudes_poligono.php")
  .then(res => res.json())
  .then(data => {
    capaSolicitudes = L.geoJSON(data, {
      onEachFeature: (feature, layer) => {
        layer.bindPopup(
          `<strong>Expte:</strong> ${feature.properties.expediente}<br>` +
          `<strong>Denominación:</strong> ${feature.properties.denominacion}<br>`
        );
      },
      style: { color: 'Camel', weight: 0.5, fillOpacity: 0.5 }
    }).addTo(map);
    overlayMaps["Solicitudes"] = capaSolicitudes;
    actualizarControlCapas();
  });


let controlCapas = null;
function actualizarControlCapas() {
  if (controlCapas) map.removeControl(controlCapas);
  controlCapas = L.control.layers(baseMaps, overlayMaps).addTo(map);
}