<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\COR;

use App\Adapter\Secondary\Entity\BankAccount;
use App\Adapter\Secondary\Entity\User;
use App\Application\Infrastructure\COR\TransferChain\ClientDailyTransferLimitExceeded;
use App\Application\Infrastructure\COR\TransferChain\ClientOwnsSenderBankAccount;
use App\Application\Infrastructure\COR\TransferChain\InternalTransfer;
use App\Application\Infrastructure\COR\TransferChain\ValidateExternalTransferAmount;
use App\Application\Infrastructure\COR\TransferChain\ValidateTransferAmount;
use App\Application\Infrastructure\Exception\AuthenticationException;
use App\Application\Infrastructure\Exception\InvalidLinkCallException;
use App\Application\Infrastructure\Message\ProcessTransactionMessage;
use App\Application\Infrastructure\Message\TransferCommissionFeeFoundsMessage;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\DatabaseManagerInterface;
use App\Application\Port\Secondary\MessageBusInterface;
use App\Application\Port\Secondary\StoreTransactionRepositoryInterface;
use App\Application\Port\Secondary\TransactionChainLinkInterface;
use App\Domain\CurrencyEnum;
use App\Domain\CurrencyMismatchException;
use App\Domain\DailyLimitExceededException;
use App\Domain\NotEnoughCreditException;
use App\Domain\Transfer;
use App\Tests\Support\Override\BankAccountRepository;
use App\Tests\Support\Override\DatabaseManager;
use App\Tests\Support\Override\MessageBus;
use App\Tests\Unit\BaseUnitAbstract;
use Codeception\Stub\Expected;

/**
 * @covers \App\Application\Infrastructure\COR\TransferChain\TransferChainRootLink
 * @covers \App\Application\Infrastructure\COR\TransferChain\ClientOwnsSenderBankAccount
 * @covers \App\Application\Infrastructure\COR\TransferChain\ClientDailyTransferLimitExceeded
 * @covers \App\Application\Infrastructure\COR\TransferChain\ValidateTransferAmount
 * @covers \App\Application\Infrastructure\COR\TransferChain\ValidateExternalTransferAmount
 * @covers \App\Application\Infrastructure\COR\TransferChain\DeterminateTransferType
 * @covers \App\Application\Infrastructure\COR\TransferChain\BankToBankTransaction
 * @covers \App\Application\Infrastructure\COR\TransferChain\InternalTransfer
 * @covers \App\Application\Infrastructure\COR\TransferChain\FinishChain
 */
class TransferChainTest extends BaseUnitAbstract
{
    protected static ?BankAccountInterface $sender = null;
    protected static ?BankAccountInterface $receiver = null;

    public function _after(): void
    {
        parent::_after();
        self::$sender = self::$receiver = null;
        MessageBus::$mock = BankAccountRepository::$mock = DatabaseManager::$mock = null;
    }

    public function testSuccessfulInternalTransaction(): void
    {
        $senderCredit = 50;
        $receiverCredit = 50;
        $userId = 1;
        $this->assertDatabasePersistence();
        self::$sender = $this->makeEmpty(BankAccount::class, [
            'getId' => 2,
            'setCredit' => Expected::once(function () {
                return self::$sender;
            }),
            'getCredit' => Expected::once($senderCredit),
            'getOwner' => $this->makeEmpty(User::class, [
                'getId' => $userId,
            ]),
        ]);

        self::$receiver = $this->makeEmpty(BankAccount::class, [
            'getCurrency' => CurrencyEnum::PLN,
            'setCredit' => Expected::once(function () {
                return self::$receiver;
            }),
            'getCredit' => Expected::once($receiverCredit),
        ]);

        $this->setBankRepositoryMock([
            'get' => self::$sender,
            'getByIdentifier' => $this->makeEmpty(BankAccount::class),
            'lockOptimistic' => self::$receiver,
        ]);
        MessageBus::$mock = $this->makeEmpty(MessageBusInterface::class, [
            'dispatch' => Expected::once(function (mixed $message) {
                $this->tester->assertInstanceOf(TransferCommissionFeeFoundsMessage::class, $message);
            }),
        ]);
        $transfer = $this->tester->generateTransfer(
            $userId,
            senderTransactionsDoneToday: 0,
            senderBankAccountCredit: $senderCredit,
            senderBankAccountReserved: 0,
            amount: 10,
            currency: CurrencyEnum::PLN,
        );

        $this->startChain($transfer, self::$sender);
    }

    public function testSuccessfulBankToBankTransaction(): void
    {
        $userId = 1;
        $this->assertDatabasePersistence();
        self::$sender = $this->makeEmpty(BankAccount::class, [
            'getId' => 2,
            'setCredit' => Expected::never(),
            'setReserved' => Expected::once(function () {
                return self::$sender;
            }),
            'getOwner' => $this->makeEmpty(User::class, [
                'getId' => $userId,
            ]),
        ]);

        $this->setBankRepositoryMock([
            'get' => self::$sender,
            'lockOptimistic' => Expected::never(),
        ]);
        MessageBus::$mock = $this->makeEmpty(MessageBusInterface::class, [
            'dispatch' => Expected::once(function (mixed $message) {
                $this->tester->assertInstanceOf(ProcessTransactionMessage::class, $message);
            }),
        ]);
        $transfer = $this->tester->generateTransfer(
            $userId,
            senderTransactionsDoneToday: 0,
            senderBankAccountCredit: 50,
            senderBankAccountReserved: 0,
            amount: 10,
            currency: CurrencyEnum::PLN,
        );

        $this->startChain($transfer, self::$sender);
    }

    public function testExceptionIsThrownOnNotOwnedAccount(): void
    {
        self::$sender = $this->makeEmpty(BankAccount::class, [
            'getId' => 0,
        ]);

        $transfer = $this->tester->generateTransfer(
            1,
            senderTransactionsDoneToday: 0,
            senderBankAccountCredit: 50,
            senderBankAccountReserved: 0,
            amount: 10,
            currency: CurrencyEnum::PLN,
        );
        $this->setBankRepositoryMock(['get' => self::$sender]);
        $this->expectException(AuthenticationException::class);
        try {
            $this->startChain($transfer, self::$sender);
        } catch (AuthenticationException $e) {
            $this->assertThrower(ClientOwnsSenderBankAccount::class, $e);
            throw $e;
        }
    }

    public function testExceptionIsThrownOnNotExistingAccount(): void
    {
        self::$sender = $this->makeEmpty(BankAccount::class, [
            'getId' => 0,
        ]);

        $transfer = $this->tester->generateTransfer(
            1,
            senderTransactionsDoneToday: 0,
            senderBankAccountCredit: 50,
            senderBankAccountReserved: 0,
            amount: 10,
            currency: CurrencyEnum::PLN,
        );
        $this->expectException(AuthenticationException::class);
        try {
            $this->startChain($transfer, self::$sender);
        } catch (AuthenticationException $e) {
            $this->assertThrower(ClientOwnsSenderBankAccount::class, $e);
            throw $e;
        }
    }

    public function testExceptionIsThrownOnDailyLimitExceeded(): void
    {
        $userId = 1;
        $this->setDefaultSender($userId);

        $transfer = $this->tester->generateTransfer(
            $userId,
            senderTransactionsDoneToday: (int) $_ENV['MAX_DAILY_TRANSACTION_LIMIT'],
            senderBankAccountCredit: 50,
            senderBankAccountReserved: 0,
            amount: 10,
            currency: CurrencyEnum::PLN,
        );
        $this->setBankRepositoryMock(['get' => self::$sender]);
        $this->expectException(DailyLimitExceededException::class);
        try {
            $this->startChain($transfer, self::$sender);
        } catch (DailyLimitExceededException $e) {
            $this->assertThrower(ClientDailyTransferLimitExceeded::class, $e);
            throw $e;
        }
    }

    public function testExceptionIsThrownOnInsufficientFoundsExternal(): void
    {
        $userId = 1;
        $this->setDefaultSender($userId);

        $transfer = $this->tester->generateTransfer(
            $userId,
            senderTransactionsDoneToday: 0,
            senderBankAccountCredit: 10,
            senderBankAccountReserved: 0,
            amount: 10,
            currency: CurrencyEnum::PLN,
        );
        $this->setBankRepositoryMock(['get' => self::$sender]);
        $this->expectException(NotEnoughCreditException::class);
        try {
            $this->startChain($transfer, self::$sender);
        } catch (NotEnoughCreditException $e) {
            $this->assertThrower(ValidateExternalTransferAmount::class, $e);
            throw $e;
        }
    }

    public function testExceptionIsThrownOnInsufficientFoundsReservedExternal(): void
    {
        $userId = 1;
        $this->setDefaultSender($userId);

        $transfer = $this->tester->generateTransfer(
            $userId,
            senderTransactionsDoneToday: 0,
            senderBankAccountCredit: 20,
            senderBankAccountReserved: 11,
            amount: 10,
            currency: CurrencyEnum::PLN,
        );
        $this->setBankRepositoryMock(['get' => self::$sender]);
        $this->expectException(NotEnoughCreditException::class);
        try {
            $this->startChain($transfer, self::$sender);
        } catch (NotEnoughCreditException $e) {
            $this->assertThrower(ValidateExternalTransferAmount::class, $e);
            throw $e;
        }
    }

    public function testExceptionIsThrownOnInsufficientFoundsInternal(): void
    {
        $userId = 1;
        $this->setDefaultSender($userId);
        $receiver = $this->makeEmpty(BankAccount::class, ['getCurrency' => CurrencyEnum::PLN]);
        $this->setBankRepositoryMock([
            'get' => self::$sender,
            'getByIdentifier' => $this->makeEmpty(BankAccount::class),
            'lockOptimistic' => $receiver,
        ]);

        $transfer = $this->tester->generateTransfer(
            $userId,
            senderTransactionsDoneToday: 0,
            senderBankAccountCredit: 10,
            senderBankAccountReserved: 0,
            amount: 10,
            currency: CurrencyEnum::PLN,
        );
        $this->expectException(NotEnoughCreditException::class);
        try {
            $this->startChain($transfer, self::$sender);
        } catch (NotEnoughCreditException $e) {
            $this->assertThrower(ValidateTransferAmount::class, $e);
            throw $e;
        }
    }

    public function testExceptionIsThrownOnInsufficientFoundsReservedInternal(): void
    {
        $userId = 1;
        $this->setDefaultSender($userId);
        $receiver = $this->makeEmpty(BankAccount::class, ['getCurrency' => CurrencyEnum::PLN]);
        $this->setBankRepositoryMock([
            'get' => self::$sender,
            'getByIdentifier' => $this->makeEmpty(BankAccount::class),
            'lockOptimistic' => $receiver,
        ]);

        $transfer = $this->tester->generateTransfer(
            $userId,
            senderTransactionsDoneToday: 0,
            senderBankAccountCredit: 20,
            senderBankAccountReserved: 11,
            amount: 10,
            currency: CurrencyEnum::PLN,
        );
        $this->expectException(NotEnoughCreditException::class);
        try {
            $this->startChain($transfer, self::$sender);
        } catch (NotEnoughCreditException $e) {
            $this->assertThrower(ValidateTransferAmount::class, $e);
            throw $e;
        }
    }

    public function testExceptionIsThrownOnCurrencyMissMatch(): void
    {
        $senderCredit = 50;
        $userId = 1;
        self::$sender = $this->makeEmpty(BankAccount::class, [
            'getOwner' => $this->makeEmpty(User::class, [
                'getId' => $userId,
            ]),
        ]);

        self::$receiver = $this->makeEmpty(BankAccount::class, [
            'getCurrency' => CurrencyEnum::USD,
        ]);

        $this->setBankRepositoryMock([
            'get' => self::$sender,
            'getByIdentifier' => $this->makeEmpty(BankAccount::class),
            'lockOptimistic' => self::$receiver,
        ]);

        $transfer = $this->tester->generateTransfer(
            $userId,
            senderTransactionsDoneToday: 0,
            senderBankAccountCredit: $senderCredit,
            senderBankAccountReserved: 0,
            amount: 10,
            currency: CurrencyEnum::PLN,
        );

        $this->expectException(CurrencyMismatchException::class);
        $this->startChain($transfer, self::$sender);
    }

    public function testExceptionIsThrownOnNonExistingReceiver(): void
    {
        $transfer = $this->tester->generateTransfer();
        $internalTransferLink = new InternalTransfer(
            $this->makeEmpty(TransactionChainLinkInterface::class),
            $this->makeEmpty(StoreTransactionRepositoryInterface::class),
            $this->makeEmpty(DatabaseManagerInterface::class),
            $this->makeEmpty(BankAccountRepositoryInterface::class, [
                'getByIdentifier' => null,
            ]),
            $this->makeEmpty(MessageBusInterface::class),
        );
        $this->expectException(InvalidLinkCallException::class);
        $internalTransferLink->process($transfer, $this->makeEmpty(BankAccountInterface::class));
    }

    protected function assertThrower(string $class, \Throwable $e): void
    {
        $this->tester->assertEquals($class, $e->getTrace()[0]['class'] ?? 'N/A');
    }

    protected function startChain(Transfer $transfer, BankAccountInterface $account): void
    {
        $this->tester->getService(TransactionChainLinkInterface::class)->process($transfer, $account);
    }

    protected function setDefaultSender(int $userId): void
    {
        self::$sender = $this->makeEmpty(BankAccount::class, [
            'getId' => 2,
            'getOwner' => $this->makeEmpty(User::class, [
                'getId' => $userId,
            ]),
        ]);
    }

    protected function assertDatabasePersistence(): void
    {
        DatabaseManager::$mock = $this->makeEmpty(DatabaseManagerInterface::class, [
            'persist' => Expected::once(),
        ]);
    }

    protected function setBankRepositoryMock(array $parameters): void
    {
        BankAccountRepository::$mock = $this->makeEmpty(BankAccountRepositoryInterface::class, $parameters);
    }
}
