<?php
session_start();

// Mostrar mensajes de éxito o error
if (isset($_SESSION['exito'])) {
    echo "<div class='mensaje-exito'>" . 
         htmlspecialchars($_SESSION['exito']) . "</div>";
    unset($_SESSION['exito']);
}
if (!isset($_SESSION['id_usuario'])) {
    header('Location: http://localhost/Biblioteca-Online');
}

$conn = require("conection.php");

// Obtener todos los libros de la base de datos
$sql = "SELECT * FROM libros ORDER BY titulo";
$stmt = $conn->query($sql);
$libros = $stmt->fetchAll(PDO::FETCH_ASSOC); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="container">
    <h1>Biblioteca Online</h1>
    
    <?php if(isset($_SESSION['nombre'])): ?>
        <h2><?php echo htmlspecialchars($_SESSION['nombre']); ?></h2>
        <form action="formularioLibro.php" method="GET" class="form-inline">
            <button type="submit">Registrar un nuevo libro</button>
        </form>
    <?php endif; ?>
    
    <hr>
    
    <h2>Catálogo de Libros</h2>
    
    <?php if (count($libros) > 0): ?>
        <div class="libros-container">
            <?php foreach ($libros as $libro): ?>
                <div class="libro-card">
                    <?php if (!empty($libro['img'])): 
                        $imagen = "../" . $libro['img'];?>
                        <a href="mostrarLibro.php?isbn=<?php echo htmlspecialchars($libro['isbn']); ?>" 
                           class="libro-link">
                            <img src="<?php echo htmlspecialchars($imagen); ?>" 
                                 alt="Portada de <?php echo htmlspecialchars($libro['titulo']); ?>" 
                                 title="<?php echo htmlspecialchars($libro['titulo']); ?>"
                                 width="100" 
                                 height="150">
                            <div class="libro-titulo-hover">
                                <?php echo htmlspecialchars($libro['titulo']); ?>
                            </div>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-libros">
            <h3>No hay libros registrados todavía</h3>
            <p>Sé el primero en añadir un libro a la biblioteca</p>
        </div>
    <?php endif; ?>
    <form action="cierre.php" method="POST" class="form-inline" style="text-align: center; margin-top: 30px;">
        <input type="submit" name="cerrar" value="Cerrar Sesión">
    </form>
    </div>
</body>
</html>