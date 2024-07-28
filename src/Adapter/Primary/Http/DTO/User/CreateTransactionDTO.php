<?php

declare(strict_types=1);

namespace App\Adapter\Primary\Http\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
final readonly class CreateTransactionDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Sender Account Identifier cannot be blank')]
        #[Assert\Type(type: 'integer', message: 'Sender Account Identifier must be an integer')]
        public ?int $senderAccountId,
        #[Assert\NotBlank(message: 'Receiver Account Number cannot be blank')]
        #[Assert\Type(type: 'string', message: 'Receiver Account Number must be a string')]
        public ?string $receiverAccountNumber,
        #[Assert\NotBlank(message: 'Transaction title cannot be blank')]
        #[Assert\Type(type: 'string', message: 'Transaction title must be a string')]
        public ?string $title,
        #[Assert\Type(type: 'string', message: 'Address must be a string')]
        public ?string $address,
        #[Assert\NotBlank(message: 'Receiver name cannot be blank')]
        #[Assert\Type(type: 'string', message: 'Receiver name must be a string')]
        public ?string $receiverName,
        #[Assert\NotBlank(message: 'Amount cannot be blank')]
        #[Assert\Type(type: 'float', message: 'Amount must be a float')]
        public ?float $amount,
    ) {
    }
}
