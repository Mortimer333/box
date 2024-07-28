<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Service;

use App\Application\Infrastructure\Exception\AuthenticationException;
use App\Application\Port\Primary\TransactionServiceInterface;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\RetrieveTransactionRepositoryInterface;
use App\Application\Port\Secondary\TransactionChainLinkInterface;
use App\Application\Port\Secondary\TransactionValidatorInterface;
use App\Application\Port\Secondary\UserInterface;
use App\Domain\CurrencyEnum;
use App\Domain\Receiver;
use App\Domain\Sender;
use App\Domain\Transfer;

class TransactionService implements TransactionServiceInterface
{
    public function __construct(
        protected TransactionValidatorInterface $transactionValidator,
        protected BankAccountRepositoryInterface $bankAccountRepository,
        protected TransactionChainLinkInterface $transferChainRoot,
        protected RetrieveTransactionRepositoryInterface $retrieveTransactionRepository,
    ) {
    }

    public function process(
        UserInterface $user,
        int $senderAccountId,
        string $receiverAccountIdentifier,
        string $title,
        string $receiverName,
        float $amount,
        ?string $address = null,
    ): void {
        if ($this->transactionValidator->ownsSelectedBankAccount($user, $senderAccountId)) {
            throw new AuthenticationException('Cannot move credit from not owned bank account');
        }

        /** @var BankAccountInterface $senderAccount */
        $senderAccount = $this->bankAccountRepository->lockOptimistic($senderAccountId);

        /** @var CurrencyEnum $currency */
        $currency = $senderAccount->getCurrency();

        $transaction = new Transfer(
            new Sender(
                (string) $senderAccount->getAccountNumber(),
                $senderAccount->getCredit() - $senderAccount->getReserved(),
                $this->getSumOfTransactionsFromToday(),
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
    }

    protected function getSumOfTransactionsFromToday(): int
    {
        $now = new \DateTime();

        return $this->retrieveTransactionRepository->retrieveSumBetweenDateWithoutFailures(
            new \DateTime($now->format('Y-m-d') . ' 00:00:00'),
            new \DateTime($now->format('Y-m-d') . ' 23:59:59'),
        );
    }
}
