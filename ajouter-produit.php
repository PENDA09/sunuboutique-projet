<?php
session_start();
require_once 'config.php';
// Compter les produits existants
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM products WHERE shop_id = ?");
$stmtCount->execute([$shop_id]);
$nb_produits = $stmtCount->fetchColumn();

// Si l'utilisateur est en plan Gratuit/Standard et a déjà 20 produits
if ($plan == 'Standard' && $nb_produits >= 20) {
    die("Limite de produits atteinte ! Passez au plan Business Pro pour continuer.");
}
// Sécurité : Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$user_id = $_SESSION['user_id'];

// Récupérer l'ID de la boutique de l'utilisateur
$stmt = $pdo->prepare("SELECT id FROM shops WHERE user_id = ?");
$stmt->execute([$user_id]);
$shop = $stmt->fetch();
$shop_id = $shop['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom_produit']);
    $prix_vente = $_POST['prix_vente'];
    $stock = $_POST['stock'];
    
    // Gestion de l'image
    $image_name = $_FILES['image']['name'];
    $target_dir = "uploads/";
    
    // Créer le dossier uploads s'il n'existe pas
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $target_file = $target_dir . time() . "_" . basename($image_name);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        // Insertion en base de données
        $sql = "INSERT INTO products (shop_id, nom_produit, prix_vente, stock_actuel, image_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$shop_id, $nom, $prix_vente, $stock, $target_file])) {
            $success = "Produit ajouté avec succès !";
        }
    } else {
        $error = "Erreur lors du téléchargement de l'image.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Produit - Sunuboutique</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 40px; }
        .form-container { background: white; max-width: 500px; margin: auto; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #1a73e8; text-align: center; }
        input, button { width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box; }
        button { background: #1a73e8; color: white; border: none; font-weight: bold; cursor: pointer; }
        button:hover { background: #1557b0; }
        .msg { text-align: center; padding: 10px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Nouveau Produit</h2>
    
    <?php if(isset($success)) echo "<div class='msg success'>$success</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="nom_produit" placeholder="Nom du produit (ex: Sac à main)" required>
        <input type="number" name="prix_vente" placeholder="Prix de vente (FCFA)" required>
        <input type="number" name="stock" placeholder="Quantité en stock" required>
        <label>Image du produit :</label>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Enregistrer le produit</button>
    </form>
    <p style="text-align:center;"><a href="dashboard.php">Retour au tableau de bord</a></p>
</div>

</body>
</html>