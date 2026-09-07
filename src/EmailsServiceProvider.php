<?php

namespace Modules\Emails;

use Illuminate\Support\ServiceProvider;

class EmailsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/emails.php', 'emails');

        $this->app->singleton(EmailSender::class);
        $this->app->alias(EmailSender::class, 'emails');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/emails.php' => config_path('emails.php'),
        ], 'emails-config');
    }
}
