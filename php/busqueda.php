<?php
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo "<div class='no-libros'><p>Sesión no válida</p></div>";
    exit();
}

$conn = require("conection.php");

// Obtener el término de búsqueda
$q = $_GET["q"] ?? '';

// Si no hay término de búsqueda, no devolver nada
if (empty($q)) {
    exit();
}

// Preparar la búsqueda con LIKE para buscar en título, autor y editorial
$search = "%{$q}%";
$sql = "SELECT * FROM libros 
        WHERE titulo LIKE :search 
        OR autor LIKE :search 
        ORDER BY titulo";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':search', $search, PDO::PARAM_STR);
$stmt->execute();
$libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mostrar los resultados
if (count($libros) > 0): ?>
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
        <p>No se encontraron libros que coincidan con "<?php echo htmlspecialchars($q); ?>"</p>
    </div>
<?php endif; ?>