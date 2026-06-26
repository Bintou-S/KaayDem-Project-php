<?php
declare(strict_types=1);
namespace App\Models\Entities;

use App\Enums\StatutTrajet;
use App\Traits\Timestampable;
use App\Exceptions\PlacesIndisponiblesException;

class Trajet
{
    use Timestampable;

    private ?int $id = null;
    private Membre $conducteur;
    private string $villeDepart;
    private string $villeArrivee;
    private \DateTime $dateHeureDepart;
    private int $nbPlacesTotal;
    private int $nbPlacesDispo;
    private float $prixParPlace;
    public StatutTrajet $statut;
    private array $arrets = [];

    public function __construct(
        Membre $conducteur,
        string $villeDepart,
        string $villeArrivee,
        \DateTime $dateHeureDepart,
        int $nbPlacesTotal,
        float $prixParPlace,
        StatutTrajet $statut = null,
        ?int $id = null
    ) {
        $this->conducteur      = $conducteur;
        $this->villeDepart     = $villeDepart;
        $this->villeArrivee    = $villeArrivee;
        $this->dateHeureDepart = $dateHeureDepart;
        $this->nbPlacesTotal   = $nbPlacesTotal;
        $this->nbPlacesDispo   = $nbPlacesTotal;
        $this->prixParPlace    = $prixParPlace;
        $this->statut          = $statut ?? StatutTrajet::from(StatutTrajet::OUVERT);
        $this->id              = $id;
    }

    public function reserverPlace(int $nb): void
    {
        if ($nb > $this->nbPlacesDispo) {
            throw new PlacesIndisponiblesException("Seulement {$this->nbPlacesDispo} place(s) disponible(s).");
        }
        $this->nbPlacesDispo -= $nb;
        if ($this->nbPlacesDispo === 0) {
            $this->statut = StatutTrajet::from(StatutTrajet::COMPLET);
        }
        $this->touch();
    }

    public function libererPlace(int $nb): void
    {
        $this->nbPlacesDispo = min($this->nbPlacesTotal, $this->nbPlacesDispo + $nb);
        if ($this->statut->value === StatutTrajet::COMPLET && $this->nbPlacesDispo > 0) {
            $this->statut = StatutTrajet::from(StatutTrajet::OUVERT);
        }
        $this->touch();
    }

    public function estComplet(): bool { return $this->nbPlacesDispo === 0; }
    public function estModifiable(): bool { return $this->statut->value === StatutTrajet::OUVERT; }

    public function clore(): void { $this->statut = StatutTrajet::from(StatutTrajet::CLOTURE); $this->touch(); }
    public function annuler(): void { $this->statut = StatutTrajet::from(StatutTrajet::ANNULE); $this->touch(); }

    public function mettreAJour(array $donnees): void
    {
        if (isset($donnees['villeDepart']))     $this->villeDepart     = $donnees['villeDepart'];
        if (isset($donnees['villeArrivee']))    $this->villeArrivee    = $donnees['villeArrivee'];
        if (isset($donnees['dateHeureDepart'])) $this->dateHeureDepart = $donnees['dateHeureDepart'];
        if (isset($donnees['nbPlacesTotal']))   $this->nbPlacesTotal   = $donnees['nbPlacesTotal'];
        if (isset($donnees['prixParPlace']))    $this->prixParPlace    = $donnees['prixParPlace'];
        $this->touch();
    }

    public function ajouterArret(string $libelle, int $ordre): void
    {
        $this->arrets[] = new Arret($libelle, $ordre, $this->id);
    }

    public function getId(): ?int { return $this->id; }
    public function getConducteur(): Membre { return $this->conducteur; }
    public function getVilleDepart(): string { return $this->villeDepart; }
    public function getVilleArrivee(): string { return $this->villeArrivee; }
    public function getDateHeureDepart(): \DateTime { return $this->dateHeureDepart; }
    public function getNbPlacesTotal(): int { return $this->nbPlacesTotal; }
    public function getNbPlacesDispo(): int { return $this->nbPlacesDispo; }
    public function getPrixParPlace(): float { return $this->prixParPlace; }
    public function getStatut(): StatutTrajet { return $this->statut; }
    public function getArrets(): array { return $this->arrets; }

    public function setId(int $id): void { $this->id = $id; }
    public function setNbPlacesDispo(int $nb): void { $this->nbPlacesDispo = $nb; }
    public function setStatut(StatutTrajet $statut): void { $this->statut = $statut; }
    public function setArrets(array $arrets): void { $this->arrets = $arrets; }
    
}
