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
        switch ($this->value) {
            case self::NON_DEMANDE:
                return 'Non demandé';
            case self::EN_ATTENTE:
                return 'En attente de validation';
            case self::VALIDE:
                return 'Validé';
            case self::REFUSE:
                return 'Refusé';
            default:
                return $this->value;
        }
    }

    public function getValue(): string { return $this->value; }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
