<h1 class="page-title">⭐ Évaluer le conducteur</h1>

<div class="card">
    <p style="margin-bottom:1rem">
        Trajet : <strong><?= htmlspecialchars($reservation->getTrajet()->getVilleDepart()) ?> → <?= htmlspecialchars($reservation->getTrajet()->getVilleArrivee()) ?></strong><br>
        Conducteur : <strong><?= htmlspecialchars($reservation->getTrajet()->getConducteur()->getNomComplet()) ?></strong>
    </p>

    <?php if (!empty($erreurs)): ?>
    <ul class="erreurs"><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>

    <form action="/kaaydem75/evaluations/nouveau/<?= $reservation->getId() ?>" method="POST">
        <div class="form-group">
            <label>Note (1 à 5)</label>
            <div style="display:flex;gap:.5rem">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <label style="cursor:pointer">
                    <input type="radio" name="note" value="<?= $i ?>" style="margin-right:.2rem" required>
                    <?= str_repeat('★', $i) ?>
                </label>
                <?php endfor; ?>
            </div>
        </div>
        <div class="form-group">
            <label>Commentaire</label>
            <textarea name="commentaire" rows="4" required placeholder="Partagez votre expérience..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Envoyer l'évaluation</button>
        <a href="/kaaydem75/dashboard/passager" class="btn btn-secondary" style="margin-left:.5rem">Annuler</a>
    </form>
</div>
