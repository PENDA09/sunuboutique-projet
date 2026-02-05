<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$user_id = $_SESSION['user_id'];

// Récupérer l'ID de la boutique
$stmt = $pdo->prepare("SELECT id FROM shops WHERE user_id = ?");
$stmt->execute([$user_id]);
$shop = $stmt->fetch();
$shop_id = $shop['id'];

// Traitement de la vente
if (isset($_POST['vendre'])) {
    $product_id = $_POST['product_id'];
    $quantite = 1; // On peut améliorer cela plus tard avec un sélecteur
    $prix = $_POST['prix_vente'];

    try {
        $pdo->beginTransaction();

        // 1. Enregistrer la vente
        $stmtVente = $pdo->prepare("INSERT INTO sales (shop_id, total_amount, mode_paiement) VALUES (?, ?, 'especes')");
        $stmtVente->execute([$shop_id, $prix]);

        // 2. Diminuer le stock (La perfection : on ne vend pas si stock = 0)
        $stmtStock = $pdo->prepare("UPDATE products SET stock_actuel = stock_actuel - ? WHERE id = ? AND stock_actuel > 0");
        $stmtStock->execute([$quantite, $product_id]);

        if ($stmtStock->rowCount() > 0) {
            $pdo->commit();
            $success = "Vente enregistrée !";
        } else {
            $pdo->rollBack();
            $error = "Stock insuffisant !";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Erreur : " . $e->getMessage();
    }
}

// Liste des produits disponibles
$stmtProd = $pdo->prepare("SELECT * FROM products WHERE shop_id = ? AND stock_actuel > 0");
$stmtProd->execute([$shop_id]);
$produits = $stmtProd->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vendre - Sunuboutique</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; padding: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
        .product-card { background: white; padding: 15px; border-radius: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .product-card img { max-width: 100%; height: 120px; object-fit: cover; border-radius: 5px; }
        .btn-vendre { background: #28a745; color: white; border: none; padding: 10px; width: 100%; border-radius: 5px; cursor: pointer; margin-top: 10px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
        .success { background: #d4edda; color: #155724; }
        .danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

    <h2>Point de Vente - <?php echo $_SESSION['user_name']; ?></h2>
    
    <?php if(isset($success)) echo "<div class='alert success'>$success</div>"; ?>
    <?php if(isset($error)) echo "<div class='alert danger'>$error</div>"; ?>

    <div class="grid">
        <?php foreach($produits as $p): ?>
        <div class="product-card">
            <img src="<?php echo $p['image_url']; ?>" alt="">
            <h4><?php echo $p['nom_produit']; ?></h4>
            <p><strong><?php echo $p['prix_vente']; ?> FCFA</strong></p>
            <p><small>Stock: <?php echo $p['stock_actuel']; ?></small></p>
            
            <form method="POST">
                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                <input type="hidden" name="prix_vente" value="<?php echo $p['prix_vente']; ?>">
                <button type="submit" name="vendre" class="btn-vendre">Vendre</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>

    <p style="margin-top:30px;"><a href="dashboard.php">⬅ Retour au Tableau de Bord</a></p>

</body>
</html>