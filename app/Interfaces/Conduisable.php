<?php
declare(strict_types=1);
namespace App\Interfaces;

interface Conduisable
{
    public function peutConduire(): bool;
    public function publierTrajet(
        string $villeDepart, string $villeArrivee, \DateTime $dateHeureDepart,
        int $nbPlaces, float $prixParPlace, array $arrets = []
    ): \App\Models\Entities\Trajet;
}