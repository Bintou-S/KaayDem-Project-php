<?php
declare(strict_types=1);
namespace App\Models\Repositories;

use App\Core\Database;

abstract class Repository
{
    protected \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    abstract public function find(int $id);
    abstract public function findAll(): array;
    abstract public function save($entity): bool;
    abstract public function delete(int $id): bool;
    
}
