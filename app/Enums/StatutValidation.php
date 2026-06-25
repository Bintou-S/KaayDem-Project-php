<?php
declare(strict_types=1);
namespace App\Enums;

class StatutValidation
{
    const NON_DEMANDE = 'non_demande';
    const EN_ATTENTE  = 'en_attente';
    const VALIDE      = 'valide';
    const REFUSE      = 'refuse';

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
            self::NON_DEMANDE => 'Non demandé',
            self::EN_ATTENTE  => 'En attente de validation',
            self::VALIDE      => 'Validé',
            self::REFUSE      => 'Refusé',
            default           => $this->value,
        };
    }

    public function getValue(): string { return $this->value; }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
