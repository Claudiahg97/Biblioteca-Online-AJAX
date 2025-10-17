<?php
session_start();

$isbn = $_GET['isbn'] ?? null;

if (!$isbn) {
    die("ISBN no proporcionado");
}

$conn = require("conection.php");

// Consulta el libro por ISBN
$sql = "SELECT * FROM libros WHERE isbn = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$isbn]);
$libro = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($libro['visitas'])) {
    $sqlVisitas = "UPDATE libros SET visitas = 1 WHERE id = ?";
    $stmtVisitas = $conn->prepare($sqlVisitas);
    $stmtVisitas->execute([$libro['id']]);
} else{
    // Actualiza las visitas
    $sqlVisitas = "UPDATE libros SET visitas = visitas + 1 WHERE id = ?";
    $stmtVisitas = $conn->prepare($sqlVisitas);
    $stmtVisitas->execute([$libro['id']]);
}

// Verificar si se encontró el libro
if (!$libro) {
    die("Libro no encontrado");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca - <?php echo htmlspecialchars($libro['titulo']); ?></title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body> 
    <div class="container">
        <div class="nav-superior">
            <a href="biblioteca.php">← Volver a la Biblioteca</a><br>
            
            <?php if(isset($_SESSION['nombre'])): ?>
                <form action="formularioLibro.php" method="GET" class="form-inline">
                    <button type="submit">Registrar un nuevo libro</button>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="libro-detalle-container">
            <div class="libro-card-detalle">
                <?php if (!empty($libro['img'])): 
                    $imagen = "../" . $libro['img'];?>
                    <img src="<?php echo htmlspecialchars($imagen); ?>" alt="Portada de <?php echo htmlspecialchars($libro['titulo']); ?>">
                <?php endif; ?>
                
                <h3><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                
                <p><strong>Autor:</strong> <?php echo htmlspecialchars($libro['autor']); ?></p>
                
                <?php if (!empty($libro['isbn'])): ?>
                    <p><strong>ISBN:</strong> <?php echo htmlspecialchars($libro['isbn']); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($libro['fecha'])): 
                        if ($libro['fecha'] != "0000-00-00"):?>
                            <p><strong>Fecha de publicación:</strong> <?php echo htmlspecialchars($libro['fecha']); ?></p>
                <?php endif; 
                endif; ?>
                
                <?php if (!empty($libro['descripcion'])): ?>
                    <div class="descripcion-completa">
                        <strong>Descripción:</strong><br><br>
                        <?php echo nl2br(htmlspecialchars($libro['descripcion'])); ?>
                    </div>
                <?php endif; ?>
                
                
                <div class="botones-accion">
                    <?php if (!empty($libro['link'])): ?>
                        <a href="<?php echo htmlspecialchars($libro['link']); ?>" target="_blank" class="enlace-compra">Comprar</a>
                    <?php endif; ?>

                    
                    <?php if(isset($_SESSION['id_usuario']) && $libro['id_usuario'] === $_SESSION['id_usuario']): ?>
                        <form action="editarLibro.php" method="GET" class="form-inline">
                            <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($isbn); ?>">
                            <button type="submit">Editar el libro</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <form action="cierre.php" method="POST" class="form-inline" style="text-align: center; margin-top: 30px;">
            <input type="submit" name="cerrar" value="Cerrar Sesión">
        </form>
    </div>
</body>
</html>