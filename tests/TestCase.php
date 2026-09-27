<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Tests;

use Illuminate\Support\Facades\Http;
use NorthBees\IvendiApi\IvendiServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    protected function getPackageProviders($app)
    {
        return [
            IvendiServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('ivendi.api_key', 'test-api-key');
        $app['config']->set('ivendi.base_url', 'https://api.ivendi.test');
        $app['config']->set('ivendi.quotee_id', 'CONFIG-QUOTEE');
        $app['config']->set('ivendi.retry.sleep_ms', 0);
    }
}
