<?php
// ... code de session et config existant ...

$message_coupon = "";
$reduction = 0;

if (isset($_POST['appliquer_promo'])) {
    $code_saisi = htmlspecialchars($_POST['coupon_code']);
    
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND actif = TRUE AND (date_expiration >= CURDATE() OR date_expiration IS NULL)");
    $stmt->execute([$code_saisi]);
    $coupon = $stmt->fetch();

    if ($coupon) {
        $reduction = $coupon['reduction_pourcentage'];
        $message_coupon = "<span style='color: #2ecc71;'>Code appliqué ! Vous bénéficiez de -$reduction% sur votre abonnement.</span>";
    } else {
        $message_coupon = "<span style='color: #e74c3c;'>Code promo invalide ou expiré.</span>";
    }
}
?>

<div style="background: white; padding: 20px; border-radius: 10px; max-width: 600px; margin: 20px auto; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
<div style="background: white; padding: 20px; border-radius: 10px; max-width: 600px; margin: 20px auto; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
    <h4 style="margin-top:0;">Vous avez un code promo ?</h4>
    <form method="POST" style="display: flex; gap: 10px;">
        <input type="text" name="coupon_code" placeholder="Entrez votre code" required 
               style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        <button type="submit" name="appliquer_promo" 
                style="background: #1c2431; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
            Appliquer
        </button>
    </form>
    <p style="font-size: 0.9em; margin-top: 10px;"><?php echo $message_coupon; ?></p>
</div><?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$user_id = $_SESSION['user_id'];

// Récupérer le plan actuel (on suppose une colonne 'plan' dans votre table users)
$stmt = $pdo->prepare("SELECT plan, date_expiration FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$plan = $user['plan'] ?? 'Gratuit';
$expiration = $user['date_expiration'] ?? '18 février 2026'; // Date d'exemple
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Abonnement - Sunuboutique</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; text-align: center; }
        .promo-card { background: #3b33b5; color: white; padding: 30px; border-radius: 15px; max-width: 600px; margin: auto; }
        .plans { display: flex; gap: 20px; justify-content: center; margin-top: 30px; flex-wrap: wrap; }
        .plan-card { background: white; padding: 20px; border-radius: 10px; width: 250px; border: 2px solid #eee; }
        .plan-pro { border-color: #9b51e0; position: relative; }
        .badge { background: #9b51e0; color: white; padding: 5px; position: absolute; top: -10px; right: 10px; border-radius: 5px; font-size: 0.8em; }
        .price { font-size: 24px; font-weight: bold; margin: 10px 0; }
        .btn-sub { background: #1c2431; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; }
    </style>
</head>
<body>

    <div class="promo-card">
        <h2>✅ Abonnement <?php echo $plan; ?></h2>
        <p>Il vous reste environ 14 jours.</p>
        <p><strong>DATE D'ÉCHÉANCE :</strong> <?php echo $expiration; ?></p>
    </div>

    <div class="plans">
        <div class="plan-card">
            <h3>STANDARD</h3>
            <p class="price">3 000 <small>FCFA/mois</small></p>
            <ul style="text-align:left; font-size: 0.9em;">
                <li>⚠️ Limite 20 produits</li>
                <li>✅ 1 boutique</li>
                <li>✅ Ventes & Factures</li>
            </ul>
            <button class="btn-sub">S'abonner</button>
        </div>

        <div class="plan-card plan-pro">
            <div class="badge">POPULAIRE</div>
            <h3 style="color:#9b51e0;">BUSINESS PRO</h3>
            <p class="price">5 000 <small>FCFA/mois</small></p>
            <ul style="text-align:left; font-size: 0.9em;">
                <li>🚀 Produits illimités</li>
                <li>✅ 3 boutiques</li>
                <li>✅ Commandes en ligne</li>
            </ul>
            <button class="btn-sub" style="background:#9b51e0;">S'abonner Pro</button>
        </div>
    </div>

</body>
</html>