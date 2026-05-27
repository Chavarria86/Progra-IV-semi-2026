<?php
try {
    $db = new PDO('mysql:host=127.0.0.1;port=3306;dbname=db_institucional', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Limpiando datos de prueba...\n";
    $db->exec("TRUNCATE TABLE curriculum_vitae");
    $db->exec("TRUNCATE TABLE cv");
    $db->exec("TRUNCATE TABLE informes");
    echo "¡Base de datos limpia y lista!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
