<?php
declare(strict_types=1);
namespace App\Models\Repositories;

use App\Models\Entities\Reservation;
use App\Models\Entities\Membre;
use App\Models\Entities\Trajet;
use App\Models\Entities\HistoriqueTransition;
use App\Enums\StatutReservation;
use App\Enums\StatutCompte;
use App\Enums\StatutTrajet;

class ReservationRepository extends Repository
{
    public function find(int $id): ?Reservation
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*,
                    p.nom as pass_nom, p.prenom as pass_prenom, p.email as pass_email,
                    p.telephone as pass_tel, p.statut_compte as pass_statut, p.mot_de_passe_hash as pass_hash,
                    t.ville_depart, t.ville_arrivee, t.date_heure_depart, t.nb_places_total,
                    t.nb_places_dispo, t.prix_par_place, t.statut as trajet_statut, t.conducteur_id,
                    u.nom as cond_nom, u.prenom as cond_prenom, u.email as cond_email,
                    u.telephone as cond_tel, u.statut_compte as cond_statut, u.mot_de_passe_hash as cond_hash
             FROM reservations r
             JOIN utilisateurs p ON p.id = r.passager_id
             JOIN trajets t ON t.id = r.trajet_id
             JOIN utilisateurs u ON u.id = t.conducteur_id
             WHERE r.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrater($row) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT r.*,
                    p.nom as pass_nom, p.prenom as pass_prenom, p.email as pass_email,
                    p.telephone as pass_tel, p.statut_compte as pass_statut, p.mot_de_passe_hash as pass_hash,
                    t.ville_depart, t.ville_arrivee, t.date_heure_depart, t.nb_places_total,
                    t.nb_places_dispo, t.prix_par_place, t.statut as trajet_statut, t.conducteur_id,
                    u.nom as cond_nom, u.prenom as cond_prenom, u.email as cond_email,
                    u.telephone as cond_tel, u.statut_compte as cond_statut, u.mot_de_passe_hash as cond_hash
             FROM reservations r
             JOIN utilisateurs p ON p.id = r.passager_id
             JOIN trajets t ON t.id = r.trajet_id
             JOIN utilisateurs u ON u.id = t.conducteur_id
             ORDER BY r.date_reservation DESC'
        );
        return array_map([$this, 'hydrater'], $stmt->fetchAll());
    }

    public function findParPassager(int $passagerId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*,
                    p.nom as pass_nom, p.prenom as pass_prenom, p.email as pass_email,
                    p.telephone as pass_tel, p.statut_compte as pass_statut, p.mot_de_passe_hash as pass_hash,
                    t.ville_depart, t.ville_arrivee, t.date_heure_depart, t.nb_places_total,
                    t.nb_places_dispo, t.prix_par_place, t.statut as trajet_statut, t.conducteur_id,
                    u.nom as cond_nom, u.prenom as cond_prenom, u.email as cond_email,
                    u.telephone as cond_tel, u.statut_compte as cond_statut, u.mot_de_passe_hash as cond_hash
             FROM reservations r
             JOIN utilisateurs p ON p.id = r.passager_id
             JOIN trajets t ON t.id = r.trajet_id
             JOIN utilisateurs u ON u.id = t.conducteur_id
             WHERE r.passager_id = ?
             ORDER BY r.date_reservation DESC'
        );
        $stmt->execute([$passagerId]);
        return array_map([$this, 'hydrater'], $stmt->fetchAll());
    }

    public function findParTrajet(int $trajetId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*,
                    p.nom as pass_nom, p.prenom as pass_prenom, p.email as pass_email,
                    p.telephone as pass_tel, p.statut_compte as pass_statut, p.mot_de_passe_hash as pass_hash,
                    t.ville_depart, t.ville_arrivee, t.date_heure_depart, t.nb_places_total,
                    t.nb_places_dispo, t.prix_par_place, t.statut as trajet_statut, t.conducteur_id,
                    u.nom as cond_nom, u.prenom as cond_prenom, u.email as cond_email,
                    u.telephone as cond_tel, u.statut_compte as cond_statut, u.mot_de_passe_hash as cond_hash
             FROM reservations r
             JOIN utilisateurs p ON p.id = r.passager_id
             JOIN trajets t ON t.id = r.trajet_id
             JOIN utilisateurs u ON u.id = t.conducteur_id
             WHERE r.trajet_id = ?
             ORDER BY r.date_reservation ASC'
        );
        $stmt->execute([$trajetId]);
        return array_map([$this, 'hydrater'], $stmt->fetchAll());
    }

    public function findChevauchements(int $passagerId, \DateTime $dateDepart): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.id FROM reservations r
             JOIN trajets t ON t.id = r.trajet_id
             WHERE r.passager_id = ?
             AND r.statut IN ('en_attente','confirmee')
             AND DATE(t.date_heure_depart) = DATE(?)"
        );
        $stmt->execute([$passagerId, $dateDepart->format('Y-m-d H:i:s')]);
        return $stmt->fetchAll();
    }

public function save($reservation): bool
    {
        if ($reservation->getId() === null) {
            return $this->inserer($reservation);
        }
        return $this->mettreAJour($reservation);
    }

    private function inserer(Reservation $r): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO reservations (passager_id, trajet_id, nb_places_reservees, statut, date_reservation, created_at, updated_at)
             VALUES (?, ?, ?, ?, NOW(), NOW(), NOW())'
        );
        $result = $stmt->execute([
            $r->getPassager()->getId(),
            $r->getTrajet()->getId(),
            $r->getNbPlacesReservees(),
            $r->getStatut()->value,
        ]);
        if ($result) {
            $r->setId((int) $this->pdo->lastInsertId());
            $this->sauvegarderHistorique($r);
        }
        return $result;
    }

    private function mettreAJour(Reservation $r): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE reservations SET statut=?, updated_at=NOW() WHERE id=?'
        );
        $result = $stmt->execute([$r->getStatut()->value, $r->getId()]);
        if ($result) {
            $this->sauvegarderHistorique($r);
        }
        return $result;
    }

    private function sauvegarderHistorique(Reservation $r): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO historique_transitions (reservation_id, statut_avant, statut_apres, date_transition)
             VALUES (?, ?, ?, NOW())'
        );
        foreach ($r->getHistorique() as $h) {
            if ($h->getId() === null) {
                $stmt->execute([$r->getId(), $h->getStatutAvant(), $h->getStatutApres()]);
                $h->setId((int) $this->pdo->lastInsertId());
                $h->setReservationId($r->getId());
            }
        }
    }

    public function delete(int $id): bool
    {
        return $this->pdo->prepare('DELETE FROM reservations WHERE id=?')->execute([$id]);
    }

    private function hydrater(array $row): Reservation
    {
        $passager = new Membre(
            $row['pass_nom'], $row['pass_prenom'], $row['pass_email'],
            $row['pass_hash'], $row['pass_tel'],
            StatutCompte::from($row['pass_statut']), (int) $row['passager_id']
        );
        $conducteur = new Membre(
            $row['cond_nom'], $row['cond_prenom'], $row['cond_email'],
            $row['cond_hash'], $row['cond_tel'],
            StatutCompte::from($row['cond_statut']), (int) $row['conducteur_id']
        );
        $trajet = new Trajet(
            $conducteur,
            $row['ville_depart'], $row['ville_arrivee'],
            new \DateTime($row['date_heure_depart']),
            (int) $row['nb_places_total'], (float) $row['prix_par_place'],
            StatutTrajet::from($row['trajet_statut']),
            (int) $row['trajet_id']
        );
        $trajet->setNbPlacesDispo((int) $row['nb_places_dispo']);

        $reservation = new Reservation(
            $passager, $trajet,
            (int) $row['nb_places_reservees'],
            StatutReservation::from($row['statut']),
            (int) $row['id']
        );
        $reservation->setDateReservation(new \DateTime($row['date_reservation']));

        $stmtH = $this->pdo->prepare('SELECT * FROM historique_transitions WHERE reservation_id=? ORDER BY date_transition');
        $stmtH->execute([(int)$row['id']]);
        $historique = array_map(function($h) {
            $t = new HistoriqueTransition((int)$h['reservation_id'], $h['statut_avant'], $h['statut_apres'], (int)$h['id']);
            $t->setDateTransition(new \DateTime($h['date_transition']));
            return $t;
        }, $stmtH->fetchAll());
        $reservation->setHistorique($historique);

        return $reservation;
    }
}
