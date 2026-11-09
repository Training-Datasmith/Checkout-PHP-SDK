<?php

declare (strict_types=1);
namespace Pay_Pal_Checkout_Sdk\Core;

use PayPalHttp\HttpClient;
class Pay_Pal_Http_Client extends HttpClient
{
    /**
     * @var Authorization_Injector
     */
    public $auth_injector;
    public function __construct(Pay_Pal_Environment $environment, private $refresh_token = null)
    {
        parent::__construct($environment);
        $this->auth_injector = new Authorization_Injector($this, $environment, $this->refresh_token);
        $this->addInjector($this->auth_injector);
        $this->addInjector(new Gzip_Injector());
        $this->addInjector(new FPTI_Instrumentation_Injector());
    }

    public function userAgent()
    {
        return User_Agent::get_value();
    }
}