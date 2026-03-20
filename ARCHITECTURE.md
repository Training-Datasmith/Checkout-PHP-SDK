# Checkout-PHP-SDK Architecture

## Purpose

PayPal's official PHP SDK for the Orders v2 REST API, enabling merchants to
create, authorise, capture, patch, and refund orders.

## Directory Structure

```
lib/
  PayPalCheckoutSdk/
    Core/
      PayPalHttpClient.php     — HTTP client wrapping paypal/paypalhttp
      ProductionEnvironment.php / SandboxEnvironment.php
                               — base URL + credentials per environment
    Orders/
      OrdersAuthorizeRequest.php
      OrdersCaptureRequest.php
      OrdersCreateRequest.php
      OrdersGetRequest.php
      OrdersPatchRequest.php
      OrdersValidateRequest.php — (internal)
samples/
  AuthorizeIntentExamples/     — runnable examples for auth-then-capture flow
    Create_Order.php / Authorize_Order.php / Capture_Order.php / Run_All.php
  CaptureIntentExamples/       — runnable examples for direct-capture flow
    Create_Order.php / Capture_Order.php / Run_All.php
  Pay_Pal_Client.php           — shared sandbox client factory used by examples
  Get_Order.php / Patch_Order.php / Refund_Order.php / Error_Sample.php
tests/
  Orders/
    Orders_*_Test.php          — PHPUnit tests (use HTTP replay / mock responses)
  Test_Harness.php
phpunit.xml
```

## Key Design Decisions

- **Request objects pattern**: each API operation is a self-contained request
  class (e.g. `OrdersCreateRequest`) that holds the HTTP method, path, headers,
  and body; the `PayPalHttpClient` executes them and returns a typed response.
- **Environment abstraction**: `ProductionEnvironment` and `SandboxEnvironment`
  encapsulate base URL + credential validation, making environment switching a
  constructor argument change.
- **Two intent flows**: the SDK explicitly supports both `AUTHORIZE` (two-step:
  authorise then capture) and `CAPTURE` (single-step) order intents, each
  demonstrated in separate example directories.
- **Thin wrapper**: the SDK is intentionally thin — it delegates HTTP transport
  to `paypal/paypalhttp` and focuses on request/response shaping.

## Extension Points

- Subclass `PayPalHttpClient` to add custom middleware (logging, retries).
- Inject a custom `PayPalEnvironment` subclass for private API gateways.

## Dependency Flow

```
Consumer
  └── PayPalHttpClient (env + credentials)
        └── Orders\OrdersCreateRequest / OrdersCaptureRequest / …
              └── paypal/paypalhttp  (HTTP transport)
                    └── PayPal REST API (api.paypal.com / api.sandbox.paypal.com)
```
