# Checkout-PHP-SDK Architecture

## Purpose

PayPal's official PHP SDK for the Orders v2 REST API, enabling merchants to
create, authorise, capture, patch, and refund orders.

## Directory Structure

```
lib/
  PayPalCheckoutSdk/
    Core/
      Pay_Pal_Http_Client.php       — HTTP client wrapping paypal/paypalhttp
      Production_Environment.php / Sandbox_Environment.php
                                    — base URL + credentials per environment
    Orders/
      Orders_Authorize_Request.php
      Orders_Capture_Request.php
      Orders_Create_Request.php
      Orders_Get_Request.php
      Orders_Patch_Request.php
      Orders_Validate_Request.php   — (internal)
samples/
  AuthorizeIntentExamples/          — runnable examples for auth-then-capture flow
    Create_Order.php / Authorize_Order.php / Capture_Order.php / Run_All.php
  CaptureIntentExamples/            — runnable examples for direct-capture flow
    Create_Order.php / Capture_Order.php / Run_All.php
  Pay_Pal_Client.php                — shared sandbox client factory used by examples
  Get_Order.php / Patch_Order.php / Refund_Order.php / Error_Sample.php
tests/
  Orders/
    Orders_*_Test.php                 — PHPUnit tests (live sandbox when creds set)
  Test_Harness.php
phpunit.xml
```

## Key Design Decisions

- **Request objects pattern**: each API operation is a self-contained request
  class (e.g. `Orders_Create_Request`) that holds the HTTP method, path, headers,
  and body; the `Pay_Pal_Http_Client` executes them and returns a typed response.
- **Environment abstraction**: `Production_Environment` and `Sandbox_Environment`
  encapsulate base URL + credential validation, making environment switching a
  constructor argument change.
- **Two intent flows**: the SDK explicitly supports both `AUTHORIZE` (two-step:
  authorise then capture) and `CAPTURE` (single-step) order intents, each
  demonstrated in separate example directories.
- **Thin wrapper**: the SDK is intentionally thin — it delegates HTTP transport
  to `paypal/paypalhttp` (`PayPalHttp\*` classes) and focuses on request/response shaping.

## Extension Points

- Subclass `Pay_Pal_Http_Client` to add custom middleware (logging, retries).
- Inject a custom `Pay_Pal_Checkout_Sdk\Core\Pay_Pal_Environment` subclass for private API gateways.

## Dependency Flow

```
Consumer
  └── Pay_Pal_Http_Client (env + credentials)
        └── Orders\Orders_Create_Request / Orders_Capture_Request / …
              └── paypal/paypalhttp  (PayPalHttp HTTP transport)
                    └── PayPal REST API (api.paypal.com / api.sandbox.paypal.com)
```
