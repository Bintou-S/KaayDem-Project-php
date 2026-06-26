<h1 class="page-title">🚨 Signalements</h1>
<?php if (empty($signalements)): ?>
<div class="card" style="text-align:center;padding:2rem"><p>Aucun signalement non traité.</p></div>
<?php else: ?>
<?php foreach ($signalements as $s): ?>
<div class="card">
    <div class="card-header">
        <span class="card-title"><?= htmlspecialchars($s->getMotif()) ?></span>
        <small><?= $s->getDateSignalement()->format('d/m/Y H:i') ?></small>
    </div>
    <p><?= htmlspecialchars($s->getDescription()) ?></p>
    <p style="font-size:.85rem;color:#666">Auteur ID: <?= $s->getAuteurId() ?> → Cible ID: <?= $s->getCibleId() ?></p>
    <form action="/kaaydem75/admin/signalements/<?= $s->getId() ?>/traiter" method="POST" style="margin-top:.8rem">
        <button class="btn btn-primary btn-sm">Marquer traité</button>
    </form>
</div>
<?php endforeach; ?>
<?php endif; ?>


