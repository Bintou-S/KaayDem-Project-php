<?php
declare(strict_types=1);
namespace App\Models\Entities;

use App\Enums\StatutReservation;
use App\Traits\Timestampable;
use App\Exceptions\TransitionInvalideException;

class Reservation
{
    use Timestampable;

    private ?int $id = null;
    private Membre $passager;
    private Trajet $trajet;
    private int $nbPlacesReservees;
    public StatutReservation $statut;
    private \DateTime $dateReservation;
    private array $historique = [];

    public function __construct(
        Membre $passager,
        Trajet $trajet,
        int $nbPlacesReservees,
        StatutReservation $statut = null,
        ?int $id = null
    ) {
        $this->passager          = $passager;
        $this->trajet            = $trajet;
        $this->nbPlacesReservees = $nbPlacesReservees;
        $this->statut            = $statut ?? StatutReservation::from(StatutReservation::EN_ATTENTE);
        $this->dateReservation   = new \DateTime();
        $this->id                = $id;
    }

    private function changerStatut(StatutReservation $nouveau): void
    {
        if (!$this->statut->peutTransitionnerVers($nouveau)) {
            throw new TransitionInvalideException(
                "Impossible de passer de {$this->statut->libelle()} à {$nouveau->libelle()}."
            );
        }
        $this->historique[] = new HistoriqueTransition(
            $this->id, $this->statut->value, $nouveau->value
        );
        $this->statut = $nouveau;
        $this->touch();
    }

    public function confirmer(): void { $this->changerStatut(StatutReservation::from(StatutReservation::CONFIRMEE)); }
    public function annuler(): void   { $this->changerStatut(StatutReservation::from(StatutReservation::ANNULEE)); }
    public function terminer(): void  { $this->changerStatut(StatutReservation::from(StatutReservation::TERMINEE)); }

    public function getId(): ?int { return $this->id; }
    public function getPassager(): Membre { return $this->passager; }
    public function getTrajet(): Trajet { return $this->trajet; }
    public function getNbPlacesReservees(): int { return $this->nbPlacesReservees; }
    public function getStatut(): StatutReservation { return $this->statut; }
    public function getDateReservation(): \DateTime { return $this->dateReservation; }
    public function getHistorique(): array { return $this->historique; }

    public function setId(int $id): void { $this->id = $id; }
    public function setDateReservation(\DateTime $date): void { $this->dateReservation = $date; }
    public function setHistorique(array $h): void { $this->historique = $h; }
    
}
