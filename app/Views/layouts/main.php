<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titre ?? 'Kaay Deem') ?> — Kaay Deem</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/kaaydem75/public/css/style.css">
</head>
<body>

<nav class="navbar">
    <a class="navbar-brand" href="/kaaydem75/">
        <img src="/kaaydem75/public/img/logo.jpeg" alt="Kaay Deem">
    </a>
    <div class="navbar-links">
        <a href="/kaaydem75/trajets">Trajets</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="/kaaydem75/dashboard">Dashboard</a>
            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <a href="/kaaydem75/admin">Admin</a>
            <?php endif; ?>
            <span class="user-name">👤 <?= htmlspecialchars($_SESSION['user_nom'] ?? '') ?></span>
            <a href="/kaaydem75/deconnexion" class="btn btn-outline btn-sm">Déconnexion</a>
        <?php else: ?>
            <a href="/kaaydem75/connexion">Connexion</a>
            <a href="/kaaydem75/inscription" class="btn btn-outline btn-sm">S'inscrire</a>
        <?php endif; ?>
    </div>
</nav>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'info' ? 'info' : 'error') ?>">
    <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>

<div class="container">
    <?= $content ?>
</div>

<footer class="footer">
    <p>Kaay Deem — Transport Étudiant &copy; <?= date('Y') ?> · Sécurité · Ponctualité · Confort</p>
</footer>

<script src="/kaaydem75/public/js/app.js"></script>
</body>
</html>