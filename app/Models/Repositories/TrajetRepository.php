<?php
declare(strict_types=1);
namespace App\Models\Repositories;

use App\Models\Entities\Trajet;
use App\Models\Entities\Membre;
use App\Models\Entities\Arret;
use App\Enums\StatutTrajet;
use App\Enums\StatutCompte;

class TrajetRepository extends Repository
{
    public function find(int $id)
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.*, u.nom, u.prenom, u.email, u.telephone, u.statut_compte, u.mot_de_passe_hash
             FROM trajets t JOIN utilisateurs u ON u.id = t.conducteur_id WHERE t.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrater($row) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT t.*, u.nom, u.prenom, u.email, u.telephone, u.statut_compte, u.mot_de_passe_hash
             FROM trajets t JOIN utilisateurs u ON u.id = t.conducteur_id
             ORDER BY t.date_heure_depart ASC'
        );
        return array_map([$this, 'hydrater'], $stmt->fetchAll());
    }

    public function rechercher(array $criteres, int $page = 1, int $perPage = 10): array
    {
        $where  = ["t.statut = 'ouvert'", "t.date_heure_depart > NOW()"];
        $params = [];

        if (!empty($criteres['villeDepart'])) {
            $where[]  = 't.ville_depart LIKE ?';
            $params[] = '%' . $criteres['villeDepart'] . '%';
        }
        if (!empty($criteres['villeArrivee'])) {
            $where[]  = 't.ville_arrivee LIKE ?';
            $params[] = '%' . $criteres['villeArrivee'] . '%';
        }
        if (!empty($criteres['date'])) {
            $where[]  = 'DATE(t.date_heure_depart) = ?';
            $params[] = $criteres['date'];
        }
        if (!empty($criteres['prixMax'])) {
            $where[]  = 't.prix_par_place <= ?';
            $params[] = (float) $criteres['prixMax'];
        }
        if (!empty($criteres['placesMin'])) {
            $where[]  = 't.nb_places_dispo >= ?';
            $params[] = (int) $criteres['placesMin'];
        }

        $offset   = ($page - 1) * $perPage;
        $sql      = 'SELECT t.*, u.nom, u.prenom, u.email, u.telephone, u.statut_compte, u.mot_de_passe_hash
                     FROM trajets t JOIN utilisateurs u ON u.id = t.conducteur_id
                     WHERE ' . implode(' AND ', $where) . '
                     ORDER BY t.date_heure_depart ASC LIMIT ? OFFSET ?';
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return array_map([$this, 'hydrater'], $stmt->fetchAll());
    }

    public function findParConducteur(int $conducteurId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.*, u.nom, u.prenom, u.email, u.telephone, u.statut_compte, u.mot_de_passe_hash
             FROM trajets t JOIN utilisateurs u ON u.id = t.conducteur_id
             WHERE t.conducteur_id = ? ORDER BY t.date_heure_depart DESC'
        );
        $stmt->execute([$conducteurId]);
        return array_map([$this, 'hydrater'], $stmt->fetchAll());
    }

    public function save($trajet): bool
    {
        if ($trajet->getId() === null) return $this->inserer($trajet);
        return $this->mettreAJour($trajet);
    }

    private function inserer(Trajet $t): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO trajets (conducteur_id, ville_depart, ville_arrivee, date_heure_depart,
             nb_places_total, nb_places_dispo, prix_par_place, statut, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $result = $stmt->execute([
            $t->getConducteur()->getId(), $t->getVilleDepart(), $t->getVilleArrivee(),
            $t->getDateHeureDepart()->format('Y-m-d H:i:s'), $t->getNbPlacesTotal(),
            $t->getNbPlacesDispo(), $t->getPrixParPlace(), $t->getStatut()->value,
        ]);
        if ($result) {
            $t->setId((int) $this->pdo->lastInsertId());
            $this->sauvegarderArrets($t);
        }
        return $result;
    }

    private function mettreAJour(Trajet $t): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE trajets SET ville_depart=?, ville_arrivee=?, date_heure_depart=?,
             nb_places_total=?, nb_places_dispo=?, prix_par_place=?, statut=?, updated_at=NOW() WHERE id=?'
        );
        return $stmt->execute([
            $t->getVilleDepart(), $t->getVilleArrivee(),
            $t->getDateHeureDepart()->format('Y-m-d H:i:s'), $t->getNbPlacesTotal(),
            $t->getNbPlacesDispo(), $t->getPrixParPlace(), $t->getStatut()->value, $t->getId(),
        ]);
    }

    private function sauvegarderArrets(Trajet $trajet): void
    {
        $this->pdo->prepare('DELETE FROM arrets WHERE trajet_id = ?')->execute([$trajet->getId()]);
        $stmt = $this->pdo->prepare('INSERT INTO arrets (trajet_id, libelle, ordre) VALUES (?, ?, ?)');
        foreach ($trajet->getArrets() as $arret) {
            $stmt->execute([$trajet->getId(), $arret->getLibelle(), $arret->getOrdre()]);
        }
    }

    public function delete(int $id): bool
    {
        return $this->pdo->prepare('DELETE FROM trajets WHERE id=?')->execute([$id]);
    }

    public function compterTotal(array $criteres = []): int
    {
        $where  = ["statut = 'ouvert'", "date_heure_depart > NOW()"];
        $params = [];
        if (!empty($criteres['villeDepart'])) { $where[] = 'ville_depart LIKE ?'; $params[] = '%' . $criteres['villeDepart'] . '%'; }
        if (!empty($criteres['villeArrivee'])) { $where[] = 'ville_arrivee LIKE ?'; $params[] = '%' . $criteres['villeArrivee'] . '%'; }
        if (!empty($criteres['date'])) { $where[] = 'DATE(date_heure_depart) = ?'; $params[] = $criteres['date']; }
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM trajets WHERE ' . implode(' AND ', $where));
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function hydrater(array $row): Trajet
    {
        $conducteur = new Membre(
            $row['nom'], $row['prenom'], $row['email'], $row['mot_de_passe_hash'],
            $row['telephone'], StatutCompte::from($row['statut_compte']), (int) $row['conducteur_id']
        );
        $trajet = new Trajet(
            $conducteur, $row['ville_depart'], $row['ville_arrivee'],
            new \DateTime($row['date_heure_depart']), (int) $row['nb_places_total'],
            (float) $row['prix_par_place'], StatutTrajet::from($row['statut']), (int) $row['id']
        );
        $trajet->setNbPlacesDispo((int) $row['nb_places_dispo']);
        $stmtArrets = $this->pdo->prepare('SELECT * FROM arrets WHERE trajet_id = ? ORDER BY ordre');
        $stmtArrets->execute([(int) $row['id']]);
        $arrets = array_map(fn($a) => new Arret($a['libelle'], (int)$a['ordre'], (int)$a['trajet_id'], (int)$a['id']), $stmtArrets->fetchAll());
        $trajet->setArrets($arrets);
        return $trajet;
        
    }
}
