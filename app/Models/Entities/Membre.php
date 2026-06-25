<?php
declare(strict_types=1);
namespace App\Models\Entities;

use App\Enums\StatutCompte;
use App\Interfaces\Conduisable;
use App\Interfaces\Reservable;
use App\Interfaces\EvaluableInterface;
use App\Exceptions\CompteNonValideException;
use App\Exceptions\PlacesIndisponiblesException;
use App\Exceptions\ChevauchementTrajetException;

class Membre extends Utilisateur implements Conduisable, Reservable, EvaluableInterface
{
    private ?ProfilConducteur $profilConducteur = null;
    private array $reservations = [];
    private array $trajetsPublies = [];
    private array $notesRecues = [];

    public function getRole(): string { return 'membre'; }

    public function peutConduire(): bool
    {
        return $this->profilConducteur !== null && $this->profilConducteur->estValide();
    }

    public function demanderStatutConducteur(
        string $numeroPermis, string $marque, string $modele,
        string $immatriculation, int $nbPlaces
    ): ProfilConducteur {
        if (!$this->estValide()) {
            throw new CompteNonValideException("Votre compte doit être actif pour demander le statut conducteur.");
        }
        $this->profilConducteur = new ProfilConducteur(
            $this->id, $numeroPermis, $marque, $modele, $immatriculation, $nbPlaces
        );
        return $this->profilConducteur;
    }

    public function publierTrajet(
        string $villeDepart, string $villeArrivee, \DateTime $dateHeureDepart,
        int $nbPlaces, float $prixParPlace, array $arrets = []
    ): Trajet {
        if (!$this->peutConduire()) {
            throw new CompteNonValideException("Vous devez être conducteur validé pour publier un trajet.");
        }
        $trajet = new Trajet($this, $villeDepart, $villeArrivee, $dateHeureDepart, $nbPlaces, $prixParPlace);
        foreach ($arrets as $index => $libelle) {
            $trajet->ajouterArret($libelle, $index);
        }
        $this->trajetsPublies[] = $trajet;
        return $trajet;
    }

    public function modifierTrajet(Trajet $trajet, array $donnees): bool
    {
        if (!$trajet->estModifiable()) return false;
        $trajet->mettreAJour($donnees);
        return true;
    }

    public function annulerTrajet(Trajet $trajet): bool
    {
        if (!$trajet->estModifiable()) return false;
        $trajet->annuler();
        return true;
    }

    public function cloreTrajet(Trajet $trajet): bool
    {
        $trajet->clore();
        return true;
    }

    public function reserver(Trajet $trajet, int $nbPlaces): Reservation
    {
        if (!$this->estValide()) {
            throw new CompteNonValideException("Votre compte doit être actif pour réserver.");
        }
        $reservation = new Reservation($this, $trajet, $nbPlaces);
        $trajet->reserverPlace($nbPlaces);
        $this->reservations[] = $reservation;
        return $reservation;
    }

    public function annuler(Reservation $reservation): bool
    {
        $reservation->annuler();
        $reservation->getTrajet()->libererPlace($reservation->getNbPlacesReservees());
        return true;
    }

    public function evaluer(Reservation $reservation, int $note, string $commentaire): bool
    {
        if ($reservation->getStatut()->value !== 'terminee') return false;
        new Evaluation($reservation, $this, $reservation->getTrajet()->getConducteur(), $note, $commentaire);
        return true;
    }

    public function getNoteMoyenne(): float
    {
        if (empty($this->notesRecues)) return 0.0;
        $total = array_sum(array_map(fn(Evaluation $e) => $e->getNote(), $this->notesRecues));
        return round($total / count($this->notesRecues), 2);
    }

    public function getNombreEvaluations(): int { return count($this->notesRecues); }
    public function ajouterEvaluation(int $note): void {}

    public function getProfilConducteur(): ?ProfilConducteur { return $this->profilConducteur; }
    public function setProfilConducteur(?ProfilConducteur $profil): void { $this->profilConducteur = $profil; }
    public function getReservations(): array { return $this->reservations; }
    public function getTrajetsPublies(): array { return $this->trajetsPublies; }
}
