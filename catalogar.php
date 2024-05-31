<?php

// Obtener las etiquetas detectadas, si están disponibles
$labels = [];
if (isset($_GET['labels'])) {
    $labels = json_decode($_GET['labels'], true);
}

// Obtener la URL de la imagen, si está disponible
$imageUrl = '';
if (isset($_GET['image'])) {
    $imageUrl = $_GET['image'];
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

    <h1>Catalogación de Imágenes</h1>
    <form action="backend.php" method="post" enctype="multipart/form-data">
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Analizar Imagen</button>
    </form>

    <div class = "analisis">
        <?php if ($imageUrl): ?>
        <div class="imagen">
            <img src="<?= $imageUrl ?>" alt="Imagen Analizada">
        </div>
        <?php endif; ?>

        <?php if ($labels): ?>
        <div class="resultados">
            <?php foreach ($labels as $label): ?>
                <p><?= $label['Name'] ?>: <?= round($label['Confidence'], 2) ?>%</p>
            <?php endforeach; ?>
        </div>
    </div>

    
<?php endif; ?>    
</body>
    <div class="menu-footer">
        <ul>
            <li><a href="index.php"><img src="/img/home.svg"></a></li>
            <li><a href="catalogar.php"><img src="/img/upload.svg"></a></li>
            <li><a href="buscar.php"><img src="/img/search.svg"></a></li>
        </ul>
    </div>
</html>