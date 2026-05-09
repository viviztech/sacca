<?php

namespace App\Modules\Attendance\Services;

readonly class GPSVerificationResult
{
    private function __construct(
        public bool $verified,
        public string $errorCode,
        public string $message,
    ) {}

    public static function pass(bool $verified, string $message): self
    {
        return new self($verified, '', $message);
    }

    public static function fail(string $errorCode, string $message): self
    {
        return new self(false, $errorCode, $message);
    }

    public function passed(): bool
    {
        return $this->verified;
    }

    public function failed(): bool
    {
        return ! $this->verified;
    }
}
