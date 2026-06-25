<?php

declare(strict_types=1);

namespace App\Models\Entities;

use App\Traits\Timestampable;

class Signalement
{
    use Timestampable;

    private ?int $id = null;
    private int $auteurId;
    private int $cibleId;
    private string $motif;
    private string $description;
    private \DateTime $dateSignalement;
    private bool $traite = false;

    public function __construct(
        int $auteurId,
        int $cibleId,
        string $motif,
        string $description,
        ?int $id = null
    ) {
        $this->auteurId        = $auteurId;
        $this->cibleId         = $cibleId;
        $this->motif           = $motif;
        $this->description     = $description;
        $this->dateSignalement = new \DateTime();
        $this->id              = $id;
    }

    public function marquerTraite(): void { $this->traite = true; }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getAuteurId(): int { return $this->auteurId; }
    public function getCibleId(): int { return $this->cibleId; }
    public function getMotif(): string { return $this->motif; }
    public function getDescription(): string { return $this->description; }
    public function getDateSignalement(): \DateTime { return $this->dateSignalement; }
    public function isTraite(): bool { return $this->traite; }
    public function setId(int $id): void { $this->id = $id; }
    public function setTraite(bool $t): void { $this->traite = $t; }
    public function setDateSignalement(\DateTime $d): void { $this->dateSignalement = $d; }
}
