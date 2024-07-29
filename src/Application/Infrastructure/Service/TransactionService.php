<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Service;

use App\Application\Port\Primary\TransactionServiceInterface;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\DatabaseManagerInterface;
use App\Application\Port\Secondary\RetrieveTransactionRepositoryInterface;
use App\Application\Port\Secondary\TransactionChainLinkInterface;
use App\Domain\ConfigurationException;
use App\Domain\CurrencyEnum;
use App\Domain\Receiver;
use App\Domain\Sender;
use App\Domain\Transfer;
use App\Domain\ValidationException;

final readonly class TransactionService implements TransactionServiceInterface
{
    public function __construct(
        protected BankAccountRepositoryInterface $bankAccountRepository,
        protected TransactionChainLinkInterface $transferChainRoot,
        protected RetrieveTransactionRepositoryInterface $retrieveTransactionRepository,
        protected DatabaseManagerInterface $databaseManager,
    ) {
    }

    public function process(
        int $userId,
        int $senderAccountId,
        string $receiverAccountIdentifier,
        string $title,
        string $receiverName,
        float $amount,
        ?string $address = null,
    ): void {
        $maxTry = $_ENV['MAX_PROCESS_RETRY_COUNT']
            ?? throw new ConfigurationException('Maximal retry count for transfer is not configured')
        ;
        for ($currentTry = 0; $currentTry < $maxTry; ++$currentTry) {
            try {
                if (!$this->databaseManager->hasActiveTransaction()) {
                    $this->databaseManager->beginTransaction();
                }

                /** @var BankAccountInterface $senderAccount */
                $senderAccount = $this->bankAccountRepository->lockOptimistic($senderAccountId);

                /** @var CurrencyEnum $currency */
                $currency = $senderAccount->getCurrency();

                $transaction = new Transfer(
                    new Sender(
                        $userId,
                        (string) $senderAccount->getAccountNumber(),
                        (float) $senderAccount->getCredit(),
                        (float) $senderAccount->getReserved(),
                        $this->getSumOfTransactionsFromToday((int) $senderAccount->getId()),
                    ),
                    new Receiver(
                        $receiverAccountIdentifier,
                        $receiverName,
                        $address,
                    ),
                    $title,
                    $currency,
                    $amount,
                );

                $this->transferChainRoot->process($transaction, $senderAccount);
                break;
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                $this->databaseManager->rollback();
                $this->databaseManager->clear();

                if ($maxTry <= $currentTry + 1) {
                    throw $e;
                }

                $this->databaseManager->reconnectIfNecessary();
            }
        }
    }

    protected function getSumOfTransactionsFromToday(int $senderAccountId): int
    {
        $now = new \DateTime();

        return $this->retrieveTransactionRepository->retrieveSumBetweenDateWithoutFailures(
            new \DateTime($now->format('Y-m-d') . ' 00:00:00'),
            new \DateTime($now->format('Y-m-d') . ' 23:59:59'),
            $senderAccountId,
        );
    }
}
