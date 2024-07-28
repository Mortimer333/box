<?php

namespace App\Adapter\Secondary\Repository;

use App\Adapter\Secondary\Entity\Transaction;
use App\Application\Infrastructure\Exception\TransactionNotFoundException;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\RetrieveTransactionRepositoryInterface;
use App\Application\Port\Secondary\StoreTransactionRepositoryInterface;
use App\Application\Port\Secondary\TransactionInterface;
use App\Domain\CurrencyEnum;
use App\Domain\TransactionStatusEnum;
use App\Domain\TransactionTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;

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

    public function create(
        BankAccountInterface $sender,
        TransactionTypeEnum $type,
        CurrencyEnum $currency,
        float $amount,
        float $commissionFee,
        string $title,
        string $receiver,
        string $receiverAccountNumber,
        ?string $address = null,
    ): TransactionInterface {
        $this->cacheItemPool->deleteItem(self::TRANSACTION_CACHE_SUM_KEY_PREFIX . ((int) $sender->getId()));

        $transaction = (new Transaction())
            ->setType($type)
            ->setCurrency($currency)
            ->setAmount($amount)
            ->setAddress($address)
            ->setTitle($title)
            ->setReceiver($receiver)
            ->setReceiverAccountNumber($receiverAccountNumber)
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
