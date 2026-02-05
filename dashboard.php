<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit(); 
}
// 5. Calcul des dépenses totales du mois
$stmtDep = $pdo->prepare("SELECT SUM(montant) as total_dep FROM expenses WHERE shop_id = ? AND MONTH(date_depense) = MONTH(CURDATE())");
$stmtDep->execute([$shop_id]);
$total_depenses = $stmtDep->fetch()['total_dep'] ?? 0;

// 6. Bénéfice Net (CA Mois - Dépenses Mois)
$benefice_net = $ca_mois - $total_depenses;
$user_id = $_SESSION['user_id'];

// 1. Récupérer les infos de la boutique
$stmt = $pdo->prepare("SELECT * FROM shops WHERE user_id = ?");
$stmt->execute([$user_id]);
$shop = $stmt->fetch();
$shop_id = $shop['id'];

// 2. STATISTIQUES RÉELLES
// Chiffre d'Affaires du Jour
$stmtCA = $pdo->prepare("SELECT SUM(total_amount) as total_jour FROM sales WHERE shop_id = ? AND DATE(date_vente) = CURDATE()");
$stmtCA->execute([$shop_id]);
$ca_jour = $stmtCA->fetch()['total_jour'] ?? 0;

// Chiffre d'Affaires du Mois
$stmtMois = $pdo->prepare("SELECT SUM(total_amount) as total_mois FROM sales WHERE shop_id = ? AND MONTH(date_vente) = MONTH(CURDATE()) AND YEAR(date_vente) = YEAR(CURDATE())");
$stmtMois->execute([$shop_id]);
$ca_mois = $stmtMois->fetch()['total_mois'] ?? 0;

// Valeur totale du stock
$stmtStock = $pdo->prepare("SELECT SUM(prix_vente * stock_actuel) as valeur_stock FROM products WHERE shop_id = ?");
$stmtStock->execute([$shop_id]);
$valeur_stock = $stmtStock->fetch()['valeur_stock'] ?? 0;

// 3. RÉCUPÉRER LES PRODUITS
$stmtProd = $pdo->prepare("SELECT * FROM products WHERE shop_id = ?");
$stmtProd->execute([$shop_id]);
$produits = $stmtProd->fetchAll();

// 4. LIEN DE LA VITRINE
$url_vitrine = "https://" . $_SERVER['HTTP_HOST'] . "/boutique.php?s=" . $shop['slug'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunuboutique - Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .header { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .share-box { background: #e8f0fe; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px dashed #1a73e8; }
        .stats { display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap; }
        .card { background: #fff; padding: 20px; flex: 1; min-width: 200px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .card h3 { color: #555; font-size: 0.9em; margin-bottom: 10px; }
        .card .price { font-size: 22px; font-weight: bold; color: #2ecc71; }
        .btn { padding: 10px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; }
        .btn-add { background: #1a73e8; color: white; }
        .btn-sell { background: #2ecc71; color: white; }
        .btn-logout { background: #e74c3c; color: white; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <h1 style="margin:0;">Bienvenue chez **<?php echo $shop['nom_boutique']; ?>**</h1>
            <p style="margin:5px 0 0; color: #666;">Tableau de bord Sunuboutique</p>
        </div>
        <div>
            <a href="vendre.php" class="btn btn-sell">Faire une vente</a>
            <a href="logout.php" class="btn btn-logout">Quitter</a>
        </div>
    </div>

    <div class="share-box">
        <strong>Lien de votre boutique en ligne :</strong><br>
        <code id="link"><?php echo $url_vitrine; ?></code>
        <button onclick="copyLink()" style="margin-left:10px; cursor:pointer;">Copier le lien</button>
        <a href="https://wa.me/?text=Découvrez ma boutique en ligne sur Sunuboutique : <?php echo $url_vitrine; ?>" target="_blank" style="margin-left:10px; color: #25D366; text-decoration:none; font-weight:bold;">Partager sur WhatsApp</a>
    </div>

    <div class="stats">
        <div class="card">
            <h3>Ventes du jour</h3>
            <p class="price"><?php echo number_format($ca_jour, 0, ',', ' '); ?> FCFA</p>
        </div>
       <div class="card">
    <h3>Bénéfice Net (ce mois)</h3>
    <p class="price" style="color: <?php echo ($benefice_net >= 0) ? '#2ecc71' : '#e74c3c'; ?>">
        <?php echo number_format($benefice_net, 0, ',', ' '); ?> FCFA
    </p>
</div>
        <div class="card">
            <h3>Valeur du Stock</h3>
            <p class="price"><?php echo number_format($valeur_stock, 0, ',', ' '); ?> FCFA</p>
        </div>
    </div>

    <div style="margin-top:30px; display: flex; justify-content: space-between; align-items: center;">
        <h2>Mes Produits</h2>
        <a href="ajouter_produit.php" class="btn btn-add">+ Nouveau Produit</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Nom</th>
                <th>Prix de vente</th>
                <th>Stock actuel</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($produits)): ?>
                <tr><td colspan="4" style="text-align:center;">Aucun produit pour le moment.</td></tr>
            <?php else: ?>
                <?php foreach($produits as $p): ?>
                <tr>
                    <td><img src="<?php echo $p['image_url']; ?>" width="50" style="border-radius:5px;"></td>
                    <td><?php echo $p['nom_produit']; ?></td>
                    <td><strong><?php echo number_format($p['prix_vente'], 0, ',', ' '); ?> FCFA</strong></td>
                    <td>
                        <span style="color: <?php echo ($p['stock_actuel'] < 5) ? 'red' : 'inherit'; ?>">
                            <?php echo $p['stock_actuel']; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
    function copyLink() {
        var link = document.getElementById("link").innerText;
        navigator.clipboard.writeText(link);
        alert("Lien copié ! Vous pouvez l'envoyer à vos clients.");
    }
    </script>
<div style="margin-top: 20px; padding: 15px; background: #fffbe6; border: 1px solid #ffe58f; border-radius: 8px;">
    <strong>🛡️ Sécurité des données</strong>
    <p style="font-size: 0.85em; color: #856404;">Téléchargez régulièrement une copie de vos données pour ne jamais rien perdre.</p>
    <a href="backup.php" style="display: inline-block; background: #faad14; color: white; padding: 8px 12px; border-radius: 5px; text-decoration: none; font-weight: bold;">
        📥 Télécharger ma sauvegarde (.sql)
    </a>
</div>
    <div style="margin-top: 20px; padding: 15px; background: #fffbe6; border: 1px solid #ffe58f; border-radius: 8px;">
    <strong>🛡️ Sécurité des données</strong>
    <p style="font-size: 0.85em; color: #856404;">Téléchargez régulièrement une copie de vos données pour ne jamais rien perdre.</p>
    <a href="backup.php" style="display: inline-block; background: #faad14; color: white; padding: 8px 12px; border-radius: 5px; text-decoration: none; font-weight: bold;">
        📥 Télécharger ma sauvegarde (.sql)
    </a>
</div>
    </body>
</html>
