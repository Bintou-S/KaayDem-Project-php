<h1 class="page-title">📊 Statistiques</h1>
<div class="dashboard-grid">
    <div class="stat-card"><div class="stat-number"><?= $stats['total_membres'] ?></div><div class="stat-label">Membres</div></div>
    <div class="stat-card"><div class="stat-number"><?= $stats['total_trajets'] ?></div><div class="stat-label">Trajets</div></div>
    <div class="stat-card"><div class="stat-number"><?= $stats['total_reservations'] ?></div><div class="stat-label">Réservations</div></div>
    <div class="stat-card"><div class="stat-number"><?= number_format((float)($stats['taux_occupation'] ?? 0), 1) ?>%</div><div class="stat-label">Taux d'occupation moyen</div></div>
</div>
<div class="card" style="margin-top:1rem">
    <h3>Top conducteurs</h3>
    <?php if (!empty($topConducteurs)): ?>
    <table style="margin-top:1rem">
        <thead><tr><th>Conducteur</th><th>Note</th><th>Évaluations</th><th>Trajets</th></tr></thead>
        <tbody>
        <?php foreach ($topConducteurs as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></td>
            <td><span class="stars"><?= str_repeat('★', (int)round($c['note_moyenne'])) ?></span> <?= number_format((float)$c['note_moyenne'], 2) ?></td>
            <td><?= $c['nombre_evaluations'] ?></td>
            <td><?= $c['nb_trajets'] ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>


