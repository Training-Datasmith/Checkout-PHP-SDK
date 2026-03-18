<?php

declare(strict_types=1);

namespace PayPalCheckoutSdk\Core;

class AccessToken
{
    private readonly int $createDate;

    public function __construct(public $token, public $tokenType, public $expiresIn)
    {
        $this->createDate = time();
    }

    public function isExpired(): bool
    {
        return time() >= $this->createDate + $this->expiresIn;
    }
}
