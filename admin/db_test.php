<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre style='font-family:monospace; padding:20px; background:#f4f4f4;'>";
echo "1. Script arrancó correctamente.\n";

echo "2. Intentando cargar config/database.php...\n";
require_once __DIR__ . '/../config/database.php';
echo "   OK, el archivo cargó sin errores de sintaxis.\n";

echo "3. Intentando conectar a MySQL...\n";
try {
    $pdo = Database::getConnection();
    echo "   OK CONEXION EXITOSA.\n";

    echo "4. Probando una consulta real...\n";
    $count = $pdo->query('SELECT COUNT(*) FROM zones')->fetchColumn();
    echo "   OK Zonas encontradas en la tabla: $count\n";

} catch (Throwable $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . " (linea " . $e->getLine() . ")\n";
}

echo "</pre>";
