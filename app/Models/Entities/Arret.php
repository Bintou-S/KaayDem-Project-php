<?php

declare(strict_types=1);

namespace App\Models\Entities;

class Arret
{
    private ?int $id = null;
    private string $libelle;
    private int $ordre;
    private ?int $trajetId;

    public function __construct(string $libelle, int $ordre, ?int $trajetId = null, ?int $id = null)
    {
        $this->libelle  = $libelle;
        $this->ordre    = $ordre;
        $this->trajetId = $trajetId;
        $this->id       = $id;
    }

    public function getId(): ?int { return $this->id; }
    public function getLibelle(): string { return $this->libelle; }
    public function getOrdre(): int { return $this->ordre; }
    public function getTrajetId(): ?int { return $this->trajetId; }
    public function setId(int $id): void { $this->id = $id; }
    public function setTrajetId(int $id): void { $this->trajetId = $id; }

}