
<?php
/**
 * Script para limpiar tokens expirados
 * Ejecutar periódicamente (por ejemplo, diariamente con cron job)
 * Ejemplo cron: 0 3 * * * /usr/bin/php /ruta/a/limpiar_tokens_expirados.php
 */

$conn = require("conection.php");

try {
    // Eliminar tokens que hayan expirado
    $stmt = $conn->prepare("DELETE FROM tokens WHERE fecha_expiracion < NOW()");
    $stmt->execute();
    
    $eliminados = $stmt->rowCount();
    
    echo "Limpieza completada. Tokens eliminados: " . $eliminados . "\n";
    
} catch(PDOException $e) {
    echo "Error al limpiar tokens: " . $e->getMessage() . "\n";
}

$conn = null;
?>