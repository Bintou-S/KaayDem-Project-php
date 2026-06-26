<h1 class="page-title">🧳 Mes réservations</h1>

<div style="margin-bottom:1.5rem;display:flex;gap:1rem">
    <a href="/kaaydem75/trajets" class="btn btn-primary">Rechercher un trajet</a>
    <a href="/kaaydem75/profil/conducteur" class="btn btn-warning">Devenir conducteur</a>
</div>

<?php if (empty($reservations)): ?>
<div class="card" style="text-align:center;padding:3rem">
    <p style="color:#888">Vous n'avez pas encore de réservations.</p>
    <a href="/kaaydem75/trajets" class="btn btn-primary" style="margin-top:1rem">Trouver un trajet</a>
</div>
<?php else: ?>
<?php foreach ($reservations as $res): ?>
<div class="card">
    <div class="card-header">
        <div>
            <strong><?= htmlspecialchars($res->getTrajet()->getVilleDepart()) ?> → <?= htmlspecialchars($res->getTrajet()->getVilleArrivee()) ?></strong><br>
            <small style="color:#666">📅 <?= $res->getTrajet()->getDateHeureDepart()->format('d/m/Y à H\hi') ?></small>
        </div>
        <?php
            $badgeClass = 'danger';
            if ($res->getStatut()->value === 'confirmee') {
                $badgeClass = 'success';
            } elseif ($res->getStatut()->value === 'en_attente') {
                $badgeClass = 'warning';
            } elseif ($res->getStatut()->value === 'terminee') {
                $badgeClass = 'secondary';
            }
        ?>
        <span class="badge badge-<?= $badgeClass ?>"><?= $res->getStatut()->libelle() ?></span>
    </div>
    <div style="display:flex;gap:2rem;margin-bottom:.8rem">
        <span>💺 <?= $res->getNbPlacesReservees() ?> place(s)</span>
        <span>💰 <?= number_format($res->getTrajet()->getPrixParPlace() * $res->getNbPlacesReservees(), 0, ',', ' ') ?> FCFA</span>
        <span>👤 <?= htmlspecialchars($res->getTrajet()->getConducteur()->getNomComplet()) ?></span>
    </div>
    <div style="display:flex;gap:.5rem">
        <?php if (in_array($res->getStatut()->value, ['en_attente','confirmee'])): ?>
        <form action="/kaaydem75/reservations/<?= $res->getId() ?>/annuler" method="POST">
            <button type="submit" class="btn btn-danger btn-sm" data-confirm="Annuler cette réservation ?">Annuler</button>
        </form>
        <?php endif; ?>
        <?php if ($res->getStatut()->value === 'terminee'): ?>
        <a href="/kaaydem75/evaluations/nouveau/<?= $res->getId() ?>" class="btn btn-warning btn-sm">⭐ Évaluer</a>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
