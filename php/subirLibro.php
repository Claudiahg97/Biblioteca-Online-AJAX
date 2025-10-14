<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: http://localhost/Biblioteca-Online");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = require("conection.php");

    $target_dir = "../uploads/";
    $target_file_db = "";

    // Manejo de la imagen
    if (empty($_FILES["fileToUpload"]["name"])) {
        $target_file_db = "uploads/default.jpg"; // Ruta relativa para guardar en BD
    } else {
        $nameOriginal = pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
        // Hash único: sha256(nombreArchivo_nombreUsuario)
        $hashNombre = hash('sha256', $nameOriginal . '_' . $_SESSION['nombre']);
        $newFileName = $hashNombre . '.' . $extension; // Nombre único
        $target_file = $target_dir . $newFileName;
        $target_file_db = "uploads/" . $newFileName; // Ruta para BD

        // Verificar que es una imagen
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if($check === false) {
            echo "<script>alert('El archivo no es una imagen válida'); window.history.back();</script>";
            exit;
        }

        // Verificar tamaño (5MB máximo)
        if ($_FILES["fileToUpload"]["size"] > 5000000) {
            echo "<script>alert('El archivo es demasiado grande'); window.history.back();</script>";
            exit;
        }

        // Permitir ciertos formatos
        if(!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo "<script>alert('Solo se permiten archivos JPG, JPEG, PNG y GIF'); window.history.back();</script>";
            exit;
        }

        // Intentar subir el archivo
        if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "<script>alert('Error al subir la imagen'); window.history.back();</script>";
            exit;
        }
    }
    
    // Obtener datos del formulario
    $isbn = $_POST['isbn'] ?? '';
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $fecha = $_POST['fecha'] ?? null;
    $link = $_POST['link'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $generos = $_POST['generos']; // Array de IDs de géneros
    $id_usuario = $_SESSION['id_usuario'];
    $img = $target_file_db; //Elijo la ruta de la imagen subida o la imagen por defecto.

        // Validar géneros
    if (empty($generos)) {
        echo "<script>alert('Debes seleccionar al menos un género'); window.history.back();</script>";
        exit;
    }
    
    try {
        // Preparar consulta para insertar libro (con PDO)
        $stmt = $conn->prepare("INSERT INTO libros (isbn, titulo, autor, fecha, link, descripcion, id_usuario, img) 
                               VALUES (:isbn, :titulo, :autor, :fecha, :link, :descripcion, :id_usuario, :img)");
        
        $stmt->bindParam(':isbn', $isbn);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':autor', $autor);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':link', $link);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':img', $img);
        
        if ($stmt->execute()) {
            $id_libro = $conn->lastInsertId(); // Obtener ID del libro insertado
            
            // Insertar los géneros en la tabla libro_genero
            $stmt_genero = $conn->prepare("INSERT INTO libro_genero (id_libro, id_genero) VALUES (:id_libro, :id_genero)");
            
            foreach ($generos as $id_genero) {
                $stmt_genero->bindParam(':id_libro', $id_libro);
                $stmt_genero->bindParam(':id_genero', $id_genero);
                $stmt_genero->execute();
            }
            
        }
        $_SESSION['exito'] = "Libro registrado exitosamente";
        header("Location: http://localhost/Biblioteca-Online/php/biblioteca.php");
        exit;
    } catch(PDOException $e) {
        if (isset($target_file) && file_exists($target_file) && $target_file != $target_dir . "default.jpg") {
            unlink($target_file);
        }
        
        $_SESSION['errorLibro'] = "Error al registrar el libro: " . $e->getMessage();
        header("Location: http://localhost/Biblioteca-Online/php/formularioLibro.php");
        exit;
    }
    
    $conn = null;
}
