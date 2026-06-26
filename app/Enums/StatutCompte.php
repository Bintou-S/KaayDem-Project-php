<?php
declare(strict_types=1);
namespace App\Enums;

class StatutCompte
{
    const EN_ATTENTE = 'en_attente';
    const ACTIF      = 'actif';
    const SUSPENDU   = 'suspendu';

    private string $value;

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
            case self::ACTIF:
                return 'Actif';
            case self::SUSPENDU:
                return 'Suspendu';
            default:
                return $this->value;
        }
    }

    public function __get(string $name)
    {
        if ($name === 'value') return $this->value;
    }

    public function getValue(): string { return $this->value; }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
    
}
