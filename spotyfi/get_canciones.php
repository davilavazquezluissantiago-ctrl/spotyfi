<?php
// Motor de respuesta JSON rápido para AJAX
header('Content-Type: application/json');

$servidor = "localhost";
$usuario_db = "root";
$contrasena_db = "";
$base_datos = "disquera_spotify";
$conexion = new mysqli($servidor, $usuario_db, $contrasena_db, $base_datos);

if ($conexion->connect_error) { die(json_encode([])); }

$id_album = isset($_GET['id_album']) ? intval($_GET['id_album']) : 0;

if ($id_album > 0) {
    // Busca las canciones y junta el nombre del artista del álbum
    $sql = "SELECT m.*, a.artista 
            FROM multimedia m 
            INNER JOIN albumes a ON m.id_album = a.id_album 
            WHERE m.id_album = $id_album 
            ORDER BY m.id_cancion ASC";
            
    $resultado = $conexion->query($sql);
    $canciones = [];
    
    while ($row = $resultado->fetch_assoc()) {
        $canciones[] = $row;
    }
    
    echo json_encode($canciones);
} else {
    echo json_encode([]);
}

$conexion->close();
?>