<?php
namespace App\Shared\Infrastructure\Persistence\Doctrine;

use App\Shared\Application\Transaction\TransactionManager;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineTransactionManager implements TransactionManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * Executes an operation within a Doctrine transaction.
     *
     * @param callable $operation
     *
     * @return mixed
     */
    public function execute(callable $operation): mixed
    {
        return $this->entityManager->wrapInTransaction($operation);
    }
}
