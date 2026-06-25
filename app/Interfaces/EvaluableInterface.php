<?php
declare(strict_types=1);
namespace App\Interfaces;

interface EvaluableInterface
{
    public function getNoteMoyenne(): float;
    public function getNombreEvaluations(): int;
    public function ajouterEvaluation(int $note): void;
}
