<?php

namespace Modules\Emails;

use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Contracts\Mail\Mailer;

class EmailSender
{
    public function __construct(private readonly MailFactory $mail) {}

    public function make(): EmailMessage
    {
        return new EmailMessage;
    }

    public function send(EmailMessage $email): void
    {
        $email->validate();

        $this->mailer($email)->send(new EmailMailable($email));
    }

    public function queue(EmailMessage $email): void
    {
        $email->validate();

        $mailable = new EmailMailable($email);
        $queue = config('emails.queue', []);

        if (($queue['connection'] ?? null) !== null) {
            $mailable->onConnection($queue['connection']);
        }

        if (($queue['queue'] ?? null) !== null) {
            $mailable->onQueue($queue['queue']);
        }

        if (($queue['tries'] ?? null) !== null) {
            $mailable->tries = (int) $queue['tries'];
        }

        $mailable->backoff = $queue['backoff'] ?? [];

        $this->mailer($email)->queue($mailable);
    }

    private function mailer(EmailMessage $email): Mailer
    {
        return $this->mail->mailer($email->mailerName() ?? config('emails.mailer'));
    }
}
