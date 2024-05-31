<?php

// Conexión a la base de datos
$servername = "mysql-tfg-valentina.alwaysdata.net"; // Cambia esto por el host de tu base de datos en Alwaysdata
$username = "355274"; // Cambia esto por tu usuario de la base de datos en Alwaysdata
$password = "TFG2024Valentina_"; // Cambia esto por tu contraseña de la base de datos en Alwaysdata
$dbname = "tfg-valentina_catalogo_imagenes"; // Cambia esto por el nombre de tu base de datos en Alwaysdata

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Procesar el formulario de búsqueda
if (isset($_POST['buscar'])) {
    $termino_busqueda = $_POST['termino_busqueda'];

    // Consulta SQL para buscar imágenes que coincidan con el término de búsqueda y ordenarlas por confianza descendente
    $sql = "SELECT i.*, ei.confianza FROM imagenes i 
        INNER JOIN etiquetas_imagenes ei ON i.id_imagen = ei.id_imagen
        INNER JOIN etiquetas e ON ei.id_etiqueta = e.id_etiqueta
        WHERE e.nombre_etiqueta = ?
        ORDER BY ei.confianza DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $termino_busqueda);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style/style.css">
    <link rel="icon" type="image/jpg" href="/img/favicon.svg"/>
    <title>Rekotag</title>
</head>
<body>
    <h1>Buscar Imágenes</h1>
    <form action="buscar.php" method="post">
        <input type="text" name="termino_busqueda" placeholder="Introduce el término de búsqueda">
        <button type="submit" name="buscar">Buscar</button>
    </form>

    <?php if (isset($result) && $result->num_rows > 0): ?>
        <div class="imagenes-busqueda">
            <div class="etiqueta-busqueda">
                <h3>Resultados para: <?php echo $termino_busqueda ?><h3>
            </div>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="imagen-busqueda">
                    <img src="<?= $row['ruta_imagen'] ?>" alt="<?= $row['nombre_imagen'] ?>">
                    <p>Confianza: <?= $row['confianza'] ?>%</p>
                </div>
            <?php endwhile; ?>
        </div>
    <?php elseif (isset($_POST['buscar'])): ?>
        <p>No se encontraron imágenes que coincidan con el término de búsqueda.</p>
    <?php endif; ?>

    <div class="menu-footer">
        <ul>
            <li><a href="index.php"><img src="/img/home.svg"></a></li>
            <li><a href="catalogar.php"><img src="/img/upload.svg"></a></li>
            <li><a href="buscar.php"><img src="/img/search.svg"></a></li>
        </ul>
    </div>
</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>
