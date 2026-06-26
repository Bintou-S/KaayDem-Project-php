<div class="hero">
    <img src="/kaaydem75/public/img/logo.jpeg" alt="Kaay Deem" class="hero-logo">
    <h1>Voyagez <span>ensemble</span>,<br>voyagez moins cher</h1>
    <p>Covoiturage étudiant au Sénégal — trouvez ou proposez un trajet en quelques secondes</p>
    <div class="hero-actions">
        <a href="/kaaydem75/trajets/recherche" class="btn btn-warning btn-lg">🔍 Rechercher un trajet</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="/kaaydem75/trajets/nouveau" class="btn btn-outline btn-lg">+ Proposer un trajet</a>
        <?php else: ?>
            <a href="/kaaydem75/inscription" class="btn btn-outline btn-lg">Créer un compte</a>
        <?php endif; ?>
    </div>
    <div class="hero-values">
        <span class="val">Sécurité</span>
        <span class="val">Ponctualité</span>
        <span class="val">Confort</span>
        <span class="val">Transport Étudiant</span>
    </div>
</div>

<div class="section-header">
    <span class="section-title">Trajets disponibles <span style="color:var(--text-muted);font-weight:500;font-size:.9rem">(<?= $total ?>)</span></span>
    <a href="/kaaydem75/trajets/recherche" class="btn btn-sm btn-secondary">Filtrer</a>
</div>

<?php if (empty($trajets)): ?>
<div class="card">
    <div class="empty-state">
        <span class="empty-icon">🚐</span>
        <p>Aucun trajet disponible pour le moment.</p>
    </div>
</div>
<?php else: ?>
<?php foreach ($trajets as $trajet): ?>
<div class="trajet-card">
    <div class="tc-stripe"></div>
    <div class="tc-body">
        <div class="trajet-route">
            <div class="villes">
                <?= htmlspecialchars($trajet->getVilleDepart()) ?>
                <span class="arrow">→</span>
                <?= htmlspecialchars($trajet->getVilleArrivee()) ?>
            </div>
            <div class="details">
                <span>📅 <?= $trajet->getDateHeureDepart()->format('d/m/Y à H\hi') ?></span>
                <span>👤 <?= htmlspecialchars($trajet->getConducteur()->getNomComplet()) ?></span>
                <?php if ($trajet->getArrets()): ?>
                    <span>📍 <?= count($trajet->getArrets()) ?> arrêt(s)</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="trajet-info">
            <div class="trajet-prix"><?= number_format($trajet->getPrixParPlace(), 0, ',', ' ') ?></div>
            <span class="trajet-prix-unit">FCFA / place</span>
            <div class="trajet-places"><?= $trajet->getNbPlacesDispo() ?> place(s) dispo</div>
        </div>
    </div>
    <div class="tc-action">
        <a href="/kaaydem75/trajets/<?= $trajet->getId() ?>" class="btn btn-primary btn-sm">Voir</a>
    </div>
</div>

<?php endforeach; ?>
<?php endif; ?>