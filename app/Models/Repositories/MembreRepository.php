<?php
declare(strict_types=1);
namespace App\Models\Repositories;

use App\Models\Entities\Membre;
use App\Models\Entities\Admin;
use App\Models\Entities\ProfilConducteur;
use App\Models\Entities\Utilisateur;
use App\Enums\StatutCompte;
use App\Enums\StatutValidation;

class MembreRepository extends Repository
{
    public function find(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM utilisateurs WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrater($row) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM utilisateurs ORDER BY created_at DESC');
        return array_map([$this, 'hydrater'], $stmt->fetchAll());
    }

    public function findByEmail(string $email)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM utilisateurs WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ? $this->hydrater($row) : null;
    }

    public function findConducteursEnAttente(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, p.id as profil_id, p.numero_permis, p.marque_vehicule, p.modele_vehicule,
             p.immatriculation, p.nb_places_vehicule, p.statut_validation, p.note_moyenne, p.nombre_evaluations, p.date_validation
             FROM utilisateurs u
             JOIN profils_conducteur p ON p.membre_id = u.id
             WHERE p.statut_validation = ?'
        );
        $stmt->execute([StatutValidation::EN_ATTENTE]);
        return array_map([$this, 'hydraterAvecProfil'], $stmt->fetchAll());
    }

    public function findMembres(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE role = 'membre'");
        $stmt->execute();
        return array_map([$this, 'hydrater'], $stmt->fetchAll());
    }

    public function save($utilisateur): bool
    {
        if ($utilisateur->getId() === null) {
            return $this->inserer($utilisateur);
        }
        return $this->mettrAJour($utilisateur);
    }

    private function inserer(Utilisateur $u): bool
    {
        $role = $u instanceof Admin ? 'admin' : 'membre';
        $stmt = $this->pdo->prepare(
            'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe_hash, telephone, statut_compte, role, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $result = $stmt->execute([
            $u->getNom(), $u->getPrenom(), $u->getEmail(),
            $u->getMotDePasseHash(), $u->getTelephone(),
            $u->getStatutCompte()->value, $role,
        ]);
        if ($result) $u->setId((int) $this->pdo->lastInsertId());
        return $result;
    }

    private function mettrAJour(Utilisateur $u): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE utilisateurs SET nom=?, prenom=?, telephone=?, statut_compte=?, updated_at=NOW() WHERE id=?'
        );
        return $stmt->execute([
            $u->getNom(), $u->getPrenom(), $u->getTelephone(),
            $u->getStatutCompte()->value, $u->getId(),
        ]);
    }

    public function updateStatut(int $id, StatutCompte $statut): bool
    {
        $stmt = $this->pdo->prepare('UPDATE utilisateurs SET statut_compte=?, updated_at=NOW() WHERE id=?');
        return $stmt->execute([$statut->value, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM utilisateurs WHERE id=?');
        return $stmt->execute([$id]);
    }

    private function hydrater(array $row)
    {
        if ($row['role'] === 'admin') {
            $u = new Admin(
                $row['nom'], $row['prenom'], $row['email'],
                $row['mot_de_passe_hash'], $row['telephone'],
                StatutCompte::from($row['statut_compte']),
                (int) $row['id']
            );
        } else {
            $u = new Membre(
                $row['nom'], $row['prenom'], $row['email'],
                $row['mot_de_passe_hash'], $row['telephone'],
                StatutCompte::from($row['statut_compte']),
                (int) $row['id']
            );
        }
        return $u;
    }

    private function hydraterAvecProfil(array $row)
    {
        $membre = $this->hydrater($row);
        if ($membre instanceof Membre && isset($row['profil_id'])) {
            $profil = new ProfilConducteur(
                (int) $row['id'], $row['numero_permis'],
                $row['marque_vehicule'], $row['modele_vehicule'],
                $row['immatriculation'], (int) $row['nb_places_vehicule'],
                StatutValidation::from($row['statut_validation']),
                (int) $row['profil_id']
            );
            $profil->setNoteMoyenne((float) $row['note_moyenne']);
            $profil->setNombreEvaluations((int) $row['nombre_evaluations']);
            $membre->setProfilConducteur($profil);
        }
        return $membre;
    }

    public function findAvecProfil(int $id)
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.*, p.id as profil_id, p.numero_permis, p.marque_vehicule, p.modele_vehicule,
             p.immatriculation, p.nb_places_vehicule, p.statut_validation, p.note_moyenne, p.nombre_evaluations, p.date_validation
             FROM utilisateurs u
             LEFT JOIN profils_conducteur p ON p.membre_id = u.id
             WHERE u.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        return $this->hydraterAvecProfil($row);
    }
}
