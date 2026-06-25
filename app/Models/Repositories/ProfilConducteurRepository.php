<?php
declare(strict_types=1);
namespace App\Models\Repositories;

use App\Models\Entities\ProfilConducteur;
use App\Enums\StatutValidation;

class ProfilConducteurRepository extends Repository
{
    public function find(int $id): ?ProfilConducteur
    {
        $stmt = $this->pdo->prepare('SELECT * FROM profils_conducteur WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrater($row) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM profils_conducteur ORDER BY created_at DESC');
        return array_map([$this, 'hydrater'], $stmt->fetchAll());
    }

    public function findByMembre(int $membreId): ?ProfilConducteur
    {
        $stmt = $this->pdo->prepare('SELECT * FROM profils_conducteur WHERE membre_id = ?');
        $stmt->execute([$membreId]);
        $row = $stmt->fetch();
        return $row ? $this->hydrater($row) : null;
    }

public function save($profil): bool
    {
        if ($profil->getId() === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO profils_conducteur (membre_id, numero_permis, marque_vehicule, modele_vehicule,
                 immatriculation, nb_places_vehicule, statut_validation, note_moyenne, nombre_evaluations, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, NOW(), NOW())'
            );
            $result = $stmt->execute([
                $profil->getMembreId(), $profil->getNumeroPermis(),
                $profil->getMarqueVehicule(), $profil->getModeleVehicule(),
                $profil->getImmatriculation(), $profil->getNbPlacesVehicule(),
                $profil->getStatutValidation()->value,
            ]);
            if ($result) $profil->setId((int) $this->pdo->lastInsertId());
            return $result;
        }
        $stmt = $this->pdo->prepare(
            'UPDATE profils_conducteur SET statut_validation=?, date_validation=?, updated_at=NOW() WHERE id=?'
        );
        return $stmt->execute([
            $profil->getStatutValidation()->value,
            $profil->getDateValidation()->format('Y-m-d H:i:s'),
            $profil->getId(),
        ]);
    }

    public function delete(int $id): bool
    {
        return $this->pdo->prepare('DELETE FROM profils_conducteur WHERE id=?')->execute([$id]);
    }

    private function hydrater(array $row): ProfilConducteur
    {
        $profil = new ProfilConducteur(
            (int) $row['membre_id'], $row['numero_permis'],
            $row['marque_vehicule'], $row['modele_vehicule'],
            $row['immatriculation'], (int) $row['nb_places_vehicule'],
            StatutValidation::from($row['statut_validation']),
            (int) $row['id']
        );
        $profil->setNoteMoyenne((float) $row['note_moyenne']);
        $profil->setNombreEvaluations((int) $row['nombre_evaluations']);
        if (!empty($row['date_validation'])) {
            $profil->setDateValidation(new \DateTime($row['date_validation']));
        }
        return $profil;
    }
}
