<?php
declare(strict_types=1);
namespace App\Traits;

trait Timestampable
{
    private ?\DateTime $createdAt = null;
    private ?\DateTime $updatedAt = null;

    public function getCreatedAt(): ?\DateTime { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTime { return $this->updatedAt; }

    public function setCreatedAt(\DateTime $date): void { $this->createdAt = $date; }
    public function setUpdatedAt(\DateTime $date): void { $this->updatedAt = $date; }

    public function touch(): void
    {
        $this->updatedAt = new \DateTime();
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
    }
}
