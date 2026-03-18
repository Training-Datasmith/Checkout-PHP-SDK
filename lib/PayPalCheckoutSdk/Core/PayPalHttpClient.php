<?php

declare(strict_types=1);

namespace PayPalCheckoutSdk\Core;

use PayPalHttp\HttpClient;

class PayPalHttpClient extends HttpClient
{
    /**
     * @var \PayPalCheckoutSdk\Core\AuthorizationInjector
     */
    public $authInjector;

    public function __construct(PayPalEnvironment $environment, private $refreshToken = null)
    {
        parent::__construct($environment);
        $this->authInjector = new AuthorizationInjector($this, $environment, $this->refreshToken);
        $this->addInjector($this->authInjector);
        $this->addInjector(new GzipInjector());
        $this->addInjector(new FPTIInstrumentationInjector());
    }

    public function userAgent()
    {
        return UserAgent::getValue();
    }
}
