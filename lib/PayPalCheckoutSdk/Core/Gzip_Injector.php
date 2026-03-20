<?php

declare (strict_types=1);
namespace Pay_Pal_Checkout_Sdk\Core;

use Pay_Pal_Http\Injector;
class Gzip_Injector implements Injector
{
    public function inject($http_request): void
    {
        $http_request->headers['Accept-Encoding'] = 'gzip';
    }
}