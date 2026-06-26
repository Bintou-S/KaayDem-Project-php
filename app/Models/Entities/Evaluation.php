<?php

declare(strict_types=1);

namespace App\Models\Entities;

use App\Traits\Timestampable;

class Evaluation
{
    use Timestampable;

    private ?int $id = null;
    private Reservation $reservation;
    private Membre $evaluateur;
    private Membre $evalue;
    private int $note;
    private string $commentaire;
    private \DateTime $dateEvaluation;

    public function __construct(
        Reservation $reservation,
        Membre $evaluateur,
        Membre $evalue,
        int $note,
        string $commentaire,
        ?int $id = null
    ) {
        $this->reservation    = $reservation;
        $this->evaluateur     = $evaluateur;
        $this->evalue         = $evalue;
        $this->note           = $note;
        $this->commentaire    = $commentaire;
        $this->dateEvaluation = new \DateTime();
        $this->id             = $id;
    }

    public function estValide(): bool
    {
        return $this->note >= 1 && $this->note <= 5 && !empty($this->commentaire);
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getReservation(): Reservation { return $this->reservation; }
    public function getEvaluateur(): Membre { return $this->evaluateur; }
    public function getEvalue(): Membre { return $this->evalue; }
    public function getNote(): int { return $this->note; }
    public function getCommentaire(): string { return $this->commentaire; }
    public function getDateEvaluation(): \DateTime { return $this->dateEvaluation; }
    public function setId(int $id): void { $this->id = $id; }
    public function setDateEvaluation(\DateTime $date): void { $this->dateEvaluation = $date; }
    
}
