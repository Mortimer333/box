<?php

declare(strict_types=1);

namespace App\Adapter\Primary\Http\Controller\User;

use App\Adapter\Primary\Http\DTO\User\CreateTransactionDTO;
use App\Application\Port\Primary\TransactionServiceInterface;
use App\Application\Port\Secondary\UserInterface;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[SWG\Tag('User Transactions')]
#[Route('transaction', name: 'api_user_transaction_')]
final class TransactionController extends AbstractController
{
    public function __construct(
        protected Security $security,
        protected TransactionServiceInterface $transactionService,
    ) {
    }

    #[Route('/create', name: 'create', methods: 'POST')]
    public function create(#[MapRequestPayload] CreateTransactionDTO $createTransactionModel): JsonResponse
    {
        $user = $this->security->getUser();
        if (!($user instanceof UserInterface)) {
            throw new AuthenticationException('Cannot create new transactions when not authorized');
        }

        $this->transactionService->process(
            $user,
            (int) $createTransactionModel->senderAccountId,
            (string) $createTransactionModel->receiverAccountNumber,
            (string) $createTransactionModel->title,
            (string) $createTransactionModel->receiverName,
            (int) $createTransactionModel->amount,
            (string) $createTransactionModel->address,
        );

        return $this->json(['success' => true]);
    }
}
