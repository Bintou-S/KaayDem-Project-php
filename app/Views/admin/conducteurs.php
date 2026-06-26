<h1 class="page-title">📋 Validation des conducteurs</h1>
<?php if (empty($enAttente)): ?>
<div class="card" style="text-align:center;padding:2rem"><p>Aucune demande en attente.</p></div>
<?php else: ?>
<?php foreach ($enAttente as $membre): ?>
<?php $profil = $membre->getProfilConducteur(); ?>
<div class="card">
    <div class="card-header">
        <span class="card-title"><?= htmlspecialchars($membre->getNomComplet()) ?></span>
        <span class="badge badge-warning"><?= $profil->getStatutValidation()->libelle() ?></span>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1rem">
        <div><strong>Permis :</strong> <?= htmlspecialchars($profil->getNumeroPermis()) ?></div>
        <div><strong>Véhicule :</strong> <?= htmlspecialchars($profil->getMarqueVehicule()) ?> <?= htmlspecialchars($profil->getModeleVehicule()) ?></div>
        <div><strong>Immat :</strong> <?= htmlspecialchars($profil->getImmatriculation()) ?></div>
    </div>
    <div style="display:flex;gap:.5rem">
        <form action="/kaaydem75/admin/conducteurs/<?= $profil->getId() ?>/valider" method="POST">
            <button class="btn btn-primary btn-sm">✓ Valider</button>
        </form>
        <form action="/kaaydem75/admin/conducteurs/<?= $profil->getId() ?>/refuser" method="POST">
            <button class="btn btn-danger btn-sm" data-confirm="Refuser ce conducteur ?">✗ Refuser</button>
        </form>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

