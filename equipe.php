<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id FROM shops WHERE user_id = ?");
$stmt->execute([$user_id]);
$shop_id = $stmt->fetch()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_vendeur = htmlspecialchars($_POST['email']);
    $pass_vendeur = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role']; // "Vendeur" ou "Gestionnaire"

    // On crée l'utilisateur et on le lie à la boutique
    $stmt = $pdo->prepare("INSERT INTO users (email, password, role, shop_id) VALUES (?, ?, ?, ?)");
    if($stmt->execute([$email_vendeur, $pass_vendeur, $role, $shop_id])) {
        $success = "Membre ajouté avec succès !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Équipe - Sunuboutique</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .form-row { display: flex; gap: 10px; margin-top: 20px; }
        input, select, button { padding: 10px; border-radius: 5px; border: 1px solid #ddd; }
        button { background: #1c2431; color: white; cursor: pointer; border: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Équipe & Gestionnaires</h2>
        <p>Gérez les accès à votre boutique.</p>

        <div style="background: #eef4ff; padding: 15px; border-radius: 8px;">
            <strong>PROPRIÉTAIRE :</strong> Vous (ID: <?php echo $_SESSION['user_id']; ?>)
        </div>

        <form method="POST" class="form-row">
            <input type="email" name="email" placeholder="Email du vendeur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <select name="role">
                <option value="Vendeur">Vendeur</option>
                <option value="Gestionnaire">Gestionnaire</option>
            </select>
            <button type="submit">Créer & Ajouter</button>
        </form>

        <h3 style="margin-top:40px;">Membres actuels</h3>
        <p style="color: #888;">Aucun membre supplémentaire pour le moment.</p>
        
        <p><a href="dashboard.php">⬅ Retour au Dashboard</a></p>
    </div>
</body>
</html>