<?php

declare(strict_types=1);

namespace App\Tests\Support\Helper\SerializedTrait;

use Faker\Factory;
use Faker\Provider\Base as FakerBase;

trait UserSerializedTrait
{
    /**
     * @return array<string, string>
     */
    public function getUserArray(
        ?string $email = null,
        ?string $password = null,
        ?string $passwordRepeat = null,
        ?string $username = null,
    ): array {
        $password = $password ?? FakerBase::lexify('???????BIG1@');
        $passwordRepeat = $passwordRepeat ?? $password;
        $faker = Factory::create();

        return [
            'email' => $email ?? $faker->email(),
            'password' => $password,
            'passwordRepeat' => $passwordRepeat,
            'username' => $username ?? FakerBase::lexify('?????'),
        ];
    }
}
