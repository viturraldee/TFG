<?php

// Configuración del SDK de AWS para PHP
require 'vendor/autoload.php';

use Aws\Rekognition\RekognitionClient;

// Configuración de las credenciales de AWS
$sharedConfig = [
    'region'  => 'us-east-1',
    'version' => 'latest',
    'credentials' => [
        'key'    => '#',
        'secret' => '#',
    ],
];

$rekognition = new RekognitionClient($sharedConfig);

$fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

if ($fileExtension != 'jpg' && $fileExtension != 'jpeg') {
    // Mostrar un mensaje de error si no subem JPG o JPEG
    echo "Solo se permiten archivos JPG o JPEG.";
    exit;
}

// Obtener la imagen enviada por el usuario
$imageData = file_get_contents($_FILES['image']['tmp_name']);

// Ruta donde se almacenará temporalmente la imagen en el servidor
$uploadPath = 'uploads/' . $_FILES['image']['name'];

// Guardar la imagen en el servidor
move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);

// Realizar la llamada a la API de Rekognition para analizar la imagen
$resultLabels = $rekognition->detectLabels([
    'Image' => [
        'Bytes' => $imageData,
    ],
]);

$resultText = $rekognition->detectText([
    'Image' => [
        'Bytes' => $imageData,
    ],
]);

// Extraer las etiquetas detectadas y sus confianzas
$labels = [];
foreach ($resultLabels['Labels'] as $label) {
    $labels[] = [
        'Name' => $label['Name'],
        'Confidence' => $label['Confidence'],
    ];
}

// Extraer los textos detectados
foreach ($resultText['TextDetections'] as $textDetection) {
    $labels[] = [
        'Name' => $textDetection['DetectedText'],
        'Confidence' => $textDetection['Confidence'],
    ];
}

// Almacenar la información en la base de datos en Alwaysdata
$servername = "mysql-tfg-valentina.alwaysdata.net";
$username = "355274";
$password = "TFG2024Valentina_";
$dbname = "tfg-valentina_catalogo_imagenes";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Preparar la consulta SQL para insertar 
$sql_imagen = "INSERT INTO imagenes (nombre_imagen, ruta_imagen) VALUES (?, ?) ON DUPLICATE KEY UPDATE id_imagen=id_imagen";
$stmt_imagen = $conn->prepare($sql_imagen);
$stmt_imagen->bind_param("ss", $nombre_imagen, $ruta_imagen);


$nombre_imagen = $_FILES['image']['name'];
$ruta_imagen = $uploadPath;
$stmt_imagen->execute();
$stmt_imagen->close();

// Obtener el ID de la imagen recién insertada
$id_imagen = $conn->insert_id;

// Preparar la consulta SQL para insertar las etiquetas en la tabla de etiquetas, si no existen aún
$sql_etiquetas = "INSERT INTO etiquetas (nombre_etiqueta) VALUES (?) ON DUPLICATE KEY UPDATE id_etiqueta=id_etiqueta";
$stmt_etiquetas = $conn->prepare($sql_etiquetas);

// Setear los parámetros y ejecutar la consulta para cada etiqueta
foreach ($labels as $label) {
    $stmt_etiquetas->bind_param("s", $label['Name']);
    $stmt_etiquetas->execute();
}
$stmt_etiquetas->close();

// Preparar la consulta SQL para insertar las relaciones entre imagen y etiquetas
$sql_relaciones = "INSERT INTO etiquetas_imagenes (id_imagen, id_etiqueta, confianza) VALUES (?, ?, ?)";
$stmt_relaciones = $conn->prepare($sql_relaciones);
$stmt_relaciones->bind_param("iid", $id_imagen, $id_etiqueta, $confianza);

// Obtener el ID de cada etiqueta recién insertada y relacionarla con la imagen
foreach ($labels as $label) {
    // Obtener el ID de la etiqueta correspondiente
    $sql_select_etiqueta = "SELECT id_etiqueta FROM etiquetas WHERE nombre_etiqueta = ?";
    $stmt_select_etiqueta = $conn->prepare($sql_select_etiqueta);
    $stmt_select_etiqueta->bind_param("s", $label['Name']);
    $stmt_select_etiqueta->execute();
    $result_select_etiqueta = $stmt_select_etiqueta->get_result();
    $row_etiqueta = $result_select_etiqueta->fetch_assoc();
    $id_etiqueta = $row_etiqueta['id_etiqueta'];

    // Insertar la relación entre la imagen, la etiqueta y la confianza
    $confianza = $label['Confidence'];
    $stmt_relaciones->execute();
}

$stmt_relaciones->close();
$stmt_select_etiqueta->close();


$conn->close();

header('Location: catalogar.php?labels=' . urlencode(json_encode($labels)) . '&image=' . urlencode($uploadPath));
exit;
?>