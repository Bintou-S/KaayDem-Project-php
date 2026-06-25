<h1 class="page-title">🔍 Rechercher un trajet</h1>

<div class="search-bar">
    <form action="/kaaydem75/trajets/recherche" method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;width:100%;align-items:flex-end">
        <div class="form-group">
            <label>Départ</label>
            <input type="text" name="depart" value="<?= htmlspecialchars($criteres['villeDepart'] ?? '') ?>" placeholder="Ex: Dakar">
        </div>
        <div class="form-group">
            <label>Arrivée</label>
            <input type="text" name="arrivee" value="<?= htmlspecialchars($criteres['villeArrivee'] ?? '') ?>" placeholder="Ex: Thiès">
        </div>
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($criteres['date'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Prix max (FCFA)</label>
            <input type="number" name="prix_max" value="<?= htmlspecialchars($criteres['prixMax'] ?? '') ?>" min="0">
        </div>
        <div class="form-group">
            <label>Places min</label>
            <input type="number" name="places_min" value="<?= htmlspecialchars($criteres['placesMin'] ?? '') ?>" min="1" max="8">
        </div>
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>
</div>

<p style="margin-bottom:1rem;color:#666"><?= $total ?> trajet(s) trouvé(s)</p>

<?php if (empty($trajets)): ?>
<div class="card" style="text-align:center;padding:3rem">
    <p style="color:#888">Aucun trajet ne correspond à votre recherche.</p>
    <a href="/kaaydem75/trajets" class="btn btn-primary" style="margin-top:1rem">Voir tous les trajets</a>
</div>
<?php else: ?>
<?php foreach ($trajets as $trajet): ?>
<div class="trajet-card">
    <div class="trajet-route">
        <div class="villes">
            <?= htmlspecialchars($trajet->getVilleDepart()) ?> <span>→</span> <?= htmlspecialchars($trajet->getVilleArrivee()) ?>
        </div>
        <div class="details">
            📅 <?= $trajet->getDateHeureDepart()->format('d/m/Y à H\hi') ?>
            &nbsp;·&nbsp; 👤 <?= htmlspecialchars($trajet->getConducteur()->getNomComplet()) ?>
        </div>
    </div>
    <div class="trajet-info">
        <div class="trajet-prix"><?= number_format($trajet->getPrixParPlace(), 0, ',', ' ') ?> FCFA</div>
        <div class="trajet-places"><?= $trajet->getNbPlacesDispo() ?> place(s)</div>
    </div>
    <a href="/kaaydem75/trajets/<?= $trajet->getId() ?>" class="btn btn-primary btn-sm">Réserver</a>
</div>
<?php endforeach; ?>

<?php if ($total > 10): ?>
<div style="display:flex;gap:.5rem;justify-content:center;margin-top:1.5rem">
    <?php for ($i = 1; $i <= ceil($total/10); $i++): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
       class="btn btn-sm <?= $i == $page ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>
