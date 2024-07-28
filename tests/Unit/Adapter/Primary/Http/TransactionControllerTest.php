<?php

declare(strict_types=1);

namespace App\Tests\Unit\Adapter\Primary\Http;

use App\Adapter\Secondary\DataFixtures\Test\UserFixtures;
use App\Application\Port\Primary\TransactionServiceInterface;
use Faker\Factory;
use Faker\Provider\Base as FakerBase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \App\Adapter\Primary\Http\Controller\User\TransactionController
 */
class TransactionControllerTest extends WebTestCase
{
    public function testSuccessfullyLoginAndPassValidation(): void
    {
        $faker = Factory::create();
        $userId = 1;
        $senderAccountId = 1;
        $receiverAccountNumber = FakerBase::bothify(str_repeat('*', 34));
        $title = $faker->title();
        $receiverName = $faker->name();
        $amount = FakerBase::numberBetween(10, 100);
        $address = $faker->address();

        $transactionService = $this->createMock(TransactionServiceInterface::class);
        $transactionService->expects(self::once())->method('process')->with(
            $userId,
            $senderAccountId,
            $receiverAccountNumber,
            $title,
            $receiverName,
            $amount,
            $address,
        );

        $client = self::createClient();
        self::getContainer()->set(TransactionServiceInterface::class, $transactionService);

        $client->request(
            method: 'POST',
            uri: '/_/user/transaction/create',
            parameters: [
                'senderAccountId' => $senderAccountId,
                'receiverAccountNumber' => $receiverAccountNumber,
                'title' => $title,
                'address' => $address,
                'receiverName' => $receiverName,
                'amount' => $amount,
            ],
            server: $this->getAuthHeaders(),
        );
        $response = $client->getResponse();

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertEquals(json_encode(['success' => true]), $response->getContent());
    }

    public function testGetUnauthorizedResponse(): void
    {
        $client = self::createClient();
        $client->request(
            method: 'POST',
            uri: '/_/user/transaction/create',
        );
        $response = $client->getResponse();

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testValidResponseOnInvalidPayload(): void
    {
        $client = self::createClient();
        $client->request(
            method: 'POST',
            uri: '/_/user/transaction/create',
            parameters: [
                'senderAccountId' => 0,
            ],
            server: $this->getAuthHeaders(),
        );
        $response = $client->getResponse();

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    /**
     * @return array<string, string>
     */
    protected function getAuthHeaders(): array
    {
        return [
            'HTTP_Authorization' => 'Bearer '
                . base64_encode(UserFixtures::USER_EMAIL . ':' . UserFixtures::USER_PLAIN_PASSWORD),
        ];
    }
}
