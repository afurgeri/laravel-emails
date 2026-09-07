<?php

namespace Modules\Emails;

use Illuminate\Mail\Mailables\Attachment as LaravelAttachment;

class Attachment
{
    private function __construct(
        private readonly string $source,
        private readonly string $value,
        private readonly ?string $name = null,
        private readonly ?string $mime = null,
        private readonly ?string $disk = null,
    ) {}

    public static function fromPath(string $path): self
    {
        return new self('path', $path);
    }

    public static function fromStorage(string $path): self
    {
        return new self('storage', $path);
    }

    public static function fromStorageDisk(string $disk, string $path): self
    {
        return new self('storage', $path, disk: $disk);
    }

    public static function fromData(string $data, string $name): self
    {
        return new self('data', $data, name: $name);
    }

    public function as(string $name): self
    {
        return new self($this->source, $this->value, $name, $this->mime, $this->disk);
    }

    public function withMime(string $mime): self
    {
        return new self($this->source, $this->value, $this->name, $mime, $this->disk);
    }

    public function toLaravelAttachment(): LaravelAttachment
    {
        $attachment = match ($this->source) {
            'path' => LaravelAttachment::fromPath($this->value),
            'storage' => $this->disk === null
                ? LaravelAttachment::fromStorage($this->value)
                : LaravelAttachment::fromStorageDisk($this->disk, $this->value),
            'data' => LaravelAttachment::fromData(fn (): string => $this->value, $this->name ?? 'attachment'),
        };

        if ($this->name !== null && $this->source !== 'data') {
            $attachment = $attachment->as($this->name);
        }

        if ($this->mime !== null) {
            $attachment = $attachment->withMime($this->mime);
        }

        return $attachment;
    }
}
