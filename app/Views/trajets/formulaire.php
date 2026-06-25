<h1 class="page-title"><?= isset($trajet) ? 'Modifier le trajet' : 'Nouveau trajet' ?></h1>

<?php if (!empty($erreurs)): ?>
<ul class="erreurs"><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
<?php endif; ?>

<div class="card">
<form action="/kaaydem75/trajets/<?= isset($trajet) ? $trajet->getId() . '/modifier' : 'nouveau' ?>" method="POST">
    <div class="form-row">
        <div class="form-group">
            <label>Ville de départ</label>
            <input type="text" name="ville_depart" value="<?= htmlspecialchars($ancien['ville_depart'] ?? (isset($trajet) ? $trajet->getVilleDepart() : '')) ?>" required placeholder="Ex: Dakar">
        </div>
        <div class="form-group">
            <label>Ville d'arrivée</label>
            <input type="text" name="ville_arrivee" value="<?= htmlspecialchars($ancien['ville_arrivee'] ?? (isset($trajet) ? $trajet->getVilleArrivee() : '')) ?>" required placeholder="Ex: Thiès">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Date et heure de départ</label>
            <input type="datetime-local" name="date_heure_depart"
                value="<?= isset($trajet) ? $trajet->getDateHeureDepart()->format('Y-m-d\TH:i') : ($ancien['date_heure_depart'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Nombre de places</label>
            <input type="number" name="nb_places" value="<?= htmlspecialchars($ancien['nb_places'] ?? (isset($trajet) ? $trajet->getNbPlacesTotal() : 3)) ?>" min="1" max="8" required>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Prix par place (FCFA)</label>
            <input type="number" name="prix_par_place" value="<?= htmlspecialchars($ancien['prix_par_place'] ?? (isset($trajet) ? $trajet->getPrixParPlace() : '')) ?>" min="0" step="100" required>
        </div>
        <?php if (!isset($trajet)): ?>
        <div class="form-group">
            <label>Arrêts intermédiaires (séparés par des virgules)</label>
            <input type="text" name="arrets" value="<?= htmlspecialchars($ancien['arrets'] ?? '') ?>" placeholder="Ex: Rufisque, Bargny">
        </div>
        <?php endif; ?>
    </div>
    <div style="display:flex;gap:1rem">
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="/kaaydem75/dashboard/conducteur" class="btn btn-secondary">Annuler</a>
    </div>
</form>
</div>
