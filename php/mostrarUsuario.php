<?php
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

$conn = require("conection.php");

// Obtener el usuario actual
$stmt_usuario = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt_usuario->bindParam(':id', $_SESSION['id_usuario']);
$stmt_usuario->execute();
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

// Verificar si se encontró el usuario
if (!$usuario) {
    die("Usuario no encontrado");
}

// Obtener los libros del usuario
$sql = "SELECT * FROM libros WHERE id_usuario = ? ORDER BY titulo";
$stmt = $conn->prepare($sql);
$stmt->execute([$usuario['id']]);
$libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener puntuaciones para cada libro
$libros_con_puntuacion = [];
foreach ($libros as $libro) {
    $sqlVotos = "SELECT 
                    SUM(CASE WHEN clasificacion = 1 THEN 1 ELSE 0 END) as likes,
                    SUM(CASE WHEN clasificacion = 0 THEN 1 ELSE 0 END) as dislikes
                 FROM clacificacion 
                 WHERE id_libro = ?";
    $stmtVotos = $conn->prepare($sqlVotos);
    $stmtVotos->execute([$libro['id']]);
    $votos = $stmtVotos->fetch(PDO::FETCH_ASSOC);
    
    $likes = $votos['likes'] ?? 0;
    $dislikes = $votos['dislikes'] ?? 0;
    $total = $likes + $dislikes;
    $porcentaje = $total > 0 ? round(($likes / $total) * 100) : 0;
    
    $libro['likes'] = $likes;
    $libro['dislikes'] = $dislikes;
    $libro['porcentaje'] = $porcentaje;
    $libros_con_puntuacion[] = $libro;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - <?php echo htmlspecialchars($usuario['nombre']); ?></title>
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .libro-card-detalle-usuario {
            background: #fefdfb;
            border: 1px solid #d4a574;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 4px 12px rgba(139, 90, 43, 0.1);
        }
        
        .libro-card-detalle-usuario img {
            width: 100%;
            max-width: 150px;
            height: auto;
            border: 3px solid #d4a574;
            margin-bottom: 15px;
        }
        
        .puntuacion-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 3px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .puntuacion-positiva {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #4caf50;
        }
        
        .puntuacion-neutra {
            background-color: #fff3e0;
            color: #e65100;
            border: 1px solid #ff9800;
        }
        
        .puntuacion-negativa {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #f44336;
        }
        
        .sin-votos {
            background-color: #f5f1e8;
            color: #8b7355;
            border: 1px solid #d4a574;
        }
    </style>
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
                    <img src="../uploads/users/default.png" alt="Foto de perfil predeterminada">
                <?php endif; ?>
                
                <h3><?php echo htmlspecialchars($usuario['nombre']); ?></h3>
                
                <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
                
                <?php if (!empty($usuario['fecha_registro'])): ?>
                    <p><strong>Miembro desde:</strong> <?php echo htmlspecialchars($usuario['fecha_registro']); ?></p>
                <?php endif; ?>
                
                <div class="botones-accion">
                    <?php if(isset($_SESSION['id_usuario']) && $usuario['id'] == $_SESSION['id_usuario']): ?>
                        <form action="editarPerfil.php" method="GET" class="form-inline">
                            <button type="submit">Editar Perfil</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <h2>Mis Libros Registrados</h2>
        
        <?php if (count($libros_con_puntuacion) > 0): ?>
            <div class="libros-container">
                <?php foreach ($libros_con_puntuacion as $libro): ?>
                    <div class="libro-card">
                        <?php if (!empty($libro['img'])): 
                            $imagen = "../" . $libro['img'];?>
                            <a href="mostrarLibro.php?isbn=<?php echo htmlspecialchars($libro['isbn']); ?>" 
                                class="libro-link">
                                <img src="<?php echo htmlspecialchars($imagen); ?>" 
                                    alt="Portada de <?php echo htmlspecialchars($libro['titulo']); ?>" 
                                    title="<?php echo htmlspecialchars($libro['titulo']); ?>">
                                <div class="libro-titulo-hover">
                                    <?php echo htmlspecialchars($libro['titulo']); ?>
                                </div>
                            </a>
                        <?php endif; ?>
                        
                        <h3><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                        <p><strong>Autor:</strong> <?php echo htmlspecialchars($libro['autor']); ?></p>
                        
                        <?php if (!empty($libro['visitas'])): ?>
                            <p><strong>Visitas:</strong> <?php echo htmlspecialchars($libro['visitas']); ?></p>
                        <?php endif; ?>
                        
                        <!-- Mostrar puntuación -->
                        <?php 
                        $total_votos = $libro['likes'] + $libro['dislikes'];
                        if ($total_votos > 0):
                            $clase_puntuacion = '';
                            if ($libro['porcentaje'] >= 70) {
                                $clase_puntuacion = 'puntuacion-positiva';
                            } elseif ($libro['porcentaje'] >= 40) {
                                $clase_puntuacion = 'puntuacion-neutra';
                            } else {
                                $clase_puntuacion = 'puntuacion-negativa';
                            }
                        ?>
                            <p>
                                <strong>Puntuación:</strong><br>
                                <span class="puntuacion-badge <?php echo $clase_puntuacion; ?>">
                                    <?php echo $libro['porcentaje']; ?>% positivo
                                </span>
                            </p>
                            <p style="font-size: 0.85em; color: #8b7355;">
                                👍 <?php echo $libro['likes']; ?> | 👎 <?php echo $libro['dislikes']; ?>
                            </p>
                        <?php else: ?>
                            <p>
                                <strong>Puntuación:</strong><br>
                                <span class="puntuacion-badge sin-votos">
                                    Sin votos aún
                                </span>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-libros">
                <h3>Aún no has registrado ningún libro</h3>
                <p>Comienza añadiendo tu primer libro a la biblioteca</p>
                <form action="formularioLibro.php" method="GET" class="form-inline" style="margin-top: 20px;">
                    <button type="submit">Registrar mi primer libro</button>
                </form>
            </div>
        <?php endif; ?>
        
        <form action="cierre.php" method="POST" class="form-inline" style="text-align: center; margin-top: 30px;">
            <input type="submit" name="cerrar" value="Cerrar Sesión">
        </form>
    </div>
</body>
</html>