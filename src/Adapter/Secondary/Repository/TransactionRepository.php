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
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
final class TransactionRepository extends ServiceEntityRepository implements
    RetrieveTransactionRepositoryInterface,
    StoreTransactionRepositoryInterface
{
    public const TRANSACTION_CACHE_SUM_KEY_PREFIX = 'transaction_sum_';

    public function __construct(
        ManagerRegistry $registry,
        protected CacheItemPoolInterface $cacheItemPool,
    ) {
        parent::__construct($registry, Transaction::class);
    }

    public function get(int $id): TransactionInterface
    {
        return $this->find($id) ?? throw new TransactionNotFoundException($id);
    }

    public function create(Transfer $transfer, BankAccountInterface $sender): TransactionInterface
    {
        $this->cacheItemPool->deleteItem(self::TRANSACTION_CACHE_SUM_KEY_PREFIX . ((int) $sender->getId()));

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

    public function retrieveSumBetweenDateWithoutFailures(
        \DateTime $from,
        \DateTime $to,
        int $bankAccountId,
    ): int {
        $item = $this->cacheItemPool->getItem(self::TRANSACTION_CACHE_SUM_KEY_PREFIX . $bankAccountId);
        if ($item->isHit()) {
            return (int) $item->get();
        }

        $qb = $this->createQueryBuilder('t');
        $qb
            ->select('SUM(t.id) as sum')
            ->andWhere('t.created BETWEEN :from AND :to')
            ->andWhere('t.status NOT IN (:statuses)')
            ->andWhere('t.id = :id')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('statuses', [TransactionStatusEnum::Failed])
            ->setParameter('id', $bankAccountId)
        ;

        $sum = (int) $qb->getQuery()->getOneOrNullResult();

        $item->set($sum);
        $this->cacheItemPool->save($item);

        return $sum;
    }
}
