<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Infrastructure\Exception\AuthenticationException;
use App\Application\Infrastructure\Exception\BankAccountNotFoundException;
use App\Application\Infrastructure\Exception\UserNotFoundException;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\TransactionChainLinkInterface;
use App\Application\Port\Secondary\UserRepositoryInterface;
use App\Domain\Transfer;

final readonly class ClientOwnsSenderBankAccount implements TransactionChainLinkInterface
{
    public function __construct(
        private TransactionChainLinkInterface $next,
        protected UserRepositoryInterface $userRepository,
        protected BankAccountRepositoryInterface $bankAccountRepository,
    ) {
    }

    public function process(
        Transfer $transfer,
        BankAccountInterface $sender,
    ): void {
        if (!$this->userOwnsSelectedBankAccount((int) $sender->getId(), $transfer->sender->userId)) {
            throw new AuthenticationException('Cannot move credit from not owned bank account', 401);
        }

        $this->next->process($transfer, $sender);
    }

    protected function userOwnsSelectedBankAccount(int $bankAccountId, int $ownerId): bool
    {
        try {
            $bankAccount = $this->bankAccountRepository->get($bankAccountId);
            $owner = $this->userRepository->get($ownerId);

            if ($bankAccount->getOwner()?->getId() === $owner->getId()) {
                return false;
            }
        } catch (BankAccountNotFoundException | UserNotFoundException) {
            return false;
        }

        return true;
    }
}
