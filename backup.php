<?php
session_start();
require_once 'config.php';

// Sécurité : Seul le propriétaire peut sauvegarder
if (!isset($_SESSION['user_id'])) { exit("Accès refusé"); }

$tables = array();
$result = $pdo->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

$return = "";

// Parcourir toutes les tables pour extraire les données
foreach ($tables as $table) {
    $result = $pdo->query("SELECT * FROM $table");
    $num_fields = $result->columnCount();

    $return .= "DROP TABLE IF EXISTS $table;";
    $row2 = $pdo->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_NUM);
    $return .= "\n\n" . $row2[1] . ";\n\n";

    for ($i = 0; $i < $num_fields; $i++) {
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $return .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $num_fields; $j++) {
                $row[$j] = addslashes($row[$j]);
                if (isset($row[$j])) { $return .= '"' . $row[$j] . '"'; } else { $return .= '""'; }
                if ($j < ($num_fields - 1)) { $return .= ','; }
            }
            $return .= ");\n";
        }
    }
    $return .= "\n\n\n";
}

// Générer le fichier pour le téléchargement
$filename = 'backup_sunuboutique_' . date('d-m-Y') . '.sql';
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $filename);
echo $return;
exit;
?>