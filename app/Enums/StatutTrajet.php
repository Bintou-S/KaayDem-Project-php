<?php
declare(strict_types=1);
namespace App\Enums;

class StatutTrajet
{
    const OUVERT  = 'ouvert';
    const COMPLET = 'complet';
    const CLOTURE = 'cloture';
    const ANNULE  = 'annule';

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
            self::OUVERT  => 'Ouvert',
            self::COMPLET => 'Complet',
            self::CLOTURE => 'Clôturé',
            self::ANNULE  => 'Annulé',
            default       => $this->value,
        };
    }

    public function getValue(): string { return $this->value; }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
