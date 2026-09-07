<?php

namespace Tests;

use Modules\Emails\EmailsServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [EmailsServiceProvider::class];
    }
}
