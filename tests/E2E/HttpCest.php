<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Adapter\Secondary\DataFixtures\Test\BankAccountFixtures;
use App\Adapter\Secondary\Entity\BankAccount;
use App\Application\Infrastructure\Message\ProcessTransactionMessage;
use App\Tests\Support\E2ETester;
use Codeception\Util\HttpCode;
use Zenstruck\Messenger\Test\Transport\TestTransport;

class HttpCest extends AbstractCest
{
    protected TestTransport $messenger;

    public function _before(E2ETester $I): void
    {
        parent::_before($I);
        $this->messenger = $I->getMessenger('process_transaction');
        $this->messenger->reset();
    }

    public function transferFoundsBetweenInternalAccounts(E2ETester $I): void
    {
        /** @var ?BankAccount $sender */
        $sender = $I->grabEntityFromRepository(BankAccount::class, [
            'accountNumber' => BankAccountFixtures::ACCOUNT_NUMBER_ONE,
        ]);
        if (!$sender) {
            $I->fail(
                'Test is lacking prepared data to be preformed - Bank account: '
                . BankAccountFixtures::ACCOUNT_NUMBER_ONE
            );
        }
        /** @var BankAccount $sender */

        /** @var ?BankAccount $receiver */
        $receiver = $I->grabEntityFromRepository(BankAccount::class, [
            'accountNumber' => BankAccountFixtures::ACCOUNT_NUMBER_TWO,
        ]);
        if (!$receiver) {
            $I->fail(
                'Test is lacking prepared data to be preformed - Bank account: '
                . BankAccountFixtures::ACCOUNT_NUMBER_TWO
            );
        }
        /** @var BankAccount $receiver */

        $senderCreditBefore = $sender->getCredit();
        $receiverCreditBefore = $receiver->getCredit();

        $I->request('/user/transaction/create', 'POST', [
            'senderAccountId' => $sender->getId(),
            'receiverAccountNumber' => BankAccountFixtures::ACCOUNT_NUMBER_TWO,
            'title' => 'E2E test transaction',
            'receiverName' => 'John Smith',
            'amount' => 10,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $sender = $I->grabEntityFromRepository(BankAccount::class, [
            'accountNumber' => BankAccountFixtures::ACCOUNT_NUMBER_ONE,
        ]);
        $receiver = $I->grabEntityFromRepository(BankAccount::class, [
            'accountNumber' => BankAccountFixtures::ACCOUNT_NUMBER_TWO,
        ]);
        $I->assertNotSame($senderCreditBefore, $sender->getCredit());
        $I->assertNotSame($receiverCreditBefore, $receiver->getCredit());

        $this->messenger->throwExceptions();
        $this->messenger->queue()->assertCount(1);
        $this->messenger->process(1);
        $rejected = $this->messenger->rejected();
        $I->assertSame(0, $rejected->count(), 'Transaction Fee Transfer message was rejected');
        $this->messenger->queue()->assertEmpty();
    }

    public function transferFoundsBetweenExternalAccounts(E2ETester $I): void
    {
        /** @var ?BankAccount $sender */
        $sender = $I->grabEntityFromRepository(BankAccount::class, [
            'accountNumber' => BankAccountFixtures::ACCOUNT_NUMBER_ONE,
        ]);
        if (!$sender) {
            $I->fail(
                'Test is lacking prepared data to be preformed - Bank account: '
                . BankAccountFixtures::ACCOUNT_NUMBER_ONE
            );
        }
        /** @var BankAccount $sender */

        $senderCreditBefore = $sender->getCredit();
        $senderReservedBefore = $sender->getReserved();

        $I->request('/user/transaction/create', 'POST', [
            'senderAccountId' => $sender->getId(),
            'receiverAccountNumber' => 'not_existing_account',
            'title' => 'E2E test transaction',
            'receiverName' => 'John Smith',
            'amount' => 10,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $sender = $I->grabEntityFromRepository(BankAccount::class, [
            'accountNumber' => BankAccountFixtures::ACCOUNT_NUMBER_ONE,
        ]);
        $I->assertSame($senderCreditBefore, $sender->getCredit());
        $I->assertNotSame($senderReservedBefore, $sender->getReserved());

        $this->messenger->throwExceptions();
        $this->messenger->queue()->assertCount(1);
        $this->messenger->queue()->assertContains(ProcessTransactionMessage::class);
        $this->messenger->process(1);
        $rejected = $this->messenger->rejected();
        $I->assertSame(0, $rejected->count(), 'External message was rejected');
        $this->messenger->queue()->assertCount(1);
        $this->messenger->process(1);
        $rejected = $this->messenger->rejected();
        $I->assertSame(0, $rejected->count(), 'Transaction Fee Transfer message was rejected');
        $this->messenger->queue()->assertEmpty();

        $sender = $I->grabEntityFromRepository(BankAccount::class, [
            'accountNumber' => BankAccountFixtures::ACCOUNT_NUMBER_ONE,
        ]);
        $I->assertSame($senderReservedBefore, $sender->getReserved(), 'Reserved was not properly cleared');
        $I->assertNotSame($senderCreditBefore, $sender->getCredit(), 'Credit was not properly removed');
    }
}
