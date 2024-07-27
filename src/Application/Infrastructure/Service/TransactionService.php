<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Service;

use App\Application\Infrastructure\COR\TransferChain\TransferChainRoot;
use App\Application\Infrastructure\Exception\AuthenticationException;
use App\Application\Port\Primary\TransactionServiceInterface;
use App\Application\Port\Primary\TransactionValidatorInterface;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
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
        protected TransferChainRoot $transferChainRoot,
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
}
