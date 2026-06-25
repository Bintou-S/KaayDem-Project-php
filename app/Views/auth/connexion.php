<div class="auth-box">
    <img src="/kaaydem75/public/img/logo.jpeg" alt="Kaay Deem" class="auth-logo">
    <h2>Connexion</h2>
    <p class="subtitle">Bienvenue ! Connectez-vous à votre compte.</p>

    <?php if (!empty($erreurs)): ?>
    <ul class="erreurs"><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>

    <form action="/kaaydem75/connexion" method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="votre@email.com" required>
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="mot_de_passe" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:.5rem">Se connecter</button>
    </form>

    <p style="text-align:center;margin-top:1.25rem;font-size:.875rem;color:var(--text-soft)">
        Pas encore de compte ? <a href="/kaaydem75/inscription" style="color:var(--navy);font-weight:600">S'inscrire</a>
    </p>
    
</div>
