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

// Verificar si se encontró el libro
if (!$libro) {
    die("Libro no encontrado");
}

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

// Obtener votos del libro
$sqlVotos = "SELECT 
                SUM(CASE WHEN clasificacion = 1 THEN 1 ELSE 0 END) as likes,
                SUM(CASE WHEN clasificacion = 0 THEN 1 ELSE 0 END) as dislikes
             FROM clasificacion 
             WHERE id_libro = ?";
$stmtVotos = $conn->prepare($sqlVotos);
$stmtVotos->execute([$libro['id']]);
$votos = $stmtVotos->fetch(PDO::FETCH_ASSOC);

$likes = $votos['likes'] ?? 0;
$dislikes = $votos['dislikes'] ?? 0;
$total = $likes + $dislikes;
$porcentaje_likes = $total > 0 ? round(($likes / $total) * 100) : 0;

// Verificar si el usuario ya votó
$usuario_voto = null;
if (isset($_SESSION['id_usuario'])) {
    $sqlUserVote = "SELECT clasificacion FROM clasificacion WHERE id_libro = ? AND id_usuario = ?";
    $stmtUserVote = $conn->prepare($sqlUserVote);
    $stmtUserVote->execute([$libro['id'], $_SESSION['id_usuario']]);
    $voto = $stmtUserVote->fetch(PDO::FETCH_ASSOC);
    $usuario_voto = $voto ? $voto['clasificacion'] : null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca - <?php echo htmlspecialchars($libro['titulo']); ?></title>
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .votacion-container {
            margin: 25px 0;
            padding: 25px;
            background: #faf8f5;
            border: 2px solid #d4a574;
            text-align: center;
        }
        
        .votacion-botones {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin: 20px 0;
        }
        
        .btn-voto {
            background-color: #fefdfb;
            color: #2c1810;
            padding: 15px 30px;
            border: 2px solid #d4a574;
            font-size: 1.5em;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
        }
        
        .btn-voto:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(139, 90, 43, 0.3);
        }
        
        .btn-voto.like {
            border-color: #4a7c59;
        }
        
        .btn-voto.like:hover {
            background-color: #4a7c59;
            color: #fefdfb;
        }
        
        .btn-voto.like.active {
            background-color: #4a7c59;
            color: #fefdfb;
            font-weight: bold;
        }
        
        .btn-voto.dislike {
            border-color: #c9302c;
        }
        
        .btn-voto.dislike:hover {
            background-color: #c9302c;
            color: #fefdfb;
        }
        
        .btn-voto.dislike.active {
            background-color: #c9302c;
            color: #fefdfb;
            font-weight: bold;
        }
        
        .votacion-stats {
            margin-top: 20px;
            padding: 15px;
            background: #fefdfb;
            border: 1px solid #d4a574;
        }
        
        .barra-progreso {
            width: 100%;
            height: 30px;
            background-color: #f5f1e8;
            border: 2px solid #d4a574;
            margin: 15px 0;
            position: relative;
            overflow: hidden;
        }
        
        .barra-like {
            height: 100%;
            background: linear-gradient(90deg, #4a7c59, #5a9c69);
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fefdfb;
            font-weight: bold;
        }
        
        .votacion-numeros {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            color: #6b4423;
            font-weight: 600;
        }
        
        .mensaje-voto {
            margin-top: 15px;
            padding: 10px;
            display: none;
            font-weight: 500;
        }
        
        .mensaje-voto.success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 2px solid #4caf50;
        }
        
        .mensaje-voto.error {
            background-color: #ffebee;
            color: #c62828;
            border: 2px solid #f44336;
        }
    </style>
    <script>
        let userVote = <?php echo $usuario_voto !== null ? $usuario_voto : 'null'; ?>;
        const libroId = <?php echo $libro['id']; ?>;
        
        function votar(tipo) {
            <?php if (!isset($_SESSION['id_usuario'])): ?>
                showMessage('Debes iniciar sesión para votar', 'error');
                return;
            <?php endif; ?>
            
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var response = JSON.parse(this.responseText);
                    if (response.success) {
                        userVote = tipo;
                        actualizarBotones();
                        actualizarEstadisticas(response.likes, response.dislikes);
                        showMessage(response.message, 'success');
                    } else {
                        showMessage(response.message, 'error');
                    }
                }
            };
            xmlhttp.open("GET", "votos.php?id_libro=" + libroId + "&voto=" + tipo, true);
            xmlhttp.send();
        }
        
        function actualizarBotones() {
            var btnLike = document.getElementById('btn-like');
            var btnDislike = document.getElementById('btn-dislike');
            
            btnLike.classList.remove('active');
            btnDislike.classList.remove('active');
            
            if (userVote === 1) {
                btnLike.classList.add('active');
            } else if (userVote === 0) {
                btnDislike.classList.add('active');
            }
        }
        
        function actualizarEstadisticas(likes, dislikes) {
            var total = likes + dislikes;
            var porcentaje = total > 0 ? Math.round((likes / total) * 100) : 0;
            
            document.getElementById('total-likes').textContent = likes;
            document.getElementById('total-dislikes').textContent = dislikes;
            document.getElementById('porcentaje-likes').textContent = porcentaje + '%';
            document.getElementById('barra-like').style.width = porcentaje + '%';
            
            if (porcentaje > 0) {
                document.getElementById('barra-like').textContent = porcentaje + '%';
            } else {
                document.getElementById('barra-like').textContent = '';
            }
        }
        
        function showMessage(text, type) {
            var messageDiv = document.getElementById('mensaje-voto');
            messageDiv.textContent = text;
            messageDiv.className = 'mensaje-voto ' + type;
            messageDiv.style.display = 'block';
            
            setTimeout(function() {
                messageDiv.style.display = 'none';
            }, 3000);
        }
        
        window.onload = function() {
            actualizarBotones();
        };
    </script>
</head>
<body> 
    <div class="container">
        <div class="nav-superior">
            <a href="biblioteca.php">← Volver a la Biblioteca</a>
            
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

                <?php if (!empty($libro['visitas'])): ?>
                    <p><strong>Visitas:</strong> <?php echo htmlspecialchars($libro['visitas']); ?></p>
                <?php endif; ?>
                
                <!-- Sistema de votación Like/Dislike -->
                <div class="votacion-container">
                    <h4>¿Te gustó este libro?</h4>
                    
                    <div class="votacion-botones">
                        <button id="btn-like" class="btn-voto like <?php echo $usuario_voto === 1 ? 'active' : ''; ?>" 
                                onclick="votar(1)">
                            👍 Me gusta
                        </button>
                        <button id="btn-dislike" class="btn-voto dislike <?php echo $usuario_voto === 0 ? 'active' : ''; ?>" 
                                onclick="votar(0)">
                            👎 No me gusta
                        </button>
                    </div>
                    
                    <div class="votacion-stats">
                        <div class="barra-progreso">
                            <div id="barra-like" class="barra-like" style="width: <?php echo $porcentaje_likes; ?>%;">
                                <?php if ($porcentaje_likes > 0): ?>
                                    <?php echo $porcentaje_likes; ?>%
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="votacion-numeros">
                            <span>👍 <strong id="total-likes"><?php echo $likes; ?></strong> Me gusta</span>
                            <span id="porcentaje-likes" style="color: #8b5a2b; font-size: 1.2em;"><?php echo $porcentaje_likes; ?>%</span>
                            <span>👎 <strong id="total-dislikes"><?php echo $dislikes; ?></strong> No me gusta</span>
                        </div>
                    </div>
                    
                    <div id="mensaje-voto" class="mensaje-voto"></div>
                </div>
                
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
                    
                    <?php if(isset($_SESSION['id_usuario']) && $libro['id_usuario'] == $_SESSION['id_usuario']): ?>
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