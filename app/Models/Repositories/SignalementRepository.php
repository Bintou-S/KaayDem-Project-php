<?php
declare(strict_types=1);
namespace App\Models\Repositories;

use App\Models\Entities\Signalement;

class SignalementRepository extends Repository
{
    public function find(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM signalements WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrater($row) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM signalements ORDER BY date_signalement DESC');
        return array_map([$this, 'hydrater'], $stmt->fetchAll());
    }

    public function findNonTraites(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM signalements WHERE traite = 0 ORDER BY date_signalement DESC');
        $stmt->execute();
        return array_map([$this, 'hydrater'], $stmt->fetchAll());
    }

    public function save($signalement): bool
    {
        if ($signalement->getId() === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO signalements (auteur_id, cible_id, motif, description, date_signalement, traite, created_at, updated_at)
                 VALUES (?, ?, ?, ?, NOW(), 0, NOW(), NOW())'
            );
            $result = $stmt->execute([
                $signalement->getAuteurId(), $signalement->getCibleId(),
                $signalement->getMotif(), $signalement->getDescription(),
            ]);
            if ($result) $signalement->setId((int) $this->pdo->lastInsertId());
            return $result;
        }
        $stmt = $this->pdo->prepare('UPDATE signalements SET traite=?, updated_at=NOW() WHERE id=?');
        return $stmt->execute([$signalement->isTraite() ? 1 : 0, $signalement->getId()]);
    }

    public function delete(int $id): bool
    {
        return $this->pdo->prepare('DELETE FROM signalements WHERE id=?')->execute([$id]);
    }

    private function hydrater(array $row): Signalement
    {
        $s = new Signalement((int)$row['auteur_id'], (int)$row['cible_id'], $row['motif'], $row['description'], (int)$row['id']);
        $s->setTraite((bool)$row['traite']);
        $s->setDateSignalement(new \DateTime($row['date_signalement']));
        return $s;
    }
}
