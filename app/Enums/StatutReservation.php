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
        return match($this->value) {
            self::EN_ATTENTE => 'En attente',
            self::CONFIRMEE  => 'Confirmée',
            self::TERMINEE   => 'Terminée',
            self::ANNULEE    => 'Annulée',
            default          => $this->value,
        };
    }

    public function getValue(): string { return $this->value; }

    public function peutTransitionnerVers(self $nouveau): bool
    {
        return match($this->value) {
            self::EN_ATTENTE => in_array($nouveau->value, [self::CONFIRMEE, self::ANNULEE]),
            self::CONFIRMEE  => in_array($nouveau->value, [self::TERMINEE, self::ANNULEE]),
            default          => false,
        };
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
