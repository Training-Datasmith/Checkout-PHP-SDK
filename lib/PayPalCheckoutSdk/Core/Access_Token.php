<?php

declare (strict_types=1);
namespace Pay_Pal_Checkout_Sdk\Core;

class Access_Token
{
    private readonly int $create_date;
    public function __construct(public $token, public $token_type, public $expires_in)
    {
        $this->create_date = time();
    }
    public function is_expired(): bool
    {
        return time() >= $this->create_date + $this->expires_in;
    }
}