<?php
declare(strict_types=1);
namespace App\Models\Entities;

use App\Enums\StatutValidation;
use App\Traits\Timestampable;

class ProfilConducteur
{
    use Timestampable;

    private ?int $id = null;
    private int $membreId;
    private string $numeroPermis;
    private string $marqueVehicule;
    private string $modeleVehicule;
    private string $immatriculation;
    private int $nbPlacesVehicule;
    private StatutValidation $statutValidation;
    private float $noteMoyenne = 0.0;
    private int $nombreEvaluations = 0;
    private ?\DateTime $dateValidation = null;

    public function __construct(
        int $membreId,
        string $numeroPermis,
        string $marqueVehicule,
        string $modeleVehicule,
        string $immatriculation,
        int $nbPlacesVehicule,
        StatutValidation $statutValidation = null,
        ?int $id = null
    ) {
        $this->membreId         = $membreId;
        $this->numeroPermis     = $numeroPermis;
        $this->marqueVehicule   = $marqueVehicule;
        $this->modeleVehicule   = $modeleVehicule;
        $this->immatriculation  = $immatriculation;
        $this->nbPlacesVehicule = $nbPlacesVehicule;
        $this->statutValidation = $statutValidation ?? StatutValidation::from(StatutValidation::EN_ATTENTE);
        $this->id               = $id;
    }

    public function estValide(): bool
    {
        return $this->statutValidation->value === StatutValidation::VALIDE;
    }

    public function getId(): ?int { return $this->id; }
    public function getMembreId(): int { return $this->membreId; }
    public function getNumeroPermis(): string { return $this->numeroPermis; }
    public function getMarqueVehicule(): string { return $this->marqueVehicule; }
    public function getModeleVehicule(): string { return $this->modeleVehicule; }
    public function getImmatriculation(): string { return $this->immatriculation; }
    public function getNbPlacesVehicule(): int { return $this->nbPlacesVehicule; }
    public function getStatutValidation(): StatutValidation { return $this->statutValidation; }
    public function getNoteMoyenne(): float { return $this->noteMoyenne; }
    public function getNombreEvaluations(): int { return $this->nombreEvaluations; }
    public function getDateValidation(): ?\DateTime { return $this->dateValidation; }

    public function setId(int $id): void { $this->id = $id; }
    public function setStatutValidation(StatutValidation $statut): void { $this->statutValidation = $statut; }
    public function setNoteMoyenne(float $note): void { $this->noteMoyenne = $note; }
    public function setNombreEvaluations(int $n): void { $this->nombreEvaluations = $n; }
    public function setDateValidation(\DateTime $date): void { $this->dateValidation = $date; }
}
