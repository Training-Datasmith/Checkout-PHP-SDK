<?php

declare (strict_types=1);
namespace Pay_Pal_Checkout_Sdk\Core;

use Pay_Pal_Http\Injector;
class Fpti_Instrumentation_Injector implements Injector
{
    public function inject($request): void
    {
        $request->headers['sdk_name'] = 'Checkout SDK';
        $request->headers['sdk_version'] = '1.0.2';
        $request->headers['sdk_tech_stack'] = 'PHP ' . PHP_VERSION;
        $request->headers['api_integration_type'] = 'PAYPALSDK';
    }
}