<?php

use Illuminate\Mail\Mailables\Attachment as LaravelAttachment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\Emails\Attachment;
use Modules\Emails\EmailMailable;
use Modules\Emails\Facades\Emails;

test('it sends a complete email through the configured Laravel mailer', function () {
    Mail::fake();

    Emails::make()
        ->from('sender@example.com', 'Sender')
        ->to('recipient@example.com', 'Recipient')
        ->cc('copy@example.com')
        ->bcc('hidden@example.com')
        ->replyTo('replies@example.com')
        ->subject('A test email')
        ->text('Plain text body')
        ->html('<p>HTML body</p>')
        ->send();

    Mail::assertSent(EmailMailable::class, function (EmailMailable $mailable): bool {
        $mailable->assertSeeInText('Plain text body');
        $mailable->assertSeeInHtml('HTML body');

        return $mailable->hasFrom('sender@example.com')
            && $mailable->hasTo('recipient@example.com')
            && $mailable->hasCc('copy@example.com')
            && $mailable->hasBcc('hidden@example.com')
            && $mailable->hasReplyTo('replies@example.com')
            && $mailable->hasSubject('A test email');
    });
});

test('it supports local and storage attachments', function () {
    Mail::fake();
    Storage::fake('local');
    Storage::disk('local')->put('reports/report.pdf', 'pdf contents');

    $localPath = tempnam(sys_get_temp_dir(), 'emails-');
    file_put_contents($localPath, 'local contents');

    Emails::make()
        ->to('recipient@example.com')
        ->subject('Attachments')
        ->text('See attached files.')
        ->attach(Attachment::fromPath($localPath)->as('local.txt')->withMime('text/plain'))
        ->attach(Attachment::fromStorageDisk('local', 'reports/report.pdf'))
        ->attach(Attachment::fromData('data contents', 'data.txt'))
        ->send();

    Mail::assertSent(EmailMailable::class, function (EmailMailable $mailable) use ($localPath): bool {
        $mailable->assertHasAttachment(
            LaravelAttachment::fromPath($localPath)
                ->as('local.txt')
                ->withMime('text/plain')
        );
        $mailable->assertHasAttachment(
            LaravelAttachment::fromStorageDisk('local', 'reports/report.pdf')
        );
        $mailable->assertHasAttachedData('data contents', 'data.txt');

        return true;
    });

    unlink($localPath);
});

test('it queues an email with the configured queue policy', function () {
    Mail::fake();
    config()->set('emails.queue.connection', 'sync');
    config()->set('emails.queue.queue', 'emails');
    config()->set('emails.queue.tries', 4);
    config()->set('emails.queue.backoff', [10, 30]);

    Emails::make()
        ->to('recipient@example.com')
        ->subject('Queued email')
        ->text('Queued body')
        ->queue();

    Mail::assertQueued(EmailMailable::class, function (EmailMailable $mailable): bool {
        return $mailable->connection === 'sync'
            && $mailable->queue === 'emails'
            && $mailable->tries === 4
            && $mailable->backoff === [10, 30];
    });
});

test('it requires a recipient and a body', function () {
    expect(fn () => Emails::make()->subject('Missing recipient')->text('Body')->send())
        ->toThrow(LogicException::class, 'At least one email recipient is required.');

    expect(fn () => Emails::make()->to('recipient@example.com')->subject('Missing body')->send())
        ->toThrow(LogicException::class, 'An email text or HTML body is required.');
});
