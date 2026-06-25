<?php
declare(strict_types=1);
namespace App\Interfaces;

interface Reservable
{
    public function reserver(\App\Models\Entities\Trajet $trajet, int $nbPlaces): \App\Models\Entities\Reservation;
}
