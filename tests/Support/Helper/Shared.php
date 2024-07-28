<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Domain\CurrencyEnum;
use App\Domain\ExternalTransfer;
use App\Domain\ExternalTransferSender;
use App\Domain\Receiver;
use App\Domain\Sender;
use App\Domain\TransactionTypeEnum;
use App\Domain\Transfer;
use Faker\Factory;
use Faker\Provider\Base as FakerBase;

class Shared extends \Codeception\Module
{
    /**
     * @template T
     *
     * @param class-string<T> $service
     *
     * @return T
     *
     * @throws \Exception
     */
    public function getService(string $service)
    {
        return $this->getModule('Symfony')->_getContainer()->get($service);
    }

    public function generateAccountNumber(): string
    {
        return FakerBase::bothify(str_repeat('*', 34));
    }

    public function generateExternalTransfer(
        ?string $senderBankAccountNumber = null,
        ?float $senderBankAccountCredit = null,
        ?float $senderBankAccountReserved = null,
        ?float $amount = null,
        ?float $commissionFee = null,
    ): ExternalTransfer {
        $senderBankAccountNumber ??= $this->generateAccountNumber();
        $senderBankAccountCredit ??= FakerBase::numberBetween(0, 100);
        $amount ??= FakerBase::numberBetween(0, 100);
        $senderBankAccountReserved ??= FakerBase::numberBetween(0, 100);
        if ($senderBankAccountReserved > $senderBankAccountCredit) {
            $senderBankAccountReserved = $senderBankAccountCredit;
        }

        return new ExternalTransfer(
            new ExternalTransferSender(
                $senderBankAccountNumber,
                $senderBankAccountCredit,
                $senderBankAccountReserved,
            ),
            $amount,
            $commissionFee,
        );
    }

    public function generateTransfer(
        ?int $userId = 0,
        ?string $senderBankAccountNumber = null,
        ?float $senderBankAccountCredit = null,
        ?float $senderBankAccountReserved = null,
        ?int $senderTransactionsDoneToday = null,
        ?string $receiverBankAccountNumber = null,
        ?string $receiverName = null,
        ?string $receiverAddress = null,
        ?string $title = null,
        ?CurrencyEnum $currency = null,
        ?float $amount = null,
        ?TransactionTypeEnum $type = null,
    ): Transfer {
        $faker = Factory::create();

        $senderBankAccountNumber ??= $this->generateAccountNumber();
        $senderBankAccountCredit ??= FakerBase::numberBetween(0, 100);
        $senderBankAccountReserved ??= FakerBase::numberBetween(0, 100);
        if ($senderBankAccountReserved > $senderBankAccountCredit) {
            $senderBankAccountReserved = $senderBankAccountCredit;
        }
        $senderTransactionsDoneToday ??= FakerBase::numberBetween(0, 3);
        $receiverBankAccountNumber ??= $this->generateAccountNumber();
        $receiverName ??= $faker->name();
        $title ??= $faker->title();
        $currency ??= FakerBase::numberBetween(0, 1) ? CurrencyEnum::PLN : CurrencyEnum::USD;
        $amount ??= FakerBase::numberBetween(0, 100);

        return new Transfer(
            new Sender(
                $userId,
                $senderBankAccountNumber,
                $senderBankAccountCredit,
                $senderBankAccountReserved,
                $senderTransactionsDoneToday,
            ),
            new Receiver(
                $receiverBankAccountNumber,
                $receiverName,
                $receiverAddress,
            ),
            $title,
            $currency,
            $amount,
            $type,
        );
    }
}
