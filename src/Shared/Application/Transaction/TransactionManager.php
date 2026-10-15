<?php
namespace App\Shared\Application\Transaction;

interface TransactionManager
{
    /**
     * Executes an operation within a transaction.
     *
     * @param callable $operation
     *
     * @return mixed
     */
    public function execute(callable $operation): mixed;
}
