<?php

namespace App\Adapter\Secondary\Repository;

use App\Adapter\Secondary\Entity\Transaction;
use App\Application\Infrastructure\Exception\TransactionNotFoundException;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\TransactionInterface;
use App\Application\Port\Secondary\TransactionRepositoryInterface;
use App\Domain\Transfer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository implements TransactionRepositoryInterface
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

    public function retrieveBetweenDate(\DateTime $from, \DateTime $to, int $limit = 10, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->andWhere('t.created BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
        ;

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        if ($offset > -1) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getArrayResult();
    }
}
