<?php

declare(strict_types=1);

namespace App\Adapter\Secondary\DataFixtures\Test;

use App\Adapter\Secondary\DataFixtures\TestFixturesAbstract;
use App\Adapter\Secondary\Entity\BankAccount;
use App\Adapter\Secondary\Entity\User;
use App\Domain\CurrencyEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
class BankAccountFixtures extends TestFixturesAbstract implements DependentFixtureInterface
{
    public const NORMAL_ACCOUNT_ONE = 'account_one';
    public const NORMAL_ACCOUNT_TWO = 'account_tow';
    public const ACCOUNT_NUMBER_ONE = 'PL61109010140000071219812874';
    public const ACCOUNT_NUMBER_TWO = 'PL61109010140000071219812875';

    public static function getGroups(): array
    {
        return array_merge(['bank_account'], parent::getGroups());
    }

    public function load(ObjectManager $manager): void
    {
        /** @var User $owner */
        $owner = $this->getReference(UserFixtures::NORMAL_USER);
        $bankAccount = (new BankAccount())
            ->setCurrency(CurrencyEnum::PLN)
            ->setCredit(100)
            ->setAccountNumber(self::ACCOUNT_NUMBER_ONE)
            ->setOwner($owner)
        ;

        $bankAccount2 = (new BankAccount())
            ->setCurrency(CurrencyEnum::PLN)
            ->setCredit(100)
            ->setAccountNumber(self::ACCOUNT_NUMBER_TWO)
            ->setOwner($owner)
        ;

        $manager->persist($bankAccount);
        $manager->persist($bankAccount2);
        $this->setReference(self::NORMAL_ACCOUNT_ONE, $bankAccount);
        $this->setReference(self::NORMAL_ACCOUNT_TWO, $bankAccount2);
        $manager->flush();
    }

    /**
     * @return array<string>
     */
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
