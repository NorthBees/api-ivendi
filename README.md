# NorthBees iVendi API

[![Tests](https://github.com/northbees/api-ivendi/actions/workflows/tests.yml/badge.svg)](https://github.com/northbees/api-ivendi/actions/workflows/tests.yml)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.md)
[![PHP Version](https://img.shields.io/badge/php-%5E8.4-777bb4.svg?style=flat-square)](composer.json)

A Laravel SDK for the [iVendi Connect API](https://ivendi-connect.ivendi.com/):

- quote config: deposit, term and mileage ranges for the products a retailer offers;
- quotes: full finance quotes for every product;
- payment search: monthly payments for a vehicle across a matrix of terms, deposits and mileages, suited to precomputing finance for stock search;
- the retailer's representative example.

Requests go through Laravel's HTTP client, so `Http::fake()` works in tests. Responses are unwrapped from iVendi's `{data, errors}` envelope into typed, readonly DTOs.

This SDK targets iVendi Connect (`x-api-key` authentication). It does not support the legacy QuoteWare API, which used a username and password.

## Requirements

- PHP 8.4+
- Laravel 12 or 13

## Installation

```bash
composer require northbees/ivendi-api
```

The service provider and the `Ivendi` facade are auto-discovered.

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ivendi.config
```

## Configuration

```env
IVENDI_BASE_URL=https://...        # supplied by iVendi at onboarding
IVENDI_API_KEY=your-partner-key
IVENDI_QUOTEE_ID=retailer-quotee-id

# Optional
IVENDI_ACCEPT_LANGUAGE=en-GB
IVENDI_TIMEOUT=15
IVENDI_CONNECT_TIMEOUT=5
IVENDI_RETRY_TIMES=2
IVENDI_RETRY_SLEEP_MS=250
IVENDI_LOG=false
IVENDI_LOG_CHANNEL=
```

The API key identifies you, the partner. The `quoteeId` identifies the retailer being quoted for, so multi-tenant apps usually keep one API key in config and set the quotee per tenant.

## Usage

```php
use NorthBees\IvendiApi\Ivendi;

$ivendi = app(Ivendi::class)->withQuotee($tenant->ivendi_quotee_id);

$ivendi->quotes();         // Quote config, quotes
$ivendi->paymentSearch();  // Payment matrices
$ivendi->retailers();      // Representative example
```

The client is immutable. `withCredentials()`, `withBaseUrl()` and `withQuotee()` return new instances. It is bound as a scoped service, so queue workers and Octane get a fresh instance per job or request. A request's own `quoteeId` takes precedence over the client's.

### Describing a vehicle

```php
use NorthBees\IvendiApi\Enums\VehicleClass;
use NorthBees\IvendiApi\Requests\Asset;
use NorthBees\IvendiApi\Requests\AssetIdentifier;

$asset = new Asset(
    isNew: false,
    currentOdometerReading: 24000,
    registrationDate: $firstRegisteredAt,
    identifiers: [AssetIdentifier::capId(90132), AssetIdentifier::vrm('AB21CDE')],
    vatIncluded: true,
    vatQualifying: false,
    vehicleClass: VehicleClass::Car,
);
```

A CAP taxonomy code (`capId` or `capCode`) is needed for residual value based products such as PCP. A VRM on its own triggers a UK vehicle lookup, which iVendi charges for.

### Quote config

```php
use NorthBees\IvendiApi\Requests\QuoteConfigRequest;

foreach ($ivendi->quotes()->config(new QuoteConfigRequest($asset, 15995))->products as $product) {
    $product->facilityType;  // FacilityType::PersonalContractPurchase
    $product->term?->values; // [24, 36, 48]
    $product->deposit?->max; // 10000.0
}
```

### Quotes

```php
use NorthBees\IvendiApi\Enums\FacilityType;
use NorthBees\IvendiApi\Requests\QuoteRequest;

$result = $ivendi->quotes()->create(new QuoteRequest($asset, cashPrice: 15995, cashDeposit: 1000, term: 48, annualDistance: 10000));

$pcp = $result->cheapest(FacilityType::PersonalContractPurchase);

$pcp?->productName;
$pcp?->figures?->regularPayment;
$pcp?->figures?->apr;
$pcp?->figures?->totalPayable;
$pcp?->figures?->optionalFinalPayment();

$result->regulatoryText;
$ivendi->quotes()->find($pcp->quoteId);
```

### Payment search

```php
use NorthBees\IvendiApi\Requests\PaymentSearchRequest;

$result = $ivendi->paymentSearch()->search(new PaymentSearchRequest(
    $asset,
    cashPrice: 15995,
    terms: [24, 36, 48],
    annualMileages: [8000, 10000],
    deposits: [0, 1000, 2000],
));

foreach ($result->rows as $row) {
    foreach ($row->products->filter->isQuoted() as $product) {
        [$row->term, $row->deposit, $row->annualMileage, $product->facilityType(), $product->payment];
    }
}
```

Payment search covers one vehicle per request and returns payments only, without APR.

### Representative example

```php
$example = $ivendi->retailers()->representativeExample(); // defaults to the client's quotee

$example->funderName;
$example->quote?->figures?->apr;
```

### Fields the SDK does not model

Every request object has `with()`, which merges extra documented fields into its payload. Extra fields are merged last, so they win. Every response DTO keeps the raw data in `$attributes`.

## Errors

Every exception extends `NorthBees\IvendiApi\Exceptions\IvendiException`. Messages never contain the API key.

| Exception | When |
|---|---|
| `IvendiConnectionException` | The API could not be reached, or returned a 5xx after retries |
| `IvendiAuthenticationException` | The API key was rejected (401/403) |
| `IvendiValidationException` | The request failed validation (400). `$errors` holds each message |
| `IvendiRequestException` | Any other 4xx, or an envelope with errors and no data |
| `IvendiInvalidResponseException` | The body was not JSON, or had no `data` |
| `IvendiMissingConfigurationException` | No API key, base URL or quotee ID was configured or supplied |

Errors and warnings on individual product quotes are not thrown. They are returned on the DTOs (`$quote->errors`, `$quote->hasErrors()`), and `successful()` / `cheapest()` skip quotes with errors.

Connection failures and 429/502/503/504 responses are retried (`retry.times`, `retry.sleep_ms`). Other errors are not.

## Testing your application

`Ivendi::fake()` fakes the API through `Http::fake()`. Responses are keyed by endpoint name (`quotes.config`, `quotes.create`, `quotes.find`, `paymentSearch.search`, `retailers.representativeExample`) or `*`. Array responses are the envelope's `data`. `IvendiResponse` builds realistic data:

```php
use NorthBees\IvendiApi\Enums\Endpoint;
use NorthBees\IvendiApi\Facades\Ivendi;
use NorthBees\IvendiApi\Testing\IvendiResponse;

$fake = Ivendi::fake([
    Endpoint::QuoteConfig->value => IvendiResponse::quoteConfig(),
    Endpoint::PaymentSearch->value => fn (array $payload) => IvendiResponse::paymentGrid(
        $payload['parameters']['termsInMonths'], $payload['parameters']['deposits'], $payload['parameters']['annualMileages'],
    ),
    Endpoint::CreateQuotes->value => IvendiResponse::error('Retailer not found', 404),
]);

// ...

$fake->assertSent(Endpoint::PaymentSearch, fn (array $payload) => $payload['quoteeId'] === 'Q1');
```

Requests that were not faked fail with an `IvendiRequestException`.

## Development

```bash
composer test      # Pest
composer lint      # Pint
composer analyse   # Larastan
```

Live contract checks are excluded by default:

```bash
IVENDI_LIVE=1 IVENDI_BASE_URL=... IVENDI_API_KEY=... IVENDI_QUOTEE_ID=... vendor/bin/pest --group=live
```

### Not yet covered

The private APIs that need an OAuth client ID and secret (applications, drafts, finance checks, finance scans), leads, and suitability questions.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
