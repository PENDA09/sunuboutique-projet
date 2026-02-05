<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $boutique = htmlspecialchars($_POST['nom_boutique']);

    try {
        $pdo->beginTransaction();

        // 1. Créer l'utilisateur
        $stmt = $pdo->prepare("INSERT INTO users (nom_complet, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $email, $pass]);
        $user_id = $pdo->lastInsertId();

        // 2. Créer sa boutique par défaut
        $slug = strtolower(str_replace(' ', '-', $boutique));
        $stmtB = $pdo->prepare("INSERT INTO shops (user_id, nom_boutique, slug) VALUES (?, ?, ?)");
        $stmtB->execute([$user_id, $boutique, $slug]);

        $pdo->commit();
        echo "Compte et boutique créés ! <a href='login.php'>Connectez-vous ici</a>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<form method="POST">
    <h2>Créer mon compte Sunuboutique</h2>
    <input type="text" name="nom" placeholder="Nom Complet" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Mot de passe" required><br>
    <input type="text" name="nom_boutique" placeholder="Nom de votre Boutique" required><br>
    <button type="submit">Lancer ma boutique</button>
</form>