<?php
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

$conn = require("conection.php");


// Obtener el usuario actual
$stmt_usuario = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
$stmt_usuario->bindParam(':email', $_SESSION['email']);
$stmt_usuario->execute();
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM libros WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$usuario['id']]);
$libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si se encontró el usuario
if (!$usuario) {
    die("Usuario no encontrado");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - <?php echo htmlspecialchars($usuario['nombre']); ?></title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body> 
    <div class="container">
        <div class="nav-superior">
            <a href="biblioteca.php">← Volver a la Biblioteca</a>
            
        </div>
        
        <div class="libro-detalle-container">
            <div class="libro-card-detalle">
                <?php if (!empty($usuario['img'])): 
                    $imagen = "../" . $usuario['img'];?>
                    <img src="<?php echo htmlspecialchars($imagen); ?>" alt="Foto de perfil de <?php echo htmlspecialchars($usuario['nombre']); ?>">
                <?php else: ?>
                    <img src="../images/default-user.png" alt="Foto de perfil predeterminada">
                <?php endif; ?>
                
                <h3><?php echo htmlspecialchars($usuario['nombre']); ?></h3>
                
                <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
                
                <?php if (!empty($usuario['fecha_registro'])): ?>
                    <p><strong>Miembro desde:</strong> <?php echo htmlspecialchars($usuario['fecha_registro']); ?></p>
                <?php endif; ?>
                
                <div class="botones-accion">
                    <?php if(isset($_SESSION['id_usuario']) && $usuario['id'] === $_SESSION['id_usuario']): ?>
                        <form action="editarPerfil.php" method="GET" class="form-inline">
                            <button type="submit">Editar Perfil</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div>
            <?php if (count($libros) > 0): ?>
                <div class="libro-detalle-container">
                    <?php foreach ($libros as $libro): ?>
                        <div class="libro-card-detalle-usuario">
                            <?php if (!empty($libro['img'])): 
                                $imagen = "../" . $libro['img'];?>
                                <img src="<?php echo htmlspecialchars($imagen); ?>" 
                                    alt="Portada de <?php echo htmlspecialchars($libro['titulo']); ?>" 
                                    title="<?php echo htmlspecialchars($libro['titulo']); ?>">
                                    <p><strong>Titulo:</strong> <?php echo htmlspecialchars($libro['titulo']); ?>
                                    <p><strong>Nº de visitas:</strong> <?php echo htmlspecialchars($libro['visitas']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <form action="cierre.php" method="POST" class="form-inline" style="text-align: center; margin-top: 30px;">
            <input type="submit" name="cerrar" value="Cerrar Sesión">
        </form>
    </div>
</body>
</html>