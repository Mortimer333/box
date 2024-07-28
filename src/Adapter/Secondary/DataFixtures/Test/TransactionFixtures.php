<?php

declare(strict_types=1);

namespace App\Adapter\Secondary\DataFixtures\Test;

use App\Adapter\Secondary\DataFixtures\TestFixturesAbstract;
use App\Adapter\Secondary\Entity\BankAccount;
use App\Adapter\Secondary\Entity\Transaction;
use App\Adapter\Secondary\Repository\TransactionRepository;
use App\Domain\CurrencyEnum;
use App\Domain\TransactionStatusEnum;
use App\Domain\TransactionTypeEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @codeCoverageIgnore
 */
class TransactionFixtures extends TestFixturesAbstract implements DependentFixtureInterface
{
    public function __construct(
        protected CacheItemPoolInterface $cacheItemPool,
    ) {
    }

    public static function getGroups(): array
    {
        return array_merge(['transaction'], parent::getGroups());
    }

    public function load(ObjectManager $manager): void
    {
        $this->cacheItemPool->deleteItem(TransactionRepository::TRANSACTION_CACHE_SUM_KEY_PREFIX . 1);
        /** @var BankAccount $sender */
        $sender = $this->getReference(BankAccountFixtures::NORMAL_ACCOUNT_ONE);
        $transaction = (new Transaction())
            ->setCurrency(CurrencyEnum::PLN)
            ->setSender($sender)
            ->setTitle('Transaction #1')
            ->setStatus(TransactionStatusEnum::Finished)
            ->setType(TransactionTypeEnum::Internal)
            ->setReceiver('John Smith')
            ->setReceiverAccountNumber(BankAccountFixtures::ACCOUNT_NUMBER_TWO)
            ->setCommissionFee(0.05)
            ->setAmount(10)
        ;
        $transaction2 = (new Transaction())
            ->setCurrency(CurrencyEnum::PLN)
            ->setSender($sender)
            ->setTitle('Transaction #2')
            ->setStatus(TransactionStatusEnum::Awaiting)
            ->setType(TransactionTypeEnum::Internal)
            ->setReceiver('John Smith')
            ->setReceiverAccountNumber(BankAccountFixtures::ACCOUNT_NUMBER_TWO)
            ->setCommissionFee(0.05)
            ->setAmount(10)
        ;
        $transaction3 = (new Transaction())
            ->setCurrency(CurrencyEnum::PLN)
            ->setSender($sender)
            ->setTitle('Transaction #3')
            ->setStatus(TransactionStatusEnum::Failed)
            ->setType(TransactionTypeEnum::Internal)
            ->setReceiver('John Smith')
            ->setReceiverAccountNumber(BankAccountFixtures::ACCOUNT_NUMBER_TWO)
            ->setCommissionFee(0.05)
            ->setAmount(10)
        ;

        $manager->persist($transaction);
        $manager->persist($transaction2);
        $manager->persist($transaction3);
        $manager->flush();
    }

    /**
     * @return array<string>
     */
    public function getDependencies(): array
    {
        return [
            BankAccountFixtures::class,
        ];
    }
}
