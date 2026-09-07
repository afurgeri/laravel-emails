<?php

namespace Modules\Emails;

use Illuminate\Mail\Mailables\Address;

class EmailMessage
{
    /** @var list<Address> */
    private array $to = [];

    /** @var list<Address> */
    private array $cc = [];

    /** @var list<Address> */
    private array $bcc = [];

    /** @var list<Address> */
    private array $replyTo = [];

    /** @var list<Address> */
    private array $from = [];

    /** @var list<Attachment> */
    private array $attachments = [];

    private ?string $subject = null;

    private ?string $text = null;

    private ?string $html = null;

    private ?string $mailer = null;

    public function to(string|Address $address, ?string $name = null): self
    {
        $this->to[] = $this->address($address, $name);

        return $this;
    }

    public function cc(string|Address $address, ?string $name = null): self
    {
        $this->cc[] = $this->address($address, $name);

        return $this;
    }

    public function bcc(string|Address $address, ?string $name = null): self
    {
        $this->bcc[] = $this->address($address, $name);

        return $this;
    }

    public function replyTo(string|Address $address, ?string $name = null): self
    {
        $this->replyTo[] = $this->address($address, $name);

        return $this;
    }

    public function from(string|Address $address, ?string $name = null): self
    {
        $this->from[] = $this->address($address, $name);

        return $this;
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function text(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function html(string $html): self
    {
        $this->html = $html;

        return $this;
    }

    public function mailer(?string $mailer): self
    {
        $this->mailer = $mailer;

        return $this;
    }

    public function attach(Attachment $attachment): self
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    public function send(): void
    {
        app(EmailSender::class)->send($this);
    }

    public function queue(): void
    {
        app(EmailSender::class)->queue($this);
    }

    /** @return list<Address> */
    public function recipients(): array
    {
        return $this->to;
    }

    /** @return list<Address> */
    public function ccRecipients(): array
    {
        return $this->cc;
    }

    /** @return list<Address> */
    public function bccRecipients(): array
    {
        return $this->bcc;
    }

    /** @return list<Address> */
    public function replyToRecipients(): array
    {
        return $this->replyTo;
    }

    /** @return list<Address> */
    public function fromAddresses(): array
    {
        return $this->from;
    }

    /** @return list<Attachment> */
    public function attachments(): array
    {
        return $this->attachments;
    }

    public function subjectLine(): string
    {
        return $this->subject ?? throw new \LogicException('An email subject is required.');
    }

    public function textBody(): ?string
    {
        return $this->text;
    }

    public function htmlBody(): ?string
    {
        return $this->html;
    }

    public function mailerName(): ?string
    {
        return $this->mailer;
    }

    public function validate(): void
    {
        if ($this->to === []) {
            throw new \LogicException('At least one email recipient is required.');
        }

        if ($this->text === null && $this->html === null) {
            throw new \LogicException('An email text or HTML body is required.');
        }
    }

    private function address(string|Address $address, ?string $name): Address
    {
        return $address instanceof Address ? $address : new Address($address, $name);
    }
}
