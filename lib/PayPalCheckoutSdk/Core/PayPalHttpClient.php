<?php

declare (strict_types=1);
namespace Pay_Pal_Checkout_Sdk\Core;

use Pay_Pal_Http\Http_Client;
class Pay_Pal_Http_Client extends Http_Client
{
    /**
     * @var \PayPalCheckoutSdk\Core\AuthorizationInjector
     */
    public $auth_injector;
    public function __construct(Pay_Pal_Environment $environment, private $refresh_token = null)
    {
        parent::__construct($environment);
        $this->auth_injector = new Authorization_Injector($this, $environment, $this->refresh_token);
        $this->add_injector($this->auth_injector);
        $this->add_injector(new Gzip_Injector());
        $this->add_injector(new Fpti_Instrumentation_Injector());
    }
    public function user_agent()
    {
        return User_Agent::get_value();
    }
}