<?php

declare(strict_types=1);

namespace App\Models\Entities;

class HistoriqueTransition
{
    private ?int $id = null;
    private ?int $reservationId;
    private string $statutAvant;
    private string $statutApres;
    private \DateTime $dateTransition;

    public function __construct(?int $reservationId, string $statutAvant, string $statutApres, ?int $id = null)
    {
        $this->reservationId  = $reservationId;
        $this->statutAvant    = $statutAvant;
        $this->statutApres    = $statutApres;
        $this->dateTransition = new \DateTime();
        $this->id             = $id;
    }

    public function getId(): ?int { return $this->id; }
    public function getReservationId(): ?int { return $this->reservationId; }
    public function getStatutAvant(): string { return $this->statutAvant; }
    public function getStatutApres(): string { return $this->statutApres; }
    public function getDateTransition(): \DateTime { return $this->dateTransition; }
    public function setId(int $id): void { $this->id = $id; }
    public function setReservationId(int $id): void { $this->reservationId = $id; }
    public function setDateTransition(\DateTime $date): void { $this->dateTransition = $date; }
    
}
