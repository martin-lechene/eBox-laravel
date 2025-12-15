<?php

namespace Ebox\Enterprise\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Ebox\Enterprise\Providers\EboxServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }
    
    protected function getPackageProviders($app): array
    {
        return [
            EboxServiceProvider::class,
        ];
    }
    
    protected function getEnvironmentSetUp($app): void
    {
        // Configuration de test
        $app['config']->set('database.default', 'testing');
    }
}

