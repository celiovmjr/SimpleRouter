<?php

declare(strict_types=1);

namespace SimpleRouter\Application\Validation\Rules;

use SimpleRouter\Domain\Contracts\ValidationRule;

abstract class AbstractRule implements ValidationRule
{
    protected string $customMessage = '';

    public function withMessage(string $message): self
    {
        $this->customMessage = $message;
        return $this;
    }

    public function message(): string
    {
        return $this->customMessage ?: $this->defaultMessage();
    }

    abstract protected function defaultMessage(): string;
}