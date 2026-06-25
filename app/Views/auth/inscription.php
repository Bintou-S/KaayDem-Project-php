<div class="auth-box" style="max-width:520px">
    <h2>📝 Créer un compte</h2>
    <?php if (!empty($erreurs)): ?>
    <ul class="erreurs"><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
    <form action="/kaaydem75/inscription" method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="prenom" value="<?= htmlspecialchars($ancien['prenom'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($ancien['nom'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($ancien['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Téléphone</label>
            <input type="tel" name="telephone" value="<?= htmlspecialchars($ancien['telephone'] ?? '') ?>">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="mot_de_passe" required minlength="8">
            </div>
            <div class="form-group">
                <label>Confirmer</label>
                <input type="password" name="mot_de_passe_confirm" required minlength="8">
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Créer mon compte</button>
    </form>
    <p style="text-align:center;margin-top:1rem;font-size:.9rem">
        Déjà un compte ? <a href="/kaaydem75/connexion">Se connecter</a>
    </p>
</div>