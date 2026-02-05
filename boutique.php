<?php
require_once 'config.php';

// On récupère la boutique via le "slug" dans l'URL (ex: boutique.php?s=soumaya-shop)
if (!isset($_GET['s'])) { die("Boutique non trouvée."); }
$slug = htmlspecialchars($_GET['s']);

// Récupérer les infos de la boutique et du vendeur
$stmt = $pdo->prepare("SELECT shops.*, users.telephone, users.nom_complet FROM shops 
                       JOIN users ON shops.user_id = users.id 
                       WHERE shops.slug = ?");
$stmt->execute([$slug]);
$shop = $stmt->fetch();

if (!$shop) { die("Cette boutique n'existe pas."); }

// Récupérer les produits
$stmtP = $pdo->prepare("SELECT * FROM products WHERE shop_id = ? AND stock_actuel > 0");
$stmtP->execute([$shop['id']]);
$produits = $stmtP->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $shop['nom_boutique']; ?> - Sunuboutique</title>
    <style>
        body { font-family: 'Poppins', sans-serif; margin: 0; background: #f4f4f9; }
        .banner { background: #1a73e8; color: white; padding: 40px 20px; text-align: center; }
        .container { max-width: 1000px; margin: 20px auto; padding: 10px; display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px; }
        .product-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; padding-bottom: 10px; }
        .product-card img { width: 100%; height: 150px; object-fit: cover; }
        .price { color: #2ecc71; font-weight: bold; font-size: 1.1em; }
        .btn-wa { background: #25D366; color: white; text-decoration: none; padding: 8px 12px; border-radius: 20px; display: inline-block; font-size: 0.9em; margin-top: 5px; }
    </style>
</head>
<body>

<div class="banner">
    <h1><?php echo $shop['nom_boutique']; ?></h1>
    <p>Bienvenue dans ma boutique en ligne</p>
</div>

<div class="container">
    <?php foreach($produits as $p): 
        // Préparation du message WhatsApp
        $message = "Bonjour " . $shop['nom_boutique'] . ", je souhaite commander l'article : " . $p['nom_produit'] . " au prix de " . $p['prix_vente'] . " FCFA.";
        $wa_link = "https://wa.me/" . $shop['telephone'] . "?text=" . urlencode($message);
    ?>
    <div class="product-card">
        <img src="<?php echo $p['image_url']; ?>" alt="">
        <h4><?php echo $p['nom_produit']; ?></h4>
        <p class="price"><?php echo number_format($p['prix_vente'], 0, '.', ' '); ?> FCFA</p>
        <a href="<?php echo $wa_link; ?>" target="_blank" class="btn-wa">🛒 Commander via WhatsApp</a>
    </div>
    <?php endforeach; ?>
</div>

<footer style="text-align: center; padding: 20px; color: #888;">
    Propulsé par **Sunuboutique**
</footer>

</body>
</html>