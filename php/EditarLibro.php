<?php
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

$isbn = $_GET['isbn'] ?? null;

if (!$isbn) {
    die("ISBN no proporcionado");
}

$conn = require("conection.php");

// Obtener el libro actual
$sql = "SELECT * FROM libros WHERE isbn = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$isbn]);
$libro = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar si se encontró el libro
if (!$libro) {
    die("Libro no encontrado");
}

// Verificar que el libro pertenece al usuario actual
if ($libro['id_usuario'] !== $_SESSION['id_usuario']) {
    die("No tienes permiso para editar este libro");
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $autor = $_POST['autor'] ?? '';
    $isbn_nuevo = $_POST['isbn'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $link = $_POST['link'] ?? '';
    
    // Manejar la imagen si se sube una nueva
    $img_path = $libro['img']; // Mantener la imagen anterior por defecto
    
    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_name = basename($_FILES['img']['name']);
        $target_file = $upload_dir . uniqid() . '_' . $file_name;
        
        // Validar tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['img']['type'], $allowed_types)) {
            if (move_uploaded_file($_FILES['img']['tmp_name'], $target_file)) {
                $img_path = str_replace('../', '', $target_file);
                
                // Eliminar la imagen anterior si existe
                if (!empty($libro['img']) && file_exists('../' . $libro['img'])) {
                    unlink('../' . $libro['img']);
                }
            }
        }
    }
    
    // Actualizar el libro en la base de datos
    $sql_update = "UPDATE libros SET 
                   titulo = ?, 
                   autor = ?, 
                   isbn = ?, 
                   fecha = ?, 
                   descripcion = ?, 
                   link = ?, 
                   img = ? 
                   WHERE isbn = ? AND id_usuario = ?";
    
    $stmt_update = $conn->prepare($sql_update);
    $resultado = $stmt_update->execute([
        $titulo, 
        $autor, 
        $isbn_nuevo, 
        $fecha, 
        $descripcion, 
        $link, 
        $img_path, 
        $isbn, 
        $_SESSION['id_usuario']
    ]);
    
    if ($resultado) {
        // Redirigir al libro actualizado
        header("Location: mostrarLibro.php?isbn=" . urlencode($isbn_nuevo));
        exit();
    } else {
        $error = "Error al actualizar el libro";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Libro</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="container">
    <h1>Editar Libro</h1>
    
    <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="titulo">Título *</label>
            <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($libro['titulo']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="autor">Autor *</label>
            <input type="text" id="autor" name="autor" value="<?php echo htmlspecialchars($libro['autor']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="isbn">ISBN *</label>
            <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($libro['isbn']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="fecha">Fecha de publicación</label>
            <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($libro['fecha'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($libro['descripcion'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="link">Link de compra</label>
            <input type="url" id="link" name="link" value="<?php echo htmlspecialchars($libro['link'] ?? ''); ?>" placeholder="https://...">
        </div>
        
        <div class="form-group">
            <label for="img">Imagen de portada</label>
            <?php if (!empty($libro['img'])): ?>
                <p>Imagen actual:</p>
                <img src="<?php echo htmlspecialchars('../' . $libro['img']); ?>" alt="Portada actual" class="imagen-actual">
            <?php endif; ?>
            <input type="file" id="img" name="img" accept="image/*">
            <small>Deja vacío si no deseas cambiar la imagen</small>
        </div>
        
        <button type="submit">Guardar cambios</button>
        <button type="button" class="btn-cancelar" onclick="window.location.href='mostrarLibro.php?isbn=<?php echo urlencode($libro['isbn']); ?>'">Cancelar</button>
    </form>
    
    <br>
    <a href="biblioteca.php">← Volver a la página principal</a>
    </div>
</body>
</html>