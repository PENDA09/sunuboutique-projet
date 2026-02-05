<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id FROM shops WHERE user_id = ?");
$stmt->execute([$user_id]);
$shop_id = $stmt->fetch()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motif = htmlspecialchars($_POST['motif']);
    $montant = $_POST['montant'];
    $stmt = $pdo->prepare("INSERT INTO expenses (shop_id, motif, montant) VALUES (?, ?, ?)");
    $stmt->execute([$shop_id, $motif, $montant]);
    $success = "Dépense enregistrée !";
}

$stmtExp = $pdo->prepare("SELECT * FROM expenses WHERE shop_id = ? ORDER BY date_depense DESC");
$stmtExp->execute([$shop_id]);
$depenses = $stmtExp->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dépenses - Sunuboutique</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        input, button { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { background: #e74c3c; color: white; border: none; cursor: pointer; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #eee; padding: 10px; text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Enregistrer une dépense</h2>
        <?php if(isset($success)) echo "<p style='color:green'>$success</p>"; ?>
        <form method="POST">
            <input type="text" name="motif" placeholder="Motif (ex: Transport Marchandise)" required>
            <input type="number" name="montant" placeholder="Montant (FCFA)" required>
            <button type="submit">Ajouter la dépense</button>
        </form>

        <h3>Dépenses Récentes</h3>
        <table>
            <tr><th>Date</th><th>Motif</th><th>Montant</th></tr>
            <?php foreach($depenses as $d): ?>
            <tr>
                <td><?php echo date('d/m', strtotime($d['date_depense'])); ?></td>
                <td><?php echo $d['motif']; ?></td>
                <td><?php echo number_format($d['montant'], 0, ',', ' '); ?> F</td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p><a href="dashboard.php">Retour au Dashboard</a></p>
    </div>
</body>
</html>