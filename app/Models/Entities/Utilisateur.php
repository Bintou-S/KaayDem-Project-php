<?php
declare(strict_types=1);
namespace App\Models\Entities;

use App\Enums\StatutCompte;
use App\Traits\Timestampable;

abstract class Utilisateur
{
    use Timestampable;

    protected ?int $id;
    protected string $nom;
    protected string $prenom;
    protected string $email;
    protected string $motDePasseHash;
    protected string $telephone;
    protected StatutCompte $statutCompte;

    public function __construct(
        string $nom,
        string $prenom,
        string $email,
        string $motDePasseHash,
        string $telephone,
        StatutCompte $statutCompte = null,
        ?int $id = null
    ) {
        $this->nom            = $nom;
        $this->prenom         = $prenom;
        $this->email          = $email;
        $this->motDePasseHash = $motDePasseHash;
        $this->telephone      = $telephone;
        $this->statutCompte   = $statutCompte ?? StatutCompte::from(StatutCompte::EN_ATTENTE);
        $this->id             = $id;
    }

    abstract public function getRole(): string;

    public function getNomComplet(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function verifierMotDePasse(string $motDePasse): bool
    {
        return password_verify($motDePasse, $this->motDePasseHash);
    }

    public function estValide(): bool
    {
        return $this->statutCompte->value === StatutCompte::ACTIF;
    }

    public function changerMotDePasse(string $nouveauMotDePasse): void
    {
        $this->motDePasseHash = password_hash($nouveauMotDePasse, PASSWORD_BCRYPT);
        $this->touch();
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getEmail(): string { return $this->email; }
    public function getTelephone(): string { return $this->telephone; }
    public function getStatutCompte(): StatutCompte { return $this->statutCompte; }
    public function getMotDePasseHash(): string { return $this->motDePasseHash; }

    public function setId(int $id): void { $this->id = $id; }
    public function setStatutCompte(StatutCompte $statut): void { $this->statutCompte = $statut; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setTelephone(string $tel): void { $this->telephone = $tel; }
    
}
