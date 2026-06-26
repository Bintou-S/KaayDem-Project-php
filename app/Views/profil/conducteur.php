<h1 class="page-title">🚗 Devenir conducteur</h1>

<?php $profil = isset($membre) ? $membre->getProfilConducteur() : null; ?>

<?php if ($profil && $profil->estValide()): ?>
<div class="card" style="border-left:4px solid #28a745">
    <p>✅ Vous êtes déjà conducteur validé !</p>
    <a href="/kaaydem75/dashboard/conducteur" class="btn btn-primary" style="margin-top:1rem">Mon espace conducteur</a>
</div>
<?php elseif ($profil): ?>
<div class="card" style="border-left:4px solid #ffc107">
    <p>⏳ Votre demande est en cours d'examen (statut : <?= $profil->getStatutValidation()->libelle() ?>).</p>
</div>
<?php else: ?>
<?php if (!empty($erreurs)): ?>
<ul class="erreurs"><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
<?php endif; ?>
<div class="card">
    <p style="margin-bottom:1.5rem">Remplissez ce formulaire pour soumettre votre demande de statut conducteur.</p>
    <form action="/kaaydem75/profil/conducteur" method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Numéro de permis</label>
                <input type="text" name="numero_permis" required placeholder="Ex: SN-2020-001">
            </div>
            <div class="form-group">
                <label>Immatriculation</label>
                <input type="text" name="immatriculation" required placeholder="Ex: DK-1234-A">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Marque du véhicule</label>
                <input type="text" name="marque_vehicule" required placeholder="Ex: Toyota">
            </div>
            <div class="form-group">
                <label>Modèle</label>
                <input type="text" name="modele_vehicule" required placeholder="Ex: Corolla">
            </div>
        </div>
        <div class="form-group" style="max-width:200px">
            <label>Nombre de places</label>
            <input type="number" name="nb_places" value="4" min="2" max="8" required>
        </div>
        <button type="submit" class="btn btn-primary">Soumettre la demande</button>
    </form>
</div>
<?php endif; ?>


