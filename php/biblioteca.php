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

// Obtener foto de perfil del usuario
$foto_perfil = isset($_SESSION['foto_perfil']) ? $_SESSION['foto_perfil'] : '../uploads/users/default.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca</title>
    <link rel="stylesheet" href="../style/style.css">
    <script>
        function showResult(str) {
            if (str.length == 0) {
                // Si el campo está vacío, mostrar todos los libros
                document.getElementById("livesearch").innerHTML = document.getElementById("todos-libros").innerHTML;
                return;
            }
            
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("livesearch").innerHTML = this.responseText;
                }
            }
            xmlhttp.open("GET", "livesearch.php?q=" + str, true);
            xmlhttp.send();
        }

        function toggleUserMenu() {
            var menu = document.getElementById("user-menu");
            menu.style.display = (menu.style.display === "block") ? "none" : "block";
        }

        // Cerrar el menú si se hace clic fuera de él
        window.onclick = function(event) {
            if (!event.target.matches('.user-avatar') && !event.target.matches('.user-profile')) {
                var menu = document.getElementById("user-menu");
                if (menu.style.display === "block") {
                    menu.style.display = "none";
                }
            }
        }
    </script>
</head>
<body>
    <!-- Barra de usuario superior -->
    <div class="top-bar">
        <div class=" container user-profile" onclick="toggleUserMenu()">
            <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil" class="user-avatar" width='30'>
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            <span class="menu-arrow">▼</span>
        </div>
        
        <div id="user-menu" class=" container user-menu" style="display: none;">
            
            <form action="cierre.php" method="POST" style="margin: 0;">
                <a href="editarPerfil.php" class="menu-item">
                    <span class="menu-icon"></span> Editar Perfil
                </a>
                <button type="submit" name="cerrar" class="menu-item ">
                    <span class="menu-icon"></span> Cerrar Sesión
                </button>
            </form>
        </div>
    </div>

    <div class="container">
        <h1>Biblioteca Online</h1>
        
        <?php if(isset($_SESSION['nombre'])): ?>
            <form action="formularioLibro.php" method="GET" class="form-inline">
                <button type="submit">Registrar un nuevo libro</button>
            </form>
        <?php endif; ?>
        
        <hr>
        
        <h2>Catálogo de Libros</h2>
        <input type="text" size="30" onkeyup="showResult(this.value)" placeholder="Buscar libro...">
        
        <div id="livesearch">
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
        </div>
        
        <!-- Contenedor oculto con todos los libros para restaurar la vista -->
        <div id="todos-libros" style="display: none;">
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
            <?php endif; ?>
        </div>
    </div>
</body>
</html>