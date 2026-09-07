# Laravel Emails

A small fluent API for sending emails through Laravel's configured mailers. It uses Laravel Mail for transport and MIME message creation, so SMTP and every provider supported by Laravel remain configured in the consuming application.

## Requirements

- PHP 8.3+
- Laravel 13+

## Installation

```bash
composer require afurgeri/laravel-emails
```

The service provider is discovered automatically. Publish the optional package configuration with:

```bash
php artisan vendor:publish --tag=emails-config
```

The package does not configure a provider for you. Configure the mailer in the application's `config/mail.php` and environment file as usual. For example:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-user
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="Example App"
```

## Sending an Email

Use the `Emails` facade from any application service, action, controller, or job:

```php
use Modules\Emails\Facades\Emails;

Emails::make()
    ->to('customer@example.com', 'Customer')
    ->subject('Your order is ready')
    ->text('Your order is ready for pickup.')
    ->html('<p>Your order is ready for pickup.</p>')
    ->send();
```

At least one recipient and one body (`text` or `html`) are required. The application's global `from` address is used unless the message defines its own sender:

```php
Emails::make()
    ->from('billing@example.com', 'Billing')
    ->to('customer@example.com')
    ->subject('Invoice')
    ->html('<p>Your invoice is attached.</p>')
    ->send();
```

Additional recipients can be added with `cc`, `bcc`, and `replyTo`:

```php
Emails::make()
    ->to('customer@example.com')
    ->cc('accounting@example.com')
    ->bcc('audit@example.com')
    ->replyTo('support@example.com')
    ->subject('Invoice')
    ->text('Your invoice is attached.')
    ->send();
```

## Selecting a Mailer

The default mailer is read from `config('emails.mailer')`, which can be set with `EMAILS_MAILER`. A message can select another mailer when the application has configured multiple mailers in `config/mail.php`:

```php
Emails::make()
    ->mailer('smtp-secondary')
    ->to('customer@example.com')
    ->subject('Notification')
    ->text('This message uses the secondary mailer.')
    ->send();
```

## Attachments

Attachments are represented by serializable references and are resolved when Laravel sends the message.

### Local Files

```php
use Modules\Emails\Attachment;

Emails::make()
    ->to('customer@example.com')
    ->subject('Report')
    ->text('The report is attached.')
    ->attach(
        Attachment::fromPath(storage_path('app/reports/report.pdf'))
            ->as('monthly-report.pdf')
            ->withMime('application/pdf')
    )
    ->send();
```

### Laravel Storage

```php
Emails::make()
    ->to('customer@example.com')
    ->subject('Document')
    ->text('The document is attached.')
    ->attach(
        Attachment::fromStorageDisk('s3', 'documents/contract.pdf')
            ->as('contract.pdf')
    )
    ->queue();
```

`Attachment::fromStorage($path)` uses the application's default filesystem disk.

### In-Memory Data

Use `fromData` for small generated files:

```php
Emails::make()
    ->to('customer@example.com')
    ->subject('Export')
    ->text('The export is attached.')
    ->attach(
        Attachment::fromData($csvContent, 'export.csv')
            ->withMime('text/csv')
    )
    ->send();
```

For queued emails, persist large files in Laravel Storage instead of keeping their contents in the queued payload.

### `laravel-documents`

The package does not depend directly on `laravel-documents`. A stored document can be attached using its persisted Storage metadata:

```php
Emails::make()
    ->to('customer@example.com')
    ->subject('Document')
    ->text('The document is attached.')
    ->attach(
        Attachment::fromStorageDisk($document->disk, $document->path)
            ->as($document->original_name)
            ->withMime($document->mime_type)
    )
    ->queue();
```

Authorize the document before attaching it, and keep it available until the queued email has been processed.

## Queued Emails

Call `queue()` to send the email through Laravel's queue:

```php
Emails::make()
    ->to('customer@example.com')
    ->subject('Background notification')
    ->text('This email is sent by a queue worker.')
    ->queue();
```

The package uses the queue connection and queue name configured in `config/emails.php`:

```dotenv
EMAILS_QUEUE_CONNECTION=database
EMAILS_QUEUE=emails
EMAILS_QUEUE_TRIES=3
```

The default retry delays are 60, 300, and 900 seconds. Run a worker to process queued emails:

```bash
php artisan queue:work database --queue=emails
```

Failed jobs are handled by Laravel and stored according to the application's `config/queue.php` configuration. As with any email retry, a provider accepting a message before a connection failure can result in a duplicate delivery.

If an email is queued inside a database transaction and depends on data from that transaction, dispatch it after commit according to the application's queue configuration.

## Testing

```bash
composer test
```

Applications can use Laravel's normal mail assertions:

```php
use Modules\Emails\EmailMailable;
use Illuminate\Support\Facades\Mail;

Mail::fake();

// ... send or queue an email

Mail::assertSent(EmailMailable::class);
Mail::assertQueued(EmailMailable::class);
```

## Scope

This package intentionally does not implement its own SMTP provider, IMAP support, Gmail API/OAuth, email history, or delivery tracking. Those concerns remain either in Laravel's mail configuration or outside the MVP.
