<?php

namespace Modules\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\HtmlString;

class EmailMailable extends Mailable
{
    use Queueable;

    public int $tries = 3;

    /** @var int|list<int> */
    public int|array $backoff = [];

    public function __construct(public readonly EmailMessage $email)
    {
        $this->subject($email->subjectLine());
    }

    public function build(): static
    {
        foreach ($this->email->fromAddresses() as $address) {
            $this->from($address->address, $address->name);
        }

        foreach ($this->email->recipients() as $address) {
            $this->to($address->address, $address->name);
        }

        foreach ($this->email->ccRecipients() as $address) {
            $this->cc($address->address, $address->name);
        }

        foreach ($this->email->bccRecipients() as $address) {
            $this->bcc($address->address, $address->name);
        }

        foreach ($this->email->replyToRecipients() as $address) {
            $this->replyTo($address->address, $address->name);
        }

        foreach ($this->email->attachments() as $attachment) {
            $this->attach($attachment->toLaravelAttachment());
        }

        return $this;
    }

    protected function buildView(): array
    {
        $view = [];

        if ($this->email->htmlBody() !== null) {
            $view['html'] = new HtmlString($this->email->htmlBody());
        }

        if ($this->email->textBody() !== null) {
            $view['text'] = new HtmlString($this->email->textBody());
            $view['raw'] = $this->email->textBody();
        }

        return $view;
    }
}
