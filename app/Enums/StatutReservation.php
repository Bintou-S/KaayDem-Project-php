<?php
declare(strict_types=1);
namespace App\Enums;

class StatutReservation
{
    const EN_ATTENTE = 'en_attente';
    const CONFIRMEE  = 'confirmee';
    const TERMINEE   = 'terminee';
    const ANNULEE    = 'annulee';

    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function from(string $value): self
    {
        return new self($value);
    }

    public function libelle(): string
    {
        switch ($this->value) {
            case self::EN_ATTENTE:
                return 'En attente';
            case self::CONFIRMEE:
                return 'Confirmée';
            case self::TERMINEE:
                return 'Terminée';
            case self::ANNULEE:
                return 'Annulée';
            default:
                return $this->value;
        }
    }

    public function getValue(): string { return $this->value; }

    public function peutTransitionnerVers(self $nouveau): bool
    {
        switch ($this->value) {
            case self::EN_ATTENTE:
                return in_array($nouveau->value, [self::CONFIRMEE, self::ANNULEE], true);
            case self::CONFIRMEE:
                return in_array($nouveau->value, [self::TERMINEE, self::ANNULEE], true);
            default:
                return false;
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}