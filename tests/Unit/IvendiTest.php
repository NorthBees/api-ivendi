<?php

declare(strict_types=1);

use NorthBees\IvendiApi\Exceptions\IvendiMissingConfigurationException;
use NorthBees\IvendiApi\Ivendi;
use NorthBees\IvendiApi\IvendiCredentials;

it('is bound as a scoped service', function () {
    expect(app(Ivendi::class))->toBe(app(Ivendi::class))
        ->and(app('ivendi'))->toBe(app(Ivendi::class));
});

it('returns new instances from the with methods', function () {
    $client = app(Ivendi::class);
    $tenant = $client->withCredentials(new IvendiCredentials('tenant-key'))->withQuotee('TENANT-QUOTEE')->withBaseUrl('https://other.test');

    expect($tenant)->not->toBe($client)
        ->and($tenant->credentials()->apiKey)->toBe('tenant-key')
        ->and($tenant->quoteeId())->toBe('TENANT-QUOTEE')
        ->and($tenant->baseUrl())->toBe('https://other.test')
        ->and($client->quoteeId())->toBe('CONFIG-QUOTEE');
});

it('throws when the base URL is not configured', function () {
    config()->set('ivendi.base_url', null);
    app()->forgetScopedInstances();

    app(Ivendi::class)->baseUrl();
})->throws(IvendiMissingConfigurationException::class);

it('throws when no API key is configured', function () {
    config()->set('ivendi.api_key', '');
    app()->forgetScopedInstances();

    expect(app(Ivendi::class)->hasCredentials())->toBeFalse();

    app(Ivendi::class)->credentials();
})->throws(IvendiMissingConfigurationException::class);

it('redacts the API key from dumps', function () {
    expect(print_r(new IvendiCredentials('secret-key'), true))->not->toContain('secret-key');
});
