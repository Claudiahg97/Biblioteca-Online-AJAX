<?php
session_start();
header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para votar'
    ]);
    exit();
}

// Validar datos recibidos
$id_libro = $_GET['id_libro'] ?? null;
$voto = $_GET['voto'] ?? null;

if (!$id_libro || $voto === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit();
}

// Validar que el voto sea 0 o 1
if ($voto != 0 && $voto != 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Voto inválido'
    ]);
    exit();
}

try {
    $conn = require("conection.php");
    
    // Verificar si el usuario ya votó este libro
    $sqlCheck = "SELECT clasificacion FROM clasificacion WHERE id_libro = ? AND id_usuario = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->execute([$id_libro, $_SESSION['id_usuario']]);
    $votoExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if ($votoExistente) {
        // Si el voto es el mismo, no hacer nada
        if ($votoExistente['clasificacion'] == $voto) {
            // Obtener estadísticas actuales
            $sqlStats = "SELECT 
                            SUM(CASE WHEN clasificacion = 1 THEN 1 ELSE 0 END) as likes,
                            SUM(CASE WHEN clasificacion = 0 THEN 1 ELSE 0 END) as dislikes
                         FROM clasificacion WHERE id_libro = ?";
            $stmtStats = $conn->prepare($sqlStats);
            $stmtStats->execute([$id_libro]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Ya has votado así anteriormente',
                'likes' => (int)$stats['likes'],
                'dislikes' => (int)$stats['dislikes']
            ]);
            exit();
        }
        
        // Actualizar voto existente
        $sqlUpdate = "UPDATE clasificacion SET clasificacion = ? WHERE id_libro = ? AND id_usuario = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->execute([$voto, $id_libro, $_SESSION['id_usuario']]);
        $mensaje = 'Has cambiado tu voto';
    } else {
        // Insertar nuevo voto
        $sqlInsert = "INSERT INTO clasificacion (id_libro, id_usuario, clasificacion) VALUES (?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->execute([$id_libro, $_SESSION['id_usuario'], $voto]);
        $mensaje = '¡Gracias por tu voto!';
    }
    
    // Obtener las estadísticas actualizadas
    $sqlStats = "SELECT 
                    SUM(CASE WHEN clasificacion = 1 THEN 1 ELSE 0 END) as likes,
                    SUM(CASE WHEN clasificacion = 0 THEN 1 ELSE 0 END) as dislikes
                 FROM clasificacion WHERE id_libro = ?";
    $stmtStats = $conn->prepare($sqlStats);
    $stmtStats->execute([$id_libro]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => $mensaje,
        'likes' => (int)$stats['likes'],
        'dislikes' => (int)$stats['dislikes']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar el voto'
    ]);
}
?>