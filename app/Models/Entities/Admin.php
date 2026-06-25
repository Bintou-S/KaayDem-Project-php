<?php
declare(strict_types=1);
namespace App\Models\Entities;

use App\Enums\StatutCompte;
use App\Enums\StatutValidation;

class Admin extends Utilisateur
{
    public function getRole(): string { return 'admin'; }

    public function peutConduire(): bool { return false; }

    public function validerConducteur(ProfilConducteur $profil): void
    {
        $profil->setStatutValidation(StatutValidation::from(StatutValidation::VALIDE));
        $profil->setDateValidation(new \DateTime());
    }

    public function refuserConducteur(ProfilConducteur $profil): void
    {
        $profil->setStatutValidation(StatutValidation::from(StatutValidation::REFUSE));
    }

    public function suspendreCompte(Utilisateur $utilisateur): void
    {
        $utilisateur->setStatutCompte(StatutCompte::from(StatutCompte::SUSPENDU));
    }

    public function activerCompte(Utilisateur $utilisateur): void
    {
        $utilisateur->setStatutCompte(StatutCompte::from(StatutCompte::ACTIF));
    }
}