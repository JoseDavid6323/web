<?php
// -------------------
// LEER CSV
// -------------------
$coordenadas = [];

if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
    $ruta = $_FILES['archivo']['tmp_name'];
    $file = fopen($ruta, 'r');

    // Leer cabecera (Descripcion,Latitud,Longitud)
    fgetcsv($file);

    while (($data = fgetcsv($file)) !== false) {

        // Validar que existan las 3 columnas
        if (count($data) < 3) continue;

        $descripcion = trim($data[0]);
        $lat = floatval($data[1]);
        $lng = floatval($data[2]);

        // Validar que sea coordenada válida
        if ($lat != 0 && $lng != 0) {
            $coordenadas[] = [
                "descripcion" => $descripcion,
                "lat" => $lat,
                "lng" => $lng
            ];
        }
    }

    fclose($file);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Georeferenciación </title>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        body { font-family: Arial; margin: 20px; }
        #map { height: 550px; width: 100%; margin-top: 20px; }
    </style>
</head>
<body>

<h2>📌 Georeferenciación sube tu archivo en csv y vas a ver en donde estas ubicado</h2>

<!-- Subir archivo CSV -->
<form action="" method="post" enctype="multipart/form-data">
    <label><b>Subir archivo  de CSV </b></label><br><br>
    <input type="file" name="archivo" accept=".csv" required>
    <button type="submit">Cargar</button>
</form>

<div id="map"></div>

<script>
// -------------------
// MAPA
// -------------------
var mapa = L.map('map').setView([-9.19, -75.015], 6); // Perú por defecto

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18
}).addTo(mapa);

// Convertir PHP a JSON
var puntos = <?php echo json_encode($coordenadas); ?>;

// Dibujar marcadores
puntos.forEach(p => {
    L.marker([p.lat, p.lng]).addTo(mapa)
        .bindPopup("<b>" + p.descripcion + "</b><br>(" + p.lat + ", " + p.lng + ")");
});

// Ajustar a los puntos
if (puntos.length > 0) {
    var group = new L.featureGroup(
        puntos.map(p => L.marker([p.lat, p.lng]))
    );
    mapa.fitBounds(group.getBounds());
}
</script>

</body>
</html>
