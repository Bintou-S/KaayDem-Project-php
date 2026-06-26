<div style="margin-bottom:1rem"><a href="/kaaydem75/trajets">← Retour aux trajets</a></div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <?= htmlspecialchars($trajet->getVilleDepart()) ?> → <?= htmlspecialchars($trajet->getVilleArrivee()) ?>
        </h2>
        <span class="badge badge-<?= $trajet->getStatut()->value === 'ouvert' ? 'success' : 'secondary' ?>">
            <?= $trajet->getStatut()->libelle() ?>
        </span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">
        <div>
            <strong>📅 Date et heure</strong><br>
            <?= $trajet->getDateHeureDepart()->format('d/m/Y à H\hi') ?>
        </div>
        <div>
            <strong>💰 Prix par place</strong><br>
            <?= number_format($trajet->getPrixParPlace(), 0, ',', ' ') ?> FCFA
        </div>
        <div>
            <strong>💺 Places disponibles</strong><br>
            <?= $trajet->getNbPlacesDispo() ?> / <?= $trajet->getNbPlacesTotal() ?>
        </div>
    </div>

    <div style="margin-bottom:1.5rem">
        <strong>👤 Conducteur</strong><br>
        <?= htmlspecialchars($trajet->getConducteur()->getNomComplet()) ?>
        · <?= htmlspecialchars($trajet->getConducteur()->getTelephone()) ?>
    </div>

    <?php if ($trajet->getArrets()): ?>
    <div style="margin-bottom:1.5rem">
        <strong>📍 Arrêts</strong><br>
        <?php foreach ($trajet->getArrets() as $a): ?>
            <span class="badge badge-secondary" style="margin:.2rem"><?= htmlspecialchars($a->getLibelle()) ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    $estConducteur = $userId === $trajet->getConducteur()->getId();
    $estOuvert = $trajet->getStatut()->value === 'ouvert';
    $aDesPlaces = $trajet->getNbPlacesDispo() > 0;
    ?>

    <?php if (!empty($_SESSION['user_id']) && !$estConducteur && $estOuvert && $aDesPlaces): ?>
    <div class="card" style="background:#f8fff8;border:2px solid #1a7a4a">
        <h3 style="margin-bottom:1rem">Réserver ce trajet</h3>
        <form action="/kaaydem75/reservations/creer" method="POST" style="display:flex;gap:1rem;align-items:flex-end">
            <input type="hidden" name="trajet_id" value="<?= $trajet->getId() ?>">
            <div class="form-group" style="margin:0">
                <label>Nb de places</label>
                <input type="number" name="nb_places" value="1" min="1" max="<?= $trajet->getNbPlacesDispo() ?>" style="width:80px">
            </div>
            <button type="submit" class="btn btn-primary">Réserver</button>
        </form>
    </div>
    <?php elseif (empty($_SESSION['user_id'])): ?>
    <p><a href="/kaaydem75/connexion" class="btn btn-primary">Connectez-vous pour réserver</a></p>
    <?php elseif ($estConducteur): ?>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <a href="/kaaydem75/trajets/<?= $trajet->getId() ?>/modifier" class="btn btn-warning btn-sm">Modifier</a>
        <?php if ($estOuvert): ?>
        <form action="/kaaydem75/trajets/<?= $trajet->getId() ?>/annuler" method="POST" style="display:inline">
            <button type="submit" class="btn btn-danger btn-sm" data-confirm="Annuler ce trajet ?">Annuler</button>
        </form>
        <form action="/kaaydem75/trajets/<?= $trajet->getId() ?>/clore" method="POST" style="display:inline">
            <button type="submit" class="btn btn-secondary btn-sm" data-confirm="Clôturer ce trajet ?">Clôturer</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
</div>

