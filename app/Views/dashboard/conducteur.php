<h1 class="page-title">🚗 Mon espace conducteur</h1>

<div style="margin-bottom:1.5rem;display:flex;gap:1rem">
    <a href="/kaaydem75/trajets/nouveau" class="btn btn-primary">+ Nouveau trajet</a>
    <a href="/kaaydem75/dashboard/passager" class="btn btn-secondary">Mes réservations passager</a>
</div>

<?php if (!empty($evaluations)): ?>
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header"><span class="card-title">⭐ Mes évaluations</span></div>
    <?php foreach ($evaluations as $eval): ?>
    <div style="padding:.6rem 0;border-bottom:1px solid #eee">
        <div class="stars"><?= str_repeat('★', $eval->getNote()) ?><?= str_repeat('☆', 5 - $eval->getNote()) ?></div>
        <p style="font-size:.9rem;margin:.3rem 0"><?= htmlspecialchars($eval->getCommentaire()) ?></p>
        <small style="color:#888">par <?= htmlspecialchars($eval->getEvaluateur()->getNomComplet()) ?></small>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<h2 style="font-size:1.2rem;margin-bottom:1rem">Mes trajets publiés</h2>

<?php if (empty($trajets)): ?>
<div class="card" style="text-align:center;padding:3rem">
    <p style="color:#888">Vous n'avez pas encore publié de trajet.</p>
    <a href="/kaaydem75/trajets/nouveau" class="btn btn-primary" style="margin-top:1rem">Publier un trajet</a>
</div>
<?php else: ?>
<?php foreach ($trajets as $trajet): ?>
<div class="card">
    <div class="card-header">
        <div>
            <strong><?= htmlspecialchars($trajet->getVilleDepart()) ?> → <?= htmlspecialchars($trajet->getVilleArrivee()) ?></strong><br>
            <small>📅 <?= $trajet->getDateHeureDepart()->format('d/m/Y à H\hi') ?></small>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem">
            <span class="badge badge-<?= $trajet->getStatut()->value === 'ouvert' ? 'success' : 'secondary' ?>">
                <?= $trajet->getStatut()->libelle() ?>
            </span>
        </div>
    </div>

    <div style="display:flex;gap:2rem;margin-bottom:1rem">
        <span>💺 <?= $trajet->getNbPlacesDispo() ?>/<?= $trajet->getNbPlacesTotal() ?> places</span>
        <span>💰 <?= number_format($trajet->getPrixParPlace(), 0, ',', ' ') ?> FCFA/place</span>
    </div>

    <?php $reservations = $reservationsParTrajet[$trajet->getId()] ?? []; ?>
    <?php if (!empty($reservations)): ?>
    <div class="table-wrapper" style="margin-bottom:.8rem">
        <table>
            <thead><tr><th>Passager</th><th>Places</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($reservations as $res): ?>
            <tr>
                <td><?= htmlspecialchars($res->getPassager()->getNomComplet()) ?></td>
                <td><?= $res->getNbPlacesReservees() ?></td>
                <td>
                    <?php
                    $statusClass = [
                        'confirmee' => 'success',
                        'en_attente' => 'warning',
                        'terminee' => 'secondary',
                    ];
                    $badgeClass = $statusClass[$res->getStatut()->value] ?? 'danger';
                    ?>
                    <span class="badge badge-<?= $badgeClass ?>"><?= $res->getStatut()->libelle() ?></span>
                </td>
                <td style="display:flex;gap:.3rem">
                    <?php if ($res->getStatut()->value === 'en_attente'): ?>
                    <form action="/kaaydem75/reservations/<?= $res->getId() ?>/confirmer" method="POST">
                        <button class="btn btn-primary btn-sm">✓ Confirmer</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($res->getStatut()->value === 'confirmee'): ?>
                    <form action="/kaaydem75/reservations/<?= $res->getId() ?>/terminer" method="POST">
                        <button class="btn btn-warning btn-sm">Terminer</button>
                    </form>
                    <?php endif; ?>
                    <?php if (in_array($res->getStatut()->value, ['en_attente','confirmee'])): ?>
                    <form action="/kaaydem75/reservations/<?= $res->getId() ?>/annuler" method="POST">
                        <button class="btn btn-danger btn-sm" data-confirm="Annuler ?">✗</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if ($trajet->getStatut()->value === 'ouvert'): ?>
    <div style="display:flex;gap:.5rem">
        <a href="/kaaydem75/trajets/<?= $trajet->getId() ?>/modifier" class="btn btn-warning btn-sm">Modifier</a>
        <form action="/kaaydem75/trajets/<?= $trajet->getId() ?>/annuler" method="POST">
            <button class="btn btn-danger btn-sm" data-confirm="Annuler ce trajet ?">Annuler</button>
        </form>
        <form action="/kaaydem75/trajets/<?= $trajet->getId() ?>/clore" method="POST">
            <button class="btn btn-secondary btn-sm" data-confirm="Clôturer ce trajet ?">Clôturer</button>
        </form>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>
