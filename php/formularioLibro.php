<?php 
session_start(); 
if (isset($_SESSION['errorLibro'])) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px;'>" . 
         htmlspecialchars($_SESSION['error']) . "</div>";
    unset($_SESSION['error']);
}
$conn = require( "conection.php");

// Obtener géneros de la base de datos
$sql = "SELECT * FROM generos ORDER BY nombre";
$stmt = $conn->query($sql);
$generos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Online</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    
    <div class="container">
        <a href="biblioteca.php">← Volver a la Biblioteca</a>
    <form action="subirLibro.php" method="POST" enctype="multipart/form-data">
        <h3>Subir un nuevo libro</h3>
        
        <label for="isbn">ISBN*:</label>
        <input type="text" id="isbn" name="isbn" placeholder="978-8420606898" required>
        <br>
        <label for="titulo">Título*:</label>
        <input type="text" id="titulo" name="titulo" required>
        <br>
        <label for="autor">Autor*:</label>
        <input type="text" id="autor" name="autor" required>
        <br>
        <label for="generos">Géneros*:</label>
        <select id="generos" name="generos[]" multiple required>
            <?php
                if (count($generos) > 0) {
                    foreach ($generos as $fila) {
                        echo '<option value="' . $fila['id'] . '">' . $fila['nombre'] . '</option>';
                    }
                } else {
                    echo '<option value="">No hay géneros disponibles</option>';
                }
            ?>
        </select>
        <p class="info">Mantén presionado Ctrl (Cmd en Mac) para seleccionar múltiples géneros</p>
        <br>
        <label for="fecha">Fecha de publicación:</label>
        <input type="date" id="fecha" name="fecha">
        <br>
        <label for ="portada">Portada</label> 
        <input type="file" name="fileToUpload" id="fileToUpload">
        <br>
        <label for="link">Link de compra:</label>
        <input type="url" id="link" name="link" placeholder="https://ejemplo.com">
        <br>
        <label for="descripcion">Sinopsis:</label>
        <textarea id="descripcion" name="descripcion" rows="5"></textarea>
        <br>
        <input type="submit" name="subir" value="Registrar libro">
    </form>
    </div>
</body>
</html>

