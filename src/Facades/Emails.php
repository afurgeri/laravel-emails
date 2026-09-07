<?php

namespace Modules\Emails\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Emails\EmailMessage;

class Emails extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'emails';
    }

    public static function make(): EmailMessage
    {
        return static::getFacadeRoot()->make();
    }
}
