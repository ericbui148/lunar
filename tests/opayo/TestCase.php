<?php

namespace Lunar\Tests\Opayo;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Kalnoy\Nestedset\NestedSetServiceProvider;
use Livewire\LivewireServiceProvider;
use Lunar\LunarServiceProvider;
use Lunar\Opayo\OpayoServiceProvider;
use Lunar\Shipping\ShippingServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $getResponse = fn ($file) => Http::response(
            json_decode(
                file_get_contents(
                    __DIR__."/Opayo/{$file}.json"
                ), true
            )
        );

        Http::fake([
            'https://pi-test.sagepay.com/api/v1/transactions' => fn (Request $request) => match ($request->data()['paymentMethod']['card']['merchantSessionKey']) {
                'SUCCESS' => $getResponse('transaction_201'),
                'FAILED' => $getResponse('transaction_not_authed'),
                'SUCCESS_3DSV2' => $getResponse('transaction_202'),
                default => Http::response('ok'),
            },
            'https://pi-test.sagepay.com/api/v1/transactions/3DSV2_SUCCESS/3d-secure-challenge' => fn (Request $request) => $getResponse('3dsv2_successful'),
            'https://pi-test.sagepay.com/api/v1/transactions/3DSV2_FAILURE/3d-secure-challenge' => fn (Request $request) => $getResponse('3dsv2_not_authed'),
            'https://pi-test.sagepay.com/api/v1/transactions/3DSV2_SUCCESS' => fn (Request $request) => $getResponse('3dsv2_successful'),
            'https://pi-test.sagepay.com/api/v1/transactions/3DSV2_FAILURE' => fn (Request $request) => $getResponse('3dsv2_not_authed'),
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            LunarServiceProvider::class,
            LivewireServiceProvider::class,
            MediaLibraryServiceProvider::class,
            ActivitylogServiceProvider::class,
            ConverterServiceProvider::class,
            NestedSetServiceProvider::class,
            ShippingServiceProvider::class,
            BlinkServiceProvider::class,
            OpayoServiceProvider::class,

        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }
}
