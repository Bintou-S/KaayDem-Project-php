<?php
declare(strict_types=1);
namespace App\Models\Repositories;

use App\Models\Entities\Evaluation;
use App\Models\Entities\Reservation;
use App\Models\Entities\Membre;
use App\Models\Entities\Trajet;
use App\Enums\StatutCompte;
use App\Enums\StatutReservation;
use App\Enums\StatutTrajet;

class EvaluationRepository extends Repository
{
    public function find(int $id): ?Evaluation
    {
        $stmt = $this->pdo->prepare('SELECT * FROM evaluations WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrater($row) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM evaluations ORDER BY date_evaluation DESC');
        return array_map([$this, 'hydrater'], $stmt->fetchAll());
    }

    public function findParEvalue(int $evalueId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM evaluations WHERE evalue_id = ? ORDER BY date_evaluation DESC');
        $stmt->execute([$evalueId]);
        return array_map([$this, 'hydrater'], $stmt->fetchAll());
    }

    public function existePourReservation(int $reservationId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM evaluations WHERE reservation_id = ?');
        $stmt->execute([$reservationId]);
        return (int) $stmt->fetchColumn() > 0;
    }

public function save($evaluation): bool
    {
        if ($evaluation->getId() === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO evaluations (reservation_id, evaluateur_id, evalue_id, note, commentaire, date_evaluation, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, NOW(), NOW(), NOW())'
            );
            $result = $stmt->execute([
                $evaluation->getReservation()->getId(),
                $evaluation->getEvaluateur()->getId(),
                $evaluation->getEvalue()->getId(),
                $evaluation->getNote(),
                $evaluation->getCommentaire(),
            ]);
            if ($result) {
                $evaluation->setId((int) $this->pdo->lastInsertId());
                // Update conductor's note moyenne
                $this->updateNoteMoyenne($evaluation->getEvalue()->getId());
            }
            return $result;
        }
        $stmt = $this->pdo->prepare('UPDATE evaluations SET note=?, commentaire=?, updated_at=NOW() WHERE id=?');
        return $stmt->execute([$evaluation->getNote(), $evaluation->getCommentaire(), $evaluation->getId()]);
    }

    private function updateNoteMoyenne(int $evalueId): void
    {
        $this->pdo->prepare(
            'UPDATE profils_conducteur
             SET note_moyenne = (SELECT AVG(note) FROM evaluations WHERE evalue_id = ?),
                 nombre_evaluations = (SELECT COUNT(*) FROM evaluations WHERE evalue_id = ?)
             WHERE membre_id = ?'
        )->execute([$evalueId, $evalueId, $evalueId]);
    }

    public function delete(int $id): bool
    {
        return $this->pdo->prepare('DELETE FROM evaluations WHERE id=?')->execute([$id]);
    }

    private function hydrater(array $row): Evaluation
    {
        $repoMembre = new MembreRepository();
        $evaluateur = $repoMembre->find((int) $row['evaluateur_id']) ?? new Membre('', '', '', '', '', StatutCompte::from('actif'), (int)$row['evaluateur_id']);
        $evalue     = $repoMembre->find((int) $row['evalue_id']) ?? new Membre('', '', '', '', '', StatutCompte::from('actif'), (int)$row['evalue_id']);

        // Minimal reservation object
        $stmtR = $this->pdo->prepare(
            'SELECT r.*, t.ville_depart, t.ville_arrivee, t.date_heure_depart, t.nb_places_total,
                    t.nb_places_dispo, t.prix_par_place, t.statut as trajet_statut, t.conducteur_id
             FROM reservations r JOIN trajets t ON t.id = r.trajet_id WHERE r.id = ?'
        );
        $stmtR->execute([(int)$row['reservation_id']]);
        $rowR = $stmtR->fetch();

        $conducteur = new Membre('', '', '', '', '', StatutCompte::from('actif'), (int)($rowR['conducteur_id'] ?? 0));
        $trajet = new Trajet(
            $conducteur,
            $rowR['ville_depart'] ?? '', $rowR['ville_arrivee'] ?? '',
            new \DateTime($rowR['date_heure_depart'] ?? 'now'),
            (int)($rowR['nb_places_total'] ?? 0), (float)($rowR['prix_par_place'] ?? 0),
            StatutTrajet::from($rowR['trajet_statut'] ?? 'ouvert'),
            (int)($rowR['trajet_id'] ?? 0)
        );
        $reservation = new Reservation(
            $evaluateur, $trajet, (int)($rowR['nb_places_reservees'] ?? 1),
            StatutReservation::from($rowR['statut'] ?? 'terminee'),
            (int)$row['reservation_id']
        );

        $eval = new Evaluation($reservation, $evaluateur, $evalue, (int)$row['note'], $row['commentaire'], (int)$row['id']);
        $eval->setDateEvaluation(new \DateTime($row['date_evaluation']));
        return $eval;
        
    }
}
