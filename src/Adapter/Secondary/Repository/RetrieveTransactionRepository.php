<?php

namespace App\Adapter\Secondary\Repository;

use App\Adapter\Secondary\Entity\Transaction;
use App\Application\Infrastructure\Exception\TransactionNotFoundException;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\RetrieveTransactionRepositoryInterface;
use App\Application\Port\Secondary\StoreTransactionRepositoryInterface;
use App\Application\Port\Secondary\TransactionInterface;
use App\Domain\TransactionStatusEnum;
use App\Domain\Transfer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class RetrieveTransactionRepository extends ServiceEntityRepository implements
    RetrieveTransactionRepositoryInterface,
    StoreTransactionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function get(int $id): TransactionInterface
    {
        return $this->find($id) ?? throw new TransactionNotFoundException($id);
    }

    public function create(Transfer $transfer, BankAccountInterface $sender): TransactionInterface
    {
        $transaction = (new Transaction())
            ->setType(
                $transfer->type
                    ?? throw new \InvalidArgumentException(
                        'Transaction type not determinated',
                        Response::HTTP_INTERNAL_SERVER_ERROR,
                    )
            )->setAmount($transfer->getAmount())
            ->setAddress($transfer->receiver->address)
            ->setTitle($transfer->title)
            ->setReceiver($transfer->receiver->name)
            ->setReceiverAccountNumber($transfer->receiver->bankAccountNumber)
            ->setSender($sender)
        ;
        $this->getEntityManager()->persist($transaction);

        return $transaction;
    }

    public function retrieveSumBetweenDateWithoutFailures(\DateTime $from, \DateTime $to): int
    {
        // @TODO Cache the result
        $qb = $this->createQueryBuilder('t');
        $qb
            ->select('SUM(t.id) as sum')
            ->andWhere('t.created BETWEEN :from AND :to')
            ->andWhere('t.status NOT IN (:statuses)')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('statuses', [TransactionStatusEnum::Failed])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
