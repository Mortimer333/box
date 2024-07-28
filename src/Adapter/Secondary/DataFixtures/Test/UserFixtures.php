<?php

declare(strict_types=1);

namespace App\Adapter\Secondary\DataFixtures\Test;

use App\Adapter\Secondary\DataFixtures\TestFixturesAbstract;
use App\Adapter\Secondary\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends TestFixturesAbstract
{
    public const NORMAL_USER = 'normal_user';
    public const USER_EMAIL = 'user@test.com';
    public const USER_NAME = 'user';
    public const USER_SURNAME = 'surname';
    public const USER_PLAIN_PASSWORD = 'passPASS123@';

    public function __construct(
        protected UserPasswordHasherInterface $hasher,
    ) {
    }

    public static function getGroups(): array
    {
        return array_merge(['user'], parent::getGroups());
    }

    public function load(ObjectManager $manager): void
    {
        $user = (new User())
            ->setEmail(self::USER_EMAIL)
            ->setFirstname(self::USER_NAME)
            ->setSurname(self::USER_SURNAME)
            ->setRoles(['ROLE_USER'])
        ;
        $this->setHashedPassword($user, self::USER_PLAIN_PASSWORD);
        $manager->persist($user);
        $this->setReference(self::NORMAL_USER, $user);
        $manager->flush();
    }

    protected function setHashedPassword(User $user, string $plainPassword): void
    {
        $user->setPassword($this->hasher->hashPassword($user, $plainPassword));
    }
}
