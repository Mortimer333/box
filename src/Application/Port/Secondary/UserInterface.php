<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

interface UserInterface
{
    public function getId(): ?int;

    public function getEmail(): ?string;
}
